<?php

// Проверка
if (! defined('BASE_DIR'))
	exit('Access denied');

/**
 * This source file is part of the AVE.cms. More information,
 * documentation and tutorials can be found at http://www.ave-cms.ru
 *
 * @package      AVE.cms
 * @file         system/helpers/hooks.php
 * @author       @
 * @copyright    2007-2016 (c) AVE.cms
 * @link         http://www.ave-cms.ru
 * @version      4.0
 * @since        $date$
 * @license      license GPL v.2 http://www.ave-cms.ru/license.txt
*/

class Hooks
{
	public static $instance;

	public static $hooks;

	public static $current_hook;

	public static $run_hooks;


	public static function init()
	{
		if (!self::$instance) {
			self::$instance = new Hooks();
		}
		return self::$instance;
	}

	/**
	 * Add Hook
	 */
	public static function register($name, $function, $priority = 10)
	{
		// If we have already registered this action return true
		if (isset(self::$hooks[$name][$priority][$function]))
		{
			return true;
		}
		/**
		 * Allows us to iterate through multiple action hooks.
		 */
		if (is_array($name))
		{
			foreach ($name AS $item)
			{
				// Store the action hook in the $hooks array
				self::$hooks[$item][$priority][$function] = array(
					"function" => $function
				);
			}
		}
		else
		{
			// Store the action hook in the $hooks array
			self::$hooks[$name][$priority][$function] = array(
				"function" => $function
			);
		}

		return true;
	}

	/**
	 * Do Hook
	 */
	public static function trigger($name, $arguments = "")
	{
		// Oh, no you didn't. Are you trying to run an action hook that doesn't exist?
		if (! isset(self::$hooks[$name]))
		{
			return $arguments;
		}

		// Set the current running hook to this
		self::$current_hook = $name;

		// Key sort our action hooks
		ksort(self::$hooks[$name]);
		foreach (self::$hooks[$name] AS $priority => $names)
		{
			if (is_array($names))
			{
				foreach ($names AS $name)
				{
					$return = call_user_func_array($name['function'], array(
						&$arguments
					));

					if ($return)
					{
						$arguments = $return;
					}

					self::$run_hooks[$name][$priority];
				}
			}
		}

		self::$current_hook = '';

		return $arguments;
	}

	/**
	 * Remove Hook
	 */
	public static function unregister($name, $function, $priority = 10)
	{
		// If the action hook doesn't, just return true
		if (!isset(self::$hooks[$name][$priority][$function]))
		{
			return true;
		}
		// Remove the action hook from our hooks array
		unset(self::$hooks[$name][$priority][$function]);

		return '';
	}


	/**
	 * Current Hook
	 *
	 * Get the currently running action hook
	 *
	 */
	public static function current()
	{
		return self::$current_hook;
	}


	/**
	 * Has Run
	 */
	public static function has($hook, $priority = 10)
	{
		if (isset(self::$hooks[$hook][$priority]))
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	/**
	 * Hook Exists
	 */
	public static function exists($name)
	{
		if (isset(self::$hooks[$name]))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
