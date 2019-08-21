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

	class Sysblocks
	{
		/*
		|--------------------------------------------------------------------------------------
		| aliasValidate
		|--------------------------------------------------------------------------------------
		|
		| Проверка алиаса на валидность и уникальность
		|
		*/
		public static function aliasValidate ($alias = '', $id = 0)
		{
			global $AVE_DB;

			//-- Соответствие требованиям
			if (empty ($alias) || preg_match('/^[A-Za-z0-9-_]{1,20}$/i', $alias) !== 1 || is_numeric($alias))
				return 'syn';

			$sql = "
				SELECT
					1
				FROM
					" . PREFIX . "_sysblocks
				WHERE
					sysblock_alias = '" . $alias . "'
					AND
					id != '" . $id . "'
			";

			//-- Уникальность
			return !(bool)$AVE_DB->Query($sql)->GetCell();
		}


		/*
		|--------------------------------------------------------------------------------------
		| startPage
		|--------------------------------------------------------------------------------------
		|
		| Гланая страница
		|
		*/
		public static function startPage()
		{
			global $AVE_DB, $AVE_Template;

			//-- Группы
			$groups = [];

			$sql = "
				SELECT
					*
				FROM
					" . PREFIX . "_sysblocks_groups
				ORDER BY
					position ASC
			";

			$query = $AVE_DB->Query($sql);

			while ($row = $query->FetchAssocArray())
			{
				$row['count'] = 0;
				$groups[$row['id']] = $row;
			}

			//-- Блоки
			$sysblocks = [];

			$sql = "
				SELECT
					a.*,
				    a.sysblock_group_id
				FROM
					" . PREFIX . "_sysblocks AS a
				LEFT JOIN
					" . PREFIX . "_sysblocks_groups AS b
					ON a.sysblock_group_id = b.id
				ORDER BY
					b.position ASC, a.id ASC
			";

			$query = $AVE_DB->Query($sql);

			while ($row = $query->FetchAssocArray())
			{
				$row['author'] = get_username_by_id($row['sysblock_author_id']);
				$sysblocks[$row['sysblock_group_id']][] = $row;
			}

			foreach ($sysblocks AS $_k => $_v)
			{
				if ($_k == 0)
				{
					$groups[$_k]['position'] = 0;
					$groups[$_k]['title'] = 'Без группы';
					$groups[$_k]['description'] = 'Описание отсутсвует';
				}

				$groups[$_k]['count'] = count($sysblocks[$_k]);
			}

//Debug::_echo($sysblocks, true);
//Debug::_echo($groups, true);
			$AVE_Template->assign('groups', $groups);
			$AVE_Template->assign('sysblocks', $sysblocks);
			$AVE_Template->assign('content', $AVE_Template->fetch('sysblocks/start.tpl'));
		}


		/*
		|--------------------------------------------------------------------------------------
		| listBlocks
		|--------------------------------------------------------------------------------------
		|
		| Список системных блоков
		|
		*/
		public static function listBlocks()
		{
			global $AVE_DB, $AVE_Template;

			$sysblocks = [];

			$sql = "
				SELECT
					*
				FROM
					" . PREFIX . "_sysblocks
				ORDER BY
					id
			";

			$query = $AVE_DB->Query($sql);

			// Формируем массив из полученных данных
			while ($row = $query->FetchRow())
			{
				$row->sysblock_author_id = get_username_by_id($row->sysblock_author_id);
				array_push($sys_blocks, $row);
			}

			$AVE_Template->assign('sid', 0);
			$AVE_Template->assign('sysblocks', $sysblocks);
			$AVE_Template->assign('content', $AVE_Template->fetch('sysblocks/list.tpl'));
		}


		/*
		|--------------------------------------------------------------------------------------
		| listGroups
		|--------------------------------------------------------------------------------------
		|
		| Список групп системных блоков
		|
		*/
		public static function listGroups()
		{
			global $AVE_DB, $AVE_Template;

			$groups = [];

			$sql = "
				SELECT
					*
				FROM
					" . PREFIX . "_sysblocks_groups
				ORDER BY
					id
			";

			$query = $AVE_DB->Query($sql);

			// Формируем массив из полученных данных
			while ($row = $query->FetchAssocArray())
				array_push($groups, $row);

			$AVE_Template->assign('sid', 0);
			$AVE_Template->assign('groups', $groups);
			$AVE_Template->assign('content', $AVE_Template->fetch('sysblocks/groups.tpl'));
		}


		/*
		|--------------------------------------------------------------------------------------
		| newBlock
		|--------------------------------------------------------------------------------------
		|
		| Создание системного блока
		|
		*/
		public static function newBlock ()
		{
			global $AVE_Template;

			$row['sysblock_name'] = '';
			$row['sysblock_alias'] = '';
			$row['sysblock_text'] = '';
			$row['sysblock_visual'] = (isset($_REQUEST['sysblock_visual']) && $_REQUEST['sysblock_visual'] != 0) ? $_REQUEST['sysblock_visual'] : '';

			$AVE_Template->assign('sid', 0);

			if ((isset($_REQUEST['sysblock_visual']) && $_REQUEST['sysblock_visual'] == 1) ||  $row['sysblock_visual'] == 1)
			{
				$oCKeditor = new CKeditor();
				$oCKeditor->returnOutput = true;
				$oCKeditor->config['customConfig'] = 'sysblock.js';
				$oCKeditor->config['toolbar'] = 'Big';
				$oCKeditor->config['height'] = 400;
				$config = array();
				$row['sysblock_text'] = $oCKeditor->editor('sysblock_text', $row['sysblock_text'], $config);

				$AVE_Template->assign($row);
				$AVE_Template->assign('content', $AVE_Template->fetch('sysblocks/form_visual.tpl'));
			}
			else
			{
				$AVE_Template->assign($row);
				$AVE_Template->assign('content', $AVE_Template->fetch('sysblocks/form.tpl'));
			}
		}


		/*
		|--------------------------------------------------------------------------------------
		| editBlock
		|--------------------------------------------------------------------------------------
		|
		| Редактирование системного блока
		|
		*/
		public static function editBlock ()
		{
			global $AVE_DB, $AVE_Template;

			$sysblock_id = (int)$_REQUEST['id'];

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
				$oCKeditor = new CKeditor();
				$oCKeditor->returnOutput = true;
				$oCKeditor->config['customConfig'] = 'sysblock.js';
				$oCKeditor->config['toolbar'] = 'Big';
				$oCKeditor->config['height'] = 400;
				$config = array();
				$row['sysblock_text'] = $oCKeditor->editor('sysblock_text', $row['sysblock_text'], $config);

				$AVE_Template->assign($row);
				$AVE_Template->assign('content', $AVE_Template->fetch('sysblocks/form_visual.tpl'));
			}
			else
				{
					$AVE_Template->assign($row);
					$AVE_Template->assign('content', $AVE_Template->fetch('sysblocks/form.tpl'));
				}
		}


		/*
		|--------------------------------------------------------------------------------------
		| editBlock
		|--------------------------------------------------------------------------------------
		|
		| Сохранение системного блока
		|
		*/
		public static function saveBlock()
		{
			global $AVE_DB, $AVE_Template;

			$sysblock_id = $_REQUEST['id']
				? (int)$_REQUEST['id']
				: null;

			if (is_numeric($sysblock_id))
			{

				$_REQUEST['sysblock_external'] = (isset($_REQUEST['sysblock_external'])) ? $_REQUEST['sysblock_external'] : 0;
				$_REQUEST['sysblock_ajax'] = (isset($_REQUEST['sysblock_ajax'])) ? $_REQUEST['sysblock_ajax'] : 0;
				$_REQUEST['sysblock_eval'] = (isset($_REQUEST['sysblock_eval'])) ? $_REQUEST['sysblock_eval'] : 0;
				$_REQUEST['sysblock_visual'] = (isset($_REQUEST['sysblock_visual'])) ? $_REQUEST['sysblock_visual'] : 0;
				$_REQUEST['sysblock_alias'] = isset($_REQUEST['sysblock_alias']) ? $_REQUEST['sysblock_alias'] : '';

				$sql = $AVE_DB->Query("
					UPDATE
						" . PREFIX . "_sysblocks
					SET
						sysblock_name			 = '" . $_REQUEST['sysblock_name'] . "',
						sysblock_description	 = '" . addslashes($_REQUEST['sysblock_description']) . "',
						sysblock_alias			 = '" . $_REQUEST['sysblock_alias'] . "',
						sysblock_text			 = '" . $_REQUEST['sysblock_text'] . "',
						sysblock_eval			 = '" . (int)$_REQUEST['sysblock_eval'] . "',
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
						self::clearCache($sysblock_id, $_REQUEST['sysblock_alias']);

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
							sysblock_eval			= '" . (int)$_REQUEST['sysblock_eval'] . "',
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


		/*
		|--------------------------------------------------------------------------------------
		| delBlock
		|--------------------------------------------------------------------------------------
		|
		| Удаление системного блока
		|
		*/
		public static function delBlock ()
		{
			global $AVE_DB, $AVE_Template;

			$sysblock_id = $_REQUEST['id'];

			if (is_numeric($sysblock_id))
			{
				$sysblock = $AVE_DB->Query("
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
				self::clearCache($sysblock_id, $sysblock->sysblock_alias);

				//-- Сохраняем системное сообщение в журнал
				reportLog($AVE_Template->get_config_vars('SYSBLOCK_SQLDEL') . " (" . stripslashes($sysblock->sysblock_name) . ") (id: $sysblock_id)");
			}

			header('Location:index.php?do=sysblocks&cp=' . SESSION);
		}


		/*
		|--------------------------------------------------------------------------------------
		| editBlock
		|--------------------------------------------------------------------------------------
		|
		| Очистка кеша системного блока
		|
		*/
		public static function clearCache ($id, $alias = null)
		{
			$from_id = BASE_DIR . '/tmp/cache/sql/sysblocks/' . $id;
			rrmdir($from_id);

			if ($alias)
			{
				$from_alias = BASE_DIR . '/tmp/cache/sql/sysblocks/' . $alias;
				rrmdir($from_alias);
			}
		}
	}
?>