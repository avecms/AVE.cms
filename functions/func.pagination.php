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
	 * Текущая страница
	 *
	 * @param string $type	тип постраничной навигации,
	 * 						допустимые значения: page, apage, artpage
	 * @return int			номер текущей страницы
	 */
	function get_current_page($type = 'page')
	{
		if (!in_array($type, array('page', 'apage', 'artpage'))) return 1;

		$page = (isset($_REQUEST[$type]) && is_numeric($_REQUEST[$type])) ? $_REQUEST[$type] : 1;

		return (int)$page;
	}

	/**
	 * Постраничная навигация для запросов и модулей
	 *
	 * @param int $total_pages			количество страниц в документе
	 * @param string $type				тип постраничной навигации,
	 * 									допустимые значения: page, apage, artpage
	 * @param string $template_label	шаблон метки навигации
	 * @param string $navi_box			контейнер постраничной навигации %s
	 * @return string					HTML-код постраничной навигации
	 */

	function get_pagination($total_pages, $type, $template_label, $navi_box = '')
	{
		$nav = '';

		if (!in_array($type, array('page', 'apage', 'artpage'))) $type = 'page';

		$curent_page = get_current_page($type);

		if	 ($curent_page     == 1)			$pages = array ($curent_page,   $curent_page+1, $curent_page+2, $curent_page+3, $curent_page+4);
		elseif ($curent_page   == 2)			$pages = array ($curent_page-1, $curent_page,   $curent_page+1, $curent_page+2, $curent_page+3);
		elseif ($curent_page+1 == $total_pages)	$pages = array ($curent_page-3, $curent_page-2, $curent_page-1, $curent_page,   $curent_page+1);
		elseif ($curent_page   == $total_pages)	$pages = array ($curent_page-4, $curent_page-3, $curent_page-2, $curent_page-1, $curent_page);
		else									$pages = array ($curent_page-2, $curent_page-1, $curent_page,   $curent_page+1, $curent_page+2);

		$pages = array_unique($pages);

		$link_box			= trim(get_settings('link_box'));			// Контенйнер для ссылок %s
		$separator_box		= trim(get_settings('separator_box'));		// Контенйнер для метки о наличии страниц кроме видимых %s
		$total_box			= trim(get_settings('total_box'));			// Контенйнер для Страница ХХХ из ХХХ %s
		$active_box			= trim(get_settings('active_box'));			// Контенйнер для активного элемента %s
		$total_label		= trim(get_settings('total_label'));		// Текст перед номерами страниц %d
		$start_label		= trim(get_settings('start_label'));		// Текст ссылки "Первая"
		$end_label			= trim(get_settings('end_label'));			// Текст ссылки "Последняя"
		$separator_label	= trim(get_settings('separator_label'));	// Текст метки о наличии страниц кроме видимых
		$next_label			= trim(get_settings('next_label'));			// Текст ссылки "Следующая"
		$prev_label			= trim(get_settings('prev_label'));			// Текст ссылки "Предыдущая"

		// Первая
		if ($total_pages > 5 && $curent_page > 3)
		{
			$first =  str_replace('data-pagination="{s}"', 'data-pagination="1"', $template_label);

			$nav .= sprintf($link_box, str_replace(array('{s}', '{t}'), $start_label, str_replace(array('&amp;'. $type .'={s}', '&' . $type .'={s}', '/' . $type . '-{s}'), '', $first)));
			if ($separator_label != '')
				$nav .= sprintf($separator_box, $separator_label);
		}

		// Предыдущая
		if ($curent_page > 1)
		{
			if ($curent_page - 1 == 1)
				$nav .= sprintf($link_box, str_replace(array('{s}', '{t}'), $prev_label, str_replace(array('&amp;'. $type .'={s}', '&' . $type .'={s}', '/' . $type . '-{s}'), '', $template_label)));
			else
				$nav .= sprintf($link_box, str_replace('{t}', $prev_label, str_replace('{s}', ($curent_page - 1), $template_label)));
		}

		foreach($pages as $val)
		{
			if ($val >= 1 && $val <= $total_pages)
			{
				if ($curent_page == $val)
				{
					// Текущий номер страницы (активная страница)
					$nav .= sprintf($link_box, sprintf($active_box, str_replace(array('{s}', '{t}'), $val, $curent_page)));
				}
				else
				{
					if ($val == 1)
					{
						// Страница номер 1
						$nav .= sprintf($link_box, str_replace(array('{s}', '{t}'), $val, str_replace(array('&amp;'.$type.'={s}','&'.$type.'={s}','/'.$type.'-{s}'), '', $template_label)));
					}
					else
					{
						// Остальные неактивные номера страниц
						$nav .= sprintf($link_box, str_replace(array('{s}', '{t}'), $val, $template_label));
					}
				}
			}
		}

		// Следующая
		if ($curent_page < $total_pages)
		{
			$nav .= sprintf($link_box, str_replace('{t}', $next_label, str_replace('{s}', ($curent_page + 1), $template_label)));
		}

		// Последняя
		if ($total_pages > 5 && ($curent_page < $total_pages-2))
		{
			if ($separator_label != '')
				$nav .= sprintf($separator_box, $separator_label);

			$nav .= sprintf($link_box, str_replace('{t}', $end_label, str_replace('{s}', $total_pages, $template_label)));
		}

		// Страница ХХХ из ХХХ
		if ($nav != '')
		{
			if ($total_label != '')
				$nav = sprintf($total_box, sprintf($total_label, $curent_page, $total_pages)) . $nav;

			// Оборачиваем в общий контейнер
			if ($navi_box != '')
				$nav = sprintf($navi_box, $nav);
		}

		return $nav;
	}

?>