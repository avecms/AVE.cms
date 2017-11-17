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

// Многострочное (Упрощенное)
function get_field_multi_line_simple($field_value, $action, $field_id=0, $tpl='', $tpl_empty=0, &$maxlength=null, $document_fields=array(), $rubric_id=0, $default=null){

	global $AVE_Template;

	$fld_dir  = dirname(__FILE__) . '/';

	$lang_file = $fld_dir . 'lang/' . (defined('ACP') ? $_SESSION['admin_language'] : $_SESSION['user_language']) . '.txt';

	$AVE_Template->config_load($lang_file, 'lang');
	$AVE_Template->assign('config_vars', $AVE_Template->get_config_vars());
	$AVE_Template->config_load($lang_file, 'admin');

	$res=0;

	switch ($action)
	{
		case 'edit':
			if (isset($_COOKIE['no_wysiwyg']) && $_COOKIE['no_wysiwyg'] == 1)
			{
				$field  = "<a name=\"" . $field_id . "\"></a>";
				$field .= "<textarea style=\"98%\"  name=\"feld[" . $field_id . "]\">" . $field_value . "</textarea>";
			}
			else
			{
				if (isset($_REQUEST['outside']) && ($_REQUEST['outside'] === (bool)true)) {
					switch ($_SESSION['use_editor'])
					{
						case '0':
						case '1':
						case '2':
							$oCKeditor = new CKeditor();
							$oCKeditor->returnOutput = true;
							$oCKeditor->config['toolbar'] = 'Verysmall';
							$oCKeditor->config['height'] = 200;
							$config = array();
							$field = $oCKeditor->editor('data['.$_REQUEST['Id'].'][feld][' . $field_id . ']', $field_value, $config);
							break;

						default:
							$field = $field_value;
							break;
					}
				} else {
					switch ($_SESSION['use_editor']) {
						case '0': // CKEditor
						case '1':
							$oCKeditor = new CKeditor();
							$oCKeditor->returnOutput = true;
							$oCKeditor->config['toolbar'] = 'Small';
							$oCKeditor->config['height'] = 300;
							$config = array();
							$field = $oCKeditor->editor('feld[' . $field_id . ']', $field_value, $config);
							break;

						default:
							$field = $field_value;
							break;
					}
				}
			}

			$res = $field;
			break;

		case 'doc':
		case 'req':
			$res = get_field_default($field_value,$action,$field_id,$tpl,$tpl_empty,$maxlength,$document_fields,$rubric_id);
			$res = document_pagination($res);
			break;

		case 'name' :
			return $AVE_Template->get_config_vars('name');
			break;
	}
	return ($res ? $res : $field_value);
}
?>
