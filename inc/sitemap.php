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

	header ('Content-type: text/xml');

	define ('START_MICROTIME', microtime());

	define ('BASE_DIR', str_replace("\\", "/", rtrim($_SERVER['DOCUMENT_ROOT'],'/')));

	if (! @filesize(BASE_DIR . '/config/db.config.php'))
	{
		header ('Location: Location:install/index.php');
		exit;
	}

	if (substr($_SERVER['REQUEST_URI'], 0, strlen('/index.php?')) != '/index.php?')
	{
		$_SERVER['REQUEST_URI'] = str_ireplace('_','-',$_SERVER['REQUEST_URI']);
	}

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

	// Вытаскиваем кол-во документов
	$sql = "
		SELECT STRAIGHT_JOIN SQL_CALC_FOUND_ROWS
			COUNT(doc.Id) AS count
		FROM
			" . PREFIX . "_documents doc
		LEFT JOIN
			" . PREFIX . "_rubrics rub
			ON rub.Id = doc.rubric_id
		LEFT JOIN
			" . PREFIX . "_rubric_permissions rubperm
			ON rubperm.rubric_id = doc.rubric_id
		WHERE
			rub.rubric_template NOT LIKE ''
			AND doc.document_status = 1
			AND doc.document_deleted = 1
			$publish
			AND doc.Id != " . PAGE_NOT_FOUND_ID . "
			AND (document_meta_robots NOT LIKE '%noindex%' or document_meta_robots NOT LIKE '%nofollow%')
			AND (rubperm.user_group_id = 2 AND rubperm.rubric_permission LIKE '%docread%')
	";

	$num = $AVE_DB->Query($sql, SITEMAP_CACHE_LIFETIME, 'sitemap')->GetCell();

	if ($num > $_end)
		$parts = ceil($num/$_end);

	if (! isset($_REQUEST['id'])):
		echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
		echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

	for ($i = 1; $i <= $parts; $i++):
?>
	<sitemap>
		<loc><?= HOST . '/sitemap-' . $i . '.xml'; ?></loc>
		<lastmod><?= date("c"); ?></lastmod>
	</sitemap>
<? endfor;
	echo '</sitemapindex>';
	else:
?>
<?
	if ((int)$_REQUEST['id'] > 1)
		$_start = ((int)$_REQUEST['id']-1) * $_end;

	$sql = "
		SELECT STRAIGHT_JOIN SQL_CALC_FOUND_ROWS
			doc.Id,
			doc.document_alias,
			doc.document_published,
			doc.document_changed,
			doc.document_sitemap_freq,
			doc.document_sitemap_pr
		FROM " . PREFIX . "_documents doc
		LEFT JOIN " . PREFIX . "_rubrics rub
			ON rub.Id = doc.rubric_id
		LEFT JOIN " . PREFIX . "_rubric_permissions rubperm
			ON rubperm.rubric_id = doc.rubric_id
		WHERE
			rub.rubric_template NOT LIKE ''
			AND doc.document_status = 1
			AND doc.document_deleted = 1
			$publish
			AND doc.Id != 1
			AND doc.Id != " . PAGE_NOT_FOUND_ID . "
			AND (document_meta_robots NOT LIKE '%noindex%' or document_meta_robots NOT LIKE '%nofollow%')
			AND (rubperm.user_group_id = 2 AND rubperm.rubric_permission LIKE '%docread%')
		ORDER BY doc.document_published ASC
		LIMIT ".$_start.",".$_end."
	";

	$res = $AVE_DB->Query($sql, SITEMAP_CACHE_LIFETIME, 'sitemap');

	echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
	if ((int)$_REQUEST['id'] == 1):
?>
	<url>
		<loc><? echo HOST . '/'; ?></loc>
		<lastmod><? echo date("c", time()); ?></lastmod>
		<changefreq>weekly</changefreq>
		<priority>0.8</priority>
	</url>
<? endif; ?>
<?
	while($row = $res->FetchAssocArray()):
		$document_alias = $abs_path . $row['document_alias'] . URL_SUFF;
		$document_alias = HOST . str_ireplace($abs_path . '/' . URL_SUFF, '/', $document_alias);
		$date = $row["document_published"] ? date("c", $row["document_published"]) : date("c");
?>
	<url>
		<loc><? echo $document_alias; ?></loc>
		<lastmod><? echo $date; ?></lastmod>
		<changefreq><? echo $changefreq[$row['document_sitemap_freq']]; ?></changefreq>
		<priority><? echo $row['document_sitemap_pr']; ?></priority>
	</url>
<? endwhile; ?>
</urlset>
<? endif; ?>