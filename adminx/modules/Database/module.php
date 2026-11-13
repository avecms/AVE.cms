<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Database/module.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	/**
	 * Раздел «База данных»: таблицы текущего префикса, обслуживание (OPTIMIZE/REPAIR)
	 * и резервные копии (gzip-дамп в tmp/backup).
	 */
	return [
		'code'    => 'database',
		'name'    => 'База данных',
		'version' => '0.2.3',

		'permissions' => [
			'key'      => 'database',
			'items'    => array(
				array(
					'code' => 'view_database',
					'group_code' => 'navigation',
					'name' => 'База данных: просмотр',
					'description' => 'Просмотр таблиц и резервных копий.',
					'sort_order' => 10,
				),
				array(
					'code' => 'manage_database',
					'group_code' => 'admin',
					'name' => 'База данных: обслуживание',
					'description' => 'Обслуживание таблиц, миграции ядра, создание, восстановление и удаление бэкапов.',
					'sort_order' => 20,
				),
			),
			'icon'     => 'ti ti-database',
			'priority' => 40,
		],

		'navigation' => array(
			array(
				'code' => 'database',
				'label' => 'База данных',
				'url' => '/database',
				'icon' => 'ti ti-database',
				'permission' => 'view_database',
				'group' => 'Система',
				'sort_order' => 34,
				'match' => array(
					'/database',
				),
			),
		),

		'migrations' => array(
			array(
				'id' => '001_mysql_legacy_index_compatibility',
				'file' => 'migrations/001_mysql_legacy_index_compatibility.php',
			),
			array(
				'id' => '002_module_lifecycle_recovery',
				'file' => 'migrations/002_module_lifecycle_recovery.php',
			),
			array(
				'id' => '003_document_fields_text_composite_index',
				'file' => 'migrations/003_document_fields_text_composite_index.php',
			),
		),

		'routes' => array(
			array('GET', '/database', array(\App\Adminx\Database\Controller::class, 'index')),
			array('POST', '/database/maintenance', array(\App\Adminx\Database\Controller::class, 'maintenance')),
			array('POST', '/database/backup', array(\App\Adminx\Database\Controller::class, 'backupCreate')),
			array('POST', '/database/backup/keep', array(\App\Adminx\Database\Controller::class, 'backupKeep'), array('permission' => 'manage_database')),
			array('POST', '/database/backup/upload', array(\App\Adminx\Database\Controller::class, 'backupUpload'), array('permission' => 'manage_database')),
			array('GET', '/database/backup/download', array(\App\Adminx\Database\Controller::class, 'backupDownload')),
			array('POST', '/database/backup/delete', array(\App\Adminx\Database\Controller::class, 'backupDelete')),
			array('POST', '/database/backup/inspect', array(\App\Adminx\Database\Controller::class, 'backupInspect'), array('permission' => 'manage_database')),
			array('POST', '/database/backup/restore/start', array(\App\Adminx\Database\Controller::class, 'backupRestoreStart'), array(
				'permission' => 'manage_database',
				'sensitive' => 'database.restore',
				'reauth' => array('reason' => 'Восстановление заменит все таблицы текущей установки данными из резервной копии.'),
			)),
			array('POST', '/database/backup/restore/run', array(\App\Adminx\Database\Controller::class, 'backupRestoreRun'), array('permission' => 'manage_database')),
			array('POST', '/database/backup/restore/status', array(\App\Adminx\Database\Controller::class, 'backupRestoreStatus'), array('permission' => 'manage_database')),
			array('POST', '/database/engines/innodb', array(\App\Adminx\Database\Controller::class, 'convertToInnoDb')),
			array('POST', '/database/schema/update', array(\App\Adminx\Database\Controller::class, 'updateSchema')),
		),

		'view_globals' => [
			'module_code' => 'database',
		],
	];
