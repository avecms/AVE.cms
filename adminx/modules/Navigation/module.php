<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Navigation/module.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	return array(
		'code' => 'navigation',
		'name' => 'Навигация',
		'version' => '0.2.3',

		'hook_definitions' => array(
			array(
				'name' => 'navigation.item.panel_sources',
				'description' => 'Дополнительные источники правой панели для пункта навигации товарного каталога.',
			),
		),

		'permissions' => array(
			'key' => 'navigation',
			'items' => array(
				array(
					'code' => 'view_navigation',
					'group_code' => 'navigation',
					'name' => 'Навигация: просмотр',
					'description' => 'Просмотр шаблонов и пунктов навигации.',
					'sort_order' => 10,
				),
				array(
					'code' => 'manage_navigation',
					'group_code' => 'content',
					'name' => 'Навигация: управление',
					'description' => 'Создание, изменение, копирование, удаление и импорт навигаций.',
					'sort_order' => 20,
				),
			),
			'icon' => 'ti ti-sitemap',
			'priority' => 35,
		),

		'navigation' => array(
			array(
				'code' => 'navigation',
				'label' => 'Навигация',
				'url' => '/navigation',
				'icon' => 'ti ti-sitemap',
				'permission' => 'view_navigation',
				'group' => 'Контент',
				'sort_order' => 26,
				'match' => array(
					'/navigation',
				),
			),
		),

		'migrations' => array(
			array('id' => '001_create_navigation_tables', 'file' => 'migrations/001_create_navigation_tables.sql'),
			array('id' => '002_navigation_revisions', 'file' => 'migrations/002_navigation_revisions.sql'),
		),

		'routes' => array(
			array('GET', '/navigation', array(\App\Adminx\Navigation\Controller::class, 'index')),
			array('GET', '/navigation/alias-check', array(\App\Adminx\Navigation\Controller::class, 'aliasCheck')),
			array('GET', '/navigation/documents/picker', array(\App\Adminx\Navigation\Controller::class, 'documentPicker')),
			array('POST', '/navigation/lint', array(\App\Adminx\Navigation\Controller::class, 'lint'), array('permission' => 'manage_navigation')),
			array('GET', '/navigation/{id}/revisions', array(\App\Adminx\Navigation\Controller::class, 'revisions')),
			array('GET', '/navigation/revisions/{revision}', array(\App\Adminx\Navigation\Controller::class, 'revision')),
			array('POST', '/navigation/revisions/{revision}/restore', array(\App\Adminx\Navigation\Controller::class, 'restoreRevision'), array('permission' => 'manage_navigation', 'sensitive' => 'stored_php.write', 'reauth' => true)),
			array('GET', '/navigation/{id}/items', array(\App\Adminx\Navigation\Controller::class, 'items')),
			array('POST', '/navigation/{id}/items', array(\App\Adminx\Navigation\Controller::class, 'storeItem')),
			array('POST', '/navigation/{id}/items/reorder', array(\App\Adminx\Navigation\Controller::class, 'reorderItems')),
			array('GET', '/navigation/items/{item}', array(\App\Adminx\Navigation\Controller::class, 'item')),
			array('POST', '/navigation/items/{item}', array(\App\Adminx\Navigation\Controller::class, 'updateItem')),
			array('POST', '/navigation/items/{item}/toggle', array(\App\Adminx\Navigation\Controller::class, 'toggleItem')),
			array('POST', '/navigation/items/{item}/delete', array(\App\Adminx\Navigation\Controller::class, 'destroyItem')),
			array('GET', '/navigation/{id}', array(\App\Adminx\Navigation\Controller::class, 'show')),
			array('POST', '/navigation', array(\App\Adminx\Navigation\Controller::class, 'store'), array('permission' => 'manage_navigation', 'sensitive' => 'stored_php.write', 'reauth' => true)),
			array('POST', '/navigation/{id}', array(\App\Adminx\Navigation\Controller::class, 'update'), array('permission' => 'manage_navigation', 'sensitive' => 'stored_php.write', 'reauth' => true)),
			array('POST', '/navigation/{id}/copy', array(\App\Adminx\Navigation\Controller::class, 'copy'), array('permission' => 'manage_navigation', 'sensitive' => 'stored_php.write', 'reauth' => true)),
			array('POST', '/navigation/{id}/clear-cache', array(\App\Adminx\Navigation\Controller::class, 'clearCache')),
			array('POST', '/navigation/{id}/delete', array(\App\Adminx\Navigation\Controller::class, 'destroy')),
		),

		'view_globals' => array(
			'module_code' => 'navigation',
		),
	);
