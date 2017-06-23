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

if (!defined('ACP') || !check_permission('mediapool_int'))
{
	header('Location:index.php');
	exit;
}

global $AVE_DB, $AVE_Template;

ob_start();
ob_implicit_flush(0);

$_REQUEST['onlycontent'] = 1;

$max_size = 128; // максимальный размер миниатюры
$thumb_size = '-t' . $max_size . 'x' . $max_size; // формат миниатюр
$images_ext =  array('jpg', 'jpeg', 'png', 'gif', 'JPG', 'JPEG', 'PNG', 'GIF');

$upload_path = BASE_DIR . '/' . UPLOAD_DIR;

$lang = empty($_SESSION['admin_language']) ? 'ru' : $_SESSION['admin_language'];

$AVE_Template = new AVE_Template(BASE_DIR . '/admin/templates/browser');
$AVE_Template->config_load(BASE_DIR . '/admin/lang/' . $lang . '/main.txt');
$AVE_Template->assign('tpl_dir', 'templates/');
$AVE_Template->assign('ABS_PATH', '../');

if (!isset($_REQUEST['action'])) $_REQUEST['action'] = '';

switch ($_REQUEST['action'])
{
	case 'list':
		$dir = (empty($_REQUEST['dir'])
			|| strpos($_REQUEST['dir'], '..') !== false
			|| strpos($_REQUEST['dir'], '//') !== false) ? '/' : $_REQUEST['dir'];

		$path = $upload_path . (is_dir($upload_path . $dir) ? $dir : '/');

		$new_dir = $path . (isset($_REQUEST['newdir']) ? $_REQUEST['newdir'] : '');
		$new_dir_rezult = (!is_dir($new_dir) && !mkdir($new_dir, 0777));

		$skip_entry = array(THUMBNAIL_DIR, 'recycled', 'index.php');

		$dirs = array();
		$files = array();

		$d = @dir($path);
		while (false !== ($entry = @$d->read()))
		{
			if (in_array($entry, $skip_entry) || $entry{0} === '.') continue;

			if (is_dir($path . $entry))
			{
				$dirs[$entry] = 'index.php?do=browser&type=' . $_REQUEST['type']
					. '&amp;action=list&amp;dir=' . $dir . $entry . '/';
			}
			else
			{
				$nameParts = explode('.', $entry);
				$ext = strtolower(end($nameParts));

				$file['icon'] = file_exists("templates/images/mediapool/{$ext}.gif") ? $ext : 'attach';
				$file['filesize'] = @round(@filesize($path . $entry)/1024, 2);
				$file['moddate'] = date("d.m.y, H:i", @filemtime($path . $entry));

				if (in_array($ext, $images_ext))
				{
					$nameParts[count($nameParts)-2] .= $thumb_size;
					$file['bild'] = '/' . UPLOAD_DIR . $dir . THUMBNAIL_DIR . '/' . implode('.', $nameParts);
				}
				else
				{
					$file['bild'] = 'templates/images/file.gif';
				}

				$files[$entry] = $file;
			}
		}
		$d->close();

		ksort($dirs);
		ksort($files);

		$AVE_Template->assign('new_dir_rezult', $new_dir_rezult);
		$AVE_Template->assign('recycled', strpos($dir, '/recycled/') === 0);
		$AVE_Template->assign('dirs', $dirs);
		$AVE_Template->assign('files', $files);
		$AVE_Template->assign('max_size', $max_size);
		$AVE_Template->assign('dir', $dir);
		$AVE_Template->assign('dirup', rtrim(dirname($dir), '\\/') . '/');
		$AVE_Template->assign('mediapath', UPLOAD_DIR);

		$AVE_Template->display('browser.tpl');
		break;

	case 'upload':
		if (check_permission('mediapool_add'))
		{
			$AVE_Template->display('browser_upload.tpl');
		}else{
			echo '<script type="text/javascript">window.close();</script>';
		}
		break;

	case 'upload2':
		header('Location:index.php?do=browser&type=image&target=' . $_REQUEST['target'] . '&tval=/' . UPLOAD_DIR . $_REQUEST['tval']);
		break;

	case 'delfile':
		if (check_permission('mediapool_del'))
		{
			if (empty($_REQUEST['file']) || empty($_REQUEST['dir'])) exit(0);

			$file_name = basename($_REQUEST['file']);

			$del_file = $upload_path . $_REQUEST['dir'] . $file_name;
			if (strpos($del_file, '..') !== false || !is_file($del_file)) exit(0);

			$recycled_path = $upload_path . '/recycled/';
			if (!is_dir($recycled_path) && !mkdir($recycled_path)) exit(0);

			do {$nameParts = explode('.', $file_name);
				$nameParts[count($nameParts)-2] .= '-' . uniqid(rand());
				$recycled_file_name = implode('.', $nameParts);
			} while (file_exists($recycled_path . $recycled_file_name));

			@copy($del_file, $recycled_path . $recycled_file_name);

			if (@unlink($del_file))
			{
				$nameParts = explode('.', $file_name);
				$ext = strtolower(end($nameParts));
				if (in_array($ext, $images_ext))
				{
					$nameParts[count($nameParts)-2] .= $thumb_size;
					@unlink($upload_path . $_REQUEST['dir'] . THUMBNAIL_DIR . '/' . implode('.', $nameParts));
				}

				reportLog($_SESSION['user_name'] . ' - удалил файл ('
					. UPLOAD_DIR . $_REQUEST['dir'] . $file_name  . ')');
			}
		}

		echo	'<script type="text/javascript">
					parent.frames[\'zf\'].location.href="index.php?do=browser&type=', $_REQUEST['type'], '&action=list&dir=', $_REQUEST['dir'], '";
				</script>';
		break;

	default:

		@list($target, $target_id) = explode('__', $_REQUEST['target']);

		$tval = '/';

		if (!empty($_REQUEST['tval']) && 0 === strpos($_REQUEST['tval'], '/' . UPLOAD_DIR . '/'))
		{
			if (is_dir(BASE_DIR . '/' . $_REQUEST['tval'])) {
				$tval = rtrim(substr($_REQUEST['tval'], strlen('/' . UPLOAD_DIR)), '\\/') . '/';
			}

			if (is_file(BASE_DIR . '/' . $_REQUEST['tval'])) {
				$tval = rtrim(dirname(substr($_REQUEST['tval'], strlen('/' . UPLOAD_DIR))), '\\/') . '/';
			}
		}

		$AVE_Template->assign('dir', $tval);
		$AVE_Template->assign('target', $target);
		$AVE_Template->assign('target_id', $target_id);
		$AVE_Template->assign('cppath', substr($_SERVER['PHP_SELF'], 0, -18));
		$AVE_Template->assign('mediapath', UPLOAD_DIR);

		$AVE_Template->display('browser_2frames.tpl');
		break;
}

$out = ob_get_clean();

echo $out;

?>
