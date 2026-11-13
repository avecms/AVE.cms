<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Database/Backup.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Adminx\Database;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Common\Settings;
	use App\Common\DatabaseConfiguration;
	use App\Common\Db\Core\MysqliDriver;
	use DB;

	/**
	 * Резервные копии БД: создание gzip-дампа единой рабочей схемы,
	 * путь и удаление. Дампы лежат в tmp/backup. Имена файлов строго валидируются,
	 * выход за пределы каталога исключён.
	 */
	class Backup
	{
		/** Ключ настройки «сколько копий хранить»; 0 — хранить все. */
		const KEEP_SETTING = 'database_backup_keep';
		const KEEP_DEFAULT = 10;
		const KEEP_MAX = 200;

		public static function dir()
		{
			return BASEPATH . DS . 'tmp' . DS . 'backup';
		}

		public static function keep()
		{
			$value = Settings::get(self::KEEP_SETTING);
			if ($value === null || $value === '') { return self::KEEP_DEFAULT; }
			return max(0, min(self::KEEP_MAX, (int) $value));
		}

		public static function setKeep($value)
		{
			$value = max(0, min(self::KEEP_MAX, (int) $value));
			Settings::set(self::KEEP_SETTING, $value, 'integer');
			return $value;
		}

		/**
		 * Оставляет только последние $keep копий, созданных системой.
		 *
		 * Загруженные вручную и чужие файлы не трогаются: их имя не проходит
		 * isManagedName(), и админ мог положить их сюда намеренно. Страховочные
		 * копии перед восстановлением тоже участвуют в ротации — иначе каждое
		 * восстановление оставляло бы вечный файл.
		 */
		public static function prune($keep = null)
		{
			$keep = $keep === null ? self::keep() : $keep;
			$names = array();
			foreach (self::prunable(self::all(), $keep) as $name) {
				$path = self::path($name);
				if ($path !== null && @unlink($path)) { $names[] = $name; }
			}

			return array('deleted' => count($names), 'names' => $names);
		}

		/**
		 * Отбирает имена лишних копий: чистая функция, файлы не трогает.
		 * Список приходит из all() и уже отсортирован «новые сверху».
		 */
		public static function prunable(array $items, $keep)
		{
			$keep = max(0, min(self::KEEP_MAX, (int) $keep));
			if ($keep < 1) { return array(); }

			$managed = array();
			foreach ($items as $item) {
				if (!empty($item['own'])) { $managed[] = (string) $item['name']; }
			}

			return count($managed) <= $keep ? array() : array_slice($managed, $keep);
		}

		/** Список файлов резервных копий (name/size/mtime), новые сверху. */
		public static function all()
		{
			$dir = self::dir();
			if (!is_dir($dir)) {
				return array();
			}

			$out = array();
			foreach (scandir($dir) as $f) {
				if ($f === '.' || $f === '..' || $f[0] === '.') {
					continue;
				}

				if (!preg_match('/\.(sql|sql\.gz)$/i', $f)) {
					continue;
				}

				$path = $dir . DS . $f;
				if (!is_file($path)) {
					continue;
				}

				$out[] = array(
					'name'   => $f,
					'size'   => filesize($path),
					'size_h' => Model::human(filesize($path)),
					'mtime'  => filemtime($path),
					'own'    => self::isManagedName($f),
				);
			}

			usort($out, function ($a, $b) { return $b['mtime'] - $a['mtime']; });
			return $out;
		}

		/** Полный путь к файлу бэкапа по имени (или null, если имя небезопасно/нет файла). */
		public static function path($name)
		{
			$name = basename((string) $name);
			if (!preg_match('/^[A-Za-z0-9._-]+\.(sql|sql\.gz)$/', $name)) {
				return null;
			}

			$path = self::dir() . DS . $name;
			return is_file($path) ? $path : null;
		}

		public static function delete($name)
		{
			$path = self::path($name);
			if ($path === null) {
				return false;
			}

			return @unlink($path);
		}

		/** Принять скачанный ранее дамп и добавить его в список после полной проверки. */
		public static function upload(array $file)
		{
			if (!isset($file['error']) || (int) $file['error'] !== UPLOAD_ERR_OK
				|| empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
				throw new \RuntimeException('Файл резервной копии не загружен');
			}

			$size = isset($file['size']) ? (int) $file['size'] : 0;
			if ($size <= 0 || $size > BackupRestore::MAX_ARCHIVE_BYTES) {
				throw new \RuntimeException('Размер резервной копии недопустим');
			}

			$original = isset($file['name']) ? (string) $file['name'] : '';
			if (preg_match('/\.sql\.gz$/i', $original)) {
				$extension = '.sql.gz';
			} elseif (preg_match('/\.sql$/i', $original)) {
				$extension = '.sql';
			} else {
				throw new \RuntimeException('Поддерживаются только файлы .sql и .sql.gz');
			}

			$dir = self::ensureDir();
			$name = self::uniqueName('avecms_uploaded_' . date('Ymd_His'), $extension);
			$temporary = $dir . DS . '.upload-' . bin2hex(random_bytes(6)) . $extension;
			if (!move_uploaded_file($file['tmp_name'], $temporary)) {
				throw new \RuntimeException('Не удалось сохранить загруженный файл');
			}

			try {
				$summary = SqlDumpReader::scan($temporary);
				$path = $dir . DS . $name;
				if (!@rename($temporary, $path)) {
					throw new \RuntimeException('Не удалось завершить загрузку резервной копии');
				}
			} catch (\Throwable $e) {
				@unlink($temporary);
				throw $e;
			}

			return array(
				'name' => $name,
				'size' => filesize($path),
				'tables' => $summary['table_count'],
				'rows' => $summary['inserts'],
			);
		}

		/**
		 * Создать gzip-дамп всех таблиц текущего префикса.
		 * @return array{name:string, size:int, tables:int}
		 */
		public static function create($purpose = '', callable $progress = null)
		{
			$dir = self::ensureDir();

			$tables = Model::names();
			if (!$tables) {
				throw new \RuntimeException('Нет таблиц для резервного копирования');
			}

			$prefixes = Model::prefixes();
			$purpose = preg_replace('/[^a-z0-9_-]+/', '', strtolower((string) $purpose));
			$name = self::uniqueName('avecms_' . ($purpose !== '' ? $purpose . '_' : '') . implode('-', $prefixes) . '_' . date('Ymd_His'), '.sql.gz');
			$path = $dir . DS . $name;

			$temporary = $path . '.part';
			$lockPath = $dir . DS . '.avecms-backup.lock';
			$lock = fopen($lockPath, 'c');
			if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
				if ($lock) { fclose($lock); }
				throw new \RuntimeException('Другая резервная копия уже создаётся');
			}

			$fp = gzopen($temporary, 'wb6');
			if (!$fp) {
				flock($lock, LOCK_UN);
				fclose($lock);
				throw new \RuntimeException('Не удалось создать файл дампа');
			}

			gzwrite($fp, "-- AVE.cms full dump\n-- database: " . Model::databaseName() . "\n-- prefixes: {{prefix}}\n-- source-prefix: " . Model::basePrefix() .
				"\n-- date: " . date('Y-m-d H:i:s') . "\n\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

			$driver = null;
			try {
				$driver = self::openSnapshot();
				$mysqli = $driver->getMysqli();
				$total = count($tables);
				foreach ($tables as $index => $table) {
					self::notify($progress, array(
						'table' => $table,
						'current' => $index + 1,
						'total' => $total,
						'rows' => 0,
					));
					self::dumpTable($fp, $table, $mysqli, $progress, $index + 1, $total);
				}

				gzwrite($fp, "\nSET FOREIGN_KEY_CHECKS=1;\n");
				gzclose($fp);
				$fp = null;
				if (!@rename($temporary, $path)) {
					throw new \RuntimeException('Не удалось завершить файл дампа');
				}
			} catch (\Throwable $e) {
				if ($fp) { gzclose($fp); }
				@unlink($temporary);
				flock($lock, LOCK_UN);
				fclose($lock);
				throw $e;
			} finally {
				if ($driver !== null) {
					$driver->getMysqli()->rollback();
					$driver->close();
				}
			}

			flock($lock, LOCK_UN);
			fclose($lock);

			//-- Ротация только после успешного переименования: сначала новая
			//-- копия на диске, и лишь потом удаляются лишние старые.
			$pruned = self::prune();

			return array(
				'name' => $name,
				'size' => filesize($path),
				'tables' => count($tables),
				'prefixes' => $prefixes,
				'pruned' => (int) $pruned['deleted'],
			);
		}

		/** Новые имена AVE.cms и старые adminx-дампы поддерживаются одинаково. */
		public static function isManagedName($name)
		{
			$name = basename((string) $name);
			return strpos($name, 'avecms_') === 0 || strpos($name, 'adminx_') === 0;
		}

		protected static function dumpTable($fp, $table, $mysqli, $progress = null, $current = 0, $total = 0)
		{
			$portableTable = Model::portableTableName($table);
			gzwrite($fp, "\n-- ----- Table `{$portableTable}` -----\nDROP TABLE IF EXISTS `{$portableTable}`;\n");

			$result = $mysqli->query('SHOW CREATE TABLE `' . $table . '`');
			if (!$result) { throw new \RuntimeException('Не удалось прочитать структуру таблицы ' . $table . ': ' . $mysqli->error); }
			$create = $result->fetch_assoc();
			$result->free();
			$ddl = isset($create['Create Table']) ? $create['Create Table'] : '';
			$enumFallbacks = SqlDumpReader::enumFallbacks($ddl);
			if ($ddl !== '') {
				gzwrite($fp, self::portableDdl($ddl) . ";\n\n");
			}

			// A single streaming SELECT cannot repeat or skip rows when writers change offsets.
			$result = $mysqli->query('SELECT * FROM `' . $table . '`', MYSQLI_USE_RESULT);
			if (!$result) { throw new \RuntimeException('Не удалось прочитать таблицу ' . $table . ': ' . $mysqli->error); }
			$count = 0;
			try {
				while ($row = $result->fetch_assoc()) {
					$cols = array();
					$vals = array();
					foreach ($row as $col => $val) {
						$cols[] = '`' . $col . '`';
						if ($val === '' && isset($enumFallbacks[$col])) {
							$val = $enumFallbacks[$col];
						}

						if ($val === null) {
							$vals[] = 'NULL';
						} else {
							$vals[] = "'" . $mysqli->real_escape_string((string) $val) . "'";
						}
					}

					gzwrite($fp, 'INSERT INTO `' . $portableTable . '` (' . implode(',', $cols) . ') VALUES (' . implode(',', $vals) . ");\n");
					$count++;
					if ($count % 5000 === 0) {
						self::notify($progress, array('table' => $table, 'current' => (int) $current, 'total' => (int) $total, 'rows' => $count));
					}
				}

				if ($mysqli->errno) { throw new \RuntimeException('Ошибка чтения таблицы ' . $table . ': ' . $mysqli->error); }
			} finally {
				$result->free();
			}

			self::notify($progress, array('table' => $table, 'current' => (int) $current, 'total' => (int) $total, 'rows' => $count));

			gzwrite($fp, "\n");
		}

		/** Keep the read snapshot independent of the caller and progress callbacks. */
		protected static function openSnapshot()
		{
			$config = DatabaseConfiguration::all();
			$config['dbname'] = Model::databaseName();
			$driver = new MysqliDriver($config);
			try {
				foreach (array('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ', 'START TRANSACTION WITH CONSISTENT SNAPSHOT, READ ONLY') as $sql) {
					if (!$driver->query($sql)) { throw new \RuntimeException('Не удалось открыть согласованный снимок БД: ' . $driver->error()); }
				}
			} catch (\Throwable $e) {
				$driver->close();
				throw $e;
			}

			return $driver;
		}

		/** Заменяет префикс только внутри SQL-идентификаторов, не затрагивая данные строк. */
		protected static function portableDdl($ddl)
		{
			$ddl = preg_replace_callback('/\bCONSTRAINT\s+`([^`]+)`/i', function ($match) {
				return 'CONSTRAINT `{{prefix}}_fk_' . substr(hash('sha256', (string) $match[1]), 0, 16) . '`';
			}, (string) $ddl);

			$ddl = preg_replace_callback('/`([^`]+)`/', function ($match) {
				return '`' . Model::portableTableName($match[1]) . '`';
			}, $ddl);

			//-- MariaDB выводит DEFAULT NULL у TEXT/BLOB, тогда как MySQL 5.7
			//-- запрещает default для этих типов. NULL и без clause остаётся default.
			return preg_replace_callback(
				'/^(\s*`[^`]+`\s+(?:tiny|medium|long)?(?:text|blob)\b[^,\r\n]*?)\s+DEFAULT\s+NULL(\s*,?)$/mi',
				function ($match) { return $match[1] . $match[2]; },
				$ddl
			);
		}

		protected static function ensureDir()
		{
			$dir = self::dir();
			if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
				throw new \RuntimeException('Каталог бэкапов недоступен для записи');
			}

			return $dir;
		}

		protected static function uniqueName($base, $extension)
		{
			$name = (string) $base . (string) $extension;
			if (is_file(self::dir() . DS . $name)) {
				$name = (string) $base . '_' . bin2hex(random_bytes(3)) . (string) $extension;
			}

			return $name;
		}

		protected static function notify($progress, array $payload)
		{
			if ($progress) { call_user_func($progress, $payload); }
		}
	}
