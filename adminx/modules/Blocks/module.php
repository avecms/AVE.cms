<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Blocks/module.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	return array(
		'code' => 'blocks',
		'name' => 'Блоки',
		'version' => '0.1.6',

		'permissions' => array(
			'key' => 'blocks',
			'items' => array(
				array(
					'code' => 'view_blocks',
					'group_code' => 'navigation',
					'name' => 'Блоки: просмотр',
					'description' => 'Просмотр HTML- и системных блоков, а также их групп.',
					'sort_order' => 10,
				),
				array(
					'code' => 'manage_blocks',
					'group_code' => 'content',
					'name' => 'Блоки: управление',
					'description' => 'Создание, изменение, копирование и удаление блоков.',
					'sort_order' => 20,
				),
				array(
					'code' => 'manage_php_blocks',
					'group_code' => 'admin',
					'name' => 'Блоки: исполняемый PHP',
					'description' => 'Создание, изменение, копирование и восстановление блоков с PHP-кодом.',
					'sort_order' => 30,
				),
			),
			'icon' => 'ti ti-blockquote',
			'priority' => 32,
		),

		'navigation' => array(
			array(
				'code' => 'blocks',
				'label' => 'Блоки',
				'url' => '/blocks',
				'icon' => 'ti ti-blockquote',
				'permission' => 'view_blocks',
				'group' => 'Контент',
				'sort_order' => 22,
				'match' => array(
					'/blocks',
				),
			),
		),

		'migrations' => array(
			array('id' => '001_create_sysblocks_tables', 'file' => 'migrations/001_create_sysblocks_tables.sql'),
			array('id' => '002_create_sysblock_revisions', 'file' => 'migrations/002_create_sysblock_revisions.sql'),
			array('id' => '003_native_public_document_context', 'file' => 'migrations/003_native_public_document_context.sql'),
			array('id' => '004_native_database_calls', 'file' => 'migrations/004_native_database_calls.sql'),
			array('id' => '005_correct_native_database_escape', 'file' => 'migrations/005_correct_native_database_escape.sql'),
			array('id' => '006_native_public_quick_edit', 'file' => 'migrations/006_native_public_quick_edit.sql'),
			array('id' => '007_cache_safe_public_quick_edit', 'file' => 'migrations/007_cache_safe_public_quick_edit.sql'),
			array('id' => '008_deferred_public_quick_edit_id', 'file' => 'migrations/008_deferred_public_quick_edit_id.sql'),
			array('id' => '009_disable_public_php_endpoint', 'file' => 'migrations/009_disable_public_php_endpoint.sql'),
			array('id' => '010_disable_retired_catalog_endpoints', 'file' => 'migrations/010_disable_retired_catalog_endpoints.sql'),
			array('id' => '011_sysblock_editor_mode', 'file' => 'migrations/011_sysblock_editor_mode.php'),
			array(
				'id' => '012_merge_visual_blocks',
				'file' => 'migrations/012_merge_visual_blocks.php',
				'legacy_checksums' => array('5bf93d4a309e679ed94b2dec4e3a39f9b7d71a8c'),
			),
		),

		'routes' => array(
			array('GET', '/blocks', array(\App\Adminx\Blocks\Controller::class, 'index')),
			array('GET', '/blocks/alias-check', array(\App\Adminx\Blocks\Controller::class, 'aliasCheck')),
			array('POST', '/blocks/lint', array(\App\Adminx\Blocks\Controller::class, 'lint')),
			array('POST', '/blocks/groups', array(\App\Adminx\Blocks\Controller::class, 'storeGroup')),
			array('GET', '/blocks/{id}/revisions', array(\App\Adminx\Blocks\Controller::class, 'revisions')),
			array('POST', '/blocks/{id}/revisions/delete', array(\App\Adminx\Blocks\Controller::class, 'deleteRevisions')),
			array('GET', '/blocks/revisions/{revision}', array(\App\Adminx\Blocks\Controller::class, 'revision')),
			array('POST', '/blocks/revisions/{revision}/restore', array(\App\Adminx\Blocks\Controller::class, 'restoreRevision'), array('permission' => 'manage_blocks', 'sensitive' => 'stored_php.write', 'reauth' => true)),
			array('POST', '/blocks/revisions/{revision}/delete', array(\App\Adminx\Blocks\Controller::class, 'deleteRevision')),
			array('GET', '/blocks/{id}', array(\App\Adminx\Blocks\Controller::class, 'show')),
			array('POST', '/blocks', array(\App\Adminx\Blocks\Controller::class, 'store'), array('permission' => 'manage_blocks', 'sensitive' => 'stored_php.write', 'reauth' => true)),
			array('POST', '/blocks/{id}', array(\App\Adminx\Blocks\Controller::class, 'update'), array('permission' => 'manage_blocks', 'sensitive' => 'stored_php.write', 'reauth' => true)),
			array('POST', '/blocks/{id}/copy', array(\App\Adminx\Blocks\Controller::class, 'copy'), array('permission' => 'manage_blocks', 'sensitive' => 'stored_php.write', 'reauth' => true)),
			array('POST', '/blocks/{id}/clear-cache', array(\App\Adminx\Blocks\Controller::class, 'clearCache')),
			array('POST', '/blocks/{id}/delete', array(\App\Adminx\Blocks\Controller::class, 'destroy')),
			array('GET', '/blocks/groups/{id}', array(\App\Adminx\Blocks\Controller::class, 'group')),
			array('POST', '/blocks/groups/reorder', array(\App\Adminx\Blocks\Controller::class, 'reorderGroups')),
			array('POST', '/blocks/groups/{id}', array(\App\Adminx\Blocks\Controller::class, 'updateGroup')),
			array('POST', '/blocks/groups/{id}/delete', array(\App\Adminx\Blocks\Controller::class, 'destroyGroup')),
		),

		'view_globals' => array(
			'module_code' => 'blocks',
		),
	);
