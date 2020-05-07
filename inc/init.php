<?php

	/**
	 * AVE.cms
	 *
	 * @package AVE.cms
	 * @version 3.x
	 * @filesource
	 * @copyright © 2007-2020 AVE.cms, https://ave-cms.ru
	 *
	 * @license GPL v.2
	 */

	if (! defined('BASE_DIR'))
		exit;

	define ('DS', DIRECTORY_SEPARATOR);

	@date_default_timezone_set('Europe/Moscow');

	//-- Подключаем файл настроек
	require_once (BASE_DIR . '/inc/config.php');

	if (PHP_DEBUGGING_FILE && ! defined('ACP'))
		include_once BASE_DIR . '/inc/errors.php';

	//-- Debug Class
	require(BASE_DIR . '/class/class.debug.php');
	new Debug;

	//-- Hooks Class
	require(BASE_DIR . '/class/class.hooks.php');
	new Hooks;

	//-- Удаление глобальных массивов
	function unsetGlobals()
	{
		if (! ini_get('register_globals'))
			return;

		$allowed = array(
			'_ENV'		=> 1,
			'_GET'		=> 1,
			'_POST'		=> 1,
			'_COOKIE'	=> 1,
			'_FILES'	=> 1,
			'_SERVER'	=> 1,
			'_REQUEST'	=> 1,
			'GLOBALS'	=> 1
		);

		foreach ($GLOBALS as $key => $value)
		{
			if (! isset($allowed[$key]))
				unset($GLOBALS[$key]);
		}
	}

	unsetGlobals();

	if (isset($HTTP_POST_VARS))
	{
		$_GET		= $HTTP_GET_VARS;
		$_POST		= $HTTP_POST_VARS;
		$_REQUEST	= array_merge($_POST, $_GET);
		$_COOKIE 	= $HTTP_COOKIE_VARS;
	}

	/**
	 * Слешевание (для глобальных массивов)
	 * рекурсивно обрабатывает вложенные массивы
	 *
	 * @param array $array обрабатываемый массив
	 * @return array обработанный массив
	 */
	function add_slashes($array = array())
	{
		@reset($array);

		foreach ($array AS $_k => $_v)
		{
			if (is_string($_v))
				$array[$_k] = addslashes($_v);
			elseif (is_array($_v))
				$array[$_k] = add_slashes($_v);
		}

		return $array;
	}


	if (! get_magic_quotes_gpc())
	{
		$_GET		= add_slashes($_GET);
		$_POST		= add_slashes($_POST);
		$_REQUEST	= array_merge($_POST, $_GET);
		$_COOKIE	= add_slashes($_COOKIE);
	}


	function isSSL()
	{
		if (isset($_SERVER['HTTPS']))
		{
			if ('on' == strtolower($_SERVER['HTTPS']))
				return true;

			if ('1' == $_SERVER['HTTPS'])
				return true;
		}
		elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT']))
			{
				return true;
			}

		return false;
	}


	function setHost()
	{
		if (isset($_SERVER['HTTP_HOST']))
		{
			// Все символы $_SERVER['HTTP_HOST'] приводим к строчным и проверяем
			// на наличие запрещённых символов в соответствии с RFC 952 и RFC 2181.
			$_SERVER['HTTP_HOST'] = strtolower($_SERVER['HTTP_HOST']);

			if (! preg_match('/^\[?(?:[a-z0-9-:\]_]+\.?)+$/', $_SERVER['HTTP_HOST']))
			{
				// $_SERVER['HTTP_HOST'] не соответствует спецификациям.
				// Возможно попытка взлома, даём отлуп статусом 400.
				header('HTTP/1.1 400 Bad Request');
				exit;
			}
		}
		else
			{
				$_SERVER['HTTP_HOST'] = '';
			}

		$ssl = isSSL();
		$schema = ($ssl) ? 'https://' : 'http://';
		$host = str_replace(':' . $_SERVER['SERVER_PORT'], '', $_SERVER['HTTP_HOST']);
		$port = ($_SERVER['SERVER_PORT'] == '80' || $_SERVER['SERVER_PORT'] == '443' || $ssl)
			? ''
			: ':' . $_SERVER['SERVER_PORT'];

		define('HOST', $schema . $host . $port);

		$abs_path = dirname((!strstr($_SERVER['PHP_SELF'], $_SERVER['SCRIPT_NAME']) && (@php_sapi_name() == 'cgi'))
			? $_SERVER['PHP_SELF']
			: $_SERVER['SCRIPT_NAME']);

		if (defined('ACP'))
			$abs_path = dirname($abs_path);

		define('ABS_PATH', rtrim(str_replace("\\", "/", $abs_path), '/') . '/');
	}

	setHost();

	set_include_path (get_include_path() . '/' . BASE_DIR . '/lib');

	ini_set ('arg_separator.output',     '&amp;');
	ini_set ('session.cache_limiter',    'none');
	ini_set ('session.cookie_lifetime',  60*60*24*14);
	ini_set ('session.gc_maxlifetime',   60*24);
	ini_set ('session.use_cookies',      1);
	ini_set ('session.use_only_cookies', 1);
	ini_set ('session.use_trans_sid',    0);
	ini_set ('url_rewriter.tags',        '');

	if (SESSION_SAVE_HANDLER == 'memcached')
	{
		ini_set ('session.lazy_write',		0);
		ini_set ('session.save_handler',	'memcached');
		ini_set ('session.save_path',		MEMCACHED_SERVER.':'.MEMCACHED_PORT . '?persistent=1&weight=1&timeout=1&retry_interval=15');
	}

	//-- Переключение для нормальной работы с русскими буквами в некоторых функциях
	mb_internal_encoding("UTF-8");

	//-- Вкл/Выкл отображения ошибок php
	if (! PHP_DEBUGGING_FILE)
	{
		if (! PHP_DEBUGGING)
		{
			error_reporting(E_ERROR);
			ini_set('display_errors', 7);
		}
		else
			{
				error_reporting(E_ALL);
				ini_set('display_errors', 1);
			}
	}

	//-- Подкючаем необходимые файлы функций
	require_once (BASE_DIR . '/functions/func.breadcrumbs.php');	// Хлебные крошки
	require_once (BASE_DIR . '/functions/func.common.php');			// Основные функции
	require_once (BASE_DIR . '/functions/func.locale.php');			// Языковые функции
	require_once (BASE_DIR . '/functions/func.documents.php');		// Функции по работе с документами
	require_once (BASE_DIR . '/functions/func.fields.php');			// Функции по работе с полями
	require_once (BASE_DIR . '/functions/func.helpers.php');		// Второстепенные функции
	require_once (BASE_DIR . '/functions/func.hidden.php');			// Парс тега [hide]
	require_once (BASE_DIR . '/functions/func.login.php');			// Авторизация пользователей
	require_once (BASE_DIR . '/functions/func.logs.php');			// Системные сообщения
	require_once (BASE_DIR . '/functions/func.mail.php');			// Отправка писем
	require_once (BASE_DIR . '/functions/func.navigation.php');		// Функции по работе с меню навигации
	require_once (BASE_DIR . '/functions/func.pagination.php');		// Постраничная навигация
	require_once (BASE_DIR . '/functions/func.parserequest.php');	// Функции по работе с запросами
	require_once (BASE_DIR . '/functions/func.block.php');			// Функции по работе с визуальными блоками
	require_once (BASE_DIR . '/functions/func.sysblock.php');		// Функции по работе с системными блоками
	require_once (BASE_DIR . '/functions/func.thumbnails.php');		// Функции по работе с превьюшками изображений
	require_once (BASE_DIR . '/functions/func.users.php');			// Функции по работе с пользователями
	require_once (BASE_DIR . '/functions/func.watermarks.php');		// Функции по работе с водными знаками

	//-- Logs Class
	require(BASE_DIR . '/class/class.logs.php');


	//-- Создание папок и файлов
	foreach ([ATTACH_DIR, 'cache', 'backup', 'logs', 'session', 'update'] AS $dir)
		write_htaccess_deny(BASE_DIR . '/tmp/' . $dir);

	foreach (['combine', 'module', 'redactor', 'smarty', 'sql', 'tpl'] AS $dir)
		write_htaccess_deny(BASE_DIR . '/tmp/cache/' . $dir);

	//-- Шаблоны
	write_htaccess_deny(BASE_DIR . '/templates/' . DEFAULT_THEME_FOLDER . '/include/');

	global $AVE_DB, $fields_data;

	//-- Класс для работы с MySQL (Global $AVE_DB)
	require_once (BASE_DIR . '/class/class.database.php');

	//-- Если не существует объекта по работе с БД
	if (! isset($AVE_DB))
	{
		//-- Подключаем конфигурационный файл с параметрами подключения
		require_once (BASE_DIR . '/config/db.config.php');

		//-- Если параметры не указаны, прерываем работу
		if (! isset($config) OR empty($config))
			die('No database config');

		//-- Если константа префикса таблиц не задана, принудительно определяем ее на основании параметров в файле db.config.php
		if (! defined('PREFIX'))
			define('PREFIX', $config['dbpref']);

		//-- Создаем объект для работы с БД
		try {
			$AVE_DB = AVE_DB::getInstance($config)
				//-- Назначаем кодировку
				->setCharset('utf8')
				//-- Назначаем БД
				->setDatabaseName($config['dbname'])
				//-- SQL Mode
				->setSqlMode();
		}
		catch (AVE_DB_Exception $e)
			{
				ob_start();
				header('HTTP/1.1 503 Service Temporarily Unavailable');
				header('Status: 503 Service Temporarily Unavailable');
				header('Retry-After: 3600');
				header('X-Powered-By:');
				echo $e->getMessage();
				die;
			}

		unset ($config);
	}

	//-- Устанавливаем обновления системы
	if ($AVE_DB)
	{
		$updaters = (glob(BASE_DIR . '/tmp/update/*.update.php'));

		if ($updaters)
		{
			sort ($updaters);

			foreach ($updaters as $ufile)
			{
				@eval(' ?'.'>' . @file_get_contents($ufile) . '<?'.'php ');

				if ($ufile != BASE_DIR . '/tmp/update/debug.update.php')
				{
					@unlink($ufile);
					@reportLog('Установил обновления (' . $ufile . ')');
				}
			}
		}
	}

	set_cookie_domain();

	//-- Работа с сессиями
	if (! SESSION_SAVE_HANDLER || SESSION_SAVE_HANDLER == 'files')
	{
		//-- Класс для работы с сессиями
		require (BASE_DIR . '/class/class.session.files.php');
		$ses_class = new AVE_Session();
	}
	//-- Работа с сессиями
	else if (SESSION_SAVE_HANDLER == 'memcached')
	{
		//-- Класс для работы с сессиями
		require (BASE_DIR . '/class/class.session.memcached.php');
		$ses_class = new AVE_Session_Memcached();
	}
	else
		{
			//-- Класс для работы с сессиями
			require (BASE_DIR . '/class/class.session.php');
			$ses_class = new AVE_Session_DB();
		}

	//-- Изменяем save_handler, используем функции класса
	session_set_save_handler (
		array(&$ses_class, '_open'),
		array(&$ses_class, '_close'),
		array(&$ses_class, '_read'),
		array(&$ses_class, '_write'),
		array(&$ses_class, '_destroy'),
		array(&$ses_class, '_gc')
	);

	//-- Страт сессии
	if (session_status() !== PHP_SESSION_ACTIVE)
		session_start();

	if (isset($HTTP_SESSION_VARS))
		$_SESSION = $HTTP_SESSION_VARS;

	//-- Logout
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'logout')
	{
		user_logout();

		header('Location:' . get_referer_link());
		exit;
	}

	//-- Если нет авторизации
	if (! defined('ACPL') && ! auth_sessions())
	{
		if (! auth_cookie())
		{
			//-- Чистим данные авторизации в сессии
			unset($_SESSION['user_id'], $_SESSION['user_pass']);

			//-- Считаем пользователя Гостем
			$_SESSION['user_group'] = 2;
			$_SESSION['user_name'] = get_username();

			define('UID', 0);
			define('UGROUP', 2);
			define('UNAME', $_SESSION['user_name']);
		}
	}

	//-- Запоминаем время последнего визита пользователя
	if (! empty($_SESSION['user_id']))
	{
		$AVE_DB->Query("
			UPDATE
				" . PREFIX . "_users
			SET
				last_visit = '" . time() . "'
			WHERE
				Id = '" . intval($_SESSION['user_id']) . "'
		");
	}

	//-- Запоминаем язык браузера
	$browlang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	$browlang = explode('-', $browlang);
	$browlang = $browlang[0];

	$_SESSION['accept_langs'] = array();

	$sql = "
		SELECT
			# LANGS
			*
		FROM
			" . PREFIX . "_settings_lang
		WHERE
			lang_status = '1'
		ORDER BY
			lang_default ASC
	";

	$query = $AVE_DB->Query($sql, -1, 'langs', true, '.langs');

	while ($row = $query->FetchRow())
	{
		if (trim($row->lang_key) > '')
		{
			$_SESSION['accept_langs'][trim($row->lang_key)] = trim($row->lang_alias_pref);

			if (! @defined('DEFAULT_LANGUAGE') && $row->lang_default == 1)
				define('DEFAULT_LANGUAGE', trim($row->lang_key));
		}
	}

	//-- Язык пользователя
	$_SESSION['user_language'] = (! empty($_SESSION['user_language'])
		? $_SESSION['user_language']
		:(isset($_SESSION['accept_langs'][$browlang])
			? $browlang
			: DEFAULT_LANGUAGE));

	define('DATE_FORMAT', get_settings('date_format'));
	define('TIME_FORMAT', get_settings('time_format'));
	define('PAGE_NOT_FOUND_ID', (int)get_settings('page_not_found_id'));

	//-- Вывод данных документа без общего шаблона
	if (isset($_REQUEST['onlycontent']) && 1 == $_REQUEST['onlycontent'])
		define('ONLYCONTENT', 1);

	//-- Язык системы
	set_locale();

	//-- Класс Шаблонов SMARTY
	require (BASE_DIR . '/class/class.template.php');

	//-- Класс пагинации
	require (BASE_DIR . '/class/class.paginations.php');

	// Класс UTM
	require (BASE_DIR . '/class/class.utm.php');
	$AVE_Utm = new UTMCookie;

	$AVE_Utm->save_parameters();

	//-- Класс Модулей
	require (BASE_DIR . '/class/class.modules.php');
	$AVE_Module = AVE_Module::init();
?>