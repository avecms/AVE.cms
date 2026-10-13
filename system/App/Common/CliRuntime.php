<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Common/CliRuntime.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Common;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	class CliRuntime
	{
		public static function php(array $preferred = array(), $minimumVersion = 70300)
		{
			if (!function_exists('proc_open')) { throw new \RuntimeException('proc_open недоступен: запуск PHP CLI невозможен.'); }
			$runtime = array(
				'binary' => defined('PHP_BINARY') ? PHP_BINARY : '',
				'bindir' => defined('PHP_BINDIR') ? PHP_BINDIR : '',
				'ini' => php_ini_loaded_file() ?: '',
				'path' => (string) getenv('PATH'),
				'windows' => DIRECTORY_SEPARATOR === '\\',
				'version' => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
			);
			$preferred[] = trim((string) getenv('PHP_CLI_BINARY'));
			foreach (self::phpCandidates($preferred, $runtime) as $candidate) {
				if (!self::isPhpName($candidate) || !is_file($candidate) || (!$runtime['windows'] && !is_executable($candidate))) { continue; }
				if (self::probe($candidate) >= (int) $minimumVersion) { return $candidate; }
			}

			$version = (int) floor((int) $minimumVersion / 10000) . '.' . (int) floor(((int) $minimumVersion % 10000) / 100);
			throw new \RuntimeException('Консольный PHP ' . $version . ' или новее не найден. Укажите полный путь к php или php.exe в PHP_CLI_BINARY.');
		}

		public static function findCommand($name, array $preferred = array())
		{
			if (!preg_match('/^[A-Za-z0-9_.-]+$/', (string) $name)) { throw new \InvalidArgumentException('Invalid command name'); }
			$windows = DIRECTORY_SEPARATOR === '\\';
			$extensions = $windows ? explode(';', (string) getenv('PATHEXT')) : array();
			$paths = explode($windows ? ';' : ':', (string) getenv('PATH'));
			foreach (self::commandCandidates($name, $paths, $extensions, $windows, $preferred) as $candidate) {
				if (is_file($candidate) && ($windows || is_executable($candidate))) { return $candidate; }
			}

			return '';
		}

		protected static function phpCandidates(array $preferred, array $runtime)
		{
			$windows = !empty($runtime['windows']);
			$separator = $windows ? '\\' : '/';
			$name = $windows ? 'php.exe' : 'php';
			$out = $preferred;
			if (self::isPhpName($runtime['binary'])) { $out[] = $runtime['binary']; }
			foreach (array($runtime['bindir'], dirname(str_replace('\\', '/', (string) $runtime['ini'])), dirname(str_replace('\\', '/', (string) $runtime['binary']))) as $directory) {
				if ($directory === '' || $directory === '.') { continue; }
				$directory = $windows ? str_replace('/', '\\', $directory) : $directory;
				$out[] = rtrim($directory, '/\\') . $separator . $name;
				$out[] = rtrim($directory, '/\\') . $separator . 'bin' . $separator . $name;
			}

			$paths = explode($windows ? ';' : ':', (string) $runtime['path']);
			$out = array_merge($out, self::commandCandidates($name, $paths, array(), $windows));
			if ($windows && preg_match('~^(.*/modules)/~i', str_replace('\\', '/', (string) $runtime['binary']), $match)) {
				$found = array();
				foreach (array('/php/*/php.exe', '/php/*/bin/php.exe', '/PHP*/php.exe', '/PHP*/bin/php.exe') as $suffix) {
					$found = array_merge($found, glob($match[1] . $suffix) ?: array());
				}

				$version = isset($runtime['version']) ? preg_quote($runtime['version'], '~') : '';
				if ($version !== '') {
					$version = str_replace('\\.', '[._-]', $version);
					usort($found, function ($a, $b) use ($version) { return (int) preg_match('~' . $version . '~', $b) - (int) preg_match('~' . $version . '~', $a); });
				}

				$out = array_merge($out, $found);
			}

			return array_values(array_unique(array_filter($out, function ($candidate) { return trim((string) $candidate) !== ''; })));
		}

		protected static function commandCandidates($name, array $paths, array $extensions, $windows, array $preferred = array())
		{
			$suffixes = array('');
			if ($windows && pathinfo((string) $name, PATHINFO_EXTENSION) === '') {
				foreach (array_merge($extensions, array('.EXE', '.COM', '.BAT', '.CMD')) as $extension) {
					$extension = trim((string) $extension);
					if (preg_match('/^\.[A-Za-z0-9]+$/', $extension)) { $suffixes[] = strtolower($extension); }
				}
			}

			$out = $preferred;
			foreach ($paths as $directory) {
				$directory = trim((string) $directory, " \t\n\r\0\x0B\"");
				if ($directory === '') { continue; }
				foreach (array_unique($suffixes) as $suffix) { $out[] = rtrim($directory, '/\\') . ($windows ? '\\' : '/') . $name . $suffix; }
			}

			return array_values(array_unique($out));
		}

		protected static function isPhpName($path)
		{
			return preg_match('/^php(?:[0-9]+(?:\.[0-9]+)*)?(?:\.exe)?$/i', basename(str_replace('\\', '/', (string) $path))) === 1;
		}

		protected static function probe($binary)
		{
			$command = escapeshellarg((string) $binary) . ' -n -r ' . escapeshellarg('echo PHP_SAPI,chr(124),PHP_VERSION_ID;');
			$pipes = array();
			try {
				$process = @proc_open($command, array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes, null, null, array('bypass_shell' => DIRECTORY_SEPARATOR === '\\'));
			} catch (\Throwable $e) { return 0; }
			if (!is_resource($process)) { return 0; }
			fclose($pipes[0]);
			stream_set_blocking($pipes[1], false);
			stream_set_blocking($pipes[2], false);
			$start = microtime(true); $stdout = ''; $stderr = ''; $exitCode = null; $timedOut = false;
			do {
				$stdout .= (string) stream_get_contents($pipes[1]);
				$stderr .= (string) stream_get_contents($pipes[2]);
				$status = proc_get_status($process);
				if (!$status['running']) { $exitCode = (int) $status['exitcode']; break; }
				if (microtime(true) - $start >= 2 || strlen($stdout) + strlen($stderr) > 8192) { $timedOut = true; proc_terminate($process, 9); break; }
				usleep(20000);
			} while (true);
			$stdout .= (string) stream_get_contents($pipes[1]);
			$stderr .= (string) stream_get_contents($pipes[2]);
			fclose($pipes[1]); fclose($pipes[2]);
			$closed = proc_close($process);
			$exitCode = $exitCode !== null && $exitCode >= 0 ? $exitCode : $closed;
			if ($timedOut || $exitCode !== 0 || trim($stderr) !== '' || !preg_match('/^cli\|([0-9]{5,6})$/', trim($stdout), $match)) { return 0; }
			return (int) $match[1];
		}
	}
