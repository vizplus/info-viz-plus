<?php
ob_start();
if('go'==$path_array[1]){
	if($_GET['url']){
		header('location:'.$_GET['url']);
	}
	else{
		header('location:/');
	}
	exit;
}
else
if('props'==$path_array[1]){
	$props_list=['account_creation_fee','create_account_delegation_ratio','create_account_delegation_time','bandwidth_reserve_percent','bandwidth_reserve_below','data_operations_cost_additional_bandwidth','min_delegation','vote_accounting_min_rshares','committee_request_approve_min_percent','witness_miss_penalty_percent','witness_miss_penalty_duration','maximum_block_size','inflation_witness_percent','inflation_ratio_committee_vs_reward_fund','inflation_recalc_period'];
	$prop=$path_array[2];
	if(in_array($prop,$props_list)){
		if(strtolower($prop)!=$prop){
			header('location:/props/'.strtolower($prop).'/');
			exit;
		}
		$props_list_descr=[
			'account_creation_fee'=>'Стоимость создания аккаунта (viz)',
			'create_account_delegation_ratio'=>'Стоимость создания аккаунта делегированием (делегированных viz)',
			'create_account_delegation_time'=>'Срок делегирования при создании аккаунта (суток)',
			'bandwidth_reserve_percent'=>'Резерв пропускной способности для микроаккаунтов (%)',
			'bandwidth_reserve_below'=>'Максимальный капитал микроаккаунта (viz)',
			'maximum_block_size'=>'Максимальный размер блока (байт)',
			'data_operations_cost_additional_bandwidth'=>'Дополнительная наценка пропускной способности за каждую data операцию в транзакции (%)',
			'min_delegation'=>'Минимальное количество токенов при делегировании (viz)',
			'vote_accounting_min_rshares'=>'Минимальный размер капитала награждающего аккаунта (viz)',
			'committee_request_approve_min_percent'=>'Минимальная доля совокупного социального капитала для решения по заявке в Фонде ДАО (%)',
			'witness_miss_penalty_percent'=>'Штраф делегату за пропуск блока (% суммарного веса голосов за делегата)',
			'witness_miss_penalty_duration'=>'Продолжительность штрафа делегату за пропуск блока (суток)',
			'inflation_witness_percent'=>'Доля эмиссии, идущая на вознаграждение делегатов',
			'inflation_ratio_committee_vs_reward_fund'=>'Доля оставшейся эмиссии, идущая в Фонд ДАО (остальное - в Фонд наград)',
			'inflation_recalc_period'=>'Срок фиксации эмиссионной модели',
		];
		$props_list_item=[
			'account_creation_fee'=>'viz',
			'create_account_delegation_ratio'=>'viz',
			'create_account_delegation_time'=>'сут.',
			'bandwidth_reserve_percent'=>'%',
			'bandwidth_reserve_below'=>'viz',
			'maximum_block_size'=>'байт',
			'data_operations_cost_additional_bandwidth'=>'%',
			'min_delegation'=>'viz',
			'vote_accounting_min_rshares'=>'viz',
			'committee_request_approve_min_percent'=>'%',
			'witness_miss_penalty_percent'=>'%',
			'witness_miss_penalty_duration'=>'сут.',
			'inflation_recalc_period'=>'сут.',
			'inflation_witness_percent'=>'%',
			'inflation_ratio_committee_vs_reward_fund'=>'%',
		];
		$chain_props=$db->sql_row("SELECT * FROM `chain_props_snapshot` ORDER BY `id` DESC LIMIT 1");
		$replace['title']='Параметр '.$prop.' - '.$replace['title'];
		$replace['description']=$props_list_descr[$prop];
		print '
		<div class="cards-view">
			<div class="cards-container">
				<div class="card">
				<h2 class="left">Параметр <span class="secondary">'.$prop.'</span></h2>';

		print '<p>'.$props_list_descr[$prop].'</p>';
		$current_value=$chain_props[$prop];
		if('maximum_block_size'==$prop){
			$current_value=$chain_props['maximum_block_size'];
		}
		if('account_creation_fee'==$prop){
			$current_value=short_viz($chain_props['account_creation_fee']/1000,false);
		}
		if('create_account_delegation_ratio'==$prop){
			$current_value=short_viz($chain_props['account_creation_fee']*$chain_props['create_account_delegation_ratio']/1000,false);
		}
		if('create_account_delegation_time'==$prop){
			$current_value=($chain_props['create_account_delegation_time']/3600/24);
		}
		if('bandwidth_reserve_percent'==$prop){
			$current_value=round($chain_props['bandwidth_reserve_percent']/10000,2);
		}
		if('bandwidth_reserve_below'==$prop){
			$current_value=short_viz($chain_props['bandwidth_reserve_below']/1000000,false);
		}
		if('data_operations_cost_additional_bandwidth'==$prop){
			$current_value=round($chain_props['data_operations_cost_additional_bandwidth']/10000);
		}
		if('min_delegation'==$prop){
			$current_value=short_viz($chain_props['min_delegation']/1000,false);
		}
		if('vote_accounting_min_rshares'==$prop){
			$current_value=short_viz($chain_props['vote_accounting_min_rshares']/1000000,false);
		}
		if('committee_request_approve_min_percent'==$prop){
			$current_value=round($chain_props['committee_request_approve_min_percent']/100);
		}
		if('witness_miss_penalty_percent'==$prop){
			$current_value=round($chain_props['witness_miss_penalty_percent']/100);
		}
		if('inflation_witness_percent'==$prop){
			$current_value=round($chain_props['inflation_witness_percent']/100);
		}
		if('inflation_ratio_committee_vs_reward_fund'==$prop){
			$current_value=round($chain_props['inflation_ratio_committee_vs_reward_fund']/100);
		}
		if('witness_miss_penalty_duration'==$prop){
			$current_value=($chain_props['witness_miss_penalty_duration']/3600/24);
		}
		if('inflation_recalc_period'==$prop){
			$current_value=($chain_props['inflation_recalc_period']*3/3600/24);
		}

		print '<p>Значение для диапазона блоков <span class="captions">'.$chain_props['current_shuffle_block'].'&ndash;'.$chain_props['next_shuffle_block'].': '.$current_value.('%'==$props_list_item[$prop]?$props_list_item[$prop]:' '.$props_list_item[$prop]).'</span></p>';

		print '<h3 class="left">Параметры делегатов</h3>';
		print '<table class="witnesses captions sortable-theme-slick" width="50%" data-sortable>';
		print '<thead>';
		print '
		<tr>
			<th data-sorted="true" data-sorted-direction="descending" data-field="num" data-sortable-type="int">#</th>
			<th data-field="account">Делегат</th>
			<th data-field="prop_value">Параметр</th>
		</tr>';
		print '</thead>';
		print '<tbody>';
		$q=$db->sql("SELECT *, CAST((`votes` - (`votes` * `penalty_percent` / 10000)) as int) as `actual_votes` FROM `witnesses` WHERE `signing_key`!='VIZ1111111111111111111111111111111114T1Anm' ORDER BY `actual_votes` DESC");
		$num=1;
		$i=0;
		while($m=$db->row($q)){
			$account_name=$db->select_one('accounts','name','WHERE `id`='.$m['account']);
			$json=json_decode($m['props'],true);
			$raw_value=$json[$prop];
			$view_value=$raw_value;
			if('account_creation_fee'==$prop){
				$raw_value=floatval($raw_value)*1000;
				$view_value=short_viz($raw_value/1000,false);
			}
			if('create_account_delegation_ratio'==$prop){
				$raw_value=intval($raw_value);
				$view_value=short_viz($chain_props['account_creation_fee']*$raw_value/1000,false);
			}
			if('create_account_delegation_time'==$prop){
				$view_value=($raw_value/3600/24);
			}
			if('bandwidth_reserve_percent'==$prop){
				$view_value=round($raw_value/10000,2);
			}
			if('bandwidth_reserve_below'==$prop){
				$raw_value=floatval($raw_value)*1000000;
				$view_value=short_viz($raw_value/1000000,false);
			}
			if('data_operations_cost_additional_bandwidth'==$prop){
				$view_value=round($raw_value/10000);
			}
			if('min_delegation'==$prop){
				$raw_value=floatval($raw_value)*1000;
				$view_value=short_viz($raw_value/1000,false);
			}
			if('vote_accounting_min_rshares'==$prop){
				$view_value=short_viz($raw_value/1000000,false);
			}
			if('committee_request_approve_min_percent'==$prop){
				$view_value=round($raw_value/100);
			}
			if('witness_miss_penalty_percent'==$prop){
				$view_value=round($raw_value/100);
			}
			if('inflation_witness_percent'==$prop){
				$view_value=round($raw_value/100);
			}
			if('inflation_ratio_committee_vs_reward_fund'==$prop){
				$view_value=round($raw_value/100);
			}
			if('witness_miss_penalty_duration'==$prop){
				$view_value=($raw_value/3600/24);
			}
			if('inflation_recalc_period'==$prop){
				$view_value=($raw_value*3/3600/24);
			}
			print '
			<tr>
				<td data-value="'.($i/*+$m['actual_votes']*/).'">'.($num<=11?'<strong>'.$num.'</strong>':$num).'</td>
				<td data-value="'.$account_name.'"><a href="/witnesses/'.$account_name.'/">'.$account_name.'</a></td>
				<td data-value="'.$raw_value.'">'.($raw_value==$chain_props[$prop]?'<strong>':'').$view_value.('%'==$props_list_item[$prop]?$props_list_item[$prop]:' '.$props_list_item[$prop]).($raw_value==$chain_props[$prop]?'</strong>':'').'</td>
			</tr>';
			$num++;
			$i++;
		}
		print '</tbody>';
		print '</table>';

		print '<br><h3 class="left">Динамика изменений</h3>';
		$table_str='';
		$table_str.='<table class="chain-props captions sortable-theme-slick" data-sortable="false">';
		$table_str.='<thead>';
		$table_str.='
		<tr>
			<th>Время</th>
			<th>Значение</th>
		</tr>';
		$table_str.='</thead>';
		$table_str.='<tbody>';
		$q=$db->sql("SELECT * FROM `chain_props_snapshot` ORDER BY `id` DESC LIMIT 5000");
		$num=0;
		$prev_value=0;
		$prev_time=0;
		$new_value=0;
		$new_time=0;
		$charts_arr=[];
		$change=true;
		while($chain_props=$db->row($q)){
			$table=false;
			if(0==$num%60){
				$table=true;
			}
			$table_item='';
			$new_time=$chain_props['time'];

			if('maximum_block_size'==$prop){
				$new_value=$chain_props['maximum_block_size'];
				$table_item='<tr><td>'.date('d.m.Y H:i:s',$chain_props['time']).' GMT</td><td>'.$new_value.'</td></tr>';
			}
			if('account_creation_fee'==$prop){
				$new_value=short_viz($chain_props['account_creation_fee']/1000,true);
				$table_item='<tr><td>'.date('d.m.Y H:i:s',$chain_props['time']).' GMT</td><td>'.$new_value.'</td></tr>';
			}
			if('create_account_delegation_ratio'==$prop){
				$new_value=short_viz($chain_props['account_creation_fee']*$chain_props['create_account_delegation_ratio']/1000,true);
				$table_item='<tr><td>'.date('d.m.Y H:i:s',$chain_props['time']).' GMT</td><td>'.$new_value.'</td></tr>';
			}
			if('create_account_delegation_time'==$prop){
				$new_value=($chain_props['create_account_delegation_time']/3600/24);
				$table_item='<tr><td>'.date('d.m.Y H:i:s',$chain_props['time']).' GMT</td><td>'.$new_value.' сут.</td></tr>';
			}
			if('bandwidth_reserve_percent'==$prop){
				$new_value=round($chain_props['bandwidth_reserve_percent']/10000,2);
				$table_item='<tr><td>'.date('d.m.Y H:i:s',$chain_props['time']).' GMT</td><td>'.$new_value.'%</td></tr>';
			}
			if('bandwidth_reserve_below'==$prop){
				$new_value=short_viz($chain_props['bandwidth_reserve_below']/1000000,true);
				$table_item='<tr><td>'.date('d.m.Y H:i:s',$chain_props['time']).' GMT</td><td>'.$new_value.'</td></tr>';
			}
			if('data_operations_cost_additional_bandwidth'==$prop){
				$new_value=round($chain_props['data_operations_cost_additional_bandwidth']/10000);
				$table_item='<tr><td>'.date('d.m.Y H:i:s',$chain_props['time']).' GMT</td><td>'.$new_value.'%</td></tr>';
			}
			if('min_delegation'==$prop){
				$new_value=short_viz($chain_props['min_delegation']/1000,true);
				$table_item='<tr><td>'.date('d.m.Y H:i:s',$chain_props['time']).' GMT</td><td>'.$new_value.'</td></tr>';
			}
			if('vote_accounting_min_rshares'==$prop){
				$new_value=short_viz($chain_props['vote_accounting_min_rshares']/1000000,true);
				$table_item='<tr><td>'.date('d.m.Y H:i:s',$chain_props['time']).' GMT</td><td>'.$new_value.'</td></tr>';
			}
			if('committee_request_approve_min_percent'==$prop){
				$new_value=round($chain_props['committee_request_approve_min_percent']/100);
				$table_item='<tr><td>'.date('d.m.Y H:i:s',$chain_props['time']).' GMT</td><td>'.$new_value.'%</td></tr>';
			}
			if('witness_miss_penalty_percent'==$prop){
				$new_value=round($chain_props['witness_miss_penalty_percent']/100);
				$table_item='<tr><td>'.date('d.m.Y H:i:s',$chain_props['time']).' GMT</td><td>'.$new_value.'%</td></tr>';
			}
			if('inflation_witness_percent'==$prop){
				$new_value=round($chain_props['inflation_witness_percent']/100);
				$table_item='<tr><td>'.date('d.m.Y H:i:s',$chain_props['time']).' GMT</td><td>'.$new_value.'%</td></tr>';
			}
			if('inflation_ratio_committee_vs_reward_fund'==$prop){
				$new_value=round($chain_props['inflation_ratio_committee_vs_reward_fund']/100);
				$table_item='<tr><td>'.date('d.m.Y H:i:s',$chain_props['time']).' GMT</td><td>'.$new_value.'%</td></tr>';
			}
			if('witness_miss_penalty_duration'==$prop){
				$new_value=($chain_props['witness_miss_penalty_duration']/3600/24);
				$table_item='<tr><td>'.date('d.m.Y H:i:s',$chain_props['time']).' GMT</td><td>'.$new_value.' сут.</td></tr>';
			}
			if('inflation_recalc_period'==$prop){
				$new_value=($chain_props['inflation_recalc_period']*3/3600/24);
				$table_item='<tr><td>'.date('d.m.Y H:i:s',$chain_props['time']).' GMT</td><td>'.$new_value.' сут.</td></tr>';
			}
			if(0==$num){
				$prev_value=$new_value;
				$prev_time=$new_time;
				$charts_arr[]=[($prev_time)*1000,floatval($prev_value)];
			}
			if($prev_value!=$new_value){
				$change=true;
				/* work if select order ascending */
				if($prev_time<($new_time-3)){
					$charts_arr[]=[($new_time-3)*1000,floatval($prev_value)];
				}
				/* work if select order descending */
				if(($prev_time-3)>$new_time){
					$charts_arr[]=[($prev_time-3)*1000,floatval($new_value)];
				}

				$prev_value=$new_value;
				$prev_time=$new_time;
				$charts_arr[]=[($prev_time)*1000,floatval($prev_value)];
			}
			if($change){
				$table_str.=$table_item;
			}
			$change=false;
			$num++;
		}
		$charts_arr[]=[($new_time)*1000,floatval($new_value)];
		$table_str.=$table_item;
		$table_str.='</tbody></table>';

		$chart_str_arr=[];
		$chart_str_arr[]='{name:\''.addslashes($prop).'\',data:'.json_encode($charts_arr).'}';

		print '<div id="prop" class="page-charts"></div>';
		print '<script type="text/javascript">';
		print "
		Highcharts.chart('prop',
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
			colors:['#0071d2','#389bf1','#67b8ff','#9dd1ff'],
			title: {
				text: '".$prop."'
			},
			xAxis: {
				type: 'datetime'
			},
			yAxis: {
				title: {
					text: '".$props_list_item[$prop]."',
				},
				opposite: true,
			},
			legend: {
				layout: 'vertical',
				align: 'right',
				verticalAlign: 'middle'
			},
			tooltip: {
				/*headerFormat: '<b>{series.name}</b><br/>',
				pointFormat: '{point.x} km: {point.y}°C'*/
				xDateFormat:'%d.%m.%Y %H:%M:%S GMT',
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

		print $table_str;
		print '</div></div></div>';
	}
}
else
if(''==$path_array[1]){
	$replace['title']='Главная - '.$replace['title'];
	$replace['index_page_selected']=' selected';
	$th_sorted=' data-sorted="true" data-sorted-direction="descending"';
	$dgp=$db->sql_row("SELECT * FROM `dgp_snapshot` ORDER BY `id` DESC LIMIT 1");
	$dgp_json=json_decode($dgp['json'],true);
	$chain_props=$db->sql_row("SELECT * FROM `chain_props_snapshot` ORDER BY `id` DESC LIMIT 1");
	$parsed_blocks=$db->select_one('blocks','id',"ORDER BY `id` DESC");
	$parsed_ops=$db->select_one('ops','id',"ORDER BY `id` DESC");
	$parsed_ops_not_counted=$db->table_count('ops',"WHERE `counted`=0");
	$parsed_ops_counted=$parsed_ops - $parsed_ops_not_counted;

	$escrow_tokens=$db->sql_row('SELECT SUM(`amount`) as `amount_sum`,SUM(`fee`) as `fee_sum` FROM `escrow`');
	$escrow_tokens_sum=$escrow_tokens['amount_sum']+$escrow_tokens['fee_sum'];

	print '
	<div class="cards-view">
		<div class="cards-container">
			<div class="card">
				<h1 class="main">Состояние блокчейна VIZ
				<div class="captions"> на '.(date('d.m.Y H:i:s',$dgp['time'])).' GMT</div>
				</h1>
			';

	$charts_arr=[];
	$q=$db->sql("SELECT * FROM `stats` ORDER BY `id` DESC LIMIT 1000");
	while($m=$db->row($q)){
		$charts_arr['accounts_1'][]=[($m['time'])*1000,(int)$m['accounts_1']];
		$charts_arr['accounts_7'][]=[($m['time'])*1000,(int)$m['accounts_7']];
		$charts_arr['accounts_30'][]=[($m['time'])*1000,(int)$m['accounts_30']];
		$charts_arr['trx_count'][]=[($m['time'])*1000,(int)$m['trx_count']];
		//$charts_arr['capacity'][]=[($m['time'])*1000,floatval((int)$m['capacity']/100)];
	}
	$chart_str_arr=[];
	$chart_str_arr['accounts_1']='{name:\''.addslashes('за 1 день').'\',data:'.json_encode($charts_arr['accounts_1']).'}';
	$chart_str_arr['accounts_7']='{name:\''.addslashes('за 7 дней').'\',data:'.json_encode($charts_arr['accounts_7']).'}';
	$chart_str_arr['accounts_30']='{name:\''.addslashes('за 30 дней').'\',data:'.json_encode($charts_arr['accounts_30']).'}';
	$chart_str_arr['trx_count']='{name:\''.addslashes('за сутки').'\',data:'.json_encode($charts_arr['trx_count']).'}';
	//$chart_str_arr['capacity']='{name:\''.addslashes('средняя за неделю').'\',data:'.json_encode($charts_arr['capacity']).'}';

	print '<div id="activity" class="index-charts selected"></div>';
	print '<script type="text/javascript">';
	print "
	Highcharts.chart('activity',
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
		colors:['#0071d2','#389bf1','#67b8ff','#9dd1ff'],
		title: {
			text: 'Активность аккаунтов'
		},
		xAxis: {
			type: 'datetime'
		},
		yAxis: {
			title: {
				text: 'Количество',
			},
			opposite: true,
		},
		legend: {
			layout: 'vertical',
			align: 'right',
			verticalAlign: 'middle'
		},
		tooltip: {
			/*headerFormat: '<b>{series.name}</b><br/>',
			pointFormat: '{point.x} km: {point.y}°C'*/
			xDateFormat:'%d.%m.%Y %H:%M:%S GMT',
		},
		plotOptions: {
			series: {
				label: {
					connectorAllowed: false
				},
			}
		},
		series: [".$chart_str_arr['accounts_30'].",".$chart_str_arr['accounts_7'].",".$chart_str_arr['accounts_1']."],
		responsive: {
				rules: [{
					condition: {
						maxWidth: 920,
						maxHeight: 340,
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
/*
	print '<div id="activity-7" class="index-charts"></div>';
	print '<script type="text/javascript">';
	print "
	Highcharts.chart('activity-7',
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
			text: 'Активность аккаунтов'
		},
		xAxis: {
			type: 'datetime'
		},
		yAxis: {
			title: {
				text: 'Количество',
			},
			opposite: true,
		},
		legend: {
			layout: 'vertical',
			align: 'right',
			verticalAlign: 'middle'
		},
		tooltip: {
			xDateFormat:'%d.%m.%Y %H:%M:%S GMT',
		},
		plotOptions: {
			series: {
				label: {
					connectorAllowed: false
				},
			}
		},
		series: [".$chart_str_arr['accounts_7']."],
		responsive: {
				rules: [{
					condition: {
						maxWidth: 920,
						maxHeight: 320,
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
*/
/*
	print '<div id="activity-30" class="index-charts selected"></div>';
	print '<script type="text/javascript">';
	print "
	Highcharts.chart('activity-30',
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
			text: 'Активность аккаунтов'
		},
		xAxis: {
			type: 'datetime'
		},
		yAxis: {
			title: {
				text: 'Количество',
			},
			opposite: true,
		},
		legend: {
			layout: 'vertical',
			align: 'right',
			verticalAlign: 'middle'
		},
		tooltip: {
			xDateFormat:'%d.%m.%Y %H:%M:%S GMT',
		},
		plotOptions: {
			series: {
				label: {
					connectorAllowed: false
				},
			}
		},
		series: [".$chart_str_arr['accounts_30']."],
		responsive: {
				rules: [{
					condition: {
						maxWidth: 920,
						maxHeight: 320,
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
*/
	print '<div id="trx-count" class="index-charts"></div>';
	print '<script type="text/javascript">';
	print "
	Highcharts.chart('trx-count',
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
		colors:['#0071d2','#389bf1','#67b8ff','#9dd1ff'],
		title: {
			text: 'Транзакций'
		},
		xAxis: {
			type: 'datetime'
		},
		yAxis: {
			title: {
				text: 'Количество',
			},
			opposite: true,
		},
		legend: {
			layout: 'vertical',
			align: 'right',
			verticalAlign: 'middle'
		},
		tooltip: {
			xDateFormat:'%d.%m.%Y %H:%M:%S GMT',
		},
		plotOptions: {
			series: {
				label: {
					connectorAllowed: false
				},
			}
		},
		series: [".$chart_str_arr['trx_count']."],
		responsive: {
				rules: [{
					condition: {
						maxWidth: 920,
						maxHeight: 320,
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
	/*
	print '<div id="capacity" class="index-charts"></div>';
	print '<script type="text/javascript">';
	print "
	Highcharts.chart('capacity',
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
			text: 'Загруженность'
		},
		xAxis: {
			type: 'datetime'
		},
		yAxis: {
			title: {
				text: 'Процент',
			},
			opposite: true,
		},
		legend: {
			layout: 'vertical',
			align: 'right',
			verticalAlign: 'middle'
		},
		tooltip: {
			xDateFormat:'%d.%m.%Y %H:%M:%S GMT',
		},
		plotOptions: {
			series: {
				label: {
					connectorAllowed: false
				},
			}
		},
		series: [".$chart_str_arr['capacity']."],
		responsive: {
				rules: [{
					condition: {
						maxWidth: 920,
						maxHeight: 250,
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
	*/

	$time=time();
	$hour_offset=$time-3600;
	$day_offset=$time-86400;
	$day_offset_block=$db->select_one('blocks','id',"WHERE `time`>='".$day_offset."'");
	$week_offset=$time-604800;//7 days
	$month_offset=$time-2592000;//30 days
	print '<h2 class="left">Активность</h2>';
	print '<div class="columns-view">';
	print '<div class="column column-2 thin">';
	print '<table class="users-activity captions sortable-theme-slick" width="100%" data-sortable="false">';
	print '<thead>';
	print '
	<tr>
		<th>Аккаунты</th>
		<th class="text-right">Количество</th>
	</tr>';
	print '</thead>';
	print '<tbody>';
	print '<tr><td>За 30 дней</td><td class="text-right"><a class="index-charts-selector" rel="activity">'.$db->table_count('accounts',"WHERE `activity`>'".$month_offset."'").'</a></td></tr>';
	print '<tr><td>За 7 дней</td><td class="text-right">'.$db->table_count('accounts',"WHERE `activity`>'".$week_offset."'").'</td></tr>';
	print '<tr><td>За 1 день</td><td class="text-right">'.$db->table_count('accounts',"WHERE `activity`>'".$day_offset."'").'</td></tr>';
	print '<tr><td>За 1 час</td><td class="text-right">'.$db->table_count('accounts',"WHERE `activity`>'".$hour_offset."'").'</td></tr>';
	print '</tbody></table>';
	print '</div>';

	print '<div class="column column-2 thin">';
	print '<table class="block-size captions sortable-theme-slick" width="100%" data-sortable="false">';
	print '<thead>';
	print '
	<tr>
		<th>Блоки</th>
		<th>Значение</th>
	</tr>';
	print '</thead>';
	print '<tbody>';
	print '<tr><td>Средний размер блока</td><td>'.($dgp['average_block_size']).' байт</td></tr>';
	print '<tr><td>Заполнение блока</td><td>'.round($dgp['average_block_size']/$dgp['maximum_block_size']*100,2).'%</td></tr>';
	print '<tr><td>Транзакций за сутки</td><td><a class="index-charts-selector" rel="trx-count">'.($db->table_count('trx',"WHERE `block`>='".$day_offset_block."'")).'</a></td></tr>';
	print '<tr><td title="Ограничение пропускной способности">Доступность сети</td><td>'.round($dgp['current_reserve_ratio']/20000*100,2).'%</td></tr>';
	print '</tbody></table>';
	print '</div>';

	print '</div>';

	print '<h2 class="left">Экономика</h2>';
	print '<div class="columns-view">';
	print '<div class="column column-2 thin">';
	print '<table class="funds captions sortable-theme-slick" width="100%" data-sortable="false">';
	print '<thead>';
	print '
	<tr>
		<th width="50%">Токены</th>
		<th class="text-right">Количество, viz</th>
	</tr>';
	print '</thead>';
	print '<tbody>';
	print '<tr>
	<td>Ликвидные</td>
	<td class="text-right">
	<a href="/accounts/?type=tokens&page=1">
	'.short_viz(($dgp['current_supply']-$dgp['total_vesting_fund']-$dgp['committee_fund']-$dgp['total_reward_fund']-$escrow_tokens_sum)/1000,false).'
	</a>
	</td></tr>';
	print '<tr>
	<td>В капитале</td>
	<td class="text-right">
	<a href="/accounts/?type=shares&page=1">
	'.short_viz($dgp['total_vesting_fund']/1000,false).'
	</a>
	</td></tr>';
	print '<tr>
	<td>Фонд ДАО</td>
	<td class="text-right">'.short_viz($dgp['committee_fund']/1000,false).'</td></tr>';
	print '<tr>
	<td>Фонд наград</td>
	<td class="text-right">'.short_viz($dgp['total_reward_fund']/1000,false).'</td></tr>';
	print '<tr>
	<td class="text-right"><strong>Всего в экономике</strong></td>
	<td class="text-right"><strong>'.short_viz(($dgp['current_supply']-$escrow_tokens_sum)/1000,false).'</strong></td></tr>';
	print '<tr>
	<td>Замороженные</td>
	<td class="text-right">'.short_viz($escrow_tokens_sum/1000,false).'</td></tr>';
	print '<tr>
	<td class="text-right"><strong><em>Всего</em></strong></td>
	<td class="text-right"><strong><em>'.short_viz($dgp['current_supply']/1000,false).'</em></strong></td></tr>';
	print '</tbody></table>';
	print '</div>';

	print '<div class="column column-2 thin">';
	print '<table class="emission captions sortable-theme-slick" width="100%" data-sortable="false">';
	print '<thead>';
	print '
	<tr>
		<th>Эмиссия</th>
		<th>Значение</th>
	</tr>';
	print '</thead>';
	print '<tbody>';
	$inflation=0.1;
	$new_supply=50000000000;
	$emission=floor($new_supply*$inflation);
	$new_supply+=$emission;
	$circles=floor($dgp['head_block_number']/10512000);//1 year
	if($circles>0){
		for($i=0;$i<$circles;++$i){
			$emission=floor($new_supply*$inflation);
			$new_supply+=$emission;
		}
	}
	print '<tr><td>Фонд наград</td><td>'.round(((10000 - $dgp['inflation_witness_percent'] - (10000 - $dgp['inflation_witness_percent'])*$dgp['inflation_ratio']/10000)) /100,2).'%</td></tr>';
	print '<tr><td>Фонд ДАО</td><td>'.round(((10000 - $dgp['inflation_witness_percent'])*$dgp['inflation_ratio']/10000) /100,2).'%</td></tr>';
	print '<tr><td>Делегаты</td><td>'.round($dgp['inflation_witness_percent']/100,2).'%</td></tr>';
	print '<tr><td class="nowrap">Срок фиксации</td><td>'.round($chain_props['inflation_recalc_period']*3/3600/24,2).' сут.</td></tr>';
	print '<tr><td class="nowrap">Пересчёт распределения</td><td class="nowrap">'.date('d.m.Y H:i:s',($dgp['time']+($dgp_json['inflation_calc_block_num']+$chain_props['inflation_recalc_period']-$dgp['head_block_number']*3))).' GMT</td></tr>';
	print '<tr><td class="nowrap"><strong>Всего в год</strong></td><td><strong>'.short_viz($emission/1000,true).'</strong></td></tr>';
	print '</tbody></table>';
	print '</div>';

	print '</div>';

	/*
	print '<br><h3 class="left">Пропускная способность</h3>';
	print '<table class="bandwidth captions sortable-theme-slick" width="50%" data-sortable="false">';
	print '<thead>';
	print '
	<tr>
		<th>Параметр</th>
		<th>Значение</th>
	</tr>';
	print '</thead>';
	print '<tbody>';
	print '<tr><td>Доступность сети</td><td>'.round($dgp['current_reserve_ratio']/20000*100,2).'%</td></tr>';
	print '<tr><td>Максимальная виртуальная пропускная способность</td><td>'.floor($dgp['max_virtual_bandwidth']/1000000).' bytes</td></tr>';
	print '<tr><td>Резерв микроаккаунтам</td><td>'.round($chain_props['bandwidth_reserve_percent']/10000,2).'%</td></tr>';
	print '<tr><td>Резерв микроаккаунтам виртуальной пропускной способности</td><td>'.floor($dgp['max_virtual_bandwidth']/1000000*$chain_props['bandwidth_reserve_percent']/100000).' bytes</td></tr>';
	print '<tr><td>Доступная виртуальная пропускная способность</td><td class="nowrap">'.floor($dgp['max_virtual_bandwidth']/1000000-floor($dgp['max_virtual_bandwidth']/1000000*$chain_props['bandwidth_reserve_percent']/100000)).' bytes</td></tr>';

	print '</tbody></table>';
	*/

	print '<br><h2 class="left">Делегатские параметры</h2>';
	print '<table class="chain-props captions sortable-theme-slick" width="100%" data-sortable="false">';
	print '<thead>';
	print '
	<tr>
		<th>Параметр</th>
		<th>Значение</th>
	</tr>';
	print '</thead>';
	print '<tbody>';
	print '<tr><td>Стоимость создания аккаунта</td>
	<td><a href="/props/account_creation_fee/">'.short_viz($chain_props['account_creation_fee']/1000,true).'</a></td></tr>';
	print '<tr><td>Стоимость создания аккаунта делегированием</td>
	<td><a href="/props/create_account_delegation_ratio/">'.short_viz($chain_props['account_creation_fee']*$chain_props['create_account_delegation_ratio']/1000,true).'</a></td></tr>';
	print '<tr><td>Срок делегирования при создании аккаунта</td>
	<td><a href="/props/create_account_delegation_time/">'.($chain_props['create_account_delegation_time']/3600/24).' сут.</a></td></tr>';
	print '<tr><td>Резерв пропускной способности для микроаккаунтов</td>
	<td><a href="/props/bandwidth_reserve_percent/">'.round($chain_props['bandwidth_reserve_percent']/10000,2).'%</a></td></tr>';
	print '<tr><td>Максимальный капитал микроаккаунта</td>
	<td><a href="/props/bandwidth_reserve_below/">'.short_viz($chain_props['bandwidth_reserve_below']/1000000,true).'</a></td></tr>';

	print '<tr><td>Максимальный размер блока</td>
	<td class="nowrap"><a href="/props/maximum_block_size/">'.$chain_props['maximum_block_size'].' байт</a></td></tr>';

	print '<tr><td>Дополнительная наценка пропускной способности за каждую data операцию в транзакции</td>
	<td><a href="/props/data_operations_cost_additional_bandwidth/">'.round($chain_props['data_operations_cost_additional_bandwidth']/10000).'%</a></td></tr>';
	print '<tr><td>Минимальное количество токенов при делегировании</td>
	<td><a href="/props/min_delegation/">'.short_viz($chain_props['min_delegation']/1000,true).'</a></td></tr>';
	print '<tr><td>Минимальный размер капитала награждающего аккаунта</td>
	<td><a href="/props/vote_accounting_min_rshares/">'.short_viz($chain_props['vote_accounting_min_rshares']/1000000,true).'</a></td></tr>';
	print '<tr><td>Минимальная доля совокупного социального капитала для решения по заявке в Фонде ДАО</td>
	<td><a href="/props/committee_request_approve_min_percent/">'.round($chain_props['committee_request_approve_min_percent']/100).'%</a></td></tr>';
	print '<tr><td>Штраф делегату за пропуск блока (% суммарного веса голосов за делегата)</td>
	<td><a href="/props/witness_miss_penalty_percent/">'.round($chain_props['witness_miss_penalty_percent']/100).'%</a></td></tr>';
	print '<tr><td>Продолжительность штрафа делегату за пропуск блока</td>
	<td><a href="/props/witness_miss_penalty_duration/">'.($chain_props['witness_miss_penalty_duration']/3600/24).' сут.</a></td></tr>';
	print '<tr><td>Доля эмиссии, идущая на вознаграждение делегатов</td>
	<td><a href="/props/inflation_witness_percent/">'.round($chain_props['inflation_witness_percent']/100).'%</a></td></tr>';
	print '<tr><td>Доля оставшейся эмиссии, идущая в Фонд ДАО</td>
	<td><a href="/props/inflation_ratio_committee_vs_reward_fund/">'.round($chain_props['inflation_ratio_committee_vs_reward_fund']/100).'%</a></td></tr>';
	print '<tr><td>Срок фиксации эмиссионной модели</td>
	<td><a href="/props/inflation_recalc_period/">'.round($chain_props['inflation_recalc_period']*3/3600/24).' сут.</a></td></tr>';
	print '</tbody></table>';
	/*
	print '
	<hr><h2 class="left">Статус обработки</h2>
	<p>Собрано блоков: <span class="captions">'.$parsed_blocks.' ('.(round(($parsed_blocks/$dgp['head_block_number'])*100,2)).'%)</span></p>
	<p>Обработано операций: <span class="captions">'.$parsed_ops_counted.' ('.(round(($parsed_ops_counted/$parsed_ops)*100,2)).'%)</span></p>
	<p>Ждут обработки: <span class="captions">'.$parsed_ops_not_counted.' ('.(round(($parsed_ops_not_counted/$parsed_ops)*100,2)).'%)</span></p>';
	*/
	print '</div></div></div>';
}
else{
	print '
	<div class="cards-view">
		<div class="cards-container">

			<div class="card">
				<h3 class="captions">Test</h3>
				<p>В разработке...</p>
			</div>

		</div>
	</div>';
}
$content=ob_get_contents();
ob_end_clean();
