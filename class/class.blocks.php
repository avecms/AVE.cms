<?php

	/**
	 * AVE.cms
	 *
	 * @package AVE.cms
	 * @version 3.x
	 * @filesource
	 * @copyright © 2007-2017 AVE.cms, http://www.ave-cms.ru
	 *
	 */

	class AVE_Block
	{
		/**
		 * Проверка алиаса тега на валидность и уникальность
		 */
		function blockValidate ($alias = '', $id = 0)
		{
			global $AVE_DB;

			//-- Соответствие требованиям
			if (empty ($alias) || preg_match('/^[A-Za-z0-9-_]{1,20}$/i', $alias) !== 1 || is_numeric($alias))
				return 'syn';

			//-- Уникальность
			return !(bool)$AVE_DB->Query("
				SELECT 1
				FROM
					" . PREFIX . "_blocks
				WHERE
					block_alias = '" . $alias . "'
				AND
					id != '" . $id . "'
			")->GetCell();
		}

		/**
		 * Вывод списка системных блоков
		 */
		function blockList()
		{
			global $AVE_DB, $AVE_Template;

			$vis_blocks = array();

			$sql = $AVE_DB->Query("
				SELECT
					*
				FROM
					" . PREFIX . "_blocks
				ORDER BY
					id
			");

			// Формируем массив из полученных данных
			while ($row = $sql->FetchRow())
			{
				$row->block_author_id = get_username_by_id($row->block_author_id);
				array_push($vis_blocks, $row);
			}

			$AVE_Template->assign('sid', 0);
			$AVE_Template->assign('vis_blocks', $vis_blocks);
			$AVE_Template->assign('content', $AVE_Template->fetch('blocks/list.tpl'));
		}

		/**
		 * Сохранение системного блока
		 *
		 * @param int $block_id идентификатор системного блока
		 */
		function blockSave($block_id = null)
		{
			global $AVE_DB, $AVE_Template;

			if (is_numeric($block_id))
			{
				$_REQUEST['block_visual'] = (isset($_REQUEST['block_visual'])) ? $_REQUEST['block_visual'] : 0;

				$block_alias = isset($_REQUEST['block_alias']) ? $_REQUEST['block_alias'] : '';

				$sql = $AVE_DB->Query("
					UPDATE
						" . PREFIX . "_blocks
					SET
						block_name			 = '" . $_REQUEST['block_name'] . "',
						block_description	 = '" . addslashes($_REQUEST['block_description']) . "',
						block_alias			 = '" . $_REQUEST['block_alias'] . "',
						block_text			 = '" . $_REQUEST['block_text'] . "'
					WHERE
						id = '" . $block_id . "'
				");

				if ($sql->_result === false)
				{
					$message = $AVE_Template->get_config_vars('BLOCK_SAVED_ERR');
					$header = $AVE_Template->get_config_vars('BLOCK_ERROR');
					$theme = 'error';
				}
				else
					{
						$message = $AVE_Template->get_config_vars('BLOCK_SAVED');
						$header = $AVE_Template->get_config_vars('BLOCK_SUCCESS');
						$theme = 'accept';

						//-- Стираем кеш блока
						if (file_exists(BASE_DIR . '/cache/sql/block/' . $block_id . '.cache'))
							unlink(BASE_DIR . '/cache/sql/block/' . $block_id . '.cache');

						if ($block_alias != '' && file_exists(BASE_DIR . '/cache/sql/block/' . $block_alias . '.cache'))
							unlink(BASE_DIR . '/cache/sql/block/' . $block_alias . '.cache');

						//-- Сохраняем системное сообщение в журнал
						reportLog($AVE_Template->get_config_vars('BLOCK_SQLUPDATE') . " (" . stripslashes($_REQUEST['block_name']) . ") (id: $block_id)");
					}

				if (isAjax())
				{
					echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
				}
				else
					{
						$AVE_Template->assign('message', $message);
						header('Location:index.php?do=blocks&cp=' . SESSION);
					}

				exit;
			}
			else
				{
					$AVE_DB->Query("
						INSERT INTO
							" . PREFIX . "_blocks
						SET
							block_name			= '" . $_REQUEST['block_name'] . "',
							block_description	= '" . addslashes($_REQUEST['block_description']) . "',
							block_alias			= '" . $_REQUEST['block_alias'] . "',
							block_text			= '" . $_REQUEST['block_text'] . "',
							block_author_id		= '" . (int)$_SESSION['user_id'] . "',
							block_created		= '" . time() . "'
					");

					$block_id = $AVE_DB->InsertId();

					//-- Сохраняем системное сообщение в журнал
					reportLog($AVE_Template->get_config_vars('BLOCK_SQLNEW') . " (" . stripslashes($_REQUEST['block_name']) . ") (id: $block_id)");
				}

			if (! isset($_REQUEST['next_edit']))
				header('Location:index.php?do=blocks&cp=' . SESSION);
			else
				header('Location:index.php?do=blocks&action=edit&&id=' . $block_id . '&cp=' . SESSION);
		}

		/**
		 * Редактирование системного блока
		 *
		 * @param int $block_id идентификатор системного блока
		 */
		function blockEdit($block_id)
		{
			global $AVE_DB, $AVE_Template;

			$row = $AVE_DB->Query("
				SELECT
					*
				FROM
					" . PREFIX . "_blocks
				WHERE
					id = '" . $block_id . "'
			")->FetchAssocArray();

			$AVE_Template->assign('sid', $block_id);

			switch ($_SESSION['use_editor'])
			{
				case '0': // CKEditor
				case '1':
					$oCKeditor = new CKeditor();
					$oCKeditor->returnOutput = true;
					$oCKeditor->config['customConfig'] = 'block.js';
					$oCKeditor->config['toolbar'] = 'Big';
					$oCKeditor->config['height'] = 400;
					$config = array();
					$row['block_text'] = $oCKeditor->editor('block_text', $row['block_text'], $config);
					break;
			}

			$AVE_Template->assign($row);
			$AVE_Template->assign('content', $AVE_Template->fetch('blocks/form.tpl'));
		}

		/**
		 * Создание блока
		 */
		function blockNew()
		{
			global $AVE_DB, $AVE_Template;

			$row['block_name'] = '';
			$row['block_alias'] = '';
			$row['block_text'] = '';

			$AVE_Template->assign('sid', 0);

			switch ($_SESSION['use_editor'])
			{
				case '0': // CKEditor
				case '1':
					$oCKeditor = new CKeditor();
					$oCKeditor->returnOutput = true;
					$oCKeditor->config['customConfig'] = 'block.js';
					$oCKeditor->config['toolbar'] = 'Big';
					$oCKeditor->config['height'] = 400;
					$config = array();
					$row['block_text'] = $oCKeditor->editor('block_text', $row['block_text'], $config);
					break;
			}

			$AVE_Template->assign($row);
			$AVE_Template->assign('content', $AVE_Template->fetch('blocks/form.tpl'));
		}

		/**
		 * Удаление блока
		 *
		 * @param int $block_id идентификатор блока
		 */
		function blockDelete($block_id)
		{
			global $AVE_DB, $AVE_Template;

			if (is_numeric($block_id))
			{
				$row = $AVE_DB->Query("
					SELECT
						*
					FROM
						" . PREFIX . "_blocks
					WHERE
						id = '" . $block_id . "'
				")->FetchRow();

				$AVE_DB->Query("
					DELETE FROM
						" . PREFIX . "_blocks
					WHERE
						id = '" . $block_id . "'
				");

				//-- Стираем кеш сисблока
				if (file_exists(BASE_DIR . '/cache/sql/block-' . $block_id . '.cache'))
					unlink(BASE_DIR . '/cache/sql/block/' . $block_id . '.cache');

				if ($row->block_alias != '')
					unlink(BASE_DIR . '/cache/sql/block/' . $row->block_alias . '.cache');

				//-- Сохраняем системное сообщение в журнал
				reportLog($AVE_Template->get_config_vars('BLOCK_SQLDEL') . " (" . stripslashes($row->block_name) . ") (id: $block_id)");
			}

			header('Location:index.php?do=blocks&cp=' . SESSION);
		}
	}
?>