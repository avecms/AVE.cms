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

// Ссылка
function get_field_link($field_value, $action, $field_id=0, $tpl='', $tpl_empty=0, &$maxlength=null, $document_fields=array(), $rubric_id=0, $default=null){

	global $AVE_Template;

	$fld_dir  = dirname(__FILE__) . '/';
	$tpl_dir  = $fld_dir . 'tpl/';

	$lang_file = $fld_dir . 'lang/' . (defined('ACP') ? $_SESSION['admin_language'] : $_SESSION['user_language']) . '.txt';

	$AVE_Template->config_load($lang_file, 'lang');
	$AVE_Template->assign('config_vars', $AVE_Template->get_config_vars());
	$AVE_Template->config_load($lang_file, 'admin');

	$res = '';

	switch ($action)
	{
		case 'edit':
			$AVE_Template->assign('field_id', $field_id);
			$AVE_Template->assign('field_value', $field_value);

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'admin');

			return $AVE_Template->fetch($tpl_file);
			break;

		case 'doc':
			$field_value = clean_php($field_value);
			$field_param = explode('|', $field_value);
			$field_param[1] = empty($field_param[1]) ? $field_param[0] : $field_param[1];

			if ($tpl_empty)
			{
				$field_value = ' <a target="_self" href="' . ABS_PATH . $field_param[0] . '">' . $field_param[1] . '</a>';
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
			$field_value = clean_php($field_value);
			$field_param = explode('|', $field_value);

			if (empty($field_param[1]))
				$field_param[1] = $field_param[0];

			if (isset($document_fields[$rubric_id]['tpl_req_empty']))
			{
				$field_value = " <a target=\"_self\" href=\"" . ABS_PATH . $field_param[0] . "\">" . $field_param[1] . "</a>";
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

			$maxlength = null;

			$res = $field_value;
			break;

		case 'name' :
			return $AVE_Template->get_config_vars('name');
			break;

	}
	return ($res ? $res : $field_value);
}
?>