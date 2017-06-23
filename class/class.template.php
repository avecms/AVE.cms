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
 * Подключаем файл шаблонизатора Smarty
 */
require(BASE_DIR . '/lib/Smarty/Smarty.class.php');

/**
 * Расширение класса шаблонизатора Smarty
 *
 */
class AVE_Template extends Smarty
{
/**
 *	СВОЙСТВА
 */

	/**
	 * Конструктор
	 *
	 * @param string $template_dir путь к директории шаблонов по умолчанию
	 * @return AVE_Template
	 */
	function __construct($template_dir)
	{
		/**
		 * Путь к директории шаблонов по умолчанию.
		 * Если вы не передадите тип ресурса во время подключения файлов, они будут искаться здесь.
		 */
		$this->template_dir = $template_dir;

		/**
		 * Имя каталога, в котором хранятся компилированные шаблоны.
		 */
		$this->compile_dir = BASE_DIR . '/cache/smarty';

		/**
		 * Имя каталога, в котором хранится кэш.
		 */
		$this->cache_dir_root = BASE_DIR . '/cache';

		/**
		 * Имя каталога, в котором хранится кэш шаблонов.
		 */
		$this->cache_dir = BASE_DIR . '/cache/tpl';

		/**
		 * Имя каталога, в котором хранится кэш модулей.
		 */
		$this->module_cache_dir = BASE_DIR . '/cache/module';

		/**
		 * Имя каталога, в котором хранится сессии пользователей.
		 */
		$this->session_dir = BASE_DIR . '/session';

		/**
		 * Имя каталога, в котором хранится сессии пользователей.
		 */
		$this->sql_cache_dir = BASE_DIR . '/cache/sql';

		/**
		 * Использование поддиректорий для хранения кэша и скомпилированных шаблонов.
		 */
		$this->use_sub_dirs = SMARTY_USE_SUB_DIRS;

		/**
		 * При каждом вызове РНР-приложения Smarty проверяет, изменился или нет текущий шаблон
		 * с момента последней компиляции. Если шаблон изменился, он перекомпилируется.
		 * В случае, если шаблон еще не был скомпилирован, его компиляция производится
		 * с игнорированием значения этого параметра.
		 */
		$this->compile_check = SMARTY_COMPILE_CHECK;

		/**
		 * Активирует debugging console - порожденное при помощи javascript окно браузера,
		 * содержащее информацию о подключенных шаблонах и загруженных переменных для текущей страницы.
		 */
		$this->debugging = SMARTY_DEBUGGING;

		/**
		 * Регистрация плагинов-функций Smarty.
		 * Передается наименование функции шаблона и имя функции, реализующей ее.
		 */
		$this->register_function('check_permission', 'check_permission');
		$this->register_function('get_home_link', 'get_home_link');
		$this->register_function('num_format', 'num_format');
		$this->register_function('thumb', 'make_thumbnail');

		/**
		 * Регистрация плагинов-модификаторов Smarty.
		 * Передается имя модификатора и имя функции, реализующей его.
		 */
		$this->register_modifier('pretty_date', 'pretty_date');
		$this->register_modifier('translate_date', 'translate_date');
		$this->register_modifier('utf8', 'utf8');

		// плагин позволяющий поставить метки шаблонов
		// для быстрого поиска шаблона отвечающего за вывод
		// перед использованием очистить cache/smarty
		// $this->register_postfilter('add_template_comment');

		/**
		 * Присваиваем общие значения для шаблонов.
		 * Можно явно передавать пары имя/значение,
		 * или ассоциативные массивы, содержащие пары имя/значение.
		 */
		$assign['BASE_DIR']          = BASE_DIR;
		$assign['ABS_PATH']          = ABS_PATH;
		$assign['DATE_FORMAT']       = DATE_FORMAT;
		$assign['TIME_FORMAT']       = TIME_FORMAT;
		$assign['PAGE_NOT_FOUND_ID'] = PAGE_NOT_FOUND_ID;

		$this->assign($assign);
	}

/**
 *	ВНУТРЕННИЕ МЕТОДЫ
 */

	/**
	 * Проверка наличия одноименного шаблона в директории темы дизайна.
	 * При наличии шаблона в директории темы дизайна используется этот шаблон.
	 *
	 * @param string $tpl	путь к шаблону
	 * @return string
	 */
	function _redefine_template($tpl)
	{
		if (!defined('THEME_FOLDER')) return $tpl;

		$r_tpl = str_replace(BASE_DIR, BASE_DIR . '/templates/' . THEME_FOLDER, $tpl);

		return (file_exists($r_tpl) && is_file($r_tpl)) ? $r_tpl : $tpl;
	}

/**
 *	ВНЕШНИЕ МЕТОДЫ
 */

	/**
	 * Переопределение одноименного метода Smarty
	 * для конфигурационных файлов созданных в теме дизайна.
	 *
	 * @param string $file
	 * @param string $section
	 * @param string $scope
	 */
	function config_load($file, $section = null, $scope = 'global')
	{
		Smarty::config_load($this->_redefine_template($file), $section, $scope);
	}

	/**
	 * Переопределение одноименного метода Smarty
	 * для пользовательских шаблонов созданных в теме дизайна.
	 *
	 * @param string $tpl_file name of template file
	 * @param string $cache_id
	 * @param string $compile_id
	 * @return string|false results of {@link _read_cache_file()}
	 */
	function is_cached($tpl_file, $cache_id = null, $compile_id = null)
	{
		return Smarty::is_cached($this->_redefine_template($tpl_file), $cache_id, $compile_id);
	}

	/**
	 * Переопределение одноименного метода Smarty
	 * для пользовательских шаблонов созданных в теме дизайна.
	 *
	 * @param string $resource_name
	 * @param string $cache_id
	 * @param string $compile_id
	 * @param boolean $display
	 */
	function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false)
	{
		return Smarty::fetch($this->_redefine_template($resource_name), $cache_id, $compile_id, $display);
	}

	/**
	 * Переопределение одноименного метода Smarty
	 * для пользовательских шаблонов созданных в теме дизайна.
	 *
	 * @param string $resource_name
	 * @param string $cache_id
	 * @param string $compile_id
	 */
	function display($resource_name, $cache_id = null, $compile_id = null)
	{
		$this->fetch($resource_name, $cache_id, $compile_id, true);
	}

	/**
	 * Метод очистки кэша
	 *
	 */
	function CacheClear()
	{
		global $AVE_DB, $AVE_Template;

		$message = array();

		//Метод очистки кэша
		if (isset($_REQUEST['templateCache']) && $_REQUEST['templateCache'] == '1')
		{
			$this->clear_all_cache();

			foreach (glob($this->cache_dir_root."/cache_*") as $filename)
			{
				@unlink($filename);
			}

			$filename = $this->cache_dir . '/.htaccess';
			if (!file_exists($filename))
			{
				$fp = @fopen($filename, 'w');
				if ($fp)
				{
					fputs($fp, 'Deny from all');
					fclose($fp);
				}
			}

			if($_REQUEST['ajax'] && Memcached_Server && Memcached_Port)
			{
				$memcache = new Memcache;
				$memcache->connect(Memcached_Server, Memcached_Port);
				$memcache->flush();
			}

			// Очищаем кэш шаблона документов рубрики
			$GLOBALS['AVE_DB']->Query("
				DELETE
				FROM
					" . PREFIX . "_rubric_template_cache
			");

			$message[] = $AVE_Template->get_config_vars('TEMPLATES_CACHE_SUCCESS');
			reportLog($AVE_Template->get_config_vars('TEMPLATES_CACHE_SUCCESS_LOG'));

			// Очищаем кэш сессий в БД в таблице _sessions
			$GLOBALS['AVE_DB']->Query("
				DELETE
				FROM
					" . PREFIX . "_sessions
			");

			$message[] = $AVE_Template->get_config_vars('TEMPLATES_CACHE_DB_SUCCESS');
			reportLog($AVE_Template->get_config_vars('TEMPLATES_CACHE_DB_SUCCESS_LOG'));
		}

		//Метод удаления скомпилированных шаблонов
		if (isset($_REQUEST['templateCompiledTemplate']) && $_REQUEST['templateCompiledTemplate'] == '1')
		{
			$this->clear_compiled_tpl();

			$filename = $this->compile_dir . '/.htaccess';
			if (! file_exists($filename))
			{
				$fp = @fopen($filename, 'w');
				if ($fp)
				{
					fputs($fp, 'Deny from all');
					fclose($fp);
				}
			}

			$message[] = $AVE_Template->get_config_vars('TEMPLATES_CACHE_CT_SUCCESS');
			reportLog($AVE_Template->get_config_vars('TEMPLATES_CACHE_CT_SUCCESS_LOG'));
		}

		//Метод удаления скомпилированных шаблонов модулей
		if (isset($_REQUEST['moduleCache']) && $_REQUEST['moduleCache'] == '1')
		{
			rrmdir($this->module_cache_dir);

			mkdir($this->module_cache_dir,0777,true);

			$filename = $this->module_cache_dir . '/.htaccess';

			if (! file_exists($filename))
			{
				$fp = @fopen($filename, 'w');
				if ($fp)
				{
					fputs($fp, 'Deny from all');
					fclose($fp);
				}
			}

			$message[] = $AVE_Template->get_config_vars('TEMPLATES_CACHE_MC_SUCCESS');
			reportLog($AVE_Template->get_config_vars('TEMPLATES_CACHE_MC_SUCCESS_LOG'));
		}

		//Метод удаления всех сессий
		if (isset($_REQUEST['sessionUsers']) && $_REQUEST['sessionUsers'] == '1')
		{
			rrmdir($this->session_dir);

			mkdir($this->session_dir,0777,true);

			$filename = $this->session_dir . '/.htaccess';

			if (! file_exists($filename))
			{
				$fp = @fopen($filename, 'w');
				if ($fp)
				{
					fputs($fp, 'Deny from all');
					fclose($fp);
				}
			}

			$message[] = $AVE_Template->get_config_vars('TEMPLATES_CACHE_SU_SUCCESS');
			reportLog($AVE_Template->get_config_vars('TEMPLATES_CACHE_SU_SUCCESS_LOG'));
		}

		//Метод удаления кэша запросов
		if (isset($_REQUEST['sqlCache']) && $_REQUEST['sqlCache'] == '1')
		{
			rrmdir($this->sql_cache_dir);

			mkdir($this->sql_cache_dir,0777,true);

			$filename = $this->sql_cache_dir . '/.htaccess';

			if (!file_exists($filename))
			{
				$fp = @fopen($filename, 'w');
				if ($fp)
				{
					fputs($fp, 'Deny from all');
					fclose($fp);
				}
			}

			$message[] = $AVE_Template->get_config_vars('TEMPLATES_CACHE_SC_SUCCESS');
			reportLog($AVE_Template->get_config_vars('TEMPLATES_CACHE_SC_SUCCESS_LOG'));
		}

		echo json_encode(array($AVE_Template->get_config_vars('TEMPLATES_MESSAGE') . "<br />" . implode('<br />', $message), 'accept'));
	}

	/**
	 * Метод очистки миниатюр
	 *
	 */
	function ThumbnailsClear()
	{
		global $AVE_DB, $AVE_Template;

		$message = array();

		function rdel_thumb($dirname)
		{
			$dirs = glob("$dirname/*", GLOB_ONLYDIR|GLOB_NOSORT);

			foreach ($dirs as $dir)
			{
				$dir_thumb = THUMBNAIL_DIR;
				$tmb = glob("$dir/$dir_thumb", GLOB_ONLYDIR|GLOB_NOSORT);

				foreach ($tmb as $tmbs)
				{
					rrmdir($tmbs);
				}

				rdel_thumb($dir);
			}

			$hid_cat=(glob("$dirname/{.tmb}*", GLOB_ONLYDIR|GLOB_BRACE));
			$hid_tmb=$hid_cat[0];

			foreach (glob("$hid_cat[0]/*.png", GLOB_NOSORT) as $filename)
				unlink("$filename");
		}

		rdel_thumb(BASE_DIR . '/' . UPLOAD_DIR);

		$message[] = $AVE_Template->get_config_vars('TEMPLATES_THUMBNAILS_SUCCESS');

		reportLog($AVE_Template->get_config_vars('TEMPLATES_THUMBNAILS_SUCCESS_LOG'));

		echo json_encode(array($AVE_Template->get_config_vars('TEMPLATES_MESSAGE') . "<br />" . implode('<br />', $message), 'accept'));
	}

}
?>