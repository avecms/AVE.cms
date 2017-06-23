<?

/**
 * AVE.cms
 *
 * @package AVE.cms
 * @version 3.x
 * @filesource
 * @copyright © 2007-2014 AVE.cms, http://www.ave-cms.ru
 *
 */

// Мульти лист
function get_field_multi_list_single($field_value, $action, $field_id=0, $tpl='', $tpl_empty=0, &$maxlength=null, $document_fields=array(), $rubric_id=0, $default=null){

	global $AVE_Template;

	$fld_dir  = dirname(__FILE__) . '/';
	$tpl_dir  = $fld_dir . 'tpl/';
	$fld_name = basename($fld_dir);

	$lang_file = $fld_dir . 'lang/' . (defined('ACP') ? $_SESSION['admin_language'] : $_SESSION['user_language']) . '.txt';

	$AVE_Template->config_load($lang_file, 'lang');
	$AVE_Template->assign('config_vars', $AVE_Template->get_config_vars());
	$AVE_Template->config_load($lang_file, 'admin');

	$res = array();

	switch ($action)
	{
		case 'edit':

			$items = array();

			$items = unserialize($field_value);

			if ($items != false)
			{
				$items = $items;
			}
			else
				{
					$items = explode(',', $default);
				}

			$AVE_Template->assign('doc_id', $_REQUEST['Id']);
			$AVE_Template->assign('field_dir', $fld_name);
			$AVE_Template->assign('items', $items);
			$AVE_Template->assign('field_id', $field_id);

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'admin');

			return $AVE_Template->fetch($tpl_file);
			break;

		case 'doc':
			$items = unserialize($field_value);

			if ($items != false)
			{
				foreach($items as $list_item)
				{
					$list_item = clean_php($list_item);
					$field_param = explode('|', $list_item);

					if ($list_item)
					{
						if ($tpl_empty)
						{
							$list_item = $field_param;
						}
						else
						{
							$list_item = preg_replace_callback(
								'/\[tag:parametr:(\d+)\]/i',
								function($data) use($field_param)
								{
									return $field_param[(int)$data[1]];
								},
								$tpl
							);
						}
					}
					$res[] = $list_item;
				}
			}

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'doc');

			if ($tpl_empty && $tpl_file)
			{
				$AVE_Template->assign('field_id', $field_id);
				$AVE_Template->assign('field_value', $res);
				$AVE_Template->assign('field_count', count($res));
				$AVE_Template->assign('default', $default);

				return $AVE_Template->fetch($tpl_file);
			}

			return (! empty($res)) ? implode(PHP_EOL, $res) : $tpl;
			break;

		case 'req':
			$items = unserialize($field_value);

			if ($items != false)
			{
				foreach($items as $list_item)
				{
					$list_item = clean_php($list_item);
					$field_param = explode('|', $list_item);

					if ($list_item)
					{
						if ($tpl_empty)
						{
							$list_item = $field_param;
						}
						else
						{
							$list_item = preg_replace_callback(
								'/\[tag:parametr:(\d+)\]/i',
								function($data) use($field_param)
								{
									return $field_param[(int)$data[1]];
								},
								$tpl
							);
						}
					}
					$res[] = $list_item;
				}
			}

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'req');

			if ($tpl_empty && $tpl_file)
			{
				$AVE_Template->assign('field_id', $field_id);
				$AVE_Template->assign('field_value', $res);
				$AVE_Template->assign('field_count', count($res));
				$AVE_Template->assign('default', $default);

				return $AVE_Template->fetch($tpl_file);
			}

			return (! empty($res)) ? implode(PHP_EOL, $res) : $tpl;
			break;

		case 'save':
			foreach ($field_value as $v)
			{
				if (! empty($v))
				{
					$field_value_new[] = $v;
				}
			}

			if (isset($field_value_new))
			{
				return @serialize($field_value_new);
			}
			else
				{
					return $field_value_new = '';
				}
			break;

		case 'name':
			return $AVE_Template->get_config_vars('name');
			break;

	}
	return ($res ? $res : $field_value);
}
?>