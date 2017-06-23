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

	<div class="title first"><h5>{#install#} {$version_setup}</h5></div>

		<div class="breadCrumbHolder module">
			<div class="breadCrumb module">
				<ul>
					<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}"></a></li>
					<li>{#install#} {$version_setup}</li>
					<li>{#bread_stepstatus#}</li>
					<li><strong class="code">{#install_step#} 5</strong></li>
				</ul>
			</div>
		</div>

		<div class="first">
			{if $errors}
			<ul class="messages">

				<li class="highlight red">
			{foreach from=$errors item="error"}
				&bull;&nbsp;{$error}<br />
			{/foreach}
				</li>

			</ul>
			{/if}
		</div>

		<div class="widget first">
			<div class="head"><h5>{#bread_stepstatus#}</h5><h5 style="float: right;">{#install_step#} 5</h5></div>
			<div class="body">{#header_logindata#}</div>
			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic mainForm">
				<col width="300">
				<col>
				<thead>
				<tr>
					<td>{#col_parametr#}</td>
					<td>{#col_requered#}</td>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td><span class="star">*</span> {#username#}:</td>
					<td><div class="pr12"><input name="username" type="text" id="username" size="40" value="{$smarty.request.username|escape|stripslashes|default:'Admin'}" placeholder="{#username#}"></div></td>
				</tr>
				<tr>
					<td><span class="star">*</span> {#email#}:</td>
					<td><div class="pr12"><input name="email" type="text" id="email" size="40" value="{$smarty.request.email|escape|stripslashes}" placeholder="{#email#}"></div></td>
				</tr>
				<tr>
					<td><span class="star">*</span> {#password#}:</td>
					<td><div class="pr12"><input name="pass" type="text" id="pass" size="40" value="{$smarty.request.pass|escape|stripslashes}" placeholder="{#password#}"></div></td>
				</tr>
				<tr>
					<td colspan="3">
						<ul class="messages">
							<li class="highlight grey">{#loginstar#}</li>
						</ul>
					</td>
				</tr>
				</tbody>
			</table>
			<input name="force" type="hidden" id="force" value="" />
			<input name="step" type="hidden" id="step" value="5" />
		</div>

		<div class="widget first">
			<div class="rowElem">
				<input class="basicBtn" type="submit" value="{#button_setup_next#}" />
				&nbsp;
				<button id="ask-cancel" class="redBtn">{#exit#}</button>
			</div>
		</div>

			<input name="force" type="hidden" id="force" value="" />
			<input name="step" type="hidden" id="step" value="5" />

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