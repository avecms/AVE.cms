<?php

	/**
	 * AVE.cms
	 *
	 * Класс, предназначенный для управления журналом системных сообщений
	 *
	 * @package AVE.cms
	 * @version 3.x
	 * @filesource
	 * @copyright © 2007-2014 AVE.cms, http://www.ave-cms.ru
	 *
	 */

	class AVE_Logs
	{

	/**
	 *	Свойства класса
	 */

		/**
		 * Файлы для хранения записей
		 *
		 * @public
		 */
		public $_404dir = '/tmp/logs/404.csv';
		public $_logdir = '/tmp/logs/log.csv';
		public $_sqldir = '/tmp/logs/sql.csv';

	/**
	 *	Внутренние методы класса
	 */


	/**
	 *	Внешние методы класса
	 */

		/**
		 * Метод, предназначенный для отображения всех записей Журнала событий
		 *
		 */
		function logList ()
		{
			global $AVE_Template;

			$file_name = BASE_DIR . $this->_logdir;
			$_lines = [];

			if (file_exists($file_name) && $fp = @fopen($file_name, 'rb'))
			{
				$_count = 10000;

				$_size = @filesize($file_name);

				$_slice = 10240;

				$_size > $_slice && fseek($fp, $_size - $_slice);

				while (!feof($fp))
				{
					$event = fgetcsv($fp, $_slice);

					if (empty($event[0]) || count($event) < 3) {
						continue;
					}

					$_lines[] = [
						'log_time'		=> $event['0'],
						'log_ip'		=> $event['1'],
						'log_url'		=> $event['2'],
						'log_user_id'	=> $event['3'],
						'log_user_name'	=> $event['4'],
						'log_text'		=> $event['5'],
						'log_type'		=> $event['6'],
						'log_rubric'	=> $event['7']
					];
				}

				count($_lines) > $_count && $_lines = array_slice($_lines, -$_count);
			}

			// Передаем данные в шаблон для вывода и отображаем страницу
			$AVE_Template->assign('logs', $_lines);
			$AVE_Template->assign('content', $AVE_Template->fetch('logs/logs.tpl'));
		}

		/**
		 * Метод, предназначенный для отображения всех записей Журнала событий 404
		 *
		 */
		function List404()
		{
			global $AVE_Template;

			$file_name = BASE_DIR . $this->_404dir;
			$_lines = [];

			if (file_exists($file_name) && $fp = @fopen($file_name, 'rb'))
			{
				$_count = 10000;

				$_size = @filesize($file_name);

				$_slice = 10240;

				$_size > $_slice && fseek($fp, $_size - $_slice);

				while (!feof($fp))
				{
					$event = fgetcsv($fp, $_slice);

					if (empty($event[0]) || count($event) < 3) {
						continue;
					}

					$_lines[] = [
						'log_time'		    => $event['0'],
						'log_ip'		    => $event['1'],
						'log_query'		    => $event['2'],
						'log_user_agent'	    => $event['3'],
						'log_user_referer'	    => $event['4'],
						'log_request_uri'		=> $event['5']
					];
				}

				count($_lines) > $_count && $_lines = array_slice($_lines, -$_count);
			}

			// Передаем данные в шаблон для вывода и отображаем страницу
			$AVE_Template->assign('logs', $_lines);
			$AVE_Template->assign('content', $AVE_Template->fetch('logs/404.tpl'));
		}

		/**
		 * Метод, предназначенный для отображения всех записей Журнала событий 404
		 *
		 */
		function ListSql()
		{
			global $AVE_Template;

			$file_name = BASE_DIR . $this->_sqldir;
			$_lines = [];

			if (file_exists($file_name) && $fp = @fopen($file_name, 'rb'))
			{
				$_count = 10000;

				$_size = @filesize($file_name);

				$_slice = 10240;

				$_size > $_slice && fseek($fp, $_size - $_slice);

				while (!feof($fp))
				{
					$event = fgetcsv($fp, $_slice);

					if (empty($event[0]) || count($event) < 3) {
						continue;
					}

					$_lines[] = [
						'log_time'		=> $event['0'],
						'log_ip'		=> $event['1'],
						'log_url'		=> $event['2'],
						'log_user_id'	=> $event['3'],
						'log_user_name'	=> $event['4'],
						'log_text'		=> unserialize(base64_decode($event['5']))
					];
				}

				count($_lines) > $_count && $_lines = array_slice($_lines, -$_count);
			}

			// Передаем данные в шаблон для вывода и отображаем страницу
			$AVE_Template->assign('logs', $_lines);
			$AVE_Template->assign('content', $AVE_Template->fetch('logs/sql.tpl'));
		}

		/**
		 * Метод, предназначенный для удаление записей Журнала событий
		 *
		 */
		function logDelete()
		{
			global $AVE_Template;

			$logfile = BASE_DIR . $this->_logdir;

			if(file_exists($logfile))
				unlink($logfile);

			// Сохраняем системное сообщение в журнал
			reportLog($AVE_Template->get_config_vars('LOGS_CLEAN'));

			header('Location:index.php?do=logs&cp=' . SESSION);
			exit;
		}

		/**
		 * Метод, предназначенный для удаление записей Журнала событий 404
		 *
		 */
		function DeleteSql()
		{
			global $AVE_Template;

			$logfile = BASE_DIR . $this->_sqldir;

			if(file_exists($logfile))
				unlink($logfile);

			// Сохраняем системное сообщение в журнал
			reportLog($AVE_Template->get_config_vars('LOGS_SQL_CLEAN'));

			header('Location:index.php?do=logs&action=logsql&cp=' . SESSION);
			exit;
		}

		/**
		 * Метод, предназначенный для удаление записей Журнала событий 404
		 *
		 */
		function Delete404()
		{
			global $AVE_Template;

			$logfile = BASE_DIR . $this->_404dir;

			if(file_exists($logfile))
				unlink($logfile);

			// Сохраняем системное сообщение в журнал
			reportLog($AVE_Template->get_config_vars('LOGS_404_CLEAN'));

			header('Location:index.php?do=logs&action=log404&cp=' . SESSION);
			exit;
		}

		/**
		 * Метод, предназначенный для экспорта системных сообщений
		 *
		 */
		function logExport()
		{
			global $AVE_Template;

			$file_name = BASE_DIR . $this->_logdir;
			$dateName = 'system_log_' . date('dmyhis', time()) . '.csv';

			// Определяем заголовки документа
			header('Content-Encoding: windows-1251');
			header('Content-type: text/csv; charset=windows-1251');
			header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			header('Content-Disposition: attachment; filename="' . $dateName . '"');
			header('Content-Length: ' . filesize($file_name));
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');

			// Выводим данные
			readfile($file_name);

			// Сохраняем системное сообщение в журнал
			reportLog($AVE_Template->get_config_vars('LOGS_EXPORT'));

			exit;
		}

		/**
		 * Метод, предназначенный для экспорта сообщений 404
		 *
		 */
		function Export404()
		{
			global $AVE_Template;

			$file_name = BASE_DIR . $this->_404dir;
			$dateName = 'system_log_' . date('dmyhis', time()) . '.csv';

			// Определяем заголовки документа
			header('Content-Encoding: windows-1251');
			header('Content-type: text/csv; charset=windows-1251');
			header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			header('Content-Disposition: attachment; filename="' . $dateName . '"');
			header('Content-Length: ' . filesize($file_name));
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');

			// Выводим данные
			readfile($file_name);

			// Сохраняем системное сообщение в журнал
			reportLog($AVE_Template->get_config_vars('LOGS_EXPORT'));

			exit;
		}

		/**
		 * Метод, предназначенный для экспорта сообщений 404
		 *
		 */
		function ExportSql()
		{
			global $AVE_Template;

			$file_name = BASE_DIR . $this->_sqldir;
			$dateName = 'system_log_' . date('dmyhis', time()) . '.csv';

			// Определяем заголовки документа
			header('Content-Encoding: windows-1251');
			header('Content-type: text/csv; charset=windows-1251');
			header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			header('Content-Disposition: attachment; filename="' . $dateName . '"');
			header('Content-Length: ' . filesize($file_name));
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');

			// Выводим данные
			readfile($file_name);

			// Сохраняем системное сообщение в журнал
			reportLog($AVE_Template->get_config_vars('LOGS_EXPORT'));

			exit;
		}
	}