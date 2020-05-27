<?php
error_reporting(255);
if(!$_SERVER['PWD']){
	exit;
}
$include_path=substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'],'/'));
$include_path=substr($include_path,0,strrpos($include_path,'/'));
set_include_path($include_path);
include('config.php');
$site_root=$include_path;
include('autoloader.php');
include('module/prepare.php');

$backup_pid_file=$site_root.'/module/backup.pid';
$backup_pid=false;
if(file_exists($backup_pid_file)){
	$backup_pid=file_get_contents($pid_file);
	$backup_pid_working=posix_getpgid($backup_pid);
	if($backup_pid_working){
		print 'VIZ Backup is working: delay to next checkup...';
		exit;
	}
}

$pid_file=$site_root.'/module/props_snapshot.pid';
$pid=false;
if(file_exists($pid_file)){
	$pid=file_get_contents($pid_file);
}
$new_pid=posix_getpid();
if($pid){
	$working=posix_getpgid($pid);
	if($working){
		print 'VIZ Props snapshot already working with PID: '.$pid.PHP_EOL;
		exit;
	}
	else{
		unlink($pid_file);
		print 'VIZ Props snapshot stopped, restarting... with PID: '.$new_pid.PHP_EOL;
	}
}
file_put_contents($pid_file,$new_pid);
print 'STARTUP: pid file: '.$pid_file.', pid: '.$new_pid.PHP_EOL;

$api=new viz_jsonrpc_web($api_arr[array_rand($api_arr)]);

$dgp=$api->execute_method('get_dynamic_global_properties');
$date=date_parse_from_format('Y-m-d\TH:i:s',$dgp['time']);
$dgp_time=mktime($date['hour'],$date['minute'],$date['second'],$date['month'],$date['day'],$date['year']);
$last_dgp=$dgp;
print '================================================================'.PHP_EOL.'Start from DGP:'.PHP_EOL;
print_r($dgp);

$schedule=$api->execute_method('get_witness_schedule');
print '================================================================'.PHP_EOL.'Start from Schedule:'.PHP_EOL;
print_r($schedule);

$need_update=true;

$work=true;
$start_time=microtime(true);
$proceed_ops=0;
$info_time=time();
while($work){
	if($need_update){
		$last_schedule=$schedule;
		$schedule=$api->execute_method('get_witness_schedule');
		if($schedule['next_shuffle_block_num']>$last_schedule['next_shuffle_block_num']){

			$dgp=$api->execute_method('get_dynamic_global_properties');
			$date=date_parse_from_format('Y-m-d\TH:i:s',$dgp['time']);
			$dgp_time=mktime($date['hour'],$date['minute'],$date['second'],$date['month'],$date['day'],$date['year']);

			if(0==$db->table_count('chain_props_snapshot',"WHERE `time`='".$dgp_time."'")){
				$shuffled_witnesses=str_split($schedule['current_shuffled_witnesses'],64);
				foreach($shuffled_witnesses as $k=>$v){
					$shuffled_witnesses[$k]=rtrim(pack('H*',$v),chr(0));
				}
				$shuffled_witnesses_str=implode(';',$shuffled_witnesses);
				$current_shuffle_block=$schedule['next_shuffle_block_num']-$schedule['num_scheduled_witnesses'];
				$db->sql("INSERT INTO `chain_props_snapshot`
					(
					`time`,
					`current_shuffle_block`,
					`next_shuffle_block`,
					`scheduled_witnesses`,
					`shuffled_witnesses`,
					`majority_version`,
					`json`,
					`account_creation_fee`,
					`maximum_block_size`,
					`create_account_delegation_ratio`,
					`create_account_delegation_time`,
					`min_delegation`,
					`min_curation_percent`,
					`max_curation_percent`,
					`bandwidth_reserve_percent`,
					`bandwidth_reserve_below`,
					`flag_energy_additional_cost`,
					`vote_accounting_min_rshares`,
					`committee_request_approve_min_percent`,
					`inflation_witness_percent`,
					`inflation_ratio_committee_vs_reward_fund`,
					`inflation_recalc_period`,
					`data_operations_cost_additional_bandwidth`,
					`witness_miss_penalty_percent`,
					`witness_miss_penalty_duration`,

					`create_invite_min_balance`,
					`committee_create_request_fee`,
					`create_paid_subscription_fee`,
					`account_on_sale_fee`,
					`subaccount_on_sale_fee`,
					`witness_declaration_fee`,
					`withdraw_intervals`
					)
					VALUES (
					'".(int)$dgp_time."',
					'".(int)$current_shuffle_block."',
					'".(int)$schedule['next_shuffle_block_num']."',
					'".(int)$schedule['num_scheduled_witnesses']."',
					'".$db->prepare($shuffled_witnesses_str)."',
					'".$db->prepare($schedule['majority_version'])."',
					'".$db->prepare(json_encode($schedule['median_props']))."',
					'".(int)(floatval($schedule['median_props']['account_creation_fee'])*1000)."',
					'".(int)$schedule['median_props']['maximum_block_size']."',
					'".(int)$schedule['median_props']['create_account_delegation_ratio']."',
					'".(int)$schedule['median_props']['create_account_delegation_time']."',
					'".(int)(floatval($schedule['median_props']['min_delegation'])*1000)."',
					'".(int)$schedule['median_props']['min_curation_percent']."',
					'".(int)$schedule['median_props']['max_curation_percent']."',
					'".(int)$schedule['median_props']['bandwidth_reserve_percent']."',
					'".(int)(floatval($schedule['median_props']['bandwidth_reserve_below'])*1000000)."',
					'".(int)$schedule['median_props']['flag_energy_additional_cost']."',
					'".(int)$schedule['median_props']['vote_accounting_min_rshares']."',
					'".(int)$schedule['median_props']['committee_request_approve_min_percent']."',
					'".(int)$schedule['median_props']['inflation_witness_percent']."',
					'".(int)$schedule['median_props']['inflation_ratio_committee_vs_reward_fund']."',
					'".(int)$schedule['median_props']['inflation_recalc_period']."',
					'".(int)$schedule['median_props']['data_operations_cost_additional_bandwidth']."',
					'".(int)$schedule['median_props']['witness_miss_penalty_percent']."',
					'".(int)$schedule['median_props']['witness_miss_penalty_duration']."',

					'".(int)(floatval($schedule['median_props']['create_invite_min_balance'])*1000)."',
					'".(int)(floatval($schedule['median_props']['committee_create_request_fee'])*1000)."',
					'".(int)(floatval($schedule['median_props']['create_paid_subscription_fee'])*1000)."',
					'".(int)(floatval($schedule['median_props']['account_on_sale_fee'])*1000)."',
					'".(int)(floatval($schedule['median_props']['subaccount_on_sale_fee'])*1000)."',
					'".(int)(floatval($schedule['median_props']['witness_declaration_fee'])*1000)."',
					'".(int)$schedule['median_props']['withdraw_intervals']."'
				)");
			}


			if(0==$db->table_count('dgp_snapshot',"WHERE `time`='".$dgp_time."'")){
				$db->sql("INSERT INTO `dgp_snapshot`
					(`time`,`json`,
					`current_reserve_ratio`,`max_virtual_bandwidth`,`average_block_size`,`maximum_block_size`,
					`current_supply`,`committee_fund`,`committee_requests`,`total_vesting_fund`,`total_vesting_shares`,
					`total_reward_fund`,`total_reward_shares`,
					`last_irreversible_block_num`,`head_block_number`,`inflation_witness_percent`,`inflation_ratio`) VALUES
					('".$dgp_time."','".$db->prepare(json_encode($dgp))."',
					'".(int)$dgp['current_reserve_ratio']."','".(int)$dgp['max_virtual_bandwidth']."','".(int)$dgp['average_block_size']."','".(int)$dgp['maximum_block_size']."',
					'".(int)(floatval($dgp['current_supply'])*1000)."','".(int)(floatval($dgp['committee_fund'])*1000)."','".(int)$dgp['committee_requests']."','".(int)(floatval($dgp['total_vesting_fund'])*1000)."','".(int)(floatval($dgp['total_vesting_shares'])*1000000)."',
					'".(int)(floatval($dgp['total_reward_fund'])*1000)."','".(int)$dgp['total_reward_shares']."',
					'".(int)$dgp['last_irreversible_block_num']."','".(int)$dgp['head_block_number']."','".(int)$dgp['inflation_witness_percent']."','".(int)$dgp['inflation_ratio']."')");
			}
		}
	}

	if(($dgp_time+3)<=(time())){//3 sec offset for lookup new state info
		$last_dgp=$dgp;
		$dgp=$api->execute_method('get_dynamic_global_properties');
		$date=date_parse_from_format('Y-m-d\TH:i:s',$dgp['time']);
		$dgp_time=mktime($date['hour'],$date['minute'],$date['second'],$date['month'],$date['day'],$date['year']);
	}

	if(time()>(9+$info_time)){//print log info each 9 sec (3 blocks)
		$current_time=microtime(true);
		$current_work_time=(int)(1000*($current_time-$start_time));
		print 'Last dgp block '.$dgp['head_block_number'].', next schedule '.$schedule['next_shuffle_block_num'].' ('.round($current_work_time/1000,2).'s execute time)'.PHP_EOL;
		$info_time=time();
	}

	if($last_dgp['head_block_number']<$dgp['head_block_number']){
		//workout only when dgp was changed, or small sleep
		$need_wait_blocks=$schedule['next_shuffle_block_num']-$dgp['head_block_number'];
		if($need_wait_blocks>0){
			$need_update=false;
			print 'Need sleeping '.($need_wait_blocks*3).'s'.PHP_EOL;
			sleep(3);//sleep 1 block and waiting new state
		}
		else{
			usleep(100);
			$need_update=true;
		}
	}
	else{
		usleep(100);
	}
	if(!$need_update){
		if(!file_exists($pid_file)){
			$work=false;
		}
	}
}
print 'INFO: PID file was deleted, self-terminating...'.PHP_EOL;
exit;