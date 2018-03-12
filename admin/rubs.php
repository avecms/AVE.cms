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

if (!defined('ACP'))
{
	header('Location:index.php');
	exit;
}

require(BASE_DIR . '/class/class.rubs.php');
$AVE_Rubric = new AVE_Rubric;

$AVE_Template->config_load(BASE_DIR . '/admin/lang/' . $_SESSION['admin_language'] . '/rubs.txt', 'rubs');

switch($_REQUEST['action'])
{
	case '' :
		if(check_permission('rubric_view'))
		{
			if(check_permission('rubric_edit'))
			{
				switch($_REQUEST['sub'])
				{
					case 'quicksave':
						$AVE_Rubric->quickSave();
						break;
				}
			}
			$AVE_Rubric->rubricList();
			$AVE_Template->assign('templates', get_all_templates());
			$AVE_Template->assign('content', $AVE_Template->fetch('rubs/list.tpl'));
		}
		else
		{
			$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('RUBRIK_NO_VIEW'));
			$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
		}
		break;

	case 'new':
		if(check_permission('rubric_edit'))
		{
			$AVE_Template->assign('templates', get_all_templates());
			$AVE_Rubric->rubricNew();
		}
		else
		{
			$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('RUBRIK_NO_CHANGE3'));
			$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
		}
		break;

	case 'template':
		if(check_permission('rubric_edit'))
		{
			switch($_REQUEST['sub'])
			{
				case '':
					$AVE_Rubric->rubricTemplateShow();
					break;

				case 'save':

					$Rtemplate = $_POST['rubric_template'];
					$Htemplate = $_POST['rubric_header_template'];
					$Ftemplate = $_POST['rubric_footer_template'];
					$Ttemplate = $_POST['rubric_teaser_template'];
					$Atemplate = $_POST['rubric_admin_teaser_template'];

					$check_code = strtolower($Rtemplate.$Htemplate.$Ttemplate.$Atemplate.$Ftemplate);

					$ok = true;

					if ((is_php_code($check_code)) && !check_permission('rubric_php') )
					{
						$AVE_Template->assign('php_forbidden', 1);

						$ok = false;
					}

					if (! $ok)
					{
						$message = $AVE_Template->get_config_vars('RUBRIC_SAVED_PHP_ERR');
						$header = $AVE_Template->get_config_vars('RUBRIC_ERROR');
						$theme = 'error';

						if (isAjax())
						{
							echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
							exit;
						}
						else
						{
							$AVE_Rubric->rubricTemplateShow(1);
						}
					}
					else
					{
						$AVE_Rubric->rubricTemplateSave($Rtemplate, $Htemplate, $Ttemplate, $Atemplate, $Ftemplate);
					}
					break;
			}
		}
		else
		{
			$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('RUBRIK_NO_CHANGE2'));
			$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
		}
		break;

	case 'delete':
		if(check_permission('rubric_edit'))
		{
			$AVE_Rubric->rubricDelete();
		}
		else
		{
			$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('RUBRIK_NO_PERMISSION'));
			$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
		}
		break;

	case 'multi':
		if(check_permission('rubric_edit'))
		{
			switch($_REQUEST['sub'])
			{
				case 'save':
					$AVE_Rubric->rubricCopy();
					break;
			}
			$AVE_Template->assign('content', $AVE_Template->fetch('rubs/multi.tpl'));
		}
		else
		{
			$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('RUBRIK_NO_MULTIPLY'));
			$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
		}
		break;

	case 'edit':
		if(check_permission('rubric_edit'))
		{
			switch($_REQUEST['sub'])
			{
				case '':
					switch($_REQUEST['submit'])
					{
						case 'saveperms':
							if (check_permission('rubric_perms')){
								$AVE_Rubric->rubricPermissionSave((int)$_REQUEST['Id']);
							}
							break;

						case 'save':
							$AVE_Rubric->rubricFieldSave((int)$_REQUEST['Id']);
							break;

						case 'linked_rubric':
							$AVE_Rubric->rubricShow(1);
							break;

						case 'code':
							if (check_permission('rubric_code')){
								$AVE_Rubric->rubricCode((int)$_REQUEST['Id']);
							}
							break;

						case 'description':
							$AVE_Rubric->rubricDesc((int)$_REQUEST['Id']);
							break;
					}
			}
			$AVE_Rubric->rubricFieldShow((int)$_REQUEST['Id'], null);
			break;
		}
		else
		{
			$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('RUBRIK_NO_CHANGE1'));
			$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
		}
		break;

	case 'alias_add':
		if(check_permission_acp('rubric_edit'))
		{
			$AVE_Rubric->rubricAliasAdd();
		}
		break;

	case 'code':
		if (check_permission('rubric_code'))
		{
			$AVE_Rubric->rubricCodeEdit($_REQUEST['Id']);
		}
		break;

	case 'field_template':
		if(check_permission_acp('rubric_edit'))
		{
			$AVE_Rubric->rubricFieldTemplate();
		}
		break;

	case 'field_template_save':
		if(check_permission_acp('rubric_edit'))
		{
			$AVE_Rubric->rubricFieldTemplateSave((int)$_REQUEST['field_id'], (int)$_REQUEST['rubric_id']);
		}
		break;

	case 'fieldssort':
		if(check_permission_acp('rubric_edit'))
		{
			$AVE_Rubric->rubricFieldsSort((array)$_REQUEST['sort']);
		}
		exit;

	case 'rubssort':
		if(check_permission_acp('rubric_edit'))
		{
			$AVE_Rubric->rubricsSort((array)$_REQUEST['sort']);
		}
		exit;

	case 'alias_check':
		if(check_permission_acp('rubric_edit'))
		{
			$AVE_Rubric->rubricAliasCheck((int)$_REQUEST['rubric_id'],(int)$_REQUEST['field_id'], $_REQUEST['rubric_field_alias']);
		}
		break;

	case 'newfield':
		if(check_permission_acp('rubric_edit'))
		{
			$AVE_Rubric->rubricFieldNew((int)$_REQUEST['Id'], $_REQUEST['ajax']);
		}
		break;

	case 'fields':
		if(check_permission_acp('rubric_edit'))
		{
			$AVE_Rubric->rubricFieldShow((int)$_REQUEST['Id'], $_REQUEST['ajax']);
		}
		break;

	case 'change':
		if(check_permission_acp('rubric_edit'))
		{
			$AVE_Rubric->rubricFieldChange((int)$_REQUEST['field_id'], (int)$_REQUEST['rubric_id']);
		}
		break;

	case 'changesave':
		if(check_permission_acp('rubric_edit'))
		{
			$AVE_Rubric->rubricFieldChangeSave((int)$_REQUEST['field_id'], (int)$_REQUEST['rubric_id']);
		}
		break;

	case 'changegroup':
		if(check_permission_acp('rubric_edit'))
		{
			$AVE_Rubric->rubricFieldGroupChange((int)$_REQUEST['field_id'], (int)$_REQUEST['rubric_id']);
		}
		break;

	case 'changegroupsave':
		if(check_permission_acp('rubric_edit'))
		{
			$AVE_Rubric->rubricFieldGroupChangeSave((int)$_REQUEST['field_id'], (int)$_REQUEST['rubric_id']);
		}
		break;

	case 'fieldsgroups':
		if(check_permission_acp('rubric_edit'))
		{
			$AVE_Rubric->rubricFieldsGroups((int)$_REQUEST['Id']);
		}
		break;

	case 'newfieldsgroup':
		if(check_permission_acp('rubric_edit'))
		{
			$AVE_Rubric->rubricNewGroupFields((int)$_REQUEST['Id']);
		}
		break;

	case 'savefieldsgroup':
		if(check_permission_acp('rubric_edit'))
		{
			$AVE_Rubric->rubricEditGroupFields((int)$_REQUEST['Id']);
		}
		break;

	case 'delfieldsgroup':
		if(check_permission_acp('rubric_edit'))
		{
			$AVE_Rubric->rubricDelGroupFields((int)$_REQUEST['Id'], (int)$_REQUEST['rubric_id']);
		}
		break;

	case 'fieldsgroupssort':
		if(check_permission_acp('rubric_edit'))
		{
			$AVE_Rubric->rubricFieldsGroupsSort((array)$_REQUEST['sort']);
		}
		exit;

	case 'tmpls':
		if (check_permission_acp('rubric_edit'))
		{
			$AVE_Rubric->tmplsList();
			$AVE_Template->assign('content', $AVE_Template->fetch('rubs/tmpls.tpl'));
		}
		break;

	case 'tmpls_edit':
		if(check_permission('rubric_edit'))
		{
			switch($_REQUEST['sub'])
			{
				case '':
					$AVE_Rubric->tmplsEdit();
					break;

				case 'save':

					$title = $_POST['template_title'];
					$template = $_POST['rubric_template'];

					$check_code = strtolower($template);

					$ok = true;

					if((is_php_code($check_code)) && !check_permission('rubric_php') )
					{
						$AVE_Template->assign('php_forbidden', 1);

						$ok = false;
					}

					if(! $ok)
					{
						$message = $AVE_Template->get_config_vars('RUBRIC_SAVED_PHP_ERR');
						$header = $AVE_Template->get_config_vars('RUBRIC_ERROR');
						$theme = 'error';

						if (isAjax())
						{
							echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
							exit;
						}
						else
						{
							$AVE_Rubric->tmplsEdit();
						}
					}
					else
					{
						$AVE_Rubric->tmplsSave($template, $title);
					}
					break;
			}
		}
		else
		{
			$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('RUBRIK_NO_CHANGE2'));
			$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
		}
		break;

	case 'tmpls_new':
		if(check_permission('rubric_edit'))
		{
			switch($_REQUEST['sub'])
			{
				case '':
					$AVE_Rubric->tmplsEdit();
					break;

				case 'save':

					$title = $_POST['template_title'];
					$template = $_POST['rubric_template'];

					$check_code = strtolower($template);

					$ok = true;

					if((is_php_code($check_code)) && !check_permission('rubric_php') )
					{
						$AVE_Template->assign('php_forbidden', 1);

						$ok = false;
					}

					if(! $ok)
					{
						$message = $AVE_Template->get_config_vars('RUBRIC_SAVED_PHP_ERR');
						$header = $AVE_Template->get_config_vars('RUBRIC_ERROR');
						$theme = 'error';

						if (isAjax())
						{
							echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
							exit;
						}
						else
						{
							$AVE_Rubric->tmplsEdit();
						}
					}
					else
					{
						$AVE_Rubric->tmplsSave($template, $title);
					}
					break;
			}
		}
		else
		{
			$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('RUBRIK_NO_CHANGE2'));
			$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
		}
		break;

	case 'tmpls_from':
		if(check_permission('rubric_edit'))
		{
			switch($_REQUEST['sub'])
			{
				case '':
					$AVE_Rubric->tmplsEdit();
					break;

				case 'save':

					$title = $_POST['template_title'];
					$template = $_POST['rubric_template'];

					$check_code = strtolower($template);

					$ok = true;

					if((is_php_code($check_code)) && !check_permission('rubric_php') )
					{
						$AVE_Template->assign('php_forbidden', 1);

						$ok = false;
					}

					if(! $ok)
					{
						$message = $AVE_Template->get_config_vars('RUBRIC_SAVED_PHP_ERR');
						$header = $AVE_Template->get_config_vars('RUBRIC_ERROR');
						$theme = 'error';

						if (isAjax())
						{
							echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
							exit;
						}
						else
						{
							$AVE_Rubric->tmplsEdit();
						}
					}
					else
					{
						$AVE_Rubric->tmplsSave($template, $title);
					}
					break;
			}
		}
		else
		{
			$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('RUBRIK_NO_CHANGE2'));
			$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
		}
		break;

	case 'tmpls_copy':
		if(check_permission('rubric_edit'))
		{
			switch($_REQUEST['sub'])
			{
				case '':
					$AVE_Rubric->tmplsEdit();
					break;

				case 'save':

					$title = $_POST['template_title'];
					$template = $_POST['rubric_template'];

					$check_code = strtolower($template);

					$ok = true;

					if((is_php_code($check_code)) && !check_permission('rubric_php') )
					{
						$AVE_Template->assign('php_forbidden', 1);

						$ok = false;
					}

					if(! $ok)
					{
						$message = $AVE_Template->get_config_vars('RUBRIC_SAVED_PHP_ERR');
						$header = $AVE_Template->get_config_vars('RUBRIC_ERROR');
						$theme = 'error';

						if (isAjax())
						{
							echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
							exit;
						}
						else
						{
							$AVE_Rubric->tmplsEdit();
						}
					}
					else
					{
						$AVE_Rubric->tmplsSave($template, $title);
					}
					break;
			}
		}
		else
		{
			$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('RUBRIK_NO_CHANGE2'));
			$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
		}
		break;

	case 'tmpls_del':
		if(check_permission('rubric_edit'))
		{
			$AVE_Rubric->tmplsDelete();
		}
		else
		{
			$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('RUBRIK_NO_PERMISSION'));
			$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
		}
		break;

	case 'rules':
		if (check_permission('rubric_edit'))
		{
			switch($_REQUEST['sub'])
			{
				case '':
					switch($_REQUEST['submit'])
					{
						case 'saveperms':
							if (check_permission('rubric_perms'))
								$AVE_Rubric->rubricPermissionSave((int)$_REQUEST['Id']);
							break;
					}
			}
			$AVE_Rubric->rubricRulesShow((int)$_REQUEST['Id'], null);
			break;
		}
		else
		{
			$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('RUBRIK_NO_CHANGE1'));
			$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
		}
		break;

	case 'ftlist':
		if (check_permission('rubric_edit'))
		{
			$AVE_Rubric->ShowFields();
		}
		else
		{
			$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('RUBRIK_NO_PERMISSION'));
			$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
		}
		break;

	case 'ftshowfield':
		if (check_permission('rubric_edit'))
		{
			$AVE_Rubric->ShowFieldsByType($_REQUEST['type']);
		}
		else
		{
			$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('RUBRIK_NO_PERMISSION'));
			$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
		}
		break;


	case 'ftcreate':
		if (check_permission('rubric_edit'))
		{
			$AVE_Rubric->EditFieldTpl((int)$_REQUEST['id'], $_REQUEST['fld'], $_REQUEST['type']);
		}
		else
		{
			$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('RUBRIK_NO_PERMISSION'));
			$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
		}
		break;

	case 'ftedit':
		if (check_permission('rubric_edit'))
		{
			$AVE_Rubric->EditFieldTpl((int)$_REQUEST['id'], $_REQUEST['fld'], $_REQUEST['type']);
		}
		else
		{
			$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('RUBRIK_NO_PERMISSION'));
			$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
		}
		break;

	case 'ftsave':
		if (check_permission('rubric_edit'))
		{
			$AVE_Rubric->SaveFieldTpl((int)$_REQUEST['field_id'], $_REQUEST['field_name'], $_REQUEST['field_type'], $_REQUEST['func']);
		}
		else
		{
			$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('RUBRIK_NO_PERMISSION'));
			$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
		}
		break;


	case 'ftdelete':
		if (check_permission('rubric_edit'))
		{
			$AVE_Rubric->DeleteFieldTpl((int)$_REQUEST['id'], $_REQUEST['fld'], $_REQUEST['type'], $_REQUEST['func']);
		}
		else
		{
			$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('RUBRIK_NO_PERMISSION'));
			$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
		}
		break;
}

?>
