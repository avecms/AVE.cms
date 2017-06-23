<?php

	/**
	 * AVE.cms
	 *
	 * Класс, предназначенный для работы шаблонами и пунктами меню навигаций
	 *
	 * @package AVE.cms
	 * @version 3.x
	 * @filesource
	 * @copyright © 2007-2014 AVE.cms, http://www.ave-cms.ru
	 *
	 */

	class AVE_Navigation
	{

	/**
	 *	Свойства класса
	 */


	/**
	 *	Внутренние методы класса
	 */

		/**
		 * Метод, предназначенный для удаления запрещённых символов
		 * и преобразование специальных символов в HTML сущности
		 *
		 * @param string $text
		 * @return string
		 */
		function _replace_wildcode($text)
		{
			//$text = html_entity_decode($text,ENT_QUOTES,'UTF-8');
			//$text = preg_replace('/\s/i',' ',$text);
			//$text = str_replace(array('ô','ç','é','è','ä','à','â','ü','ñ'),array('o','c','e','e','a','a','a','u','n'), $text);
			//$text = htmlspecialchars($text, ENT_QUOTES, '"&><\'');
			return $text;
		}

	/**
	 *	Внутренние методы
	 */

		/**
		 * Проверка алиаса тега на валидность и уникальность
		 */
		function navigationValidate ($alias = '', $id = 0)
		{
			global $AVE_DB;

			//-- Соответствие требованиям
			if (empty ($alias) || preg_match('/^[A-Za-z0-9-_]{1,20}$/i', $alias) !== 1 || is_numeric($alias))
				return 'syn';

			//-- Уникальность
			return !(bool)$AVE_DB->Query("
				SELECT 1
				FROM
					" . PREFIX . "_navigation
				WHERE
					alias = '" . $alias . "'
				AND
					navigation_id != '" . $id . "'
			")->GetCell();
		}


		/**
		 * Метод, предназначенный для вывода списка всех существующих меню навигаций в Паели управления
		 *
		 */
		function navigationList()
		{
			global $AVE_DB, $AVE_Template;

			$navigations = array();

			// Выполняем запрос к БД на получение списка всех меню навигаций
			$sql = $AVE_DB->Query("
				SELECT
					navigation_id,
					alias,
					title
				FROM
					" . PREFIX . "_navigation
				ORDER BY
					navigation_id ASC
			");

			// Формируем данные в массив
			while ($row = $sql->FetchRow())
			{
				array_push($navigations, $row);
			}

			// Передаем данные в шаблон для вывода и отображаем страницу со списком меню
			$AVE_Template->assign('nid', 0);
			$AVE_Template->assign('navigations', $navigations);
			$AVE_Template->assign('content', $AVE_Template->fetch('navigation/list.tpl'));
		}



		/**
		 * Метод, предназначенный для добавления нового меню
		 *
		 */
		function navigationNew()
		{
			global $AVE_DB, $AVE_Template, $AVE_User;

			// Определяем действие пользователя
			switch($_REQUEST['sub'])
			{
				// Если действие не определено, отображаем чистую форму для создания шаблона навигации
				case '':

					// Передаем данные в шаблон и отображаем страницу для добавления нового шаблона меню
					$AVE_Template->assign('groups', $AVE_User->userGroupListGet());
					$AVE_Template->assign('form_action', 'index.php?do=navigation&action=new&sub=save&cp=' . SESSION);
					$AVE_Template->assign('content', $AVE_Template->fetch('navigation/template.tpl'));
					break;


				// Если пользователь нажал на кнопку Добавить (Сохранить)
				case 'save':

					// Определяем название меню навигации
					$navigation_title			= (empty($_REQUEST['title']))   ? 'title' : $_REQUEST['title'];

					// Определяем шаблон оформления 1-го уровня ссылок
					// в меню. Если шаблон не указан пользователем,тогда
					// используем вариант "по умолчанию"
					$navigation_level1			= (empty($_REQUEST['level1']))  ? "<a target=\"[tag:target]\" href=\"[tag:link]\">[tag:linkname]</a>" : $_REQUEST['level1'];
					$navigation_level1_active	= (empty($_REQUEST['level1_active'])) ? "<a target=\"[tag:target]\" href=\"[tag:link]\" class=\"first_active\">[tag:linkname]</a>" : $_REQUEST['level1_active'];

					// Выполняем запрос к БД на добавление нового меню
					$AVE_DB->Query("
						INSERT INTO
							" . PREFIX . "_navigation
						SET
							navigation_id		= '',
							alias				= '" . $_REQUEST['alias'] . "',
							title				= '" . $navigation_title . "',
							level1				= '" . $navigation_level1 . "',
							level1_active		= '" . $navigation_level1_active . "',
							level2				= '" . $_REQUEST['level2'] . "',
							level2_active 		= '" . $_REQUEST['level2_active'] . "',
							level3				= '" . $_REQUEST['level3'] . "',
							level3_active 		= '" . $_REQUEST['level3_active'] . "',
							level1_begin		= '" . $_REQUEST['level1_begin'] . "',
							level2_begin		= '" . $_REQUEST['level2_begin'] . "',
							level3_begin		= '" . $_REQUEST['level3_begin'] . "',
							level1_end			= '" . $_REQUEST['level1_end'] . "',
							level2_end			= '" . $_REQUEST['level2_end'] . "',
							level3_end			= '" . $_REQUEST['level3_end'] . "',
							begin				= '" . $_REQUEST['begin'] . "',
							end					= '" . $_REQUEST['end'] . "',
							user_group			= '" . (empty($_REQUEST['user_group']) ? '' : implode(',', $_REQUEST['user_group'])) . "',
							expand_ext			= '" . $_REQUEST['expand_ext'] . "'
					");

					$navigation_id = $AVE_DB->getLastInsertId();

					// Сохраняем системное сообщение в журнал
					reportLog($AVE_Template->get_config_vars('NAVI_REPORT_NEW') . " (" . stripslashes($navigation_title) . ") (ID: $navigation_id)");

					// Выполянем переход к списку меню навигаций
					header('Location:index.php?do=navigation&cp=' . SESSION);
					break;
			}
		}



		/**
		 * Метод, предназначенный для редактирования шаблона навигации
		 *
		 * @param int $navigation_id идентификатор меню навигации
		 */
		function navigationEdit($navigation_id)
		{
			global $AVE_DB, $AVE_Template, $AVE_User;

			// Определяем действие пользователя
			switch ($_REQUEST['sub'])
			{
				// Если действие не определено, отображаем форму с данными для редактирования
				case '':

					// Выполняем запрос к БД и получаем всю информацию о данном меню
					$row = $AVE_DB->Query("
						SELECT *
						FROM
							" . PREFIX . "_navigation
						WHERE
							navigation_id = '" . $navigation_id . "'
					")->FetchRow();

					// Формируем список групп пользователей
					$row->user_group = explode(',', $row->user_group);

					// Формируем ряд переменных для использования в шаблоне и отображаем форм с данными для редактирования
					$AVE_Template->assign('nid', $navigation_id);
					$AVE_Template->assign('navigation', $row);
					$AVE_Template->assign('groups', $AVE_User->userGroupListGet());
					$AVE_Template->assign('form_action', 'index.php?do=navigation&action=templates&sub=save&navigation_id=' . $navigation_id . '&cp=' . SESSION);
					$AVE_Template->assign('content', $AVE_Template->fetch('navigation/template.tpl'));
					break;

				// Если пользователь нажал на кнопку Сохранить изменения
				case 'save':

					// Выполняем запрос к БД и обновляем информацию в таблице для данного меню
					$sql = $AVE_DB->Query("
						UPDATE " . PREFIX . "_navigation
						SET
							title				= '" . $_REQUEST['title'] . "',
							alias				= '" . $_REQUEST['alias'] . "',
							level1				= '" . $_REQUEST['level1'] . "',
							level1_active		= '" . $_REQUEST['level1_active'] . "',
							level2				= '" . $_REQUEST['level2'] . "',
							level2_active 		= '" . $_REQUEST['level2_active'] . "',
							level3				= '" . $_REQUEST['level3'] . "',
							level3_active 		= '" . $_REQUEST['level3_active'] . "',
							level1_begin		= '" . $_REQUEST['level1_begin'] . "',
							level2_begin		= '" . $_REQUEST['level2_begin'] . "',
							level3_begin		= '" . $_REQUEST['level3_begin'] . "',
							level1_end			= '" . $_REQUEST['level1_end'] . "',
							level2_end			= '" . $_REQUEST['level2_end'] . "',
							level3_end			= '" . $_REQUEST['level3_end'] . "',
							begin				= '" . $_REQUEST['begin'] . "',
							end					= '" . $_REQUEST['end'] . "',
							user_group			= '" . (empty($_REQUEST['user_group']) ? '' : implode(',', $_REQUEST['user_group'])) . "',
							expand_ext			= '" . $_REQUEST['expand_ext'] . "'
						WHERE
							navigation_id		= '" . $navigation_id . "'
					");

					//-- Стираем кеш навигации
					$this->clearCahe($navigation_id, $_REQUEST['alias']);

					if ($sql === false)
					{
						$message = $AVE_Template->get_config_vars('NAVI_REPORT_SAVED_ERR');
						$header = $AVE_Template->get_config_vars('NAVI_REPORT_ERROR');
						$theme = 'error';
					}
					else
					{
						$message = $AVE_Template->get_config_vars('NAVI_REPORT_SAVED');
						$header = $AVE_Template->get_config_vars('NAVI_REPORT_SUCCESS');
						$theme = 'accept';
						reportLog($AVE_Template->get_config_vars('NAVI_REPORT_EDIT') . " (" . stripslashes($_REQUEST['title']) . ") (ID: $navigation_id)");
					}

					if (isAjax())
					{
						echo json_encode(
							array(
								'message' => $message,
								'header' => $header,
								'theme' => $theme
								)
							);
					}
					else
					{
						$AVE_Template->assign('message', $message);
						header('Location:index.php?do=navigation&cp=' . SESSION);
					}
					exit;
			}
		}



		/**
		 * Метод, предназначенный для копирования шаблона меню
		 *
		 * @param int $navigation_id идентификатор меню навигации источника
		 */
		function navigationCopy($navigation_id)
		{
			global $AVE_DB, $AVE_Template;

			// Если в запросе указано числовое значение id меню
			if (is_numeric($navigation_id))
			{
				// Выполняем запрос к БД на получение информации о копируемом меню
				$row = $AVE_DB->Query("
					SELECT *
					FROM
						" . PREFIX . "_navigation
					WHERE
						navigation_id = '" . $navigation_id . "'
				")->FetchRow();

				// Если данные получены, тогда
				if ($row)
				{
					// Выполняем запрос к БД на добавление нового меню и сохраняем информацию с учетом данных,
					// полученных в предыдущем запросе к БД
					$AVE_DB->Query("
						INSERT INTO
							" . PREFIX . "_navigation
						SET
							navigation_id		= '',
							title				= '" . addslashes((empty($_REQUEST['title']) ? $row->title : $_REQUEST['title'])) . "',
							alias				= '',
							level1				= '" . addslashes($row->level1) . "',
							level1_active		= '" . addslashes($row->level1_active) . "',
							level2				= '" . addslashes($row->level2) . "',
							level2_active 		= '" . addslashes($row->level2_active) . "',
							level3				= '" . addslashes($row->level3) . "',
							level3_active 		= '" . addslashes($row->level3_active) . "',
							level1_begin		= '" . addslashes($row->level1_begin) . "',
							level2_begin		= '" . addslashes($row->level2_begin) . "',
							level3_begin		= '" . addslashes($row->level3_begin) . "',
							level1_end			= '" . addslashes($row->level1_end) . "',
							level2_end			= '" . addslashes($row->level2_end) . "',
							level3_end			= '" . addslashes($row->level3_end) . "',
							begin				= '" . addslashes($row->begin) . "',
							end					= '" . addslashes($row->end) . "',
							user_group			= '" . addslashes($row->user_group) . "',
							expand_ext			= '" . $row->expand_ext . "'
					");

					// Сохраняем системное сообщение в журнал
					reportLog($AVE_Template->get_config_vars('NAVI_REPORT_COPY') . " (" . (empty($_REQUEST['title']) ? $row->title : $_REQUEST['title']) . ") (ID: $navigation_id)");
				}
			}

			// Выполянем переход к списку меню навигаций
			header('Location:index.php?do=navigation&cp=' . SESSION);
		}



		/**
		 * Метод, предназначенный для удаления меню навигации и всех пунктов относящихся к нему
		 *
		 * @param int $navigation_id идентификатор меню навигации
		 */
		function navigationDelete($navigation_id)
		{
			global $AVE_DB, $AVE_Template;

			// Если id меню числовой и это не первое меню (id не 1)
			if (is_numeric($navigation_id) && $navigation_id != 1)
			{

				 $sql= $AVE_DB->Query("
					SELECT *
					FROM
						" . PREFIX . "_navigation
					WHERE
						navigation_id = '" . $navigation_id . "'
				")->FetchRow();

				//-- Стираем кеш навигации
				$this->clearCahe($navigation_id, $sql->alias);

				//-- Выполняем запрос к БД на удаление общей информации и шаблона оформления меню
				$AVE_DB->Query("DELETE FROM " . PREFIX . "_navigation WHERE navigation_id = '" . $navigation_id . "'");
				//-- Выполняем запрос к БД на удаление всех пунктов для данного меню
				$AVE_DB->Query("DELETE FROM " . PREFIX . "_navigation_items WHERE navigation_id = '" . $navigation_id . "'");

				//-- Сохраняем системное сообщение в журнал
				reportLog($AVE_Template->get_config_vars('NAVI_REPORT_DEL') . " (" . stripslashes($sql->title) . ") (ID: $navigation_id)");
			}

			//-- Выполянем переход к списку меню навигаций
			header('Location:index.php?do=navigation&cp=' . SESSION);
		}



		/**
		 * Метод, предназначенный для получения списка всех пунктов у всех меню навигации
		 *
		 */
		function navigationAllItemList()
		{
			global $AVE_DB, $AVE_Template;

			$navigations = array();

			//-- Выполняем запрос к БД на получение id и названия меню навигации
			$sql = $AVE_DB->Query("
				SELECT
					navigation_id,
					title
				FROM
					" . PREFIX . "_navigation
			");

			//-- Циклически обрабатываем полученные данные
			while ($navigation = $sql->FetchRow())
			{
				//-- Выполняем запрос к БД на получение всех пунктов для каждого меню.
				$sql_items = $AVE_DB->Query("
					SELECT
						*
					FROM
						" . PREFIX . "_navigation_items
					WHERE
						navigation_id = " . (int)$navigation->navigation_id . "
					AND
						parent_id = 0
					ORDER BY
						position ASC
				");

				while ($row = $sql_items->FetchAssocArray())
				{
					//-- имя связанного документа
					if ($row['document_id'] > 0)
					{
							$doc_info = get_document((int)$row['document_id']);
							$row['document_title'] = (($doc_info['document_breadcrum_title']) ? $doc_info['document_breadcrum_title'] : $doc_info['document_title']);
					}
					else
						{
							$row['document_title'] = '';
						}

					$row['children'] = $this->getChildrenById($row['navigation_item_id'], 0, true);

					if (! empty($item_id))
						$items = $row;
					else
						$items[] = $row;
				}

				$navigation->navigation_items = $items;

				unset($items);

				array_push($navigations, $navigation);
			}

			//-- Передаем полученные данные в шаблон для вывода
			$AVE_Template->assign('navigations', $navigations);
			$AVE_Template->assign('select_tpl', 'navigation/select.tpl');
		}


		/**
		 * Метод, предназначенный для вывода пунктов меню навигации в Панели управления
		 *
		 * @param int $id идентификатор меню навигации
		 */
		function navigationItemList($navigation_id)
		{
			global $AVE_DB, $AVE_Template;

			$sql = $AVE_DB->Query("
				SELECT
					*
				FROM
					" . PREFIX . "_navigation_items
				WHERE
					navigation_id = " . (int)$navigation_id . "
				AND
					parent_id = 0
				ORDER BY
					position ASC
			");

			$items = array();

			while ($row = $sql->FetchAssocArray())
			{
				// имя связанного документа
				if ($row['document_id'] > 0)
				{
						$doc_info = get_document((int)$row['document_id']);
						$row['document_title'] = (($doc_info['document_breadcrum_title'])
							? $doc_info['document_breadcrum_title']
							: $doc_info['document_title']);
				}
				else
					{
						$row['document_title'] = '';
					}

				$row['children'] = $this->getChildrenById($row['navigation_item_id'], 0, true);

				if (! empty($item_id))
					$items = $row;
				else
					$items[] = $row;
			}

			 $navigation = $AVE_DB->Query("
				SELECT *
				FROM
					" . PREFIX . "_navigation
				WHERE
					navigation_id = '" . $navigation_id . "'
			")->FetchRow();

			$AVE_Template->assign('navigation', $navigation);
			$AVE_Template->assign('items', $items);
			$AVE_Template->assign('level', 1);

			$AVE_Template->assign('nestable_tpl', 'navigation/nestable.tpl');
			$AVE_Template->assign('content', $AVE_Template->fetch('navigation/items.tpl'));
		}


		/**
		 *	Метод для рекурсивного получения
		 *	пунктов меню навигации в Панели управления
		 */
		public function getChildrenById($navigation_item_id, $rec_status = 1, $recurse = false)
		{
			global $AVE_DB;

			$sql = $AVE_DB->Query("
				SELECT
					*
				FROM
					" . PREFIX . "_navigation_items
				WHERE
					parent_id = " . $navigation_item_id . "
				ORDER BY
					position ASC
			");

			$children = array();

			while($row = $sql->FetchAssocArray())
			{
				if($recurse)
				{
					// имя связанного документа
					if ($row['document_id'] > 0)
					{
							$doc_info = get_document((int)$row['document_id']);
							$row['document_title'] = (($doc_info['document_breadcrum_title'])
								? $doc_info['document_breadcrum_title']
								: $doc_info['document_title']);
					}
					else
						{
							$row['document_title'] = '';
						}

					$row['children'] = $this->getChildrenById($row['navigation_item_id'], $rec_status,$recurse);
				}

				$children[] = $row;
			}

			return ((count($children) > 0)
				? $children
				: false
			);
		}


		/**
		 * Метод, предназначенный для управления пунктами меню навигации в Панели управления
		 *
		 * @param int $id идентификатор меню навигации
		 */
		function navigationItemEdit($navigation_item_id = null)
		{
			global $AVE_DB, $AVE_Template;

			// Определяем действие пользователя
			switch ($_REQUEST['sub'])
			{
				// Если действие не определено, отображаем форму с данными для редактирования
				case 'new':

					$sql = $AVE_DB->Query("
						SELECT
							*
						FROM
							" . PREFIX . "_navigation_items
						WHERE
							navigation_id = " . (int)$_REQUEST['navigation_id'] . "
						AND
							parent_id = 0
						ORDER BY
							position ASC
					");

					$items = array();

					while ($row = $sql->FetchAssocArray())
					{
						$row['css_style'] = stripslashes($row['css_style']);

							// имя связанного документа
							if ($row['document_id'] > 0)
							{
									$doc_info = get_document((int)$row['document_id']);

									$row['document_title'] = (($doc_info['document_breadcrum_title'])
										? $doc_info['document_breadcrum_title']
										: $doc_info['document_title']);
							}
							else
								{
									$row['document_title'] = '';
								}

						$row['children'] = $this->getChildrenById($row['navigation_item_id'], 0, true);

						if (! empty($item_id))
							$items = $row;
						else
							$items[] = $row;
					}

					$alias = $AVE_DB->Query("
						SELECT
							alias
						FROM
							" . PREFIX . "_navigation
						WHERE
							navigation_id = " . $_REQUEST['navigation_id'] . "
					")->GetCell();

					//-- Стираем кеш навигации
					$this->clearCahe($_REQUEST['navigation_id'], $alias);

					$AVE_Template->assign('select_tpl', 'navigation/select.tpl');
					$AVE_Template->assign('items', $items);
					$AVE_Template->assign('content', $AVE_Template->fetch('navigation/item_new.tpl'));
				break;

				case 'edit':

					$item = $AVE_DB->Query("
						SELECT
							*
						FROM
							" . PREFIX . "_navigation_items
						WHERE
							navigation_item_id = " . $navigation_item_id . "
					")->FetchRow();

					$item->css_style = stripslashes($item->css_style);

					if ($item->document_id)
						$doc_info = get_document((int)$item->document_id);

					$item->document_title = (($doc_info['document_breadcrum_title'])
						? $doc_info['document_breadcrum_title']
						: $doc_info['document_title']);

					$item->document_alias = $doc_info['document_alias'];

					$AVE_Template->assign('item', $item);
					$AVE_Template->assign('content', $AVE_Template->fetch('navigation/item_edit.tpl'));
				break;

				case 'save':

					$_REQUEST['alias'] = (strpos($_REQUEST['alias'], 'javascript') !== false)
						? str_replace(array(' ', '%'), '-', $_REQUEST['alias'])
						: $_REQUEST['alias'];

					// Определяем флаг статуса пункта меню (активен/неактивен)
					$status = (empty($_REQUEST['alias']))
						? 0
						: 1;

					if ($navigation_item_id)
					{
						// Выполняем запрос к БД и обновляем информацию в таблице для данного меню
						$sql = $AVE_DB->Query("
							UPDATE
								" . PREFIX . "_navigation_items
							SET
								document_id			= '" . (($_REQUEST['document_id']) ? (int)$_REQUEST['document_id'] : '') . "',
								title				= '" . $this->_replace_wildcode($_REQUEST['title']) . "',
								alias				= '" . $_REQUEST['alias'] . "',
								description			= '" . $this->_replace_wildcode($_REQUEST['description']) . "',
								image				= '" . $_REQUEST['image'] . "',
								css_style			= '" . addslashes($_REQUEST['css_style']) . "',
								css_class			= '" . $_REQUEST['css_class'] . "',
								css_id				= '" . $_REQUEST['css_id'] . "',
								target				= '" . $_REQUEST['target'] . "',
								status				= '" . $status . "'
							WHERE
								navigation_item_id		= '" . $navigation_item_id . "'
						");

						$navigation_id = $AVE_DB->Query("
							SELECT
								navigation_id
							FROM
								" . PREFIX . "_navigation_items
							WHERE
								navigation_item_id = '" . $navigation_item_id . "'
						")->GetCell();

						$alias = $AVE_DB->Query("
							SELECT
								alias
							FROM
								" . PREFIX . "_navigation
							WHERE
								navigation_id = " . $navigation_id . "
						")->GetCell();

						//-- Стираем кеш навигации
						$this->clearCahe($_REQUEST['navigation_id'], $alias);
					}
					else
						{
							if ($_REQUEST['after'])
								$after = $AVE_DB->Query("SELECT * FROM ".PREFIX."_navigation_items WHERE navigation_item_id = '" . $_REQUEST['after'] . "' ")->FetchArray();
							else
								$after = array('parent_id' => 0, 'level' => 1, 'position' => 0);

							// Выполняем запрос к БД и обновляем информацию в таблице для данного меню
							$sql = $AVE_DB->Query("
								INSERT
									" . PREFIX . "_navigation_items
								SET
									navigation_id		= '" . $_REQUEST['navigation_id'] . "',
									document_id			= '" . (($_REQUEST['document_id']) ? (int)$_REQUEST['document_id'] : '') . "',
									title				= '" . $this->_replace_wildcode($_REQUEST['title']) . "',
									alias				= '" . $_REQUEST['alias'] . "',
									description			= '" . $this->_replace_wildcode($_REQUEST['description']) . "',
									image				= '" . $_REQUEST['image'] . "',
									css_style			= '" . addslashes($_REQUEST['css_style']) . "',
									css_class			= '" . $_REQUEST['css_class'] . "',
									css_id				= '" . $_REQUEST['css_id'] . "',
									target				= '" . $_REQUEST['target'] . "',
									parent_id			= '" . $after['parent_id'] . "',
									level				= '" . $after['level'] . "',
									position			= '" . $after['position'] . "',
									status				= '" . $status . "'
							");

							$navigation_item_id = $AVE_DB->getLastInsertId();

							$alias = $AVE_DB->Query("
								SELECT
									alias
								FROM
									" . PREFIX . "_navigation
								WHERE
									navigation_id = " . $_REQUEST['navigation_id'] . "
							")->GetCell();

							//-- Стираем кеш навигации
							$this->clearCahe($_REQUEST['navigation_id'], $alias);
						}

					$message = 'Пункт меню успешно сохранен';
					$header = 'Выполнено';
					$theme = 'accept';

					echo json_encode(
						array(
							'message' => $message,
							'header' => $header,
							'theme' => $theme,
							'after' => $_REQUEST['after'],
							'item_id' => $navigation_item_id)
						);
				exit;
			}
			/*
			// Сохраняем системное сообщение в журнал
			reportLog($AVE_Template->get_config_vars('NAVI_REPORT_ADDIT') . " (" . $this->_replace_wildcode($title) . ") - ". $AVE_Template->get_config_vars('NAVI_REPORT_TLEV'));

			// Выполняем обновление страницы
			header('Location:index.php?do=navigation&action=entries&id=' . $nav_id . '&cp=' . SESSION);
			exit;
			*/
		}



		/**
		 * Метод, предназначенный для удаления пунктов меню навигации связанных с удаляемым документом.
		 * Данный метод вызывается при удалении документа с идентификатором $document_id.
		 * Если у пункта меню нет потомков - пункт удаляется, в противном случае пункт деактивируется
		 *
		 * @param int $document_id идентификатор удаляемого документа
		 */
		function navigationItemDeleteFromDoc($document_id)
		{
			global $AVE_DB, $AVE_Template;

			if (! is_numeric($document_id))
				return;

			// Выполняем запрос к БД и получаем ID пункта меню, с которым связан документ
			$sql = $AVE_DB->Query("
				SELECT
					navigation_item_id
				FROM
					" . PREFIX . "_navigation_items
				WHERE
					document_id = '" . $document_id . "'
			");

			while ($row = $sql->FetchRow())
			{
				// Выполняем запрос к БД для определения у удаляемого пункта подпунктов
				$num = $AVE_DB->Query("
					SELECT
						COUNT(1)
					FROM
						" . PREFIX . "_navigation_items
					WHERE
						parent_id = '" . $row->navigation_item_id . "'
				")->GetCell();

				// Если данный пункт имеет подпункты, тогда
				if ($num > 0)
				{
					// Выполняем запрос к БД и деактивируем пункт меню
					$AVE_DB->Query("
						UPDATE
							" . PREFIX . "_navigation_items
						SET
							status = '0'
						WHERE
							navigation_item_id = '" . $row->navigation_item_id . "'
					");

					// Сохраняем системное сообщение в журнал
					reportLog($AVE_Template->get_config_vars('NAVI_REPORT_DEACT') . " (id: $row->navigation_item_id)");
				}
				else
					{ // В противном случае, если данный пункт не имеет подпунктов, тогда

						// Выполняем запрос к БД и удаляем помеченный пункт
						$AVE_DB->Query("
							DELETE
							FROM
								" . PREFIX . "_navigation_items
							WHERE
								navigation_item_id = '" . $row->navigation_item_id . "'
						");

						// Сохраняем системное сообщение в журнал
						reportLog($AVE_Template->get_config_vars('NAVI_REPORT_DELIT') . " (id: $row->navigation_item_id)");
					}
			}
		}


		/**
		 * Метод, предназначенный для удаления пункта меню навигации в Панели управления
		 *
		 * @param int $navigation_item_id идентификатор меню навигации
		 */
		function navigationItemDelete($navigation_item_id)
		{
			global $AVE_DB, $AVE_Template;

			if (!is_numeric($navigation_item_id))
				return;

			// Выполняем запрос к БД для определения у удаляемого пункта подпунктов
			$num = $AVE_DB->Query("
				SELECT
					COUNT(1)
				FROM
					" . PREFIX . "_navigation_items
				WHERE
					parent_id = '" . $navigation_item_id . "'
			")->GetCell();

			// Если данный пункт имеет подпункты, тогда
			if ($num > 0)
			{
				$sql = $AVE_DB->Query("
					SELECT
						*
					FROM
						" . PREFIX . "_navigation_items
					WHERE
						navigation_item_id = '" . $navigation_item_id . "'
					LIMIT 1
				")->FetchRow();

				// Выполняем запрос к БД и деактивируем пункт меню
				$AVE_DB->Query("
					UPDATE
						" . PREFIX . "_navigation_items
					SET
						status = '0'
					WHERE
						navigation_item_id = '" . $navigation_item_id . "'
				");

				// Сохраняем системное сообщение в журнал
				reportLog($AVE_Template->get_config_vars('NAVI_REPORT_DEACT') . " (" . stripslashes($sql->title) . ") (id: $navigation_item_id)");
			}
			else
				{
					// В противном случае, если данный пункт не имеет подпунктов, тогда
					$sql = $AVE_DB->Query("
						SELECT *
						FROM
							" . PREFIX . "_navigation_items
						WHERE
							navigation_item_id = '" . $navigation_item_id . "'
						LIMIT 1
					")->FetchRow();

					// Выполняем запрос к БД и удаляем помеченный пункт
					$AVE_DB->Query("
						DELETE
						FROM
							" . PREFIX . "_navigation_items
						WHERE
							navigation_item_id = '" . $navigation_item_id . "'
					");

					// Сохраняем системное сообщение в журнал
					reportLog($AVE_Template->get_config_vars('NAVI_REPORT_DELIT') . " (" . stripslashes($sql->title) . ") (id: $navigation_item_id)");
				}

			$nav = $AVE_DB->Query("
				SELECT
					navigation_id, alias
				FROM
					" . PREFIX . "_navigation
				WHERE
					navigation_id = " . $sql->navigation_id . "
			")->FetchRow();

			//-- Стираем кеш навигации
			$this->clearCahe($nav->navigation_id, $nav->alias);

			// Выполняем обновление страницы
			header('Location:' . get_referer_admin_link());
			exit;
		}


		/**
		 * Метод, предназначенный для активации пункта меню навигации.
		 * Данный метод используется при изменении статуса документа с идентификатором $document_id
		 *
		 * @param int $document_id идентификатор документа на который ссылается пункт меню
		 */
		function navigationItemStatusOn($document_id)
		{
			global $AVE_DB, $AVE_Template;

			if (!is_numeric($document_id))
				return;

			// Выполняем запрос к БД и получаем id пункта меню, который соответствует идентификатору документа в ссылке
			$sql = $AVE_DB->Query("
				SELECT
					navigation_id,
					navigation_item_id
				FROM
					" . PREFIX . "_navigation_items
				WHERE
					document_id = '" . $document_id . "'
				AND
					status = '0'
			");

			while ($row = $sql->FetchRow())
			{
				// Выполняем запрос к БД изменяем статус пункта меню на активный (1)
				$AVE_DB->Query("
					UPDATE
						" . PREFIX . "_navigation_items
					SET
						status = '1'
					WHERE
						navigation_item_id = '" . $row->navigation_item_id . "'
				");

				$nav = $AVE_DB->Query("
					SELECT
						navigation_id, alias
					FROM
						" . PREFIX . "_navigation
					WHERE
						navigation_id = " . $row->navigation_id . "
				")->FetchRow();

				//-- Стираем кеш навигации
				$this->clearCahe($nav->navigation_id, $nav->alias);

				// Сохраняем системное сообщение в журнал
				reportLog($AVE_Template->get_config_vars('NAVI_REPORT_ACT') . " (id: $row->navigation_item_id)");
			}


		}

		/**
		 * Метод, предназначенный для деактивации пункта меню навигации.
		 * Данный метод используется при изменении статуса документа с идентификатором $document_id
		 *
		 * @param int $document_id идентификатор документа на который ссылается пункт меню
		 */
		function navigationItemStatusOff($document_id)
		{
			global $AVE_DB, $AVE_Template;

			if (! is_numeric($document_id))
				return;

			// Выполняем запрос к БД и получаем id пункта меню,
			// который соответствует идентификатору документа в ссылке
			$sql = $AVE_DB->Query("
				SELECT
					navigation_id,
					navigation_item_id
				FROM
					" . PREFIX . "_navigation_items
				WHERE
					document_id = '" . $document_id . "'
				AND
					status = '1'
			");

			while ($row = $sql->fetchrow())
			{
				// Выполняем запрос к БД изменяем статус пункта меню на неактивный (0)
				$AVE_DB->Query("
					UPDATE
						" . PREFIX . "_navigation_items
					SET
						status = '0'
					WHERE
						navigation_item_id = '" . $row->navigation_item_id . "'
				");


				$nav = $AVE_DB->Query("
					SELECT
						navigation_id, alias
					FROM
						" . PREFIX . "_navigation
					WHERE
						navigation_id = " . $row->navigation_id . "
				")->FetchRow();

				//-- Стираем кеш навигации
				$this->clearCahe($nav->navigation_id, $nav->alias);

				// Сохраняем системное сообщение в журнал
				reportLog($AVE_Template->get_config_vars('NAVI_REPORT_DEACT') . " (id: $row->navigation_item_id)");
			}
		}

		/**
		 * Метод, предназначенный для активации пункта меню навигации.
		 * Данный метод используется при изменении статуса документа с идентификатором $document_id
		 *
		 * @param int $document_id идентификатор документа на который ссылается пункт меню
		 */
		function navigationItemGet($navigation_item_id)
		{
			global $AVE_DB, $AVE_Template;

			if (! is_numeric($navigation_item_id))
				return;

			// Выполняем запрос к БД и получаем id пункта меню, который соответствует идентификатору документа в ссылке
			$item = $AVE_DB->Query("
				SELECT
					*
				FROM
					" . PREFIX . "_navigation_items
				WHERE
					navigation_item_id = '" . $navigation_item_id . "'
			")->FetchAssocArray();

			if ($item['document_id'])
				$doc_info = get_document((int)$item['document_id']);

			$item['document_title'] = (($doc_info['document_breadcrum_title'])
				? $doc_info['document_breadcrum_title']
				: $doc_info['document_title']);

			$item['document_alias'] = $doc_info['document_alias'];

			$nav = $AVE_DB->Query("
				SELECT
					navigation_id, alias
				FROM
					" . PREFIX . "_navigation
				WHERE
					navigation_id = " . $item['navigation_id'] . "
			")->FetchRow();

			//-- Стираем кеш навигации
			$this->clearCahe($nav->navigation_id, $nav->alias);

			$AVE_Template->assign('item', $item);
			$AVE_Template->assign('content', $AVE_Template->fetch('navigation/item.tpl'));
		}

		/**
		 * Метод, предназначенный для рекурсивоной
		 * сортировки пунктов меню навигации.
		 */
		function navigationSort()
		{
			global $AVE_DB, $AVE_Template;

			$level = 1;

			$navigation_id = (int)$_REQUEST['navigation_id'];

			foreach ($_REQUEST['data'] as $item_id => $item)
			{
				$AVE_DB->Query("
					UPDATE
						" . PREFIX . "_navigation_items
					SET
						level			= '" . $level . "',
						parent_id		= '0',
						position		= '" . (int)$item_id . "'
					WHERE
						navigation_item_id = " . $item['id'] ."
					AND
						navigation_id = " . $navigation_id . "
				");

				if (is_array($item['children']))
				{
					$this->navigationSortNested($item['children'], $item['id'], $level, $navigation_id);
				}
			}

			$nav = $AVE_DB->Query("
				SELECT
					navigation_id, alias
				FROM
					" . PREFIX . "_navigation
				WHERE
					navigation_id = " . $navigation_id . "
			")->FetchRow();

			//-- Стираем кеш навигации
			$this->clearCahe($nav->navigation_id, $nav->alias);

			if (isAjax())
			{
				echo json_encode(
					array(
						'message' => $AVE_Template->get_config_vars('NAVI_SORTED'),
						'header' => $AVE_Template->get_config_vars('NAVI_REPORT_SUCCESS'),
						'theme' => 'accept'
						)
					);
			}

			exit;
		}

		/**
		 * Метод, предназначенный для рекурсивоной
		 * сортировки пунктов меню навигации.
		 */
		function navigationSortNested($array = array(), $parent_id = null, $level = null, $navigation_id = null)
		{
			global $AVE_DB;

			$level++;

			foreach($array as $key => $value)
			{
				$AVE_DB->Query("
					UPDATE
						" . PREFIX . "_navigation_items
					SET
						level			= '" . $level . "',
						parent_id		= '" . (int)$parent_id . "',
						position		= '" . $key . "'
					WHERE
						navigation_item_id = " . $value['id'] . "
					AND
						navigation_id = " . $navigation_id . "
				");

				if (is_array($value['children']))
				{
					$this->navigationSortNested($value['children'], $value['id'], $level, $navigation_id);
				}
			}
		}


		function getDocumentById($doc_id = null)
		{
			$document = get_document($doc_id);

			echo json_encode(
				array(
					'doc_id' => $doc_id,
					'document_title' => $document['document_title'],
					'document_alias' => $document['document_alias']
				)
			);
			exit;
		}


		function navigationItemStatus($navigation_item_id, $status = 1)
		{
			global $AVE_DB;

			$AVE_DB->Query("
				UPDATE
					" . PREFIX . "_navigation_items
				SET
					status			= '" . $status . "'
				WHERE
					navigation_item_id = '" . $navigation_item_id . "'
			");

			$navigation_id = $AVE_DB->Query("
				SELECT
					navigation_id
				FROM
					" . PREFIX . "_navigation_items
				WHERE
					navigation_item_id = '" . $navigation_item_id . "'
			")->GetCell();

			$nav = $AVE_DB->Query("
				SELECT
					navigation_id, alias
				FROM
					" . PREFIX . "_navigation
				WHERE
					navigation_id = " . $navigation_id . "
			")->FetchRow();

			//-- Стираем кеш навигации
			$this->clearCahe($nav->navigation_id, $nav->alias);

			echo json_encode(
				array(
					'status' => ($status == 0 ? 1 : 0)
					)
			);

			exit;
		}


		function clearCahe($id, $alias = '')
		{
			if (file_exists(BASE_DIR . '/cache/sql/nav/template-' . $id . '.cache'))
				unlink(BASE_DIR . '/cache/sql/nav/template-' . $id . '.cache');

			if (file_exists(BASE_DIR . '/cache/sql/nav/template-' . $alias . '.cache'))
				unlink(BASE_DIR . '/cache/sql/nav/template-' . $alias . '.cache');

			if (file_exists(BASE_DIR . '/cache/sql/nav/items-' . $id . '.cache'))
				unlink(BASE_DIR . '/cache/sql/nav/items-' . $id . '.cache');

			if (file_exists(BASE_DIR . '/cache/sql/nav/items-' . $alias . '.cache'))
				unlink(BASE_DIR . '/cache/sql/nav/items-' . $alias . '.cache');
		}

	}
?>
