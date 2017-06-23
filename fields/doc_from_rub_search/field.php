<?php

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

// Документы из рубрик (Поиск)
function get_field_doc_from_rub_search($field_value, $action, $field_id=0, $tpl='', $tpl_empty=0, &$maxlength = '', $doc_fields=array(), $rubric_id=0, $default='')
{
	global $AVE_DB, $AVE_Template;

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

			if ($field_value != '' && $field_value != $default)
			{
				$items = explode('|', $field_value);
				$items = array_values(array_diff($items, array('')));
			}

			if(! empty($items))
			{
				foreach($items as $k => $v)
				{
					$list[$k]['param'] = htmlspecialchars(get_document($v, 'document_title'), ENT_QUOTES);
					$list[$k]['value'] = $v;
				}

				$items = $list;
			}
			else
				{
					$items[0]['param'] = '';
					$items[0]['value'] = '';
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
				if (! empty($v['value']))
				{
					$field_value_new[] = $v['value'];
				}
			}

			if (isset($field_value_new))
			{
				return '|' . implode('|', $field_value_new) . '|';
			}
			else
				{
					return $field_value_new = '';
				}
			break;

		case 'doc':
			$field_value_array = explode('|', $field_value);
			$field_value_array = array_values(array_diff($field_value_array, array('')));

			if ($field_value_array != false)
			{
				foreach ($field_value_array as $list_item)
				{
					if ($list_item)
					{
						if ($tpl_empty)
						{
							$list_item = $AVE_DB->Query("
								SELECT
									Id,
									document_title,
									document_alias,
									document_breadcrum_title
								FROM
									".PREFIX."_documents
								WHERE
									Id = '" . $list_item . "'
							")->FetchAssocArray();
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
			$field_value_array = explode('|', $field_value);
			$field_value_array = array_values(array_diff($field_value_array, array('')));

			if ($field_value_array != false)
			{
				foreach ($field_value_array as $list_item)
				{
					if ($list_item)
					{
						if ($tpl_empty)
						{
							$list_item = $AVE_DB->Query("
								SELECT
									Id,
									document_title,
									document_alias,
									document_breadcrum_title
								FROM
									".PREFIX."_documents
								WHERE
									Id = '" . $list_item . "'
							")->FetchAssocArray();
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

		case 'name' :
			return $AVE_Template->get_config_vars('name');
			break;

		case 'search':
			$default = get_field_default_value($_REQUEST['field_id']);

			$sql = $AVE_DB->Query("
				SELECT
					doc.Id,
					doc.document_title,
					rub.rubric_title
				FROM
					" . PREFIX . "_documents AS doc
				JOIN
					" . PREFIX . "_rubrics AS rub
					ON doc.rubric_id = rub.Id
				WHERE
					doc.rubric_id IN (" . $default . ")
				AND
					doc.document_status = 1
				AND
					UPPER (doc.document_title) LIKE UPPER('%" . $_REQUEST['q'] . "%')
				GROUP BY
					doc.Id
				LIMIT
					0,5
			");

			$doc_finded = array();

			while ($row = $sql->FetchRow())
			{
				$doc_finded[] = array(
					'doc_id'		=> $row->Id,
					'doc_title'		=> $row->document_title,
					'doc_rubric'	=> $row->rubric_title
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