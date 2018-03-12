<?

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

// Чекбокс (Checkbox)
function get_field_checkbox($field_value, $action, $field_id = 0, $tpl = '', $tpl_empty = 0, &$maxlength = null, $document_fields = array(), $rubric_id = 0, $default = null)
{

	global $AVE_Template;

	$fld_dir  = dirname(__FILE__) . '/';
	$tpl_dir  = $fld_dir . 'tpl/';

	$lang_file = $fld_dir . 'lang/' . (defined('ACP')
		? $_SESSION['admin_language']
		: $_SESSION['user_language']) . '.txt';

	$AVE_Template->config_load($lang_file, 'lang');
	$AVE_Template->assign('config_vars', $AVE_Template->get_config_vars());
	$AVE_Template->config_load($lang_file, 'admin');

	$res = '';

	switch ($action)
	{
		case 'edit':
			$AVE_Template->assign('field_id', $field_id);
			$AVE_Template->assign('field_value', (int)$field_value);
			$AVE_Template->assign('doc_id', (isset($_REQUEST['Id']) ? (int)$_REQUEST['Id'] : 0));

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'admin');

			return $AVE_Template->fetch($tpl_file);
			break;

		case 'doc':
			$field_value = clean_php($field_value);

			$res = ((int)$field_value === 1)
				? (int)$field_value
				: 0;

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'doc');

			if ($tpl_empty && $tpl_file)
			{
				$AVE_Template->assign('field_id', $field_id);
				$AVE_Template->assign('field_value', $field_value);
				return $AVE_Template->fetch($tpl_file);
			}

			return $res;
			break;

		case 'req':
			$field_value = clean_php($field_value);

			$res = ((int)$field_value === 1)
				? (int)$field_value
				: 0;

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'req');

			if ($tpl_empty && $tpl_file)
			{
				$AVE_Template->assign('field_id', $field_id);
				$AVE_Template->assign('field_value', $field_value);
				return $AVE_Template->fetch($tpl_file);
			}

			return $res;
			break;

		case 'save':
			$field_value = clean_php($field_value);
			$res = ((int)$field_value === 1)
				? $field_value
				: '0';
			break;

		case 'name':
			return $AVE_Template->get_config_vars('name');
			break;
	}

	return ($res ? $res : $field_value);
}
?>
