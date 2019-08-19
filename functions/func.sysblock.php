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
	 *
	 * @return bool|mixed|null|string|string[]
	 */
	function parse_sysblock ($id, $params = null)
	{
		global $AVE_DB, $sysParams;

		$sysblock_eval = _getSysBlock($id, 'sysblock_eval');

		$sparams_id = $id . md5($params);							// Создаем уникальный id для каждого набора параметров
		$sysParams[$sparams_id] = [];								// Для отмены лишних ворнингов

		$params = trim($params,':');						// Удаляем: слева ':[', справа ']'
		$params = json_decode($params, true);

		$sysParams[$sparams_id] = $params;

		if (is_array($id))
			$id = $id[1];

		Debug::startTime('SYSBLOCK_' . $id);

		// $eval_sysblock = false;

		if ($id != '')
		{
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

			// Парсим теги системных блоков
			$return = preg_replace_callback('/\[tag:sysblock:([A-Za-z0-9-_]{1,20}+)(|:\{(.*?)\})\]/',
				function ($m)
				{
					return parse_sysblock($m[1], $m[2]);
				},
				$return);

			// Если был вызов
			if ($sysParams != '')
			{
				// Заменяем
				$return = preg_replace_callback('/\[sys:param:([A-Za-z0-9-+_]+)\]/',
					function ($m) use ($sparams_id)
					{
						return params_of_sysblocks($sparams_id, $m[1]);
					},
					$return);
			}
			else
				{
					// Если чистый запрос тизера, просто вытираем tparam
					$return = preg_replace('/\[sysparam:([A-Za-z0-9-+_]+)\]/', '', $return);
				}

			if ($sysblock_eval)
				$return = eval2var('?'.'>' . $return . '<'.'?php ');

			$GLOBALS['block_generate']['SYSBLOCK'][$id] = Debug::endTime('SYSBLOCK_' . $id);

			return $return;
		}

		return false;
	}


	/**
	 * Функция получения уникальных параметров для каждого
	 *
	 * @param $id
	 * @param $el
	 * @return string
	 */
	function params_of_sysblocks($id, $el)
	{
		global $sysParams;

		if (isset($sysParams[$id][$el]))
			return $sysParams[$id][$el];
		else
			return false;
	}


	/**
	 * Получение основных настроек сисблока
	 *
	 * @param $param string параметр настройки, если не указан - все параметры
	 * @return mixed
	 */
	function _getSysBlock($id, $param = '')
	{
		global $AVE_DB;

		$sys_block = null;

		if ($sys_block === null)
		{
			$sql = "
				SELECT
					*
				FROM
					" . PREFIX . "_sysblocks
				WHERE
					" . (is_numeric($id) ? 'id' : 'sysblock_alias') . " = '" . $id . "'
			";

			$sys_block = $AVE_DB->Query($sql, -1, 'sysblocks/' . $id . '/')->FetchAssocArray();
		}

		if ($param == '')
			return $sys_block;

		return isset($sys_block[$param])
			? $sys_block[$param]
			: null;
	}
?>