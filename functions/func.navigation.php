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
	 * Функция обработки навигации
	 *
	 * @param int $navi_tag - идентификатор меню навигации
	 * @return mixed|string
	 */
	function parse_navigation($navi_tag)
	{
		global $AVE_DB, $AVE_Core;

		// Извлекаем id из аргумента
		$navi_id = $navi_tag[1];

		Debug::startTime('NAVIAGTION_' . $navi_id);

		// Достаем для проверки тип меню
		$sql = "
			SELECT
				# NAVIGATION = $navi_id
				expand_ext
			FROM
				".PREFIX."_navigation
			WHERE
				navigation_id = '" . $navi_id . "'
			OR
				alias = '" . $navi_id . "'
		";

		$expnad_ext = $AVE_DB->Query($sql, -1, 'nav_' . $navi_id, true, '.naviagtion')->GetCell();

		// извлекаем level из аргумента
		$navi_print_level = (! empty($navi_tag[2]))
			? $navi_tag[2]
			: '';

		$navi = '';

		$cache_file = BASE_DIR . '/tmp/cache/sql/navigations/' . $navi_id . '/template.cache';

		// Если включен DEV MODE, то отключаем кеширование запросов
		if (defined('DEV_MODE') AND DEV_MODE || $expnad_ext != 1)
			$cache_file = null;

		if (! file_exists(dirname($cache_file)))
			mkdir(dirname($cache_file), 0766, true);

		// получаем меню навигации по id,
		// и если такой не существует, выводим сообщение

		if (file_exists($cache_file))
		{
			$navi_menu = unserialize(file_get_contents($cache_file));
		}
		else
			{
				$navi_menu = get_navigations($navi_id);

				if ($cache_file)
					file_put_contents($cache_file, serialize($navi_menu));
			}

		if (! $navi_menu)
		{
			echo 'Menu ', $navi_id, ' not found!';
			return;
		}

		// выставляем гостевую группу по дефолту
		if (! defined('UGROUP'))
			define('UGROUP', 2);

		// выходим, если навиг. не предназначена для текущей группы
		if (! in_array(UGROUP, $navi_menu->user_group))
			return false;

		// Находим активный пункт (связь текущего открытого документа и навигации). Нас интересуют:
		//		1) документы, которые сами связаны с пунктом меню
		//		2) пункты навигации, у которых ссылка совпадает с алиасом дока
		//		3) текущий level, текущий id
		// возвращаем в $navi_active через запятую id пунктов:
		//		1) активный пункт
		//		2) родители активного пункта
		// после ; через запятую все level-ы текущего пути, чтобы потом взять max
		// после ; id текущего пункта

		// id текущего документа. Если не задан, то главная страница
		$doc_active_id = (int)(($_REQUEST['id'])
			? $_REQUEST['id']
			: 1);

		// алиас текущего документа
		$alias = ltrim(isset($AVE_Core->curentdoc->document_alias)
			? $AVE_Core->curentdoc->document_alias
			: '');

		// запрос для выборки по текущему алиасу
		$sql_doc_active_alias = '';

		$url_suff = '';

		if (defined('URL_SUFF') AND URL_SUFF)
		{
			$url_suff = "
				OR nav.alias = '" . $alias . URL_SUFF . "'
				OR nav.alias = '/" . $alias . URL_SUFF . "'
			";
		}

		if ($AVE_Core->curentdoc->Id == $doc_active_id)
		{
			$sql_doc_active_alias = "
				OR nav.alias = '" . $alias . "'
				OR nav.alias = '/" . $alias . "'
				" . $url_suff . "
			";
		}

		$navi_active = $AVE_DB->Query("
			SELECT
				CONCAT_WS(
					';',
					CONCAT_WS(',', nav.navigation_item_id, nav.parent_id, nav2.parent_id),
					CONCAT_WS(',', nav.level),
					nav.navigation_item_id
				)
			FROM
				" . PREFIX . "_navigation_items AS nav
			JOIN
				" . PREFIX . "_documents AS doc
			LEFT JOIN
				" . PREFIX . "_navigation_items AS nav2
				ON
					nav2.navigation_item_id = nav.parent_id
			WHERE
				nav.status = 1
			AND
				nav.navigation_id = '" . $navi_menu->navigation_id . "'
			AND
				doc.Id = " . $doc_active_id . "
			AND (
					nav.document_id = '" . $doc_active_id . "'" .
					$sql_doc_active_alias . "
					OR
						nav.navigation_item_id = doc.document_linked_navi_id
				)
		")->GetCell();

		$navi_active = @explode(';', $navi_active);

		// готовим 2 переменные с путём
		if ($navi_active[0])
			$navi_active_way = @explode(',', $navi_active[0]);

		$navi_active_way[] = '0';

		$navi_active_way_str = implode(',', $navi_active_way);

		// текущий уровень
		$navi_active_level = (int)max(@explode(',', (isset($navi_active[1])
			? (int)$navi_active[1]
			: 0)))+1;

		// текущий id
		$navi_active_id = (isset($navi_active[2])
			? (int)$navi_active[2]
			: 0);

		// если просят вывести какие-то конкретные уровни:
		$sql_navi_level = '';
		$sql_navi_active = '';

		if ($navi_print_level)
		{
			$sql_navi_level = ' AND level IN (' . $navi_print_level . ') ';
			$sql_navi_active = ' AND parent_id IN (' . $navi_active_way_str . ') ';
		}

		// обычное использование навигации
		else
		{
			switch ($navi_menu->expand_ext)
			{
				// текущий и родительский уровни
				case 0:
					$sql_navi_active = ' AND parent_id IN (' . $navi_active_way_str . ') ';
					$navi_parent = 0;
					break;

				// все уровни
				case 1:
					$navi_parent = 0;
					break;

				// только текущий уровень
				case 2:
					$sql_navi_level = ' AND level = ' . $navi_active_level . ' ';
					$navi_parent = $navi_active_id;
					break;
			}
		}

		$cache_items = BASE_DIR . '/tmp/cache/sql/navigations/' . $navi_id . '/items.cache';

		$navi_items = array();

		// Если включен DEV MODE, то отключаем кеширование запросов
		if (defined('DEV_MODE') AND DEV_MODE || $expnad_ext != 1)
			$cache_items = null;

		if (empty($navi_print_level))
		{
			//-- Проверяем есть файл кеша, если есть пропускам запрос к БД
			if (! file_exists($cache_items))
			{
				//-- Запрос пунктов меню
				$sql = "
					SELECT
						*
					FROM
						" . PREFIX . "_navigation_items
					WHERE
						status = '1'
					AND
						navigation_id = '" . $navi_menu->navigation_id . "'" .
					$sql_navi_level .
					$sql_navi_active . "
					ORDER BY
						position ASC
				";

				$sql_navi_items = $AVE_DB->Query($sql);

				while ($row_navi_items = $sql_navi_items->FetchAssocArray())
					$navi_items[$row_navi_items['parent_id']][] = $row_navi_items;

				if ($cache_items)
					file_put_contents($cache_items, serialize($navi_items));
			}
			else
				{
					$navi_items = unserialize(file_get_contents($cache_items));
				}
		}
		else
			{
				//-- Запрос пунктов меню
				$sql = "
					SELECT
						*
					FROM
						" . PREFIX . "_navigation_items
					WHERE
						status = '1'
					AND
						navigation_id = '" . $navi_menu->navigation_id . "'" .
					$sql_navi_level . "
					ORDER BY
						position ASC
				";

				$sql_navi_items = $AVE_DB->Query($sql);

				while ($row_navi_items = $sql_navi_items->FetchAssocArray())
				{
					$navi_items[$row_navi_items['parent_id']][] = $row_navi_items;
				}

				$keys = array_keys($navi_items);
				$navi_parent = ! empty($keys)
					? $keys[0]
					: 0;
			}

		// Парсим теги в шаблонах пунктов
		$navi_item_tpl = array(
			1 =>  array(
				'inactive'	=> $navi_menu->level1,
				'active'	=> $navi_menu->level1_active
			),
			2 =>  array(
				'inactive'	=> $navi_menu->level2,
				'active'	=> $navi_menu->level2_active
			),
			3 =>  array(
				'inactive'	=> $navi_menu->level3,
				'active'	=> $navi_menu->level3_active
			)
		);

		// запускаем рекурсивную сборку навигации
		if ($navi_items)
			$navi = printNavi($navi_menu, $navi_items, $navi_active_way, $navi_item_tpl, $navi_parent);

		// преобразуем все ссылке в коде
		$navi = rewrite_link($navi);

		// удаляем переводы строк и табуляции
		$navi = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $navi);
		$navi = str_replace(array("\n","\r"),'',$navi);

		$gen_time = Debug::endTime('NAVIAGTION_' . $navi_id);

		$GLOBALS['block_generate']['NAVIAGTIONS'][$navi_id] = $gen_time;

		return $navi;
	}


	/**
	 * Рекурсивная функция для формирования меню навигации
	 *
	 * @param object	$navi_menu меню (шаблоны, параметры)
	 * @param array		$navi_items (пункты по родителям)
	 * @param array		$navi_active_way ("активный путь")
	 * @param array		$navi_item_tpl (шаблоны пунктов)
	 * @param int		$parent (исследуемый родитель, изначально 0 - верхний уровень)
	 * @return string	$navi - готовый код навигации
	 */
	function printNavi($navi_menu, $navi_items, $navi_active_way, $navi_item_tpl, $parent = 0)
	{
		// выясняем уровень
		$navi_item_level = $navi_items[$parent][0]['level'];

		// собираем каждый пункт в данном родителе -> в переменной $item

		$x = 0;
		$items_count = count($navi_items[$parent]);

		foreach ($navi_items[$parent] as $row)
		{
			$x++;
			$last_item = ($x == $items_count ? true : false);
			$item_num = $x;

			// Проверяем пункт меню на принадлежность к "активному пути" и выбираем шаблон
			$item = (in_array($row['navigation_item_id'], $navi_active_way))
				? $navi_item_tpl[$navi_item_level]['active']
				: $navi_item_tpl[$navi_item_level]['inactive'];

			################### ПАРСИМ ТЕГИ ###################
			// id
			@$item = str_replace('[tag:linkid]', $row['navigation_item_id'], $item);
			// название
			@$item = str_replace('[tag:linkname]', $row['title'], $item);
			//Путь
			$item = str_replace('[tag:path]', ABS_PATH, $item);
			// ссылка
			if ($row['document_id'])
			{
				//запрещаем слешь в конце ссылки
				//$item = str_replace('[tag:link]', 'index.php?id=' . $row['document_id'] . "&amp;doc=" . ((!$row['alias']) ? prepare_url($row['title']) : trim($row['alias'], '/')), $item);
				//разрешаем слешь в конце ссылки
				$item = str_replace('[tag:link]', 'index.php?id=' . $row['document_id'] . "&amp;doc=" . ((!$row['alias']) ? prepare_url($row['title']) : trim($row['alias'])), $item);

				$item = str_ireplace('"//"', '"/"', str_ireplace('///', '/', rewrite_link($item)));
			}
			else
				{
					$item = str_replace('[tag:link]', $row['alias'], $item);
				}

			if (start_with('www.', $row['alias']))
				$item = str_replace('www.', 'http://www.', $item);

			// target
			$item = str_replace('[tag:target]', $row['target'], $item);
			// описание
			@$item = str_replace('[tag:desc]', stripslashes($row['description']), $item);
			// изображение
			@$item = str_replace('[tag:img]', stripslashes($row['image']), $item);

			if ($row['image'] != '')
			{
				@$img = explode('.', $row['image']);
				@$row['image_act'] = $img[0] . "_act.".$img[1];
				@$item = str_replace('[tag:img_act]', stripslashes($row['image_act']), $item);
			}

			if ($row['css_id'] != '')
			{
				@$item = str_replace('[tag:css_id]', stripslashes($row['css_id']), $item);
			}
			else
				{
					@$item = str_replace('[tag:css_id]', '', $item);
				}

			if ($row['css_class'] != '')
			{
				@$item = str_replace('[tag:css_class]', stripslashes($row['css_class']), $item);
			}
			else
				{
					@$item = str_replace('[tag:css_class]', '', $item);
				}

			if ($row['css_style'] != '')
			{
				@$item = str_replace('[tag:css_style]', stripslashes($row['css_style']), $item);
			}
			else
				{
					@$item = str_replace('[tag:css_style]', '', $item);
				}

			$item = '<'.'?php $item_num = ' . var_export($item_num, true) . '; ?'.'>' . $item;
			$item = '<'.'?php $last_item = ' . var_export($last_item, true) . '; ?'.'>' . $item;

			$item = str_replace('[tag:if_first]', '<'.'?php if(isset($item_num) && $item_num===1) { ?'.'>', $item);
			$item = str_replace('[tag:if_not_first]', '<'.'?php if(isset($item_num) && $item_num!==1) { ?'.'>', $item);

			$item = str_replace('[tag:if_last]', '<'.'?php if(isset($last_item) && $last_item) { ?'.'>', $item);
			$item = str_replace('[tag:if_not_last]', '<'.'?php if(isset($item_num) && !$last_item) { ?'.'>', $item);

			$item = str_replace('[tag:if_sub_level]', '<'.'?php if (isset($exist_level) && $exist_level) { ?'.'>', $item);
			$item = str_replace('[tag:if_no_sub_level]', '<'.'?php if (isset($exist_level) && !$exist_level) { ?'.'>', $item);

			$item = preg_replace('/\[tag:if_every:([0-9-]+)\]/u', '<'.'?php if(isset($item_num) && !($item_num % $1)){ '.'?'.'>', $item);
			$item = preg_replace('/\[tag:if_not_every:([0-9-]+)\]/u', '<'.'?php if(isset($item_num) && ($item_num % $1)){ '.'?'.'>', $item);

			$item = str_replace('[tag:/if]', '<'.'?php  } ?>', $item);
			$item = str_replace('[tag:if_else]', '<'.'?php  }else{ ?>', $item);

			################### //ПАРСИМ ТЕГИ ##################

			// Удаляем ошибочные теги
			@$item = preg_replace('/\[tag:([a-zA-Z0-9-_]+)\]/', '', $item);

			// Определяем тег для вставки следующего уровня
			switch ($navi_item_level)
			{
				case 1 :
					$tag = '[tag:level:2]';
					$tag_exist = '[tag:level:2:exist]';
					break;

				case 2 :
					$tag = '[tag:level:3]';
					$tag_exist = '[tag:level:3:exist]';
					break;

				default:
					$tag = '';
					$tag_exist = 0;
			}

			// Если есть подуровень, то заново запускаем для него функцию и вставляем вместо тега
			if (! empty($navi_items[$row['navigation_item_id']]))
			{
				$item = '<'.'?php $exist_level = true; ?'.'>' . $item;
				$item_sublevel = printNavi($navi_menu, $navi_items, $navi_active_way, $navi_item_tpl, $row['navigation_item_id']);
				$item = @str_replace($tag, $item_sublevel, $item);
				$item = @str_replace($tag_exist, 1, $item);
			}
			// Если нет подуровня, то удаляем тег
			else
				{
					$item = '<'.'?php $exist_level = false; ?'.'>' . $item;
					$item = @str_replace(@$tag,'',$item);
					$item = @str_replace($tag_exist, 0, $item);
				}

			// Подставляем в переменную навигации готовый пункт
			if (empty($navi))
				$navi = '';

			$navi .= eval2var(' ?'.'>' . $item . '<'.'?php ');
		}

		// Вставляем все пункты уровня в шаблон уровня
		switch ($navi_item_level)
		{
			case 1 :
				$navi = str_replace("[tag:content]", $navi, $navi_menu->level1_begin);
				break;
			case 2 :
				$navi = str_replace("[tag:content]", $navi, $navi_menu->level2_begin);
				break;
			case 3 :
				$navi = str_replace("[tag:content]", $navi, $navi_menu->level3_begin);
				break;
		}

		// Возвращаем сформированный уровень
		return $navi;
	}


	/**
	 * Возвращает меню навигации
	 *
	 * @param int $id идентификатор меню навигации
	 * @return string|mixed объект с навигацией по id, либо массив всех навигаций
	 */
	function get_navigations($id = null)
	{
		global $AVE_DB;

		$navigations = array();

		if ($id)
		{
				$sql = $AVE_DB->Query("
					SELECT
						*
					FROM
						" . PREFIX . "_navigation
					WHERE
						" . (is_numeric($id) ? 'navigation_id' : 'alias') . " = '" . $id . "'
				");
		}
		else
			{
				$sql = $AVE_DB->Query("
					SELECT
						*
					FROM
						" . PREFIX . "_navigation
				");
			}

		while ($row = $sql->FetchRow())
		{
			$row->user_group = explode(',', $row->user_group);

			if ($id)
				$navigations[$id] = $row;
			else
				$navigations[$row->navigation_id] = $row;
		}

		if ($id)
			return $navigations[$id];
		else
			return $navigations;
	}


	/**
	 * Проверка прав доступа к навигации по группе пользователя
	 *
	 * @param int $id идентификатор меню навигации
	 * @return boolean
	 */
	function check_navi_permission($id)
	{
		$navigation = get_navigations($id);

		if (empty($navigation->user_group))
			return false;

		if (! defined('UGROUP'))
			define('UGROUP', 2);

		if (! in_array(UGROUP, $navigation->user_group))
			return false;

		return true;
	}
?>