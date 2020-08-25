<?php

	// Проверка
	if (! defined('BASE_DIR'))
		exit('Access denied');

	/**
	 * This source file is part of the AVE.cms. More information,
	 * documentation and tutorials can be found at http://www.ave-cms.ru
	 *
	 * @package      AVE.cms
	 * @file         system/helpers/errors.php
	 * @author       @
	 * @copyright    2007-2016 (c) AVE.cms
	 * @link         http://www.ave-cms.ru
	 * @version      4.0
	 * @since        $date$
	 * @license      license GPL v.2 http://www.ave-cms.ru/license.txt
	*/

	class Errors
	{
		/**
		 * Errors constructor.
		 */
		public function __construct ()
		{
			set_error_handler([$this, 'scriptError']);
			register_shutdown_function([$this, 'shutDown']);
		}


		/**
		 * @param $errno
		 * @param $errstr
		 * @param $errfile
		 * @param $errline
		 */
		public function scriptError ($errno, $errstr, $errfile, $errline)
		{
			switch($errno)
			{
				case E_ERROR:
					$errseverity = 'Error';
					$color = '#f05050';
					break;

				case E_WARNING:
					$errseverity = 'Warning';
					$color = '#fad733';
					break;

				case E_NOTICE:
					$errseverity = 'Notice';
					$color = '#23b7e5';
					break;

				case E_CORE_ERROR:
					$errseverity = 'Core Error';
					$color = '#f05050';
					break;

				case E_CORE_WARNING:
					$errseverity = 'Core Warning';
					$color = '#fad733';
					break;

				case E_COMPILE_ERROR:
					$errseverity = 'Compile Error';
					$color = '#f05050';
					break;

				case E_COMPILE_WARNING:
					$errseverity = 'Compile Warning';
					$color = '#fad733';
					break;

				case E_USER_ERROR:
					$errseverity = 'User Error';
					$color = '#f05050';
					break;

				case E_USER_WARNING:
					$errseverity = 'User Warning';
					$color = '#fad733';
					break;

				case E_USER_NOTICE:
					$errseverity = 'User Notice';
					$color = '#23b7e5';
					break;

				case E_STRICT:
					$errseverity = 'Strict Standards';
					$color = '#edf1f2';
					break;

				case E_PARSE:
					$errseverity = 'Parse Error';
					$color = '#f05050';
					break;

				case E_RECOVERABLE_ERROR:
					$errseverity = 'Recoverable Error';
					$color = '#f05050';
					break;

				case E_DEPRECATED:
					$errseverity = 'Deprecated';
					$color = '#fad733';
					break;

				case E_USER_DEPRECATED:
					$errseverity = 'User Deprecated';
					$color = '#fad733';
					break;

				default:
					$errseverity = 'Error';
					$color = '#fad733';
					break;
			}

			$out  = '<div style="border: 1px solid ' . $color . '; margin: 10px; font-size: 11px; font-family: Consolas, Verdana, Arial; border-radius: 5px; box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.2);">';
			$out .= '<div style="background: ' . $color . '; color: #000; margin: 0; padding: 6px;">';
			$out .= '<strong>' . $errseverity . '</strong> Line <strong>' . $errline . '</strong>: ' . $errfile;
			$out .= '</div>';
			$out .= '<div style="background: #f0f0f0; color: #000; margin: 0; padding: 6px; text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);">';
			$out .= '['.$errno.'] '. $errstr;
			$out .= '</div>';
			$out .= '</div>';

			echo $out;
		}


		/**
		 *
		 */
		public function shutDown ()
		{
			if ($error = error_get_last())
			{
				if (! headers_sent())
					header('HTTP/1.1 500 Internal Server Error');

				switch($error['type'])
				{
					case E_ERROR:
					case E_PARSE:
					case E_STRICT:
					case E_CORE_ERROR:
					case E_CORE_WARNING:
					case E_COMPILE_ERROR:
					case E_COMPILE_WARNING:
					case E_USER_ERROR:
					case E_RECOVERABLE_ERROR:
						$this->scriptError($error['type'], $error['message'], $error['file'], $error['line']);
						break;
				}
			}
		}
	}