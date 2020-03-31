<?php
ob_start();
$replace['title']='Делегаты'.' - '.$replace['title'];
$lookup_witness=$path_array[2];
if($lookup_witness){
	if($lookup_witness!=strtolower($lookup_witness)){
		header('location:/witnesses/'.strtolower($lookup_witness).'/');
		exit;
	}
	$account_id=$db->select_one('accounts','id',"WHERE `name`='".$db->prepare($lookup_witness)."'");
	if($account_id){
		$witness=$db->sql_row("SELECT *, CAST((`votes` - (`votes` * `penalty_percent` / 10000)) as int) as `actual_votes` FROM `witnesses` WHERE `account`='".$account_id."'");
		if($witness['id']){
			$replace['title']=$lookup_witness.' - '.$replace['title'];
			$replace['description']='Статистика и информация по делегату '.$lookup_witness.' в блокчейне VIZ';
			print '
			<div class="cards-view">
				<div class="cards-container">
					<div class="card">
					<h2 class="left">Делегат <span class="secondary">'.$lookup_witness.'</span></h2>';
					print '<p>Заявление делегата: <a href="'.($witness['url']?htmlspecialchars($witness['url']):'#none').'" target="_blank" class="captions">'.date('d.m.Y H:i:s',$witness['created']).'</a></p>';
					if('VIZ1111111111111111111111111111111114T1Anm'!=$witness['signing_key']){
						print '<p>Ключ подписи: <span class="view-key captions">'.$witness['signing_key'].'</span></p>';
					}
					else{
						print '<p>Статус: <span class="red">отключен</span></p>';
					}
					$witness_votes=$db->sql_row("SELECT SUM(`votes`) as `sum` FROM `witnesses_votes` WHERE `witness` = '".$witness['id']."'");
					print '<p>Суммарный вес полученных голосов: <span class="view-tokens captions">'.(number_format($witness_votes['sum']/1000000,0,'.',' ')).' viz</span></p>';


					print '<br><h3 class="left">Список проголосовавших</h3>';
					$q=$db->sql("SELECT * FROM `witnesses_votes` WHERE `witness`='".$witness['id']."' AND `votes`>1000000 ORDER BY `votes` DESC");
					while($m=$db->row($q)){
						$voter=get_account_name($m['account']);
						print '<p><a class="view-account captions" href="/accounts/'.$voter.'/">'.$voter.'</a> вес голоса: <span class="view-tokens captions">'.number_format($m['votes']/1000000,2,'.',' ').' viz</span></p>';
					}

					print '<br><h3 class="left">Голосуемые параметры</h3>';
					$props=json_decode($witness['props'],true);
					print '
					<p>Стоимость создания аккаунта: <span class="view-tokens captions">'.number_format($props['account_creation_fee'],2,'.',' ').' viz</span></p>
					<p>Стоимость создания аккаунта делегированием: <span class="view-tokens captions">'.number_format(floatval($props['account_creation_fee'])*intval($props['create_account_delegation_ratio']),2,'.',' ').' viz</span></p>
					<p>Срок делегирования при создании аккаунта: <span class="view-memo captions">'.(round($props['create_account_delegation_time']/86400,2)).' суток</span></p>
					<hr>
					<p>Резерв пропускной способности для микроаккаунтов: <span class="view-percent captions">'.$props['bandwidth_reserve_percent'].'%</span></p>
					<p>Максимальный капитал микроаккаунта: <span class="view-tokens captions">'.number_format($props['bandwidth_reserve_below'],2,'.',' ').' viz</span></p>
					<hr>
					<p>Дополнительная наценка пропускной способности за каждую data операцию в транзакции: <span class="view-percent captions">'.($props['data_operations_cost_additional_bandwidth']/2).'%</span></p>
					<hr>
					<p>Минимальное количество токенов при делегировании: <span class="view-tokens captions">'.number_format($props['min_delegation'],2,'.',' ').' viz</span></p>
					<hr>
					<p>Минимальный размер капитала награждающего аккаунта: <span class="view-tokens captions">'.number_format($props['vote_accounting_min_rshares']/1000000,2,'.',' ').' viz</span></p>
					<hr>
					<p>Максимальный размер блока: <span class="view-memo captions">'.$props['maximum_block_size'].' байт</span></p>
					<hr>
					<p>Минимальная доля совокупного социального капитала для решения по заявке в Фонде ДАО: <span class="view-percent captions">'.($props['committee_request_approve_min_percent']/100).'%</span></p>
					<hr>
					<p>Штраф делегату за пропуск блока (% от суммарного веса голосов за делегата): <span class="view-percent captions">'.($props['account_creation_fee']/100).'%</span></p>
					<p>Продолжительность штрафа делегату за пропуск блока: <span class="view-memo captions">'.(round($props['witness_miss_penalty_duration']/86400,2)).' суток</span>
					</p>';
					print '<hr>
					<p><strong>Распределение эмиссии</strong></p>
					<p>Фонд наград: <span class="view-percent captions">'.(100-($props['inflation_witness_percent']/100)-((10000-$props['inflation_witness_percent'])/100)/(10000/$props['inflation_ratio_committee_vs_reward_fund'])).'%</span></p>
					<p>Фонд ДАО: <span class="view-percent captions">'.(((10000-$props['inflation_witness_percent'])/100)/(10000/$props['inflation_ratio_committee_vs_reward_fund'])).'%</span></p>
					<p>Фонд делегатов: <span class="view-percent captions">'.($props['inflation_witness_percent']/100).'%</span></p>';


			print '</div>
				</div>
			</div>';
		}
	}
}
else{
	$replace['description']='Таблица делегатов блокчейна VIZ';
	print '
	<div class="cards-view">
		<div class="cards-container">
			<div class="card">
			<h1>Делегаты</h1>';
			//🗷
	print '
	<p>
		<a class="button right unselectable toggle-inactive-witnesses"><span class="toggle_emoji" data-active="🗹" data-inactive="☐">☐</span> Неактивные</a>
		<select class="table-witnesses-selector simple-rounded simple-rounded-size-x2">
			<option value="none">Дополнительный столбец:</option>
			<option value="penalty">Штраф</option>
			<option value="blocks">Блоков</option>
			<option value="total_missed">Пропусков</option>
			<option value="rewards">Награда</option>

			<option value="account_creation_fee">Параметр: account_creation_fee</option>
			<option value="maximum_block_size">Параметр: maximum_block_size</option>

			<option value="create_account_delegation_ratio">Параметр: create_account_delegation_ratio</option>
			<option value="create_account_delegation_time">Параметр: create_account_delegation_time</option>
			<option value="min_delegation">Параметр: min_delegation</option>

			<option value="bandwidth_reserve_percent">Параметр: bandwidth_reserve_percent</option>
			<option value="bandwidth_reserve_below">Параметр: bandwidth_reserve_below</option>
			<option value="vote_accounting_min_rshares">Параметр: vote_accounting_min_rshares</option>
			<option value="committee_request_approve_min_percent">Параметр: committee_request_approve_min_percent</option>

			<option value="inflation_witness_percent">Параметр: inflation_witness_percent</option>
			<option value="inflation_ratio_committee_vs_reward_fund">Параметр: inflation_ratio_committee_vs_reward_fund</option>
			<option value="inflation_recalc_period">Параметр: inflation_recalc_period</option>

			<option value="data_operations_cost_additional_bandwidth">Параметр: data_operations_cost_additional_bandwidth</option>
			<option value="witness_miss_penalty_percent">Параметр: witness_miss_penalty_percent</option>
			<option value="witness_miss_penalty_duration">Параметр: witness_miss_penalty_duration</option>

		</select>
	</p>';
	print '<table class="witnesses captions sortable-theme-slick" width="100%" data-sortable>';
	print '<thead>';
	print '
	<tr>
		<th data-sorted="true" data-sorted-direction="descending" data-field="num" data-sortable-type="int">#</th>
		<th data-field="account">Делегат</th>
		<th title="Суммарный передаваемый вес голосов от участников сети" data-field="votes">Вес голосов</th>
		<th title="Используемая версия протокола" data-field="version">Версия</th>
		<th title="Штраф за пропущенные блоки влияют на учитываемый вес голосов" data-field="penalty" class="from-selector hidden">Штраф</th>
		<th title="Количество подписанных блоков" data-field="blocks" class="from-selector hidden">Блоков</th>
		<th title="Количество пропущенных блоков" data-field="total_missed" class="from-selector hidden">Пропусков</th>
		<th title="Сумма полученных наград за подписание блоков" data-field="rewards" class="from-selector hidden">Награда</th>

		<th title="account_creation_fee" data-field="account_creation_fee" class="from-selector hidden">Параметр</th>
		<th title="maximum_block_size" data-field="maximum_block_size" class="from-selector hidden">Параметр</th>

		<th title="create_account_delegation_ratio" data-field="create_account_delegation_ratio" class="from-selector hidden">Параметр</th>
		<th title="create_account_delegation_time" data-field="create_account_delegation_time" class="from-selector hidden">Параметр</th>
		<th title="min_delegation" data-field="min_delegation" class="from-selector hidden">Параметр</th>

		<th title="bandwidth_reserve_percent" data-field="bandwidth_reserve_percent" class="from-selector hidden">Параметр</th>
		<th title="bandwidth_reserve_below" data-field="bandwidth_reserve_below" class="from-selector hidden">Параметр</th>
		<th title="vote_accounting_min_rshares" data-field="vote_accounting_min_rshares" class="from-selector hidden">Параметр</th>
		<th title="committee_request_approve_min_percent" data-field="committee_request_approve_min_percent" class="from-selector hidden">Параметр</th>

		<th title="inflation_witness_percent" data-field="inflation_witness_percent" class="from-selector hidden">Параметр</th>
		<th title="inflation_ratio_committee_vs_reward_fund" data-field="inflation_ratio_committee_vs_reward_fund" class="from-selector hidden">Параметр</th>
		<th title="inflation_recalc_period" data-field="inflation_recalc_period" class="from-selector hidden">Параметр</th>

		<th title="data_operations_cost_additional_bandwidth" data-field="data_operations_cost_additional_bandwidth" class="from-selector hidden">Параметр</th>
		<th title="witness_miss_penalty_percent" data-field="witness_miss_penalty_percent" class="from-selector hidden">Параметр</th>
		<th title="witness_miss_penalty_duration" data-field="witness_miss_penalty_duration" class="from-selector hidden">Параметр</th>
	</tr>';
	print '</thead>';
	print '<tbody>';
	$q=$db->sql("SELECT *, CAST((`votes` - (`votes` * `penalty_percent` / 10000)) as int) as `actual_votes` FROM `witnesses` ORDER BY `actual_votes` DESC");
	$num=1;
	$i=0;
	while($m=$db->row($q)){
		$account_name=$db->select_one('accounts','name','WHERE `id`='.$m['account']);
		$active=('VIZ1111111111111111111111111111111114T1Anm'!=$m['signing_key']);
		$hardfork_version_upgrade=version_compare($m['running_version'],$m['hardfork_version_vote'],'<');
		$total_missed_percent='0.00';
		if($m['blocks']){
			$total_missed_percent=''.number_format(100*$m['total_missed']/$m['blocks'],2,'.','');
		}
		$props=json_decode($m['props'],true);
		print '
		<tr'.($active?'':' class="inactive"').'>
			<td data-value="'.($i).'">'.($active?($num<=11?'<strong>'.$num.'</strong>':$num):'&mdash;').'</td>
			<td data-value="'.$account_name.'"><a href="/witnesses/'.$account_name.'/">'.$account_name.'</a></td>
			<td data-value="'.$m['votes'].'">'.number_format($m['votes']/1000000,0,'.',' ').'</td>
			<td data-value="'.(str_replace('.','',$m['running_version'])).'"'.($hardfork_version_upgrade?' title="Голосует за активацию версии '.$m['hardfork_version_vote'].'" class="positive-color"':'').'>'.$m['running_version'].'</td>
			<td data-field="penalty" data-value="'.$m['penalty_percent'].'" class="from-selector hidden">'.($m['penalty_percent']/100).'%</td>
			<td data-field="blocks" class="from-selector hidden">'.$m['blocks'].'</td>
			<td data-field="total_missed"  data-value="'.$total_missed_percent.'" class="from-selector hidden">'.$total_missed_percent.'% ('.$m['total_missed'].')</td>
			<td data-field="rewards" data-value="'.$m['rewards'].'" class="from-selector hidden">'.number_format($m['rewards']/1000000,2,'.',' ').' viz</td>

			<td data-field="account_creation_fee" data-value="'.intval(1000*floatval($props['account_creation_fee'])).'" class="from-selector hidden">'.number_format(floatval($props['account_creation_fee']),2,'.',' ').' viz</td>
			<td data-field="maximum_block_size" class="from-selector hidden">'.$props['maximum_block_size'].'</td>

			<td data-field="create_account_delegation_ratio" class="from-selector hidden">'.$props['create_account_delegation_ratio'].'</td>
			<td data-field="create_account_delegation_time" class="from-selector hidden">'.$props['create_account_delegation_time'].'</td>
			<td data-field="min_delegation" data-value="'.intval(1000*floatval($props['min_delegation'])).'" class="from-selector hidden">'.number_format(floatval($props['account_creation_fee']),2,'.',' ').' viz</td>

			<td data-field="bandwidth_reserve_percent" data-value="'.$props['bandwidth_reserve_percent'].'" class="from-selector hidden">'.number_format($props['bandwidth_reserve_percent']/100,2).'%</td>
			<td data-field="bandwidth_reserve_below" data-value="'.$props['bandwidth_reserve_below'].'" class="from-selector hidden">'.number_format($props['bandwidth_reserve_below']/1000000,2,'.',' ').' viz</td>
			<td data-field="vote_accounting_min_rshares" class="from-selector hidden">'.$props['vote_accounting_min_rshares'].'</td>
			<td data-field="committee_request_approve_min_percent" data-value="'.$props['committee_request_approve_min_percent'].'" class="from-selector hidden">'.number_format($props['committee_request_approve_min_percent']/100,2).'%</td>

			<td data-field="inflation_witness_percent" data-value="'.$props['inflation_witness_percent'].'" class="from-selector hidden">'.number_format($props['inflation_witness_percent']/100,2).'%</td>
			<td data-field="inflation_ratio_committee_vs_reward_fund" data-value="'.$props['inflation_ratio_committee_vs_reward_fund'].'" class="from-selector hidden">'.number_format($props['inflation_ratio_committee_vs_reward_fund']/100,2).'%</td>
			<td data-field="inflation_recalc_period" class="from-selector hidden">'.$props['inflation_recalc_period'].'</td>

			<td data-field="data_operations_cost_additional_bandwidth" data-value="'.$props['data_operations_cost_additional_bandwidth'].'" class="from-selector hidden">'.number_format($props['data_operations_cost_additional_bandwidth']/100,2).'%</td>
			<td data-field="witness_miss_penalty_percent" class="from-selector hidden">'.number_format($props['witness_miss_penalty_percent']/100,2).'%</td>
			<td data-field="witness_miss_penalty_duration" class="from-selector hidden">'.$props['witness_miss_penalty_duration'].'</td>
		</tr>';
		if($active){
			$num++;
		}
		$i++;
	}
	print '</tbody>';
	print '</table>';
	print '</div>
		</div>
	</div>';
}
$content=ob_get_contents();
ob_end_clean();