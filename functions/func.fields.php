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
	 * Определяем пустое изображение
	 */
	$img_pixel = 'templates/images/default.png';


	/**
	 * Проверка папки /fields/ на наличие полей
	 */
	if (is_dir(BASE_DIR . '/fields/'))
	{
		$d = dir(BASE_DIR . '/fields');

		while (false !== ($entry = $d->read()))
		{
			$field_dir = $d->path . '/' . $entry;

			if (is_dir($field_dir) && file_exists($field_dir . '/field.php'))
			{
				require_once ($field_dir . '/field.php');
			}
		}

		$d->Close();
	}


	/**
	 * Проверка папок /fields/ в модулях, на наличие полей
	 */
	if (is_dir(BASE_DIR . '/modules/'))
	{
		$d = dir(BASE_DIR . '/modules');

		while (false !== ($entry = $d->read()))
		{
			$module_dir = $d->path . '/' . $entry;

			if (is_dir($module_dir) && file_exists($module_dir . '/field.php'))
				require_once($module_dir . '/field.php');
		}

		$d->Close();
	}


	/**
	 * Поле по умолчанию
	 *
	 * @param        $field_value
	 * @param        $action
	 * @param int    $field_id
	 * @param string $tpl
	 * @param int    $tpl_empty
	 * @param null   $maxlength
	 * @param array  $document_fields
	 * @param int    $rubric_id
	 * @param null   $default
	 *
	 * @return string
	 */
	function get_field_default($field_value, $action, $field_id=0, $tpl='', $tpl_empty=0, &$maxlength=null, $document_fields=array(), $rubric_id=0, $default=null, $_tpl=null)
	{
		switch ($action)
		{
			case 'edit':
					return '<input type="text" style="width: 100%" name="feld[' . $field_id . ']" value="' . $field_value . '">';
			case 'doc':
			case 'req':
				if (! $tpl_empty)
				{
					$field_param = explode('|', $field_value);
					$field_value = preg_replace_callback(
						'/\[tag:parametr:(\d+)\]/i',
						function($data) use($field_param)
						{
							return $field_param[(int)$data[1]];
						},
						$tpl
					);
				}
				return $field_value;

			default:
				return $field_value;
		}
	}


	/**
	 * Возвращаем тип поля
	 *
	 * @return mixed
	 */
	function get_field_type ($type = '')
	{
		static $fields;

		if (is_array($fields))
			return $fields;

		$arr = get_defined_functions();

		$fields = array();
		$field = array();

		foreach ($arr['user'] as $v)
		{
			if (trim(substr($v, 0, strlen('get_field_'))) == 'get_field_')
			{
				$d = '';

				$name = @$v('', 'name', '', '', 0, $d);

				$id = substr($v, strlen('get_field_'));

				if ($name != false && is_string($name))
					$fields[] = array('id' => $id,'name' => (isset($fields_vars[$name])
						? $fields_vars[$name]
						: $name));

				if (! empty($type) && $id == $type)
					$field =  array('id' => $id,'name' => (isset($fields_vars[$name])
						? $fields_vars[$name]
						: $name));
			}
			else
				continue;
		}

		$fields = msort($fields, array('name'));

		return (! empty($type))
			? $field
			: $fields;
	}


	/**
	 * Возвращаем алиас по номеру поля
	 *
	 * @param $id
	 * @return string
	 */
	function get_field_alias($id)
	{
		global $AVE_DB;

		static $alias_field_id = array();

		if (isset($alias_field_id[$id]))
			return $alias_field_id[$id];

		$alias_field_id[$id] = $AVE_DB->Query("SELECT rubric_field_alias FROM " . PREFIX . "_rubric_fields WHERE Id=".intval($id))->GetCell();

		return $alias_field_id[$id];
	}


	/**
	 * Возвращаем номер поля по рубрике и алиасу
	 *
	 * @param $rubric_id
	 * @param $alias
	 *
	 * @return string
	 */
	function get_field_num($rubric_id, $alias)
	{
		global $AVE_DB;

		static $alias_field_id = array();

		if (isset($alias_field_id[$rubric_id][$alias]))
			return $alias_field_id[$rubric_id][$alias];

		$sql = "
			SELECT Id
			FROM
				" . PREFIX . "_rubric_fields
			WHERE
				(rubric_field_alias = '".addslashes($alias)."'
				OR Id = '".intval($alias)."')
			AND
				rubric_id = ".intval($rubric_id)
		;

		$alias_field_id[$rubric_id][$alias] = $AVE_DB->Query($sql)->GetCell();

		return $alias_field_id[$rubric_id][$alias];
	}


	/**
	 * Возвращаем
	 *
	 * @param $rubric_id
	 * @param $id
	 *
	 * @return string
	 */
	function get_field_default_value($id)
	{
		global $AVE_DB;

		static $alias_field_id = array();

		if (isset($alias_field_id[$id]))
			return $alias_field_id[$id];

		$sql = "
			SELECT
				rubric_field_default
			FROM
				" . PREFIX . "_rubric_fields
			WHERE
				Id = ".intval($id)
		;

		$alias_field_id[$id] = $AVE_DB->Query($sql)->GetCell();

		return $alias_field_id[$id];
	}


	/**
	 * Возвращаем шаблон tpl или пусто
	 *
	 * @param string $dir папка шаблона
	 * @param int    $field_id идентификатор поля
	 * @param string $type тип поля
	 * @param int    $_tpl номер шаблона
	 *
	 * @return string
	 */
	function get_field_tpl($dir = '', $field_id = 0, $type = 'admin', $_tpl = null)
	{
		if (! $type)
			return false;

		$alias_field_id = get_field_alias($field_id);

		// Если существет файл с ID поля и ID шаблона
		if (file_exists($dir.'field-'.$type.'-'.$field_id.'-'.$_tpl.'.tpl'))
			$tpl = $dir.'field-'.$type.'-'.$field_id.'-'.$_tpl.'.tpl';
		// Если существет файл с аласом поля и ID шаблона
		else if (file_exists($dir.'field-'.$type.'-'.$alias_field_id.'-'.$_tpl.'.tpl'))
			$tpl = $dir.'field-'.$type.'-'.$alias_field_id.'-'.$_tpl.'.tpl';
		// Если существет файл с ID поля
		else if (file_exists($dir.'field-'.$type.'-'.$field_id.'.tpl'))
			$tpl = $dir.'field-'.$type.'-'.$field_id.'.tpl';
		// Если существет файл с алиасом поля
		else if (file_exists($dir.'field-'.$type.'-'.$alias_field_id.'.tpl'))
			$tpl = $dir.'field-'.$type.'-'.$alias_field_id.'.tpl';
		// Если существет файл c типом поля
		else if (file_exists($dir.'field-'.$type.'.tpl'))
			$tpl = $dir.'field-'.$type.'.tpl';
		// Если существет файл c ID поля
		else if (file_exists($dir.'field-'.$field_id.'.tpl'))
			$tpl = $dir.'field-'.$field_id.'.tpl';
		// Иначе
		else
			$tpl = $dir.'field.tpl';

		return $tpl;
	}


	/**
	 * Формирование поля документа в соответствии с шаблоном отображения
	 *
	 * @param int  $field_id идентификатор поля
	 * @param int  $document_id
	 *
	 * @return string
	 */
	function document_get_field($field_id, $document_id = null, $_tpl = null)
	{
		global $AVE_Core;

		if (! $_tpl && is_array($field_id))
			$_tpl = $field_id[2];

		if (is_array($field_id))
			$field_id = $field_id[1];

		$document_fields = get_document_fields(empty($document_id)
			? $AVE_Core->curentdoc->Id
			: intval($document_id));

		if (! is_array($document_fields[$field_id]))
			$field_id = intval($document_fields[$field_id]);

		if (empty($document_fields[$field_id]))
			return '';

		$field_value = trim($document_fields[$field_id]['field_value']);

		$tpl_field_empty = $document_fields[$field_id]['tpl_field_empty'];

		// if ($field_value == '' && $tpl_field_empty) return '';

		$field_type = $document_fields[$field_id]['rubric_field_type'];

		$rubric_field_template = trim($document_fields[$field_id]['rubric_field_template']);

		$rubric_field_default = $document_fields[$field_id]['rubric_field_default'];

		//	$field_value = parse_hide($field_value);
		//	$field_value = ($length != '') ? truncate_text($field_value, $length, '…', true) : $field_value;

		$func = 'get_field_' . $field_type;

		if (! is_callable($func))
			$func = 'get_field_default';

		$field_value = $func($field_value, 'doc', $field_id, $rubric_field_template, $tpl_field_empty, $maxlength, $document_fields, RUB_ID, $rubric_field_default, $_tpl);

		return $field_value;
	}


	/**
	 * Функция получения содержимого поля для обработки в шаблоне рубрики
	 *
	 * @param int $field_id	идентификатор поля, для [tag:fld:12] $field_id = 12
	 * @param int $length	необязательный параметр,
	 * 						количество возвращаемых символов содержимого поля.
	 * 						если данный параметр указать со знаком минус
	 * 						содержимое поля будет очищено от HTML-тегов.
	 * @return string
	 */
	function document_get_field_value($field_id, $length = 0)
	{
		if (! is_numeric($field_id))
			return '';

		$document_fields = get_document_fields(get_current_document_id());

		$field_value = trim($document_fields[$field_id]['field_value']);

		if ($field_value != '')
		{
			$field_value = strip_tags($field_value); // "<br /><strong><em><p><i>"

			if (is_numeric($length) && $length != 0)
			{
				if ($length < 0)
				{
					$field_value = strip_tags($field_value);
					$field_value = preg_replace('/  +/', ' ', $field_value);
					$field_value = trim($field_value);
					$length = abs($length);
				}

				$field_value = truncate_text($field_value, $length, '…', true);
			}
		}

		return $field_value;
	}


	/**
	 * Возвращаем истинное значение поля для документа
	 *
	 * @param int    $document_id id документа
	 * @param string $field       id поля или его алиас
	 *
	 * @return string
	 */
	function get_document_field($document_id, $field)
	{
		$document_fields = get_document_fields($document_id);

		if (! is_array($document_fields[$field]))
			$field = intval($document_fields[$field]);

		if (empty($document_fields[$field]))
			return false;

		$field_value = $document_fields[$field]['field_value'];

		return $field_value;
	}


	/**
	 * Функция возвращает массив со значениями полей
	 *
	 * @param       $document_id
	 * @param       array $values если надо вернуть документ с произвольными значениями - используется для ревизий документов
	 * @internal    param int $id id документа
	 * @return      mixed
	 */
	function get_document_fields($document_id, $values = null)
	{
		global $AVE_DB, $request_documents, $AVE_Core;

		static $document_fields = array();

		if (! is_numeric($document_id))
			return false;

		if (! isset($document_fields[$document_id]))
		{
			$document_fields[$document_id] = false;

			$where = "WHERE doc_field.document_id = '" . $document_id . "'";

			$query = "
				SELECT
					# DOC FIELDS = $document_id
					doc.document_author_id,
					doc_field.Id,
					doc_field.document_id,
					doc_field.rubric_field_id,
					doc_field.field_value,
					text_field.field_value as field_value_more,
					rub_field.rubric_field_alias,
					rub_field.rubric_field_type,
					rub_field.rubric_field_default,
					rub_field.rubric_field_title,
					rub_field.rubric_field_template,
					rub_field.rubric_field_template_request
				FROM
					" . PREFIX . "_document_fields AS doc_field
				JOIN
					" . PREFIX . "_rubric_fields AS rub_field
						ON doc_field.rubric_field_id = rub_field.Id
				LEFT JOIN
					" . PREFIX . "_document_fields_text AS text_field
					ON (doc_field.rubric_field_id = text_field.rubric_field_id AND doc_field.document_id = text_field.document_id)
				JOIN
					" . PREFIX . "_documents AS doc
					ON doc.Id = doc_field.document_id
				" . $where;

			$cache_id = (int)$AVE_Core->curentdoc->Id;
			$cache_id = 'documents/fields/' . (floor($cache_id / 1000)) . '/' . $cache_id;

			$cache_file = md5($query) . '.fields';

			$cache_dir = BASE_DIR . '/tmp/cache/sql/' . (trim($cache_id) > ''
				? trim($cache_id) . '/'
				: substr($cache_file, 0, 2) . '/' . substr($cache_file, 2, 2) . '/' . substr($cache_file, 4, 2) . '/');

			// Наличие файла
			if (file_exists($cache_dir . $cache_file))
			{
				// Получаем время создания файла
				$file_time = filemtime($cache_dir . $cache_file);

				// Получаем время для проверки
				$cache_time = $AVE_Core->curentdoc->rubric_changed_fields;

				// Сравниваем временные метки
				if (! $cache_time || $cache_time > $file_time)
					unlink ($cache_dir . $cache_file);
			}

			// Безусловный кеш
			$sql = $AVE_DB->Query($query, -1, 'fld_' . $document_id, true, '.fields');

			// Вдруг памяти мало!!!!
			if (memory_panic() && (count($document_fields) > 3))
				$document_fields = array();

			while ($row = $sql->FetchAssocArray())
			{
				$row['tpl_req_empty'] = (trim($row['rubric_field_template_request']) == '');
				$row['tpl_field_empty'] = (trim($row['rubric_field_template']) == '');

				$row['field_value']=(string)$row['field_value'].(string)$row['field_value_more'];

				if($values)
				{
					$row['field_value']=(isset($values[$row['rubric_field_id']]) ? $values[$row['rubric_field_id']] : $row['field_value']);
				}

				if ($row['field_value'] === '')
				{
					$row['rubric_field_template_request'] = preg_replace('/\[tag:if_notempty](.*?)\[\/tag:if_notempty]/si', '', $row['rubric_field_template_request']);
					$row['rubric_field_template_request'] = trim(str_replace(array('[tag:if_empty]','[/tag:if_empty]'), '', $row['rubric_field_template_request']));

					$row['rubric_field_template'] = preg_replace('/\[tag:if_notempty](.*?)\[\/tag:if_notempty]/si', '', $row['rubric_field_template']);
					$row['rubric_field_template'] = trim(str_replace(array('[tag:if_empty]','[/tag:if_empty]'), '', $row['rubric_field_template']));
				}
				else
					{
						$row['rubric_field_template_request'] = preg_replace('/\[tag:if_empty](.*?)\[\/tag:if_empty]/si', '', $row['rubric_field_template_request']);
						$row['rubric_field_template_request'] = trim(str_replace(array('[tag:if_notempty]','[/tag:if_notempty]'), '', $row['rubric_field_template_request']));

						$row['rubric_field_template'] = preg_replace('/\[tag:if_empty](.*?)\[\/tag:if_empty]/si', '', $row['rubric_field_template']);
						$row['rubric_field_template'] = trim(str_replace(array('[tag:if_notempty]','[/tag:if_notempty]'), '', $row['rubric_field_template']));
					}

				$document_fields[$row['document_id']][$row['rubric_field_id']] = $row;
				$document_fields[$row['document_id']][$row['rubric_field_alias']] = $row['rubric_field_id'];
			}
		}

		return $document_fields[$document_id];
	}


	/**
	 * Возвращает содержимое поля документа по номеру
	 *
	 * @param int  $field_id ([tag:fld:X]) - номер поля
	 * @param int  $doc_id
	 * @param int  $parametr ([tag:parametr:X]) - часть поля
	 *
	 * @return string
	 */
	function get_field($field_id, $doc_id = null, $parametr = null)
	{
		global $req_item_id;

		//-- Если не передан $doc_id, то проверяем реквест
		if (! $doc_id && $req_item_id)
			$doc_id = $req_item_id;
		//-- Или берём для текущего дока
		elseif (! $doc_id && $_REQUEST['id'] > 0)
			$doc_id = $_REQUEST['id'];
		//-- Возвращаем FALSE, если не число
		elseif (! is_numeric($doc_id))
			return false;

		//-- Забираем из базы массив полей
		$field = get_document_field($doc_id, $field_id);

		//-- Возвращаем нужную часть поля
		if ($parametr !==  null)
		{
			$field = explode("|", $field);
			$field = array_values(array_diff($field, array('')));
			$field = $field[$parametr];
		}

		return $field;
	}


	/**
	 * Возвращает содержимое поля документа по номеру
	 *
	 * @param int  $field_id ([tag:fld:X]) - номер поля
	 * @param int  $doc_id
	 * @param int  $parametr ([tag:parametr:X]) - часть поля
	 *
	 * @return mixed
	 */
	function get_true_field($field_id, $doc_id = null, $parametr = null)
	{
		global $req_item_id, $AVE_DB;

		//-- Если не передан $doc_id, то проверяем реквест
		if (! $doc_id && $req_item_id)
			$doc_id = $req_item_id;
		//-- Или берём для текущего дока
		elseif (! $doc_id && $_REQUEST['id'] > 0)
			$doc_id = $_REQUEST['id'];
		//-- Возвращаем FALSE, если не число
		elseif (! is_numeric($doc_id))
			return false;

		//-- Забираем поле из базы
		$sql = "
			SELECT
				doc_field.field_value,
				text_field.field_value AS field_value_more
			FROM
				" . PREFIX . "_document_fields AS doc_field
			LEFT JOIN
				" . PREFIX . "_document_fields_text AS text_field
				ON (doc_field.rubric_field_id = text_field.rubric_field_id AND doc_field.document_id = text_field.document_id)
			WHERE
				doc_field.document_id = '" . $doc_id . "'
			AND
				doc_field.rubric_field_id = '" . $field_id . "'
		";

		$query = $AVE_DB->Query($sql)->FetchRow();

		$field = (string)$query->field_value . (string)$query->field_value_more;

		unset ($sql, $query);

		//-- Возвращаем нужную часть поля
		if ($parametr !==  null)
		{
			$field = explode("|", $field);
			$field = array_values(array_diff($field, array('')));
			$field = $field[$parametr];
		}

		return $field;
	}


	/**
	 * Возвращает элемент сериализованного поля по номеру и ключу
	 *
	 * @param int $field_id	([tag:fld:X]) - номер поля
	 * @param int $item_id - номер элемента
	 * @param int $doc_id	([tag:docid]) - id документа
	 * @param int $parametr	([tag:parametr:X]) - номер параметра элемента
	 * @return string
	 */
	function get_element($field_id, $item_id = 0, $parametr = null, $doc_id = null)
	{
		global $req_item_id;

		//-- Если не передан $doc_id, то проверяем реквест
		if (! $doc_id && $req_item_id)
			$doc_id = $req_item_id;
		//-- Или берём для текущего дока
		elseif (! $doc_id && $_REQUEST['id'] > 0)
			$doc_id = $_REQUEST['id'];
		//-- Возвращаем FALSE, если не число
		elseif (! is_numeric($doc_id))
			return false;

		//-- Забираем из базы поле
		$field = get_field($field_id, $doc_id);
		$field = unserialize($field);

		// возвращаем нужную часть поля
		if ($parametr !==  null)
		{
			$field = $field[$item_id];
			$field = explode("|", $field);
			$field = $field[$parametr];
		}
		else
			{
				$field = $field[$item_id];
				$field = explode("|", $field);
				$field = $field[0];
			}

		return $field;
	}


	/**
	 * Возвращает сериализованны(й|е) элемент(ы) поля
	 *
	 * @param int $field_id	([tag:fld:X]) - номер поля
	 * @param int $item_id - номер элемента
	 * @param int $doc_id	([tag:docid]) - id документа
	 * @return mixed
	 */
	function get_serialize($field_id, $item_id = null, $doc_id = null)
	{
		global $req_item_id;

		//-- Если не передан $doc_id, то проверяем реквест
		if (! $doc_id && $req_item_id)
			$doc_id = $req_item_id;
		//-- Или берём для текущего дока
		elseif (! $doc_id && $_REQUEST['id'] > 0)
			$doc_id = $_REQUEST['id'];
		//-- Возвращаем FALSE, если не число
		elseif (! is_numeric($doc_id))
			return false;

		//-- Забираем поле
		$field = get_field($field_id, $doc_id);
		$field = unserialize($field);

		$field_data = array();

		//-- Если получили массив из данных, собираем новый
		if (! empty($field))
			foreach ($field AS $k => $v)
				$field_data[$k] = explode('|', $v);
		//-- Иначе возвращаем FALSE
		else
			return false;

		unset($field);

		//-- Если пришло $item_id
		if (is_numeric($item_id))
			return $field_data[$item_id];
		else
			return $field_data;
	}


	/**
	 * Возвращает элемент сериализованного поля по номеру и ключу, через тег [tag:fld:XXX][XXX][XXX]
	 *
	 * @return string
	 */
	function get_field_element()
	{
		$param = func_get_args();

		// Field ID
		$param_1 = isset($param[0]) ? $param[0] : null;
		// Item ID
		$param_2 = isset($param[1]) ? $param[1] : null;
		// Param ID
		$param_3 = isset($param[2]) ? $param[2] : null;
		// Document ID
		$param_4 = isset($param[3]) ? $param[3] : null;

		$return = get_element($param_1, $param_2, $param_3, $param_4);

		return $return;
	}


	/**
	 * Возвращает наименование поля документа по номеру
	 *
	 * @param int  $field_id ([tag:fld:X]) - номер поля
	 * @param int  $doc_id
	 * @param int  $parametr ([tag:parametr:X]) - часть поля
	 *
	 * @return string
	 */
	function get_field_name($field_id, $doc_id = null)
	{
		global $req_item_id;

		//-- Если не передан $doc_id, то проверяем реквест
		if (! $doc_id && $req_item_id)
			$doc_id = $req_item_id;
		//-- Или берём для текущего дока
		elseif (! $doc_id && $_REQUEST['id'] > 0)
			$doc_id = $_REQUEST['id'];
		//-- Возвращаем FALSE, если не число
		elseif (! is_numeric($doc_id))
			return false;

		$document_fields = get_document_fields($doc_id);

		if (empty($document_fields[$field_id]))
			return false;

		$field_name = $document_fields[$field_id]['rubric_field_title'];

		return $field_name;
	}
?>