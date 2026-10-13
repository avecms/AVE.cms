<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Frontend/Sitemap/Repository.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Frontend\Sitemap;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use DB;
	use App\Content\ContentTables;
	use App\Content\Documents\DocumentVisibility;

	class Repository
	{
		public static function count()
		{
			return (int) DB::query(self::baseSql('COUNT(DISTINCT doc.Id) AS total'))->getValue();
		}

		public static function page($offset, $limit)
		{
			$offset = max(0, (int) $offset);
			$limit = max(1, (int) $limit);
			return DB::query(
				self::baseSql(
					'doc.Id, doc.document_alias, doc.document_published, doc.document_changed,'
					. ' doc.document_sitemap_freq, doc.document_sitemap_pr'
				) . ' GROUP BY doc.Id ORDER BY doc.document_published ASC, doc.Id ASC LIMIT '
				. $offset . ', ' . $limit
			)->getAll() ?: array();
		}

		public static function home()
		{
			return DB::query(self::baseSql(
				'doc.Id, doc.document_alias, doc.document_published, doc.document_changed,'
					. ' doc.document_sitemap_freq, doc.document_sitemap_pr',
				true
			) . ' LIMIT 1')->getAssoc() ?: null;
		}

		protected static function baseSql($select, $home = false)
		{
			$documents = ContentTables::table('documents');
			$rubrics = ContentTables::table('rubrics');
			$templates = ContentTables::table('rubric_templates');
			$permissions = ContentTables::table('rubric_permissions');
			$publication = \App\Frontend\PublicSettings::get('use_doctime')
				? ' AND doc.document_published <= UNIX_TIMESTAMP()'
					. " AND (doc.document_expire = '0' OR doc.document_expire >= UNIX_TIMESTAMP())"
				: '';

			return 'SELECT ' . $select
				. ' FROM `' . $documents . '` doc'
				. ' INNER JOIN `' . $rubrics . '` rub ON rub.Id = doc.rubric_id'
				. " WHERE doc.document_status = '1' AND doc.document_deleted = '0'"
				. " AND doc.document_in_sitemap = 1"
				. ' AND ' . DocumentVisibility::publicSql('doc', 'rub')
				. ($home ? ' AND doc.Id = 1' : ' AND doc.Id != 1')
				. ' AND doc.Id != ' . (defined('PAGE_NOT_FOUND_ID') ? (int) PAGE_NOT_FOUND_ID : (int) \App\Frontend\PublicSettings::get('page_not_found_id', 0))
				. " AND doc.document_meta_robots NOT LIKE '%noindex%'"
				. " AND (rub.rubric_template != '' OR EXISTS (SELECT 1 FROM `" . $templates . "` rt"
				. " WHERE rt.rubric_id = rub.Id AND rt.template != ''))"
				. ' AND EXISTS (SELECT 1 FROM `' . $permissions . '` rp WHERE rp.rubric_id = rub.Id'
				. " AND rp.user_group_id = '2' AND rp.rubric_permission LIKE '%docread%')"
				. $publication;
		}
	}
