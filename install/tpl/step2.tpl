<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />

	<title>{#bread_lictexttitle#} - {$smarty.const.APP_NAME} v{$smarty.const.APP_VERSION}</title>

	<meta name="robots" content="noindex, nofollow">
	<meta http-equiv="pragma" content="no-cache">
	<meta name="generator" content="AVE.CMS" >
	<meta name="Expires" content="Mon, 06 Jan 1990 00:00:01 GMT">

	<!-- Favicon -->
	<link rel="icon" type="image/vnd.microsoft.icon" href="../admin/favicon.ico">
	<link rel="SHORTCUT ICON" href="../admin/favicon.ico">

	<!-- CSS Files -->
	<link href="../admin/templates/css/reset.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="../admin/templates/css/main.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="../admin/templates/css/data_table.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="../admin/templates/css/jquery-ui_custom.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="../admin/templates/css/jquery.fancybox.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="../admin/templates/css/color_{$smarty.const.DEFAULT_THEME_FOLDER_COLOR}.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="/install/tpl/css/fix.css" rel="stylesheet" type="text/css" media="screen" />

	<!-- JS files -->
	<script src="../lib/scripts/jquery-1.11.1.min.js" type="text/javascript"></script>
	<script src="../lib/scripts/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>
	<script src="../lib/scripts/jquery-ui.min.js" type="text/javascript"></script>
	<script src="../lib/scripts/jquery-ui-time.js" type="text/javascript"></script>
	<script src="../lib/scripts/jquery.form.min.js" type="text/javascript"></script>
	<script src="../lib/scripts/jquery.transform.js" type="text/javascript"></script>
	<script src="../lib/scripts/jquery.cookie.js" type="text/javascript"></script>
	<script src="../lib/scripts/jquery.jgrowl.js" type="text/javascript"></script>
	<script src="../lib/scripts/jquery.alerts.js" type="text/javascript"></script>
	<script src="../lib/scripts/jquery.tipsy.js" type="text/javascript"></script>
	<script src="../lib/scripts/jquery.totop.js" type="text/javascript"></script>
	<script src="../lib/scripts/jquery.placeholder.min.js" type="text/javascript"></script>
	<script src="../lib/scripts/mousetrap.js" type="text/javascript"></script>

	<script src="/install/tpl/js/main.js" type="text/javascript"></script>
	<script src="/install/lang/ru.js" type="text/javascript"></script>

</head>
<body>

<div id="topNav">
	<div class="fixed">
	<div class="wrapper">
			<div class="userNav">
				<ul>
					<li>
						<a href="#" title=""><img src="../admin/templates/images/icons/help.png" alt="" /><span>{#install_help#}</span></a>
					</li>
				</ul>
			</div>
			<div class="fix">
			</div>
		</div>
	</div>
</div>


<div class="wrapper_fixed">

<div class="content" id="contentPage">
	<form action="index.php" method="post" enctype="multipart/form-data" name="s" id="s" class="mainForm">

		<div class="first" align="center"><img src="../admin/templates/images/loginLogo.png" /></div>

		<div class="title first"><h5>{#install#} {$smarty.const.APP_NAME} v{$smarty.const.APP_VERSION}</h5></div>

		<div class="breadCrumbHolder module">
			<div class="breadCrumb module">
				<ul>
					<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}"></a></li>
					<li>{#install#} {$smarty.const.APP_NAME} v{$smarty.const.APP_VERSION}</li>
					<li>{#bread_lictexttitle#}</li>
					<li><strong class="code">{#install_step#} 2</strong></li>
				</ul>
			</div>
		</div>

		<div class="widget first">
			<div class="head"><h5>{#bread_lictexttitle#}</h5><h5 style="float: right;">{#install_step#} 2</h5></div>
			<div class="body" style="height: 300px; overflow: auto;">
				{include file="../eula/ru.tpl"}
			</div>
			<br />
			<div class="rowElem">
				<input name="agree" type="checkbox" id="agree" class="float" /><label style="float: none; margin-left: 20px;"> {#lic_agree#}</label>
			</div>
		</div>

		<div class="widget first">
			<div class="rowElem">
				<input class="basicBtn disabled" type="submit" value="{#button_setup_next#}" name="Submit" id="Submit" disabled="disabled" />
				&nbsp;
				<button id="ask-cancel" class="redBtn">{#exit#}</button>
			</div>
		</div>

			<input name="force" type="hidden" id="force" value="" />
			<input name="step" type="hidden" id="step" value="3" />
			
 		</form>
	</div><!-- /Content -->

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

<script type="text/javascript">
{literal}
$(document).ready(function(){
	$("#agree").click(function(){
		if ($("#agree").is(":checked")){
			$("#Submit").attr('disabled', false).removeClass("disabled");
		}else{
			$("#Submit").attr('disabled', true).addClass("disabled");
		}
	});
});
{/literal}
</script>

</body>

</html>