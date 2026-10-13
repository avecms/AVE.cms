<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Updates/module.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	$updateRoute = array('permission' => 'install_core_updates', 'sensitive' => 'core.update', 'reauth' => array('reason' => 'Обновление изменит исполняемые файлы ядра и может выполнить миграции БД.'));

	return array(
		'code' => 'updates',
		'name' => 'Обновления',
		'version' => '0.2.2',
		'notifications' => array('provider' => array(\App\Adminx\Updates\NotificationProvider::class, 'items'), 'permission' => 'view_core_updates', 'sort_order' => 45),
		'permissions' => array(
			'key' => 'updates',
			'items' => array(
				array('code' => 'view_core_updates', 'group_code' => 'navigation', 'name' => 'Обновления: просмотр', 'description' => 'Просмотр версии, подписанного каталога и истории обновлений.', 'sort_order' => 10),
				array('code' => 'manage_core_updates', 'group_code' => 'admin', 'name' => 'Обновления: настройки', 'description' => 'Настройка официального источника обновлений ядра.', 'sort_order' => 20),
				array('code' => 'install_core_updates', 'group_code' => 'admin', 'name' => 'Обновления: установка', 'description' => 'Загрузка и применение подписанного исполняемого кода ядра.', 'sort_order' => 30),
			),
			'icon' => 'ti ti-refresh',
			'priority' => 42,
		),
		'navigation' => array(
			array('code' => 'core_updates', 'label' => 'Обновления', 'url' => '/system/updates', 'icon' => 'ti ti-refresh', 'permission' => 'view_core_updates', 'group' => 'Система', 'parent' => 'settings', 'sort_order' => 90, 'match' => array('/system/updates')),
		),
		'routes' => array(
			array('GET', '/system/updates', array(\App\Adminx\Updates\Controller::class, 'index')),
			array('POST', '/system/updates/settings', array(\App\Adminx\Updates\Controller::class, 'settings'), $updateRoute),
			array('POST', '/system/updates/settings/official', array(\App\Adminx\Updates\Controller::class, 'officialSettings'), $updateRoute),
			array('POST', '/system/updates/refresh', array(\App\Adminx\Updates\Controller::class, 'refresh')),
			array('POST', '/system/updates/upload', array(\App\Adminx\Updates\Controller::class, 'upload'), $updateRoute),
			array('POST', '/system/updates/download/{id}', array(\App\Adminx\Updates\Controller::class, 'download'), $updateRoute),
			array('POST', '/system/updates/jobs/{id}/step', array(\App\Adminx\Updates\Controller::class, 'step'), $updateRoute),
			array('POST', '/system/updates/jobs/{id}/rollback', array(\App\Adminx\Updates\Controller::class, 'rollback'), $updateRoute),
			array('POST', '/system/updates/jobs/{id}/discard', array(\App\Adminx\Updates\Controller::class, 'discard'), array('permission' => 'install_core_updates')),
		),
		'view_globals' => array('module_code' => 'updates'),
	);
