<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />

	<title>{#MAIN_PAGE_TITLE#} - {*#SUB_TITLE#*} ({$smarty.session.user_name|escape})</title>

	<meta name="robots" content="noindex, nofollow">
	<meta http-equiv="pragma" content="no-cache">
	<meta name="generator" content="Notepad" >
	<meta name="Expires" content="Mon, 06 Jan 1990 00:00:01 GMT">
	
	<!-- Favicon -->
	<link rel="icon" type="image/vnd.microsoft.icon" href="{$ABS_PATH}admin/admin.favicon.ico">
	<link rel="SHORTCUT ICON" href="{$ABS_PATH}admin/admin.favicon.ico">

	<!-- CSS Files -->
	<link href="{$tpl_dir}/css/reset.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="{$tpl_dir}/css/main.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="{$tpl_dir}/css/data_table.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="{$tpl_dir}/css/jquery-ui_custom.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="{$tpl_dir}/css/jquery.fancybox.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="{$tpl_dir}/css/color_{$smarty.const.DEFAULT_THEME_FOLDER_COLOR}.css" rel="stylesheet" type="text/css" media="screen" />

	<!-- JS files -->
	{include file='scripts.tpl'}

	<script type="text/javascript" src="/admin/lang/{$smarty.session.admin_language}/scripts.js"></script>
	<script src="{$tpl_dir}/js/main.js" type="text/javascript"></script>

	<!-- JS Scripts -->
	<script>
	  var ave_path = "{$ABS_PATH}";
	  var ave_theme = "{$smarty.const.DEFAULT_THEME_FOLDER}";
	  var ave_admintpl = "{$tpl_dir}";
	</script>

</head>

<body>

<!-- Wrapper -->
<div class="wrapper">

	<!-- Content -->
	<div class="content" id="contentPage">
		{$content}
	</div>

	<div class="fix"></div>
</div>



<!-- Footer -->
<div id="footer">
	<div class="wrapper">
		<span class="floatleft">{#oficial_site#}: {$smarty.const.APP_INFO}</span>
		<span class="floatleft ml20">{#support#}: <a href="mailto:support@ave-cms.ru">support@ave-cms.ru</a></span>
		<span class="floatright">{$smarty.const.APP_NAME} v{$smarty.const.APP_VERSION}</span>
	</div>
</div>

</body>
</html>