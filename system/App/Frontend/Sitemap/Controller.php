<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Frontend/Sitemap/Controller.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Frontend\Sitemap;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	class Controller
	{
		const PAGE_SIZE = 2000;

		public function sitemap(array $params = array())
		{
			return self::handle(null);
		}

		public function page(array $params = array())
		{
			return self::handle(isset($params['part']) ? $params['part'] : null);
		}

		public static function handle($part = null)
		{
			header('Content-Type: application/xml; charset=UTF-8');
			if ($part === null || $part === '') {
				return self::index();
			}

			$part = (int) $part;
			if ($part < 1) {
				http_response_code(404);
				return self::emptySet();
			}

			$rows = Repository::page(($part - 1) * self::PAGE_SIZE, self::PAGE_SIZE);
			if (!$rows && $part !== 1) {
				http_response_code(404);
				return self::emptySet();
			}

			return self::urlSet($rows, $part === 1);
		}

		protected static function index()
		{
			$parts = max(1, (int) ceil(Repository::count() / self::PAGE_SIZE));
			$xml = self::declaration() . "\n<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
			for ($part = 1; $part <= $parts; $part++) {
				$xml .= "  <sitemap><loc>" . self::escape(rtrim(HOST, '/') . '/sitemap-' . $part . '.xml')
					. "</loc></sitemap>\n";
			}

			return $xml . '</sitemapindex>';
		}

		protected static function urlSet(array $rows, $includeHome)
		{
			$frequencies = array('always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never');
			$xml = self::declaration() . "\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
			if ($includeHome) {
				$home = Repository::home();
				if ($home) { array_unshift($rows, $home); }
			}

			foreach ($rows as $row) {
				$row = (array) $row;
				$frequency = isset($frequencies[(int) $row['document_sitemap_freq']])
					? $frequencies[(int) $row['document_sitemap_freq']]
					: 'weekly';
				$changed = !empty($row['document_changed']) ? (int) $row['document_changed'] : (int) $row['document_published'];
				$xml .= self::url(
					(int) $row['Id'] === 1 ? rtrim(HOST, '/') . '/' : rtrim(HOST, '/') . '/' . ltrim((string) $row['document_alias'], '/') . URL_SUFF,
					$changed,
					$frequency,
					(string) $row['document_sitemap_pr']
				);
			}

			return $xml . '</urlset>';
		}

		protected static function url($location, $timestamp, $frequency, $priority)
		{
			return '  <url><loc>' . self::escape($location) . '</loc>'
				. ($timestamp > 0 ? '<lastmod>' . date('c', $timestamp) . '</lastmod>' : '')
				. '<changefreq>' . self::escape($frequency) . '</changefreq><priority>'
				. self::escape($priority) . "</priority></url>\n";
		}

		protected static function emptySet()
		{
			return self::declaration() . "\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"></urlset>";
		}

		protected static function declaration()
		{
			return '<?xml version="1.0" encoding="UTF-8"?>';
		}

		protected static function escape($value)
		{
			return htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');
		}
	}
