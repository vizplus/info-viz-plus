<?php
ob_start();
$replace['title']=$ltmp_arr['explorer']['title'].' - '.$replace['title'];
if('search'==$path_array[2]){
	$replace['title']=$ltmp_arr['explorer']['search_title'].' - '.$replace['title'];
	$replace['description']=$ltmp_arr['explorer']['search_description'];
	if(isset($_POST['viz-explorer-query'])){
		$query=$_POST['viz-explorer-query'];
		$query=trim($query," \r\n\t");
		if(40==mb_strlen($query)){
			$query_addon='000000000000000000000000';
			$find_hash=$db->table_count('trx',"WHERE `hash`=UNHEX('".$db->prepare($query.$query_addon)."')");
			if($find_hash){
				header('location:/explorer/tx/'.strtoupper($query).'/');
				exit;
			}
			$find_block=$db->select_one('blocks','id',"WHERE `hash`=UNHEX('".$db->prepare($query)."')");
			if($find_block){
				header('location:/explorer/block/'.$find_block.'/');
				exit;
			}
		}
		else{
			if((int)$query==$query){
				$find_block=$db->table_count('blocks',"WHERE `id`='".$db->prepare((int)$query)."'");
				if($find_block){
					header('location:/explorer/block/'.((int)$query).'/');
					exit;
				}
			}
		}
	}
	print '
	<div class="cards-view">
	<div class="cards-container">
	<div class="card">
	<h2 class="left">'.$ltmp_arr['explorer']['search_caption'].'</h2>
	<form action="/explorer/search/" method="POST" style="margin-bottom:20px;"><input type="text" name="viz-explorer-query" placeholder="'.$ltmp_arr['explorer']['search_placeholder'].'" class="simple-rounded wide explorer-search">
	<input type="submit" style="position: absolute; left: -9999px; width: 1px; height: 1px;" tabindex="-1"></form>
	<p class="grey captions">'.ltmp($ltmp_arr['explorer']['search_none'],['query'=>htmlspecialchars($query)]).'</p>
	</div></div></div>';
}
else
if('tx'==$path_array[2]){
	$replace['title']=$ltmp_arr['explorer']['tx_title'].' - '.$replace['title'];
	$replace['description']=$ltmp_arr['explorer']['tx_description'];
	$hash=false;
	if(isset($path_array[3])){
		$hash=strtoupper($path_array[3]);
		if($hash!=$path_array[3]){
			header('location:/explorer/tx/'.$hash.'/');
			exit;
		}
	}
	if($hash){
		if(40==strlen($hash)){
			$hash.='000000000000000000000000';
		}
		$trx_arr=$db->sql_row("SELECT *, HEX(`hash`) as `hash` FROM `trx` WHERE `hash`=UNHEX('".$hash."')");
		if($trx_arr['id']){
			$trx_arr['hash']=substr($trx_arr['hash'],0,-24);
			$replace['title']=$trx_arr['hash'].' - '.$replace['title'];
			$replace['description']=ltmp($ltmp_arr['explorer']['tx_data'],['hash'=>$trx_arr['hash'],'block'=>$trx_arr['block'],'ops'=>$trx_arr['ops'],'vops'=>$trx_arr['vops']]);
			print '
			<div class="cards-view">
			<div class="cards-container">
			<div class="card">
			<h2 class="left">'.$ltmp_arr['explorer']['tx_caption'].' '.hash_view($trx_arr['hash'],true).'';
			print '</h2>';
			print '<p>'.$ltmp_arr['explorer']['tx_block'].' <a href="/explorer/block/'.$trx_arr['block'].'/" class="captions">'.$trx_arr['block'].'</a></p>';
			$ops_arr=array();
			$q=$db->sql("SELECT `id`,`name` FROM `ops_type`");
			while($m=$db->row($q)){
				$ops_arr[$m['id']]=$m['name'];
			}
			$ops='';
			$q=$db->sql("SELECT * FROM `ops` WHERE `trx`='".$trx_arr['id']."' ORDER BY `id` ASC");
			while($m=$db->row($q)){
				$ops.='
				<tr data-id="'.$m['id'].'">
					<td>'.($m['v']?'<span class="grey" title="'.$ltmp_arr['explorer']['tx_op_virtual'].'">':'').$ops_arr[$m['type']].($m['v']?'</span>':'').'</td>
					<td><div class="view-json" data-type="'.$ops_arr[$m['type']].'">'.htmlspecialchars($m['json']).'</div></td>
				</tr>';
			}
			if($ops){
				print '<hr><h3 class="left">'.$ltmp_arr['explorer']['tx_ops_caption'].'</h3>';
				print '<table class="ops captions sortable-theme-slick" width="100%" data-sortable="false">';
				print '<thead>';
				print '
				<tr>
					<th>'.$ltmp_arr['explorer']['tx_op_type'].'</th>
					<th width="100%">'.$ltmp_arr['explorer']['tx_op_json'].'</th>
				</tr>';
				print '</thead>';
				print '<tbody>';
				print $ops;
				print '</tbody></table>';
			}
			else{
				print '<p>В транзакции присутствует '.$trx_arr['ops'].' операций связанных с спамом.</p>';
			}
			print '</div></div></div>';
		}
	}
	else{
		header('location:/explorer/');
		exit;
		/*
		print '
		<div class="cards-view">
		<div class="cards-container">
		<div class="card">
		<h2 class="left">Транзакции</h2>';
		print '<table class="trx captions sortable-theme-slick" width="100%" data-sortable="false">';
		print '<thead>';
		$th_sorted=' data-sorted="true" data-sorted-direction="descending"';
		print '
		<tr>
			<th'.$th_sorted.'>Блок</th>
			<th>Hash</th>
			<th class="text-right">Операций</th>
			<th class="text-right">Виртуальных операций</th>
		</tr>';
		print '</thead>';
		print '<tbody>';
		$q=$db->sql("SELECT *, HEX(`hash`) as `hash` FROM `trx` ORDER BY `id` DESC LIMIT 100");
		while($m=$db->row($q)){
			$m['hash']=substr($m['hash'],0,-24);
			print '
			<tr data-id="'.$m['id'].'">
					<td><a href="/explorer/block/'.$m['block'].'/">'.$m['block'].'</a></td>
					<td class="nowrap"><a href="/explorer/tx/'.$m['hash'].'/">'.hash_view($m['hash'],true).'</a></td>
					<td class="text-right">'.$m['ops'].'</td>
					<td class="text-right">'.$m['vops'].'</td>
			</tr>';
		}
		print '</tbody></table>';
		print '</div></div></div>';
		*/
	}
}
else
if('block'==$path_array[2]){
	$replace['title']=$ltmp_arr['explorer']['blocks_title'].' - '.$replace['title'];
	$replace['description']=$ltmp_arr['explorer']['blocks_description'];
	$id=false;
	if(isset($path_array[3])){
		$id=(int)$path_array[3];
		if($id!=$path_array[3]){
			header('location:/explorer/block/'.$id.'/');
			exit;
		}
	}
	if($id){
		$block_arr=$db->sql_row("SELECT *, HEX(`hash`) as `hash` FROM `blocks` WHERE `id`='".$id."'");
		if($block_arr['id']){
			$witness=get_witness_name($block_arr['witness']);
			$replace['title']=$id.' - '.$replace['title'];
			$replace['description']=ltmp($ltmp_arr['explorer']['blocks_data'],['id'=>$id,'date'=>date('d.m.Y H:i:s',$block_arr['time']),'trx'=>$block_arr['trx'],'ops'=>$block_arr['ops'],'vops'=>$block_arr['vops']]);
			print '
			<div class="cards-view">
			<div class="cards-container">
			<div class="card">
			<h2 class="left">'.$ltmp_arr['explorer']['blocks_caption'].' <span class="captions secondary">'.$id.'</span>';
			print '<span class="captions block-nav">';
			if(1!=$id){
				print '<a href="/explorer/block/'.(-1+$id).'/" class="">&larr;</a>';
			}
			print '<a href="/explorer/block/'.(1+$id).'/" class="">&rarr;</a>';
			print '</span>';
			print '</h2>';
			print '<p>'.$ltmp_arr['explorer']['blocks_hash'].' <span class="captions">'.hash_view($block_arr['hash'],true).'</span></p>';
			print '<p>'.$ltmp_arr['explorer']['blocks_datetime'].' <span class="captions">'.date('d.m.Y H:i:s',$block_arr['time']).' GMT</span></p>';
			print '<p>'.$ltmp_arr['explorer']['blocks_witness'].' <a href="/witnesses/'.$witness.'/" class="view-account">'.$witness.'</a></p>';
			$th_sorted=' data-sorted="true" data-sorted-direction="descending"';
			$trx='';

			$q=$db->sql("SELECT *, HEX(`hash`) as `hash` FROM `trx` WHERE `block`='".$block_arr['id']."' ORDER BY `id` ASC");
			while($m=$db->row($q)){
				$m['hash']=substr($m['hash'],0,-24);
				$trx.='
				<tr data-id="'.$m['id'].'">
					<td>'.$m['num'].'</td>
					<td class="nowrap"><a href="/explorer/tx/'.$m['hash'].'/">'.hash_view($m['hash']).'</a></td>
					<td class="text-right">'.$m['ops'].'</td>
					<td class="text-right">'.$m['vops'].'</td>
				</tr>';
			}
			if($trx){
				print '<hr><h3 class="left">'.$ltmp_arr['explorer']['tx_table_caption'].'</h3>';
				print '<table class="trx captions sortable-theme-slick" width="100%" data-sortable="false">';
				print '<thead>';
				print '
				<tr>
					<th'.$th_sorted.'>'.$ltmp_arr['explorer']['tx_table_num'].'</th>
					<th>'.$ltmp_arr['explorer']['tx_table_hash'].'</th>
					<th class="text-right"></th>
					<th class="text-right nowrap">'.$ltmp_arr['explorer']['tx_table_vops'].'</th>
				</tr>';
				print '</thead>';
				print '<tbody>';
				print $trx;
				print '</tbody></table>';
			}

			$ops_arr=array();
			$q=$db->sql("SELECT `id`,`name` FROM `ops_type`");
			while($m=$db->row($q)){
				$ops_arr[$m['id']]=$m['name'];
			}
			$ops='';
			$q=$db->sql("SELECT * FROM `ops` WHERE `block`='".$block_arr['id']."' AND `trx` IS NULL ORDER BY `id` ASC");
			while($m=$db->row($q)){
				$ops.='
				<tr data-id="'.$m['id'].'">
					<td>'.($m['v']?'<span class="grey" title="'.$ltmp_arr['explorer']['tx_op_virtual'].'">':'').$ops_arr[$m['type']].($m['v']?'</span>':'').'</td>
					<td><div class="view-json" data-type="'.$ops_arr[$m['type']].'">'.htmlspecialchars($m['json']).'</div></td>
				</tr>';
			}
			if($ops){
				print '<hr><h3 class="left">'.$ltmp_arr['explorer']['tx_vops_caption'].'</h3>';
				print '<table class="ops captions sortable-theme-slick" width="100%" data-sortable="false">';
				print '<thead>';
				print '
				<tr>
					<th>'.$ltmp_arr['explorer']['tx_op_type'].'</th>
					<th width="100%">'.$ltmp_arr['explorer']['tx_op_json'].'</th>
				</tr>';
				print '</thead>';
				print '<tbody>';
				print $ops;
				print '</tbody></table>';
			}
			print '</div></div></div>';
		}
	}
	else{
		header('location:/explorer/');
		exit;
		/*
		print '
		<div class="cards-view">
		<div class="cards-container">
		<div class="card">
		<h2 class="left">Блоки</h2>';
		print '<table class="blocks captions sortable-theme-slick" width="100%" data-sortable="false">';
		print '<thead>';
		$th_sorted=' data-sorted="true" data-sorted-direction="descending"';
		print '
		<tr>
			<th>Дата (GMT)</th>
			<th'.$th_sorted.'>Номер</th>
			<th>Hash</th>
			<th>Делегат</th>
			<th class="text-right">Транзакций</th>
			<th class="text-right">Операций</th>
			<th class="text-right">Виртуальных операций</th>
		</tr>';
		print '</thead>';
		print '<tbody>';
		$q=$db->sql("SELECT *, HEX(`hash`) as `hash` FROM `blocks` ORDER BY `id` DESC LIMIT 100");
		while($m=$db->row($q)){
			$witness=get_witness_name($m['witness']);
			print '
			<tr data-id="'.$m['id'].'">
				<td>'.date('d.m.Y H:i:s',$m['time']).'</td>
				<td><a href="/explorer/block/'.$m['id'].'/">'.$m['id'].'</a></td>
				<td class="nowrap">'.hash_view($m['hash']).'</td>
				<td><a href="/witnesses/'.$witness.'/" class="view-account nowrap">'.$witness.'</a></td>
				<td class="text-right">'.$m['trx'].'</td>
				<td class="text-right">'.$m['ops'].'</td>
				<td class="text-right">'.$m['vops'].'</td>
			</tr>';
		}
		print '</tbody></table>';
		print '</div></div></div>';
		*/
	}
}
else
if('schedule'==$path_array[2]){
	$replace['title']='Очереди'.' - '.$replace['title'];
	$replace['description']='Очередь делегатов в системе VIZ, историческая сводка изменений голосуемых параметров системы.';
}
else
if('global-properies'==$path_array[2]){
	$replace['title']='Глобальные свойства'.' - '.$replace['title'];
	$replace['description']='Динамические глобальные свойства системы VIZ, историческая сводка изменений, графики по слепкам.';
}
else{
	$replace['description']=$ltmp_arr['explorer']['description'];
	print '
	<div class="cards-view">
	<div class="cards-container">
	<div class="card">
	<h1>'.$ltmp_arr['explorer']['title'].'</h1>';
	print '<form action="/explorer/search/" method="POST" style="margin-bottom:20px;"><input type="text" name="viz-explorer-query" placeholder="'.$ltmp_arr['explorer']['search_placeholder'].'" class="simple-rounded wide explorer-search">
	<input type="submit" style="position: absolute; left: -9999px; width: 1px; height: 1px;" tabindex="-1"></form>';
	print '<h3 class="left">'.$ltmp_arr['explorer']['last_tx_caption'].'</h3>';
	print '<table class="trx captions sortable-theme-slick" width="100%" data-sortable="false">';
	print '<thead>';
	$th_sorted=' data-sorted="true" data-sorted-direction="descending"';
	print '
	<tr>
		<th>'.$ltmp_arr['explorer']['tx_table_hash'].'</th>
		<th class="text-right">'.$ltmp_arr['explorer']['tx_table_ops'].'</th>
		<th class="text-right nowrap">'.$ltmp_arr['explorer']['tx_table_vops'].'</th>
	</tr>';
	print '</thead>';
	print '<tbody>';
	$q=$db->sql("SELECT *, HEX(`hash`) as `hash` FROM `trx` ORDER BY `id` DESC LIMIT 5");
	while($m=$db->row($q)){
		$m['hash']=substr($m['hash'],0,-24);
		print '
		<tr data-id="'.$m['id'].'">
				<td class="nowrap"><a href="/explorer/tx/'.$m['hash'].'/">'.hash_view($m['hash'],true).'</a></td>
				<td class="text-right">'.$m['ops'].'</td>
				<td class="text-right">'.$m['vops'].'</td>
		</tr>';
	}
	print '</tbody></table>';

	print '<hr><h3 class="left">'.$ltmp_arr['explorer']['last_blocks_caption'].'</h3>';
	print '<table class="blocks captions sortable-theme-slick" width="100%" data-sortable="false">';
	print '<thead>';
	$th_sorted=' data-sorted="true" data-sorted-direction="descending"';
	print '
	<tr>
		<th>'.$ltmp_arr['explorer']['tx_table_datetime'].'</th>
		<th'.$th_sorted.'>'.$ltmp_arr['explorer']['tx_table_num'].'</th>
		<th>'.$ltmp_arr['explorer']['tx_table_hash'].'</th>
		<th>'.$ltmp_arr['explorer']['tx_table_witness'].'</th>
		<th class="text-right">'.$ltmp_arr['explorer']['tx_table_trx'].'</th>
		<th class="text-right">'.$ltmp_arr['explorer']['tx_table_ops'].'</th>
		<th class="text-right nowrap">'.$ltmp_arr['explorer']['tx_table_vops'].'</th>
	</tr>';
	print '</thead>';
	print '<tbody>';
	$q=$db->sql("SELECT *, HEX(`hash`) as `hash` FROM `blocks` ORDER BY `id` DESC LIMIT 3");
	while($m=$db->row($q)){
		$witness=get_witness_name($m['witness']);
		print '
		<tr>
			<td>'.date('d.m.Y H:i:s',$m['time']).'</td>
			<td><a href="/explorer/block/'.$m['id'].'/">'.$m['id'].'</a></td>
			<td class="nowrap">'.hash_view($m['hash']).'</td>
			<td><a href="/witnesses/'.$witness.'/" class="view-account nowrap">'.$witness.'</a></td>
			<td class="text-right">'.$m['trx'].'</td>
			<td class="text-right">'.$m['ops'].'</td>
			<td class="text-right">'.$m['vops'].'</td>
		</tr>';
	}
	print '</tbody></table>';

	print '<hr><h3 class="left">'.$ltmp_arr['explorer']['schedule_title'].'</h3>';
	$last_block_time=$db->select_one('blocks','time','ORDER BY `id` DESC');
	$last_chain_props=$db->sql_row("SELECT * FROM `chain_props_snapshot` WHERE `time`<='".$last_block_time."' ORDER BY `id` DESC LIMIT 1");
	$shuffled_witnesses=explode(';',$last_chain_props['shuffled_witnesses']);
	$last_dgp=$db->sql_row("SELECT * FROM `dgp_snapshot`WHERE `time`<='".$last_block_time."' ORDER BY `id` DESC LIMIT 1");
	$last_dgp_json=json_decode($last_dgp['json'],true);
	print '<p>'.$ltmp_arr['explorer']['schedule_datetime'].' <span class="captions">'.date('d.m.Y H:i:s',$last_chain_props['time']).' GMT</span></p>';
	print '<p>'.$ltmp_arr['explorer']['schedule_shuffle_block'].' <span class="captions">'.$last_chain_props['current_shuffle_block'].'</span></p>';
	print '<table class="blocks captions sortable-theme-slick" width="500" data-sortable="false">';
	print '<thead>';
	$th_sorted=' data-sorted="true" data-sorted-direction="descending"';
	print '
	<tr>
		<th width="15%">'.$ltmp_arr['explorer']['schedule_table_num'].'</th>
		<th width="20%">'.$ltmp_arr['explorer']['schedule_table_target_witness'].'</th>
		<th class="nowrap" width="20%">'.$ltmp_arr['explorer']['schedule_table_witness'].'</th>
	</tr>';
	print '</thead>';
	print '<tbody>';
	$aslot_fix=0;
	for($i=1;$i<=$last_chain_props['scheduled_witnesses'];$i++){
		$block_arr=$db->sql_row("SELECT * FROM `blocks` WHERE `id`='".($last_chain_props['current_shuffle_block']+$i)."'");
		$witness=get_witness_name($block_arr['witness']);
		//https://github.com/VIZ-Blockchain/viz-cpp-node/blob/master/libraries/chain/database.cpp#L1199
		$current_aslot=$last_dgp_json['current_aslot']+$i-$aslot_fix;
		$shuffled_witness=$shuffled_witnesses[$current_aslot % $last_chain_props['scheduled_witnesses']];
		print '
		<tr>
			<td>'.($block_arr['id']?'<a href="/explorer/block/'.($last_chain_props['current_shuffle_block']+$i).'/">'.($last_chain_props['current_shuffle_block']+$i).'</a>':($last_chain_props['current_shuffle_block']+$i)).'</td>
			<td><a href="/witnesses/'.$shuffled_witness.'/" class="view-account'.($witness!=$shuffled_witness?($block_arr['id']?' red':' orange'):'').' nowrap">'.$shuffled_witness.'</a></td>
			<td>'.($block_arr['id']?'<a href="/witnesses/'.$witness.'/" class="view-account nowrap">'.$witness.'</a>':'&mdash;').'</td>
		</tr>';
		if($block_arr['id']){
			if($witness!=$shuffled_witness){//if miss, move aslot
				$aslot_fix--;
			}
		}
	}
	print '</tbody></table>';
	print '</div></div></div>';
}
$content=ob_get_contents();
ob_end_clean();