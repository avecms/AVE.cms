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

	require(BASE_DIR . '/class/class.blocks.php');

	$AVE_Block = new AVE_Block;

	$AVE_Template->config_load(BASE_DIR . '/admin/lang/' . $_SESSION['admin_language'] . '/blocks.txt', 'blocks');

	switch ($_REQUEST['action'])
	{
		case '':
			if (check_permission_acp('blocks_view'))
			{
				$AVE_Block->blockList();
			}
			break;

		case 'new':
			if (check_permission_acp('blocks_edit'))
			{
				$_SESSION['use_editor'] = get_settings('use_editor');
				$AVE_Block->blockNew();
			}
			break;

		case 'edit':
			if (check_permission_acp('blocks_edit'))
			{
				$_SESSION['use_editor'] = get_settings('use_editor');
				$AVE_Block->blockEdit(isset($_REQUEST['id']) ? $_REQUEST['id'] : null);
			}
			break;

		case 'save':
			if (check_permission_acp('blocks_edit'))
			{
				$AVE_Block->blockSave(isset($_REQUEST['id']) ? $_REQUEST['id'] : null);
			}
			break;

		case 'del':
			if (check_permission_acp('blocks_edit'))
			{
				$AVE_Block->blockDelete($_REQUEST['id']);
			}
			break;

		case 'alias':
			if (check_permission_acp('blocks_edit'))
			{
				echo $AVE_Block->blockValidate($_REQUEST['alias'], (int)$_REQUEST['id']);
			}
			exit;

		case 'multi':
			if (check_permission_acp('blocks_edit'))
			{
				$_REQUEST['sub'] = (!isset($_REQUEST['sub'])) ? '' : $_REQUEST['sub'];
				$errors = array();
				switch ($_REQUEST['sub'])
				{
					case 'save':
						$ok = true;
						$row = $AVE_DB->Query("
							SELECT block_name
							FROM " . PREFIX . "_blocks
							WHERE block_name = '" . $_REQUEST['block_name'] . "'
						")->FetchRow();

						if (@$row->block_name != '')
						{
							array_push($errors, $AVE_Template->get_config_vars('BLOCK_EXIST'));
							$AVE_Template->assign('errors', $errors);
							$ok = false;
						}

						if ($_REQUEST['block_name'] == '')
						{
							array_push($errors, $AVE_Template->get_config_vars('BLOCK_COPY_TIP'));
							$AVE_Template->assign('errors', $errors);
							$ok = false;
						}

						if ($ok)
						{
							$row = $AVE_DB->Query("
								SELECT block_text
								FROM " . PREFIX . "_blocks
								WHERE id = '" . (int)$_REQUEST['id'] . "'
							")->FetchRow();

							$AVE_DB->Query("
								INSERT
								INTO " . PREFIX . "_blocks
								SET
									Id = '',
									block_name      = '" . $_REQUEST['block_name'] . "',
									block_text      = '" . addslashes($row->block_text) . "',
									block_author_id = '" . $_SESSION['user_id'] . "',
									block_created   = '" . time() . "'
							");

							reportLog($_SESSION['user_name'] . ' - создал копию блока (' . (int)$_REQUEST['id'] . ')', 2, 2);

							header('Location:index.php?do=blocks'.'&cp=' . SESSION);
						}

						$AVE_Template->assign('content', $AVE_Template->fetch('blocks/multi.tpl'));

						break;
				}
			}
	}
?>