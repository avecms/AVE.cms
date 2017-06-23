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

global $AVE_Template;

require(BASE_DIR . '/class/class.docs.php');
require(BASE_DIR . '/class/class.settings.php');
$AVE_Settings = new AVE_Settings;
$AVE_Document   = new AVE_Document;

$AVE_Template->config_load(BASE_DIR . '/admin/lang/' . $_SESSION['admin_language'] . '/settings.txt','settings');

switch($_REQUEST['action'])
{
	case '':
		switch ($_REQUEST['sub'])
		{
			case '':
			if(check_permission_acp('gen_settings'))
			{
				$AVE_Settings->settingsShow();
				break;
			}

			case 'case':
			if(check_permission_acp('gen_settings_more'))
			{
				$AVE_Settings->settingsCase();
				break;
			}

			case 'save':
				if (isset($_REQUEST['more'])) {
					if(check_permission_acp('gen_settings_more')) $AVE_Settings->settingsCase();
				} else {
					if(check_permission_acp('gen_settings')) $AVE_Settings->settingsSave();
				}
				break;

			case 'countries':
			if(check_permission_acp('gen_settings_countries'))
			{
				if (isset($_REQUEST['save']) && $_REQUEST['save'] == 1)
				{
					$AVE_Settings->settingsCountriesSave();

					header('Location:index.php?do=settings&sub=countries&cp=' . SESSION);
					exit;
				}
				$AVE_Settings->settingsCountriesList();
				break;
			}

			case 'language':
			if(check_permission_acp('gen_settings_languages'))
			{
				if(isset($_REQUEST['func'])){
					switch($_REQUEST['func'])
					{
						case 'default':
							if(isset($_REQUEST['Id'])){
								$exists=$AVE_DB->Query("SELECT Id FROM ".PREFIX."_settings_lang WHERE Id=".(int)$_REQUEST['Id'])->GetCell();
								if($exists){
									$AVE_DB->Query("UPDATE ".PREFIX."_settings_lang SET lang_default=0");
									$AVE_DB->Query("UPDATE ".PREFIX."_settings_lang SET lang_default=1 WHERE Id=".(int)$_REQUEST['Id']." LIMIT 1");
								}
							}
							header('Location:index.php?do=settings&sub=language&cp=' . SESSION);
							exit;

						case 'on':
							if(isset($_REQUEST['Id'])){
								$AVE_DB->Query("UPDATE ".PREFIX."_settings_lang SET lang_status=1 WHERE Id=".(int)$_REQUEST['Id']);
							}
							header('Location:index.php?do=settings&sub=language&cp=' . SESSION);
							exit;

						case 'off':
							if(isset($_REQUEST['Id'])){
								$AVE_DB->Query("UPDATE ".PREFIX."_settings_lang SET lang_status=0 WHERE Id=".(int)$_REQUEST['Id']);
							}
							header('Location:index.php?do=settings&sub=language&cp=' . SESSION);
							exit;

						case 'save':
							$AVE_Settings->settingsLanguageEditSave();
							exit;
					}
				}
				else
				{
					$AVE_Settings->settingsLanguageList();
					break;
				}
			}

			case 'editlang':
			if(check_permission_acp('gen_settings_languages'))
			{
				$AVE_Settings->settingsLanguageEdit();
				break;
			}

			case 'clearcache':
			if(check_permission_acp('cache_clear'))
			{
				$AVE_Template->CacheClear();
				exit;
			}

			case 'clearthumb':
			if(check_permission_acp('cache_thumb'))
			{
				$AVE_Template->ThumbnailsClear();
				exit;
			}

			case 'clearrevision':
			if(check_permission_acp('document_revisions'))
			{
				$AVE_Document->documentsRevisionsClear();
				exit;
			}

			case 'clearcounter':
			if(check_permission_acp('gen_settings'))
			{
				$AVE_Document->documentCounterClear();
				exit;
			}

			case 'showcache':
				cacheShow();
				exit;
		}
	break;

	//-- v3.2
	case 'paginations':
		$AVE_Settings->settingsPaginationsList();
	break;

	case 'new_paginations':
		$AVE_Settings->settingsPaginationsNew();
	break;

	case 'edit_paginations':
		$AVE_Settings->settingsPaginationsEdit();
	break;

	case 'save_paginations':
		$AVE_Settings->settingsPaginationsSave();
	break;

	case 'del_paginations':
		$AVE_Settings->settingsPaginationsDel();
	break;
	//-- v3.2
}

?>