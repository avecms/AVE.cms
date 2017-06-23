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
 * Класс для работы с группами и учетными записями пользователей
 */
class AVE_User
{
/**
 *	СВОЙСТВА
 */

	/**
	 * Количество Пользователей отображаемых на одной странице списка
	 *
	 * @public int
	 */
	public $_limit = 25;

	/**
	 * Допустимые права доступа в административной панели
	 *
	 * @public array
	 */
	public $_allowed_admin_permission = array(
		'alles',																								// все права
		'adminpanel',																							// доступ в админку
		'group_view', 'group_edit',																				// группы пользователей
		'user_view', 'user_edit', 'user_perms',																	// пользователи
		'template_view', 'template_edit', 'template_php',														// шаблоны
		'rubric_view', 'rubric_edit', 'rubric_php', 'rubric_perms', 'rubric_code',								// рубрики
		'document_view', 'document_php', 'document_revisions',													// документы
		'remark_view', 'remark_edit',																			// заметки
		'request_view', 'request_edit', 'request_php',															// запросы
		'navigation_view', 'navigation_edit',																	// навигация
		'blocks_view', 'blocks_edit',																			// визуальные блоки
		'sysblocks_view', 'sysblocks_edit',																		// системные блоки
		'modules_view', 'modules_admin', 'modules_system',														// модули
		'mediapool_int', 'mediapool_add', 'mediapool_del', 'mediapool_finder',									// файловый менеджер
		'gen_settings', 'gen_settings_more', 'gen_settings_countries', 'gen_settings_languages',				// общие настройки
		'db_actions',																							// база данных
		'logs_view', 'logs_clear',																				// логи
		'cache_clear', 'cache_thumb'																			// сессии и кеш
	);

	/**
	 * Разделитель используемый при записи даты рождения
	 *
	 * @public string
	 */
	public $_birthday_delimetr = '.';

/**
 *	ВНУТРЕННИЕ МЕТОДЫ
 */

	/**
	 * Проверка элементов учетной записи пользователя
	 *
	 * @param boolean $new признак проверки элементов новой учетной записи
	 * @return array
	 */
	function _userFieldValidate($new = false)
	{
		global $AVE_DB, $AVE_Template;

		$errors = array();

		$regex = '/[^\x20-\xFF]/';
		$regex_username = '/[^\w-]/';
		$regex_password = '/[^\x21-\xFF]/';
		$regex_birthday = '#(0[1-9]|[12][0-9]|3[01])([[:punct:]| ])(0[1-9]|1[012])\2(19|20)\d\d#';
//		$regex_email = "¬^[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[a-z]{2}|com|org|net|edu|gov|mil|biz|info|mobi|name|aero|asia|jobs|museum)$¬i";
		$regex_email = '/^[\w.-]+@[a-z0-9.-]+\.(?:[a-z]{2}|com|org|net|edu|gov|mil|biz|info|mobi|name|aero|asia|jobs|museum)$/i';

		// Проверка логина
		if (empty($_POST['user_name']))
		{
			$errors[] = @$AVE_Template->get_config_vars('USER_NO_USERNAME');
		}
		elseif (preg_match($regex_username, $_POST['user_name']))
		{
			$errors[] = @$AVE_Template->get_config_vars('USER_ERROR_USERNAME');
		}

		// Проверка имени
		if (empty($_POST['firstname']))
		{
			$errors[] = @$AVE_Template->get_config_vars('USER_NO_FIRSTNAME');
		}
		elseif (preg_match($regex, stripslashes($_POST['firstname'])))
		{
			$errors[] = @$AVE_Template->get_config_vars('USER_ERROR_FIRSTNAME');
		}

		// Проверка фамилии
		if (empty($_POST['lastname']))
		{
			$errors[] = @$AVE_Template->get_config_vars('USER_NO_LASTNAME');
		}
		elseif (preg_match($regex, stripslashes($_POST['lastname'])))
		{
			$errors[] = @$AVE_Template->get_config_vars('USER_ERROR_LASTNAME');
		}

		// Проверка e-Mail
		if (empty($_POST['email']))
		{
			$errors[] = @$AVE_Template->get_config_vars('USER_NO_EMAIL');
		}
		elseif (!preg_match($regex_email, $_POST['email']))
		{
			$errors[] = @$AVE_Template->get_config_vars('USER_EMAIL_ERROR');
		}
		else
		{
			$email_exist = $AVE_DB->Query("
				SELECT *
				FROM " . PREFIX . "_users
				WHERE email != '" . $_POST['Email_Old'] . "'
				AND email = '" . $_POST['email'] . "'
				" . ($new ? "AND email != '" . $_SESSION['user_email'] . "'" : '') . "
				LIMIT 1
			")->NumRows();
			if ($email_exist==1)
			{
				$errors[] = @$AVE_Template->get_config_vars('USER_EMAIL_EXIST');
			}
		}

		// Проверка пароля
		if (isset($_REQUEST['action']) && $_REQUEST['action'] != 'edit')
		{
			if (empty($_POST['password']))
			{
				$errors[] = @$AVE_Template->get_config_vars('USER_NO_PASSWORD');
			}
			elseif (strlen($_POST['password']) < 4)
			{
				$errors[] = @$AVE_Template->get_config_vars('USER_PASSWORD_SHORT');
			}
			elseif (preg_match($regex_password, $_POST['password']))
			{
				$errors[] = @$AVE_Template->get_config_vars('USER_PASSWORD_ERROR');
			}
		}

		// Проверка даты рождения
		$match = '';
		if (!empty($_POST['birthday']) && !preg_match($regex_birthday, $_POST['birthday'], $match))
		{
			$errors[] = @$AVE_Template->get_config_vars('USER_ERROR_DATEFORMAT');
		}
		elseif (!empty($match))
		{

			$_POST['birthday'] = $match[1]
			. $this->_birthday_delimetr . $match[3]
			. $this->_birthday_delimetr . $match[4];
		}

		return $errors;
	}

/**
 *	ВНЕШНИЕ МЕТОДЫ
 */

	/**
	 * Группы пользователей
	 */

	/**
	 * Получение списка Групп пользователей
	 *
	 * @param string $exclude идентификатор исключаемой Группы пользователей (гостей)
	 * @return array
	 */
	function userGroupListGet($exclude = '')
	{
		global $AVE_DB;

		$user_groups = array();
		$sql = $AVE_DB->Query("
			SELECT
				grp.*,
				COUNT(usr.Id) AS UserCount
			FROM
				" . PREFIX . "_user_groups AS grp
			LEFT JOIN
				" . PREFIX . "_users AS usr
					ON usr.user_group = grp.user_group
			" . (($exclude != '' && is_numeric($exclude)) ? "WHERE grp.user_group != '" . $exclude . "'" : '') . "
			GROUP BY grp.user_group
		");

		while ($row = $sql->FetchRow())
		{
			array_push($user_groups, $row);
		}

		return $user_groups;
	}

	/**
	 * Отобразить список Групп пользователей
	 *
	 */
	function userGroupListShow()
	{
		global $AVE_Template;

		$AVE_Template->assign('ugroups', $this->userGroupListGet());
		$AVE_Template->assign('content', $AVE_Template->fetch('groups/groups.tpl'));
	}

	/**
	 * Создание новой Группы пользователей
	 *
	 */
	function userGroupNew()
	{
		global $AVE_DB, $AVE_Template;

		if (!empty($_POST['user_group_name']))
		{
			$AVE_DB->Query("
				INSERT
				INTO " . PREFIX . "_user_groups
				SET
					user_group				= '',
					user_group_name			= '" . $_POST['user_group_name'] . "',
					status					= '1',
					user_group_permission 	= ''
			");
			$iid = $AVE_DB->InsertId();

			reportLog($AVE_Template->get_config_vars('UGROUP_REPORT_ADD') . ' - (' . groupName($iid) . ')');

			header('Location:index.php?do=groups&action=grouprights&Id=' . $iid . '&cp=' . SESSION);
		}
		else
		{
			header('Location:index.php?do=groups&cp=' . SESSION);
		}
	}

	/**
	 * Удаление Группы пользователей
	 *
	 * @param int $user_group_id идентификатор Группы пользователей
	 */
	function userGroupDelete($user_group_id = '0')
	{
		global $AVE_DB, $AVE_Template;

		if (is_numeric($user_group_id) && $user_group_id > 2)
		{
			$exist_user_in_group = $AVE_DB->Query("
				SELECT user_group
				FROM " . PREFIX . "_users
				WHERE user_group = '" . $user_group_id . "'
				LIMIT 1
			")->NumRows();

			if (!$exist_user_in_group)
			{
				$AVE_DB->Query("
					DELETE
					FROM " . PREFIX . "_user_groups
					WHERE user_group = '" . $user_group_id . "'
				");

				reportLog($AVE_Template->get_config_vars('UGROUP_REPORT_DEL') . ' - (' . groupName($user_group_id) . ')');
			}
		}

		header('Location:index.php?do=groups&cp=' . SESSION);
	}

	/**
	 * Редактирование прав Группы пользователей
	 *
	 * @param int $user_group_id идентификатор Группы пользователей
	 */
	function userGroupPermissionEdit($user_group_id)
	{
		global $AVE_DB, $AVE_Template, $AVE_Module;

		if ((UGROUP != 1 && UGROUP == $user_group_id) || (UGROUP != 1 && $user_group_id == 1) || (UGROUP != 1 && $user_group_id == 2))
		{

			$AVE_Template->assign('own_group', true);
		}
		else
		{
			if (is_numeric($user_group_id) && $user_group_id)
			{
				$row = $AVE_DB->Query("
					SELECT
						user_group_name,
						user_group_permission
					FROM " . PREFIX . "_user_groups
					WHERE user_group = '" . $user_group_id . "'
				")->FetchRow();
			}

			if (empty($row))
			{
				$AVE_Template->assign('no_group', true);
			}
			else
			{
				$AVE_Template->assign('g_all_permissions', $this->_allowed_admin_permission);
				$AVE_Template->assign('g_group_permissions', explode('|', $row->user_group_permission));
				$AVE_Template->assign('g_name', $row->user_group_name);
				$AVE_Template->assign('modules', $AVE_Module->moduleListGet(1));
			}
		}

		$AVE_Template->assign('content', $AVE_Template->fetch('groups/perms.tpl'));
	}

	/**
	 * Запись прав Групп пользователей
	 *
	 * @param int $user_group_id идентификатор Группы пользователей
	 */
	function userGroupPermissionSave($user_group_id)
	{
		global $AVE_DB, $AVE_Template;

		if (is_numeric($user_group_id))
		{
			$perms = (!empty($_REQUEST['perms']) && is_array($_REQUEST['perms'])) ? implode('|', $_REQUEST['perms']) : '';
			$perms = ($user_group_id == '1' || in_array('alles', $_REQUEST['perms'])) ? 'alles' : $perms;
			$perms = ($user_group_id == '2') ? '' : $perms;

			$sql = $AVE_DB->Query("
				UPDATE " . PREFIX . "_user_groups
				SET user_group_permission = '" . $perms . "'
				" . (!empty($_POST['user_group_name']) ? ", user_group_name = '" . $_POST['user_group_name'] . "'" : '') . "
				WHERE user_group = '" . $user_group_id . "'
			");

		}

		if ($sql->_result === false) {
			$message = $AVE_Template->get_config_vars('UGROUP_SAVED_ERR');
			$header = $AVE_Template->get_config_vars('UGROUP_ERROR');
			$theme = 'error';

		}else{
			$message = $AVE_Template->get_config_vars('UGROUP_SAVED');
			$header = $AVE_Template->get_config_vars('UGROUP_SUCCESS');
			$theme = 'accept';
			reportLog($AVE_Template->get_config_vars('UGROUP_SAVE_MAIN') . ' - (' . groupName($user_group_id) . ')');
		}

		if (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] = '1') {

			echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
		} else {
			$AVE_Template->assign('message', $message);
			header('Location:index.php?do=groups&cp=' . SESSION);
		}

		exit;
	}

	/**
	 * Учетные записи пользователей
	 */

	/**
	 * Формирование спискка учетных записей пользователей
	 *
	 * @param int $user_group_id идентификатор Группы пользователей
	 */
	function userListFetch($user_group_id = '')
	{
		global $AVE_DB, $AVE_Template;

		$search_by_group = '';
		$search_by_id_or_name = '';
		$user_group_navi = '';
		$query_navi = '';
		$status_search = '';
		$status_navi = '';

		if (isset($_REQUEST['user_group']) && $_REQUEST['user_group'] != '0')
		{
			$user_group_id = ($user_group_id != '') ? $user_group_id : $_REQUEST['user_group'];
			$user_group_navi = '&amp;user_group=' . $user_group_id;
			$search_by_group = " AND user_group = '" . $user_group_id . "' ";
		}

		if (!empty($_REQUEST['query']))
		{
			$q = urldecode($_REQUEST['query']);
			$search_by_id_or_name = "
				AND (email LIKE '%" . $q . "%'
				OR email = '" . $q . "'
				OR Id = '" . $q . "'
				OR firstname LIKE '" . $q . "%'
				OR lastname LIKE '" . $q . "%')
			";
			$query_navi = '&amp;query=' . urlencode($_REQUEST['query']);
		}

		if (isset($_REQUEST['status']) && $_REQUEST['status'] != 'all')
		{
			$status_search = " AND status = '" . $_REQUEST['status'] . "' ";
			$status_navi   = '&amp;status=' . $_REQUEST['status'];
		}

		$num = $AVE_DB->Query("
			SELECT COUNT(*)
			FROM " . PREFIX . "_users
			WHERE 1"
			. $search_by_group
			. $search_by_id_or_name
			. $status_search
		)->GetCell();

		$sql = $AVE_DB->Query("
			SELECT *
			FROM " . PREFIX . "_users
			WHERE 1"
			. $search_by_group
			. $search_by_id_or_name
			. $status_search
			. " LIMIT " . (get_current_page()*$this->_limit-$this->_limit) . "," . $this->_limit
		);

		$isShop = $AVE_DB->Query("SHOW TABLES LIKE '" . PREFIX . "_modul_shop_bestellungen'")->GetCell();
		$users = array();

		while ($row = $sql->FetchRow())
		{
			// для комментариев
			//$sqla = $AVE_DB->Query("SELECT * FROM " . PREFIX . "_modul_comment_info WHERE comment_author_id = '".(int)$row->Id."'");
			//$row->comments = $sqla->numrows();
			$row->avatar=getAvatar($row->Id,40);
			array_push($users, $row);
		}

		if ($num > $this->_limit)
		{
			$page_nav = '<li><a href="index.php?do=user' . $status_navi . '&page={s}&cp=' . SESSION . $user_group_navi . $query_navi . '">{t}</a></li>';
			$page_nav = get_pagination(ceil($num/$this->_limit), 'page', $page_nav);
			$AVE_Template->assign('page_nav', $page_nav);
		}

		$AVE_Template->assign('ugroups', $this->userGroupListGet(2));
		$AVE_Template->assign('users', $users);
	}

	/**
	 * Создание новой учетной записи
	 *
	 */
	function userNew()
	{
		global $AVE_DB, $AVE_Template;

		switch($_REQUEST['sub'])
		{
			case '':
				$AVE_Template->assign('available_countries', get_country_list(1));
				$AVE_Template->assign('ugroups', $this->userGroupListGet(2));
				$AVE_Template->assign('formaction', 'index.php?do=user&action=new&sub=save&cp=' . SESSION);
				$AVE_Template->assign('content', $AVE_Template->fetch('user/form.tpl'));
				break;

			case 'save':
				$errors = $this->_userFieldValidate(1);
				if (!empty($errors))
				{
					$AVE_Template->assign('errors', $errors);
					$AVE_Template->assign('available_countries', get_country_list(1));
					$AVE_Template->assign('ugroups', $this->userGroupListGet(2));
					$AVE_Template->assign('formaction', 'index.php?do=user&action=new&sub=save&cp=' . SESSION);
					$AVE_Template->assign('content', $AVE_Template->fetch('user/form.tpl'));
				}
				else
				{
					$salt = make_random_string();
					$password = md5(md5(trim($_POST['password']) . $salt));
					$AVE_DB->Query("
						INSERT INTO " . PREFIX . "_users
						SET
							Id		  = '',
							password	= '" . $password . "',
							salt		= '" . $salt . "',
							email	   = '" . $_POST['email'] . "',
							street	  = '" . $_POST['street'] . "',
							street_nr   = '" . $_POST['street_nr'] . "',
							zipcode	 = '" . $_POST['zipcode'] . "',
							city		= '" . $_POST['city'] . "',
							phone	   = '" . $_POST['phone'] . "',
							telefax	 = '" . $_POST['telefax'] . "',
							description = '" . $_POST['description'] . "',
							firstname   = '" . $_POST['firstname'] . "',
							lastname	= '" . $_POST['lastname'] . "',
							user_name   = '" . $_POST['user_name'] . "',
							user_group  = '" . $_POST['user_group'] . "',
							reg_time	= '" . time() . "',
							status	  = '" . $_POST['status'] . "',
							last_visit  = '" . time() . "',
							country	 = '" . $_POST['country'] . "',
							birthday	= '" . $_POST['birthday'] . "',
							company	 = '" . $_POST['company'] . "',
							taxpay	  = '" . $_POST['taxpay'] . "',
							user_group_extra = '" . @implode(';', $_POST['user_group_extra']) . "'
					");
					$user_id=$AVE_DB->InsertId();
					if(is_uploaded_file($_FILES["avatar"]["tmp_name"]))
					{
						// Если файл загружен успешно, перемещаем его
						// из временной директории в конечную
						$newf_n= BASE_DIR.'/'. UPLOAD_DIR.'/avatars/new/'.$_FILES["avatar"]["name"];
						move_uploaded_file($_FILES["avatar"]["tmp_name"],$newf_n);
						SetAvatar($user_id,$newf_n);
					}

					$message = get_settings('mail_new_user');
					$message = str_replace('%NAME%', $_POST['user_name'], $message);
					$message = str_replace('%HOST%', HOST . ABS_PATH, $message);
					$message = str_replace('%PASSWORD%', $_POST['password'], $message);
					$message = str_replace('%EMAIL%', $_POST['email'], $message);
					$message = str_replace('%EMAILSIGNATURE%', get_settings('mail_signature'), $message);
/*
					send_mail(
						$_POST['email'],
						$message,
						$AVE_Template->get_config_vars('USER_MAIL_SUBJECT')
					);
*/
					reportLog($AVE_Template->get_config_vars('USER_REPORT_ADD') . ' - (' . stripslashes($_POST['user_name']) . ')');

					header('Location:index.php?do=user&cp=' . SESSION);
				}
				break;
		}
	}

	/**
	 * Редактирование учетной записи пользователя
	 *
	 * @param int $user_id идентификатор учетной записи пользователя
	 */
	function userEdit($user_id)
	{
		global $AVE_DB, $AVE_Template;

		$user_id = (int)$user_id;

		switch($_REQUEST['sub'])
		{
			case '':
				$row = $AVE_DB->Query("
					SELECT *
					FROM " . PREFIX . "_users
					WHERE Id = '" . $user_id . "'
				")->FetchRow();

				if (!$row)
				{
					header('Location:index.php?do=user&cp=' . SESSION);
					exit;
				}
/*
				if ($AVE_DB->Query("SHOW TABLES LIKE '" . PREFIX . "_modul_shop'")->GetCell())
				{
					$AVE_Template->assign('is_shop', 1);
				}

				if ($AVE_DB->Query("SHOW TABLES LIKE '" . PREFIX . "_modul_forum_userprofile'")->GetCell())
				{
					$row = $AVE_DB->Query("
						SELECT *
						FROM " . PREFIX . "_modul_forum_userprofile
						WHERE BenutzerId = '" . $user_id . "'
					")->FetchRow();

					if (is_object($row))
					{
						$AVE_Template->assign('row_fp', $row);
						$AVE_Template->assign('is_forum', 1);
					}
				}
*/
				if (($_SESSION['user_group'] != 1)){

					if (($_SESSION['user_group'] == $row->user_group) && ($_SESSION['user_id'] != $row->Id)){
						$AVE_Template->assign('no_edit', true);
					}

					if ($row->user_group == 1 && $row->Id == 1) {
						$AVE_Template->assign('no_edit', true);
					}

				}

				$row->avatar = getAvatar($user_id, 70);

				$AVE_Template->assign('row', $row);

				$AVE_Template->assign('user_group_extra', explode(';', $row->user_group_extra));
				$AVE_Template->assign('available_countries', get_country_list(1));
				$AVE_Template->assign('ugroups', $this->userGroupListGet(2));
				$AVE_Template->assign('us_groups', explode(';', $row->user_group_extra));
				$AVE_Template->assign('formaction', 'index.php?do=user&action=edit&Id='. $user_id .'&sub=save&cp=' . SESSION);
				$AVE_Template->assign('content', $AVE_Template->fetch('user/form.tpl'));
				break;

			case 'save':
				$errors = $this->_userFieldValidate();
				if (!empty($errors))
				{

					$row = $AVE_DB->Query("
						SELECT *
						FROM " . PREFIX . "_users
						WHERE Id = '" . $user_id . "'
					")->FetchRow();

					if (!$row)
					{
						header('Location:index.php?do=user&cp=' . SESSION);
						exit;
					}

					if (($_SESSION['user_group'] != 1)){

						if (($_SESSION['user_group'] == $row->user_group) && ($_SESSION['user_id'] != $row->Id)){
							$AVE_Template->assign('no_edit', true);
						}

						if ($row->user_group == 1 && $row->Id == 1) {
							$AVE_Template->assign('no_edit', true);
						}

					}

					$row->avatar = getAvatar($user_id, 70);

					$AVE_Template->assign('row', $row);
					$AVE_Template->assign('errors', $errors);
					$AVE_Template->assign('user_group_extra', explode(';', $row->user_group_extra));
					$AVE_Template->assign('available_countries', get_country_list(1));
					$AVE_Template->assign('ugroups', $this->userGroupListGet(2));
					$AVE_Template->assign('us_groups', explode(';', $row->user_group_extra));
					$AVE_Template->assign('formaction', 'index.php?do=user&action=edit&Id='. $user_id .'&sub=save&cp=' . SESSION);
					$AVE_Template->assign('content', $AVE_Template->fetch('user/form.tpl'));
				}
				else
				{
					if (!empty($_REQUEST['password']))
					{
						$salt = make_random_string();
						$password = md5(md5(trim($_POST['password']) . $salt));
						$password_set = "password = '" . $password . "', salt = '" . $salt . "',";
					}
					else
					{
						$password_set = '';
					}

					$user_group_set = ($_SESSION['user_id'] != $user_id) ? "user_group = '" . $_REQUEST['user_group'] . "'," : '';

					$times = ($_REQUEST['deleted'] == "1") ? time() : '';

					if(is_uploaded_file($_FILES["avatar"]["tmp_name"]))
					{
						// Если файл загружен успешно, перемещаем его
						// из временной директории в конечную
						$newf_n = BASE_DIR.'/'. UPLOAD_DIR.'/avatars/new/'.$_FILES["avatar"]["name"];
						move_uploaded_file($_FILES["avatar"]["tmp_name"],$newf_n);
						SetAvatar($user_id,$newf_n);
					}

					$AVE_DB->Query("
						UPDATE " . PREFIX . "_users
						SET
							" . $password_set . "
							" . $user_group_set . "
							email	   = '" . $_REQUEST['email'] . "',
							street	  = '" . $_REQUEST['street'] . "',
							street_nr   = '" . $_REQUEST['street_nr'] . "',
							zipcode	 = '" . $_REQUEST['zipcode'] . "',
							city		= '" . $_REQUEST['city'] . "',
							phone	   = '" . $_REQUEST['phone'] . "',
							telefax	 = '" . $_REQUEST['telefax'] . "',
							description = '" . $_REQUEST['description'] . "',
							firstname   = '" . $_REQUEST['firstname'] . "',
							lastname	= '" . $_REQUEST['lastname'] . "',
							user_name   = '" . $_REQUEST['user_name'] . "',
							status	  = '" . $_REQUEST['status'] . "',
							country	 = '" . $_REQUEST['country'] . "',
							birthday	= '" . $_REQUEST['birthday'] . "',
							deleted	 = '" . $_REQUEST['deleted'] . "',
							del_time	  = '" . $times . "',
							taxpay	  = '" . $_REQUEST['taxpay'] . "',
							company	 = '" . $_REQUEST['company'] . "',
							user_group_extra = '" . @implode(';', $_REQUEST['user_group_extra']) . "'
						WHERE
							Id = '" . $user_id . "'
					");

/*
					if ($AVE_DB->Query("SHOW TABLES LIKE '" . PREFIX . "_module_forum_userprofile'")->GetCell())
					{
						$AVE_DB->Query("
							UPDATE " . PREFIX . "_modul_forum_userprofile
							SET
								GroupIdMisc  = '" . @implode(';', $_REQUEST['user_group_extra']) . "',
								BenutzerName = '" . @$_REQUEST['BenutzerName_fp']. "',
								Signatur	 = '" . @$_REQUEST['Signatur_fp'] . "' ,
								Avatar	   = '" . @$_REQUEST['Avatar_fp'] . "'
							WHERE
								BenutzerId = '" . $user_id . "'
						");
					}
*/

					if ($_REQUEST['status'] == 1 && @$_REQUEST['SendFreeMail'] == 1)
					{
						$host = HOST . ABS_PATH;
						$body_start  = $AVE_Template->get_config_vars('USER_MAIL_BODY1');
						$body_start  = str_replace('%USER%', $_REQUEST['user_name'], $body_start);
						$body_start .= str_replace('%HOST%', $host, $AVE_Template->get_config_vars('USER_MAIL_BODY2'));
						$body_start .= str_replace('%HOMEPAGENAME%', get_settings('site_name'), $AVE_Template->get_config_vars('USER_MAIL_FOOTER'));
						$body_start  = str_replace('%N%', "\n", $body_start);
						$body_start  = str_replace('%HOST%', $host, $body_start);

						send_mail(
							$_POST['email'],
							$body_start,
							$AVE_Template->get_config_vars('USER_MAIL_SUBJECT'),
							get_settings('mail_from'),
							get_settings('mail_from_name') . ' (' . get_settings('site_name') . ')',
							'text'
						);
					}

					if (!empty($_REQUEST['password']) && $_REQUEST['PassChange'] == 1)
					{
						$host = HOST . ABS_PATH;
						$body_start  = $AVE_Template->get_config_vars('USER_MAIL_BODY1');
						$body_start  = str_replace('%USER%', $_REQUEST['user_name'], $body_start);
						$body_start .= str_replace('%HOST%', $host, $AVE_Template->get_config_vars('USER_MAIL_PASSWORD2'));
						$body_start  = str_replace('%NEWPASS%', $_REQUEST['password'], $body_start);
						$body_start .= str_replace('%HOMEPAGENAME%', get_settings('site_name'), $AVE_Template->get_config_vars('USER_MAIL_FOOTER'));
						$body_start  = str_replace('%N%', "\n", $body_start);
						$body_start  = str_replace('%HOST%', $host, $body_start);

						send_mail(
							$_POST['email'],
							$body_start,
							$AVE_Template->get_config_vars('USER_MAIL_PASSWORD'),
							get_settings('mail_from'),
							get_settings('mail_from_name') . ' (' . get_settings('site_name') . ')',
							'text'
						);
					}

					if ($_REQUEST['SimpleMessage'] != '')
					{
						send_mail(
							$_POST['email'],
							stripslashes($_POST['SimpleMessage']),
							stripslashes($_POST['SubjectMessage']),
							$_SESSION['user_email'],
							$_SESSION['user_name'],
							'text'
						);
					}

					if (!empty($_REQUEST['password']) && $_SESSION['user_id'] == $user_id)
					{
						$_SESSION['user_pass'] = $password;
						$_SESSION['user_email'] = $_POST['email'];
					}

					reportLog($AVE_Template->get_config_vars('USER_REPORT_EDIT') . ' - (' . stripslashes($_POST['user_name']) . ')');

					header('Location:index.php?do=user&cp=' . SESSION);
					exit;
				}
				break;
		}
	}

	/**
	 * Удаление учетной записи пользователя
	 *
	 * @param int $user_id идентификатор учетной записи пользователя
	 */
	function userDelete($user_id)
	{
		global $AVE_DB, $AVE_Template;

		if (is_numeric($user_id) && $user_id != 1)
		{
			$AVE_DB->Query("
				DELETE
				FROM " . PREFIX . "_users
				WHERE Id = '" . $user_id . "'
			");

			if ($AVE_DB->Query("SHOW TABLES LIKE '" . PREFIX . "_modul_forum_userprofile'")->GetCell())
			{
				$AVE_DB->Query("
					DELETE
					FROM " . PREFIX . "_modul_forum_userprofile
					WHERE BenutzerId = '" . $user_id . "'
				");
			}

			reportLog($AVE_Template->get_config_vars('USER_REPORT_DEL') . ' - (' . get_username_by_id($user_id) . ')');
		}

		header('Location:index.php?do=user&cp=' . SESSION);
	}

	/**
	 * Запись изменений учетных записей пользователей в списке
	 *
	 */
	function userListEdit()
	{
		global $AVE_DB, $AVE_Template;

		foreach ($_POST['del'] as $user_id => $del)
		{
			if (is_numeric($user_id) && $user_id > 1)
			{
				$AVE_DB->Query("
					DELETE
					FROM " . PREFIX . "_users
					WHERE Id = '" . $user_id . "'
				");

				reportLog($AVE_Template->get_config_vars('USER_REPORT_DEL') . ' - (' . get_username_by_id($user_id) . ')');
			}
		}

		foreach ($_POST['user_group'] as $user_id => $user_group_id)
		{
			if (is_numeric($user_id) && $user_id > 0 &&
				is_numeric($user_group_id) && $user_group_id > 0)
			{
				$AVE_DB->Query("
					UPDATE " . PREFIX . "_users
					SET user_group = '" . $user_group_id . "'
					WHERE Id = '" . $user_id . "'
				");
				reportLog($AVE_Template->get_config_vars('USER_REPORT_GROUP') . ' - (' . get_username_by_id($user_id) . ')');
			}
		}

		header('Location:index.php?do=user&cp=' . SESSION);
		exit;
	}
}

?>
