<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Settings/module.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	return array(
		'code' => 'settings',
		'name' => 'Настройки',
		'version' => '0.1.5',

		'permissions' => array(
			'key' => 'settings',
			'items' => array(
				array(
					'code' => 'view_settings',
					'group_code' => 'navigation',
					'name' => 'Настройки: просмотр',
					'description' => 'Доступ к разделу настроек сайта.',
					'sort_order' => 10,
				),
				array(
					'code' => 'manage_settings',
					'group_code' => 'admin',
					'name' => 'Настройки: управление',
					'description' => 'Изменение настроек, констант, пагинаций; обслуживание кэша.',
					'sort_order' => 20,
				),
			),
			'icon' => 'ti ti-settings',
			'priority' => 30,
		),

		'navigation' => array(
			array(
				'code' => 'settings',
				'label' => 'Настройки',
				'url' => '#',
				'icon' => 'ti ti-settings',
				'permission' => '',
				'group' => 'Система',
				'sort_order' => 33,
				'match' => array(
					'/settings',
					'/security/ip-blocks',
					'/system/console',
					'/system/updates',
				),
			),
			array(
				'code' => 'settings_main',
				'label' => 'Основные',
				'url' => '/settings/main',
				'permission' => 'view_settings',
				'group' => 'Система',
				'parent' => 'settings',
				'sort_order' => 31,
				'match' => array(
					'/settings/main',
				),
				'exact' => true,
			),
			array(
				'code' => 'settings_interface',
				'label' => 'Интерфейс',
				'url' => '/settings/interface',
				'permission' => 'view_settings',
				'group' => 'Система',
				'parent' => 'settings',
				'sort_order' => 32,
				'match' => array(
					'/settings/interface',
				),
				'exact' => true,
			),
			array(
				'code' => 'settings_constants',
				'label' => 'Константы',
				'url' => '/settings/constants',
				'permission' => 'view_settings',
				'group' => 'Система',
				'parent' => 'settings',
				'sort_order' => 34,
				'match' => array(
					'/settings/constants',
				),
				'exact' => true,
			),
			array(
				'code' => 'settings_security',
				'label' => 'Безопасность',
				'url' => '/settings/security',
				'permission' => 'view_settings',
				'group' => 'Система',
				'parent' => 'settings',
				'sort_order' => 33,
				'match' => array(
					'/settings/security',
				),
				'exact' => true,
			),
			array(
				'code' => 'settings_paginations',
				'label' => 'Пагинация',
				'url' => '/settings/paginations',
				'permission' => 'view_settings',
				'group' => 'Система',
				'parent' => 'settings',
				'sort_order' => 35,
				'match' => array(
					'/settings/paginations',
				),
				'exact' => true,
			),
			array(
				'code' => 'settings_maintenance',
				'label' => 'Обслуживание',
				'url' => '/settings/maintenance',
				'permission' => 'view_settings',
				'group' => 'Система',
				'parent' => 'settings',
				'sort_order' => 36,
				'match' => array(
					'/settings/maintenance',
				),
				'exact' => true,
			),
			array(
				'code' => 'settings_diagnostics',
				'label' => 'Диагностика',
				'url' => '/settings/diagnostics',
				'permission' => 'view_settings',
				'group' => 'Система',
				'parent' => 'settings',
				'sort_order' => 37,
				'match' => array(
					'/settings/diagnostics',
				),
				'exact' => true,
			),
			array(
				'code' => 'settings_files',
				'label' => 'Системные файлы',
				'url' => '/settings/files',
				'permission' => 'view_settings',
				'group' => 'Система',
				'parent' => 'settings',
				'sort_order' => 38,
				'match' => array(
					'/settings/files',
				),
				'exact' => true,
			),
		),

		'migrations' => array(
			array('id' => '001_create_settings_tables', 'file' => 'migrations/001_create_settings_tables.sql', 'legacy_checksums' => array('6346c2e9ed66ed6d253c51aa1addadb50b5feb3d')),
			array('id' => '002_normalize_breadcrumb_separator', 'file' => 'migrations/002_normalize_breadcrumb_separator.sql'),
			array('id' => '003_remove_unused_constants', 'file' => 'migrations/003_remove_unused_constants.sql'),
			array('id' => '004_seed_core_display_defaults', 'file' => 'migrations/004_seed_core_display_defaults.sql'),
			array('id' => '005_order_system_navigation', 'file' => 'migrations/005_order_system_navigation.php'),
			array('id' => '006_create_admin_saved_views', 'file' => 'migrations/006_create_admin_saved_views.sql'),
			array('id' => '007_public_site_url', 'file' => 'migrations/007_public_site_url.sql'),
		),

		'routes' => array(
			array('GET', '/settings', array(\App\Adminx\Settings\Controller::class, 'index')),
			array('GET', '/settings/main', array(\App\Adminx\Settings\Controller::class, 'page')),
			array('GET', '/settings/interface', array(\App\Adminx\Settings\Controller::class, 'page')),
			array('GET', '/settings/security', array(\App\Adminx\Settings\Controller::class, 'page')),
			array('GET', '/settings/constants', array(\App\Adminx\Settings\Controller::class, 'page')),
			array('GET', '/settings/paginations', array(\App\Adminx\Settings\Controller::class, 'page')),
			array('GET', '/settings/maintenance', array(\App\Adminx\Settings\Controller::class, 'page')),
			array('GET', '/settings/diagnostics', array(\App\Adminx\Settings\Controller::class, 'page')),
			array('GET', '/settings/files', array(\App\Adminx\Settings\Controller::class, 'page')),
			array('POST', '/settings', array(\App\Adminx\Settings\Controller::class, 'save')),
			array('POST', '/settings/interface', array(\App\Adminx\Settings\Controller::class, 'saveInterface')),
			array('POST', '/settings/security', array(\App\Adminx\Settings\Controller::class, 'saveSecurity')),
			array('POST', '/settings/notifications', array(\App\Adminx\Settings\Controller::class, 'saveNotificationPreference')),
			array('GET', '/settings/paginations/{id}', array(\App\Adminx\Settings\Controller::class, 'pagination')),
			array('POST', '/settings/paginations', array(\App\Adminx\Settings\Controller::class, 'savePagination')),
			array('POST', '/settings/paginations/{id}', array(\App\Adminx\Settings\Controller::class, 'savePagination')),
			array('POST', '/settings/paginations/{id}/delete', array(\App\Adminx\Settings\Controller::class, 'deletePagination')),
			array('POST', '/settings/cache/{source}/clear', array(\App\Adminx\Settings\Controller::class, 'clearCache')),
			array('POST', '/settings/maintenance/{target}/clear', array(\App\Adminx\Settings\Controller::class, 'clearMaintenance')),
			array('GET', '/settings/files/{code}', array(\App\Adminx\Settings\Controller::class, 'systemFile')),
			array('POST', '/settings/files/{code}', array(\App\Adminx\Settings\Controller::class, 'saveSystemFile')),
			array('GET', '/settings/constants/{name}', array(\App\Adminx\Settings\Controller::class, 'constant')),
			array('POST', '/settings/constants', array(\App\Adminx\Settings\Controller::class, 'saveConstant')),
			array('POST', '/settings/constants/thumbnail-sizes/scan', array(\App\Adminx\Settings\Controller::class, 'scanThumbnailSizes')),
			array('POST', '/settings/constants/{name}/delete', array(\App\Adminx\Settings\Controller::class, 'deleteConstant')),
		),

		'view_globals' => array(
			'module_code' => 'settings',
		),
	);
