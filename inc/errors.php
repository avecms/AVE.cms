<?php

	// Проверка
	if (! defined('BASE_DIR'))
		exit('Access denied');

	/**
	 * This source file is part of the AVE.cms. More information,
	 * documentation and tutorials can be found at http://www.ave-cms.ru
	 *
	 * @package      AVE.cms
	 * @file         includes/error.php
	 * @author       @
	 * @copyright    2007-2015 (c) AVE.cms
	 * @link         http://www.ave-cms.ru
	 * @version      4.0
	 * @since        $date$
	 * @license      license GPL v.2 http://www.ave-cms.ru/license.txt
	*/

	set_error_handler("errorHandler");
	register_shutdown_function("shutdownHandler");

	function errorHandler($error_level, $error_message, $error_file, $error_line, $error_context)
	{
		$error =
		sprintf('
			Lvl: <strong>%s</strong><br>Message: <strong>%s</strong><br>File: <strong>%s</strong><br>Line: <strong>%s</strong>
		', $error_level, nl2br($error_message), $error_file, $error_line);

		switch ($error_level) {
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_PARSE:
				$color = '#f05050';
				errorLogs($error, "Fatal", $color);
				break;
			case E_USER_ERROR:
			case E_RECOVERABLE_ERROR:
				$color = '#f05050';
				errorLogs($error, "Error", $color);
				break;
			case E_WARNING:
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			case E_USER_WARNING:
				$color = '#fad733';
				errorLogs($error, "Warning", $color);
				break;
			case E_NOTICE:
			case E_USER_NOTICE:
				$color = '#23b7e5';
				errorLogs($error, "Info", $color);
				break;
			case E_STRICT:
				$color = '#edf1f2';
				errorLogs($error, "Debug", $color);
				break;
			default:
				$color = '#fad733';
				errorLogs($error, "Warning", $color);
		}
	}

	function shutdownHandler()
	{
		$lasterror = error_get_last();

		switch ($lasterror['type'])
		{
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
			case E_RECOVERABLE_ERROR:
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			case E_PARSE:
				$color = '#f05050';
				$error =
				sprintf('
					[SHUTDOWN] Lvl: <strong>%s</strong><br>Message: <strong>%s</strong><br>File: <strong>%s</strong><br>Line: <strong>%s</strong>
				', $lasterror['type'], nl2br($lasterror['message']), $lasterror['file'], $lasterror['line']);
				errorLogs($error, "Fatal", $color);
		}
	}

	function errorLogs($error, $errlvl, $color)
	{
		$render = '
			<div style="border: 1px solid '.$color.'; margin: 5px; font-size: 11px; font-family: Consolas, Verdana, Arial; border-radius: 3px;">
				<div style="background: '.$color.'; color: #000; margin: 0; padding: 5px;">
					' . $errlvl .'
				</div>
				<div style="background:#f0f0f0; color: #000; margin: 0; padding: 5px;">'
				. $error .
				'</div>
			</div>
		';

		echo $render;
	}
?>