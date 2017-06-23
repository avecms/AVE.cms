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

require_once(BASE_DIR . '/class/class.logs.php');

$AVE_Logs = new AVE_Logs;

$AVE_Template->config_load(BASE_DIR . '/admin/lang/' . $_SESSION['admin_language'] . '/logs.txt', 'logs');

switch ($_REQUEST['action'])
{
	case '':
		if (check_permission_acp('logs_view'))
		{
			$AVE_Logs->logList();
		}
		break;

	case 'log404':
		if (check_permission_acp('logs_view'))
		{
			$AVE_Logs->List404();
		}
		break;

	case 'logsql':
		if (check_permission_acp('logs_view'))
		{
			$AVE_Logs->ListSql();
		}
		break;

	case 'delete':
		if (check_permission_acp('logs_clear'))
		{
			$AVE_Logs->logDelete();
		}
		break;

	case 'deletesql':
		if (check_permission_acp('logs_clear'))
		{
			$AVE_Logs->DeleteSql();
		}
		break;

	case 'delete404':
		if (check_permission_acp('logs_clear'))
		{
			$AVE_Logs->Delete404();
		}
		break;

	case 'export':
		if (check_permission_acp('logs_view'))
		{
			$AVE_Logs->logExport();
		}
		break;

	case 'export404':
		if (check_permission_acp('logs_view'))
		{
			$AVE_Logs->Export404();
		}
		break;

	case 'exportsql':
		if (check_permission_acp('logs_view'))
		{
			$AVE_Logs->ExportSql();
		}
		break;
}

?>