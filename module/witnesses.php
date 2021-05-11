<?php
ob_start();
$replace['title']=$ltmp_arr['witnesses']['title'].' - '.$replace['title'];
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
			$replace['description']=ltmp($ltmp_arr['witnesses']['personal_description'],['witness'=>$lookup_witness]);
			print '
			<div class="cards-view">
				<div class="cards-container">
					<div class="card">
					<h2 class="left">'.$ltmp_arr['witnesses']['witness'].' <span class="secondary">'.$lookup_witness.'</span></h2>';
					print '<p>'.$ltmp_arr['witnesses']['url'].': <a href="'.($witness['url']?htmlspecialchars($witness['url']):'#none').'" target="_blank" class="captions">'.date('d.m.Y H:i:s',$witness['created']).'</a></p>';
					if('VIZ1111111111111111111111111111111114T1Anm'!=$witness['signing_key']){
						print '<p>'.$ltmp_arr['witnesses']['key'].': <span class="view-key captions">'.$witness['signing_key'].'</span></p>';
					}
					else{
						print '<p>'.$ltmp_arr['witnesses']['status'].': <span class="red">'.$ltmp_arr['witnesses']['status_disabled'].'</span></p>';
					}
					$witness_votes=$db->sql_row("SELECT SUM(`votes`) as `sum` FROM `witnesses_votes` WHERE `witness` = '".$witness['id']."'");
					print '<p>'.$ltmp_arr['witnesses']['sum_votes'].': <span class="captions">'.(number_format($witness_votes['sum']/1000000,0,'.',' ')).' viz</span></p>';


					print '<br><h3 class="left">'.$ltmp_arr['witnesses']['voter_list'].'</h3>';
					$q=$db->sql("SELECT * FROM `witnesses_votes` WHERE `witness`='".$witness['id']."' AND `votes`>1000000 ORDER BY `votes` DESC");
					while($m=$db->row($q)){
						$voter=get_account_name($m['account']);
						print '<p><a class="view-account captions" href="/accounts/'.$voter.'/">'.$voter.'</a> '.$ltmp_arr['witnesses']['vote_weight'].' <span class="captions">'.number_format($m['votes']/1000000,2,'.',' ').' viz</span></p>';
					}

					print '<br><h3 class="left">'.$ltmp_arr['witnesses']['voted_props'].'</h3>';
					$props=json_decode($witness['props'],true);
					print '
					<p>'.$ltmp_arr['props_descr']['maximum_block_size'].': <span class="captions">'.$props['maximum_block_size'].' '.$ltmp_arr['props_descr_type']['maximum_block_size'].'</span></p>
					<p>'.$ltmp_arr['props_descr']['account_creation_fee'].': <span class="captions">'.number_format($props['account_creation_fee'],2,'.',' ').' '.$ltmp_arr['props_descr_type']['account_creation_fee'].'</span></p>
					<p>'.$ltmp_arr['props_descr']['create_account_delegation_ratio'].': <span class="captions">'.number_format(floatval($props['account_creation_fee'])*intval($props['create_account_delegation_ratio']),2,'.',' ').' '.$ltmp_arr['props_descr_type']['create_account_delegation_ratio'].'</span></p>
					<p>'.$ltmp_arr['props_descr']['create_account_delegation_time'].': <span class="captions">'.(round($props['create_account_delegation_time']/86400,2)).' '.$ltmp_arr['props_descr_type']['create_account_delegation_time'].'</span></p>
					<hr>
					<p>'.$ltmp_arr['props_descr']['min_delegation'].': <span class="captions">'.number_format($props['min_delegation'],2,'.',' ').' '.$ltmp_arr['props_descr_type']['min_delegation'].'</span></p>
					<p>'.$ltmp_arr['props_descr']['create_invite_min_balance'].': <span class="captions">'.number_format($props['create_invite_min_balance'],2,'.',' ').' '.$ltmp_arr['props_descr_type']['create_invite_min_balance'].'</span>
					<hr>
					<p>'.$ltmp_arr['props_descr']['bandwidth_reserve_percent'].': <span class="captions">'.round($props['bandwidth_reserve_percent']/100,2).''.$ltmp_arr['props_descr_type']['bandwidth_reserve_percent'].'</span></p>
					<p>'.$ltmp_arr['props_descr']['bandwidth_reserve_below'].': <span class="captions">'.number_format($props['bandwidth_reserve_below'],2,'.',' ').' '.$ltmp_arr['props_descr_type']['bandwidth_reserve_below'].'</span></p>
					<p>'.$ltmp_arr['props_descr']['vote_accounting_min_rshares'].': <span class="captions">'.number_format($props['vote_accounting_min_rshares']/1000000,2,'.',' ').' '.$ltmp_arr['props_descr_type']['vote_accounting_min_rshares'].'</span></p>
					<p>'.$ltmp_arr['props_descr']['withdraw_intervals'].': <span class="captions">'.$props['withdraw_intervals'].'</span>
					<p>'.$ltmp_arr['props_descr']['committee_request_approve_min_percent'].': <span class="captions">'.round($props['committee_request_approve_min_percent']/100,2).''.$ltmp_arr['props_descr_type']['committee_request_approve_min_percent'].'</span></p>
					<p>'.$ltmp_arr['props_descr']['data_operations_cost_additional_bandwidth'].': <span class="captions">'.round($props['data_operations_cost_additional_bandwidth']/100,2).''.$ltmp_arr['props_descr_type']['data_operations_cost_additional_bandwidth'].'</span></p>
					<hr>
					<p>'.$ltmp_arr['props_descr']['witness_miss_penalty_percent'].': <span class="captions">'.round($props['witness_miss_penalty_percent']/100,2).''.$ltmp_arr['props_item']['witness_miss_penalty_percent'].'</span></p>
					<p>'.$ltmp_arr['props_descr']['witness_miss_penalty_duration'].': <span class="captions">'.(round($props['witness_miss_penalty_duration']/86400,2)).' '.$ltmp_arr['props_item']['witness_miss_penalty_duration'].'</span>
					<hr>
					<p>'.$ltmp_arr['props_descr']['committee_create_request_fee'].': <span class="captions">'.number_format($props['committee_create_request_fee'],2,'.',' ').' '.$ltmp_arr['props_item']['committee_create_request_fee'].'</span>
					<p>'.$ltmp_arr['props_descr']['create_paid_subscription_fee'].': <span class="captions">'.number_format($props['create_paid_subscription_fee'],2,'.',' ').' '.$ltmp_arr['props_item']['create_paid_subscription_fee'].'</span>
					<p>'.$ltmp_arr['props_descr']['account_on_sale_fee'].': <span class="captions">'.number_format($props['account_on_sale_fee'],2,'.',' ').' '.$ltmp_arr['props_item']['account_on_sale_fee'].'</span>
					<p>'.$ltmp_arr['props_descr']['subaccount_on_sale_fee'].': <span class="captions">'.number_format($props['subaccount_on_sale_fee'],2,'.',' ').' '.$ltmp_arr['props_item']['subaccount_on_sale_fee'].'</span>
					<p>'.$ltmp_arr['props_descr']['witness_declaration_fee'].': <span class="captions">'.number_format($props['witness_declaration_fee'],2,'.',' ').' '.$ltmp_arr['props_item']['witness_declaration_fee'].'</span>
					</p>';
					print '<hr>
					<p><strong>'.$ltmp_arr['witnesses']['emission_distribution'].'</strong></p>
					<p>'.$ltmp_arr['witnesses']['reward_fund'].': <span class="captions">'.round(100-($props['inflation_witness_percent']/100)-((10000-$props['inflation_witness_percent'])/100)/(10000/$props['inflation_ratio_committee_vs_reward_fund']),2).''.$ltmp_arr['props_item']['inflation_ratio_committee_vs_reward_fund'].'</span></p>
					<p>'.$ltmp_arr['witnesses']['dao_fund'].': <span class="captions">'.round(((10000-$props['inflation_witness_percent'])/100)/(10000/$props['inflation_ratio_committee_vs_reward_fund']),2).''.$ltmp_arr['props_item']['inflation_ratio_committee_vs_reward_fund'].'</span></p>
					<p>'.$ltmp_arr['witnesses']['witnesses_fund'].': <span class="captions">'.round($props['inflation_witness_percent']/100,2).''.$ltmp_arr['props_item']['inflation_witness_percent'].'</span></p>';
			print '</div>
				</div>
			</div>';
		}
	}
}
else{
	$replace['description']=$ltmp_arr['witnesses']['description'];
	print '
	<div class="cards-view">
		<div class="cards-container">
			<div class="card">
			<h1>'.$ltmp_arr['witnesses']['title'].'</h1>';
			//🗷
	print '
	<p>
		<a class="button right unselectable toggle-inactive-witnesses"><span class="toggle_emoji" data-active="🗹" data-inactive="☐">☐</span> '.$ltmp_arr['witnesses']['inactive'].'</a>
		<select class="table-witnesses-selector simple-rounded simple-rounded-size-x2">
			<option value="none">'.$ltmp_arr['witnesses']['addon_col'].'</option>
			<option value="penalty">'.$ltmp_arr['witnesses']['col_penalty'].'</option>
			<option value="blocks">'.$ltmp_arr['witnesses']['col_blocks'].'</option>
			<option value="total_missed">'.$ltmp_arr['witnesses']['col_total_missed'].'</option>
			<option value="rewards">'.$ltmp_arr['witnesses']['col_rewards'].'</option>

			<option value="account_creation_fee">'.$ltmp_arr['witnesses']['col_prop'].' account_creation_fee</option>
			<option value="maximum_block_size">'.$ltmp_arr['witnesses']['col_prop'].' maximum_block_size</option>

			<option value="create_account_delegation_ratio">'.$ltmp_arr['witnesses']['col_prop'].' create_account_delegation_ratio</option>
			<option value="create_account_delegation_time">'.$ltmp_arr['witnesses']['col_prop'].' create_account_delegation_time</option>
			<option value="min_delegation">'.$ltmp_arr['witnesses']['col_prop'].' min_delegation</option>

			<option value="bandwidth_reserve_percent">'.$ltmp_arr['witnesses']['col_prop'].' bandwidth_reserve_percent</option>
			<option value="bandwidth_reserve_below">'.$ltmp_arr['witnesses']['col_prop'].' bandwidth_reserve_below</option>
			<option value="vote_accounting_min_rshares">'.$ltmp_arr['witnesses']['col_prop'].' vote_accounting_min_rshares</option>
			<option value="committee_request_approve_min_percent">'.$ltmp_arr['witnesses']['col_prop'].' committee_request_approve_min_percent</option>

			<option value="inflation_witness_percent">'.$ltmp_arr['witnesses']['col_prop'].' inflation_witness_percent</option>
			<option value="inflation_ratio_committee_vs_reward_fund">'.$ltmp_arr['witnesses']['col_prop'].' inflation_ratio_committee_vs_reward_fund</option>
			<option value="inflation_recalc_period">'.$ltmp_arr['witnesses']['col_prop'].' inflation_recalc_period</option>

			<option value="data_operations_cost_additional_bandwidth">'.$ltmp_arr['witnesses']['col_prop'].' data_operations_cost_additional_bandwidth</option>
			<option value="witness_miss_penalty_percent">'.$ltmp_arr['witnesses']['col_prop'].' witness_miss_penalty_percent</option>
			<option value="witness_miss_penalty_duration">'.$ltmp_arr['witnesses']['col_prop'].' witness_miss_penalty_duration</option>

			<option value="create_invite_min_balance">'.$ltmp_arr['witnesses']['col_prop'].' create_invite_min_balance</option>
			<option value="committee_create_request_fee">'.$ltmp_arr['witnesses']['col_prop'].' committee_create_request_fee</option>
			<option value="create_paid_subscription_fee">'.$ltmp_arr['witnesses']['col_prop'].' create_paid_subscription_fee</option>
			<option value="account_on_sale_fee">'.$ltmp_arr['witnesses']['col_prop'].' account_on_sale_fee</option>
			<option value="subaccount_on_sale_fee">'.$ltmp_arr['witnesses']['col_prop'].' subaccount_on_sale_fee</option>
			<option value="witness_declaration_fee">'.$ltmp_arr['witnesses']['col_prop'].' witness_declaration_fee</option>
			<option value="withdraw_intervals">'.$ltmp_arr['witnesses']['col_prop'].' withdraw_intervals</option>

		</select>
	</p>';
	print '<table class="witnesses captions sortable-theme-slick" width="100%" data-sortable>';
	print '<thead>';
	print '
	<tr>
		<th data-sorted="true" data-sorted-direction="descending" data-field="num" data-sortable-type="int">#</th>
		<th data-field="account">'.$ltmp_arr['witnesses']['witness'].'</th>
		<th title="'.$ltmp_arr['witnesses']['votes_descr'].'" data-field="votes" class="text-right">'.$ltmp_arr['witnesses']['votes'].'</th>
		<th title="'.$ltmp_arr['witnesses']['version_descr'].'" data-field="version">'.$ltmp_arr['witnesses']['version'].'</th>
		<th title="'.$ltmp_arr['witnesses']['penalty_descr'].'" data-field="penalty" class="from-selector hidden">'.$ltmp_arr['witnesses']['penalty'].'</th>
		<th title="'.$ltmp_arr['witnesses']['blocks_descr'].'" data-field="blocks" class="from-selector hidden">'.$ltmp_arr['witnesses']['blocks'].'</th>
		<th title="'.$ltmp_arr['witnesses']['total_missed_descr'].'" data-field="total_missed" class="from-selector hidden">'.$ltmp_arr['witnesses']['total_missed'].'</th>
		<th title="'.$ltmp_arr['witnesses']['rewards_descr'].'" data-field="rewards" class="from-selector hidden">'.$ltmp_arr['witnesses']['rewards'].'</th>

		<th title="account_creation_fee" data-field="account_creation_fee" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>
		<th title="maximum_block_size" data-field="maximum_block_size" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>

		<th title="create_account_delegation_ratio" data-field="create_account_delegation_ratio" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>
		<th title="create_account_delegation_time" data-field="create_account_delegation_time" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>
		<th title="min_delegation" data-field="min_delegation" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>

		<th title="bandwidth_reserve_percent" data-field="bandwidth_reserve_percent" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>
		<th title="bandwidth_reserve_below" data-field="bandwidth_reserve_below" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>
		<th title="vote_accounting_min_rshares" data-field="vote_accounting_min_rshares" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>
		<th title="committee_request_approve_min_percent" data-field="committee_request_approve_min_percent" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>

		<th title="inflation_witness_percent" data-field="inflation_witness_percent" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>
		<th title="inflation_ratio_committee_vs_reward_fund" data-field="inflation_ratio_committee_vs_reward_fund" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>
		<th title="inflation_recalc_period" data-field="inflation_recalc_period" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>

		<th title="data_operations_cost_additional_bandwidth" data-field="data_operations_cost_additional_bandwidth" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>
		<th title="witness_miss_penalty_percent" data-field="witness_miss_penalty_percent" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>
		<th title="witness_miss_penalty_duration" data-field="witness_miss_penalty_duration" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>

		<th title="create_invite_min_balance" data-field="create_invite_min_balance" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>
		<th title="committee_create_request_fee" data-field="committee_create_request_fee" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>
		<th title="create_paid_subscription_fee" data-field="create_paid_subscription_fee" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>
		<th title="account_on_sale_fee" data-field="account_on_sale_fee" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>
		<th title="subaccount_on_sale_fee" data-field="subaccount_on_sale_fee" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>
		<th title="witness_declaration_fee" data-field="witness_declaration_fee" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>
		<th title="withdraw_intervals" data-field="withdraw_intervals" class="from-selector hidden">'.$ltmp_arr['witnesses']['prop'].'</th>
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
			<td data-value="'.$m['votes'].'" class="text-right">'.number_format($m['votes']/1000000,0,'.',' ').'</td>
			<td data-value="'.(str_replace('.','',$m['running_version'])).'"'.($hardfork_version_upgrade?' title="Голосует за активацию версии '.$m['hardfork_version_vote'].'" class="positive-color"':'').'>'.$m['running_version'].'</td>
			<td data-field="penalty" data-value="'.$m['penalty_percent'].'" class="from-selector hidden">'.round($m['penalty_percent']/100,2).'%</td>
			<td data-field="blocks" class="from-selector hidden">'.$m['blocks'].'</td>
			<td data-field="total_missed"  data-value="'.$total_missed_percent.'" class="from-selector hidden">'.$total_missed_percent.'% ('.$m['total_missed'].')</td>
			<td data-field="rewards" data-value="'.$m['rewards'].'" class="from-selector hidden">'.number_format($m['rewards']/1000000,2,'.',' ').' viz</td>

			<td data-field="account_creation_fee" data-value="'.intval(1000*floatval($props['account_creation_fee'])).'" class="from-selector hidden">'.number_format(floatval($props['account_creation_fee']),2,'.',' ').' viz</td>
			<td data-field="maximum_block_size" class="from-selector hidden">'.$props['maximum_block_size'].'</td>

			<td data-field="create_account_delegation_ratio" class="from-selector hidden">'.$props['create_account_delegation_ratio'].'</td>
			<td data-field="create_account_delegation_time" class="from-selector hidden">'.$props['create_account_delegation_time'].'</td>
			<td data-field="min_delegation" data-value="'.intval(1000*floatval($props['min_delegation'])).'" class="from-selector hidden">'.number_format(floatval($props['account_creation_fee']),2,'.',' ').' viz</td>

			<td data-field="bandwidth_reserve_percent" data-value="'.$props['bandwidth_reserve_percent'].'" class="from-selector hidden">'.round($props['bandwidth_reserve_percent']/100,2).'%</td>
			<td data-field="bandwidth_reserve_below" data-value="'.$props['bandwidth_reserve_below'].'" class="from-selector hidden">'.number_format($props['bandwidth_reserve_below'],2,'.',' ').' viz</td>
			<td data-field="vote_accounting_min_rshares" class="from-selector hidden">'.$props['vote_accounting_min_rshares'].'</td>
			<td data-field="committee_request_approve_min_percent" data-value="'.$props['committee_request_approve_min_percent'].'" class="from-selector hidden">'.round($props['committee_request_approve_min_percent']/100,2).'%</td>

			<td data-field="inflation_witness_percent" data-value="'.$props['inflation_witness_percent'].'" class="from-selector hidden">'.round($props['inflation_witness_percent']/100,2).'%</td>
			<td data-field="inflation_ratio_committee_vs_reward_fund" data-value="'.$props['inflation_ratio_committee_vs_reward_fund'].'" class="from-selector hidden">'.round($props['inflation_ratio_committee_vs_reward_fund']/100,2).'%</td>
			<td data-field="inflation_recalc_period" class="from-selector hidden">'.$props['inflation_recalc_period'].'</td>

			<td data-field="data_operations_cost_additional_bandwidth" data-value="'.$props['data_operations_cost_additional_bandwidth'].'" class="from-selector hidden">'.round($props['data_operations_cost_additional_bandwidth']/100,2).'%</td>
			<td data-field="witness_miss_penalty_percent" class="from-selector hidden">'.round($props['witness_miss_penalty_percent']/100,2).'%</td>
			<td data-field="witness_miss_penalty_duration" class="from-selector hidden">'.$props['witness_miss_penalty_duration'].'</td>

			<td data-field="create_invite_min_balance" data-value="'.intval(1000*floatval($props['create_invite_min_balance'])).'" class="from-selector hidden">'.number_format(floatval($props['create_invite_min_balance']),2,'.',' ').' viz</td>
			<td data-field="committee_create_request_fee" data-value="'.intval(1000*floatval($props['committee_create_request_fee'])).'" class="from-selector hidden">'.number_format(floatval($props['committee_create_request_fee']),2,'.',' ').' viz</td>
			<td data-field="create_paid_subscription_fee" data-value="'.intval(1000*floatval($props['create_paid_subscription_fee'])).'" class="from-selector hidden">'.number_format(floatval($props['create_paid_subscription_fee']),2,'.',' ').' viz</td>
			<td data-field="account_on_sale_fee" data-value="'.intval(1000*floatval($props['account_on_sale_fee'])).'" class="from-selector hidden">'.number_format(floatval($props['account_on_sale_fee']),2,'.',' ').' viz</td>
			<td data-field="subaccount_on_sale_fee" data-value="'.intval(1000*floatval($props['subaccount_on_sale_fee'])).'" class="from-selector hidden">'.number_format(floatval($props['subaccount_on_sale_fee']),2,'.',' ').' viz</td>
			<td data-field="witness_declaration_fee" data-value="'.intval(1000*floatval($props['witness_declaration_fee'])).'" class="from-selector hidden">'.number_format(floatval($props['witness_declaration_fee']),2,'.',' ').' viz</td>
			<td data-field="withdraw_intervals" class="from-selector hidden">'.$props['withdraw_intervals'].'</td>
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