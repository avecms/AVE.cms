<?php

	/**
	 * AVE.cms
	 *
	 * @package AVE.cms
	 * @version 3.x
	 * @filesource
	 * @copyright © 2007-2014 AVE.cms, http://www.ave-cms.ru
	 *
	 */

	/**
	 * Класс работы с рубриками
	 */
	class AVE_Rubric
	{
		/**
		 * Количество рубрик на странице
		 *
		 * @public int
		 */
		public $_limit = 30;


		function get_rubric_fields_group($rubric_id)
		{

			global $AVE_DB, $AVE_Template;

			$sql = $AVE_DB->Query("
				SELECT
					*
				FROM
					" . PREFIX . "_rubric_fields_group
				WHERE
					rubric_id = '" . $rubric_id . "'
				ORDER BY
					group_position ASC
			");

			$groups = array();

			while($row = $sql->FetchRow())
			{
				array_push($groups, $row);
			}

			return $groups;
		}


		/**
		 * Вывод списка рубрик
		 *
		 */
		function rubricList()
		{
			global $AVE_DB, $AVE_Template;

			$rubrics = array();
			$num = $AVE_DB->Query("SELECT COUNT(*) FROM " . PREFIX . "_rubrics")->GetCell();

			$page_limit = $this->_limit;
			$pages = ceil($num / $page_limit);
			$set_start = get_current_page() * $page_limit - $page_limit;

			if ($num > $page_limit)
			{
				$page_nav = " <a class=\"pnav\" href=\"index.php?do=rubs&page={s}&cp=" . SESSION . "\">{t}</a> ";
				$page_nav = get_pagination($pages, 'page', $page_nav);
				$AVE_Template->assign('page_nav', $page_nav);
			}

			$sql = $AVE_DB->Query("
				SELECT
					rub.*,
					(SELECT 1 FROM " . PREFIX . "_documents WHERE rubric_id = rub.Id LIMIT 1) AS doc_count,
					(SELECT count(*) FROM " . PREFIX . "_rubric_fields AS fld WHERE fld.rubric_id = rub.Id) AS fld_count,
					(SELECT count(*) FROM " . PREFIX . "_rubric_templates AS tmpls WHERE tmpls.rubric_id = rub.Id) AS tmpls_count
				FROM
					" . PREFIX . "_rubrics AS rub
				GROUP BY rub.Id
				ORDER BY rub.rubric_position
				LIMIT " . $set_start . "," . $page_limit
			);

			while ($row = $sql->FetchRow())
				array_push($rubrics, $row);

			$AVE_Template->assign('rubrics', $rubrics);
		}

		/**
		 * создание рубрики
		 *
		 */
		function rubricNew()
		{
			global $AVE_DB, $AVE_Template;

			switch ($_REQUEST['sub'])
			{
				case '':
					$AVE_Template->assign('content', $AVE_Template->fetch('rubs/rubnew.tpl'));
					break;

				case 'save':
					$errors = array();

					if (empty($_POST['rubric_title']))
					{
						array_push($errors, $AVE_Template->get_config_vars('RUBRIK_NO_NAME'));
					}
					else
					{
						$name_exist = $AVE_DB->Query("
							SELECT 1
							FROM
								" . PREFIX . "_rubrics
							WHERE
								rubric_title = '" . $_POST['rubric_title'] . "'
							LIMIT 1
						")->NumRows();

						if ($name_exist) array_push($errors, $AVE_Template->get_config_vars('RUBRIK_NAME_EXIST'));

						if (! empty($_POST['rubric_alias']))
						{
							if (preg_match(TRANSLIT_URL ? '/[^\%HYa-z0-9\/_-]+/' : '/[^\%HYa-zа-яА-Яёїєі0-9\/_-]+/u', $_POST['rubric_alias']))
							{
								array_push($errors, $AVE_Template->get_config_vars('RUBRIK_PREFIX_BAD_CHAR'));
							}
							else
							{
								$prefix_exist = $AVE_DB->Query("
									SELECT 1
									FROM
										" . PREFIX . "_rubrics
									WHERE
										rubric_alias = '" . $_POST['rubric_alias'] . "'
									LIMIT 1
								")->NumRows();

								if ($prefix_exist) array_push($errors, $AVE_Template->get_config_vars('RUBRIK_PREFIX_EXIST'));
							}
						}

						if (!empty($errors))
						{
							$AVE_Template->assign('errors', $errors);
							$AVE_Template->assign('templates', get_all_templates());
							$AVE_Template->assign('content', $AVE_Template->fetch('rubs/rubnew.tpl'));
						}
						else
						{
							$position = (int)$AVE_DB->Query("
								SELECT
									MAX(rubric_position)
								FROM
									" . PREFIX . "_rubrics
							")->GetCell() + 1;

							$AVE_DB->Query("
								INSERT
									" . PREFIX . "_rubrics
								SET
									rubric_title			= '" . $_POST['rubric_title'] . "',
									rubric_alias			= '" . $_POST['rubric_alias'] . "',
									rubric_template_id		= '" . intval($_POST['rubric_template_id']) . "',
									rubric_author_id		= '" . $_SESSION['user_id'] . "',
									rubric_created			= '" . time() . "',
									rubric_position			= '" . $position . "',
									rubric_changed			= '" . time() . "',
									rubric_changed_fields	= '" . time() . "'
							");

							$iid = $AVE_DB->InsertId();

							// Выставляем всем право на просмотр рубрики, админу - все права
							$sql_user = $AVE_DB->Query("
								SELECT
									grp.*,
									COUNT(usr.Id) AS UserCount
								FROM
									" . PREFIX . "_user_groups AS grp
								LEFT JOIN
									" . PREFIX . "_users AS usr
										ON usr.user_group = grp.user_group
								GROUP BY grp.user_group
							");
							while ($row = $sql_user->FetchRow())
							{
								$AVE_DB->Query("
									INSERT
										" . PREFIX . "_rubric_permissions
									SET
										rubric_id         = '" . $iid . "',
										user_group_id     = '" . $row->user_group . "',
										rubric_permission = '". (($row->user_group == 1) ? "alles|docread|new|newnow|editown|editall|delrev" : "docread")."'
								");
							}

							// Сохраняем системное сообщение в журнал
							reportLog($AVE_Template->get_config_vars('RUBRIK_LOG_NEW_RUBRIC') . ' - ' . stripslashes(htmlspecialchars($_POST['rubric_title'], ENT_QUOTES)) . ' (id: '.$iid.')');

							header('Location:index.php?do=rubs&action=edit&Id=' . $iid . '&cp=' . SESSION);
							exit;
						}
					}
					break;
			}
		}


		/**
		 * Запись настроек рубрики
		 *
		 */
		function quickSave()
		{
			global $AVE_DB, $AVE_Template;

			if (check_permission_acp('rubric_edit'))
			{
				foreach ($_POST['rubric_title'] as $rubric_id => $rubric_title)
				{
					if (! empty($rubric_title))
					{
						$set_rubric_title = '';
						$set_rubric_alias = '';

						$name_exist = $AVE_DB->Query("
							SELECT 1
							FROM
								" . PREFIX . "_rubrics
							WHERE
								rubric_title = '" . $rubric_title . "'
							AND
								Id != '" . $rubric_id . "'
							LIMIT 1
						")->NumRows();

						if (!$name_exist)
						{
							$set_rubric_title = "rubric_title = '" . $rubric_title . "',";
						}

						if (isset($_POST['rubric_alias'][$rubric_id]) && $_POST['rubric_alias'][$rubric_id] != '')
						{
							$pattern = TRANSLIT_URL ? '/[^\%HYa-z0-9\/_-]+/' : '/[^\%HYa-zа-яА-Яёїєі0-9\/_-]+/u';

							if (! (preg_match($pattern, $_POST['rubric_alias'][$rubric_id])))
							{
								$prefix_exist = $AVE_DB->Query("
									SELECT 1
									FROM
										" . PREFIX . "_rubrics
									WHERE
										rubric_alias = '" . $_POST['rubric_alias'][$rubric_id] . "'
									AND
										Id != '" . $rubric_id . "'
									LIMIT 1
								")->NumRows();

								if (! $prefix_exist)
								{
									$set_rubric_alias = "rubric_alias = '" . trim(preg_replace($pattern, '', $_POST['rubric_alias'][$rubric_id]), '/') . "',";
								}
							}
						}
						else
						{
							$set_rubric_alias = "rubric_alias = '',";
						}

						$AVE_DB->Query("
							UPDATE
								" . PREFIX . "_rubrics
							SET
								" . $set_rubric_title . "
								" . $set_rubric_alias . "
								rubric_meta_gen = '" . (isset($_POST['rubric_meta_gen'][$rubric_id]) ? $_POST['rubric_meta_gen'][$rubric_id] : '0') . "',
								rubric_alias_history = '" . (isset($_POST['rubric_alias_history'][$rubric_id]) ? $_POST['rubric_alias_history'][$rubric_id] : '0') . "',
								rubric_template_id = '" . (int)$_POST['rubric_template_id'][$rubric_id] . "',
								rubric_docs_active = '" . (isset($_POST['rubric_docs_active'][$rubric_id]) ? $_POST['rubric_docs_active'][$rubric_id] : '0') . "',
								rubric_changed = '".time()."',
								rubric_changed_fields = '".time()."'
							WHERE
								Id = '" . $rubric_id . "'
						");
					}
				}

				$AVE_DB->clearCache('rub_' . $rubric_id);

				$message = $AVE_Template->get_config_vars('RUBRIK_REP_QUICKSAVE_T');
				$header = $AVE_Template->get_config_vars('RUBRIK_REP_QUICKSAVE_H');
				$theme = 'accept';

				reportLog($AVE_Template->get_config_vars('RUBRIK_REPORT_QUICKSAVE'));

				if (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] = 'run')
				{
					echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
				}
				else
				{
					$page = !empty($_REQUEST['page']) ? '&page=' . $_REQUEST['page'] : '' ;
					header('Location:index.php?do=rubs' . $page . '&cp=' . SESSION);
				}

				exit;
			}
		}


		/**
		 * Копирование рубрики
		 *
		 */
		function rubricCopy()
		{
			global $AVE_DB, $AVE_Template;

			$rubric_id = (int)$_REQUEST['Id'];

			$errors = array();

			if (empty($_REQUEST['rubric_title']))
			{
				array_push($errors, $AVE_Template->get_config_vars('RUBRIK_NO_NAME'));
			}
			else
			{
				$name_exist = $AVE_DB->Query("
					SELECT 1
					FROM
						" . PREFIX . "_rubrics
					WHERE
						rubric_title = '" . $_POST['rubric_title'] . "'
					LIMIT 1
				")->NumRows();

				if ($name_exist)
					array_push($errors, $AVE_Template->get_config_vars('RUBRIK_NAME_EXIST'));
			}

			if (! empty($_POST['rubric_alias']))
			{
				if (preg_match(TRANSLIT_URL ? '/[^\%HYa-z0-9\/-]+/' : '/[^\%HYa-zа-яёїєі0-9\/_-]+/', $_POST['rubric_alias']))
				{
					array_push($errors, $AVE_Template->get_config_vars('RUBRIK_PREFIX_BAD_CHAR'));
				}
				else
				{
					$prefix_exist = $AVE_DB->Query("
						SELECT 1
						FROM
							" . PREFIX . "_rubrics
						WHERE
							rubric_alias = '" . $_POST['rubric_alias'] . "'
						LIMIT 1
					")->NumRows();

					if ($prefix_exist)
						array_push($errors, $AVE_Template->get_config_vars('RUBRIK_PREFIX_EXIST'));
				}
			}

			$row = $AVE_DB->Query("
				SELECT *
				FROM
					" . PREFIX . "_rubrics
				WHERE
					Id = '" . $rubric_id . "'
			")->FetchRow();

			if (! $row)
				array_push($errors, $AVE_Template->get_config_vars('RUBRIK_NO_RUBRIK'));

			if (! empty($errors))
			{
				$AVE_Template->assign('errors', $errors);
			}
			else
			{
				$AVE_DB->Query("
					INSERT
						" . PREFIX . "_rubrics
					SET
						rubric_title					= '" . $_POST['rubric_title'] . "',
						rubric_alias					= '" . $_POST['rubric_alias'] . "',
						rubric_template					= '" . addslashes($row->rubric_template) . "',
						rubric_template_id				= '" . addslashes($row->rubric_template_id) . "',
						rubric_author_id				= '" . (int)$_SESSION['user_id'] . "',
						rubric_created					= '" . time() . "',
						rubric_teaser_template			= '" . addslashes($row->rubric_teaser_template) . "',
						rubric_header_template			= '" . addslashes($row->rubric_header_template) . "',
						rubric_footer_template			= '" . addslashes($row->rubric_footer_template) . "',
						rubric_admin_teaser_template	= '" . addslashes($row->rubric_admin_teaser_template) . "',
						rubric_changed					= '" . time() . "',
						rubric_changed_fields			= '" . time() . "'
				");

				$iid = $AVE_DB->InsertId();

				$sql = $AVE_DB->Query("
					SELECT
						user_group_id,
						rubric_permission
					FROM
						" . PREFIX . "_rubric_permissions
					WHERE
						rubric_id = '" . $rubric_id . "'
				");

				while ($row = $sql->FetchRow())
				{
					$AVE_DB->Query("
						INSERT
							" . PREFIX . "_rubric_permissions
						SET
							rubric_id = '" . $iid . "',
							user_group_id = '" . (int)$row->user_group_id . "',
							rubric_permission = '" . addslashes($row->rubric_permission) . "'
					");
				}

				$sql = $AVE_DB->Query("
					SELECT
						rubric_field_title,
						rubric_field_alias,
						rubric_field_type,
						rubric_field_position,
						rubric_field_default,
						rubric_field_template,
						rubric_field_template_request,
						rubric_field_description
					FROM
						" . PREFIX . "_rubric_fields
					WHERE
						rubric_id = '" . $rubric_id . "'
					ORDER BY rubric_field_position ASC
				");

				while ($row = $sql->FetchRow())
				{
					$AVE_DB->Query("
						INSERT
							" . PREFIX . "_rubric_fields
						SET
							rubric_id                     = '" . $iid . "',
							rubric_field_title            = '" . addslashes($row->rubric_field_title) . "',
							rubric_field_alias            = '" . addslashes($row->rubric_field_alias) . "',
							rubric_field_type             = '" . addslashes($row->rubric_field_type) . "',
							rubric_field_position         = '" . (int)$row->rubric_field_position . "',
							rubric_field_default          = '" . addslashes($row->rubric_field_default) . "',
							rubric_field_template         = '" . addslashes($row->rubric_field_template) . "',
							rubric_field_template_request = '" . addslashes($row->rubric_field_template_request) . "',
							rubric_field_description      = '" . addslashes($row->rubric_field_description) . "'
					");
				}

				reportLog($AVE_Template->get_config_vars('RUBRIK_REPORT_COPY') . ' - ' . stripslashes(htmlspecialchars($this->rubricNameByIdGet($rubric_id)->rubric_title, ENT_QUOTES)) . ' (id: '.$rubric_id.')');

				echo '<script>window.opener.location.reload();window.close();</script>';
			}
		}


		/**
		 * Удаление рубрики
		 *
		 */
		function rubricDelete()
		{
			global $AVE_DB, $AVE_Template;

			$rubric_id = (int)$_REQUEST['Id'];

			if ($rubric_id <= 1)
			{
				header('Location:index.php?do=rubs&cp=' . SESSION);
				exit;
			}

			$rubric_not_empty = $AVE_DB->Query("
				SELECT 1
				FROM
					" . PREFIX . "_documents
				WHERE
					rubric_id = '" . $rubric_id . "'
				LIMIT 1
			")->GetCell();

			if (!$rubric_not_empty)
			{
				$AVE_DB->Query("
					DELETE FROM
						" . PREFIX . "_rubrics
					WHERE
						Id = '" . $rubric_id . "'
				");

				$AVE_DB->Query("
					DELETE FROM
						" . PREFIX . "_rubric_fields
					WHERE
						rubric_id = '" . $rubric_id . "'
				");

				$AVE_DB->Query("
					DELETE FROM
						" . PREFIX . "_rubric_permissions
					WHERE
						rubric_id = '" . $rubric_id . "'
				");

				$AVE_DB->Query("
					DELETE FROM
						" . PREFIX . "_rubric_templates
					WHERE
						rubric_id = '" . $rubric_id . "'
				");

				// Очищаем кэш шаблона документов рубрики
				$AVE_DB->Query("
					DELETE FROM
						" . PREFIX . "_rubric_template_cache
					WHERE
						rub_id = '" . $rubric_id . "'
				");

				// Удалить КЕШ
				$AVE_DB->clearCache('rub_' . $rubric_id);

				// Удалить файлы шаблонов
				$this->clearTemplates($rubric_id);

				// Сохраняем системное сообщение в журнал
				reportLog($AVE_Template->get_config_vars('RUBRIK_LOG_DEL_RUBRIC') . ' - ' . stripslashes(htmlspecialchars($this->rubricNameByIdGet($rubric_id)->rubric_title, ENT_QUOTES)) . ' (id: '.$rubric_id.')');
			}

			header('Location:index.php?do=rubs&cp=' . SESSION);
			exit;
		}


		/**
		 * Вывод списка полей рубрики
		 *
		 * @param int $rubric_id	идентификатор рубрики
		 */
		function rubricFieldShow($rubric_id = 0, $ajax)
		{
			global $AVE_DB, $AVE_Template;

			if (check_permission_acp('rubric_edit'))
			{
				// Поля
				$sql = $AVE_DB->Query("
					SELECT
						a.*,
						b.group_title,
						b.group_description,
						b.group_position
					FROM
						" . PREFIX . "_rubric_fields AS a
					LEFT JOIN
						" . PREFIX . "_rubric_fields_group AS b
						ON a.rubric_field_group = b.Id
					WHERE
						a.rubric_id = '" . $rubric_id . "'
					ORDER BY
						b.group_position ASC, a.rubric_field_position ASC
				");

				$fields_list = array();

				while ($row = $sql->FetchRow())
				{
					$group_id = ($row->rubric_field_group) ? $row->rubric_field_group : 0;

					$fields_list[$group_id]['group_position'] = ($row->group_position) ? $row->group_position : 100;
					$fields_list[$group_id]['group_title'] = $row->group_title;
					$fields_list[$group_id]['group_description'] = $row->group_description;
					$fields_list[$group_id]['fields'][$row->Id]['Id'] = $row->Id;
					$fields_list[$group_id]['fields'][$row->Id]['rubric_id'] = $row->rubric_id;
					$fields_list[$group_id]['fields'][$row->Id]['rubric_field_group'] = $row->rubric_field_group;
					$fields_list[$group_id]['fields'][$row->Id]['rubric_field_alias'] = $row->rubric_field_alias;
					$fields_list[$group_id]['fields'][$row->Id]['rubric_field_title'] = $row->rubric_field_title;
					$fields_list[$group_id]['fields'][$row->Id]['rubric_field_type'] = $row->rubric_field_type;
					$fields_list[$group_id]['fields'][$row->Id]['rubric_field_numeric'] = $row->rubric_field_numeric;
					$fields_list[$group_id]['fields'][$row->Id]['rubric_field_default'] = $row->rubric_field_default;
					$fields_list[$group_id]['fields'][$row->Id]['rubric_field_search'] = $row->rubric_field_search;
				}

				$fields_list = msort($fields_list, 'group_position');

				$AVE_Template->assign('groups_count', count($fields_list));
				$AVE_Template->assign('fields_list', $fields_list);

				// Группы полей
				$fields_groups = array();

				$sql = $AVE_DB->Query("
					SELECT *
					FROM
						" . PREFIX . "_rubric_fields_group
					WHERE
						rubric_id = '" . $rubric_id . "'
					ORDER BY
						group_position ASC
				");

				while ($row = $sql->FetchRow())
					array_push($fields_groups, $row);

				$AVE_Template->assign('fields_groups', $fields_groups);

				// Права
				$groups = array();

				$sql = $AVE_DB->Query("SELECT * FROM " . PREFIX . "_user_groups");

				while ($row = $sql->FetchRow())
				{
					$row->doall = ($row->user_group == 1) ? ' disabled="disabled" checked="checked"' : '';
					$row->doall_h = ($row->user_group == 1) ? 1 : '';

					$rubric_permission = $AVE_DB->Query("
						SELECT
							rubric_permission
						FROM
							" . PREFIX . "_rubric_permissions
						WHERE
							user_group_id = '" . $row->user_group . "'
						AND
							rubric_id = '" . $rubric_id . "'
					")->GetCell();

					$row->permissions = @explode('|', $rubric_permission);

					array_push($groups,$row);
				}

				$sql = $AVE_DB->Query("
					SELECT
						rubric_title,
						rubric_linked_rubric,
						rubric_description
					FROM
						" . PREFIX . "_rubrics
					WHERE
						id = '" . $rubric_id . "'
					LIMIT 1
				");

				$rubrik = $sql->FetchRow();

				$rubrik->rubric_linked_rubric = ($rubrik->rubric_linked_rubric != '0')
					? unserialize($rubrik->rubric_linked_rubric)
					: array();

				$AVE_Template->assign('rubric', $rubrik);
				$AVE_Template->assign('groups', $groups);
				$AVE_Template->assign('fields', get_field_type());
				$AVE_Template->assign('rubs', $this->rubricShow());

				if (isAjax())
					$AVE_Template->assign('content', $AVE_Template->fetch('rubs/fields.tpl'));
				else
					$AVE_Template->assign('content', $AVE_Template->fetch('rubs/fields_list.tpl'));
			}
			else
			{
				header('Location:index.php?do=rubs&cp=' . SESSION);
				exit;
			}
		}


		/**
		 * Вывод списка полей рубрики
		 *
		 * @param int $rubric_id	идентификатор рубрики
		 */
		function rubricRulesShow($rubric_id = 0)
		{
			global $AVE_DB, $AVE_Template;

			if (check_permission_acp('rubric_edit'))
			{
				// Права
				$groups = array();

				$sql = $AVE_DB->Query("
					SELECT
						*
					FROM
						" . PREFIX . "_user_groups
				");

				while ($row = $sql->FetchRow())
				{
					$row->doall = ($row->user_group == 1) ? ' disabled="disabled" checked="checked"' : '';
					$row->doall_h = ($row->user_group == 1) ? 1 : '';

					$rubric_permission = $AVE_DB->Query("
						SELECT
							rubric_permission
						FROM
							" . PREFIX . "_rubric_permissions
						WHERE
							user_group_id = '" . $row->user_group . "'
						AND
							rubric_id = '" . $rubric_id . "'
					")->GetCell();

					$row->permissions = @explode('|', $rubric_permission);

					array_push($groups,$row);
				}

				$sql = $AVE_DB->Query("
					SELECT
						rubric_title,
						rubric_linked_rubric,
						rubric_description
					FROM
						" . PREFIX . "_rubrics
					WHERE
						id = '" . $rubric_id . "'
					LIMIT 1
				");

				$rubric = $sql->FetchRow();

				$AVE_Template->assign('rubric', $rubric);
				$AVE_Template->assign('groups', $groups);

				if (isAjax())
				{

				}
				else
				{
					$AVE_Template->assign('content', $AVE_Template->fetch('rubs/rules.tpl'));
				}
			}
			else
			{
				header('Location:index.php?do=rubs&cp=' . SESSION);
				exit;
			}
		}



		/**
		 * Вывод списка рубрик
		 *
		 * @param int $rubric_id идентификатор текущей рубрики
		 */
		function rubricShow($RubLink=null)
		{
			global $AVE_DB;

			if ($RubLink !== null)
			{
				$AVE_DB->Query("
					UPDATE
						" . PREFIX . "_rubrics
					SET
						rubric_linked_rubric = '" . serialize($_REQUEST['rubric_linked']) . "'
					WHERE
						Id = '" . (int)$_REQUEST['Id'] . "'
				");

				header('Location:index.php?do=rubs&action=edit&Id=' . (int)$_REQUEST['Id'] . '&cp=' . SESSION);
				exit;
			}
			else
			{
				$rubs = array();

				$sql = $AVE_DB->Query("
					SELECT
						rubric_title,
						Id
					FROM
						" . PREFIX . "_rubrics
					ORDER BY
						rubric_position ASC
				");

				while ($row = $sql->FetchRow())
					array_push($rubs, $row);

				return $rubs;
			}
		}

		/**
		 * Создание нового поля рубрики
		 *
		 * @param int $rubric_id	идентификатор рубрики
		 */
		function rubricFieldNew($rubric_id = 0, $ajax)
		{
			global $AVE_DB, $AVE_Template;

			if (! empty($_POST['title_new']))
			{
				$position = (int)$AVE_DB->Query("
					SELECT
						MAX(rubric_field_position)
					FROM
						" . PREFIX . "_rubric_fields
					WHERE
						rubric_id = '" . $rubric_id . "'
				")->GetCell() + 1;

				if ($_POST['rub_type_new'] == 'dropdown')
				{
					$rubric_field_default = trim($_POST['default_value']);
					$rubric_field_default = preg_split('/\s*,\s*/', $rubric_field_default);
					$rubric_field_default = implode(',', $rubric_field_default);
				}
				else
				{
					$rubric_field_default = $_POST['default_value'];
				}

				$AVE_DB->Query("
					INSERT
						" . PREFIX . "_rubric_fields
					SET
						rubric_id             = '" . $rubric_id . "',
						rubric_field_group    = '" . (($_POST['group_new'] != '') ? (int)$_POST['group_new'] : '0') . "',
						rubric_field_title    = '" . $_POST['title_new'] . "',
						rubric_field_type     = '" . $_POST['rub_type_new'] . "',
						rubric_field_position = '" . $position . "',
						rubric_field_default  = '" . $rubric_field_default . "',
						rubric_field_numeric     = '" . (($_POST['rubric_field_numeric'] == 1) ? $_POST['rubric_field_numeric'] : '0') . "',
						rubric_field_search     = '" . (($_POST['rubric_field_search'] == 1) ? $_POST['rubric_field_search'] : '0') . "'
				");

				$UpdateRubricField = $AVE_DB->InsertId();

				$sql = $AVE_DB->Query("
					SELECT
						Id
					FROM
						" . PREFIX . "_documents
					WHERE
						rubric_id = '" . $rubric_id . "'
				");

				while ($row = $sql->FetchRow())
				{
					$AVE_DB->Query("
						INSERT
							" . PREFIX . "_document_fields
						SET
							rubric_field_id = '" . $UpdateRubricField . "',
							document_id = '" . $row->Id . "'
					");
				}

				$AVE_DB->Query("
					UPDATE
						" . PREFIX . "_rubrics
					SET
						rubric_changed_fields = '" . time() . "'
					WHERE
						Id = '" . $rubric_id . "'
				");

				// Сохраняем системное сообщение в журнал
				reportLog($AVE_Template->get_config_vars('RUBRIK_LOG_NEW_FIELD').' (' . stripslashes(htmlspecialchars($_POST['title_new'], ENT_QUOTES)) . ') '. stripslashes(htmlspecialchars($this->rubricNameByIdGet($rubric_id)->rubric_title, ENT_QUOTES)). ' (id: '.$rubric_id.')');
			}
			else
			{
				if (! isAjax())
				{
					header('Location:index.php?do=rubs&action=edit&Id=' . $rubric_id . '&cp=' . SESSION);
				}
				else
				{
					$message = $AVE_Template->get_config_vars('RUBRIK_EMPTY_MESSAGE');
					$header = $AVE_Template->get_config_vars('RUBRIK_FILDS_SUCCESS');
					$theme = 'error';
					echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
				}

				exit;
			}

			if (! isAjax())
			{
				header('Location:index.php?do=rubs&action=edit&Id=' . $rubric_id . '&cp=' . SESSION);
			}
			else
			{
				$message = $AVE_Template->get_config_vars('RUBRIK_FILD_SAVED');
				$header = $AVE_Template->get_config_vars('RUBRIK_FILDS_SUCCESS');
				$theme = 'accept';
				echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
			}

			exit;
		}


		/**
		 * Редактирование кода для рубрики
		 *
		 * @param int $rubric_id	идентификатор рубрики
		 */
		function rubricCodeEdit($rubric_id = 0)
		{
			global $AVE_DB, $AVE_Template;

			switch ($_REQUEST['sub']) {

				case '':
					$code = $AVE_DB->Query("
						SELECT
							rubric_code_start,
							rubric_code_end,
							rubric_start_code
						FROM
							" . PREFIX . "_rubrics
						WHERE
							Id = '" . $rubric_id . "'
					")->FetchRow();

					$AVE_Template->assign('code', $code);
					$AVE_Template->assign('rubric_title', $this->rubricNameByIdGet($rubric_id)->rubric_title);
					$AVE_Template->assign('formaction', 'index.php?do=rubs&action=code&sub=save&Id=' . $rubric_id . '&cp=' . SESSION);
					$AVE_Template->assign('content', $AVE_Template->fetch('rubs/code.tpl'));
					break;

				case 'save':
					$sql = $AVE_DB->Query("
						UPDATE
							" . PREFIX . "_rubrics
						SET
							rubric_start_code			= '" . $_POST['rubric_start_code'] . "',
							rubric_code_start			= '" . $_POST['rubric_code_start'] . "',
							rubric_code_end				= '" . $_POST['rubric_code_end'] . "',
							rubric_changed				= '" . time() . "'
						WHERE
							Id = '" . $rubric_id . "'
					");

					// Очищаем кэш рубрики
					$AVE_DB->clearCache('rub_' . $rubric_id);

					if ($sql->_result === false)
					{
						$message = $AVE_Template->get_config_vars('RUBRIK_CODE_SAVED_ERR');
						$header = $AVE_Template->get_config_vars('RUBRIK_CODE_ERROR');
						$theme = 'error';
					}
					else
					{
						$message = $AVE_Template->get_config_vars('RUBRIK_CODE_SAVED');
						$header = $AVE_Template->get_config_vars('RUBRIK_CODE_SUCCESS');
						$theme = 'accept';
						reportLog($AVE_Template->get_config_vars('RUBRIK_CODE_UPDATE') . " (" . stripslashes(htmlspecialchars($this->rubricNameByIdGet($rubric_id)->rubric_title, ENT_QUOTES)) . ") (id: $rubric_id)");
					}

					if (isAjax())
						echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
					else
						header('Location:index.php?do=rubs&action=code&Id=' . $rubric_id . '&cp=' . SESSION);

					exit;
			}
		}

		/**
		 * Редактирование кода для рубрики
		 *
		 * @param int $rubric_id	идентификатор рубрики
		 */
		function rubricCode($rubric_id = 0)
		{
			global $AVE_DB, $AVE_Template;

			$sql = $AVE_DB->Query("
				UPDATE
					" . PREFIX . "_rubrics
				SET
					rubric_start_code			= '" . $_POST['rubric_start_code'] . "',
					rubric_code_start			= '" . $_POST['rubric_code_start'] . "',
					rubric_code_end				= '" . $_POST['rubric_code_end'] . "',
					rubric_changed				= '" . time() . "'
				WHERE
					Id = '" . $rubric_id . "'
			");

			$AVE_DB->clearCache('rub_' . $rubric_id);

			if ($sql->_result === false)
			{
				$message = $AVE_Template->get_config_vars('RUBRIK_CODE_SAVED_ERR');
				$header = $AVE_Template->get_config_vars('RUBRIK_CODE_ERROR');
				$theme = 'error';
			}
			else
			{
				$message = $AVE_Template->get_config_vars('RUBRIK_CODE_SAVED');
				$header = $AVE_Template->get_config_vars('RUBRIK_CODE_SUCCESS');
				$theme = 'accept';
				reportLog($AVE_Template->get_config_vars('RUBRIK_CODE_UPDATE') . " (" . stripslashes(htmlspecialchars($this->rubricNameByIdGet($rubric_id)->rubric_title, ENT_QUOTES)) . ") (id: $rubric_id)");
			}

			if (isAjax())
			{
				echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
			}
			else
			{
				header('Location:index.php?do=rubs&action=edit&Id=' . $rubric_id . '&cp=' . SESSION);
			}
			exit;
		}

		/**
		 * Редактирование описания рубрики
		 *
		 * @param int $rubric_id идентификатор рубрики
		 */
		function rubricDesc($rubric_id = 0)
		{
			global $AVE_DB;

			$AVE_DB->Query("
				UPDATE
					" . PREFIX . "_rubrics
				SET
					rubric_description		= '" . $_POST['rubric_description'] . "'
				WHERE
					Id = '" . $rubric_id . "'
			");

			header('Location:index.php?do=rubs&action=edit&Id=' . $rubric_id . '&cp=' . SESSION);
			exit;
		}

		/**
		 * Управление полями рубрики
		 *
		 * @param int $rubric_id	идентификатор рубрики
		 */
		function rubricFieldSave($rubric_id = 0)
		{
			global $AVE_DB, $AVE_Template;

			foreach ($_POST['title'] as $id => $title)
			{
				if (! empty($title))
				{
					$AVE_DB->Query("
						UPDATE
							" . PREFIX . "_rubric_fields
						SET
							rubric_field_title		= '" . $title . "',
							rubric_field_numeric	= '" . $_POST['rubric_field_numeric'][$id] . "',
							rubric_field_search		= '" . $_POST['rubric_field_search'][$id] . "'
						WHERE
							Id = '" . $id . "'
					");

					reportLog($AVE_Template->get_config_vars('RUBRIK_REPORT_FIELD_EDIT') . ' (' . stripslashes($title) . ') '.$AVE_Template->get_config_vars('RUBRIK_REPORT_RUB').' (' . stripslashes(htmlspecialchars($this->rubricNameByIdGet($rubric_id)->rubric_title)) . ') (Id:' . $rubric_id . ')');
				}
			}

			foreach ($_POST['del'] as $id => $Del)
			{
				if (! empty($Del))
				{
					$AVE_DB->Query("
						DELETE FROM
							" . PREFIX . "_rubric_fields
						WHERE
							Id = '" . $id . "'
						AND
							rubric_id = '" . $rubric_id . "'
					");

					$AVE_DB->Query("
						DELETE FROM
							" . PREFIX . "_document_fields
						WHERE
							rubric_field_id = '" . $id . "'
					");

					reportLog($AVE_Template->get_config_vars('RUBRIK_REPORT_FIELD_DEL') . ' (' . stripslashes($_POST['title'][$id]) . ') '.$AVE_Template->get_config_vars('RUBRIK_REPORT_RUB').' (' . stripslashes(htmlspecialchars($this->rubricNameByIdGet($rubric_id)->rubric_title)) . ') (Id:' . $rubric_id . ')');
				}
			}

			// Очищаем кэш шаблона документов рубрики
			$AVE_DB->Query("
				UPDATE
					" . PREFIX . "_rubrics
				SET
					rubric_changed = '" . time() . "',
					rubric_changed_fields = '" . time() . "'
				WHERE
					Id = '" . $rubric_id . "'
			");

			$AVE_DB->clearCache('rub_' . $rubric_id);

			$message = $AVE_Template->get_config_vars('RUBRIK_FILDS_SAVED');
			$header = $AVE_Template->get_config_vars('RUBRIK_FILDS_SUCCESS');
			$theme = 'accept';

			reportLog($AVE_Template->get_config_vars('RUBRIK_FILDS_REPORT') . ' (' . stripslashes(htmlspecialchars($this->rubricNameByIdGet($rubric_id)->rubric_title)) . ') (Id:' . $rubric_id . ')');

			if (isAjax())
				echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
			else
			{
				$AVE_Template->assign('message', $message);
				header('Location:index.php?do=rubs&action=edit&Id=' . $rubric_id . '&cp=' . SESSION);
			}

			exit;
		}

		/**
		 * Сортировка полей рубрики
		 *
		 * @param array $sorted	последовательность id полей
		 */
		function rubricFieldsSort()
		{
			global $AVE_DB, $AVE_Template;

			foreach ($_REQUEST['sort'] as $position => $field_id)
			{
				$AVE_DB->Query("
					UPDATE
						" . PREFIX . "_rubric_fields
					SET
						rubric_field_position = '" . (int)$position . "'
					WHERE
						Id = '" . (int)$field_id . "'
				");
			}

			reportLog($AVE_Template->get_config_vars('RUBRIK_REPORT_SORTE_FIELDS'));

			if (isAjax())
			{
				$message = $AVE_Template->get_config_vars('RUBRIK_SORTED');
				$header = $AVE_Template->get_config_vars('RUBRIK_FILDS_SUCCESS');
				$theme = 'accept';

				echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
			}

		}

		/**
		 * Сортировка рубрик
		 *
		 * @param array $sorted	последовательность id полей
		 */
		function rubricsSort()
		{
			global $AVE_DB, $AVE_Template;

			foreach ($_REQUEST['sort'] as $position => $rub_id)
			{
				$AVE_DB->Query("
					UPDATE
						" . PREFIX . "_rubrics
					SET
						rubric_position = '" . (int)$position . "'
					WHERE
						Id = '" . (int)$rub_id . "'
				");
			}

			reportLog($AVE_Template->get_config_vars('RUBRIK_REPORT_SORTE'));

			if (isAjax())
			{
				$message = $AVE_Template->get_config_vars('RUBRIK_SORTED');
				$header = $AVE_Template->get_config_vars('RUBRIK_FILDS_SUCCESS');
				$theme = 'accept';

				echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
			}
		}

		/**
		 * Вывод шаблона рубрики
		 *
		 * @param int $show
		 * @param int $extern
		 */
		function rubricTemplateShow($show = '', $extern = '0')
		{
			global $AVE_DB, $AVE_Template;

			if ($extern == 1)
				$fetchId = (isset($_REQUEST['rubric_id']) && is_numeric($_REQUEST['rubric_id'])) ? $_REQUEST['rubric_id'] : 0;
			else
				$fetchId = (isset($_REQUEST['Id']) && is_numeric($_REQUEST['Id'])) ? $_REQUEST['Id'] : 0;

			$rubric = $AVE_DB->Query("
				SELECT
					rubric_title,
					rubric_template,
					rubric_header_template,
					rubric_footer_template,
					rubric_teaser_template,
					rubric_admin_teaser_template,
					rubric_description
				FROM
					" . PREFIX . "_rubrics
				WHERE
					Id = '" . $fetchId . "'
			")->FetchRow();

			// Поля
			$sql = $AVE_DB->Query("
				SELECT
					a.*,
					b.group_title,
					b.group_description,
					b.group_position
				FROM
					" . PREFIX . "_rubric_fields AS a
				LEFT JOIN
					" . PREFIX . "_rubric_fields_group AS b
					ON a.rubric_field_group = b.Id
				WHERE
					a.rubric_id = '" . $fetchId . "'
				ORDER BY
					b.group_position ASC, a.rubric_field_position ASC
			");

			$fields_list = array();
			$drop_down_fields = array();

			while ($row = $sql->FetchRow())
			{
				$group_id = ($row->rubric_field_group) ? $row->rubric_field_group : 0;

				if ($row->rubric_field_type == 'drop_down' || $row->rubric_field_type == 'drop_down_key')
					array_push($drop_down_fields, $row->Id);

				$fields_list[$group_id]['group_position'] = ($row->group_position) ? $row->group_position : 100;
				$fields_list[$group_id]['group_title'] = $row->group_title;
				$fields_list[$group_id]['group_description'] = $row->group_description;
				$fields_list[$group_id]['fields'][$row->Id]['Id'] = $row->Id;
				$fields_list[$group_id]['fields'][$row->Id]['rubric_id'] = $row->rubric_id;
				$fields_list[$group_id]['fields'][$row->Id]['rubric_field_group'] = $row->rubric_field_group;
				$fields_list[$group_id]['fields'][$row->Id]['rubric_field_alias'] = $row->rubric_field_alias;
				$fields_list[$group_id]['fields'][$row->Id]['rubric_field_title'] = $row->rubric_field_title;
				$fields_list[$group_id]['fields'][$row->Id]['rubric_field_type'] = $row->rubric_field_type;
				$fields_list[$group_id]['fields'][$row->Id]['rubric_field_numeric'] = $row->rubric_field_numeric;
				$fields_list[$group_id]['fields'][$row->Id]['rubric_field_default'] = $row->rubric_field_default;
				$fields_list[$group_id]['fields'][$row->Id]['rubric_field_search'] = $row->rubric_field_search;
			}

			$fields_list = msort($fields_list, 'group_position');

			$AVE_Template->assign('groups_count', count($fields_list));
			$AVE_Template->assign('fields_list', $fields_list);

			$AVE_Template->assign('field_array', get_field_type());

			if ($show == 1 )
				$rubric->rubric_template = stripslashes($_POST['rubric_template']);

			if ($extern == 1)
			{
				$AVE_Template->assign('ddid', implode(',', $drop_down_fields));
			}
			else
			{
				$AVE_Template->assign('rubric', $rubric);
				$AVE_Template->assign('formaction', 'index.php?do=rubs&action=template&sub=save&Id=' . $fetchId . '&cp=' . SESSION);
				$AVE_Template->assign('content', $AVE_Template->fetch('rubs/form.tpl'));
			}
		}

		/**
		 * Редактирование шаблона рубрики
		 *
		 * @param string $data
		 */
		function rubricTemplateSave($Rtemplate, $Htemplate = '', $Ttemplate = '', $Atemplate = '', $Ftemplate = '')
		{
			global $AVE_DB, $AVE_Template;

			$rubric_id = (int)$_REQUEST['Id'];

			$sql = $AVE_DB->Query("
				UPDATE
					" . PREFIX . "_rubrics
				SET
					rubric_template					= '" . ($Rtemplate) . "',
					rubric_header_template			= '" . $Htemplate . "',
					rubric_footer_template			= '" . $Ftemplate . "',
					rubric_teaser_template			= '" . $Ttemplate . "',
					rubric_admin_teaser_template	= '" . $Atemplate . "',
					rubric_changed					= '" . time() . "'
				WHERE
					Id = '" . $rubric_id . "'
			");

			$AVE_DB->clearCache('rub_' . $rubric_id);

			if ($sql === false)
			{
				$message = $AVE_Template->get_config_vars('RUBRIC_SAVED_TPL_ERR');
				$header = $AVE_Template->get_config_vars('RUBRIK_ERROR');
				$theme = 'error';
			}
			else
			{
				$message = $AVE_Template->get_config_vars('RUBRIC_SAVED_TPL');
				$header = $AVE_Template->get_config_vars('RUBRIC_SUCCESS');
				$theme = 'accept';
				reportLog($AVE_Template->get_config_vars('RUBRIK_REPORT_TEMPL_RUB') . ' (' . stripslashes(htmlspecialchars($this->rubricNameByIdGet($rubric_id)->rubric_title)) . ') (Id:' . $rubric_id . ')');
			}

			if (isAjax())
			{
				echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
			}
			else
			{
				$AVE_Template->assign('message', $message);
				header('Location:index.php?do=rubs&cp=' . SESSION);
			}

			exit;
		}

		/**
		 * Управление правами доступа к документам рубрик
		 *
		 * @param int $rubric_id идентификатор рубрики
		 */
		function rubricPermissionSave($rubric_id = 0)
		{
			global $AVE_DB, $AVE_Template;

			if (check_permission_acp('rubric_perms') && is_numeric($rubric_id) && $rubric_id > 0)
			{
				foreach ($_POST['user_group'] as $key => $user_group_id)
				{
					$exist = $AVE_DB->Query("
						SELECT 1
						FROM
							" . PREFIX . "_rubric_permissions
						WHERE
							user_group_id = '" . $user_group_id . "'
						AND
							rubric_id = '" . $rubric_id . "'
						LIMIT 1
					")->NumRows();

					$rubric_permission = @implode('|', $_POST['perm'][$key]);

					if ($exist)
					{
						$AVE_DB->Query("
							UPDATE
								" . PREFIX . "_rubric_permissions
							SET
								rubric_permission = '" . $rubric_permission . "'
							WHERE
								user_group_id = '" . $user_group_id . "'
							AND
								rubric_id = '" . $rubric_id . "'
						");
					}
					else
					{
						$AVE_DB->Query("
							INSERT
								" . PREFIX . "_rubric_permissions
							SET
								rubric_id = '" . $rubric_id . "',
								user_group_id = '" . $user_group_id . "',
								rubric_permission = '" . $rubric_permission . "'
						");
					}
				}

				$AVE_DB->Query("
					UPDATE
						" . PREFIX . "_rubrics
					SET
						rubric_changed = '" . time() . "'
					WHERE
						Id = '" . $rubric_id . "'
				");

				$AVE_DB->clearCache('rub_' . $rubric_id);

				$message = $AVE_Template->get_config_vars('RUBRIC_SAVED_PERMS');
				$header = $AVE_Template->get_config_vars('RUBRIC_SUCCESS');
				$theme = 'accept';

				reportLog($AVE_Template->get_config_vars('RUBRIK_REPORT_PERMISION') . ' (' . stripslashes(htmlspecialchars($this->rubricNameByIdGet($rubric_id)->rubric_title)) . ') (Id:' . $rubric_id . ')');

				if (isAjax())
					echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
				else
					header('Location:index.php?do=rubs&action=edit&Id=' . $rubric_id . '&cp=' . SESSION);

				exit;
			}
		}

		/**
		 * Получить наименование и URL-префикс Рубрики по идентификатору
		 *
		 * @param int $rubric_id идентификатор Рубрики
		 * @return object наименование Рубрики
		 */
		function rubricNameByIdGet($rubric_id = 0)
		{
			global $AVE_DB;

			static $rubrics = array();

			if (! isset($rubrics[$rubric_id]))
			{
				$rubrics[$rubric_id] = $AVE_DB->Query("
					SELECT
						rubric_title,
						rubric_alias,
						rubric_description
					FROM
						" . PREFIX . "_rubrics
					WHERE
						Id = '" . $rubric_id . "'
					LIMIT 1
				")->FetchRow();
			}

			return $rubrics[$rubric_id];
		}

		/**
		 * Формирование прав доступа Групп пользователей на все Рубрики
		 *
		 */
		function rubricPermissionFetch()
		{
			global $AVE_DB, $AVE_Document, $AVE_Template;

			$items = array();

			$sql = $AVE_DB->Query("
				SELECT
					Id,
					rubric_title,
					rubric_docs_active
				FROM
					" . PREFIX . "_rubrics
				ORDER
					BY rubric_position
			");

			while ($row = $sql->FetchRow())
			{
				$AVE_Document->documentPermissionFetch($row->Id);

				if (defined('UGROUP') && UGROUP == 1) $row->Show = 1;
				elseif (isset($_SESSION[$row->Id . '_editown']) && $_SESSION[$row->Id . '_editown'] == 1) $row->Show = 1;
				elseif (isset($_SESSION[$row->Id . '_editall']) && $_SESSION[$row->Id . '_editall'] == 1) $row->Show = 1;
				elseif (isset($_SESSION[$row->Id . '_new'])     && $_SESSION[$row->Id . '_new']     == 1) $row->Show = 1;
				elseif (isset($_SESSION[$row->Id . '_newnow'])  && $_SESSION[$row->Id . '_newnow']  == 1) $row->Show = 1;
				elseif (isset($_SESSION[$row->Id . '_alles'])   && $_SESSION[$row->Id . '_alles']   == 1) $row->Show = 1;

				array_push($items, $row);
			}

			$AVE_Template->assign('rubrics', $items);
		}

		/**
		 * Получить
		 */
		function rubricAliasAdd()
		{
			global $AVE_DB, $AVE_Template;

				$sql = $AVE_DB->Query("
					SELECT
						a.rubric_title,
						b.rubric_field_title,
						b.rubric_field_alias
					FROM
						" . PREFIX . "_rubrics AS a
					JOIN
						 " . PREFIX . "_rubric_fields AS b
					WHERE
						a.Id = '" . $_REQUEST['rubric_id'] . "'
					AND
						b.Id = '" . $_REQUEST['field_id'] . "'
				")->FetchAssocArray();

			$AVE_Template->assign($sql);
			$AVE_Template->assign('content', $AVE_Template->fetch('rubs/alias.tpl'));
		}


		function rubricAliasCheck($rubric_id, $field_id, $value)
		{
			global $AVE_DB, $AVE_Template;

			$errors = array();

			if (! intval($rubric_id)>0)
				$errors[] = $AVE_Template->get_config_vars('RUBRIK_ALIAS_RUBID');

			if (! intval($field_id)>0)
				$errors[] = $AVE_Template->get_config_vars('RUBRIK_ALIAS_FIELDID');

			if (! preg_match('/^[A-Za-z][[:word:]]{0,19}$/', $value))
				$errors[] = $AVE_Template->get_config_vars('RUBRIK_ALIAS_MATCH');

			//Проверяем есть такой алиас уже
			$res = $AVE_DB->Query("
				SELECT
					COUNT(*)
				FROM
					" . PREFIX . "_rubric_fields
				WHERE
					Id <> " . intval($field_id) . "
					AND rubric_id = " . intval($rubric_id) . "
					AND rubric_field_alias = '" . addslashes($value) . "'
				")->GetCell();

			if ($res > 0)
				$errors[] = $AVE_Template->get_config_vars('RUBRIK_ALIAS_MATCH');

			if (empty($errors))
			{
				$AVE_DB->Query("
					UPDATE
						" . PREFIX . "_rubric_fields
					SET
						rubric_field_alias = '" . addslashes($value) . "'
					WHERE
						Id = '" . intval($field_id) . "'
					AND
						rubric_id = '" . intval($rubric_id) . "'
				");

				$AVE_Template->assign('success', true);
			}
			else
			{
				$AVE_Template->assign('errors', $errors);
			}

			$sql = $AVE_DB->Query("
				SELECT
					a.rubric_title,
					b.rubric_field_title,
					b.rubric_field_alias
				FROM
					" . PREFIX . "_rubrics AS a
				JOIN
					 " . PREFIX . "_rubric_fields AS b
				WHERE
					a.Id = '" . $_REQUEST['rubric_id'] . "'
				AND
					b.Id = '" . $_REQUEST['field_id'] . "'
			")->FetchAssocArray();

			$AVE_DB->Query("
				UPDATE
					" . PREFIX . "_rubrics
				SET
					rubric_changed_fields = '" . time() . "'
				WHERE
					Id = '" . intval($rubric_id) . "'
			");

			$AVE_Template->assign($sql);

			$AVE_Template->assign('content', $AVE_Template->fetch('rubs/alias.tpl'));
	 	}

		function rubricFieldTemplate()
		{
			global $AVE_DB, $AVE_Template;

			$field = $AVE_DB->Query("
				SELECT
					a.rubric_title,
					b.rubric_field_default,
					b.rubric_field_title,
					b.rubric_field_template,
					b.rubric_field_template_request,
					b.rubric_field_description
				FROM
					" . PREFIX . "_rubrics AS a
				JOIN
					 " . PREFIX . "_rubric_fields AS b
				WHERE
					a.Id = '" . $_REQUEST['rubric_id'] . "'
				AND
					b.Id = '" . $_REQUEST['field_id'] . "'
			")->FetchAssocArray();

			$AVE_Template->assign($field);
			$AVE_Template->assign('content', $AVE_Template->fetch('rubs/field_template.tpl'));
		}


		function rubricFieldTemplateSave($id, $rubric_id)
		{
			global $AVE_DB, $AVE_Template;

			$sql = $AVE_DB->Query("
				UPDATE
					" . PREFIX . "_rubric_fields
				SET
					rubric_field_default          = '" . $_POST['rubric_field_default'] . "',
					rubric_field_template         = '" . $_POST['rubric_field_template'] . "',
					rubric_field_template_request = '" . $_POST['rubric_field_template_request'] . "',
					rubric_field_description      = '" . $_POST['rubric_field_description'] . "'
				WHERE
					Id = '" . $id . "'
			");

			$AVE_DB->Query("
				UPDATE
					" . PREFIX . "_rubrics
				SET
					rubric_changed_fields = '" . time() . "'
				WHERE
					Id = '" . intval($rubric_id) . "'
			");

			$AVE_DB->clearCache('rub_' . $rubric_id);

			if ($sql === false)
			{
				$message = $AVE_Template->get_config_vars('RUBRIC_SAVED_FLDTPL_ERR');
				$header = $AVE_Template->get_config_vars('RUBRIK_ERROR');
				$theme = 'error';

				if (isAjax() && ! $_REQUEST['save'])
					echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
				else
					$this->rubricFieldTemplate();

				exit;
			}
			else
			{
				$message = $AVE_Template->get_config_vars('RUBRIC_SAVED_FLDTPL');
				$header = $AVE_Template->get_config_vars('RUBRIC_SUCCESS');
				$theme = 'accept';

				if (isAjax())
				{
					echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
					exit;
				}
			}
		}


		function rubricFieldChange($field_id, $rubric_id)
		{
			global $AVE_DB, $AVE_Template;

			$sql = $AVE_DB->Query("
				SELECT
					*
				FROM
					" . PREFIX . "_rubric_fields
				WHERE
					rubric_id = '" . $rubric_id . "'
				AND
					Id = " . $field_id . "
			")->FetchAssocArray();

			$AVE_Template->assign('rf', $sql);
			$AVE_Template->assign('fields', get_field_type());
			$AVE_Template->assign('content', $AVE_Template->fetch('rubs/change.tpl'));
		}


		function rubricFieldChangeSave($field_id, $rubric_id)
		{
			global $AVE_DB, $AVE_Template;

			$AVE_DB->Query("
				UPDATE
					" . PREFIX . "_rubric_fields
				SET
					rubric_field_type = '" . trim($_POST['rubric_field_type']) . "'
				WHERE
					Id = '" . $field_id . "'
				AND
					rubric_id = '" . $rubric_id . "'
			");

			$sql = $AVE_DB->Query("
				SELECT
					*
				FROM
					" . PREFIX . "_rubric_fields
				WHERE
					rubric_id = '" . $rubric_id . "'
				AND
					Id = " . $field_id . "
			")->FetchAssocArray();

			$AVE_DB->Query("
				UPDATE
					" . PREFIX . "_rubrics
				SET
					rubric_changed_fields = '" . time() . "'
				WHERE
					Id = '" . intval($rubric_id) . "'
			");

			$AVE_Template->assign('rf', $sql);
			$AVE_Template->assign('fields', get_field_type());
			$AVE_Template->assign('content', $AVE_Template->fetch('rubs/change.tpl'));
		}


		function rubricFieldsGroups($rubric_id)
		{
			global $AVE_DB, $AVE_Template;

			$sql = $AVE_DB->Query("
				SELECT
					*
				FROM
					" . PREFIX . "_rubric_fields_group
				WHERE
					rubric_id = '" . $rubric_id . "'
				ORDER BY
					group_position
			");

			$groups = array();

			while ($row = $sql->FetchRow())
				array_push($groups, $row);

			$AVE_Template->assign('rubric', $this->rubricNameByIdGet($rubric_id));
			$AVE_Template->assign('groups', $groups);
			$AVE_Template->assign('content', $AVE_Template->fetch('rubs/fields_groups.tpl'));
		}

		/**
		 * Сортировка групп полей рубрики
		 *
		 * @param array $sorted	последовательность id полей
		 */
		function rubricFieldsGroupsSort()
		{
			global $AVE_DB, $AVE_Template;

			foreach ($_REQUEST['sort'] as $position => $group_id)
			{
				$position++;

				$AVE_DB->Query("
					UPDATE
						" . PREFIX . "_rubric_fields_group
					SET
						group_position = '" . (int)$position . "'
					WHERE
						Id = '" . (int)$group_id . "'
				");
			}

			if (isAjax())
			{
				$message = $AVE_Template->get_config_vars('RUBRIK_SORTED');
				$header = $AVE_Template->get_config_vars('RUBRIK_FILDS_SUCCESS');
				$theme = 'accept';

				echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
				exit;
			}
		}


		function rubricNewGroupFields($rubric_id)
		{
			global $AVE_DB;

			$position = $AVE_DB->Query("
				SELECT
					MAX(group_position)
				FROM
					" . PREFIX . "_rubric_fields_group
				WHERE
					rubric_id = '" . $rubric_id . "'
			")->GetCell();

			$position++;

			$AVE_DB->Query("
				INSERT
					" . PREFIX . "_rubric_fields_group
				SET
					rubric_id = '" . $rubric_id . "',
					group_position = '" . $position . "',
					group_title= '" . $_REQUEST['group_title'] . "'
			");

			header('Location:index.php?do=rubs&action=fieldsgroups&Id=' . $rubric_id . '&cp=' . SESSION);
			exit;
		}


		function rubricEditGroupFields($rubric_id)
		{
			global $AVE_DB;

			foreach($_REQUEST['group_title'] as $k => $v)
			{
				$AVE_DB->Query("
					UPDATE
						" . PREFIX . "_rubric_fields_group
					SET
						group_title= '" . $v . "'
					WHERE
						Id = '" . $k . "'
				");
			}

			header('Location:index.php?do=rubs&action=fieldsgroups&Id=' . $rubric_id . '&cp=' . SESSION);
			exit;
		}


		function rubricDelGroupFields($Id, $rubric_id)
		{
			global $AVE_DB;

			$AVE_DB->Query("
				DELETE FROM
					" . PREFIX . "_rubric_fields_group
				WHERE
					Id = '" . $Id . "'
			");

			$AVE_DB->Query("
				UPDATE
					" . PREFIX . "_rubric_fields
				SET
					rubric_field_group = '0'
				WHERE
					rubric_field_group = '" . $Id . "'
				AND
					rubric_id = '" . $rubric_id . "'
			");

			header('Location:index.php?do=rubs&action=fieldsgroups&Id=' . $rubric_id . '&cp=' . SESSION);
			exit;
		}


		function rubricFieldGroupChange($field_id, $rubric_id)
		{

			global $AVE_DB, $AVE_Template;

			$sql = $AVE_DB->Query("
				SELECT
					*
				FROM
					" . PREFIX . "_rubric_fields
				WHERE
					rubric_id = '" . $rubric_id . "'
				AND
					Id = " . $field_id . "
			")->FetchAssocArray();

			$AVE_Template->assign('rf', $sql);
			$AVE_Template->assign('groups', $this->get_rubric_fields_group($rubric_id));
			$AVE_Template->assign('content', $AVE_Template->fetch('rubs/groups.tpl'));
		}


		function rubricFieldGroupChangeSave($field_id, $rubric_id)
		{
			global $AVE_DB, $AVE_Template;

			$AVE_DB->Query("
				UPDATE
					" . PREFIX . "_rubric_fields
				SET
					rubric_field_group = '" . trim($_POST['rubric_field_group']) . "'
				WHERE
					Id = '" . $field_id . "'
				AND
					rubric_id = '" . $rubric_id . "'
			");

			$sql = $AVE_DB->Query("
				SELECT
					*
				FROM
					" . PREFIX . "_rubric_fields
				WHERE
					rubric_id = '" . $rubric_id . "'
				AND
					Id = " . $field_id . "
			")->FetchAssocArray();

			$AVE_Template->assign('rf', $sql);
			$AVE_Template->assign('groups', $this->get_rubric_fields_group($rubric_id));
			$AVE_Template->assign('content', $AVE_Template->fetch('rubs/groups.tpl'));
		}


		// Список дополнительных шаблон для данной рубрики
		function tmplsList()
		{
			global $AVE_DB, $AVE_Template;

			$templates = array();

			$num = $AVE_DB->Query("
				SELECT
					COUNT(*)
				FROM
					" . PREFIX . "_rubric_templates
				WHERE
					rubric_id = '" . $_REQUEST['Id'] . "'
			")->GetCell();

			$page_limit = $this->_limit;

			$pages = ceil($num / $page_limit);

			$set_start = get_current_page() * $page_limit - $page_limit;

			if ($num > $page_limit)
			{
				$page_nav = " <a class=\"pnav\" href=\"index.php?do=rubs&action=tmpls&page={s}&cp=" . SESSION . "\">{t}</a> ";
				$page_nav = get_pagination($pages, 'page', $page_nav);
				$AVE_Template->assign('page_nav', $page_nav);
			}

			$sql = $AVE_DB->Query("
				SELECT
					rub.*,
					rubrics.rubric_title,
					(SELECT 1 FROM " . PREFIX . "_documents WHERE rubric_id = rub.rubric_id AND rubric_tmpl_id = rub.id LIMIT 1) AS doc_count
				FROM
					" . PREFIX . "_rubric_templates AS rub
				LEFT JOIN
					" . PREFIX . "_rubrics AS rubrics
						ON rubrics.Id = rub.rubric_id
				WHERE
					rub.rubric_id = '" . (int)$_REQUEST['Id'] . "'
				GROUP
					BY rub.id
				ORDER
					BY rub.id
				LIMIT
					" . $set_start . "," . $page_limit
			);

			while ($row = $sql->FetchRow())
			{
				$row->author_id = get_username_by_id($row->author_id);
				array_push($templates, $row);
			}

			$rubric = $this->rubricNameByIdGet((int)$_REQUEST['Id']);

			$AVE_Template->assign('rubric', $rubric);
			$AVE_Template->assign('templates', $templates);
		}

		/**
		 * Вывод шаблона рубрики
		 *
		 * @param int $show
		 * @param int $extern
		 */
		function tmplsEdit()
		{
			global $AVE_DB, $AVE_Template;

			$tmpls_id = (isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) ? $_REQUEST['id'] : 0;
			$rubric_id = (int)$_REQUEST['rubric_id'];

			if ($tmpls_id)
			{
				$template = $AVE_DB->Query("
					SELECT
						title,
						template
					FROM
						" . PREFIX . "_rubric_templates
					WHERE
						id = '" . $tmpls_id . "'
				")
				->FetchRow();
			}

			if ($_REQUEST['action'] == 'tmpls_from')
			{
				$template = $AVE_DB->Query("
					SELECT
						rubric_title as title,
						rubric_template as template
					FROM
						" . PREFIX . "_rubrics
					WHERE
						Id = '" . $rubric_id . "'
				")
				->FetchRow();
			}

			if ($_REQUEST['action'] == 'tmpls_copy')
			{
				$template = $AVE_DB->Query("
					SELECT
						title,
						template
					FROM
						" . PREFIX . "_rubric_templates
					WHERE
						id = '" . $_REQUEST['tmpls_id'] . "'
				")
				->FetchRow();
			}

			// Поля
			$sql = $AVE_DB->Query("
				SELECT
					a.*,
					b.group_title,
					b.group_description,
					b.group_position
				FROM
					" . PREFIX . "_rubric_fields AS a
				LEFT JOIN
					" . PREFIX . "_rubric_fields_group AS b
					ON a.rubric_field_group = b.Id
				WHERE
					a.rubric_id = '" . $rubric_id . "'
				ORDER BY
					b.group_position ASC, a.rubric_field_position ASC
			");

			$fields_list = array();
			$drop_down_fields = array();

			while ($row = $sql->FetchRow())
			{
				$group_id = ($row->rubric_field_group) ? $row->rubric_field_group : 0;

				if ($row->rubric_field_type == 'drop_down' || $row->rubric_field_type == 'drop_down_key')
					array_push($drop_down_fields, $row->Id);

				$fields_list[$group_id]['group_position'] = ($row->group_position) ? $row->group_position : 100;
				$fields_list[$group_id]['group_title'] = $row->group_title;
				$fields_list[$group_id]['group_description'] = $row->group_description;
				$fields_list[$group_id]['fields'][$row->Id]['Id'] = $row->Id;
				$fields_list[$group_id]['fields'][$row->Id]['rubric_id'] = $row->rubric_id;
				$fields_list[$group_id]['fields'][$row->Id]['rubric_field_group'] = $row->rubric_field_group;
				$fields_list[$group_id]['fields'][$row->Id]['rubric_field_alias'] = $row->rubric_field_alias;
				$fields_list[$group_id]['fields'][$row->Id]['rubric_field_title'] = $row->rubric_field_title;
				$fields_list[$group_id]['fields'][$row->Id]['rubric_field_type'] = $row->rubric_field_type;
				$fields_list[$group_id]['fields'][$row->Id]['rubric_field_numeric'] = $row->rubric_field_numeric;
				$fields_list[$group_id]['fields'][$row->Id]['rubric_field_default'] = $row->rubric_field_default;
				$fields_list[$group_id]['fields'][$row->Id]['rubric_field_search'] = $row->rubric_field_search;
			}

			$fields_list = msort($fields_list, 'group_position');

			$AVE_Template->assign('groups_count', count($fields_list));
			$AVE_Template->assign('fields_list', $fields_list);

			$AVE_Template->assign('field_array', get_field_type());

			$rubric = $this->rubricNameByIdGet($rubric_id);

			$AVE_Template->assign('rubric', $rubric);

			$AVE_Template->assign('template', $template);

			$AVE_Template->assign('formaction', 'index.php?do=rubs&action=tmpls_edit&sub=save&id=' . $tmpls_id . '&rubric_id=' . $_REQUEST['rubric_id'] . '&cp=' . SESSION);
			$AVE_Template->assign('content', $AVE_Template->fetch('rubs/tmpls_form.tpl'));
		}


		/**
		 * Редактирование шаблона рубрики
		 *
		 * @param string $data
		 */
		function tmplsSave($template = '', $title = '')
		{
			global $AVE_DB, $AVE_Template;

			$tmpls_id = (int)$_REQUEST['id'];
			$rubric_id = (int)$_REQUEST['rubric_id'];

			if ($tmpls_id)
			{
				$sql = $AVE_DB->Query("
					UPDATE
						" . PREFIX . "_rubric_templates
					SET
						title = '" . $title . "',
						template = '" . $template . "'
					WHERE
						id = '" . $tmpls_id . "'
				");
			}
			else
			{
				$sql = $AVE_DB->Query("
					INSERT INTO
						" . PREFIX . "_rubric_templates
					SET
						title = '" . $title . "',
						template = '" . $template . "',
						rubric_id = '" . $rubric_id . "',
						author_id = '" . UID . "',
						created = '" . time() . "'
				");

				$tmpls_id = $AVE_DB->InsertId();
			}

			$AVE_DB->Query("
				UPDATE
					" . PREFIX . "_rubrics
				SET
					rubric_changed = '" . time() . "'
				WHERE
					Id = '" . intval($rubric_id) . "'
			");

			$AVE_DB->clearCache('rub_' . $rubric_id);

			if ($sql === false)
			{
				$message = $AVE_Template->get_config_vars('RUBRIC_SAVED_TPL_ERR');
				$header = $AVE_Template->get_config_vars('RUBRIK_ERROR');
				$theme = 'error';
			}
			else
			{
				$message = $AVE_Template->get_config_vars('RUBRIC_SAVED_TPL');
				$header = $AVE_Template->get_config_vars('RUBRIC_SUCCESS');
				$theme = 'accept';
				reportLog($AVE_Template->get_config_vars('RUBRIC_TEMPL_REPORT') . ' ' . stripslashes(htmlspecialchars($this->rubricNameByIdGet($rubric_id)->rubric_title)) . ' (Id шаблона:' . $tmpls_id . ')');
			}

			if (isAjax())
			{
				echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
			}
			else
			{
				$AVE_Template->assign('message', $message);
				header('Location:index.php?do=rubs&action=tmpls&Id='.$rubric_id.'&cp=' . SESSION);
			}

			exit;
		}


		function tmplsDelete()
		{
			global $AVE_DB, $AVE_Template;

			$rubric_id = (int)$_REQUEST['rubric_id'];
			$tmpls_id = (int)$_REQUEST['tmpls_id'];

			$rubric_not_empty = $AVE_DB->Query("
				SELECT 1
					FROM " . PREFIX . "_documents
				WHERE
					rubric_id = '" . $rubric_id . "'
				AND
					rubric_tmpl_id = '" . $tmpls_id . "'
				LIMIT 1
			")->GetCell();

			if (! $rubric_not_empty)
			{
				$AVE_DB->Query("
					DELETE
					FROM
						" . PREFIX . "_rubric_templates
					WHERE
						id = '" . $tmpls_id . "'
					AND
						rubric_id = '" . $rubric_id . "'
				");

				$AVE_DB->Query("
					UPDATE
						" . PREFIX . "_rubrics
					SET
						rubric_changed = '" . time() . "'
					WHERE
						Id = '" . intval($rubric_id) . "'
				");

				$AVE_DB->clearCache('rub_' . $rubric_id);

				// Сохраняем системное сообщение в журнал
				reportLog($AVE_Template->get_config_vars('RUBRIC_TMPLS_LOG_DEL') . ' - ' . stripslashes(htmlspecialchars($this->rubricNameByIdGet($rubric_id)->rubric_title, ENT_QUOTES)) . ' (Id шаблона: '.$rubric_id.')');
			}

			header('Location:index.php?do=rubs&action=tmpls&Id='.$rubric_id.'&cp=' . SESSION);
			exit;
		}



		function _get_fields_type($type = null)
		{
			static $fields;

			if (is_array($fields))
				return $fields;

			$arr = get_defined_functions();

			$fields = array();
			$field = array();

			foreach ($arr['user'] as $v)
			{
				if (trim(substr($v, 0, strlen('get_field_'))) == 'get_field_')
				{
					$d = '';

					$name = @$v('', 'name', '', '', 0, $d);

					$id = substr($v, strlen('get_field_'));

					if ($name != false && is_string($name))
						$fields[] = array('id' => $id,'name' => (isset($fields_vars[$name])
								? $fields_vars[$name]
								: $name));

					if (! empty($type) && $id == $type)
						$field =  array('id' => $id,'name' => (isset($fields_vars[$name])
								? $fields_vars[$name]
								: $name));
				}
			}

			$fields = msort($fields, array('name'));

			return (! empty($type)) ? $field : $fields;
		}


		function ShowFields()
		{
			global $AVE_DB, $AVE_Template;

			$rubric_id = (int)$_REQUEST['Id'];

			$sql = $AVE_DB->Query("
				SELECT
					rubric_field_type
				FROM
					" . PREFIX . "_rubric_fields
				WHERE
					rubric_id = '".$rubric_id."'
				GROUP BY
					rubric_field_type
			");

			$enable = array();

			while ($row = $sql->FetchArray())
				$enable[] = $row['rubric_field_type'];

			$fields = $this->_get_fields_type();

			foreach ($fields as $field)
			{
				$exists[$field['id']]['adm'] = file_exists(BASE_DIR . '/fields/' . $field['id'] . '/tpl/field.tpl');
				$exists[$field['id']]['doc'] = file_exists(BASE_DIR . '/fields/' . $field['id'] . '/tpl/field-doc.tpl');
				$exists[$field['id']]['req'] = file_exists(BASE_DIR . '/fields/' . $field['id'] . '/tpl/field-req.tpl');
			}

			$sql = $AVE_DB->Query("
				SELECT
					rubric_title,
					rubric_linked_rubric,
					rubric_description
				FROM
					" . PREFIX . "_rubrics
				WHERE
					id = '" . $rubric_id . "'
				LIMIT 1
			");

			$rubric = $sql->FetchRow();

			$AVE_Template->assign('rubric', $rubric);
			$AVE_Template->assign("enable", $enable);
			$AVE_Template->assign("exists", $exists);
			$AVE_Template->assign("fields", $fields);
			$AVE_Template->assign("content", $AVE_Template->fetch('rubs/_fields_list.tpl'));
		}


		function ShowFieldsByType($fld)
		{
			global $AVE_DB, $AVE_Template;

			$rubric_id = (int)$_REQUEST['rubric_id'];

			$sql = $AVE_DB->Query("
				SELECT
					a.Id,
					a.rubric_id,
					a.rubric_field_type,
					a.rubric_field_title,
					b.rubric_title
				FROM
					" . PREFIX . "_rubric_fields AS a
				LEFT JOIN
					" . PREFIX . "_rubrics AS b
					ON a.rubric_id = b.Id
				WHERE
					a.rubric_field_type = '" . $fld ."'
					AND
					a.rubric_id = '".$rubric_id."'
				ORDER BY
					a.rubric_id
			");

			$rubrics = array();

			while ($row = $sql->FetchRow())
			{
				$rubrics[$row->rubric_id]['rubric_id'] = $row->rubric_id;
				$rubrics[$row->rubric_id]['rubric_title'] = $row->rubric_title;
				$rubrics[$row->rubric_id]['rubric_field_type'] = $row->rubric_field_type;
				$rubrics[$row->rubric_id]['fields'][$row->Id]['id'] = $row->Id;
				$rubrics[$row->rubric_id]['fields'][$row->Id]['title'] = $row->rubric_field_title;
				$rubrics[$row->rubric_id]['fields'][$row->Id]['adm_tpl'] = file_exists(BASE_DIR . '/fields/' . $fld . '/tpl/field-' . $row->Id . '.tpl');
				$rubrics[$row->rubric_id]['fields'][$row->Id]['doc_tpl'] = file_exists(BASE_DIR . '/fields/' . $fld . '/tpl/field-doc-' . $row->Id . '.tpl');
				$rubrics[$row->rubric_id]['fields'][$row->Id]['req_tpl'] = file_exists(BASE_DIR . '/fields/' . $fld . '/tpl/field-req-' . $row->Id . '.tpl');
				$rubrics[$row->rubric_id]['fields'][$row->Id]['adm_main'] = file_exists(BASE_DIR . '/fields/' . $fld . '/tpl/field.tpl');
				$rubrics[$row->rubric_id]['fields'][$row->Id]['doc_main'] = file_exists(BASE_DIR . '/fields/' . $fld . '/tpl/field-doc.tpl');
				$rubrics[$row->rubric_id]['fields'][$row->Id]['req_main'] = file_exists(BASE_DIR . '/fields/' . $fld . '/tpl/field-req.tpl');
			}

			$sql = $AVE_DB->Query("
				SELECT
					rubric_title,
					rubric_linked_rubric,
					rubric_description
				FROM
					" . PREFIX . "_rubrics
				WHERE
					id = '" . $rubric_id . "'
				LIMIT 1
			");

			$rubric = $sql->FetchRow();

			$AVE_Template->assign('rubric', $rubric);
			$AVE_Template->assign('main', $this->_get_fields_type($fld));
			$AVE_Template->assign("rubrics", $rubrics);
			$AVE_Template->assign("content", $AVE_Template->fetch('rubs/_field_list.tpl'));
		}


		function EditFieldTpl($id = '', $fld, $type)
		{
			global $AVE_DB, $AVE_Template, $_fm_dir;

			switch ($type)
			{
				case 'adm':
					$file = BASE_DIR . '/fields/' . $fld . '/tpl/field-' . $id . '.tpl';
					$source = BASE_DIR . '/fields/' . $fld . '/tpl/field.tpl';
					break;

				case 'doc':
					$file = BASE_DIR . '/fields/' . $fld . '/tpl/field-doc-' . $id . '.tpl';
					$source = BASE_DIR . '/fields/' . $fld . '/tpl/field-doc.tpl';
					break;

				case 'req':
					$file = BASE_DIR . '/fields/' . $fld . '/tpl/field-req-' . $id . '.tpl';
					$source = BASE_DIR . '/fields/' . $fld . '/tpl/field-req.tpl';
					break;
			}

			if (empty($id))
				$file = $source;

			if (file_exists($file))
				$code_text = file_get_contents($file);
			else
				$code_text = file_get_contents($source);

			$sql = $AVE_DB->Query("
				SELECT
					a.rubric_field_title,
					b.rubric_title
				FROM
					" . PREFIX . "_rubric_fields AS a
				LEFT JOIN
					" . PREFIX . "_rubrics AS b
					ON a.rubric_id = b.Id
				WHERE
					a.rubric_field_type = '" . $fld ."'
				AND
					a.Id = '" . $id ."'
			")->FetchAssocArray();

			$params =
				array(
					'id' => $id,
					'fld' => $fld,
					'type' => $type,
					'func' => (file_exists($file) ? 'edit' : 'new'),
					'field' => $sql,
				);

			$AVE_Template->assign('main', $this->_get_fields_type($fld));
			$AVE_Template->assign('params', $params);
			$AVE_Template->assign('code_text', $code_text);
			$AVE_Template->assign("content", $AVE_Template->fetch('rubs/_field_code.tpl'));
		}


		/**
		 * Сохранение шаблона
		 *
		 */
		function SaveFieldTpl($id = '', $fld, $type, $func)
		{
			global $AVE_DB;

			switch ($type)
			{
				case 'adm':
					$file = (! empty($id))
						? BASE_DIR . '/fields/' . $fld . '/tpl/field-' . $id . '.tpl'
						: BASE_DIR . '/fields/' . $fld . '/tpl/field.tpl';
					break;

				case 'doc':
					$file = (! empty($id))
						? BASE_DIR . '/fields/' . $fld . '/tpl/field-doc-' . $id . '.tpl'
						: BASE_DIR . '/fields/' . $fld . '/tpl/field-doc.tpl';
					break;

				case 'req':
					$file = (! empty($id))
						? BASE_DIR . '/fields/' . $fld . '/tpl/field-req-' . $id . '.tpl'
						: BASE_DIR . '/fields/' . $fld . '/tpl/field-req.tpl';
					break;
			}

			$data = stripcslashes($_REQUEST['code_text']);

			@file_put_contents($file, $data);
			chmod($file, 0644);

			$rubric_id = (int)$_REQUEST['rubric_id'];

			$AVE_DB->Query("
				UPDATE
					" . PREFIX . "_rubrics
				SET
					rubric_changed_fields = '" . time() . "'
				WHERE
					Id = '" . intval($rubric_id) . "'
			");

			$AVE_DB->clearCache('rub_' . $rubric_id);

			$message = 'Шаблон успешнно сохранен';
			$header = 'Выполнено';
			$theme = 'accept';

			echo json_encode(
				array(
					'message' => $message,
					'header' => $header,
					'theme' => $theme)
				);

			exit;
		}

		/**
		 * Удаление шаблона поля
		 *
		 */
		function DeleteFieldTpl($id, $fld, $type, $func)
		{
			global $AVE_DB;

			switch ($type)
			{
				case 'adm':
					$file = BASE_DIR . '/fields/' . $fld . '/tpl/field-' . $id . '.tpl';
					break;

				case 'doc':
					$file = BASE_DIR . '/fields/' . $fld . '/tpl/field-doc-' . $id . '.tpl';
					break;

				case 'req':
					$file = BASE_DIR . '/fields/' . $fld . '/tpl/field-req-' . $id . '.tpl';
					break;
			}

			@unlink($file);

			$rubric_id = $AVE_DB->Query("SELECT rubric_id FROM " . PREFIX . "_rubric_fields WHERE Id = '".$id."'")->GetCell();

			$AVE_DB->Query("
				UPDATE
					" . PREFIX . "_rubrics
				SET
					rubric_changed_fields = '" . time() . "'
				WHERE
					Id = '" . intval($rubric_id) . "'
			");

			$AVE_DB->clearCache('rub_' . $rubric_id);

			header('Location:' . get_referer_link());
			exit;
		}


		function clearTemplates($rubric_id)
		{

		}
	}
?>