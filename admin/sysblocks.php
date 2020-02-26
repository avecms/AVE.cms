<?php

	/**
	 * AVE.cms
	 *
	 * @package AVE.cms
	 * @version 3.x
	 * @filesource
	 * @copyright © 2007-2017 AVE.cms, http://www.ave-cms.ru
	 *
	 * @license GPL v.2
	 */

	if (! defined('ACP'))
	{
		header('Location:index.php');
		exit;
	}

	global $AVE_DB, $AVE_Template;

	require(BASE_DIR . '/class/class.sysblocks.php');

	new Sysblocks;

	$AVE_Template->config_load(BASE_DIR . '/admin/lang/' . $_SESSION['admin_language'] . '/sysblocks.txt', 'sysblocks');

	switch ($_REQUEST['action'])
	{
		// Список системных блоков
		case '':
			if (check_permission_acp('sysblocks_view'))
			{
				Sysblocks::startPage();
			}
			break;

		// Список групп системных блоков
		case 'groups':
			if (check_permission_acp('sysblocks_view'))
			{
				Sysblocks::listGroups();
			}
			break;

		// Сортировка списока групп
		case 'groupssort':
			if (check_permission_acp('sysblocks_edit'))
			{
				Sysblocks::groupsSort();
			}
			break;

		// Новая группа
		case 'newgroup':
			if (check_permission_acp('sysblocks_edit'))
			{
				Sysblocks::newGroup();
			}
			break;

		// Удаление группы
		case 'delgroup':
			if (check_permission_acp('sysblocks_edit'))
			{
				Sysblocks::delGroup();
			}
			break;

		// Создать новый системный блок
		case 'new':
			if (check_permission_acp('sysblocks_edit'))
			{
				Sysblocks::newBlock();
			}
			break;

		// Редактировать системный блок
		case 'edit':
			if (check_permission_acp('sysblocks_edit'))
			{
				Sysblocks::editBlock();
			}
			break;

		// Сохранить системный блок
		case 'save':
			if (check_permission_acp('sysblocks_edit'))
			{
				Sysblocks::saveBlock();
			}
			break;

		// Удалить системный блок
		case 'del':
			if (check_permission_acp('sysblocks_edit'))
			{
				Sysblocks::delBlock();
			}
			break;

		// Проверка алиаса
		case 'alias':
			if (check_permission_acp('sysblocks_edit'))
			{
				echo Sysblocks::aliasValidate($_REQUEST['alias'], (int)$_REQUEST['id']);
			}
			exit;

		// Копирование системного блока
		case 'multi':
			if (check_permission_acp('sysblocks_edit'))
			{
				Sysblocks::multiBlock();
			}
	}
?>