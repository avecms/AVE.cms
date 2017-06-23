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
function get_field_code ($field_value, $action, $field_id=0, $tpl='', $tpl_empty=0, &$maxlength='', $document_fields=array(), $rubric_id=0, $default='')
{
	global $AVE_Template;

	$fld_dir  = dirname(__FILE__) . '/';
	$tpl_dir  = $fld_dir . 'tpl/';

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
			$AVE_Template->assign('f_id', $field_id.'_'.$_REQUEST['Id']);

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'admin');

			return $AVE_Template->fetch($tpl_file);
			break;

		case 'doc':
		case 'req':
			return get_field_default($field_value, $action, $field_id, $tpl, $tpl_empty);
			break;

		case 'name':
			return $AVE_Template->get_config_vars('name');
			break;

		default: return $field_value;
	}
}
?>