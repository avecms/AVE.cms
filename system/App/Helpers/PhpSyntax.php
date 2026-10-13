<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Helpers/PhpSyntax.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Helpers;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	class PhpSyntax
	{
		public static function check($source, $lineOffset = 0)
		{
			if (!function_exists('token_get_all') || !defined('TOKEN_PARSE')) {
				return array('ok' => false, 'checked' => false, 'message' => 'Встроенная проверка PHP недоступна на сервере.', 'output' => '', 'line' => 0);
			}

			try {
				token_get_all((string) $source, TOKEN_PARSE);
			} catch (\ParseError $e) {
				$line = max(1, (int) $e->getLine() + (int) $lineOffset);
				return array('ok' => false, 'checked' => true, 'message' => 'В PHP-коде есть синтаксическая ошибка.', 'output' => $e->getMessage() . ' (строка ' . $line . ')', 'line' => $line);
			}

			return array('ok' => true, 'checked' => true, 'message' => 'Синтаксис PHP без ошибок.', 'output' => '', 'line' => 0);
		}
	}
