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

class AVE_Session_DB
{

	public $sess_lifetime;

	/**
	 * Хост
	 *
	 * @var string
	 */
	protected $db_host;

	/**
	 * Имя пользователя
	 *
	 * @var string
	 */
	protected $db_user;

	/**
	 * Пароль
	 *
	 * @var string
	 */
	protected $db_pass;

	/**
	 * Имя текущей БД.
	 *
	 * @var string
	 */
	protected $db_dbase;

	/**
	 * Префикс БД.
	 *
	 * @var string
	 */
	protected $db_prefix;

	private $mysql_connect = null;
	private $mysql_db = null;

	/* Create a connection to a database */
	function __construct()
	{
		// Подключаем конфигурационный файл с параметрами подключения
		require (BASE_DIR . '/inc/db.config.php');

		$this->db_host = $config['dbhost'];
		$this->db_user = $config['dbuser'];
		$this->db_pass = $config['dbpass'];
		$this->db_dbase = $config['dbname'];
		$this->db_prefix = $config['dbpref'];

		$this->sess_lifetime = (defined('SESSION_LIFETIME') && is_numeric(SESSION_LIFETIME))
			? SESSION_LIFETIME
			: (get_cfg_var("session.gc_maxlifetime") < 1440
				? 1440
				: get_cfg_var("session.gc_maxlifetime"));

		if (! $this->mysql_connect = mysqli_connect ($this->db_host, $this->db_user, $this->db_pass))
		{
			$this->error();
		}

		if (! $this->mysql_db = mysqli_select_db ($this->mysql_connect, $this->db_dbase))
		{
			$this->error();
		}

		$this->mysql_connect->set_charset('utf8');

		return true;
	}

	/* Open session */
	function _open($path, $name)
	{
		return true;
	}

	/* Close session */
	function _close()
	{
		@mysqli_query($this->mysql_connect, "DELETE FROM " . PREFIX . "_sessions WHERE expiry < '" . time() . "'");

		if ($this->mysql_connect !== null)
			@mysqli_close($this->mysql_connect);

		return true;
	}

	/* Read session */
	function _read($ses_id)
	{
		$qid = @mysqli_query($this->mysql_connect, "SELECT value, Ip FROM " . PREFIX . "_sessions WHERE sesskey = '" . $ses_id . "' AND expiry > '" . time() . "'");

		if ((list($value, $ip) = @mysqli_fetch_row($qid)) && $ip == $_SERVER['REMOTE_ADDR'])
		{
			return $value;
		}

		return '';
	}

	/* Write new data */
	function _write($ses_id, $data)
	{
		if (! $qid = @mysqli_query($this->mysql_connect, "INSERT INTO ".PREFIX."_sessions VALUES ('".$ses_id."', ".(time()+$this->sess_lifetime).", '".addslashes($data)."', '".$_SERVER['REMOTE_ADDR']."', FROM_UNIXTIME(expiry, '%d.%m.%Y, %H:%i:%s'))"))
		{
			$qid = @mysqli_query($this->mysql_connect, "UPDATE ".PREFIX."_sessions SET expiry = ".(time()+$this->sess_lifetime).", expire_datum = FROM_UNIXTIME(expiry,'%d.%m.%Y, %H:%i:%s'), value = '".addslashes($data)."', Ip = '".$_SERVER['REMOTE_ADDR']."' WHERE sesskey = '".$ses_id."' AND expiry > '".time()."'");
		}

		return $qid;
	}

	/* Destroy session */
	function _destroy($ses_id)
	{
		return @mysqli_query($this->mysql_connect, "DELETE FROM ".PREFIX."_sessions WHERE sesskey = '".$ses_id."'");
	}

	/* Garbage collection, deletes old sessions */
	function _gc($maxlifetime)
	{
		$session_res = @mysqli_query($this->mysql_connect, "DELETE FROM ".PREFIX."_sessions WHERE expire < (UNIX_TIMESTAMP(NOW()) - " . (int)$maxlifetime . ")");

		if (!$session_res) {
			return false;
		}
		else
		{
			return true;
		}
	}

	function error() {
		ob_start();
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Status: 503 Service Temporarily Unavailable');
		header('Retry-After: 3600');
		header('X-Powered-By:');
		display_notice("Error connect to MySQL.");
		die;
	}
}
?>