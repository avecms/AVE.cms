<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Dashboard/module.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	/**
	 * Модуль Dashboard — пилотный CRUD-каркас: главная страница + пункт меню.
	 * Декларативный дескриптор (modules.md §1); регистрацию делает ModuleManager.
	 */
	return [
		'code'    => 'dashboard',
		'name'    => 'Дашборд',
		'version' => '0.2.3',

		'navigation' => array(
			array(
				'code' => 'dashboard',
				'label' => 'Дашборд',
				'url' => '/dashboard',
				'icon' => 'ti ti-layout-dashboard',
				'permission' => '',
				'group' => 'Обзор',
				'sort_order' => 10,
				'match' => array(
					'/dashboard',
				),
			),
		),

		'routes' => array(
			array('GET', '/dashboard/attention', array(\App\Adminx\Dashboard\Controller::class, 'attention')),
			array('GET', '/', array(\App\Adminx\Dashboard\Controller::class, 'index')),
			array('GET', '/dashboard', array(\App\Adminx\Dashboard\Controller::class, 'index')),
		),

		'view_globals' => [
			'module_code' => 'dashboard',
		],
	];
