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

	define('START_MICROTIME', microtime());

	ob_start();

	define('ACP', 1);

	define('BASE_DIR', str_replace("\\", "/", dirname(dirname(__FILE__))));

	if (! @filesize(BASE_DIR . '/inc/db.config.php'))
	{
		header('Location: ../install/index.php');
		exit;
	}

	require(BASE_DIR . '/admin/init.php');

	if (! isset($_SESSION['user_id']))
	{
		@session_destroy();

		if (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 'run' || ( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ))
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorised access', true);
			exit;
		}

		$AVE_Template->assign('captcha', ADMIN_CAPTCHA);
		$AVE_Template->display('login.tpl');
		exit;
	}

	if (! defined('UID') || ! check_permission('adminpanel'))
	{
		user_logout();
		header('Location:admin.php');
		exit;
	}

	if (empty($_SESSION['admin_language']))
	{
		if (! empty($_REQUEST['feld']) && ! empty($_REQUEST['Id']) && ! empty($_REQUEST['rubric_id']))
		{
			$_SESSION['redirectlink'] = 'index.php?do=docs&action=edit&pop=1'
										. '&rubric_id=' . (int)$_REQUEST['rubric_id']
										. '&Id='        . (int)$_REQUEST['Id']
										. '&feld='      . (int)$_REQUEST['feld']
										. '#'           . (int)$_REQUEST['feld'];
		}
		else
			{
				unset($_SESSION['redirectlink']);
			}

		header('Location:admin.php');
		exit;
	}

	/* Вывод модулей на всех страницах */
	get_editable_module();
	LoginModuleCheck();

	$AVE_Template->assign('use_editor', get_settings('use_editor'));
	$AVE_Template->assign('user_avatar', getAvatar($_SESSION['user_id'],25));

	if (!isset($_REQUEST['do']))     $_REQUEST['do']     = '';
	if (!isset($_REQUEST['action'])) $_REQUEST['action'] = '';
	if (!isset($_REQUEST['sub']))    $_REQUEST['sub']    = '';
	if (!isset($_REQUEST['submit'])) $_REQUEST['submit'] = '';

	//Шаблоны навигации
	$AVE_Template->assign('navi', $AVE_Template->fetch('navi/navi.tpl'));
	$AVE_Template->assign('navi_top', $AVE_Template->fetch('navi/navi_top.tpl'));

	//Разрешенные методы
	$allowed = array(
		'index',
		'start',
		'templates',
		'rubs',
		'user',
		'finder',
		'groups',
		'docs',
		'navigation',
		'logs',
		'request',
		'modules',
		'settings',
		'blocks',
		'sysblocks',
		'dbsettings',
		'browser',
		'fields'
	);

	$do = (! empty($_REQUEST['do']) && in_array($_REQUEST['do'], $allowed))
		? $_REQUEST['do']
		: 'start';

	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Expires: " . date("r"));

	include(BASE_DIR . '/admin/' . $do . '.php');

	if (defined('NOPERM'))
		$AVE_Template->assign('content', $config_vars['MAIN_NO_PERMISSION']);

	//Шаблоны
	$tpl = (isset($_REQUEST['pop']) && $_REQUEST['pop'] == 1)
		? 'pop.tpl'
		: 'main.tpl';

	if (isset($_REQUEST['onlycontent']) && $_REQUEST['onlycontent'] == 1)
		$tpl = 'onlycontent.tpl';

	// Выводим шаблон
	$AVE_Template->display($tpl);

	// Статистика
	if (defined('PROFILING') && PROFILING)
		echo get_statistic(1, 1, 1, 1);
?>