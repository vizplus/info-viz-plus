<?php
if('check-login-available'==$path_array[2]){
	header("Content-type:text/html; charset=UTF-8");
	header('HTTP/1.1 200 Ok');
	$api=new viz_jsonrpc_web($api_arr[array_rand($api_arr)]);
	$account_login=$_POST['account_login'];
	$account_login=preg_replace('~[^a-z0-9\-]~','',$account_login);
	$user_arr=$api->execute_method('get_accounts',array(array($account_login)))[0];
	if($user_arr['name']){
		print '{"result":"failure"}';
	}
	else{
		print '{"result":"success"}';
	}
	exit;
}
if('check-invite-public'==$path_array[2]){
	header("Content-type:text/html; charset=UTF-8");
	header('HTTP/1.1 200 Ok');
	$result=$db->sql_row("SELECT * FROM `invites` WHERE `invite_key`='".$db->prepare($path_array[3])."'");
	if($result){
		$result['creator']=get_account_name($result['creator']);
		if($result['initiator']){
			$result['initiator']=get_account_name($result['initiator']);
		}
		if($result['receiver']){
			$result['receiver']=get_account_name($result['receiver']);
		}
		print json_encode(['result'=>$result]);
	}
	else{
		print json_encode(['result'=>false]);
	}
	exit;
}
if('check-invite-secret'==$path_array[2]){
	header("Content-type:text/html; charset=UTF-8");
	header('HTTP/1.1 200 Ok');
	$result=$db->sql_row("SELECT * FROM `invites` WHERE `secret_key`='".$db->prepare($path_array[3])."'");
	if($result){
		$result['creator']=get_account_name($result['creator']);
		if($result['initiator']){
			$result['initiator']=get_account_name($result['initiator']);
		}
		if($result['receiver']){
			$result['receiver']=get_account_name($result['receiver']);
		}
		print json_encode(['result'=>$result]);
	}
	else{
		print json_encode(['result'=>false]);
	}
	exit;
}
if('ops-history'==$path_array[2]){
	header("Content-type:text/html; charset=UTF-8");
	header('HTTP/1.1 200 Ok');
	$account='';
	$account_id=false;
	if(isset($_POST['account'])){
		$account=$_POST['account'];
		$account_id=$db->select_one('accounts','id',"WHERE `name`='".$db->prepare($account)."'");
	}
	if($account_id){
		$ops_arr=array();
		$q=$db->sql("SELECT `id`,`name` FROM `ops_type`");
		while($m=$db->row($q)){
			$ops_arr[$m['id']]=$m['name'];
		}
		$type_arr=array(
			'Аккаунты'=>array(5,45,46,47,48),
			'Капитал'=>array(4,8,19,21,29,31),
			'Переводы'=>array(7,21,24,26,30),
			'Награды'=>array(37,38,40),
			'ДАО'=>array(3,6,14,22,23,27,32,33,34,36),
			'Подписки'=>array(41,42,43,44,),
		);
		$addon='';
		$last_id=false;
		if(isset($_POST['last_id'])){
			$last_id=(int)$_POST['last_id'];
			$addon.='`ops_link`.`id`<'.$last_id.' AND ';
		}
		$per_page=100;
		$sql='SELECT `ops_link`.`id` as `order_id`, `ops`.*
		FROM `ops_link`
		RIGHT JOIN `ops` ON `ops_link`.`op`=`ops`.`id`
		WHERE '.$addon.'`ops_link`.`account`='.$account_id.'
		ORDER BY `order_id` DESC
		LIMIT '.$per_page;
		$q=$db->sql($sql);
		$count=0;
		$account_arr['id']=$account_id;
		$account_arr['name']=$account;
		while($m=$db->row($q)){
			$descr='';
			$op_trx=false;
			if(0==$m['v']){
				if($m['trx']){
					$op_trx=$db->sql_row("SELECT HEX(`hash`) as `hash` FROM `trx` WHERE `id`='".$m['trx']."'");
					if('000000000000000000000000'==substr($op_trx['hash'],-24)){
						$op_trx['hash']=substr($op_trx['hash'],0,-24);
					}
				}
			}
			$op_json=json_decode($m['json'],true);
			$ignore=false;
			if(2==$m['type']){
				$op_type='witness_update';
				if('VIZ1111111111111111111111111111111114T1Anm'==$op_json['block_signing_key']){
					$op_type.='_stop';
				}
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['text'=>htmlspecialchars($op_json['url']),'key'=>$op_json['block_signing_key']]);
			}
			else
			if(3==$m['type']){
				$op_type='account_witness_vote';
				if($account_arr['name']==$op_json['account']){
					if(!$op_json['approve']){
						$op_type.='_stop';
					}
					$descr=ltmp($ltmp_arr['ops-history'][$op_type],['account'=>$op_json['account'],'witness'=>$op_json['witness']]);
				}
				else{
					$op_type.='_income';
					if(!$op_json['approve']){
						$op_type.='_stop';
					}
					$descr=ltmp($ltmp_arr['ops-history'][$op_type],['account'=>$op_json['account']]);
				}
			}
			else
			if(4==$m['type']){
				$op_type='withdraw_vesting';
				if('0.000000 SHARES'!=$op_json['vesting_shares']){
					$descr=ltmp($ltmp_arr['ops-history'][$op_type],['shares'=>short_viz($op_json['vesting_shares'])]);
				}
				else{
					$descr=ltmp($ltmp_arr['ops-history']['withdraw_vesting_stop']);
				}
			}
			else
			if(5==$m['type']){
				$op_type='account_create';
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['account'=>$op_json['new_account_name']]);
				if('0.000 VIZ'!=$op_json['fee']){
					$descr.=ltmp($ltmp_arr['ops-history']['account_create_tokens'],['tokens'=>short_viz($op_json['fee'])]);
				}
				if('0.000000 SHARES'!=$op_json['delegation']){
					$descr.=ltmp($ltmp_arr['ops-history']['account_create_delegation'],['shares'=>short_viz($op_json['delegation'])]);
				}
			}
			else
			if(6==$m['type']){
				$op_type='shutdown_witness';
				$descr=ltmp($ltmp_arr['ops-history'][$op_type]);
			}
			else
			if(7==$m['type']){
				$op_type='transfer';
				if($account_arr['name']!=$op_json['to']){
					$op_type.='_from';
				}
				else{
					$op_type.='_to';
				}
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['from'=>$op_json['from'],'to'=>$op_json['to'],'tokens'=>short_viz($op_json['amount'])]);
				if($op_json['memo']){
					$op_json['memo']=htmlspecialchars($op_json['memo']);
					if(mb_strlen($op_json['memo'])<=50){
						$descr.=$ltmp_arr['ops-history']['transfer_memo'].'<span class="view-memo">'.$op_json['memo'].'</span>';
					}
					else{
						$descr.=$ltmp_arr['ops-history']['transfer_memo'].'<span class="view-memo" data-text="'.$op_json['memo'].'">'.mb_substr($op_json['memo'],0,50).'&hellip;</span>';
					}
				}
			}
			else
			if(8==$m['type']){
				$op_type='delegate_vesting_shares';
				if($account_arr['name']==$op_json['delegatee']){
					$op_type.='_from';
				}
				else{
					$op_type.='_to';
				}
				if('0.000000 SHARES'==$op_json['vesting_shares']){
					$op_type.='_stop';
				}
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['from'=>$op_json['delegator'],'to'=>$op_json['delegatee'],'shares'=>short_viz($op_json['vesting_shares'])]);
			}
			else
			if(12==$m['type']){
				$op_type='account_update';
				$descr=ltmp($ltmp_arr['ops-history'][$op_type]);
			}
			else
			if(14==$m['type']){
				$op_type='committee_worker_create_request';
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],[
					'worker'=>$op_json['worker'],
					'tokens'=>short_viz($op_json['required_amount_max']),
					'days'=>intval($op_json['duration']/3600/24),
					'text'=>htmlspecialchars($op_json['url'])
				]);
			}
			else
			if(19==$m['type']){
				$op_type='fill_vesting_withdraw';
				if($op_json['to_account']==$op_json['from_account']){
					$descr=ltmp($ltmp_arr['ops-history'][$op_type],['tokens'=>short_viz($op_json['deposited'])]);
				}
				else{
					if($account_arr['name']==$op_json['to_account']){
						$descr=ltmp($ltmp_arr['ops-history']['fill_vesting_withdraw_to'],['from'=>$op_json['from_account'],'tokens'=>short_viz($op_json['deposited'])]);
					}
					else{
						$descr=ltmp($ltmp_arr['ops-history']['fill_vesting_withdraw_from'],['to'=>$op_json['to_account'],'tokens'=>short_viz($op_json['deposited'])]);
					}
				}
			}
			else
			if(21==$m['type']){
				$op_type='transfer_to_vesting';
				if($account_arr['name']!=$op_json['to']){
					$op_type.='_from';
				}
				else{
					$op_type.='_to';
				}
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['from'=>$op_json['from'],'to'=>$op_json['to'],'tokens'=>short_viz($op_json['amount'])]);
			}
			else
			if(22==$m['type']){
				$op_type='committee_vote_request';
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],[
					'request_id'=>$op_json['request_id'],
					'percent'=>($op_json['vote_percent']/100),
				]);
			}
			else
			if(23==$m['type']){
				$op_type='committee_worker_cancel_request';
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['request_id'=>$op_json['request_id']]);
			}
			else
			if(24==$m['type']){
				$op_type='create_invite';
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['key'=>$op_json['invite_key'],'tokens'=>short_viz($op_json['balance'])]);
			}
			else
			if(26==$m['type']){
				$op_type='invite_registration';
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['key'=>$op_json['invite_secret']]);
			}
			else
			if(28==$m['type']){
				$op_type='account_metadata';
				$descr=ltmp($ltmp_arr['ops-history'][$op_type]);
			}
			else
			if(30==$m['type']){
				$op_type='claim_invite_balance';
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['key'=>$op_json['invite_secret']]);
			}
			else
			if(31==$m['type']){
				$op_type='return_vesting_delegation';
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['shares'=>short_viz($op_json['vesting_shares'])]);
			}
			else
			if(33==$m['type']){
				$op_type='committee_pay_request';
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['request_id'=>$op_json['request_id'],'tokens'=>short_viz($op_json['tokens'])]);
			}
			else
			if(37==$m['type']){
				$op_type='award';
				if($account_arr['name']==$op_json['receiver']){
					$op_type.='_from';
				}
				else{
					$op_type.='_to';
				}
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['initiator'=>$op_json['initiator'],'receiver'=>$op_json['receiver'],'energy'=>$op_json['energy']/100]);
				if($op_json['memo']){
					$op_json['memo']=htmlspecialchars($op_json['memo']);
					if(mb_strlen($op_json['memo'])<=50){
						$descr.=$ltmp_arr['ops-history']['award_memo'].'<span class="view-memo">'.$op_json['memo'].'</span>';
					}
					else{
						$descr.=$ltmp_arr['ops-history']['award_memo'].'<span class="view-memo" data-text="'.$op_json['memo'].'">'.mb_substr($op_json['memo'],0,50).'&hellip;</span>';
					}
				}
			}
			else
			if(38==$m['type']){
				$op_type='receive_award';
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['initiator'=>$op_json['initiator'],'shares'=>short_viz($op_json['shares'])]);
			}
			else
			if(40==$m['type']){
				$op_type='benefactor_award';
				if('0.000000 SHARES'==$op_json['shares']){
					$ignore=true;//пустая награда
				}
				else{
					$descr=ltmp($ltmp_arr['ops-history'][$op_type],['initiator'=>$op_json['initiator'],'shares'=>short_viz($op_json['shares'])]);
				}
			}
			else
			if(41==$m['type']){
				$op_type='set_paid_subscription';
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['link'=>htmlspecialchars($op_json['url']),'levels'=>$op_json['levels'],'amount'=>short_viz($op_json['amount']),'period'=>$op_json['period']]);
			}
			else
			if(42==$m['type']){
				$op_type='paid_subscribe';
				if($account_arr['name']==$op_json['subscriber']){
					$op_type.='_to';
				}
				else{
					$op_type.='_from';
				}
				$op_json['summary_amount']=($op_json['level']*floatval($op_json['amount'])).' VIZ';
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['subscriber'=>$op_json['subscriber'],'account'=>$op_json['account'],'level'=>$op_json['level'],'summary_amount'=>short_viz($op_json['summary_amount'])]);
				if($op_json['auto_renewal']){
					$descr.=$ltmp_arr['ops-history']['paid_subscribe_auto_renewal'];
				}
			}
			else
			if(43==$m['type']){
				$op_type='paid_subscription_action';
				if($account_arr['name']==$op_json['subscriber']){
					$op_type.='_to';
				}
				else{
					$op_type.='_from';
				}
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['subscriber'=>$op_json['subscriber'],'account'=>$op_json['account'],'level'=>$op_json['level'],'summary_amount'=>short_viz($op_json['summary_amount'])]);
			}
			else
			if(44==$m['type']){
				$op_type='cancel_paid_subscription';
				if($account_arr['name']==$op_json['subscriber']){
					$op_type.='_to';
				}
				else{
					$op_type.='_from';
				}
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['subscriber'=>$op_json['subscriber'],'account'=>$op_json['account']]);
			}
			else
			if(45==$m['type']){
				$op_type='set_subaccount_price';
				if(!$op_json['subaccount_on_sale']){
					$op_type.='_stop';
				}
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['seller'=>$op_json['subaccount_seller'],'tokens'=>short_viz($op_json['subaccount_offer_price'])]);
			}
			else
			if(46==$m['type']){
				$op_type='set_account_price';
				if(!$op_json['account_on_sale']){
					$op_type.='_stop';
				}
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['account'=>$op_json['account'],'seller'=>$op_json['account_seller'],'tokens'=>short_viz($op_json['account_offer_price'])]);
			}
			else
			if(47==$m['type']){
				$op_type='buy_account';
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['account'=>$op_json['account'],'tokens_to_shares'=>short_viz($op_json['tokens_to_shares']),'tokens'=>short_viz($op_json['account_offer_price'])]);
			}
			else
			if(48==$m['type']){
				$op_type='account_sale';
				$descr=ltmp($ltmp_arr['ops-history'][$op_type],['account'=>$op_json['account'],'buyer'=>$op_json['buyer'],'tokens'=>short_viz($op_json['price'])]);
			}
			else{
				$descr.=$m['json'];
			}
			if(false===$ignore){
				print '
				<tr data-id="'.$m['order_id'].'" data-type="'.$m['type'].'" class="text-small">
					<td>'.($op_trx['hash']?'<a href="/explorer/tx/'.$op_trx['hash'].'/">':'').date('d.m.Y H:i:s',$m['time']).($op_trx['hash']?'</a>':'').'</td>
					<!--<td>'.$ops_arr[$m['type']].'</td>-->
					<td>'.$descr.'</td>
				</tr>';
			}
			$count++;
		}
	}
	exit;
}
if('accounts'==$path_array[2]){
	header("Content-type:text/html; charset=UTF-8");
	header('HTTP/1.1 200 Ok');

	$types_arr=array('shares'=>'shares','effective'=>'effective','tokens'=>'balance','summary'=>'summary');
	$type='shares';
	if(isset($_POST['type'])){
		if(isset($types_arr[$_POST['type']])){
			$type=$_POST['type'];
		}
	}
	$search='';
	if($_POST['search']){
		$_POST['search']=urldecode($_POST['search']);
		$search=preg_replace('~[^a-zA-Z0-9\.\-]~iUs','',$_POST['search']);
	}
	$addon='';
	if(''!=$search){
		$addon=' WHERE `name` LIKE \'%'.$db->prepare($search).'%\'';
	}

	print '<table class="accounts captions sortable-theme-slick" width="100%" data-sortable="false">';
	print '<thead>';
	$th_sorted=' data-sorted="true" data-sorted-direction="descending"';
	print '
		<tr>
			<th data-field="num">#</th>
			<th data-field="account">Аккаунт</th>
			<th class="text-right right-border" title="Действующий социальный капитал с учетом делегированного исходящего и входящего капиталов"'.($type=='effective'?$th_sorted:'').'><a data-type="effective" href="/accounts/?type=effective">Действующий капитал</a></th>
			<th class="text-right" '.($type=='shares'?$th_sorted:'').'><a data-type="shares" href="/accounts/?type=shares">Собственный капитал</a></th>
			<th class="text-right" title="Баланс переносимых токенов VIZ"'.($type=='tokens'?$th_sorted:'').'><a data-type="tokens" href="/accounts/?type=tokens">Кошелёк</a></th>
			<th class="text-right" title="Сумма собственного капитала и токенов"'.($type=='summary'?$th_sorted:'').'><a data-type="summary" href="/accounts/?type=summary">Сумма</a></th>
		</tr>';
	print '</thead>';
	print '<tbody>';

	$per_page=50;
	$offset=0;
	$count=$db->table_count('accounts',$addon);
	$pages=ceil($count/$per_page)-1;
	$page=0;
	if(isset($_POST['page'])){
		$page=(int)$_POST['page'];
		$page=$page-1;
		if($page>$pages){
			$page=$pages;
		}
		if($page<0){
			$page=0;
		}
		if((1+$page)!=$_POST['page']){
			header('location:?page='.(1+$page));
			exit;
		}
	}
	$offset=$page*$per_page;
	$q=$db->sql("SELECT *, (`shares`+(`balance`*1000)) as `summary` FROM `accounts`".$addon." ORDER BY `".$types_arr[$type]."` DESC LIMIT ".$per_page." OFFSET ".$offset);
	$num=1+$offset;
	while($m=$db->row($q)){
		$withdraw_info='';
		if(0==$m['withdraw_rate']){
			$m['to_withdraw']=0;
		}
		else{
			$withdraw_info='<span class="red" title="Включено уменьшение">➘</span> ';
		}
		if($m['activity']<$m['created']){
			$m['activity']=$m['created'];
		}
		print '
		<tr>
			<td>'.$num.'</td>
			<td data-value="'.htmlspecialchars($m['name']).'"><a href="/accounts/'.htmlspecialchars($m['name']).'/">'.$m['name'].'</a></td>
			<td data-value="'.$m['effective'].'" class="text-right right-border">'.number_format($m['effective']/1000000,2,'.','&nbsp;').'</td>
			<td data-value="'.$m['shares'].'" class="text-right">'.$withdraw_info.number_format($m['shares']/1000000,2,'.','&nbsp;').'</td>
			<td data-value="'.$m['balance'].'" class="text-right">'.number_format($m['balance']/1000,2,'.','&nbsp;').'</td>
			<td data-value="'.$m['summary'].'" class="text-right">'.number_format($m['summary']/1000000,2,'.','&nbsp;').'</td>
		</tr>';
		$num++;
	}
	if($num==(1+$offset)){
		print '<tr><td colspan="6">Ничего не найдено, попробуйте задать другие условия для поиска.</td></tr>';
	}
	print '</tbody>';
	print '</table>';
	print '<div class="pagination">';
	if($page>0){
		print '<a href="?page='.($page).($search?'&search='.urlencode($search):'').($type?'&type='.urlencode($type):'').'" class="button unselectable page-button" data-page="'.$page.'">&larr; Предыдущая страница</a>';
	}
	if($page<$pages){
		print '<a href="?page='.(2+$page).($search?'&search='.urlencode($search):'').($type?'&type='.urlencode($type):'').'" class="button unselectable page-button" data-page="'.(2+$page).'">Следующая страница &rarr;</a>';
	}
	print '</div>';
	exit;
}