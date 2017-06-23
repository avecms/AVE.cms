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
 * Функция дописывает в JPG файлы IPTC Tag
 *
 * @param string $rec
 * @param string $data
 * @param string $value
 * @return string
 */
function iptc_make_tag($rec, $data, $value)
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

/**
 * Функция накладывает watermark на заданный файл
 *
 * @param string $file URL Файла
 * @param string $position Позиция
 * @param int $transparency Прозарчность
 * @return string link
 */
function watermarks($file, $position='center', $transparency=100) {

	global $AVE_DB;

	if (!defined('WATERMARKS_DIR') || !defined('WATERMARKS_FILE')) exit(0);

	$save = true;

	$margin = 10;

	$file_info = pathinfo($file);

	$watermarkFile = BASE_DIR . '/' . WATERMARKS_FILE;
	$watermarkDir  = BASE_DIR . '/' . WATERMARKS_DIR;

	$imagePath = BASE_DIR . '/' . trim($file_info['dirname'], '/');
	$imageName = $file_info['basename'];

	$copyPath = $watermarkDir . '/' . trim($file_info['dirname'], '/');
	$copyName = $imageName;

	if(!is_dir($watermarkDir)) {
		@mkdir($watermarkDir, 0777, true);
		write_htaccess_deny($watermarkDir);
	}

	if(file_exists("$copyPath/$copyName") || !file_exists("$imagePath/$imageName")){
		$save = false;
	}

	if(file_exists($watermarkFile) && file_exists("$imagePath/$imageName")){

		$size_image =  getimagesize("$imagePath/$imageName");
		$size_wtmrk =  getimagesize($watermarkFile);

		list($xImage, $yImage) = $size_image;
		list($xWtmrk, $yWtmrk) = $size_wtmrk;

		if ($xImage < $xWtmrk || $yImage < $yWtmrk) {
			$save = false;
		} else {
			$save = true;
		}
	}

	if (file_exists("$copyPath/$copyName")) $save = false;

	if ($save){
		if (!is_dir($copyPath) && !@mkdir($copyPath, 0777, true)) exit(0);
		require_once BASE_DIR.'/class/class.thumbnail.php';
		$watermark = new Image_Toolbox("$imagePath/$imageName");

		if (rename("$imagePath/$imageName", "$copyPath/$copyName"))
		{
			$old = umask(0);
			chmod("$copyPath/$copyName", 0777);
			umask($old);
		}

		switch ($position)
		{
			case 'top':
			case 'topcenter':
				$xLogoPosition = 'center -';
				$yLogoPosition = 'top +'.$margin;
				break;

			case 'topleft':
				$xLogoPosition = 'left +'.$margin;
				$yLogoPosition = 'top +'.$margin;
				break;

			case 'topright':
				$xLogoPosition = 'right -'.$margin;
				$yLogoPosition = 'top +'.$margin;
				break;

			case 'center':
				$xLogoPosition = 'center -';
				$yLogoPosition = 'center -';
				break;

			case 'centerleft':
				$xLogoPosition = 'left +'.$margin;
				$yLogoPosition = 'center -';
				break;

			case 'centerright':
				$xLogoPosition = 'right -'.$margin;
				$yLogoPosition = 'center -';
				break;

			case 'bottom':
			case 'bottomcenter':
				$xLogoPosition = 'center -';
				$yLogoPosition = 'bottom -'.$margin;
				break;

			case 'bottomleft':
				$xLogoPosition = 'left +'.$margin;
				$yLogoPosition = 'bottom -'.$margin;
				break;

			case 'bottomright':
				$xLogoPosition = 'right -'.$margin;
				$yLogoPosition = 'bottom -'.$margin;
				break;

			case 'repeat':
				$xLogoPosition = 'repeat ';
				$yLogoPosition = 'repeat ';
				break;

			default:
				$xLogoPosition = 'center -';
				$yLogoPosition = 'center -';
				break;
		}

		$watermark->addImage($watermarkFile);
		$watermark->blend($xLogoPosition, $yLogoPosition, IMAGE_TOOLBOX_BLEND_COPY, $transparency);
		$watermark->save("$imagePath/$imageName");

		if($watermark->_img['main']['type']==2){
			$image = getimagesize("$imagePath/$imageName", $info);
			if(!isset($info['APP13']))
			{

				$sitename = get_settings('site_name');

				// установка IPTC тэгов
				$iptc = array(
					'2#120' => iconv("UTF-8","WINDOWS-1251",$sitename),
					'2#116' => "http://".$_SERVER['SERVER_NAME']
				);

				// Преобразование IPTC тэгов в двоичный код
				$data = '';

				foreach($iptc as $tag => $string)
				{
					$tag = substr($tag, 2);
					$data .= iptc_make_tag(2, $tag, $string);
				}

				// Встраивание IPTC данных
				$content = iptcembed($data, "$imagePath/$imageName");

				// запись нового изображения в файл
				$fp = fopen("$imagePath/$imageName", "wb");
				fwrite($fp, $content);
				fclose($fp);
			}
		}

		unset($watermark);
	}

	return $file;
}

?>
