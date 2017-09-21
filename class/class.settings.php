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
 * Класс управления настройками системы
 */
class AVE_Settings
{
/**
 *	СВОЙСТВА
 */

	/**
	 * Количество стран на странице
	 *
	 * @public int
	 */
	public $_limit = 15;

/**
 *	ВНЕШНИЕ МЕТОДЫ
 */

	/**
	 * Метод отображения настроек
	 *
	 */
	function settingsShow()
	{
		global $AVE_Template;

		$date_formats = array(
			'%d.%m.%Y',
			'%d %B %Y',
			'%A, %d.%m.%Y',
			'%A, %d %B %Y'
		);

		$time_formats = array(
			'%d.%m.%Y, %H:%M',
			'%d %B %Y, %H:%M',
			'%A, %d.%m.%Y (%H:%M)',
			'%A, %d %B %Y (%H:%M)'
		);

		$AVE_Template->assign('date_formats', $date_formats);
		$AVE_Template->assign('time_formats', $time_formats);
		$AVE_Template->assign('row', get_settings());
		$AVE_Template->assign('available_countries', get_country_list(1));
		$AVE_Template->assign('content', $AVE_Template->fetch('settings/settings_main.tpl'));
	}

	/**
	 * Метод отображения дополнительных настроек
	 *
	 */
	function settingsCase()
	{
		global $AVE_Template;

		// Сохраняем настройки
		if (isset($_REQUEST['more']))
		{
			$set = '<?php' . "\r\n\r\n";

			foreach($_REQUEST['GLOB'] as $k => $v)
			{
				switch ($GLOBALS['CMS_CONFIG'][$k]['TYPE'])
				{
						case 'bool' :
							$v = $v ? 'true' : 'false';
						break;

						case 'integer' :
							$v = intval($v);
						break;

						case 'string' :
							$v = "'" . add_slashes($v) . "'";
						break;

						case 'dropdown' :
							$v = "'" . add_slashes($v) . "'";
						break;

						default :
							$v = "'" . add_slashes($v) . "'";
						break;
				}

				$set .= "//" . $GLOBALS['CMS_CONFIG'][$k]['DESCR'] . "\r\n";
				$set .= "define('" . $k . "', " . $v . ");\r\n\r\n";
			}

			$set .= '?>';

			$result = file_put_contents(BASE_DIR . '/inc/config.inc.php', $set);

			if ($result > 0)
			{
				$message = $AVE_Template->get_config_vars('SETTINGS_SAVED');
				$header = $AVE_Template->get_config_vars('SETTINGS_SUCCESS');
				$theme = 'accept';
				reportLog($AVE_Template->get_config_vars('SETTINGS_SAVE_DOP'));
			}
			else
				{
					$message = $AVE_Template->get_config_vars('SETTINGS_SAVED_ERR');
					$header = $AVE_Template->get_config_vars('SETTINGS_ERROR');
					$theme = 'error';
				}

			if (isAjax())
			{
				echo json_encode(array(
					'message' => $message,
					'header' => $header,
					'theme' => $theme)
				);
			}
			else
				{
					$AVE_Template->assign('message', $message);
					header('Location:index.php?do=settings&sub=case&cp=' . SESSION);
				}

			exit;
		// Выводим настройки
 		}
 		else
	 		{
				$AVE_Template->assign('CMS_CONFIG', $GLOBALS['CMS_CONFIG']);
				$AVE_Template->assign('content', $AVE_Template->fetch('settings/settings_case.tpl'));
			}
	}

	/**
	 * Метод записи настроек
	 *
	 */
	function settingsSave()
	{
		global $AVE_DB,  $AVE_Template;

		$muname = ($_REQUEST['mail_smtp_login'])	? "mail_smtp_login = '" . $_REQUEST['mail_smtp_login'] . "',"	   : '';
		$mpass  = ($_REQUEST['mail_smtp_pass'])	 ? "mail_smtp_pass = '" . $_REQUEST['mail_smtp_pass'] . "',"		 : '';
		$msmp   = ($_REQUEST['mail_sendmail_path']) ? "mail_sendmail_path = '" . $_REQUEST['mail_sendmail_path'] . "'," : '';
		$mn	 = ($_REQUEST['mail_from_name'])	 ? "mail_from_name = '" . $_REQUEST['mail_from_name'] . "',"		 : '';
		$ma	 = ($_REQUEST['mail_from'])		  ? "mail_from = '" . $_REQUEST['mail_from'] . "',"				   : '';
		$ep	 = ($_REQUEST['page_not_found_id'])  ? "page_not_found_id = '" . $_REQUEST['page_not_found_id'] . "',"   : '';
		$sn	 = ($_REQUEST['site_name'])		  ? "site_name = '" . $_REQUEST['site_name'] . "',"				   : '';
		$mp	 = ($_REQUEST['mail_port'])		  ? "mail_port = '" . $_REQUEST['mail_port'] . "',"				   : '';
		$mh	 = ($_REQUEST['mail_host'])		  ? "mail_host = '" . $_REQUEST['mail_host'] . "',"				   : '';

		$sql = $AVE_DB->Query("
			UPDATE " . PREFIX . "_settings
			SET
				" . $muname . "
				" . $mpass . "
				mail_smtp_encrypt = '" . $_REQUEST['mail_smtp_encrypt'] . "',
				" . $msmp . "
				" . $ma . "
				" . $mn . "
				" . $ep . "
				" . $sn . "
				" . $mp . "
				" . $mh . "
				default_country 	  = '" . $_REQUEST['default_country'] . "',
				mail_type			  = '" . $_REQUEST['mail_type'] . "',
				mail_content_type	  = '" . $_REQUEST['mail_content_type'] . "',
				mail_word_wrap		  = '" . (int)$_REQUEST['mail_word_wrap'] . "',
				mail_new_user		  = '" . $_REQUEST['mail_new_user'] . "',
				mail_signature		  = '" . $_REQUEST['mail_signature'] . "',
				message_forbidden	  = '" . $_REQUEST['message_forbidden'] . "',
				hidden_text			  = '" . $_REQUEST['hidden_text'] . "',
				navi_box			  = '" . $_REQUEST['navi_box'] . "',
				start_label			  = '" . $_REQUEST['start_label'] . "',
				end_label			  = '" . $_REQUEST['end_label'] . "',
				separator_label		  = '" . $_REQUEST['separator_label'] . "',
				next_label			  = '" . $_REQUEST['next_label'] . "',
				prev_label			  = '" . $_REQUEST['prev_label'] . "',
				total_label			  = '" . $_REQUEST['total_label'] . "',
				link_box			  = '" . $_REQUEST['link_box'] . "',
				total_box			  = '" . $_REQUEST['total_box'] . "',
				active_box			  = '" . $_REQUEST['active_box'] . "',
				separator_box		  = '" . $_REQUEST['separator_box'] . "',
				bread_box			  = '" . $_REQUEST['bread_box'] . "',
				bread_show_main		  = '" . ($_REQUEST['bread_show_main'] != 0 ? 1 : 0) . "',
				bread_show_host		  = '" . ($_REQUEST['bread_show_host'] != 0 ? 1 : 0) . "',
				bread_sepparator	  = '" . $_REQUEST['bread_sepparator'] . "',
				bread_sepparator_use  = '" . ($_REQUEST['bread_sepparator_use'] != 0 ? 1 : 0) . "',
				bread_link_box		  = '" . $_REQUEST['bread_link_box'] . "',
				bread_link_template	  = '" . $_REQUEST['bread_link_template'] . "',
				bread_self_box		  = '" . $_REQUEST['bread_self_box'] . "',
				bread_link_box_last  = '" . ($_REQUEST['bread_link_box_last'] != 0 ? 1 : 0) . "',
				date_format			  = '" . $_REQUEST['date_format'] . "',
				time_format			  = '" . $_REQUEST['time_format'] . "',
				use_doctime			  = '" . intval($_REQUEST['use_doctime']) . "',
				use_editor			  = '" . intval($_REQUEST['use_editor']) . "'
			WHERE
				Id = 1
		");

		if ($sql->_result === false)
		{
			$message = $AVE_Template->get_config_vars('SETTINGS_SAVED_ERR');
			$header = $AVE_Template->get_config_vars('SETTINGS_ERROR');
			$theme = 'error';
		}
		else
			{
				$_SESSION['use_editor'] = intval($_REQUEST['use_editor']);
				$message = $AVE_Template->get_config_vars('SETTINGS_SAVED');
				$header = $AVE_Template->get_config_vars('SETTINGS_SUCCESS');
				$theme = 'accept';
				reportLog($AVE_Template->get_config_vars('SETTINGS_SAVE_MAIN'));
			}

		if (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] = '1') {
			echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
		} else {
			$AVE_Template->assign('message', $message);
			header('Location:index.php?do=settings&cp=' . SESSION);
		}

		exit;
	}

	/**
	 * Метод отображения списка стран
	 *
	 */
	function settingsCountriesList()
	{
		global $AVE_DB, $AVE_Template;

		$sql = $AVE_DB->Query("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM " . PREFIX . "_countries
			ORDER BY country_status ASC, country_name ASC
			LIMIT " . (get_current_page() * $this->_limit - $this->_limit) . "," . $this->_limit
		);

		$countries = array();
		while ($row = $sql->FetchAssocArray())
		{
			array_push($countries, $row);
		}

		$num = $AVE_DB->Query("SELECT FOUND_ROWS()")->GetCell();

		if ($num > $this->_limit)
		{
			$page_nav = "<a href=\"index.php?do=settings&sub=countries&page={s}&cp=" . SESSION . "\">{t}</a>";
			$page_nav = get_pagination(ceil($num / $this->_limit), 'page', $page_nav);
			$AVE_Template->assign('page_nav', $page_nav);
		}

		$AVE_Template->assign('countries', $countries);
		$AVE_Template->assign('content', $AVE_Template->fetch('settings/settings_countries.tpl'));
	}

	/**
	 * Метод записи параметров стран
	 *
	 */
	function settingsCountriesSave()
	{
		global $AVE_DB, $AVE_Template;

		foreach ($_POST['country_name'] as $id => $country_name)
		{
			$AVE_DB->Query("
				UPDATE " . PREFIX . "_countries
				SET
					country_name   = '" . $country_name . "',
					country_status = '" . $_POST['country_status'][$id] . "',
					country_eu	 = '" . $_POST['country_eu'][$id] . "'
				WHERE
					Id = '" . $id . "'
			");
		}

		reportLog($AVE_Template->get_config_vars('SETTINGS_SAVE_COUNTRY'));
	}


	/**
	 * Метод отображения списка языков
	 *
	 */
	function settingsLanguageList()
	{
		global $AVE_DB, $AVE_Template;

		$sql = $AVE_DB->Query("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM " . PREFIX . "_settings_lang
			ORDER BY  lang_default DESC, lang_status ASC, lang_key ASC
		");

		$language = array();
		while ($row = $sql->FetchAssocArray())
		{
			array_push($language, $row);
		}
		$AVE_Template->assign('language', $language);
		$AVE_Template->assign('content', $AVE_Template->fetch('settings/settings_lang.tpl'));
	}

	/**
	 * Метод Редактирования параметров языков
	 *
	 */
	function settingsLanguageEdit()
	{
		global $AVE_DB, $AVE_Template;

		if (isset($_REQUEST["Id"]))
		{
			$items = $AVE_DB->Query("
			SELECT *
			FROM " . PREFIX . "_settings_lang
			WHERE
					Id = '" . $_REQUEST["Id"] . "'
			")->FetchRow();

			$AVE_Template->assign('items', $items);
		}

		$AVE_Template->assign('content', $AVE_Template->fetch('settings/settings_lang_edit.tpl'));
	}

	function settingsLanguageEditSave()
	{
		global $AVE_DB, $AVE_Template;

		if (!empty($_REQUEST["Id"]))
		{
			$AVE_DB->Query("
			UPDATE
			" . PREFIX . "_settings_lang
			SET
				lang_key = '" .$_REQUEST['lang_key']. "',
				lang_alias_pref = '" .$_REQUEST['lang_alias_pref']. "',
				lang_name = '" .$_REQUEST['lang_name']. "'
			WHERE
					Id = '" . $_REQUEST["Id"] . "'
			");
		}
		else
		{
			$AVE_DB->Query("
			INSERT INTO
			" . PREFIX . "_settings_lang
			SET
				lang_key = '" .$_REQUEST['lang_key']. "',
				lang_name = '" .$_REQUEST['lang_name']. "',
				lang_alias_pref = '" .$_REQUEST['lang_alias_pref']. "',
				lang_default = '0',
				lang_status = '0'
			");

		}
		echo "<script>window.opener.location.reload(); window.close();</script>";
	}


	function settingsPaginationsList()
	{
		global $AVE_DB, $AVE_Template;

		$sql = "
			SELECT
				id,
				pagination_name
			FROM
				" . PREFIX . "_paginations
		";

		$query = $AVE_DB->Query($sql);

		$items = array();

		while ($row = $query->FetchRow())
			array_push($items, $row);

		$AVE_Template->assign('items', $items);

		$AVE_Template->assign('content', $AVE_Template->fetch('settings/settings_pagination.tpl'));
	}


	function settingsPaginationsNew()
	{
		global $AVE_DB, $AVE_Template;

		$pagination = new stdClass();

		$AVE_Template->assign('pagination', $pagination);
		$AVE_Template->assign('content', $AVE_Template->fetch('settings/pagination_edit.tpl'));
	}


	function settingsPaginationsEdit()
	{
		global $AVE_DB, $AVE_Template;

		$sql = "
			SELECT
				*
			FROM
				" . PREFIX . "_paginations
			WHERE
				id = '" . $_REQUEST['id'] . "'
		";

		$pagination = $AVE_DB->Query($sql)->FetchRow();

		$AVE_Template->assign('pagination', $pagination);
		$AVE_Template->assign('content', $AVE_Template->fetch('settings/pagination_edit.tpl'));
	}


	function settingsPaginationsSave()
	{
		global $AVE_DB, $AVE_Template;

		// Если пришел ID
		if (isset($_REQUEST['id']) && $_REQUEST['id'] > 0)
		{
			$sql = "
				UPDATE
					" . PREFIX . "_paginations
				SET
					pagination_name						= '" . $_REQUEST['pagination_name'] . "',
					pagination_box						= '" . $_REQUEST['pagination_box'] . "',
					pagination_start_label				= '" . $_REQUEST['pagination_start_label'] . "',
					pagination_end_label				= '" . $_REQUEST['pagination_end_label'] . "',
					pagination_separator_box			= '" . $_REQUEST['pagination_separator_box'] . "',
					pagination_separator_label			= '" . $_REQUEST['pagination_separator_label'] . "',
					pagination_next_label				= '" . $_REQUEST['pagination_next_label'] . "',
					pagination_prev_label				= '" . $_REQUEST['pagination_prev_label'] . "',
					pagination_link_box					= '" . $_REQUEST['pagination_link_box'] . "',
					pagination_active_link_box			= '" . $_REQUEST['pagination_active_link_box'] . "',
					pagination_link_template			= '" . $_REQUEST['pagination_link_template'] . "',
					pagination_link_active_template		= '" . $_REQUEST['pagination_link_active_template'] . "'
				WHERE
					id = '" . $_REQUEST['id'] . "'
			";

			$query = $AVE_DB->Query($sql);

			if ($query === false)
			{
				$message = $AVE_Template->get_config_vars('PAGINATION_SAVED_ERR');
				$header = $AVE_Template->get_config_vars('PAGINATION_ERROR');
				$theme = 'error';
			}
			else
				{
					$message = $AVE_Template->get_config_vars('PAGINATION_SAVED');
					$header = $AVE_Template->get_config_vars('PAGINATION_SUCCESS');
					$theme = 'accept';
				}

			if (isAjax())
			{
				echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
			}
			else
				{
					header('Location:index.php?do=settings&action=paginations&cp=' . SESSION);
				}

			exit;
		}
		// Если не пришел ID
		else
			{
				$sql = "
					INSERT INTO
						" . PREFIX . "_paginations
					SET
						pagination_name						= '" . $_REQUEST['pagination_name'] . "',
						pagination_box						= '" . $_REQUEST['pagination_box'] . "',
						pagination_start_label				= '" . $_REQUEST['pagination_start_label'] . "',
						pagination_end_label				= '" . $_REQUEST['pagination_end_label'] . "',
						pagination_separator_box			= '" . $_REQUEST['pagination_separator_box'] . "',
						pagination_separator_label			= '" . $_REQUEST['pagination_separator_label'] . "',
						pagination_next_label				= '" . $_REQUEST['pagination_next_label'] . "',
						pagination_prev_label				= '" . $_REQUEST['pagination_prev_label'] . "',
						pagination_link_box					= '" . $_REQUEST['pagination_link_box'] . "',
						pagination_active_link_box			= '" . $_REQUEST['pagination_active_link_box'] . "',
						pagination_link_template			= '" . $_REQUEST['pagination_link_template'] . "',
						pagination_link_active_template		= '" . $_REQUEST['pagination_link_active_template'] . "'
				";
			}

			$query = $AVE_DB->Query($sql);

			header('Location:index.php?do=settings&action=paginations&cp=' . SESSION);
			exit;
	}


	function settingsPaginationsDel()
	{
		global $AVE_DB, $AVE_Template;

		if (isset($_REQUEST['id']) && $_REQUEST['id'] > 1)
		{
			$sql = "
				DELETE
				FROM
					" . PREFIX . "_paginations
				WHERE
					id = '" . $_REQUEST['id'] . "'
			";

			$AVE_DB->Query($sql);
		}

		header('Location:index.php?do=settings&action=paginations&cp=' . SESSION);
		exit;
	}


	/**
	 * Функция делает рекурсивный обход вложенных папок, и добавляет их в архив
	 *
	 * @param string $src_dir папка которую хотим заархивировать
	 * @param string $zip Куда кладем и как называем файл архива
	 * @return ZIP
	 */
	function ZipDirectory($src_dir, $zip, $dir_in_archive = '')
	{
		$dirHandle = opendir($src_dir);

		while (false !== ($file = readdir($dirHandle)))
		{
			if (($file != '.') && ($file != '..'))
			{
				if (! is_dir($src_dir . $file))
				{
					$zip->addFile($src_dir . $file, $dir_in_archive.$file);
				}
				else
					{
						$zip->addEmptyDir($dir_in_archive.$file);
						$zip = ZipDirectory($src_dir . $file . DIRECTORY_SEPARATOR, $zip, $dir_in_archive . $file . DIRECTORY_SEPARATOR);
					}
			}
		}

		return $zip;
	}

	/**
	 * Функция проверяет, возможно ли создать zip-архив, запускает
	 * ZipDirectory и закрывает файл при завершении обхода папок.
	 *
	 * @param string $src_dir папка которую хотим заархивировать
	 * @param string $archive_path Куда кладем и как называем файл архива
	 * @return bool true|false
	 */
	function ZipFull($src_dir, $archive_path)
	{
		$zip = new ZipArchive();

		if ($zip->open($archive_path, ZIPARCHIVE::CREATE) !== true)
		{
			return false;
		}

		$zip = ZipDirectory($src_dir,$zip);

		$zip->close();

		return true;
	}

}
?>