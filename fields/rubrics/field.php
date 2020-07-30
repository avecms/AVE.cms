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

// Шаблоны рубрик
function get_field_rubrics($field_value, $action, $field_id = 0, $tpl = '', $tpl_empty = 0, &$maxlength = null, $document_fields = array(), $rubric_id = 0, $default = null)
{
	global $AVE_DB, $AVE_Template;

	$fld_dir  = dirname(__FILE__) . '/';
	$tpl_dir  = $fld_dir . 'tpl/';

	$lang_file = $fld_dir . 'lang/' . (defined('ACP') ? $_SESSION['admin_language'] : $_SESSION['user_language']) . '.txt';

	$AVE_Template->config_load($lang_file, 'lang');
	$AVE_Template->assign('config_vars', $AVE_Template->get_config_vars());
	$AVE_Template->config_load($lang_file, 'admin');

	$res = 0;

	if ($_REQUEST['action'] == 'new')
		$field_value = 0;

	switch ($action)
	{
		case 'edit':
			if (isset($default))
			{
				$sql = $AVE_DB->Query("
						SELECT
							id, title
						FROM
							". PREFIX ."_rubric_templates
						WHERE
							rubric_id IN (" . $default . ")
				");

				$rubrics = array();

				while($row = $sql->FetchAssocArray())
					array_push($rubrics, $row);

				$AVE_Template->assign('default', $default);
				$AVE_Template->assign('rubrics', $rubrics);
				$AVE_Template->assign('field_id', $field_id);
				$AVE_Template->assign('doc_id', (isset($_REQUEST['Id']) ? (int)$_REQUEST['Id'] : 0));
				$AVE_Template->assign('field_value', $field_value);
			}
			else
				{
					$AVE_Template->assign('error', $AVE_Template->get_config_vars('error'));
				}

			$tpl_file = get_field_tpl($tpl_dir, $field_id, 'admin');

			return $AVE_Template->fetch($tpl_file);
			break;

		case 'doc':
			$res = $field_value;
			break;

		case 'req':
			$res = $field_value;
			break;

		case 'api':
			return $field_value;
			break;

		case 'name':
			return $AVE_Template->get_config_vars('name');
			break;
	}
	return ($res ? $res : $field_value);
}
?>