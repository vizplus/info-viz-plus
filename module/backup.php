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

$pid_file=$site_root.'/module/backup.pid';
$pid=false;
if(file_exists($pid_file)){
	$pid=file_get_contents($pid_file);
}
$new_pid=posix_getpid();
if($pid){
	$working=posix_getpgid($pid);
	if($working){
		print 'VIZ backup already working with PID: '.$pid.PHP_EOL;
		exit;
	}
	else{
		unlink($pid_file);
		print 'VIZ backup stopped, restarting... with PID: '.$new_pid.PHP_EOL;
	}
}
file_put_contents($pid_file,$new_pid);
print 'STARTUP: pid file: '.$pid_file.', pid: '.$new_pid.PHP_EOL;

$remove_pids=['waterfall_irreversible','props_snapshot','ops_linking','ops_processing','ops_working','updater'];
foreach($remove_pids as $remove_pid){
	unlink($site_root.'/module/'.$remove_pid.'.pid');
}

$tables=array(
	'accounts',
	'accounts_authority',
	'accounts_keys',
	'accounts_snapshot',
	'blocks',
	'chain_props_snapshot',
	'delegations',
	'dgp_snapshot',
	'escrow',
	'ops',
	'ops_link',
	'ops_type',
	'stats',
	'trx',
	'witnesses',
	'witnesses_snapshot',
	'witnesses_votes',
);
$start_time=microtime(true);
$summary_filesize=0;
foreach($tables as $table){
	exec('rm /backup/'.$table.'.sql.gz*');
	exec('mysqldump -u"'.$config['db_login'].'" -p"'.$config['db_password'].'" '.$config['db_base'].' '.$table.' | gzip -9 > /backup/'.$table.'.sql.gz');
	$current_time=microtime(true);
	$current_work_time=(int)(1000*($current_time-$start_time));
	$filesize=filesize('/backup/'.$table.'.sql.gz');
	if($filesize>50*1024*1024){//split if more 50MB
		exec('split -b50M /backup/'.$table.'.sql.gz /backup/'.$table.'.sql.gz.');
		exec('rm /backup/'.$table.'.sql.gz');
	}
	$summary_filesize+=$filesize;
	print $table.' table OK within '.$current_work_time.'ms ['.round($filesize/1024,2).'Kb]'.PHP_EOL;
}
if(!file_exists($pid_file)){
	$work=false;
	print 'INFO: PID file was deleted, self-terminating...'.PHP_EOL;
	exit;
}
$current_time=microtime(true);
$current_work_time=round($current_time-$start_time,2);
print 'Finished in '.$current_work_time.'s ['.round($summary_filesize/1024/1024,2).'Mb]'.PHP_EOL;
unlink($pid_file);
exit;