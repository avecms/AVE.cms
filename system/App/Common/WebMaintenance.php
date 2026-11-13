<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Common/WebMaintenance.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Common;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Common\Session\SessionFiles;
	use App\Common\Session\SessionDB;
	use App\Content\PublicShellTables;

	/** Runs short, idempotent maintenance batches without requiring production CLI. */
	class WebMaintenance
	{
		const INTERVAL = 900;
		const BATCH_SIZE = 250;
		const SESSION_INTERVAL = 60;
		const SESSION_BATCH_SIZE = 1000;

		protected static $ran = false;
		protected static $sessionCleanupScheduled = false;

		public static function runIfDue()
		{
			self::scheduleSessionCleanup();
			if (self::$ran) { return false; }
			self::$ran = true;

			try {
				$key = 'web-maintenance:v1';
				if (Cache::get($key)) { return false; }
				Cache::set($key, time(), self::INTERVAL);
			} catch (\Throwable $e) {
				error_log('Web maintenance scheduling failed: ' . $e->getMessage());
				return false;
			}

			self::task('expired IP blocks', array(IpBlocker::class, 'pruneExpired'));
			self::task('expired referrer rows', array(ReferrerLog::class, 'pruneExpired'));
			self::pruneFileSessions();
			return true;
		}

		public static function scheduleSessionCleanup()
		{
			if (!self::usesDatabaseSessions() || !self::responseFinisher()) { return false; }
			if (!self::$sessionCleanupScheduled) {
				self::$sessionCleanupScheduled = true;
				register_shutdown_function(array(__CLASS__, 'finishSessionCleanup'));
			}

			return true;
		}

		public static function finishSessionCleanup()
		{
			if (!self::$sessionCleanupScheduled) { return; }
			self::$sessionCleanupScheduled = false;
			try {
				if (Cache::get(self::sessionCleanupKey())) { return; }
				$finish = self::responseFinisher();
				if (!$finish) { return; }
				if (session_status() === PHP_SESSION_ACTIVE) { session_write_close(); }
				// A shutdown callback alone does not release an Apache HTTP response.
				if (call_user_func($finish) === false) { return; }
				self::pruneDatabaseSessions();
			} catch (\Throwable $e) {
				error_log('Web maintenance [expired database sessions] failed: ' . $e->getMessage());
			}
		}

		/** Shared by the post-response callback, PHP GC and the optional scheduler. */
		public static function pruneDatabaseSessions()
		{
			if (!self::usesDatabaseSessions()) { return array('deleted' => 0, 'skipped' => 'handler'); }
			if (\DB::$transaction_in_progress) { return array('deleted' => 0, 'skipped' => 'transaction'); }
			$key = self::sessionCleanupKey();
			if (Cache::get($key)) { return array('deleted' => 0, 'skipped' => 'interval'); }
			$started = false;

			try {
				return Lock::run($key, function () use ($key, &$started) {
					$started = true;
					if (Cache::get($key)) { return array('deleted' => 0, 'skipped' => 'interval'); }
					if (!Cache::set($key, time(), self::SESSION_INTERVAL)) {
						throw new \RuntimeException('Cannot save the session cleanup interval');
					}

					$deleted = SessionDB::pruneExpired(self::SESSION_BATCH_SIZE);
					self::recordSessionCleanup($deleted, '');
					return array('deleted' => $deleted, 'skipped' => '');
				}, 0.0, true);
			} catch (\Throwable $e) {
				if (!$started) { return array('deleted' => 0, 'skipped' => 'busy'); }
				self::recordSessionCleanup(0, $e->getMessage());
				throw $e;
			}
		}

		public static function sessionCleanupStatus()
		{
			$record = Cache::get(self::sessionCleanupKey() . ':last');
			return array(
				'database' => self::usesDatabaseSessions(),
				'after_response' => self::responseFinisher() !== null,
				'sapi' => PHP_SAPI,
				'gc_enabled' => (int) ini_get('session.gc_probability') > 0,
				'lifetime' => defined('SESSION_LIFETIME') && (int) SESSION_LIFETIME > 0 ? (int) SESSION_LIFETIME : max(1440, (int) ini_get('session.gc_maxlifetime')),
				'last' => is_array($record) ? $record : null,
			);
		}

		protected static function recordSessionCleanup($deleted, $error)
		{
			try {
				Cache::set(self::sessionCleanupKey() . ':last', array('at' => time(), 'deleted' => (int) $deleted, 'error' => (string) $error), 604800);
			} catch (\Throwable $e) {
				error_log('Cannot record session cleanup result: ' . $e->getMessage());
			}
		}

		protected static function sessionCleanupKey()
		{
			return 'web-maintenance:sessions:v1:' . PublicShellTables::table('sessions');
		}

		protected static function usesDatabaseSessions()
		{
			$handler = defined('SESSION_SAVE_HANDLER') ? strtolower((string) SESSION_SAVE_HANDLER) : 'db';
			return !in_array($handler, array('file', 'files', 'native'), true);
		}

		protected static function responseFinisher()
		{
			if (PHP_SAPI === 'fpm-fcgi' && function_exists('fastcgi_finish_request')) { return 'fastcgi_finish_request'; }
			if (PHP_SAPI === 'litespeed' && function_exists('litespeed_finish_request')) { return 'litespeed_finish_request'; }
			return null;
		}

		protected static function task($label, callable $callback)
		{
			try {
				call_user_func($callback, self::BATCH_SIZE);
			} catch (\Throwable $e) {
				error_log('Web maintenance [' . $label . '] failed: ' . $e->getMessage());
			}
		}

		protected static function pruneFileSessions()
		{
			$handler = defined('SESSION_SAVE_HANDLER') ? strtolower((string) SESSION_SAVE_HANDLER) : '';
			if (!in_array($handler, array('file', 'files'), true)) { return; }

			$configured = (int) ini_get('session.gc_maxlifetime');
			$lifetime = defined('SESSION_LIFETIME') && (int) SESSION_LIFETIME > 0
				? (int) SESSION_LIFETIME
				: max(1440, $configured);
			SessionFiles::clearBounded(BASEPATH . SESSION_DIR, 'sess', $lifetime, 500);
		}
	}
