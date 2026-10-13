<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Helpers/Html.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Helpers;

	if (! defined("BASEPATH"))
	{
		die ('Direct access to this location is not allowed.');
	}

	class Html
	{
		/** Remove inline CSS from an HTML fragment, leaving other attributes and elements intact. */
		public static function removeInlineStyles($html)
		{
			$html = (string) $html;
			if (stripos($html, 'style') === false) { return array('html' => $html, 'removed' => 0); }
			$previous = libxml_use_internal_errors(true);
			try {
				$dom = new \DOMDocument('1.0', 'UTF-8');
				if (!$dom->loadHTML('<?xml encoding="UTF-8"><html><body>' . $html . '</body></html>', LIBXML_NONET)) {
					throw new \InvalidArgumentException('Не удалось разобрать HTML описания');
				}

				$nodes = (new \DOMXPath($dom))->query('//*[@style]');
				$count = $nodes->length;
				if (!$count) { return array('html' => $html, 'removed' => 0); }
				foreach ($nodes as $node) { $node->removeAttribute('style'); }
				$body = $dom->getElementsByTagName('body')->item(0);
				if (!$body) { throw new \InvalidArgumentException('Не удалось разобрать HTML описания'); }
				$result = '';
				foreach ($body->childNodes as $node) { $result .= $dom->saveHTML($node); }
				return array('html' => $result, 'removed' => $count);
			} finally {
				libxml_clear_errors();
				libxml_use_internal_errors($previous);
			}
		}

		protected function __construct()
		{
			//
		}

		/*
		|--------------------------------------------------------------------------------------
		| Функция делает html в 1 строчку, удаляет лишние пробелы, комментарии и т.д.
		|--------------------------------------------------------------------------------------
		|
		| @param string $data
		| @return string
		|
		*/
		public static function compressHtml (string $data)
		{
			//remove redundant (white-space) characters
			$replace = [
				//remove tabs before and after HTML tags
				'/\>[^\S ]+/s' => '>',
				'/[^\S ]+\</s' => '<',
				//shorten multiple whitespace sequences; keep new-line characters because they matter in JS!!!
				'/([\t ])+/s' => ' ',
				//remove leading and trailing spaces
				'/^([\t ])+/m' => '',
				'/([\t ])+$/m' => '',
				// remove JS line comments (simple only); do NOT remove lines containing URL (e.g. 'src="http://server.com/"')!!!
				'~//[a-zA-Z0-9 ]+$~m' => '',
				//remove empty lines (sequence of line-end and white-space characters)
				'/[\r\n]+([\t ]?[\r\n]+)+/s' => "\n",
				//remove empty lines (between HTML tags); cannot remove just any line-end characters because in inline JS they can matter!
				'/\>[\r\n\t ]+\</s'    => '><',
				//remove "empty" lines containing only JS's block end character; join with next line (e.g. "}\n}\n</script>" --> "}}\n</script>"
				'/}[\r\n\t ]+/s'  => '}',
				'/}[\r\n\t ]+,[\r\n\t ]+/s'  => '},',
				//remove new-line after JS's function or condition start; join with next line
				'/\)[\r\n\t ]?{[\r\n\t ]+/s'  => '){',
				'/,[\r\n\t ]?{[\r\n\t ]+/s'  => ',{',
				//remove new-line after JS's line end (only most obvious and safe cases)
				'/\),[\r\n\t ]+/s'  => '),',
				//remove quotes from HTML attributes that does not contain spaces; keep quotes around URLs!
				'~([\r\n\t ])?([a-zA-Z0-9]+)="([a-zA-Z0-9_/\\-]+)"([\r\n\t ])?~s' => '$1$2=$3$4' //$1 and $4 insert first white-space character found before/after attribute
			];

			return preg_replace(array_keys($replace), array_values($replace), $data);
		}


		/*
		|--------------------------------------------------------------------------------------
		| Функция делает html в 1 строчку, удаляет лишние пробелы, комментарии и т.д.
		|--------------------------------------------------------------------------------------
		|
		| @param string $data
		| @return string
		|
		*/
		public static function compress (string $data)
		{
			$search = [
				'/\>[^\S ]+/s', // strip whitespaces after tags, except space
				'/[^\S ]+\</s', // strip whitespaces before tags, except space
				'/([\t ])+/s', // shorten multiple whitespace sequences
				'/<!--(.|\s)*?-->/' // remove HTML comments
			];

			$replace = [
				'>',
				'<',
				' ',
				''
			];

			return preg_replace($search, $replace, $data);
		}


		/*
		|--------------------------------------------------------------------------------------
		| Если есть GZIP и есть константа
		|--------------------------------------------------------------------------------------
		|
		| @return void
		|
		*/
		public static function setGzip ()
		{
			if (! in_array('ob_gzhandler', ob_list_handlers(), 'gzip') && (defined('GZIP_COMPRESSION') && GZIP_COMPRESSION))
			{
				ob_start('ob_gzhandler');
			}
			else
			{
				ob_start();
			}
		}


		/*
		|--------------------------------------------------------------------------------------
		| Функция делает компрессию данных
		|--------------------------------------------------------------------------------------
		|
		| @param string $data
		| @return string
		|
		*/
		public static function output (string $data)
		{
			$headers = [];

			$Gzip = strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false;

			if (HTML_COMPRESSION) {
				$data = self::compressHtml($data);
			}

			if ($Gzip && GZIP_COMPRESSION) {
				$data = gzencode($data, 9);
				$headers[] = 'Content-Encoding: gzip';
			}

			$headers[] = 'Content-Type: text/html; charset=utf-8';
			$headers[] = 'Cache-Control: must-revalidate';

			if (OUTPUT_EXPIRE) {
				$headers[] = 'Expires: ' . gmdate("D, d M Y H:i:s", time() + OUTPUT_EXPIRE_OFFSET) . ' GMT';
			}

			$headers[] = 'Content-Length: ' . strlen($data);
			$headers[] = 'Vary: Accept-Encoding';

			Request::setHeaders($headers);

			unset($headers);

			return $data;
		}


		/*
		|--------------------------------------------------------------------------------------
		| Замена переносов на <br>
		|--------------------------------------------------------------------------------------
		|
		| @param string $string
		| @return string
		|
		*/
		public static function nl2br ($string)
		{
			$string = str_replace(array("\r\n", "\r", "\n"), '<br>', $string);

			return $string;
		}


		/*
		|--------------------------------------------------------------------------------------
		|
		|--------------------------------------------------------------------------------------
		|
		| @param string $string
		| @return string
		|
		*/
		public static function htmlDecode ( $string )
		{
			return html_entity_decode( $string, ENT_QUOTES );
		}


		/*
		|--------------------------------------------------------------------------------------
		|
		|--------------------------------------------------------------------------------------
		|
		| @param string $string
		| @return string
		|
		*/
		public static function htmlEncode ( $string )
		{
			return htmlentities( $string, ENT_QUOTES );
		}



		public static function buildQuery( $query )
		{
			$query_array = [];

			foreach( $query as $key => $key_value )
			{
				$query_array[] = urlencode( $key ) . '=' . urlencode( $key_value );
			}

			return implode( '&', $query_array );

		}


		public static function appendQuery ( $url, $query )
		{
			$relativeScheme = false;

			if (strpos($url, '://') === 0)
			{
				$relativeScheme = true;
				$url = 'a' . $url;
			}

			$newUrl = $url . '?' . self::buildQuery( $query );

			if ($relativeScheme)
			{
				return substr($newUrl, 1);
			}

			return $newUrl;
		}


		protected function __clone ()
		{
			//
		}


		protected function __wakeup ()
		{
			//
		}
	}
