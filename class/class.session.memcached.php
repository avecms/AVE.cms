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

	class AVE_Session_Memcached
	{
		private $memcached;
		private $ttl;
		private $prefix;

		function __construct()
		{
			$this->memcached = new Memcached;
			$this->memcached->addServer(MEMCACHED_SERVER, MEMCACHED_PORT);

			$this->ttl = (defined('SESSION_LIFETIME') && is_numeric(SESSION_LIFETIME))
				? SESSION_LIFETIME
				: (get_cfg_var("session.gc_maxlifetime") < 1440 ? 1440 : get_cfg_var("session.gc_maxlifetime"));

			$this->prefix = 'sess_';
		}

		/* Open session */
		function _open($sess_save_path, $session_name)
		{
			return true;
		}

		/* Close session */
		function _close()
		{
			return true;
		}

		/* Read session */
		function _read($id)
		{
			return $this->memcached->get($this->prefix . $id) ? : '';
		}

		/* Write new data */
		function _write ($id, $sess_data)
		{
			$this->memcached->set($this->prefix . $id, $sess_data, time() + $this->ttl);

			return true;
		}

		/* Destroy session */
		function _destroy ($id)
		{
			$this->memcached->delete($this->prefix . $id);

			return true;
		}

		/* Garbage collection, deletes old sessions */
		function _gc ($maxlifetime)
		{
			return true;
		}
	}
?>