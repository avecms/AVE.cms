<?php

/**
 * AVE.cms
 *
 * Класс, предназначенный для управления документами в Панели управления
 *
 * @package AVE.cms
 * @version 3.x
 * @filesource
 * @copyright © 2007-2014 AVE.cms, http://www.ave-cms.ru
 *
 */

class AVE_Document
{

/**
 *	Свойства класса
 */

	/**
	 * Количество документов отображаемых на одной странице
	 *
	 * @public int
	 *
	 */
	public $_limit = 25;
	public $_max_remark_length = 500;

/**
 *	Внутренние методы класса
 */

	/**
	 * Метод, предназначенный для формирование метки времени,
	 * которая будет определять начало периода показа списка Документов.
	 * Т.е. с какого числа/времени начать вывод списка документов.
	 *
	 * @return int	метка времени Unix
	 */
	function _documentListStart()
	{
		$published = explode(".", $_REQUEST['document_published']);

		$timestamp = time(0);

		if (! empty($published[0]))
		{
			$timestamp = mktime(
				0,
				0,
				0,
				$published[1],
				$published[0],
				$published[2]
			);
		}

		return ($timestamp==time(0)
		? ''
		: $timestamp);
	}

	/**
	 * Метод, предназначенный для формирование метки времени,
	 * которая будет определять окончание периода показа списка Документов.
	 * Т.е. по какое число/время ограничить вывод списка документов.
	 *
	 * @return int	метка времени Unix
	 */
	function _documentListEnd()
	{
		$expire = explode(".", $_REQUEST['document_expire']);

		$timestamp = time(0);

		if (! empty($expire[0]))
		{
			$timestamp = mktime(
				23,
				59,
				59,
				$expire[1],
				$expire[0],
				$expire[2]
			);
		}
		return ($timestamp==time(0) ? '' : $timestamp);
	}

	/**
	 * Метод, предназначенный для формирование метки времени начала публикации Документа
	 *
	 * @return int	метка времени Unix
	 */
	function _documentStart($data = 0)
	{
		if (is_numeric($data))
			return $data;

		$data = explode(" ", $data);
		$stamp['day'] = explode(".", $data[0]);
		$stamp['time'] = explode(":", $data[1]);

		if (!empty($stamp))
		{
			$timestamp = mktime(
				$stamp['time'][0],
				$stamp['time'][1],
				0,
				$stamp['day'][1],
				$stamp['day'][0],
				$stamp['day'][2]
			);
		}

		return $timestamp;
	}

	/**
	 * Метод, предназначенный для формирование метки времени окончания публикации Документа
	 *
	 * @return int	метка времени Unix
	 */
	function _documentEnd($data = 0)
	{
		if (is_numeric($data))
			return $data;

		$data = explode(" ", $data);
		$stamp['day'] = explode(".", $data[0]);
		$stamp['time'] = explode(":", $data[1]);

		if (!empty($stamp))
		{
			$timestamp = mktime(
				$stamp['time'][0],
				$stamp['time'][1],
				0,
				$stamp['day'][1],
				$stamp['day'][0],
				$stamp['day'][2]
			);
		}

		return $timestamp;
	}

	/**
	 * Метод, предназначенный для получения типа поля
	 * (изображения, однострочное поле, многострочный текст и т.д.),
	 * а также формирования вспомогательных элементов управления этим полем (например кнопка)
	 *
	 * @param string $field_type	тип поля
	 * @param string $field_value	содержимое поля
	 * @param int    $field_id		идентификатор поля
	 * @param string $dropdown		значения для поля типа "Выпадающий список"
	 * @return string				HTML-код поля Документа
	 */
	function _documentFieldGet($field_type, $field_value, $field_id, $default = '')
	{
		$func = 'get_field_'.$field_type;

		if (! is_callable($func))
			$func='get_field_default';

		$field = $func($field_value, 'edit', $field_id, '', 0, $x, 0, 0, $default);

		return $field;
	}

	function _documentFieldSave($field_type, $field_value, $field_id, $default = '')
	{
		$func = 'get_field_'.$field_type;

		if (! is_callable($func))
			$func='get_field_default';

		$field = $func($field_value, 'save', $field_id, '', 0, $x, 0, 0, $default);

		return $field;
	}

/**
 *	Внутренние методы
 */

	/**
	 *	Управление Документами
	 */

	/**
	 * Метод, предназначенный для получения списка документов в Панели управления
	 *
	 */
	function documentListGet()
	{
		global $AVE_DB, $AVE_Rubric, $AVE_Template;

		$ex_titel = '';
		$nav_titel = '';
		$ex_time = '';
		$nav_time = '';
		$request = '';
		$ex_rub = '';
		$ex_delete = '';
		$nav_rub = '';
		$ex_docstatus = '';
		$navi_docstatus = '';

		// Если в запросе пришел параметр на поиск документа по названию
		if (!empty($_REQUEST['QueryTitel']))
		{
			$request = $_REQUEST['QueryTitel'];
			$chain = explode(' ', $request);  // Получаем список слов, разделяя по пробелу (если их несколько)

			// Циклически обрабатываем слова, формируя условия, которые будут применены в запросе к БД
			foreach ($chain as $search)
			{
				$and = @explode(' +', $search);
				foreach ($and as $and_word)
				{
					if (strpos($and_word, '+') !== false)
					{
						$ex_titel .= " AND ((UPPER(doc.document_title) LIKE '%" . mb_strtoupper(substr($and_word, 1)) . "%')OR(UPPER(doc.document_alias) LIKE '%" . mb_strtoupper(substr($and_word, 1)) . "%'))";
					}
				}

				$and_not = @explode(' -', $search);
				foreach ($and_not as $and_not_word)
				{
					if (strpos($and_not_word, '-') !== false)
					{
						$ex_titel .= " AND (UPPER(doc.document_title) NOT LIKE '%" . mb_strtoupper($and_not_word, 1) . "%')";
					}
				}

				$start = explode(' +', $request);
				if (strpos($start[0], ' -') !== false) $start = explode(' -', $request);
				$start = $start[0];
			}

			$ex_titel = "AND ((UPPER(doc.document_title) LIKE '%" . mb_strtoupper($start) . "%')OR(UPPER(doc.document_alias) LIKE '%" . mb_strtoupper($start) . "%'))" . $ex_titel;
			$nav_titel = '&QueryTitel=' . urlencode($request);
		}

		$sql_join_field = '';
		$sql_where_field = '';
		$field_link = '';

		if ($_REQUEST['field_id'] && (int)$_REQUEST['field_id'] > 0)
		{
			$sql_join_field = "
				LEFT JOIN
					" . PREFIX . "_document_fields AS df1
				ON
					doc.Id = df1.document_id
				LEFT JOIN
					" . PREFIX . "_document_fields_text AS df2
				ON
					df1.document_id = df2.document_id
			";

			if ($_REQUEST['field_request'] == 'eq')
			{
				$sql_where_field = "
				 AND
				 	(df1.rubric_field_id = '" . (int)$_REQUEST['field_id'] . "'
				 	AND
				 	UPPER(CONCAT_WS('', df1.field_value, NULLIF(df2.field_value, '')) = '" . mb_strtoupper($_REQUEST['field_search']) . "'))
				 ";
			}
			else if ($_REQUEST['field_request'] == 'like')
			{
				$sql_where_field = "
					 AND
						(df1.rubric_field_id = '" . (int)$_REQUEST['field_id'] . "'
						AND
						UPPER(CONCAT_WS('', df1.field_value, NULLIF(df2.field_value, '')) LIKE '%" . mb_strtoupper($_REQUEST['field_search']) . "%'))
					";
			}

			$field_link = '&field_id=' . (int)$_REQUEST['field_id'] . '&field_request=' . $_REQUEST['field_request'] . '&field_search=' . $_REQUEST['field_search'];
		}

		// Если в запросе пришел id определенной рубрики
		if (isset($_REQUEST['rubric_id']) && $_REQUEST['rubric_id'] != 'all')
		{
			// Формируем условия, которые будут применены в запросе к БД
			$ex_rub = " AND doc.rubric_id = '" . $_REQUEST['rubric_id'] . "'";

			// формируем условия, которые будут применены в ссылках
			$nav_rub = '&rubric_id=' . (int)$_REQUEST['rubric_id'];

			$sql = $AVE_DB->Query("
				SELECT
					Id,
					rubric_field_type,
					rubric_field_title
				FROM
					" . PREFIX . "_rubric_fields
				WHERE
					rubric_id = '" . $_REQUEST['rubric_id'] ."'
				ORDER BY
					rubric_field_position ASC
			");

			$fields = array();

			while($row = $sql->FetchRow())
			{
				array_push($fields, $row);
			}

			$AVE_Template->assign('fields', $fields);
		}

		$ex_db = '';

		// Поиск с учётом условий настроек рубрик
		if (! isset($_REQUEST['rubric_id']) && empty($_REQUEST['QueryTitel']))
		{
			// Формируем условия, которые будут применены в запросе к БД
			$ex_rub = " AND rub.rubric_docs_active = '1'";

			// формируем условия для бд
			$ex_db = "LEFT JOIN " . PREFIX . "_rubrics as rub on rub.Id = rubric_id";
		}

		$ex_lang = '';

		// Поиск с учётом языка документа
		if (isset($_REQUEST['lang_id']) && $_REQUEST['lang_id'] != '')
		{
			// Формируем условия, которые будут применены в запросе к БД
			$ex_lang = " AND doc.document_lang = '{$_REQUEST["lang_id"]}'";

			$nav_lang = '&lang_id=' . $_REQUEST['lang_id'];
		}

		// Поиск с выводом всех результатов из всех рубрик
		if (@$_REQUEST['rubric_id'] == 'all')
		{
			$nav_rub = '&rubric_id=all';
		}

		// Если в запросе пришел параметр на фильтрацию документов по определенному временному интервалу
		if (@$_REQUEST['document_published'] && @$_REQUEST['document_expire'])
		{
			// Формируем условия, которые будут применены в запросе к БД
			$ex_time = 'AND ((doc.document_published BETWEEN ' . $this->_documentListStart() . ' AND ' . $this->_documentListEnd() . ') OR doc.document_published = 0)';

			// формируем условия, которые будут применены в ссылках
			$nav_time = '&TimeSelect=1'
				. '&document_published=' . $_REQUEST['document_published']
				. '&document_expire='   . $_REQUEST['document_expire'];
		}

		// Если в запросе пришел параметр на фильтрацию документов по статусу
		if (! empty($_REQUEST['status']))
		{
			// Определяем, какой статус запрашивается и формируем условия, которые будут применены в запросе к БД,
			// а также в ссылках, для дальнейшей навигации
			switch ($_REQUEST['status'])
			{
				// С любым статусом
				case '':
				case 'All':
					break;

				// Только опубликованные
				case 'Opened':
					$ex_docstatus = "AND doc.document_status = '1'";
					$navi_docstatus = '&status=Opened';
					break;

				// Только неопубликованные
				case 'Closed':
					$ex_docstatus = "AND doc.document_status = '0'";
					$navi_docstatus = '&status=Closed';
					break;

				// Помеченные на удаление
				case 'Deleted':
					$ex_docstatus = "AND doc.document_deleted = '1'";
					$navi_docstatus = '&status=Deleted';
					break;
			}
		}

		// Определяем группу пользоваеля и id документа, если он присутствует в запросе
		// $ex_delete = (UGROUP != 1) ? "AND doc.document_deleted != '1'" : '' ;
		$w_id = !empty($_REQUEST['doc_id'])
			? " AND doc.Id = '" . $_REQUEST['doc_id'] . "'"
			: '';

		// Выполняем запрос к БД на получение количества документов соответствующих вышеопределенным условиям
		$sql = "
			SELECT COUNT(doc.Id)
			FROM " . PREFIX . "_documents as doc
			" . $ex_db . "
			" . $sql_join_field . "
			WHERE 1
			" . $ex_delete . "
			" . $ex_time . "
			" . $ex_titel . "
			" . $ex_rub . "
			" . $ex_docstatus . "
			" . $ex_lang . "
			" . $w_id . "
			" . $sql_where_field . "
		";

		$num = $AVE_DB->Query($sql)->GetCell();

		// Определяем лимит документов, который будет показан на 1 странице
		$limit = (isset($_REQUEST['Datalimit']) && is_numeric($_REQUEST['Datalimit']) && $_REQUEST['Datalimit'] > 0)
			? $_REQUEST['Datalimit']
			: $limit = $this->_limit;

		$nav_limit = '&Datalimit=' . $limit;

		// Определяем количество страниц, которые будут сформированы на основании количества полученных документов
		$pages = ceil($num / $limit);
		$start = get_current_page() * $limit - $limit;

		$db_sort   = 'ORDER BY doc.Id DESC';
		$navi_sort = '&sort=id_desc';

		// Если в запросе используется параметр сортировки
		if (!empty($_REQUEST['sort']))
		{
			// Определяем, по какому параметру происходит сортировка
			switch ($_REQUEST['sort'])
			{
				// По id документа, по возрастанию
				case 'id' :
					$db_sort   = 'ORDER BY doc.Id ASC';
					$navi_sort = '&sort=id';
					break;

				// По id документа, по убыванию
				case 'id_desc' :
					$db_sort   = 'ORDER BY doc.Id DESC';
					$navi_sort = '&sort=id_desc';
					break;

				// По названию документа, в алфавитном порядке
				case 'title' :
					$db_sort   = 'ORDER BY doc.document_title ASC';
					$navi_sort = '&sort=title';
					break;

				// По названию документа, в обратном алфавитном порядке
				case 'title_desc' :
					$db_sort   = 'ORDER BY doc.document_title DESC';
					$navi_sort = '&sort=title_desc';
					break;

				// По url-адресу, в алфавитном порядке
				case 'alias' :
					$db_sort   = 'ORDER BY doc.document_alias ASC';
					$navi_sort = '&sort=alias';
					break;

				// По url-адресу, в обратном алфавитном порядке
				case 'alias_desc' :
					$db_sort   = 'ORDER BY doc.document_alias DESC';
					$navi_sort = '&sort=alias_desc';
					break;

				// По id рубрики, по возрастанию
				case 'rubric' :
					$db_sort   = 'ORDER BY doc.rubric_id ASC';
					$navi_sort = '&sort=rubric';
					break;

				// По id рубрики, по убыванию
				case 'rubric_desc' :
					$db_sort   = 'ORDER BY doc.rubric_id DESC';
					$navi_sort = '&sort=rubric_desc';
					break;

				// По дате публикации, по возрастанию
				case 'published' :
					$db_sort   = 'ORDER BY doc.document_published ASC';
					$navi_sort = '&sort=published';
					break;

				// По дате публикации, по убыванию
				case 'published_desc' :
					$db_sort   = 'ORDER BY doc.document_published DESC';
					$navi_sort = '&sort=published_desc';
					break;

				// По количеству просмотров, по возрастанию
				case 'view' :
					$db_sort   = 'ORDER BY doc.document_count_view ASC';
					$navi_sort = '&sort=view';
					break;

				// По количеству просмотров, по убыванию
				case 'view_desc' :
					$db_sort   = 'ORDER BY doc.document_count_view DESC';
					$navi_sort = '&sort=view_desc';
					break;

				// По количеству печати документа, по возрастанию
				case 'print' :
					$db_sort   = 'ORDER BY doc.document_count_print ASC';
					$navi_sort = '&sort=print';
					break;

				// По количеству печати документа, по убыванию
				case 'print_desc' :
					$db_sort   = 'ORDER BY doc.document_count_print DESC';
					$navi_sort = '&sort=print_desc';
					break;

				// По автору, по алфавитному возрастанию
				case 'author' :
					$db_sort   = 'ORDER BY doc.document_author_id ASC';
					$navi_sort = '&sort=author';
					break;

				// По автору, по алфавитному убыванию
				case 'author_desc' :
					$db_sort   = 'ORDER BY doc.document_author_id DESC';
					$navi_sort = '&sort=author_desc';
					break;

				// По дате последнего редактирования, по возрастанию
				case 'changed':
					$db_sort   = 'ORDER BY doc.document_changed ASC';
					$navi_sort = '&sort=changed';
					break;

				// По дате последнего редактирования, по убыванию
				case 'changed_desc':
					$db_sort   = 'ORDER BY doc.document_changed DESC';
					$navi_sort = '&sort=changed_desc';
					break;

				// По языку документа, по возрастанию
				case 'lang':
					$db_sort   = 'ORDER BY doc.document_lang ASC';
					$navi_sort = '&sort=lang';
					break;

				// По языку документа, по убыванию
				case 'lang_desc':
					$db_sort   = 'ORDER BY doc.document_lang DESC';
					$navi_sort = '&sort=lang_desc';
					break;

				// По умолчанию, по дате последнего редактирования по убыванию.
				// Последний отредактированный документ, будет первым в списке.
				default :
					$db_sort   = 'ORDER BY doc.document_changed DESC';
					$navi_sort = '&sort=changed_desc';
					break;
			}
		}

		$docs = array();

		// Выполняем запрос к БД на получение уже не количества документов, отвечающих условиям, а уже на
		// получение всех данных, с учетом всех условий, а также типа сортировки и лимита для вывода на
		// одну страницу.
		$sql = "
			SELECT STRAIGHT_JOIN SQL_CALC_FOUND_ROWS
				doc.*,
				rub.rubric_admin_teaser_template
			FROM
				" . PREFIX . "_documents as doc
			LEFT JOIN
				" . PREFIX . "_rubrics AS rub
				ON rub.Id = doc.rubric_id
			" . $sql_join_field . "
			WHERE 1
			" . $ex_rub . "
			" . $ex_delete . "
			" . $ex_time . "
			" . $ex_titel . "
			" . $ex_docstatus . "
			" . $ex_lang . "
			" . $w_id . "
			" . $sql_where_field . "
			" . $db_sort . "
			LIMIT
				" . $start . "," . $limit . "
		";

		$sql = $AVE_DB->Query($sql);

		// Циклически обрабатываем полученные данные с целью приведения некоторых из них к удобочитаемому виду
		while ($row = $sql->FetchRow())
		{
			// Определяем количество комментариев, оставленных для данного документа
			$row->ist_remark = $AVE_DB->Query("
				SELECT
					COUNT(*)
				FROM
					" . PREFIX . "_document_remarks
				WHERE
					document_id = '" . $row->Id . "'
			")->GetCell();

			$this->documentPermissionFetch($row->rubric_id);

			// Получаем название рубрики по ее Id
			$row->RubName         = $AVE_Rubric->rubricNameByIdGet($row->rubric_id)->rubric_title;
			$row->document_author = get_username_by_id($row->document_author_id); // Получаем имя пользователя (Автора)
			$row->cantEdit        = 0;
			$row->canDelete       = 0;
			$row->canEndDel       = 0;
			$row->canOpenClose    = 0;
			$row->rubric_admin_teaser_template = @eval2var('?>'.($row->rubric_admin_teaser_template>''
				? @showrequestelement($row, $row->rubric_admin_teaser_template)
				: '').'<?');

			$row->document_title  = stripslashes(htmlspecialchars_decode($row->document_title));
			$row->document_breadcrum_title  = stripslashes(htmlspecialchars_decode($row->document_breadcrum_title));

			$lang_pack = array();

			if($row->document_lang_group > 0)
			{
				$sql1 = $AVE_DB->Query("
					SELECT SQL_CALC_FOUND_ROWS
						Id,
						rubric_id,
						document_alias,
						document_lang,
						document_status
					FROM
						".PREFIX."_documents
					WHERE
						document_lang_group=" . $row->document_lang_group . "
					OR
						Id = " . $row->document_lang_group
				);

				while ($row1 = $sql1->FetchAssocArray())
				{
					$lang_pack[$row1['document_lang']] = $row1;
				}
			}

			$row->lang_pack = $lang_pack;

			// разрешаем редактирование и удаление
			// если автор имеет право изменять свои документы в рубрике
			// или пользователю разрешено изменять все документы в рубрике
			if (
				($row->document_author_id == @$_SESSION['user_id'] && isset($_SESSION[$row->rubric_id . '_editown']) && @$_SESSION[$row->rubric_id . '_editown'] == 1)
				||
				(isset($_SESSION[$row->rubric_id . '_editall']) && $_SESSION[$row->rubric_id . '_editall'] == 1)
			)
			{
					$row->cantEdit  = 1;
					$row->canDelete = 1;
					$row->canOpenClose = 1;
			}

			// запрещаем редактирование главной страницы и страницу ошибки 404 если требуется одобрение Администратора
			if ( ($row->Id == 1 || $row->Id == PAGE_NOT_FOUND_ID)
				&& isset($_SESSION[$row->rubric_id . '_newnow']) && @$_SESSION[$row->rubric_id . '_newnow'] != 1)
			{
				$row->cantEdit = 0;
			}

			// разрешаем автору блокировать и разблокировать свои документы если не требуется одобрение Администратора
			if ($row->document_author_id == @$_SESSION['user_id']
				&& isset($_SESSION[$row->rubric_id . '_newnow']) && @$_SESSION[$row->rubric_id . '_newnow'] == 1)
			{
				$row->canOpenClose = 1;
			}

			// разрешаем всё, если пользователь принадлежит группе Администраторов или имеет все права на рубрику
			if (UGROUP == 1 || @$_SESSION[$row->rubric_id . '_alles'] == 1)
			{
				$row->cantEdit     = 1;
				$row->canDelete    = 1;
				$row->canEndDel    = 1;
				$row->canOpenClose = 1;
			}
			// Запрещаем удаление Главной страницы и страницы с 404 ошибкой
			if ($row->Id == 1 || $row->Id == PAGE_NOT_FOUND_ID)
			{
				$row->canDelete = 0;
				$row->canEndDel = 0;
			}

			array_push($docs, $row);
		}

		// Передаем полученные данные в шаблон для вывода
		$AVE_Template->assign('docs', $docs);

		$link  = "index.php?do=docs";
		$link .= (isset($_REQUEST['action']) && $_REQUEST['action'] == 'showsimple') ? '&action=showsimple' : '';
		$link .= !empty($_REQUEST['target']) ? '&target=' . urlencode($_REQUEST['target']) : '';
		$link .= !empty($_REQUEST['doc']) ? '&doc=' . urlencode($_REQUEST['doc']) : '';
		$link .= !empty($_REQUEST['document_alias']) ? '&document_alias=' . urlencode($_REQUEST['document_alias']) : '';
		$link .= !empty($_REQUEST['navi_item_target']) ? '&navi_item_target=' . urlencode($_REQUEST['navi_item_target']) : '';
		$link .= $navi_docstatus;
		$link .= $nav_titel;
		$link .= $nav_rub;
		$link .= $nav_lang;
		$link .= $nav_time;
		$link .= $nav_limit;
		$link .= $field_link;
		$link .= (isset($_REQUEST['selurl']) && $_REQUEST['selurl'] == 1) ? '&selurl=1' : '';
		$link .= (isset($_REQUEST['selecturl']) && $_REQUEST['selecturl'] == 1) ? '&selecturl=1' : '';
		$link .= (isset($_REQUEST['function']) && $_REQUEST['function'] == 1) ? '&function=1' : '';
		$link .= (isset($_REQUEST['idonly']) && $_REQUEST['idonly'] == 1) ? '&idonly=1' : '';
		$link .= (isset($_REQUEST['idtitle']) && $_REQUEST['idtitle'] == 1) ? '&idtitle=1' : '';
		$link .= (isset($_REQUEST['pop']) && $_REQUEST['pop'] == 1) ? '&pop=1' : '';
		$link .= (isset($_REQUEST['onlycontent']) && $_REQUEST['onlycontent'] == 1) ? '&onlycontent=1' : '';
		$link .= (isset($_REQUEST['langCode']) && !empty($_REQUEST['langCode'])) ? '&langCode='.$_REQUEST['langCode'] : '';
		$link .= (isset($_REQUEST['CKEditor']) && !empty($_REQUEST['CKEditor'])) ? '&CKEditor='.$_REQUEST['CKEditor'] : '';
		$link .= (isset($_REQUEST['CKEditorFuncNum']) && $_REQUEST['CKEditorFuncNum'] == 1) ? '&CKEditorFuncNum=1' : '';

		$AVE_Template->assign('link', $link);

		// Если количество отобранных документов превышает лимит на одной странице - формируем постраничную навигацию
		if ($num > $limit)
		{
			$page_nav = get_pagination($pages, 'page', ' <a href="' . $link . $navi_sort . '&page={s}'.(empty($_REQUEST['rubric_id'])
				? ''
				: '&rubric_id='.$_REQUEST['rubric_id']).'&cp=' . SESSION . '">{t}</a>');

			$AVE_Template->assign('page_nav', $page_nav);
		}

		$AVE_Template->assign('DEF_DOC_START_YEAR', mktime(0, 0, 0, date("m"), date("d"), date("Y") - 10));
		$AVE_Template->assign('DEF_DOC_END_YEAR', mktime(0, 0, 0, date("m"), date("d"), date("Y") + 10));
	}

	/**
	 * Метод, предназначенный для сохранения статусов документа в БД
	 *
	 */
	function documentEditStatus()
	{
		global $AVE_DB;

		switch(@$_REQUEST['moderation'])
		{
			// статусы
			case "1" :
				foreach (@$_REQUEST['document'] as $id => $status)
				{
					if (is_numeric($id) && is_numeric($status))
					{
						$AVE_DB->Query("UPDATE " . PREFIX . "_documents SET document_status = '1' WHERE Id = '".$id."'	");
					}
				}
			break;

			// статусы
			case "0" :
				foreach (@$_REQUEST['document'] as $id => $status)
				{
					if (is_numeric($id) && is_numeric($status))
					{
						$AVE_DB->Query("UPDATE " . PREFIX . "_documents SET document_status = '0' WHERE Id = '".$id."'	");
					}
				}
			break;

			// в корзину
			case "intrash" :
				foreach (@$_REQUEST['document'] as $id => $status)
				{
					if (is_numeric($id) && is_numeric($status))
					{
						$AVE_DB->Query("UPDATE " . PREFIX . "_documents SET document_deleted = '1' WHERE Id = '".$id."'	");
					}
				}
			break;

			// из корзины
			case "outtrash" :
				foreach (@$_REQUEST['document'] as $id => $status)
				{
					if (is_numeric($id) && is_numeric($status))
					{
						$AVE_DB->Query("UPDATE " . PREFIX . "_documents SET document_deleted = '0' WHERE Id = '".$id."'	");
					}
				}
			break;

			// совсем удалить
			case "trash" :
				foreach (@$_REQUEST['document'] as $id => $status)
				{
					if (is_numeric($id) && is_numeric($status))
					{
						$AVE_DB->Query("DELETE FROM " . PREFIX . "_documents WHERE Id = '".$id."'");
						$AVE_DB->Query("DELETE FROM " . PREFIX . "_document_fields WHERE document_id = '".$id."'");
						$AVE_DB->Query("DELETE FROM " . PREFIX . "_document_fields_text WHERE document_id = '".$id."'");
					}
				}
			break;
		}

		header('Location:index.php?do=docs'.(empty($_REQUEST['rubric_id']) ? '' : '&rubric_id='.$_REQUEST['rubric_id']).'&cp=' . SESSION);
		exit;
	 }

	/**
	* Функция предназначенна для анализа ключевых слов и разненсения их по табличке _document_keyword
	*
	*/
	function generateKeywords($document_id, $keywords=null)
	{
		global $AVE_DB;

		if (! $keywords)
			$keywords = $AVE_DB->Query("SELECT document_meta_keywords FROM " . PREFIX . "_documents WHERE Id = " . intval($document_id) . " LIMIT 1")->GetCell();

		$keywords = explode(',', $keywords);

		$res = $AVE_DB->Query("DELETE FROM " . PREFIX . "_document_keywords where document_id = " . intval($document_id));

		foreach ($keywords as $k => $v)
		{
			if (trim($v) > '')
			{
				$key = trim(mb_substr($v, 0, 254));

				$res= $AVE_DB->Query("INSERT INTO ".PREFIX."_document_keywords
					(
						document_id,
						keyword
					)
					VALUES
					(
						'".intval($document_id)."',
						'".clean_no_print_char($key)."'
					)
				");
			}
		}
	}

	/**
	* Функция предназначенна для анализа ключевых слов и разненсения их по табличке _document_tags
	*
	*/
	function generateTags($document_id,$keywords=null)
	{
		global $AVE_DB;

		if (! $keywords)
			$keywords = $AVE_DB->Query("SELECT document_tags FROM " . PREFIX . "_documents WHERE Id=" . intval($document_id) . " LIMIT 1")->GetCell();

		$keywords = explode(',',$keywords);

		$res = $AVE_DB->Query("DELETE FROM " . PREFIX . "_document_tags where document_id = " . intval($document_id));

		foreach($keywords as $k => $v)
		{
			if (trim($v) > '')
			{
				$key = trim(mb_substr($v, 0, 254));

				$res = $AVE_DB->Query("
					INSERT
					INTO
					" . PREFIX . "_document_tags
					(
						document_id,
						tag
					)
					VALUES
					(
						'" . intval($document_id) . "',
						'" . clean_no_print_char($key) . "'
					)
				");
			}
		}
	}

	/**
	 * Метод, предназначенный для сохранения ревизии документа в БД
	 *
	 */
	static function SaveRevission($document_id)
	{
		global $AVE_DB;

		$sql = $AVE_DB->Query("
			SELECT
				doc.rubric_field_id,
				doc.field_value,
				more.field_value as more
			FROM
				" . PREFIX . "_document_fields AS doc
			LEFT JOIN
				" . PREFIX . "_document_fields_text AS more
			ON
				(more.rubric_field_id = doc.rubric_field_id and more.document_id=doc.document_id)
			WHERE
				doc.document_id = '" . $document_id . "'
		");

		$rows = array();

		while ($row = $sql->FetchAssocArray())
		{
			$row['field_value'] = (string)$row['field_value'] . (string)$row['more'];
			$rows[$row['rubric_field_id']] = pretty_chars(clean_no_print_char($row['field_value']));
		}

		$dtime = $AVE_DB->Query('SELECT document_changed FROM ' . PREFIX . '_documents WHERE Id = ' . $document_id)->GetCell();

		$last_rev = @unserialize($AVE_DB->Query("SELECT doc_data FROM " . PREFIX . "_document_rev WHERE doc_id=" . $document_id . " ORDER BY doc_revision DESC LIMIT 1")->GetCell());
		// это я долго пытался понять почему всегда старая ревизия не равна новой даже если просто нажали лишний раз сохранить
		// оказывается редактор подсовывет alt="" если альта в имге нету и сносит его если он есть там пустой ))))))))))
		// но пусть проверка будет - может редакторы сменятся/апдейтятся а может кто просто хардкором будет код править)))
		$dorev = false;

		foreach ($rows as $k => $v)
		{
			if ($rows[$k] <> $last_rev[$k])
			{
				$dorev = true;
			}
		}

		if ($dorev)
		{
			$AVE_DB->Query("
			INSERT INTO
				" . PREFIX . "_document_rev
			SET
				doc_id			= '" . $document_id . "',
				doc_revision	= '" . $dtime . "',
				doc_data		= '" . addslashes(serialize($rows)) . "',
				user_id			= '" . $_SESSION['user_id'] ."'
			");
		}

		return $rows;
	}

	/**
	 * Метод, предназначенный для востановления ревизии документа
	 *
	 */
	function documentRevissionRestore($document_id, $revision, $rubric_id)
	{

		global $AVE_DB, $AVE_Template;

		$this->documentPermissionFetch($rubric_id);

		if ( (isset($_SESSION[$rubric_id . '_delrev'])  && $_SESSION[$rubric_id . '_delrev'] == 1)
			|| (isset($_SESSION[$rubric_id . '_alles']) && $_SESSION[$rubric_id . '_alles']  == 1)
			|| (defined('UGROUP') && UGROUP == 1) )
		{
			$run = true;
		}

		if ($run === true)
		{
			$res = $AVE_DB->Query("
				SELECT
					doc_data
				FROM
					" . PREFIX . "_document_rev
				WHERE
					doc_id = '" . $document_id . "'
				AND
					doc_revision = '" . $revision . "'
				LIMIT 1
			")->GetCell();

			if (! $res)
				return false;

			$data = @unserialize($res);

			foreach($data as $k => $v)
			{
				if ($k)
				{
					$AVE_DB->Query("
						UPDATE
							" . PREFIX . "_document_fields
						SET
							field_value        = '" . mb_substr($v,0,499) . "',
							field_number_value = '" . preg_replace('/[^\d.]/', '', $v) . "'
						WHERE
							document_id = '" . $document_id . "'
						AND
							rubric_field_id = '" . $k . "'
					");

					if (mb_strlen($v) > 500)
					{
						$AVE_DB->Query("
							UPDATE
								" . PREFIX . "_document_fields_text
							SET
								field_value = '" . mb_substr($v,500) . "'
							WHERE
								document_id = '" . $document_id . "'
							AND
								rubric_field_id = '" . $k . "'
						");
					}
					else
						{
							$AVE_DB->Query("
								DELETE
								FROM
									". PREFIX . "_document_fields_text
								WHERE
									document_id= '" . $document_id . "'
								AND
									rubric_field_id='" . $k . "'
							");
						}
				}
			}

			// Сохраняем системное сообщение в журнал
			reportLog($AVE_Template->get_config_vars('DOC_REVISION_RECOVER')." (Doc: $document_id Rev: $revision)");
			header('Location:index.php?do=docs&action=edit&Id=' . (int)$_REQUEST['doc_id'] . '&rubric_id=' . (int)$_REQUEST['rubric_id'] . '&cp=' . SESSION);
		}
		else
			{
				$AVE_Template->assign('content', $AVE_Template->get_config_vars('DOC_NO_RES_REVISION'));
			}
	}

	/**
	 * Метод, предназначенный для удаления ревизии документа
	 *
	 */
	function documentRevissionDelete($document_id, $revision, $rubric_id){

		global $AVE_DB, $AVE_Template;

		$this->documentPermissionFetch($rubric_id);

		if ( (isset($_SESSION[$rubric_id . '_delrev'])  && $_SESSION[$rubric_id . '_delrev'] == 1)
			|| (isset($_SESSION[$rubric_id . '_alles']) && $_SESSION[$rubric_id . '_alles']  == 1)
			|| (defined('UGROUP') && UGROUP == 1) ){ $run = true; }

		if ($run === true)
		{

			$AVE_DB->Query("
				DELETE
				FROM
					" . PREFIX . "_document_rev
				WHERE
					doc_id = '" . $document_id . "'
				AND
					doc_revision='".$revision."'
			");

			reportLog($AVE_Template->get_config_vars('DOC_REVISION_DELETE')." (Doc: $document_id Rev: $revision)");

			if (! isset($_REQUEST['ajax']))
			{
				header('Location:index.php?do=docs&action=edit&rubric_id=' . $rubric_id . '&Id=' . $document_id . '&cp=' . SESSION);
			}

		}
		else
			{
				$AVE_Template->assign('content', $AVE_Template->get_config_vars('DOC_NO_DEL_REVISION'));
			}
	}


	/**
	 * Метод, предназначенный для сохранения документа в БД
	 *
	 * @param int $rubric_id	идентификатор Рубрики
	 * @param int $document_id	идентификатор Документа
	 * @param array $data			Документ в массиве структура - хитрая
	 * @param bool $update_non_exists_fields	Изменять поля на пустые
	 * значения у не переданных полей или не надо
	 * @param bool $rubric_cod	Использовать код рубрики или не надо
	 * возвращает номер документа если все удачно и false если все плохо
	 */

	function documentSave($rubric_id, $document_id, $data, $update_non_exists_fields = false, $rubric_code = true, $revisions = true, $logs = true, $generate = true)
	{
		global $AVE_DB, $AVE_Template;

		//-- Проверяем входящие данные -- //

		$rubric_id 		= (int)$rubric_id;
		$document_id 	= (int)$document_id;

		// Если отсутсвуют данные, ничего не делаем
		if(! isset($data))
			return false;

		// Если отсутсвуют данные полей, ничего не делаем
		if(! isset($data['feld']))
			return false;

		// Определяем тип опреации
		$oper = 'INSERT';

		// Забираем параметры рубрики
		$_rubric = $AVE_DB->Query("
			SELECT
				rubric_alias,
				rubric_alias_history,
				rubric_code_start,
				rubric_code_end
			FROM
				" . PREFIX . "_rubrics
			WHERE
				Id = '" . $rubric_id . "'
		")->FetchRow();

		// Запускаем триггер перед сохранением
		Hooks::trigger('DocumentBeforeSave', array('rubric_id' => $rubric_id, 'document_id' => $document_id, 'data' => $data));

		// Выполняем стартовый код рубрики
		if ($rubric_code)
		{
			eval ('?>' . $_rubric->rubric_code_start . '<?');
		}

		// Если нет данных для сохранения, перкращаем сохранение и переходим на страницу документов
		if (empty($data))
		{
			header('Location:index.php?do=docs&rubric_id=' . $rubric_id . '&cp=' . SESSION);
			exit;
		}

		// Если есть ID документа, то ставим оператор UPDATE
		if ($document_id > 0)
			$oper = 'UPDATE';

		// Если пользователь имеет права на добавление документов в указанную рубрику, тогда
		if ($oper == 'INSERT' && !( (isset($_SESSION[$rubric_id . '_newnow'])  && $_SESSION[$rubric_id . '_newnow'] == 1)
			|| (isset($_SESSION[$rubric_id . '_new'])   && $_SESSION[$rubric_id . '_new']    == 1)
			|| (isset($_SESSION[$rubric_id . '_alles']) && $_SESSION[$rubric_id . '_alles']  == 1)
			|| (defined('UGROUP') && UGROUP == 1) ))
			return false;

		$data['document_title'] = $_url = empty($data['document_title'])
			? $AVE_Template->get_config_vars('DOC_WITHOUT_TITLE')
			: $data['document_title'];

		// Если оператор равен UPDATE
		if ($oper == 'UPDATE')
		{
			// Выполняем запрос к БД на получение автора документа и id Рубрики
			$row = $AVE_DB->Query("
				SELECT
					rubric_id,
					document_author_id
				FROM
					" . PREFIX . "_documents
				WHERE
					Id = '" . $document_id . "'
			")->FetchRow();

			// Присваиваем значение переменной $rubric_id
			$rubric_id = $row->rubric_id;

			// Запрещаем редактирвание
			$row->cantEdit = 0;

			// Определяем права доступа к документам в данной рубрики
			$this->documentPermissionFetch($row->rubric_id);

			// Разрешаем редактирование
			// если автор имеет право изменять свои документы в рубрике
			// или пользователю разрешено изменять все документы в рубрике
			if ( (isset($_SESSION['user_id']) && $row->document_author_id == $_SESSION['user_id'] &&
					isset($_SESSION[$row->rubric_id . '_editown']) && $_SESSION[$row->rubric_id . '_editown'] == 1)
				|| (isset($_SESSION[$row->rubric_id . '_editall']) && @$_SESSION[$row->rubric_id . '_editall'] == 1) )
			{
				// Разрешаем редактирование
				$row->cantEdit = 1;
			}

			// Запрещаем редактирование главной страницы и страницы ошибки 404 если требуется одобрение Администратора
			if ( ($document_id == 1 || $document_id == PAGE_NOT_FOUND_ID) && @$_SESSION[$row->rubric_id . '_editall'] != 1 )
			{
				// Запрещаем редактирвание
				$row->cantEdit = 0;
			}

			// Разрешаем редактирование, если пользователь принадлежит группе Администраторов или имеет все права на рубрику
			if ( (defined('UGROUP') && UGROUP == 1)
				|| (isset($_SESSION[$row->rubric_id . '_alles']) && $_SESSION[$row->rubric_id . '_alles'] == 1) )
			{
				// Разрешаем редактирование
				$row->cantEdit = 1;
			}

			// выходим если нельзя редактировать
			if(! $row->cantEdit==1 )
				return false;

			//-- Обрабатываем все данные, пришедшие в запросе --//


			// Поиск по документу 1 - Да / 0 - Нет
			$search = (isset($data['document_in_search']) && $data['document_in_search'] == 1)
				? '1'
				: '0';

			// Если пользователь имеет права на добавление/редактирование документов в указанную рубрику, тогда
			if (
				(isset($_SESSION[$row->rubric_id . '_newnow']) && $_SESSION[$row->rubric_id . '_newnow'] == 1)
				||
				(isset($_SESSION[$row->rubric_id . '_editall']) && $_SESSION[$row->rubric_id . '_editall'] == 1)
				||
				(isset($_SESSION[$row->rubric_id . '_alles']) && $_SESSION[$row->rubric_id . '_alles'] == 1)
				||
				(defined('UGROUP') && UGROUP == 1)
			)
			{
				// Статус документа 1 - Опубликован / 0 - Нет
				$document_status = (isset($data['document_status'])
					? $data['document_status']
					: '0');
			}
			else
				{
					// Не опубликован
					$document_status = '0';
				}

			// Если ID документа равно 1 или ID равно Документа 404
			// то стату всегда будет 1
			$document_status = ($document_id == 1 || $document_id == PAGE_NOT_FOUND_ID)
				? '1'
				: $document_status;

			// Формируем/проверяем адрес на уникальность
			if ($document_id != 1)
			{
				$data['document_alias'] = $_url = prepare_url(empty($data['document_alias'])
					? trim($_POST['prefix'] . '/' . $data['document_title'], '/')
					: $data['document_alias']);
			}
			// Если ID документа = 1, то алиас не меняем
			else
				{
					$data['document_alias'] = "/";
				}

			$cnt = 1;

			// Проверяем адрес на уникальность, если не уникален
			// добавляем число к адресу
			while ($AVE_DB->Query("
				SELECT 1
				FROM
					" . PREFIX . "_documents
				WHERE
					Id != '" . $document_id . "'
				AND
					document_alias = '" . $data['document_alias'] . "'
				LIMIT 1
			")->NumRows() == 1)
			{
				$data['document_alias'] = $_url . '-' . $cnt;
				$cnt++;
			}
		}
		// Если оператор INSERT
		else
			{
				// Поиск по документу 1 - Да / 0 - Нет
				$search = (isset($data['document_in_search']) && $data['document_in_search'] == 1)
					? '1'
					: '0';

				// Статус документа 1 - Опубликован / 0 - Нет
				$document_status = ! empty($data['document_status'])
					? (int)$data['document_status']
					: '0';

				// Формируем/проверяем адрес на уникальность
				$data['document_alias'] = $_url = prepare_url(empty($data['document_alias'])
					? trim($data['prefix'] . '/' . $data['document_title'], '/')
					: $data['document_alias']);

				$cnt = 1;

				// Проверяем адрес на уникальность, если не уникален
				// добавляем число к адресу
				while (
					$AVE_DB->Query("
						SELECT 1
						FROM
							" . PREFIX . "_documents
						WHERE
							document_alias = '" . $data['document_alias'] . "'
						LIMIT 1
					")->NumRows()
				)
				{
					$data['document_alias'] = $_url . '-' . $cnt;
					$cnt++;
				}
			}

		// Если оператор UPDATE, забираем перед сохранением старый алиас документа
		if ($oper == 'UPDATE')
		{
			$data['document_alias_old'] = $AVE_DB->Query("
				SELECT
					document_alias
				FROM
					" . PREFIX . "_documents
				WHERE
					Id = '" . $document_id . "'
			")->GetCell();
		}
		else
			{
				// Если оператор INSERT
				// Если новый алиас документа, сопадает с алиасом в истории, просто стираем историю
				$AVE_DB->Query("
					DELETE FROM
						". PREFIX . "_document_alias_history
					WHERE
						document_alias = '" . $data['document_alias'] . "'
				");
			}

		// Дата публикации документа
		// Для документов с ID = 1 и Ошибки 404, дата не пишется
		$data['document_published'] = ($document_id == 1 || $document_id == PAGE_NOT_FOUND_ID)
			? '0'
			: $this->_documentStart($data['document_published']);

		// Дата окончания публикации документа
		// Для документов с ID = 1 и Ошибки 404, дата не пишется
		$data['document_expire'] = ($document_id == 1 || $document_id == PAGE_NOT_FOUND_ID)
			? '0'
			: $this->_documentEnd($data['document_expire']);

		// Дата изменения документа
		$data['document_changed'] = time();

		// Использовать ли историю алиасов
		$data['document_alias_history'] = (empty($data['document_alias_history']))
			? '0'
			: $data['document_alias_history'];

		// Sitemap
		$data['document_sitemap_freq']	= ($data['document_sitemap_freq'] != ''
			? (int)$data['document_sitemap_freq']
			: 3);

		// Sitemap
		$data['document_sitemap_pr'] = ($data['document_sitemap_pr'] != ''
			? $data['document_sitemap_pr']
			: '0.5');

		$data['document_linked_navi_id'] = ($data['document_linked_navi_id'] != ''
			? $data['document_linked_navi_id']
			: 0);

		$fields = array();

		// Получаем структуру документа
		if($oper == 'INSERT')
		{
			$sql = $AVE_DB->Query("
				SELECT
					*
				FROM
					" . PREFIX . "_rubric_fields
				WHERE
					rubric_id = '" . $rubric_id . "'
				ORDER BY
					rubric_field_position ASC
			");
		}
		else
		{
			$sql = $AVE_DB->Query("
				SELECT
					doc.Id AS df_id,
					rub.*,
					rubric_field_default,
					doc.field_value
				FROM
					" . PREFIX . "_rubric_fields AS rub
				LEFT JOIN
					" . PREFIX . "_document_fields AS doc
					ON rubric_field_id = rub.Id
				WHERE
					document_id = '" . $document_id . "'
				ORDER BY
					rubric_field_position ASC
			");
		}

		// Если пришел вызов поля, который связан с модулем
		if (isset($data['field_module']))
		{
			while(list($mod_key, $mod_val) = each($_REQUEST['field_module']))
			{
				require_once(BASE_DIR . '/modules/' . $mod_val . '/document.php');

				$mod_function = (string)$mod_val . '_document_save';

				$fields = $mod_function($mod_key, $mod_val, $sql, $data['feld'][$mod_key], $mod_key, $rubric_id);
			}
		}
		else
		{
			while ($row = $sql->FetchRow())
			{
				array_push($fields, $row);
			}
		}

		unset($sql);

		$where = ($oper == 'UPDATE' ? 'WHERE Id = ' . $document_id : '');
		$author = ($oper != 'UPDATE' ? 'document_author_id = ' . $_SESSION['user_id'] . ',' : '');
		$operator = ($oper == 'UPDATE' ? "UPDATE " . PREFIX . "_documents" : "INSERT INTO " . PREFIX . "_documents");

		$breadcrumb_title = (isset($data['document_breadcrum_title']) && $data['document_breadcrum_title'] != '')
			? $data['document_breadcrum_title']
			: '';

		// Сохраняем все параметры документа
		$sql = "
			$operator
			SET
				rubric_id					= '" . $rubric_id . "',
				rubric_tmpl_id				= '" . (int)$data['rubric_tmpl_id'] . "',
				document_parent				= '" . (int)$data['document_parent'] . "',
				document_title				= '" . htmlspecialchars(clean_no_print_char($data['document_title']), ENT_QUOTES) . "',
				document_breadcrum_title	= '" . htmlspecialchars(clean_no_print_char($breadcrumb_title), ENT_QUOTES) . "',
				document_alias				= '" . $data['document_alias'] . "',
				document_alias_history		= '" . $data['document_alias_log'] . "',
				document_published			= '" . $data["document_published"] . "',
				document_expire				= '" . $data["document_expire"] . "',
				document_changed			= '" . $data["document_changed"] . "',
				$author
				document_in_search			= '" . $search . "',
				document_meta_keywords		= '" . htmlspecialchars(clean_no_print_char($data['document_meta_keywords']), ENT_QUOTES) . "',
				document_meta_description	= '" . htmlspecialchars(clean_no_print_char($data['document_meta_description']), ENT_QUOTES) . "',
				document_meta_robots		= '" . $data['document_meta_robots'] . "',
				document_sitemap_freq		= '" . $data['document_sitemap_freq'] . "',
				document_sitemap_pr			= '" . $data['document_sitemap_pr'] . "',
				document_status				= '" . $data['document_status'] . "',
				document_linked_navi_id		= '" . (int)$data['document_linked_navi_id'] . "',
				document_tags				= '" . addslashes(htmlspecialchars(clean_no_print_char($data['document_tags']))). "',
				document_lang				= '" . (empty($data['document_lang']) ? DEFAULT_LANGUAGE : $data['document_lang']). "',
				document_lang_group			= '" . (empty($data['document_lang_group']) ? '0' : (int)$data['document_lang_group']). "',
				document_property			= '" . (empty($data['document_property']) ? '' : $data['document_property']). "'
				$where
			";

		$AVE_DB->Query($sql);

		// Получаем id добавленной записи
		$iid = $AVE_DB->InsertId();

		// Сохраняем ревизию документа
		if ($oper == 'UPDATE' && $revisions)
			$this->SaveRevission($document_id);

		// Переназначаем $document_id
		$document_id = $_REQUEST['Id'] = ($oper == "INSERT" ? $iid : $document_id);

		//Проверяем алиас на изменения (Старый/Новый)
		if (
			$oper == 'UPDATE'
			AND $data['document_alias'] != $data['document_alias_old']
			AND (
				($data['document_alias_history'] == '0' AND $_rubric->rubric_alias_history == '1')
				OR
				($data['document_alias_history'] == '1')
			)
			AND
			($AVE_DB->Query("
				SELECT 1
				FROM
					" . PREFIX . "_document_alias_history
				WHERE
					document_alias = '" . $data['document_alias_old'] . "'
				LIMIT 1
			")->NumRows() == 0)
		)
		{
			$AVE_DB->Query("
				INSERT INTO
					" . PREFIX . "_document_alias_history
				SET
					document_id					= '" . $document_id . "',
					document_alias				= '" . $data['document_alias_old'] . "',
					document_alias_author		= '" . $_SESSION['user_id'] . "',
					document_alias_changed		= '" . time() . "'
			");
		}

		// Сохраняем системное сообщение в журнал
		if ($logs)
			reportLog(($oper=='INSERT'
				? $AVE_Template->get_config_vars('DOC_SAVE_ADD')
				: $AVE_Template->get_config_vars('DOC_SAVE_EDIT'))
		. $AVE_Template->get_config_vars('DOC_SAVE_LOG_DOC') .' (' . $data['document_title'] . ' Id: ' . $document_id . ')');

		// Циклически обрабатываем поля документа
		foreach ($fields as $k => $v)
		{
			$fld_id = $v->Id;
			$slash = false;

			// Если в данных нет поля и мы редактируем документ - изменять ли это поле на пустое значение
			if ($oper == 'UPDATE' && (! (isset($data['feld'][$fld_id]))) && ! $update_non_exists_fields)
				continue;

			$fld_val = (isset($data['feld'][$fld_id])
				? $data['feld'][$fld_id]
				: $v->rubric_field_default);

			if (! $AVE_DB->Query("
					SELECT 1
					FROM
						" . PREFIX . "_rubric_fields
					WHERE
						Id = '" . $fld_id . "'
					AND
						rubric_id = '" . $rubric_id . "'
					LIMIT 1
				")->NumRows())
			{
				continue;
			}

			/* ------------------------------------------------------------------- */

			if(! is_array($fld_val))
			{
				// Если запрещено использование php кода, тогда обнуляем данные поля
				if (! check_permission('document_php'))
				{
					if (is_php_code($fld_val))
						$fld_val = '';
				}

				// Убираем из текста непечатабельные символы
				$fld_val = clean_no_print_char($fld_val);
				$fld_val = pretty_chars($fld_val);
			}

			$field_rubric = $AVE_DB->Query("
				SELECT
					*
				FROM
					" . PREFIX . "_rubric_fields
				WHERE
					Id = '" . $fld_id . "'
			")->FetchRow();

			// Отправляем полученные данные в функцию поля, в раздел "Save"
			// для преобразования перед сохранением
			$func = 'get_field_' . $field_rubric->rubric_field_type;

			if (is_callable($func))
			{
				$fld_val = $func($fld_val, 'save', $fld_id, '', 0, $x, 0, 0, 0);
			}

			//-- Собираем запрос к БД на добавление нового поля с его содержимым --//

			$where = ($oper == 'UPDATE'
				? "WHERE document_id = '" . $document_id . "' AND rubric_field_id = '" . $fld_id . "'"
				: '');

			$operator = ($oper == 'UPDATE'
				? "UPDATE " . PREFIX . "_document_fields"
				: "INSERT INTO " . PREFIX . "_document_fields");

			$insert = ($oper == 'UPDATE'
				? ''
				: "rubric_field_id = '" . $fld_id . "', document_id = '" . $document_id . "',");

			$fval = (is_array($fld_val)
				? serialize($fld_val)
				: ($fld_val));

			$substr = 500;

			if (mb_substr($fval, 501, 1))
				$substr = 499;

			// Сохраняем первые 500 символов
			$f_val_500 = mb_substr($fval, 0, $substr);

			// Проверяем чтобы не было в конце слеша - \
			if (mb_substr($f_val_500, 498, 1) == '\\')
				$slash = true;

			if ($slash)
				$f_val_500 = rtrim($f_val_500, '\\');

			$sql = "
				$operator
				SET
					$insert
					field_value				= '" . $f_val_500 . "',
					field_number_value		= '" . (($field_rubric->rubric_field_numeric)
													? preg_replace('/[^\d.]/', '', $fld_val)
													: 0) . "',
					document_in_search		= '" . $search . "'
				$where
			";

			$AVE_DB->Query($sql);

			unset ($sql, $f_val_500, $fld_val);

			// Если символов больше 500, то сохраняем их в другой таблице
			if (mb_strlen($fval) > $substr)
			{
				// Проверяем есть ли запись о поле в БД
				$check_field = $AVE_DB->Query("
					SELECT
						Id
					FROM
						" .PREFIX . "_document_fields_text
					WHERE
						document_id = '" . $document_id . "'
					AND
						rubric_field_id='" . $fld_id . "'
				")->GetCell();

				$operator = ($check_field > 0
					? "UPDATE " . PREFIX . "_document_fields_text"
					: "INSERT INTO " . PREFIX . "_document_fields_text"
				);

				$where = ($check_field > 0
					? "WHERE document_id = '" . $document_id . "' AND rubric_field_id = '" . $fld_id . "'"
					: '');

				$insert = ($check_field > 0
					? ''
					: "rubric_field_id = '" . $fld_id . "', document_id = '" . $document_id . "',");

				$f_val_unlim = mb_substr($fval, $substr);

				if ($slash)
					$f_val_unlim = '\\' . $f_val_unlim;

				$sql = "
					$operator
					SET
						$insert
						field_value			= '" . $f_val_unlim . "'
					$where
				";

				$AVE_DB->Query($sql);

				unset($sql, $f_val_unlim);
			}
			// Если символов меньше 500, то чистим поле в другой таблице
			else
				{
					$AVE_DB->Query("
						DELETE
						FROM
							". PREFIX . "_document_fields_text
						WHERE
							document_id = '" . $document_id . "'
						AND
							rubric_field_id='" . $fld_id . "'
					");
				}
		}

		// Очищаем кэш шаблона
		$AVE_DB->Query("
			DELETE
			FROM
				" . PREFIX . "_rubric_template_cache
			WHERE
				doc_id = '" . $document_id . "'
		");

		// Выполняем код рубрики, после сохранения
		if ($rubric_code)
		{
			eval ('?>' . $_rubric->rubric_code_end . '<?');
		}

		// Запускаем триггер после сохранения
		Hooks::trigger('DocumentAfterSave', array('rubric_id' => $rubric_id, 'document_id' => $document_id, 'data' => $data, 'field_module' => $data['field_module']));

		// Чистим кеш
		$AVE_DB->clearcache('rub_' . $rubric_id);
		$AVE_DB->clearcache('doc_' . $document_id);
		$AVE_DB->clearcompile('doc_' . $document_id);
		$AVE_DB->clearcacherequest('doc_' . $document_id);

		unset($_rubric);

		// Дополнительные обработки
		if ($generate)
		{
			$this->generateKeywords($document_id);
			$this->generateTags($document_id);
		}

		return $document_id;
	}

	/**
	 * Метод, предназначенный для добавления нового документа в БД
	 *
	 * @param int $rubric_id	идентификатор Рубрики
	 */
	function documentNew($rubric_id)
	{
		global $AVE_DB, $AVE_Rubric, $AVE_Template;

		$this->documentPermissionFetch($rubric_id);

		// Если пользователь имеет права на добавление документов в указанную рубрику, тогда
		if ( (isset($_SESSION[$rubric_id . '_newnow'])  && $_SESSION[$rubric_id . '_newnow'] == 1)
			|| (isset($_SESSION[$rubric_id . '_new'])   && $_SESSION[$rubric_id . '_new']    == 1)
			|| (isset($_SESSION[$rubric_id . '_alles']) && $_SESSION[$rubric_id . '_alles']  == 1)
			|| (defined('UGROUP') && UGROUP == 1) )
		{
			// Поля
			$sql = $AVE_DB->Query("
				SELECT
					a.*,
					b.Id AS group_id,
					b.group_title,
					b.group_description,
					b.group_position
				FROM
					" . PREFIX . "_rubric_fields AS a
				LEFT JOIN
					" . PREFIX . "_rubric_fields_group AS b
					ON a.rubric_field_group = b.Id
				WHERE
					a.rubric_id = '" . $rubric_id . "'
				ORDER BY
					b.group_position ASC,
					a.rubric_field_position ASC
			");

			$fields_list = array();

			while ($row = $sql->FetchRow())
			{
				$group_id = ($row->rubric_field_group) ? $row->rubric_field_group : 0;

				$fields_list[$group_id]['group_id'] = $row->group_id;
				$fields_list[$group_id]['group_position'] = ($row->group_position) ? $row->group_position : 100;
				$fields_list[$group_id]['group_title'] = $row->group_title;
				$fields_list[$group_id]['group_description'] = $row->group_description;
				$fields_list[$group_id]['fields'][$row->Id]['Id'] = $row->Id;
				$fields_list[$group_id]['fields'][$row->Id]['rubric_id'] = $row->rubric_id;
				$fields_list[$group_id]['fields'][$row->Id]['rubric_field_title'] = $row->rubric_field_title;
				$fields_list[$group_id]['fields'][$row->Id]['rubric_field_description'] = $row->rubric_field_description;
				$fields_list[$group_id]['fields'][$row->Id]['rubric_field_default'] = $row->rubric_field_default;
				$fields_list[$group_id]['fields'][$row->Id]['result'] = $this->_documentFieldGet($row->rubric_field_type, $row->rubric_field_default, $row->Id, $row->rubric_field_default);
			}

			$fields_list = msort($fields_list, 'group_position');

			$AVE_Template->assign('groups_count', count($fields_list));
			$AVE_Template->assign('fields_list', $fields_list);

			// Группы полей
			$fields_groups = array();

			$sql = $AVE_DB->Query("
				SELECT *
				FROM
					" . PREFIX . "_rubric_fields_group
				WHERE
					rubric_id = '" . $rubric_id . "'
				ORDER BY
					group_position ASC
			");

			while ($row = $sql->FetchRow())
			{
				array_push($fields_groups, $row);
			}

			$AVE_Template->assign('fields_groups', $fields_groups);

			// Определяем вид действия, переданный в параметре sub
			switch ($_REQUEST['sub'])
			{
				case 'save': // Сохранение документа в БД

					$public_start  = $this->_documentStart(); // Дата/время начала публикации документа
					$public_end   = $this->_documentEnd();   // Дата/время окончания публикации документа

					$innavi = check_permission_acp('navigation_new') ? '&innavi=1' : '';

					// Определяем статус документа
					$document_status = !empty($_REQUEST['document_status']) ? (int)$_REQUEST['document_status'] : '0';

					// Если статус документа не определен
					if (empty($document_status) && $_SESSION['user_group'] != 1)
					{
						$innavi = '';
						@reset($_POST);
						$newtext = "\n\n";

						// Формируем текст сообщения, состоящий из данных,
						// которые пользователь ввел в поля документа
						foreach ($_POST['feld'] as $val)
						{
							if (!empty($val))
							{
								$newtext .= $val;
								$newtext .= "\n---------------------\n";
							}
						}

						$text = strip_tags($newtext);

						// Получаем e-mail адрес из общих настроек системы
						$system_mail = get_settings('mail_from');
						$system_mail_name = get_settings('mail_from_name');

						// Отправляем администартору уведомление, о том что необходимо проверить документ
						$body_to_admin = $AVE_Template->get_config_vars('DOC_MAIL_BODY_CHECK');
						$body_to_admin = str_replace('%N%', "\n", $body_to_admin);
						$body_to_admin = str_replace('%TITLE%', stripslashes($_POST['document_title']), $body_to_admin);
						$body_to_admin = str_replace('%USER%', "'" . $_SESSION['user_name'] . "'", $body_to_admin);
						send_mail(
							$system_mail,
							$body_to_admin . $text,
							$AVE_Template->get_config_vars('DOC_MAIL_SUBJECT_CHECK'),
							$system_mail,
							$system_mail_name,
							'text'
						);

						// Отправляем уведомление автору, о том что документ находится на проверке
						$body_to_author = str_replace('%N%', "\n", $AVE_Template->get_config_vars('DOC_MAIL_BODY_USER'));
						$body_to_author = str_replace('%TITLE%', stripslashes($_POST['document_title']), $body_to_author);
						$body_to_author = str_replace('%USER%', "'" . $_SESSION['user_name'] . "'", $body_to_author);
						send_mail(
							$_SESSION['user_email'],
							$body_to_author,
							$AVE_Template->get_config_vars('DOC_MAIL_SUBJECT_USER'),
							$system_mail,
							$system_mail_name,
							'text'
						);
					}

					if (! ((isset($_SESSION[$rubric_id . '_newnow']) && $_SESSION[$rubric_id . '_newnow'] == 1)
						|| (isset($_SESSION[$rubric_id . '_alles']) && $_SESSION[$rubric_id . '_alles'] == 1)
						|| (defined('UGROUP') && UGROUP == 1)) )
					{
						$document_status = 0;
					}

					$_POST['document_status'] = $document_status;

					$iid = $this->documentSave($rubric_id, null, $_POST, true);

					if ($_REQUEST['doc_after'])
						header('Location:index.php?do=docs&action=after&document_id=' . $iid . '&rubric_id=' . $rubric_id . '&cp=' . SESSION . $innavi);
					else
						header('Location:index.php?do=docs&action=edit&Id=' . $iid . '&rubric_id=' . $rubric_id . '&cp=' . SESSION);
					exit;

				case '': // Действия по умолчанию, если не задано
					$document = new stdClass();

					// Получаем список прав доступа на добавление документов в определенную рубрику
					$this->documentPermissionFetch($rubric_id);

					// Определяем флаг, который будет активировать или запрещать смену статуса у документа
					if ( (defined('UGROUP') && UGROUP == 1)
						|| (isset($_SESSION[$rubric_id . '_alles']) && $_SESSION[$rubric_id . '_alles'] == 1)
						|| (isset($_SESSION[$rubric_id . '_newnow']) && $_SESSION[$rubric_id . '_newnow'] == 1) )
					{
						$document->dontChangeStatus = 0;
					}
					else
					{
						$document->dontChangeStatus = 1;
					}

					$maxId = $AVE_DB->Query("
						SELECT
							MAX(Id)
						FROM
							" . PREFIX . "_documents
					")->GetCell();

					// получения списка документов из связанной рубрики
					$linked_id = $AVE_DB->Query("
						SELECT
							rubric_linked_rubric
						FROM
							" . PREFIX . "_rubrics
						WHERE
							Id = '".$rubric_id."'
					")->GetCell();

					$linked_id = @unserialize($linked_id);

					$document_alias = array();

					if ($linked_id)
					{
						foreach ($linked_id as $linked_id)
						{
							$sql = $AVE_DB->Query("
								SELECT
									doc.document_alias,
									doc.document_title,
									doc.document_breadcrum_title,
									doc.Id,
									rub.rubric_title
								FROM
									" . PREFIX . "_documents as doc
								JOIN
									" . PREFIX . "_rubrics as rub
									ON rub.Id = doc.rubric_id
								WHERE
									doc.rubric_id = '".$linked_id."'
							");

							while ($row = $sql->FetchRow())
							{
								$document_alias[$row->rubric_title][] = array(
									'document_alias' => $row->document_alias,
									'document_title' => $row->document_title,
									'document_breadcrum_title' => $row->document_breadcrum_title,
									'Id' => $row->Id
								);
							}
						}
					}

					// получения списка документов из связанной рубрики
					$AVE_Template->assign('document_alias', $document_alias);

					$lang_pack = array();

					if (! empty($_REQUEST['lang_pack']))
					{
						$sql1 = $AVE_DB->Query("
							SELECT
								Id,
								rubric_id,
								document_alias,
								document_lang,
								document_status
							FROM
								".PREFIX."_documents
							WHERE
								document_lang_group=".intval($_REQUEST['lang_pack'])." OR Id=".intval($_REQUEST['lang_pack']));

						while ($row1 = $sql1->FetchAssocArray())
						{
							$lang_pack[$row1['document_lang']] = $row1;

							if ($row1['Id'] == intval($_REQUEST['lang_pack']))
								$document->document_alias=$_REQUEST['lang'].'/'.trim(ltrim($row1['document_alias'], $row1['document_lang']), '/');
						}
					}

					// Формируем данные и передаем в шаблон
					$document->lang_pack=$lang_pack;
					$document->fields = $fields_list;
					$document->rubric_title = $AVE_Rubric->rubricNameByIdGet($rubric_id)->rubric_title;
					$document->rubric_url_prefix = strftime(str_ireplace("%id", $maxId+1, $AVE_Rubric->rubricNameByIdGet($rubric_id)->rubric_alias));
					$document->formaction = 'index.php?do=docs&action=new&sub=save&rubric_id=' . $rubric_id . ((isset($_REQUEST['pop']) && $_REQUEST['pop']==1) ? 'pop=1' : '') . '&cp=' . SESSION;
					$document->count_groups = count($fields_list);
					$document->document_published = time();
					$document->document_expire = mktime(date("H"), date("i"), 0, date("m"), date("d"), date("Y") + 10);

					$rubric_tmpls = array();

					$sql = $AVE_DB->Query("
						SELECT
							id,
							title
						FROM
							" . PREFIX . "_rubric_templates
						WHERE
							rubric_id = '" . $rubric_id . "'
					");

					while ($row = $sql->FetchRow())
					{
						array_push($rubric_tmpls, $row);
					}

					// Доступные шаблоны рубрики
					$AVE_Template->assign('rubric_tmpls', $rubric_tmpls);

					$AVE_Template->assign('document', $document);
					$AVE_Template->assign('content', $AVE_Template->fetch('documents/form.tpl'));
					break;
			}
		}
		else
		{	// Пользователь не имеет прав на создание документа, формируем сообщение с ошибкой
			$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('DOC_NO_PERMISSION_RUB'));
			$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
		}
	}

	/**
	 * Метод, предназначенный для редактирования документа
	 *
	 * @param int $document_id	идентификатор Документа
	 */
	function documentEdit($document_id)
	{
		global $AVE_DB, $AVE_Rubric, $AVE_Template;

		// Определяем действие, выбранное пользователем
		switch ($_REQUEST['sub'])
		{
			// Если была нажата кнопка Сохранить изменения
			case 'save':

				$row = $AVE_DB->Query("
					SELECT
						rubric_id,
						document_author_id
					FROM
						" . PREFIX . "_documents
					WHERE
						Id = '" . $document_id . "'
				")->FetchRow();

				$this->documentSave($row->rubric_id, $document_id, $_POST, true);

				if (isset($_REQUEST['closeafter']) && $_REQUEST['closeafter'] == 1)
				{
					if (! isAjax())
					{
						echo "<script>window.opener.location.reload(); window.close();</script>";
					}
					else
						{
							$message = $AVE_Template->get_config_vars('DOCUMENT_SAVED');
							$header = $AVE_Template->get_config_vars('DOC_REV_SUCCESS');
							$theme = 'accept';
							echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
							exit;
						}
				}
				else
				{

					if (! isAjax())
					{
						header('Location:index.php?do=docs&action=after&document_id=' . $document_id . '&rubric_id=' . $row->rubric_id . '&cp=' . SESSION);
					}
						else
						{
							$message = $AVE_Template->get_config_vars('DOCUMENT_SAVED');
							$header = $AVE_Template->get_config_vars('DOC_REV_SUCCESS');
							$theme = 'accept';
							echo json_encode(array('message' => $message, 'header' => $header, 'theme' => $theme));
							exit;
						}
				}
				exit;

			// Если пользователь не выполнял никаких действий, а просто открыл документ для редактирования
			case '':
				// Выполняем запрос к БД на получение данных о документе
				$document = $AVE_DB->Query("
					SELECT *
					FROM
						" . PREFIX . "_documents
					WHERE
						Id = '" . $document_id . "'
				")->FetchRow();

				$lang_pack = array();

				$sql = $AVE_DB->Query("
					SELECT
						Id,
						document_alias,
						document_lang,
						document_lang_group,
						document_status
					FROM
						".PREFIX."_documents
					WHERE
					". ($document->document_lang_group > 0
						? "document_lang_group = ".$document->document_lang_group." OR Id = ".$document->document_lang_group." OR "
						: "")."document_lang_group = ".$document_id." OR Id = ".$document_id
				);

				while ($row = $sql->FetchAssocArray())
				{
					$lang_pack[$row['document_lang']] = $row;
				}

				$document->lang_pack = $lang_pack;

				$show = true;

				// Проверяем права доступа к документу
				$this->documentPermissionFetch($document->rubric_id);

				// запрещаем доступ,
				// если автору документа не разрешено изменять свои документы в рубрике
				// или пользователю не разрешено изменять все документы в рубрике
				if (!( (isset($_SESSION['user_id']) && $document->document_author_id == $_SESSION['user_id']
					&& isset($_SESSION[$document->rubric_id . '_editown']) && $_SESSION[$document->rubric_id . '_editown'] == 1)
					|| (isset($_SESSION[$document->rubric_id . '_editall']) && $_SESSION[$document->rubric_id . '_editall'] == 1)))
				{
					$show = false;
				}

				// запрещаем доступ к главной странице и странице ошибки 404, если требуется одобрение Администратора
				if ( ($document_id == 1 || $document_id == PAGE_NOT_FOUND_ID) &&
					!(isset($_SESSION[$document->rubric_id . '_editall']) && $_SESSION[$document->rubric_id . '_editall'] == 1) )
				{
					$show = false;
				}

				// разрешаем доступ, если пользователь принадлежит группе Администраторов или имеет все права на рубрику
				if ( (defined('UGROUP') && UGROUP == 1)
					|| (isset($_SESSION[$document->rubric_id . '_alles']) && $_SESSION[$document->rubric_id . '_alles'] == 1) )
				{
					$show = true;
				}

				if ($show)
				{
					$fields = array();

					if (
						(defined('UGROUP') && UGROUP == 1)
						|| (isset($_SESSION[$document->rubric_id . '_newnow']) && $_SESSION[$document->rubric_id . '_newnow'] == 1)
						|| (isset($_SESSION[$document->rubric_id . '_editall']) && $_SESSION[$document->rubric_id . '_editall'] == 1)
						|| (isset($_SESSION[$document->rubric_id . '_editown']) && $_SESSION[$document->rubric_id . '_editown'] == 1)
						|| (isset($_SESSION[$document->rubric_id . '_alles']) && $_SESSION[$document->rubric_id . '_alles'] == 1)
					)
					{
						$document->dontChangeStatus = 0;
					}
					else
					{
						$document->dontChangeStatus = 1;
					}

					// Выполняем запрос к БД на получение списка полей, которые относятся к данному документу
					$sql = $AVE_DB->Query("
						SELECT *
						FROM
							" . PREFIX . "_rubric_fields
						WHERE
							rubric_id = '" . $document->rubric_id . "'
						ORDER BY
							rubric_field_position ASC
					");

					// не парсим поля, просто создаём контрольный массив полей
					while ($row = $sql->FetchRow())
					{
						$fields[$row->Id] = $row;
					}

					$doc_fields = array();

					// Выполняем запрос к БД и получаем все данные для полей документа
					$sql = $AVE_DB->Query("
						SELECT
							doc.Id AS df_id,
							groups.*,
							groups.Id AS group_id,
							rub.*,
							rubric_field_default,
							doc.field_value,
							field_more.field_value as field_value_more
						FROM
							" . PREFIX . "_rubric_fields AS rub
						LEFT JOIN
							" . PREFIX . "_rubric_fields_group AS groups
							ON rub.rubric_field_group = groups.Id
						LEFT JOIN
							" . PREFIX . "_document_fields AS doc
							ON (rubric_field_id = rub.Id)
						LEFT JOIN
							" . PREFIX . "_document_fields_text AS field_more
							ON (field_more.rubric_field_id = doc.rubric_field_id AND doc.document_id=field_more.document_id)
						WHERE
							doc.document_id = '" . $document_id . "'
						ORDER BY
							groups.group_position ASC, rub.rubric_field_position ASC
					");

					// записываем массив с полями документа
					while ($row = $sql->FetchRow())
					{
						$row->field_value = (string)$row->field_value . (string)$row->field_value_more;
						$row->field = $this->_documentFieldGet($row->rubric_field_type, $row->field_value, $row->Id, $row->rubric_field_default);

						$doc_fields[$row->Id] = $row;
					}

					// для каждого поля из контрольного массива...
					foreach ($fields as $field_id => $row)
					{
						// если в документе поле есть, то записываем его
						if ($doc_fields[$field_id])
						{
							$fields[$field_id] = $doc_fields[$field_id];
						}

						// если нет, парсим чистое поле и добавляем в бд
						else
						{
							$row->field = $this->_documentFieldGet($row->rubric_field_type, $row->rubric_field_default, $row->Id, $row->rubric_field_default);

							$fields[$field_id] = $row;

							$AVE_DB->Query("
								INSERT INTO " . PREFIX . "_document_fields
								SET
									rubric_field_id		= '" . $field_id . "',
									document_id				= '" . $document->Id . "'
							");
						}
					}

					foreach ($fields as $field)
					{
						$group_id = ($field->rubric_field_group) ? $field->rubric_field_group : 0;
						$fields_list[$group_id]['group_id'] = $field->group_id;
						$fields_list[$group_id]['group_position'] = ($field->group_position) ? $field->group_position : 100;
						$fields_list[$group_id]['group_title'] = $field->group_title;
						$fields_list[$group_id]['fields'][$field->Id]['Id'] = $field->Id;
						$fields_list[$group_id]['fields'][$field->Id]['rubric_id'] = $row->rubric_id;
						$fields_list[$group_id]['fields'][$field->Id]['rubric_field_title'] = $field->rubric_field_title;
						$fields_list[$group_id]['fields'][$field->Id]['rubric_field_description'] = $field->rubric_field_description;
						$fields_list[$group_id]['fields'][$field->Id]['result'] = $field->field;
					}

					$fields_list = msort($fields_list, 'group_position');

					unset($doc_fields);
					unset($fields);

					// Заглушка на время публикации
					$document->document_published = ($document_id == 1 || $document_id == PAGE_NOT_FOUND_ID) ? '0' : $document->document_published = $document->document_published == 0 ? time() : $document->document_published;
					$document->document_expire = ($document_id == 1 || $document_id == PAGE_NOT_FOUND_ID) ? '0' : $document->document_expire = $document->document_expire == 0 ? mktime(date("H"), date("i"), 0, date("m"), date("d"), date("Y") + 10) : $document->document_expire;

					// Формируем ряд переменных и передаем их в шаблон для вывода
					$document->fields = $fields_list;
					$document->count_groups = count($fields_list);
					$document->document_title = htmlspecialchars_decode(stripslashes(html_entity_decode($document->document_title)));
					$document->document_meta_keywords = htmlspecialchars_decode(stripslashes(html_entity_decode($document->document_meta_keywords)));
					$document->document_meta_description = htmlspecialchars_decode(stripslashes(html_entity_decode($document->document_meta_description)));
					$document->document_breadcrum_title = htmlspecialchars_decode(stripslashes(html_entity_decode($document->document_breadcrum_title)));
					$document->document_alias_breadcrumb = rewrite_link('index.php?id=' . $document->Id . '&amp;doc=' . (empty($document->document_alias) ? prepare_url($document->document_title) : $document->document_alias));
					$document->rubric_title = $AVE_Rubric->rubricNameByIdGet($document->rubric_id)->rubric_title;
					$document->rubric_url_prefix = $AVE_Rubric->rubricNameByIdGet($document->rubric_id)->rubric_alias;
					$document->formaction = 'index.php?do=docs&action=edit&sub=save&Id=' . $document_id . '&cp=' . SESSION;

					if ($document->document_parent != 0) $document->parent = $AVE_DB->Query("SELECT document_alias, document_title, Id FROM " . PREFIX . "_documents WHERE Id = '".$document->document_parent."' ")->FetchRow();

					$document_rev = array();

					if ( (isset($_SESSION[$document->rubric_id . '_delrev'])  && $_SESSION[$document->rubric_id . '_delrev'] == 1)
						|| (isset($_SESSION[$document->rubric_id . '_alles']) && $_SESSION[$document->rubric_id . '_alles']  == 1)
						|| (defined('UGROUP') && UGROUP == 1) )
					{
						$document->canDelRev = 1;
					}

					$sql_rev = $AVE_DB->Query("
						SELECT *
						FROM
							" . PREFIX . "_document_rev
						WHERE
							doc_id = '" . $document_id . "'
						ORDER BY
							doc_revision DESC
						LIMIT 7
					");

					// Формируем массив из полученных данных
					while ($result = $sql_rev->FetchRow())
					{
						$result->user_id = get_username_by_id($result->user_id);
						array_push($document_rev, $result);
					}

					$AVE_Template->assign('document_rev', $document_rev);

					// получения списка документов из связанной рубрики
					$linked_id = $AVE_DB->Query("
						SELECT
							rubric_linked_rubric
						FROM
							" . PREFIX . "_rubrics
						WHERE
							Id = '".$document->rubric_id."'
					")->GetCell();

					@$linked_id = unserialize($linked_id);

					$document_alias = array();

					if ($linked_id)
					{
						foreach ($linked_id as $linked_id)
						{
							$sql = $AVE_DB->Query("
								SELECT
									doc.document_alias,
									doc.document_title,
									doc.document_breadcrum_title,
									doc.Id,
									rub.rubric_title
								FROM
									" . PREFIX . "_documents as doc
								JOIN
									" . PREFIX . "_rubrics as rub
									ON rub.Id = doc.rubric_id
								WHERE
									doc.rubric_id = '" . $linked_id . "'
							");

							while ($row = $sql->FetchRow())
							{
								$document_alias[$row->rubric_title][] = array(
									'document_alias'=>$row->document_alias,
									'document_title'=>htmlspecialchars_decode(stripslashes(html_entity_decode($row->document_title))),
									'document_breadcrum_title'=>htmlspecialchars_decode(stripslashes(html_entity_decode($row->document_breadcrum_title))),
									'Id'=>$row->Id
								);
							}
						}
					}

					$rubric_tmpls = array();

					$sql = $AVE_DB->Query("
						SELECT
							id,
							title
						FROM
							" . PREFIX . "_rubric_templates
						WHERE
							rubric_id = '" . $document->rubric_id . "'
					");

					while ($row = $sql->FetchRow())
					{
						array_push($rubric_tmpls, $row);
					}

					// Доступные шаблоны рубрики
					$AVE_Template->assign('rubric_tmpls', $rubric_tmpls);

					// получения списка документов из связанной рубрики
					$AVE_Template->assign('document_alias', $document_alias);

					$AVE_Template->assign('document', $document);

					// Отображаем страницу для редактирования
					$AVE_Template->assign('content', $AVE_Template->fetch('documents/form.tpl'));
				}
				else // Если пользователь не имеет прав на редактирование, формируем сообщение об ошибке
				{
					$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('DOC_NO_PERMISSION'));
					$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
				}
				break;
		}
	}


	/**
	 * Метод, предназначенный для копирования документа
	 *
	 * @param int $document_id	идентификатор Документа
	 */
	function documentCopy($document_id)
	{
		global $AVE_DB, $AVE_Rubric, $AVE_Template;

		// Определяем действие, выбранное пользователем
		switch ($_REQUEST['sub'])
		{
			// Если была нажата кнопка Сохранить изменения
				case 'save': // Сохранение документа в БД
					$public_start  = $this->_documentStart(); // Дата/время начала публикации документа
					$public_end   = $this->_documentEnd();   // Дата/время окончания публикации документа
					$innavi = check_permission_acp('navigation_new') ? '&innavi=1' : '';

					// Определяем статус документа
					$document_status = !empty($_REQUEST['document_status']) ? (int)$_REQUEST['document_status'] : '0';

					// Если статус документа не определен
					if (empty($document_status) && $_SESSION['user_group'] != 1)
					{
						$innavi = '';
						@reset($_POST);
						$newtext = "\n\n";

						// Формируем текст сообщения, состоящий из данных,
						// которые пользователь ввел в поля документа
						foreach ($_POST['feld'] as $val)
						{
							if (!empty($val))
							{
								$newtext .= $val;
								$newtext .= "\n---------------------\n";
							}
						}
						$text = strip_tags($newtext);

						// Получаем e-mail адрес из общих настроек системы
						$system_mail = get_settings('mail_from');
						$system_mail_name = get_settings('mail_from_name');

						// Отправляем администартору уведомление, о том что необходимо проверить документ
						$body_to_admin = $AVE_Template->get_config_vars('DOC_MAIL_BODY_CHECK');
						$body_to_admin = str_replace('%N%', "\n", $body_to_admin);
						$body_to_admin = str_replace('%TITLE%', stripslashes($_POST['document_title']), $body_to_admin);
						$body_to_admin = str_replace('%USER%', "'" . $_SESSION['user_name'] . "'", $body_to_admin);
						send_mail(
							$system_mail,
							$body_to_admin . $text,
							$AVE_Template->get_config_vars('DOC_MAIL_SUBJECT_CHECK'),
							$system_mail,
							$system_mail_name,
							'text'
						);

						// Отправляем уведомление автору, о том что документ находится на проверке
						$body_to_author = str_replace('%N%', "\n", $AVE_Template->get_config_vars('DOC_MAIL_BODY_USER'));
						$body_to_author = str_replace('%TITLE%', stripslashes($_POST['document_title']), $body_to_author);
						$body_to_author = str_replace('%USER%', "'" . $_SESSION['user_name'] . "'", $body_to_author);
						send_mail(
							$_SESSION['user_email'],
							$body_to_author,
							$AVE_Template->get_config_vars('DOC_MAIL_SUBJECT_USER'),
							$system_mail,
							$system_mail_name,
							'text'
						);
					}

					if (! ((isset($_SESSION[$rubric_id . '_newnow']) && $_SESSION[$rubric_id . '_newnow'] == 1)
						|| (isset($_SESSION[$rubric_id . '_alles']) && $_SESSION[$rubric_id . '_alles'] == 1)
						|| (defined('UGROUP') && UGROUP == 1)) )
					{
						$document_status = 0;
					}

					$_POST['document_status']=$document_status;

					$iid = $this->documentSave($_REQUEST['rubric_id'],null,$_POST,true);

					if (! $_REQUEST['next_edit'])
					{
						header('Location:index.php?do=docs&action=after&document_id=' . $iid . '&rubric_id=' . $rubric_id . '&cp=' . SESSION . $innavi);
					}
					else
						{
							header('Location:index.php?do=docs&action=edit&Id=' . $iid . '&rubric_id=' . $rubric_id . '&cp=' . SESSION);
						}

					exit;

			// Если пользователь не выполнял никаких действий, а просто открыл документ для копирования
			// Если пользователь не выполнял никаких действий, а просто открыл документ для редактирования
			case '':
				// Выполняем запрос к БД на получение данных о документе
				$document = $AVE_DB->Query("
					SELECT *
					FROM
						" . PREFIX . "_documents
					WHERE
						Id = '" . $document_id . "'
				")->FetchRow();

				$show = true;

				// Проверяем права доступа к документу
				$this->documentPermissionFetch($document->rubric_id);

				// запрещаем доступ,
				// если автору документа не разрешено изменять свои документы в рубрике
				// или пользователю не разрешено изменять все документы в рубрике
				if (!( (isset($_SESSION['user_id']) && $document->document_author_id == $_SESSION['user_id']
					&& isset($_SESSION[$document->rubric_id . '_editown']) && $_SESSION[$document->rubric_id . '_editown'] == 1)
					|| (isset($_SESSION[$document->rubric_id . '_editall']) && $_SESSION[$document->rubric_id . '_editall'] == 1)))
				{
					$show = false;
				}

				// запрещаем доступ к главной странице и странице ошибки 404, если требуется одобрение Администратора
				if ( ($document_id == 1 || $document_id == PAGE_NOT_FOUND_ID) &&
					!(isset($_SESSION[$document->rubric_id . '_newnow']) && $_SESSION[$document->rubric_id . '_newnow'] == 1) )
				{
					$show = false;
				}

				// разрешаем доступ, если пользователь принадлежит группе Администраторов или имеет все права на рубрику
				if ( (defined('UGROUP') && UGROUP == 1)
					|| (isset($_SESSION[$document->rubric_id . '_alles']) && $_SESSION[$document->rubric_id . '_alles'] == 1) )
				{
					$show = true;
				}

				if ($show)
				{
					$fields = array();

					if ( (defined('UGROUP') && UGROUP == 1)
						|| (isset($_SESSION[$document->rubric_id . '_newnow']) && $_SESSION[$document->rubric_id . '_newnow'] == 1) )
					{
						$document->dontChangeStatus = 0;
					}
					else
						{
							$document->dontChangeStatus = 1;
						}

					// Выполняем запрос к БД и получаем все данные для полей документа
					$sql = $AVE_DB->Query("
						SELECT
							doc.Id AS df_id,
							groups.*,
							rub.*,
							rubric_field_default,
							doc.field_value,
							field_more.field_value as field_value_more
						FROM
							" . PREFIX . "_rubric_fields AS rub
						LEFT JOIN
							" . PREFIX . "_rubric_fields_group AS groups
							ON rub.rubric_field_group = groups.Id
						LEFT JOIN
							" . PREFIX . "_document_fields AS doc
							ON (rubric_field_id = rub.Id)
						LEFT JOIN
							" . PREFIX . "_document_fields_text AS field_more
							ON (field_more.rubric_field_id = doc.rubric_field_id AND doc.document_id=field_more.document_id)
						WHERE
							doc.document_id = '" . $document_id . "'
						ORDER BY
							groups.group_position ASC, rub.rubric_field_position ASC
					");


					while ($row = $sql->FetchRow())
					{
						$row->field_value = (string)$row->field_value . (string)$row->field_value_more;
						$row->field = $this->_documentFieldGet($row->rubric_field_type, $row->field_value, $row->Id, $row->rubric_field_default);
						array_push($fields, $row);
					}

					$maxId = $AVE_DB->Query("
						SELECT
							MAX(Id)
						FROM
							" . PREFIX . "_documents
					")->GetCell();

					foreach ($fields as $field)
					{
						$group_id = ($field->rubric_field_group) ? $field->rubric_field_group : 0;

						$fields_list[$group_id]['group_position'] = ($field->group_position) ? $field->group_position : 100;
						$fields_list[$group_id]['group_title'] = $field->group_title;
						$fields_list[$group_id]['fields'][$field->Id]['Id'] = $field->Id;
						$fields_list[$group_id]['fields'][$field->Id]['rubric_id'] = $row->rubric_id;
						$fields_list[$group_id]['fields'][$field->Id]['rubric_field_title'] = $field->rubric_field_title;
						$fields_list[$group_id]['fields'][$field->Id]['rubric_field_description'] = $field->rubric_field_description;
						$fields_list[$group_id]['fields'][$field->Id]['result'] = $field->field;
					}

					$fields_list = msort($fields_list, 'group_position');

					unset($doc_fields);
					unset($fields);

					// Формируем ряд переменных и передаем их в шаблон для вывода
					$document->fields = $fields_list;
					$document->count_groups = count($fields_list);
					$document->document_alias = '';
					$document->rubric_title = $AVE_Rubric->rubricNameByIdGet($_REQUEST['rubric_id'])->rubric_title;
					$document->rubric_url_prefix = strftime(str_ireplace("%id", $maxId+1, $AVE_Rubric->rubricNameByIdGet($_REQUEST['rubric_id'])->rubric_alias));
					$document->formaction = 'index.php?do=docs&action=copy&sub=save&rubric_id=' . $_REQUEST['rubric_id'] . ((isset($_REQUEST['pop']) && $_REQUEST['pop']==1) ? 'pop=1' : '') . '&cp=' . SESSION;
					$document->document_published = time();
					$document->document_expire = mktime(date("H"), date("i"), 0, date("m"), date("d"), date("Y") + 10);

					if ($document->document_parent != 0)
						$document->parent = $AVE_DB->Query("SELECT document_title, Id FROM " . PREFIX . "_documents WHERE Id = '" . $document->document_parent . "' ")->FetchRow();

					$AVE_Template->assign('document', $document);

					// Отображаем страницу для редактирования
					$AVE_Template->assign('content', $AVE_Template->fetch('documents/form.tpl'));
				}
				else // Если пользователь не имеет прав на редактирование, формируем сообщение об ошибке
					{
						$AVE_Template->assign('erorr', $AVE_Template->get_config_vars('DOC_NO_PERMISSION'));
						$AVE_Template->assign('content', $AVE_Template->fetch('error.tpl'));
					}
				break;
		}
	}

	/**
	 * Метод, предназначенный для пометки документа к удалению
	 *
	 * @param int $document_id	идентификатор Документа
	 */
	function documentMarkDelete($document_id)
	{
		global $AVE_DB;

		// Выполняем запрос к БД на получение информации о документе (id, id рубрики, автор)
		$row = $AVE_DB->Query("
			SELECT
				Id,
				rubric_id,
				document_author_id
			FROM
				" . PREFIX . "_documents
			WHERE
				Id = '" . $document_id . "'
		")->FetchRow();

		// Если у пользователя достаточно прав на выполнение данной операции
		if (
			(isset($_SESSION[$row->rubric_id . '_editall']) && $_SESSION[$row->rubric_id . '_editall'] == 1)
			||
			(isset($_SESSION[$row->rubric_id . '_editown']) && $_SESSION[$row->rubric_id . '_editown'] == 1)
			||
			(isset($_SESSION[$row->rubric_id . '_alles']) && $_SESSION[$row->rubric_id . '_alles'] == 1)
			||
			(defined('UGROUP') && UGROUP == 1)
		)
		{
			// и это не главная страница и не страница с ошибкой 404
			if ($document_id != 1 && $document_id != PAGE_NOT_FOUND_ID)
			{
				// Выполняем запрос к БД на обновление данных (пометка на удаление)
				$AVE_DB->Query("
					UPDATE
						" . PREFIX . "_documents
					SET
						document_deleted = '1'
					WHERE
						Id = '" . $document_id . "'
				");

				$AVE_DB->clearcache('rub_'.$row->rubric_id);
				$AVE_DB->clearcache('doc_'.$document_id);
				$AVE_DB->clearcompile('doc_'.$document_id);

				// Сохраняем системное сообщение в журнал
				reportLog('Положил документ в корзину (' . $document_id . ')');
			}
		}

		// Выполняем обновление страницы
		header('Location:index.php?do=docs'.(empty($_REQUEST['rubric_id']) ? '' : '&rubric_id='.$_REQUEST['rubric_id']).'&cp=' . SESSION);
	}

	/**
	 * Метод, предназначенный для снятия отметки об удаления
	 *
	 * @param int $document_id	идентификатор Документа
	 */
	function documentUnmarkDelete($document_id)
	{
		global $AVE_DB;

		// Выполняем запрос к БД на обновление информации (снятие отметки об удалении)
		$row = $AVE_DB->Query("
			SELECT *
			FROM
				" . PREFIX . "_documents
			WHERE
				Id = '" . $document_id . "'
		")->FetchRow();

		$AVE_DB->Query("
			UPDATE
				" . PREFIX . "_documents
			SET
				document_deleted = '0'
			WHERE
				Id = '" . $document_id . "'
		");

		// Сохраняем системное сообщение в журнал
		reportLog('Восстановил удаленный документ (' . $document_id . ')');

		$AVE_DB->clearcache('rub_'.$row->rubric_id);
		$AVE_DB->clearcache('doc_'.$document_id);
		$AVE_DB->clearcompile('doc_'.$document_id);

		// Выполняем обновление страницы
		header('Location:index.php?do=docs'.(empty($_REQUEST['rubric_id']) ? '' : '&rubric_id='.$_REQUEST['rubric_id']).'&cp=' . SESSION);
	}

	/**
	 * Метод, предназначенный для полного удаления документа без возможности восстановления
	 *
	 * @param int $document_id	идентификатор Документа
	 */
	function documentDelete($document_id)
	{
		global $AVE_DB;

		// Проверяем, чтобы удаляемый документ не являлся главной страницей и не страницей с 404 ощибкой
		if ($document_id != 1 && $document_id != PAGE_NOT_FOUND_ID)
		{
			$row = $AVE_DB->Query("
				SELECT *
				FROM
					" . PREFIX . "_documents
				WHERE
					Id = '" . $document_id . "'
			")->FetchRow();

			// Выполняем запрос к БД на удаление информации о документе
			$AVE_DB->Query("
				DELETE
				FROM
					" . PREFIX . "_documents
				WHERE
					Id = '" . $document_id . "'
			");

			// Выполняем запрос к БД на удаление полей, которые относились к документу
			$AVE_DB->Query("
				DELETE
					f1.*,
					f2.*
				FROM
					" . PREFIX . "_document_fields AS f1
				INNER JOIN
					" . PREFIX . "_document_fields_text AS f2
				WHERE
					f1.document_id = '" . $document_id . "'
				AND
					f2.document_id = f1.document_id
			");

			// Очищаем кэш шаблона
			$AVE_DB->Query("
				DELETE
				FROM
					" . PREFIX . "_rubric_template_cache
				WHERE
					doc_id = '" . $document_id . "'
			");

			// Сохраняем системное сообщение в журнал
			reportLog('Удалил документ <strong>'. $row->document_title . ' (ID: ' . $document_id . ')</strong>');
		}

		// Удаляем кеш докеумента
		$AVE_DB->clearcache('rub_' . $row->rubric_id);
		$AVE_DB->clearcache('doc_' . $document_id);
		$AVE_DB->clearcompile('doc_'.$document_id);

		// Выполняем обновление страницы
		header('Location:index.php?do=docs'.(empty($_REQUEST['rubric_id'])
			? ''
			: '&rubric_id='.$_REQUEST['rubric_id']).'&cp=' . SESSION);
	}

	/**
	 * Метод, предназначенный для публикации или отмены публикации документа
	 *
	 * @param int $document_id	идентификатор Документа
	 * @param string $openclose	статус Документа {open|close}
	 */
	function documentStatusSet($document_id, $openclose = 0)
	{
		global $AVE_DB, $AVE_Template;

		$errors = array();

		$show = true;

		// Выполняем запрос к БД на получение информации о документе (id, id рубрики, автор)
		$document = $AVE_DB->Query("
			SELECT
				Id,
				rubric_id,
				document_author_id
			FROM
				" . PREFIX . "_documents
			WHERE
				Id = '" . $document_id . "'
		")->FetchRow();

		// Проверяем права доступа к документу
		$this->documentPermissionFetch($document->rubric_id);

		// запрещаем доступ,
		// если автору документа не разрешено изменять свои документы в рубрике
		// или пользователю не разрешено изменять все документы в рубрике
		if (!
			(
				(isset($_SESSION[$document->rubric_id . '_editall']) && $_SESSION[$document->rubric_id . '_editall'] == 1)
				||
				(isset($_SESSION[$document->rubric_id . '_editown']) && $_SESSION[$document->rubric_id . '_editown'] == 1)
				||
				(isset($_SESSION[$document->rubric_id . '_alles']) && $_SESSION[$document->rubric_id . '_alles'] == 1)
				||
				(defined('UGROUP') && UGROUP == 1)
			)
		)
		{
			$show = false;
		}

		// запрещаем доступ к главной странице и странице ошибки 404, если требуется одобрение Администратора
		if ( ($document_id == 1 || $document_id == PAGE_NOT_FOUND_ID) &&
			!(isset($_SESSION[$document->rubric_id . '_newnow']) && $_SESSION[$document->rubric_id . '_newnow'] == 1) )
		{
			$show = false;
		}

		// разрешаем доступ, если пользователь принадлежит группе Администраторов или имеет все права на рубрику
		if ( (defined('UGROUP') && UGROUP == 1)
			|| (isset($_SESSION[$document->rubric_id . '_alles']) && $_SESSION[$document->rubric_id . '_alles'] == 1) )
		{
			$show = true;
		}

		if ($show)
		{
			// Выполняем запрос к БД на получение id автора документа, чтобы проверить уровень прав доступа

			// Проверем, чтобы у пользователя было достаточно прав на выполнение данной операции
			if (
					(
						($document->document_author_id == @$_SESSION['user_id'])
						&&
						(isset($_SESSION[$document->rubric_id . '_newnow']) && @$_SESSION[$row->rubric_id . '_newnow'] == 1)
						||
						@$_SESSION[$row->rubric_id . '_alles'] == 1
						||
						(defined('UGROUP') && UGROUP == 1)
					)
					||
					(isset($_SESSION[$document->rubric_id . '_editall']) && $_SESSION[$document->rubric_id . '_editall'] == 1)
					||
					(isset($_SESSION[$document->rubric_id . '_editown']) && $_SESSION[$document->rubric_id . '_editown'] == 1)
					||
					(isset($_SESSION[$document->rubric_id . '_alles']) && $_SESSION[$document->rubric_id . '_alles'] == 1)
					||
					(defined('UGROUP') && UGROUP == 1)
				)
				{
				// Если это не главная страница и не страница с 404 ошибкой
				if ($document_id != 1 && $document_id != PAGE_NOT_FOUND_ID)
				{
					// Выполянем запрос к БД на смену статуса у документа
					$AVE_DB->Query("
						UPDATE
							" . PREFIX . "_documents
						SET
							document_status = '" . $openclose . "'
						WHERE
							Id = '" . $document_id . "'
					");

					$AVE_DB->clearcache('rub_'.$row->rubric_id);
					$AVE_DB->clearcache('doc_'.$document_id);
					$AVE_DB->clearcompile('doc_'.$document_id);

					// Сохраняем системное сообщение в журнал
					reportLog($_SESSION['user_name'] . ' - ' . (($openclose==1) ? $AVE_Template->get_config_vars('DOC_DOCUMENT_ACT') : $AVE_Template->get_config_vars('DOC_DOCUMENT_DISACT')) . ' ' . $AVE_Template->get_config_vars('DOC_DOCUMENT_DOC') . ' (' . $document_id . ')', 2, 2);

				}
				else
					{
						$errors[] = $AVE_Template->get_config_vars('DOC_DOCUMENT_OPEN_ERR');
					}

			}
			else
				{
					$errors[] = $AVE_Template->get_config_vars('DOC_DOCUMENT_OPEN_PRIVE');
				}

			if (isset($_REQUEST['ajax']))
			{
				if (empty($errors))
				{
					// Если ошибок не найдено, формируем сообщение об успешной операции
					echo json_encode(array((($openclose==1) ? $AVE_Template->get_config_vars('DOC_DOCUMENT_OPEN') : $AVE_Template->get_config_vars('DOC_DOCUMENT_CLOSE')) . implode(',<br />', $errors), 'accept'));
				}
				else
					{
						// В противном случае формируем сообщение с ошибкой
						echo json_encode(array($AVE_Template->get_config_vars('DOC_URL_CHECK_ER') . implode(',<br />', $errors), 'error'));

					}

				$AVE_DB->clearcache('rub_'.$row->rubric_id);
				$AVE_DB->clearcache('doc_'.$document_id);
				$AVE_DB->clearcompile('doc_'.$document_id);
				exit;

			}
			else
				{
					$AVE_DB->clearcache('rub_'.$row->rubric_id);
					$AVE_DB->clearcache('doc_'.$document_id);
					$AVE_DB->clearcompile('doc_'.$document_id);
					// Выполняем обновление страницы
					header('Location:index.php?do=docs'.(empty($_REQUEST['rubric_id']) ? '' : '&rubric_id='.$_REQUEST['rubric_id']).'&cp=' . SESSION);
					exit;
				}
		}
		else
			{
				header('Location:index.php?do=docs&cp=' . SESSION);
				exit;
			}
	}

	/**
	 * Метод, предназначенный для передачи в Smarty шаблонизатор меток периода времени отображаемых
	 * в списке документов
	 *
	 */
	function documentTemplateTimeAssign()
	{
		global $AVE_Template;

		if (!empty($_REQUEST['TimeSelect']))
		{
			$AVE_Template->assign('sel_start', $this->_documentListStart());
			$AVE_Template->assign('sel_end', $this->_documentListEnd());
		}
	}

	/**
	 * Метод, предназначенный для переноса документа в другую рубрику
	 *
	 */
	function documentRubricChange()
	{
		global $AVE_DB, $AVE_Template;

		$document_id = (int)$_REQUEST['Id'];        // идентификатор документа
		$rubric_id   = (int)$_REQUEST['rubric_id']; // идентификатор текущей рубрики

		// Если в запросе пришел идентификатор новой рубрики и id документа, тогда
		// выполняем автоматический перенос документа из одной рубрики в другую
		if ((! empty($_POST['NewRubr'])) and (! empty($_GET['Id'])))
		{
			$new_rubric_id = (int)$_POST['NewRubr']; // идентификатор целевой рубрики

			// Циклически обрабатываем данные, пришедшие в запросе методо POST
			foreach ($_POST as $key => $value)
			{
				if (is_integer($key))
				{
					// Определяем флаг поля
					switch ($value)
					{
						// Если 0, тогда
						case 0:
							// Выполняем запрос к БД на удаление старого поля (лишнее или не требует переноса)
							$AVE_DB->Query("
								DELETE
								FROM
									" . PREFIX . "_document_fields
								WHERE
									document_id = '" . $document_id . "'
								AND
									rubric_field_id = '" . $key . "'
							");

							$AVE_DB->Query("
								DELETE
								FROM
									" . PREFIX . "_document_fields_text
								WHERE
									document_id = '" . $document_id . "'
								AND
									rubric_field_id = '" . $key . "'
							");
							break;

						// Если -1, тогда
						case -1:
							// Выполняем запрос на получение данных для этого (старого) поля
							$row_fd = $AVE_DB->Query("
								SELECT
									rubric_field_title,
									rubric_field_type
								FROM
									" . PREFIX . "_rubric_fields
								WHERE
									Id = '" . $key . "'
							")->FetchRow();

							// Выполняем запрос к БД и получаем последнюю позицию полей в рубрики КУДА переносим
							$new_pos = $AVE_DB->Query("
								SELECT
									rubric_field_position
								FROM
									" . PREFIX . "_rubric_fields
								WHERE
									rubric_id = '" . $new_rubric_id . "'
								ORDER BY
									rubric_field_position DESC
								LIMIT 1
							")->GetCell();

							++$new_pos;

							// Выполняем запрос к БД и добавляем новое поле в новую рубрику
							$AVE_DB->Query("
								INSERT
								INTO
									" . PREFIX . "_rubric_fields
								SET
									rubric_id             = '" . $new_rubric_id . "',
									rubric_field_title    = '" . addslashes($row_fd->rubric_field_title) . "',
									rubric_field_type     = '" . addslashes($row_fd->rubric_field_type) . "',
									rubric_field_position = '" . $new_pos . "'
							");

							$lastid = $AVE_DB->InsertId();

							// Выполняем запрос к БД и добавляем запись о поле в таблицу с полями документов
							$sql_docs = $AVE_DB->Query("
								SELECT Id
								FROM
									" . PREFIX . "_documents
								WHERE
									rubric_id = '" . $new_rubric_id . "'
							");

							while ($row_docs = $sql_docs->FetchRow())
							{
								$AVE_DB->Query("
									INSERT
									INTO
										" . PREFIX . "_document_fields
									SET
										rubric_field_id    = '" . $lastid . "',
										document_id        = '" . $row_docs->Id . "',
										field_value        = '',
										document_in_search = '1'
								");
							}

							// Выполняем запрос к БД и создаем новое поле для изменяемого документа
							$AVE_DB->Query("
								UPDATE
									" . PREFIX . "_document_fields
								SET
									rubric_field_id = '" . $lastid . "'
								WHERE
									rubric_field_id = '" . $key . "'
								AND
									document_id = '" . $document_id . "'
							");
							$AVE_DB->Query("
								UPDATE
									" . PREFIX . "_document_fields_text
								SET
									rubric_field_id = '" . $lastid . "'
								WHERE
									rubric_field_id = '" . $key . "'
								AND
									document_id = '" . $document_id . "'
							");
							break;

						// По умолчанию
						default:
							// Выполняем запрос к БД и просто обновляем имеющиеся данные
							$AVE_DB->Query("
								UPDATE
									" . PREFIX . "_document_fields
								SET
									rubric_field_id = '" . $value . "'
								WHERE
									rubric_field_id = '" . $key . "'
								AND
									document_id = '" . $document_id . "'
							");

							$AVE_DB->Query("
								UPDATE
									" . PREFIX . "_document_fields_text
								SET
									rubric_field_id = '" . $value . "'
								WHERE
									rubric_field_id = '" . $key . "'
								AND
									document_id = '" . $document_id . "'
							");
							break;
					}
				}
			}

			// Выполняем запрос к БД и получаем список всех полей у новой рубрики
			$sql_rub = $AVE_DB->Query("
				SELECT Id
				FROM
					" . PREFIX . "_rubric_fields
				WHERE
					rubric_id = '" . $new_rubric_id . "'
				ORDER BY
					Id ASC
			");

			// Выполняем запросы к БД на проверку наличия нужных полей.
			while ($row_rub = $sql_rub->FetchRow())
			{
				$num = $AVE_DB->Query("
					SELECT 1
					FROM
						" . PREFIX . "_document_fields
					WHERE
						rubric_field_id = '" . $row_rub->Id . "'
					AND
						document_id = '" . $document_id . "'
					LIMIT 1
				")->NumRows();

				// Если в новой рубрики требуемого поля нет, выполняем запрос к БД на добавление нового типа поля
				if ($num != 1)
				{
					$AVE_DB->Query("
						INSERT
							" . PREFIX . "_document_fields
						SET
							rubric_field_id    = '" . $row_rub->Id . "',
							document_id        = '" . $document_id . "',
							field_value        = '',
							document_in_search = '1'
					");
				}
			}

			// Выполянем запрос к БД на обновление информации, в котором устанавливаем для перенесенного документа
			// новое значение id рубрики
			$AVE_DB->Query("
				UPDATE
					" . PREFIX . "_documents
				SET
					rubric_id = '" . $new_rubric_id . "'
				WHERE
					Id = '" . $document_id . "'
			");

			// Выполняем запрос к БД и очищаем кэш шаблона документа
			$AVE_DB->Query("
				DELETE
				FROM
					" . PREFIX . "_rubric_template_cache
				WHERE
					doc_id = '" . $document_id . "'
			");

			echo '<script>window.opener.location.reload(); window.close();</script>';
		}
		else  // Если в запросе не был указан id рубрики и id документа
		{
			// Формируем и отображаем форму, где пользователь самостоятельно определяет перенос
			$fields = array();

			if ((! empty($_GET['NewRubr'])) and ($rubric_id != (int)$_GET['NewRubr']))
			{
				// Выполняем запрос к БД  и выбираем все поля новой рубрики
				$sql_rub = $AVE_DB->Query("
					SELECT
						Id,
						rubric_field_title,
						rubric_field_type
					FROM
						" . PREFIX . "_rubric_fields
					WHERE
						rubric_id = '" . (int)$_GET['NewRubr'] . "'
					ORDER BY
						Id ASC
				");
				$mass_new_rubr = array();

				while ($row_rub = $sql_rub->FetchRow())
				{
					$mass_new_rubr[] = array('Id'                => $row_rub->Id,
											 'title'             => $row_rub->rubric_field_title,
											 'rubric_field_type' => $row_rub->rubric_field_type
					);
				}

				// Выполняем запрос к БД и выбираем все поля старой рубрики
				$sql_old_rub = $AVE_DB->Query("
					SELECT
						Id,
						rubric_field_title,
						rubric_field_type
					FROM
						" . PREFIX . "_rubric_fields
					WHERE
						rubric_id = '" . $rubric_id . "'
					ORDER BY
						Id ASC
				");

				// Циклически обрабатываем полученные данные
				while ($row_nr = $sql_old_rub->FetchRow()) {
					$type = $row_nr->rubric_field_type;
					$option_arr = array('0'  => $AVE_Template->get_config_vars('DOC_CHANGE_DROP_FIELD'),
										'-1' => $AVE_Template->get_config_vars('DOC_CHANGE_CREATE_FIELD')
					);
					$selected = -1;
					foreach ($mass_new_rubr as $row)
					{
						if ($row['rubric_field_type'] == $type)
						{
							$option_arr[$row['Id']] = $row['title'];

							if ($row_nr->rubric_field_title == $row['title'])
								$selected = $row['Id'];
						}
					}
					$fields[$row_nr->Id] = array('title'    => $row_nr->rubric_field_title,
												 'Options'  => $option_arr,
												 'Selected' => $selected
					);
				}
			}

			// Формируем ряд переменых и отображаем страницу с выбором рубрики
			$AVE_Template->assign('fields', $fields);
			$AVE_Template->assign('formaction', 'index.php?do=docs&action=change&Id=' . $document_id . '&rubric_id=' . $rubric_id . '&pop=1&cp=' . SESSION);
			$AVE_Template->assign('content', $AVE_Template->fetch('documents/change.tpl'));
		}
	}

	/**
	 * Метод, предназначенный для формирования прав доступа Группы пользователей на Документы определённой Рубрики
	 *
	 * @param int $rubric_id	идентификатор Рубрики
	 */
	function documentPermissionFetch($rubric_id)
	{
		global $AVE_DB;

		// Массив прав пользователей
		static $rubric_permissions = array();

		// Если у нас уже имеются полученные права для данной рубрики, просто прерываем проверку
		if (isset($rubric_permissions[$rubric_id])) return;

		// Выполняем запрос к БД на получение прав для данной рубрики
		$sql = $AVE_DB->Query("
			SELECT
				rubric_id,
				rubric_permission
			FROM
				" . PREFIX . "_rubric_permissions
			WHERE
				user_group_id = '" . UGROUP . "'
		");

		// Циклически обрабатываем полученные данные и формируем массив прав
		while ($row = $sql->FetchRow())
		{
			$rubric_permissions[$row->rubric_id] = 1;

			$permissions = explode('|', $row->rubric_permission);

			foreach ($permissions as $rubric_permission)
			{
				if (! empty($rubric_permission))
				{
					$_SESSION[$row->rubric_id . '_' . $rubric_permission] = 1;
				}
			}
		}
	}

	/**
	 * Метод, предназначенный для просмотра и добавления Заметок к Документу
	 *
	 * @param int $reply	признак ответа на Заметку
	 */
	function documentRemarkNew($document_id = 0, $reply = 0)
	{
		global $AVE_DB, $AVE_Template, $AVE_Core;

		// Если id документа не число или 0, прерываем выполнение
		if (! (is_numeric($document_id) && $document_id > 0))
			exit;

		$document_title = get_document($document_id, 'document_title');
		$AVE_Template->assign('document_title', $document_title);

		// Если в запросе пришел параметр на Сохранение
		if (isset($_REQUEST['sub']) && $_REQUEST['sub'] == 'save')
		{
			// Если пользователь оставил комментарий и у него имеются права и это не ответ, а новая заметка, тогда
			if (!empty($_REQUEST['remark_text']) && check_permission('remarks') && empty($_REQUEST['reply']))
			{
				// Выполняем запрос к БД на добавление новой заметки для документа
				$AVE_DB->Query("
					INSERT
						" . PREFIX . "_document_remarks
					SET
						document_id         = '" . $document_id . "',
						remark_title        = '" . clean_no_print_char($_REQUEST['remark_title']) . "',
						remark_text         = '" . substr(clean_no_print_char($_REQUEST['remark_text']), 0, $this->_max_remark_length) . "',
						remark_author_id    = '" . $_SESSION['user_id'] . "',
						remark_published    = '" . time() . "',
						remark_first        = '1',
						remark_author_email = '" . $_SESSION['user_email'] . "'
				");
			}

			// Выполняем обновление страницы
			header('Location:index.php?do=docs&action=remark_reply&Id=' . $document_id . '&pop=1&cp=' . SESSION);
		}

		// Если это ответ на уже существующую заметку
		if ($reply == 1)
		{
			if (isset($_REQUEST['sub']) && $_REQUEST['sub'] == 'save')
			{
				// Если пользователь оставил ответ и имеет на это права
				if (! empty($_REQUEST['remark_text']) && check_permission('remarks'))
				{
					// Выполняем запрос на получение e-mail адреса автора заметки
					$remark_author_email = $AVE_DB->Query("
						SELECT
							remark_author_email
						FROM
							" . PREFIX . "_document_remarks
						WHERE
							remark_first = '1'
						AND
							document_id = '" . $document_id . "'
					")->GetCell();

					// Выполняем запрос к БД на добавление заметки в БД
					$AVE_DB->Query("
						INSERT
							" . PREFIX . "_document_remarks
						SET
							document_id         = '" . $document_id . "',
							remark_title        = '" . clean_no_print_char($_REQUEST['remark_title']) . "',
							remark_text         = '" . substr(clean_no_print_char($_REQUEST['remark_text']), 0, $this->_max_remark_length) . "',
							remark_author_id    = '" . $_SESSION['user_id'] . "',
							remark_published    = '" . time() . "',
							remark_first        = '0',
							remark_author_email = '" . $_SESSION['user_email'] . "'
					");
				}

				// Формируем сообщение и отправляем письмо автору, с информацией о том, что на его заметку есть ответ
				$system_mail = get_settings('mail_from');
				$system_mail_name = get_settings('mail_from_name');
				$link = get_home_link() . 'index.php?do=docs&doc_id=' . $document_id;

				$body_to_admin = $AVE_Template->get_config_vars('DOC_MAIL_BODY_NOTICE');
				$body_to_admin = str_replace('%N%', "\n", $body_to_admin);
				$body_to_admin = str_replace('%TITLE%', stripslashes($_POST['remark_title']), $body_to_admin);
				$body_to_admin = str_replace('%USER%', get_username_by_id($_SESSION['user_id']), $body_to_admin);
				$body_to_admin = str_replace('%LINK%', $link, $body_to_admin);
				send_mail(
					$remark_author_email,
					$body_to_admin,
					$AVE_Template->get_config_vars('DOC_MAIL_SUBJECT_NOTICE'),
					$system_mail,
					$system_mail_name,
					'text'
				);

				// Выполняем обновление страницы
				header('Location:index.php?do=docs&action=remark_reply&Id=' . $document_id . '&pop=1&cp=' . SESSION);
			}

			// Получаем общее количество заметок для документа
			$num = $AVE_DB->Query("
				SELECT COUNT(*)
				FROM " . PREFIX . "_document_remarks
				WHERE document_id = '" . $document_id . "'
			")->GetCell();

			// Определяыем лимит заметок на 1 странице и подсчитываем количество страниц
			$limit = 10;
			$pages = ceil($num / $limit);
			$start = get_current_page() * $limit - $limit;

			$answers = array();

			// Выполняем запрос к БД на получение заметок с учетом количества на 1 странцу
			$sql = $AVE_DB->Query("
				SELECT *
				FROM
					" . PREFIX . "_document_remarks
				WHERE
					document_id = '" . $document_id . "'
				ORDER BY
					Id DESC
				LIMIT " . $start . "," . $limit
			);

			while ($row = $sql->FetchAssocArray())
			{
				$row['remark_author'] = get_username_by_id($row['remark_author_id']);
				$row['remark_text'] = nl2br($row['remark_text']);
				$row['remark_avatar'] = getAvatar($row['remark_author_id'],40);
				array_push($answers, $row);
			}

			$remark_status = $AVE_DB->Query("
				SELECT
					remark_status
				FROM
					" . PREFIX . "_document_remarks
				WHERE
					document_id = '" . $document_id . "'
				AND
					remark_first = '1'
			")->GetCell();

			// Если количество заметок превышает допустимое значение, определенное в переменной $limit, тогда
			// формируем постраничную навигацию
			if ($num > $limit)
			{
				$page_nav = "<li><a href=\"index.php?do=docs&action=remark_reply&Id=" . $document_id . "&page={s}&pop=1&cp=" . SESSION . "\">{t}</a></li>";
				$page_nav = get_pagination($pages, 'page', $page_nav);
				$AVE_Template->assign('page_nav', $page_nav);
			}

			// Передаем данные  в шаблон и отображаем страницу со списком заметок
			$AVE_Template->assign('document_title', $document_title);
			$AVE_Template->assign('remark_status', $remark_status);
			$AVE_Template->assign('answers', $answers);
			$AVE_Template->assign('reply', 1);
			$AVE_Template->assign('formaction', 'index.php?do=docs&action=remark_reply&sub=save&Id=' . $document_id . '&reply=1&cp=' . SESSION);
			$AVE_Template->assign('content', $AVE_Template->fetch('documents/newremark.tpl'));
		}
		else
			{ // В противном случае, если заметок еще нет, открываем форму для добавление заметки
				$AVE_Template->assign('reply', 1);
				$AVE_Template->assign('new', 1);
				$AVE_Template->assign('formaction', 'index.php?do=docs&action=remark&sub=save&Id=' . $document_id . '&cp=' . SESSION);
				$AVE_Template->assign('content', $AVE_Template->fetch('documents/newremark.tpl'));
			}
	}

	/**
	 * Метод, предназначенный для управления статусами дискусии (разрешить или запретить оставлять
	 * ответы на заметки для других пользователей)
	 *
	 * @param int $document_id	идентификатор документа
	 * @param int $status		статус дискусии
	 */
	function documentRemarkStatus($document_id = 0, $status = 0)
	{
		global $AVE_DB;

		// Если id документа число и оно больше 0, тогда
		if (is_numeric($document_id) && $document_id > 0)
		{
			// Выполняем запрос к БД на обновление статуса у заметок
			$AVE_DB->Query("
				UPDATE
					" . PREFIX . "_document_remarks
				SET
					remark_status  = '" . ($status != 1 ? 0 : 1) . "'
				WHERE
					remark_first = '1'
				AND
					document_id = '" . $document_id . "'
			");
		}

		// Выполняем обновление данных
		header('Location:index.php?do=docs&action=remark_reply&Id=' . $document_id . '&pop=1&cp=' . SESSION);
		exit;
	}

	/**
	 * Метод, предназначенный для удаление заметок
	 *
	 * @param int $all	признак удаления всех Заметок (1 - удалить все)
	 */
	function documentRemarkDelete($document_id = 0, $all = 0)
	{
		global $AVE_DB;

		// Если id документа не число или 0, прерываем выполнение
		if (! (is_numeric($document_id) && $document_id > 0))
			exit;

		// Если в запросе пришел параметр на удаление всех заметок
		if ($all == 1)
		{
			// Выполянем запрос к БД и удалаем заметки
			$AVE_DB->Query("
				DELETE
				FROM
					" . PREFIX . "_document_remarks
				WHERE
					document_id = '" . $document_id . "'
			");

			// Выполняем обновление страницы
			header('Location:index.php?do=docs&action=remark&Id=' . $document_id . '&pop=1&cp=' . SESSION);
			exit;
		}
		else
		{
			if (! (isset($_REQUEST['CId']) && is_numeric($_REQUEST['CId']) && $_REQUEST['CId'] > 0))
				exit;

			// В противном случае, выполняем запрос к БД и удаляем только ту заметку, которая была выбрана
			$AVE_DB->Query("
				DELETE
				FROM
					" . PREFIX . "_document_remarks
				WHERE
					document_id = '" . $document_id . "'
				AND
					Id = '" . $_REQUEST['CId'] . "'
			");

			// Выполняем обновление страницы
			header('Location:index.php?do=docs&action=remark_reply&Id=' . $document_id . '&pop=1&cp=' . SESSION);
			exit;
		}
	}

	/**
	 * Добавить в навигацию пункт ссылающийся на документ
	 *
	 */
	function documentInNavi()
	{
		global $AVE_DB;

		$document_id = isset($_REQUEST['document_id']) ? (int)$_REQUEST['document_id'] : 0;
		$rubric_id = isset($_REQUEST['rubric_id']) ? (int)$_REQUEST['rubric_id'] : 0;
		$title  = isset($_REQUEST['navi_title']) ? clean_no_print_char($_REQUEST['navi_title']) : '';

		if ($document_id > 0 && $rubric_id > 0 && $title != '' && check_permission_acp('navigation_new'))
		{
			$document_alias = $AVE_DB->Query("
				SELECT
					document_alias
				FROM
					" . PREFIX . "_documents
				WHERE
					Id = '" . $document_id . "'
				AND
					rubric_id = '" . $rubric_id . "'
				LIMIT 1
			")->GetCell();
		}


		if (isset($document_alias) && $document_alias !== false)
		{
			// Получаем id пункта меню из запроса
			$parent_id = isset($_REQUEST['parent_id']) ? (int)$_REQUEST['parent_id'] : 0;

			// Если пункт не родительский, а какой-либо дочерний
			if ($parent_id > 0)
			{
				// Выполняем запрос к БД на получение id меню навигации и уровня
				list($navigation_id, $status, $level) = $AVE_DB->Query("
					SELECT
						navigation_id,
						status,
						level+1
					FROM
						" . PREFIX . "_navigation_items
					WHERE
						navigation_item_id = '" . $parent_id . "'
					LIMIT 1
				")->FetchArray();
			}
			else
				{
					$navigation_id = (isset($_REQUEST['navi_id']) && (int)$_REQUEST['navi_id'] > 0) ? (int)$_REQUEST['navi_id'] : 1;
					$status  = 1;
					$level   = 1;
				}

			$target = (isset($_REQUEST['navi_item_target']) && $_REQUEST['navi_item_target'] == '_blank') ? '_blank' : '_self';

			$position = empty($_REQUEST['navi_item_position']) ? 1 : (int)$_REQUEST['navi_item_position'];

			if ($level > 3)
				$level = '3';

			// Добавляем информации о новой связке Документ<->Пункт меню
			$AVE_DB->Query("
				INSERT
				INTO
					" . PREFIX . "_navigation_items
				SET
					title              = '" . $title . "',
					document_id        = '" . $document_id . "',
					alias              = '" . $document_alias . "',
					parent_id          = '" . $parent_id . "',
					navigation_id      = '" . $navigation_id . "',
					level              = '" . $level . "',
					target             = '" . $target . "',
					position           = '" . $position . "',
					status             = '" . $status . "'
			");
		}

		header('Location:index.php?do=docs&action=after&document_id=' . $document_id . '&rubric_id=' . $rubric_id . '&cp=' . SESSION);
		exit;
	}

	/**
	 * Вывод формы дополнительных действий с новым или отредактированным документом
	 *
	 */
	function documentFormAfter()
	{
		global $AVE_DB, $AVE_Template;

		$document_id = isset($_REQUEST['document_id']) ? (int)$_REQUEST['document_id'] : 0;
		$rubric_id = isset($_REQUEST['rubric_id']) ? (int)$_REQUEST['rubric_id'] : 0;
		$innavi = (isset($_REQUEST['innavi']) && check_permission_acp('navigation_new')) ? 1 : 0;

		if ($document_id > 0 && $rubric_id > 0)
		{
			$document = $AVE_DB->Query("
				SELECT
					Id AS document_id,
					rubric_id,
					document_title AS document_title,
					'" . $innavi . "' AS innavi
				FROM
					" . PREFIX . "_documents
				WHERE
					Id = '" . $document_id . "'
				AND
					rubric_id = '" . $rubric_id . "'
				LIMIT 1
			")->FetchAssocArray();
		}

		if (empty($document))
		{
			header('Location:index.php?do=docs&cp=' . SESSION);
			exit;
		}

		$AVE_Template->assign($document);
		$AVE_Template->assign('content', $AVE_Template->fetch('documents/form_after.tpl'));
	}

	/**
	 * Метод, предназначенный для смены автора документа
	 *
	 * @param int $doc_id		идентификатор документа
	 * @param int $user_id		идентификатор пользователя
	 */
	function changeAutorSave()
	{
		global $AVE_DB;

		// Если id документа число и оно больше 0, тогда
		if (is_numeric($_REQUEST['doc_id']) && $_REQUEST['doc_id'] > 0)
		{
			// Выполняем запрос к БД на обновление статуса у заметок
			$AVE_DB->Query("
				UPDATE
					" . PREFIX . "_documents
				SET
					document_author_id  = '" . $_REQUEST['user_id'] . "'
				WHERE
					Id = '" . $_REQUEST['doc_id'] . "'
			");

			$username = get_username_by_id($_REQUEST['user_id']);

			echo "
				<script>
					window.opener.document.getElementById('doc_id_". $_REQUEST['doc_id'] ."').textContent = '$username';
					window.close();
				</script>
			";
		}
		exit;
	}

	/**
	 * Метод, предназначенный для удаления ревизий документов
	 *
	 */
	function documentsRevisionsClear()
	{
		global $AVE_DB, $AVE_Template;

		if (check_permission('document_php'))
		{
			$sql = $AVE_DB->Query("
				TRUNCATE TABLE " . PREFIX . "_document_rev
			");

			if ($sql->_result === false)
			{
				$message = $AVE_Template->get_config_vars('SETTINGS_REV_DELETED_ERR');
				$header = $AVE_Template->get_config_vars('SETTINGS_ERROR');
				$theme = 'error';
			}
			else
				{
					$message = $AVE_Template->get_config_vars('SETTINGS_REV_DELETED');
					$header = $AVE_Template->get_config_vars('SETTINGS_SUCCESS');
					$theme = 'accept';
					reportLog($AVE_Template->get_config_vars('SETTINGS_REV_UPDATE'));
				}

			if (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] = 'run')
			{
				echo json_encode(
					array(
						'message' => $message,
						'header' => $header,
						'theme' => $theme
					)
				);
			}
			else
				{
					header('Location:index.php?do=settings&cp=' . SESSION);
				}
		}
		exit;
	}

	/**
	 * Метод, предназначенный для удаления ревизий документов
	 *
	 */
	function documentCounterClear()
	{
		global $AVE_DB, $AVE_Template;

		if (check_permission('gen_settings'))
		{
			$sql = $AVE_DB->Query("
				TRUNCATE TABLE
					" . PREFIX . "_view_count
			");

			if ($sql->_result === false)
			{
				$message = $AVE_Template->get_config_vars('SETTINGS_COUNT_DELETED_ERR');
				$header = $AVE_Template->get_config_vars('SETTINGS_ERROR');
				$theme = 'error';
			}
			else
				{
					$message = $AVE_Template->get_config_vars('SETTINGS_COUNT_DELETED');
					$header = $AVE_Template->get_config_vars('SETTINGS_SUCCESS');
					$theme = 'accept';
					reportLog($AVE_Template->get_config_vars('SETTINGS_COUNT_UPDATE'));
				}

			if (isAjax())
			{
				echo json_encode(
					array(
						'message' => $message,
						'header' => $header,
						'theme' => $theme
					)
				);
			}
			else
				{
					header('Location:index.php?do=settings&cp=' . SESSION);
				}
		}
		exit;
	}


	/**
	 * Метод, предназначенный для формирования URL
	 *
	 */
	function documentAliasCreate()
	{
		$alias  = empty($_REQUEST['alias'])  ? '' : prepare_url($_REQUEST['alias']);
		$prefix = empty($_REQUEST['prefix']) ? '' : prepare_url($_REQUEST['prefix']);
		$title  = empty($_REQUEST['title'])  ? '' : $_REQUEST['title'];
		$title  = (URL_YANDEX==true) ? y_translate($title) : prepare_url($title);

		if ($alias != $title && $alias != trim($prefix . '/' . $title, '/'))
			$alias = trim($alias . '/' . $title, '/');

		return $alias;
	}

	/**
	 * Метод, предназначенный для контроля уникальности URL
	 *
	 */
	function documentAliasCheck()
	{
		global $AVE_DB, $AVE_Template;

		$document_id = (isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) ? $_REQUEST['id'] : 0;
		$document_alias = (isset($_REQUEST['alias'])) ? $_REQUEST['alias'] : '';

		$check = (isset($_REQUEST['check']) && (bool)$_REQUEST['check'] === true) ? true : false;
		$alias_id = (isset($_REQUEST['alias_id'])) ? (int)$_REQUEST['alias_id'] : 0;

		$errors = array();

		// Если указанный URL пользователем не пустой
		if (! empty($document_alias))
		{
			// Проверяем, чтобы данный URL соответствовал требованиям
			if (preg_match(TRANSLIT_URL ? '/[^\.a-z0-9\/_-]+/' : '/^[^0-9A-Za-zА-Яа-яЁё]+$/u', $document_alias))
			{
				$errors[] = $AVE_Template->get_config_vars('DOC_URL_ERROR_SYMBOL');
			}

			// Если URL начинается с "/" - фиксируем ошибку
			if ($document_alias[0] == '/')
				$errors[] = $AVE_Template->get_config_vars('DOC_URL_ERROR_START');

			// Если суффикс URL заканчивается на "/" и URL заканчивается на "/" - фиксируем ошибку
			if (substr(URL_SUFF, 0, 1) == '/' && substr($document_alias, -1) == '/')
				$errors[] = $AVE_Template->get_config_vars('DOC_URL_ERROR_END');

			// Если в URL используются слова apage-XX, artpage-XX,page-XX,print, фиксируем ошибку, где ХХ - число
			$matches = preg_grep('/^(apage-\d+|artpage-\d+|page-\d+|print)$/i', explode('/', $document_alias));

			if (! empty($matches))
				$errors[] = $AVE_Template->get_config_vars('DOC_URL_ERROR_SEGMENT') . implode(', ', $matches);

			$and_docs = (($check === false) ? "AND Id != '" . $document_id . "'" : '');
			//$and_aliace = (($check === true) ? "AND document_id != '" . $document_id . "'" : '');
			$and_alias_id = (isset($alias_id) ? "AND id != '" . $alias_id . "'" : '');

			// Выполняем запрос к БД на получение всех URL и проверку на уникальность
			if (empty($errors))
			{
				$alias_exist = $AVE_DB->Query("
					SELECT 1
					FROM
						" . PREFIX . "_documents
					WHERE
						document_alias = '" . $document_alias . "'
						$and_docs
					LIMIT 1
				")->NumRows();

				if ($alias_exist)
					$errors[] = $AVE_Template->get_config_vars('DOC_URL_ERROR_DUPLICATES');

				$alias_exist = $AVE_DB->Query("
					SELECT 1
					FROM
						" . PREFIX . "_document_alias_history
					WHERE
						document_alias = '" . $document_alias . "'
						$and_alias_id
					LIMIT 1
				")->NumRows();

				if ($alias_exist)
					$errors[] = $AVE_Template->get_config_vars('DOC_URL_H_ERROR_DUPLICATES');

			}
		}
		else
			{  // В противном случае, если URL пустой, формируем сообщение об ошибке
				$errors[] = $AVE_Template->get_config_vars('DOC_URL_ERROR_EMTY');
			}

		// Если ошибок не найдено, формируем сообщение об успешной операции
		if (empty($errors))
		{
			return json_encode(array($AVE_Template->get_config_vars('DOC_URL_CHECK_OK') . implode(',<br />', $errors), 'accept', $check));
		}
		else
			{ // В противном случае формируем сообщение с ошибкой
				return json_encode(array($AVE_Template->get_config_vars('DOC_URL_CHECK_ER') . implode(',<br />', $errors), 'error'));
			}
	}

	/**
	 * Метод, предназначенный для
	 *
	 */
	function documentAliasHistoryList()
	{
		global $AVE_DB, $AVE_Template;

		$sql = $AVE_DB->Query("
			SELECT
				h.id,
				h.document_id,
				h.document_alias_changed,
				COUNT(h.document_id) as count,
				d.rubric_id,
				d.document_title,
				d.document_alias,
				r.rubric_title
			FROM
				" . PREFIX . "_document_alias_history AS h
			LEFT JOIN
				" . PREFIX . "_documents AS d
				ON h.document_id = d.Id
			LEFT JOIN
				" . PREFIX . "_rubrics AS r
				ON d.rubric_id = r.Id
			WHERE
				h.document_id = d.Id
			GROUP BY
				h.document_id
			ORDER BY
				h.document_alias_changed DESC
		");

		$documents = array();

		while ($row = $sql->FetchAssocArray())
		{
			array_push($documents, $row);
		}

		$AVE_Template->assign('documents', $documents);
		$AVE_Template->assign('content', $AVE_Template->fetch('documents/alias_list.tpl'));
	}

	/**
	 * Метод, предназначенный для
	 *
	 */
	function documentAliasListDoc($id)
	{
		global $AVE_DB, $AVE_Template, $AVE_Rubric;

		$document = $AVE_DB->Query("
			SELECT
				d.rubric_id,
				d.document_title,
				d.document_alias,
				r.rubric_title
			FROM
				" . PREFIX . "_documents AS d
			LEFT JOIN
				" . PREFIX . "_rubrics AS r
				ON d.rubric_id = r.Id
			WHERE
				d.Id = " . $id . "
		")->FetchRow();

		$sql = $AVE_DB->Query("
			SELECT *
			FROM
				".PREFIX."_document_alias_history
			WHERE
				document_id = '". $id ."'
		");

		$aliases = array();

		while ($row = $sql->FetchRow())
		{
			$row->document_alias_author_name = get_username_by_id($row->document_alias_author);
			array_push($aliases, $row);
		}

		$AVE_Template->assign('document', $document);
		$AVE_Template->assign('aliases', $aliases);

		switch($_REQUEST['sub'])
		{
			case 'list':
				$AVE_Template->assign('content', $AVE_Template->fetch('documents/alias_doc_list.tpl'));
				break;

			default:
				$AVE_Template->assign('content', $AVE_Template->fetch('documents/alias_doc.tpl'));
				break;
		}
	}

	/**
	 * Метод, предназначенный для
	 *
	 */
	function documentAliasNew()
	{
		global $AVE_DB, $AVE_Template;

		$sql = $AVE_DB->Query("
			INSERT
			INTO
				" . PREFIX . "_document_alias_history
			SET
				document_id              = '" . (int)$_REQUEST['doc_id'] . "',
				document_alias           = '" . trim($_REQUEST['alias']) . "',
				document_alias_author    = '" . (int)UID . "',
				document_alias_changed   = '" . time() . "'
		");

		if ($sql === false)
		{
			$message = $AVE_Template->get_config_vars('DOC_ALIASES_REP_ER_T');
			$header = $AVE_Template->get_config_vars('DOC_ALIASES_REP_ER');
			$theme = 'error';
		}
		else
			{
				$message = $AVE_Template->get_config_vars('DOC_ALIASES_REP_OK_T');
				$header = $AVE_Template->get_config_vars('DOC_ALIASES_REP_OK');
				$theme = 'accept';
			}

		if (isAjax())
		{
			echo json_encode(
				array(
					'message' => $message,
					'header' => $header,
					'theme' => $theme
				)
			);
		}
		else
			{
				header('Location:index.php?do=docs&action=aliases_doc&cp=' . SESSION);
			}
		exit;
	}

	/**
	 * Метод, предназначенный для
	 *
	 */
	function documentAliasEdit()
	{
		global $AVE_DB, $AVE_Template;

			$sql = $AVE_DB->Query("
				UPDATE
					" . PREFIX . "_document_alias_history
				SET
					document_alias  = '" . $_REQUEST['alias'] . "'
				WHERE
					id = '" . $_REQUEST['id'] . "'
			");

			if ($sql === false)
			{
				$message = $AVE_Template->get_config_vars('DOC_ALIASES_REP_ER_T_E');
				$header = $AVE_Template->get_config_vars('DOC_ALIASES_REP_ER');
				$theme = 'error';
			}
			else
				{
					$message = $AVE_Template->get_config_vars('DOC_ALIASES_REP_OK_T_E');
					$header = $AVE_Template->get_config_vars('DOC_ALIASES_REP_OK');
					$theme = 'accept';
				}

			if (isAjax())
			{
				echo json_encode(
					array(
						'message' => $message,
						'header' => $header,
						'theme' => $theme
					)
				);
			}
			else
				{
					header('Location:index.php?do=docs&action=aliases_doc&cp=' . SESSION);
				}

		exit;
	}

	/**
	 * Метод, предназначенный для
	 *
	 */
	function documentAliasSave()
	{
		global $AVE_DB, $AVE_Template;

		if (isset($_REQUEST['alias_del']))
		{
			foreach ($_REQUEST['alias_del'] as $id => $val)
			{
				$AVE_DB->Query("
					DELETE
					FROM
						" . PREFIX . "_document_alias_history
					WHERE
						id = '" . $id . "'
				");
			}
		}

		exit;
	}

	/**
	 * Метод, предназначенный для
	 *
	 */
	function documentAliasDel()
	{
		global $AVE_DB, $AVE_Template;

		if (isset($_REQUEST['alias_id']))
		{
			$AVE_DB->Query("
				DELETE
				FROM
					" . PREFIX . "_document_alias_history
				WHERE
					id = '" . $_REQUEST['alias_id'] . "'
			");
		}

		exit;
	}
}
?>
