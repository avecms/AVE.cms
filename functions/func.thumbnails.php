<?php

	/**
	 * AVE.cms
	 *
	 * @package AVE.cms
	 * @version 3.x
	 * @filesource
	 * @copyright © 2007-2017 AVE.cms, https://www.ave-cms.ru
	 *
	 * @license GPL v.2
	 */


	/*
	 |----------------------------------------------------------------------------------
	 | Формирование ссылки на миниатюру определённого размера,
	 | если размер не указан формируется миниатюра шириной 120px
	 |----------------------------------------------------------------------------------
	 | @param array $params - параметры
	 |
	 | <ul>
	 |    <li>link - путь к оригиналу</li>
	 |    <li>size - размер миниатюры</li>
	 | </ul>
	 |
	 | @return string
	 */
	function make_thumbnail ($params)
	{
		if (empty($params['link']))
			return false;

		if ((strpos($params['link'], '/http://') === 0 || strpos($params['link'], '/https://') === 0))
		{
			$md5 = md5($params['link']);

			$path = BASE_DIR . ABS_PATH . UPLOAD_DIR . '/ext/' . substr($md5, 0, 4);

			if (! is_dir($path))
			{
				if(! is_dir(dirname($path))) mkdir(dirname($path), 0777);
				mkdir($path, 0777);
			}

			$link = ABS_PATH . UPLOAD_DIR . '/ext/' . substr($md5, 0, 4) . '/' . $md5 . '.jpg';

			if (! file_exists(BASE_DIR . $link))
			{
				file_put_contents(BASE_DIR . $link . '.tmp', $params['link']);
			}

			$params['link'] = $link;
		}

		if (isset($params['size']))
		{
			$size = $params['size'];

			if (! preg_match('/^[r|c|f|t|s]\d+x\d+r*$/', $size))
				return false;
		}
		else
			{
				$size = 't128x128';
			}

		$nameParts = explode('.', basename($params['link']));

		$countParts = count($nameParts);

		if ($countParts < 2)
			return false;

		$nameParts[$countParts-2] .= '-' . $size;

		return dirname($params['link']) . '/' . THUMBNAIL_DIR . '/' . implode('.', $nameParts);
	}


	/*
	 |----------------------------------------------------------------------------------
	 | Формирование ссылки на миниатюру определённого размера
	 |----------------------------------------------------------------------------------
	 | @param array $params - параметры
	 |
	 | @return string
	 */
	function callback_make_thumbnail ($params)
	{
		return ((is_array($params) && isset($params[2]))
			? make_thumbnail(array('size' => $params[1], 'link' => $params[2]))
			: '');
	}
?>