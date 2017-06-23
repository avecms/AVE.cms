<?php

/**
 * Replace function htmlspecialchars_decode()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.htmlspecialchars_decode
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.6 $
 * @since       PHP 5.1.0
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_htmlspecialchars_decode($string, $quote_style = null)
{
    // Sanity check
    if (!is_scalar($string)) {
        user_error('htmlspecialchars_decode() expects parameter 1 to be string, ' .
            gettype($string) . ' given', E_USER_WARNING);
        return;
    }

    if (!is_int($quote_style) && $quote_style !== null) {
        user_error('htmlspecialchars_decode() expects parameter 2 to be integer, ' .
            gettype($quote_style) . ' given', E_USER_WARNING);
        return;
    }

    // The function does not behave as documented
    // This matches the actual behaviour of the function
    if ($quote_style & ENT_COMPAT || $quote_style & ENT_QUOTES) {
        $from = array('&quot;', '&#039;', '&lt;', '&gt;', '&amp;');
        $to   = array('"', "'", '<', '>', '&');
    } else {
        $from = array('&lt;', '&gt;', '&amp;');
        $to   = array('<', '>', '&');
    }

    return str_replace($from, $to, $string);
}

// Define
if (!function_exists('htmlspecialchars_decode')) {
    function htmlspecialchars_decode($string, $quote_style = null)
    {
        return php_compat_htmlspecialchars_decode($string, $quote_style);
    }
}

/**
 * AVE.cms
 *
 * @package AVE.cms
 * @filesource
 */

/**
 * Класс генерации YML
 * YML (Yandex Market Language) - стандарт, разработанный "Яндексом"
 * для принятия и публикации информации в базе данных Яндекс.Маркет
 * YML основан на стандарте XML (Extensible Markup Language)
 * описание формата данных YML http://partner.market.yandex.ru/legal/tt/
 */
class AVE_YML
{

/**
 *	СВОЙСТВА
 */

	/**
	 * Кодировка
	 *
	 * @public string
	 */
	public $from_charset = 'windows-1251';

	/**
	 * Элемент описания магазина
	 *
	 * @public string
	 */
	public $shop = array('name'=>'', 'company'=>'', 'url'=>'');

	/**
	 * Элемент валюты
	 *
	 * @public string
	 */
	public $currencies = array();

	/**
	 * Элемент категории
	 *
	 * @public string
	 */
	public $categories = array();

	/**
	 * Элемент предложения
	 *
	 * @public string
	 */
	public $offers = array();

	/**
	 * Конструктор
	 *
	 * @param string $from_charset
	 */
	function AVE_YML($from_charset = 'windows-1251')
	{
		$this->from_charset = trim(strtolower($from_charset));
	}

/**
 *	ВНУТРЕННИЕ МЕТОДЫ
 */

	/**
	 * Преобразование массива в тег
	 *
	 * @param array $tags
	 * @return string
	 */
	function _ymlArray2Tag($tags)
	{
		$tag = '';
		foreach ($tags as $tag_name => $tag_value)
		{
			$tag .= '<' . $tag_name . '>' . $tag_value . '</' . $tag_name . '>';
		}
		$tag .= "\r\n";

		return $tag;
	}

	/**
	 * Преобразование массива в атрибуты
	 *
	 * @param array $attributes
	 * @param string $tag_name
	 * @param string $tag_value
	 * @return string
	 */
	function _ymlArray2Attribute($attributes, $tag_name, $tag_value = '')
	{
		$attribute = '<' . $tag_name . ' ';
		foreach ($attributes as $attribute_name => $attribute_value)
		{
			$attribute .= $attribute_name . '="' . $attribute_value . '" ';
		}
		$attribute .= ($tag_value != '') ? '>' . $tag_value . '</' . $tag_name . '>' : '/>';
		$attribute .= "\r\n";

		return $attribute;
	}

	/**
	 * Подготовка текстового поля в соответствии с требованиями Яндекса
	 * Запрещены любые html-теги. Стандарт XML не допускает использования в текстовых данных
	 * непечатаемых символов с ASCII-кодами в диапазоне значений от 0 до 31 (за исключением
	 * символов с кодами 9, 10, 13 - табуляция, перевод строки, возврат каретки). Также этот
	 * стандарт требует обязательной замены некоторых символов на эквивалентные им символьные
	 * примитивы.
	 * @param string $field
	 * @return string
	 */
	function _ymlFieldPrepare($field)
	{
		$field = htmlspecialchars_decode(trim($field));
		$field = strip_tags($field);
		$from = array('"', '&', '>', '<', '\'');
		$to = array('&quot;', '&amp;', '&gt;', '&lt;', '&apos;');
		$field = str_replace($from, $to, $field);
		if ($this->from_charset != 'windows-1251')
		{
			$field = iconv($this->from_charset, 'windows-1251//IGNORE//TRANSLIT', $field);
		}
		$field = preg_replace('#[\x00-\x08\x0B-\x0C\x0E-\x1F]+#is', ' ', $field);

		return trim($field);
	}

	/**
	 * формирование элемента catalog
	 *
	 * @return string
	 */
	function _ymlElementCatalogGet()
	{
		$eol = "\r\n";

		$catalog = '<shop>' . $eol;

		// информация о магазине
		$catalog .= $this->_ymlArray2Tag($this->shop);

		// валюты
		$catalog .= '<currencies>' . $eol;
		foreach ($this->currencies as $currency)
		{
			$catalog .= $this->_ymlArray2Attribute($currency, 'currency');
		}
		$catalog .= '</currencies>' . $eol;

		// категории
		$catalog .= '<categories>' . $eol;
		foreach ($this->categories as $category)
		{
			$category_name = $category['name'];
			unset($category['name']);
			$catalog .= $this->_ymlArray2Attribute($category, 'category', $category_name);
		}
		$catalog .= '</categories>' . $eol;

		// товарные позиции
		$catalog .= '<offers>' . $eol;
		foreach ($this->offers as $offer)
		{
			$data = $offer['data'];
			unset($offer['data']);
			$catalog .= $this->_ymlArray2Attribute($offer, 'offer', $this->_ymlArray2Tag($data));
		}
		$catalog .= '</offers>' . $eol;

		$catalog .= '</shop>';

		return $catalog;
	}

/**
 *	ВНЕШНИЕ МЕТОДЫ
 */

	/**
	 * Формирование массива для элемента shop описывающего магазин
	 *
	 * @param string $name - Короткое название магазина (название, которое выводится в списке
	 * 		найденных на Яндекс.Маркете товаров. Не должно содержать более 20 символов).
	 * 		Нельзя использовать слова, не имеющие отношения к наименованию магазина ("лучший", "дешевый"),
	 *		указывать номер телефона и т.п. Название магазина, должно совпадать с фактическим названием
	 * 		магазина, которое публикуется на сайте. При несоблюдении данного требования наименование
	 * 		может быть изменено Яндексом самостоятельно без уведомления Клиента.
	 * @param string $company - Полное наименование компании, владеющей магазином.
	 * 		Не публикуется, используется для внутренней идентификации.
	 * @param string $url - URL-адрес главной страницы магазина
	 */
	function ymlElementShopSet($name, $company, $url)
	{
		$this->shop['name'] = substr($this->_ymlFieldPrepare($name), 0, 20);
		$this->shop['company'] = $this->_ymlFieldPrepare($company);
		$this->shop['url'] = $this->_ymlFieldPrepare($url);
	}

	/**
	 * Добавление валюты
	 *
	 * @param string $id - код валюты (RUR, UAH, USD, EUR...)
	 * @param float|string $rate - курс этой валюты к валюте, взятой за единицу.
	 *	Параметр rate может иметь так же следующие значения:
	 *		CBRF - курс по Центральному банку РФ.
	 *		NBU - курс по Национальному банку Украины.
	 *		СВ - курс по банку той страны, к которой относится интернет-магазин
	 * 		по Своему региону, указанному в Партнерском интерфейсе Яндекс.Маркета.
	 * @param int $plus - используется только в случае rate = CBRF, NBU или СВ
	 *		и означает насколько увеличить курс в процентах от курса выбранного банка
	 * @return bool
	 */
	function ymlElementCurrencySet($id, $rate = 'CBRF', $plus = 0)
	{
		$rate = strtoupper($rate);
		$allow_rate = array('CBRF', 'NBU', 'CB');
		if (in_array($rate, $allow_rate))
		{
			$plus = str_replace(',', '.', $plus);
			if ($plus > 0)
			{
				$this->currencies[] = array(
					'id'=>$this->_ymlFieldPrepare(strtoupper($id)),
					'rate'=>$rate,
					'plus'=>(float)$plus
				);
			}
			else
			{
				$this->currencies[] = array(
					'id'=>$this->_ymlFieldPrepare(strtoupper($id)),
					'rate'=>$rate
				);
			}
		}
		else
		{
			$rate = str_replace(',', '.', $rate);
			$this->currencies[] = array(
				'id'=>$this->_ymlFieldPrepare(strtoupper($id)),
				'rate'=>(float)$rate
			);
		}

		return true;
	}

	/**
	 * Добавление категории товаров
	 *
	 * @param string $name - название рубрики
	 * @param int $id - id рубрики
	 * @param int $parent_id - id родительской рубрики, если нет, то -1
	 * @return bool
	 */
	function ymlElementCategorySet($name, $id, $parent_id = -1)
	{
		if ((int)$id < 1 || trim($name) == '') return false;
		if ((int)$parent_id > 0)
		{
			$this->categories[] = array(
				'id'=>(int)$id,
				'parentId'=>(int)$parent_id,
				'name'=>$this->_ymlFieldPrepare($name)
			);
		}
		else
		{
			$this->categories[] = array(
				'id'=>(int)$id,
				'name'=>$this->_ymlFieldPrepare($name)
			);
		}

		return true;
	}

	/**
	 * Добавление товарного предложения
	 *
	 * @param int $id - id товара (товарного предложения)
	 * @param array $data - массив остальных параметров (звездочкой помечены обязательные)
	 *	*url - URL-адрес страницы товара.
	 *	*name - Наименование товарного предложения.
	 *	*description - Описание товарного предложения.
	 *	*price - Цена, по которой данный товар можно приобрести.
	 *		Цена товарного предложения округляеся и выводится в зависимости от настроек пользователя.
	 *	*currencyId - Идентификатор валюты товара (RUR, USD, UAH).
	 *		Для корректного отображения цены в национальной валюте, необходимо использовать
	 * 		идентификатор (например, UAH) с соответствующим значением цены.
	 *	*categoryId - Идентификатор категории товара (целое число не более 18 знаков).
	 *		Товарное предложение может принадлежать только одной категории.
	 *	*delivery - Элемент, обозначающий возможность доставить соответствующий товар.
	 *		"false" данный товар не может быть доставлен("самовывоз").
	 *		"true" товар доставляется на условиях, которые указываются в партнерском интерфейсе
	 * 			 http://partner.market.yandex.ru на странице "редактирование".
	 *	picture - Ссылка на картинку соответствующего товарного предложения.
	 * 		Недопустимо давать ссылку на "заглушку", т.е. на картинку где написано
	 * 		"картинка отсутствует" или на логотип магазина.
	 *	vendor - Производитель.
	 *	vendorCode - Код товара (указывается код производителя).
	 *	local_delivery_cost - Стоимость доставки данного товара в Своем регионе.
	 *	sales_notes - Элемент, предназначенный для того, чтобы показать пользователям,
	 * 		чем отличается данный товар от других, или для описания акций магазина (кроме скидок).
	 * 		Допустимая длина текста в элементе - 50 символов.
	 *	manufacturer_warranty - Элемент предназначен для отметки товаров,
	 * 		имеющих официальную гарантию производителя. {true|false}
	 *	country_of_origin - Элемент предназначен для указания страны производства товара.
	 *	downloadable - Элемент предназначен для обозначения товара, который можно скачать.
	 * @param bool $available - Статус доступности товара - в наличии/на заказ.
	 *	"true" - товарное предложение в наличии.
	 * 		Магазин готов сразу договариваться с покупателем о доставке товара
	 *	"false" - товарное предложение на заказ.
	 * 		Магазин готов осуществить поставку товара на указанных условиях в течение месяца
	 *		(срок может быть больше для товаров, которые всеми участниками рынка поставляются только на заказ)..
	 *		Те товарные предложения, на которые заказы не принимаются, не должны выгружаться в Яндекс.Маркет.
	 */
	function ymlElementOfferSet($id, $data, $available = true)
	{
		$allowed = array(
			'url',
			'price',
			'currencyId',
			'categoryId',
			'picture',
			'delivery',
			'local_delivery_cost',
			'name',
			'vendor',
			'vendorCode',
			'description',
			'sales_notes',
			'manufacturer_warranty',
			'country_of_origin',
			'downloadable'
		);

		foreach ($data as $k => $v)
		{
			if (!in_array($k, $allowed)) unset($data[$k]);
			$data[$k] = strip_tags($this->_ymlFieldPrepare($v));
		}
		$tmp = $data;
		$data = array();
		// Стандарт XML учитывает порядок следования элементов,
		// поэтому важно соблюдать его в соответствии с порядком описанным в DTD
		foreach ($allowed as $key)
		{
			if (isset($tmp[$key])) $data[$key] = $tmp[$key];
		}
		$this->offers[] = array(
			'id'=>(int)$id,
			'data'=>$data,
			'available'=>($available) ? 'true' : 'false'
		);
	}

	/**
	 * Получить весь YML файл
	 *
	 * @return string
	 */
	function ymlGet()
	{
		$eol = "\r\n";

		$yml  = '<?xml version="1.0" encoding="windows-1251"?>' . $eol;
		$yml .= '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">' . $eol;
		$yml .= '<yml_catalog date="' . date('Y-m-d H:i') . '">' . $eol;
		$yml .= $this->_ymlElementCatalogGet();
		$yml .= '</yml_catalog>';

		return $yml;
	}
}

?>