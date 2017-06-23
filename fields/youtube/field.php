<?

/**
 * AVE.cms
 *
 * @package AVE.cms
 * @version 3.x
 * @filesource
 * @field YouTube
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

// YouTube

	function get_field_youtube($field_value, $action, $field_id = 0, $tpl = '', $tpl_empty = 0, &$maxlength = null, $document_fields = array(), $rubric_id = 0, $default = null)
	{
		global $AVE_Template;

		$fld_dir  = dirname(__FILE__) . '/';
		$tpl_dir  = $fld_dir . 'tpl/';
		$fld_name = basename($fld_dir);

		$lang_file = $fld_dir . 'lang/' . (defined('ACP') ? $_SESSION['admin_language'] : $_SESSION['user_language']) . '.txt';

		$AVE_Template->config_load($lang_file, 'lang');
		$AVE_Template->config_load($lang_file, 'admin');
		$AVE_Template->assign('config_vars', $AVE_Template->get_config_vars());

		$result = 0;

		switch ($action)
		{

			// Отображение поля в административной части
			case 'edit':
				$video = explode('|', $field_value);

				$tpl_file = get_field_tpl($tpl_dir, $field_id, 'admin');

				$AVE_Template->assign('field_dir', $fld_name);
				$AVE_Template->assign('video', $video);
				$AVE_Template->assign('rubric_id', $rubric_id);
				$AVE_Template->assign('doc_id', (int)$_REQUEST['Id']);
				$AVE_Template->assign('field_id', $field_id);
				$AVE_Template->assign('field_value', $field_value);

				return $AVE_Template->fetch($tpl_file);

			// Отображение поля в документах
			case 'doc':
				$field_value = clean_php($field_value);

				$field_param = explode('|', $field_value);

				if (! empty($field_param[0]))
					$url = youtube_url_parser($field_param[0], $field_param[4]);

				if (! $tpl_empty)
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

				$tpl_file = get_field_tpl($tpl_dir, $field_id, 'doc');

				if($tpl_empty && $tpl_file)
				{
					$AVE_Template->assign('param', $field_param);
					$AVE_Template->assign('video_url', $url);
					return $AVE_Template->fetch($tpl_file);
				}

				return $field_value;

			// Отображение поля в запросах
			case 'req':
				$field_value = clean_php($field_value);

				$field_param = explode('|', $field_value);

				if (! empty($field_param[0]))
					$url = youtube_url_parser($field_param[0], $field_param[4]);

				if (! $tpl_empty)
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

				$tpl_file = get_field_tpl($tpl_dir, $field_id, 'req');

				if($tpl_empty && $tpl_file)
				{
					$AVE_Template->assign('param', $field_param);
					$AVE_Template->assign('video_url', $url);
					return $AVE_Template->fetch($tpl_file);
				}

				return $field_value;

			// Сохранение поля в административной части
			case 'save':
				if (isset($field_value) && $field_value['url'] != '' )
				{
					$field_value = htmlspecialchars(implode("|", $field_value), ENT_QUOTES);
				}
				else
				{
					$field_value = '';
				}
				break;

			// Тип/Имя поля в административной части
			case 'name' :
				return $AVE_Template->get_config_vars('name');
		}

		return ($result ? $result : $field_value);
	}

	// Check YouTube link
	function youtube_url_parser($url, $source = 'embed')
	{
		// Parse URL
		$p_url = parse_url($url);

		// Find host
		$host = $p_url['host'];

		// Check if youtube
		if ($host == 'www.youtube.com') {

			if (preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $url, $match))
			 {
				$vid = $match[1];

				if ($source == 'embed')
				{
					return 'http://www.youtube.com/v/'.$vid;
				}
				else
				{
					return 'http://www.youtube.com/embed/'.$vid;
				}

			}
			else
			{
				return $url;
			}

		// Check the new video url
		}
		else if ($host == 'youtu.be')
		{
			if (preg_match('/^(http|https):\/\/youtu\.be\/(.*)/i', $url, $match))
			{
				$vid = $match[2];

				if ($source == 'embed')
				{
					return 'http://www.youtube.com/v/'.$vid;
				}
				else
				{
					return 'http://www.youtube.com/embed/'.$vid;
				}
			}
			else
			{
				return $url;
			}
		}
		// Nothing just return the url
		else
		{
			return $url;
		}
	}
?>
