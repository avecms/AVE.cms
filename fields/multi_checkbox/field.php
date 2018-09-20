<?

/**
 * AVE.cms
 *
 * @package AVE.cms
 * @version 3.x
 * @filesource
 * @copyright © 2007-2015 AVE.cms, http://www.ave-cms.ru
 *
 * @license GPL v.2
 */

// Мульти чекбокс
function get_field_multi_checkbox($field_value, $action, $field_id=0, $tpl='', $tpl_empty=0, &$maxlength=null, $document_fields=array(), $rubric_id=0, $default=null)
{
	global $AVE_Template;

	$fld_dir  = dirname(__FILE__) . '/';
	$tpl_dir  = $fld_dir . 'tpl/';

	$lang_file = $fld_dir . 'lang/' . (defined('ACP') ? $_SESSION['admin_language'] : $_SESSION['user_language']) . '.txt';

	$AVE_Template->config_load($lang_file, 'lang');
	$AVE_Template->assign('config_vars', $AVE_Template->get_config_vars());
	$AVE_Template->config_load($lang_file, 'admin');

	$res = array();

	switch ($action)
	{
		case 'edit':
			$default_items = explode(',', $default);
			$default_items = array_diff($default_items, array(''));

			$field_value_array = explode('|', $field_value);
			$field_value_array = array_values(array_diff($field_value_array, array('')));

			$AVE_Template->assign('items', $default_items);
			$AVE_Template->assign('used', $field_value_array);
			$AVE_Template->assign('doc_id', (isset($_REQUEST['Id']) ? (int)$_REQUEST['Id'] : 0));
			$AVE_Template->assign('field_id', $field_id);
			$AVE_Template->assign('field_value', $field_value);

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'admin');

			return $AVE_Template->fetch($tpl_file);
			break;

		case 'doc':
			$default_items =  explode(',', $default);

			$items = explode('|', $field_value);
			$items = array_diff($items, array(''));

			if (! empty($items))
			{
				foreach($items as $item)
				{
					if ($item)
					{
						if ($tpl_empty)
						{
							$item = $default_items[(int)$item-1];
						}
						else
						{
							$field_param = explode('|', $item);

							$item = preg_replace_callback(
								'/\[tag:parametr:(\d+)\]/i',
								function($data) use($field_param, $default_items)
								{
									return $default_items[$field_param[(int)$data[1]]-1];
								},
								$tpl
							);
						}
					}

					$res[] = $item;
				}
			}

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'doc');

			if ($tpl_empty && $tpl_file)
			{
				$AVE_Template->assign('field_id', $field_id);
				$AVE_Template->assign('field_value', $res);
				$AVE_Template->assign('rubric_id', $rubric_id);
				$AVE_Template->assign('default', $default_items);

				return $AVE_Template->fetch($tpl_file);
			}

			return (! empty($res))
				? implode(PHP_EOL, $res)
				: $tpl;

			break;

		case 'req':
			$default_items =  explode(',', $default);

			$items = explode('|', $field_value);
			$items = array_diff($items, array(''));

			if (! empty($items))
			{
				foreach($items as $item)
				{
					if ($item)
					{
						if ($tpl_empty)
						{
							$item = $default_items[(int)$item-1];
						}
						else
						{
							$field_param = explode('|', $item);

							$item = preg_replace_callback(
								'/\[tag:parametr:(\d+)\]/i',
								function($data) use($field_param, $default_items)
								{
									return $default_items[$field_param[(int)$data[1]]-1];
								},
								$tpl
							);
						}
					}

					$res[] = $item;
				}
			}

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'req');

			if ($tpl_empty && $tpl_file)
			{
				$AVE_Template->assign('field_id', $field_id);
				$AVE_Template->assign('field_value', $res);
				$AVE_Template->assign('rubric_id', $rubric_id);
				$AVE_Template->assign('default', $default_items);

				return $AVE_Template->fetch($tpl_file);
			}

			return (! empty($res))
				? implode(PHP_EOL, $res)
				: $tpl;

			break;

		case 'name':
			return $AVE_Template->get_config_vars('name');
			break;

	}
	return ($res ? $res : $field_value);
}
?>