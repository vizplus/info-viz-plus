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

$pid_file=$site_root.'/module/updater.pid';
$pid=false;
if(file_exists($pid_file)){
	$pid=file_get_contents($pid_file);
}
$new_pid=posix_getpid();
if($pid){
	$working=posix_getpgid($pid);
	if($working){
		print 'VIZ Updater already working with PID: '.$pid.PHP_EOL;
		exit;
	}
	else{
		unlink($pid_file);
		print 'VIZ Updater stopped, restarting... with PID: '.$new_pid.PHP_EOL;
	}
}
file_put_contents($pid_file,$new_pid);
print 'STARTUP: pid file: '.$pid_file.', pid: '.$new_pid.PHP_EOL;

$api=new viz_jsonrpc_web($api_arr[array_rand($api_arr)]);

$work=true;
$start_time=microtime(true);
$current_work_time=0;
$proceed=0;
$failed=0;
$info_time=time();
while($work){
	$current_start_time=microtime(true);
	$q=$db->sql("SELECT `id`,`account` FROM `witnesses` WHERE `update`=1 LIMIT 10");// ORDER BY `id` ASC
	while($m=$db->row($q)){
		$test=update_witness($m['id'],$db->select_one('accounts','name',"WHERE `id`='".$m['account']."'"));
		$proceed++;
		if(!$test){
			$failed++;
		}
	}

	$q=$db->sql("SELECT `id`,`name` FROM `accounts` WHERE `update`=1 LIMIT 10");// ORDER BY `id` ASC
	while($m=$db->row($q)){
		$test=update_account($m['id'],$m['name']);
		$proceed++;
		if(!$test){
			$failed++;
		}
	}
	$current_end_time=microtime(true);
	$current_work_time+=(int)(1000*($current_end_time - $current_start_time));
	if(time()>(5+$info_time)){
		print 'Proceed '.$proceed.', failed '.$failed.' ('.((int)(1000*(microtime(true) - $start_time))).'ms execute time, avg '.round($current_work_time/($proceed),2).'ms per)'.PHP_EOL;
		$info_time=time();
	}
	usleep(200);
	if(!file_exists($pid_file)){
		$work=false;
	}
}
print 'INFO: PID file was deleted, self-terminating...'.PHP_EOL;
exit;