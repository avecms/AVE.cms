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

	if (! defined('ACP'))
	{
		header('Location:index.php');
		exit;
	}

	global $AVE_DB, $AVE_Template;

	require(BASE_DIR . '/class/class.templates.php');
	$AVE_Templates = new AVE_Templates;

	$AVE_Template->config_load(BASE_DIR . '/admin/lang/' . $_SESSION['admin_language'] . '/templates.txt');

	switch ($_REQUEST['action'])
	{
		case '':
			if (check_permission_acp('template_view'))
			{
				AVE_Templates::templatesList();
			}
			break;


		case 'new':
			if (check_permission_acp('template_edit'))
			{
				AVE_Templates::templatesNew();
			}
			break;


		case 'edit':
			if (check_permission_acp('template_edit'))
			{
				AVE_Templates::templatesEdit();
			}
			break;


		case 'save':
			if (check_permission_acp('template_edit'))
			{
				AVE_Templates::templatesSave();
			}
			break;


		case 'delete':
			if (check_permission_acp('template_edit'))
			{
				AVE_Templates::templatesDelete();
			}
			break;


		case 'multi':
			if (check_permission_acp('template_edit'))
			{
				AVE_Templates::templatesMulti();
			}
			break;


		case 'edit_css':
			if (check_permission_acp('template_edit'))
			{
				AVE_Templates::templatesEditCss();
			}
			break;

		case 'edit_js':
			if (check_permission_acp('template_edit'))
			{
				AVE_Templates::templatesEditJs();
			}
			break;
	}
?>