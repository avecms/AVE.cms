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

// Изображение
function get_field_image_single($field_value, $action, $field_id=0, $tpl='', $tpl_empty=0, &$maxlength=null, $document_fields=array(), $rubric_id=0, $default=null){

	global $AVE_Template;

	$fld_dir  = dirname(__FILE__) . '/';
	$tpl_dir  = $fld_dir . 'tpl/';
	$fld_name = basename($fld_dir);

	$lang_file = $fld_dir . 'lang/' . (defined('ACP') ? $_SESSION['admin_language'] : $_SESSION['user_language']) . '.txt';

	$AVE_Template->config_load($lang_file, 'lang');
	$AVE_Template->assign('config_vars', $AVE_Template->get_config_vars());
	$AVE_Template->config_load($lang_file, 'admin');

	$res = 0;

	switch ($action)
	{
		case 'edit':
			$blanc = '/uploads/images/noimage.gif';
			$image = explode('|', $field_value);
			$img = $image[0];
			unset($image[0]);
			$image = array($img, implode('|', $image));
			$field = (!empty($image[0]) ? '../' . make_thumbnail(array('link' => $image[0], 'size' => 'f128x128')) : make_thumbnail(array('link' => $blanc, 'size' => 'f128x128')));

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'admin');

			$AVE_Template->assign('field_dir', $fld_name);
			$AVE_Template->assign('image', $image);
			$AVE_Template->assign('doc_id', (int)$_REQUEST['Id']);
			$AVE_Template->assign('field', $field);
			$AVE_Template->assign('field_id', $field_id);
			$AVE_Template->assign('field_value', $field_value);

			return $AVE_Template->fetch($tpl_file);
			break;

		case 'doc':
			$field_value = clean_php($field_value);

			$field_param = explode('|', $field_value);

			if ($tpl_empty)
			{
				$field_value = '<img alt="' . (isset($field_param[1]) ? $field_param[1] : '')
					. '" src="' . $field_param[0] . '" border="0" />';
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

				$field_value = preg_replace_callback(
					'/\[tag:watermark:(.+?):([a-zA-Z]+):([0-9]+)\]/',
						create_function(
							'$m',
							'return watermarks(\'$m[1]\', \'$m[2]\', $m[3]);'
						),
					$field_value
				);

				$field_value = preg_replace_callback('/\[tag:([r|c|f|t|s]\d+x\d+r*):(.+?)]/', 'callback_make_thumbnail', $field_value);
			}

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'doc');

			if($tpl_empty && $tpl_file)
			{
				$AVE_Template->assign('image', $field_param);
				return $AVE_Template->fetch($tpl_file);
			}

			return $field_value;
			break;

		case 'req':
			$field_value = clean_php($field_value);

			$field_param = explode('|', $field_value);

			if ($tpl_empty)
			{
				$field_param[1] = isset($field_param[1]) ? $field_param[1] : '';
				$field_value = '<img src="' . $field_param[0] . '" alt="' . $field_param[1] . '" border="0" />';
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

				$field_value = preg_replace_callback(
					'/\[tag:watermark:(.+?):([a-zA-Z]+):([0-9]+)\]/',
						create_function(
							'$m',
							'return watermarks(\'$m[1]\', \'$m[2]\', $m[3]);'
						),
					$field_value
				);

				$field_value = preg_replace_callback('/\[tag:([r|c|f|t|s]\d+x\d+r*):(.+?)]/', 'callback_make_thumbnail', $field_value);
			}

			$maxlength = null;

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'req');

			if($tpl_empty && $tpl_file)
			{
				$AVE_Template->assign('image', $field_param);
				return $AVE_Template->fetch($tpl_file);
			}

			return $field_value;
			break;

		case 'save':
			if (isset($field_value) && $field_value['img'] != '' )
			{
				$field_value = htmlspecialchars(implode("|", $field_value), ENT_QUOTES);
			}
			else
			{
				$field_value = '';
			}
			break;

		case 'name' :
			return $AVE_Template->get_config_vars('name');
			break;
	}

	return ($res ? $res : $field_value);
}
?>
