<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Common/CoreUpdate/ReleaseState.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Common\CoreUpdate;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	/** Installed release ledger and logical-to-physical core path resolver. */
	class ReleaseState
	{
		public static function source()
		{
			$release = is_file(BASEPATH . '/system/release.php')
				? require BASEPATH . '/system/release.php'
				: array();

			return array(
				'product' => isset($release['product']) ? (string) $release['product'] : 'AVE.cms',
				'version' => isset($release['version']) ? (string) $release['version'] : '3.3',
				'build' => isset($release['build']) ? (string) $release['build'] : '0.11',
			);
		}

		public static function installed()
		{
			$file = self::lockFile();
			$data = is_file($file) ? json_decode((string) file_get_contents($file), true) : array();
			if (!is_array($data)) { $data = array(); }
			$source = self::source();

			$data['version'] = isset($data['version']) ? (string) $data['version'] : $source['version'];
			$data['build'] = isset($data['build']) ? (string) $data['build'] : $source['build'];
			$data['admin_directory'] = self::adminDirectory($data);
			$data['core_fingerprint'] = isset($data['core_fingerprint']) ? (string) $data['core_fingerprint'] : '';
			return $data;
		}

		public static function identifier(array $release = array())
		{
			$release = $release ?: self::installed();
			return (isset($release['version']) ? (string) $release['version'] : '')
				. '-build-'
				. (isset($release['build']) ? (string) $release['build'] : '');
		}

		public static function matches(array $release)
		{
			return self::compatibility($release)['applicable'];
		}

		/** Определить, входит ли целевой релиз патча в уже установленную версию. */
		public static function targetInstalled(array $target, array $current = array())
		{
			$current = $current ?: self::installed();
			if (!isset($target['version'], $target['build'], $current['version'], $current['build'])) { return false; }
			$version = version_compare((string) $current['version'], (string) $target['version']);
			return $version > 0 || ($version === 0
				&& version_compare((string) $current['build'], (string) $target['build'], '>='));
		}

		/** Check a cumulative patch by version/build range without file fingerprints. */
		public static function cumulativeCompatibility(array $from, array $to, array $current = array())
		{
			$current = $current ?: self::installed();
			if (!isset($from['version'], $from['build'], $to['version'], $to['build'])) {
				return array('applicable' => false, 'code' => 'invalid_source', 'reason' => 'В патче не указан диапазон версий');
			}

			if ((string) $current['version'] !== (string) $from['version']
				|| (string) $current['version'] !== (string) $to['version']
				|| version_compare((string) $current['build'], (string) $from['build'], '<')
				|| version_compare((string) $current['build'], (string) $to['build'], '>=')) {
				return array(
					'applicable' => false,
					'code' => 'release_chain',
					'reason' => 'Патч поддерживает build от ' . (string) $from['build']
						. ' до ' . (string) $to['build'] . ', установлен build ' . (string) $current['build'],
				);
			}

			return array('applicable' => true, 'code' => 'ready', 'reason' => 'Кумулятивный патч подходит к установленной сборке');
		}

		/** Patch compatibility intentionally ignores local file fingerprints. */
		public static function patchCompatibility(array $from, array $to, $cumulative = false, array $current = array())
		{
			if ($cumulative) { return self::cumulativeCompatibility($from, $to, $current); }

			$current = $current ?: self::installed();
			$source = $from;
			$source['core_fingerprint'] = '';
			$current['core_fingerprint'] = '';
			return self::compatibility($source, $current);
		}

		/** Explain whether a patch source exactly matches the installed release. */
		public static function compatibility(array $release, array $current = array())
		{
			$current = $current ?: self::installed();
			if (!isset($release['version'], $release['build'])) {
				return array(
					'applicable' => false,
					'code' => 'invalid_source',
					'reason' => 'В каталоге не указана исходная версия патча',
				);
			}

			if ((string) $release['version'] !== (string) $current['version']
				|| (string) $release['build'] !== (string) $current['build']) {
				return array(
					'applicable' => false,
					'code' => 'release_chain',
					'reason' => 'Патч начинается с ' . (string) $release['version']
						. ' build ' . (string) $release['build']
						. ', установлено ' . (string) $current['version']
						. ' build ' . (string) $current['build'],
				);
			}

			$expectedFingerprint = isset($release['core_fingerprint'])
				? strtolower(trim((string) $release['core_fingerprint']))
				: '';
			$currentFingerprint = isset($current['core_fingerprint'])
				? strtolower(trim((string) $current['core_fingerprint']))
				: '';

			if ($expectedFingerprint !== '' && $currentFingerprint !== ''
				&& !hash_equals($expectedFingerprint, $currentFingerprint)) {
				return array(
					'applicable' => false,
					'code' => 'fingerprint_mismatch',
					'reason' => 'Номер build совпадает, но патч выпущен для другой сборки ядра',
				);
			}

			return array('applicable' => true, 'code' => 'ready', 'reason' => 'Патч подходит к установленной сборке');
		}

		public static function write(array $target, $fingerprint = '')
		{
			if (empty($target['version']) || !isset($target['build'])) {
				throw new \InvalidArgumentException('Целевая версия релиза не задана');
			}

			$data = self::installed();
			$data['version'] = (string) $target['version'];
			$data['build'] = (string) $target['build'];
			$data['core_fingerprint'] = (string) $fingerprint;
			$manifestFile = BASEPATH . '/PACKAGE-MANIFEST.json';
			$data['package_manifest_sha256'] = is_file($manifestFile) ? hash_file('sha256', $manifestFile) : '';
			$data['updated_at'] = date(DATE_ATOM);
			self::writeJson(self::lockFile(), $data, 0640);
			return $data;
		}

		public static function resolve($logicalPath)
		{
			$logicalPath = self::normalizeLogicalPath($logicalPath);
			if (strpos($logicalPath, '@admin/') === 0) {
				$relative = self::installed()['admin_directory'] . '/' . substr($logicalPath, 7);
			} else {
				$relative = $logicalPath;
			}

			return rtrim(str_replace('\\', '/', BASEPATH), '/') . '/' . $relative;
		}

		public static function normalizeLogicalPath($path)
		{
			$path = str_replace('\\', '/', trim((string) $path));
			if ($path === '' || $path[0] === '/' || strpos($path, "\0") !== false
				|| preg_match('#(^|/)(?:\.|\.\.)(?:/|$)#', $path)) {
				throw new \InvalidArgumentException('Некорректный путь файла обновления');
			}


			$relative = strpos($path, '@admin/') === 0 ? substr($path, 7) : $path;
			foreach (explode('/', $relative) as $segment) {
				if ($segment === '' || $segment === '.' || $segment === '..'
					|| preg_match('/[\x00-\x1F\x7F<>:"|?*]/', $segment)) {
					throw new \InvalidArgumentException('Некорректный путь файла обновления');
				}
			}

			return $path;
		}

		public static function protectedPath($logicalPath)
		{
			$path = self::normalizeLogicalPath($logicalPath);
			foreach (array(
				'configs/db.config.php',
				'configs/public.config.php',
				'storage/',
				'tmp/',
				'uploads/',
				'modules/',
				'templates/public/templates/',
			) as $protected) {
				if ($path === rtrim($protected, '/') || strpos($path, $protected) === 0) {
					return true;
				}
			}

			return false;
		}

		/** Installation defaults become project-owned and must survive core updates. */
		public static function preservedPath($logicalPath)
		{
			return in_array(strtolower(self::normalizeLogicalPath($logicalPath)), array('.htaccess', 'robots.txt'), true);
		}

		public static function lockFile()
		{
			return BASEPATH . '/storage/installed.lock';
		}

		public static function packageManifest()
		{
			$file = BASEPATH . '/PACKAGE-MANIFEST.json';
			$data = is_file($file) ? json_decode((string) file_get_contents($file), true) : array();
			return is_array($data) ? $data : array();
		}

		public static function logicalPackagePath($path)
		{
			$path = str_replace('\\', '/', trim((string) $path));
			return self::normalizeLogicalPath(strpos($path, 'adminx/') === 0 ? '@admin/' . substr($path, 7) : $path);
		}

		public static function optionalAfterInstallPath($path, array $manifest)
		{
			$path = self::normalizeLogicalPath($path);
			$prefixes = isset($manifest['optional_after_install']) && is_array($manifest['optional_after_install'])
				? $manifest['optional_after_install']
				: array();
			foreach ($prefixes as $prefix) {
				$prefix = str_replace('\\', '/', trim((string) $prefix));
				if ($prefix === '') { continue; }
				$prefix = rtrim(self::normalizeLogicalPath(rtrim($prefix, '/')), '/') . '/';
				if ($path === rtrim($prefix, '/') || strpos($path, $prefix) === 0) { return true; }
			}

			return false;
		}

		protected static function adminDirectory(array $installed)
		{
			$directory = isset($installed['admin_directory']) ? trim((string) $installed['admin_directory']) : '';
			if ($directory === '') {
				$config = is_file(BASEPATH . '/configs/public.config.php')
					? require BASEPATH . '/configs/public.config.php'
					: array();
				$directory = is_array($config) && isset($config['admin_directory'])
					? trim((string) $config['admin_directory'])
					: 'adminx';
			}

			return preg_match('/^[A-Za-z][A-Za-z0-9_-]{0,47}$/', $directory) ? $directory : 'adminx';
		}

		protected static function writeJson($file, array $data, $mode)
		{
			$payload = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
			$temporary = $file . '.tmp-' . bin2hex(random_bytes(6));
			if (file_put_contents($temporary, $payload, LOCK_EX) === false || !@rename($temporary, $file)) {
				@unlink($temporary);
				throw new \RuntimeException('Не удалось обновить реестр установленной версии');
			}

			@chmod($file, $mode);
		}
	}
