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

if (!defined('ACP'))
{
	header('Location:index.php');
	exit;
}

$AVE_Template->config_load(BASE_DIR . '/admin/lang/' . $_SESSION['admin_language'] . '/modules.txt', 'modules');

if (!empty($_REQUEST['moduleaction']))
{
	if (!check_permission('mod_' . $_REQUEST['mod']))
	{
		echo $AVE_Template->get_config_vars('MAIN_NO_PERM_MODULES');
		exit;
	}
}

if (!empty($_REQUEST['module']))
{
	$module_path = preg_replace('/[^\w]/', '', $_REQUEST['module']);
	if (!empty($module_path)) define('MODULE_PATH', $module_path);
}

switch($_REQUEST['action'])
{
	case '':
		if (check_permission_acp('modules_view'))
		{
			$AVE_Module->moduleList();
		}
		break;

	case 'quicksave':
		if (check_permission_acp('modules_system'))
		{
			$AVE_Module->moduleOptionsSave();
		}
		break;

	case 'install':
	case 'reinstall':
		if (check_permission_acp('modules_system'))
		{
			$AVE_Module->moduleInstall();
		}
		break;

	case 'update':
		if (check_permission_acp('modules_system'))
		{
			$AVE_Module->moduleUpdate();
		}
		break;

	case 'delete':
		if (check_permission_acp('modules_system'))
		{
			$AVE_Module->moduleDelete();
		}
		break;

	case 'onoff':
		if (check_permission_acp('modules_system'))
		{
			$AVE_Module->moduleStatusChange();
		}
		break;

	case 'modedit':
		if (check_permission_acp('modules_admin'))
		{
			$mod_path = preg_replace('/[^\w]/', '', $_REQUEST['mod']);
			$mod_path = BASE_DIR . '/modules/' . $mod_path . '/module.php';
			if (is_file($mod_path)) include($mod_path);
		}
		break;

	case 'remove':
		if (check_permission_acp('modules_system'))
		{
			$AVE_Module->moduleRemove($_REQUEST['module']);
		}
		break;
}

?>