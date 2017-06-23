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

global $AVE_Template;

require(BASE_DIR . '/class/class.user.php');
$AVE_User = new AVE_User;

$AVE_User->userListFetch();

$AVE_Template->config_load(BASE_DIR . '/admin/lang/' . $_SESSION['admin_language'] . '/user.txt', 'user');

switch ($_REQUEST['action'])
{
	case '':
		if (check_permission_acp('user_view'))
		{
			$AVE_Template->assign('content', $AVE_Template->fetch('user/users.tpl'));
		}
		break;

	case 'edit':
		if (check_permission_acp('user_edit'))
		{
			$AVE_User->userEdit($_REQUEST['Id']);
		}
		break;

	case 'new':
		if (check_permission_acp('user_edit'))
		{
			$AVE_User->userNew();
		}
		break;

	case 'delete':
		if (check_permission_acp('user_edit'))
		{
			$AVE_User->userDelete($_REQUEST['Id']);
		}
		break;

	case 'quicksave':
		if (check_permission_acp('user_edit'))
		{
			$AVE_User->userListEdit();
		}
		break;
}

?>