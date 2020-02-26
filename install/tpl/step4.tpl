<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />

	<title>{#bread_database_setting#} - {$smarty.const.APP_NAME} v{$smarty.const.APP_VERSION}</title>

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
					<li>{#bread_database_setting#}</li>
					<li><strong class="code">{#install_step#} 4</strong></li>
				</ul>
			</div>
		</div>

		<div class="first">
			{if $warning}
			<ul class="messages">
				<li class="highlight red">
				{$warning}
				</li>
			</ul>
			{elseif $installed}
			<ul class="messages">
				<li class="highlight red">
				{$installed}
				</li>
			</ul>
			{/if}
		</div>

		<div class="widget first">
			<div class="head"><h5>{#bread_database_setting#}</h5><h5 style="float: right;">{#install_step#} 4</h5></div>
			<div class="body">{#database_setting#}</div>
			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic mainForm">
				<col width="5">
				<col width="300">
				<col>
				<thead>
				<tr>
					<td>?</td>
					<td>{#col_parametr#}</td>
					<td>{#col_requered#}</td>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td style="text-align: center;"><a href="javascript:void(0);" style="cursor:help;" class="rightDir link btext" title="{#field_host#}">[?]</a></td>
					<td>{#dbserver#}</td>
					<td><div class="pr12"><input name="dbhost" type="text" id="dbhost" size="40" value="{$smarty.request.dbhost|escape|stripslashes|default:'localhost'}"></div></td>
				</tr>
				<tr>
					<td style="text-align: center;"><a href="javascript:void(0);" style="cursor:help;" class="rightDir link btext" title="{#field_user#}">[?]</a></td>
					<td>{#dbuser#}</td>
					<td><div class="pr12"><input name="dbuser" type="text" id="dbuser" size="40" value="{$smarty.request.dbuser|escape|stripslashes|default:root}"></div></td>
				</tr>
				<tr>
					<td style="text-align: center;"><a href="javascript:void(0);" style="cursor:help;" class="rightDir link btext" title="{#field_name#}">[?]</a></td>
					<td>{#dbpass#}</td>
					<td><div class="pr12"><input name="dbpass" type="text" id="dbpass" size="40" value=""></div></td>
				</tr>
				<tr>
					<td style="text-align: center;"><a href="javascript:void(0);" style="cursor:help;" class="rightDir link btext" title="{#field_pass#}">[?]</a></td>
					<td>{#dbname#}</td>
					<td><div class="pr12"><input name="dbname" type="text" id="dbname" size="40" value="{$smarty.request.dbname|escape|stripslashes}"></div></td>
				</tr>
				<tr>
					<td style="text-align: center;"><a href="javascript:void(0);" style="cursor:help;" class="rightDir link btext" title="{#field_prf#}">[?]</a></td>
					<td>{#dbprefix#}</td>
					<td><div class="pr12"><input name="dbprefix" type="text" id="dbprefix" size="40" value="{$smarty.request.dbprefix|escape|stripslashes|default:$dbpref}"></div></td>
				</tr>
				<tr class="yellow">
					<td style="text-align: center;"><a href="javascript:void(0);" style="cursor:help;" class="rightDir link btext" title="{#database_not_checked#}">[?]</a></td>
					<td>{#dbcreat#}</td>
					<td><input type="checkbox" class="float" name="dbcreat" value="1" /> <label>{#database_not_checked#}</label></td>
				</tr>
				<tr class="red">
					<td style="text-align: center;"><a href="javascript:void(0);" style="cursor:help;" class="rightDir link btext" title="{#database_clean#}">[?]</a></td>
					<td>{#dbclear#}</td>
					<td><input type="checkbox" class="float" name="dbclean" value="1" /> <label>{#database_clean#}</label></td>
				</tr>
				<tr>
					<td colspan="3">
						<ul class="messages">
							<li class="highlight grey">{#database_setting_foot#}</li>
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
				<input class="basicBtn" type="submit" value="{#database_setting_save#}" />
				&nbsp;
				<button id="ask-cancel" class="redBtn">{#exit#}</button>
			</div>
		</div>

			<input name="force" type="hidden" id="force" value="" />
			<input name="step" type="hidden" id="step" value="4" />

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