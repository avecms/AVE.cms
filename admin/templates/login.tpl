<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />

	<title>{#MAIN_LOGIN_TEXT#}</title>

	<meta name="robots" content="noindex, nofollow">
	<meta http-equiv="pragma" content="no-cache">
	<meta name="generator" content="Notepad" >
	<meta name="Expires" content="Mon, 06 Jan 1990 00:00:01 GMT">

	<!-- Favicon -->
	<link rel="icon" type="image/vnd.microsoft.icon" href="{$ABS_PATH}admin/admin.favicon.ico">
	<link rel="SHORTCUT ICON" href="{$ABS_PATH}admin/admin.favicon.ico">

	<!-- CSS Files -->
	<link href="{$tpl_dir}/css/reset.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="{$tpl_dir}/css/login.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="{$tpl_dir}/css/color_{$smarty.const.DEFAULT_THEME_FOLDER_COLOR}.css" rel="stylesheet" type="text/css" media="screen" />

	<!-- JS files -->
	{include file='login_scripts.tpl'}
	<script src="{$tpl_dir}/js/login.js" type="text/javascript"></script>

</head>

<body>
	<div id="topNav">
		<div class="fixed">
			<div class="wrapper">
				<div class="backTo">
					<a href="../" title=""><img src="{$tpl_dir}/images/icons/mainWebsite.png" alt="" /><span>{#MAIN_LOGIN_BACK_SITE#}</span></a>
				</div>
				<div class="userNav">
					<ul>
						<li>
							<a href="#" title=""><img src="{$tpl_dir}/images/icons/register.png" alt="" /><span>{#MAIN_LOGIN_REGISTER#}</span></a>
						</li>
						<li>
							<a href="#" title=""><img src="{$tpl_dir}/images/icons/contact.png" alt="" /><span>{#MAIN_LOGIN_LOST#}</span></a>
						</li>
						<li>
							<a href="#" title=""><img src="{$tpl_dir}/images/icons/help.png" alt="" /><span>{#MAIN_LOGIN_HELP#}</span></a>
						</li>
					</ul>
				</div>
				<div class="fix">
				</div>
			</div>
		</div>
	</div>

	<div class="loginWrapper">
		<div class="loginLogo">
			<img src="{$tpl_dir}/images/loginLogo.png" alt="" />
		</div>
		<div class="loginPanel">
			<div class="head">
				<h5>{#MAIN_LOGIN_INTRO#}</h5>
			</div>
			<form method="post" action="admin.php" class="mainForm">
				<input type="hidden" name="action" value="login">
				<fieldset>
					<div class="loginRow">
						<label for="user_login">{#MAIN_LOGIN_NAME#}</label>
						<div class="loginInput">
							<input type="text" name="user_login" value="{$smarty.request.user_login|escape}" class="loginEmail">
						</div>
						<div class="fix">
						</div>
					</div>
					<div class="loginRow">
						<label for="user_pass">{#MAIN_LOGIN_PASSWORD#}</label>
						<div class="loginInput">
							<input type="password" name="user_pass" class="loginPassword">
						</div>
						<div class="fix">
						</div>
					</div>
					{if $captcha}
					<div class="loginRow">
						<label for="req2">{#MAIN_LOGIN_CAP_CODE#}</label>
						<div class="loginInput">
							<span id="captcha"><img src="/inc/captcha.php" alt="" width="120" height="60" border="0" /></span>
						</div>
						<div class="fix">
						</div>
					<label>{#MAIN_LOGIN_CAP_CODE_REF#}</label>	
						<div>
							<img id="captcha-ref" style="cursor: pointer; margin-left: 58px;" src="{$tpl_dir}/images/ref.png" alt="" title="{#MAIN_LOGIN_CAP_CODE_REFR#}" width="30" height="30" border="0" />
						</div>	
					</div>
					<div class="loginRow">
						<label for="securecode">{#MAIN_LOGIN_CAPTCHA#}</label>
						<div class="loginInput">
							<input name="securecode" type="text" id="securecode"	class="field" autocomplete="off"/>
						</div>
						<div class="fix">
						</div>
					</div>
					{/if}
					<div class="loginRow">
						<div class="rememberMe">
							<input type="checkbox" id="SaveLogin" name="SaveLogin" value="1" />
							<label style="cursor: pointer; ">{#MAIN_LOGIN_REMEMBER#}</label>
						</div>
						<input type="submit" value="{#MAIN_LOGIN_BUTTON#}" class="basicBtn submitForm" style="margin-bottom:14px">
					</div>
				</fieldset>
			</form>
		</div>
		{if $error}
		<div class="loginRowError">
			<ul class="messages">
				<li class="highlight red">{$error}</li>
			</ul>
		</div>
		{/if}
	</div>

	<div id="footer">
		<div class="wrapper">
			<span class="floatleft">{#oficial_site#}: {$smarty.const.APP_INFO}</span>
			<span class="floatleft ml20">{#support#}: <a href="mailto:support@ave-cms.ru">support@ave-cms.ru</a></span>
			<span class="floatright">{$smarty.const.APP_NAME} v{$smarty.const.APP_VERSION}</span>
		</div>
	</div>
</body>
</html>