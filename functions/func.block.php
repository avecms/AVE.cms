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
 * Обработка тега блока
 *
 * @param int $id идентификатор системного блока
 */
function parse_block($id)
{
	global $AVE_DB, $AVE_Core;

	if (is_array($id))
		$id = $id[1];

	Debug::startTime('BLOCK_' . $id);

	if ($id != '')
	{
		$cache_file = BASE_DIR . '/cache/sql/block/' . $id . '.cache';

		if(! file_exists(dirname($cache_file)))
			mkdir(dirname($cache_file), 0766, true);

		if(file_exists($cache_file))
		{
			$return = file_get_contents($cache_file);
		}
		else
			{
				$return = $AVE_DB->Query("
					SELECT
						block_text
					FROM
						" . PREFIX . "_blocks
					WHERE
						" . (is_numeric($id) ? 'id' : 'block_alias') . " = '" . $id . "'
					LIMIT 1
				")->GetCell();

				file_put_contents($cache_file,$return);
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
			$return = preg_replace_callback('/\[tag:fld:([a-zA-Z0-9-_]+)\]/', 'document_get_field', $return);
			$return = preg_replace_callback('/\[tag:([r|c|f|t]\d+x\d+r*):(.+?)]/', 'callback_make_thumbnail', $return);
		}

		$gen_time = Debug::endTime('SYSBLOCK_' . $id);

		$GLOBALS['block_generate'][] = array('BLOCK_'. $id => $gen_time);

		return $return;
	}
}
?>