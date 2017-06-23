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

// Выпадающий список (Ключ)
function get_field_drop_down_key($field_value, $action, $field_id=0, $tpl='', $tpl_empty=0, &$maxlength=null, $document_fields=array(), $rubric_id=0, $default=null)
{
	global $AVE_Template;

	$fld_dir  = dirname(__FILE__) . '/';
	$tpl_dir  = $fld_dir . 'tpl/';

	$lang_file = $fld_dir . 'lang/' . (defined('ACP') ? $_SESSION['admin_language'] : $_SESSION['user_language']) . '.txt';

	$AVE_Template->config_load($lang_file, 'lang');
	$AVE_Template->assign('config_vars', $AVE_Template->get_config_vars());
	$AVE_Template->config_load($lang_file, 'admin');

	$res = 0;

	switch ($action)
	{
		case 'edit':
			$items = explode(',', $default);
			$items = array_diff($items, array(''));

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'admin');

			$AVE_Template->assign('items', $items);
			$AVE_Template->assign('field_id', $field_id);
			$AVE_Template->assign('field_value', trim($field_value));

			return $AVE_Template->fetch($tpl_file);
			break;

		case 'doc':
		case 'req':
			@$field_value = clean_php($field_value);

			if (!$tpl_empty)
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
			else
			{
				$items = explode(',', $default);
				$items = array_diff($items, array(''));
				$field_value = $items[$field_value];
			}

			return $field_value = ($field_value != '') ? $field_value : '';
			break;

		case 'name':
			return $AVE_Template->get_config_vars('name');
			break;

	}
	return ($res ? $res : $field_value);
}
?>
