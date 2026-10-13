<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Frontend/ContentCollectionTags.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Frontend;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Common\Twig;
	use App\Content\ContentTables;
	use App\Content\Documents\DocumentSnapshotRepository;
	use App\Content\Presentation\DocumentPresentationBridge;
	use App\Helpers\Request;
	use DB;

	/** Generic public list for document-based sections such as news and articles. */
	class ContentCollectionTags
	{
		public static function render(array $matches, array $context = array())
		{
			$rubricId = isset($matches[1]) ? (int) $matches[1] : 0;
			$limit = isset($matches[2]) ? (int) $matches[2] : 12;
			$limit = max(1, min(48, $limit));
			$paginate = !isset($matches[3]) || (int) $matches[3] === 1;
			if ($rubricId <= 0) { return ''; }

			$page = $paginate ? self::page() : 1;
			$where = array('d.rubric_id=%i', 'd.document_status=%s', 'd.document_deleted=%s');
			$params = array($rubricId, '1', '0');
			if (PublicSettings::get('use_doctime')) {
				$where[] = 'd.document_published<=UNIX_TIMESTAMP()';
				$where[] = '(d.document_expire=0 OR d.document_expire>=UNIX_TIMESTAMP())';
			}

			$fieldAlias = isset($matches[4]) ? trim((string) $matches[4]) : '';
			$fieldValue = isset($matches[5]) ? rawurldecode((string) $matches[5]) : '';
			if ($fieldAlias !== '') {
				$fieldId = self::fieldId($rubricId, $fieldAlias);
				if ($fieldId <= 0) { return ''; }
				$where[] = 'EXISTS (SELECT 1 FROM %b cf'
					. ' WHERE cf.document_id=d.Id AND cf.rubric_field_id=%i AND cf.field_value=%s)';
				$params[] = ContentTables::table('document_fields');
				$params[] = $fieldId;
				$params[] = $fieldValue;
			}

			$table = ContentTables::table('documents');
			$condition = implode(' AND ', $where);
			$total = (int) call_user_func_array(array('DB', 'query'), array_merge(
				array('SELECT COUNT(*) FROM %b d WHERE ' . $condition, $table),
				$params
			))->getValue();
			$pages = max(1, (int) ceil($total / $limit));
			if ($paginate) { PublicPageContext::recordPagination(PublicPageContext::documentId(), 'page', $pages); }
			$page = min($page, $pages);
			$sql = 'SELECT d.Id,d.document_title,d.document_alias,d.document_excerpt,d.document_published,d.document_count_view'
				. ' FROM %b d WHERE ' . $condition . ' ORDER BY d.document_published DESC,d.Id DESC'
				. ' LIMIT ' . (($page - 1) * $limit) . ',' . $limit;
			$rows = call_user_func_array(array('DB', 'query'), array_merge(array($sql, $table), $params))->getAll() ?: array();
			$snapshots = (new DocumentSnapshotRepository())->findMany(array_column($rows, 'Id'));
			$items = array();
			foreach ($rows as $row) {
				$id = (int) $row['Id'];
				$fields = self::fields(isset($snapshots[$id]['fields']) ? $snapshots[$id]['fields'] : array());
				$items[] = array(
					'id' => $id,
					'title' => self::text(self::first($fields, array('title', 'name'), $row['document_title'])),
					'url' => '/' . ltrim((string) $row['document_alias'], '/'),
					'target_url' => self::text(self::value($fields, 'link', '')) ?: '/' . ltrim((string) $row['document_alias'], '/'),
					'excerpt' => self::text(self::first($fields, array('teaser', 'excerpt', 'description'), $row['document_excerpt'])),
					'image' => self::media(self::value($fields, 'image', array())),
					'date_label' => self::text(self::value($fields, 'date', '')),
					'published_at' => (int) $row['document_published'],
					'views' => (int) $row['document_count_view'],
				);
			}

			$document = isset($context['document']) ? $context['document'] : null;
			$alias = is_object($document) && isset($document->document_alias) ? (string) $document->document_alias : '';
			$pagination = $paginate ? self::pagination($alias, $page, $pages) : array();
			$presentation = DocumentPresentationBridge::renderList(
				'content_list',
				$rows,
				array('rubric' => (string) $rubricId, 'module' => 'content'),
				array(
					'source' => 'content_list',
					'rubric_id' => $rubricId,
					'total' => $total,
					'page' => $page,
					'pages' => $pages,
					'pagination' => $pagination,
				)
			);
			if ($presentation !== null) { return $presentation; }

			return Twig::twig()->render('@content_public/list.twig', array(
				'rubric_id' => $rubricId,
				'items' => $items,
				'total' => $total,
				'page' => $page,
				'pages' => $pages,
				'pagination' => $pagination,
			));
		}

		protected static function fieldId($rubricId, $alias)
		{
			return (int) DB::query(
				'SELECT Id FROM %b WHERE rubric_id=%i AND rubric_field_alias=%s LIMIT 1',
				ContentTables::table('rubric_fields'),
				(int) $rubricId,
				(string) $alias
			)->getValue();
		}

		protected static function page()
		{
			$page = max(1, (int) Request::get('page', 1));
			$path = parse_url(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '', PHP_URL_PATH);
			if (preg_match('#/page-(\d+)/?$#', (string) $path, $match)) { $page = max(1, (int) $match[1]); }
			return $page;
		}

		protected static function pagination($alias, $current, $last)
		{
			if ($last <= 1) { return array(); }
			$from = max(1, $current - 2);
			$to = min($last, $current + 2);
			$items = array();
			for ($page = $from; $page <= $to; $page++) {
				$items[] = array('page' => $page, 'current' => $page === $current, 'url' => self::pageUrl($alias, $page));
			}

			return array(
				'items' => $items,
				'previous' => $current > 1 ? self::pageUrl($alias, $current - 1) : '',
				'next' => $current < $last ? self::pageUrl($alias, $current + 1) : '',
			);
		}

		protected static function pageUrl($alias, $page)
		{
			$url = '/' . trim((string) $alias, '/');
			return $page > 1 ? $url . '/page-' . (int) $page : $url;
		}

		protected static function fields($fields)
		{
			$out = array();
			foreach (is_array($fields) ? $fields : array() as $field) {
				if (is_array($field) && !empty($field['alias'])) { $out[(string) $field['alias']] = $field; }
			}

			return $out;
		}

		protected static function value(array $fields, $alias, $default)
		{
			return isset($fields[$alias]) && array_key_exists('value', $fields[$alias]) ? $fields[$alias]['value'] : $default;
		}

		protected static function first(array $fields, array $aliases, $default)
		{
			foreach ($aliases as $alias) {
				$value = self::value($fields, $alias, '');
				if (is_scalar($value) && trim((string) $value) !== '') { return $value; }
			}

			return $default;
		}

		protected static function media($value)
		{
			$url = is_array($value) && isset($value['url']) ? $value['url'] : (is_scalar($value) ? $value : '');
			$url = trim((string) $url);
			if ($url === '') { return ''; }
			$url = '/' . ltrim($url, '/');
			if (strpos($url, '/uploads/') === 0 && defined('BASEPATH') && !is_file(BASEPATH . $url)) {
				return '';
			}

			return $url;
		}

		protected static function text($value)
		{
			$value = is_scalar($value) ? (string) $value : '';
			return trim(html_entity_decode(stripslashes(strip_tags($value)), ENT_QUOTES, 'UTF-8'));
		}
	}
