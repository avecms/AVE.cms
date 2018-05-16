<?php

	// Проверка
	if (! defined('BASE_DIR'))
		exit('Directly access denied');

	/**
	 * This source file is part of the AVE.cms. More information,
	 * documentation and tutorials can be found at http://www.ave-cms.ru
	 *
	 * @package		 AVE.cms
	 * @file		 system/core/helper/pagination.php
	 * @author		 @
	 * @copyright	 2007-2017 (c) AVE.cms
	 * @link		 http://www.ave-cms.ru
	 * @version		 4.0
	 * @since		 $date$
	 * @license		 license GPL v.2 http://www.ave-cms.ru/license.txt
	*/


	class AVE_Paginations
	{
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

		public static function getPagination($total_pages, $type, $template_label, $pagination_id = 1, $pagination_box_ext = '')
		{
			$pagination = '';

			if (! in_array($type, array('page', 'apage', 'artpage')))
				$type = 'page';

			$containers = array();

			$curent_page = self::getCurrentPage($type);

			$containers = self::getContainers($pagination_id);

			if ($curent_page == 1)						$pages = array ($curent_page,   $curent_page + 1, $curent_page + 2, $curent_page + 3, $curent_page + 4);
			elseif ($curent_page == 2)					$pages = array ($curent_page-1, $curent_page,     $curent_page+1,   $curent_page+2,   $curent_page+3);
			elseif ($curent_page + 1 == $total_pages)	$pages = array ($curent_page-3, $curent_page-2,   $curent_page-1,   $curent_page,     $curent_page+1);
			elseif ($curent_page == $total_pages)		$pages = array ($curent_page-4, $curent_page-3,   $curent_page-2,   $curent_page-1,   $curent_page);
			else										$pages = array ($curent_page-2, $curent_page-1,   $curent_page,     $curent_page+1,   $curent_page+2);

			$pages = array_unique($pages);

			// $pagination_link_box						Контенйнер для ссылок %s
			// $pagination_separator_box				Контенйнер для метки о наличии страниц кроме видимых %s
			// $pagination_active_link_box				Контенйнер для активного элемента %s
			// $pagination_start_label					Текст ссылки "Первая"
			// $pagination_end_label					Текст ссылки "Последняя"
			// $pagination_separator_label				Текст метки о наличии страниц кроме видимых
			// $pagination_next_label					Текст ссылки "Следующая"
			// $pagination_prev_label					Текст ссылки "Предыдущая"
			// $pagination_link_template				Шаблон ссылки
			// $pagination_link_active_template			Шаблон активной ссылки
			// $pagination_navigation_box				Общий контейнер

			extract($containers);

			// index.php?id=8&amp;doc=catalog&amp;apage={s}

			// Первая
			if ($total_pages > 5 && $curent_page > 3)
			{
				$search = array('[link]', '[page]', '[name]');
				$replace = array($template_label, 1, $pagination_start_label);

				$first = str_replace($search, $replace, $pagination_link_template);

				$pagination .= sprintf($pagination_link_box, str_replace(array('{s}', '{t}'), $pagination_start_label, str_replace(array('&amp;'. $type .'={s}', '&' . $type .'={s}', '/' . $type . '-{s}'), '', $first)));

				// Если есть шаблон метки о наличии страниц, добавляем
				if ($pagination_separator_label != '')
					$pagination .= sprintf($pagination_separator_box, $pagination_separator_label);
			}

			// Предыдущая
			if ($curent_page > 1)
			{
				// Если равна 2
				if ($curent_page - 1 == 1)
				{
					$search = array('[link]', '[page]', '[name]');
					$replace = array($template_label, $curent_page-1, $pagination_prev_label);

					$link = str_replace($search, $replace, $pagination_link_template);

					$pagination .= sprintf($pagination_link_box, str_replace('{t}', $pagination_prev_label, str_replace(array('&amp;' . $type . '={s}', '&' . $type . '={s}', '/' . $type . '-{s}'), '', $link)));
				}
				// Если больше 2х
				else
				{

					$search = array('[link]', '[page]', '[name]');
					$replace = array($template_label, $curent_page - 1, $pagination_prev_label);

					$link = str_replace($search, $replace, $pagination_link_template);

					$pagination .= sprintf($pagination_link_box, str_replace('{t}', $pagination_prev_label, str_replace('{s}', ($curent_page - 1), $link)));
				}
			}

			foreach ($pages as $page)
			{
				if ($page >= 1 && $page <= $total_pages)
				{
					// Текущий номер страницы (активная страница)
					if ($curent_page == $page && $curent_page != 1)
					{
						$search = array('[link]', '[page]', '[name]');
						$replace = array($template_label, $curent_page, $curent_page);

						$link = str_replace($search, $replace, $pagination_link_active_template);

						$pagination .= sprintf($pagination_active_link_box, str_replace('{s}', ($curent_page), $link));
					}
					else
					{
						// Страница номер 1
						if ($page == 1)
						{
							$search = array('[link]', '[page]', '[name]');
							$replace = array($template_label, $page, $page);

							$link = str_replace($search, $replace, $pagination_link_template);

							$pagination .= sprintf($pagination_link_box, str_replace(array('{s}', '{t}'), $page, str_replace(array('&amp;' . $type . '={s}', '&' . $type . '={s}', '/' . $type . '-{s}'), '', $link)));
						}
						// Остальные неактивные номера страниц
						else
						{
							$search = array('[link]', '[page]', '[name]');
							$replace = array($template_label, $page, $page);

							$link = str_replace($search, $replace, $pagination_link_template);

							$pagination .= sprintf($pagination_link_box, str_replace(array('{s}', '{t}'), $page, $link));
						}
					}
				}
			}

			// Следующая
			if ($curent_page < $total_pages)
			{
				$search = array('[link]', '[page]', '[name]');
				$replace = array($template_label, $curent_page + 1, $pagination_next_label);

				$link = str_replace($search, $replace, $pagination_link_template);

				$pagination .= sprintf($pagination_link_box, str_replace('{t}', $pagination_next_label, str_replace('{s}', ($curent_page + 1), $link)));
			}

			// Последняя
			if ($total_pages > 5 && ($curent_page < $total_pages - 2))
			{
				// Если есть шаблон метки о наличии страниц, добавляем
				if ($pagination_separator_label != '')
					$pagination .= sprintf($pagination_separator_box, $pagination_separator_label);

				$search = array('[link]', '[page]', '[name]');
				$replace = array($template_label, $total_pages, $pagination_end_label);

				$last = str_replace($search, $replace, $pagination_link_template);

				$pagination .= sprintf($pagination_link_box, str_replace('{t}', $pagination_end_label, str_replace('{s}', $total_pages, $last)));
			}

			// Общий контейнер
			if ($pagination != '')
			{
				// Если пришел внешний контейнер для
				if ($pagination_box_ext != '')
					$pagination = sprintf($pagination_box_ext, $pagination);
				else if ($pagination_box != '')
					$pagination = sprintf($pagination_box, $pagination);
			}

			return $pagination;
		}


		/**
		 * Текущая страница
		 *
		 * @param string $type	тип постраничной навигации,
		 * 						допустимые значения: page, apage, artpage
		 * @return int			номер текущей страницы
		 */
		public static function getCurrentPage($type = 'page')
		{
			if (! in_array($type, array('page', 'apage', 'artpage')))
				return 1;

			$page = (isset($_REQUEST[$type]) && is_numeric($_REQUEST[$type]))
				? $_REQUEST[$type]
				: 1;

			return (int)$page;
		}


		/**
		 * Достаем всю информацию о данной пагинации
		 *
		 * @param int $id	id постраничной навигации
		 * @return array	информация
		 */
		public static function getContainers($id)
		{
			global $AVE_DB;

			$containers = $AVE_DB->Query("
				SELECT
					# PAGINATION = $id
					*
				FROM
					" . PREFIX . "_paginations
				WHERE
					id = '" . $id . "'
			", -1, 'paginations', true, '.paginations')->FetchAssocArray();

			return $containers;
		}


		/**
		 * Очистка кеша постраничной навигации
		 *
		 * @param void
		 * @return
		 */
		public static function clearCache()
		{
			global $AVE_DB;

			$AVE_DB->clearCache('paginations');
		}
	}