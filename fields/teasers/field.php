<?php
/**
 *	Поле Цена
 */
function get_field_teasers($field_value, $action, $field_id=0, $tpl='', $tpl_empty=0, &$maxlength = '', $doc_fields=array(), $rubric_id=0, $default='')
{
	global $AVE_DB, $AVE_Template;

	$fld_dir  = dirname(__FILE__) . '/';
	$tpl_dir  = $fld_dir . 'tpl/';
	$fld_name = basename($fld_dir);

	$lang_file = $fld_dir . 'lang/' . (defined('ACP') ? $_SESSION['admin_language'] : $_SESSION['user_language']) . '.txt';

	$AVE_Template->config_load($lang_file, 'lang');
	$AVE_Template->assign('config_vars', $AVE_Template->get_config_vars());
	$AVE_Template->config_load($lang_file, 'admin');

	switch ($action)
	{
		case 'edit':

			$items = array();

			$items = unserialize($field_value);

			if($items != false){

				foreach($items as $k => $v){
					$list_item = explode('|', $v);

					$list[$k]['param'] = (isset($list_item[0])) ? htmlspecialchars($list_item[0], ENT_QUOTES) : '';
					$list[$k]['value'] = (isset($list_item[1])) ? htmlspecialchars($list_item[1], ENT_QUOTES) : '';
				}

				$items = $list;
			}

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'admin');

			$AVE_Template->assign('doc_id', $_REQUEST['Id']);
			$AVE_Template->assign('items', $items);
			$AVE_Template->assign('field_dir', $fld_name);
			$AVE_Template->assign('field_id', $field_id);

			return $AVE_Template->fetch($tpl_file);

		case 'save':
			foreach ($field_value as $v)
			{
				if(! empty($v['value']) && ! empty($v['param']))
				{
					$field_value_new[] = $v['param'] . ($v['value'] ? '|' . $v['value'] : '');
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

		case 'doc':
			$items = (isset($field_value)) ? unserialize($field_value) : array();

			$res = array();

			if ($items != false)
			{
				foreach($items as $item)
				{
					$item = explode('|', $item);

					if ($item[1])
						$res[] = eval2var('?>' . showteaser($item[1]) . '<?');
				}
			}

			return (! empty($res))
				? implode(PHP_EOL, $res)
				: $tpl;

			break;

		case 'req':
			return get_field_default($field_value, $action, $field_id, $tpl, $tpl_empty);

		case 'name' :
			return $AVE_Template->get_config_vars('name');
			break;

		case 'search':

			$field_default = explode(',', $default);

			$zap_1 = ($field_default[0]) ? ',' : '';
			$zap_2 = ($field_default[0] && $field_default[1]) ? ',' : '';

			$sel_1 = ($field_default[0]) ? 'b.field_value as b1' : '';
			$sel_2 = ($field_default[1]) ? 'c.field_value as c1' : '';

			$from_1 =  ($field_default[0]) ? PREFIX . '_document_fields b' : '';
			$from_2 =  ($field_default[1]) ? PREFIX . '_document_fields c' : '';

			$search_1 = ($field_default[0]) ? "AND (b.document_id=a.Id AND b.rubric_field_id = " . $field_default[0] . ")" : "";
			$search_2 = ($field_default[1]) ? "AND (c.document_id=a.Id AND c.rubric_field_id = " . $field_default[1] . ")" : "";

			$or_1 = ($field_default[0]) ? "OR (UPPER(b.field_value) LIKE UPPER('%" . $_REQUEST['q'] . "%'))" : "";
			$or_2 = ($field_default[1]) ? "OR (UPPER(c.field_value) LIKE UPPER('%" . $_REQUEST['q'] . "%'))" : "";

			$sql = $AVE_DB->Query("
				SELECT
					a.Id,
					a.document_title
					$zap_1
					$sel_1
					$zap_2
					$sel_2
				FROM
					" . PREFIX . "_documents a
					$zap_1
					" . $from_1 . "
					$zap_2
					" . $from_2 . "
				WHERE
					a.rubric_id = '" . $_REQUEST['rubric_id'] . "'
					AND
					a.document_status = 1
					" . $search_1 . "
					" . $search_2 . "
					AND
						(
							(UPPER(document_title) LIKE UPPER('%" . $_REQUEST['q'] . "%'))
							$or_1
							$or_2
						)
				GROUP BY a.Id
				LIMIT 0,5
			");

			$doc_finded = array();

			while ($row = $sql->FetchRow())
			{
				$doc_finded[] = array(
					'doc_id'		=> $row->Id,
					'doc_title'		=> $row->document_title,
					'doc_name'		=> (($field_default[0]) ? $row->b1 : $row->document_title),
					'doc_article'	=> (($field_default[1]) ? $row->c1 : '')
				);
			}

			echo json_encode($doc_finded);
		exit;

		default:
			return $field_value;
	}

	return ($res ? $res : $field_value);
}
?>