<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Common/CoreUpdate/UpdateJob.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Common\CoreUpdate;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Common\Cache;
	use App\Common\Lock;
	use App\Common\ModuleMigrator;
	use DB;

	/** Persistent, resumable state machine for one verified core patch. */
	class UpdateJob
	{
		public static function create($archive, $publicKey, $actorId = null)
		{
			return Lock::run('core-update-operation', function () use ($archive, $publicKey, $actorId) {
				return self::createUnlocked($archive, $publicKey, $actorId);
			}, 30.0, true);
		}

		protected static function createUnlocked($archive, $publicKey, $actorId)
		{
			self::assertMaintenanceAvailable();
			self::assertNoActiveJob();
			$manifest = PatchPackage::inspect($archive, $publicKey);
			$id = date('YmdHis') . '-' . bin2hex(random_bytes(6));
			$directory = self::directory($id);
			try {
				if (!@mkdir($directory, 0750, true) && !is_dir($directory)) { throw new \RuntimeException('Не удалось создать задание обновления'); }
				$targetArchive = $directory . '/patch.zip';
				if (!copy($archive, $targetArchive)) { throw new \RuntimeException('Не удалось сохранить архив обновления'); }
				@chmod($targetArchive, 0600);
				PatchPackage::extract($targetArchive, $directory . '/stage', $manifest);
				$job = array('id' => $id, 'status' => 'prepared', 'created_at' => date(DATE_ATOM), 'updated_at' => date(DATE_ATOM), 'actor_id' => $actorId === null ? null : (int) $actorId, 'manifest' => $manifest, 'progress' => 15, 'message' => 'Патч проверен и подготовлен', 'journal' => array(), 'database_backup' => '', 'error' => '', 'recovery_token' => '');
				self::save($job);
				return self::withPreflight($job);
			} catch (\Throwable $e) {
				self::removeTree($directory);
				throw $e;
			}
		}

		public static function load($id)
		{
			$id = self::validateId($id);
			$file = self::directory($id) . '/job.json';
			$data = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
			if (!is_array($data) || !isset($data['id'], $data['manifest'])) { throw new \RuntimeException('Задание обновления не найдено'); }
			return $data;
		}

		public static function latest()
		{
			$files = glob(self::root() . '/*/job.json') ?: array();
			rsort($files, SORT_STRING);
			if (!$files) { return null; }
			$data = json_decode((string) file_get_contents($files[0]), true);
			return is_array($data) ? $data : null;
		}

		public static function step($id)
		{
			return Lock::run('core-update-operation', function () use ($id) {
				$job = self::load($id);
				if ($job['status'] === 'prepared') { return self::backup($job); }
				if ($job['status'] === 'backing_up') { return self::backupDatabaseStep($job); }
				if ($job['status'] === 'backed_up' || $job['status'] === 'applying') { return self::apply($job); }
				if ($job['status'] === 'applied') { return self::verify($job); }
				return $job;
			}, 30.0, true);
		}

		public static function preflight(array $job)
		{
			$manifest = $job['manifest'];
			$errors = array();
			$compatibility = ReleaseState::patchCompatibility(
				$manifest['from'],
				$manifest['to'],
				!empty($manifest['cumulative'])
			);
			if (!$compatibility['applicable']) { $errors[] = $compatibility['reason']; }
			$requirements = isset($manifest['requirements']) ? $manifest['requirements'] : array();
			if (!empty($requirements['php_min']) && version_compare(PHP_VERSION, $requirements['php_min'], '<')) { $errors[] = 'Требуется PHP ' . $requirements['php_min'] . '+'; }
			$databaseError = self::databaseRequirementError(
				(string) DB::query('SELECT VERSION()')->getValue(),
				$requirements
			);
			if ($databaseError !== '') { $errors[] = $databaseError; }

			foreach (isset($requirements['extensions']) ? (array) $requirements['extensions'] : array() as $extension) { if (!extension_loaded((string) $extension)) { $errors[] = 'Отсутствует PHP-расширение ' . $extension; } }
			$conflicts = array();
			foreach (self::managedEntries($manifest['files']) as $file) {
				if (!self::writableTarget(ReleaseState::resolve($file['path']))) { $errors[] = 'Нет прав записи: ' . $file['path']; }
			}

			foreach (self::managedEntries($manifest['deletions']) as $file) {
				$target = ReleaseState::resolve($file['path']);
				if (is_file($target) && !is_writable($target)) { $errors[] = 'Нет прав удаления: ' . $file['path']; }
			}

			$pendingMigrations = self::pendingMigrations($manifest['migrations']);
			$databasePlan = self::databaseBackupPlan(array('migrations' => $pendingMigrations));
			$databaseBytes = $pendingMigrations ? DatabaseBackup::estimatedBytes($databasePlan) : 0;
			$required = max(10485760, (int) filesize(self::directory($job['id']) . '/patch.zip') * 4 + (int) ceil($databaseBytes * 1.25));
			$free = @disk_free_space(self::root());
			if ($free !== false && $free < $required) { $errors[] = 'Недостаточно свободного места для staging и отката'; }
			return array('ready' => !$errors && !$conflicts, 'errors' => $errors, 'conflicts' => $conflicts, 'files' => count(self::managedEntries($manifest['files'])), 'deletions' => count(self::managedEntries($manifest['deletions'])), 'preserved' => self::preservedEntries($manifest), 'migrations' => count($manifest['migrations']), 'required_bytes' => $required);
		}

		public static function rollback($id, $restoreDatabase = true)
		{
			return Lock::run('core-update-operation', function () use ($id, $restoreDatabase) {
				$job = self::load($id);
				self::restoreFiles($job);
				if ($restoreDatabase && !empty($job['database_applied']) && !empty($job['database_backup']) && DatabaseBackup::complete($job['database_backup'])) { DatabaseBackup::restore($job['database_backup']); }
				self::removeMaintenance();
				$job['status'] = 'rolled_back';
				$job['progress'] = 100;
				$job['message'] = 'Изменения обновления отменены';
				$job['recovery_token'] = '';
				$job['updated_at'] = date(DATE_ATOM);
				self::save($job);
				return $job;
			}, 30.0, true);
		}

		public static function discard($id)
		{
			return Lock::run('core-update-operation', function () use ($id) {
				$job = self::load($id);
				if ($job['status'] !== 'prepared') { throw new \RuntimeException('Отменить можно только подготовленный, но не запущенный патч'); }
				foreach (array('patch.zip', 'stage', 'backup', 'database-backup') as $relative) { self::removeTree(self::directory($id) . '/' . $relative); }
				$job['status'] = 'discarded';
				$job['progress'] = 100;
				$job['message'] = 'Подготовленный патч отменён';
				$job['recovery_token'] = '';
				self::save($job);
				return $job;
			}, 30.0, true);
		}

		protected static function backup(array $job)
		{
			$preflight = self::preflight($job);
			if (!$preflight['ready']) { throw new \RuntimeException('Предварительная проверка не пройдена'); }
			self::assertMaintenanceAvailable($job['id']);
			$directory = self::directory($job['id']) . '/backup/files';
			@mkdir($directory, 0750, true);
			$job['journal'] = array();
			$paths = array();
			foreach (self::managedEntries(array_merge($job['manifest']['files'], $job['manifest']['deletions'])) as $file) { $paths[$file['path']] = $file['path']; }
			$paths['storage/installed.lock'] = 'storage/installed.lock';
			foreach ($paths as $logical) {
				$actual = $logical === 'storage/installed.lock' ? ReleaseState::lockFile() : ReleaseState::resolve($logical);
				$backup = $directory . '/' . str_replace('@admin/', '__admin__/', $logical);
				$entry = array('path' => $logical, 'actual' => $actual, 'existed' => is_file($actual), 'backup' => '');
				if ($entry['existed']) { @mkdir(dirname($backup), 0750, true); if (!copy($actual, $backup)) { throw new \RuntimeException('Не удалось сохранить файл для отката: ' . $logical); } $entry['backup'] = $backup; }
				$job['journal'][] = $entry;
			}

			if (empty($job['recovery_token'])) { $job['recovery_token'] = bin2hex(random_bytes(24)); }
			$pendingMigrations = self::pendingMigrations($job['manifest']['migrations']);
			$job['pending_migrations'] = $pendingMigrations;
			if ($pendingMigrations) {
				$job['database_backup'] = self::directory($job['id']) . '/database-backup';
				$tables = DatabaseBackup::begin(
					$job['database_backup'],
					self::databaseBackupPlan(array('migrations' => $pendingMigrations))
				);
				$job['database_backup_state'] = array('tables' => $tables, 'index' => 0, 'offset' => 0, 'schema_written' => false);
				$job['database_applied'] = false;
				if ($tables) {
					$job['status'] = 'backing_up'; $job['progress'] = 30; $job['message'] = 'Файлы сохранены, создаётся копия затрагиваемых таблиц БД';
				} else {
					DatabaseBackup::finish($job['database_backup']);
					$job['status'] = 'backed_up'; $job['progress'] = 45; $job['message'] = 'Файлы сохранены, миграции не требуют копирования данных';
				}

				$job['updated_at'] = date(DATE_ATOM); self::save($job); self::writeMaintenance($job);
			} else {
				$job['status'] = 'backed_up';
				$job['progress'] = 45;
				$job['message'] = !empty($job['manifest']['migrations'])
					? 'Все миграции уже применены, копирование БД не требуется'
					: 'Резервная копия создана';
				$job['updated_at'] = date(DATE_ATOM);
				self::save($job);
			}

			return $job;
		}

		protected static function backupDatabaseStep(array $job)
		{
			self::assertMaintenanceAvailable($job['id']);
			$state = isset($job['database_backup_state']) && is_array($job['database_backup_state']) ? $job['database_backup_state'] : array();
			$tables = isset($state['tables']) && is_array($state['tables']) ? $state['tables'] : array();
			$index = isset($state['index']) ? (int) $state['index'] : 0;
			if ($index >= count($tables)) {
				DatabaseBackup::finish($job['database_backup']);
				$job['status'] = 'backed_up'; $job['progress'] = 45; $job['message'] = 'Резервная копия файлов и БД создана'; self::save($job); return $job;
			}

			$table = (string) $tables[$index];
			if (empty($state['schema_written'])) { DatabaseBackup::writeSchema($job['database_backup'], $table); $state['schema_written'] = true; }
			$offset = isset($state['offset']) ? max(0, (int) $state['offset']) : 0;
			$count = DatabaseBackup::writeRows($job['database_backup'], $table, $offset);
			if ($count < DatabaseBackup::BATCH_SIZE) { $state['index'] = $index + 1; $state['offset'] = 0; $state['schema_written'] = false; }
			else { $state['offset'] = $offset + DatabaseBackup::BATCH_SIZE; }
			$job['database_backup_state'] = $state;
			$done = min(count($tables), (int) $state['index']);
			$job['progress'] = 30 + (count($tables) ? (int) floor(($done / count($tables)) * 14) : 14);
			$job['message'] = 'Резервная копия БД: ' . $table . ', строк ' . ($offset + $count);
			self::save($job);
			return $job;
		}

		protected static function apply(array $job)
		{
			if ($job['status'] === 'backed_up') {
				$preflight = self::preflight($job);
				if (!$preflight['ready']) { throw new \RuntimeException('Исходное состояние изменилось после резервного копирования'); }
				$job['status'] = 'applying';
				$job['progress'] = 55;
				$job['message'] = 'Применяются файлы и миграции';
				self::save($job);
			}

			self::assertMaintenanceAvailable($job['id']);
			self::writeMaintenance($job);
			try {
				foreach (self::managedEntries($job['manifest']['files']) as $file) {
					$source = self::directory($job['id']) . '/stage/payload/' . $file['path'];
					$target = ReleaseState::resolve($file['path']);
					self::replaceFile($source, $target, $file['sha256'], $file['mode']);
				}

				foreach (self::managedEntries($job['manifest']['deletions']) as $file) { $target = ReleaseState::resolve($file['path']); if (is_file($target) && !@unlink($target)) { throw new \RuntimeException('Не удалось удалить устаревший файл: ' . $file['path']); } }
				$migrations = isset($job['pending_migrations']) && is_array($job['pending_migrations'])
					? $job['pending_migrations']
					: $job['manifest']['migrations'];
				if ($migrations) {
					$job['database_applied'] = true;
					self::save($job);
					foreach (self::migrationGroups($migrations) as $module => $moduleMigrations) {
						ModuleMigrator::apply($module, $moduleMigrations, self::directory($job['id']) . '/stage/migrations', array(
							'operation' => 'core_update',
							'actor_id' => $job['actor_id'],
						));
					}
				}

				ReleaseState::write($job['manifest']['to'], isset($job['manifest']['to']['core_fingerprint']) ? $job['manifest']['to']['core_fingerprint'] : '');
				self::clearRuntimeCaches();
				$job['status'] = 'applied'; $job['progress'] = 85; $job['message'] = 'Файлы и миграции применены, выполняется проверка'; $job['updated_at'] = date(DATE_ATOM); self::save($job); self::writeMaintenance($job);
				return $job;
			} catch (\Throwable $e) {
				$job['error'] = $e->getMessage(); self::save($job);
				try { self::restoreFiles($job); if (!empty($job['database_applied']) && !empty($job['database_backup']) && DatabaseBackup::complete($job['database_backup'])) { DatabaseBackup::restore($job['database_backup']); } } catch (\Throwable $rollbackError) { $job['error'] .= '; откат: ' . $rollbackError->getMessage(); }
				self::removeMaintenance(); $job['status'] = 'failed'; $job['progress'] = 100; $job['message'] = 'Обновление отменено'; self::save($job); throw $e;
			}
		}

		protected static function verify(array $job)
		{
			try {
				if (!ReleaseState::matches($job['manifest']['to'])) { throw new \RuntimeException('Реестр версии не подтвердил обновление'); }
				foreach (self::managedEntries($job['manifest']['files']) as $file) { $target = ReleaseState::resolve($file['path']); if (!is_file($target) || !hash_equals($file['sha256'], hash_file('sha256', $target))) { throw new \RuntimeException('Контрольная проверка файла не пройдена: ' . $file['path']); } }
				self::verifyRuntime();
				$job['preserved_files'] = self::preservedEntries($job['manifest']);
				self::removeMaintenance(); $job['status'] = 'completed'; $job['progress'] = 100; $job['message'] = 'Обновление установлено' . ($job['preserved_files'] ? '. Сохранены файлы проекта: ' . implode(', ', $job['preserved_files']) : ''); $job['recovery_token'] = ''; $job['updated_at'] = date(DATE_ATOM); self::save($job); return $job;
			} catch (\Throwable $e) {
				$job['status'] = 'verification_failed'; $job['error'] = $e->getMessage(); $job['message'] = 'Контрольная проверка не пройдена, требуется откат'; $job['updated_at'] = date(DATE_ATOM); self::save($job); throw $e;
			}
		}

		protected static function withPreflight(array $job) { $job['preflight'] = self::preflight($job); return $job; }
		protected static function managedEntries(array $entries)
		{
			return array_values(array_filter($entries, function ($file) { return !ReleaseState::preservedPath($file['path']); }));
		}

		protected static function preservedEntries(array $manifest)
		{
			$paths = array();
			foreach (array_merge($manifest['files'], $manifest['deletions']) as $file) {
				if (ReleaseState::preservedPath($file['path'])) { $paths[] = $file['path']; }
			}

			return array_values(array_unique($paths));
		}

		protected static function writableTarget($path) { $path = (string) $path; if (is_file($path)) { return is_writable($path) && is_writable(dirname($path)); } $directory = dirname($path); while (!is_dir($directory) && $directory !== dirname($directory)) { $directory = dirname($directory); } return is_dir($directory) && is_writable($directory); }
		public static function databaseRequirementError($databaseVersion, array $requirements)
		{
			$databaseVersion = trim((string) $databaseVersion);
			if (stripos($databaseVersion, 'mariadb') !== false) {
				$required = isset($requirements['mariadb_min']) ? trim((string) $requirements['mariadb_min']) : '';
				$version = '';
				if (preg_match('/(?:5\.5\.5-)?([0-9]+(?:\.[0-9]+){1,2})-MariaDB/i', $databaseVersion, $match)) {
					$version = $match[1];
				}

				return $required !== '' && $version !== '' && version_compare($version, $required, '<')
					? 'Требуется MariaDB ' . $required . '+'
					: '';
			}

			$required = isset($requirements['mysql_min']) ? trim((string) $requirements['mysql_min']) : '';
			if ($required !== '' && preg_match('/^[0-9]+(?:\.[0-9]+){1,2}/', $databaseVersion, $match)
				&& version_compare($match[0], $required, '<')) {
				return 'Требуется MySQL ' . $required . '+';
			}

			return '';
		}

		protected static function migrationGroups(array $migrations) { $groups = array(); foreach ($migrations as $migration) { $module = isset($migration['module']) ? (string) $migration['module'] : 'core_update'; if (!isset($groups[$module])) { $groups[$module] = array(); } $groups[$module][] = $migration; } return $groups; }
		public static function pendingMigrations(array $migrations)
		{
			$pending = array();
			foreach ($migrations as $migration) {
				if (!is_array($migration)) { continue; }
				$module = isset($migration['module']) ? trim((string) $migration['module']) : 'core_update';
				$id = isset($migration['id']) ? trim((string) $migration['id']) : '';
				if ($id === '' || !ModuleMigrator::isApplied($module, $id)) { $pending[] = $migration; }
			}

			return $pending;
		}

		public static function databaseBackupPlan(array $manifest)
		{
			$migrations = isset($manifest['migrations']) && is_array($manifest['migrations']) ? $manifest['migrations'] : array();
			$tables = $migrations ? array(
				'{{prefix}}_module_migrations' => '{{prefix}}_module_migrations',
				'{{prefix}}_module_migration_attempts' => '{{prefix}}_module_migration_attempts',
			) : array();
			foreach ($migrations as $migration) {
				if (!is_array($migration) || !array_key_exists('backup_tables', $migration)) {
					return null;
				}

				foreach ((array) $migration['backup_tables'] as $table) {
					$table = trim((string) $table);
					if ($table !== '') { $tables[$table] = $table; }
				}
			}

			ksort($tables, SORT_STRING);
			return array_values($tables);
		}

		protected static function replaceFile($source, $target, $checksum, $mode) { if (!is_file($source) || !hash_equals($checksum, hash_file('sha256', $source))) { throw new \RuntimeException('Staging-файл повреждён'); } $directory = dirname($target); if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) { throw new \RuntimeException('Не удалось создать каталог обновления'); } $temporary = $target . '.update-' . bin2hex(random_bytes(4)); if (!copy($source, $temporary) || !@rename($temporary, $target)) { @unlink($temporary); throw new \RuntimeException('Не удалось заменить файл: ' . $target); } @chmod($target, $mode ?: 0644); if (function_exists('opcache_invalidate')) { @opcache_invalidate($target, true); } }
		protected static function restoreFiles(array $job) { foreach (array_reverse(isset($job['journal']) ? $job['journal'] : array()) as $entry) { if (ReleaseState::preservedPath($entry['path'])) { continue; } if (!empty($entry['existed'])) { self::replaceFile($entry['backup'], $entry['actual'], hash_file('sha256', $entry['backup']), fileperms($entry['backup']) & 0777); } elseif (is_file($entry['actual'])) { @unlink($entry['actual']); } } }
		protected static function clearRuntimeCaches()
		{
			Cache::flush();
			self::removeTree(BASEPATH . '/tmp/cache/twig');
			$file = BASEPATH . '/storage/runtime/module-registry.json';
			if (is_file($file)) { @unlink($file); }
		}

		protected static function verifyRuntime() { if (!class_exists('App\\Frontend\\PublicKernel') || !class_exists('App\\Common\\Router')) { throw new \RuntimeException('Публичный bootstrap не загрузил критические классы'); } if ((int) DB::query('SELECT 1')->getValue() !== 1) { throw new \RuntimeException('Контрольный запрос к базе данных не выполнен'); } }
		protected static function assertMaintenanceAvailable($jobId = '') { $file = BASEPATH . '/storage/updates/maintenance.json'; if (!is_file($file)) { return; } $data = json_decode((string) file_get_contents($file), true); if (!is_array($data) || $jobId === '' || !isset($data['job_id']) || !hash_equals((string) $data['job_id'], (string) $jobId)) { throw new \RuntimeException('Другое обновление уже выполняется'); } }
		protected static function assertNoActiveJob() { $job = self::latest(); if ($job && in_array($job['status'], array('prepared', 'backing_up', 'backed_up', 'applying', 'applied', 'verification_failed'), true)) { throw new \RuntimeException('Сначала завершите, откатите или отмените текущее обновление'); } }
		protected static function writeMaintenance(array $job) { $payload = array('job_id' => $job['id'], 'started_at' => date(DATE_ATOM), 'recovery_token_hash' => !empty($job['recovery_token']) ? hash('sha256', $job['recovery_token']) : ''); self::writeJson(BASEPATH . '/storage/updates/maintenance.json', $payload, 0600); }
		protected static function removeMaintenance() { @unlink(BASEPATH . '/storage/updates/maintenance.json'); }
		protected static function save(array $job) { $job['updated_at'] = date(DATE_ATOM); self::writeJson(self::directory($job['id']) . '/job.json', $job, 0600); }
		protected static function writeJson($file, array $data, $mode) { @mkdir(dirname($file), 0750, true); $tmp = $file . '.tmp-' . bin2hex(random_bytes(5)); $raw = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n"; if (file_put_contents($tmp, $raw, LOCK_EX) === false || !@rename($tmp, $file)) { @unlink($tmp); throw new \RuntimeException('Не удалось сохранить состояние обновления'); } @chmod($file, $mode); }
		protected static function validateId($id) { $id = trim((string) $id); if (!preg_match('/^[0-9]{14}-[a-f0-9]{12}$/', $id)) { throw new \InvalidArgumentException('Некорректное задание обновления'); } return $id; }
		protected static function root() { $root = BASEPATH . '/storage/updates/jobs'; if (!is_dir($root)) { @mkdir($root, 0750, true); } return $root; }
		protected static function directory($id) { return self::root() . '/' . self::validateId($id); }
		protected static function removeTree($path) { if (!is_dir($path)) { if (is_file($path)) { @unlink($path); } return; } $items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST); foreach ($items as $item) { $item->isDir() && !$item->isLink() ? @rmdir($item->getPathname()) : @unlink($item->getPathname()); } @rmdir($path); }
	}
