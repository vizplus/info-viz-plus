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

$pid_file=$site_root.'/module/ops_processing.pid';
$pid=false;
if(file_exists($pid_file)){
	$pid=file_get_contents($pid_file);
}
$new_pid=posix_getpid();
if($pid){
	$working=posix_getpgid($pid);
	if($working){
		print 'VIZ Ops processing already working with PID: '.$pid.PHP_EOL;
		exit;
	}
	else{
		unlink($pid_file);
		print 'VIZ Ops processing stopped, restarting... with PID: '.$new_pid.PHP_EOL;
	}
}
file_put_contents($pid_file,$new_pid);
print 'STARTUP: pid file: '.$pid_file.', pid: '.$new_pid.PHP_EOL;

$ignored_accounts=[949,948];//spam from bbb,ooo

$api=new viz_jsonrpc_web($api_arr[array_rand($api_arr)]);
$work=true;
$start_time=microtime(true);
$proceed_ops=0;
$info_time=time();
while($work){
	$current_time=microtime(true);
	$current_work_time=(int)(1000*($current_time-$start_time));
	$op_q=$db->sql("SELECT * FROM `ops` WHERE `counted`=0 LIMIT 10");// ORDER BY `id` ASC
	while($op_arr=$db->row($op_q)){
		$op_json=json_decode($op_arr['json'],true);
		if(1==$op_arr['type']){//witness_reward
			$account_id=parse_account($op_json['witness']);
			$witness_id=parse_witness($op_json['witness']);
			$reward=intval(floatval($op_json['shares'])*1000000);

			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `witnesses` SET `blocks`=1+`blocks`, `rewards`=`rewards`+'".$reward."', `update`=1 WHERE `id`='".$witness_id."'");

			$db->sql("UPDATE `ops` SET `target`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(2==$op_arr['type']){//witness_update
			$account_id=parse_account($op_json['owner']);
			$witness_id=parse_witness($op_json['owner']);

			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `witnesses` SET `update`='1' WHERE `id`='".$witness_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(3==$op_arr['type']){//account_witness_vote
			$account_id=parse_account($op_json['account']);
			$witness_id=parse_witness($op_json['witness']);
			$witness_account_id=parse_account($op_json['witness']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `witnesses` SET `update`='1' WHERE `id`='".$witness_id."'");

			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$witness_account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(4==$op_arr['type']){//withdraw_vesting
			$account_id=parse_account($op_json['account']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(5==$op_arr['type']){//account_create
			$account_id=parse_account($op_json['creator']);
			$target_id=parse_account($op_json['new_account_name']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$target_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(6==$op_arr['type']){//shutdown_witness
			$account_id=parse_account($op_json['owner']);
			$witness_id=parse_witness($op_json['owner']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."' WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `witnesses` SET `update`='1' WHERE `id`='".$witness_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(7==$op_arr['type']){//transfer
			$account_id=parse_account($op_json['from']);
			$target_id=parse_account($op_json['to']);
			$memo=$db->prepare($op_json['memo']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$target_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."', `memo`='".$memo."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(8==$op_arr['type']){//delegate_vesting_shares
			$account_id=parse_account($op_json['delegator']);
			$target_id=parse_account($op_json['delegatee']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$target_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(9==$op_arr['type']){//content DEPRECATED
			$account_id=parse_account($op_json['author']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."' WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(10==$op_arr['type']){//vote DEPRECATED
			$account_id=parse_account($op_json['voter']);
			$target_id=parse_account($op_json['author']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."' WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(11==$op_arr['type']){//chain_properties_update
			$account_id=parse_account($op_json['owner']);
			$witness_id=parse_witness($op_json['owner']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."' WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `witnesses` SET `update`='1' WHERE `id`='".$witness_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(12==$op_arr['type']){//account_update
			$account_id=parse_account($op_json['account']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(13==$op_arr['type']){//custom
			$accounts=[];
			foreach($op_json['required_active_auths'] as $account){
				$accounts[]=$account;
			}
			foreach($op_json['required_regular_auths'] as $account){
				$accounts[]=$account;
			}
			$account_id=0;//first account in list
			foreach($accounts as $account){
				$find_account_id=parse_account($account);
				if(!$account_id){
					$account_id=$find_account_id;
				}
			}
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."' WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(14==$op_arr['type']){//committee_worker_create_request
			$account_id=parse_account($op_json['creator']);
			$target_id=parse_account($op_json['worker']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."' WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(15==$op_arr['type']){//curation_reward DEPRECATED
			$account_id=parse_account($op_json['curator']);
			$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `target`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(16==$op_arr['type']){//author_reward DEPRECATED
			$account_id=parse_account($op_json['author']);
			$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `target`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(17==$op_arr['type']){//content_reward DEPRECATED
			$account_id=parse_account($op_json['author']);
		}
		elseif(18==$op_arr['type']){//content_payout_update DEPRECATED
			$account_id=parse_account($op_json['author']);
		}
		elseif(19==$op_arr['type']){//fill_vesting_withdraw
			$account_id=parse_account($op_json['from_account']);
			$target_id=parse_account($op_json['to_account']);
			$db->sql("UPDATE `accounts` SET `update`=1 WHERE (`id`='".$account_id."' OR `id`='".$target_id."')");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(20==$op_arr['type']){//content_benefactor_reward DEPRECATED
			$account_id=parse_account($op_json['benefactor']);
			$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `target`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(21==$op_arr['type']){//transfer_to_vesting
			$account_id=parse_account($op_json['from']);
			$target_id=parse_account($op_json['to']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$target_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(22==$op_arr['type']){//committee_vote_request
			$account_id=parse_account($op_json['voter']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."' WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(23==$op_arr['type']){//committee_worker_cancel_request
			$account_id=parse_account($op_json['creator']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."' WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(24==$op_arr['type']){//create_invite
			$account_id=parse_account($op_json['creator']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(25==$op_arr['type']){//hardfork
		}
		elseif(26==$op_arr['type']){//invite_registration
			$account_id=parse_account($op_json['initiator']);
			$target_id=parse_account($op_json['new_account_name']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$target_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(27==$op_arr['type']){//committee_cancel_request
		}
		elseif(28==$op_arr['type']){//account_metadata
			$account_id=parse_account($op_json['account']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(29==$op_arr['type']){//set_withdraw_vesting_route
			$account_id=parse_account($op_json['from_account']);
			$target_id=parse_account($op_json['to_account']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$target_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(30==$op_arr['type']){//claim_invite_balance
			$account_id=parse_account($op_json['initiator']);
			$target_id=parse_account($op_json['receiver']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$target_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(31==$op_arr['type']){//return_vesting_delegation
			$account_id=parse_account($op_json['account']);
			$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `target`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(32==$op_arr['type']){//committee_approve_request
		}
		elseif(33==$op_arr['type']){//committee_pay_request
			$account_id=parse_account($op_json['worker']);
			$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `target`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(34==$op_arr['type']){//committee_payout_request
		}
		elseif(35==$op_arr['type']){//delete_content DEPRECATED
		}
		elseif(36==$op_arr['type']){//account_witness_proxy
			$account_id=parse_account($op_json['account']);
			$target_id=parse_account($op_json['proxy']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(37==$op_arr['type']){//award
			$account_id=parse_account($op_json['initiator']);
			$target_id=parse_account($op_json['receiver']);
			$memo=$db->prepare($op_json['memo']);
			foreach($op_json['beneficiaries'] as $beneficiaries){
				$beneficiaries_account_id=parse_account($beneficiaries['account']);
				if($beneficiaries_account_id){
					$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$beneficiaries_account_id."'");
				}
			}
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$target_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."', `memo`='".$memo."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(38==$op_arr['type']){//receive_award
			$target_id=parse_account($op_json['receiver']);
			$db->sql("UPDATE `ops` SET `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(39==$op_arr['type']){//versioned_chain_properties_update
			$account_id=parse_account($op_json['owner']);
			$witness_id=parse_witness($op_json['owner']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."' WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `witnesses` SET `update`='1' WHERE `id`='".$witness_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(40==$op_arr['type']){//benefactor_award
			$target_id=parse_account($op_json['benefactor']);
			$db->sql("UPDATE `ops` SET `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(41==$op_arr['type']){//set_paid_subscription
			$account_id=parse_account($op_json['account']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."' WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(42==$op_arr['type']){//paid_subscribe
			$account_id=parse_account($op_json['subscriber']);
			$target_id=parse_account($op_json['account']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$target_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(43==$op_arr['type']){//paid_subscription_action
			$account_id=parse_account($op_json['subscriber']);
			$target_id=parse_account($op_json['account']);
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(44==$op_arr['type']){//cancel_paid_subscription
			$account_id=parse_account($op_json['subscriber']);
			$target_id=parse_account($op_json['account']);
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(45==$op_arr['type']){//set_subaccount_price
			$account_id=parse_account($op_json['account']);
			$target_id=parse_account($op_json['subaccount_seller']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."' WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(46==$op_arr['type']){//set_account_price
			$account_id=parse_account($op_json['account']);
			$target_id=parse_account($op_json['account_seller']);
			$ignore=false;
			if(in_array($account_id,$ignored_accounts)){
				$ignore=true;
			}
			if(in_array($target_id,$ignored_accounts)){
				$ignore=true;
			}
			if(!$ignore){
				$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."' WHERE `id`='".$account_id."'");
				$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
			}
		}
		elseif(47==$op_arr['type']){//buy_account
			$account_id=parse_account($op_json['buyer']);
			$target_id=parse_account($op_json['account']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$target_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(48==$op_arr['type']){//account_sale
			$account_id=parse_account($op_json['buyer']);
			$target_id=parse_account($op_json['seller']);
			$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$target_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(49==$op_arr['type']){//escrow_transfer
			$account_id=parse_account($op_json['from']);
			$target_id=parse_account($op_json['to']);
			$agent_id=parse_account($op_json['agent']);//second target? going put in memo field
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."', `memo`='".$agent_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(50==$op_arr['type']){//change_recovery_account
			$account_id=parse_account($op_json['account_to_recover']);
			$target_id=parse_account($op_json['new_recovery_account']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."' WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(51==$op_arr['type']){//proposal_create
			$account_id=parse_account($op_json['author']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."' WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(52==$op_arr['type']){//proposal_update
			$accounts_update_arr=array();
			foreach($op_json['active_approvals_to_add'] as $find_account){
				$accounts_update_arr[]=parse_account($find_account);
			}
			foreach($op_json['active_approvals_to_remove'] as $find_account){
				$accounts_update_arr[]=parse_account($find_account);
			}
			foreach($op_json['master_approvals_to_add'] as $find_account){
				$accounts_update_arr[]=parse_account($find_account);
			}
			foreach($op_json['master_approvals_to_remove'] as $find_account){
				$accounts_update_arr[]=parse_account($find_account);
			}
			foreach($op_json['regular_approvals_to_add'] as $find_account){
				$accounts_update_arr[]=parse_account($find_account);
			}
			foreach($op_json['regular_approvals_to_remove'] as $find_account){
				$accounts_update_arr[]=parse_account($find_account);
			}

			foreach($op_json['key_approvals_to_add'] as $find_key){
				$find_account_with_key=$db->select_one('accounts_keys','account',"WHERE `key`='".$db->prepare($find_key)."'");
				if($find_account_with_key){
					$accounts_update_arr[]=$find_account_with_key;
				}
			}
			foreach($op_json['key_approvals_to_remove'] as $find_key){
				$find_account_with_key=$db->select_one('accounts_keys','account',"WHERE `key`='".$db->prepare($find_key)."'");
				if($find_account_with_key){
					$accounts_update_arr[]=$find_account_with_key;
				}
			}

			foreach($accounts_update_arr as $account_id){
				$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."' WHERE `id`='".$account_id."'");
			}
			if($accounts_update_arr[0]){
				$account_id=$accounts_update_arr[0];
				$db->sql("UPDATE `ops` SET `initiator`='".$account_id."' WHERE `id`='".$op_arr['id']."'");
			}
		}
		elseif(53==$op_arr['type']){//escrow_approve
			$approve=$op_json['approve'];
			$from_id=parse_account($op_json['from']);
			$to_id=parse_account($op_json['to']);
			$agent_id=parse_account($op_json['agent']);//second target? going put in memo field
			$who=parse_account($op_json['who']);
			$target_id=$from_id;
			if($who==$from_id){
				$target_id=$to_id;
			}
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$who."'");
			if($approve){
				$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$agent_id."'");
			}
			else{
				$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$from_id."'");
			}
			$db->sql("UPDATE `ops` SET `initiator`='".$who."', `target`='".$target_id."', `memo`='".$agent_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(54==$op_arr['type']){//escrow_release
			$from_id=parse_account($op_json['from']);
			$to_id=parse_account($op_json['to']);
			$agent_id=parse_account($op_json['agent']);//second target? going put in memo field
			$who=parse_account($op_json['who']);
			$receiver=parse_account($op_json['receiver']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."' WHERE `id`='".$who."'");
			$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$receiver."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$who."', `target`='".$receiver."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(55==$op_arr['type']){//escrow_dispute
			$from_id=parse_account($op_json['from']);
			$to_id=parse_account($op_json['to']);
			$agent_id=parse_account($op_json['agent']);//second target? going put in memo field
			$who=parse_account($op_json['who']);
			$target_id=$from_id;
			if($who==$from_id){
				$target_id=$to_id;
			}
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."' WHERE `id`='".$who."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$who."', `target`='".$target_id."', `memo`='".$agent_id."' WHERE `id`='".$op_arr['id']."'");
		}
		elseif(56==$op_arr['type']){//use_invite_balance
			$account_id=parse_account($op_json['initiator']);
			$target_id=parse_account($op_json['receiver']);
			$db->sql("UPDATE `accounts` SET `activity`='".$op_arr['time']."', `update`=1 WHERE `id`='".$account_id."'");
			$db->sql("UPDATE `accounts` SET `update`=1 WHERE `id`='".$target_id."'");
			$db->sql("UPDATE `ops` SET `initiator`='".$account_id."', `target`='".$target_id."' WHERE `id`='".$op_arr['id']."'");
		}
		else{
			exit;
		}
		$db->sql("UPDATE `ops_type` SET `count`=1+`count`, `d_count`=1+`d_count`, `w_count`=1+`w_count`, `m_count`=1+`m_count` WHERE `id`='".$op_arr['type']."'");
		$db->sql("UPDATE `ops` SET `counted`=1 WHERE `id`='".$op_arr['id']."'");
		$proceed_ops++;
	}
	if(!file_exists($pid_file)){
		$work=false;
		break;
	}

	$time=time();
	$day_offset=$time-86400;
	$week_offset=$time-604800;//7 days
	$month_offset=$time-2592000;//30 days

	$op_q=$db->sql("SELECT `id`,`type` FROM `ops` WHERE `counted`=1 AND `time`<'".$day_offset."' LIMIT 50");
	while($op_arr=$db->row($op_q)){
		$db->sql("UPDATE `ops_type` SET `d_count`=`d_count`-1 WHERE `id`='".$op_arr['type']."'");
		$db->sql("UPDATE `ops` SET `counted`=2 WHERE `id`='".$op_arr['id']."'");
	}
	if(!file_exists($pid_file)){
		$work=false;
		break;
	}

	$op_q=$db->sql("SELECT `id`,`type` FROM `ops` WHERE `counted`=2 AND `time`<'".$week_offset."' LIMIT 50");
	while($op_arr=$db->row($op_q)){
		$db->sql("UPDATE `ops_type` SET `w_count`=`w_count`-1 WHERE `id`='".$op_arr['type']."'");
		$db->sql("UPDATE `ops` SET `counted`=3 WHERE `id`='".$op_arr['id']."'");
	}
	if(!file_exists($pid_file)){
		$work=false;
		break;
	}

	$op_q=$db->sql("SELECT `id`,`type` FROM `ops` WHERE `counted`=3 AND `time`<'".$month_offset."' LIMIT 50");
	while($op_arr=$db->row($op_q)){
		$db->sql("UPDATE `ops_type` SET `m_count`=`m_count`-1 WHERE `id`='".$op_arr['type']."'");
		$db->sql("UPDATE `ops` SET `counted`=4 WHERE `id`='".$op_arr['id']."'");
	}
	if(time()>(5+$info_time)){
		print 'Proceed ops '.$proceed_ops.' ('.$current_work_time.'ms execute time, avg '.round(100*$current_work_time/$proceed_ops,2).'ms per 100 ops)'.PHP_EOL;
		$info_time=time();
	}
	if(!file_exists($pid_file)){
		$work=false;
		break;
	}
	usleep(100);
}
print 'INFO: PID file was deleted, self-terminating...'.PHP_EOL;
exit;