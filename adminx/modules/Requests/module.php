<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Requests/module.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	/**
	 * Раздел «Запросы» (листинги AVE): управление запросами, их шаблонами
	 * (item/main) и условиями. Данные переносятся как есть; теги/PHP в шаблонах и
	 * условиях не исполняются здесь (движок тегов — отдельно).
	 */
	return [
		'code'    => 'requests',
		'name'    => 'Запросы',
		'version' => '0.2.1',

		'permissions' => [
			'key'      => 'requests',
			'items'    => array(
				array(
					'code' => 'view_requests',
					'group_code' => 'navigation',
					'name' => 'Запросы: просмотр',
					'description' => 'Просмотр списка запросов (листингов).',
					'sort_order' => 10,
				),
				array(
					'code' => 'manage_requests',
					'group_code' => 'admin',
					'name' => 'Запросы: управление',
					'description' => 'Создание/изменение запросов, шаблонов и условий.',
					'sort_order' => 20,
				),
			),
			'icon'     => 'ti ti-list-search',
			'priority' => 26,
		],

		'navigation' => array(
			array(
				'code' => 'requests',
				'label' => 'Запросы',
				'url' => '/requests',
				'icon' => 'ti ti-list-search',
				'permission' => 'view_requests',
				'group' => 'Контент',
				'sort_order' => 27,
				'match' => array(
					'/requests',
				),
			),
		),

		'migrations' => [
			['id' => '001_native_database_calls', 'file' => 'migrations/001_native_database_calls.sql'],
			['id' => '002_correct_native_database_escape', 'file' => 'migrations/002_correct_native_database_escape.sql'],
			['id' => '003_condition_groups', 'file' => 'migrations/003_condition_groups.sql'],
			['id' => '004_recompile_condition_groups', 'file' => 'migrations/004_recompile_condition_groups.sql'],
			['id' => '005_rebuild_native_condition_cache', 'file' => 'migrations/005_rebuild_native_condition_cache.sql'],
			['id' => '006_sargable_condition_cache', 'file' => 'migrations/006_sargable_condition_cache.sql'],
			['id' => '007_result_contract', 'file' => 'migrations/007_result_contract.php'],
			['id' => '008_preview_renderer', 'file' => 'migrations/008_preview_renderer.php'],
			['id' => '009_native_executor', 'file' => 'migrations/009_native_executor.php'],
			['id' => '010_native_audit_result', 'file' => 'migrations/010_native_audit_result.php'],
			['id' => '011_order_tiebreaker', 'file' => 'migrations/011_order_tiebreaker.php'],
			['id' => '012_condition_value_sources', 'file' => 'migrations/012_condition_value_sources.php'],
			['id' => '013_sort_rules', 'file' => 'migrations/013_sort_rules.php'],
			['id' => '014_request_revisions', 'file' => 'migrations/014_request_revisions.sql'],
			['id' => '015_request_description_capacity', 'file' => 'migrations/015_request_description_capacity.php'],
		],

		'routes' => array(
			array('GET', '/requests', array(\App\Adminx\Requests\Controller::class, 'index')),
			array('GET', '/requests/native-audit', array(\App\Adminx\Requests\Controller::class, 'nativeAudit'), array('permission' => 'view_requests')),
			array('POST', '/requests/native-audit/prepare', array(\App\Adminx\Requests\Controller::class, 'nativeAuditPrepare'), array('permission' => 'manage_requests')),
			array('POST', '/requests/native-audit/activate', array(\App\Adminx\Requests\Controller::class, 'nativeAuditActivate'), array('permission' => 'manage_requests')),
			array('POST', '/requests/native-audit/rollback', array(\App\Adminx\Requests\Controller::class, 'nativeAuditRollback'), array('permission' => 'manage_requests')),
			array('GET', '/requests/new', array(\App\Adminx\Requests\Controller::class, 'createForm')),
			array('GET', '/requests/alias', array(\App\Adminx\Requests\Controller::class, 'aliasCheck')),
			array('GET', '/requests/documents/picker', array(\App\Adminx\Requests\Controller::class, 'documentPicker'), array('permission' => 'manage_requests')),
			array('POST', '/requests', array(\App\Adminx\Requests\Controller::class, 'store'), array('permission' => 'manage_requests', 'sensitive' => 'stored_php.write', 'reauth' => true)),
			array('POST', '/requests/lint', array(\App\Adminx\Requests\Controller::class, 'lint')),
			array('GET', '/requests/{id}/revisions', array(\App\Adminx\Requests\Controller::class, 'revisions')),
			array('GET', '/requests/revisions/{revision}', array(\App\Adminx\Requests\Controller::class, 'revision')),
			array('POST', '/requests/revisions/{revision}/restore', array(\App\Adminx\Requests\Controller::class, 'restoreRevision'), array('permission' => 'manage_requests', 'sensitive' => 'stored_php.write', 'reauth' => true)),
			array('POST', '/requests/conditions/{id}/delete', array(\App\Adminx\Requests\Controller::class, 'conditionDelete')),
			array('POST', '/requests/{id}/conditions/reorder', array(\App\Adminx\Requests\Controller::class, 'conditionReorder')),
			array('POST', '/requests/{id}/condition-groups', array(\App\Adminx\Requests\Controller::class, 'groupSave')),
			array('POST', '/requests/{id}/condition-groups/{groupId}/delete', array(\App\Adminx\Requests\Controller::class, 'groupDelete')),
			array('POST', '/requests/{id}/preview', array(\App\Adminx\Requests\Controller::class, 'preview'), array('permission' => 'view_requests')),
			array('POST', '/requests/{id}/native-audit', array(\App\Adminx\Requests\Controller::class, 'nativeAuditOne'), array('permission' => 'manage_requests')),
			array('POST', '/requests/{id}/native-stabilize', array(\App\Adminx\Requests\Controller::class, 'nativeAuditStabilize'), array('permission' => 'manage_requests')),
			array('GET', '/requests/{id}/explain-documents', array(\App\Adminx\Requests\Controller::class, 'explainDocuments'), array('permission' => 'view_requests')),
			array('POST', '/requests/{id}/explain', array(\App\Adminx\Requests\Controller::class, 'explain'), array('permission' => 'view_requests')),
			array('GET', '/requests/{id}', array(\App\Adminx\Requests\Controller::class, 'edit')),
			array('POST', '/requests/{id}', array(\App\Adminx\Requests\Controller::class, 'update'), array('permission' => 'manage_requests', 'sensitive' => 'stored_php.write', 'reauth' => true)),
			array('POST', '/requests/{id}/copy', array(\App\Adminx\Requests\Controller::class, 'copy'), array('permission' => 'manage_requests', 'sensitive' => 'stored_php.write', 'reauth' => true)),
			array('POST', '/requests/{id}/delete', array(\App\Adminx\Requests\Controller::class, 'destroy')),
			array('POST', '/requests/{id}/conditions', array(\App\Adminx\Requests\Controller::class, 'conditionSave'), array('permission' => 'manage_requests', 'sensitive' => 'stored_php.write', 'reauth' => true)),
		),

		'view_globals' => [
			'module_code' => 'requests',
		],
	];
