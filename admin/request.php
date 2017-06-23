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

	require(BASE_DIR . "/class/class.request.php");
	require(BASE_DIR . "/class/class.docs.php");
	require(BASE_DIR . "/class/class.rubs.php");

	$AVE_Request = new AVE_Request;
	$AVE_Document = new AVE_Document;
	$AVE_Rubric = new AVE_Rubric;

	$AVE_Rubric->rubricPermissionFetch();

	$AVE_Template->config_load(BASE_DIR . "/admin/lang/" . $_SESSION['admin_language'] . "/request.txt", 'request');

	switch ($_REQUEST['action'])
	{
		case '':
			if(check_permission_acp('request_view'))
			{
				$AVE_Rubric->rubricTemplateShow(0, 1);
				$AVE_Request->requestListShow();
			}
			break;

		case 'edit':
			if(check_permission_acp('request_edit'))
			{
				$AVE_Rubric->rubricTemplateShow(0, 1);
				$AVE_Request->requestEdit((int)$_REQUEST['Id']);
			}
			break;

		case 'copy':
			if(check_permission_acp('request_edit'))
			{
				$AVE_Request->requestCopy((int)$_REQUEST['Id']);
			}
			break;

		case 'new':
			if(check_permission_acp('request_edit'))
			{
				$AVE_Rubric->rubricTemplateShow(0, 1);
				$AVE_Request->requestNew();
			}
			break;

		case 'delete_query':
			if(check_permission_acp('request_edit'))
			{
				$AVE_Request->requestDelete((int)$_REQUEST['Id']);
			}
			break;

		case 'conditions':
			if(check_permission_acp('request_edit'))
			{
				$AVE_Rubric->rubricTemplateShow(0, 1);
				$AVE_Request->requestConditionEdit((int)$_REQUEST['Id']);
			}
			break;

		case 'change':
			if(check_permission_acp('request_edit'))
			{
				switch($_REQUEST['sub'])
				{
					case '':
						$AVE_Rubric->rubricTemplateShow(0, 1);
						$AVE_Request->conditionFieldChange((int)$_REQUEST['field_id'], (int)$_REQUEST['cond_id']);
					break;

					case 'save':
						$AVE_Rubric->rubricTemplateShow(0, 1);
						$AVE_Request->conditionFieldChangeSave((int)$_REQUEST['field_id'], (int)$_REQUEST['cond_id']);
					break;
				}
			}
			break;

		case 'alias':
			if (check_permission_acp('request_edit'))
			{
				echo $AVE_Request->requestValidate($_REQUEST['alias'], (int)$_REQUEST['id']);
			}
			exit;
	}
?>