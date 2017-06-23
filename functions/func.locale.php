<?
	// Язык системы
	function set_locale()
	{
		$acp_language = empty($_SESSION['admin_language'])
			? $_SESSION['user_language']
			: $_SESSION['admin_language'];

		$locale = strtolower(defined('ACP')
			? $acp_language
			: $_SESSION['user_language']);

		switch ($locale)
		{
			case 'ru':
				@setlocale(LC_ALL, 'ru_RU.UTF-8', 'rus_RUS.UTF-8', 'russian');
				@setlocale(LC_NUMERIC, "C");
				break;

			case 'bg':
				@setlocale(LC_ALL, 'bg_BG.UTF-8', 'bgr_BGR.UTF-8', 'bulgarian');
				@setlocale(LC_NUMERIC, "C");
				break;

			default:
				@setlocale (LC_ALL, $locale . '_' . strtoupper($locale), $locale, '');
				break;
		}
	}


	/**
	 * Переводит в нижний регистр
	 *
	 * @param string $string строка для перевода в нижний регистр
	 * @return string
	 */
	function _strtolower($string)
	{
		$language = (defined('ACP') && ACP)
			? $_SESSION['admin_language']
			: $_SESSION['user_language'];

		$language = strtolower($language);

		switch ($language)
		{
			case 'de':
				return mb_strtolower($string);
				break;

			case 'en':
				return mb_strtolower($string);
				break;

			case 'ru':
			case 'bg':
				$small = array('а','б','в','г','д','е','ё','ж','з','и','й',
					'к','л','м','н','о','п','р','с','т','у','ф',
					'х','ч','ц','ш','щ','э','ю','я','ы','ъ','ь',
					'э', 'ю', 'я');
				$large = array('А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й',
					'К','Л','М','Н','О','П','Р','С','Т','У','Ф',
					'Х','Ч','Ц','Ш','Щ','Э','Ю','Я','Ы','Ъ','Ь',
					'Э', 'Ю', 'Я');
				break;

			case 'ua':
			case 'uk':
				$small = array('а','б','в','г','д','е','є','ж','з','и','і','й','ї',
					'к','л','м','н','о','п','р','с','т','у','ф',
					'х','ч','ц','ш','щ','ь','ю','я');
				$large = array('А','Б','В','Г','Д','Е','Є','Ж','З','И','І','Й','Ї',
					'К','Л','М','Н','О','П','Р','С','Т','У','Ф',
					'Х','Ч','Ц','Ш','Щ','Ь','Ю','Я');
				break;

			default:
				return mb_strtolower($string);
				break;
		}

		return str_replace($large, $small, $string);
	}

	/**
	 * Транслитерация
	 *
	 * @param string $string строка для транслитерации
	 * @return string
	 */
	function translit_string($string)
	{
		//	$st = htmlspecialchars_decode($st);
		//
		//	// Convert all named HTML entities to numeric entities
		//	$st = preg_replace_callback('/&([a-zA-Z][a-zA-Z0-9]{1,7});/', 'convert_entity', $st);
		//
		//	// Convert all numeric entities to their actual character
		//	$st = preg_replace('/&#x([0-9a-f]{1,7});/ei', 'chr(hexdec("\\1"))', $st);
		//	$st = preg_replace('/&#([0-9]{1,7});/e', 'chr("\\1")', $st);
		//

		$language = (defined('ACP') && ACP)
			? $_SESSION['admin_language']
			: $_SESSION['user_language'];

		$language = strtolower($language);

		switch ($language)
		{
			default:
			case 'ru':
				$table = array(
					//-- Заглавные
					'А' => 'A',
					'Б' => 'B',
					'В' => 'V',
					'Г' => 'G',
					'Д' => 'D',
					'Е' => 'E',
					'Ё' => 'YO',
					'Ж' => 'ZH',
					'З' => 'Z',
					'И' => 'I',
					'Й' => 'J',
					'К' => 'K',
					'Л' => 'L',
					'М' => 'M',
					'Н' => 'N',
					'О' => 'O',
					'П' => 'P',
					'Р' => 'R',
					'С' => 'S',
					'Т' => 'T',
					'У' => 'U',
					'Ф' => 'F',
					'Х' => 'H',
					'Ц' => 'C',
					'Ч' => 'CH',
					'Ш' => 'SH',
					'Щ' => 'CSH',
					'Ь' => '',
					'Ы' => 'Y',
					'Ъ' => '',
					'Э' => 'E',
					'Ю' => 'YU',
					'Я' => 'YA',
					//-- Строчные
					'а' => 'a',
					'б' => 'b',
					'в' => 'v',
					'г' => 'g',
					'д' => 'd',
					'е' => 'e',
					'ё' => 'yo',
					'ж' => 'zh',
					'з' => 'z',
					'и' => 'i',
					'й' => 'j',
					'к' => 'k',
					'л' => 'l',
					'м' => 'm',
					'н' => 'n',
					'о' => 'o',
					'п' => 'p',
					'р' => 'r',
					'с' => 's',
					'т' => 't',
					'у' => 'u',
					'ф' => 'f',
					'х' => 'h',
					'ц' => 'c',
					'ч' => 'ch',
					'ш' => 'sh',
					'щ' => 'csh',
					'ь' => '',
					'ы' => 'y',
					'ъ' => '',
					'э' => 'e',
					'ю' => 'yu',
					'я' => 'ya',
					);
				break;

            //болгарский
            case 'bg':
				$table = array(
					//-- Заглавные
					'А' => 'A',
					'Б' => 'B',
					'В' => 'V',
					'Г' => 'G',
					'Д' => 'D',
					'Е' => 'E',
					'Ё' => 'YO',
					'Ж' => 'ZH',
					'З' => 'Z',
					'И' => 'I',
					'Й' => 'J',
					'К' => 'K',
					'Л' => 'L',
					'М' => 'M',
					'Н' => 'N',
					'О' => 'O',
					'П' => 'P',
					'Р' => 'R',
					'С' => 'S',
					'Т' => 'T',
					'У' => 'U',
					'Ф' => 'F',
					'Х' => 'H',
					'Ц' => 'C',
					'Ч' => 'CH',
					'Ш' => 'SH',
					'Щ' => 'SHT',
					'Ь' => 'Y',
					'Ы' => 'Y',
					'Ъ' => 'A',
					'Э' => 'E',
					'Ю' => 'YU',
					'Я' => 'YA',
					//-- Строчные
					'а' => 'a',
					'б' => 'b',
					'в' => 'v',
					'г' => 'g',
					'д' => 'd',
					'е' => 'e',
					'ё' => 'yo',
					'ж' => 'zh',
					'з' => 'z',
					'и' => 'i',
					'й' => 'j',
					'к' => 'k',
					'л' => 'l',
					'м' => 'm',
					'н' => 'n',
					'о' => 'o',
					'п' => 'p',
					'р' => 'r',
					'с' => 's',
					'т' => 't',
					'у' => 'u',
					'ф' => 'f',
					'х' => 'h',
					'ц' => 'c',
					'ч' => 'ch',
					'ш' => 'sh',
					'щ' => 'sht',
					'ь' => 'a',
					'ы' => 'y',
					'ъ' => 'a',
					'э' => 'e',
					'ю' => 'yu',
					'я' => 'ya',
					);
				break;

			//-- українська мова:
			case 'ua':
				$table = array(
					//-- Заглавные
					'А' => 'A',
					'Б' => 'B',
					'В' => 'V',
					'Г' => 'G',
					'Д' => 'D',
					'Е' => 'E',
					'Є' => 'IE',
					'Ж' => 'J',
					'З' => 'Z',
					'И' => 'Y',
					'І' => 'I',
					'Й' => 'I',
					'К' => 'K',
					'Л' => 'L',
					'М' => 'M',
					'Н' => 'N',
					'О' => 'O',
					'П' => 'P',
					'Р' => 'R',
					'С' => 'S',
					'Т' => 'T',
					'У' => 'U',
					'Ф' => 'F',
					'Х' => 'H',
					'Ц' => 'C',
					'Ч' => 'CH',
					'Ш' => 'SH',
					'Щ' => 'CSH',
					'Ь' => '',
					'Ю' => 'IU',
					'Я' => 'IA',
					//-- Строчные
					'а' => 'a',
					'б' => 'b',
					'в' => 'v',
					'г' => 'g',
					'д' => 'd',
					'е' => 'e',
					'є' => 'ie',
					'ж' => 'j',
					'з' => 'z',
					'и' => 'y',
					'і' => 'i',
					'й' => 'i',
					'к' => 'k',
					'л' => 'l',
					'м' => 'm',
					'н' => 'n',
					'о' => 'o',
					'п' => 'p',
					'р' => 'r',
					'с' => 's',
					'т' => 't',
					'у' => 'u',
					'ф' => 'f',
					'х' => 'h',
					'ц' => 'c',
					'ч' => 'ch',
					'ш' => 'sh',
					'щ' => 'csh',
					'ь' => '',
					'ю' => 'iu',
					'я' => 'ia',
					);
				break;

			case 'uk':
				$table = array(
					//-- Заглавные
					'А' => 'A',
					'Б' => 'B',
					'В' => 'V',
					'Г' => 'G',
					'Ґ' => 'G',
					'Д' => 'D',
					'Е' => 'E',
					'Є' => 'IE',
					'Ж' => 'J',
					'З' => 'Z',
					'И' => 'Y',
					'І' => 'I',
					'Й' => 'I',
					'К' => 'K',
					'Л' => 'L',
					'М' => 'M',
					'Н' => 'N',
					'О' => 'O',
					'П' => 'P',
					'Р' => 'R',
					'С' => 'S',
					'Т' => 'T',
					'У' => 'U',
					'Ф' => 'F',
					'Х' => 'H',
					'Ц' => 'C',
					'Ч' => 'CH',
					'Ш' => 'SH',
					'Щ' => 'CSH',
					'Ь' => '',
					'Ю' => 'IU',
					'Я' => 'IA',
					//-- Строчные
					'а' => 'a',
					'б' => 'b',
					'в' => 'v',
					'г' => 'g',
					'ґ' => 'g',
					'д' => 'd',
					'е' => 'e',
					'є' => 'ie',
					'ж' => 'j',
					'з' => 'z',
					'и' => 'y',
					'і' => 'i',
					'й' => 'i',
					'к' => 'k',
					'л' => 'l',
					'м' => 'm',
					'н' => 'n',
					'о' => 'o',
					'п' => 'p',
					'р' => 'r',
					'с' => 's',
					'т' => 't',
					'у' => 'u',
					'ф' => 'f',
					'х' => 'h',
					'ц' => 'c',
					'ч' => 'ch',
					'ш' => 'sh',
					'щ' => 'csh',
					'ь' => '',
					'ю' => 'iu',
					'я' => 'ia',
					);
				break;

			//-- polski język
			case 'pl':
				$table = array(
					'Ą' => 'ya',
					'ą' => 'ya',
					'Ć' => 'ya',
					'ć' => 'ya',
					'Ę' => 'ya',
					'ę' => 'ya',
					'Ł' => 'ya',
					'ł' => 'ya',
					'Ń' => 'ya',
					'ń' => 'ya',
					'Ó' => 'ya',
					'ó' => 'ya',
					'Ś' => 'ya',
					'ś' => 'ya',
					'Ź' => 'ya',
					'ź' => 'ya',
					'Ż' => 'ya',
					'ż' => 'ya',
				);
				break;
		}

		$string = str_replace(array_keys($table), array_values($table), $string);

		switch ($language)
		{
			default:
			case 'ru':
				$replace = array(
					'ье'=>'ye',
					'ъе'=>'ye',
					'ьи'=>'yi',
					'ъи'=>'yi',
					'ъо'=>'yo',
					'ьо'=>'yo',
					'ё'=>'yo',
					'ю'=>'yu',
					'я'=>'ya',
					'ж'=>'zh',
					'х'=>'kh',
					'ц'=>'ts',
					'ч'=>'ch',
					'ш'=>'sh',
					'щ'=>'shch',
					'ъ'=>'',
					'ь'=>'',
					'є'=>'ye'
				);
				break;

			case 'bg':
				$replace = array(
					'ье'=>'ye',
					'ъе'=>'ye',
					'ьи'=>'yi',
					'ъи'=>'yi',
					'ъо'=>'yo',
					'ьо'=>'yo',
					'ё'=>'yo',
					'ю'=>'yu',
					'я'=>'ya',
					'ж'=>'zh',
					'ц'=>'ts',
					'ч'=>'ch',
					'ш'=>'sh',
					'щ'=>'sht',
					'ъ'=>'a',
					'ь'=>'a'
				);
				break;
		}

		$string = strtr($string, $replace);

		switch ($language)
		{
			default:
			case 'ru':
				$search = 'абвгдезийклмнопрстуфыэі';
				$replace = 'abvgdeziyklmnoprstufyei';
				break;

			case 'ua':
				$search = 'абвгґдеєжзиійїклмнопрстуфхцчшщьюя';
				$replace = 'abvggdeejzyiiklmnoprstufhccssua';
				break;

			case 'uk':
				$search = 'абвгґдеєжзиійїклмнопрстуфхцчшщьюя';
				$replace = 'abvggdeejzyiiklmnoprstufhccssua';
				break;

			case 'bg':
				$search = 'абвгдезийклмнопрстуфыэіъ';
				$replace = 'abvgdeziyklmnoprstufyeia';
				break;
		}

		$string = strtr($string, $search, $replace);

		return trim($string, '-');
	}


	/**
	 * Исправление форматирования даты
	 * Функцию можно использовать в шаблонах Smarty как модификатор
	 *
	 * @param string $string - дата отформатированная в соответствии с текущей локалью
	 * @param string $language - язык
	 * @return string
	 */
	function pretty_date($string, $language = '')
	{
		// пытаемся решить проблему для кодировки дат на лок. серверах
		if (! mb_check_encoding($string, 'UTF-8'))
		{
			$string = iconv('Windows-1251', 'UTF-8', $string);
		}

		if ($language == '')
		{
			$language = (defined('ACP') && ACP)
				? $_SESSION['admin_language']
				: $_SESSION['user_language'];
		}

		$language = strtolower($language);

		switch ($language)
		{
			default:
			case 'ru':
				$pretty = array(
					'Январь'	=>'января',		'Февраль'	=>'февраля',	'Март'	=>'марта',
					'Апрель'	=>'апреля',		'Май'		=>'мая',		'Июнь'	=>'июня',
					'Июль'		=>'июля',		'Август'	=>'августа',	'Сентябрь'=>'сентября',
					'Октябрь'	=>'октября',	'Ноябрь'	=>'ноября',		'Декабрь' =>'декабря',

					'воскресенье'=>'Воскресенье', 'понедельник'=>'Понедельник', 'вторник' =>'Вторник',
					'среда'		=>'Среда',		'четверг'	=>'Четверг',	'пятница' =>'Пятница',
					'суббота'	=>'Суббота'
				);
				break;

			case 'ua':
			case 'uk':
				$pretty = array(
					'Січень' =>'січня',  'Лютий'	=>'лютого',	'Березень'=>'березня',
					'Квітень'=>'квітня', 'Травень'  =>'травня',	'Червень' =>'червня',
					'Липень' =>'липня',  'Серпень'  =>'серпня',	'Вересень'=>'вересня',
					'Жовтень'=>'жовтня', 'Листопад' =>'листопада', 'Грудень' =>'грудня',

					'неділя' =>'Неділя', 'понеділок'=>'Понеділок', 'вівторок'=>'Вівторок',
					'середа' =>'Середа', 'четвер'   =>'Четвер',	'п’ятниця'=>'П’ятниця',
					'субота' =>'Субота'
				);
				break;
		}

		return (isset($pretty)
			? strtr($string, $pretty)
			: $string);
	}


	/**
	 * Функция перевода даты с en на ru
	 *
	 * @param string $data данные
	 * @return string
	 */
	function translate_date($data)
	{
		$language = (defined('ACP') && ACP)
			? $_SESSION['admin_language']
			: $_SESSION['user_language'];

		$language = strtolower($language);

		switch ($language)
		{
			default:
			case 'ru':
				$data = strtr($data, array(
					'January'=>'Января',
					'February'=>'Февраля',
					'March'=>'Марта',
					'April'=>'Апреля',
					'May'=>'Мая',
					'June'=>'Июня',
					'July'=>'Июля',
					'August'=>'Августа',
					'September'=>'Сентября',
					'October'=>'Октября',
					'November'=>'Ноября',
					'December'=>'Декабря',

					'Jan'=>'Янв',
					'Feb'=>'Фев',
					'Mar'=>'Мрт',
					'Apr'=>'Апр',
					'May'=>'Май',
					'Jun'=>'Июн',
					'Jul'=>'Июл',
					'Aug'=>'Авг',
					'Sep'=>'Сен',
					'Oct'=>'Окт',
					'Nov'=>'Нбр',
					'Dec'=>'Дек',

					'Monday'=>'Понедельник',
					'Tuesday'=>'Вторник',
					'Wednesday'=>'Среда',
					'Thursday'=>'Четверг',
					'Friday'=>'Пятница',
					'Saturday'=>'Суббота',
					'Sunday'=>'Воскресенье',

					'Mon'=>'Пн',
					'Tue'=>'Вт',
					'Wed'=>'Ср',
					'Thu'=>'Чт',
					'Fri'=>'Пт',
					'Sat'=>'Сб',
					'Sun'=>'Вс'
				));
			break;


			case 'ua':
			case 'uk':
				$data = strtr($data, array(
					'January'=>'Січня',
					'February'=>'Лютого',
					'March'=>'Березня',
					'April'=>'Квітня',
					'May'=>'Травня',
					'June'=>'Червня',
					'July'=>'Липня',
					'August'=>'Серпня',
					'September'=>'Вересня',
					'October'=>'Жовтня',
					'November'=>'Листопада',
					'December'=>'Грудня',

					'Jan'=>'Січ',
					'Feb'=>'Лют',
					'Mar'=>'Бер',
					'Apr'=>'Кві',
					'May'=>'Тра',
					'Jun'=>'Чер',
					'Jul'=>'Лип',
					'Aug'=>'Сер',
					'Sep'=>'Вер',
					'Oct'=>'Жов',
					'Nov'=>'Лис',
					'Dec'=>'Гру',

					'Monday'=>'Понеділок',
					'Tuesday'=>'Вівторок',
					'Wednesday'=>'Середа',
					'Thursday'=>'Четвер',
					'Friday'=>'П’ятниця',
					'Saturday'=>'Субота',
					'Sunday'=>'Неділя',

					'Mon'=>'Пн',
					'Tue'=>'Вт',
					'Wed'=>'Ср',
					'Thu'=>'Чт',
					'Fri'=>'Пт',
					'Sat'=>'Сб',
					'Sun'=>'Нд'
				));
			break;

			case 'bg':
				$data = strtr($data, array(
					'January'=>'Януари',
					'February'=>'Февруари',
					'March'=>'Март',
					'April'=>'Април',
					'May'=>'Май',
					'June'=>'Юни',
					'July'=>'Юли',
					'August'=>'Август',
					'September'=>'Септември',
					'October'=>'Октомври',
					'November'=>'Нември',
					'December'=>'Декември',

					'Jan'=>'Яну',
					'Feb'=>'Фев',
					'Mar'=>'Мрт',
					'Apr'=>'Апр',
					'May'=>'Май',
					'Jun'=>'Юни',
					'Jul'=>'Юли',
					'Aug'=>'Авг',
					'Sep'=>'Сеп',
					'Oct'=>'Окт',
					'Nov'=>'Ное',
					'Dec'=>'Дек',

					'Monday'=>'Понеделник',
					'Tuesday'=>'Вторник',
					'Wednesday'=>'Сряда',
					'Thursday'=>'Четвъртък',
					'Friday'=>'Петък',
					'Saturday'=>'Събота',
					'Sunday'=>'Неделя',

					'Mon'=>'Пн',
					'Tue'=>'Вт',
					'Wed'=>'Ср',
					'Thu'=>'Чт',
					'Fri'=>'Пт',
					'Sat'=>'Сб',
					'Sun'=>'Нд'
				));
			break;
		}

		return $data;
	}


	/**
	 * Подготовка имени файла или директории
	 *
	 * @param string $st
	 * @return string
	 */
	function prepare_fname($st)
	{
		$st = strip_tags($st);

		$st = strtr($st,'ABCDEFGHIJKLMNOPQRSTUVWXYZАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЬЪЫЭЮЯ',
						'abcdefghijklmnopqrstuvwxyzабвгдеёжзийклмнопрстуфхцчшщьъыэюя');

		$st = translit_string(trim($st));

		$st = preg_replace(array('/[^a-z0-9_-]/', '/--+/'), '-', $st);

		return trim($st, '-');
	}


	/**
	 * Отображение даты в фармате
	 * 25 дней/минут/секунд назад
	 * и т.д.
	 *
	 * @param string $date (TIMESTAMP)
	 * @return string
	 */
	function human_date($date)
	{
		$stf		= 0;
		$cur_time	= time();
		$diff		= $cur_time - $date;

		$language = (defined('ACP') && ACP)
			? $_SESSION['admin_language']
			: $_SESSION['user_language'];

		$language = strtolower($language);

		switch ($language)
		{
			default:
			case 'ru':
				$seconds	= array('секунда', 'секунды', 'секунд');
				$minutes	= array('минута', 'минуты', 'минут');
				$hours		= array('час', 'часа', 'часов');
				$days		= array('день', 'дня', 'дней');
				$weeks		= array('неделя', 'недели', 'недель' );
				$months		= array('месяц', 'месяца', 'месяцев');
				$years		= array('год', 'года', 'лет');
				$decades	= array('десятилетие', 'десятилетия', 'десятилетий');
				$ago		= 'назад';
			break;

			case 'ua':
			case 'uk':
				$seconds	= array('секунда', 'секунди', 'секунд');
				$minutes	= array('хвилина', 'хвилини', 'хвилин');
				$hours		= array('година', 'години', 'годин');
				$days		= array('день', 'дня', 'днів');
				$weeks		= array('тиждень', 'тижня', 'тижнів' );
				$months		= array('месяць', 'місяця', 'місяців');
				$years		= array('рік', 'року', 'років');
				$decades	= array('десятиріччя', 'десятиріччя', 'десятиріч');
				$ago		= 'назад';
			break;

			case 'en':
				$seconds	= array('second', 'seconds', 'seconds');
				$minutes	= array('minute', 'minutes', 'minutes');
				$hours		= array('hour', 'hours', 'hours');
				$days		= array('day', 'days', 'days');
				$weeks		= array('week', 'weeks', 'weeks' );
				$months		= array('month', 'months', 'months');
				$years		= array('year', 'years', 'years');
				$decades	= array('decade', 'decades', 'decades');
				$ago		= 'ago';
			break;
		}

		$phrase = array($seconds, $minutes, $hours, $days, $weeks, $months, $years, $decades);

		$length = array(1, 60, 3600, 86400, 604800, 2630880, 31570560, 315705600);

		for ($i = sizeof($length) - 1; ($i >= 0) && (($no = $diff / $length[ $i ]) <= 1 ); $i --)
		{
			;
		}

		if ($i < 0)
		{
			$i = 0;
		}

		$_time = $cur_time - ($diff % $length[$i]);

		$no = floor($no);

		$value = sprintf("%d %s ", $no, date_phrase($no, $phrase[$i]));

		if (($stf == 1) && ($i >= 1) && (($cur_time - $_time) > 0))
		{
			$value .= time_ago($_time);
		}

		return $value . $ago;
	}


	function date_phrase($number, $titles)
	{
		$cases = array( 2, 0, 1, 1, 1, 2 );

		return $titles[($number % 100 > 4 && $number % 100 < 20)
			? 2
			: $cases[min($number % 10, 5)]];
	}
?>