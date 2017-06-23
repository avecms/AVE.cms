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

$AVE_Template->config_load(BASE_DIR . '/admin/lang/' . $_SESSION['admin_language'] . '/groups.txt', 'groups');

switch ($_REQUEST['action'])
{
	case '':
		if (check_permission_acp('group_view'))
		{
			$AVE_User->userGroupListShow();
		}
		break;

	case 'grouprights':
		if (check_permission_acp('group_edit'))
		{
			switch ($_REQUEST['sub'])
			{
				case '':
					$AVE_User->userGroupPermissionEdit($_REQUEST['Id']);
					break;

				case 'save':
					$AVE_User->userGroupPermissionSave($_REQUEST['Id']);
					break;
			}
		}
		break;

	case 'new':
		if (check_permission_acp('group_edit'))
		{
			$AVE_User->userGroupNew();
		}
		break;

	case 'delete':
		if (check_permission_acp('group_edit'))
		{
			$AVE_User->userGroupDelete($_REQUEST['Id']);
		}
		break;
}

?>