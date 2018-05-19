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

	function request_get_settings($id)
	{
		global $AVE_DB;

		// Получаем информацию о запросе
		$sql = "
			SELECT
				#REQUEST SETTINGS = $id
				*
			FROM
				" . PREFIX . "_request
			WHERE
				" . (is_numeric($id) ? 'Id' : 'request_alias') . " = '" . $id . "'
		";

		$reqest_settings = $AVE_DB->Query($sql, -1, 'rqs_' . $id, true, '.settings')->FetchRow();

		// Выходим, если нет запроса
		if (! is_object($reqest_settings))
			return '';
		else
			return $reqest_settings;
	}


	/**
	 * Обработка условий запроса.
	 * Возвращает строку условий в SQL-формате
	 *
	 * @param int $id	идентификатор запроса
	 * @return string
	 */
	function request_get_condition_sql_string($id, $update_db = false)
	{
		global $AVE_DB, $AVE_Core;

		$id = (int)$id;
		$from = array();
		$where = array();

		$sql_ak = $AVE_DB->Query("
			SELECT *
			FROM
				" . PREFIX . "_request_conditions
			WHERE
				request_id = '" . $id . "'
			AND
				condition_status = '1'
			ORDER BY
				condition_position ASC
		");

		// Обрабатываем выпадающие списки

		if (! defined('ACP'))
		{
			$doc = 'doc_' . $AVE_Core->curentdoc->Id;

			if (isset($_POST['req_' . $id]))
				$_SESSION[$doc]['req_' . $id] = $_POST['req_' . $id];
			elseif (isset($_SESSION[$doc]['req_' . $id]))
				$_POST['req_' . $id] = $_SESSION[$doc]['req_' . $id];
		}

		if (! empty($_POST['req_' . $id]) && is_array($_POST['req_' . $id]))
		{
			$i = 1;

			foreach ($_POST['req_' . $id] as $fid => $val)
			{
				if (! ($val != '' && isset($_SESSION['val_' . $fid]) && in_array($val, $_SESSION['val_' . $fid])))
					continue;

				$from_dd[] = "%%PREFIX%%_document_fields AS t0$i, ";

				$where_dd[] = "((t0$i.document_id = a.Id) AND (t0$i.rubric_field_id = $fid AND t0$i.field_value = '$val'))";

				++$i;
			}
		}

		$i = 0;

		while ($row_ak = $sql_ak->FetchRow())
		{
			// id поля рубрики
			$fid = $row_ak->condition_field_id;
			// значение для условия
			$val = trim($row_ak->condition_value);
			// если это поле используется для выпадающего списка или пустое значение для условия, пропускаем
			if (isset($_POST['req_' . $id]) && isset($_POST['req_' . $id][$fid]) || $val==='')
				continue;
			// И / ИЛИ
			if (! isset($join) && $row_ak->condition_join)
				$join = $row_ak->condition_join;
			// тип сравнения
			$type = $row_ak->condition_compare;

			// выясняем, числовое поле или нет
			if (! isset($numeric[$fid]))
			{
				$numeric[$fid] = (bool)$AVE_DB->Query("
					SELECT
						rubric_field_numeric
					FROM
						" . PREFIX . "_rubric_fields
					WHERE
						Id = '" . $fid . "'
				")->GetCell();
			}

			$fv = $numeric[$fid] ? "t$fid.field_number_value" : "UPPER(t$fid.field_value)";

			// подставляем название таблицы в свободные условия
			$val = addcslashes(str_ireplace(array('[field]','[numeric_field]'),$fv,$val),"'");

			// формируем выбор таблицы
			// первый раз евалом проходим значение и запоминаем это в переменной $v[$i]
			// как только таблица выбрана, фиксируем это в $t[$fid], чтобы не выбирать по несколько раз одни и те же таблицы
			$from[] = "<?php \$v[$i] = trim(eval2var(' ?>$val<? ')); \$t = array(); if (\$v[$i]>'' && !isset(\$t[$fid])) {echo \"%%PREFIX%%_document_fields AS t$fid,\"; \$t[$fid]=1;}?>";

			// обрабатываем условия
			switch ($type)
			{
				case 'N<':case '<': $where[] = "<?=\$v[$i]>'' ? \"(t$fid.document_id = a.id AND (t$fid.rubric_field_id = '$fid' AND $fv < UPPER('\$v[$i]'))) $join\" : ''?>"; break;
				case 'N>':case '>': $where[] = "<?=\$v[$i]>'' ? \"(t$fid.document_id = a.id AND (t$fid.rubric_field_id = '$fid' AND $fv > UPPER('\$v[$i]'))) $join\" : ''?>"; break;
				case 'N<=':case '<=': $where[] = "<?=\$v[$i]>'' ? \"(t$fid.document_id = a.id AND (t$fid.rubric_field_id = '$fid' AND $fv <= UPPER('\$v[$i]'))) $join\" : ''?>"; break;
				case 'N>=':case '>=': $where[] = "<?=\$v[$i]>'' ? \"(t$fid.document_id = a.id AND (t$fid.rubric_field_id = '$fid' AND $fv >= UPPER('\$v[$i]'))) $join\" : ''?>"; break;

				case '==': $where[] = "<?=\$v[$i]>'' ? \"(t$fid.document_id = a.id AND (t$fid.rubric_field_id = '$fid' AND $fv = UPPER('\$v[$i]'))) $join\" : ''?>"; break;
				case '!=': $where[] = "<?=\$v[$i]>'' ? \"(t$fid.document_id = a.id AND (t$fid.rubric_field_id = '$fid' AND $fv != UPPER('\$v[$i]'))) $join\" : ''?>"; break;
				case '%%': $where[] = "<?=\$v[$i]>'' ? \"(t$fid.document_id = a.id AND (t$fid.rubric_field_id = '$fid' AND $fv LIKE UPPER('%\$v[$i]%'))) $join\" : ''?>"; break;
				case '%': $where[] = "<?=\$v[$i]>'' ? \"(t$fid.document_id = a.id AND (t$fid.rubric_field_id = '$fid' AND $fv LIKE UPPER('\$v[$i]%'))) $join\" : ''?>"; break;
				case '--': $where[] = "<?=\$v[$i]>'' ? \"(t$fid.document_id = a.id AND (t$fid.rubric_field_id = '$fid' AND $fv NOT LIKE UPPER('%\$v[$i]%'))) $join\" : ''?>"; break;
				case '!-': $where[] = "<?=\$v[$i]>'' ? \"(t$fid.document_id = a.id AND (t$fid.rubric_field_id = '$fid' AND $fv NOT LIKE UPPER('\$v[$i]%'))) $join\" : ''?>"; break;

				case 'SEGMENT': $where[] = "<?
					\$v[$i]['seg']=@explode(',',\$v[$i]);
					\$v[$i]['seg'][0]=(int)trim(\$v[$i]['seg'][0]);
					\$v[$i]['seg'][1]=(int)trim(\$v[$i]['seg'][1]);
					echo (\$v[$i]>'' && \$v[$i]{0}!=',' && \$v[$i]['seg'][0] <= \$v[$i]['seg'][1]) ? \"(t$fid.document_id = a.id AND (t$fid.rubric_field_id = '$fid' AND $fv >= '\" . \$v[$i]['seg'][0] . \"' AND $fv <= '\" . \$v[$i]['seg'][1] . \"')) $join\" : '');?>"; break;
				case 'INTERVAL': $where[] = "<?
					\$v[$i]['seg']=@explode(',',\$v[$i]);
					\$v[$i]['seg'][0]=(int)trim(\$v[$i]['seg'][0]);
					\$v[$i]['seg'][1]=(int)trim(\$v[$i]['seg'][1]);
					echo (\$v[$i]>'' && \$v[$i]{0}!=',' && \$v[$i]['seg'][0] < \$v[$i]['seg'][1]) ? \"(t$fid.document_id = a.id AND (t$fid.rubric_field_id = '$fid' AND $fv > '\" . \$v[$i]['seg'][0] . \"' AND $fv < '\" . \$v[$i]['seg'][1] . \"')) $join\" : '');?>"; break;

				case 'IN=': $where[] = "<?=(\$v[$i]>'' && \$v[$i]{0}!=',') ? \"(t$fid.document_id = a.id AND (t$fid.rubric_field_id = '$fid' AND $fv IN (\$v[$i]))) $join\" : ''?>"; break;
				case 'NOTIN=': $where[] = "<?=(\$v[$i]>'' && \$v[$i]{0}!=',') ? \"(t$fid.document_id = a.id AND (t$fid.rubric_field_id = '$fid' AND $fv NOT IN (\$v[$i]))) $join\" : ''?>"; break;

				case 'ANY': $where[] = "<?=\$v[$i]>'' ? \"(t$fid.document_id = a.id AND (t$fid.rubric_field_id = '$fid' AND $fv=ANY(\$v[$i]))) $join\" : ''?>"; break;
				case 'FRE': $where[] = "<?=\$v[$i]>'' ? \"(t$fid.document_id = a.id AND (t$fid.rubric_field_id = '$fid' AND (\$v[$i]))) $join\" : ''?>"; break;
			}

			$i++;
		}

		$retval = array();

		if (! empty($where) || ! empty($where_dd))
		{
			if (! empty($where_dd))
			{
				$from		= (isset($from_dd) ? array_merge($from, $from_dd) : $from);
				$from		= implode(' ', $from);
				$where_dd	= (isset($where_dd) ? ' AND ' : '') . implode(' AND ', $where_dd);
				$where		= implode(' ', $where) . " <?php \$a = array(); echo (!array_sum(\$a) || '$join'=='AND') ? '1=1' : '1=0'?>";
				$retval		= array('from'=>$from,'where'=> $where.$where_dd);
			}
			else
				{
					$from	= implode(' ', $from);
					$where	= implode(' ', $where) . " <?php \$a = array(); echo (!array_sum(\$a) || '$join'=='AND') ? '1=1' : '1=0'?>";
					$retval	= array('from'=>$from,'where'=> $where);
				}
		}

		// если вызвано из админки или просили обновить, обновляем запрос в бд
		if (defined('ACP') || $update_db)
		{
			$AVE_DB->Query("
				UPDATE
					" . PREFIX . "_request
				SET
					request_where_cond = '" . ($retval ? addslashes(serialize($retval)) : '') . "'
				WHERE
					Id = '" . $id . "'
			");

			$AVE_DB->clearRequest($id);
		}

		return @$retval;
	}


	/*
	* Функция принимает строку, и возвращает
	* адрес первого изображения, которую найдет
	*/

	function getImgSrc($data)
	{
		preg_match_all("/(<img )(.+?)( \/)?(>)/u", $data, $images);

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
				{
					return $matches[1] . $matches[2] . $matches[3];
				}
				else
					{
						return $host;
					}
			}
	}

	/**
	 * Функция обработки тэгов полей с использованием шаблонов
	 * в соответствии с типом поля
	 *
	 * @param int $rubric_id	идентификатор рубрики
	 * @param int $document_id	идентификатор документа
	 * @param int $maxlength	максимальное количество символов обрабатываемого поля
	 * @return string
	 */
	function request_get_document_field($field_id, $document_id, $maxlength = '', $rubric_id = 0)
	{
		if (! is_numeric($document_id) || $document_id < 1)
			return '';

		$document_fields = get_document_fields($document_id);

		if (! is_array($document_fields[$field_id]))
			$field_id = intval($document_fields[$field_id]);

		if (empty($document_fields[$field_id]))
			return '';

		$field_value = trim($document_fields[$field_id]['field_value']);

		if ($field_value == '' && $document_fields[$field_id]['tpl_req_empty'])
			return '';

		$func = 'get_field_' . $document_fields[$field_id]['rubric_field_type'];

		if (! is_callable($func))
			$func = 'get_field_default';

		$field_value = $func($field_value, 'req', $field_id, $document_fields[$field_id]['rubric_field_template_request'], $document_fields[$field_id]['tpl_req_empty'], $maxlength, $document_fields, $rubric_id, $document_fields[$field_id]['rubric_field_default']);

		if ($maxlength != '')
		{
			if ($maxlength == 'more' || $maxlength == 'esc'|| $maxlength == 'img' || $maxlength == 'strip')
			{
				if ($maxlength == 'more')
				{
					// ToDo - Вывести в настройки или в настройки самого запроса
					$teaser = explode('<a name="more"></a>', $field_value);
					$field_value = $teaser[0];
				}
				elseif ($maxlength == 'esc')
					{
						$field_value = addslashes($field_value);
					}
					elseif ($maxlength == 'img')
						{
							$field_value = getImgSrc($field_value);
						}
						elseif ($maxlength == 'strip')
							{
								$field_value = str_replace(array("\r\n","\n","\r"), " ", $field_value);
								$field_value = strip_tags($field_value, REQUEST_STRIP_TAGS);
								$field_value = preg_replace('/  +/', ' ', $field_value);
								$field_value = trim($field_value);
							}
			}
			elseif (is_numeric($maxlength))
				{
					if ($maxlength < 0)
					{
						$field_value = str_replace(array("\r\n","\n","\r"), " ", $field_value);
						$field_value = strip_tags($field_value, REQUEST_STRIP_TAGS);
						$field_value = preg_replace('/  +/', ' ', $field_value);
						$field_value = trim($field_value);

						$maxlength = abs($maxlength);
					}
					// ToDo - сделать настройки окончаний
					if ($maxlength != 0)
					{
						$field_value = truncate($field_value, $maxlength, REQUEST_ETC, REQUEST_BREAK_WORDS);
					}

				}
				else
					return false;
		}

		return $field_value;
	}

	function showteaser($id, $tparams = '')
	{
		$item = showrequestelement($id, '', $tparams);
		$item = str_replace('[tag:path]', ABS_PATH, $item);
		$item = str_replace('[tag:mediapath]', ABS_PATH . 'templates/' . ((defined('THEME_FOLDER') === false) ? DEFAULT_THEME_FOLDER : THEME_FOLDER) . '/', $item);

		return $item;
	}

	// Функция получения уникальных параметров для каждого тизера
	function f_params_of_teaser($id_param_array,$num)
	{
		global $params_of_teaser;
		return $params_of_teaser[$id_param_array][$num];
	}

	// Функция получения элемента запроса
	function showrequestelement($mixed, $template = '', $tparams = '')
	{
		global
			$AVE_DB,
			$req_item_num,
			$params_of_teaser,
			$use_cache,
			$request_id,
			$request_changed,
			$request_changed_elements;

		if (is_array($mixed))
			$row = intval($mixed[1]);

		$row = (is_object($mixed)
			? $mixed
			: getDocument($row));

		unset ($mixed);

		if (! $row)
			return '';

		$tparams_id = '';

		if ($tparams != '')
		{
			$tparams_id = $row->Id . md5($tparams);								 // Создаем уникальный id для каждого набора параметров
			$params_of_teaser[$tparams_id] = array();							 // Для отмены лишних ворнингов
			$tparams = trim($tparams,'[]:');									 // Удаляем: слева ':[', справа ']'
			$params_of_teaser[$tparams_id] = explode('|',$tparams);				 // Заносим параметры в массив уникального id
		}

		$sql = "
			SELECT
				rubric_teaser_template
			FROM
				" . PREFIX . "_rubrics
			WHERE
				Id = '" . intval($row->rubric_id) . "'
		";

		$template = ($template > ''
			? $template
			: $AVE_DB->Query($sql)->GetCell());

		$hash  = 'g-' . UGROUP; // Группа пользователей
		$hash .= 'r-' . $request_id; // ID Запроса
		$hash .= 't-' . $row->Id; // ID документа

		$hash = md5($hash);

		$cache_id = 'requests/elements/' . (floor($row->Id / 1000)) . '/' . $row->Id;

		$cachefile_docid = BASE_DIR . '/tmp/cache/sql/' . $cache_id . '/' . $hash . '.element';

		if (file_exists($cachefile_docid) && isset($use_cache) && $use_cache == 1)
		{
			$check_file = $request_changed_elements;

			if ($check_file > filemtime($cachefile_docid))
				unlink ($cachefile_docid);
		}
		else
			{
				if (file_exists($cachefile_docid))
					unlink ($cachefile_docid);
			}

		// Если включен DEV MODE, то отключаем кеширование запросов
		if (defined('DEV_MODE') AND DEV_MODE)
			$cachefile_docid = null;

		if (! file_exists($cachefile_docid))
		{
			$template = preg_replace("/\[tag:if_notempty:rfld:([a-zA-Z0-9-_]+)]\[(more|esc|img|strip|[0-9-]+)]/u", '<'.'?php if((htmlspecialchars(request_get_document_field(\'$1\', '.$row->Id.', \'$2\', '.(int)$row->rubric_id.'), ENT_QUOTES)) != \'\') { '.'?'.'>', $template);
			$template = preg_replace("/\[tag:if_empty:rfld:([a-zA-Z0-9-_]+)]\[(more|esc|img|strip|[0-9-]+)]/u", '<'.'?php if((htmlspecialchars(request_get_document_field(\'$1\', '.$row->Id.', \'$2\', '.(int)$row->rubric_id.'), ENT_QUOTES)) == \'\') { '.'?'.'>', $template);
			$template = str_replace('[tag:if:else]', '<?php }else{ ?>', $template);
			$template = str_replace('[tag:/if]', '<?php } ?>', $template);

			// Парсим теги визуальных блоков
			$item = preg_replace_callback('/\[tag:block:([A-Za-z0-9-_]{1,20}+)\]/', 'parse_block', $template);

			// Парсим теги системных блоков
			$item = preg_replace_callback('/\[tag:sysblock:([A-Za-z0-9-_]{1,20}+)\]/', 'parse_sysblock', $item);

			// Парсим элементы полей
			$item = preg_replace_callback(
				'/\[tag:rfld:([a-zA-Z0-9-_]+)\]\[([0-9]+)]\[([0-9]+)]/',
					create_function(
						'$m',
						'return get_field_element($m[1], $m[2], $m[3], ' . $row->Id . ');'
					),
				$item
			);

			// Парсим теги полей
			$item = preg_replace_callback(
				'/\[tag:rfld:([a-zA-Z0-9-_]+)]\[(more|esc|img|strip|[0-9-]+)]/',
					create_function(
						'$m',
						'return request_get_document_field($m[1], ' . $row->Id . ', $m[2], ' . (int)$row->rubric_id . ');'
					),
				$item
			);

			// Повторно парсим теги полей
			$item = preg_replace_callback(
				'/\[tag:rfld:([a-zA-Z0-9-_]+)]\[(more|esc|img|strip|[0-9-]+)]/',
					create_function(
						'$m',
						'return request_get_document_field($m[1], ' . $row->Id . ', $m[2], ' . (int)$row->rubric_id . ');'
					),
				$item
			);

			// Возвращаем поле документа из БД (document_***)
			$item = preg_replace_callback('/\[tag:doc:([a-zA-Z0-9-_]+)\]/u',
				function ($match) use ($row)
				{
					return isset($row->{$match[1]})
						? $row->{$match[1]}
						: null;
				},
				$item
			);

			// Если пришел вызов на активацию языковых файлов
			$item = preg_replace_callback(
				'/\[tag:langfile:([a-zA-Z0-9-_]+)\]/u',
				function ($match)
				{
					global $AVE_Template;

					return $AVE_Template->get_config_vars($match[1]);
				},
				$item
			);

			// Абсолютный путь
			$item = str_replace('[tag:path]', ABS_PATH, $item);

			// Путь к папке шаблона
			$item = str_replace('[tag:mediapath]', ABS_PATH . 'templates/' . ((defined('THEME_FOLDER') === false)
				? DEFAULT_THEME_FOLDER
				: THEME_FOLDER)
			. '/', $item);

			// Watermarks
			$item = preg_replace_callback(
				'/\[tag:watermark:(.+?):([a-zA-Z]+):([0-9]+)\]/',
					create_function(
						'$m',
						'watermarks($m[1], $m[2], $m[3]);'
					),
				$item
			);

			// Удаляем ошибочные теги полей документа и языковые, в шаблоне рубрики
			$item = preg_replace('/\[tag:doc:\d*\]/', '', $item);
			$item = preg_replace('/\[tag:langfile:\d*\]/', '', $item);

			// Делаем линки на миниатюры
			$item = preg_replace_callback('/\[tag:([r|c|f|t|s]\d+x\d+r*):(.+?)]/', 'callback_make_thumbnail', $item);

			// Если был вызов тизера, ищем параметры
			if ($tparams != '')
			{
				// Заменяем tparam в тизере
				$item = preg_replace_callback(
					'/\[tparam:([0-9]+)\]/',
						create_function(
							'$m',
							'return f_params_of_teaser('.$tparams_id.', $m[1]);'
						),
					$item
				);
			}
			else
				{
					// Если чистый запрос тизера, просто вытираем tparam
					$item = preg_replace('/\[tparam:([0-9]+)\]/', '', $item);
				}

			// Блок для проверки передачи параметров тизеру
			/*
				if (count($params_of_teaser[$tparams_id]))
				{
					Debug::_echo($params_of_teaser);
					Debug::_echo($row_Id_mas);
					Debug::_echo($item, true);
				}
			*/

			$item = str_replace('[tag:domain]', getSiteUrl(), $item);

			$link = rewrite_link('index.php?id=' . $row->Id . '&amp;doc=' . (empty($row->document_alias) ? prepare_url($row->document_title) : $row->document_alias));
			$item = str_replace('[tag:link]', $link, $item);
			$item = str_replace('[tag:docid]', $row->Id, $item);
			$item = str_replace('[tag:docitemnum]', $req_item_num, $item);
			$item = str_replace('[tag:adminlink]', 'index.php?do=docs&action=edit&rubric_id=' . $row->rubric_id . '&Id=' . $row->Id . '&cp=' . session_id() . '', $item);
			$item = str_replace('[tag:doctitle]', stripslashes(htmlspecialchars_decode($row->document_title)), $item);
			$item = str_replace('[tag:docparent]', $row->document_parent, $item);
			$item = str_replace('[tag:doclang]', $row->document_lang, $item);
			$item = str_replace('[tag:docdate]', pretty_date(strftime(DATE_FORMAT, $row->document_published)), $item);
			$item = str_replace('[tag:doctime]', pretty_date(strftime(TIME_FORMAT, $row->document_published)), $item);
			$item = str_replace('[tag:humandate]', human_date($row->document_published), $item);
			$item = preg_replace_callback(
				'/\[tag:date:([a-zA-Z0-9-. \/]+)\]/',
					create_function('$m','return translate_date(date($m[1], '.$row->document_published.'));
				'),
				$item
			);

			if (preg_match('/\[tag:docauthor]/u', $item))
			{
				$item = str_replace('[tag:docauthor]', get_username_by_id($row->document_author_id), $item);
			}

			$item = str_replace('[tag:docauthorid]', $row->document_author_id, $item);

			$item = preg_replace_callback(
				'/\[tag:docauthoravatar:(\d+)\]/',
					create_function(
						'$m',
						'return getAvatar('.intval($row->document_author_id).', $m[1]);'
					),
				$item
			);

			if (isset($use_cache) && $use_cache == 1)
			{
				// Кеширование элементов запроса
				if (! file_exists(dirname($cachefile_docid)))
					@mkdir(dirname($cachefile_docid), 0777, true);

				file_put_contents($cachefile_docid, $item);
			}
		}
		else
			{
				$item = file_get_contents($cachefile_docid);
			}

		// Кол-во просмотров
		$item = str_replace('[tag:docviews]', $row->document_count_view, $item);

		unset($row);

		return $item;
	}

	/**
	 * Обработка тега запроса.
	 * Возвращает список документов удовлетворяющих параметрам запроса
	 * оформленный с использованием шаблона
	 *
	 * @param int $id	идентификатор запроса
	 * @return string
	 */
	function request_parse($id, $params = array())
	{
		global $AVE_Core, $AVE_DB, $request_documents;

		// Если id пришёл из тега, берём нужную часть массива
		if (is_array($id))
			$id = $id[1];

		$t = array();
		$a = array();
		$v = array();

		// Получаем информацию о запросе
		$request = request_get_settings($id);

		// Фиксируем время начала генерации запроса
		Debug::startTime('request_' . $id);

		// Массив для полей SELECT
		$request_select = array();
		// Массив для присоединения таблиц JOIN
		$request_join = array();
		// Массив для добавления условий WHERE
		$request_where = array();
		// Массив для сортировки результатов ORDER BY
		$request_order = array();
		// Массив для сортировки результатов ORDER BY
		$request_order_fields = array();

		$request_order_str = '';
		$request_select_str = '';

		// Сортировка по полям из переданных параметров
		if (empty($params['SORT']) && ! empty($_REQUEST['requestsort_' . $id]) && ! is_array($_REQUEST['requestsort_' . $id]))
		{
			// Разрешаем перебор полей для сортировки через ";"
			$sort = explode(';', $_REQUEST['requestsort_' . $id]);

			foreach($sort as $v)
			{
				$v1 = explode('=', $v);

				// Если хотим сортировку DESC то пишем alias = 0
				$params['SORT'][$v1[0]] = (isset($v1[1]) && $v1[1] == 0
					? 'DESC'
					: 'ASC');
			}
		}

		// Сортировка по полям
		// Если пришел параметр SORT
		if (! empty($params['SORT']) && is_array($params['SORT']))
		{
			foreach($params['SORT'] as $fid => $sort)
			{
				if (is_numeric($fid))
					$fid = (int)get_field_num($request->rubric_id, $fid);

				// Если значение больше 0
				if ((int)$fid > 0)
				{
					$sort = strtolower($sort);

					// Добавляем условие в SQL
					$request_join[$fid] = "<? if (preg_match('t[]'))?><?=(! isset(\$t[$fid])) ? \"LEFT JOIN " . PREFIX . "_document_fields AS t$fid ON (t$fid.document_id = a.Id AND t$fid.rubric_field_id='$fid')\" : ''?>";

					// Если в сортировке указано ASC иначе DESC
					$asc_desc = strpos(strtolower($sort),'asc') !== false
						? 'ASC'
						: 'DESC';

					$request_order['field-'.$fid] = "t$fid.field_value " . $asc_desc;

					$request_order_fields[] = $fid;
				}
				else
					{
						// Если в сортировке указано ASC иначе DESC
						$asc_desc = strpos(strtolower($sort),'asc') !== false
							? 'ASC'
							: 'DESC';

						$request_order[$param] = "$fid " . $asc_desc;
					}
			}
		}
		// Сортировка по полю из настроек (только если не передана другая в параметрах)
		elseif ($request->request_order_by_nat)
			{
				$fid = (int)$request->request_order_by_nat;

				// Добавляем с учётом переменной $t из условий, чтобы не выбирать те же таблиы заново - это оптимизирует время
				$request_join[$fid] = "<?= (! isset(\$t[$fid])) ? \"LEFT JOIN " . PREFIX . "_document_fields AS t$fid ON (t$fid.document_id = a.Id AND t$fid.rubric_field_id='$fid')\" : ''?>";

				$request_order['field-' . $fid] = "t$fid.field_value " . $request->request_asc_desc;
				$request_order_fields[] = $fid;
			}

		// Вторичная сортировка по параметру документа - добавляем в конец сортировок
		if (! empty($params['RANDOM']))
		{
			$request_order['sort'] = ($params['RANDOM'] == 1)
				? 'RAND()'
				: '';
		}
		elseif ($request->request_order_by)
			{
				$request_order['sort'] = ($request->request_order_by == 'RAND()')
					? 'RAND()'
					: 'a.' . $request->request_order_by . ' ' . $request->request_asc_desc;
			}

		// Заменяем field_value на field_number_value во всех полях для сортировки, если поле числовое
		if (! empty($request_order_fields))
		{
			$sql_numeric = $AVE_DB->Query("
				SELECT
					Id
				FROM
					" . PREFIX . "_rubric_fields
				WHERE
					Id IN (" . implode(',', $request_order_fields) . ")
				AND
					rubric_field_numeric = '1'
			");

			if ($sql_numeric->_result->num_rows > 0)
			{
				while ($fid = (int)$sql_numeric->FetchRow()->Id)
					$request_order['field-' . $fid] = str_replace('field_value','field_number_value', $request_order['field-' . $fid]);
			}
		}

		// Статус: если в параметрах, то его ставим. Иначе выводим только активные доки
		$request_where[] = "a.document_status = '" . ((isset($params['STATUS']))
			? (int)$params['STATUS']
			: '1') . "'";

		// Не выводить текущий документ
		if ($request->request_hide_current)
			$request_where[] = "a.Id != '" . get_current_document_id() . "'";

		// Язык
		if ($request->request_lang)
			$request_where[] = "a.document_lang = '" . $_SESSION['user_language'] . "'";

		// Дата публикации документов
		if (get_settings('use_doctime'))
			$request_where[] = "a.document_published <= UNIX_TIMESTAMP() AND (a.document_expire = 0 OR a.document_expire >= UNIX_TIMESTAMP())";

		// Условия запроса
		// если условия пустые, получаем строку с сохранением её в бд
		if (! $request->request_where_cond)
			$where_cond = request_get_condition_sql_string($request->Id, false);
		// иначе, берём из запроса
		else
			$where_cond = unserialize($request->request_where_cond);

		$where_cond['from'] = (isset($where_cond['from']))
			? str_replace('%%PREFIX%%', PREFIX, $where_cond['from'])
			: '';

		if (isset($where_cond['where']))
			$request_where[] = $where_cond['where'];

		// Родительский документ
		if (isset($params['PARENT']) && (int)$params['PARENT'] > 0)
			$request_where[] = "a.document_parent = '" . (int)$params['PARENT'] . "'";

		// Автор
		// Если задано в параметрах
		if (isset($params['USER_ID']))
			$user_id = (int)$params['USER_ID'];
		// Если стоит галка, показывать только СВОИ документы в настройках
		// Аноним не увидит ничего, так как 0 юзера нет
		elseif ($request->request_only_owner == '1')
			$user_id = (int)$_SESSION['user_id'];

		// Если что-то добавили, пишем
		if (isset($user_id))
			$request_where[] = "a.document_author_id = '" . $user_id . "'";

		// Произвольные условия WHERE
		if (isset($params['USER_WHERE']) && $params['USER_WHERE'] > '')
		{
			if (is_array($params['USER_WHERE']))
				$request_where = array_merge($request_where,$params['USER_WHERE']);
			else
				$request_where[] = $params['USER_WHERE'];
		}

		// Готовим строку с условиями
		array_unshift($request_where,"
			a.Id != '1' AND a.Id != '" . PAGE_NOT_FOUND_ID . "' AND
			a.rubric_id = '" . $request->rubric_id . "' AND
			a.document_deleted != '1'");

		$request_where_str = '(' . implode(') AND (',$request_where) . ')';

		// Количество выводимых доков
		$params['LIMIT'] = (! empty($params['LIMIT'])
			? $params['LIMIT']
			: (! empty($_REQUEST['requestlimiter_'.$id])
				? $_REQUEST['requestlimiter_'.$id]
				: (int)$request->request_items_per_page));

		$limit = (isset($params['LIMIT']) && is_numeric($params['LIMIT']) && $params['LIMIT'] > '')
			? (int)$params['LIMIT']
			: (int)$request->request_items_per_page;

		$start = (isset($params['START']))
			? (int)$params['START']
			: (($request->request_show_pagination == 1)
				? get_current_page('apage') * $limit - $limit
				: 0);

		$limit_str = ($limit > 0)
			? "LIMIT " . $start . "," . $limit
			: '';

		// Готовим строку с сортировкой
		if ($request_order)
			$request_order_str = "ORDER BY " . implode(', ',$request_order);

		// Готовим строку с полями
		if ($request_select)
			$request_select_str = ',' . implode(",\r\n",$request_select);

		unset ($a, $t, $v);

		if (! isset($params['SQL_QUERY']))
		{
			// Составляем запрос к БД
			$sql = " ?>
				SELECT STRAIGHT_JOIN SQL_CALC_FOUND_ROWS
				#REQUEST = $request->Id
					a.*
					" . $request_select_str . "
				FROM
					" . $where_cond['from'] . "
					" . (isset($params['USER_FROM']) ? $params['USER_FROM'] : '') . "
					" . PREFIX . "_documents AS a
					" . implode(' ', $request_join) . "
					" . (isset($params['USER_JOIN']) ? $params['USER_FROM'] : '') . "
				WHERE
					" . $request_where_str . "
				GROUP BY a.Id
				" . $request_order_str . "
				" . $limit_str . "
			<?"."php ";

			$sql_request = eval2var($sql);

			unset ($sql);

			// Убираем дубли в выборе полей
			foreach (array_keys($request_join) AS $key)
			{
				$search = PREFIX . '_document_fields AS t' . $key . ',';

				if (preg_match('/' . $search . '/', $sql_request) > 0)
				{
					$sql_request = str_replace($search, '', $sql_request);
				}
			}
		}
		else
			{
				$sql_request = $params['SQL_QUERY'];
			}

		// Если просили просто показать сформированный запрос
		if ((isset($params['DEBUG']) && $params['DEBUG'] == 1) || $request->request_show_sql == 1)
		{
			$return = Debug::_print($sql_request);

			return $return;
		}

		// Выполняем запрос к бд
		$sql = $AVE_DB->Query($sql_request, (int)$request->request_cache_lifetime, 'rqs_' . $id, true, '.request');

		// Если просили просто вернуть резльтат запроса, возвращаем результат
		if (isset($params['RETURN_SQL']) && $params['RETURN_SQL'] == 1)
			return $AVE_DB->GetFoundRows();

		$num_items = 0;

		// Если есть вывод пагинации, то выполняем запрос на получение кол-ва элементов
		if ($request->request_show_pagination == 1 || (isset($params['SHOW']) && $params['SHOW'] == 1))
			$num_items = $AVE_DB->NumAllRows($sql_request, (int)$request->request_cache_lifetime, 'rqs_' . $id);
		else
			$num_items = ((isset($params['NO_FOUND_ROWS']) && $params['NO_FOUND_ROWS'] == 1) || ! $request->request_count_items
				? 0
				: $AVE_DB->GetFoundRows());

		// Если просили просто вернуть кол-во, возвращаем результат
		if (isset($params['RETURN_COUNT']) && $params['RETURN_COUNT'] == 1)
			return $num_items;

		unset ($sql_request);

		// Приступаем к обработке шаблона
		$main_template = $request->request_template_main;

		//-- Если кол-во элементов больше 0, удалаяем лишнее
		if ($num_items > 0)
		{
			$main_template = preg_replace('/\[tag:if_empty](.*?)\[\/tag:if_empty]/si', '', $main_template);
			$main_template = str_replace (array('[tag:if_notempty]','[/tag:if_notempty]'), '', $main_template);
		}
		else
			{
				$main_template = preg_replace('/\[tag:if_notempty](.*?)\[\/tag:if_notempty]/si', '', $main_template);
				$main_template = str_replace (array('[tag:if_empty]','[/tag:if_empty]'), '', $main_template);
			}

		$pagination = '';

		// Кол-во страниц
		$num_pages = ($limit > 0)
			? ceil($num_items / $limit)
			: 0;

		// Собираем пагинацию, еслиесть указание ее выводить
		if ($request->request_show_pagination == 1 || (isset($params['SHOW']) && $params['SHOW'] == 1))
		{
			// Если в запросе пришел номер страницы и он больше, чем кол-во страниц
			// Делаем перенаправление
			if (isset($_REQUEST['apage']) && is_numeric($_REQUEST['apage']) && $_REQUEST['apage'] > $num_pages)
			{
				$redirect_link = rewrite_link('index.php?id=' . $AVE_Core->curentdoc->Id
					. '&amp;doc=' . (empty($AVE_Core->curentdoc->document_alias)
						? prepare_url($AVE_Core->curentdoc->document_title)
						: $AVE_Core->curentdoc->document_alias)
					. ((isset($_REQUEST['artpage']) && is_numeric($_REQUEST['artpage']))
						? '&amp;artpage=' . $_REQUEST['artpage']
						: '')
					. ((isset($_REQUEST['page']) && is_numeric($_REQUEST['page']))
						? '&amp;page=' . $_REQUEST['page']
						: ''));

				header('Location:' . $redirect_link);
				exit;
			}

			// Запоминаем глобально
			@$GLOBALS['page_id'][$_REQUEST['id']]['apage'] = (isset($GLOBALS['page_id'][$_REQUEST['id']]['apage']) && $GLOBALS['page_id'][$_REQUEST['id']]['apage'] > $num_pages
				? @$GLOBALS['page_id'][$_REQUEST['id']]['apage']
				: $num_pages);

			$pagination = '';

			// Если кол-во страниц больше 1й
			if ($num_pages > 1)
			{
				$queries = '';

				// Добавляем GET-запрос в пагинацию если пришло ADD_GET
				// или указанов настройках запроса
				if ($request->request_use_query == 1 || (isset($params['ADD_GET']) && $params['ADD_GET'] == 1))
					$queries = ($_SERVER['QUERY_STRING'])
						? '?' . $_SERVER['QUERY_STRING']
						: '';

				$pagination = 'index.php?id='
					. $AVE_Core->curentdoc->Id

					. '&amp;doc=' . (empty($AVE_Core->curentdoc->document_alias)
						? prepare_url($AVE_Core->curentdoc->document_title)
						: $AVE_Core->curentdoc->document_alias)

					. '&amp;apage={s}'

					. ((isset($_REQUEST['artpage']) && is_numeric($_REQUEST['artpage']))
						? '&amp;artpage=' . $_REQUEST['artpage']
						: '')

					. ((isset($_REQUEST['page']) && is_numeric($_REQUEST['page']))
						? '&amp;page=' . $_REQUEST['page']
						: '')

					// Добавляем GET-запрос в пагинацию
					. clean_php($queries)
				;

				// ID пагинации
				$pagination_id = (isset($params['PAGINATION']) && $params['PAGINATION'] > 0)
					? $params['PAGINATION']
					: $request->request_pagination;

				// Собираем пагинацию
				$pagination = AVE_Paginations::getPagination($num_pages, 'apage', $pagination, $pagination_id);

				// Костыли для Главной страницы
				$pagination = str_ireplace('"//"', '"/"', str_ireplace('///', '/', rewrite_link($pagination)));
				$pagination = str_ireplace('"//' . URL_SUFF . '"', '"/"', $pagination);
			}
		}

		// Элементы запроса
		$rows = array();

		// id найденных документов
		$request_documents = array();

		while ($row = $sql->FetchRow())
		{
			// Собираем Id документов
			array_push($request_documents, $row->Id);
			// Собираем оставшуюся информацию
			array_push($rows, $row);
		}

		//-- Обрабатываем шаблоны элементов
		$items = '';
		//-- Счетчик
		$x = 0;
		//-- Общее число элементов
		$items_count = count($rows);

		global $req_item_num, $use_cache, $request_id, $request_changed, $request_changed_elements;

		$use_cache = $request->request_cache_elements;

		$request_id = $request->Id;

		$request_changed = $request->request_changed;
		$request_changed_elements = $request->request_changed_elements;

		$item = '';

		foreach ($rows as $row)
		{
			$x++;
			$last_item = ($x == $items_count ? true : false);
			$item_num = $x;
			$req_item_num = $item_num;
			$item = showrequestelement($row, $request->request_template_item);
			$item = '<'.'?php $item_num='.var_export($item_num,1).'; $last_item='.var_export($last_item,1).'?'.'>'.$item;
			$item = '<?php $req_item_id = ' . $row->Id . ';?>' . $item;
			$item = str_replace('[tag:if_first]', '<'.'?php if(isset($item_num) && $item_num===1) { ?'.'>', $item);
			$item = str_replace('[tag:if_not_first]', '<'.'?php if(isset($item_num) && $item_num!==1) { ?'.'>', $item);
			$item = str_replace('[tag:if_last]', '<'.'?php if(isset($last_item) && $last_item) { ?'.'>', $item);
			$item = str_replace('[tag:if_not_last]', '<'.'?php if(isset($item_num) && !$last_item) { ?'.'>', $item);
			$item = preg_replace('/\[tag:if_every:([0-9-]+)\]/u', '<'.'?php if(isset($item_num) && !($item_num % $1)){ '.'?'.'>', $item);
			$item = preg_replace('/\[tag:if_not_every:([0-9-]+)\]/u', '<'.'?php if(isset($item_num) && ($item_num % $1)){ '.'?'.'>', $item);
			$item = str_replace('[tag:/if]', '<'.'?php  } ?>', $item);
			$item = str_replace('[tag:if_else]', '<'.'?php  }else{ ?>', $item);
			$items .= $item;
		}

		//== Обрабатываем теги запроса

		//-- Парсим теги визуальных блоков
		$main_template = preg_replace_callback('/\[tag:block:([A-Za-z0-9-_]{1,20}+)\]/', 'parse_block', $main_template);

		//-- Парсим теги системных блоков
		$main_template = preg_replace_callback('/\[tag:sysblock:([A-Za-z0-9-_]{1,20}+)\]/', 'parse_sysblock', $main_template);

		//-- Заменяем тег пагинации на пагинацию
		$main_template = str_replace('[tag:pages]', $pagination, $main_template);

		//-- Дата
		$main_template = preg_replace_callback(
			'/\[tag:date:([a-zA-Z0-9-. \/]+)\]/',
				create_function('$m','return translate_date(date($m[1], '.$AVE_Core->curentdoc->document_published.'));
			'),
			$main_template
		);

		//-- ID Документа
		$main_template = str_replace('[tag:docid]', $AVE_Core->curentdoc->Id, $main_template);
		//-- ID Автора
		$main_template = str_replace('[tag:docauthorid]', $AVE_Core->curentdoc->document_author_id, $main_template);

		//-- Имя автора
		if (preg_match('[tag:docauthor]', $main_template))
			$main_template = str_replace('[tag:docauthor]', get_username_by_id($AVE_Core->curentdoc->document_author_id), $main_template);

		//-- Время - 1 день назад
		$main_template = str_replace('[tag:humandate]', human_date($AVE_Core->curentdoc->document_published), $main_template);

		//-- Дата создания
		$main_template = str_replace('[tag:docdate]', pretty_date(strftime(DATE_FORMAT, $AVE_Core->curentdoc->document_published)), $main_template);

		//-- Время создания
		$main_template = str_replace('[tag:doctime]', pretty_date(strftime(TIME_FORMAT, $AVE_Core->curentdoc->document_published)), $main_template);

		//-- Домен
		$main_template = str_replace('[tag:domain]', getSiteUrl(), $main_template);

		//-- Общее число элементов запроса
		$main_template = str_replace('[tag:doctotal]', $num_items, $main_template);
		//-- Показано элементов запроса на странице
		$main_template = str_replace('[tag:doconpage]', $x, $main_template);

		//-- Номер страницы пагинации
		$main_template = str_replace('[tag:pages:curent]', get_current_page('apage'), $main_template);

		//-- Общее кол-во страниц пагинации
		$main_template = str_replace('[tag:pages:total]', $num_pages, $main_template);

		//-- Title
		$main_template = str_replace('[tag:pagetitle]', stripslashes(htmlspecialchars_decode($AVE_Core->curentdoc->document_title)), $main_template);

		//-- Alias
		$main_template = str_replace('[tag:alias]', (isset($AVE_Core->curentdoc->document_alias) ? $AVE_Core->curentdoc->document_alias : ''), $main_template);

		//-- Возвращаем параметр документа из БД
		$main_template = preg_replace_callback('/\[tag:doc:([a-zA-Z0-9-_]+)\]/u',
			function ($match)
			{
				return isset($row->{$match[1]})
					? $row->{$match[1]}
					: null;
			},
			$main_template
		);

		//-- Если пришел вызов на активацию языковых файлов
		$main_template = preg_replace_callback(
			'/\[tag:langfile:([a-zA-Z0-9-_]+)\]/u',
			function ($match)
			{
				global $AVE_Template;

				return $AVE_Template->get_config_vars($match[1]);
			},
			$main_template
		);

		//-- Вставляем элементы запроса
		$return = str_replace('[tag:content]', $items, $main_template);

		//-- Парсим тег [hide]
		$return = parse_hide($return);

		//-- Абсолютный путь
		$return = str_replace('[tag:path]', ABS_PATH, $return);

		//-- Путь до папки шаблона
		$return = str_replace('[tag:mediapath]', ABS_PATH . 'templates/' . ((defined('THEME_FOLDER') === false) ? DEFAULT_THEME_FOLDER : THEME_FOLDER) . '/', $return);

		//-- Парсим модули
		$return = $AVE_Core->coreModuleTagParse($return);

		//-- Фиксируем время генерации запроса
		$GLOBALS['block_generate']['REQUESTS'][$id][] = Debug::endTime('request_' . $id);

		//	Статистика
		if ($request->request_show_statistic)
			$return .= "<div class=\"request_statistic\"><br>Найдено: $num_items<br>Показано: $items_count<br>Время генерации: " . Debug::endTime('request_' . $id) . " сек<br>Пиковое значение: ".number_format(memory_get_peak_usage()/1024, 0, ',', ' ') . ' Kb</div>';

		return $return;
	}


	/**
	 * Функция получения содержимого поля для обработки в шаблоне запроса
	 * <pre>
	 * Пример использования в шаблоне:
	 *	<li>
	 *		<?php
	 *			$r = request_get_document_field_value(12, [tag:docid]);
	 *			echo $r . ' (' . strlen($r) . ')';
	 *		?>
	 *	</li>
	 * </pre>
	 *
	 * @param int $rubric_id	идентификатор поля, для [tag:rfld:12][150] $rubric_id = 12
	 * @param int $document_id	идентификатор документа к которому принадлежит поле.
	 * @param int $maxlength	необязательный параметр, количество возвращаемых символов.
	 * 							Если данный параметр указать со знаком минус
	 * 							содержимое поля будет очищено от HTML-тегов.
	 * @return string
	 */
	function request_get_document_field_value($rubric_id, $document_id, $maxlength = 0)
	{
		if (! is_numeric($rubric_id) || $rubric_id < 1 || ! is_numeric($document_id) || $document_id < 1)
			return '';

		$document_fields = get_document_fields($document_id);

		$field_value = isset($document_fields[$rubric_id])
			? $document_fields[$rubric_id]['field_value']
			: '';

		if (! empty($field_value))
		{
			$field_value = strip_tags($field_value, '<br /><strong><em><p><i>');
			$field_value = str_replace('[tag:mediapath]', ABS_PATH . 'templates/' . ((defined('THEME_FOLDER') === false) ? DEFAULT_THEME_FOLDER : THEME_FOLDER) . '/', $field_value);
		}

		if (is_numeric($maxlength) && $maxlength != 0)
		{
			if ($maxlength < 0)
			{
				$field_value = str_replace(array("\r\n", "\n", "\r"), ' ', $field_value);
				$field_value = strip_tags($field_value, "<a>");
				$field_value = preg_replace('/  +/', ' ', $field_value);
				$maxlength = abs($maxlength);
			}

			$field_value = mb_substr($field_value, 0, $maxlength) . (strlen($field_value) > $maxlength
				? '... '
				: '');
		}

		return $field_value;
	}
?>