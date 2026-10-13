<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Blocks/Syntax.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Adminx\Blocks;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Helpers\PhpSyntax;

	class Syntax
	{
		public static function check($code, $eval)
		{
			if ((string) $eval !== '1') {
				return array(
					'ok' => true,
					'checked' => false,
					'message' => 'Проверка PHP не нужна: Eval отключён.',
					'line' => 0,
					'output' => '',
				);
			}

			$result = PhpSyntax::check('<?php ?>' . (string) $code);
			return $result['checked'] ? $result : self::unavailable($result['message']);
		}

		protected static function unavailable($message)
		{
			return array(
				'ok' => true,
				'checked' => false,
				'message' => $message,
				'line' => 0,
				'output' => '',
			);
		}
	}
