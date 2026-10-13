<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Content/Fields/RichEditorSettings.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Content\Fields;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	class RichEditorSettings
	{
		public static function schema()
		{
			$schema = array(
				array('key' => 'rich_profile', 'type' => 'select', 'label' => 'Панель визуального редактора',
					'options' => array('full' => 'Полная', 'compact' => 'Компактная', 'product' => 'Описание товара', 'news' => 'Новость / статья'), 'default' => 'full'),
				array('key' => 'rich_headings', 'type' => 'select', 'label' => 'Заголовки в панели',
					'options' => array('profile' => 'По профилю', 'all' => 'H1–H6', 'no_h1' => 'H2–H6', 'none' => 'Без заголовков'), 'default' => 'profile'),
				array('key' => 'rich_snippets', 'type' => 'select', 'label' => 'Готовые фрагменты',
					'options' => array('all' => 'Все', 'info' => 'Информационные', 'product' => 'Товарные', 'none' => 'Не показывать'), 'default' => 'all'),
			);
			foreach (array('image' => 'изображения', 'table' => 'таблицы', 'source' => 'исходный код', 'align' => 'выравнивание', 'blocks' => 'перемещение блоков') as $key => $label) {
				$schema[] = array('key' => 'rich_hide_' . $key, 'type' => 'bool', 'label' => 'Скрыть в панели: ' . $label, 'default' => false);
			}

			return $schema;
		}

		public static function options(array $settings)
		{
			$result = array();
			foreach (self::schema() as $desc) {
				$key = $desc['key'];
				$value = isset($settings[$key]) ? $settings[$key] : $desc['default'];
				if ($desc['type'] === 'select' && (!is_scalar($value) || !isset($desc['options'][$value]))) { $value = $desc['default']; }
				$result[substr($key, 5)] = $desc['type'] === 'bool' ? (bool) $value : (string) $value;
			}

			return $result;
		}
	}
