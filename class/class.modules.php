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

	class AVE_Module
	{
		public $_modules = array();


		function __construct()
		{
			$this->_modules = $this->getModules();
		}


		/**
		 * Метод, который обрабатывает все module.php и записывает как свойство класса списки модулей
		 */
		function getModules()
		{
			$modules = array();

			// Получаем из БД информацию о всех установленных модулях
			$modules_db = $this->moduleListGet();

			// Определяем директорию, где хранятся модули
			$d = dir(BASE_DIR . '/modules');

			// Циклически обрабатываем директории
			while (false !== ($entry = $d->read()))
			{
				if (substr($entry, 0, 1) == '.')
					continue;

				if (! is_dir(BASE_DIR . '/modules/' . $entry))
					continue;

				$AVE_Template = new AVE_Template(BASE_DIR . '/admin/templates');

				$_no_lang_file = false;

				// Языковой файл
				$lang_file = BASE_DIR . '/modules/'.$entry.'/lang/' . $_SESSION['user_language'] . '.txt';

				// Отдаем смарти переменные
				if (file_exists($lang_file))
					$AVE_Template->config_load($lang_file, 'name');
				else
					$_no_lang_file = true;

				$module_dir = $d->path . '/' . $entry;

				if (! is_dir($module_dir))
					continue;

				$module = array();

				// Если не удалось подключить основной файл модуля module.php - Фиксируем ошибку
				if (! (is_file($module_dir . '/info.php') && @include_once($module_dir . '/info.php')))
				{
					$modules['errors'][] = $entry;
					continue;
				}

				$module['ModuleName'] = !$_no_lang_file
					? $AVE_Template->get_config_vars('MODULE_NAME')
					: 'No name';

				$module['ModuleDescription'] = !$_no_lang_file
					? $AVE_Template->get_config_vars('MODULE_DESCRIPTION')
					: 'No description';

				// Дополняем массив с данными модуля
				$module['permission'] = check_permission('mod_'. $module['ModuleSysName']);

				// Установлен/Не установлен по системному имени
				$row = isset($modules_db[$module['ModuleSysName']])
					? $modules_db[$module['ModuleSysName']]
					: false;

				// установленные модули
				if ($row)
				{
					$module['id']				= $row->Id;
					$module['need_update']		= ($row->ModuleVersion != $module['ModuleVersion']);
					$module['template']			= ($row->ModuleTemplate ? $row->ModuleTemplate : 0);
					$module['ModuleAveTag']		= $row->ModuleAveTag;
					$module['ModulePHPTag']		= $row->ModulePHPTag;
					$module['ModuleStatus']		= $row->ModuleStatus;
					$module['ModuleVersion']	= $row->ModuleVersion;
				}
				// неустановленные модули
				else
					{
						$module['id']				= $module['ModuleSysName'];
						$module['template']			= (! empty($module['ModuleTemplate']) ? $module['ModuleTemplate'] : '');
						$module['ModuleStatus']		= false;
					}

				// записываем в массив
				$modules[$module['ModuleSysName']] = $module;

				unset ($module);
			}

			// Закрываем папку
			$d->Close();

			return $modules;
		}


		/**
		 * Метод, преданзначеный для выода модулей
		 *
		 */
		function moduleList()
		{
			global $AVE_DB, $AVE_Template;

			$assign				= array(); // Массив для передачи в Smarty
			$errors				= array(); // Массив с ошибками

			// Получаем список всех шаблонов
			$sql = $AVE_DB->Query("
				SELECT
					Id,
					template_title
				FROM
					" . PREFIX . "_templates
			");

			while ($row = $sql->FetchRow())
				$assign['all_templates'][$row->Id] = htmlspecialchars($row->template_title, ENT_QUOTES);

			// Заголовок: Автор модуля
			$author_title = $AVE_Template->get_config_vars('MODULES_AUTHOR');

			// Получаем список всех модулей
			$modules = $this->_modules;

			foreach ($modules as $module)
			{
				$module['info'] = $module['ModuleDescription'] . (! $module['ModuleAutor']
					? '<br /><br />'
					: "<br /><br /><strong>$author_title</strong><br />" . $module['ModuleAutor'] . "<br />") . '<br /><em>' . $module['ModuleCopyright'] . '</em>';
				// установленные модули
				if ($module['ModuleStatus'] !== false)
					$installed_modules[$module['ModuleSysName']] = $module;
				// неустановленные модули
				else
					$not_installed_modules[$module['ModuleSysName']] = $module;
			}

			! empty($installed_modules)
				? ksort($installed_modules)
				: $installed_modules = '';

			! empty($not_installed_modules)
				? ksort($not_installed_modules)
				: $not_installed_modules = '';

			$assign['installed_modules'] 		= $installed_modules;
			$assign['not_installed_modules'] 	= $not_installed_modules;

			// Массив с ошибками
			if (! empty($modules['errors']))
			{
				foreach ($modules['errors'] as $error)
				{
					$assign['errors'][] = $AVE_Template->get_config_vars('MODULES_ERROR') . $error;
				}
			}

			// Передаем данные в шаблон и отображаем страницу со списком модулей
			$AVE_Template->assign($assign);
			$AVE_Template->assign('content', $AVE_Template->fetch('modules/modules.tpl'));
		}


		/**
		 * Метод получения списка модулей
		 *
		 * @param int $status статус возвращаемых модулей
		 *		1 - активные модули
		 *		0 - неактивные модули
		 * если не указано возвращает модули без учета статуса
		 * @return array
		 */
		function moduleListGet($status = null)
		{
			global $AVE_DB;

			$modules = array();

			if (! empty($this->_modules))
			{
				foreach ($this->_modules AS $k => $v)
				{
					if ($status && $v['ModuleStatus'] != $status)
						continue;

					$modules[$k] = $v;
				}
			}
			else
				{
					// Условие, определяющее статус документа для запроса к БД
					$where_status = ($status !== null)
						? "WHERE ModuleStatus = '" . (int)$status . "'"
						: '';

					// Выполняем запрос к БД и получаем список документов,
					// согласно статусу, либо все модули, если статус не указан
					$sql = "
						SELECT
							# MODULES
							*
						FROM
							" . PREFIX . "_module
						" . $where_status . "
						ORDER BY
							ModuleName ASC
					";

					$query = $AVE_DB->Query($sql, -1, 'modules', true, '.modules');

					while ($row = $query->FetchRow())
						$modules[$row->ModuleSysName] = $row;
				}

			// Возвращаем список модулей
			return $modules;
		}


		/**
		 * Метод, предназначенный для обновления в БД информации о шаблонах модулей
		 *
		 */
		function moduleOptionsSave()
		{
			global $AVE_DB;

			// Циклически обрабатываем массив с информацией о шаблонах модулей
			foreach ($_POST['Template'] as $id => $template_id)
			{
				// Обновление информации о шаблоне модуля
				$AVE_DB->Query("
					UPDATE
						" . PREFIX . "_module
					SET
						ModuleTemplate = '" . (int)$template_id . "'
					WHERE
						Id = '" . (int)$id . "'
				");
			}

			$this->clearModulesCache();

			// Выполянем обновление страницы со списком модулей
			header('Location:index.php?do=modules&cp=' . SESSION);
			exit;
		}


		/**
		 * Метод, предназанченный для установки или переустановки модуля
		 *
		 */
		function moduleInstall()
		{
			global $AVE_DB, $AVE_Template;

			// Получаем данные модуля
			$modules = $this->_modules;

			$module = $modules[MODULE_PATH];

			// Удаляем информацию о модуле в таблице module
			$AVE_DB->Query("
				DELETE
				FROM
					" . PREFIX . "_module
				WHERE
					ModuleSysName = '" . MODULE_PATH . "'
			");

			// Определяем, имеет ли модуль возможность настройки в Панели управления
			$module['ModuleAdminEdit'] = (!empty($module['ModuleAdminEdit']))
				? $module['ModuleAdminEdit']
				: 0;

			// Определяем, имеет ли модуль возможность смены шаблона
			$module['ModuleTemplate'] = ($module['ModuleTemplate'])
				? $module['ModuleTemplate']
				: 0;

			// Добавляем информацию о модуле в таблицу module
			$AVE_DB->Query("
				INSERT INTO
					" . PREFIX . "_module
				SET
					ModuleName			= '" . $module['ModuleName'] . "',
					ModuleStatus		= '1',
					ModuleAveTag		= '" . $module['ModuleAveTag'] . "',
					ModulePHPTag		= '" . $module['ModulePHPTag'] . "',
					ModuleFunction		= '" . $module['ModuleFunction'] . "',
					ModuleIsFunction	= '" . $module['ModuleIsFunction'] . "',
					ModuleSysName		= '" . MODULE_PATH . "',
					ModuleVersion		= '" . $module['ModuleVersion'] . "',
					ModuleTemplate		= '" . $module['ModuleTemplate'] . "',
					ModuleAdminEdit		= '" . $module['ModuleAdminEdit'] . "'
			");

			// Подключаем файл с запросами к БД для данного модуля
			$module_sql_deinstall = array();
			$module_sql_install = array();
			$sql_file = BASE_DIR . '/modules/' . MODULE_PATH . '/sql.php';

			if (is_file($sql_file) && @include($sql_file))
			{
				// Выполняем запросы удаления таблиц модуля
				// из массива $module_sql_deinstall файла sql.php
				foreach ($module_sql_deinstall as $sql)
				{
					$AVE_DB->Query(str_replace('%%PRFX%%', PREFIX, $sql));
				}

				// Выполняем запросы создания таблиц и данных модуля
				// из массива $module_sql_install файла sql.php
				foreach ($module_sql_install as $sql)
				{
					$AVE_DB->Query(str_replace('%%PRFX%%', PREFIX, $sql));
				}
			}
			// Сохраняем системное сообщение в журнал
			($_REQUEST['action'] == "reinstall")
				? reportLog($AVE_Template->get_config_vars('MODULES_ACTION_REINSTALL') . ' (' . $module['ModuleName'] . ')')
				: reportLog($AVE_Template->get_config_vars('MODULES_ACTION_INSTALL') . ' (' . $module['ModuleName'] . ')');

			$this->clearModulesCache();

			// Выполняем обновление страницы со списком модулей
			header('Location:index.php?do=modules&cp=' . SESSION);
			exit;
		}


		/**
		 * Метод, предназначенный для обновления модуля при увеличении номера версии модуля
		 *
		 */
		function moduleUpdate()
		{
			global $AVE_DB, $AVE_Template;

			// Подключаем файл с запросами к БД для данного модуля
			$module_sql_update = array();

			$sql_file = BASE_DIR . '/modules/' . MODULE_PATH . '/sql.php';

			$mod_file = BASE_DIR . '/modules/' . MODULE_PATH . '/info.php';

			if (file_exists($mod_file) && file_exists($sql_file))
			{
				include ($mod_file);
				include ($sql_file);

				// Выполняем запросы обновления модуля
				// из массива $module_sql_update файла sql.php
				foreach ($module_sql_update as $sql)
				{
					$AVE_DB->Query(str_replace('%%PRFX%%', PREFIX, $sql));
				}
			}
			// Обновляем модуль, если в нем не применяется (отсутствует) файл sql.php
			elseif (file_exists($mod_file) && file_exists($sql_file) === false)
			{
				include_once ($mod_file);

				$AVE_DB->Query("
					UPDATE
						" . PREFIX . "_module
					SET
						ModuleAveTag		= '" . $module['ModuleAveTag'] . "',
						ModulePHPTag		= '" . $module['ModulePHPTag'] . "',
						ModuleFunction		= '" . $module['ModuleFunction'] . "',
						ModuleIsFunction	= '" . $module['ModuleIsFunction'] . "',
						ModuleSysName		= '" . MODULE_PATH . "',
						ModuleVersion		= '" . $module['ModuleVersion'] . "',
						ModuleTemplate		= '" . $module['ModuleTemplate'] . "',
						ModuleAdminEdit		= '" . $module['ModuleAdminEdit'] . "',
						ModuleStatus		= '1'
					WHERE
						ModuleSysName = '" . MODULE_PATH . "'
				");
			}
			// Сохраняем системное сообщение в журнал
			reportLog ($AVE_Template->get_config_vars('MODULES_ACTION_UPDATE') . ' (' . MODULE_PATH . ')');

			$this->clearModulesCache();

			// Выполянем обновление страницы со списком модулей
			header('Location:index.php?do=modules&cp=' . SESSION);
			exit;
		}


		/**
		 * Метод, предназанченный для удаление модуля
		 *
		 */
		function moduleDelete()
		{
			global $AVE_DB, $AVE_Template;

			// Подключаем файл с запросами к БД для данного модуля
			$module_sql_deinstall = array();

			$sql_file = BASE_DIR . '/modules/' . MODULE_PATH . '/sql.php';

			if (is_file($sql_file) && @include($sql_file))
			{
				// Выполняем запросы удаления таблиц модуля
				// из массива $module_sql_deinstall файла sql.php
				foreach ($module_sql_deinstall as $sql)
				{
					$AVE_DB->Query(str_replace('%%PRFX%%', PREFIX, $sql));
				}
			}

			// Удаляем информацию о модуле в таблице module
			$AVE_DB->Query("
				DELETE
				FROM
					" . PREFIX . "_module
				WHERE
					ModuleSysName = '" . MODULE_PATH . "'
			");

			$this->clearModulesCache();

			// Сохраняем системное сообщение в журнал
			reportLog ($AVE_Template->get_config_vars('MODULES_ACTION_DELETE') .' (' . MODULE_PATH . ')');

			// Выполянем обновление страницы со списком модулей
			header('Location:index.php?do=modules&cp=' . SESSION);
			exit;
		}


		/**
		 * Метод, предназначенный для отключения/включение модуля в Панели управления
		 *
		 */
		function moduleStatusChange()
		{
			global $AVE_DB, $AVE_Template;

			$status = $AVE_DB->Query("
				SELECT
					ModuleName,
					ModuleStatus
				FROM
					" . PREFIX . "_module
				WHERE
					ModuleSysName = '" . MODULE_PATH . "'
			")->FetchRow();

			$ModuleStatus = ($status->ModuleStatus == "0" || $status->ModuleStatus == NULL)
				? "1"
				: "0";

			// Выполняем запрос к БД на смену статуса модуля
			$AVE_DB->Query("
				UPDATE
					" . PREFIX . "_module
				SET
					ModuleStatus = '".$ModuleStatus."'
				WHERE
					ModuleSysName = '" . MODULE_PATH . "'
			");

			$this->clearModulesCache();

			// Сохраняем системное сообщение в журнал
			reportLog ((($ModuleStatus == "0")
				? $AVE_Template->get_config_vars('MODULES_ACTION_OFFLINE')
				: $AVE_Template->get_config_vars('MODULES_ACTION_ONLINE')) . ' (' . $status->ModuleName . ')');

			// Выполняем обновление страницы со списком модулей
			header('Location:index.php?do=modules&cp=' . SESSION);
			exit;
		}


		function moduleRemove($dir)
		{
			global $AVE_DB, $AVE_Template;

			$directory = BASE_DIR . '/modules/' . $dir;

			$files = glob($directory . '*', GLOB_MARK);

			foreach ($files as $file)
			{
				if(substr($file, -1) == '/')
					moduleRemove($file);
				else
					unlink($file);
			}

			rrmdir ($directory);

			$this->clearModulesCache();

			// Сохраняем системное сообщение в журнал
			reportLog ($AVE_Template->get_config_vars('MODULES_ACTION_REMOVE') . ' (' . $dir . ')');

			// Выполянем обновление страницы со списком модулей
			header('Location:index.php?do=modules&cp=' . SESSION);
			exit;
		}


		/**
		 * Функция очищает кеш системных настроек
		 *
		 */
		function clearModulesCache()
		{
			$cache_dir = BASE_DIR . '/tmp/cache/sql/modules/';

			return rrmdir($cache_dir);
		}
	}
?>