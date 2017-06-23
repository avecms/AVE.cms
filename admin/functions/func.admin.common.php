<?php

/**
 * AVE.cms
 *
 * @package AVE.cms
 * @subpackage admin
 * @filesource
 */

/**
 * Если был referer, то перенапрявляем на него
 *
 * @param 
 * @return $link
 */
function get_referer_admin_link()
{
	static $link = null;

	if ($link === null)
	{
		if (isset($_SERVER['HTTP_REFERER']))
		{
			$link = parse_url($_SERVER['HTTP_REFERER']);
			$ok = (trim($link['host']) == $_SERVER['HTTP_HOST']) ? true : false;
			$ok = (trim($link['path']) != '/admin/admin.php') ? true : false;

		}
		$link = ($ok === true ? $_SERVER['HTTP_REFERER'] : '/admin/index.php');
	}

	return $link;
}


/**
 * Получаем кол-во записей в журналах событий
 *
 * @return Array массив из кол-ва записей
 */
function getLogRecords(){
	global $AVE_DB, $AVE_Template;

	$logs = array();
	$logdata = array();
	$log404 = array();
	$logsql = array();

	$_404dir = BASE_DIR . '/cache/404.php';
	$_logdir = BASE_DIR . '/cache/log.php';
	$_sqldir = BASE_DIR . '/cache/sql.php';

	if(file_exists($_logdir))
		@eval('?>' . file_get_contents($_logdir) . '<?');

	$logs['logs'] = count($logdata);

	if(file_exists($_404dir))
		@eval('?>' . file_get_contents($_404dir) . '<?');

	$logs['404'] = count($log404);

	if(file_exists($_sqldir))
		@eval('?>' . file_get_contents($_sqldir) . '<?');

	$logs['sql'] = count($logsql);

	unset($logdata);
	unset($log404);
	unset($logsql);

	// Передаем данные в шаблон для вывода
	$AVE_Template->assign('logs', $logs);
}

/**
 * Список пользователей за последние $onlinetime секунд
 *
 * @param int $onlinetime количество секунд
 * @return Array массив из пользователей отсортированный по последней активности
 */
function get_online_users($onlinetime=USERS_TIME_SHOW){
	global $AVE_DB, $AVE_Template;
	$time=(time()-intval($onlinetime));
	$sql=@$AVE_DB->Query("SELECT * FROM ".PREFIX."_users WHERE last_visit>".$time." ORDER BY last_visit DESC");
	$online_users=Array();
	while ($row = $sql->FetchRow())
	{
		$row->user_name = get_username_by_id($row->Id);
		$row->user_group_name = get_usergroup_by_id($row->user_group);
		array_push($online_users,$row);
	}
	$AVE_Template->assign('online_users', $online_users);
}

/**
 * Форматированный вывод размера
 *
 * @param int $file_size размер
 * @return string нормированный размер с единицой измерения
 */
function format_size($file_size)
{
	if ($file_size >= 1073741824)
	{
		$file_size = round($file_size / 1073741824 * 100) / 100 . ' Gb';
	}
	elseif ($file_size >= 1048576)
	{
		$file_size = round($file_size / 1048576 * 100) / 100 . ' Mb';
	}
	elseif ($file_size >= 1024)
	{
		$file_size = round($file_size / 1024 * 100) / 100 . ' Kb';
	}
	else
	{
		$file_size = $file_size . ' b';
	}

	return $file_size;
}

/**
 * Извлечение из БД статистики по основным компонентам системы
 *
 */
function get_ave_info()
{
	global $AVE_DB, $AVE_Template;

	$cnts = array();

	$cnts['templates'] = $AVE_DB->Query("SELECT COUNT(*) FROM " . PREFIX . "_templates")->GetCell();
	$cnts['documents'] = $AVE_DB->Query("SELECT COUNT(*) FROM " . PREFIX . "_documents")->GetCell();
	$cnts['request']   = $AVE_DB->Query("SELECT COUNT(*) FROM " . PREFIX . "_request")  ->GetCell();
	$cnts['rubrics']   = $AVE_DB->Query("SELECT COUNT(*) FROM " . PREFIX . "_rubrics")  ->GetCell();

	$sql = $AVE_DB->Query("
		SELECT
			`ModuleStatus`,
			COUNT(`ModuleStatus`) AS cntStatus
		FROM " . PREFIX . "_module
		GROUP BY `ModuleStatus`
	");
	while ($row = $sql->FetchRow())
	{
		$cnts['modules_' . $row->ModuleStatus] = $row->cntStatus;
	}

	$sql = $AVE_DB->Query("
		SELECT
			status,
			COUNT(status) AS cntStatus
		FROM " . PREFIX . "_users
		GROUP BY status
	");
	while ($row = $sql->FetchRow())
	{
		$cnts['users_' . $row->status] = $row->cntStatus;
	}

	$AVE_Template->assign('cnts', $cnts);
}

/**
 * Размер дириктории
 *
 * @param string $directory наименование директории
 * @return int
 */
function get_dir_size($directory)
{
	if (!is_dir($directory)) return -1;
	$size = 0;
	if ($DIR = opendir($directory))
	{
		while (($dirfile = readdir($DIR)) !== false)
		{
			if (@is_link($directory . '/' . $dirfile) || $dirfile == '.' || $dirfile == '..') continue;
			if (@is_file($directory . '/' . $dirfile))
			{
				$size += filesize($directory . '/' . $dirfile);
			}
			elseif (@is_dir($directory . '/' . $dirfile))
			{
				$dirSize = get_dir_size($directory . '/' . $dirfile);
				if ($dirSize >= 0)
				{
					$size += $dirSize;
				}
				else
				{
					return -1;
				}
			}
		}
		closedir($DIR);
	}

	return $size;
}

/**
 * Размер базы данных
 *
 * @return int
 */
function get_mysql_size()
{
	global $AVE_DB;

	$mysql_size = 0;
	$sql = $AVE_DB->Query("SHOW TABLE STATUS LIKE '" . PREFIX . "_%'");
	while ($row = $sql->FetchAssocArray())
	{
		$mysql_size += $row['Data_length'] + $row['Index_length'];
	}

	return format_size($mysql_size);
}

function get_ave_tags($srcfile)
{
	if (@include_once($srcfile))
	{
		reset ($vorlage);
		$vl = array();

		while (list($key, $value) = each($vorlage))
		{
			$tag = new stdClass;
			$tag->cp_tag = $key;
			$tag->cp_desc = $value;
			array_push($vl, $tag);
			unset($tag);
		}

		return $vl;
	}

	return null;
}


function get_all_templates()
{
	global $AVE_DB;

	static $templates = null;

	if ($templates == null)
	{
		$templates = array();

		$sql = $AVE_DB->Query("
			SELECT
				Id,
				template_title
			FROM " . PREFIX . "_templates
		");

		while ($row = $sql->FetchRow())
		{
			array_push($templates, $row);
		}
	}

	return $templates;
}

function get_editable_module()
{
	global $AVE_DB, $AVE_Template;

	$modules = array();
	$sql = $AVE_DB->Query("
		SELECT
			ModuleName,
			ModuleSysName
		FROM " . PREFIX . "_module
		WHERE `ModuleStatus` = '1'
		AND `ModuleAdminEdit` = '1'
		ORDER BY ModuleName ASC
	");
	while ($row = $sql->FetchRow())
	{
		if (check_permission('mod_' . $row->ModuleSysName))
		{
			array_push($modules, $row);
		}
	}

	$AVE_Template->assign('modules', $modules);
}

function get_mime_type($file)
{
	$file_extension = strtolower(mb_substr(strrchr($file, '.'), 1));

	switch ($file_extension)
	{
		case 'psd': $ctype = 'image/x-photoshop'; break;
		case 'rar': $ctype = 'application/x-rar-compressed'; break;
		case 'zip': $ctype = 'application/x-zip-compressed'; break;
		case 'pdf': $ctype = 'application/pdf'; break;
		case 'bz2': $ctype = 'application/bzip2'; break;
		case 'doc':
		case 'dot':
		case 'wiz':
		case 'wzs': $ctype = 'application/msword'; break;
		case 'eps': $ctype = 'application/postscript'; break;
		case 'pot':
		case 'ppa':
		case 'pps':
		case 'ppt':
		case 'pwz': $ctype = 'application/vnd.ms-powerpoint'; break;
		case 'rtf': $ctype = 'application/rtf'; break;
		case 'rnx': $ctype = 'application/vnd.rn-realmedia'; break;
		case 'hlp': $ctype = 'hlp'; break;
		case 'gtar': $ctype = 'application/x-gtar'; break;
		case 'gzip':
		case 'tgz': $ctype = 'application/x-gzip'; break;
		case 'lnx': $ctype = 'application/x-latex'; break;
		case 'exe': $ctype = 'application/x-msdownload'; break;
		case 'swf': $ctype = 'application/x-shockwafe-flash'; break;
		case 'xml': $ctype = 'application/xml'; break;
		case 'midi': $ctype = 'audio/midi'; break;
		case 'mp3':
		case 'mp2':
		case 'mpga': $ctype = 'audio/mpeg'; break;
		case 'wav': $ctype = 'audio/wav'; break;
		case 'bmp': $ctype = 'audio/wav'; break;
		case 'gif': $ctype = 'image/gif'; break;
		case 'jpeg':
		case 'jpg':
		case 'jpe': $ctype = 'image/jpeg'; break;
		case 'png': $ctype = 'image/png'; break;
		case 'tif':
		case 'tiff': $ctype = 'image/tiff'; break;
		case 'ico': $ctype = 'image/x-icon'; break;
		case 'csv': $ctype = 'text/comma-separated-values'; break;
		case 'css': $ctype = 'text/css'; break;
		case 'htm':
		case 'html':
		case 'shtml': $ctype = 'text/html'; break;
		case 'txt':
		case 'klp':
		case 'tex':
		case 'php':
		case 'asp':
		case 'aspx':
		case 'php3':
		case 'php4':
		case 'php5':
		case 'sql': $ctype = 'text/plain'; break;
		case 'xml': $ctype = 'text/xml'; break;
		case 'xhtm': $ctype = 'text/xhtml'; break;
		case 'wml': $ctype = 'text/wml'; break;
		case 'mpeg':
		case 'mpg':
		case 'mpe':
		case 'mlv':
		case 'mpa':
		case 'wma':
		case 'wmv': $ctype = 'video/mpeg'; break;
		case 'avi': $ctype = 'video/x-msvideo'; break;
		case 'mov': $ctype = 'video/quicktime'; break;
		case 'xls': $ctype = 'application/vnd.ms-excel'; break;
		case 'ai': $ctype = 'application/postscript'; break;
		case 'rm': $ctype = 'application/vnd.rn-realmedia'; break;
		case 'gz': $ctype = 'application/x-gzip'; break;
		case 'js': $ctype = 'application/x-javascript'; break;
		case 'pl':
		case 'cc': $ctype = 'text/plain'; break;
		case 'qt': $ctype = 'video/quicktime'; break;
		default : $ctype='application/force-download';
	}

	return $ctype;
}

function file_download($filename, $retbytes = true)
{
	$chunksize = 1*(1024*1024);
	$buffer = '';
	$cnt = 0;

	$handle = fopen($filename, 'rb');

	if ($handle === false) return false;

	while (!feof($handle))
	{
		$buffer = fread($handle, $chunksize);
		echo $buffer;
		flush();
		if ($retbytes) $cnt += strlen($buffer);
	}

	$status = fclose($handle);

	if ($retbytes && $status) return $cnt;

	return $status;
}

function is_php_code($check_code)
{
	$check_code = stripslashes($check_code);
	$check_code = str_replace(' ', '', $check_code);
	$check_code = strtolower($check_code);

	if (strpos($check_code, '<?php') !== false ||
		strpos($check_code, '<?') !== false ||
		strpos($check_code, '<? ') !== false ||
		strpos($check_code, '<?=') !== false ||
		strpos($check_code, '<script language="php">') !== false ||
		strpos($check_code, 'language="php"') !== false ||
		strpos($check_code, "language='php'") !== false ||
		strpos($check_code, 'language=php') !== false)
	{
		return true;
	}

	return false;
}

function check_permission_acp($perm)
{
	if (!check_permission($perm))
	{
		if (!defined('NOPERM')) define('NOPERM', 1);
		return false;
	}

	return true;
}

//Проверка на наличие модуля Контакты и новых писем
function ContactsModuleCheck() {
	global $AVE_DB, $AVE_Template;

  $sql = $AVE_DB->Query("SELECT * FROM " . PREFIX . "_module WHERE ModuleFunction = 'contact' and  ModuleStatus  = '1'");
	$enable = $sql->numrows();
	if ($enable != "0" || $enable != ""){
		$contacts = "1";
		$sql_num = $AVE_DB->Query("SELECT * FROM " . PREFIX . "_modul_contact_info WHERE Aw_Zeit = '0'");
		$num_posts = $sql_num->numrows();
	} else {
	  $contacts = "0";
	}
  $AVE_Template->assign('num_posts', $num_posts);
  $AVE_Template->assign('contacts', $contacts);
}

//Проверка на наличие модуля Логин
function LoginModuleCheck() {
	global $AVE_DB, $AVE_Template;

  $sql = $AVE_DB->Query("SELECT * FROM " . PREFIX . "_module WHERE ModuleFunction = 'mod_login' and  ModuleStatus  = '1'");
	$enable = $sql->numrows();
	if ($enable != "0" || $enable != ""){
		$login_menu = "1";
	} else {
	  $login_menu = "0";
	}
  $AVE_Template->assign('login_menu', $login_menu);
}

//Выводим на главную список последних 15 документов
function DisplayMainDocuments() {
	global $AVE_DB, $AVE_Template;

	$doc_start = array();
	$sql = $AVE_DB->Query("
		SELECT 
			doc.*,
			rub.rubric_admin_teaser_template
		FROM " . PREFIX . "_documents doc
		LEFT JOIN " . PREFIX . "_rubrics AS rub ON rub.Id = doc.rubric_id
		WHERE 1 = 1
			AND rub.rubric_docs_active = '1'
		ORDER BY doc.document_published DESC LIMIT 0,10");
		while($row = $sql->fetchrow()) {
			$row->rubric_title = showrubricName($row->rubric_id);
			$row->document_title = stripslashes(htmlspecialchars_decode(pretty_chars($row->document_title)));
			$row->document_breadcrum_title = stripslashes(htmlspecialchars_decode(pretty_chars($row->document_breadcrum_title)));
			$row->document_author = get_username_by_id($row->document_author_id); // Получаем имя пользователя (Автора)
			$row->cantEdit		= 0;
			$row->canDelete	   = 0;
			$row->canEndDel	   = 0;
			$row->canOpenClose	= 0;
			$row->rubric_admin_teaser_template=@eval2var('?>'.($row->rubric_admin_teaser_template>'' ? @showrequestelement($row,$row->rubric_admin_teaser_template) : '').'<?');

			// разрешаем редактирование и удаление
			// если автор имеет право изменять свои документы в рубрике
			// или пользователю разрешено изменять все документы в рубрике
			if ( ($row->document_author_id == @$_SESSION['user_id']
				&& isset($_SESSION[$row->rubric_id . '_editown']) && @$_SESSION[$row->rubric_id . '_editown'] == 1)
				|| (isset($_SESSION[$row->rubric_id . '_editall']) && $_SESSION[$row->rubric_id . '_editall'] == 1) )
			{
					$row->cantEdit  = 1;
					$row->canDelete = 1;
			}
			// запрещаем редактирование главной страницы и страницу ошибки 404 если требуется одобрение Администратора
			if ( ($row->Id == 1 || $row->Id == PAGE_NOT_FOUND_ID)
				&& isset($_SESSION[$row->rubric_id . '_newnow']) && @$_SESSION[$row->rubric_id . '_newnow'] != 1)
			{
				$row->cantEdit = 0;
			}
			// разрешаем автору блокировать и разблокировать свои документы если не требуется одобрение Администратора
			if ($row->document_author_id == @$_SESSION['user_id']
				&& isset($_SESSION[$row->rubric_id . '_newnow']) && @$_SESSION[$row->rubric_id . '_newnow'] == 1)
			{
				$row->canOpenClose = 1;
			}
			// разрешаем всё, если пользователь принадлежит группе Администраторов или имеет все права на рубрику
			if (UGROUP == 1 || @$_SESSION[$row->rubric_id . '_alles'] == 1)
			{
				$row->cantEdit	 = 1;
				$row->canDelete	= 1;
				$row->canEndDel	= 1;
				$row->canOpenClose = 1;
			}
			// Запрещаем удаление Главной страницы и страницы с 404 ошибкой
			if ($row->Id == 1 || $row->Id == PAGE_NOT_FOUND_ID)
			{
				$row->canDelete = 0;
				$row->canEndDel = 0;
			}
			array_push($doc_start, $row);
		}
	$AVE_Template->assign('doc_start', $doc_start);
}

function showrubricName($id) {
	global $AVE_DB, $AVE_Template;

	$sql = $AVE_DB->Query("SELECT rubric_title FROM " . PREFIX . "_rubrics WHERE Id='$id'");
	$row = $sql->fetchrow();
	return $row->rubric_title;
}

function showuserName($id) {
	global $AVE_DB, $AVE_Template;

	$sql = $AVE_DB->Query("SELECT user_name FROM " . PREFIX . "_users WHERE Id='$id'");
	$row = $sql->fetchrow();
	return $row->user_name;
}

function cacheShow() {
	global $AVE_Template;

	$showCache = format_size(get_dir_size($AVE_Template->compile_dir)+get_dir_size($AVE_Template->cache_dir_root));
	echo json_encode(array($showCache, 'accept'));
}

function templateName($id) {
	global $AVE_DB, $AVE_Template;

	$sql = $AVE_DB->Query("
		SELECT * FROM " . PREFIX . "_templates
		WHERE Id = '$id'
		");
	$row = $sql->fetchrow();

	return $row->template_title;
}

function groupName($id) {
	global $AVE_DB, $AVE_Template;

	$sql = $AVE_DB->Query("
		SELECT * FROM " . PREFIX . "_user_groups
		WHERE user_group = '$id'
		");
	$row = $sql->fetchrow();

	return $row->user_group_name;
}

?>