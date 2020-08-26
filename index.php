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

	define ('START_MICROTIME', microtime());
	define ('START_MEMORY', memory_get_usage());
	define ('BASE_DIR', str_replace("\\", "/", dirname(__FILE__)));

	//-- Проверяем уставлена ли CMS
	if (! @filesize(BASE_DIR . '/config/db.config.php'))
	{
		header ('Location:install/index.php');
		exit;
	}

	//-- Если в запросе пришел вызов thumbnail
	//-- подключаем файл обработки thumbnail
	if (! empty($_REQUEST['thumb']))
	{
		require (BASE_DIR . '/inc/thumb.php');
		exit;
	}

	ob_start();

	//-- Подключаем файл определения мобильных устройств
	//-- далее пользуемся $MobileDetect
	require_once (BASE_DIR . '/lib/mobile_detect/Mobile_Detect.php');
	$MobileDetect = new Mobile_Detect;

	$init_start = microtime();

	//-- Подключаем файл инициализации
	require (BASE_DIR . '/inc/init.php');

	$GLOBALS['block_generate']['INIT']['END'] = number_format(microtime_diff($init_start, microtime()), 3, ',', ' ') . ' sec';

	//-- Проверяем нет ли в запросе папки UPLOADS_DIR
	//-- подключаем файл для работы thumbsnail
	if (strpos ($_SERVER['REQUEST_URI'], ABS_PATH . UPLOAD_DIR . '/') === 0)
	{
		require (BASE_DIR . '/inc/thumb.php');
		exit;
	}

	//-- Папка с шаблонами для Smarty
	$AVE_Template = new AVE_Template(BASE_DIR . '/templates/');

	//-- Подключаем ядро системы
	require (BASE_DIR . '/class/class.core.php');

	$AVE_Core = new AVE_Core;

	//-- Проверям на вызов внешних модулей и системных блоков
	if (
		empty ($_REQUEST['module'])
		||
		empty ($_REQUEST['sysblock'])
		||
		empty ($_REQUEST['request'])
	)
		$AVE_Core->coreUrlParse($_SERVER['REQUEST_URI']);

	$GLOBALS['page_id'] = [(isset($_REQUEST['id'])
		? $_REQUEST['id']
		: '')
			=> ['page' => floatval(0)]];

	//-- Если пришел вызов на показ ревизии документа
	if (! empty($_REQUEST['revission']))
	{
		$sql = "
			SELECT
				doc_data
			FROM
				" . PREFIX . "_document_rev
			WHERE
				doc_id = '" . (int)$_REQUEST['id'] . "'
			AND
				doc_revision = '" . (int)$_REQUEST['revission'] . "'
			LIMIT 1
		";

		$res =	$AVE_DB->Query($sql)->GetCell();

		$res =	@unserialize($res);

		$flds =	get_document_fields((int)$_REQUEST['id'], $res);
	}

	//-- Собираем страницу
	$AVE_Core->coreSiteFetch(get_current_document_id());

	Debug::startTime('CONTENT');

	$content = ob_get_clean();

	if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') && (defined('GZIP_COMPRESSION') && GZIP_COMPRESSION))
		ob_start('ob_gzhandler');
	else
		ob_start();

	eval (' '.'?>' . $content . '<?'.'php ');

	$render = ob_get_clean();

	unset ($content);

	Registry::clean();

	//-- Ловим 404 ошибку
	if (isset($_REQUEST['id']) AND ($_REQUEST['id']) == PAGE_NOT_FOUND_ID)
	{
		report404();
		header ($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true);
	}

	//-- Постраничка
	if (
		empty($_REQUEST['module']) &&
		(
			(
				isset($_REQUEST['page'])
				&& is_numeric($_REQUEST['page'])
				&& ($_REQUEST['page'] < 2 OR ($_REQUEST['page'] > @$GLOBALS['page_id'][$_REQUEST['id']]['page']))
			)
			OR
			(
				isset($_REQUEST['apage'])
				&& is_numeric($_REQUEST['apage'])
				&& ($_REQUEST['apage'] < 2 OR ($_REQUEST['apage'] > @$GLOBALS['page_id'][$_REQUEST['id']]['apage']))
			)
			OR
			(
				isset($_REQUEST['artpage'])
				&& is_numeric($_REQUEST['artpage'])
				&& ($_REQUEST['artpage'] < 2 OR ($_REQUEST['artpage'] > @$GLOBALS['page_id'][$_REQUEST['id']]['artpage']))
			)
		)
	)
	{
		if ($_REQUEST['id'] == 1)
			header ('Location:' . ABS_PATH);
		else
			header ('Location:' . ABS_PATH . $AVE_Core->curentdoc->document_alias . URL_SUFF);
		exit;
	}

	//-- Тут заменяем [tag:rubheader]
	//-- на собранный $GLOBALS["user_header"]
	$rubheader = (empty($GLOBALS['user_header'])
		? ''
		: implode(chr(10), $GLOBALS['user_header']));

	//-- Тут заменяем [tag:rubfooter]
	//-- на собранный $GLOBALS["user_footer"]
	$rubfooter = (empty($GLOBALS['user_footer'])
		? ''
		: implode(chr(10), $GLOBALS['user_footer']));

	$render = str_replace(['[tag:rubheader]', '[tag:rubfooter]'], [$rubheader, $rubfooter], $render);

	unset ($rubheader, $rubfooter);

	$GLOBALS['block_generate']['DOCUMENT']['CONTENT'] = Debug::endTime('CONTENT');

	//-- Вывод конечного результата
	output_compress($render);

	$AVE_DB->Close();