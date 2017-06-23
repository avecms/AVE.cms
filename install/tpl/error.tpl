<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />

	<title>{$version_setup}</title>

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
	<link href="tpl/css/fix.css" rel="stylesheet" type="text/css" media="screen" />

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

	<script src="tpl/js/main.js" type="text/javascript"></script>
	<script src="lang/ru.js" type="text/javascript"></script>

	<script type="text/javascript">
	{literal}
    $(document).ready(function(){

		$("#force").click(function(){
			if ($("#force").is(":checked")){
				$("#Submit").attr('disabled', true).addClass("disabled");
				$('#warning').show();
			}else{
				$("#Submit").attr('disabled', false).removeClass("disabled");
				$('#warning').hide();
			}
		});

    });
	{/literal}
    </script>
	
	<style type="text/css">
	{literal}
		div p {padding:10px;}
	{/literal}
	</style>
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
	<form action="index.php" method="post" enctype="multipart/form-data" name="s" id="s" onSubmit="return defaultagree(this)" class="mainForm">

	<div class="first" align="center"><img src="../admin/templates/images/loginLogo.png" /></div>

	<div class="title first"><h5>{#install#} {$version_setup}</h5></div>

		<div class="breadCrumbHolder module">
			<div class="breadCrumb module">
				<ul>
					<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}"></a></li>
					<li>{#install#} {$version_setup}</li>
					<li>{$error_header}</li>
				</ul>
			</div>
		</div>


		<div class="widget first">
			<div class="head"><h5>{$error_header}</h5></div>
		</div>

			<br />
			{foreach from=$error_is_required item="inc"}
			<ul class="messages">
				<li class="highlight red">
				{$inc}
				</li>
			</ul>
			{/foreach}
			<br />

			{if $config_isnt_writeable == 1}
			<ul class="messages">
				<li class="highlight red">
				{#config_isnt_writeable#}
				</li>
			</ul>
			<br />
			{/if}

			<ul class="messages">
				<li class="highlight yellow">
				{#secondchance#}
				</li>
			</ul>

			
			<div id="warning">
			<br />
			<ul class="messages">
				<li class="highlight red">
				{#warning_force#}
				</li>
			</ul>
			</div>

			<div class="widget first">
				<div class="body">
					<div class="rowElem">
						<input name="force" type="checkbox" id="force" class="float" value="1" />&nbsp;<label>{#force#} {if $config_isnt_writeable == 1}{#force_impossibly#}{/if}</span></label>
					</div>
				</div>
			</div>

			<div class="widget first">
				<div class="rowElem">
					<input class="basicBtn disabled" type="submit" value="{#button_setup_next#}" name="Submit" id="Submit" disabled="disabled" />
					&nbsp;
					<button id="ask-cancel" class="redBtn">{#exit#}</button>
				</div>
			</div>

		{if $config_isnt_writeable != 1}
			<input name="force" type="hidden" id="force" value="{$smarty.request.force|escape|stripslashes}" />
		{/if}
			<input name="step" type="hidden" id="step" value="{$smarty.request.step|default:'1'}" />
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
</body>
</html>