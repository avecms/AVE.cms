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

class AVE_Session
{

	public $sess_lifetime;

	function __construct()
	{
		ini_set('session.save_handler', 'user');

		$this->sess_lifetime = (defined('SESSION_LIFETIME') && is_numeric(SESSION_LIFETIME))
			? SESSION_LIFETIME
			: (get_cfg_var("session.gc_maxlifetime") < 1440 ? 1440 : get_cfg_var("session.gc_maxlifetime"));

		return true;
	}

	/* Open session */
	function _open($sess_save_path, $session_name)
	{
		global $sess_save_path, $sess_session_name;

		$sess_save_path = BASE_DIR . '/session';
		$sess_session_name = $session_name;

		return true;
	}

	/* Close session */
	function _close()
	{
		$this->_gc($this->sess_lifetime);
		return true;
	}

	/* Read session */
	function _read($id)
	{
		global $sess_save_path, $sess_session_name, $sess_session_id;

		$sess_session_id = $id;
		$sess_file = $this->_folder() . '/' . $id . '.sess';

		if (!file_exists($sess_file)) return "";

		if ($fp = @fopen($sess_file, "r"))
		{
			$sess_data = fread($fp, filesize($sess_file));
			return($sess_data);
		}
		else
		{
			return '';
		}
	}

	/* Write new data */
	function _write ($id, $sess_data)
	{
		global $sess_save_path, $sess_session_name, $sess_session_id;

		$sess_session_id = $id;
		$sess_file = $this->_folder() . '/' . $id . '.sess';

		if(!file_exists($this->_folder()))
			mkdir($this->_folder(), 0777, true);

		if ($fp = @fopen($sess_file, "w"))
		{
			return fwrite($fp, $sess_data);
		}
		else
		{
			return false;
		}
	}

	/* Destroy session */
	function _destroy ($id)
	{
		global $sess_save_path, $sess_session_name, $sess_session_id;

		$sess_session_id = $id;
		$sess_dir = $this->_folder();
		$sess_file = $sess_dir . '/' . $id . '.sess';

		return @unlink($sess_file);
	}

	/* Garbage collection, deletes old sessions */
	function _gc ($maxlifetime)
	{
		global $sess_save_path, $sess_session_id;

		$this->_clear($sess_save_path, 'sess', $maxlifetime);

		return true;
	}

	function _clear($dir, $mask, $maxlifetime)
	{
		foreach(glob($dir . '/*') as $filename) {

			if(strtolower(substr($filename, strlen($filename) - strlen($mask), strlen($mask))) == strtolower($mask)) {
				if((filemtime($filename) + $maxlifetime) < time())
					@unlink($filename);
			}

			if(is_dir($filename))
				if (!count(glob($filename.'/*'))) @rmdir($filename);
				self::_clear($filename, $mask, $maxlifetime);
		}
	}

	function _folder()
	{
		global $sess_session_id, $sess_save_path;

		return $sess_save_path . '/' . mb_substr($sess_session_id, 0, 3);
	}

	function __destruct ()
	{
		register_shutdown_function('session_write_close');
	}

}
?>