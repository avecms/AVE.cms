<?php

	/**
	 * AVE.cms
	 *
	 * @package AVE.cms
	 * @version 3.x
	 * @filesource
	 * @copyright © 2007-2014 AVE.cms, http://www.ave-cms.ru
	 *
	 * @license GPL v.2
	 */


	/**
	 * Запись события в лог
	 *
	 * @param string $message Текст сообщения
	 * @param int $typ тип сообщения
	 * @param int $rub номер рубрики
	 * @return void
	 */
	function reportLog(string $message, int $typ = 0, int $rub = 0)
	{
		$logfile = BASE_DIR . '/tmp/logs/log.csv';

		$logData = [
			'log_time'		=> time(),
			'log_ip'		=> $_SERVER['REMOTE_ADDR'],
			'log_url'		=> $_SERVER['REQUEST_URI'],
			'log_user_id'	=> ($_SESSION['user_id'] ?? '0'),
			'log_user_name'	=> ($_SESSION['user_name'] ?? 'Anonymous'),
			'log_text'		=> $message,
			'log_type'		=> (int)$typ,
			'log_rubric'	=> (int)$rub
		];

		if ($f_log = @fopen($logfile, 'ab'))
		{
			if (flock($f_log, LOCK_EX))
			{
				fputcsv($f_log, $logData);
				flock($f_log, LOCK_UN);
			}

			fclose($f_log);
		}
	}

	/**
	 * Запись события в лог для Sql ошибок
	 *
	 * @param string $message Текст сообщения
	 * @return void
	 */
	function reportSqlLog($message)
	{
		$logfile = BASE_DIR . '/tmp/logs/sql.csv';

		$logData = [
			'log_time'		=> time(),
			'log_ip'		=> $_SERVER['REMOTE_ADDR'],
			'log_url'		=> $_SERVER['REQUEST_URI'],
			'log_user_id'	=> $_SESSION['user_id'],
			'log_user_name'	=> $_SESSION['user_name'],
			'log_text'		=> base64_encode(serialize($message))
		];

		if ($f_log = @fopen($logfile, 'ab'))
		{
			if (flock($f_log, LOCK_EX))
			{
				fputcsv($f_log, $logData);
				flock($f_log, LOCK_UN);
			}

			fclose($f_log);
		}
	}

	/**
	 * Запись события в лог для 404 ошибок
	 *
	 * @param string $message Текст сообщения
	 * @return void
	 */
	function report404()
	{
		$logfile = BASE_DIR . '/tmp/logs/404.csv';

		$logData = [
			'log_time' 			=> time(),
			'log_ip' 			=> @$_SERVER['REMOTE_ADDR'],
			'log_query' 		=> @$_SERVER['REQUEST_URI'],
			'log_user_agent' 	=> @$_SERVER['HTTP_USER_AGENT'],
			'log_user_referer' 	=> ($_SERVER['HTTP_REFERER'] ?? ''),
			'log_request_uri' 	=> @$_SERVER['REQUEST_URI']
		];

		if ($f_log = @fopen($logfile, 'ab'))
		{
			if (flock($f_log, LOCK_EX))
			{
				fputcsv($f_log, $logData);
				flock($f_log, LOCK_UN);
			}

			fclose($f_log);
		}
	}

?>