<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Console/module.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	use App\Adminx\Support\SystemFeatures;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	if (!SystemFeatures::enabled('console')) {
		return array(
			'code' => 'console',
			'name' => 'PHP-консоль',
			'version' => '0.1.3',
			'registry' => array('dynamic' => true),
		);
	}

	return array(
		'code' => 'console',
		'name' => 'PHP-консоль',
		'version' => '0.1.3',
		'registry' => array('dynamic' => true),
		'permissions' => array(
			'key' => 'console',
			'items' => array(
				array(
					'code' => 'view_console',
					'group_code' => 'navigation',
					'name' => 'PHP-консоль: просмотр',
					'description' => 'Просмотр системной консоли и сохранённых сниппетов.',
					'sort_order' => 10,
				),
				array(
					'code' => 'manage_console',
					'group_code' => 'admin',
					'name' => 'PHP-консоль: сниппеты',
					'description' => 'Создание, изменение и удаление сниппетов.',
					'sort_order' => 20,
				),
				array(
					'code' => 'execute_console',
					'group_code' => 'admin',
					'name' => 'PHP-консоль: выполнение',
					'description' => 'Выполнение PHP-кода в изолированном процессе.',
					'sort_order' => 30,
				),
			),
			'icon' => 'ti ti-terminal-2',
			'priority' => 47,
		),
		'navigation' => array(
			array(
				'code' => 'console',
				'label' => 'PHP-консоль',
				'url' => '/system/console',
				'icon' => 'ti ti-terminal-2',
				'permission' => 'view_console',
				'group' => 'Система',
				'sort_order' => 38,
				'parent' => 'settings',
				'match' => array(
					'/system/console',
				),
			),
		),
		'migrations' => array(
			array('id' => '001_create_console_snippets', 'file' => 'migrations/001_create_console_snippets.sql'),
		),
		'routes' => array(
			array('GET', '/system/console', array(\App\Adminx\Console\Controller::class, 'index')),
			array('GET', '/system/console/snippets/{id}', array(\App\Adminx\Console\Controller::class, 'show')),
			array('POST', '/system/console/snippets', array(\App\Adminx\Console\Controller::class, 'save')),
			array('POST', '/system/console/snippets/{id}/delete', array(\App\Adminx\Console\Controller::class, 'delete')),
			array('POST', '/system/console/lint', array(\App\Adminx\Console\Controller::class, 'lint')),
			array('POST', '/system/console/execute', array(\App\Adminx\Console\Controller::class, 'execute'), array(
				'permission' => 'execute_console',
				'sensitive' => 'stored_php.execute',
				'reauth' => array('reason' => 'PHP-консоль выполнит введённый код на сервере.'),
			)),
		),
		'view_globals' => array('module_code' => 'console'),
	);
