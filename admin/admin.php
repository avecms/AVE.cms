<?php

/**
 * AVE.cms
 *
 * @package AVE.cms
 * @version 3.x
 * @filesource
 * @copyright © 2007-2014 AVE.cms, http://www.ave-cms.ru
 *
 * @license GPL v.2
 */

define('ACP', 1);
define('ACPL', 1);
define('BASE_DIR', str_replace("\\", "/", dirname(dirname(__FILE__))));

if (! @filesize(BASE_DIR . '/inc/db.config.php')) {
	header('Location:/install/index.php');
	exit;
}

require(BASE_DIR . '/admin/init.php');

unset ($captcha_ok);

if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'logout')
{
	// Завершение работы в админке
	reportLog($AVE_Template->get_config_vars('EXIT_ADMIN'));
	user_logout();
	header('Location:admin.php');
}

if(auth_cookie())
{
	header('Location:index.php');
	exit;
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'login')
{
	// Авторизация
	if (!empty($_POST['user_login']) && !empty($_POST['user_pass']))
	{
		if (ADMIN_CAPTCHA)
		{
			if (isset($_SESSION['captcha_keystring']) && isset($_POST['securecode']) && $_SESSION['captcha_keystring'] == $_POST['securecode']) $captcha_ok = 1;
			else
			{
				unset($_SESSION['user_id'], $_SESSION['user_pass']);
				unset($_SESSION['captcha_keystring']);
				$error = $AVE_Template->get_config_vars('WRONG_CAPTCHA');
				$AVE_Template->assign('error', $error);
			}
		}
		else
			$captcha_ok = 1;

		if ($captcha_ok)
		{
			if (true === user_login($_POST['user_login'], $_POST['user_pass'], 1,(int)(isset($_POST['SaveLogin']) && $_POST['SaveLogin'] == '1')))
			{
			//_echo($_SESSION);
				if (!empty($_SESSION['redirectlink']))
				{
					header('Location:' . $_SESSION['redirectlink']);
					unset($_SESSION['redirectlink']);
					exit;
				}

				reportLog($AVE_Template->get_config_vars('LOGIN_ADMIN'));
				//Перенапрявляем пользователя
				header('Location:'.get_referer_admin_link().'');
				exit;

			}
			else
			{
				reportLog($AVE_Template->get_config_vars('ERROR_ADMIN') . ' - '
							. stripslashes($_POST['user_login']) . ' / '
							. stripslashes($_POST['user_pass']));

				unset($_SESSION['user_id'], $_SESSION['user_pass']);
				unset($_SESSION['captcha_keystring']);
				$error = $AVE_Template->get_config_vars('WRONG_PASS');
				$AVE_Template->assign('error', $error);
			}

		}

	}
}

$AVE_Template->assign('captcha', ADMIN_CAPTCHA);
$AVE_Template->display('login.tpl');
?>