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

	// Код (Codemirror)
	function get_field_code ($field_value, $action, $field_id=0, $tpl='', $tpl_empty=0, &$maxlength='', $document_fields=array(), $rubric_id=0, $default='', $_tpl=null)
	{
		global $AVE_Template;

		$fld_dir = dirname(__FILE__) . '/';
		$tpl_dir = $fld_dir . 'tpl/';

		$lang_file = $fld_dir . 'lang/' . (defined('ACP') ? $_SESSION['admin_language'] : $_SESSION['user_language']) . '.txt';

		$AVE_Template->config_load($lang_file, 'lang');
		$AVE_Template->assign('config_vars', $AVE_Template->get_config_vars());
		$AVE_Template->config_load($lang_file, 'admin');

		switch ($action)
		{
			case 'edit':
				$AVE_Template->assign('field_id', $field_id);
				$AVE_Template->assign('field_value', $field_value);
				$AVE_Template->assign('doc_id', (int)$_REQUEST['Id']);
				$AVE_Template->assign('rubric_id', $rubric_id);
				$AVE_Template->assign('f_id', $field_id.'_'.(int)$_REQUEST['Id']);

				$tpl_file = get_field_tpl($tpl_dir, $field_id, 'admin', $_tpl);

				return $AVE_Template->fetch($tpl_file);
				break;

			case 'doc':
					$AVE_Template->config_load($lang_file, 'public');

					if (! $tpl_empty)
					{
						$field_param = explode('|', $field_value);
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
						$AVE_Template->assign('rubric_id', $rubric_id);

						return $AVE_Template->fetch($tpl_file);
					}

					return $field_value;
					break;

			case 'req':

				$AVE_Template->config_load($lang_file, 'public');

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

				$tpl_file = get_field_tpl($tpl_dir, $field_id, 'req', $_tpl);

				if ($tpl_empty && $tpl_file)
				{
					$AVE_Template->assign('field_id', $field_id);
					$AVE_Template->assign('field_default', $default);
					$AVE_Template->assign('field_value', $field_value);
					$AVE_Template->assign('rubric_id', $rubric_id);

					return $AVE_Template->fetch($tpl_file);
				}

				return $field_value;
				break;

			case 'name':
				return $AVE_Template->get_config_vars('name');
				break;

			default: return $field_value;
		}
	}
?>