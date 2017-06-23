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

	if (!defined('ACP') || !check_permission('document_view'))
	{
		header('Location:index.php');
		exit;
	}

	if (!(isset($_REQUEST['doc_id']) && isset($_REQUEST['type']) && isset($_REQUEST['field_id']))) exit;

	/**
	 *(int)$_REQUEST['doc_id']
	 *(int)$_REQUEST['rubric_id']
	 */
	$show = true;

	// Выполняем запрос к БД на получение данных о документе
	$document = $AVE_DB->Query("
		SELECT *
		FROM " . PREFIX . "_documents
		WHERE Id = '" . (int)$_REQUEST['doc_id'] . "'
	")->FetchRow();

	// запрещаем доступ,
	// если автору документа не разрешено изменять свои документы в рубрике
	// или пользователю не разрешено изменять все документы в рубрике
	if (is_object($document)) {
		$_REQUEST['rubric_id'] = (int)$document->rubric_id;
		if	(!
				(
					(
						isset($_SESSION['user_id']) && $document->document_author_id == $_SESSION['user_id']
						&& isset($_SESSION[$_REQUEST['rubric_id'] . '_editown']) && $_SESSION[$_REQUEST['rubric_id'] . '_editown'] == 1
					)
					|| (isset($_SESSION[$_REQUEST['rubric_id'] . '_editall']) && $_SESSION[$_REQUEST['rubric_id'] . '_editall'] == 1)
				)
			)
		{
			$show = false;
		}
	} else {
		$_REQUEST['rubric_id'] = (isset($_REQUEST['rubric_id']) && !empty($_REQUEST['rubric_id'])) ? (int)$_REQUEST['rubric_id'] : 0;
		$show = false;
	}

	// разрешаем доступ, если пользователь принадлежит группе Администраторов или имеет все права на рубрику
	if ( (defined('UGROUP') && UGROUP == 1)
		|| (isset($_SESSION[$_REQUEST['rubric_id'] . '_alles']) && $_SESSION[$_REQUEST['rubric_id'] . '_alles'] == 1) )
	{
		$show = true;
	}

	if ($show)
	{
		// Выполняем запрос к БД и получаем значение по умолчанию
		$default = $AVE_DB->Query("
			SELECT
				rubric_field_default
			FROM " . PREFIX . "_rubric_fields
			WHERE Id = '" . (int)$_REQUEST['field_id'] . "' AND rubric_id = '" . (int)$_REQUEST['rubric_id'] . "'
		")->GetCell();

		$field_value = (isset($_REQUEST['field_value']) ? (string)$_REQUEST['field_value'] : '');

		$field_function = 'get_field_' . (string)$_REQUEST['field'];
		$field_function($field_value, $_REQUEST['type'], (int)$_REQUEST['field_id'], '', 0, $x, 0, 0, $default);
	}
	else
	{
		exit;
	}

?>