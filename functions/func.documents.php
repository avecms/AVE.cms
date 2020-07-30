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
	 * Постраничная навигация документа
	 *
	 * @param string $text	текст многострочной части документа
	 * @return string
	 */
	function document_pagination ($text)
	{
		global $AVE_Core;

		// IE8                    <div style="page-break-after: always"><span style="display: none">&nbsp;</span></div>
		// Chrome                 <div style="page-break-after: always; "><span style="DISPLAY:none">&nbsp;</span></div>
		// FF                     <div style="page-break-after: always;"><span style="display: none;">&nbsp;</span></div>
		$pages = preg_split('#<div style="page-break-after:[; ]*always[; ]*"><span style="display:[ ]*none[;]*">&nbsp;</span></div>#i', $text);
		$total_page = @sizeof($pages);

		if ($total_page > 1)
		{
			$text = @$pages[get_current_page('artpage')-1];

			$page_nav = ' <a class="pnav" href="index.php?id=' . $AVE_Core->curentdoc->Id
				. '&amp;doc=' . (empty($AVE_Core->curentdoc->document_alias) ? prepare_url($AVE_Core->curentdoc->document_title) : $AVE_Core->curentdoc->document_alias)
				. '&amp;artpage={s}'
				. ((isset($_REQUEST['apage']) && is_numeric($_REQUEST['apage'])) ? '&amp;apage=' . $_REQUEST['apage'] : '')
				. ((isset($_REQUEST['page']) && is_numeric($_REQUEST['page'])) ? '&amp;page=' . $_REQUEST['page'] : '')
				. '">{t}</a> ';
			$page_nav = get_pagination($total_page, 'artpage', $page_nav, get_settings('navi_box'));

			$text .= rewrite_link($page_nav);
		}

		$pages = '<?php $GLOBALS[\'page_id\'][' . $_REQUEST['id'] . '][\'artpage\']=' . $total_page . '; ?>';

		return $pages . $text;
	}

	/**
	 * Получить идентификатор текущего документа
	 *
	 * @return int идентификатор текущего документа
	 */
	function get_current_document_id()
	{
		$_REQUEST['id'] = (isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))
			? $_REQUEST['id']
			: 1;

		return $_REQUEST['id'];
	}

	/**
	 * Получить идентификатор родительского документа
	 *
	 * @return int идентификатор родительского документа
	 */
	function get_parent_document_id ()
	{
		global $AVE_DB;
		return $AVE_DB->Query("SELECT document_parent FROM " . PREFIX . "_documents WHERE Id = '".get_current_document_id()."' ")->GetCell();
	}

	/**
	 * Функция отдаёт основные параметры дока
	 *
	 * @param int    $doc_id - номер id документа
	 * @param string $key - параметр документа
	 *
	 * @return string | bool | array
	 */
	function get_document ($doc_id , $key ='')
	{
		$doc_id = (int)$doc_id;

		if ($doc_id < 1)
			return [];
/*
		if (defined('USE_STATIC_DATA') && USE_STATIC_DATA)
			static $get_documents_data = [];
		else
			$get_documents_data = [];
*/


		if (! (Registry::stored('documents', $doc_id)))
			$documents_data = getDocument($doc_id);
		else
			$documents_data = Registry::get('documents', $doc_id);

		if (! is_object($documents_data))
			return false;

		$documents_data = object2array($documents_data);

		$documents_data['doc_title'] = $documents_data['document_title'] = htmlspecialchars_decode($documents_data['document_title'], ENT_QUOTES);

		$documents_data['feld'] = [];

		if (isset($key) && $key != '')
			return $documents_data[$key];
		else
			return $documents_data;
	}


	/**
	 * Функция отдаёт основные параметры дока
	 *
	 * @param int    $doc_id - номер id документа
	 *
	 * @return object | bool
	 */
	function getDocument ($doc_id)
	{
		global $AVE_DB;

		if (! defined('USE_STATIC_DATA') || ! USE_STATIC_DATA)
			Registry::clean();

		$doc_id = (int)$doc_id;

		if ($doc_id < 1)
			return false;

		$documents = Registry::get('documents');

		$sql = "
			SELECT
				# DOCUMENT = $doc_id
				doc.*
			FROM
				" . PREFIX . "_documents AS doc
			LEFT JOIN
				" . PREFIX . "_rubrics AS rub
				ON rub.Id = doc.rubric_id
			WHERE
				doc.Id = '" . $doc_id . "'
		";

		$cache_time = (defined('CACHE_DOC_FILE') && CACHE_DOC_FILE)
			? -1
			: 0;

		$data = $AVE_DB->Query($sql, $cache_time, 'dat_' . $doc_id, true, '.data')->FetchRow();

		if (! is_object($data))
			return false;

			$documents[$data->Id] = $data;

		Registry::set('documents', $documents);

		unset ($documents);

		return Registry::get('documents', $doc_id);
	}


	function DocumentBeforeSave ($data)
	{
		return $data;
	}


	function DocumentAfterSave ($data)
	{
		return $data;
	}
?>