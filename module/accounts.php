<?php
ob_start();
$replace['title']='Аккаунты'.' - '.$replace['title'];
$lookup_account=$path_array[2];
if($lookup_account){
	if($lookup_account!=strtolower($lookup_account)){
		header('location:/accounts/'.strtolower($lookup_account).'/');
		exit;
	}
	$account_arr=$db->sql_row("SELECT * FROM `accounts` WHERE `name`='".$db->prepare($lookup_account)."'");
	if($account_arr['name']){
		$replace['title']=$lookup_account.' - '.$replace['title'];
		$replace['description']='Информация по аккаунту '.$lookup_account.' в блокчейне VIZ';
		print '
		<div class="cards-view">
			<div class="cards-container">
				<div class="card">
				<h2 class="left" title="Аккаунт">'.$lookup_account.'</h2>';
		$json=json_decode($account_arr['json'],true);
		print '<div class="public-profile clearfix">';
		$json['profile']['avatar_image']=$json['profile']['avatar'];
		if(!isset($json['profile']['avatar'])){
			$json['profile']['avatar']='/default-avatar.png';
			$json['profile']['avatar_image']='/default-avatar.png';
		}
		else{
			if(false===strpos($json['profile']['avatar_image'],'https://')){
				$json['profile']['avatar_image']='https://i.goldvoice.club/128x128/'.$json['profile']['avatar_image'];
			}
		}
		print '<a href="'.htmlspecialchars($json['profile']['avatar']).'" target="_blank" rel="nofollow" title="Аватар"><img src="'.htmlspecialchars($json['profile']['avatar_image']).'" class="avatar"></a>';

		print '<div class="information">';
		if(!isset($json['profile']['nickname'])){
			$json['profile']['nickname']='@'.$lookup_account;
		}
		print '<h3 class="left" title="Псевдоним">'.htmlspecialchars($json['profile']['nickname']).'</h3>';
		if(!isset($json['profile']['about'])){
			$json['profile']['about']='Отсутствует описание аккаунта';
		}
		print '<p title="Об аккаунте">'.htmlspecialchars($json['profile']['about']).'</p>';
		if(isset($json['profile']['location'])){
			print '<p class="grey small captions"><img src="/globe-ico.svg" class="icon-16" alt="Местоположение" title="Местоположение">'.htmlspecialchars($json['profile']['location']).'</p>';
		}
		if(isset($json['profile']['site'])){
			print '<p class="grey small captions"><img src="/link.svg" class="icon-16" alt="Сайт" title="Сайт"><a target="_blank" href="/go/?url='.htmlspecialchars($json['profile']['site']).'">'.htmlspecialchars($json['profile']['site']).'</a></p>';
		}
		if(isset($json['profile']['mail'])){
			print '<p class="grey small captions"><img src="/mail.svg" class="icon-16" alt="Электронная почта" title="Электронная почта"><a target="_blank" href="mailto:'.htmlspecialchars($json['profile']['mail']).'">'.htmlspecialchars($json['profile']['mail']).'</a></p>';
		}
		if(isset($json['profile']['interests'])){
			print '<p class="grey small captions">Интересы: '.htmlspecialchars(implode(', ',$json['profile']['interests'])).'</p>';
		}
		if(isset($json['profile']['services'])){
			print '<p class="grey small captions">Контакты: ';
			if(isset($json['profile']['services']['facebook'])){
				$json['profile']['services']['facebook']=str_replace('https://www.facebook.com/','',$json['profile']['services']['facebook']);
				print '<a href="/go/?url=https://www.facebook.com/'.htmlspecialchars($json['profile']['services']['facebook']).'" target="_blank" class="icon-link"><img src="/facebook.svg" class="icon-16" alt="Facebook" title="Facebook"></a>';
			}
			if(isset($json['profile']['services']['instagram'])){
				$json['profile']['services']['instagram']=str_replace('https://www.instagram.com/','',$json['profile']['services']['instagram']);
				print '<a href="/go/?url=https://www.instagram.com/'.htmlspecialchars($json['profile']['services']['instagram']).'" target="_blank" class="icon-link"><img src="/instagram.svg" class="icon-16" alt="Instagram" title="Instagram"></a>';
			}
			if(isset($json['profile']['services']['twitter'])){
				$json['profile']['services']['twitter']=str_replace('https://twitter.com/','',$json['profile']['services']['twitter']);
				print '<a href="/go/?url=https://twitter.com/'.htmlspecialchars($json['profile']['services']['twitter']).'" target="_blank" class="icon-link"><img src="/twitter.svg" class="icon-16" alt="Twitter" title="Twitter"></a>';
			}
			if(isset($json['profile']['services']['vk'])){
				$json['profile']['services']['vk']=str_replace('https://vk.com/','',$json['profile']['services']['vk']);
				print '<a href="/go/?url=https://vk.com/'.htmlspecialchars($json['profile']['services']['vk']).'" target="_blank" class="icon-link"><img src="/vk.svg" class="icon-16" alt="Вконтакте" title="Вконтакте"></a>';
			}
			if(isset($json['profile']['services']['telegram'])){
				if('@'==substr($json['profile']['services']['telegram'],0,1)){
					$json['profile']['services']['telegram']=substr($json['profile']['services']['telegram'],1);
				}
				print '<a href="tg://resolve?domain='.htmlspecialchars($json['profile']['services']['telegram']).'" class="icon-link"><img src="/telegram.svg" class="icon-16" alt="Telegram" title="Telegram"></a>';
			}
			if(isset($json['profile']['services']['skype'])){
				print '<a href="skype:'.htmlspecialchars($json['profile']['services']['skype']).'?call" target="_blank" class="icon-link"><img src="/skype.svg" class="icon-16" alt="Skype" title="Skype"></a>';
			}
			if(isset($json['profile']['services']['viber'])){
				print '<a href="viber://pa/info?uri='.htmlspecialchars($json['profile']['services']['viber']).'" target="_blank" class="icon-link"><img src="/viber.svg" class="icon-16" alt="Viber" title="Viber"></a>';
			}
			if(isset($json['profile']['services']['whatsapp'])){
				print '<a href="/go/?url=https://wa.me/'.htmlspecialchars($json['profile']['services']['whatsapp']).'" target="_blank" class="icon-link"><img src="/whatsapp.svg" class="icon-16" alt="WhatsApp" title="WhatsApp"></a>';
			}
			print '</p>';
		}
		print '</div>';

		print '</div>';

		print '<p>Создан: <span class="view-date captions">'.($account_arr['created']?date('d.m.Y H:i:s',$account_arr['created']):'генезис').'</span></p>';
		if($account_arr['receiver_awards']){
			print '<p>Получено наград: <span class="view-tokens captions">'.number_format($account_arr['receiver_awards']/1000,2,'.','&nbsp;').' viz</span></p>';
		}
		if($account_arr['benefactor_awards']){
			print '<p>Получено бенефициарских: <span class="view-tokens captions">'.number_format($account_arr['benefactor_awards']/1000,2,'.','&nbsp;').' viz</span></p>';
		}


		$keys_info='';
		$keys_arr=array('1'=>'Мастер','2'=>'Активный','3'=>'Регулярный','4'=>'Мемо');
		$q=$db->sql("SELECT * FROM `accounts_keys` WHERE `account`='".$account_arr['id']."' ORDER BY `type` ASC");
		while($m=$db->row($q)){
			//$keys_info.='<p>'.$keys_arr[$m['type']].': <span class="view-key captions">'.$m['key'].'</span></p>';
			$keys_info.='<tr><td>'.$keys_arr[$m['type']].'</td><td><span class="view-key captions">'.$m['key'].'</span></td><td>'.($m['weight_threshold']?($m['weight'].' / '.$m['weight_threshold']):'&mdash;').'</td></tr>';
		}
		if($keys_info){
			print '<hr>';
			print '<h3 class="left">Публичные ключи</h3>';
			print '<table class="captions sortable-theme-slick" width="100%" data-sortable="false">';
			print '<thead>';
			print '
			<tr>
				<th width="33.33%">Тип доступа</th>
				<th width="33.33%">Публичный ключ</th>
				<th width="33.33%">Вес / Необходимый</th>
			</tr>';
			print '</thead>';
			print '<tbody>';
			print $keys_info;
			print '</tbody>';
			print '</table>';
		}

		$auths_info='';
		$auths_arr=array('1'=>'Мастер доступ','2'=>'Активный доступ','3'=>'Регулярный доступ');
		$q=$db->sql("SELECT * FROM `accounts_authority` WHERE `account`='".$account_arr['id']."' ORDER BY `type` ASC");
		while($m=$db->row($q)){
			$auths_info.='<p>'.$auths_arr[$m['type']].': <a class="view-account captions" href="/accounts/'.get_account_name($m['agent']).'/">'.get_account_name($m['agent']).'</a></p>';
		}
		if($auths_info){
			print '<hr>';
			print '<h3 class="left">Доверенные аккаунты</h3>';
			print $auths_info;
		}

		print '<hr>';
		print '<h3 class="left">Активы</h3>';
		print '<table class="balances captions sortable-theme-slick table-max-500" width="100%" data-sortable="false">';
		print '<thead>';
		print '
		<tr>
			<th class="text-right" width="33.33%">Капитал (viz)</th>
			<th class="text-right" width="33.33%">Кошелёк (viz)</th>
			<th class="text-right">Энергия (%)</th>
		</tr>';
		print '</thead>';
		print '<tbody>';
		$withdraw_info='';
		if(0==$account_arr['withdraw_rate']){
			$account_arr['to_withdraw']=0;
		}
		else{
			$withdraw_info='<span class="red" title="Включено уменьшение">➘</span> ';
		}
		$delegation_info='';
		if($account_arr['received']){
			$delegation_info.='<br><span class="green" title="Входящее делегирование">+'.number_format($account_arr['received']/1000000,2,'.','&nbsp;').'</span>';
		}
		if($account_arr['delegated']){
			$delegation_info.='<br><span class="red" title="Исходящее делегирование">−'.number_format($account_arr['delegated']/1000000,2,'.','&nbsp;').'</span>';
		}
		$new_energy=$account_arr['energy']+((time()-$account_arr['last_vote_time'])*10000/432000);//5 days
		if($new_energy>10000){
			$new_energy=10000;
		}
		print '
		<tr>
			<td class="text-right"><span class="text-big">'.$withdraw_info.number_format($account_arr['shares']/1000000,2,'.','&nbsp;').'</span>'.$delegation_info.'</td>
			<td class="text-right text-big">'.number_format($account_arr['balance']/1000,2,'.','&nbsp;').'</td>
			<td class="text-right text-big">'.round($new_energy/100,2).'%</td>
		</tr>';
		print '</tbody>';
		print '</table>';

		$delegations='';
		$q=$db->sql("SELECT * FROM `delegations` WHERE (`from`='".$account_arr['id']."' OR `to`='".$account_arr['id']."') AND `shares`>0 ORDER BY `shares` DESC");
		while($m=$db->row($q)){
			$delegation_info='';
			$target_account='';
			if($m['from']==$m['to']){
				$target_account='Отозвано';
				$delegation_info=number_format($m['shares']/1000000,2,'.','&nbsp;');
			}
			else{
				if($account_arr['id']==$m['to']){
					$delegation_info.='<span class="green" title="Входящее делегирование">+'.number_format($m['shares']/1000000,2,'.','&nbsp;').'</span>';
					$target_account=get_account_name($m['from']);
					$target_account='<a href="/accounts/'.$target_account.'/" class="view-account">'.$target_account.'</a>';
				}
				else{
					$delegation_info.='<span class="red" title="Исходящее делегирование">−'.number_format($m['shares']/1000000,2,'.','&nbsp;').'</span>';
					$target_account=get_account_name($m['to']);
					$target_account='<a href="/accounts/'.$target_account.'/" class="view-account">'.$target_account.'</a>';
				}
			}
			$delegations.='
			<tr>
				<td>'.$target_account.'</td>
				<td class="text-right "><span class="text-big">'.$delegation_info.'</td>
			</tr>';
		}
		if($delegations){
			print '<hr>';
			print '<h3 class="left">Делегирование</h3>';

			print '<table class="delegations captions sortable-theme-slick table-max-500" width="100%" data-sortable="false">';
			print '<thead>';
			print '
			<tr>
				<th width="33.33%">Аккаунт</th>
				<th class="text-right">Капитал (viz)</th>
			</tr>';
			print '</thead>';
			print '<tbody>';
			print $delegations;
			print '</tbody>';
			print '</table>';
		}


		/*
		$charts_arr=[];
		$q=$db->sql("SELECT * FROM `accounts_snapshot` WHERE `account`='".$account_arr['id']."' ORDER BY `id` DESC LIMIT 365");
		while($m=$db->row($q)){
			$charts_arr['Баланс'][]=[($m['time']+5400)*1000,$m['balance']/1000];//+1.5 hour for x-axis
			$charts_arr['Социальный Капитал'][]=[($m['time']+5400)*1000,$m['shares']/1000000];
			$charts_arr['Входящее'][]=[($m['time']+5400)*1000,$m['received']/1000000];
			$charts_arr['Исходящее'][]=[($m['time']+5400)*1000,$m['delegated']/1000000];
		}
		$chart_str_arr=[];
		foreach($charts_arr as $chart_name=>$chart_arr){
			$chart_str_arr[]='{name:\''.addslashes($chart_name).'\',data:'.json_encode($chart_arr).'}';
		}
		print '<div class="tab-container clearfix">';

		print '<div class="tab-control captions">';
		print '<a class="tab-selector selected" data-tab="1">Кошелёк</a>'.PHP_EOL;
		print '<a class="tab-selector" data-tab="2">Социальный капитал</a>'.PHP_EOL;
		print '<a class="tab-selector" data-tab="3">Делегирование</a>'.PHP_EOL;
		print '</div>';

		print '<div id="chart-account-balance" class="tab-view page-charts" data-tab="1"></div>';
		print '<script type="text/javascript">';
		print "
		Highcharts.chart('chart-account-balance',
		{
			chart: {
				zoomType: 'x',
				resetZoomButton: {
					position: {
						align: 'left',
						verticalAlign: 'top',
						x: 10,
						y: 10
					},
					relativeTo: 'plot'
				}
			},
			title: {
				text: 'Кошелёк'
			},
			subtitle: {
				text: 'Аккаунт: ".$account_arr['name']."'
			},
			xAxis: {
				type: 'datetime'
			},
			yAxis: {
				title: {
					text: 'VIZ',
				},
				opposite: true,
			},
			legend: {
				layout: 'vertical',
				align: 'right',
				verticalAlign: 'middle'
			},
			plotOptions: {
				series: {
					label: {
						connectorAllowed: false
					},
				}
			},
			series: [".$chart_str_arr[0]."],
			responsive: {
					rules: [{
						condition: {
							maxWidth: 920,
							maxHeight: 400,
						},
						chartOptions: {
							legend: {
								layout: 'horizontal',
								align: 'center',
								verticalAlign: 'bottom'
							}
						}
					}]
				}
		});
		</script>";

		print '<div id="chart-account-capital" class="tab-view page-charts" data-tab="2"></div>';
		print '<script type="text/javascript">';
		print "
		Highcharts.chart('chart-account-capital',
		{
			chart: {
				zoomType: 'x',
				resetZoomButton: {
					position: {
						align: 'left',
						verticalAlign: 'top',
						x: 10,
						y: 10
					},
					relativeTo: 'plot'
				}
			},
			title: {
				text: 'Социальный Капитал'
			},
			subtitle: {
				text: 'Аккаунт: ".$account_arr['name']."'
			},
			xAxis: {
				type: 'datetime'
			},
			yAxis: {
				title: {
					text: 'VIZ',
				},
				opposite: true,
			},
			legend: {
				layout: 'vertical',
				align: 'right',
				verticalAlign: 'middle'
			},
			plotOptions: {
				series: {
					label: {
						connectorAllowed: false
					},
				}
			},
			series: [".$chart_str_arr[1]."],
			responsive: {
					rules: [{
						condition: {
							maxWidth: 920,
							maxHeight: 400,
						},
						chartOptions: {
							legend: {
								layout: 'horizontal',
								align: 'center',
								verticalAlign: 'bottom'
							}
						}
					}]
				}
		});
		</script>";

		print '<div id="chart-account-delegations" class="tab-view page-charts" data-tab="3"></div>';
		print '<script type="text/javascript">';
		print "
		Highcharts.chart('chart-account-delegations',
		{
			chart: {
				zoomType: 'x',
				resetZoomButton: {
					position: {
						align: 'left',
						verticalAlign: 'top',
						x: 10,
						y: 10
					},
					relativeTo: 'plot'
				}
			},
			title: {
				text: 'Делегирование'
			},
			subtitle: {
				text: 'Аккаунт: ".$account_arr['name']."'
			},
			xAxis: {
				type: 'datetime'
			},
			yAxis: {
				title: {
					text: 'VIZ',
				},
				opposite: true,
			},
			legend: {
				layout: 'vertical',
				align: 'right',
				verticalAlign: 'middle'
			},
			plotOptions: {
				series: {
					label: {
						connectorAllowed: false
					},
				}
			},
			series: [".$chart_str_arr[2].",".$chart_str_arr[3]."],
			responsive: {
					rules: [{
						condition: {
							maxWidth: 920,
							maxHeight: 400,
						},
						chartOptions: {
							legend: {
								layout: 'horizontal',
								align: 'center',
								verticalAlign: 'bottom'
							}
						}
					}]
				}
		});
		</script>";

		print '</div>';
		*/

		print '<hr>';
		print '<h3 class="left">ДАО</h3>';
		$is_witness=$db->select_one('witnesses','id',"WHERE `account`='".$account_arr['id']."' AND `signing_key`!='VIZ1111111111111111111111111111111114T1Anm'");
		print '<p>Делегат: '.($is_witness?'<a href="/witnesses/'.$account_arr['name'].'/" class="green">Да</a>':'<span class="red">Нет</span>').'</p>';
		$witnesses=array();
		$q=$db->sql("SELECT * FROM `witnesses_votes` WHERE `account`='".$account_arr['id']."'");
		while($m=$db->row($q)){
			$witness=get_witness_name($m['witness']);
			$witnesses[]='<a class="view-account captions" href="/witnesses/'.htmlspecialchars($witness).'/">'.$witness.'</a>';
		}

		if(count($witnesses)>0){
			print '<p>Вес голоса за делегата: <span class="view-tokens captions">'.(number_format($account_arr['witnesses_vote_weight']/1000000,0,'.',' ')).' viz</span></p>';
			print '<p>Список голосов за делегатов: ';
			print implode(', ',$witnesses);
			print '</p>';
		}
		else{
			print '<p>Голоса за делегатов: <span class="red">Отсутствуют</span></p>';
		}

		print '<hr>';
		print '<h3 class="left">История операций</h3>';
		print '<div class="ops-history-table">';
		$ops_arr=array();
		$q=$db->sql("SELECT `id`,`name` FROM `ops_type`");
		while($m=$db->row($q)){
			$ops_arr[$m['id']]=$m['name'];
		}
		$type_arr=array(
			'Аккаунты'=>array(12,28,5,45,46,47,48),
			'Капитал'=>array(4,8,19,21,29,31),
			'Переводы'=>array(7,21,24,26,30),
			'Награды'=>array(37,38,40),
			'ДАО'=>array(3,6,14,22,23,27,32,33,34,36),
			'Подписки'=>array(41,42,43,44,),
		);
		print '<p>
		<input type="text" placeholder="Поиск" class="right simple-rounded table-ops-history-search">
		<select class="table-ops-history-selector simple-rounded">';
		print '<option value="" selected>Все</option>';
		foreach($type_arr as $type_name=>$type_vars){
			print '<option value="'.implode(',',$type_vars).'">'.$type_name.'</option>';
		}
		print '</select></p>';
		print '<table data-account="'.htmlspecialchars($account_arr['name']).'" class="ops-history captions sortable-theme-slick" width="100%" data-sortable="false">';
		print '<thead>';
		print '
		<tr>
			<th width="10%">Время (GMT)</th>
			<!--<th>Операция</th>-->
			<th>Описание</th>
		</tr>';
		print '</thead>';
		print '<tbody>';
		$per_page=100;
		$sql='SELECT `ops_link`.`id` as `order_id`, `ops`.*
		FROM `ops_link`
		RIGHT JOIN `ops` ON `ops_link`.`op`=`ops`.`id`
		WHERE `ops_link`.`account`='.$account_arr['id'].'
		ORDER BY `order_id` DESC
		LIMIT '.$per_page;
		$q=$db->sql($sql);
		$count=0;
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
		print '</tbody>';
		print '</table>';
		print '<div class="pagination">';
		if($count==$per_page){
			print '<a class="button unselectable load-more-button">Показать больше записей&hellip;</a>';
		}
		print '</div>';

		print '</div>';

		print '</div>
			</div>
		</div>';
	}
}
else{
	$replace['description']='Таблица аккаунтов в блокчейне VIZ';
	print '
	<div class="cards-view">
		<div class="cards-container">
			<div class="card">';
	$types_arr=array('shares'=>'shares','effective'=>'effective','tokens'=>'balance','summary'=>'summary');
	$type='shares';
	if(isset($_GET['type'])){
		if(isset($types_arr[$_GET['type']])){
			$type=$_GET['type'];
		}
	}
	$search='';
	if($_GET['search']){
		$_GET['search']=urldecode($_GET['search']);
		$search=preg_replace('~[^a-zA-Z0-9\.\-]~iUs','',$_GET['search']);
		if($_GET['search']!=$search){
			header('location:?search='.urlencode($search));
			exit;
		}
	}
	$addon='';
	if(''!=$search){
		$addon=' WHERE `name` LIKE \'%'.$db->prepare($search).'%\'';
	}
	print '<h1>Аккаунты</h1>';
	print '<p>
	Всего аккаунтов: <span class="captions">'.$db->table_count('accounts').'</span><br>
	Из них имеют токены viz: <span class="captions">'.$db->table_count('accounts',' WHERE `balance`+`shares`!=0').'</span>
	</p>';
	print '<p><input class="simple-rounded accounts-search-text" placeholder="Имя аккаунта" value="'.htmlspecialchars($search).'"></p>';
	print '<div class="accounts-table" data-type="'.$type.'">';
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
	if(isset($_GET['page'])){
		$page=(int)$_GET['page'];
		$page=$page-1;
		if($page>$pages){
			$page=$pages;
		}
		if($page<0){
			$page=0;
		}
		if((1+$page)!=$_GET['page']){
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
	print '</div>';
	print '</div>
		</div>
	</div>';
}
$content=ob_get_contents();
ob_end_clean();