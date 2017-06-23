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
					<li>{#bread_server#}</li>
					<li><strong class="code">{#install_step#} 3</strong></li>
				</ul>
			</div>
		</div>

		<div class="widget first">
			<div class="head"><h5>{#bread_server#}</h5><h5 style="float: right;">{#install_step#} 3</h5></div>


			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic mainForm">
				<col width="10">
				<col>
				<col width="130">
				<col width="130">
				<thead>
				<tr>
					<td></td>
					<td>{#col_parametr#}</td>
					<td>{#col_requered#}</td>
					<td>{#col_have#}</td>
				</tr>
				</thead>
				<tbody>
					<tr>
						<td><span class="icon_sprite {$check.php_version}"></span></td>
						<td>{#php_version#}:</td>
						<td align="center">{$smarty.const.PHP_version}</td>
						<td align="center">{$test.php_version}</td>
					</tr>
					<tr>
						<td><span class="icon_sprite {$check.mysql_version}"></span></td>
						<td>{#mysql_version#}:</td>
						<td align="center">{$smarty.const.MySQL_version}</td>
						<td align="center">{$test.mysql_version}</td>
					</tr>
					<tr>
						<td><span class="icon_sprite {$check.gd_version}"></span></td>
						<td>{#gd_version#}:</td>
						<td align="center">{$smarty.const.GD_version}</td>
						<td align="center">{$test.gd_version}</td>
					</tr>
					{*
					<tr>
						<td><span class="icon_sprite {$check.pcre_version}"></span></td>
						<td>{#prce_version#}:</td>
						<td align="center">{$smarty.const.PCRE_version}</td>
						<td align="center">{$test.pcre_version}</td>
					</tr>
					*}
					<tr>
						<td><span class="icon_sprite {$check.mbstring}"></span></td>
						<td>{#mbstring#}:</td>
						<td align="center">{$smarty.const.MbString}</td>
						<td align="center">{$test.mbstring}</td>
					</tr>
					<tr>
						<td><span class="icon_sprite {$check.json}"></span></td>
						<td>{#json#}:</td>
						<td align="center">{$smarty.const.JSON}</td>
						<td align="center">{$test.json}</td>
					</tr>
					{*
					<tr>
						<td><span class="icon_sprite {$check.simplexml}"></span></td>
						<td>{#simple_xml#}:</td>
						<td align="center">{$smarty.const.SimpleXML}</td>
						<td align="center">{$test.simplexml}</td>
					</tr>
					*}
					<tr>
						<td><span class="icon_sprite {$check.iconv}"></span></td>
						<td>{#iconv#}:</td>
						<td align="center">{$smarty.const.Iconv}</td>
						<td align="center">{$test.iconv}</td>
					</tr>
					{*
					<tr>
						<td><span class="icon_sprite {$check.xslt}"></span></td>
						<td>{#xslt#}:</td>
						<td align="center">{$smarty.const.XSLT}</td>
						<td align="center">{$test.xslt}</td>
					</tr>
					*}
					<tr>
						<td><span class="icon_sprite {$check.data_limit}"></span></td>
						<td>{#max_upload#}:</td>
						<td align="center">{$smarty.const.Data_limit}M</td>
						<td align="center">{$test.data_limit}</td>
					</tr>
					<tr>
						<td><span class="icon_sprite {$check.time_limit}"></span></td>
						<td>{#max_time#}:</td>
						<td align="center">{$smarty.const.TIME_limit} {#seconds#}</td>
						<td align="center">{$test.time_limit} {#seconds#}</td>
					</tr>
					<tr>
						<td><span class="icon_sprite {$check.disk_space}"></span></td>
						<td>{#disk_space#}:</td>
						<td align="center">{$smarty.const.DISC_space}M</td>
						<td align="center">{$test.disk_space}M</td>
					</tr>
					<tr>
						<td><span class="icon_sprite {$check.memmory_limit}"></span></td>
						<td>{#memmory_limit#}:</td>
						<td align="center">{$smarty.const.RAM_space}</td>
						<td align="center">{$test.memmory_limit}</td>
					</tr>
					<tr>
						<td><span class="icon_sprite {$check.s_m}"></span></td>
						<td>{#php_safe#}:</td>
						<td align="center">{$smarty.const.SAFE_MODE}</td>
						<td align="center">{$test.s_m}</td>
					</tr>
					<tr>
						<td><span class="icon_sprite {$check.r_g}"></span></td>
						<td>{#register_globals#}:</td>
						<td align="center">{$smarty.const.REGISTER_GLOBALS}</td>
						<td align="center">{$test.r_g}</td>
					</tr>
					<tr>
						<td><span class="icon_sprite {$check.m_q}"></span></td>
						<td>{#magic_qoutes#}:</td>
						<td align="center">{$smarty.const.MAGIC_QUOTES_GPC}</td>
						<td align="center">{$test.m_q}</td>
					</tr>

				</tbody>
			</table>
			<input name="force" type="hidden" id="force" value="" />
			<input name="step" type="hidden" id="step" value="4" />
		</div>

		<div class="widget first">
			<div class="rowElem">
				<input class="basicBtn" type="submit" value="{#button_setup_next#}" />
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