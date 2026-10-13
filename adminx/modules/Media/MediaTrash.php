<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Media/MediaTrash.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Adminx\Media;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Common\AuditLog;
	use App\Common\Auth;
	use App\Common\Lock;
	use App\Helpers\Dir;
	use App\Helpers\File;

	class MediaTrash
	{
		public static function store($path, array $related = array())
		{
			return self::storePaths($path, array_merge(array($path), $related), basename($path), null);
		}

		public static function storeContents($path, array $paths)
		{
			return self::storePaths($path, $paths, basename($path) . ' — содержимое', 'folder');
		}

		protected static function storePaths($path, array $paths, $name, $type)
		{
			return Lock::run('media-trash', function () use ($path, $paths, $name, $type) {
				$paths = array_values(array_unique($paths));
				$token = date('Ymd-His') . '-' . substr(sha1($path . uniqid('', true)), 0, 10);
				$base = self::itemsRoot() . '/' . $token;
				$moved = array();
				if (!Dir::create($base)) { throw new \RuntimeException('Не удалось подготовить корзину медиа'); }

				try {
					foreach ($paths as $sourcePath) {
						$sourcePath = Model::normalize($sourcePath, '');
						$source = $sourcePath !== '' ? Model::abs($sourcePath) : '';
						if ($source === '' || (!is_file($source) && !is_dir($source))) { continue; }
						$target = $base . '/payload/' . ltrim(substr($sourcePath, strlen(Model::ROOT)), '/');
						if (!Dir::create(dirname($target)) || !@rename($source, $target)) {
							throw new \RuntimeException('Не удалось переместить объект в корзину медиа');
						}

						$moved[] = array('path' => $sourcePath, 'stored' => $target);
					}
				} catch (\Throwable $e) {
					self::rollbackMoves($moved);
					self::removeTree($base);
					throw $e;
				}

				if (!$moved) { self::removeTree($base); throw new \RuntimeException('Файл или папка не найдены'); }
				$entry = array(
					'token' => $token,
					'path' => $path,
					'name' => $name,
					'type' => $type !== null ? $type : (is_dir($moved[0]['stored']) ? 'folder' : 'file'),
					'deleted_at' => time(),
					'deleted_label' => date('d.m.Y H:i:s'),
					'actor_id' => (int) Auth::id(),
					'paths' => array_column($moved, 'path'),
				);
				if (!File::putAtomic($base . '/entry.json', json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))) {
					self::rollbackMoves($moved);
					self::removeTree($base);
					throw new \RuntimeException('Не удалось записать данные корзины медиа');
				}

				AuditLog::record('media.trashed', array('actor_id' => Auth::id(), 'target_type' => 'media', 'meta' => array('path' => $path, 'token' => $token)));
				return $entry;
			});
		}

		public static function all()
		{
			$rows = array();
			foreach (glob(self::itemsRoot() . '/*/entry.json') ?: array() as $file) {
				$row = json_decode((string) File::getContent($file), true);
				if (is_array($row) && !empty($row['token'])) { $rows[] = $row; }
			}

			usort($rows, function ($left, $right) { return (int) $right['deleted_at'] - (int) $left['deleted_at']; });
			return $rows;
		}

		public static function restore($token)
		{
			return Lock::run('media-trash', function () use ($token) {
				$entry = self::entry($token);
				foreach ($entry['paths'] as $path) {
					if (file_exists(Model::abs($path))) { throw new \RuntimeException('Исходный путь уже занят: ' . $path); }
				}

				$moved = array();
				try {
					foreach ($entry['paths'] as $path) {
						$stored = self::storedPath($entry['token'], $path);
						$target = Model::abs($path);
						if ((!is_file($stored) && !is_dir($stored)) || !Dir::create(dirname($target)) || !@rename($stored, $target)) {
							throw new \RuntimeException('Не удалось восстановить: ' . $path);
						}

						$moved[] = array('path' => $path, 'stored' => $stored);
					}
				} catch (\Throwable $e) {
					foreach (array_reverse($moved) as $row) { @rename(Model::abs($row['path']), $row['stored']); }
					throw $e;
				}

				self::removeTree(self::itemsRoot() . '/' . $entry['token']);
				AuditLog::record('media.restored', array('actor_id' => Auth::id(), 'target_type' => 'media', 'meta' => array('path' => $entry['path'], 'token' => $entry['token'])));
				return $entry;
			});
		}

		public static function purge($token)
		{
			return Lock::run('media-trash', function () use ($token) {
				$entry = self::entry($token);
				$payload = self::itemsRoot() . '/' . $entry['token'] . '/payload';
				self::removeTree($payload);
				clearstatcache(true, $payload);
				if (file_exists($payload)) { throw new \RuntimeException('Не удалось удалить файлы корзины. Проверьте права доступа.'); }
				self::removeTree(self::itemsRoot() . '/' . $entry['token']);
				if (is_dir(self::itemsRoot() . '/' . $entry['token'])) { throw new \RuntimeException('Не удалось удалить запись корзины. Проверьте права доступа.'); }
				AuditLog::record('media.purged', array('actor_id' => Auth::id(), 'target_type' => 'media', 'meta' => array('path' => $entry['path'], 'token' => $entry['token'])));
				return $entry;
			});
		}

		protected static function entry($token)
		{
			$token = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $token);
			$file = self::itemsRoot() . '/' . $token . '/entry.json';
			$row = is_file($file) ? json_decode((string) File::getContent($file), true) : null;
			if (!is_array($row) || empty($row['paths'])) { throw new \RuntimeException('Объект корзины не найден'); }
			return $row;
		}

		protected static function storedPath($token, $path)
		{
			return self::itemsRoot() . '/' . $token . '/payload/' . ltrim(substr($path, strlen(Model::ROOT)), '/');
		}

		protected static function itemsRoot()
		{
			$root = rtrim(BASEPATH, '/\\') . '/storage/media-trash/items';
			Dir::create($root);
			return $root;
		}

		protected static function rollbackMoves(array $moved)
		{
			foreach (array_reverse($moved) as $row) {
				Dir::create(dirname(Model::abs($row['path'])));
				@rename($row['stored'], Model::abs($row['path']));
			}
		}

		protected static function removeTree($path)
		{
			if (!is_dir($path)) { if (is_file($path)) { @unlink($path); } return; }
			$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
			foreach ($iterator as $item) { $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname()); }
			@rmdir($path);
		}
	}
