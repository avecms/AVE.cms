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

	// Однострочное числовое
	function get_field_single_line_numeric_two ($field_value, $action, $field_id = 0, $tpl = '', $tpl_empty = 0, &$maxlength = null, $document_fields = array(), $rubric_id = 0, $default = null, $_tpl=null)
	{
		global $AVE_Template;

		$fld_dir  = dirname(__FILE__) . '/';
		$tpl_dir  = $fld_dir . 'tpl/';
		$fld_name = basename($fld_dir);

		$lang_file = $fld_dir . 'lang/' . (defined('ACP') ? $_SESSION['admin_language'] : $_SESSION['user_language']) . '.txt';

		$AVE_Template->config_load($lang_file, 'lang');
		$AVE_Template->assign('config_vars', $AVE_Template->get_config_vars());
		$AVE_Template->config_load($lang_file, 'admin');

		switch ($action)
		{
			case 'edit':
				if (! empty($field_value))
					$field_value = explode('|', $field_value);

				$AVE_Template->assign('field_dir', $fld_name);
				$AVE_Template->assign('field_id', $field_id);
				$AVE_Template->assign('field_value', $field_value);

				$tpl_file = get_field_tpl($tpl_dir, $field_id, 'admin', $_tpl);

				return $AVE_Template->fetch($tpl_file);
				break;

			case 'doc':

				if ($tpl_empty)
				{
					$value = array();

					if (! empty($field_value))
					{
						$value = array_diff(explode('|', $field_value), array(''));
						$value = array_map('clean_php', $value);
					}
				}
				else
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

				$tpl_file = get_field_tpl($tpl_dir, $field_id, 'doc', $_tpl);

				if ($tpl_empty && $tpl_file)
				{
					$AVE_Template->assign('field_value', $value);
					return $AVE_Template->fetch($tpl_file);
				}

				return $field_value;
				break;

			case 'req':
				if ($tpl_empty)
				{
					$value = array();

					if (! empty($field_value))
					{
						$value = explode('|', $field_value);
						$value = array_map('clean_php', $value);
					}
				}
				else
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

				$tpl_file = get_field_tpl($tpl_dir, $field_id, 'req', $_tpl);

				if ($tpl_empty && $tpl_file)
				{
					$AVE_Template->assign('field_value', $value);
					return $AVE_Template->fetch($tpl_file);
				}

				return $field_value;
				break;

			case 'save':
				$save = array();

				if (is_array($field_value))
				{
					foreach ($field_value AS $k => $v)
					{
						$save[] = preg_replace('/[^\d.]/', '', $v);
					}
				}

				return empty($save) ? '' : implode('|', $save);

			case 'name':
				return $AVE_Template->get_config_vars('name');

			default: return $field_value;
		}
	}
?>