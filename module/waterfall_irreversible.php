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

$pid_file=$site_root.'/module/waterfall_irreversible.pid';
$pid=false;
if(file_exists($pid_file)){
	$pid=file_get_contents($pid_file);
}
$new_pid=posix_getpid();
if($pid){
	$working=posix_getpgid($pid);
	if($working){
		print 'VIZ Waterfall-irreversible already working with PID: '.$pid.PHP_EOL;
		exit;
	}
	else{
		unlink($pid_file);
		print 'VIZ Waterfall-irreversible stopped, restarting... with PID: '.$new_pid.PHP_EOL;
	}
}
file_put_contents($pid_file,$new_pid);
print 'STARTUP: pid file: '.$pid_file.', pid: '.$new_pid.PHP_EOL;

$api=new viz_jsonrpc_web($api_arr[array_rand($api_arr)]);

$block_id=(int)$db->select_one('blocks','id',"ORDER BY `id` DESC");
if(!$block_id){
	$block_id=1;
}
else{
	$block_id++;
}
print 'STARTUP: Start from block #'.$block_id.', working with endpoint: '.$api->endpoint.'...'.PHP_EOL;

////////////////////////////////////////////// + IGNORE SYSTEM
$ignore_list=file($site_root.'/ignore_list.txt',FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
$custom_spam_list=[];
print 'Ignore list loaded: '.count($ignore_list).' accounts'.PHP_EOL;
$find_json_initiator_by_type=[
	1=>'witness',
	2=>'owner',
	3=>'account',
	4=>'account',
	5=>'creator',
	6=>'owner',
	7=>'from',
	8=>'delegator',
	9=>'author',
	10=>'voter',
	11=>'owner',
	12=>'account',
	13=>'CUSTOM',//required_active_auths,required_regular_auths
	14=>'creator',
	15=>'curator',
	16=>'author',
	17=>'author',
	18=>'author',
	19=>'from_account',
	20=>'benefactor',
	21=>'from',
	22=>'voter',
	23=>'creator',
	24=>'creator',
	26=>'initiator',
	28=>'account',
	29=>'from_account',
	30=>'initiator',
	31=>'account',
	33=>'worker',
	35=>'author',
	36=>'account',
	37=>'initiator',
	38=>'initiator',
	39=>'owner',
	40=>'initiator',
	41=>'account',
	42=>'subscriber',
	43=>'subscriber',
	44=>'subscriber',
	45=>'account',
	46=>'account',
	47=>'buyer',
	48=>'buyer',
	49=>'from',
	50=>'account_to_recover',
	51=>'author',
	52=>'author',
	53=>'who',
	54=>'who',
	55=>'who',
];
////////////////////////////////////////////// - IGNORE SYSTEM

$work=true;
$work_time=time();
$current_block=$block_id;
$dgp=$api->execute_method('get_dynamic_global_properties');
print_r($dgp);
$last_block=$dgp['last_irreversible_block_num'];
$sleep=0;
while($work){
	$current_block_time=0;
	for(;$current_block<=$last_block;$current_block++){
		$attempts=1;
		$success=false;
		$current_block_time=0;
		while(!$success){
			$current_block_time=microtime(true);
			$block_data=false;
			$ops_data=false;

			//print 'Trying get_ops_in_block with '.($api->endpoint).' on #'.$current_block.' (used '.(round(memory_get_usage(true)/1024,2).'kb').')'.PHP_EOL;
			$block_data=$api->execute_method('get_block',array($current_block),false);
			//print_r($block_data);
			//print 'DEBUG PROCCESS #'.$current_block.' ('.(int)(1000*(microtime(true)-$current_block_time)).'ms execute time) for get_block'.PHP_EOL;
			$ops_data=$api->execute_method('get_ops_in_block',array($current_block,0),false);
			//print 'DEBUG PROCCESS #'.$current_block.' ('.(int)(1000*(microtime(true)-$current_block_time)).'ms execute time) for get_ops_in_block'.PHP_EOL;
			//print_r($ops_data);

			if(false!==$block_data){
				if(false!==$ops_data){
					$success=true;
				}
			}
			if($success){
				$witness_id=parse_witness($block_data['witness']);
				//print 'DEBUG PROCCESS #'.$current_block.' ('.(int)(1000*(microtime(true)-$current_block_time)).'ms execute time) for parse_witness'.PHP_EOL;
				$date=date_parse_from_format('Y-m-d\TH:i:s',$block_data['timestamp']);
				$block_time=mktime($date['hour'],$date['minute'],$date['second'],$date['month'],$date['day'],$date['year']);
				$prev_hash=$block_data['previous'];

				$custom_spam_list=[];
				$trx=0;
				$ops=0;
				$vops=0;
				$trx_hash=array('0000000000000000000000000000000000000000'=>true);//empty for virtuals ops executed by blockchain main loop
				$trx_hash_ops=array('0000000000000000000000000000000000000000'=>0);
				$trx_hash_vops=array('0000000000000000000000000000000000000000'=>0);
				$trx_hash_ops_data=array();
				//print 'DEBUG PROCCESS #'.$current_block.' ('.(int)(1000*(microtime(true)-$current_block_time)).'ms execute time) for reset vars'.PHP_EOL;
				foreach($ops_data as $k=>$v){
					$hash=$v['trx_id'];
					$virt=false;
					if(!isset($trx_hash[$hash])){
						$trx_hash[$hash]=true;
						$trx_hash_ops_data[$hash]=array();
						$trx_hash_ops[$hash]=0;
						$trx_hash_vops[$hash]=0;
						$trx++;
					}
					$num=$v['trx_in_block'];
					$date=date_parse_from_format('Y-m-d\TH:i:s',$v['timestamp']);
					$ops_time=mktime($date['hour'],$date['minute'],$date['second'],$date['month'],$date['day'],$date['year']);
					if(0!=$v['virtual_op']){
						$virt=true;
						$trx_hash_vops[$hash]++;
						$vops++;
					}
					else{
						$trx_hash_ops[$hash]++;
						$ops++;
					}
					$data=array('time'=>$ops_time,'type'=>parse_ops_type($v['op'][0]),'json'=>json_encode($v['op'][1]),'v'=>$virt);
					$trx_hash_ops_data[$hash][]=$data;
				}
				//print 'DEBUG PROCCESS #'.$current_block.' ('.(int)(1000*(microtime(true)-$current_block_time)).'ms execute time) for calc ops_data'.PHP_EOL;

				$db->sql("UPDATE `blocks` SET `hash`=UNHEX('".$prev_hash."') WHERE `id`='".($current_block-1)."'");
				$db->sql("INSERT INTO `blocks` (`id`,`time`,`witness`,`trx`,`ops`,`vops`) VALUES ('".$current_block."','".$block_time."','".$witness_id."','".$trx."','".$ops."','".$vops."')");
				$trx_num=1;
				foreach($trx_hash as $hash=>$trx_exist){
					$trx_id=0;
					if('0000000000000000000000000000000000000000'!=$hash){
						if(0==$trx_hash_ops[$hash]){
							$hash='0000000000000000000000000000000000000000';
						}
					}
					if('0000000000000000000000000000000000000000'!=$hash){
						$db->sql("INSERT INTO `trx` (`hash`,`block`,`num`,`ops`,`vops`) VALUES (UNHEX('".$hash."'),'".$current_block."','".$trx_num."','".$trx_hash_ops[$hash]."','".$trx_hash_vops[$hash]."')");
						$trx_id=$db->last_id();

						foreach($trx_hash_ops_data[$hash] as $data){
							////////////////////////////////////////////////////// IGNORE SYSTEM TO PREVENT SPAM
							$ignore=false;
							if(isset($find_json_initiator_by_type[$data['type']])){
								$json_check=json_decode($data['json'],true);
								if('CUSTOM'==$find_json_initiator_by_type[$data['type']]){
									print 'check $custom_spam_list < '.$json_check['required_regular_auths'][0].': ';
									if(isset($custom_spam_list[$json_check['required_regular_auths'][0]])){
										print $custom_spam_list[$json_check['required_regular_auths'][0]].PHP_EOL;
									}
									else{
										print 'none'.PHP_EOL;
									}
									if(isset($json_check['required_regular_auths'][0])){
										if(isset($custom_spam_list[$json_check['required_regular_auths'][0]])){
											if($custom_spam_list[$json_check['required_regular_auths'][0]]>10){
												$ignore=true;
											}
										}
									}
									if(!$ignore)
									foreach($json_check['required_active_auths'] as $check_ignore){
										if(!isset($custom_spam_list[$check_ignore])){
											$custom_spam_list[$check_ignore]=1;
										}
										else{
											$custom_spam_list[$check_ignore]++;
										}
										if(in_array($check_ignore,$ignore_list)){
											$ignore=true;
										}
									}
									if(!$ignore)
									foreach($json_check['required_regular_auths'] as $check_ignore){
										if(!isset($custom_spam_list[$check_ignore])){
											$custom_spam_list[$check_ignore]=1;
										}
										else{
											$custom_spam_list[$check_ignore]++;
										}
										if(in_array($check_ignore,$ignore_list)){
											$ignore=true;
										}
									}
								}
								else{
									if(in_array($json_check[$find_json_initiator_by_type[$data['type']]],$ignore_list)){
										$ignore=true;
									}
								}
							}
							/*
							$ignore=false;
							if(isset($ignored_ops[$data['type']])){
								if(isset($ignored_ops[$data['type']][$data['json']])){
									$ignore=true;
								}
							}
							*/
							////////////////////////////////////////////////////// IGNORE SYSTEM TO PREVENT SPAM
							if(!$ignore){
								$db->sql("INSERT INTO `ops` (`type`,`block`,`trx`,`v`,`json`,`time`) VALUES ('".$data['type']."','".$current_block."','".$trx_id."','".($data['v']?'1':'0')."','".$db->prepare($data['json'])."','".$data['time']."')");
							}
						}
						$trx_num++;
					}
					else{
						if(isset($trx_hash_ops_data[$hash]))
						foreach($trx_hash_ops_data[$hash] as $data){
							$db->sql("INSERT INTO `ops` (`type`,`block`,`v`,`json`,`time`) VALUES ('".$data['type']."','".$current_block."','".($data['v']?'1':'0')."','".$db->prepare($data['json'])."','".$data['time']."')");
						}
					}
				}
				//print 'DEBUG PROCCESS #'.$current_block.' ('.(int)(1000*(microtime(true)-$current_block_time)).'ms execute time) for sql round'.PHP_EOL;
			}
			unset($trx_hash);
			unset($trx_hash_ops_data);
			unset($trx_hash_vops);
			unset($trx_hash_ops);
			unset($block_data);
			unset($ops_data);
			//print 'DEBUG PROCCESS #'.$current_block.' ('.(int)(1000*(microtime(true)-$current_block_time)).'ms execute time) for unset vars'.PHP_EOL;
			if(!$success){
				print 'WARNING: Attempt '.$attempts.' on #'.$current_block.PHP_EOL;
				$attempts++;
				if($attempts>100){
					print 'ERROR: Failed get #'.$current_block.' block more that 1000 times with endpoint: '.$api->endpoint.', self-terminating...'.PHP_EOL;
					exit;
				}
				usleep(100000);
			}
			else{
				$end_execute_time=microtime(true);
				print 'SUCCESS block #'.$current_block.' (sleep '.($sleep/1000).'ms) ('.(int)(1000*($end_execute_time-$current_block_time)).'ms execute time) (working '.(time() - $work_time).'s, '.(round((time() - $work_time)/60,2)).'m)'.PHP_EOL;
			}
			if(0==$current_block%5){
				if(!file_exists($pid_file)){
					print 'INFO: PID file was deleted, self-terminating...'.PHP_EOL;
					exit;
				}
			}
		}
	}
	unset($dgp);
	$dgp=$api->execute_method('get_dynamic_global_properties');
	$last_block=$dgp['last_irreversible_block_num'];
	if(($last_block+1)<=$current_block){
		$sleep=(int)((3-(microtime(true)-$current_block_time))*1000000);
		if($sleep>0){
			print 'Going sleep '.$sleep.PHP_EOL;
			usleep($sleep);
		}
		else{
			$sleep=0;
		}
	}
	if(!file_exists($pid_file)){
		print 'INFO: PID file was deleted, self-terminating...'.PHP_EOL;
		exit;
	}
}
exit;