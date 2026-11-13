<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Settings/Model.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Adminx\Settings;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Common\Cache;
	use App\Common\Settings;
	use App\Common\Lock;
	use App\Common\Session\SessionDB;
	use App\Common\WebMaintenance;
	use App\Common\FileCacheInvalidator;
	use App\Common\SystemTables;
	use App\Content\CatalogTables;
	use App\Content\ContentTables;
	use App\Content\PublicShellTables;
	use App\Frontend\PublicSettings;
	use App\Frontend\ThemeSettings;
	use DB;

	class Model
	{
		public static function countriesTable()
		{
			return SystemTables::table('countries');
		}

		public static function paginationsTable()
		{
			return SystemTables::table('paginations');
		}

		public static function value($key, $default = '')
		{
			return Settings::get($key, $default);
		}

		public static function saveMany(array $input)
		{
			$defs = Schema::definitions();
			$errors = array();
			$values = array();

			foreach ($defs as $key => $definition) {
				if (!array_key_exists($key, $input)) {
					continue;
				}

				$raw = $input[$key];
				$result = self::validate($key, $definition, $raw);
				if (!$result['success']) {
					$errors[$key] = $result['message'];
					continue;
				}

				$values[$key] = $result;
			}

			if (!empty($errors)) {
				return array('success' => false, 'errors' => $errors, 'saved' => 0);
			}

			$publicValues = array();
			foreach ($values as $key => $result) {
				Settings::set($key, $result['value'], $result['store_type']);
				$definition = isset($defs[$key]) ? $defs[$key] : array();
				if (!array_key_exists('public_shell', $definition) || $definition['public_shell'] !== false) {
					$publicValues[$key] = $result['store_type'] === 'bool'
						? ($result['value'] ? '1' : '0')
						: $result['value'];
				}
			}

			if ($publicValues) {
				DB::Update(PublicShellTables::table('settings'), $publicValues, 'Id = %i', 1);
				PublicSettings::reset();
			}

			DB::clearTags(array('settings'));
			FileCacheInvalidator::publicPresentation();

			return array('success' => true, 'errors' => array(), 'saved' => count($values));
		}

		public static function validate($key, array $definition, $raw)
		{
			$type = isset($definition['type']) ? $definition['type'] : 'string';
			if ($type === 'bool') {
				$raw = $raw === null ? '0' : $raw;
			}

			if (!empty($definition['required']) && trim((string) $raw) === '') {
				return array('success' => false, 'message' => 'Заполните поле');
			}

			if ($type === 'int') {
				$value = (int) $raw;
				if (isset($definition['min']) && $value < (int) $definition['min']) {
					return array('success' => false, 'message' => 'Минимум: ' . (int) $definition['min']);
				}

				if (isset($definition['max']) && $value > (int) $definition['max']) {
					return array('success' => false, 'message' => 'Максимум: ' . (int) $definition['max']);
				}

				return array('success' => true, 'value' => $value, 'store_type' => 'int');
			}

			if ($type === 'bool') {
				return array('success' => true, 'value' => in_array((string) $raw, array('1', 'true', 'on'), true), 'store_type' => 'bool');
			}

			if ($type === 'section_order') {
				$decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);
				if (!is_array($decoded)) {
					return array('success' => false, 'message' => 'Не удалось прочитать порядок секций');
				}

				$options = isset($definition['options']) && is_array($definition['options']) ? $definition['options'] : array();
				$value = ThemeSettings::sectionOrder($decoded, $options);
				return array('success' => true, 'value' => array_map(function ($item) {
					return array('code' => $item['code'], 'visible' => !empty($item['visible']));
				}, $value), 'store_type' => 'json');
			}

			$value = (string) $raw;

			if (($type === 'select' || !empty($definition['options'])) && !empty($definition['options'])) {
				$allowed = array_keys($definition['options']);
				if (!in_array($value, $allowed, true)) {
					return array('success' => false, 'message' => 'Недопустимое значение');
				}
			}

			if ($type === 'email' && $value !== '' && !preg_match('/^[^@\s]+@[^@\s]+$/', $value)) {
				return array('success' => false, 'message' => 'Некорректный email');
			}

			if (isset($definition['max']) && function_exists('mb_strlen') && mb_strlen($value, 'UTF-8') > (int) $definition['max']) {
				return array('success' => false, 'message' => 'Максимум символов: ' . (int) $definition['max']);
			}

			$storeType = in_array($type, array('text', 'password', 'email', 'select'), true) ? 'string' : $type;
			return array('success' => true, 'value' => $value, 'store_type' => $storeType);
		}

		public static function countries()
		{
			try {
				return DB::query(
					'SELECT Id, country_code, country_name, country_status, country_eu FROM ' . self::countriesTable() . ' ORDER BY country_status ASC, country_name ASC'
				)->getAll();
			} catch (\Throwable $e) {
				return array();
			}
		}

		public static function saveCountries(array $input)
		{
			if (empty($input['country_name']) || !is_array($input['country_name'])) {
				return 0;
			}

			$saved = 0;
			foreach ($input['country_name'] as $id => $name) {
				$id = (int) $id;
				if ($id <= 0) {
					continue;
				}

				$status = isset($input['country_status'][$id]) && (string) $input['country_status'][$id] === '1' ? '1' : '2';
				$eu = isset($input['country_eu'][$id]) && (string) $input['country_eu'][$id] === '1' ? '1' : '2';
				DB::Update(self::countriesTable(), array(
					'country_name' => trim((string) $name),
					'country_status' => $status,
					'country_eu' => $eu,
				), 'Id = %i', $id);
				$saved++;
			}

			DB::clearTags(array('settings', 'countries'));
			return $saved;
		}

		public static function paginations()
		{
			try {
				return DB::query(
					'SELECT id, pagination_name FROM ' . self::paginationsTable() . ' ORDER BY id ASC'
				)->getAll();
			} catch (\Throwable $e) {
				return array();
			}
		}

		public static function pagination($id)
		{
			return DB::query('SELECT * FROM ' . self::paginationsTable() . ' WHERE id = %i LIMIT 1', (int) $id)->getAssoc();
		}

		public static function savePagination($id, array $input)
		{
			$data = self::paginationInput($input);
			if ((int) $id > 0) {
				DB::Update(self::paginationsTable(), $data, 'id = %i', (int) $id);
				DB::clearTags(array('settings', 'paginations'));
				return (int) $id;
			}

			DB::Insert(self::paginationsTable(), $data);
			DB::clearTags(array('settings', 'paginations'));
			return (int) DB::insertId();
		}

		public static function deletePagination($id)
		{
			$id = (int) $id;
			if ($id <= 1) {
				return false;
			}

			DB::Delete(self::paginationsTable(), 'id = %i', $id);
			DB::clearTags(array('settings', 'paginations'));
			return true;
		}

		public static function cacheSources()
		{
			return array(
				'twig' => array('label' => 'Twig', 'path' => BASEPATH . '/tmp/cache/twig'),
				'templates' => array('label' => 'Шаблоны страниц', 'path' => BASEPATH . '/tmp/cache/tpl'),
				'database' => array('label' => 'Данные и запросы', 'path' => BASEPATH . '/tmp/cache/sql'),
				'snapshots' => array('label' => 'JSON-снимки документов', 'path' => BASEPATH . '/tmp/cache/content-snapshots'),
				'modules' => array('label' => 'Публичные модули', 'path' => BASEPATH . '/tmp/cache/module'),
				'feeds' => array('label' => 'Товарные фиды', 'path' => BASEPATH . '/tmp/cache/feeds'),
			);
		}

		public static function cacheRows()
		{
			$rows = array();
			foreach (self::cacheSources() as $code => $source) {
				$bytes = self::dirSize($source['path']);
				$rows[] = array(
					'code' => $code,
					'label' => $source['label'],
					'path' => str_replace(BASEPATH . '/', '', $source['path']),
					'bytes' => $bytes,
					'size' => self::formatBytes($bytes),
				);
			}

			return $rows;
		}

		public static function clearCacheSource($code)
		{
			$sources = self::cacheSources();
			$code = (string) $code;
			if (!isset($sources[$code])) {
				return false;
			}

			self::removeDirContents($sources[$code]['path']);
			Cache::forget('adminx.cache.sizes.' . md5((string) ADMINX_BASE));
			return array(
				'code' => $code,
				'size' => self::formatBytes(self::dirSize($sources[$code]['path'])),
			);
		}

		public static function maintenanceCounts()
		{
			$revisionSources = self::revisionSources();
			$revisionCount = 0;
			$revisionSummary = array();
			foreach ($revisionSources as $source) {
				$revisionCount += $source['count'];
				$revisionSummary[] = array(
					'code' => $source['code'],
					'label' => $source['label'],
					'count' => $source['count'],
				);
			}

			return array(
				'revisions' => $revisionCount,
				'revision_sources' => $revisionSummary,
				'views' => self::tableCount(ContentTables::table('view_count')),
			);
		}

		public static function publicSessionStatus()
		{
			$previousMode = DB::$throw_exception_on_error;
			DB::$throw_exception_on_error = true;
			try {
				return array_merge(array('available' => true, 'error' => ''), SessionDB::expirationStats(), WebMaintenance::sessionCleanupStatus());
			} catch (\Throwable $e) {
				return array('available' => false, 'error' => $e->getMessage());
			} finally {
				DB::$throw_exception_on_error = $previousMode;
			}
		}

		public static function clearMaintenance($target, $cutoff = 0)
		{
			if ((string) $target === 'sessions') {
				if (DB::$transaction_in_progress) { throw new \RuntimeException('Очистка сессий недоступна внутри транзакции.'); }
				$cutoff = (int) $cutoff > 0 ? min(time(), (int) $cutoff) : time();
				$previousMode = DB::$throw_exception_on_error;
				DB::$throw_exception_on_error = true;
				try {
					return Lock::run('web-maintenance:sessions:v1:' . PublicShellTables::table('sessions'), function () use ($cutoff) {
						$count = SessionDB::pruneExpired(1000, $cutoff);
						return array('target' => 'sessions', 'count' => $count, 'done' => !SessionDB::hasExpired($cutoff), 'cutoff' => $cutoff);
					}, 0.0, true);
				} finally {
					DB::$throw_exception_on_error = $previousMode;
				}
			}

			$tables = array(
				'views' => ContentTables::table('view_count'),
			);
			$target = (string) $target;
			if ($target === 'revisions') {
				$count = 0;
				$cleared = array();
				foreach (self::revisionSources() as $source) {
					$count += $source['count'];
					DB::query('TRUNCATE TABLE ' . $source['table']);
					$cleared[] = array(
						'code' => $source['code'],
						'label' => $source['label'],
						'count' => $source['count'],
					);
				}

				return array('target' => $target, 'count' => $count, 'sources' => $cleared);
			}

			if (!isset($tables[$target])) {
				return false;
			}

			$count = self::tableCount($tables[$target]);
			DB::query('TRUNCATE TABLE ' . $tables[$target]);
			return array('target' => $target, 'count' => $count);
		}

		protected static function revisionSources()
		{
			$definitions = array(
				array('code' => 'documents', 'label' => 'Документы', 'table' => ContentTables::table('document_rev')),
				array('code' => 'blocks', 'label' => 'Блоки', 'table' => ContentTables::table('sysblock_revisions')),
				array('code' => 'templates', 'label' => 'Шаблоны', 'table' => ContentTables::table('template_revisions')),
				array('code' => 'rubrics', 'label' => 'Рубрики', 'table' => ContentTables::table('rubric_schema_revisions')),
				array('code' => 'themes', 'label' => 'Темы', 'table' => SystemTables::table('theme_asset_revisions')),
				array('code' => 'presentations', 'label' => 'Представления', 'table' => ContentTables::table('presentation_revisions')),
				array('code' => 'catalog_cards', 'label' => 'Карточки товаров', 'table' => CatalogTables::table('catalog_card_template_revisions')),
				array('code' => 'catalog_filters', 'label' => 'Фильтры каталога', 'table' => CatalogTables::table('catalog_filter_template_revisions')),
			);

			$sources = array();
			foreach ($definitions as $definition) {
				if (!self::tableExists($definition['table'])) {
					continue;
				}

				$definition['count'] = self::tableCount($definition['table']);
				$sources[] = $definition;
			}

			return $sources;
		}

		protected static function tableExists($table)
		{
			try {
				return (int) DB::query(
					'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=%s',
					(string) $table
				)->getValue() > 0;
			} catch (\Throwable $e) {
				return false;
			}
		}

		public static function systemFiles()
		{
			$out = array();
			foreach (self::systemFileDefinitions() as $code => $definition) {
				$path = $definition['path'];
				$content = is_file($path) ? (string) file_get_contents($path) : '';
				$out[$code] = array(
					'code' => $code,
					'label' => $definition['label'],
					'description' => $definition['description'],
					'path' => str_replace(BASEPATH . '/', '', $path),
					'mode' => $definition['mode'],
					'content' => $content,
					'bytes' => strlen($content),
					'writable' => (is_file($path) && is_writable($path)) || (!is_file($path) && is_writable(dirname($path))),
				);
			}

			return $out;
		}

		public static function systemFile($code)
		{
			$files = self::systemFiles();
			return isset($files[$code]) ? $files[$code] : null;
		}

		public static function saveSystemFile($code, $content)
		{
			$definitions = self::systemFileDefinitions();
			$code = (string) $code;
			if (!isset($definitions[$code])) {
				throw new \RuntimeException('Неизвестный системный файл');
			}

			$content = str_replace(array("\r\n", "\r"), "\n", (string) $content);
			if ($code === 'custom') {
				if (!preg_match('/^\s*<\?php\b/', $content)) {
					throw new \RuntimeException('Файл должен начинаться с <?php');
				}

				try {
					token_get_all($content, defined('TOKEN_PARSE') ? TOKEN_PARSE : 0);
				} catch (\Throwable $e) {
					throw new \RuntimeException('Ошибка PHP: ' . $e->getMessage());
				}
			}

			$path = $definitions[$code]['path'];
			$dir = dirname($path);
			if (!is_dir($dir) || !is_writable($dir)) {
				throw new \RuntimeException('Каталог системного файла недоступен для записи');
			}

			$tmp = tempnam($dir, '.adminx-file-');
			if ($tmp === false || file_put_contents($tmp, rtrim($content) . "\n", LOCK_EX) === false) {
				if ($tmp) { @unlink($tmp); }
				throw new \RuntimeException('Не удалось подготовить системный файл');
			}

			$mode = is_file($path) ? (fileperms($path) & 0777) : 0644;
			@chmod($tmp, $mode);
			if (!@rename($tmp, $path)) {
				@unlink($tmp);
				throw new \RuntimeException('Не удалось заменить системный файл');
			}

			return self::systemFile($code);
		}

		protected static function systemFileDefinitions()
		{
			return array(
				'robots' => array('label' => 'robots.txt', 'description' => 'Правила индексации сайта поисковыми роботами.', 'path' => BASEPATH . '/robots.txt', 'mode' => 'text/plain'),
				'custom' => array('label' => 'custom.php', 'description' => 'Пользовательские функции фронта.', 'path' => BASEPATH . '/system/App/Functions/custom.php', 'mode' => 'application/x-httpd-php'),
			);
		}

		protected static function tableCount($table)
		{
			try {
				return (int) DB::query('SELECT COUNT(*) FROM ' . $table)->getValue();
			} catch (\Throwable $e) {
				return 0;
			}
		}

		protected static function paginationInput(array $input)
		{
			$fields = array(
				'pagination_name',
				'pagination_box',
				'pagination_start_label',
				'pagination_end_label',
				'pagination_separator_box',
				'pagination_separator_label',
				'pagination_next_label',
				'pagination_prev_label',
				'pagination_link_box',
				'pagination_active_link_box',
				'pagination_link_template',
				'pagination_link_active_template',
			);

			$data = array();
			foreach ($fields as $field) {
				$data[$field] = isset($input[$field]) ? trim((string) $input[$field]) : '';
			}

			if ($data['pagination_name'] === '') {
				$data['pagination_name'] = 'Без названия';
			}

			return $data;
		}

		protected static function dirSize($dir)
		{
			if (!is_dir($dir)) {
				return 0;
			}

			$size = 0;
			$it = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
				\RecursiveIteratorIterator::SELF_FIRST
			);
			foreach ($it as $file) {
				if ($file->isFile()) {
					$size += (int) $file->getSize();
				}
			}

			return $size;
		}

		protected static function removeDirContents($dir)
		{
			if (!is_dir($dir)) {
				return;
			}

			$it = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
				\RecursiveIteratorIterator::CHILD_FIRST
			);
			foreach ($it as $file) {
				if ($file->isDir()) {
					@rmdir($file->getPathname());
				} else {
					@unlink($file->getPathname());
				}
			}
		}

		protected static function formatBytes($bytes)
		{
			$bytes = (float) $bytes;
			$units = array('B', 'KB', 'MB', 'GB');
			$i = 0;
			while ($bytes >= 1024 && $i < count($units) - 1) {
				$bytes = $bytes / 1024;
				$i++;
			}

			return ($i === 0 ? (string) (int) $bytes : number_format($bytes, 2, '.', '')) . ' ' . $units[$i];
		}
	}
