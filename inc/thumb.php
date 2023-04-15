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

	if (! defined('BASE_DIR'))
		define('BASE_DIR', str_replace("\\", "/", dirname(dirname(__FILE__))));

	if (! function_exists('iptc_make_tag'))
	{
		function iptc_make_tag ($rec, $data, $value)
		{
			$length = strlen($value);
			$retval = chr(0x1C) . chr($rec) . chr($data);

			if($length < 0x8000)
			{
				$retval .= chr($length >> 8) .  chr($length & 0xFF);
			}
			else
				{
					$retval .= chr(0x80) .
							   chr(0x04) .
							   chr(($length >> 24) & 0xFF) .
							   chr(($length >> 16) & 0xFF) .
							   chr(($length >> 8) & 0xFF) .
							   chr($length & 0xFF);
				}

			return $retval . $value;
		}
	}

	/**
	 * Creates directory
	 *
	 * @param  string  $path Path to create
	 * @param  integer $mode Optional permissions
	 * @return boolean Success
	 */
	if (! function_exists('_mkdir'))
	{
		function _mkdir ($path, $mode = 0777)
		{
			$old = umask(0);
			$res = @mkdir($path, $mode);
			umask($old);

			return $res;
		}
	}

	/**
	 * Creates directories recursively
	 *
	 * @param  string  $path Path to create
	 * @param  integer $mode Optional permissions
	 * @return boolean Success
	 */
	if (! function_exists('rmkdir'))
	{
		function rmkdir ($path, $mode = 0777)
		{
			return is_dir($path) || (mkdir(dirname($path), $mode) && _mkdir($path, $mode));
		}
	}

	if (filesize(BASE_DIR . '/config/config.inc.php'))
		require_once BASE_DIR . '/config/config.inc.php';

	require_once BASE_DIR . '/inc/config.php';

	//-- Разрешенные расширения файлов
	$allowedExt = [
		'jpg',
		'jpeg',
		'png',
		'gif',
		'JPG',
		'JPEG',
		'PNG',
		'GIF'
	];

	//-- Разрешенные размеры миниатюр
	$allowedSize = (defined('THUMBNAIL_SIZES') && THUMBNAIL_SIZES != '')
		? explode(',', trim(THUMBNAIL_SIZES))
		: [];

	//-- Разрешения для админпанели
	$allowedAdmin = [
		't128x128',
		'f128x128'
	];

	//-- Ссылка на файл
	$imagefile = urldecode($_SERVER['REQUEST_URI']);

	//-- Вызов чере $_GET параметры
	//-- ToDo
	if (! empty($_REQUEST['thumb']))
	{
		$imagefile = '/'.
			rtrim(
				dirname($_REQUEST['thumb'])
				. '/' . THUMBNAIL_DIR . '/'
				. (str_replace(
					'.',
					(empty($_REQUEST['mode'])
						? '-t'
						: '-' . $_REQUEST['mode']) . ((empty($_REQUEST['width']) && empty($_REQUEST['height']))
							? '128'
							: intval(@$_REQUEST['width'])) . 'x' . ((empty($_REQUEST['width']) && empty($_REQUEST['height']))
								? '128'
								: intval(@$_REQUEST['height'])) . '.',
					basename($_REQUEST['thumb'])
					)
				),
			'/');
	}

	//-- Если пришел прямой вызов файла, то сразу отрубаем его
	if ($_SERVER['REQUEST_URI'] == '/inc/thumb.php')
	{
		die('No image');
	}

	//-- Если файл существует, показываем его
	if (file_exists(BASE_DIR . $imagefile))
	{
		$img_data = @getimagesize(BASE_DIR . $imagefile);

		header('max-age=315360000, public', true);
		header('Content-Type:' . $img_data['mime'], true);
		header('Cache-Control: max-age=:315360000', true);
		header("Expires: " . gmdate("D, d M Y H:i:s", time() + THUMBNAIL_CACHE_LIFETIME) . " GMT");
		header("Content-Length: " . (string) filesize(BASE_DIR . $imagefile), true);

		readfile(BASE_DIR . $imagefile);

		exit;
	}

	require_once BASE_DIR . '/inc/init.php';

	list(, $thumbPath) = explode('/' . UPLOAD_DIR . '/', dirname($imagefile), 2);

	$lenThumbDir = strlen(THUMBNAIL_DIR);

	// --
	if ($lenThumbDir && substr($thumbPath, -$lenThumbDir) != THUMBNAIL_DIR)
	{
		if (! file_exists(BASE_DIR . $imagefile))
		{
			report404();
			header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
		}
		exit(0);
	}

	$thumbPath = BASE_DIR . '/' . UPLOAD_DIR . '/' . $thumbPath;
	$imagePath = $lenThumbDir ? dirname($thumbPath) : $thumbPath;

	$thumbName = basename($imagefile);
	$nameParts = explode('.', $thumbName);
	$countParts = count($nameParts);

	if ($countParts < 2 || ! in_array(strtolower(end($nameParts)), $allowedExt))
	{
		exit(0);
	}

	$matches = [];

	//-- Смотрим переданные параметры
	preg_match('/-(r|c|f|t|s)(\d+)x(\d+)(r)*$/i', $nameParts[$countParts-2], $matches);

	//-- Если нет параметров, отдаем 404
	if (! isset($matches[0]))
	{
		report404();

		header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
		exit(0);
	}

	$check = ltrim($matches[0], '-');

	//-- Проверяем разрешен ли данный размер для миниатюры
	if (! empty($allowedSize) && ! in_array($check, $allowedSize))
	{
		if (! in_array($check, $allowedAdmin))
		{
			report404();

			header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
			exit(0);
		}
	}

	//-- Если есть параметр rotate
	if (isset($matches[4]))
	{
		list ($size, $method, $width, $height, $rotate) = $matches;
	}
	//-- Иначе
	else
		{
			list ($size, $method, $width, $height) = $matches;
			$rotate = false;
		}

	$nameParts[$countParts-2] = substr($nameParts[$countParts-2], 0, -strlen($size));
	$imageName = implode('.', $nameParts);

	$save = true;

	if (! file_exists("$imagePath/$imageName"))
	{
		$l = "$imagePath/$imageName";

		if (file_exists($l . '.tmp'))
		{
			$url = trim(file_get_contents($l . '.tmp'), ABS_PATH);

			$img = CURL_file_get_contents($url);

			if ($img)
			{
				file_put_contents("$imagePath/$imageName", $img);

				//setEXIFF("$imagePath/$imageName");

				$save = true;
			}

			@unlink($l . '.tmp');
		}
	}

	if (! file_exists("$imagePath/$imageName"))
	{
		header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');

		report404();

		$imageName = 'noimage.png';

		if (! file_exists("$imagePath/$imageName"))
			$imagePath = BASE_DIR . '/' . UPLOAD_DIR . '/images';

		if (! file_exists("$imagePath/$imageName"))
			exit(0);

		$save = false;
	}

	define('IMAGE_TOOLBOX_DEFAULT_JPEG_QUALITY', JPG_QUALITY);

	require BASE_DIR . '/class/class.thumbnail.php';

	$thumb = new Image_Toolbox("$imagePath/$imageName");

	//-- Методы генерации миниатюр
	switch ($method)
	{
		case 'r':
			$thumb->newOutputSize((int)$width, (int)$height, 0, (boolean)$rotate);
			break;

		case 'c':
			$thumb->newOutputSize((int)$width, (int)$height, 1, (boolean)$rotate);
			break;

		case 'f':
			$thumb->newOutputSize((int)$width, (int)$height, 2, false, '#ffffff');
			break;

		case 't':
			$thumb->newOutputSize((int)$width, (int)$height, 3, false);
			break;

		case 's':
			$thumb->newOutputSize((int)$width, (int)$height, 4, (boolean)$rotate);
			break;
	}

	//Blend
	//$thumb->addImage(BASE_DIR . '/' . 'uploads/gallery/watermark.gif');
	//$thumb->blend('right -10', 'bottom -10', IMAGE_TOOLBOX_BLEND_COPY, 70);

	//Text
	//$thumb->addText('Мой текст', BASE_DIR . '/inc/fonts/ft16.ttf', 16, '#709536', 'right -10', 'bottom -10');
	//if ($width > 200){
	//	$thumb->addImage(BASE_DIR . '/' . 'uploads/gallery/watermark.gif');
	//	$thumb->blend('right -10', 'bottom -10', IMAGE_TOOLBOX_BLEND_COPY, 70);
	//}

	$thumb->output();

	//-- Если можно сохранять миниатюру
	if ($save)
	{
		if (! file_exists($thumbPath) && ! mkdir($thumbPath, 0777, true))
			exit(0);

		if ($thumb->save("$thumbPath/$thumbName"))
		{
			$old = umask(0);
			chmod("$thumbPath/$thumbName", 0777);
			umask($old);
		}

		if ($thumb->_img['main']['type'] == 2)
		{
			$image = getimagesize("$thumbPath/$thumbName", $info);

			if (! isset($info['APP13']))
			{
				//-- Если в настройках разрешена генерация IPTC тегов для миниатюр
				if (THUMBNAIL_IPTC)
				{
					$sitename= get_settings('site_name');

					// установка IPTC тэгов
					$iptc = [
						'2#120' => iconv("UTF-8", "WINDOWS-1251", $sitename),
						'2#116' => HOST
					];

					// Преобразование IPTC тэгов в двоичный код
					$data = '';

					foreach($iptc AS $tag => $string)
					{
						$tag = substr($tag, 2);
						$data .= iptc_make_tag(2, $tag, $string);
					}

					// Встраивание IPTC данных
					$content = iptcembed($data, "$thumbPath/$thumbName");

					// запись нового изображения в файл
					$fp = fopen("$thumbPath/$thumbName", "wb");
					fwrite($fp, $content);
					fclose($fp);
				}
			}
		}
	}
?>