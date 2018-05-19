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


	define ('START_MICROTIME', microtime());

	define ('BASE_DIR', str_replace("\\", "/", rtrim($_SERVER['DOCUMENT_ROOT'], '/')));

	if (! @filesize(BASE_DIR . '/config/db.config.php'))
	{
		header ('Location: Location:install/index.php');
		exit;
	}

	if (substr($_SERVER['REQUEST_URI'], 0, strlen('/index.php?')) != '/index.php?')
		$_SERVER['REQUEST_URI'] = str_ireplace('_','-',$_SERVER['REQUEST_URI']);

	require_once (BASE_DIR . '/inc/init.php');

	$abs_path = str_ireplace(BASE_DIR, '/', str_replace("\\", "/", dirname(dirname(__FILE__))));

	// Проверяем настройку на публикацию документов
	$publish = get_settings('use_doctime')
		? 'AND doc.document_published < UNIX_TIMESTAMP() AND doc.document_expire > UNIX_TIMESTAMP()'
		: '';

	// Начало
	$_start = 0;
	// Конец
	$_end = 2000;

	// Часть
	$parts = 1;

	$changefreq = array(
		'0' => 'always',
		'1' => 'hourly',
		'2' => 'daily',
		'3' => 'weekly',
		'4' => 'monthly',
		'5' => 'yearly',
		'6' => 'never'
	);

	if (! isset($_REQUEST['id'])):

	// Вытаскиваем кол-во документов
	$sql = "
		SELECT STRAIGHT_JOIN
			COUNT(doc.Id) AS count
		FROM
			" . PREFIX . "_documents AS doc
		LEFT JOIN
			" . PREFIX . "_rubrics AS rub
			ON rub.Id = doc.rubric_id
		LEFT JOIN
			" . PREFIX . "_rubric_templates AS tmpl
			ON tmpl.rubric_id = rub.Id
		LEFT JOIN
			" . PREFIX . "_rubric_permissions AS rubperm
			ON rubperm.rubric_id = rub.Id
		WHERE
			# Не пустой шаблон
			(rub.rubric_template NOT LIKE '' OR tmpl.template NOT LIKE '')
			# Статус документа = 1
			AND doc.document_status = 1
			# Документ не удален
			AND doc.document_deleted = 1
			$publish
			# Документ не равен 1
			AND doc.Id != 1
			# Документ не равен 404 ошибке
			AND doc.Id != " . PAGE_NOT_FOUND_ID . "
			# Документы разрешены для индексации
			AND (document_meta_robots NOT LIKE '%noindex%' or document_meta_robots NOT LIKE '%nofollow%')
			# Разрешены для просмотра гостям
			AND (rubperm.user_group_id = 2 AND rubperm.rubric_permission LIKE '%docread%')
	";

	$num = $AVE_DB->Query($sql, SITEMAP_CACHE_LIFETIME, 'sitemap')->GetCell();

	if ($num > $_end)
		$parts = ceil($num/$_end);

		header ('Content-type: text/xml');

		echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
		echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

	for ($i = 1; $i <= $parts; $i++):
?>
	<sitemap>
		<loc><?php echo HOST . '/sitemap-' . $i . '.xml'; ?></loc>
		<lastmod><?php echo date("c"); ?></lastmod>
	</sitemap>
<?php endfor;
	echo '</sitemapindex>';
	else:
?>
<?php
	if ((int)$_REQUEST['id'] > 1)
		$_start = ((int)$_REQUEST['id']-1) * $_end;

	$sql = "
		SELECT STRAIGHT_JOIN
			doc.Id,
			doc.document_alias,
			doc.document_published,
			doc.document_changed,
			doc.document_sitemap_freq,
			doc.document_sitemap_pr
		FROM
			" . PREFIX . "_documents AS doc
		LEFT JOIN
			" . PREFIX . "_rubrics AS rub
			ON rub.Id = doc.rubric_id
		LEFT JOIN
			" . PREFIX . "_rubric_templates AS tmpl
			ON tmpl.rubric_id = rub.Id
		LEFT JOIN
			" . PREFIX . "_rubric_permissions AS rubperm
			ON rubperm.rubric_id = rub.Id
		WHERE
			# Не пустой шаблон
			(rub.rubric_template NOT LIKE '' OR tmpl.template NOT LIKE '')
			# Статус документа = 1
			AND doc.document_status = 1
			# Документ не удален
			AND doc.document_deleted = 1
			$publish
			# Документ не равен 1
			AND doc.Id != 1
			# Документ не равен 404 ошибке
			AND doc.Id != " . PAGE_NOT_FOUND_ID . "
			# Документы разрешены для индексации
			AND (document_meta_robots NOT LIKE '%noindex%' or document_meta_robots NOT LIKE '%nofollow%')
			# Разрешены для просмотра гостям
			AND (rubperm.user_group_id = 2 AND rubperm.rubric_permission LIKE '%docread%')
		GROUP BY doc.Id
		ORDER BY doc.document_published ASC
		LIMIT ".$_start.",".$_end.";
	";

	$res = $AVE_DB->Query($sql, SITEMAP_CACHE_LIFETIME, 'sitemap', true, '.limit');

	if (! $res->NumRows())
	{
		report404();
		$AVE_DB->clearCurrentCache('sitemap', $sql, '.limit');
		header ($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true);
		exit;
	}

	header ('Content-type: text/xml');

	echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
	if ((int)$_REQUEST['id'] == 1):
?>
	<url>
		<loc><?php echo HOST . '/'; ?></loc>
		<lastmod><?php echo date("c", time()); ?></lastmod>
		<changefreq>weekly</changefreq>
		<priority>0.8</priority>
	</url>
<?php endif; ?>
<?php
	while($row = $res->FetchAssocArray()):
		$document_alias = $abs_path . $row['document_alias'] . URL_SUFF;
		$document_alias = HOST . str_ireplace($abs_path . '/' . URL_SUFF, '/', $document_alias);
		$date = $row["document_published"] ? date("c", $row["document_published"]) : date("c");
?>
	<url>
		<loc><?php echo $document_alias; ?></loc>
		<lastmod><?php echo $date; ?></lastmod>
		<changefreq><?php echo $changefreq[$row['document_sitemap_freq']]; ?></changefreq>
		<priority><?php echo $row['document_sitemap_pr']; ?></priority>
	</url>
<?php endwhile; ?>
</urlset>
<?php endif; ?>