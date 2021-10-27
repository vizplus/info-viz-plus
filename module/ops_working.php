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

$pid_file=$site_root.'/module/ops_working.pid';
$pid=false;
if(file_exists($pid_file)){
	$pid=file_get_contents($pid_file);
}
$new_pid=posix_getpid();
if($pid){
	$working=posix_getpgid($pid);
	if($working){
		print 'VIZ Ops working already working with PID: '.$pid.PHP_EOL;
		exit;
	}
	else{
		unlink($pid_file);
		print 'VIZ Ops working stopped, restarting... with PID: '.$new_pid.PHP_EOL;
	}
}
file_put_contents($pid_file,$new_pid);
print 'STARTUP: pid file: '.$pid_file.', pid: '.$new_pid.PHP_EOL;

$work=true;
$start_time=microtime(true);
$worked_ops=1;
$info_time=time();
$op_types_arr=[
	'delegations'=>[
		'5',//account_create
		'8',//delegate_vesting_shares
		'31',//return_vesting_delegation
	],
	'escrow'=>[
		'49',//escrow_transfer
		'53',//escrow_approve
		'54',//escrow_release
		'55',//escrow_dispute
		'57',//expire_escrow_ratification
	],
	'invites'=>[
		'24',//create_invite
		'26',//invite_registration
		'30',//claim_invite_balance
		'56',//use_invite_balance
	]
];
$work_ops_types=[];
foreach($op_types_arr as $work_type=>$work_ops_types_arr){
	foreach($work_ops_types_arr as $work_ops_type){
		$work_ops_types[$work_ops_type]=true;
	}
}
$work_ops_str_arr=[];
$work_ops_str='';
foreach($work_ops_types as $work_op=>$work_op_exist){
	$work_ops_str_arr[]='`type`='.$work_op;
}
$work_ops_str=implode(' OR ',$work_ops_str_arr);
$work_ops_str='('.$work_ops_str.') AND ';
while($work){
	$current_time=microtime(true);
	$current_work_time=(int)(1000*($current_time-$start_time));
	$op_q=$db->sql("SELECT `id`,`type`,`json`,`time` FROM `ops` WHERE ".$work_ops_str." `worked`=0 LIMIT 10");// ORDER BY `id` ASC
	while($op_arr=$db->row($op_q)){
		foreach($op_types_arr as $work_type=>$work_ops_types_arr){
			if('invites'==$work_type){
				if(in_array($op_arr['type'],$work_ops_types_arr)){
					$op_json=json_decode($op_arr['json'],true);
					if(24==$op_arr['type']){//create_invite
						$creator=$op_json['creator'];
						$creator_id=parse_account($creator);
						$balance=intval(floatval($op_json['balance'])*100);
						//VIZ5WRFVYJzUK3NyBnxJnRhupLLMnZfA94QrN6KZAAuARMgYMYa6P
						//53 chars for public/invite key
						$invite_key=$op_json['invite_key'];

						$db->sql("INSERT INTO `invites` (`time`,`creator`,`balance`,`invite_key`) VALUES ('".$op_arr['time']."','".(int)$creator_id."','".(int)$balance."','".$db->prepare($invite_key)."')");
					}
					if((26==$op_arr['type'])||(30==$op_arr['type'])||(56==$op_arr['type'])){
						$initiator=$op_json['initiator'];
						$initiator_id=parse_account($initiator);

						$receiver=(26==$op_arr['type']?$op_json['new_account_name']:$op_json['receiver']);
						$receiver_id=parse_account($receiver);

						$invite_status=1;
						if(30==$op_arr['type']){
							$invite_status=2;
						}
						if(56==$op_arr['type']){
							$invite_status=3;
						}
						//5JgT6VBy3jtrRg6WNpq7Fe3xTXkeRp1CZm5JDXuCTZydi4HTcqC
						//51 chars for private/secret key
						$invite_secret=$op_json['invite_secret'];
						//new_account_key prop don't need to invites table
						$public_key=new VIZ\Key($invite_secret,true);
						$public_key->to_public();
						$public_key_str=$public_key->encode();

						$invite_id=$db->select_one('invites','id',"WHERE `invite_key`='".$public_key_str."'");
						if($invite_id){
							$db->sql("UPDATE `invites` SET `status`=".$invite_status.", `claim_time`='".$op_arr['time']."', `initiator`=".$initiator_id.", `receiver`=".$receiver_id.", `secret_key`='".$invite_secret."' WHERE `id`='".$invite_id."'");
						}
					}
				}
			}
			if('escrow'==$work_type){
				if(in_array($op_arr['type'],$work_ops_types_arr)){
					$op_json=json_decode($op_arr['json'],true);
					if(49==$op_arr['type']){//escrow_transfer
						$db->sql("UPDATE `escrow` SET `active`=0, `amount`=0, `fee`=0, `expired`=1 WHERE `approved`=0 AND `ratification_deadline`<".$op_arr['time']);
						$db->sql("UPDATE `escrow` SET `expired`=1 WHERE `approved`=1 AND `expiration`<".$op_arr['time']);

						$escrow_id=$op_json['escrow_id'];
						$from=parse_account($op_json['from']);
						$to=parse_account($op_json['to']);
						$agent=parse_account($op_json['agent']);
						$token_amount=(int)(floatval($op_json['token_amount'])*1000);
						$fee=(int)(floatval($op_json['fee'])*1000);
						$json=json_encode($op_json['json_metadata']);
						$date=date_parse_from_format('Y-m-d\TH:i:s',$op_json['ratification_deadline']);
						$ratification_deadline=mktime($date['hour'],$date['minute'],$date['second'],$date['month'],$date['day'],$date['year']);
						$date=date_parse_from_format('Y-m-d\TH:i:s',$op_json['escrow_expiration']);
						$escrow_expiration=mktime($date['hour'],$date['minute'],$date['second'],$date['month'],$date['day'],$date['year']);

						$db->sql("INSERT INTO `escrow` (`escrow_id`,`from`,`to`,`agent`,`amount`,`fee`,`json`,`time`,`ratification_deadline`,`expiration`) VALUES ('".$escrow_id."','".$from."','".$to."','".$agent."','".$token_amount."','".$fee."','".$db->prepare($json)."','".$op_arr['time']."','".$ratification_deadline."','".$escrow_expiration."')");
					}
					if(53==$op_arr['type']){//escrow_approve
						$db->sql("UPDATE `escrow` SET `active`=0, `amount`=0, `fee`=0, `expired`=1 WHERE `approved`=0 AND `ratification_deadline`<".$op_arr['time']);
						$db->sql("UPDATE `escrow` SET `expired`=1 WHERE `approved`=1 AND `expiration`<".$op_arr['time']);

						$escrow_id=$op_json['escrow_id'];
						$from=parse_account($op_json['from']);
						$to=parse_account($op_json['to']);
						$agent=parse_account($op_json['agent']);

						$who=parse_account($op_json['who']);
						$approve=$op_json['approve'];
						$full_approve=false;

						$escrow_arr=$db->sql_row("SELECT * FROM `escrow` WHERE `active`=1 AND `escrow_id`='".$escrow_id."' AND `from`='".$from."'");
						if($escrow_arr['id']){
							if($approve){
								if($who==$agent){
									$escrow_arr['approved_by_agent']=1;
									if($escrow_arr['approved_by_to']){//full approve
										$db->sql("UPDATE `escrow` SET `fee`=0, `approved`=1, `approved_by_agent`=1 WHERE `id`='".$escrow_arr['id']."'");
									}
									else{
										$db->sql("UPDATE `escrow` SET `approved_by_agent`=1 WHERE `id`='".$escrow_arr['id']."'");
									}
								}
								if($who==$to){
									$escrow_arr['approved_by_to']=1;
									if($escrow_arr['approved_by_agent']){//full approve
										$db->sql("UPDATE `escrow` SET `fee`=0, `approved`=1, `approved_by_to`=1 WHERE `id`='".$escrow_arr['id']."'");
									}
									else{
										$db->sql("UPDATE `escrow` SET `approved_by_to`=1 WHERE `id`='".$escrow_arr['id']."'");
									}
								}
							}
							else{

							}
						}
					}
					if(54==$op_arr['type']){//escrow_release
						$db->sql("UPDATE `escrow` SET `active`=0, `amount`=0, `fee`=0, `expired`=1 WHERE `approved`=0 AND `ratification_deadline`<".$op_arr['time']);
						$db->sql("UPDATE `escrow` SET `expired`=1 WHERE `approved`=1 AND `expiration`<".$op_arr['time']);

						$escrow_id=$op_json['escrow_id'];
						$from=parse_account($op_json['from']);
						$to=parse_account($op_json['to']);
						$agent=parse_account($op_json['agent']);
						$receiver=parse_account($op_json['receiver']);
						$token_amount=(int)(floatval($op_json['token_amount'])*1000);
						$escrow_arr=$db->sql_row("SELECT * FROM `escrow` WHERE `active`=1 AND `escrow_id`='".$escrow_id."' AND `from`='".$from."'");
						if($escrow_arr['id']){
							if($escrow_arr['amount']==$token_amount){
								$db->sql("UPDATE `escrow` SET `amount`=`amount`-'".$token_amount."', `active`=0 WHERE `id`='".$escrow_arr['id']."'");
							}
							else{
								$db->sql("UPDATE `escrow` SET `amount`=`amount`-'".$token_amount."' WHERE `id`='".$escrow_arr['id']."'");
							}
						}
					}
					if(55==$op_arr['type']){//escrow_dispute
						$db->sql("UPDATE `escrow` SET `active`=0, `amount`=0, `fee`=0, `expired`=1 WHERE `approved`=0 AND `ratification_deadline`<".$op_arr['time']);
						$db->sql("UPDATE `escrow` SET `expired`=1 WHERE `approved`=1 AND `expiration`<".$op_arr['time']);

						$escrow_id=$op_json['escrow_id'];
						$from=parse_account($op_json['from']);
						$to=parse_account($op_json['to']);
						$agent=parse_account($op_json['agent']);

						$who=parse_account($op_json['who']);
						$escrow_arr=$db->sql_row("SELECT * FROM `escrow` WHERE `active`=1 AND `escrow_id`='".$escrow_id."' AND `from`='".$from."'");
						if($escrow_arr['id']){
							$db->sql("UPDATE `escrow` SET `dispute_by`='".$who."', `dispute`=1 WHERE `id`='".$escrow_arr['id']."'");
						}
					}
					if(57==$op_arr['type']){//expire_escrow_ratification
						$db->sql("UPDATE `escrow` SET `active`=0, `amount`=0, `fee`=0, `expired`=1 WHERE `approved`=0 AND `ratification_deadline`<".$op_arr['time']);
						$db->sql("UPDATE `escrow` SET `expired`=1 WHERE `approved`=1 AND `expiration`<".$op_arr['time']);

						$escrow_id=$op_json['escrow_id'];
						$from=parse_account($op_json['from']);
						$to=parse_account($op_json['to']);
						$agent=parse_account($op_json['agent']);
						$receiver=parse_account($op_json['receiver']);
						$token_amount=(int)(floatval($op_json['token_amount'])*1000);
						$fee=(int)(floatval($op_json['fee'])*1000);
						$date=date_parse_from_format('Y-m-d\TH:i:s',$op_json['ratification_deadline']);
						$ratification_deadline=mktime($date['hour'],$date['minute'],$date['second'],$date['month'],$date['day'],$date['year']);
						$escrow_arr=$db->sql_row("SELECT * FROM `escrow` WHERE `active`=1 AND `escrow_id`='".$escrow_id."' AND `from`='".$from."'");
						if($escrow_arr['id']){
							$db->sql("UPDATE `escrow` SET `amount`=`amount`-'".$token_amount."', `expired`=1, `active`=0 WHERE `id`='".$escrow_arr['id']."'");
						}
					}
				}
			}
			if('delegations'==$work_type){
				if(in_array($op_arr['type'],$work_ops_types_arr)){
					$op_json=json_decode($op_arr['json'],true);
					if(5==$op_arr['type']){//account_create
						$from=parse_account($op_json['creator']);
						$to=parse_account($op_json['new_account_name']);
						$shares=(int)(floatval($op_json['delegation'])*1000000);
						$db->sql("INSERT INTO `delegations` (`from`,`to`,`shares`,`time`) VALUES ('".$from."','".$to."','".$shares."','".$op_arr['time']."')");
					}
					if(31==$op_arr['type']){//return_vesting_delegation
						$from=parse_account($op_json['account']);
						$shares=(int)(floatval($op_json['vesting_shares'])*1000000);
						$existed_return=$db->sql_row("SELECT * FROM `delegations` WHERE `from`='".$from."' AND `to`='".$from."'");
						if($existed_return['id']){
							if($existed_return['shares']<=$shares){
								$db->sql("DELETE FROM `delegations` WHERE `from`='".$from."' AND `to`='".$from."'");
							}
							else{
								$db->sql("UPDATE `delegations` SET `shares`=`shares`-'".abs($shares)."', `time`='".$op_arr['time']."' WHERE `from`='".$from."' AND `to`='".$from."'");
							}
						}
					}
					if(8==$op_arr['type']){//delegate_vesting_shares
						$from=parse_account($op_json['delegator']);
						$to=parse_account($op_json['delegatee']);
						$shares=(int)(floatval($op_json['vesting_shares'])*1000000);
						$existed=$db->sql_row("SELECT * FROM `delegations` WHERE `from`='".$from."' AND `to`='".$to."'");
						if($existed['id']){
							$shares_diff=$shares-$existed['shares'];
							if($shares_diff>=0){
								//positive, update changes
								$db->sql("UPDATE `delegations` SET `shares`='".$shares."', `time`='".$op_arr['time']."' WHERE `from`='".$from."' AND `to`='".$to."'");
							}
							else{
								if(0==$shares_diff){//can't be there, because diff = 0, same amount
									//return exist?
									$existed_return=$db->sql_row("SELECT * FROM `delegations` WHERE `from`='".$from."' AND `to`='".$from."'");
									if($existed_return['id']){//add shares amount
										$db->sql("UPDATE `delegations` SET `shares`=`shares`+'".abs($shares)."', `time`='".$op_arr['time']."' WHERE `from`='".$from."' AND `to`='".$from."'");
									}
									else{//insert new returning shares amount
										$db->sql("INSERT INTO `delegations` (`from`,`to`,`shares`,`time`) VALUES ('".$from."','".$from."','".abs($shares)."','".$op_arr['time']."')");//check new?
									}
									//delete delegation
									$db->sql("DELETE FROM `delegations` WHERE `from`='".$from."' AND `to`='".$to."'");
								}
								else{
									//negative, return and update
									$existed_return=$db->sql_row("SELECT * FROM `delegations` WHERE `from`='".$from."' AND `to`='".$from."'");
									if($existed_return['id']){//add shares amount
										$db->sql("UPDATE `delegations` SET `shares`=`shares`+'".abs($shares_diff)."', `time`='".$op_arr['time']."' WHERE `from`='".$from."' AND `to`='".$from."'");
									}
									else{//insert new returning shares amount
										$db->sql("INSERT INTO `delegations` (`from`,`to`,`shares`,`time`) VALUES ('".$from."','".$from."','".abs($shares_diff)."','".$op_arr['time']."')");//check new?
									}
									//update changes
									if(0==$shares){
										$db->sql("DELETE FROM `delegations` WHERE `from`='".$from."' AND `to`='".$to."'");
									}
									else{
										$db->sql("UPDATE `delegations` SET `shares`='".$shares."', `time`='".$op_arr['time']."' WHERE `from`='".$from."' AND `to`='".$to."'");
									}
								}
							}
						}
						else{
							$db->sql("INSERT INTO `delegations` (`from`,`to`,`shares`,`time`) VALUES ('".$from."','".$to."','".$shares."','".$op_arr['time']."')");
						}
					}
				}
			}
		}
		$db->sql("UPDATE `ops` SET `worked`=1 WHERE `id`='".$op_arr['id']."'");
		$worked_ops++;
		usleep(100000);
	}
	if(time()>(5+$info_time)){
		print 'Worked ops '.$worked_ops.' ('.$current_work_time.'ms execute time, avg '.round(100*$current_work_time/$worked_ops,2).'ms per 100 ops)'.PHP_EOL;
		$info_time=time();
	}
	if(!file_exists($pid_file)){
		$work=false;
	}
}
print 'INFO: PID file was deleted, self-terminating...'.PHP_EOL;
exit;