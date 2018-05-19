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

	@define('APP_NAME', 'AVE.cms');
	@define('APP_VERSION', '3.25');
	@define('APP_INFO', '<a target="_blank" href="https://www.ave-cms.ru/">Ave-Cms.Ru</a> '.'&copy; 2007-' . date('Y'));

	$GLOBALS['CMS_CONFIG']['USER_IP'] = array('DESCR' =>'Использовать IP для автологина на сайте','default'=>false,'TYPE'=>'bool','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['REWRITE_MODE'] = array('DESCR' =>'Использовать ЧПУ Адреса вида index.php будут преобразованы в /home/','default'=>true,'TYPE'=>'bool','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['TRANSLIT_URL'] = array('DESCR' =>'Использовать транслит в ЧПУ адреса вида /страница/ поменяються на /page/','default'=>true,'TYPE'=>'bool','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['URL_SUFF'] = array('DESCR' =>'Cуффикс ЧПУ','default'=>'','TYPE'=>'string','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['URL_YANDEX'] = array('DESCR' =>'Использовать для формирования ЧПУ API Яндекс Переводчика','default'=>false,'TYPE'=>'bool','VARIANT'=>'');

	$themes = array();

	foreach (glob(dirname(dirname(__FILE__)) . '/templates/*') as $filename)
		if (is_dir($filename))
			$themes[] = basename($filename);

	$GLOBALS['CMS_CONFIG']['DEFAULT_THEME_FOLDER'] = array('DESCR' =>'Тема публичной части','default'=>$themes[0],'TYPE'=>'dropdown','VARIANT'=>$themes);

	$GLOBALS['CMS_CONFIG']['DEFAULT_THEME_FOLDER_COLOR'] = array('DESCR' =>'Цвет панели администратора','default'=>'default', 'TYPE'=>'dropdown','VARIANT'=>array('default'));

	$GLOBALS['CMS_CONFIG']['CODEMIRROR_THEME'] = array(
		'DESCR' => 'Цветовая схема Codemirror',
		'default' => 'dracula',
		'TYPE' => 'dropdown',
		'VARIANT' => array(
			'default',
			'3024-day',
			'3024-night',
			'abcdef',
			'ambiance',
			'base16-dark',
			'base16-light',
			'bespin',
			'blackboard',
			'cobalt',
			'colorforth',
			'dracula',
			'duotone-dark',
			'duotone-light',
			'eclipse',
			'elegant',
			'erlang-dark',
			'hopscotch',
			'icecoder',
			'isotope',
			'lesser-dark',
			'liquibyte',
			'material',
			'mbo',
			'mdn-like',
			'midnight',
			'monokai',
			'neat',
			'neo',
			'night',
			'panda-syntax',
			'paraiso-dark',
			'paraiso-light',
			'pastel-on-dark',
			'railscasts',
			'rubyblue',
			'seti',
			'solarized',
			'the-matrix',
			'tomorrow-night-bright',
			'tomorrow-night-eighties',
			'ttcn',
			'twilight',
			'vibrant-ink',
			'xq-dark',
			'xq-light',
			'yeti',
			'zenburn'
		)
	);

	$GLOBALS['CMS_CONFIG']['ADMIN_MENU'] = array('DESCR' => 'Использовать плавующее боковое меню','default'=>true,'TYPE'=>'bool','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['ADMIN_MENU_QUICK_ADD'] = array('DESCR' => 'Показывать меню в шапке с действиями','default'=>false,'TYPE'=>'bool','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['ADMIN_CAPTCHA'] = array('DESCR' => 'Использовать капчу при входе в админку','default'=>false,'TYPE'=>'bool','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['ADMIN_EDITMENU'] = array('DESCR' => 'Использовать всплывающие "Действия" в системе','default'=>true,'TYPE'=>'bool','VARIANT'=>'');

	$GLOBALS['CMS_CONFIG']['ATTACH_DIR'] = array('DESCR' => 'Директория для хранения вложений','default'=>'attachments','TYPE'=>'string','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['UPLOAD_DIR'] = array('DESCR' => 'Директория для хранения файлов','default'=>'uploads','TYPE'=>'string','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['UPLOAD_SHOP_DIR'] = array('DESCR' => 'Директория для хранения миниатюр Магазина','default'=>'uploads/shop','TYPE'=>'string','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['UPLOAD_GALLERY_DIR'] = array('DESCR' => 'Директория для хранения миниатюр Галерей','default'=>'uploads/gallery','TYPE'=>'string','VARIANT'=>'');

	$GLOBALS['CMS_CONFIG']['THUMBNAIL_DIR'] = array('DESCR' => 'Директория для хранения миниатюр изображений','default'=>'thumbnail','TYPE'=>'string','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['THUMBNAIL_SIZES'] = array('DESCR' => 'Разрешенные методы и размеры для миниатюр (через запятую)','default'=>'','TYPE'=>'string','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['THUMBNAIL_IPTC'] = array('DESCR' => 'Генерировать миниютарам IPTC','default'=>false,'TYPE'=>'bool','VARIANT'=>'');

	$GLOBALS['CMS_CONFIG']['WATERMARKS_DIR'] = array('DESCR' => 'Директория для хранения оригиналов изображений (watermark)','default'=>'watermarks','TYPE'=>'string','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['WATERMARKS_FILE'] = array('DESCR' => 'Файл watermark','default'=>'uploads/watermark.png','TYPE'=>'string','VARIANT'=>'');

	$GLOBALS['CMS_CONFIG']['SESSION_SAVE_HANDLER'] = array('DESCR' => 'Хранение сессий', 'default'=>'mysql', 'TYPE'=>'dropdown', 'VARIANT' => array('mysql', 'files', 'memcached'));
	$GLOBALS['CMS_CONFIG']['SESSION_LIFETIME'] = array('DESCR' => 'Время жизни сессии (Значение по умолчанию 24 часа)','default'=>60*60*24,'TYPE'=>'integer','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['COOKIE_LIFETIME'] = array('DESCR' => 'Время жизни cookie автологина (60*60*24*14 - 2 недели)','default'=>60*60*24*14,'TYPE'=>'integer','VARIANT'=>'');

	$GLOBALS['CMS_CONFIG']['USERS_TIME_SHOW'] = array('DESCR' => 'Показывать кто был онлайн в течении: (Значение по умолчанию 24 часа)','default'=>60*60*24,'TYPE'=>'integer','VARIANT'=>'');

	$GLOBALS['CMS_CONFIG']['PROFILING'] = array('DESCR' => 'Вывод статистики','default'=>false,'TYPE'=>'bool','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['SQL_PROFILING'] = array('DESCR' => 'Вывод статистики выполненых запросов','default'=>false,'TYPE'=>'bool','VARIANT'=>'');

	$GLOBALS['CMS_CONFIG']['SEND_SQL_ERROR'] = array('DESCR' => 'Отправка писем с ошибками MySQL','default'=>false,'TYPE'=>'bool','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['PHP_DEBUGGING_FILE'] = array('DESCR' => 'Включить обработку ошибок PHP через обработчик cms','default'=>false,'TYPE'=>'bool','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['PHP_DEBUGGING'] = array('DESCR' => 'Включить стандартную обработку ошибок PHP','default'=>false,'TYPE'=>'bool','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['SMARTY_DEBUGGING'] = array('DESCR' => 'Консоль отладки Smarty','default'=>false,'TYPE'=>'bool','VARIANT'=>'');

	$GLOBALS['CMS_CONFIG']['MEMORY_LIMIT_PANIC'] = array('DESCR' => 'Пытаться очистить память если выходит за пределы ("-1" выключенно) в Мегабайтах (увеличивается нагрузка на MySQL)','default'=>-1,'TYPE'=>'dropdown','VARIANT'=>array('-1','6','12','28','54','100'));

	$GLOBALS['CMS_CONFIG']['SMARTY_COMPILE_CHECK'] = array('DESCR' => 'Контролировать изменения tpl файлов После настройки сайта установить - false','default'=>true,'TYPE'=>'bool','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['SMARTY_USE_SUB_DIRS'] = array('DESCR' => 'Создание папок для кэширования Установите это в false если ваше окружение PHP не разрешает создание директорий от имени Smarty. Поддиректории более эффективны, так что используйте их, если можете.','default'=>true,'TYPE'=>'bool','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['CACHE_DOC_TPL'] = array('DESCR' => 'Кэширование скомпилированных шаблонов документов','default'=>true,'TYPE'=>'bool','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['CACHE_DOC_FILE'] = array('DESCR' => 'Кэширование скомпилированных шаблонов документов в файлах','default'=>true,'TYPE'=>'bool','VARIANT'=>'');

	$GLOBALS['CMS_CONFIG']['SITEMAP_CACHE_LIFETIME'] = array('DESCR' => 'Время жизни кеша для карты сайта (60*60*24*14 - 2 недели)','default'=>0,'TYPE'=>'integer','VARIANT'=>'');

	$GLOBALS['CMS_CONFIG']['YANDEX_MAP_API_KEY'] = array('DESCR' => 'Yandex MAP API REY','default'=>'','TYPE'=>'string','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['GOOGLE_MAP_API_KEY'] = array('DESCR' => 'Google MAP API REY','default'=>'','TYPE'=>'string','VARIANT'=>'');

	$GLOBALS['CMS_CONFIG']['MEMCACHED_SERVER'] = array('DESCR' => 'Адрес Memcached сервера','default'=>'','TYPE'=>'string','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['MEMCACHED_PORT'] = array('DESCR' => 'Порт Memcached сервера','default'=>'','TYPE'=>'string','VARIANT'=>'');

	$GLOBALS['CMS_CONFIG']['DB_EXPORT_GZ'] = array('DESCR' => 'Создание резервной копии базы данных со сжатием','default'=>false,'TYPE'=>'bool','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['DB_EXPORT_TPL'] = array('DESCR' => 'Шаблон имени файла экспорта бд (%SERVER%,%DATE%,%TIME%)','default'=>'%SERVER%_DB_BackUP_%DATE%_%TIME%','TYPE'=>'string','VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['DB_EXPORT_PREFIX'] = array('DESCR' => 'Использовать префикс при экспорте бд','default'=>true,'TYPE'=>'bool','VARIANT'=>'');

	$GLOBALS['CMS_CONFIG']['HTML_COMPRESSION'] = array('DESCR' => 'Включить html компрессию','default'=>false,'TYPE'=>'bool','VARIANT'=>'');

	$GLOBALS['CMS_CONFIG']['GZIP_COMPRESSION'] = array('DESCR' => 'Включить gzip компрессию','default'=>false,'TYPE'=>'bool','VARIANT'=>'');

	$GLOBALS['CMS_CONFIG']['OUTPUT_EXPIRE'] = array('DESCR' => 'Отдавать заголовок на кеширование страницы', 'default'=>false, 'TYPE'=>'bool', 'VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['OUTPUT_EXPIRE_OFFSET'] = array('DESCR' => 'Время жизни кеширования страницы (60*60 - 1 час)','default'=>60*60, 'TYPE'=>'integer', 'VARIANT'=>'');

	$GLOBALS['CMS_CONFIG']['CHECK_VERSION'] = array('DESCR' => 'Проверка наличия новых версий','default'=>true,'TYPE'=>'bool','VARIANT'=>'');
	// 3.23
	$GLOBALS['CMS_CONFIG']['REQUEST_ETC'] = array('DESCR' => 'Окончание в полях запроса', 'default'=>'...', 'TYPE'=>'string', 'VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['REQUEST_BREAK_WORDS'] = array('DESCR' => 'Разбивать слова при выводе полей в запросе', 'default'=>false, 'TYPE'=>'bool', 'VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['REQUEST_STRIP_TAGS'] = array('DESCR' => 'При - у поля, оставляем теги в результате', 'default'=>'', 'TYPE'=>'string', 'VARIANT'=>'');

	$GLOBALS['CMS_CONFIG']['DEV_MODE'] = array('DESCR' => 'Режим разработчика (Отключено кеширование SQL)', 'default'=>false, 'TYPE'=>'bool', 'VARIANT'=>'');
	$GLOBALS['CMS_CONFIG']['SQL_QUERY_SANITIZE'] = array('DESCR' => 'Принудительно проверять SQL запросы', 'default'=>false, 'TYPE'=>'bool', 'VARIANT'=>'');

	if (file_exists(dirname(dirname(__FILE__)) . '/config/config.inc.php'))
		include_once(dirname(dirname(__FILE__)) . '/config/config.inc.php');

	foreach($GLOBALS['CMS_CONFIG'] as $k => $v)
		if(! defined($k))
			define($k, $v['default']);
?>