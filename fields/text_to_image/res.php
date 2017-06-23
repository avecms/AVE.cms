<?php
ob_start();
ob_implicit_flush(0);

define('BASE_DIR', str_replace("\\", "/", dirname(dirname(dirname(__FILE__)))));

require_once(BASE_DIR . '/inc/init.php');

if (! check_permission('adminpanel'))
{
	header('Location:/index.php');
	exit;
}

$a = stripslashes($_POST['a']);
$b = $_POST['b'];
$c = $_POST['c'];
$d = $_POST['d'];
$e = $_POST['e'];


require_once(BASE_DIR . '/fields/text_to_image/class.txtimage.php');

$ti = new TextImage();

// возввращает полный HTML тег
$ti->return_as_html = true;    

// определяем вывод изображения: либо в виде кода base64, либо файл .png 
// если true - тогда выводим кодом, если false - тогда выводим файл.png - но в этом режиме кеш
// изображений должен быть обязательно включен!
$ti->embed_image = false;        

// устанавливаем - будут ли сохранятся/кешироваться изображения, true - да , false- нет.                    
$ti->do_cache = true;

// путь до папки, где лежат сохраненные изображения (см. файл  class.txtimage.php)если хотите изменить то меняйте в файле класса так же...   
$ti->cache_folder = '../../uploads/txtimages';        

// Преобразуем обычный текст в изображение
echo $ti->generate("$a", "$b", "$c", "$d", $e);
?>