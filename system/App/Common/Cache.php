<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Common/Cache.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Common;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Helpers\Dir;
	use App\Helpers\File;
	use App\Helpers\Json;

	/**
	 * Файловый кеш общего назначения.
	 *
	 * Хранит данные в BASEPATH/tmp/cache/data/<shard>/, а индексы тегов —
	 * в BASEPATH/tmp/cache/tags/<shard>/. Поддерживает чтение старой плоской
	 * раскладки для бесшовного обновления.
	 *
	 * Использование:
	 *   Cache::set('key', $data, 3600);
	 *   $data = Cache::get('key');
	 *   $data = Cache::remember('key', 3600, function () { return expensiveQuery(); });
	 *   Cache::forget('key');
	 *   Cache::flush();
	 */
	class Cache
	{
		/** Директория хранения */
		protected static $_dir = null;

		/** Расширение файлов */
		const EXT = '.cache';

		// ------------------------------------------------------------------ //
		//  Инициализация
		// ------------------------------------------------------------------ //

		protected static function dir()
		{
			if (self::$_dir === null) {
				self::$_dir = rtrim(str_replace('\\', '/', BASEPATH), '/') . '/tmp/cache/';

				Dir::create(self::$_dir, 0755);
			}

			return self::$_dir;
		}

		// ------------------------------------------------------------------ //
		//  Чтение
		// ------------------------------------------------------------------ //

		/**
		 * Получить значение из кеша.
		 *
		 * @param  string $key
		 * @param  mixed  $default Значение если кеш промахнулся
		 * @return mixed
		 */
		public static function get($key, $default = null)
		{
			$file = self::existingPath($key);

			if (!is_file($file)) {
				return $default;
			}

			$raw = File::getContent($file);

			if ($raw === false) {
				return $default;
			}

			$data = Json::toArray($raw);

			if (!is_array($data) || !isset($data['expires'], $data['value'])) {
				return $default;
			}

			if ($data['expires'] !== 0 && $data['expires'] < time()) {
				self::forget($key);
				return $default;
			}

			$serialized = isset($data['encoding']) && $data['encoding'] === 'base64'
				? base64_decode((string) $data['value'], true)
				: $data['value'];
			if ($serialized === false) {
				return $default;
			}

			$value = @unserialize($serialized, array('allowed_classes' => false));
			if ($value === false && $serialized !== 'b:0;') {
				return $default;
			}

			return $value;
		}

		// ------------------------------------------------------------------ //
		//  Запись
		// ------------------------------------------------------------------ //

		/**
		 * Сохранить значение в кеш.
		 *
		 * @param  string   $key
		 * @param  mixed    $value
		 * @param  int      $ttl    Секунды. 0 = бесконечно.
		 * @param  string[] $tags   Теги для групповой инвалидации
		 * @return bool
		 */
		public static function set($key, $value, $ttl = 3600, array $tags = [])
		{
			$writer = function () use ($key, $value, $ttl, $tags) {
				$previousFile = self::existingPath($key);
				$file = self::path($key, true);
				$previous = self::payload($previousFile);
				$previousTags = is_array($previous) && isset($previous['tags']) && is_array($previous['tags'])
					? self::normalizeTags($previous['tags'])
					: array();
				$tags = self::normalizeTags($tags);
				$expires = $ttl > 0 ? time() + $ttl : 0;

				$payload = Json::encode(array(
					'key' => $key,
					'expires' => $expires,
					'tags' => $tags,
					'encoding' => 'base64',
					'value' => base64_encode(serialize($value)),
				), JSON_UNESCAPED_UNICODE);

				if (!File::putAtomic($file, $payload)) {
					return false;
				}

				self::deleteLegacyPath($key);

				try {
					self::syncTagIndexes($previousTags, $tags, $key);
				} catch (\RuntimeException $e) {
					return false;
				}

				return true;
			};

			try {
				return Lock::run('cache-key:' . (string) $key, $writer);
			} catch (\RuntimeException $e) {
				return $writer();
			}
		}

		// ------------------------------------------------------------------ //
		//  Remember
		// ------------------------------------------------------------------ //

		/**
		 * Получить из кеша или вычислить и сохранить.
		 *
		 * @param  string   $key
		 * @param  int      $ttl
		 * @param  callable $callback  Функция, возвращающая значение
		 * @return mixed
		 */
		public static function remember($key, $ttl, callable $callback)
		{
			$miss = new \stdClass();
			$value = self::get($key, $miss);

			if ($value !== $miss) {
				return $value;
			}

			$started = false;
			try {
				return Lock::run('cache-remember:' . (string) $key, function () use ($key, $ttl, $callback, $miss, &$started) {
					$started = true;
					$value = self::get($key, $miss);
					if ($value !== $miss) {
						return $value;
					}

					$value = $callback();
					self::set($key, $value, $ttl);
					return $value;
				});
			} catch (\RuntimeException $e) {
				// Retry only lock acquisition, never a failing computation or cache write.
				if ($started) { throw $e; }
				$value = self::get($key, $miss);
				if ($value !== $miss) {
					return $value;
				}

				$value = $callback();
				self::set($key, $value, $ttl);
				return $value;
			}
		}

		/**
		 * Получить из кеша или вычислить и сохранить с тегами.
		 *
		 * @param  string   $key
		 * @param  int      $ttl
		 * @param  string[] $tags
		 * @param  callable $callback
		 * @return mixed
		 */
		public static function rememberTagged($key, $ttl, array $tags, callable $callback)
		{
			$miss = new \stdClass();
			$value = self::get($key, $miss);

			if ($value !== $miss) {
				return $value;
			}

			$started = false;
			try {
				return Lock::run('cache-remember:' . (string) $key, function () use ($key, $ttl, $tags, $callback, $miss, &$started) {
					$started = true;
					$value = self::get($key, $miss);
					if ($value !== $miss) {
						return $value;
					}

					$value = $callback();
					self::set($key, $value, $ttl, $tags);
					return $value;
				});
			} catch (\RuntimeException $e) {
				if ($started) { throw $e; }
				$value = self::get($key, $miss);
				if ($value !== $miss) {
					return $value;
				}

				$value = $callback();
				self::set($key, $value, $ttl, $tags);
				return $value;
			}
		}

		/**
		 * Получить из кеша или вычислить (бесконечное хранение).
		 */
		public static function rememberForever($key, callable $callback)
		{
			return self::remember($key, 0, $callback);
		}

		/**
		 * Получить из tagged кеша или вычислить (бесконечное хранение).
		 */
		public static function rememberForeverTagged($key, array $tags, callable $callback)
		{
			return self::rememberTagged($key, 0, $tags, $callback);
		}

		// ------------------------------------------------------------------ //
		//  Проверка
		// ------------------------------------------------------------------ //

		public static function has($key)
		{
			$miss = new \stdClass();
			return self::get($key, $miss) !== $miss;
		}

		// ------------------------------------------------------------------ //
		//  Удаление
		// ------------------------------------------------------------------ //

		/** Удалить один ключ */
		public static function forget($key)
		{
			$invalidating = Lifecycle::event('cache.invalidating', 'cache', 'forget', (string) $key, array(
				'scope' => 'key',
			), null, array(), 'cache');
			if ($invalidating->cancelled()) { return false; }
			$forgetter = function () use ($key) {
				$file = self::existingPath($key);
				$data = self::payload($file);
				$tags = is_array($data) && isset($data['tags']) && is_array($data['tags'])
					? self::normalizeTags($data['tags'])
					: array();
				$deleted = self::deleteDataPaths($key);
				if ($deleted) {
					self::removeFromTagIndexes($tags, $key);
				}

				return $deleted;
			};

			try {
				$deleted = Lock::run('cache-key:' . (string) $key, $forgetter);
			} catch (\RuntimeException $e) {
				$deleted = $forgetter();
			}

			Lifecycle::event('cache.invalidated', 'cache', 'forgotten', (string) $key, array(
				'scope' => 'key',
			), $deleted, array(), 'cache');
			return $deleted;
		}

		/** Удалить все ключи с данным тегом */
		public static function forgetTag($tag)
		{
			$invalidating = Lifecycle::event('cache.invalidating', 'cache', 'forget_tag', (string) $tag, array(
				'scope' => 'tag',
			), null, array(), 'cache');
			if ($invalidating->cancelled()) { return false; }
			$result = self::forgetTagFiles($tag);

			Lifecycle::event('cache.invalidated', 'cache', 'tag_forgotten', (string) $tag, array(
				'scope' => 'tag',
			), $result, array(), 'cache');
			return $result;
		}

		/** Очистить весь кеш */
		public static function flush()
		{
			$invalidating = Lifecycle::event('cache.invalidating', 'cache', 'flush', '*', array(
				'scope' => 'all',
			), null, array(), 'cache');
			if ($invalidating->cancelled()) { return false; }
			$deleted = 0;
			$files = array_merge(
				self::cacheFiles(self::dataDir()),
				self::cacheFiles(self::tagsDir()),
				glob(self::dir() . '*' . self::EXT) ?: array()
			);
			foreach (array_unique($files) as $file) {
				if (File::delete($file)) { $deleted++; }
			}

			self::removeEmptyShards(self::dataDir());
			self::removeEmptyShards(self::tagsDir());

			Lifecycle::event('cache.invalidated', 'cache', 'flushed', '*', array(
				'scope' => 'all',
				'deleted' => $deleted,
			), true, array(), 'cache');
			return true;
		}

		// ------------------------------------------------------------------ //
		//  Инкремент / декремент
		// ------------------------------------------------------------------ //

		public static function increment($key, $by = 1)
		{
			// Атомарный read-modify-write под блокировкой — иначе параллельные
			// запросы теряют инкременты (lost update).
			return Lock::run('cache-incr:' . $key, function () use ($key, $by) {
				$new = (int) self::get($key, 0) + (int) $by;
				self::set($key, $new, 0); // бесконечное хранение для счётчиков
				return $new;
			});
		}

		public static function decrement($key, $by = 1)
		{
			return self::increment($key, -$by);
		}

		// ------------------------------------------------------------------ //
		//  Garbage collector
		// ------------------------------------------------------------------ //

		/** Удалить просроченные записи */
		public static function gc()
		{
			$now  = time();
			$deleted = 0;
			$files = self::cacheFiles(self::dataDir());

			foreach (glob(self::dir() . '*' . self::EXT) ?: array() as $file) {
				if (strpos(basename($file), 'tag_') === 0) {
					continue;
				}

				$files[] = $file;
			}

			foreach (array_unique($files) as $file) {
				$raw  = File::getContent($file);
				$data = $raw ? Json::toArray($raw) : null;

				if (!is_array($data)) {
					File::delete($file);
					self::removeEmptyShard(dirname($file), self::dataDir());
					$deleted++;
					continue;
				}

				if (isset($data['expires']) && $data['expires'] !== 0 && $data['expires'] < $now) {
					if (isset($data['key']) && self::forget($data['key'])) {
						$deleted++;
					}
				}
			}

			return $deleted;
		}

		// ------------------------------------------------------------------ //
		//  Вспомогательные
		// ------------------------------------------------------------------ //

		protected static function path($key, $create = false)
		{
			return self::shardedPath(self::dataDir(), md5((string) $key), $create);
		}

		protected static function legacyPath($key)
		{
			return self::dir() . md5((string) $key) . self::EXT;
		}

		protected static function existingPath($key)
		{
			$file = self::path($key);
			return is_file($file) ? $file : self::legacyPath($key);
		}

		protected static function syncTagIndexes(array $previous, array $current, $key)
		{
			self::removeFromTagIndexes(array_values(array_diff($previous, $current)), $key);
			self::addToTagIndexes(array_values(array_diff($current, $previous)), $key);
		}

		protected static function addToTagIndexes(array $tags, $key)
		{
			foreach ($tags as $tag) {
				self::withTagLock($tag, function () use ($tag, $key) {
					$existing = self::tagKeysFor($tag);

					if (!in_array($key, $existing, true)) {
						$existing[] = $key;
					}

					$indexFile = self::tagPath($tag, true);
					if (File::putAtomic($indexFile, Json::encode($existing))) {
						self::deleteLegacyTagPath($tag);
					}
				});
			}
		}

		protected static function removeFromTagIndexes(array $tags, $key)
		{
			foreach ($tags as $tag) {
				self::withTagLock($tag, function () use ($tag, $key) {
					$existing = self::tagKeysFor($tag);
					$existing = array_values(array_filter($existing, function ($indexedKey) use ($key) {
						return (string) $indexedKey !== (string) $key;
					}));

					if (empty($existing)) {
						self::deleteTagPaths($tag);
						return;
					}

					$indexFile = self::tagPath($tag, true);
					if (File::putAtomic($indexFile, Json::encode($existing))) {
						self::deleteLegacyTagPath($tag);
					}
				});
			}
		}

		protected static function forgetTagFiles($tag)
		{
			$detacher = function () use ($tag) {
				$keys = self::tagKeysFor($tag);
				self::deleteTagPaths($tag);
				return $keys;
			};

			try {
				$keys = Lock::run('cache-tag:' . (string) $tag, $detacher);
			} catch (\RuntimeException $e) {
				$keys = $detacher();
			}

			foreach ($keys as $key) {
				$data = self::payload(self::existingPath($key));
				$tags = is_array($data) && isset($data['tags']) && is_array($data['tags'])
					? self::normalizeTags($data['tags'])
					: array();
				if (in_array((string) $tag, $tags, true)) {
					self::forget($key);
				}
			}

			return true;
		}

		protected static function normalizeTags(array $tags)
		{
			$result = array();
			foreach ($tags as $tag) {
				$tag = trim((string) $tag);
				if ($tag !== '') { $result[$tag] = $tag; }
			}

			return array_values($result);
		}

		protected static function payload($file)
		{
			if (!is_file($file)) { return null; }
			$data = Json::toArray(File::getContent($file));
			return is_array($data) ? $data : null;
		}

		protected static function tagPath($tag, $create = false)
		{
			return self::shardedPath(self::tagsDir(), md5((string) $tag), $create);
		}

		protected static function legacyTagPath($tag)
		{
			return self::dir() . 'tag_' . md5((string) $tag) . self::EXT;
		}

		protected static function tagKeysFor($tag)
		{
			$keys = array();
			foreach (array(self::tagPath($tag), self::legacyTagPath($tag)) as $indexFile) {
				$stored = is_file($indexFile) ? Json::toArray(File::getContent($indexFile)) : array();
				if (is_array($stored)) {
					$keys = array_merge($keys, $stored);
				}
			}

			return array_values(array_unique($keys));
		}

		protected static function dataDir()
		{
			return self::dir() . 'data/';
		}

		protected static function tagsDir()
		{
			return self::dir() . 'tags/';
		}

		protected static function shardedPath($root, $hash, $create = false)
		{
			$directory = rtrim($root, '/') . '/' . substr($hash, 0, 2) . '/';
			if ($create) {
				Dir::create($directory, 0755);
			}

			return $directory . $hash . self::EXT;
		}

		protected static function deleteDataPaths($key)
		{
			$deleted = true;
			foreach (array(self::path($key), self::legacyPath($key)) as $file) {
				if (is_file($file) && !File::delete($file)) {
					$deleted = false;
				}
			}

			self::removeEmptyShard(dirname(self::path($key)), self::dataDir());

			return $deleted;
		}

		protected static function deleteLegacyPath($key)
		{
			$file = self::legacyPath($key);
			if (is_file($file)) { File::delete($file); }
		}

		protected static function deleteTagPaths($tag)
		{
			foreach (array(self::tagPath($tag), self::legacyTagPath($tag)) as $file) {
				if (is_file($file)) { File::delete($file); }
			}

			self::removeEmptyShard(dirname(self::tagPath($tag)), self::tagsDir());
		}

		protected static function deleteLegacyTagPath($tag)
		{
			$file = self::legacyTagPath($tag);
			if (is_file($file)) { File::delete($file); }
		}

		protected static function cacheFiles($directory)
		{
			if (!is_dir($directory)) { return array(); }

			$files = array();
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
			);
			foreach ($iterator as $file) {
				if ($file->isFile() && substr($file->getFilename(), -strlen(self::EXT)) === self::EXT) {
					$files[] = $file->getPathname();
				}
			}

			return $files;
		}

		protected static function removeEmptyShards($root)
		{
			if (!is_dir($root)) { return; }

			foreach (glob(rtrim($root, '/') . '/*', GLOB_ONLYDIR) ?: array() as $directory) {
				self::removeEmptyShard($directory, $root);
			}
		}

		protected static function removeEmptyShard($directory, $root)
		{
			if (rtrim($directory, '/') !== rtrim($root, '/') && is_dir($directory)) {
				@rmdir($directory);
			}
		}

		protected static function withTagLock($tag, callable $callback)
		{
			try {
				return Lock::run('cache-tag:' . (string) $tag, $callback);
			} catch (\RuntimeException $e) {
				return $callback();
			}
		}
	}
