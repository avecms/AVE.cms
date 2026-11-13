<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Common/Session/SessionDB.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Common\Session;

	defined("BASEPATH") || die ('Direct access to this location is not allowed.');

	use App\Content\PublicShellTables;
	use App\Common\WebMaintenance;
	use DB;

	class SessionDB
	{
		private static $sessLifetime;
		private static $instance = null;

		private function __construct()
		{
			register_shutdown_function('session_write_close');

			$configured = (int) ini_get('session.gc_maxlifetime');
			$defined    = defined('SESSION_LIFETIME') && is_numeric(SESSION_LIFETIME) ? (int) SESSION_LIFETIME : 0;

			self::$sessLifetime = $defined > 0 ? $defined : max($configured, 1440);
		}

		public static function init()
		{
			if (is_null(self::$instance)) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		public static function open($path, $name)
		{
			return true;
		}

		public static function close()
		{
			return true;
		}

		public static function read($sessionId)
		{
			$sql = "SELECT value, Ip FROM " . PublicShellTables::table('sessions') . "
                WHERE sesskey = %s AND expiry > %i";

			$row = DB::Query($sql, $sessionId, time())->getAssoc();

			$ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
			return $row && (string) $row['Ip'] === $ip ? (string) $row['value'] : '';
		}

		public static function write($sessionId, $data)
		{
			// Атомарный upsert — без двух отдельных запросов
			$expiry = time() + self::$sessLifetime;
			$sql = "INSERT INTO " . PublicShellTables::table('sessions') . " (sesskey, expiry, value, Ip, expire_datum)
                VALUES (%s, %i, %s, %s, %s)
                ON DUPLICATE KEY UPDATE
                    value  = VALUES(value),
                    expiry = VALUES(expiry),
                    Ip = VALUES(Ip),
                    expire_datum = VALUES(expire_datum)";

			DB::Query($sql, $sessionId, $expiry, $data, isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '', date('d.m.Y, H:i:s', $expiry));

			return true;
		}

		public static function destroy($sessionId)
		{
			return DB::Delete(PublicShellTables::table('sessions'), 'sesskey = %s', $sessionId);
		}

		public static function gc($maxlifetime)
		{
			try {
				if (!WebMaintenance::scheduleSessionCleanup()) {
					WebMaintenance::pruneDatabaseSessions();
				}

				return true;
			} catch (\Throwable $e) {
				error_log('Session garbage collection failed: ' . $e->getMessage());
				return false;
			}
		}

		/** Delete one indexed batch; never rebuild or optimize the session table here. */
		public static function pruneExpired($limit = 1000, $cutoff = null)
		{
			$limit = max(1, min(1000, (int) $limit));
			$table = PublicShellTables::table('sessions');
			$previousMode = DB::$throw_exception_on_error;
			DB::$throw_exception_on_error = true;
			$timeouts = null;

			try {
				$timeouts = DB::query('SELECT @@SESSION.innodb_lock_wait_timeout row_wait, @@SESSION.lock_wait_timeout metadata_wait')->getAssoc();
				DB::query('SET SESSION innodb_lock_wait_timeout=1, SESSION lock_wait_timeout=1');
				$expiryIndex = self::expiryIndex($table);
				$cutoff = self::expirationCutoff($cutoff);
				$keys = array_column(DB::query('SELECT sesskey FROM %b FORCE INDEX (%b) WHERE expiry <= %i ORDER BY expiry ASC LIMIT %i', $table, $expiryIndex, $cutoff, $limit)->getAll(), 'sesskey');
				if (!$keys) { return 0; }
				// Recheck expiry after selection: a concurrent request may renew a session.
				DB::query('DELETE s FROM %b s FORCE INDEX (PRIMARY) WHERE s.sesskey IN %ls AND s.expiry <= %i', $table, $keys, $cutoff);
				return (int) DB::affectedRows();
			} finally {
				try {
					if ($timeouts) {
						DB::query('SET SESSION innodb_lock_wait_timeout=%i, SESSION lock_wait_timeout=%i', (int) $timeouts['row_wait'], (int) $timeouts['metadata_wait']);
					}
				} finally {
					DB::$throw_exception_on_error = $previousMode;
				}
			}
		}

		/** Exact index-only counts for the maintenance screen, not the public request path. */
		public static function expirationStats()
		{
			$table = PublicShellTables::table('sessions');
			$cutoff = time();
			$row = DB::query('SELECT COUNT(*) total, COALESCE(SUM(expiry <= %i),0) expired FROM %b FORCE INDEX (%b)', $cutoff, $table, self::expiryIndex($table))->getAssoc();
			$expired = (int) $row['expired'];
			return array('expired' => $expired, 'active' => (int) $row['total'] - $expired, 'cutoff' => $cutoff);
		}

		public static function hasExpired($cutoff = null)
		{
			$table = PublicShellTables::table('sessions');
			return (bool) DB::query('SELECT 1 FROM %b FORCE INDEX (%b) WHERE expiry <= %i LIMIT 1', $table, self::expiryIndex($table), self::expirationCutoff($cutoff))->getValue();
		}

		protected static function expirationCutoff($cutoff)
		{
			return $cutoff === null ? time() : max(0, min(time(), (int) $cutoff));
		}

		protected static function expiryIndex($table)
		{
			foreach (DB::query('SHOW INDEX FROM %b', $table)->getAll() as $index) {
				if ((int) $index['Seq_in_index'] === 1 && strtolower((string) $index['Column_name']) === 'expiry'
					&& strtoupper((string) $index['Index_type']) === 'BTREE') {
					return (string) $index['Key_name'];
				}
			}

			throw new \RuntimeException('Session cleanup requires an index starting with expiry in ' . $table);
		}

		public function __destruct()
		{
			//
		}
	}
