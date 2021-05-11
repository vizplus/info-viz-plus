<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<title>{title}</title>
	<meta name="description" content="{description}">
	<meta property="og:description" content="{description}">
	<meta name="twitter:description" content="{description}">
	<meta name="viewport" content="width=device-width">
	{head_addon}

	<link rel="stylesheet" href="/app.css?{css_change_time}">
	<script type="text/javascript" src="/jquery-3.4.1.min.js"></script>
	<script type="text/javascript" src="/app.js?{script_change_time}"></script>

	<script type="text/javascript" src="/js/highcharts.js"></script>
	<script type="text/javascript" src="/js/highcharts-exporting.js"></script>
	<link rel="stylesheet" href="/css/sortable-theme-slick.css">
	<script type="text/javascript" src="/js/sortable.js"></script>
	<script type="text/javascript" src="/js/pretty-print-json.js"></script>
</head>
<body>
<div class="header shadow unselectable">
	<div class="horizontal-view">
		<div class="logo"><a href="/" class="prefix{index_page_selected}">info.</a><a href="https://viz.plus/"><img src="/logo.svg" alt="VIZ+"></a></div>
		<div class="menu-list captions" style="display: block;">
			<div class="menu-bg">
				{menu}
			</div>
		</div>
	</div>
</div>
<div class="horizontal-view vertical-view">
	{content}
	{select-lang}
</div>
</body>
</html>