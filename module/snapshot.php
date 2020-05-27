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

$time=time();
$from_time=$time - 3600*24;//1 day
$date=date('d.m.Y');


$work_time=microtime(true);
$accounts_in_snapshot=0;
$accounts_q=$db->sql("SELECT `id`,`balance`,`shares`,`delegated`,`received`,`effective`,`witnesses_voted_for`,`custom_sequence` FROM `accounts` WHERE (`activity`>'".$from_time."' OR `update_time`>='".$from_time."')");
while($account=$db->row($accounts_q)){
	$initiator_ops_count=$db->table_count('ops',"WHERE `time`>='".$from_time."' AND `initiator`='".$account['id']."' AND `v`=0");
	$target_ops_count=$db->table_count('ops',"WHERE `time`>='".$from_time."' AND `target`='".$account['id']."'");
	$db->sql("INSERT INTO `accounts_snapshot` (`date`,`time`,`account`,`balance`,`shares`,`delegated`,`received`,`effective`,`witnesses_voted_for`,`custom_sequence`,`initiator_ops_count`,`target_ops_count`) VALUES ('".$db->prepare($date)."','".$time."','".$account['id']."','".$db->prepare($account['balance'])."','".$db->prepare($account['shares'])."','".$db->prepare($account['delegated'])."','".$db->prepare($account['received'])."','".$db->prepare($account['effective'])."','".$db->prepare($account['witnesses_voted_for'])."','".$db->prepare($account['custom_sequence'])."','".$initiator_ops_count."','".$target_ops_count."')");
	$accounts_in_snapshot++;
}
print 'Accounts in snapshot: '.$accounts_in_snapshot.PHP_EOL;
print 'Work time for accounts snapshot: '.((int)(1000*(microtime(true)-$work_time))).'ms'.PHP_EOL;

$witnesses_q=$db->sql("SELECT `id`,`votes` FROM `witnesses`");
while($witness=$db->row($witnesses_q)){
	$witness_votes=$db->sql_row("SELECT SUM(`votes`) as `sum` FROM `witnesses_votes` WHERE `witness` = '".$witness['id']."'");
	if($witness_votes['sum']!=$witness['votes']){
		$db->sql("UPDATE `witnesses` SET `votes`='".$witness_votes['sum']."', `update_time`='".$from_time."' WHERE `id`='".$witness['id']."'");
	}
}

$work_time=microtime(true);
$witnesses_in_snapshot=0;
$witnesses_q=$db->sql("SELECT `id`,`blocks`,`rewards`,`votes`,`penalty_percent`,`total_missed`,`signing_key` FROM `witnesses` WHERE (`signing_key`!='VIZ1111111111111111111111111111111114T1Anm' OR `update_time`>='".$from_time."')");
while($witness=$db->row($witnesses_q)){
	$active=1;
	if('VIZ1111111111111111111111111111111114T1Anm'==$witness['signing_key']){
		$active=0;
	}
	$votes_count=$db->table_count('witnesses_votes',"WHERE `witness`='".$witness['id']."'");
	$db->sql("INSERT INTO `witnesses_snapshot` (`date`,`time`,`witness`,`blocks`,`rewards`,`votes`,`penalty_percent`,`total_missed`,`votes_count`,`active`) VALUES ('".$db->prepare($date)."','".$time."','".$witness['id']."','".$db->prepare($witness['blocks'])."','".$db->prepare($witness['rewards'])."','".$db->prepare($witness['votes'])."','".$db->prepare($witness['penalty_percent'])."','".$db->prepare($witness['total_missed'])."','".$votes_count."','".$active."')");
	$witnesses_in_snapshot++;
}
print 'Witnesses in snapshot: '.$witnesses_in_snapshot.PHP_EOL;
print 'Work time for witnesses snapshot: '.((int)(1000*(microtime(true)-$work_time))).'ms'.PHP_EOL;

$time=time();
$time_expand4=$time+3600*4;//4 hour to fix date joke on 0:0:0 as previous day
//$day_time=mktime(0,0,0,date('m'),date('d'),date('Y'));//-2 hour
$day_time=mktime(0,0,0,date('m',$time_expand4),date('d',$time_expand4),date('Y',$time_expand4));//0 GMT
$day_time=$day_time-7200;//-2 hour??? server lag

$hour_offset=$day_time-3600;
$day_offset=$day_time-86400;
$day_offset_block=$db->select_one('blocks','id',"WHERE `time`>='".$day_offset."'");
$day_offset_block_stop=$db->select_one('blocks','id',"WHERE `time`<='".$day_time."' ORDER BY `id` DESC");
$week_offset=$day_time-604800;//7 days
$month_offset=$day_time-2592000;//30 days

$trx_count=$db->table_count('trx',"WHERE `block`>='".$day_offset_block."' AND `block`<='".$day_offset_block_stop."'");

$accounts_30=$db->table_count('accounts',"WHERE `activity`>'".$month_offset."'");
$accounts_7=$db->table_count('accounts',"WHERE `activity`>'".$week_offset."'");
$accounts_1=$db->table_count('accounts',"WHERE `activity`>'".$day_offset."'");

$dgp=$db->sql_row("SELECT * FROM `dgp_snapshot` WHERE `time`<='".$day_time."' ORDER BY `id` DESC LIMIT 1");
$capacity=intval(floor($dgp['average_block_size']/$dgp['maximum_block_size']*10000));

$db->sql("INSERT INTO `stats` (`time`,`accounts_1`,`accounts_7`,`accounts_30`,`trx_count`,`capacity`) VALUES ('".$day_time."','".$accounts_1."','".$accounts_7."','".$accounts_30."','".$trx_count."','".$capacity."')");

exit;