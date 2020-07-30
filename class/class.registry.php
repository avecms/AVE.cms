<?php

	class Registry
	{
		private static $_storage = [];

		private static $_instance;


		protected function __construct ()
		{
			//
		}


		public static function init ()
		{
			if (self::$_instance == null)
				self::$_instance = new self;

			return self::$_instance;
		}


		/*
		|--------------------------------------------------------------------------------------
		| Установка значения
		|--------------------------------------------------------------------------------------
		|
		*/
		public static function set ($key, $value)
		{
			//if ($key == 'documents')
			//	Debug::_($value);

			return self::$_storage[$key] = $value;
		}


		/*
		|--------------------------------------------------------------------------------------
		| Получение значения
		|--------------------------------------------------------------------------------------
		|
		*/
		public static function get ($key, $arrkey = null, $default = null)
		{
			if (empty($arrkey))
				return (isset(self::$_storage[$key])) ? self::$_storage[$key] : $default;
			else
				return (isset(self::$_storage[$key][$arrkey])) ? self::$_storage[$key][$arrkey] : $default;
		}


		/*
		|--------------------------------------------------------------------------------------
		| Удаление
		|--------------------------------------------------------------------------------------
		|
		*/
		public static function remove ($key, $arrkey = null)
		{
			if (empty($arrkey))
				unset(self::$_storage[$key]);
			else
				unset(self::$_storage[$key][$arrkey]);

			return true;
		}


		/*
		|--------------------------------------------------------------------------------------
		| Проверка
		|--------------------------------------------------------------------------------------
		|
		*/
		public static function stored ($key, $arrkey = null)
		{
			if (empty($arrkey))
				return isset (self::$_storage[$key]);
			else
				return isset(self::$_storage[$key][$arrkey]);
		}


		/*
		|--------------------------------------------------------------------------------------
		| Очистка
		|--------------------------------------------------------------------------------------
		|
		*/
		public static function clean ()
		{
			self::$_storage = [];
			return true;
		}


		/*
		|--------------------------------------------------------------------------------------
		| Добавить значение в конец
		|--------------------------------------------------------------------------------------
		|
		*/
		public static function addAfter ($key, $value)
		{
			return self::$_storage[$key] .= $value;
		}


		/*
		|--------------------------------------------------------------------------------------
		| Добавить значение в начало
		|--------------------------------------------------------------------------------------
		|
		*/
		public static function addBefore ($key, $value)
		{
			return self::$_storage[$key] = self::$_storage[$key] . $value;
		}


		/*
		|--------------------------------------------------------------------------------------
		| Вывод
		|--------------------------------------------------------------------------------------
		|
		*/
		public static function output ()
		{
			return self::$_storage;
		}


		private function __sleep ()
		{
			self::$_storage = serialize (self::$_storage);
		}


		private function __wakeup ()
		{
			self::$_storage = unserialize (self::$_storage);
		}
	}
?>