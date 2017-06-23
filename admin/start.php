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

get_ave_info();
get_editable_module();
DisplayMainDocuments();
get_online_users();
getLogRecords();

//$AVE_Template->config_load(BASE_DIR . '/admin/lang/' . $_SESSION['admin_language'] . '/main.txt', 'index');
$AVE_Template->assign('php_version', (@PHP_VERSION != '') ? @PHP_VERSION : 'unknow');
$AVE_Template->assign('domain', $_SERVER["HTTP_HOST"]);
$AVE_Template->assign('mysql_version', $GLOBALS['AVE_DB']->mysql_version());
$AVE_Template->assign('mysql_size', get_mysql_size());
$AVE_Template->assign('navi', $AVE_Template->fetch('navi/navi.tpl'));
$AVE_Template->assign('navi_top', $AVE_Template->fetch('navi/navi_top.tpl'));
$AVE_Template->assign('content', $AVE_Template->fetch('start.tpl'));

?>