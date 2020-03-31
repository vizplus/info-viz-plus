function ajax_update_accounts_table(type,search,page){
	if(typeof type==='undefined'){
		type=$('.accounts-table').attr('data-type');
	}
	else{
		$('.accounts-table').attr('data-type',type);
	}
	search=typeof search==='undefined'?$('.accounts-search-text').val():search;
	page=typeof page==='undefined'?1:page;
	$('.accounts-table').html('<span class="submit-button-ring" style="display:inline-block"></span>');
	var xhr = new XMLHttpRequest();
	xhr.overrideMimeType('text/plain');
	xhr.open('POST','/ajax/accounts/',true);
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhr.setRequestHeader('accept','application/json, text/plain, */*');
	xhr.onreadystatechange=function() {
		if(4==xhr.readyState && 200==xhr.status){
			$('.accounts-buttons .inline-button').removeClass('selected');
			$('.accounts-buttons .inline-button[data-type='+type+']').addClass('selected');
			var data=xhr.responseText;
			$('.accounts-table').html(data);
			Sortable.init();
			bind_accounts_page_buttons();
			history.pushState({},'','?type='+encodeURIComponent(type)+'&search='+encodeURIComponent(search)+'&page='+encodeURIComponent(page));
		}
		if(4==xhr.readyState && 200!=xhr.status){
			$('.accounts-table').html('Ошибка. Что-то пошло не так, попробуйте позже.');
		}
	};
	xhr.onerror = function() {
		$('.accounts-table').html('Ошибка. Что-то пошло не так, попробуйте позже.');
	};
	xhr.send('type='+encodeURIComponent(type)+'&search='+encodeURIComponent(search)+'&page='+encodeURIComponent(page));
}
function escape_html(text) {
	var map = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&#039;'
	};
	return text.replace(/[&<>"']/g,function(m){return map[m];});
}
function unescape_html(text) {
	var map = {
		'&amp;': '&',
		'&lt;': '<',
		'&gt;': '>',
		'&quot;': '"',
		'&#039;': "'"
	};
	return text.replace(/&(amp|quot|lt|gt|#039);/g,function(m){console.log('find',m);return map[m];});
}
function trigger_view_memo(e){
	console.log(e);
	let target=e.target;
	target.innerHTML=escape_html(target.getAttribute('data-text'));
	target.removeAttribute('data-text');
	target.classList.add('full');
}
function bind_view_memo(){
	var elements = document.querySelectorAll('.view-memo');
	Array.prototype.forEach.call(elements, function(el, i){
		if(el.hasAttribute('data-text')){
			el.removeEventListener('click',trigger_view_memo);
			el.addEventListener('click',trigger_view_memo,{once:true});
		}
	});
}
function bind_accounts_page_buttons(){
	$('.accounts-table .page-button').unbind('click');
	$('.accounts-table .page-button').bind('click',function(e){
		e.preventDefault();
		ajax_update_accounts_table(undefined,undefined,$(this).attr('data-page'));
	});

	$('.accounts-buttons .inline-button').bind('click',function(e){
		e.preventDefault();
		ajax_update_accounts_table($(this).attr('data-type'));
	});

	$('table.accounts th a').bind('click',function(e){
		e.preventDefault();
		ajax_update_accounts_table($(this).attr('data-type'));
	});
}
function ops_history_load_more(){
	if($('.ops-history-table .load-more-button').hasClass('disabled')){
		return;
	}
	$('.ops-history-table .load-more-button').addClass('disabled');
	var account=$('.ops-history-table table').attr('data-account');
	var type=$('.ops-history-table table').attr('data-type');
	var last_id=$('.ops-history-table table tr:last').attr('data-id');
	var xhr = new XMLHttpRequest();
	xhr.overrideMimeType('text/plain');
	xhr.open('POST','/ajax/ops-history/',true);
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhr.setRequestHeader('accept','application/json, text/plain, */*');
	xhr.onreadystatechange=function() {
		if(4==xhr.readyState && 200==xhr.status){
			var data=xhr.responseText;
			if(''!=data){
				$('.ops-history-table table tbody').append(data);
				bind_view_memo();
				ops_history_filter();
				$('.ops-history-table .load-more-button').removeClass('disabled');
				check_load_more();
			}
			else{
				$('.ops-history-table .load-more-button').html('Вся история загружена').removeClass('load-more-button').unbind('click');
			}
		}
		if(4==xhr.readyState && 200!=xhr.status){
			alert('Ошибка. Что-то пошло не так, попробуйте позже.');
			$('.ops-history-table .load-more-button').removeClass('disabled');
		}
	};
	xhr.onerror = function() {
		alert('Ошибка. Что-то пошло не так, попробуйте позже.');
		$('.ops-history-table .load-more-button').removeClass('disabled');
	};
	xhr.send('account='+encodeURIComponent(account)+'&type='+encodeURIComponent(type)+'&last_id='+encodeURIComponent(last_id));
}
function ops_history_filter(){
	let search=$('.table-ops-history-search').val();
	search=search.replace(/[ \r\t\n]/g,'');
	search=search.toLowerCase();
	let types_str=$('.table-ops-history-selector').val();
	if(types_str){
		$('table.ops-history tbody tr').addClass('hidden');
		let types_arr=types_str.split(',');
		$('table.ops-history tbody tr').each(function(i,el){
			let op_type=$(el).attr('data-type');
			if(types_arr.includes(op_type)){
				if(''!=search){
					let text=$(el).text();
					text=text.replace(/[ \r\t\n]/g,'');
					text=text.toLowerCase();
					if(-1!=text.indexOf(search)){
						$(el).removeClass('hidden');
					}
				}
				else{
					$(el).removeClass('hidden');
				}
			}
		});
	}
	else{
		if(''!=search){
			$('table.ops-history tbody tr').addClass('hidden');
			$('table.ops-history tbody tr').each(function(i,el){
				let text=$(el).text();
				text=text.replace(/[ \r\t\n]/g,'');
				text=text.toLowerCase();
				if(-1!=text.indexOf(search)){
					$(el).removeClass('hidden');
				}
			});
		}
		else{
			$('table.ops-history tbody tr').removeClass('hidden');
		}
	}
}
function bind_ops_history_load_more(){
	$('.ops-history-table .load-more-button').unbind('click');
	$('.ops-history-table .load-more-button').bind('click',ops_history_load_more);
}
function check_load_more(){
	var scroll_top=$(window).scrollTop();
	var window_height=window.innerHeight;
	$('.load-more-button').each(function(i,el){
		if(!$(el).hasClass('disabled')){
			var offset=$(el).offset();
			if((scroll_top+window_height)>(offset.top+$(el).height())){
				ops_history_load_more();
			}
		}
	});
}
var search_timestep=500;
var accounts_search_timer=0;
var ops_history_search_timer=0;
$(document).ready(function(){
	$('.index-charts-selector').bind('click',function(){
		let chart=$(this).attr('rel');
		$('.index-charts').removeClass('selected');
		$('.index-charts#'+chart).addClass('selected');
	});
	$('.table-witnesses-selector').bind('change',function(){
		if($('.witnesses th[data-sorted=true]').hasClass('from-selector')){//reset sort after select if sorted by selector
			$('.witnesses th[data-field=num]').click();
		}
		$('.witnesses .from-selector').addClass('hidden');
		$('.witnesses .from-selector[data-field='+($(this).val())+']').removeClass('hidden');
	});
	$('.table-ops-history-selector').bind('change',function(){
		ops_history_filter();
	});
	$('.toggle-inactive-witnesses').bind('click',function(){
		let toggle_emoji=$(this).find('.toggle_emoji');
		if('none'==$('.witnesses tr.inactive').css('display')){
			$(this).addClass('negative');
			if(toggle_emoji.length){
				toggle_emoji.html(toggle_emoji.attr('data-active'));
			}
			$('.witnesses tr.inactive').css('display','table-row');
		}
		else{
			$(this).removeClass('negative');
			if(toggle_emoji.length){
				toggle_emoji.html(toggle_emoji.attr('data-inactive'));
			}
			$('.witnesses tr.inactive').css('display','none');
		}
	});
	$('.table-ops-history-search').bind('keyup',function(e){
		clearTimeout(ops_history_search_timer);
		if('Enter'==e.originalEvent.key){
			ops_history_filter();
		}
		else{
			ops_history_search_timer=setTimeout(function(){ops_history_filter();},search_timestep);
		}
	});
	$('.accounts-search-text').bind('keyup',function(e){
		clearTimeout(accounts_search_timer);
		if('Enter'==e.originalEvent.key){
			ajax_update_accounts_table();
		}
		else{
			accounts_search_timer=setTimeout(function(){ajax_update_accounts_table();},search_timestep);
		}
	});
	$('.tab-container').each(function(i,el){
		let tab_container=el;
		$(tab_container).find('.tab-view').css('display','none');
		$(tab_container).find('.tab-view[data-tab='+$(tab_container).find('.tab-control .tab-selector.selected').data('tab')+']').css('display','block');
		$(el).find('.tab-control .tab-selector').bind('click',function(e){
			let tab_id=$(this).data('tab');
			$(tab_container).find('.tab-control .tab-selector').removeClass('selected');
			$(this).addClass('selected');
			$(tab_container).find('.tab-view').css('display','none');
			$(tab_container).find('.tab-view[data-tab='+tab_id+']').css('display','block');
		})
	});
	bind_accounts_page_buttons();
	bind_view_memo();
	bind_ops_history_load_more();

	check_load_more();
	$(window).scroll(function(){
		check_load_more();
	});
	$(window).resize(function(){
		check_load_more();
	});
	$('.view-json').each(function(i,el){
		let json=JSON.parse($(el).html());
		$(el).html(prettyPrintJson.toHtml(json,{type:$(el).data('type')}));
	});
});