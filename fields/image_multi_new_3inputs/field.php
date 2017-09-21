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

// Изображение (Каскад)
function get_field_image_multi_new_3inputs($field_value, $action, $field_id=0, $tpl='', $tpl_empty=0, &$maxlength=null, $document_fields=array(), $rubric_id=0, $default=null)
{

	global $AVE_Template, $img_pixel;

	$fld_dir  = dirname(__FILE__) . '/';
	$tpl_dir  = $fld_dir . 'tpl/';
	$fld_name = basename($fld_dir);

	$lang_file = $fld_dir . 'lang/' . (defined('ACP') ? $_SESSION['admin_language'] : $_SESSION['user_language']) . '.txt';

	$AVE_Template->config_load($lang_file, 'lang');
	$AVE_Template->assign('config_vars', $AVE_Template->get_config_vars());
	$AVE_Template->config_load($lang_file, 'admin');

	$res = 0;

	$iniset_count = ini_get('max_file_uploads');

	switch ($action)
	{
		case 'edit':

			$items = array();
			$items = unserialize($field_value);
			$image_items = array();

			if($items != false){

				foreach($items as $k => $v){
					$image_item = explode('|', $v);

					$image[$k]['url'] = $image_item[0];
					$image[$k]['thumb'] = ($image_item[0] != '') ? make_thumbnail(array('size' => 'f100x100', 'link' => $image_item[0])) : $img_pixel;
					$image[$k]['desc'] = (isset($image_item[1])) ? htmlspecialchars($image_item[1], ENT_QUOTES) : '';
					$image[$k]['desc2'] = (isset($image_item[2])) ? htmlspecialchars($image_item[2], ENT_QUOTES) : '';
					$image[$k]['desc3'] = (isset($image_item[3])) ? htmlspecialchars($image_item[3], ENT_QUOTES) : '';
				}

				if (!empty($image)) {
					$image_items = $image;
				}
			}

			$show_upload = true;

			if(preg_match("/%id/i", $default))
			{
				if ($_REQUEST['action'] != 'new') {
					$path_upload = trim(@str_replace('%id', $_REQUEST['Id'], $default), '/');
					$show_upload = true;
				} else {
					$path_upload = (!empty($default)) ? trim($default, '/') : '';
					$show_upload = false;
				}
			}
			else
			{
				$path_upload = (!empty($default)) ? $default : '';
				$show_upload = true;
			}

			$dir_upload = '/' . UPLOAD_DIR . '/' . ((!empty($path_upload)) ? $path_upload . '/' : '');

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'admin');

			$AVE_Template->assign('max_files', $AVE_Template->get_config_vars('max_f_f') . $iniset_count);
			$AVE_Template->assign('dir_upload', $AVE_Template->get_config_vars('upl_dir') . $dir_upload);
			$AVE_Template->assign('show_upload', $show_upload);

			$AVE_Template->assign('field_dir', $fld_name);
			$AVE_Template->assign('images', $image_items);
			$AVE_Template->assign('field_id', $field_id);
			$AVE_Template->assign('doc_id', (int)$_REQUEST['Id']);
			return $AVE_Template->fetch($tpl_file);
			break;

		case 'doc':

			$items = (isset($field_value)) ? unserialize($field_value) : array();

			$res = array();

			if($items != false){
				foreach($items as $image_item)
				{
					$image_item = clean_php($image_item);
					$field_param = explode('|', $image_item);
					if($image_item){
						if ($tpl_empty)
						{
							$image_item = $field_param;
						}
						else
						{
							$image_item = preg_replace_callback(
								'/\[tag:parametr:(\d+)\]/i',
								function($data) use($field_param)
								{
									return $field_param[(int)$data[1]];
								},
								$tpl
							);

							$image_item = preg_replace_callback(
								'/\[tag:watermark:(.+?):([a-zA-Z]+):([0-9]+)\]/',
								create_function(
									'$m',
									'return watermarks(\'$m[1]\', \'$m[2]\', $m[3]);'
								),
								$image_item
							);

							$image_item = preg_replace_callback('/\[tag:([r|c|f|t|s]\d+x\d+r*):(.+?)]/', 'callback_make_thumbnail', $image_item);
						}
					}
					$res[] = $image_item;
				}
			}

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'doc');

			if($tpl_empty && $tpl_file){

				$AVE_Template->assign('field_id', $field_id);
				$AVE_Template->assign('field_value', $res);
				$AVE_Template->assign('field_count', count($res));
				$AVE_Template->assign('rubric_id', $rubric_id);
				$AVE_Template->assign('default', $default);

				return $AVE_Template->fetch($tpl_file);
			}
			return (!empty($res)) ? implode(PHP_EOL, $res) : $tpl;
			break;

		case 'req':
			$items = unserialize($field_value);

			$res = '';

			if($items != false) {
				foreach($items as $image_item)
				{
					$image_item = clean_php($image_item);
					$field_param = explode('|', $image_item);
					if($image_item){
						if ($tpl_empty)
						{
							$image_item = $field_param;
						}
						else
						{
							$image_item = preg_replace_callback(
								'/\[tag:parametr:(\d+)\]/i',
								function($data) use($field_param)
								{
									return $field_param[(int)$data[1]];
								},
								$tpl
							);

							$image_item = preg_replace_callback(
								'/\[tag:watermark:(.+?):([a-zA-Z]+):([0-9]+)\]/',
								create_function(
									'$m',
									'return watermarks(\'$m[1]\', \'$m[2]\', $m[3]);'
								),
								$image_item
							);

							$image_item = preg_replace_callback('/\[tag:([r|c|f]\d+x\d+r*):(.+?)]/', 'callback_make_thumbnail', $image_item);
						}
					}
					$res[] = $image_item;
				}
			}

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'req');

			if($tpl_empty && $tpl_file){

				$AVE_Template->assign('field_id', $field_id);
				$AVE_Template->assign('field_value', $res);
				$AVE_Template->assign('field_count', count($res));
				$AVE_Template->assign('rubric_id', $rubric_id);
				$AVE_Template->assign('default', $default);

			return $AVE_Template->fetch($tpl_file);
			}

			return (!empty($res)) ? implode(PHP_EOL, $res) : $tpl;
			break;

		case 'save':
			foreach ($field_value as $v)
			{
				if(!empty($v['url'])){
					$field_value_new[] = $v['url'] . '|' . $v['descr'] . '|' . $v['descr2'] . '|' . $v['descr3'];
				}
			}
			if (isset($field_value_new)) {
				return @serialize($field_value_new);
			} else {
				return $field_value_new = '';
			}

			break;

		case 'name' :
			return $AVE_Template->get_config_vars('name');
			break;

		case 'upload':
			$error = false;

			$files_unput = 'cascad3_files'.'_'.$_REQUEST['field_id'].'_'.$_REQUEST['doc_id'];

			if(preg_match("/%id/i", $default))
			{
				$path = trim(@str_replace('%id', $_REQUEST['doc_id'], $default), '/');
			}
			else
			{
				$path = (!empty($default)) ? $default : '';
			}

			function getExtension($file)
			{
				$file = pathinfo($file);
				return $file['extension'];
			}

			$valid_formats = array("jpg", "png", "gif", "bmp","jpeg");

			$dir = '/' . UPLOAD_DIR . '/' . ((!empty($path)) ? $path . '/' : '');
			$dir_abs = BASE_DIR . $dir;

			if (!is_dir($dir_abs)) @mkdir($dir_abs, 0777, true);

			foreach ($_FILES[$files_unput]['name'] as $name => $value) {

				$filename = strtolower(stripslashes(prepare_url($_FILES[$files_unput]['name'][$name])));
				//$size = filesize($_FILES[$files_unput]['tmp_name'][$name]);

				$ext = getExtension($filename);
				$ext = strtolower($ext);

				if(in_array($ext, $valid_formats))
				{
					if (file_exists($dir_abs . $filename))
					{
						$filename = rand(1000, 9999) . '_' . $filename;
					}

					if (@move_uploaded_file($_FILES[$files_unput]['tmp_name'][$name], $dir_abs . $filename)) {
						$new_files[] = $filename;
						$error = false;
					} else {
						$error = true;
					}
				}
				else
				{
					$error = true;
					@unlink($_FILES[$files_unput]['tmp_name'][$name]);
				}
			}
			if ($error !== true) {
				echo json_encode(array(
					'files' => $new_files,
					'dir' => $dir,
					'respons' => 'succes',
					'message' => $AVE_Template->get_config_vars('resp_s_m'),
					'header' => $AVE_Template->get_config_vars('resp_s_h'),
					'theme' => 'accept'
					)
				);
			}
			else
			{
				echo json_encode(array(
					'respons' => 'error',
					'message' => $AVE_Template->get_config_vars('resp_e_m'),
					'header' => $AVE_Template->get_config_vars('resp_e_h'),
					'theme' => 'error'
					)
				);
			}
			exit;
	}

	return ($res ? $res : $field_value);
}