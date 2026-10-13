<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/Support/PhpCode.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Adminx\Support;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Common\CliRuntime;
	use App\Common\PublicConfiguration;
	use App\Helpers\PhpSyntax;

	class PhpCode
	{
		public static function source($code)
		{
			$code = trim((string) $code);
			if (preg_match('/^<\?(?:php|=)?/i', $code)) { return $code; }
			return "<?php\n" . $code . "\n";
		}

		public static function lint($code)
		{
			$code = trim((string) $code);
			$offset = preg_match('/^<\?(?:php|=)?/i', $code) ? 0 : -1;
			return PhpSyntax::check(self::source($code), $offset);
		}

		public static function cliBinary()
		{
			$config = PublicConfiguration::value('admin_console', array());
			$preferred = array(trim((string) getenv('ADMINX_PHP_BINARY')));
			if (is_array($config) && !empty($config['php_binary'])) { $preferred[] = trim((string) $config['php_binary']); }
			try { return CliRuntime::php($preferred); }
			catch (\RuntimeException $e) { throw new \RuntimeException($e->getMessage() . ' Для панели также доступны ADMINX_PHP_BINARY и admin_console.php_binary.'); }
		}
	}
