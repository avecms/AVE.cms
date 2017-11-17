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

	class AVE_SysBlock
	{
		/**
		 * Проверка алиаса тега на валидность и уникальность
		 */
		function sys_blockValidate ($alias = '', $id = 0)
		{
			global $AVE_DB;

			//-- Соответствие требованиям
			if (empty ($alias) || preg_match('/^[A-Za-z0-9-_]{1,20}$/i', $alias) !== 1 || is_numeric($alias))
				return 'syn';

			//-- Уникальность
			return !(bool)$AVE_DB->Query("
				SELECT 1
				FROM
					" . PREFIX . "_sysblocks
				WHERE
					sysblock_alias = '" . $alias . "'
				AND
					id != '" . $id . "'
			")->GetCell();
		}

		/**
		 * Вывод списка системных блоков
		 */
		function sys_blockList()
		{
			global $AVE_DB, $AVE_Template;

			$sys_blocks = array();

			$sql = $AVE_DB->Query("
				SELECT
					*
				FROM
					" . PREFIX . "_sysblocks
				ORDER BY
					id
			");

			// Формируем массив из полученных данных
			while ($row = $sql->FetchRow())
			{
				$row->sysblock_author_id = get_username_by_id($row->sysblock_author_id);
				array_push($sys_blocks, $row);
			}

			$AVE_Template->assign('sid', 0);
			$AVE_Template->assign('sys_blocks', $sys_blocks);
			$AVE_Template->assign('content', $AVE_Template->fetch('sysblocks/list.tpl'));
		}

		/**
		 * Сохранение системного блока
		 *
		 * @param int $sysblock_id идентификатор системного блока
		 */
		function sys_blockSave($sysblock_id = null)
		{
			global $AVE_DB, $AVE_Template;

			if (is_numeric($sysblock_id))
			{

				$_REQUEST['sysblock_external'] = (isset($_REQUEST['sysblock_external'])) ? $_REQUEST['sysblock_external'] : 0;
				$_REQUEST['sysblock_ajax'] = (isset($_REQUEST['sysblock_ajax'])) ? $_REQUEST['sysblock_ajax'] : 0;
				$_REQUEST['sysblock_visual'] = (isset($_REQUEST['sysblock_visual'])) ? $_REQUEST['sysblock_visual'] : 0;

				$sysblock_alias = isset($_REQUEST['sysblock_alias']) ? $_REQUEST['sysblock_alias'] : '';

				$sql = $AVE_DB->Query("
					UPDATE
						" . PREFIX . "_sysblocks
					SET
						sysblock_name			 = '" . $_REQUEST['sysblock_name'] . "',
						sysblock_description	 = '" . addslashes($_REQUEST['sysblock_description']) . "',
						sysblock_alias			 = '" . $_REQUEST['sysblock_alias'] . "',
						sysblock_text			 = '" . $_REQUEST['sysblock_text'] . "',
						sysblock_external		 = '" . (int)$_REQUEST['sysblock_external'] . "',
						sysblock_ajax			 = '" . (int)$_REQUEST['sysblock_ajax'] . "',
						sysblock_visual			 = '" . (int)$_REQUEST['sysblock_visual'] . "'
					WHERE
						id = '" . $sysblock_id . "'
				");

				if ($sql->_result === false)
				{
					$message = $AVE_Template->get_config_vars('SYSBLOCK_SAVED_ERR');
					$header = $AVE_Template->get_config_vars('SYSBLOCK_ERROR');
					$theme = 'error';
				}
				else
					{
						$message = $AVE_Template->get_config_vars('SYSBLOCK_SAVED');
						$header = $AVE_Template->get_config_vars('SYSBLOCK_SUCCESS');
						$theme = 'accept';

						//-- Стираем кеш сисблока
						if (file_exists(BASE_DIR . '/cache/sql/sysblock/' . $sysblock_id . '.cache'))
							unlink(BASE_DIR . '/cache/sql/sysblock/' . $sysblock_id . '.cache');

						if ($sysblock_alias != '' && file_exists(BASE_DIR . '/cache/sql/sysblock/' . $sysblock_alias . '.cache'))
							unlink(BASE_DIR . '/cache/sql/sysblock/' . $sysblock_alias . '.cache');

						//-- Сохраняем системное сообщение в журнал
						reportLog($AVE_Template->get_config_vars('SYSBLOCK_SQLUPDATE') . " (" . stripslashes($_REQUEST['sysblock_name']) . ") (id: $sysblock_id)");
					}

				if (isAjax())
				{
					echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
				}
				else
					{
						$AVE_Template->assign('message', $message);
						header('Location:index.php?do=sysblocks&cp=' . SESSION);
					}

				exit;
			}
			else
				{
					$AVE_DB->Query("
						INSERT INTO
							" . PREFIX . "_sysblocks
						SET
							sysblock_name			= '" . $_REQUEST['sysblock_name'] . "',
							sysblock_description	= '" . addslashes($_REQUEST['sysblock_description']) . "',
							sysblock_alias			= '" . $_REQUEST['sysblock_alias'] . "',
							sysblock_text			= '" . $_REQUEST['sysblock_text'] . "',
							sysblock_author_id		= '" . (int)$_SESSION['user_id'] . "',
							sysblock_external		= '" . (int)$_REQUEST['sysblock_external'] . "',
							sysblock_ajax			= '" . (int)$_REQUEST['sysblock_ajax'] . "',
							sysblock_visual			= '" . (int)$_REQUEST['sysblock_visual'] . "',
							sysblock_created		= '" . time() . "'
					");

					$sysblock_id = $AVE_DB->InsertId();

					//-- Сохраняем системное сообщение в журнал
					reportLog($AVE_Template->get_config_vars('SYSBLOCK_SQLNEW') . " (" . stripslashes($_REQUEST['sysblock_name']) . ") (id: $sysblock_id)");
				}

			if (! isset($_REQUEST['next_edit']))
				header('Location:index.php?do=sysblocks&cp=' . SESSION);
			else
				header('Location:index.php?do=sysblocks&action=edit&&id=' . $sysblock_id . '&cp=' . SESSION);
		}

		/**
		 * Редактирование системного блока
		 *
		 * @param int $sysblock_id идентификатор системного блока
		 */
		function sys_blockEdit($sysblock_id)
		{
			global $AVE_DB, $AVE_Template;

			$row = $AVE_DB->Query("
				SELECT
					*
				FROM
					" . PREFIX . "_sysblocks
				WHERE
					id = '" . $sysblock_id . "'
			")->FetchAssocArray();

			$AVE_Template->assign('sid', $sysblock_id);

			if ((isset($_REQUEST['sysblock_visual']) && $_REQUEST['sysblock_visual'] == 1) ||  $row['sysblock_visual'] == 1)
			{
				switch ($_SESSION['use_editor'])
				{
					case '0': // CKEditor
					case '1':
						$oCKeditor = new CKeditor();
						$oCKeditor->returnOutput = true;
						$oCKeditor->config['customConfig'] = 'sysblock.js';
						$oCKeditor->config['toolbar'] = 'Big';
						$oCKeditor->config['height'] = 400;
						$config = array();
						$row['sysblock_text'] = $oCKeditor->editor('sysblock_text', $row['sysblock_text'], $config);
						break;
				}
				$AVE_Template->assign($row);
				$AVE_Template->assign('content', $AVE_Template->fetch('sysblocks/form_visual.tpl'));
			}
			else
				{
					$AVE_Template->assign($row);
					$AVE_Template->assign('content', $AVE_Template->fetch('sysblocks/form.tpl'));
				}
		}

		/**
		 * Создание системного блока
		 */
		function sys_blockNew()
		{
			global $AVE_DB, $AVE_Template;

			$row['sysblock_name'] = '';
			$row['sysblock_alias'] = '';
			$row['sysblock_text'] = '';
			$row['sysblock_visual'] = (isset($_REQUEST['sysblock_visual']) && $_REQUEST['sysblock_visual'] != 0) ? $_REQUEST['sysblock_visual'] : '';

			$AVE_Template->assign('sid', 0);

			if ((isset($_REQUEST['sysblock_visual']) && $_REQUEST['sysblock_visual'] == 1) ||  $row['sysblock_visual'] == 1)
			{
				switch ($_SESSION['use_editor'])
				{
					case '0': // CKEditor
					case '1':
						$oCKeditor = new CKeditor();
						$oCKeditor->returnOutput = true;
						$oCKeditor->config['customConfig'] = 'sysblock.js';
						$oCKeditor->config['toolbar'] = 'Big';
						$oCKeditor->config['height'] = 400;
						$config = array();
						$row['sysblock_text'] = $oCKeditor->editor('sysblock_text', $row['sysblock_text'], $config);
						break;
				}

				$AVE_Template->assign($row);
				$AVE_Template->assign('content', $AVE_Template->fetch('sysblocks/form_visual.tpl'));
			}
			else
				{
					$AVE_Template->assign($row);
					$AVE_Template->assign('content', $AVE_Template->fetch('sysblocks/form.tpl'));
				}
		}

		/**
		 * Удаление системного блока
		 *
		 * @param int $sysblock_id идентификатор системного блока
		 */
		function sys_blockDelete($sysblock_id)
		{
			global $AVE_DB, $AVE_Template;

			if (is_numeric($sysblock_id))
			{
				$row = $AVE_DB->Query("
					SELECT
						*
					FROM
						" . PREFIX . "_sysblocks
					WHERE
						id = '" . $sysblock_id . "'
				")->FetchRow();

				$AVE_DB->Query("
					DELETE FROM
						" . PREFIX . "_sysblocks
					WHERE
						id = '" . $sysblock_id . "'
				");

				//-- Стираем кеш сисблока
				if (file_exists(BASE_DIR . '/cache/sql/sysblock-' . $sysblock_id . '.cache'))
					unlink(BASE_DIR . '/cache/sql/sysblock/' . $sysblock_id . '.cache');

				if ($row->sysblock_alias != '')
					unlink(BASE_DIR . '/cache/sql/sysblock/' . $row->sysblock_alias . '.cache');

				//-- Сохраняем системное сообщение в журнал
				reportLog($AVE_Template->get_config_vars('SYSBLOCK_SQLDEL') . " (" . stripslashes($row->sysblock_name) . ") (id: $sysblock_id)");
			}

			header('Location:index.php?do=sysblocks&cp=' . SESSION);
		}
	}
?>