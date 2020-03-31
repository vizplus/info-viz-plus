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

$pid_file=$site_root.'/module/ops_linking.pid';
$pid=false;
if(file_exists($pid_file)){
	$pid=file_get_contents($pid_file);
}
$new_pid=posix_getpid();
if($pid){
	$working=posix_getpgid($pid);
	if($working){
		print 'VIZ Ops linking already working with PID: '.$pid.PHP_EOL;
		exit;
	}
	else{
		unlink($pid_file);
		print 'VIZ Ops linking stopped, restarting... with PID: '.$new_pid.PHP_EOL;
	}
}
file_put_contents($pid_file,$new_pid);
print 'STARTUP: pid file: '.$pid_file.', pid: '.$new_pid.PHP_EOL;
$work=true;
$start_time=microtime(true);
$linked_ops=0;
$info_time=time();
$op_types_arr=array(
	//Аккаунты
	'5',//account_create
	'12',//account_update
	'28',//account_metadata
	'45',//set_subaccount_price
	'46',//set_account_price
	'47',//buy_account
	'48',//account_sale

	//Капитал
	'4',//withdraw_vesting
	'8',//delegate_vesting_shares
	'19',//fill_vesting_withdraw
	'21',//transfer_to_vesting
	'29',//set_withdraw_vesting_route
	'31',//return_vesting_delegation

	//Кошелек
	'7',//transfer
	//'21',//transfer_to_vesting
	'24',//create_invite
	'26',//invite_registration
	'30',//claim_invite_balance
	//'49',//escrow_transfer
	//'54',//escrow_release

	//Награды
	'37',//award
	'38',//receive_award
	'40',//benefactor_award

	//ДАО
	'2',//witness_update
	'3',//account_witness_vote
	'6',//shutdown_witness
	'14',//committee_worker_create_request
	'22',//committee_vote_request
	'23',//committee_worker_cancel_request
	'27',//committee_cancel_request
	'32',//committee_approve_request
	'33',//committee_pay_request
	'34',//committee_payout_request
	'36',//account_witness_proxy

	//Подписки
	'41',//set_paid_subscription
	'42',//paid_subscribe
	'43',//paid_subscription_action
	'44',//cancel_paid_subscription
);
while($work){
	$current_time=microtime(true);
	$current_work_time=(int)(1000*($current_time-$start_time));
	$op_q=$db->sql("SELECT `id`,`type`,`initiator`,`target` FROM `ops` WHERE `linked`=0 AND `counted`!=0 LIMIT 20");// ORDER BY `id` ASC
	while($op_arr=$db->row($op_q)){
		if(in_array($op_arr['type'],$op_types_arr)){
			if(null!=$op_arr['initiator']){
				$db->sql("INSERT INTO `ops_link` (`account`,`op`) VALUES ('".$op_arr['initiator']."',".$op_arr['id'].")");
			}
			if(null!=$op_arr['target']){
				if($op_arr['initiator']!=$op_arr['target']){
					$db->sql("INSERT INTO `ops_link` (`account`,`op`) VALUES ('".$op_arr['target']."',".$op_arr['id'].")");
				}
			}
		}
		$db->sql("UPDATE `ops` SET `linked`=1 WHERE `id`='".$op_arr['id']."'");
		$linked_ops++;
	}
	if(time()>(5+$info_time)){
		print 'Linked ops '.$linked_ops.' ('.$current_work_time.'ms execute time, avg '.round(100*$current_work_time/$linked_ops,2).'ms per 100 ops)'.PHP_EOL;
		$info_time=time();
	}
	if(!file_exists($pid_file)){
		$work=false;
	}
	usleep(100);
}
print 'INFO: PID file was deleted, self-terminating...'.PHP_EOL;
exit;