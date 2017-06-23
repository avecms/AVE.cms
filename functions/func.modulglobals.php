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

/**
 * Функция формирует глобальный массив $mod с элементами:
 * <pre>
 *      tpl_dir      путь к папке с шаблонами модуля
 *      theme_folder имя папки с файлами дизайна
 *      config_vars  массив с языковыми переменными модуля
 * </pre>
 * Формирует и передаёт в шаблонизатор:
 * <pre>
 *      $tpl_dir     путь к папке с шаблонами модуля
 *      $mod_dir     имя папки с модулями
 *      $config_vars массив с языковыми переменными модуля
 * </pre>
 * Регистрирует в шаблонизаторе функцию in_array
 *
 * @param string $modulepath имя папки модуля
 * @param string $lang_section секция языкового файла
 */
function set_module_globals($modulepath, $lang_section = false)
{
	global $mod, $AVE_Template;

	$tpl_dir   = BASE_DIR . '/modules/' . $modulepath . '/templates/';
	$lang_file = BASE_DIR . '/modules/' . $modulepath . '/lang/' . $_SESSION['user_language'] . '.txt';

	if (!file_exists($lang_file))
	{
		$lang_file = BASE_DIR . '/modules/' . $modulepath . '/lang/ru.txt';
	}

	if (!file_exists($lang_file))
	{
		display_notice('Ошибка! Отсутствует языковой файл. Пожалуйста, проверьте язык, установленный по умолчанию, в файле '.ABS_PATH.'inc/config.php');
		exit;
	}

	if ($lang_section === false)
	{
		$AVE_Template->config_load($lang_file);
	}
	else
	{
		$AVE_Template->config_load($lang_file, $lang_section);
	}
	$config_vars = $AVE_Template->get_config_vars();

	$AVE_Template->assign('tpl_dir', $tpl_dir);
	$AVE_Template->assign('mod_dir', BASE_DIR . '/modules');
	$AVE_Template->assign('config_vars', $config_vars);

	$mod['tpl_dir'] = $tpl_dir;
	$mod['theme_folder'] = defined('THEME_FOLDER') ? THEME_FOLDER : DEFAULT_THEME_FOLDER;
	$mod['config_vars'] = $config_vars;

	$AVE_Template->register_function('in_array', 'in_array');
}

?>