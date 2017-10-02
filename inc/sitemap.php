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

	header("Content-type: text/xml");
	@date_default_timezone_set('Europe/Moscow');

	define('START_MICROTIME', microtime());

	define('BASE_DIR', str_replace("\\", "/", rtrim($_SERVER['DOCUMENT_ROOT'],'/')));

	if (! @filesize(BASE_DIR . '/inc/db.config.php'))
	{
		header('Location: Location:install/index.php');
		exit;
	}

	if (substr($_SERVER['REQUEST_URI'], 0, strlen('/index.php?')) != '/index.php?')
	{
		$_SERVER['REQUEST_URI'] = str_ireplace('_', '-', $_SERVER['REQUEST_URI']);
	}

	require_once (BASE_DIR . '/inc/init.php');

	$abs_path = str_ireplace(BASE_DIR, '/', str_replace("\\", "/", dirname(dirname(__FILE__))));

	if (isset ($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
	{
		$domain = 'https://' . $_SERVER['SERVER_NAME'];
	}
	else
		{
			$domain = 'http://' . $_SERVER['SERVER_NAME'];
		}

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php
	$publish = get_settings('use_doctime')
		? 'AND doc.document_expire > UNIX_TIMESTAMP()'
		: '';

	$sql = "
			SELECT
				doc.Id,
				doc.document_alias,
				doc.document_changed,
				doc.document_sitemap_freq,
				doc.document_sitemap_pr
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
			ORDER BY
				doc.Id ASC, doc.document_changed DESC
	";

	$changefreq = array(
		'0' => 'always',
		'1' => 'hourly',
		'2' => 'daily',
		'3' => 'weekly',
		'4' => 'monthly',
		'5' => 'yearly',
		'6' => 'never'
	);

	$res = $AVE_DB->Query($sql);

	while ($row = $res->FetchAssocArray()):
		$document_alias = $abs_path . $row['document_alias'] . URL_SUFF;
		$document_alias = $domain . str_ireplace($abs_path . '/' . URL_SUFF, '/', $document_alias);
		$date = $row["document_changed"] ? date("Y-m-d", $row["document_changed"]) : date("Y-m-d");
?>
	<url>
		<loc><?php echo $document_alias; ?></loc>
		<lastmod><?php echo $date; ?></lastmod>
		<changefreq><?php echo $changefreq[$row['document_sitemap_freq']]; ?></changefreq>
		<priority><?php echo $row['document_sitemap_pr']; ?></priority>
	</url>
<?php endwhile; ?>
</urlset>