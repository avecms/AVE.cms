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
	 * Обработка тега системного блока
	 *
	 * @param int $id идентификатор системного блока
	 */
	function parse_sysblock($id)
	{
		global $AVE_DB, $AVE_Core;

		if (is_array($id))
			$id = $id[1];

		Debug::startTime('SYSBLOCK_' . $id);

		$eval_sysblock = false;

		if ($id != '')
		{
			$eval_sysblock = true;

			$cache = md5('sysblock' . $id);

			$cache_file = BASE_DIR . '/tmp/cache/sql/sysblocks/' . $id . '/' . $cache . '.code';

			// Если включен DEV MODE, то отключаем кеширование запросов
			if (defined('DEV_MODE') AND DEV_MODE)
				$cache_file = null;

			if (! is_dir(dirname($cache_file)))
				mkdir(dirname($cache_file), 0766, true);

			if (file_exists($cache_file))
			{
				$return = file_get_contents($cache_file);
			}
			else
				{
					$return = $AVE_DB->Query("
						SELECT
							sysblock_text
						FROM
							" . PREFIX . "_sysblocks
						WHERE
							" . (is_numeric($id) ? 'id' : 'sysblock_alias') . " = '" . $id . "'
						LIMIT 1
					")->GetCell();

					if ($cache_file)
						file_put_contents($cache_file, $return);
				}

			//-- парсим теги
			$search = array(
				'[tag:mediapath]',
				'[tag:path]',
				'[tag:docid]'
			);

			$replace = array(
				ABS_PATH . 'templates/' . ((defined('THEME_FOLDER') === false) ? DEFAULT_THEME_FOLDER : THEME_FOLDER) . '/',
				ABS_PATH,
				get_current_document_id()
			);

			$return = str_replace($search, $replace, $return);

			$return = preg_replace_callback('/\[tag:home]/', 'get_home_link', $return);
			$return = preg_replace_callback('/\[tag:breadcrumb]/', 'get_breadcrumb', $return);
			$return = preg_replace_callback('/\[tag:request:([A-Za-z0-9-_]{1,20}+)\]/', 'request_parse', $return);

			if (isset($_REQUEST['id']) && $_REQUEST['id'] != '')
			{
				//-- парсим теги полей документа в шаблоне рубрики
				$return = preg_replace_callback('/\[tag:fld:([a-zA-Z0-9-_]+)\]\[([0-9]+)]\[([0-9]+)]/', 'get_field_element', $return);
				$return = preg_replace_callback('/\[tag:fld:([a-zA-Z0-9-_]+)(|[:(\d)])+?\]/', 'document_get_field', $return);
				$return = preg_replace_callback('/\[tag:watermark:(.+?):([a-zA-Z]+):([0-9]+)\]/', 'watermarks', $return);
				$return = preg_replace_callback('/\[tag:([r|c|f|t|s]\d+x\d+r*):(.+?)]/', 'callback_make_thumbnail', $return);
			}

			$return = preg_replace_callback('/\[tag:block:([A-Za-z0-9-_]{1,20}+)\]/', 'parse_block', $return);

			if ($eval_sysblock)
				$return = eval2var('?'.'>' . $return . '<'.'?');

			$gen_time = Debug::endTime('SYSBLOCK_' . $id);

			$GLOBALS['block_generate']['SYSBLOCK'][$id] = $gen_time;

			return $return;
		}
	}
?>