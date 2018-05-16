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

	// Многострочное
	function get_field_multi_line($field_value, $action, $field_id=0, $tpl='', $tpl_empty=0, &$maxlength=null, $document_fields=array(), $rubric_id=0, $default=null, $_tpl = null)
	{
		global $AVE_Template;

		$fld_dir  = dirname(__FILE__) . '/';

		$lang_file = $fld_dir . 'lang/' . (defined('ACP')
			? $_SESSION['admin_language']
			: $_SESSION['user_language']) . '.txt';

		$AVE_Template->config_load($lang_file, 'lang');
		$AVE_Template->assign('config_vars', $AVE_Template->get_config_vars());
		$AVE_Template->config_load($lang_file, 'admin');

		$res = null;

		switch ($action)
		{
			case 'edit':
				if (isset($_REQUEST['multiedit']) && ($_REQUEST['multiedit'] === true))
				{
					$oCKeditor = new CKeditor();
					$oCKeditor->returnOutput = true;
					$oCKeditor->config['toolbar'] = 'Verysmall';
					$oCKeditor->config['height'] = 250;
					$config = array();
					$field = $oCKeditor->editor('data['.$_REQUEST['Id'].'][feld][' . $field_id . ']', $field_value, $config);
				}
				else
					{
						$oCKeditor = new CKeditor();
						$oCKeditor->returnOutput = true;
						$oCKeditor->config['toolbar'] = 'Big';
						$oCKeditor->config['height'] = 400;
						$config = array();
						$field = $oCKeditor->editor('feld[' . $field_id . ']', $field_value, $config);
					}

				$res = $field;
				break;

			case 'doc':
				$res = get_field_default($field_value, $action, $field_id, $tpl, $tpl_empty, $maxlength, $document_fields, $rubric_id);
				$res = document_pagination($res);
				break;

			case 'req':
				$res = get_field_default($field_value, $action, $field_id, $tpl, $tpl_empty, $maxlength, $document_fields, $rubric_id);
				break;

			case 'name' :
				return $AVE_Template->get_config_vars('name');
				break;
		}

		return ($res ? $res : $field_value);
	}
?>