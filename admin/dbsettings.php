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

check_permission_acp('db_actions');

$AVE_Template->config_load(BASE_DIR . '/admin/lang/' . $_SESSION['admin_language'] . '/dbactions.txt', 'db');

require(BASE_DIR . '/class/class.dbdump.php');
$AVE_DB_Service = new AVE_DB_Service;

if (!empty($_REQUEST['action']))
{
	switch ($_REQUEST['action'])
	{
		case 'optimize':
			$AVE_DB_Service->databaseTableOptimize();
			break;

		case 'repair':
			$AVE_DB_Service->databaseTableRepair();
			break;

		case 'dump_top':
			$AVE_DB_Service->databaseDumpExport(1);
			exit;

		case 'dump':
			$AVE_DB_Service->databaseDumpExport();
			exit;

		case 'restore':
			$AVE_DB_Service->databaseDumpImport(BASE_DIR . "/" . ATTACH_DIR . "/");
			break;

		case 'download':
			$AVE_DB_Service->databaseDumpFileSave($_REQUEST['file']);
			break;

		case 'restorefile':
			$AVE_DB_Service->databaseDumpFileImport($_REQUEST['file']);
			break;

		case 'deletefile':
			$AVE_DB_Service->databaseDumpFileDelete($_REQUEST['file']);
			break;
	}
}

$AVE_Template->assign('db_size', get_mysql_size());
$AVE_Template->assign('files', $AVE_DB_Service->databaseFilesGet());
$AVE_Template->assign('tables', $AVE_DB_Service->databaseTableGet());
$AVE_Template->assign('content', $AVE_Template->fetch('dbactions/actions.tpl'));

?>