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

// Дата (TimeStamp)
function get_field_date($field_value, $action, $field_id=0, $tpl='', $tpl_empty=0, &$maxlength=null, $document_fields=array(), $rubric_id=0, $default=null){

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
			$field_value = ($field_value != 0) ? $field_value : '';

			$AVE_Template->assign('field_id', $field_id);
			$AVE_Template->assign('field_value', $field_value);
			$AVE_Template->assign('doc_id', (int)$_REQUEST['Id']);

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'admin');

			return $AVE_Template->fetch($tpl_file);
			break;

		case 'doc':
			$field_value = clean_php($field_value);
			if ($tpl_empty)
			{
				$value = pretty_date(strftime(TIME_FORMAT, $field_value));
			}
			else
			{
				$value = preg_replace_callback(
					'/\[tag:parametr:(\d+)\]/i',
					function($data) use($field_value)
					{
						return $field_value;
					},
					$tpl
				);

				return $res = $value;
			}

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'doc');

			if($tpl_empty && $tpl_file){
				$AVE_Template->assign('field_value', $field_value);
				return $AVE_Template->fetch($tpl_file);
			}

			$res = $field_value;
			break;

		case 'req':

			$field_value = clean_php($field_value);
			if ($tpl_empty)
			{
				$value = pretty_date(strftime(TIME_FORMAT, $field_value));
			}
			else
			{
				$value = preg_replace_callback(
					'/\[tag:parametr:(\d+)\]/i',
					function($data) use($field_value)
					{
						return $field_value;
					},
					$tpl
				);

				return $res = $value;
			}

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'req');

			if($tpl_empty && $tpl_file){
				$AVE_Template->assign('field_value', $field_value);
				return $AVE_Template->fetch($tpl_file);
			}

			$res = $field_value;
			break;

		case 'save':
			$res = $field_value;
			break;


		case 'name':
			return $AVE_Template->get_config_vars('name');
			break;

	}
	return ($res ? $res : $field_value);
}
?>
