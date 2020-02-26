<?php

	/**
	 * AVE.cms
	 *
	 * @package AVE.cms
	 * @version 3.x
	 * @filesource
	 * @copyright © 2007-2014 AVE.cms, http://www.ave-cms.ru
	 *
	 * @license GPL v.2
	 */


	/**
	 * Проверка подключения к БД
	 *
	 * @param string $dbhost
	 * @param string $dbuser
	 * @param string $dbpass
	 *
	 * @return bool
	 */
	function check_mysql_connect ($dbhost = '', $dbuser = '', $dbpass = '')
	{
		if ($dbhost != '' && $dbuser != '')
			if (@mysqli_connect($dbhost, $dbuser, $dbpass))
				return true;

		return false;
	}


	/**
	 * Проверка запроса к БД
	 *
	 * @param object $mysql_connect
	 * @param string $sql
	 *
	 * @return bool
	 */
	function check_mysql_query ($mysql_connect, $sql = '')
	{
		if ($sql != '' && $mysql_connect != '')
			if (@mysqli_query($mysql_connect, $sql))
				return true;

		return false;
	}


	/**
	 * @param string $dbhost
	 * @param string $dbuser
	 * @param string $dbpass
	 * @param string $dbname
	 *
	 * @return bool
	 */
	function check_db_connect ($dbhost = '', $dbuser = '', $dbpass = '', $dbname = '')
	{
		if ($dbhost != '' && $dbuser != '' && $dbname != '')
			if (@mysqli_select_db(@mysqli_connect($dbhost, $dbuser, $dbpass), $dbname))
				return true;

		return false;
	}


	/**
	 * @param $prefix
	 *
	 * @return bool
	 */
	function check_installed ($prefix)
	{
		global $config;

		$mysql = @mysqli_connect($config['dbhost'], $config['dbuser'], $config['dbpass'], $config['dbname']);
		$query = @mysqli_query($mysql, "SELECT 1 FROM " . $prefix . "_users LIMIT 1");

		if (@mysqli_num_rows($query))
			return true;
		else
			return false;
	}


	/**
	 *
	 */
	function check_required ()
	{
		global $error_is_required, $lang_i;

		$required_php = 522;
		$required = array();
		$required[] = '/install/eula/ru.tpl';

		foreach ($required as $is_required)
		{
			if (@!is_file(BASE_DIR . $is_required))
				array_push($error_is_required, $lang_i['error_is_required'] . $is_required . $lang_i['error_is_required_2'] );
		}

		$myphp = @PHP_VERSION;

		if ($myphp)
		{
			$myphp_v = str_replace('.', '', $myphp);

			if ($myphp_v < $required_php)
				array_push($error_is_required, $lang_i['phpversion_toold'] . $required_php);
		}
	}


	/**
	 *
	 */
	function check_writable ()
	{
		global $error_is_required, $lang_i;

		$writeable = array();
		$writeable[] = '/tmp/';
		$writeable[] = '/tmp/cache/';
		$writeable[] = '/uploads/';
		$writeable[] = '/config/db.config.php';

		foreach ($writeable as $must_writeable)
		{
			if (! is_writable(BASE_DIR . $must_writeable))
				array_push($error_is_required, $lang_i['error_is_writeable'] . $must_writeable . $lang_i['error_is_writeable_2'] );
		}
	}


	/**
	 * @param string $name
	 * @param string $prefix
	 * @param        $mysql_connect
	 */
	function clean_db ($name, $prefix, $mysql_connect)
	{
		@mysqli_select_db($mysql_connect, $name);

		$query = @mysqli_query($mysql_connect, "SHOW TABLES FROM " . $name);

		while ($row = @mysqli_fetch_array($query, MYSQLI_NUM))
			if (preg_match("/^" . $prefix . "/", $row[0]))
				@mysqli_query($mysql_connect ,"DROP TABLE " . $row[0]);
	}


	/**
	 * Convert size from 10M to bytes
	 *
	 * @param string $str e.g. 10M
	 * @return int
	 */
	function convertSizeToBytes($str)
	{
		$str = trim($str);
		if (strlen($str) > 0)
		{
			$cLastAlpha = strtolower(substr($str, -1));
			$size = intval($str);
			switch($cLastAlpha)
			{
				case 't':
					$size *= 1024;
				case 'g':
					$size *= 1024;
				case 'm':
					$size *= 1024;
				case 'k':
					$size *= 1024;
			}
		}
		else
		{
			$size = 0;
		}
		return $size;
	}


	/**
	 * Get GD version
	 * @return string
	 */
	function getGdVersion()
	{
		if (function_exists('gd_info'))
		{
			$gd_info = @gd_info();
			return preg_replace('/[^0-9\.]/', '', $gd_info['GD Version']);
		}

		return NULL;
	}


	/**
	 * Get PCRE version
	 * @return string
	 */
	function getPcreVersion()
	{
		defined('PCRE_VERSION')
			? list($version) = explode(' ', constant('PCRE_VERSION'))
			: $version = NULL;

		return $version;
	}


	/**
	 * Get MySQL version
	 * @return string
	 */
	function getMySQLVersion() {
		$output = mysqli_get_client_info();
		preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);
		return $version[0];
	}


	/**
	 * @param $level
	 * @param $text
	 *
	 * @return string
	 */
	function check_param($level, $text)
	{
		$level = intval($level);

		switch ($level)
		{
			//Параметр не соответствует.
			case 2:
				$img = 'ico_delete';
			break;
			//Несоответствие, не влияющее на функционирование системы.
			case 1:
				$img = 'ico_ok';
			break;
			//Параметр соответствует.
			case 0:
				$img = 'ico_ok_green';
			break;
			//По умолчанию
			default:
				$img = 'ico_ok_noproblem';
			break;
		}

		return $img;
	}


	/* @subpackage install  */


	error_reporting(E_ERROR);
	ini_set('display_errors', 7);

	global $config;

	ob_start();

	define('SETUP', 1);

	define('BASE_DIR', str_replace("\\", "/", dirname(dirname(__FILE__))));

	//-- Debug Class
	require (BASE_DIR . '/class/class.debug.php');
	$Debug = new Debug;

	if (! is_writable(BASE_DIR . '/tmp/cache/smarty/'))
		die('Cache folder is not writeable!');

	include (BASE_DIR . '/config/db.config.php');
	include (BASE_DIR . '/inc/config.php');
	include (BASE_DIR . '/functions/func.common.php');
	include (BASE_DIR . '/functions/func.logs.php');
	include (BASE_DIR . '/functions/func.helpers.php');
	include (BASE_DIR . '/class/class.template.php');

	$AVE_Template = new AVE_Template(BASE_DIR . '/install/tpl/');

	$lang_file = BASE_DIR . '/install/lang/ru.txt';

	$AVE_Template->config_load($lang_file);

	$ver = APP_NAME . ' v' . APP_VERSION;

	$db_connect = check_db_connect($config['dbhost'], $config['dbuser'], $config['dbpass'], $config['dbname']);
	$check_installed = check_installed($config['dbpref']);

	if ((true === $db_connect) && $_REQUEST['step'] != 'finish' && check_installed($config['dbpref']))
	{
		echo '<pre>' . $AVE_Template->get_config_vars('installed') . '</pre>';
		exit;
	};

	$error_is_required = array();

	check_required();
	check_writable();

	// include_once(BASE_DIR . '/inc/errors.php');

	$count_error = sizeof((array) $error_is_required);

	if (1 == $count_error)
	{
		$AVE_Template->assign('error_header', $AVE_Template->get_config_vars('erroro'));
	}
	elseif ($count_error > 1)
		{
			$AVE_Template->assign('error_header', $AVE_Template->get_config_vars('erroro_more'));
		}

	if ($count_error > 0 && ! (isset($_REQUEST['force']) && 1 == $_REQUEST['force']))
	{
		$AVE_Template->assign('error_is_required', $error_is_required);
		$AVE_Template->display('error.tpl');
		exit;
	}

	$_REQUEST['step'] = isset($_REQUEST['step']) ? $_REQUEST['step'] : '';

	// Минимальные требования к системе
	define('PHP_version', '5.6');
	define('MySQL_version', '5.0.0');
	define('GD_version', '2.0');
	define('PCRE_version', '7.0');
	define('JSON', $AVE_Template->get_config_vars('mess_on'));
	define('MbString', $AVE_Template->get_config_vars('mess_on'));
	define('SimpleXML', $AVE_Template->get_config_vars('mess_on'));
	define('Iconv', $AVE_Template->get_config_vars('mess_on'));
	define('XSLT', $AVE_Template->get_config_vars('mess_supported'));
	define('Data_limit', '2'); // Mb
	define('TIME_limit', '30'); // Sec
	define('DISC_space', '30'); // Mb
	define('RAM_space', '32M'); // Mb
	define('SAFE_MODE', $AVE_Template->get_config_vars('mess_off'));
	define('REGISTER_GLOBALS', $AVE_Template->get_config_vars('mess_off'));
	define('MAGIC_QUOTES_GPC', $AVE_Template->get_config_vars('mess_off'));

	switch ($_REQUEST['step'])
	{
		//Начало
		case '' :

		//Шаг 1
		case '1' :
			$AVE_Template->display('step1.tpl');
			break;

		//Шаг 2
		case '2' :
			$AVE_Template->display('step2.tpl');
			break;

		//Шаг 3
		case '3' :
			$test['php_version'] = phpversion();
			$test['mysql_version'] = getMySQLVersion();
			$test['gd_version'] = getGdVersion();
			$test['pcre_version'] = getPcreVersion();
			$test['json'] = function_exists('json_encode') ? $AVE_Template->get_config_vars('mess_on') : $AVE_Template->get_config_vars('mess_off');
			$test['simplexml'] = function_exists('simplexml_load_string') ? $AVE_Template->get_config_vars('mess_on') : $AVE_Template->get_config_vars('mess_off');
			$test['mbstring'] = function_exists('mb_internal_encoding') ? $AVE_Template->get_config_vars('mess_on') : $AVE_Template->get_config_vars('mess_off');
			$test['iconv'] = function_exists('iconv') ? $AVE_Template->get_config_vars('mess_on') : $AVE_Template->get_config_vars('mess_off');
			$test['xslt'] = (function_exists('xslt_create') || function_exists('domxml_xslt_stylesheet') || (class_exists('DomDocument') && class_exists('XsltProcessor'))) ? XSLT : $AVE_Template->get_config_vars('mess_unsupported');
			$test['data_limit'] = ini_get('post_max_size') ? ini_get('post_max_size') : $AVE_Template->get_config_vars('mess_undefined');
			$test['time_limit'] = ini_get('max_execution_time') ? ini_get('max_execution_time') : $AVE_Template->get_config_vars('mess_undefined');
			$test['disk_space'] = round(@disk_free_space($_SERVER['DOCUMENT_ROOT']) / 1024 / 1024, 2);
			$test['memmory_limit'] = ini_get('memory_limit') ? ini_get('memory_limit') : $AVE_Template->get_config_vars('mess_undefined');
			$test['s_m'] = (ini_get('safe_mode') == 1) ? $AVE_Template->get_config_vars('mess_on') : $AVE_Template->get_config_vars('mess_off');
			$test['r_g'] = (ini_get('register_globals') == 1) ? $AVE_Template->get_config_vars('mess_on') : $AVE_Template->get_config_vars('mess_off');
			$test['m_q'] = (ini_get('magic_quotes_gpc') == 1 || ini_get('magic_quotes_runtime') == 1 || ini_get('magic_quotes_sybase') == 1) ? $AVE_Template->get_config_vars('mess_on') : $AVE_Template->get_config_vars('mess_off');

			$check['php_version'] = check_param(version_compare(phpversion(), PHP_version, ">=") ? 0 : 2, $test['php_version']);
			$check['mysql_version'] = check_param(version_compare(getMySQLVersion(), MySQL_version, ">=") ? 0 : 2, $test['mysql_version']);
			$check['gd_version'] = check_param(version_compare($test['gd_version'], GD_version, ">=") ? 0 : 2, $test['gd_version']);
			$check['pcre_version'] = check_param(version_compare($test['pcre_version'], PCRE_version, ">=") ? 0 : 2, $test['pcre_version']);
			$check['mbstring'] = check_param(function_exists('mb_internal_encoding') ? 0 : 2, $test['mbstring']);
			$check['json'] = check_param(function_exists('json_encode') ? 0 : 2, $test['json']);
			$check['simplexml'] = check_param(function_exists('simplexml_load_string') ? 0 : 2, $test['simplexml']);
			$check['iconv'] = check_param(function_exists('iconv') ? 0 : 2, $test['iconv']);
			$check['xslt'] = check_param(($test['xslt'] == XSLT) ? 0 : 2, $test['xslt']);
			$check['data_limit'] = check_param(($test['data_limit'] != $AVE_Template->get_config_vars('mess_undefined') && version_compare($test['data_limit'], Data_limit, ">=")) ? 0 : 2, $test['data_limit']);
			$check['time_limit'] = check_param(($test['time_limit'] != $AVE_Template->get_config_vars('mess_undefined') && $test['time_limit'] >= TIME_limit) ? 0 : 2, ($test['time_limit'] != $AVE_Template->get_config_vars('mess_undefined')) ? $test['time_limit'] . " " . $AVE_Template->get_config_vars('seconds') : $t_l);
			$check['disk_space'] = check_param(($test['disk_space'] != $AVE_Template->get_config_vars('mess_undefined') && $test['disk_space'] >= DISC_space) ? 0 : 2, ($test['disk_space'] != $AVE_Template->get_config_vars('mess_undefined')) ? $test['disk_space'].$AVE_Template->get_config_vars('megabytes') : $test['disk_space']);
			$check['memmory_limit'] = check_param(($test['memmory_limit'] != $AVE_Template->get_config_vars('mess_undefined') && convertSizeToBytes($test['memmory_limit']) >= convertSizeToBytes(RAM_space)) ? 0 : (($test['memmory_limit'] != $AVE_Template->get_config_vars('mess_undefined')) ? 2 : 1), $test['memmory_limit']);
			$check['s_m'] = check_param(($test['s_m'] == SAFE_MODE) ? 0 : 1, $test['s_m']);
			$check['r_g'] = check_param(($test['r_g'] == REGISTER_GLOBALS) ? 0 : 1, $test['r_g']);
			$check['m_q'] = check_param(($test['m_q'] == MAGIC_QUOTES_GPC) ? 0 : 1, $test['m_q']);

			$AVE_Template->assign('check', $check);
			$AVE_Template->assign('test', $test);
			$AVE_Template->display('step3.tpl');
			break;

		//Шаг 4
		case '4' :
			if (!empty($_POST['dbname']) && !empty($_POST['dbprefix']))
			{
				$db_connect = check_db_connect($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass'], $_POST['dbname']);

				if (true === $db_connect) {
					$mysql_connect = @mysqli_connect($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass']);
					@mysqli_select_db($mysql_connect, $_POST['dbname']);
				}

				// Очищаем бд (по префиксу)
				if (isset($_REQUEST["dbclean"]) && $_REQUEST["dbclean"] == "1") {
					clean_db($_POST['dbname'], $_POST['dbprefix'], $mysql_connect);
				}

				// Создать новую БД
				if (isset($_REQUEST['dbcreat']))
				{
					$link = check_mysql_connect($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass']);

					if (false === $link)
						$AVE_Template->assign('warning', $AVE_Template->get_config_vars('error_is_link') . mysqli_error());
					else
						$mysqli_connect = @mysqli_connect($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass']);

					if (false === $db_connect)
					{
						@mysqli_query($mysqli_connect, "SET collation_server = 'utf8_general_ci'");
						@mysqli_query($mysqli_connect, "SET character_set_server = 'utf8'");

						$sql = 'CREATE DATABASE ' . $_POST['dbname'];

						if (false === check_mysql_query($mysqli_connect, $sql))
							$AVE_Template->assign('warning', $AVE_Template->get_config_vars('error_is_create') . mysqli_error() . "\n");
					}
				}

				$check_installed = check_installed($_POST['dbprefix']);

				$connect = check_db_connect($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass'], $_POST['dbname']);

				$config = [
					'dbhost' => $_POST['dbhost'],
					'dbuser' => $_POST['dbuser'],
					'dbpass' => $_POST['dbpass'],
					'dbname' => $_POST['dbname'],
					'dbpref' => $_POST['dbpref'],
					'dbport' => null,
					'dbsock' => null
				];

				if (true === $connect && false === $check_installed)
				{
					if (! @is_writeable(BASE_DIR . '/config/db.config.php'))
					{
						$AVE_Template->assign('config_isnt_writeable', 1);
						$AVE_Template->display('error.tpl');
						exit;
					}

					$fp = @fopen(BASE_DIR . '/config/db.config.php', 'w+');

					// Записываем данные для подключения
					@fwrite($fp, "<?php\n"
						. "\t\$config = [\n"
						. "\t\t'dbhost' => \"" . stripslashes(trim($_POST['dbhost']))   . "\",\n"
						. "\t\t'dbuser' => \"" . stripslashes(trim($_POST['dbuser']))   . "\",\n"
						. "\t\t'dbpass' => \"" . stripslashes(trim($_POST['dbpass']))   . "\",\n"
						. "\t\t'dbname' => \"" . stripslashes(trim($_POST['dbname']))   . "\",\n"
						. "\t\t'dbpref' => \"" . stripslashes(trim($_POST['dbprefix'])) . "\",\n"
						. "\t\t'dbport' => null,\n"
						. "\t\t'dbsock' => null\n"
						. "\t];\n"
						. "?>"
					);

					@fclose($fp);

					//-- Если параметры не указаны, прерываем работу
					if (! file_exists(BASE_DIR . '/config/db.config.php') || ! filesize(BASE_DIR . '/config/db.config.php'))
						die('Not writing config file');

					// Класс для работы с БД
					require_once (BASE_DIR . '/class/class.database.php');

					//-- Подключаем конфигурационный файл с параметрами подключения
					require_once (BASE_DIR . '/config/db.config.php');

					//-- Если не существует объекта по работе с БД
					if (! isset($AVE_DB))
					{
						//-- Если параметры не указаны, прерываем работу
						if (! isset($config) || empty($config))
							die('No config data');

						//-- Если константа префикса таблиц не задана, принудительно определяем ее на основании параметров в файле db.config.php
						if (! defined('PREFIX'))
							define('PREFIX', $config['dbpref']);

						//-- Создаем объект для работы с БД
						try {
							$AVE_DB = AVE_DB::getInstance($config)
								//-- Назначаем кодировку
								->setCharset('utf8')
								//-- Назначаем БД
								->setDatabaseName($config['dbname']);
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

					// Открываем файл структуры БД
					$filename = BASE_DIR . '/install/structure_base.sql';

					$handle = fopen($filename, 'r');
					$db_structure = fread($handle, filesize($filename));

					fclose($handle);

					// Подставляем префикс
					$db_structure = str_replace('%%PRFX%%', $_POST['dbprefix'], $db_structure);

					// Массив запросов
					$ar = explode('#inst#', $db_structure);

					foreach ($ar as $sql)
						if (! empty($sql))
							$AVE_DB->Query($sql);

					$AVE_Template->display('step5.tpl');
					exit;
				}
				elseif (true === $connect && true === $check_installed)
					$AVE_Template->assign('installed', $AVE_Template->get_config_vars('database_installed'));
				else
					$AVE_Template->assign('warning', $AVE_Template->get_config_vars('database_not_connect'));
			}
			else
			{
				$dbpref = make_random_string(5, 'abcdefghijklmnopqrstuvwxyz0123456789');
				$AVE_Template->assign('dbpref', $dbpref);
			}

			$AVE_Template->display('step4.tpl');
			break;

		case '5' :
			$_POST['email'] = chop($_POST['email']);
			$_POST['username'] = chop($_POST['username']);

			$regex_username = '/[^\w-]/';
			$regex_password = '/[^\x20-\xFF]/';
			$regex_email = '/^[\w.-]+@[a-z0-9.-]+\.(?:[a-z]{2}|com|org|net|edu|gov|mil|biz|info|mobi|name|aero|asia|jobs|museum)$/i';

			// Класс для работы с БД
			require_once (BASE_DIR . '/class/class.database.php');

			//-- Если не существует объекта по работе с БД
			if (! isset($AVE_DB))
			{
				//-- Подключаем конфигурационный файл с параметрами подключения
				require_once (BASE_DIR . '/config/db.config.php');

				//-- Если параметры не указаны, прерываем работу
				if (! isset($config))
					exit;

				//-- Если константа префикса таблиц не задана, принудительно определяем ее на основании параметров в файле db.config.php
				if (! defined('PREFIX'))
					define('PREFIX', $config['dbpref']);

				//-- Создаем объект для работы с БД
				try {
					$AVE_DB = AVE_DB::getInstance($config)
						//-- Назначаем кодировку
						->setCharset('utf8')
						//-- Назначаем БД
						->setDatabaseName($config['dbname']);
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
			}

			$errors = array();
			if ($_POST['email'] == '')                                                        array_push($errors, $AVE_Template->get_config_vars('noemail'));
			if (! preg_match($regex_email, $_POST['email']))                                  array_push($errors, $AVE_Template->get_config_vars('email_no_specialchars'));
			if (empty($_POST['pass']) || preg_match($regex_password, $_POST['pass']))         array_push($errors, $AVE_Template->get_config_vars('check_pass'));
			if (strlen($_POST['pass']) < 5)                                                   array_push($errors, $AVE_Template->get_config_vars('pass_too_small'));
			if (empty($_POST['username']) || preg_match($regex_username, $_POST['username'])) array_push($errors, $AVE_Template->get_config_vars('check_username'));

			$AVE_Template->assign('errors', $errors);

			if (true === $db_connect && ! sizeof($errors))
			{
				if (isset($_POST['demo']) && 1 == $_POST['demo'])
					$filename = BASE_DIR . '/install/data_demo.sql';
				else
					$filename = BASE_DIR . '/install/data_base.sql';

				$handle = fopen($filename, 'r');
				$dbin = fread($handle, filesize($filename));
				fclose($handle);

				$salt = make_random_string();
				$hash = md5(md5($_POST['pass'] . $salt));

				$dbin = str_replace('%%SITENAME%%',   $ver,                $dbin);
				$dbin = str_replace('%%PRFX%%',       $config['dbpref'],   $dbin);
				$dbin = str_replace('%%EMAIL%%',      $_POST['email'],     $dbin);
				$dbin = str_replace('%%SALT%%',       $salt,               $dbin);
				$dbin = str_replace('%%PASS%%',       $hash,               $dbin);
				$dbin = str_replace('%%TIME%%',       time(),              $dbin);
				$dbin = str_replace('%%FIRSTNAME%%',  $_POST['firstname'], $dbin);
				$dbin = str_replace('%%LASTNAME%%',   $_POST['lastname'],  $dbin);
				$dbin = str_replace('%%USERNAME%%',   $_POST['username'],  $dbin);

				$ar = explode('#inst#', $dbin);

				foreach ($ar as $sql)
					if (! empty($sql))
						$AVE_DB->Query($sql);

				$AVE_Template->display('step6.tpl');

				exit;
			}

			$AVE_Template->display('step5.tpl');
			break;
	}
?>