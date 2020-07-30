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
	 * Вставляем файл с пользовательскими функциями
	 */
	if (file_exists(BASE_DIR . '/functions/func.custom.php'))
		include (BASE_DIR . '/functions/func.custom.php');


	/**
	 * Функция загрузки файлов с удаленного сервера через CURL
	 * как альтернатива для file_get_conents
	 *
	 * @param $sourceFileName
	 *
	 * @return mixed
	 */
	function CURL_file_get_contents($sourceFileName)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $sourceFileName);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$st = curl_exec($ch);
		curl_close($ch);

		return ($st);
	}


	/**
	 * Ищет по шаблону в указанном месте пути всех директорий, поддиректорий и файлов, находящихся в них
	 *
	 * @param string     $path    - путь к директории
	 * @param string     $pattern - шаблон поиска
	 * @param int        $flags - флаги для функции glob()
	 * @param int        $depth   - глубина вложенности, просматриваемая функцией. -1 - без ограничений.
	 * @return array - найденные пути
	 */
	function bfglob($path, $pattern = '*', $flags = GLOB_NOSORT, $depth = 0)
	{
		$matches = array();
		$folders = array(rtrim($path, '/'));

		while($folder = array_shift($folders))
		{
			$matches = array_merge($matches, glob($folder.'/'.$pattern, $flags));

			if($depth != 0)
			{
				$moreFolders = glob($folder.'/'.'*', GLOB_ONLYDIR);
				$depth   = ($depth < -1) ? -1: $depth + count($moreFolders) - 2;
				$folders = array_merge($folders, $moreFolders);
			}
		}

		return $matches;
	}


	/**
	 * Рекурсивно чистит директорию
	 *
	 * @param string $dir директория
	 * @param int $result
	 *
	 * @return bool
	 */
	function rrmdir($dir, &$result = 0)
	{
		if (is_dir($dir))
		{
			$objects = scandir($dir);

			foreach ($objects as $object)
			{
				if ($object != '.' && $object != '..')
				{
					if (filetype($dir . '/' . $object) == 'dir')
						rrmdir($dir . '/' . $object, $result);
					else
						$result = $result + (unlink($dir . '/' . $object) ? 0 : 1);
				}
			}

			reset($objects);

			$result = $result + (rmdir($dir) ? 0 : 1);
		}

		return $result > 0 ? false : true;
	}


	/**
	 * Очистка текста от програмного кода
	 *
	 * @param string $text исходный текст
	 * @return string очищенный текст
	 */
	function clean_php($text)
	{
		return str_replace(array('<?php', '<?', '?>', '<script'), '', $text);
	}


	/**
	 * Очистка текста от непечатабельных символов
	 *
	 * @param string $text исходный текст
	 * @return string очищенный текст
	 */
	function clean_no_print_char($text)
	{
		return trim(preg_replace('#[\x00-\x08\x0B-\x0C\x0E-\x1F]+#is', ' ', $text));
	}


	/**
	 * Возвращает исполненный php код в переменную
	 *
	 * @param $expression
	 * @internal param int $id идентификатор запроса
	 * @return string
	 */
	if (! function_exists("eval2var"))
	{
		function eval2var($expression)
		{
			global $AVE_DB, $AVE_Core, $AVE_Template;

			ob_start();

			eval($expression);

			$content = ob_get_clean();

			return $content;
		}
	}


	/**
	 * Регистронезависимый вариант функции strpos
	 * Возвращает числовую позицию первого вхождения needle в строке haystack.
	 *
	 * @param mixed $haystack проверяемая строка
	 * @param mixed $needle искомая подстрока
	 * @param mixed $offset с какого символа в haystack начинать поиск.
	 * @return int числовая позиция
	 */
	if (! function_exists("stripos"))
	{
		function stripos($haystack, $needle, $offset = 0)
		{
			return strpos(strtoupper($haystack), strtoupper($needle), $offset);
		}
	}


	/**
	 * Форматирование числа
	 *
	 * @param array $param значение и параметры
	 * @return string отформатированное значение
	 */
	function num_format($param)
	{
		if (is_array($param))
			return number_format($param['val'], $param['dec'], $param['after'], $param['thousand']);

		return '';
	}


	/**
	 * Проверка начинается ли строка с указанной подстроки
	 *
	 * @param string $str проверяемая строка
	 * @param string $in подстрока
	 * @return boolean результат проверки
	 */
	function start_with($str, $in)
	{
		return(substr($in, 0, strlen($str)) == $str);
	}


	/**
	 * Проверка прав пользователя
	 *
	 * @param string $action проверяемое право
	 * @return boolean результат проверки
	 */
	function check_permission($action)
	{
		global $_SESSION;

		if ((isset($_SESSION['user_group']) && $_SESSION['user_group'] == 1) ||
			(isset($_SESSION['alles'])	  && $_SESSION['alles'] == 1) ||
			(isset($_SESSION[$action])	  && $_SESSION[$action] == 1))
		{
			return true;
		}

		return false;
	}


	/**
	 * Вывод системного сообщения
	 *
	 * @param string $message сообщение
	 */
	function display_notice($message)
	{
		echo '<div class="display_notice"><b>Системное сообщение: </b>' . $message . '</div>';
	}


	/**
	 * Сообщение о запрете распечатки страницы
	 *
	 */
	function print_error()
	{
		display_notice('Запрашиваемая страница не может быть распечатана.');
		exit;
	}


	/**
	 * Сообщение о проблемах доступа к файлам модуля
	 *
	 */
	function module_error()
	{
		display_notice('Запрашиваемый модуль не может быть загружен.');
		exit;
	}


	/**
	 * Получение основных настроек
	 *
	 * @param string $field параметр настройки, если не указан - все параметры
	 * @return mixed
	 */
	function get_settings($field = '')
	{
		global $AVE_DB;

		static $settings = null;

		if ($settings === null)
			$settings = $AVE_DB->Query("
				SELECT
					# SETTINGS
					*
				FROM
					" . PREFIX . "_settings
			", -1, 'settings', true, '.settings')->FetchAssocArray();

		if ($field == '')
			return $settings;

		return isset($settings[$field])
			? $settings[$field]
			: null;
	}


	/**
	 * Формирование URL редиректа
	 *
	 * @param string|mixed $exclude
	 * @return string URL
	 */
	function get_redirect_link($exclude = '')
	{
		global $AVE_Core;

		$link = 'index.php';

		if (! empty($_GET))
		{
			if ($exclude != '' && ! is_array($exclude))
				$exclude = explode(',', $exclude);

			if (empty($exclude))
				$exclude = array();

			$exclude[] = 'url';

			$params = array();

			foreach($_GET as $key => $value)
			{
				if (! in_array($key, $exclude))
				{
					if ($key == 'doc')
					{
						$params[] = 'doc=' . (empty($AVE_Core->curentdoc->document_alias) ? prepare_url($AVE_Core->curentdoc->document_title) : $AVE_Core->curentdoc->document_alias);
					}
					else
					{
						if (! is_array($value))
						{
							$params[] = @urlencode($key) . '=' . @urlencode($value);
						}
						else
						{
							foreach($value AS $k => $v)
							{
								$params[] = @urlencode($k) . '=' . @urlencode($v);
							}
						}
					}
				}
			}

			if (sizeof($params))
				$link .= '?' . implode('&', $params);
		}

		return $link;
	}

	/**
	 * Ссылка на главную страницу
	 *
	 * @return string ссылка
	 */
	function get_home_link()
	{
		return HOST . ABS_PATH . ($_SESSION['user_language'] == DEFAULT_LANGUAGE
			? ''
			: $_SESSION['accept_langs'][$_SESSION['user_language']] . URL_SUFF);
	}


	/**
	 * Ссылка на страницу версии для печати
	 *
	 * @return string ссылка
	 */
	function get_print_link()
	{
		/*
		$link = get_redirect_link('print');
		$link .= (strpos($link, '?')===false ? '?print=1' : '&amp;print=1');
		*/
		/* Временное решение */
		$link = ABS_PATH."index.php?id=".get_current_document_id()."&print=1";

		return $link;
	}


	/**
	 *
	 *
	 * @return string ссылка
	 */
	function get_referer_link()
	{
		static $link = null;

		if ($link === null)
		{
			if (isset($_SERVER['HTTP_REFERER']))
			{
				$link = parse_url($_SERVER['HTTP_REFERER']);
				$link = (trim($link['host']) == $_SERVER['SERVER_NAME']);
			}
			$link = ($link === true ? $_SERVER['HTTP_REFERER'] : get_home_link());
		}

		return $link;
	}


	/**
	 * Замена некоторых символов на их сущности
	 * замена и исправление HTML-тегов
	 *
	 * @param string|mixed $string
	 * @return string|mixed
	 */
	function pretty_chars($string)
	{
		return preg_replace(array("'©'"   , "'®'"),
							array('&copy;', '&reg;'), $string);
	}


	/**
	 * Подготовка URL
	 *
	 * @param string $url
	 * @return string
	 */
	function prepare_url($url)
	{
		$new_url = strip_tags($url);

		// спецсимволы
		$table = array(
			'«' => '',
			'»' => '',
			'—' => '',
			'–' => '',
			'“' => '',
			'”' => ''
		);

		$new_url = str_replace(array_keys($table), array_values($table), $new_url);

		if (defined('TRANSLIT_URL') && TRANSLIT_URL)
			$new_url = translit_string(trim(_strtolower($new_url)));

		$new_url = preg_replace(
			array(
				'/^[\/-]+|[\/-]+$|^[\/_]+|[\/_]+$|[^\.a-zа-яеёA-ZА-ЯЕЁ0-9\/_-]/u',
				'/--+/',
				'/-*\/+-*/',
				'/\/\/+/'
			),
			array(
				'-',
				'-',
				'/',
				'/'
			),
			$new_url
		);

		$new_url = trim($new_url, '-');

		if (substr(URL_SUFF, 0, 1) != '/' && substr($url, -1) == '/')
			$new_url = $new_url . "/";

		return mb_strtolower(rtrim($new_url, '.'), 'UTF-8');
	}


	/**
	 * Формирование ЧПУ для документов
	 *
	 * @param string $s ссылка или текст с ссылками
	 * @return string
	 */
	function rewrite_link($s)
	{
		if (!REWRITE_MODE)
			return $s;

		$doc_regex = '/index.php(?:\?)id=(?:[0-9]+)&(?:amp;)*doc='.(TRANSLIT_URL ? '([\.a-z0-9\/_-]+)' : '([\.a-zа-яёїєі0-9\/_-]+)');
		$page_regex = '&(?:amp;)*(artpage|apage|page)=([{s}0-9]+)';

		$s = preg_replace($doc_regex.$page_regex.$page_regex.$page_regex.'/', ABS_PATH.'$1/$2-$3/$4-$5/$6-$7'.URL_SUFF, $s);
		$s = preg_replace($doc_regex.$page_regex.$page_regex.'/',			 ABS_PATH.'$1/$2-$3/$4-$5'.URL_SUFF, $s);
		$s = preg_replace($doc_regex.$page_regex.'/',						 ABS_PATH.'$1/$2-$3'.URL_SUFF, $s);
		$s = preg_replace($doc_regex.'/',									 ABS_PATH.'$1'.URL_SUFF, $s);
		//$s = preg_replace('/'.preg_quote(URL_SUFF, '/').'[?|&](?:amp;)*print=1/', '/print'.URL_SUFF, $s);

		return $s;
	}


	/**
	 * Возвращаем полный домен сайта
	 *
	 * @return mixed|string
	 */
	function getSiteUrl()
	{
		$protocol = isset($_SERVER['HTTPS'])
			? 'https'
			: 'http';

		$url = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		$url = parse_url($url);

		$url = $url['scheme'] . '://' . $url['host'];

		return $url;
	}


	/**
	 * Преобразует первый символ в верхний регистр
	 * @param string $str - строка
	 * @param string $encoding - кодировка, по-умолчанию UTF-8
	 * @return string
	 */
	function ucfirst_utf8($str, $encoding='utf-8')
	{
		$str = mb_ereg_replace('^[\ ]+', '', $str);
		$str = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding) . mb_substr($str, 1, mb_strlen($str), $encoding);
		return $str;
	}


	/**
	 * Вывод статистики
	 *
	 * @param int $t
	 * @param int $m
	 * @param int $q
	 * @param int $l
	 *
	 * @return string
	 */
	function get_statistic($t=0, $m=0, $q=0, $l=0)
	{
		global $AVE_DB;

		$s = '';

		if ($t)
			$s .= "\n<br>Время генерации: " . number_format(microtime_diff(START_MICROTIME, microtime()), 3, ',', ' ') . ' сек.';

		if ($m && function_exists('memory_get_peak_usage'))
			$s .= "\n<br>Использовано памяти: " . Debug::formatSize(memory_get_usage() - START_MEMORY);

		if ($m && function_exists('memory_get_peak_usage'))
			$s .= "\n<br>Пиковое значение: " . Debug::formatSize(memory_get_peak_usage());

		if ($q && (defined('SQL_PROFILING') && SQL_PROFILING))
			$s .= "\n<br>Количество запросов: " . $AVE_DB->DBProfilesGet('count') . ' шт. за ' . $AVE_DB->DBProfilesGet('time') . ' сек.';

		if ($l && (defined('SQL_PROFILING') && SQL_PROFILING))
			$s .= $AVE_DB->DBProfilesGet('list');

		return $s;
	}


	/**
	 * Комментарии в SMARTY
	 *
	 * @param $tpl_source
	 * @param $smarty
	 *
	 * @return string
	 */
	function add_template_comment($tpl_source, &$smarty)
	{
		return "\n\n<!-- BEGIN SMARTY TEMPLATE " . $smarty->_current_file . " -->\n".$tpl_source."\n<!-- END SMARTY TEMPLATE " . $smarty->_current_file . " -->\n\n";
	}


	/**
	 * Получения списка стран
	 *
	 * @param int $status статус стран входящих в список
	 * <ul>
	 *      <li>1 - активные страны</li>
	 *      <li>0 - неактивные страны</li>
	 * </ul>
	 * если не указано возвращает список стран без учета статуса
	 *
	 * @return array
	 */
	function get_country_list($status = null)
	{
		global $AVE_DB;

		$countries = array();
		$sql = $AVE_DB->Query("
			SELECT
				country_code,
				country_name,
				country_status
			FROM " . PREFIX . "_countries
			" . (($status != '') ? "WHERE country_status = '" . $status . "'" : '') . "
			ORDER BY country_name ASC
		");
		while ($row = $sql->FetchRow()) array_push($countries, $row);

		return $countries;
	}


	/**
	 * Получение списка изображений из заданной папки
	 * @param string $path путь до директории с изображениями
	 * @return array
	 */
	function image_multi_import($path)
	{
		$images_ext = array('jpg', 'jpeg', 'png', 'gif');

		$dir = BASE_DIR . "/" . $path;

		$dir_abs = "/" . $path;

		$files = array();
		$thumbs = array();

		if ($handle = opendir($dir))
		{
			while (false !== ($file = readdir($handle)))
			{
				$nameParts = explode('.', $file);
				$ext = strtolower(end($nameParts));

				if ($file != "." && $file != ".." && in_array($ext, $images_ext))
				{
					if (! is_dir($dir . "/" . $file))
					{
						$files[] = $file;
						$thumbs[] = make_thumbnail(array('link' => $dir_abs . $file, 'size' => 'f128x128'));
					}
				}
			}
			closedir($handle);
		}

		//$return = array(
		//	'files' => $files,
		//	'thumbs' => $thumbs
		//);

		return $files;
	}


	/**
	 * Получение списка файлов из заданной папки
	 * @param string $path путь до директории с файлами
	 * @return array
	 */
	function file_multi_import($path)
	{
		$dir = BASE_DIR . "/" . $path;

		$files = array();

		if ($handle = opendir($dir))
		{
			while (false !== ($file = readdir($handle)))
			{
				$nameParts = explode('.', $file);
				$ext = strtolower(end($nameParts));

				if ($file != "." && $file != ".." && $ext == "php" || $ext == "inc")
				{
					if (! is_dir($dir . "/" . $file))
						$files[] = $file;
				}
			}
			closedir($handle);
		}

		return $files;
	}


	/**
	 * Replace PHP_EOL constant
	 *
	 * @category	PHP
	 * @package	 PHP_Compat
	 * @license	 LGPL - http://www.gnu.org/licenses/lgpl.html
	 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
	 * @link		http://php.net/reserved.constants.core
	 * @author	  Aidan Lister <aidan@php.net>
	 * @version	 $Revision: 1.3 $
	 * @since	   PHP 5.0.2
	 */
	if (! defined('PHP_EOL'))
	{
		switch (strtoupper(substr(PHP_OS, 0, 3)))
		{
			// Windows
			case 'WIN':
				define('PHP_EOL', "\r\n");
				break;

			// Mac
			case 'DAR':
				define('PHP_EOL', "\r");
				break;

			// Unix
			default:
				define('PHP_EOL', "\n");
		}
	}


	/**
	 * Функция записывает в указанную папку .htaccess с содержанием "Deny from all"
	 *
	 * @param $dir
	 */
	function write_htaccess_deny($dir)
	{
		$file = $dir . '/.htaccess';

		if (! file_exists($file))
		{
			if (! is_dir($dir))
				@mkdir($dir);

			@file_put_contents($dir . '/.htaccess','Deny from all');
		}
	}


	/**
	 * Функция которая паникует если приблизились к memory_limit
	 *
	 * @return bool превышение лимита использования памяти
	 */
	 function memory_panic()
	 {
		if (defined('MEMORY_LIMIT_PANIC') && MEMORY_LIMIT_PANIC != -1)
		{
			$use_mem = memory_get_usage();
			$lim = MEMORY_LIMIT_PANIC * 1024 * 1024;
			return ($use_mem > $lim ? true : false);
		}
		else
			return false;
	}


	/**
	 * Первод Array в Object
	 *
	 * @param array $array
	 * @return array obj
	 */
	function array2object($array)
	{
		if (is_array($array))
		{
			$obj = new StdClass();
			foreach ($array as $key => $val)
			{
				$obj->$key = $val;
			}
		}
		else
			$obj = $array;

		return $obj;
	}


	/**
	 * Первод Object в Array
	 *
	 * @param array $object
	 * @return array
	 */
	function object2array($object)
	{
		$object = (array)$object;

		if ($object === array())
			return $object;

		foreach($object as $key => &$value)
		{
			if ((is_object($value) || is_array($value)))
			{
				$object[$key] = object2array($value);
			}
		}

		return $object;
	}


	/**
	 * Sort a 2 dimensional array based on 1 or more indexes.
	 *
	 * msort() can be used to sort a rowset like array on one or more
	 * 'headers' (keys in the 2th array).
	 *
	 * @param array        $array      The array to sort.
	 * @param string|array $key        The index(es) to sort the array on.
	 * @param int          $sort_flags The optional parameter to modify the sorting
	 * @param int          $sort_way   The optional parameter to modify the sorting as DESC or ASC
	 *                                 behavior. This parameter does not work when
	 *                                 supplying an array in the $key parameter.
	 *
	 * @return array The sorted array.
    */
	function msort($array, $key, $sort_flags = SORT_REGULAR, $sort_way = SORT_ASC)
	{
		if (is_array($array) && count($array) > 0)
		{
			if (! empty($key))
			{
				$mapping = array();

				foreach ($array as $k => $v)
				{
					$sort_key = '';

					if ( !is_array($key))
					{
						$sort_key = $v[$key];
					}
					else
						{
							// @TODO This should be fixed, now it will be sorted as string
							foreach ($key as $key_key) {
								$sort_key .= $v[$key_key];
						}

						$sort_flags = SORT_STRING;
					}

					$mapping[$k] = $sort_key;
				}

				switch ($sort_way)
				{
					case SORT_ASC:
						asort($mapping, $sort_flags);
					break;

					case SORT_DESC:
						arsort($mapping, $sort_flags);
					break;
				}

				$sorted = array();

				foreach ($mapping as $k => $v)
				{
					$sorted[] = $array[$k];
				}

				return $sorted;
			}
		}
		return $array;
	}


	/**
	 * Функция возвращает каноническое имя страницы
	 *
	 * @param string $url текущий УРЛ
	 * @return string
	 */
	function canonical($url)
	{
		$link = preg_replace('/^(.+?)(\?.*?)?(#.*)?$/', '$1$3', $url);
		return $link;
	}


	/**
	 * Функция поиска автора документа, для autocmplite
	 *
	 * @param string $string
	 * @param        $limit
	 */
	function findautor($string, $limit)
	{
		global $AVE_DB;

		$search = "
			AND (UPPER(email) LIKE UPPER('%" . $string . "%')
			OR UPPER(email) = UPPER('" . $string . "')
			OR Id = '" . intval($string) . "'
			OR UPPER(user_name) LIKE UPPER('" . $string . "%')
			OR UPPER(firstname) LIKE UPPER('" . $string . "%')
			OR UPPER(lastname) LIKE UPPER('" . $string . "%'))
		";

		$limit = (!empty($limit)) ? 'LIMIT 0,'.$limit : '';

		$sql = $AVE_DB->Query("
			SELECT *
			FROM " . PREFIX . "_users
			WHERE 1"
			. $search
			. $limit
		);

		$users = array();

		while ($row = $sql->FetchRow())
		{
			$ava=getAvatar($row->Id,40);
			$users[]=array(
				'userid'=>$row->Id,
				'login'=>$row->user_name,
				'email'=>$row->email,
				'lastname'=>$row->lastname,
				'firstname'=>$row->firstname,
				'avatar'=>($ava ? $ava : ABS_PATH.'admin/templates/images/user.png')
			);
		}

		echo json_encode($users);
	}


	/**
	 * Функция поиска ключевых слов
	 *
	 * @param string $string - запрос
	 * @return string
	 */
	function searchKeywords($string)
	{
		global $AVE_DB;

		$search = "
			AND (UPPER(keyword) LIKE UPPER('" . $string . "%'))
		";

		$sql = $AVE_DB->Query("
			SELECT *
			FROM " . PREFIX . "_document_keywords
			WHERE 1"
			. $search
		);

		while ($row = $sql->FetchRow())
		{
			$keyword = $row->keyword;
			echo "$keyword\n";
		}
	}


	/**
	 * Функция поиска тегов
	 *
	 * @return string
	 */
	function searchTags()
	{
		global $AVE_DB;

		$sql = $AVE_DB->Query("
			SELECT DISTINCT
				tag
			FROM
				" . PREFIX . "_document_tags
		");

		$tags = array();

		//$ii = 0;

		while ($row = $sql->GetCell())
			$tags[]['value'] = $row;

		echo json_encode($tags);
		exit;
	}


	/**
	 * Формирование строки из случайных символов
	 *
	 * @param int $length количество символов в строке
	 * @param string $chars набор символов для формирования строки
	 * @return string сформированная строка
	 */
	function make_random_string($length = 16, $chars = '')
	{
		if ($chars == '')
		{
			$chars  = 'abcdefghijklmnopqrstuvwxyz';
			$chars .= 'ABCDEFGHIJKLMNOPRQSTUVWXYZ';
			$chars .= '~!@#$%^&*()-_=+{[;:/?.,]}';
			$chars .= '0123456789';
		}

		$clen = strlen($chars) - 1;

		$string = '';
		while (strlen($string) < $length) $string .= $chars[mt_rand(0, $clen)];

		return $string;
	}


	/**
	 * Функция preg_replace для кириллицы
	 * если заменять русские символы в строке UTF-8 при помощи preg_replace, то появляются вопросы
	 *
	 * @param mixed $pattern	 шаблон заменяемой части строки
	 * @param mixed $replacement на что заменяем
	 * @param mixed $string	  входящая строка
	 * @param int   $limit	   максимум вхождений
	 * mixed preg_replace_ru ( mixed pattern, mixed replacement, mixed subject [, int limit] )
	 *
	 * @return mixed
	 */
	function preg_replace_ru($pattern="", $replacement="", $string="", $limit=-1)
	{
		$string = iconv('UTF-8', 'cp1251', $string);
		$string = preg_replace($pattern, $replacement, $string, $limit);
		return iconv('cp1251', 'UTF-8', $string);
	}


	/**
	 * Создание cookie
	 *
	 * @param string $cookie_domain
	 */
	function set_cookie_domain($cookie_domain = '')
	{
		global $cookie_domain;

		if ($cookie_domain == '' && defined('COOKIE_DOMAIN') && COOKIE_DOMAIN != '')
		{
			$cookie_domain = COOKIE_DOMAIN;
		}
		elseif ($cookie_domain == '' && !empty($_SERVER['HTTP_HOST']))
		{
			$cookie_domain = htmlspecialchars($_SERVER['HTTP_HOST'], ENT_QUOTES);
		}

		// Удаляем ведущие www. и номер порта в имени домена для использования в cookie.
		$cookie_domain = ltrim($cookie_domain, '.');
		if (strpos($cookie_domain, 'www.') === 0)
		{
			$cookie_domain = substr($cookie_domain, 4);
		}
		$cookie_domain = explode(':', $cookie_domain);
		$cookie_domain = '.'. $cookie_domain[0];

		// В соответствии с RFC 2109, имя домена для cookie должно быть второго или более уровня.
		// Для хостов 'localhost' или указанных IP-адресом имя домена для cookie не устанавливается.
		if (count(explode('.', $cookie_domain)) > 2 && !is_numeric(str_replace('.', '', $cookie_domain)))
		{
			ini_set('session.cookie_domain', $cookie_domain);
		}

		ini_set('session.cookie_path', ABS_PATH);
	}


	/**
	 * Функция проверяет наличие Ajax запроса
	 *
	 * @return bool
	 */
	function isAjax()
	{
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'));
	}


	/**
	 * Функция делает html в 1 строчку, удаляет лишние пробелы, комментарии и т.д.
	 *
	 * @param $data
	 *
	 * @return string
	 */
	function compress_htlm($data)
	{
		$search = array(
			'/\>[^\S ]+/s',		// strip whitespaces after tags, except space
			'/[^\S ]+\</s',		// strip whitespaces before tags, except space
			'/(\s)+/s',			// shorten multiple whitespace sequences
			'/<!--(.|\s)*?-->/' // Remove HTML comments
		);

		$replace = array(
			'>',
			'<',
			'\\1',
			''
		);

		$data = preg_replace($search, $replace, $data);

		return $data;
	}


	/**
	 * Функция делает компрессию данных
	 *
	 * @param $data
	 *
	 */
	function output_compress($data)
	{
		global $AVE_DB;

		$Gzip = strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false;

		if (defined('HTML_COMPRESSION') && HTML_COMPRESSION)
			$data = compress_htlm($data);

		if (isset($_REQUEST['sysblock']))
			define('ONLYCONTENT', true);

		//-- Вывод статистики загрузки и запросов SQL (только для администраторов)
		if (! defined('ONLYCONTENT') && UGROUP == 1)
		{
			if (defined('PROFILING') && PROFILING == 'light')
			{
				$data .= "\r\n" . "<!-- ------ Time generation: ".Debug::getStatistic('time')." sec ----- -->";
				$data .= "\r\n" . "<!-- ------ Memory usage: ".Debug::getStatistic('memory')." ----- -->";
				$data .= "\r\n" . "<!-- ------ Memory peak usage: ".Debug::getStatistic('peak')." ----- -->";

				if (defined('SQL_PROFILING') && SQL_PROFILING)
					$data .= "\r\n" . "<!-- ------ SQL Queries: ".$AVE_DB->DBProfilesGet('count')." for ".$AVE_DB->DBProfilesGet('time')." sec ----- -->";
			}
			else if (defined('PROFILING') && PROFILING == 'full')
				$data .= Debug::displayInfo();
		}

		if ($Gzip && (defined('GZIP_COMPRESSION') && GZIP_COMPRESSION))
		{
			$data = gzencode($data, 9);
			header ('Content-Encoding: gzip');
		}

		header ('X-Engine: AVE.cms');
		header ('X-Engine-Copyright: 2007-' . date('Y') . ' (c) AVE.cms');
		header ('X-Engine-Site: https://www.ave-cms.ru');

		header ('Content-Type: text/html; charset=utf-8');
		header ('Cache-Control: must-revalidate');
		if (defined('OUTPUT_EXPIRE') && OUTPUT_EXPIRE)
		{
			$expire = 'Expires: ' . gmdate ("D, d M Y H:i:s", time() + OUTPUT_EXPIRE_OFFSET) . ' GMT';
			header ($expire);
		}
		header ('Content-Length: ' . strlen($data));
		header ('Vary: Accept-Encoding');

		echo $data;
	}


	/**
	 * Функция создает короткий URL документа для редиректа
	 * После выполения функции нужно очистить кеш данного документа
	 *
	 * @param int $length
	 * @param     $doc_id
	 *
	 * @return bool
	 */
	function gen_short_link ($length, $doc_id)
	{
		global $AVE_DB;

		if (! is_numeric($doc_id))
			return false;

		if (! $length)
			$length = 1;

		// Проврека на существование редиректа для данного документа
		$check_doc = $AVE_DB->Query("
			SELECT
				id
			FROM
				" . PREFIX . "_document_alias_history
			WHERE
				document_id = '" . $doc_id . "'
		")->GetCell();

		// Если редирект отсутствует
		if (! $check_doc)
		{
			$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

			$short_link = '';

			for ($i = 0; $i < $length; $i++)
				$short_link .= $characters[rand(0, strlen($characters) - 1)];

			// Проеряем есть такое редирект уже
			$exists = $AVE_DB->Query("
				SELECT
					id
				FROM
					" . PREFIX . "_document_alias_history
				WHERE
					document_alias = '" . $short_link . "'
			")->GetCell();

			// Если есть, повторяем генерацию
			if ($exists)
			{
				gen_short_link($length, $doc_id);
			}
			// Иначе заносим в БД
			else
				{
					$AVE_DB->Query("
						INSERT INTO
							" . PREFIX . "_document_alias_history
						SET
							document_id				= '" . $doc_id . "',
							document_alias			= '" . $short_link . "',
							document_alias_author	= '" . $_SESSION['user_id'] . "',
							document_alias_changed	= '" . time() . "'
					");

					$AVE_DB->Query("
						UPDATE
							" . PREFIX . "_documents
						SET
							document_short_alias	= '" . $short_link . "'
						WHERE
							Id = '" . $doc_id . "'
					");
				}
		}

		return true;
	}


	/**
	 * Функция возвращает данные в формате JSON
	 *
	 * @param $data
	 * @param bool $exit
	 */
	function _json ($data, $exit = false)
	{
		header("Content-Type: application/json;charset=utf-8");

		$json = json_encode($data);

		if ($json === false)
		{
			$json = json_encode(array("jsonError", json_last_error_msg()));

			if ($json === false)
			{
				$json = '{"jsonError": "unknown"}';
			}

			http_response_code(500);
		}

		echo $json;

		if ($exit)
			exit;
	}


	/**
	 * _base64_encode()
	 *
	 * @param string $input
	 * @return
	 */
	function _base64_encode($input)
	{
		return strtr(base64_encode($input), '+/=', '-_,');
	}


	/**
	 * _base64_decode()
	 *
	 * @param string $input
	 * @return
	 */
	function _base64_decode($input)
	{
		return base64_decode(strtr($input, '-_,', '+/='));
	}


	/**
	 * Функция принимает строку, и возвращает
	 * адрес первого изображения, которую найдет
	 *
	 * @param $data
	 *
	 * @return string
	 */
	function getImgSrc($data)
	{
		$_req_exp = '/(<img )(.+?)( \/)?(>)/u';

		preg_match_all($_req_exp, $data, $images);

		$host = $images[2][0];

		if (preg_match("/(src=)('|\")(.+?)('|\")/u", $host, $matches) == 1)
			$host = $matches[3];

		preg_match('@/index\.php\?.*thumb=(.*?)\&@i', $host, $matches);

		if (isset($matches[1]))
		{
			return $matches[1];
		}
		else
		{
			preg_match('/(.+)' . THUMBNAIL_DIR . '\/(.+)-.\d+x\d+(\..+)/u', $host, $matches);

			if (isset($matches[1]))
				return $matches[1] . $matches[2] . $matches[3];
			else
				return $host;
		}
	}


	function getIp ()
	{
		$ip = false;
		$ipa = array();

		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipa[] = trim(strtok($_SERVER['HTTP_X_FORWARDED_FOR'], ','));

		if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ipa[] = $_SERVER['HTTP_CLIENT_IP'];

		if (isset($_SERVER['REMOTE_ADDR']))
			$ipa[] = $_SERVER['REMOTE_ADDR'];

		if (isset($_SERVER['HTTP_X_REAL_IP']))
			$ipa[] = $_SERVER['HTTP_X_REAL_IP'];

		//-- Проверяем ip-адреса на валидность начиная с приоритетного.
		foreach ($ipa as $ips)
		{
			//-- Если ip валидный обрываем цикл, назначаем ip адрес и возвращаем его
			if (isValidIp($ips))
			{
				//-- Localhost IP6
				if ($ips == '::1')
					$ips = '127.0.0.1';

				$ip = $ips;
				break;
			}
		}

		return $ip;
	}


	function isValidIp ($ip = null)
	{
		//-- Если ip-адрес попадает под регулярное выражение, возвращаем true
		if (preg_match('#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#', $ip))
			return true;

		//-- Localhost IP6
		if ($ip == '::1')
			return true;

		//-- Иначе возвращаем false
		return false;
	}

	if (! function_exists('getExtension'))
	{
		function getExtension($path)
		{
			return strtolower(substr(strrchr($path, "."), 1));
		}
	}
?>