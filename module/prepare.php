<?php
include('ltmp_arr.php');
include('functions.php');
$replace['title']='info VIZ+';

$replace['description']=$ltmp_arr['meta']['description'];

$replace['css_change_time']=$css_change_time;
$replace['script_change_time']=$script_change_time;
$replace['index_page_selected']='';
$replace['head_addon']='';

if(isset($path_array)){
	$replace['menu']='
		<a class="menu-el'.('accounts'==$path_array[1]?' selected':'').'" href="/accounts/">'.$ltmp_arr['menu']['accounts'].'</a>
		<a class="menu-el'.('witnesses'==$path_array[1]?' selected':'').'" href="/witnesses/">'.$ltmp_arr['menu']['witnesses'].'</a>
		<a class="menu-el'.('explorer'==$path_array[1]?' selected':'').'" href="/explorer/">'.$ltmp_arr['menu']['explorer'].'</a>
	';
}

$replace['select-lang']='';
$select_lang_arr=[];
foreach($ltmp_base as $lang_el){
	if($lang_el['active']){
		$select_lang_arr[]='<a href="?set_lang='.$lang_el['code2'].'"'.(($lang_el['code2']==$ltmp_current)?' class="current"':'').'>'.$lang_el['local-name'].'</a>';
	}
}
$replace['select-lang']='<div class="select-lang captions">'.implode(' / ',$select_lang_arr).'</div>';