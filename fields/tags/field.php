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

	// Tags
	function get_field_tags ($field_value, $action, $field_id = 0, $tpl = '', $tpl_empty = 0, &$maxlength = null, $document_fields = array(), $rubric_id = 0, $default = null, $_tpl = null)
	{
		global $AVE_DB, $AVE_Template;

		$fld_dir  = dirname(__FILE__) . '/';
		$tpl_dir  = $fld_dir . 'tpl/';
		$fld_name = basename($fld_dir);

		$rubric_id = $rubric_id > 0
			? $rubric_id
			: $_REQUEST['rubric_id']
				? (int)$_REQUEST['rubric_id']
				: $AVE_DB->Query("SELECT rubric_id FROM ".PREFIX."_documents WHERE Id = '".$_REQUEST['Id']."'")->GetCell();

		$lang_file = $fld_dir . 'lang/' . (defined('ACP')
			? $_SESSION['admin_language']
			: $_SESSION['user_language']) . '.txt';

		$AVE_Template->config_load($lang_file, 'lang');
		$AVE_Template->assign('config_vars', $AVE_Template->get_config_vars());
		$AVE_Template->config_load($lang_file, 'admin');

		switch ($action)
		{
			case 'edit':
				$sql = "
					SELECT DISTINCT
						tag
					FROM
						".PREFIX."_document_tags
					WHERE
						rubric_id = '".$rubric_id."'
					ORDER BY tag ASC
				";

				$query = $AVE_DB->Query($sql);

				$field_tags = array();

				while ($row = $query->GetCell())
					array_push($field_tags, $row);

				$field_value = explode('|', $field_value);
				$field_value = array_diff($field_value, array(''));

				$total = count($field_tags);

				$field_points = array(ceil($total/4), 2*ceil($total/4), 3*ceil($total/4));

				$AVE_Template->assign('field_points', $field_points);
				$AVE_Template->assign('field_tags', $field_tags);
				$AVE_Template->assign('field_dir', $fld_name);
				$AVE_Template->assign('field_id', $field_id);
				$AVE_Template->assign('field_value', $field_value);

				$tpl_file = get_field_tpl($tpl_dir, $field_id, 'admin', $_tpl);

				return $AVE_Template->fetch($tpl_file);
				break;

			case 'doc':

				$AVE_Template->config_load($lang_file, 'public');

				if ($tpl_empty)
				{
					$field_value = explode('|', $field_value);
					$field_value = array_diff($field_value, array(''));
					$field_value = array_values($field_value);
				}
				else
				{
					$field_param = explode('|', $field_value);
					$field_param = array_diff($field_param, array(''));
					$field_param = array_values($field_param);
					$field_value = preg_replace_callback(
						'/\[tag:parametr:(\d+)\]/i',
						function ($data) use ($field_param)
						{
							return $field_param[(int)$data[1]];
						},
						$tpl
					);
				}

				$tpl_file = get_field_tpl($tpl_dir, $field_id, 'doc', $_tpl);

				if ($tpl_empty && $tpl_file)
				{
					$AVE_Template->assign('field_id', $field_id);
					$AVE_Template->assign('field_default', $default);
					$AVE_Template->assign('field_value', $field_value);

					return $AVE_Template->fetch($tpl_file);
				}

				return $field_value;
				break;

			case 'req':

				$AVE_Template->config_load($lang_file, 'public');

				if ($tpl_empty)
				{
					$field_value = explode('|', $field_value);
					$field_value = array_diff($field_value, array(''));
					$field_value = array_values($field_value);
				}
				else
				{
					$field_param = explode('|', $field_value);
					$field_param = array_diff($field_param, array(''));
					$field_param = array_values($field_param);
					$field_value = preg_replace_callback(
						'/\[tag:parametr:(\d+)\]/i',
						function($data) use($field_param)
						{
							return $field_param[(int)$data[1]];
						},
						$tpl
					);
				}

				$tpl_file = get_field_tpl($tpl_dir, $field_id, 'req', $_tpl);

				if ($tpl_empty && $tpl_file)
				{
					$AVE_Template->assign('field_id', $field_id);
					$AVE_Template->assign('field_default', $default);
					$AVE_Template->assign('field_value', $field_value);

					return $AVE_Template->fetch($tpl_file);
				}

				return $field_value;
				break;

			case 'save':
				// Регистрируем хук
				Hooks::register('DocumentAfterSave', 'afterTagsSave', 10);

				$field_value = tagsValue($field_value);

				if (! empty($field_value))
					$field_value = '|' . implode('|', $field_value) . '|';

				return $field_value;

			case 'name':
				return $AVE_Template->get_config_vars('name');

			default:
				return $field_value;
		}
	}

	if (! function_exists('tagsValue'))
	{
		function tagsValue ($field_value)
		{
			// Если есть выделенные теги
			if (! empty($field_value['tags']))
				$tags = $field_value['tags'];
			else
				$tags = array();

			unset ($tags['other']);

			// Если есть теги через зяпятую
			if (! empty($field_value['tags']['other']))
			{
				$tags_new = explode(',', $field_value['tags']['other']);
				$tags_new = array_map('trim', $tags_new);
			}
			else
				$tags_new = array();

			// Совмещаем массивы
			$tags = array_merge($tags, $tags_new);

			// Делаем уникальные значения
			$field_value = array_unique($tags);

			return $field_value;
		}
	}

	if (! function_exists('afterTagsSave'))
	{
		function afterTagsSave ($data)
		{
			global $AVE_Document;

			foreach ($data['data']['feld'] AS $_k => $_v)
			{
				if (array_key_exists('tags', $_v))
				{
					$tags = tagsValue($_v);

					if (! empty($tags))
					{
						$tags = implode(',', $tags);
						$AVE_Document->saveTags($data['document_id'], $data['rubric_id'], $tags);
					}
				}
			}
		}
	}
?>