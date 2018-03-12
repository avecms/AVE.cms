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

class AVE_Request
{

/**
 *	Свойстав класса
 */

	/**
	 * Количество Запросов на странице
	 *
	 * @public int
	 */
	public $_limit = 25;

/**
 *	Внутренние методы
 */

	/**
	 * Метод, предназначенный для получения и вывода списка Запросов
	 *
	 * @param boolean $pagination признак формирования постраничного списка
	 */
	function _requestListGet($pagination = true)
	{
		global $AVE_DB, $AVE_Template;

		$limit = '';

		// Если используется постраничная навигация
		if ($pagination)
		{
			// Определяем лимит записей на страницу и начало диапазона выборки
			$limit = $this->_limit;
			$start = get_current_page() * $limit - $limit;

			// Получаем общее количество запросов
			$num = $AVE_DB->Query("SELECT COUNT(*) FROM " . PREFIX . "_request")->GetCell();

			// Если количество больше, чем установленный лимит, тогда формируем постраничную навигацию
			if ($num > $limit)
			{
				$page_nav = "<li><a href=\"index.php?do=request&page={s}&amp;cp=" . SESSION . "\">{t}</a></li>";
				$page_nav = get_pagination(ceil($num / $limit), 'page', $page_nav);
				$AVE_Template->assign('page_nav', $page_nav);
			}

			$limit = $pagination ? "LIMIT " . $start . "," . $limit : '';
		}

		// Выполняем запрос к БД на получение списка запросов с учетом лимита вывода на страницу (если необходимо)
		$items = array();

		$sql = $AVE_DB->Query("
			SELECT *
			FROM " . PREFIX . "_request
			ORDER BY Id ASC
			" . $limit . "
		");

		// Формируем массив из полученных данных
		while ($row = $sql->FetchRow())
		{
			$row->request_author = get_username_by_id($row->request_author_id);
			array_push($items, $row);
		}

		// Возвращаем массив
		return $items;
	}

	/**
	 * Получить наименование и описание Запроса по идентификатору
	 *
	 * @param int $request_id идентификатор Запроса
	 * @return object наименование Запроса
	 */
	function get_request_by_id($request_id = 0)
	{
		global $AVE_DB;

		static $requests = array();

		if (!isset($requests[$request_id]))
		{
			$requests[$request_id] = $AVE_DB->Query("
				SELECT
					rubric_id,
					request_title,
					request_description
				FROM " . PREFIX . "_request
				WHERE Id = '" . $request_id . "'
				LIMIT 1
			")->FetchRow();
		}

		return $requests[$request_id];
	}

	/**
	 * Проверка алиаса тега на валидность и уникальность
	 */
	function requestValidate ($alias = '', $id = 0)
	{
		global $AVE_DB;

		//-- Соответствие требованиям
		if (empty ($alias) || preg_match('/^[A-Za-z0-9-_]{1,20}$/i', $alias) !== 1 || is_numeric($alias))
			return 'syn';

		//-- Уникальность
		return !(bool)$AVE_DB->Query("
			SELECT 1
			FROM
				" . PREFIX . "_request
			WHERE
				request_alias = '" . $alias . "'
			AND
				Id != '" . $id . "'
		")->GetCell();
	}

/**
 *	Внешние методы класса
 */

	/**
	 * Метод, предназначенный для формирования списка Запросов
	 *
	 */
	function requestListFetch()
	{
		global $AVE_Template;

		$AVE_Template->assign('conditions', $this->_requestListGet(false));
	}

	/**
	 * Метод, предназначенный для отображения списка Запросов
	 *
	 */
	function requestListShow()
	{
		global $AVE_Template;

		$AVE_Template->assign('rid', 0);

		// Получаем список запросов
		$AVE_Template->assign('items', $this->_requestListGet());

		// Передаем в шаблон и отображаем страницу со списком
		$AVE_Template->assign('content', $AVE_Template->fetch('request/list.tpl'));
	}

	/**
	 * Метод, предназначенный для создания нового Запроса
	 *
	 */
	function requestNew()
	{
		global $AVE_DB, $AVE_Template;

		// Определяем действие пользователя
		switch ($_REQUEST['sub'])
		{
			// Действие не определено
			case '':
				$AVE_Template->assign('rid', 0);
				// Отображаем пустую форму для создания нового запроса
				$AVE_Template->assign('formaction', 'index.php?do=request&action=new&sub=save&cp=' . SESSION);
				$AVE_Template->assign('content', $AVE_Template->fetch('request/form.tpl'));
				break;

			// Нажата кнопка Сохранить запрос
			case 'save':
				$save = true;
				$errors = array();

				$row->request_template_item = pretty_chars($_REQUEST['request_template_item']);
				$row->request_template_item = stripslashes($row->request_template_item);
				$row->request_template_main = pretty_chars($_REQUEST['request_template_main']);
				$row->request_template_main = stripslashes($row->request_template_main);
				$row->request_title = stripslashes($_REQUEST['request_title']);
				$row->rubric_id = stripslashes($_REQUEST['rubric_id']);
				$row->request_items_per_page = stripslashes($_REQUEST['request_items_per_page']);
				$row->request_order_by = stripslashes($_REQUEST['request_order_by']);
				$row->request_order_by_nat = stripslashes($_REQUEST['request_order_by_nat']);
				$row->request_asc_desc = stripslashes($_REQUEST['request_asc_desc']);
				$row->request_description = stripslashes($_REQUEST['request_description']);
				$row->request_show_pagination = (isset($_REQUEST['request_show_pagination']) ? (int)($_REQUEST['request_show_pagination']) : 0);
				$row->request_pagination = (isset($_REQUEST['request_pagination']) ? (int)($_REQUEST['request_pagination']) : 1);
				$row->request_only_owner = (isset($_REQUEST['request_only_owner']) ? (int)($_REQUEST['request_only_owner']) : 0);
				$row->request_cache_lifetime = (int)($_REQUEST['request_cache_lifetime']);
				$row->request_lang = (isset($_REQUEST['request_lang']) ? (int)$_REQUEST['request_lang'] : 0);
				$row->request_cache_elements = (isset($_REQUEST['request_cache_elements']) ? (int)$_REQUEST['request_cache_elements'] : 0);
				$row->request_external = (isset($_REQUEST['request_external']) ? (int)$_REQUEST['request_external'] : 0);
				$row->request_ajax = (isset($_REQUEST['request_ajax']) ? (int)$_REQUEST['request_ajax'] : 0);
				$row->request_show_sql = (isset($_REQUEST['request_show_sql']) ? (int)$_REQUEST['request_show_sql'] : 0);

				if (empty($_REQUEST['rubric_id']))
				{
					$save = false;
					$message = $AVE_Template->get_config_vars('REQUEST_REPORT_ERR_RUBRIC');
					$errors[] = $AVE_Template->get_config_vars('REQUEST_REPORT_ERR_RUBRIC');
				}

				if (empty($_REQUEST['request_title']))
				{
					$save = false;
					$errors[] = $AVE_Template->get_config_vars('REQUEST_REPORT_ERR_TITLE');
				}

				if (empty($_REQUEST['request_template_main']))
				{
					$save = false;
					$errors[] = $AVE_Template->get_config_vars('REQUEST_REPORT_ERR_TEXT');
				}

				$check_code_template_item = strtolower($_REQUEST['request_template_item']);
				$check_code_template_main = strtolower($_REQUEST['request_template_main']);

				if ((is_php_code($check_code_template_item) || is_php_code($check_code_template_main)) && !check_permission('request_php'))
				{
					$save = false;
					$message = $AVE_Template->get_config_vars('REQUEST_REPORT_ERR_PHP');
					$errors[] = $AVE_Template->get_config_vars('REQUEST_REPORT_ERR_PHP');
					reportLog($AVE_Template->get_config_vars('REQUEST_REPORT_ERR_PHP_N') . ' (' . stripslashes(htmlspecialchars($_REQUEST['request_title'], ENT_QUOTES)) . ')');
				}

				if ($save === false)
				{
					$AVE_Template->assign('row', $row);
					$AVE_Template->assign('errors', $errors);
					$AVE_Template->assign('formaction', 'index.php?do=request&action=new&sub=save&cp=' . SESSION);
					$AVE_Template->assign('content', $AVE_Template->fetch('request/form.tpl'));
				}
				else
				{
					// Выполняем запрос к БД и сохраняем введенную пользователем информацию
					$AVE_DB->Query("
						INSERT " . PREFIX . "_request
						SET
							rubric_id			  	= '" . (int)$_REQUEST['rubric_id'] . "',
							request_alias		   	= '" . $_REQUEST['request_alias'] . "',
							request_title		   	= '" . $_REQUEST['request_title'] . "',
							request_items_per_page  = '" . $_REQUEST['request_items_per_page'] . "',
							request_template_item   = '" . $_REQUEST['request_template_item'] . "',
							request_template_main   = '" . $_REQUEST['request_template_main'] . "',
							request_order_by		= '" . $_REQUEST['request_order_by'] . "',
							request_order_by_nat	= '" . $_REQUEST['request_order_by_nat'] . "',
							request_asc_desc		= '" . $_REQUEST['request_asc_desc'] . "',
							request_author_id	   	= '" . (int)$_SESSION['user_id'] . "',
							request_created		 	= '" . time() . "',
							request_description	 	= '" . $_REQUEST['request_description'] . "',
							request_show_pagination = '" . (isset($_REQUEST['request_show_pagination']) ? (int)$_REQUEST['request_show_pagination'] : 0) . "',
							request_pagination 		= '" . (isset($_REQUEST['request_pagination']) ? (int)$_REQUEST['request_pagination'] : 1) . "',
							request_use_query 		= '" . (isset($_REQUEST['request_use_query']) ? (int)$_REQUEST['request_use_query'] : 0) . "',
							request_hide_current	= '" . (int)$_REQUEST['request_hide_current'] . "',
							request_only_owner	  	= '" . (int)$_REQUEST['request_only_owner'] . "',
							request_cache_lifetime  = '" . (int)$_REQUEST['request_cache_lifetime'] . "',
							request_lang			= '" . (isset($_REQUEST['request_lang']) ? (int)$_REQUEST['request_lang'] : 0) . "',
							request_cache_elements	= '" . (isset($_REQUEST['request_cache_elements']) ? (int)$_REQUEST['request_cache_elements'] : 0). "',
							request_show_statistic	= '" . (isset($_REQUEST['request_show_statistic']) ? (int)$_REQUEST['request_show_statistic'] : 0). "',
							request_external		= '" . (isset($_REQUEST['request_external']) ? (int)$_REQUEST['request_external'] : 0). "',
							request_ajax			= '" . (isset($_REQUEST['request_ajax']) ? (int)$_REQUEST['request_ajax'] : 0). "',
							request_show_sql		= '" . (isset($_REQUEST['request_show_sql']) ? (int)$_REQUEST['request_show_sql'] : 0). "'
					");

					// Получаем id последней записи
					$iid = $AVE_DB->InsertId();

					// Сохраняем системное сообщение в журнал
					reportLog($AVE_Template->get_config_vars('REQUEST_ADD_NEW_SUC') . ' (' . stripslashes(htmlspecialchars($_REQUEST['request_title'], ENT_QUOTES)) . ') (' . $iid . ')');

					// Если в запросе пришел параметр на продолжение редактирования запроса
					if ($_REQUEST['reedit'] == 1)
					{
						// Выполняем переход на страницу с редактированием запроса
						header('Location:index.php?do=request&action=edit&Id=' . $iid . '&rubric_id=' . $_REQUEST['rubric_id'] . '&cp=' . SESSION);
					}
					else
					{
						// В противном случае выполняем переход к списку запросов
					  if (!$_REQUEST['next_edit']) {
							header('Location:index.php?do=request&cp=' . SESSION);
						} else {
							header('Location:index.php?do=request&action=edit&Id=' . $iid . '&rubric_id='.$_REQUEST['rubric_id'].'&cp=' . SESSION);
						}
					}
					exit;
				}
			}
	}

	/**
	 * Метод, предназначенный для редактирования Запроса
	 *
	 * @param int $request_id идентификатор запроса
	 */
	function requestEdit($request_id)
	{
		global $AVE_DB, $AVE_Template;

		// Определяем действие пользователя
		switch ($_REQUEST['sub'])
		{
			// Если действие не определено
			case '':
				// Выполняем запрос к БД и получаем всю информацию о запросе
				$sql = $AVE_DB->Query("
					SELECT *
					FROM " . PREFIX . "_request
					WHERE Id = '" . $request_id . "'
				");

				if ($sql->_result->num_rows == 0)
				{
					header('Location:index.php?do=request&cp=' . SESSION);
					exit;
				}

				$row = $sql->FetchRow();

				// Получаем постраничную навигацию
				$sql = $AVE_DB->Query("
					SELECT
						id,
						pagination_name
					FROM
						" . PREFIX . "_paginations
				");

				$paginations = array();

				while ($pages = $sql->FetchRow())
				{
					array_push($paginations, $pages);
				}

				// Передаем данные в шаблон и отображаем страницу с редактированием запроса
				$AVE_Template->assign('row', $row);
				$AVE_Template->assign('rid', $request_id);
				$AVE_Template->assign('paginations', $paginations);
				$AVE_Template->assign('formaction', 'index.php?do=request&action=edit&sub=save&Id=' . $request_id . '&cp=' . SESSION);
				$AVE_Template->assign('content', $AVE_Template->fetch('request/form.tpl'));

				break;

			// Пользователь нажал кнопку Сохранить изменения
			case 'save':

				$sql = $AVE_DB->Query("
					SELECT *
					FROM " . PREFIX . "_request
					WHERE Id = '" . $request_id . "'
				");

				if($sql->_result->num_rows == 0) {
					header('Location:index.php?do=request&cp=' . SESSION);
					exit;
				}

				$save = true;
				$errors = array();
				$row = new stdClass();
				$row->request_template_item 	= (isset($_REQUEST['request_template_item']) ? stripslashes(pretty_chars($_REQUEST['request_template_item'])) : '');
				$row->request_template_main 	= (isset($_REQUEST['request_template_main']) ? stripslashes(pretty_chars($_REQUEST['request_template_main'])) : '');
				$row->request_title 			= (isset($_REQUEST['request_title']) ? stripslashes($_REQUEST['request_title']) : '');
				$row->rubric_id 				= (isset($_REQUEST['rubric_id']) ? stripslashes($_REQUEST['rubric_id']) : 0);
				$row->request_items_per_page 	= (isset($_REQUEST['request_items_per_page']) ? stripslashes($_REQUEST['request_items_per_page']) : 0);
				$row->request_order_by 			= (isset($_REQUEST['request_order_by']) ? stripslashes($_REQUEST['request_order_by']) : '');
				$row->request_order_by_nat 		= (isset($_REQUEST['request_order_by_nat']) ? stripslashes($_REQUEST['request_order_by_nat']) : '');
				$row->request_asc_desc 			= (isset($_REQUEST['request_asc_desc']) ? stripslashes($_REQUEST['request_asc_desc']) : 'DESC');
				$row->request_description 		= (isset($_REQUEST['request_description']) ? stripslashes($_REQUEST['request_description']) : '');
				$row->request_show_pagination 	= (isset($_REQUEST['request_show_pagination']) ? $_REQUEST['request_show_pagination'] : 0);
				$row->request_pagination		= (isset($_REQUEST['request_pagination']) ? (int)($_REQUEST['request_pagination']) : 1);
				$row->request_use_query 		= (isset($_REQUEST['request_use_query']) ? $_REQUEST['request_use_query'] : 0);
				$row->request_only_owner 		= (isset($_REQUEST['request_only_owner']) ? (int)($_REQUEST['request_only_owner']) : 0);
				$row->request_cache_lifetime 	= (isset($_REQUEST['request_cache_lifetime']) ? (int)($_REQUEST['request_cache_lifetime']) : 0);
				$row->request_lang 				= (isset($_REQUEST['request_lang']) ? (int)$_REQUEST['request_lang'] : 0);
				$row->request_cache_elements 	= (isset($_REQUEST['request_cache_elements']) ? (int)$_REQUEST['request_cache_elements'] : 0);
				$row->request_show_statistic 	= (isset($_REQUEST['request_show_statistic']) ? (int)$_REQUEST['request_show_statistic'] : 0);
				$row->request_external 			= (isset($_REQUEST['request_external']) ? (int)$_REQUEST['request_external'] : 0);
				$row->request_ajax 				= (isset($_REQUEST['request_ajax']) ? (int)$_REQUEST['request_ajax'] : 0);
				$row->request_show_sql 			= (isset($_REQUEST['request_show_sql']) ? (int)$_REQUEST['request_show_sql'] : 0);

				if (empty($_REQUEST['rubric_id']))
				{
					$save = false;
					$message = $AVE_Template->get_config_vars('REQUEST_REPORT_ERR_RUBRIC');
					$errors[] = $AVE_Template->get_config_vars('REQUEST_REPORT_ERR_RUBRIC');
				}

				if (empty($_REQUEST['request_title']))
				{
					$save = false;
					$message = $AVE_Template->get_config_vars('REQUEST_REPORT_ERR_TITLE');
					$errors[] = $AVE_Template->get_config_vars('REQUEST_REPORT_ERR_TITLE');
				}

				if (empty($_REQUEST['request_template_main']))
				{
					$save = false;
					$message = $AVE_Template->get_config_vars('REQUEST_REPORT_ERR_TEXT');
					$errors[] = $AVE_Template->get_config_vars('REQUEST_REPORT_ERR_TEXT');
				}

				$check_code_template_item = strtolower($_REQUEST['request_template_item']);
				$check_code_template_main = strtolower($_REQUEST['request_template_main']);

				if ((is_php_code($check_code_template_item) || is_php_code($check_code_template_main)) && !check_permission('request_php'))
				{
					$save = false;
					$message = $AVE_Template->get_config_vars('REQUEST_REPORT_ERR_PHP');
					$errors[] = $AVE_Template->get_config_vars('REQUEST_REPORT_ERR_PHP');
					reportLog($AVE_Template->get_config_vars('REQUEST_REPORT_ERR_PHP_E') . ' (' . stripslashes(htmlspecialchars($_REQUEST['request_title'], ENT_QUOTES)) . ') (Id:' . $request_id . ')');
				}

				if ($save === false)
				{
					if(isAjax())
					{
						$header = $AVE_Template->get_config_vars('REQUEST_ERROR');
						echo json_encode(array('message' => $message, 'header' => $header, 'theme' => 'error'));
						exit;
					}

					$AVE_Template->assign('row', $row);
					$AVE_Template->assign('errors', $errors);
					$AVE_Template->assign('formaction', 'index.php?do=request&action=edit&sub=save&Id=' . $request_id . '&cp=' . SESSION);
					$AVE_Template->assign('content', $AVE_Template->fetch('request/form.tpl'));
				}
				else
				{
					// Выполняем запрос к БД и обновляем имеющиеся данные
					$AVE_DB->Query("
						UPDATE " . PREFIX . "_request
						SET
							rubric_id			   	= '" . (int)$_REQUEST['rubric_id'] . "',
							request_alias		   	= '" . $_REQUEST['request_alias'] . "',
							request_title		   	= '" . $_REQUEST['request_title'] . "',
							request_items_per_page  = '" . $_REQUEST['request_items_per_page'] . "',
							request_template_item   = '" . $_REQUEST['request_template_item'] . "',
							request_template_main   = '" . $_REQUEST['request_template_main'] . "',
							request_order_by		= '" . $_REQUEST['request_order_by'] . "',
							request_order_by_nat	= '" . $_REQUEST['request_order_by_nat'] . "',
							request_description	 	= '" . $_REQUEST['request_description'] . "',
							request_asc_desc		= '" . $_REQUEST['request_asc_desc'] . "',
							request_show_pagination = '" . (isset($_REQUEST['request_show_pagination']) ? (int)$_REQUEST['request_show_pagination'] : 0) . "',
							request_pagination 		= '" . (isset($_REQUEST['request_pagination']) ? (int)$_REQUEST['request_pagination'] : 1) . "',
							request_use_query       = '" . (isset($_REQUEST['request_use_query']) ? (int)$_REQUEST['request_use_query'] : 0) . "',
							request_hide_current	= '" . @(int)$_REQUEST['request_hide_current'] . "',
							request_only_owner	  	= '" . @(int)$_REQUEST['request_only_owner'] . "',
							request_cache_lifetime  = '" . (int)($_REQUEST['request_cache_lifetime']>'' ? $_REQUEST['request_cache_lifetime'] : '-1') . "',
							request_lang			= '" . (isset($_REQUEST['request_lang']) ? (int)$_REQUEST['request_lang'] : 0). "',
							request_cache_elements	= '" . (isset($_REQUEST['request_cache_elements']) ? (int)$_REQUEST['request_cache_elements'] : 0). "',
							request_show_statistic	= '" . (isset($_REQUEST['request_show_statistic']) ? (int)$_REQUEST['request_show_statistic'] : 0). "',
							request_external		= '" . (isset($_REQUEST['request_external']) ? (int)$_REQUEST['request_external'] : 0). "',
							request_ajax			= '" . (isset($_REQUEST['request_ajax']) ? (int)$_REQUEST['request_ajax'] : 0). "',
							request_show_sql		= '" . (isset($_REQUEST['request_show_sql']) ? (int)$_REQUEST['request_show_sql'] : 0). "'
						WHERE
							Id = '" . $request_id . "'
					");

					$sql = $AVE_DB->Query("SELECT Id FROM " . PREFIX . "_documents WHERE rubric_id = ".$_REQUEST['rubric_id']);

					$AVE_DB->clear_request($request_id);

					while ($row = $sql->FetchRow())
					{
						$AVE_DB->clearcacherequest('doc_' . $row->Id);
					}

					// Сохраняем системное сообщение в журнал
					reportLog($AVE_Template->get_config_vars('REQUEST_SAVE_CHA_SUC') . ' (' . stripslashes(htmlspecialchars($_REQUEST['request_title'], ENT_QUOTES)) . ') (Id:' . $request_id . ')');

					// В противном случае выполняем переход к списку запросов
					if (! isAjax())
					{
						header('Location:index.php?do=request&cp=' . SESSION);
						exit;
					} else {
						$message = $AVE_Template->get_config_vars('REQUEST_TEMPLATE_SAVED');
						$header = $AVE_Template->get_config_vars('REQUEST_SUCCESS');
						$theme = 'accept';
						echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
						exit;
					}
				}
			break;
		}
	}

	/**
	 * Метод, предназначенный для создания копии Запроса
	 *
	 * @param int $request_id идентификатор запроса
	 */
	function requestCopy($request_id)
	{
		global $AVE_DB, $AVE_Template;

		// Выполняем запрос к БД на получение информации о копиреумом запросе
		$sql = $AVE_DB->Query("
			SELECT *
			FROM " . PREFIX . "_request
			WHERE Id = '" . $request_id . "'
		");

		if($sql->_result->num_rows == 0) {
			header('Location:index.php?do=request&cp=' . SESSION);
			exit;
		}

		$row = $sql->FetchRow();

		// Выполняем запрос к БД на добавление нового запроса на основании полученных ранее данных
		$AVE_DB->Query("
			INSERT " . PREFIX . "_request
			SET
				rubric_id			   	= '" . (int)$row->rubric_id . "',
				request_items_per_page  = '" . $row->request_items_per_page . "',
				request_title		   	= '" . $_REQUEST['cname'] . "',
				request_template_item   = '" . addslashes($row->request_template_item) . "',
				request_template_main   = '" . addslashes($row->request_template_main) . "',
				request_order_by		= '" . addslashes($row->request_order_by) . "',
				request_order_by_nat	= '" . addslashes($row->request_order_by_nat) . "',
				request_author_id	   	= '" . (int)$_SESSION['user_id'] . "',
				request_created		 	= '" . time() . "',
				request_description	 	= '" . addslashes($row->request_description) . "',
				request_asc_desc		= '" . $row->request_asc_desc . "',
				request_show_pagination = '" . $row->request_show_pagination . "',
				request_use_query       = '" . $row->request_use_query . "',
				request_hide_current	= '" . $row->request_hide_current . "',
				request_lang			= '" . $row->request_lang . "',
				request_cache_elements	= '" . (isset($row->request_cache_elements) ? $row->request_cache_elements : 0) . "'
		");

		// Получаем id добавленной записи
		$iid = $AVE_DB->InsertId();

		// Сохраняем системное сообщение в журнал
		reportLog($AVE_Template->get_config_vars('REQUEST_COPY_SUC') . ' (' . stripslashes(htmlspecialchars($this->get_request_by_id($request_id)->request_title, ENT_QUOTES)) . ') ( Id:'.$iid.' )');

		// Выполняем запрос к БД и получаем все условия запроса для копируемого запроса
		$sql = $AVE_DB->Query("
			SELECT *
			FROM " . PREFIX . "_request_conditions
			WHERE request_id = '" . $request_id . "'
		");

		// Обрабатываем полученные данные и
		while ($row_cond = $sql->FetchRow())
		{
			// Выполняем запрос к БД на добавление условий для нового, скопированного запроса
			$AVE_DB->Query("
				INSERT " . PREFIX . "_request_conditions
				SET
					request_id			= '" . $iid . "',
					condition_compare   = '" . $row_cond->condition_compare . "',
					condition_field_id  = '" . $row_cond->condition_field_id . "',
					condition_value	 	= '" . $row_cond->condition_value . "',
					condition_join	  	= '" . $row_cond->condition_join . "'
			");
		}

		// Выполянем переход к списку запросов
		header('Location:index.php?do=request&cp=' . SESSION);
		exit;
	}

	/**
	 * Метод, предназначенный для удаления запроса
	 *
	 * @param int $request_id идентификатор запроса
	 */
	function requestDelete($request_id)
	{
		global $AVE_DB, $AVE_Template;

		$request_name = $this->get_request_by_id($request_id)->request_title;

		// Выполняем запрос к БД на удаление общей информации о запросе
		$AVE_DB->Query("
			DELETE
			FROM " . PREFIX . "_request
			WHERE Id = '" . $request_id . "'
		");

		// Выполняем запрос к БД на удаление условий запроса
		$AVE_DB->Query("
			DELETE
			FROM " . PREFIX . "_request_conditions
			WHERE request_id = '" . $request_id . "'
		");

		// Сохраняем системное сообщение в журнал
		reportLog($AVE_Template->get_config_vars('REQUEST_DELETE_SUC') . ' (' . stripslashes(htmlspecialchars($request_name, ENT_QUOTES)) . ') ( Id:' . $request_id . ' )');

		// Выполянем переход к списку запросов
		header('Location:index.php?do=request&cp=' . SESSION);
		exit;
	}

	/**
	 * Метод, предназначенный для редактирования условий Запроса
	 *
	 * @param int $request_id идентификатор запроса
	 */
	function requestConditionEdit($request_id)
	{
		global $AVE_DB, $AVE_Template;

		// Определяем действие пользователя
		switch ($_REQUEST['sub'])
		{
			// Если действие не определено
			case '':
				$fields = array();

				// Выполняем запрос к БД и получаем список полей у той рубрики, к которой относится данный запрос
				$sql = $AVE_DB->Query("
					SELECT *
					FROM " . PREFIX . "_rubric_fields
					WHERE rubric_id = '" . $_REQUEST['rubric_id'] . "'
					ORDER BY rubric_field_position ASC
				");

				// Обрабатываем полученные данные и формируем массив
				while ($row = $sql->FetchRow())
				{
					array_push($fields, $row);
				}

				$conditions = array();

				// Выполняем запрос к БД и получаем условия запроса
				$sql = $AVE_DB->Query("
					SELECT *
					FROM " . PREFIX . "_request_conditions
					WHERE request_id = '" . $request_id . "'
					ORDER BY condition_position ASC
				");

				// Обрабатываем полученные данные и формируем массив
				while ($row = $sql->FetchRow())
				{
					array_push($conditions, $row);
				}

				// Передаем данные в шаблон и отображаем страницу с редактированием условий
				$AVE_Template->assign('request_title', $this->get_request_by_id($request_id)->request_title);
				$AVE_Template->assign('fields', $fields);
				$AVE_Template->assign('conditions', $conditions);

				if (isAjax() && (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1)){
					$AVE_Template->assign('content', $AVE_Template->fetch('request/cond_list.tpl'));
				} else {
					$AVE_Template->assign('content', $AVE_Template->fetch('request/conditions.tpl'));
				}
				break;

			case 'sort':

				foreach ($_REQUEST['sort'] as $position => $cond_id)
				{
					$AVE_DB->Query("
						UPDATE " . PREFIX . "_request_conditions
						SET
							condition_position = '" . (int)$position . "'
						WHERE
							Id = '" . (int)$cond_id . "'
					");
				}

				if (isAjax()){
					$message = $AVE_Template->get_config_vars('REQUEST_SORTED');
					$header = $AVE_Template->get_config_vars('REQUEST_SUCCESS');
					$theme = 'accept';
					echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
				}

				exit;


			// Если пользователь нажал кнопку Сохранить изменения
			case 'save':
				// Если существует хотя бы одно условие, тогда

				if (isset($_REQUEST['conditions']) && is_array($_POST['conditions']))
				{
					$condition_edited = false;

					// Обрабатываем данные полей
					foreach ($_REQUEST['conditions'] as $condition_id => $val)
					{
						// Выполняем запрос к БД на обновление информации об условиях
						$AVE_DB->Query("
							UPDATE
								" . PREFIX . "_request_conditions
							SET
								request_id			= '" . $request_id . "',
								condition_compare   = '" . $val['condition_compare'] . "',
								condition_field_id  = '" . $val['condition_field_id'] . "',
								condition_value	 	= '" . (! empty($val['condition_value']) ? $val['condition_value'] : '') . "',
								condition_join	  	= '" . $val['condition_join'] . "',
								condition_status	= '" . ((! empty($val['condition_value'])) ? (($val['condition_status'] == '1') ? '1' : '0') : ''). "'
							WHERE
								Id = '" . $condition_id . "'
						");

						$condition_edited = true;
					}

					// Если изменения были, сохраняем системное сообщение в журнал
					if ($condition_edited)
					{
						reportLog('' . $AVE_Template->get_config_vars('REQUEST_COND_CHA_SUC') . ' (' . stripslashes(htmlspecialchars($this->get_request_by_id($request_id)->request_title, ENT_QUOTES)) . ') - ( Id: '.$request_id.' )');

						$message = $AVE_Template->get_config_vars('REQUEST_COND_POST_OK');
						$header = $AVE_Template->get_config_vars('REQUEST_SUCCESS');
						$theme = 'accept';
					}
					else
					{

						$message = $AVE_Template->get_config_vars('REQUEST_COND_POST_ERR');
						$header = $AVE_Template->get_config_vars('REQUEST_ERROR');
						$theme = 'error';
					}
				}
				else
				{
					$message = $AVE_Template->get_config_vars('REQUEST_COND_NO_POST');
					$header = $AVE_Template->get_config_vars('REQUEST_ERROR');
					$theme = 'error';
				}

				// Если некоторые из условий были помечены на удаление
				if (isset($_POST['del']) && is_array($_POST['del']))
				{
					// Обрабатываем все поля помеченные на удаление
					foreach ($_POST['del'] as $condition_id => $val)
					{
						// Выполняем запрос к БД на удаление условий
						$AVE_DB->Query("
							DELETE
							FROM " . PREFIX . "_request_conditions
							WHERE Id = '" . $condition_id . "'
						");
					}

					// Сохраняем системное сообщение в журнал
					reportLog('' . $AVE_Template->get_config_vars('REQUEST_COND_DEL_SUC') . ' (' . stripslashes(htmlspecialchars($this->get_request_by_id($request_id)->request_title, ENT_QUOTES)) . ') - ( Id: '.$request_id.' )');
				}

				// Нет смысла каждый раз формировать SQL-запрос с условиями Запроса
				// поэтому формируем SQL-запрос только при изменении условий
				// require(BASE_DIR . '/functions/func.parserequest.php');
				request_get_condition_sql_string($request_id, true);

				if (!isAjax() && $_REQUEST['ajax'] != '1'){
					// Выполняем обновление страницы
					header('Location:index.php?do=request&action=conditions&rubric_id=' . $_REQUEST['rubric_id'] . '&Id=' . $request_id . '&cp=' . SESSION . ($_REQUEST['pop'] ? '&pop=1' : ''));
					exit;
				} else {
					echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
					exit;
				}
				break;

			// Если пользователь добавил новое условие
			case 'new':
				if ($_POST['new_value'] !== '')
				{
					// Выполняем запрос к БД на добавление нового условия
					$sql = $AVE_DB->Query("
						INSERT " . PREFIX . "_request_conditions
						SET
							request_id			= '" . $request_id . "',
							condition_compare   = '" . $_POST['new_operator'] . "',
							condition_field_id  = '" . $_POST['field_new'] . "',
							condition_value	 	= '" . $_POST['new_value'] . "',
							condition_join	  	= '" . $_POST['oper_new'] . "'
					");
					if ($sql->_result === false) {
						$message = $AVE_Template->get_config_vars('REQUEST_COND_NEW_ERR');
						$header = $AVE_Template->get_config_vars('REQUEST_ERROR');
						$theme = 'error';
					} else {
						// Сохраняем системное сообщение в журнал
						reportLog('' . $AVE_Template->get_config_vars('REQUEST_COND_ADD_SUC') . ' (' . stripslashes(htmlspecialchars($this->get_request_by_id($request_id)->request_title, ENT_QUOTES)) . ') - ( Id: '.$request_id.' )');
					}

				} else {
					$message = $AVE_Template->get_config_vars('REQUEST_COND_VALUE_ERR');
					$header = $AVE_Template->get_config_vars('REQUEST_ERROR');
					$theme = 'error';
				}

				// Нет смысла каждый раз формировать SQL-запрос с условиями Запроса
				// поэтому формируем SQL-запрос только при изменении условий
				// require(BASE_DIR . '/functions/func.parserequest.php');
				request_get_condition_sql_string($request_id, true);

				if (! isAjax())
				{
					header('Location:index.php?do=request&action=conditions&rubric_id=' . $_REQUEST['rubric_id'] . '&Id=' . $request_id . '&cp=' . SESSION);
					exit;
				}
				else
				{
					if (! $message)
					{
						$message = $AVE_Template->get_config_vars('REQUEST_COND_NEW_SUC');
						$header = $AVE_Template->get_config_vars('REQUEST_SUCCESS');
						$theme = 'accept';
					}
					echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
					exit;
				}
				break;
		}
	}

	function conditionFieldChange($field_id, $cond_id)
	{
		global $AVE_DB, $AVE_Template;

		// Передаем данные в шаблон и отображаем страницу с редактированием условий
		$AVE_Template->assign('field_id', $field_id);
		$AVE_Template->assign('cond_id', $cond_id);
		$AVE_Template->assign('content', $AVE_Template->fetch('request/change.tpl'));
	}

	function conditionFieldChangeSave($field_id, $cond_id)
	{
		global $AVE_DB, $AVE_Template;

		$sql = $AVE_DB->Query("
			UPDATE " . PREFIX . "_request_conditions
			SET
				condition_field_id  = '" . $field_id . "'
			WHERE
				Id = '" . $cond_id . "'
		");

		request_get_condition_sql_string((int)$_REQUEST['req_id'], true);

		// Передаем данные в шаблон и отображаем страницу с редактированием условий
		$AVE_Template->assign('field_id', $field_id);
		$AVE_Template->assign('cond_id', $cond_id);
		$AVE_Template->assign('content', $AVE_Template->fetch('request/change.tpl'));
	}
}

?>
