<?php
include('functions.php');
$replace['title']='info VIZ+';
$replace['description']='Вся актуальная информация и статистика по блокчейну VIZ: блок эксплорер, делегаты, аккаунты';

$replace['css_change_time']=$css_change_time;
$replace['script_change_time']=$script_change_time;
$replace['index_page_selected']='';
$replace['head_addon']='';

if(isset($path_array)){
	$replace['menu']='
		<a class="menu-el'.('accounts'==$path_array[1]?' selected':'').'" href="/accounts/">Аккаунты</a>
		<a class="menu-el'.('witnesses'==$path_array[1]?' selected':'').'" href="/witnesses/">Делегаты</a>
		<a class="menu-el'.('explorer'==$path_array[1]?' selected':'').'" href="/explorer/">Блок-эксплорер</a>
	';
}