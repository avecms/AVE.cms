<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Catalog/module.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	defined('BASEPATH') || die('Direct access to this location is not allowed.');
	return array(
		'code' => 'catalog', 'name' => 'Каталог', 'version' => '0.3.10',
		'permissions' => array('key' => 'catalog', 'items' => array(
			array(
				'code' => 'view_catalog',
				'group_code' => 'navigation',
				'name' => 'Каталог: просмотр',
				'description' => 'Просмотр структуры системного каталога.',
				'sort_order' => 10,
			),
			array(
				'code' => 'manage_catalog',
				'group_code' => 'content',
				'name' => 'Каталог: управление',
				'description' => 'Изменение разделов, полей, фильтров и настроек каталога.',
				'sort_order' => 20,
			),
		), 'icon' => 'ti ti-category-2', 'priority' => 22),
		'navigation' => array(
			array(
				'code' => 'catalog',
				'label' => 'Каталог',
				'url' => '/catalog',
				'icon' => 'ti ti-category-2',
				'permission' => 'view_catalog',
				'group' => 'Контент',
				'sort_order' => 22,
				'match_regex' => array(
					'#^/catalog/\d+/\d+(?:/.*)?$#',
				),
			),
		),
		'routes' => array(
			array('GET', '/catalog', array(\App\Adminx\Catalog\Controller::class, 'index')),
			array('POST', '/catalog', array(\App\Adminx\Catalog\Controller::class, 'createCatalog')),
			array('GET', '/catalog/documents', array(\App\Adminx\Catalog\Controller::class, 'documents')),
			array('GET', '/catalog/items/{id}', array(\App\Adminx\Catalog\Controller::class, 'item')),
			array('GET', '/catalog/{rubric}/{field}', array(\App\Adminx\Catalog\Controller::class, 'edit')),
			array('GET', '/catalog/{rubric}/{field}/product-preview', array(\App\Adminx\Catalog\Controller::class, 'productPreview'), array('permission' => 'view_products')),
			array('POST', '/catalog/{rubric}/{field}/items', array(\App\Adminx\Catalog\Controller::class, 'storeItem')),
			array('POST', '/catalog/{rubric}/{field}/items/{id}', array(\App\Adminx\Catalog\Controller::class, 'updateItem')),
			array('POST', '/catalog/{rubric}/{field}/items/{id}/conditions/sync', array(\App\Adminx\Catalog\Controller::class, 'syncMissingConditions')),
			array('POST', '/catalog/{rubric}/{field}/items/{id}/conditions/{filter}', array(\App\Adminx\Catalog\Controller::class, 'syncCondition')),
			array('POST', '/catalog/{rubric}/{field}/items/{id}/status', array(\App\Adminx\Catalog\Controller::class, 'setItemStatus')),
			array('POST', '/catalog/{rubric}/{field}/items/{id}/filters/recompile', array(\App\Adminx\Catalog\Controller::class, 'recompileItem')),
			array('POST', '/catalog/{rubric}/{field}/items/{id}/delete', array(\App\Adminx\Catalog\Controller::class, 'deleteItem')),
			array('POST', '/catalog/{rubric}/{field}/reorder', array(\App\Adminx\Catalog\Controller::class, 'reorder')),
			array('POST', '/catalog/{rubric}/{field}/settings', array(\App\Adminx\Catalog\Controller::class, 'saveSettings')),
		),
		'migrations' => array(
			array('id' => '005_add_catalog_purpose', 'file' => 'migrations/005_add_catalog_purpose.sql'),
			array('id' => '006_add_item_sources', 'file' => 'migrations/006_add_item_sources.php'),
		),
		'view_globals' => array('module_code' => 'catalog'),
	);
