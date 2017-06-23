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
 * @return
 */
function reportLog($message, $typ = 0, $rub = 0)
{
	$logdata=array();

	$logfile=BASE_DIR.'/cache/log.php';
	if(file_exists($logfile))
		@eval('?>'.file_get_contents($logfile).'<?');
	$logdata[]=array(
		'log_time'		=>time(),
		'log_ip'		=>$_SERVER['REMOTE_ADDR'],
		'log_url'		=>$_SERVER['QUERY_STRING'],
		'log_user_id'	=>(isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '0'),
		'log_user_name'	=>(isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Anonymous'),
		'log_text'		=>$message,
		'log_type'		=>(int)$typ,
		'log_rubric'	=>(int)$rub
	);
	$messlimit = 1000;
	$logdata = array_slice($logdata,-1*$messlimit);
	file_put_contents($logfile,'<?php $logdata=' . var_export($logdata,true) . ' ?>');
}

/**
 * Запись события в лог для Sql ошибок
 *
 * @param string $message Текст сообщения
 * @return
 */
function reportSqlLog($message)
{
	$logsql = array();

	$logfile = BASE_DIR . '/cache/sql.php';

	if(file_exists($logfile))
		@eval('?>'.file_get_contents($logfile).'<?');

	$logsql[] = array(
		'log_time'		=>time(),
		'log_ip'		=>$_SERVER['REMOTE_ADDR'],
		'log_url'		=>$_SERVER['QUERY_STRING'],
		'log_user_id'	=>$_SESSION['user_id'],
		'log_user_name'	=>$_SESSION['user_name'],
		'log_text'		=>$message
	);

	$messlimit = 1000;

	$logsql = array_slice($logsql,-1*$messlimit);

	file_put_contents($logfile, '<?php $logsql = ' . var_export($logsql, true) . ' ?>');
}

/**
 * Запись события в лог для 404 ошибок
 *
 * @param string $message Текст сообщения
 * @return
 */
function report404()
{
	$log404 = array();

	$logfile = BASE_DIR . '/cache/404.php';

	if(file_exists($logfile))
		@include($logfile);

	$log404[] = array(
		'log_time' 			=> time(),
		'log_ip' 			=> @$_SERVER['REMOTE_ADDR'],
		'log_query' 		=> @$_SERVER['QUERY_STRING'],
		'log_user_agent' 	=> @$_SERVER['HTTP_USER_AGENT'],
		'log_user_referer' 	=> (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''),
		'log_request_uri' 	=> @$_SERVER['REQUEST_URI']
	);

	$messlimit = 1000;

	$log404 = array_slice($log404, -1*$messlimit);

	file_put_contents($logfile,'<?php $log404=' . var_export($log404, true) . ' ?>');
}

?>