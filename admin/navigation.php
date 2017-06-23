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

	require(BASE_DIR . '/class/class.navigation.php');
	$AVE_Navigation = new AVE_Navigation;

	$AVE_Template->config_load(BASE_DIR . '/admin/lang/' . $_SESSION['admin_language'] . '/navigation.txt', 'navi');

	switch ($_REQUEST['action'])
	{
		case '':
			if (check_permission_acp('navigation_view'))
			{
				$AVE_Navigation->navigationList();
			}
			break;

		case 'new':
			if (check_permission_acp('navigation_edit'))
			{
				require(BASE_DIR . '/class/class.user.php');
				$AVE_User = new AVE_User;
				$AVE_Navigation->navigationNew();
			}
			break;

		case 'copy':
			if (check_permission_acp('navigation_edit'))
			{
				$AVE_Navigation->navigationCopy((int)$_REQUEST['navigation_id']);
			}
			break;

		case 'delete':
			if (check_permission_acp('navigation_edit'))
			{
				$AVE_Navigation->navigationDelete((int)$_REQUEST['navigation_id']);
			}
			break;

		case 'templates':
			if (check_permission_acp('navigation_edit'))
			{
				require(BASE_DIR . '/class/class.user.php');
				$AVE_User = new AVE_User;
				$AVE_Navigation->navigationEdit((int)$_REQUEST['navigation_id']);
			}
			break;

		case 'entries':
			if (check_permission_acp('navigation_edit'))
			{
				$AVE_Navigation->navigationItemList((int)$_REQUEST['navigation_id']);
			}
			break;

		case 'sorting':
			if (check_permission_acp('navigation_edit'))
			{
				$AVE_Navigation->navigationSort((int)$_REQUEST['navigation_id']);
			}
			break;

		case 'itemedit':
			if (check_permission_acp('navigation_edit'))
			{
				$AVE_Navigation->navigationItemEdit((int)$_REQUEST['navigation_item_id']);
			}
			break;

		case 'itemeditid':
			if (check_permission_acp('navigation_edit'))
			{
				$AVE_Navigation->getDocumentById((int)$_REQUEST['doc_id']);
			}
			break;

		case 'saveitem':
			if (check_permission_acp('navigation_edit'))
			{
				$AVE_Navigation->navigationItemSave((int)$_REQUEST['navigation_item_id']);
			}
			break;

		case 'itemnew':
			if (check_permission_acp('navigation_edit'))
			{
				$AVE_Navigation->navigationItemNew((int)$_REQUEST['navigation_id']);
			}
			break;

		case 'itemestatus':
			if (check_permission_acp('navigation_edit'))
			{
				$AVE_Navigation->navigationItemStatus((int)$_REQUEST['navigation_item_id'], $_REQUEST['status']);
			}
			break;

		case 'getitem':
			if (check_permission_acp('navigation_edit'))
			{
				$AVE_Navigation->navigationItemGet((int)$_REQUEST['navigation_item_id']);
			}
			break;

		case 'itemdelete':
			if (check_permission_acp('navigation_edit'))
			{
				$AVE_Navigation->navigationItemDelete((int)$_REQUEST['navigation_item_id']);
			}
			break;

		case 'alias':
			if (check_permission_acp('navigation_edit'))
			{
				echo $AVE_Navigation->navigationValidate($_REQUEST['alias'], (int)$_REQUEST['id']);
			}
			exit;
	}
?>
