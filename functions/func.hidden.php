<?

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
 * Обработка парного тега [tag:hide:X,X:text]...[/tag:hide] (скрытый текст)
 * Заменяет скрываемый текст в зависимости от группы пользователя
 *
 * @param string $data обрабатываемый текст
 * @return string обработанный текст
 */
function parse_hide($data)
{
	static $matches = null;

	static $i = null;

	preg_match_all('/\[tag:hide:(\d+,)*'. UGROUP .'(,\d+)*(:.*?)?].*?\[\/tag:hide]/s', $data, $matches, PREG_SET_ORDER);

	$count_matches = count($matches);

	if ($count_matches > 0)
	{
		for ($i=0; $i <= $count_matches; $i++)
		{

			$hidden_text = substr(@$matches[$i][3], 1);

			if ($hidden_text == "")
				$hidden_text = trim(get_settings('hidden_text'));

			$data = preg_replace('/\[tag:hide:(\d+,)*'. UGROUP .'(,\d+)*(:.*?)?].*?\[\/tag:hide]/s', $hidden_text, $data, 1);
		}
	}

	$data = preg_replace('/\[tag:hide:\d+(,\d+)*.*?](.*?)\[\/tag:hide]/s', '\\2', $data);

	return $data;
}
?>