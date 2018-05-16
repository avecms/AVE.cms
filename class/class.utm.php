<?php
/*

Версия от 06.03.2018г.

Как это работает:
	1) Пользователь зашел на сайт первый раз.
	2) Если есть хотя бы один параметр - сохраняет cookies
	3) Если utm_history уже есть, то сохраняет данные в utm_last
	4) utm_last перезаписывается при каждом новом значении, а utm_history всегда остаётся неизменной (если был передан хотя бы один параметр)
	5) utm_source сохраняется всегда до закрытия браузера

*/
class UTMCookie
{

	private $parameters = array('utm_source', 'utm_medium', 'utm_campaign');

	private $utm_history = '';
	private $utm_last = '';
	private $utm_source = '';

	//Проверка параметров: Если все присутствуют - true, иначе false
	private function check_parameters()
	{
		//return true; //Сохранять всегда!

		$return = false;

		foreach ($this->parameters as $param)
			if (isset($_GET[$param]) && $_GET[$param] != '')
				$return = true;

		return $return;
	}

	//Создаёт строку с параметрами вот такого вида: utm_source=test; utm_medium=none; utm_campaign=kompaniya1;
	private function create_parameters()
	{
		$content = '';
		foreach($this->parameters as $param){

			if (isset($_GET[$param]) && $_GET[$param] != '')
			{
				$content .= $param.'='.$_GET[$param].'; ';

			}
			else
			{
				$content .= $param.'=none; ';
			}
		}

		return $content;
	}

	//Сохраняет переданные параметры, если требуется
	//Если utm_history присутствует, то сохраняет utm_last
	public function save_parameters()
	{
		if (isset($_GET['utm_source']) && trim($_GET['utm_source']) != '')
		{
			setcookie('utm_source', $_GET['utm_source']);

			$this->utm_source = $_GET['utm_source'];
		}

		//$utm_history = '';

		if (! isset($_COOKIE['utm_history']) || $_COOKIE['utm_history'] == '')
		{
			//Отсутствует utm_history
			if($this->check_parameters() == true)
			{

				$utm_history = $this->create_parameters();
				setcookie('utm_history', $utm_history, time()+15552000); //На 6 месяцев
				setcookie('utm_last', '');

				$this->utm_history = $utm_history;
			}

		}
		else
		{
			//utm_history присутствует

			$this->utm_history = $_COOKIE['utm_history'];

			//Перезапишем utm_last, если есть данные для этого
			if($this->check_parameters() == true){

				$utm_last = $this->create_parameters();

				if($utm_last != $_COOKIE['utm_history'])
					setcookie('utm_last', $utm_last, time()+15552000); //На 6 месяцев

				$this->utm_last = $utm_last;

			}

		}

		return true;
	}

	//Возвращает значение cookies
	public function get_value($name = '')
	{
		$name = trim($name);

		if ($name == '' || ! in_array($name, array('utm_history', 'utm_last', 'utm_source')))
			$name = 'utm_history';

		if (isset($this->$name) && $this->$name != '')
			return $this->$name;

		return isset($_COOKIE[$name]) ? $_COOKIE[$name] : '';
	}
}
?>