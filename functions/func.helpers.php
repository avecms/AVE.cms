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
 * Функция очищает строку от пробелов
 *
 * @param string
 * @return string
 */
if (!function_exists('str_nospace')){
	function str_nospace($string)
	{
		return trim(str_replace(array(' ',"\r","\n","\t"),'',$string));
	}
}


/**
 * Функция убирает двойные пробелы
 *
 * @param string $string данные
 * @return string
 */
if (!function_exists('full_trim')){
	function full_trim($string)
	{
		return trim(preg_replace('/\s{2,}/', ' ', $string));
	}
}


/**
 * Функция перевода из 1251 в UTF8
 *
 * @param string $string данные
 * @return string
 */
if (!function_exists('utf8')){
	function utf8($string){
		$string = iconv('windows-1251', 'utf-8', $string);
		return $string;
	}
}


/**
 * Коллекционируем уникальные значения по типу
 *
 * @param string $type
 * @param null   $val
 * @return array
 */
if (!function_exists('collect_data')){
	function collect_data($type='', $val = null)
	{
		static $arr = array();
		if(empty($type)) return false;
		if($val&& !trim($type) == ''){
			if(!is_array($val))$val = array($val);
			foreach($val as $value){
				if(!isset($arr[$type])) $arr[$type] = array();
				if(!in_array($value, $arr[$type]))
					$arr[$type][] = $value;
			}
		}
		return isset($arr[$type]) ? $arr[$type] : false;
	}
}


/**
 * strip_tags_smart(
$s,
array $allowable_tags = null,
$is_format_spaces = true,
array $pair_tags = array('script', 'style', 'map', 'iframe', 'frameset', 'object', 'applet', 'comment', 'button', 'textarea', 'select'),
array $para_tags = array('p', 'td', 'th', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'form', 'title', 'pre')
)
 * Более продвинутый аналог strip_tags() для корректного вырезания тагов из html кода.
 * Возможности:
 *   - корректно обрабатываются вхождения типа "a < b > c"
 *   - корректно обрабатывается "грязный" html, когда в значениях атрибутов тагов могут встречаться символы < >
 *   - корректно обрабатывается разбитый html
 *   - вырезаются комментарии, скрипты, стили, PHP, Perl, ASP код, MS Word таги, CDATA
 *   - автоматически форматируется текст, если он содержит html код
 *   - защита от подделок типа: "<<fake>script>alert('hi')</</fake>script>"
 *
 * @param   string  $s
 * @param   array   $allowable_tags	 Массив тагов, которые не будут вырезаны
 *									  Пример: 'b' -- таг останется с атрибутами, '<b>' -- таг останется без атрибутов
 * @param   bool	$is_format_spaces   Форматировать пробелы и переносы строк?
 *									  Вид текста на выходе (plain) максимально приближеется виду текста в браузере на входе.
 *									  Другими словами, грамотно преобразует text/html в text/plain.
 *									  Текст форматируется только в том случае, если были вырезаны какие-либо таги.
 * @param   array   $pair_tags   массив имён парных тагов, которые будут удалены вместе с содержимым
 *							   см. значения по умолчанию
 * @param   array   $para_tags   массив имён парных тагов, которые будут восприниматься как параграфы (если $is_format_spaces = true)
 *							   см. значения по умолчанию
 * @return  string
 */
include (BASE_DIR . '/lib/StripTagsSmart/strip_tags_smart.php');


/**
 * Вычисление разницы между двумя метками времени
 *
 * @param string $a начальная метка
 * @param string $b конечная метка
 * @return int время между метками
 */
if (!function_exists('microtime_diff')){
	function microtime_diff($a, $b)
	{
		list($a_dec, $a_sec) = explode(' ', $a);
		list($b_dec, $b_sec) = explode(' ', $b);
		return $b_sec - $a_sec + $b_dec - $a_dec;
	}
}


/**
 * Функция меняет кодировку файла
 *
 * @param string $path
 * @param string $to
 */
if (!function_exists('file_encoding')){
	function file_encoding($path, $to='utf')
	{
		$f = file_get_contents($path);
		$f = mb_convert_encoding($f,$to == 'utf' ? 'UTF-8' : 'CP1251', $to == 'utf' ? 'CP1251' : 'UTF-8');
		file_put_contents($path, $f);
	}
}


/**
 * Replace array_combine()
 *
 * @category	PHP
 * @package	 PHP_Compat
 * @license	 LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link		http://php.net/function.array_combine
 * @author	  Aidan Lister <aidan@php.net>
 * @version	 $Revision: 1.23 $
 * @since	   PHP 5
 * @require	 PHP 4.0.0 (user_error)
 */
function php_compat_array_combine($keys, $values)
{
	if (!is_array($keys)) {
		user_error('array_combine() expects parameter 1 to be array, ' .
			gettype($keys) . ' given', E_USER_WARNING);
		return;
	}

	if (!is_array($values)) {
		user_error('array_combine() expects parameter 2 to be array, ' .
			gettype($values) . ' given', E_USER_WARNING);
		return;
	}

	$key_count = count($keys);
	$value_count = count($values);
	if ($key_count !== $value_count) {
		user_error('array_combine() Both parameters should have equal number of elements', E_USER_WARNING);
		return false;
	}

	if ($key_count === 0 || $value_count === 0) {
		user_error('array_combine() Both parameters should have number of elements at least 0', E_USER_WARNING);
		return false;
	}

	$keys	= array_values($keys);
	$values  = array_values($values);

	$combined = array();
	for ($i = 0; $i < $key_count; $i++) {
		$combined[$keys[$i]] = $values[$i];
	}

	return $combined;
}

// Define
if (!function_exists('array_combine')) {
	function array_combine($keys, $values)
	{
		return php_compat_array_combine($keys, $values);
	}
}


/**
 * post()
 *
 * @param mixed $var
 * @return string
 */
if (!function_exists('post')){
	function post($var)
	{
		return (isset($_POST[$var])) ? $_POST[$var] : '';
	}
}


/**
 * get()
 *
 * @param mixed $var
 * @return string
 */
if (!function_exists('get')){
	function get($var)
	{
		return (isset($_GET[$var])) ? $_GET[$var] : '';
	}
}


/**
 * sanitize()
 *
 * @param mixed $string
 * @param bool $trim
 * @param bool $int
 * @param bool $str
 * @return mixed|string
 */
if (!function_exists('sanitize')){
	function sanitize($string, $trim = false, $int = false, $str = false)
	{
		$string = filter_var($string, FILTER_SANITIZE_STRING);
		$string = trim($string);
		$string = stripslashes($string);
		$string = strip_tags($string);
		$string = str_replace(
			array(
				'‘',
				'’',
				'“',
				'”'
			),
			array(
				"'",
				"'",
				'"',
				'"'
			),
			$string
		);

		if ($trim)
			$string = substr($string, 0, $trim);
		if ($int)
			$string = preg_replace("/[^0-9\s]/", "", $string);
		if ($str)
			$string = preg_replace("/[^a-zA-Z\s]/", "", $string);

		return $string;
	}
}


/**
 * cleanSanitize()
 *
 * @param mixed $string
 * @param bool $trim
 * @param string $end_char
 * @return mixed|string
 */
if (!function_exists('cleanSanitize')){
	function cleanSanitize($string, $trim = false, $end_char = '&#8230;')
	{
		$string = cleanOut($string);
		$string = filter_var($string, FILTER_SANITIZE_STRING);
		$string = trim($string);
		$string = stripslashes($string);
		$string = strip_tags($string);
		$string = str_replace(array(
			'‘',
			'’',
			'“',
			'”'), array(
			"'",
			"'",
			'"',
			'"'), $string);

		if ($trim) {
			if (strlen($string) < $trim) {
				return $string;
			}

			$string = preg_replace("/\s+/", ' ', str_replace(array(
				"\r\n",
				"\r",
				"\n"), ' ', $string));

			if (strlen($string) <= $trim) {
				return $string;
			}

			$out = "";
			foreach (explode(' ', trim($string)) as $val) {
				$out .= $val . ' ';

				if (strlen($out) >= $trim) {
					$out = trim($out);
					return (strlen($out) == strlen($string)) ? $out : $out . $end_char;
				}
			}
		}
		return $string;
	}
}


/**
 * Функция обрезает текст до заданной величины
 *
 * @param mixed  $string
 * @param int    $length        Длинна
 * @param string $etc           Окончание
 * @param bool   $break_words   Дробить слова на символы
 * @param bool   $middle        Вырезает середину, оставляет начало + разделитель + конец
 * @return mixed|string
 */
if (! function_exists('trancate'))
{
	function truncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false)
	{
		if ($length == 0)
			return '';

		if (mb_strlen($string) > $length)
		{
			$length -= min($length, mb_strlen($etc));

			if (! $break_words && ! $middle)
			{
				$string = preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length + 1));
			}

			if (! $middle)
			{
				return mb_substr($string, 0, $length) . $etc;
			}
			else
				{
					return mb_substr($string, 0, $length / 2) . $etc . mb_substr($string, - $length / 2);
				}
		}
		else
			{
				return $string;
			}
	}
}


/**
 * Функция обрезает текст до заданной величины, не бьет слова
 *
 * @param   mixed   $str
 * @param   int     $n           Длинна
 * @param   mixed   $end_char    Окончание
 * @return  mixed|string
 */
if (!function_exists('truncate_text'))
{
	function truncate_text($str, $n = 100, $end_char = '&#8230;')
	{
		if (strlen($str) < $n)
		{
			return $str;
		}

		$str = preg_replace("/\s+/", ' ', str_replace(array(
			"\r\n",
			"\r",
			"\n"), ' ', $str));

		if (strlen($str) <= $n)
		{
			return $str;
		}

		$out = "";
		foreach (explode(' ', trim($str)) as $val)
		{
			$out .= $val . ' ';

			if (strlen($out) >= $n)
			{
				$out = trim($out);
				return (strlen($out) == strlen($str))
					? $out
					: $out . $end_char;
			}
		}
		return $str;
	}
}


/**
 * Swap named HTML entities with numeric entities.
 *
 * @see http://www.lazycat.org/software/html_entity_decode_full.phps
 */
function convert_entity($matches, $destroy = true)
{
	$table = array(
		'Aacute'   => '&#193;',  'aacute'   => '&#225;',  'Acirc'	=> '&#194;',  'acirc'	=> '&#226;',  'acute'	=> '&#180;',
		'AElig'	=> '&#198;',  'aelig'	=> '&#230;',  'Agrave'   => '&#192;',  'agrave'   => '&#224;',  'alefsym'  => '&#8501;',
		'Alpha'	=> '&#913;',  'alpha'	=> '&#945;',  'amp'	  => '&#38;',   'and'	  => '&#8743;', 'ang'	  => '&#8736;',
		'Aring'	=> '&#197;',  'aring'	=> '&#229;',  'asymp'	=> '&#8776;', 'Atilde'   => '&#195;',  'atilde'   => '&#227;',
		'Auml'	 => '&#196;',  'auml'	 => '&#228;',  'bdquo'	=> '&#8222;', 'Beta'	 => '&#914;',  'beta'	 => '&#946;',
		'brvbar'   => '&#166;',  'bull'	 => '&#8226;', 'cap'	  => '&#8745;', 'Ccedil'   => '&#199;',  'ccedil'   => '&#231;',
		'cedil'	=> '&#184;',  'cent'	 => '&#162;',  'Chi'	  => '&#935;',  'chi'	  => '&#967;',  'circ'	 => '&#710;',
		'clubs'	=> '&#9827;', 'cong'	 => '&#8773;', 'copy'	 => '&#169;',  'crarr'	=> '&#8629;', 'cup'	  => '&#8746;',
		'curren'   => '&#164;',  'dagger'   => '&#8224;', 'Dagger'   => '&#8225;', 'darr'	 => '&#8595;', 'dArr'	 => '&#8659;',
		'deg'	  => '&#176;',  'Delta'	=> '&#916;',  'delta'	=> '&#948;',  'diams'	=> '&#9830;', 'divide'   => '&#247;',
		'Eacute'   => '&#201;',  'eacute'   => '&#233;',  'Ecirc'	=> '&#202;',  'ecirc'	=> '&#234;',  'Egrave'   => '&#200;',
		'egrave'   => '&#232;',  'empty'	=> '&#8709;', 'emsp'	 => '&#8195;', 'ensp'	 => '&#8194;', 'Epsilon'  => '&#917;',
		'epsilon'  => '&#949;',  'equiv'	=> '&#8801;', 'Eta'	  => '&#919;',  'eta'	  => '&#951;',  'ETH'	  => '&#208;',
		'eth'	  => '&#240;',  'Euml'	 => '&#203;',  'euml'	 => '&#235;',  'euro'	 => '&#8364;', 'exist'	=> '&#8707;',
		'fnof'	 => '&#402;',  'forall'   => '&#8704;', 'frac12'   => '&#189;',  'frac14'   => '&#188;',  'frac34'   => '&#190;',
		'frasl'	=> '&#8260;', 'Gamma'	=> '&#915;',  'gamma'	=> '&#947;',  'ge'	   => '&#8805;', 'gt'	   => '&#62;',
		'harr'	 => '&#8596;', 'hArr'	 => '&#8660;', 'hearts'   => '&#9829;', 'hellip'   => '&#8230;', 'Iacute'   => '&#205;',
		'iacute'   => '&#237;',  'Icirc'	=> '&#206;',  'icirc'	=> '&#238;',  'iexcl'	=> '&#161;',  'Igrave'   => '&#204;',
		'igrave'   => '&#236;',  'image'	=> '&#8465;', 'infin'	=> '&#8734;', 'int'	  => '&#8747;', 'Iota'	 => '&#921;',
		'iota'	 => '&#953;',  'iquest'   => '&#191;',  'isin'	 => '&#8712;', 'Iuml'	 => '&#207;',  'iuml'	 => '&#239;',
		'Kappa'	=> '&#922;',  'kappa'	=> '&#954;',  'Lambda'   => '&#923;',  'lambda'   => '&#955;',  'lang'	 => '&#9001;',
		'laquo'	=> '&#171;',  'larr'	 => '&#8592;', 'lArr'	 => '&#8656;', 'lceil'	=> '&#8968;', 'ldquo'	=> '&#8220;',
		'le'	   => '&#8804;', 'lfloor'   => '&#8970;', 'lowast'   => '&#8727;', 'loz'	  => '&#9674;', 'lrm'	  => '&#8206;',
		'lsaquo'   => '&#8249;', 'lsquo'	=> '&#8216;', 'lt'	   => '&#60;',   'macr'	 => '&#175;',  'mdash'	=> '&#8212;',
		'micro'	=> '&#181;',  'middot'   => '&#183;',  'minus'	=> '&#8722;', 'Mu'	   => '&#924;',  'mu'	   => '&#956;',
		'nabla'	=> '&#8711;', 'nbsp'	 => '&#160;',  'ndash'	=> '&#8211;', 'ne'	   => '&#8800;', 'ni'	   => '&#8715;',
		'not'	  => '&#172;',  'notin'	=> '&#8713;', 'nsub'	 => '&#8836;', 'Ntilde'   => '&#209;',  'ntilde'   => '&#241;',
		'Nu'	   => '&#925;',  'nu'	   => '&#957;',  'Oacute'   => '&#211;',  'oacute'   => '&#243;',  'Ocirc'	=> '&#212;',
		'ocirc'	=> '&#244;',  'OElig'	=> '&#338;',  'oelig'	=> '&#339;',  'Ograve'   => '&#210;',  'ograve'   => '&#242;',
		'oline'	=> '&#8254;', 'Omega'	=> '&#937;',  'omega'	=> '&#969;',  'Omicron'  => '&#927;',  'omicron'  => '&#959;',
		'oplus'	=> '&#8853;', 'or'	   => '&#8744;', 'ordf'	 => '&#170;',  'ordm'	 => '&#186;',  'Oslash'   => '&#216;',
		'oslash'   => '&#248;',  'Otilde'   => '&#213;',  'otilde'   => '&#245;',  'otimes'   => '&#8855;', 'Ouml'	 => '&#214;',
		'ouml'	 => '&#246;',  'para'	 => '&#182;',  'part'	 => '&#8706;', 'permil'   => '&#8240;', 'perp'	 => '&#8869;',
		'Phi'	  => '&#934;',  'phi'	  => '&#966;',  'Pi'	   => '&#928;',  'pi'	   => '&#960;',  'piv'	  => '&#982;',
		'plusmn'   => '&#177;',  'pound'	=> '&#163;',  'prime'	=> '&#8242;', 'Prime'	=> '&#8243;', 'prod'	 => '&#8719;',
		'prop'	 => '&#8733;', 'Psi'	  => '&#936;',  'psi'	  => '&#968;',  'quot'	 => '&#34;',   'radic'	=> '&#8730;',
		'rang'	 => '&#9002;', 'raquo'	=> '&#187;',  'rarr'	 => '&#8594;', 'rArr'	 => '&#8658;', 'rceil'	=> '&#8969;',
		'rdquo'	=> '&#8221;', 'real'	 => '&#8476;', 'reg'	  => '&#174;',  'rfloor'   => '&#8971;', 'Rho'	  => '&#929;',
		'rho'	  => '&#961;',  'rlm'	  => '&#8207;', 'rsaquo'   => '&#8250;', 'rsquo'	=> '&#8217;', 'sbquo'	=> '&#8218;',
		'Scaron'   => '&#352;',  'scaron'   => '&#353;',  'sdot'	 => '&#8901;', 'sect'	 => '&#167;',  'shy'	  => '&#173;',
		'Sigma'	=> '&#931;',  'sigma'	=> '&#963;',  'sigmaf'   => '&#962;',  'sim'	  => '&#8764;', 'spades'   => '&#9824;',
		'sub'	  => '&#8834;', 'sube'	 => '&#8838;', 'sum'	  => '&#8721;', 'sup'	  => '&#8835;', 'sup1'	 => '&#185;',
		'sup2'	 => '&#178;',  'sup3'	 => '&#179;',  'supe'	 => '&#8839;', 'szlig'	=> '&#223;',  'Tau'	  => '&#932;',
		'tau'	  => '&#964;',  'there4'   => '&#8756;', 'Theta'	=> '&#920;',  'theta'	=> '&#952;',  'thetasym' => '&#977;',
		'thinsp'   => '&#8201;', 'THORN'	=> '&#222;',  'thorn'	=> '&#254;',  'tilde'	=> '&#732;',  'times'	=> '&#215;',
		'trade'	=> '&#8482;', 'Uacute'   => '&#218;',  'uacute'   => '&#250;',  'uarr'	 => '&#8593;', 'uArr'	 => '&#8657;',
		'Ucirc'	=> '&#219;',  'ucirc'	=> '&#251;',  'Ugrave'   => '&#217;',  'ugrave'   => '&#249;',  'uml'	  => '&#168;',
		'upsih'	=> '&#978;',  'Upsilon'  => '&#933;',  'upsilon'  => '&#965;',  'Uuml'	 => '&#220;',  'uuml'	 => '&#252;',
		'weierp'   => '&#8472;', 'Xi'	   => '&#926;',  'xi'	   => '&#958;',  'Yacute'   => '&#221;',  'yacute'   => '&#253;',
		'yen'	  => '&#165;',  'Yuml'	 => '&#376;',  'yuml'	 => '&#255;',  'Zeta'	 => '&#918;',  'zeta'	 => '&#950;',
		'zwj'	  => '&#8205;', 'zwnj'	 => '&#8204;'
	);

	if (isset($table[$matches[1]])) return $table[$matches[1]];
	else							return $destroy ? '' : $matches[0];
}


/**
 * stripTags()
 *
 * @param mixed $start
 * @param mixed $end
 * @param mixed $string
 * @return string
 */
if (!function_exists('stripTags'))
{
	function stripTags($start, $end, $string)
	{
		$string = stristr($string, $start);
		$doend = stristr($string, $end);
		return substr($string, strlen($start), -strlen($doend));
	}
}


/**
 * stripExt()
 *
 * @param mixed $filename
 * @return string
 */
if (!function_exists('stripExt')){
	function stripExt($filename)
	{
		if (strpos($filename, ".") === false) {
			return ucwords($filename);
		} else
			return substr(ucwords($filename), 0, strrpos($filename, "."));
	}
}


/**
 * Очиста текста
 *
 * @param mixed $text
 * @return string
 */
if (!function_exists('cleanOut')){
	function cleanOut($text)
	{
		$text = strtr($text, array(
			'\r\n' => "",
			'\r' => "",
			'\n' => ""));
		$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
		$text = str_replace('<br>', '<br />', $text);
		return stripslashes($text);
	}
}


/**
 * Сравнение двух чисел
 *
 * @param mixed $float1
 * @param mixed $float2
 * @param string $operator
 * @return bool
 */
if (!function_exists('compareFloatNumbers')){
	function compareFloatNumbers($float1, $float2, $operator='=')
	{
		// Check numbers to 5 digits of precision
		$epsilon = 0.00001;

		$float1 = (float)$float1;
		$float2 = (float)$float2;

		switch ($operator)
		{
			// equal
			case "=":
			case "eq":
				if (abs($float1 - $float2) < $epsilon) {
					return true;
				}
				break;
			// less than
			case "<":
			case "lt":
				if (abs($float1 - $float2) < $epsilon) {
					return false;
				} else {
					if ($float1 < $float2) {
						return true;
					}
				}
				break;
			// less than or equal
			case "<=":
			case "lte":
				if (compareFloatNumbers($float1, $float2, '<') || compareFloatNumbers($float1, $float2, '=')) {
					return true;
				}
				break;
			// greater than
			case ">":
			case "gt":
				if (abs($float1 - $float2) < $epsilon) {
					return false;
				} else {
					if ($float1 > $float2) {
						return true;
					}
				}
				break;
			// greater than or equal
			case ">=":
			case "gte":
				if (compareFloatNumbers($float1, $float2, '>') || compareFloatNumbers($float1, $float2, '=')) {
					return true;
				}
				break;

			case "<>":
			case "!=":
			case "ne":
				if (abs($float1 - $float2) > $epsilon) {
					return true;
				}
				break;
			default:
				die("Unknown operator '".$operator."' in compareFloatNumbers()");
		}

		return false;
	}
}


/**
 * Поиск значения по массиву
 *
 * @param $array
 * @param $key
 * @param bool $value
 * @return bool
 */
if (!function_exists('searchforValue')){
	function searchforValue($array, $key, $value)
	{
		if($array) {
			foreach ($array as $val) {
				if ($val->$key == $value) {
					return true;
				}
			}
		}
		return false;
	}
}


/**
 * Поиск в массиве
 *
 * @param mixed $array
 * @param mixed $val1
 * @param mixed $val2
 * @return array|int
 */
if (!function_exists('findInArray')){
	function findInArray($array, $val1, $val2)
	{
		if($array) {
			$result = array();
			foreach ($array as $val) {
				if ($val->$val1 == $val2) {
					$result[] = $val;
				}
			}
			return ($result) ? $result : 0;
		}
		return 0;
	}
}


/**
 * Скачать файл
 *
 * @param $fileLocation
 * @param $fileName
 * @param int $maxSpeed
 * @return bool
 */
if (!function_exists('downloadFile')){
	function downloadFile($fileLocation, $fileName, $maxSpeed = 5120)
	{
		if (connection_status() != 0)
			return (false);

		$extension = strtolower(substr($fileName, strrpos($fileName, '.') + 1));

		/* List of File Types */
		$fileTypes['swf'] = 'application/x-shockwave-flash';
		$fileTypes['pdf'] = 'application/pdf';
		$fileTypes['exe'] = 'application/octet-stream';
		$fileTypes['zip'] = 'application/zip';
		$fileTypes['doc'] = 'application/msword';
		$fileTypes['xls'] = 'application/vnd.ms-excel';
		$fileTypes['ppt'] = 'application/vnd.ms-powerpoint';
		$fileTypes['gif'] = 'image/gif';
		$fileTypes['png'] = 'image/png';
		$fileTypes['jpeg'] = 'image/jpg';
		$fileTypes['jpg'] = 'image/jpg';
		$fileTypes['rar'] = 'application/rar';

		$fileTypes['ra'] = 'audio/x-pn-realaudio';
		$fileTypes['ram'] = 'audio/x-pn-realaudio';
		$fileTypes['ogg'] = 'audio/x-pn-realaudio';

		$fileTypes['wav'] = 'video/x-msvideo';
		$fileTypes['wmv'] = 'video/x-msvideo';
		$fileTypes['avi'] = 'video/x-msvideo';
		$fileTypes['asf'] = 'video/x-msvideo';
		$fileTypes['divx'] = 'video/x-msvideo';

		$fileTypes['mp3'] = 'audio/mpeg';
		$fileTypes['mp4'] = 'audio/mpeg';
		$fileTypes['mpeg'] = 'video/mpeg';
		$fileTypes['mpg'] = 'video/mpeg';
		$fileTypes['mpe'] = 'video/mpeg';
		$fileTypes['mov'] = 'video/quicktime';
		$fileTypes['swf'] = 'video/quicktime';
		$fileTypes['3gp'] = 'video/quicktime';
		$fileTypes['m4a'] = 'video/quicktime';
		$fileTypes['aac'] = 'video/quicktime';
		$fileTypes['m3u'] = 'video/quicktime';

		$contentType = $fileTypes[$extension];


		header("Cache-Control: public");
		header("Content-Transfer-Encoding: binary\n");
		header('Content-Type: $contentType');

		$contentDisposition = 'attachment';

		if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
			$fileName = preg_replace('/\./', '%2e', $fileName, substr_count($fileName, '.') - 1);
			header("Content-Disposition: $contentDisposition;filename=\"$fileName\"");
		} else {
			header("Content-Disposition: $contentDisposition;filename=\"$fileName\"");
		}

		header("Accept-Ranges: bytes");
		$range = 0;
		$size = filesize($fileLocation);

		if (isset($_SERVER['HTTP_RANGE'])) {
			list($a, $range) = explode("=", $_SERVER['HTTP_RANGE']);
			str_replace($range, "-", $range);
			$size2 = $size - 1;
			$new_length = $size - $range;
			header("HTTP/1.1 206 Partial Content");
			header("Content-Length: $new_length");
			header("Content-Range: bytes $range$size2/$size");
		} else {
			$size2 = $size - 1;
			header("Content-Range: bytes 0-$size2/$size");
			header("Content-Length: " . $size);
		}

		if ($size == 0) {
			die('Zero byte file! Aborting download');
		}

		$fp = fopen("$fileLocation", "rb");

		fseek($fp, $range);

		while (!feof($fp) and (connection_status() == 0)) {
			set_time_limit(0);
			print (fread($fp, 1024 * $maxSpeed));
			flush();
			ob_flush();
			sleep(1);
		}
		fclose($fp);

		exit;

		return ((connection_status() == 0) and !connection_aborted());
	}
}


	/**
	 * Конвертируем hex color в decimal color
	 * @param string $hexcolor Значение цвета в HEX. Example: #A9B7D3
	 * @return array|bool
	 */
if (!function_exists('color_h2d')){
	function color_h2d($hexcolor) {
		if(mb_strlen($hexcolor) != 7 || mb_strpos($hexcolor, "#") === false) {
			return false;
		}

		return array(	"r" => hexdec(mb_substr($hexcolor, 1, 2)),
						"g" => hexdec(mb_substr($hexcolor, 3, 2)),
						"b" => hexdec(mb_substr($hexcolor, 5, 2)));
	}
}
?>