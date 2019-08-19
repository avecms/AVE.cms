<?php

/**
 * AVE.cms
 *
 * @package AVE.cms
 * @version 3.x
 * @filesource
 * @copyright © 2007-2014 AVE.cms, http://www.ave-cms.ru
 *
 */

class Meta
{
	private $_keyword_count = 10;

	public function __construct( $keyword_count = false )
	{
		if( (int) $keyword_count ) $this->_keyword_count = $keyword_count;
	}

	public function setKeywordCount( $keyword_count )
	{
		if( (int) $keyword_count ) $this->_keyword_count = $keyword_count;
	}

	public function generateMeta( $text )
	{
		$newarr = array ();

		$quotes = array ("\x22", "\x60", "\t", "\n", "\r", ",", "/", "¬", "#", ";", ":", "@", "~", "[", "]", "{", "}", "=", "+", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"');
		$fastquotes = array ("\x22", "\x60", "\t", "\n", "\r", '"', "\\", '\r', '\n', "/", "{", "}", "[", "]" );

		$text = str_replace( chr(9), ' ', $text );
		$text = str_replace( chr(10), ' ', $text );
		$text = str_replace( chr(13), ' ', $text );
		$text = str_replace( "&nbsp;", " ", $text );
		$text = str_replace( '<br />', ' ', $text );
		$text = strip_tags( $text );
		$text = preg_replace('/ {2,}/',' ',$text);
		$text = preg_replace( "#&(.+?);#", "", $text );
		//$text = trim(str_replace( " ,", "", stripslashes( $text )));
		$text = preg_replace('/\[tag:(.+?)\]/', '', $text);
		$text = preg_replace('/\[mod_(.+?)\]/', '', $text);

		$text = str_replace( $fastquotes, '', $text );

		$text = str_replace( $quotes, ' ', $text );

		$arr = explode( " ", $text );

		foreach ( $arr as $word ) {
			if( mb_strlen( ($word) ) > 4 OR (mb_strtoupper($word)==$word) and mb_strlen( ($word) ) > 1) $newarr[] = $word;
		}

		$arr = array_count_values( $newarr );
		arsort( $arr );

		$arr = array_keys( $arr );

		$total = count( $arr );

		$offset = 0;

		$arr = array_slice( $arr, $offset, $this->_keyword_count );

		$return['keywords'] = implode( ", ", $arr );
		$return['description'] = trim(mb_substr( trim($text), 0, 220 ),'.').'.';

		return $return;
	}
}
