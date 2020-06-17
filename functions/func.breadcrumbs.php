<?php

	/**
	 * AVE.cms
	 *
	 * @package AVE.cms
	 * @version 3.x
	 * @filesource
	 * @copyright © 2007-2017 AVE.cms, http://www.ave-cms.ru
	 *
	 * @license GPL v.2
	 */

	/**
	 * Формирование хлебных крошек
	 *
	 * @return string ссылка
	 */
	function get_breadcrumb()
	{
		global $AVE_DB, $AVE_Core;

		$bread_crumb = '';

		$bread_box = trim(get_settings('bread_box'));

		$bread_show_main = trim(get_settings('bread_show_main'));

		$bread_show_host = trim(get_settings('bread_show_host'));

		$bread_sepparator = trim(get_settings('bread_sepparator'));

		$bread_sepparator_use = trim(get_settings('bread_sepparator_use'));

		$bread_link_box = trim(get_settings('bread_link_box'));

		$bread_link_template = trim(get_settings('bread_link_template'));

		$bread_self_box = trim(get_settings('bread_self_box'));

		static $crumbs = [];

		$curent_document = get_current_document_id();

		if (isset($crumbs[$curent_document]))
			return $crumbs[$curent_document];

		$noprint = null;

		if ($bread_show_main)
		{
			$home_id = ($_SESSION['user_language'] == DEFAULT_LANGUAGE)
				? 1
				: $curent_document;

			$lang_home_alias = getDocument($home_id);

			$number = 1;

			$search = [
				'[name]',
				'[link]',
				'[count]'
			];

			$replace = [
				$lang_home_alias->document_breadcrum_title,
				$bread_show_host ? HOST . '/' . ltrim($lang_home_alias->document_alias, '/') : $lang_home_alias->document_alias,
				1
			];

			$link = str_replace($search, $replace, $bread_link_template);

			$bread_crumb = $lang_home_alias
				? sprintf($bread_link_box, $link)
				: '';

			if ($bread_sepparator_use)
				$bread_crumb .= $bread_sepparator;

			unset ($search, $replace, $link, $sql, $lang_home_alias);
		}

		if ($curent_document == 1 || $curent_document == PAGE_NOT_FOUND_ID)
			$noprint = 1;

		$row_document = getDocument($curent_document);

		$current = new stdClass();

		$current->document_breadcrum_title = (empty($row_document->document_breadcrum_title)
			? stripslashes(htmlspecialchars_decode($row_document->document_title))
			: stripslashes(htmlspecialchars_decode($row_document->document_breadcrum_title)));

		$row_document->document_parent = (isset($AVE_Core->curentdoc->document_parent) && $AVE_Core->curentdoc->document_parent != 0)
			? $AVE_Core->curentdoc->document_parent
			: $row_document->document_parent;

		if (isset($row_document->document_parent) && $row_document->document_parent != 0)
		{
			$i = 0;

			$current->document_parent = $row_document->document_parent;

			while ($current->document_parent != 0)
			{
				$row_doc = getDocument($current->document_parent);

				$current->document_parent = $row_doc->document_parent;

				if ($row_doc->document_parent == $row_doc->Id)
				{
					echo "Ошибка! Вы указали в качестве родительского документа текущий документ.<br>";
					$current->document_parent = 1;
				}

				if ($row_doc->document_status==1 && $row_document->document_parent != 0)
				{
					$crumb['document_breadcrum_title'][$i] = (empty($row_doc->document_breadcrum_title)
						? stripslashes(htmlspecialchars_decode($row_doc->document_title))
						: stripslashes(htmlspecialchars_decode($row_doc->document_breadcrum_title)));

					$crumb['document_alias'][$i] = $row_doc->document_alias;
					$crumb['Id'][$i] = $row_doc->Id;

					$i++;
				}

				if ($row_doc->document_parent == 0 AND $row_doc->Id != 1)
					$current->document_parent = 1;
			}

			$length = count($crumb['document_breadcrum_title']);

			$crumb['document_breadcrum_title'] = array_reverse($crumb['document_breadcrum_title']);

			$crumb['document_alias'] = array_reverse($crumb['document_alias']);

			$crumb['Id'] = array_reverse($crumb['Id']);

			for ($n = 0; $n < $length; $n++)
			{
				if ($crumb['Id'][$n] != 1)
				{
					$number = $n;

					if ($bread_show_main)
						$number = $number + 1;

					$url = rewrite_link('index.php?id=' . $crumb['Id'][$n] . '&amp;doc=' .  $crumb['document_alias'][$n]);

					$search = ['[name]', '[link]', '[count]'];
					$replace = [$crumb['document_breadcrum_title'][$n], $bread_show_host ? HOST . '/' . ltrim($url, '/') : $url, $number];

					$link = str_replace($search, $replace, $bread_link_template);

					$bread_crumb .= sprintf($bread_link_box, $link);

					if (get_settings('bread_link_box_last') == 1)
					{
						if ($bread_sepparator_use)
							$bread_crumb .= $bread_sepparator;
					}
					else
						{
							if ($n != $length - 1)
								if ($bread_sepparator_use)
									$bread_crumb .= $bread_sepparator;
						}

					unset($search, $replace, $link, $row_doc);
				}
			}
		}

		// Последний элемент
		if ((isset($AVE_Core->curentdoc->bread_link_box_last) && $AVE_Core->curentdoc->bread_link_box_last == 0))
			$bread_crumb .= '';
		else if (get_settings('bread_link_box_last') == 1 || (isset($AVE_Core->curentdoc->bread_link_box_last) && $AVE_Core->curentdoc->bread_link_box_last == 1))
			$bread_crumb .= str_replace('[count]', $number+1, sprintf($bread_self_box, $current->document_breadcrum_title));

		if (! $noprint)
			$crumbs[$curent_document] = sprintf($bread_box, $bread_crumb);
		else
			$crumbs[$curent_document] = '';

		unset($bread_crumb);

		return $crumbs[$curent_document];
	}
?>