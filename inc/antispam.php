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

function get_securecode()
{
	@require('./db.config.php');

	if (! isset($config)) die;

	if (! (isset($config) && isset($_GET['cp_secureimage']) && is_numeric($_GET['cp_secureimage']))) die;

	if (! @mysql_select_db($config['dbname'], @mysql_connect($config['dbhost'], $config['dbuser'], $config['dbpass']))) die;

	if (! $row = mysql_fetch_assoc(mysql_query("SELECT Code FROM " . $config['dbpref'] . "_antispam WHERE Id = '" . $_GET['cp_secureimage'] . "'"))) die;

	return $row['Code'];
}

$code = get_securecode();

$font = 'fonts/ft16.ttf';
$raster = 0;
$step = 10;
$size = 26;
$code_top = 38;
$rect_width = 120;
$rect_height = 40;

$bild = imagecreate(++$rect_width, ++$rect_height);
$back = imagecolorallocate($bild, 255, 255, 255);
$gelb = imagecolorallocate($bild, 238, 192, 10);
$schwarz = imagecolorallocate($bild, 0, 0, 0);
$grau = imagecolorallocate($bild, 204, 204, 204);
$dunkelgrau = imagecolorallocate($bild, 119, 119, 119);

// Prevent the browser from caching the result.
// Date in the past
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT') ;
// always modified
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT') ;
// HTTP/1.1
header('Cache-Control: no-store, no-cache, must-revalidate') ;
header('Cache-Control: post-check=0, pre-check=0', false) ;
// HTTP/1.0
header('Pragma: no-cache') ;

// Set the response format.
header('Content-type: image/jpeg');

$count_vert = $rect_width/$step;
$count_hori = $rect_height/$step;
if ($raster == 1)
{
	for($i=0;$i<$count_vert;$i++) imageline($bild, $i*$step, 0, $i*$step, $rect_height, $grau);
	for($i=0;$i<$count_hori;$i++) imageline($bild, 0, $i*$step, $rect_width, $i*$step, $grau);
}
else
{
	for($a=0;$a<$count_hori;$a++) for($i=0;$i<$count_vert;$i++) imagesetpixel($bild, $i*$step, $a*$step, $grau);
}

imagettftext($bild, $size, 7, 25, $code_top, $dunkelgrau, $font, $code);
imagerectangle($bild, 0, 0, $rect_width, $rect_height, $dunkelgrau);
imagejpeg($bild);
imagedestroy($bild);

?>