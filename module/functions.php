<?php
include('ltmp.php');
function raw_string($str){
	$length=dechex(strlen($str));
	if(strlen($length)%2!=0){
		$length='0'.$length;
	}
	$hex=bin2hex($str);
	$result=$length.$hex;
	return $result;
}
function raw_pub_key($key){
	$pub_key=new viz_keys();
	$pub_key->import_public($key);
	return $pub_key->hex;
}
function raw_asset($str){
	$str_arr=explode(' ',$str);
	$number_arr=explode('.',$str_arr[0]);

	$precision=strlen($number_arr[1]);
	$precision_hex=dechex($precision);
	if(strlen($precision_hex)%2!=0){
		$precision_hex='0'.$precision_hex;
	}

	$number=(int)implode('',$number_arr);
	$number_hex=dechex($number);
	if(strlen($number_hex)%2!=0){
		$number_hex='0'.$number_hex;
	}
	$number_hex=bin2hex(strrev(hex2bin($number_hex)));
	$length=8-(strlen($number_hex)/2);
	if($length>0){
		$number_hex.=str_repeat('00',$length);
	}

	$asset_str=$str_arr[1];
	$asset_hex=bin2hex($asset_str);
	if(strlen($asset_hex)%2!=0){
		$asset_hex='0'.$asset_hex;
	}
	$length=7-(strlen($asset_hex)/2);
	if($length>0){
		$asset_hex.=str_repeat('00',$length);
	}

	$result=$number_hex.$precision_hex.$asset_hex;
	return $result;
}
function raw_int($int,$bytes=1){
	$hex='';
	if($int){
		$hex=dechex($int);
		if(strlen($hex)%2!=0){
			$hex='0'.$hex;
		}
		$hex=bin2hex(strrev(hex2bin($hex)));
	}
	$length=$bytes-(strlen($hex)/2);
	if($length>0){
		$hex.=str_repeat('00',$length);
	}
	return $hex;
}
function build_create_invite_tx($wif,$creator,$balance,$invite_key,$debug=false){
	global $api;
	$chain_id='2040effda178d4fffff5eab7a915d4019879f5205cc5392e4bcced2b6edda0cd';
	$nonce=0;
	$canonical_signature=false;

	$key=new viz_keys();
	$key->import_wif($wif);
	$pub_key=new viz_keys();
	$pub_key->import_wif($wif);
	$pub_key->to_public();

	$dgp=$api->execute_method('get_dynamic_global_properties');
	if(!$dgp['head_block_number']){
		return false;
	}
	$tapos_block_num=$dgp['head_block_number'] - 5;
	$ref_block_num=($tapos_block_num) & 0xFFFF;
	$ref_block_num_hex=dechex($ref_block_num);
	if(strlen($ref_block_num_hex)%2!=0){
		$ref_block_num_hex='0'.$ref_block_num_hex;
	}
	$ref_block_num_bin=bin2hex(strrev(hex2bin($ref_block_num_hex)));
	$tapos_block=$tapos_block_num+1;
	$tapos_block_info=false;
	$api_count=0;
	while(!$tapos_block_info){
		$tapos_block_info=$api->execute_method('get_block_header',array($tapos_block));
		if(!$tapos_block_info){
			$api_count++;
			if($api_count>5){
				return false;
			}
			usleep(100);
		}
	}
	if(!isset($tapos_block_info['previous'])){
		return false;
	}
	$ref_block_prefix_bin=bin2hex(strrev(substr(hex2bin($tapos_block_info['previous']),4,4)));
	$ref_block_prefix=hexdec($ref_block_prefix_bin);
	$ref_block_prefix_bin_nice=bin2hex(strrev(hex2bin($ref_block_prefix_bin)));//!!!
	$raw_tx='01';//op count
	$raw_tx.='2b';//43=create_invite
	$raw_tx.=raw_string($creator);
	$raw_tx.=raw_asset($balance);
	$raw_tx.=raw_pub_key($invite_key);
	$tx_extension='00';
	while(!$canonical_signature){
		$expiration_time=time()+600+$nonce;//+10min+nonce
		$expiration=date('Y-m-d\TH:i:s',$expiration_time);
		$expiration_bin=bin2hex(strrev(hex2bin(dechex($expiration_time))));

		$raw_block=$ref_block_num_bin.$ref_block_prefix_bin_nice.$expiration_bin;
		$raw_data=$chain_id.$raw_block.$raw_tx.$tx_extension;

		$raw_data_bin=hex2bin($raw_data);
		$data_signature=$key->sign($raw_data_bin);
		$canonical_signature=ec_check_der($data_signature);
		if($canonical_signature){
			$data_signature_compact=$key->sign_recoverable_compact($raw_data_bin);
			if(!$data_signature_compact){
				$canonical_signature=false;
			}
		}
		else{
			$nonce++;
		}
	}
	if($debug){
		print $raw_data.PHP_EOL;
	}
	$json='{"ref_block_num":'.$ref_block_num.',"ref_block_prefix":'.$ref_block_prefix.',"expiration":"'.$expiration.'","operations":[["create_invite",{"creator":"'.$creator.'","balance":"'.$balance.'","invite_key":"'.$invite_key.'"}]],"extensions":[],"signatures":["'.$data_signature_compact.'"]}';
	return $json;
}
function build_delegate_vesting_shares_tx($wif,$delegator,$delegatee,$vesting_shares,$debug=false){
	global $api;
	$chain_id='2040effda178d4fffff5eab7a915d4019879f5205cc5392e4bcced2b6edda0cd';
	$nonce=0;
	$canonical_signature=false;

	$key=new viz_keys();
	$key->import_wif($wif);
	$pub_key=new viz_keys();
	$pub_key->import_wif($wif);
	$pub_key->to_public();

	$dgp=$api->execute_method('get_dynamic_global_properties');
	if(!$dgp['head_block_number']){
		return false;
	}
	$tapos_block_num=$dgp['head_block_number'] - 5;
	$ref_block_num=($tapos_block_num) & 0xFFFF;
	$ref_block_num_hex=dechex($ref_block_num);
	if(strlen($ref_block_num_hex)%2!=0){
		$ref_block_num_hex='0'.$ref_block_num_hex;
	}
	$ref_block_num_bin=bin2hex(strrev(hex2bin($ref_block_num_hex)));
	$tapos_block=$tapos_block_num+1;
	$tapos_block_info=false;
	$api_count=0;
	while(!$tapos_block_info){
		$tapos_block_info=$api->execute_method('get_block_header',array($tapos_block));
		if(!$tapos_block_info){
			$api_count++;
			if($api_count>5){
				return false;
			}
			usleep(100);
		}
	}
	if(!isset($tapos_block_info['previous'])){
		return false;
	}
	$ref_block_prefix_bin=bin2hex(strrev(substr(hex2bin($tapos_block_info['previous']),4,4)));
	$ref_block_prefix=hexdec($ref_block_prefix_bin);
	$ref_block_prefix_bin_nice=bin2hex(strrev(hex2bin($ref_block_prefix_bin)));//!!!
	$raw_tx='01';//op count
	$raw_tx.='13';//19=delegate_vesting_shares
	$raw_tx.=raw_string($delegator);
	$raw_tx.=raw_string($delegatee);
	$raw_tx.=raw_asset($vesting_shares);
	$tx_extension='00';
	while(!$canonical_signature){
		$expiration_time=time()+600+$nonce;//+10min+nonce
		$expiration=date('Y-m-d\TH:i:s',$expiration_time);
		$expiration_bin=bin2hex(strrev(hex2bin(dechex($expiration_time))));

		$raw_block=$ref_block_num_bin.$ref_block_prefix_bin_nice.$expiration_bin;
		$raw_data=$chain_id.$raw_block.$raw_tx.$tx_extension;

		$raw_data_bin=hex2bin($raw_data);
		$data_signature=$key->sign($raw_data_bin);
		$canonical_signature=ec_check_der($data_signature);
		if($canonical_signature){
			$data_signature_compact=$key->sign_recoverable_compact($raw_data_bin);
			if(!$data_signature_compact){
				$canonical_signature=false;
			}
		}
		else{
			$nonce++;
		}
	}
	if($debug){
		print $raw_data.PHP_EOL;
	}
	$json='{"ref_block_num":'.$ref_block_num.',"ref_block_prefix":'.$ref_block_prefix.',"expiration":"'.$expiration.'","operations":[["delegate_vesting_shares",{"delegator":"'.$delegator.'","delegatee":"'.$delegatee.'","vesting_shares":"'.$vesting_shares.'"}]],"extensions":[],"signatures":["'.$data_signature_compact.'"]}';
	return $json;
}
function build_account_create_tx($wif,$fee,$delegation,$creator,$new_account_name,$master,$active,$regular,$memo_key,$json_metadata,$referrer,$debug=false){
	//users[current_user].active_key,fixed_token_amount,fixed_shares_amount,current_user,account_login,master,active,regular,memo_key,json_metadata, referrer,[]
	//(fee)(delegation)(creator)(new_account_name)(master)(active)(regular)(memo_key)(json_metadata)(referrer)(extensions)
	global $api;
	$chain_id='2040effda178d4fffff5eab7a915d4019879f5205cc5392e4bcced2b6edda0cd';
	$nonce=0;
	$canonical_signature=false;

	$key=new viz_keys();
	$key->import_wif($wif);
	$pub_key=new viz_keys();
	$pub_key->import_wif($wif);
	$pub_key->to_public();

	$dgp=$api->execute_method('get_dynamic_global_properties');
	if(!$dgp['head_block_number']){
		return false;
	}
	$tapos_block_num=$dgp['head_block_number'] - 5;
	$ref_block_num=($tapos_block_num) & 0xFFFF;
	$ref_block_num_hex=dechex($ref_block_num);
	if(strlen($ref_block_num_hex)%2!=0){
		$ref_block_num_hex='0'.$ref_block_num_hex;
	}
	$ref_block_num_bin=bin2hex(strrev(hex2bin($ref_block_num_hex)));
	$tapos_block=$tapos_block_num+1;
	$tapos_block_info=false;
	$api_count=0;
	while(!$tapos_block_info){
		$tapos_block_info=$api->execute_method('get_block_header',array($tapos_block));
		if(!$tapos_block_info){
			$api_count++;
			if($api_count>5){
				return false;
			}
			usleep(100);
		}
	}
	if(!isset($tapos_block_info['previous'])){
		return false;
	}
	$ref_block_prefix_bin=bin2hex(strrev(substr(hex2bin($tapos_block_info['previous']),4,4)));
	$ref_block_prefix=hexdec($ref_block_prefix_bin);
	$ref_block_prefix_bin_nice=bin2hex(strrev(hex2bin($ref_block_prefix_bin)));//!!!
	$raw_tx='01';//op count
	$raw_tx.='14';//20=account_create

	$raw_tx.=raw_asset($fee);
	$raw_tx.=raw_asset($delegation);

	$raw_tx.=raw_string($creator);
	$raw_tx.=raw_string($new_account_name);

	$raw_tx.='01000000';//weight_threshold=01000000(uint32)
	$raw_tx.='00';//account_auths=[]
	$raw_tx.='01';//key_auths=[[ количество записей в массиве
	$raw_tx.=raw_pub_key($master);
	$raw_tx.='0100';//,1]] = uint16_t weight_type (2 байта)

	$raw_tx.='01000000';//weight_threshold=01000000(uint32)
	$raw_tx.='00';//account_auths=[]
	$raw_tx.='01';//key_auths=[[ количество записей в массиве
	$raw_tx.=raw_pub_key($active);
	$raw_tx.='0100';//,1]] = uint16_t weight_type (2 байта)

	$raw_tx.='01000000';//weight_threshold=01000000(uint32)
	$raw_tx.='00';//account_auths=[]
	$raw_tx.='01';//key_auths=[[ количество записей в массиве
	$raw_tx.=raw_pub_key($regular);
	$raw_tx.='0100';//,1]] = uint16_t weight_type (2 байта)

	$raw_tx.=raw_pub_key($memo_key);

	$raw_tx.=raw_string($json_metadata);
	$raw_tx.=raw_string($referrer);//op referrer
	$raw_tx.='00';//op extensions

	$tx_extension='00';
	while(!$canonical_signature){
		$expiration_time=time()+600+$nonce;//+10min+nonce
		$expiration=date('Y-m-d\TH:i:s',$expiration_time);
		$expiration_bin=bin2hex(strrev(hex2bin(dechex($expiration_time))));

		$raw_block=$ref_block_num_bin.$ref_block_prefix_bin_nice.$expiration_bin;
		$raw_data=$chain_id.$raw_block.$raw_tx.$tx_extension;

		$raw_data_bin=hex2bin($raw_data);
		$data_signature=$key->sign($raw_data_bin);
		$canonical_signature=ec_check_der($data_signature);
		if($canonical_signature){
			$data_signature_compact=$key->sign_recoverable_compact($raw_data_bin);
			if(!$data_signature_compact){
				$canonical_signature=false;
			}
		}
		else{
			$nonce++;
		}
	}
	if($debug){
		print $raw_data.PHP_EOL;
	}
	//(fee)(delegation)(creator)(new_account_name)(master)(active)(regular)(memo_key)(json_metadata)(referrer)(extensions)
	$json='{"ref_block_num":'.$ref_block_num.',"ref_block_prefix":'.$ref_block_prefix.',"expiration":"'.$expiration.'","operations":[["account_create",{"fee":"'.$fee.'","delegation":"'.$delegation.'","creator":"'.$creator.'","new_account_name":"'.$new_account_name.'","master":{"weight_threshold":1,"account_auths":[],"key_auths":[["'.$master.'",1]]},"active":{"weight_threshold":1,"account_auths":[],"key_auths":[["'.$active.'",1]]},"regular":{"weight_threshold":1,"account_auths":[],"key_auths":[["'.$regular.'",1]]},"memo_key":"'.$memo_key.'","json_metadata":"'.$json_metadata.'","referrer":"'.$referrer.'","extensions":[]}]],"extensions":[],"signatures":["'.$data_signature_compact.'"]}';
	return $json;
}
function build_transfer_to_vesting_tx($wif,$from,$to,$amount,$debug=false){
	global $api;
	$chain_id='2040effda178d4fffff5eab7a915d4019879f5205cc5392e4bcced2b6edda0cd';
	$nonce=0;
	$canonical_signature=false;

	$key=new viz_keys();
	$key->import_wif($wif);
	$pub_key=new viz_keys();
	$pub_key->import_wif($wif);
	$pub_key->to_public();

	$dgp=$api->execute_method('get_dynamic_global_properties');
	if(!$dgp['head_block_number']){
		return false;
	}
	$tapos_block_num=$dgp['head_block_number'] - 5;
	$ref_block_num=($tapos_block_num) & 0xFFFF;
	$ref_block_num_hex=dechex($ref_block_num);
	if(strlen($ref_block_num_hex)%2!=0){
		$ref_block_num_hex='0'.$ref_block_num_hex;
	}
	$ref_block_num_bin=bin2hex(strrev(hex2bin($ref_block_num_hex)));
	$tapos_block=$tapos_block_num+1;
	$tapos_block_info=false;
	$api_count=0;
	while(!$tapos_block_info){
		$tapos_block_info=$api->execute_method('get_block_header',array($tapos_block));
		if(!$tapos_block_info){
			$api_count++;
			if($api_count>5){
				return false;
			}
			usleep(100);
		}
	}
	if(!isset($tapos_block_info['previous'])){
		return false;
	}
	$ref_block_prefix_bin=bin2hex(strrev(substr(hex2bin($tapos_block_info['previous']),4,4)));
	$ref_block_prefix=hexdec($ref_block_prefix_bin);
	$ref_block_prefix_bin_nice=bin2hex(strrev(hex2bin($ref_block_prefix_bin)));//!!!
	$raw_tx='01';//op count
	$raw_tx.='03';//03=transfer_to_vesting
	$raw_tx.=raw_string($from);
	$raw_tx.=raw_string($to);
	$raw_tx.=raw_asset($amount);
	$tx_extension='00';
	while(!$canonical_signature){
		$expiration_time=time()+600+$nonce;//+10min+nonce
		$expiration=date('Y-m-d\TH:i:s',$expiration_time);
		$expiration_bin=bin2hex(strrev(hex2bin(dechex($expiration_time))));

		$raw_block=$ref_block_num_bin.$ref_block_prefix_bin_nice.$expiration_bin;
		$raw_data=$chain_id.$raw_block.$raw_tx.$tx_extension;

		$raw_data_bin=hex2bin($raw_data);
		$data_signature=$key->sign($raw_data_bin);
		$canonical_signature=ec_check_der($data_signature);
		if($canonical_signature){
			$data_signature_compact=$key->sign_recoverable_compact($raw_data_bin);
			if(!$data_signature_compact){
				$canonical_signature=false;
			}
		}
		else{
			$nonce++;
		}
	}
	if($debug){
		print $raw_data.PHP_EOL;
	}
	$json='{"ref_block_num":'.$ref_block_num.',"ref_block_prefix":'.$ref_block_prefix.',"expiration":"'.$expiration.'","operations":[["transfer_to_vesting",{"from":"'.$from.'","to":"'.$to.'","amount":"'.$amount.'"}]],"extensions":[],"signatures":["'.$data_signature_compact.'"]}';
	return $json;
}
function build_transfer_tx($wif,$from,$to,$amount,$memo,$debug=false){
	global $api;
	$chain_id='2040effda178d4fffff5eab7a915d4019879f5205cc5392e4bcced2b6edda0cd';
	$nonce=0;
	$canonical_signature=false;

	$key=new viz_keys();
	$key->import_wif($wif);
	$pub_key=new viz_keys();
	$pub_key->import_wif($wif);
	$pub_key->to_public();

	$dgp=$api->execute_method('get_dynamic_global_properties');
	if(!$dgp['head_block_number']){
		return false;
	}
	$tapos_block_num=$dgp['head_block_number'] - 5;
	$ref_block_num=($tapos_block_num) & 0xFFFF;
	$ref_block_num_hex=dechex($ref_block_num);
	if(strlen($ref_block_num_hex)%2!=0){
		$ref_block_num_hex='0'.$ref_block_num_hex;
	}
	$ref_block_num_bin=bin2hex(strrev(hex2bin($ref_block_num_hex)));
	$tapos_block=$tapos_block_num+1;
	$tapos_block_info=false;
	$api_count=0;
	while(!$tapos_block_info){
		$tapos_block_info=$api->execute_method('get_block_header',array($tapos_block));
		if(!$tapos_block_info){
			$api_count++;
			if($api_count>5){
				return false;
			}
			usleep(100);
		}
	}
	if(!isset($tapos_block_info['previous'])){
		return false;
	}
	$ref_block_prefix_bin=bin2hex(strrev(substr(hex2bin($tapos_block_info['previous']),4,4)));
	$ref_block_prefix=hexdec($ref_block_prefix_bin);
	$ref_block_prefix_bin_nice=bin2hex(strrev(hex2bin($ref_block_prefix_bin)));//!!!
	$raw_tx='01';//op count
	$raw_tx.='02';//02=transfer
	$raw_tx.=raw_string($from);
	$raw_tx.=raw_string($to);
	$raw_tx.=raw_asset($amount);
	$raw_tx.=raw_string($memo);
	$tx_extension='00';
	while(!$canonical_signature){
		$expiration_time=time()+600+$nonce;//+10min+nonce
		$expiration=date('Y-m-d\TH:i:s',$expiration_time);
		$expiration_bin=bin2hex(strrev(hex2bin(dechex($expiration_time))));

		$raw_block=$ref_block_num_bin.$ref_block_prefix_bin_nice.$expiration_bin;
		$raw_data=$chain_id.$raw_block.$raw_tx.$tx_extension;

		$raw_data_bin=hex2bin($raw_data);
		$data_signature=$key->sign($raw_data_bin);
		$canonical_signature=ec_check_der($data_signature);
		if($canonical_signature){
			$data_signature_compact=$key->sign_recoverable_compact($raw_data_bin);
			if(!$data_signature_compact){
				$canonical_signature=false;
			}
		}
		else{
			$nonce++;
		}
	}
	if($debug){
		print $raw_data.PHP_EOL;
	}
	$json='{"ref_block_num":'.$ref_block_num.',"ref_block_prefix":'.$ref_block_prefix.',"expiration":"'.$expiration.'","operations":[["transfer",{"from":"'.$from.'","to":"'.$to.'","amount":"'.$amount.'","memo":"'.$memo.'"}]],"extensions":[],"signatures":["'.$data_signature_compact.'"]}';
	return $json;
}

function parse_ops_type($type){
	global $db;
	$type_id=$db->select_one('ops_type','id',"WHERE `name`='".$db->prepare($type)."'");
	if(!$type_id){
		$sql="INSERT INTO `ops_type` (`name`,`count`,`d_count`,`w_count`,`m_count`) VALUES ('".$db->prepare($type)."','0','0','0','0')";
		$db->sql($sql);
		$type_id=$db->last_id();
	}
	return $type_id;
}

function update_witness($id,$account_login){
	global $db,$api;
	if($account_login){
		$user_arr=$api->execute_method('get_witness_by_account',array($account_login),false);
		if(isset($user_arr['owner'])){
			if($user_arr['owner']==$account_login){
				$sql="UPDATE `witnesses`
					SET
					`votes`='".(int)$user_arr['votes']."',
					`penalty_percent`='".(int)$user_arr['penalty_percent']."',
					`total_missed`='".(int)$user_arr['total_missed']."',
					`url`='".$db->prepare($user_arr['url'])."',
					`props`='".$db->prepare(json_encode($user_arr['props']))."',
					`signing_key`='".$db->prepare($user_arr['signing_key'])."',
					`running_version`='".$db->prepare($user_arr['running_version'])."',
					`hardfork_version_vote`='".$db->prepare($user_arr['hardfork_version_vote'])."',
					`update_time`='".time()."',
					`update`='0'
					WHERE `id`='".$id."'";
				$db->sql($sql);
			}
			return true;
		}
	}
	return false;
}
function parse_witness($account){
	global $db,$api;
	if(!$account){
		return 0;
	}
	$account_id=$db->select_one('accounts','id',"WHERE `name`='".$db->prepare($account)."'");
	if(!$account_id){
		$users_arr=$api->execute_method('get_accounts',array(array($account)),false);
		if(isset($users_arr[0]['id'])){
			$account_id=$users_arr[0]['id'];
		}
	}
	$witness_id=$db->select_one('witnesses','id',"WHERE `account`='".$account_id."'");
	if(false!==$witness_id){
		return $witness_id;
	}
	$user_arr=$api->execute_method('get_witness_by_account',array($account),false);
	if(isset($user_arr['owner'])){
		if($user_arr['owner']==$account){
			$date=date_parse_from_format('Y-m-d\TH:i:s',$user_arr['created']);
			$created=mktime($date['hour'],$date['minute'],$date['second'],$date['month'],$date['day'],$date['year']);
			$sql="INSERT INTO `witnesses`
				(`id`,`account`,
				`blocks`,`rewards`,`votes`,
				`penalty_percent`,`total_missed`,
				`url`,`props`,`signing_key`,
				`running_version`,`hardfork_version_vote`,
				`created`,
				`update_time`,`update`)
				VALUES
				('".(int)$user_arr['id']."','".(int)$account_id."',
				0,0,'".(int)$user_arr['votes']."',
				'".(int)$user_arr['penalty_percent']."','".(int)$user_arr['total_missed']."',
				'".$db->prepare($user_arr['url'])."','".$db->prepare(json_encode($user_arr['props']))."','".$db->prepare($user_arr['signing_key'])."',
				'".$db->prepare($user_arr['running_version'])."','".$db->prepare($user_arr['hardfork_version_vote'])."',
				'".$created."',
				'".time()."',0)";
			$db->sql($sql);
			return $user_arr['id'];
		}
	}
	return 0;
}

function update_account($id,$account_login){
	global $db,$api;
	if($account_login){
		$users_arr=$api->execute_method('get_accounts',array(array($account_login)),false);
		if(isset($users_arr[0]['name'])){
			$user_arr=$users_arr[0];
			if($user_arr['name']==$account_login){
				$effective=((int)(floatval($user_arr['vesting_shares'])*1000000)+(int)(floatval($user_arr['received_vesting_shares'])*1000000))-(int)(floatval($user_arr['delegated_vesting_shares'])*1000000);
				$date=date_parse_from_format('Y-m-d\TH:i:s',$user_arr['last_vote_time']);
				$last_vote_time=mktime($date['hour'],$date['minute'],$date['second'],$date['month'],$date['day'],$date['year']);
				$sql="UPDATE `accounts` SET
					`json`='".$db->prepare($user_arr['json_metadata'])."',
					`custom_sequence`='".(int)$user_arr['custom_sequence']."',
					`custom_sequence_block`='".$user_arr['custom_sequence_block_num']."',
					`balance`='".(int)(floatval($user_arr['balance'])*1000)."',
					`shares`='".(int)(floatval($user_arr['vesting_shares'])*1000000)."',
					`delegated`='".(int)(floatval($user_arr['delegated_vesting_shares'])*1000000)."',
					`received`='".(int)(floatval($user_arr['received_vesting_shares'])*1000000)."',
					`effective`='".$effective."',
					`to_withdraw`='".(int)$user_arr['to_withdraw']."',
					`withdrawn`='".(int)$user_arr['withdrawn']."',
					`withdraw_rate`='".(int)(floatval($user_arr['vesting_withdraw_rate'])*1000000)."',
					`receiver_awards`='".(int)($user_arr['receiver_awards'])."',
					`benefactor_awards`='".(int)($user_arr['benefactor_awards'])."',
					`energy`='".(int)($user_arr['energy'])."',
					`last_vote_time`='".$last_vote_time."',
					`witnesses_voted_for`='".(int)$user_arr['witnesses_voted_for']."',
					`witnesses_vote_weight`='".(int)$user_arr['witnesses_vote_weight']."',
					`update_time`=".time().",
					`update`=0
					WHERE `id`='".(int)$id."'";
				$db->sql($sql);
				$db->sql("DELETE FROM `witnesses_votes` WHERE `account`='".(int)$id."'");
				foreach($user_arr['witness_votes'] as $witness){
					$witness_id=parse_witness($witness);
					if($witness_id){
						$db->sql("INSERT INTO `witnesses_votes` (`account`,`witness`,`votes`) VALUES ('".(int)$id."','".(int)$witness_id."','".(int)$user_arr['witnesses_vote_weight']."')");
					}
				}

				$db->sql("DELETE FROM `accounts_keys` WHERE `account`='".(int)$user_arr['id']."'");
				foreach($user_arr['master_authority']['key_auths'] as $key){
					$db->sql("INSERT INTO `accounts_keys` (`account`,`type`,`key`,`weight`,`weight_threshold`) VALUES ('".(int)$user_arr['id']."','1','".$key[0]."','".$key[1]."','".$user_arr['master_authority']['weight_threshold']."')");
				}
				foreach($user_arr['active_authority']['key_auths'] as $key){
					$db->sql("INSERT INTO `accounts_keys` (`account`,`type`,`key`,`weight`,`weight_threshold`) VALUES ('".(int)$user_arr['id']."','2','".$key[0]."','".$key[1]."','".$user_arr['master_authority']['weight_threshold']."')");
				}
				foreach($user_arr['regular_authority']['key_auths'] as $key){
					$db->sql("INSERT INTO `accounts_keys` (`account`,`type`,`key`,`weight`,`weight_threshold`) VALUES ('".(int)$user_arr['id']."','3','".$key[0]."','".$key[1]."','".$user_arr['master_authority']['weight_threshold']."')");
				}
				$db->sql("INSERT INTO `accounts_keys` (`account`,`type`,`key`,`weight`,`weight_threshold`) VALUES ('".(int)$user_arr['id']."','4','".$user_arr['memo_key']."',0,0)");

				$db->sql("DELETE FROM `accounts_authority` WHERE `account`='".(int)$user_arr['id']."'");
				foreach($user_arr['master_authority']['account_auths'] as $agent){
					$agent_id=parse_account($agent[0]);
					$db->sql("INSERT INTO `accounts_authority` (`account`,`type`,`agent`,`weight`,`weight_threshold`) VALUES ('".(int)$user_arr['id']."','1','".$agent_id."','".$agent[1]."','".$user_arr['master_authority']['weight_threshold']."')");
				}
				foreach($user_arr['active_authority']['account_auths'] as $agent){
					$agent_id=parse_account($agent[0]);
					$db->sql("INSERT INTO `accounts_authority` (`account`,`type`,`agent`,`weight`,`weight_threshold`) VALUES ('".(int)$user_arr['id']."','2','".$agent_id."','".$agent[1]."','".$user_arr['master_authority']['weight_threshold']."')");
				}
				foreach($user_arr['regular_authority']['account_auths'] as $agent){
					$agent_id=parse_account($agent[0]);
					$db->sql("INSERT INTO `accounts_authority` (`account`,`type`,`agent`,`weight`,`weight_threshold`) VALUES ('".(int)$user_arr['id']."','3','".$agent_id."','".$agent[1]."','".$user_arr['master_authority']['weight_threshold']."')");
				}

				return true;
			}
		}
	}
	return false;
}
function parse_account($account){
	global $db,$api;
	if(!$account){
		return 0;
	}
	$account_id=$db->select_one('accounts','id',"WHERE `name`='".$db->prepare($account)."'");
	if(null!==$account_id){
		return $account_id;
	}
	$users_arr=$api->execute_method('get_accounts',array(array($account)),false);
	if(isset($users_arr[0]['name'])){
		$user_arr=$users_arr[0];
		if($user_arr['name']==$account){
			$date=date_parse_from_format('Y-m-d\TH:i:s',$user_arr['created']);
			$created=mktime($date['hour'],$date['minute'],$date['second'],$date['month'],$date['day'],$date['year']);
			$level=1+substr_count($account,'.');
			$effective=((int)(floatval($user_arr['vesting_shares'])*1000000)+(int)(floatval($user_arr['received_vesting_shares'])*1000000))-(int)(floatval($user_arr['delegated_vesting_shares'])*1000000);
			$date=date_parse_from_format('Y-m-d\TH:i:s',$user_arr['last_vote_time']);
			$last_vote_time=mktime($date['hour'],$date['minute'],$date['second'],$date['month'],$date['day'],$date['year']);
			$sql="INSERT INTO `accounts`
				(`id`,`name`,`level`,
				`json`,`proxy`,`referrer`,
				`custom_sequence`,`custom_sequence_block`,
				`balance`,`shares`,
				`delegated`,`received`,`effective`,
				`to_withdraw`,`withdrawn`,`withdraw_rate`,
				`receiver_awards`,`benefactor_awards`,
				`energy`,`last_vote_time`,
				`witnesses_voted_for`,`witnesses_vote_weight`,
				`created`,
				`update_time`,`update`) VALUES
				('".(int)$user_arr['id']."','".$db->prepare($account)."','".$level."',
				'".$db->prepare($user_arr['json_metadata'])."','".parse_account($user_arr['proxy'])."','".parse_account($user_arr['referrer'])."',
				'".(int)$user_arr['custom_sequence']."','".$user_arr['custom_sequence_block_num']."',
				'".(int)(floatval($user_arr['balance'])*1000)."','".(int)(floatval($user_arr['vesting_shares'])*1000000)."',
				'".(int)(floatval($user_arr['delegated_vesting_shares'])*1000000)."','".(int)(floatval($user_arr['received_vesting_shares'])*1000000)."','".$effective."',
				'".(int)$user_arr['to_withdraw']."','".(int)$user_arr['withdrawn']."','".(int)(floatval($user_arr['vesting_withdraw_rate'])*1000000)."',
				'".(int)($user_arr['receiver_awards'])."','".(int)($user_arr['benefactor_awards'])."',
				'".(int)($user_arr['energy'])."','".$last_vote_time."',
				'".(int)$user_arr['witnesses_voted_for']."','".(int)$user_arr['witnesses_vote_weight']."',
				'".$created."',
				'".time()."',0)";
			$db->sql($sql);
			$db->sql("DELETE FROM `witnesses_votes` WHERE `account`='".(int)$user_arr['id']."'");
			foreach($user_arr['witness_votes'] as $witness){
				$witness_id=parse_witness($witness);
				if($witness_id){
					$db->sql("INSERT INTO `witnesses_votes` (`account`,`witness`,`votes`) VALUES ('".(int)$user_arr['id']."','".(int)$witness_id."','".(int)$user_arr['witnesses_vote_weight']."')");
				}
			}

			$db->sql("DELETE FROM `accounts_keys` WHERE `account`='".(int)$user_arr['id']."'");
			foreach($user_arr['master_authority']['key_auths'] as $key){
				$db->sql("INSERT INTO `accounts_keys` (`account`,`type`,`key`,`weight`,`weight_threshold`) VALUES ('".(int)$user_arr['id']."','1','".$key[0]."','".$key[1]."','".$user_arr['master_authority']['weight_threshold']."')");
			}
			foreach($user_arr['active_authority']['key_auths'] as $key){
				$db->sql("INSERT INTO `accounts_keys` (`account`,`type`,`key`,`weight`,`weight_threshold`) VALUES ('".(int)$user_arr['id']."','2','".$key[0]."','".$key[1]."','".$user_arr['master_authority']['weight_threshold']."')");
			}
			foreach($user_arr['regular_authority']['key_auths'] as $key){
				$db->sql("INSERT INTO `accounts_keys` (`account`,`type`,`key`,`weight`,`weight_threshold`) VALUES ('".(int)$user_arr['id']."','3','".$key[0]."','".$key[1]."','".$user_arr['master_authority']['weight_threshold']."')");
			}
			$db->sql("INSERT INTO `accounts_keys` (`account`,`type`,`key`,`weight`,`weight_threshold`) VALUES ('".(int)$user_arr['id']."','4','".$user_arr['memo_key']."',0,0)");

			$db->sql("DELETE FROM `accounts_authority` WHERE `account`='".(int)$user_arr['id']."'");
			foreach($user_arr['master_authority']['account_auths'] as $agent){
				$agent_id=parse_account($agent[0]);
				$db->sql("INSERT INTO `accounts_authority` (`account`,`type`,`agent`,`weight`,`weight_threshold`) VALUES ('".(int)$user_arr['id']."','1','".$agent_id."','".$agent[1]."','".$user_arr['master_authority']['weight_threshold']."')");
			}
			foreach($user_arr['active_authority']['account_auths'] as $agent){
				$agent_id=parse_account($agent[0]);
				$db->sql("INSERT INTO `accounts_authority` (`account`,`type`,`agent`,`weight`,`weight_threshold`) VALUES ('".(int)$user_arr['id']."','2','".$agent_id."','".$agent[1]."','".$user_arr['master_authority']['weight_threshold']."')");
			}
			foreach($user_arr['regular_authority']['account_auths'] as $agent){
				$agent_id=parse_account($agent[0]);
				$db->sql("INSERT INTO `accounts_authority` (`account`,`type`,`agent`,`weight`,`weight_threshold`) VALUES ('".(int)$user_arr['id']."','3','".$agent_id."','".$agent[1]."','".$user_arr['master_authority']['weight_threshold']."')");
			}

			return (int)$user_arr['id'];
		}
	}
	return 0;
}
$accounts_names=array();
function get_account_name($id){
	global $db,$accounts_names;
	if(!isset($accounts_names[$id])){
		$accounts_names[$id]=$db->select_one('accounts','name',"WHERE `id`='".$id."'");
	}
	return $accounts_names[$id];
}
$witness_accounts=array();
function get_witness_name($id){
	global $db,$witness_accounts;
	if(!isset($witness_accounts[$id])){
		$witness_accounts[$id]=$db->select_one('witnesses','account',"WHERE `id`='".$id."'");
	}
	return get_account_name($witness_accounts[$id]);
}

function short_viz($str,$symbol=true){
	return number_format(floatval($str),2,'.','&nbsp;').($symbol?' viz':'');
}

function hash_view($hash,$full=false){
	if(''==$hash){
		return '&mdash;';
	}
	$hash_len=strlen($hash);
	$hash_parts=floor($hash_len/6);
	$hash_parts_len=$hash_parts*6;
	$hash_before=substr($hash,0,($hash_len-$hash_parts_len)/2);
	$hash_middle=substr($hash,($hash_len-$hash_parts_len)/2,$hash_parts_len);
	$hash_after=substr($hash,($hash_len-$hash_parts_len)/2+$hash_parts_len);
	$hash_arr=str_split($hash_middle,6);
	$hash_str='<span class="full">'.$hash_before.'</span>';
	foreach($hash_arr as $hash_part){
		$hash_str.='<span'.($full?' class="full"':'').' style="background:#'.$hash_part.';">'.$hash_part.'</span>';
	}
	$hash_str.='<span class="full">'.$hash_after.'</span>';
	return '<span class="view-hash captions">'.$hash_str.'</span>';
}

function ltmp($ltmp_str,$ltmp_args=array()){
	foreach($ltmp_args as $k=>$v){
		$ltmp_str=str_replace('%'.$k.'%',$v,$ltmp_str);
	}
	return $ltmp_str;
}
