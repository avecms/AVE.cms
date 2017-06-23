<?
/**
 * AVE.cms
 *
 * @package AVE.cms
 * @version 3.x
 * @filesource
 * @copyright © 2007-2016 AVE.cms, http://www.ave-cms.ru
 *
 * @license GPL v.2
 *
 * @param $field_value
 * @param $action
 * @param int $field_id
 * @param string $tpl
 * @param int $tpl_empty
 * @param null $maxlength
 * @param array $document_fields
 * @param int $rubric_id
 * @param null $default
 * @return array|int|mixed|string
 */

// Текст в изображение
function get_field_text_to_image ($field_value, $action, $field_id = 0, $tpl = '', $tpl_empty = 0, &$maxlength = null, $document_fields = array(), $rubric_id = 0, $default = null)
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
		// Отображение поля в административной части
		case 'edit':
			$AVE_Template->assign('field_dir', $fld_name);
			$AVE_Template->assign('field_id', $field_id);
			$AVE_Template->assign('field_value', $field_value);

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'admin');

			return $AVE_Template->fetch($tpl_file);
			break;

        // Отображение поля в документах
		case 'doc':

			$AVE_Template->config_load($lang_file, 'public');

			if ($tpl_empty)
			{
				$field_value = clean_php($field_value);
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

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'doc');

			if($tpl_empty && $tpl_file){
				$AVE_Template->assign('field_value', $field_value);
				return $AVE_Template->fetch($tpl_file);
			}
			return $field_value;
			break;

        // Отображение поля в запросах
		case 'req':

			$AVE_Template->config_load($lang_file, 'public');

			if ($tpl_empty)
			{
				$field_value = clean_php($field_value);
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

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'req');

			if($tpl_empty && $tpl_file){
				$AVE_Template->assign('field_value', $field_value);
				return $AVE_Template->fetch($tpl_file);
			}
			return $field_value;
			break;

        // Сохранение поля в административной части
		case 'save':
			return $field_value;
        // Тип | Имя поля в административной части
		case 'name':
			return $AVE_Template->get_config_vars('name');

		default: return $field_value;
	}
}

?>