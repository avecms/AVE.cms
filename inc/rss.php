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

define('BASE_DIR', str_replace("\\", "/", dirname(dirname(__FILE__))));

require(BASE_DIR . '/inc/init.php');

if (!(isset($_GET['id']) && is_numeric($_GET['id']))) exit;

// Выполняем запрос к БД и выгребаем все параметры для данного канала
$rss_settings = $AVE_DB->Query("
	SELECT
		rss.*,
		rubric_title
	FROM
		" . PREFIX . "_module_rss AS rss
	LEFT JOIN
		" . PREFIX . "_rubrics AS rub
			ON rub.Id = rss.rss_rubric_id
	WHERE
		rss.id = '" . $_GET['id'] . "'
")->FetchRow();

if ($rss_settings !== false)
{
	$rss_settings->rss_site_name = htmlspecialchars($rss_settings->rss_site_name, ENT_QUOTES);
	$rss_settings->rss_site_description = htmlspecialchars($rss_settings->rss_site_description, ENT_QUOTES);

	$doctime = get_settings('use_doctime')
		? ("AND document_published <= " . time() . " AND (document_expire = 0 OR document_expire >= " . time() . ")") : '';

	// Получаем ID, URL и Дату публикации для документов, которые соответсвуют нашей рубрики
	// Количество выборки ограничиваем значением установленным для канала
	$sql_doc = $AVE_DB->Query("
		SELECT
			Id,
			document_title,
			document_alias,
			document_published
		FROM " . PREFIX . "_documents
		WHERE Id != 1
		AND Id != '" . PAGE_NOT_FOUND_ID . "'
		AND rubric_id = '" . $rss_settings->rss_rubric_id . "'
		AND document_status = '1'
		AND document_deleted != '1'
		" . $doctime . "
		ORDER BY document_published DESC, Id DESC
		LIMIT " . $rss_settings->rss_item_on_page
	);

	// Формируем массивы, которые будут хранить инфу
	$rss_item  = array();
	$rss_items = array();

	// Выполянем обработку полученных из БД данных
	while ($row_doc = $sql_doc->FetchRow())
	{
		$sql_fields = $AVE_DB->Query("
			SELECT
				rubric_field_id,
				field_value
			FROM " . PREFIX . "_document_fields
			WHERE document_id = '" . $row_doc->Id . "'
			AND (rubric_field_id = '" . $rss_settings->rss_title_id . "'
				OR rubric_field_id = '" . $rss_settings->rss_description_id . "')
	    ");
		while ($row_fields = $sql_fields->FetchRow())
		{
			if ($row_fields->rubric_field_id == $rss_settings->rss_title_id)
			{
				$rss_item['Title'] = $row_fields->field_value;
			}

			if ($row_fields->rubric_field_id == $rss_settings->rss_description_id)
			{
				if ($rss_settings->rss_description_lenght == 0)
				{
					$teaser = explode('<a name="more"></a>', $row_fields->field_value);
					$rss_item['description'] = $teaser[0];
				}
				else
				{
					if (mb_strlen($row_fields->field_value) > $rss_settings->rss_description_lenght)
					{
						$rss_item['description'] = mb_substr($row_fields->field_value, 0, $rss_settings->rss_description_lenght) . '…';
					}
					else
					{
						$rss_item['description'] = $row_fields->field_value;
					}
				}
				$rss_item['description'] = parse_hide($rss_item['description']);
			}
		}

		$link_doc = !empty($row_doc->document_alias) ? $row_doc->document_alias : prepare_url($row_doc->document_title);
		$link = rewrite_link('index.php?id=' . $row_doc->Id . '&amp;doc=' . $link_doc);
		$rss_item['link'] = $rss_settings->rss_site_url . mb_substr($link, mb_strlen(ABS_PATH));

		$rss_item['pubDate'] = $row_doc->document_published ? date('r', $row_doc->document_published) : date('r', time());

		array_push($rss_items, $rss_item);
	}
}

// Ну а тут собственно шлем заголовок, что у нас документ XML и в путь... выводим данные
header("Content-Type: application/xml");
header("Cache-Control: no-cache");
header("Pragma: no-cache");
echo '<?xml version="1.0" encoding="utf8"?>';
?>

<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
<channel>
<title><?php echo $rss_settings->rss_site_name; ?></title>
<link><?php echo $rss_settings->rss_site_url; ?></link>
<language>ru-ru</language>
<description><?php echo $rss_settings->rss_site_description; ?></description>
<category><![CDATA[<?php echo $rss_settings->rubric_title; ?>]]></category>
<generator>AVE.cms</generator>
<?php foreach($rss_items as $rss_item):?>
<item>
	<title><![CDATA[<?php echo $rss_item['Title']; ?>]]></title>
	<guid isPermaLink="true"><?php echo $rss_item['link']; ?></guid>
	<link><?php echo $rss_item['link']; ?></link>
	<description><![CDATA[<?php echo $rss_item['description']; ?>]]></description>
	<pubDate><?php echo $rss_item['pubDate']; ?></pubDate>
</item>
<?php endforeach; ?>
</channel>
</rss>
