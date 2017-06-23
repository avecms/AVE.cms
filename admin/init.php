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

require(BASE_DIR . '/inc/init.php');
require(BASE_DIR . '/admin/functions/func.admin.common.php');
require(BASE_DIR . '/lib/redactor/ckeditor/adapters/ckeditor.php');

$lang_system = $AVE_DB->Query("
	SELECT lang_alias_pref FROM " . PREFIX . "_settings_lang
	WHERE lang_default = '1'
")->GetCell();

$_SESSION['admin_language'] = $lang_system;

$AVE_Template = new AVE_Template(BASE_DIR . '/admin/templates');
$AVE_Template->assign('tpl_dir', ABS_PATH . 'admin/templates');

// Файлы шаблонов для CodeMirror
$AVE_Template->assign('codemirror_connect', BASE_DIR . '/lib/redactor/codemirror/codemirror_connect.tpl');
$AVE_Template->assign('codemirror_editor', BASE_DIR . '/lib/redactor/codemirror/codemirror_editor.tpl');

// Подключаем основные ланги
$AVE_Template->config_load(BASE_DIR . '/admin/lang/' . $_SESSION['admin_language'] . '/main.txt');

define('SESSION', session_id());
$AVE_Template->assign('sess', SESSION);
?>