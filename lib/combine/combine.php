<?php
// включить/выключить кэширование
$cache = true;

// уровень вложенности текущей директории относительно корня
// для определения правильного пути к папке кэша
// (например, если combine.php лежит в корне, пишем 0).
// можно передать через адр. строку: level=(int)
$level = 0;

// имя папки кэша относительно корня
$cachedir = '/cache/combine';

###############################################################


// определяем путь до папки кэша
if ($cache)
{
	$level = ($_GET['level']) ? $_GET['level'] : $level;
	$cachedir = trim($cachedir,'/');
	for ($i=0; $i<$level; $i++)
	{
		$cachedir = '../' . $cachedir;
	}
	$cachedir = str_replace("\\", "/", dirname(__FILE__)) . '/' . $cachedir;
	if(!is_dir($cachedir))
	{
		header ("HTTP/1.0 503 Not Implemented");
		exit("/*
combine.php: Error!
Неверно указан путь к папке кэша. Проверьте уровень вложенности.
*/");
	}
}

// Определяем тип файлов, полный путь к файлам и получаем список имен файлов
if (!empty($_GET['css']))
{
	$type = 'css';
	$hash = md5($_GET['css']);
	$elements = explode(',', $_GET['css']);
}
elseif (!empty($_GET['js']))
{
	$type = 'javascript';
	$hash = md5($_GET['js']);
	$elements = explode(',', $_GET['js']);
}
else
{
	@header ("HTTP/1.0 503 Not Implemented");
	exit;
}
$base = realpath(dirname(__FILE__));

// Determine last modification date of the files
$lastmodified = 0;
while (list(,$element) = each($elements)) {
	$path = realpath($base . '/' . $element);

	if (($type == 'javascript' && substr($path, -3) != '.js') || 
		($type == 'css' && substr($path, -4) != '.css')) {
		@header ("HTTP/1.0 403 Forbidden");
		exit;	
	}

	if (substr($path, 0, strlen($base)) != $base || !file_exists($path)) {
		@header ("HTTP/1.0 404 Not Found");
		exit;
	}
	
	$lastmodified = max($lastmodified, filemtime($path));
}

// Send Etag hash
@$hash = $lastmodified . '-' . md5($_GET['files']);
@header ("Etag: \"" . $hash . "\"");

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && 
	stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) == '"' . $hash . '"') 
{
	// Return visit and no modifications, so do not send anything
	@header ("HTTP/1.0 304 Not Modified");
	@header ('Content-Length: 0');
} 
else 
{
	// First time visit or files were modified
	if ($cache) 
	{
		// Determine supported compression method
		$gzip = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');
		$deflate = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate');

		// Determine used compression method
		$encoding = $gzip ? 'gzip' : ($deflate ? 'deflate' : 'none');

		// Check for buggy versions of Internet Explorer
		if (!strstr($_SERVER['HTTP_USER_AGENT'], 'Opera') && 
			preg_match('/^Mozilla\/4\.0 \(compatible; MSIE ([0-9]\.[0-9])/i', $_SERVER['HTTP_USER_AGENT'], $matches)) {
			$version = floatval($matches[1]);
			
			if ($version < 6)
				$encoding = 'none';
				
			if ($version == 6 && !strstr($_SERVER['HTTP_USER_AGENT'], 'EV1')) 
				$encoding = 'none';
		}
		
		// Try the cache first to see if the combined files were already generated
		$cachefile = 'cache-' . $hash . '.' . $type . ($encoding != 'none' ? '.' . $encoding : '');
		
		if (file_exists($cachedir . '/' . $cachefile)) {
			if ($fp = fopen($cachedir . '/' . $cachefile, 'rb')) {

				if ($encoding != 'none') {
					@header ("Content-Encoding: " . $encoding);
				}
			
				@header ("Content-Type: text/" . $type);
				@header ("Content-Length: " . filesize($cachedir . '/' . $cachefile));
	
				fpassthru($fp);
				fclose($fp);
				exit;
			}
		}
	}

	// Get contents of the files
	$contents = '';
	reset($elements);
	while (list(,$element) = each($elements)) {
		$path = realpath($base . '/' . $element);
		$contents .= file_get_contents($path);
	}

			if ($type == 'javascript')
			{
			 for ($i = 1; $i < 10; $i++)
			 {
			 $contents = str_replace("\n\n", "\n", $contents); //Удаляем переносы строк
			 $contents = str_replace("\r\r", "\r", $contents); //Удаляем переносы строк
			 $contents = str_replace("\r\n\r\n", "\r\n", $contents); //Удаляем переносы строк
			 }
			}

			if ($type == 'css')
			{
			 $contents = preg_replace('/\/\*.*\*\//Uis', '', $contents); //Удаляем комментарии
			 $contents = str_replace("\r", "", $contents); //Удаляем переносы строк
			 $contents = str_replace("\n", "", $contents); //Удаляем переносы строк
			 $contents = str_replace(chr(9), "", $contents); //Удаляем табуляцию
			 $contents = str_replace(" }", "}", $contents); //Удаляем пробелы перед }
			 $contents = str_replace(" {", "{", $contents); //Удаляем пробелы перед {
			 $contents = str_replace("{ ", "{", $contents); //Удаляем пробелы после {
			 $contents = str_replace("} ", "}", $contents); //Удаляем пробелы после }
			 $contents = str_replace("; ", ";", $contents); //Удаляем пробелы после ;
			 $contents = str_replace(" ;", ";", $contents); //Удаляем пробелы перед ;
			 $contents = str_replace(" :", ":", $contents); //Удаляем пробелы перед :
			 $contents = str_replace(": ", ":", $contents); //Удаляем пробелы после :
			 $contents = str_replace("+ ", "+", $contents); //Удаляем пробелы после +
			 $contents = str_replace(" +", "+", $contents); //Удаляем пробелы перед +
			 $contents = str_replace("= ", "=", $contents); //Удаляем пробелы после =
			 $contents = str_replace(" =", "=", $contents); //Удаляем пробелы перед =
			 $contents = str_replace("- ", "-", $contents); //Удаляем пробелы после -
			 $contents = str_replace("/ ", "/", $contents); //Удаляем пробелы после /
			 $contents = str_replace(" /", "/", $contents); //Удаляем пробелы перед /
			 $contents = str_replace(", ", ",", $contents); //Удаляем пробелы после ,
			 $contents = str_replace(" ,", ",", $contents); //Удаляем пробелы перед ,
			 $contents = str_replace("  ", " ", $contents); //Удаляем двойной пробел
			}

			// Send Content-Type
	@header ("Content-Type: text/" . $type);
	
	if (isset($encoding) && $encoding != 'none') 
	{
		// Send compressed contents
		$contents = gzencode($contents, 3, $gzip ? FORCE_GZIP : FORCE_DEFLATE);
		@header ("Content-Encoding: " . $encoding);
		@header ('Content-Length: ' . strlen($contents));
		echo $contents;
	} 
	else 
	{
		// Send regular contents
		@header ('Content-Length: ' . strlen($contents));
		echo $contents;
	}

	// Store cache
	if ($cache) {
		if ($fp = fopen($cachedir . '/' . $cachefile, 'wb')) {
			fwrite($fp, $contents);
			fclose($fp);
		}
	}
}
?>
