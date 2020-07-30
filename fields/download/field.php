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

	// Загрузить файл
	function get_field_download($field_value, $action, $field_id=0, $tpl='', $tpl_empty=0, &$maxlength=null, $document_fields=array(), $rubric_id=0, $default=null){

		global $AVE_Template;

		$fld_dir  = dirname(__FILE__) . '/';
		$tpl_dir  = $fld_dir . 'tpl/';

		$lang_file = $fld_dir . 'lang/' . (defined('ACP') ? $_SESSION['admin_language'] : $_SESSION['user_language']) . '.txt';

		$AVE_Template->config_load($lang_file, 'lang');
		$AVE_Template->assign('config_vars', $AVE_Template->get_config_vars());
		$AVE_Template->config_load($lang_file, 'admin');

		$res=0;

		switch ($action)
		{
			case 'edit':
				$field_value = !empty($field_value) ? htmlspecialchars($field_value, ENT_QUOTES) : '';

				$AVE_Template->assign('field_id', $field_id);
				$AVE_Template->assign('field_value', $field_value);

				$tpl_file = get_field_tpl($tpl_dir, $field_id, 'admin');

				return $AVE_Template->fetch($tpl_file);
				break;

			case 'doc':
				$field_value = clean_php($field_value);
				$field_param = explode('|', $field_value);
				if ($tpl_empty)
				{
					$field_value = (!empty($field_param[1]) ? $field_param[1] . '<br />' : '')
						. '<form method="get" target="_blank" action="' . $field_param[0]
						. '"><input class="basicBtn" type="submit" value="Скачать" /></form>';
				}
				else
				{
					$field_value = preg_replace_callback(
						'/\[tag:parametr:(\d+)\]/i',
						function($data) use($field_param)
						{
							return $field_param[(int)$data[1]];
						},
						$tpl
					);
				}
				$res = $field_value;
				break;

			case 'req':
				$res=get_field_default($field_value,$action,$field_id,$tpl,$tpl_empty,$maxlength,$document_fields,$rubric_id);
				break;

			case 'api' :
				return htmlspecialchars_decode($field_value, ENT_QUOTES);
				break;

			case 'name' :
				return $AVE_Template->get_config_vars('name');
				break;
		}

		return ($res ? $res : $field_value);
	}
?>