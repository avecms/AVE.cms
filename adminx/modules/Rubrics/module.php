<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Rubrics/module.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	return array(
		'code' => 'rubrics',
		'name' => 'Рубрики и поля',
		'version' => '0.3.11',
		'field_sets' => \App\Adminx\Rubrics\RubricFieldPresets::definitions(),
		'hook_definitions' => array(
			array(
				'name' => 'content.rubric.schema_impact',
				'description' => 'Дополняет анализ зависимостей перед восстановлением схемы рубрики',
				'kind' => 'filter',
				'context' => 'array{rubric_id,field_ids,fields,items}',
			),
		),

		'permissions' => array(
			'key' => 'rubrics',
			'items' => array(
				array(
					'code' => 'view_rubrics',
					'group_code' => 'content',
					'name' => 'Рубрики: просмотр',
					'description' => 'Просмотр рубрик, групп и полей.',
					'sort_order' => 10,
				),
				array(
					'code' => 'manage_rubrics',
					'group_code' => 'content',
					'name' => 'Рубрики: управление',
					'description' => 'Создание, изменение, удаление, сортировка и импорт рубрик и полей.',
					'sort_order' => 20,
				),
			),
			'icon' => 'ti ti-forms',
			'priority' => 33,
		),

		'navigation' => array(
			array(
				'code' => 'rubrics',
				'label' => 'Рубрики и поля',
				'url' => '/rubrics',
				'icon' => 'ti ti-forms',
				'permission' => 'view_rubrics',
				'group' => 'Контент',
				'sort_order' => 23,
				'match' => array(
					'/rubrics',
					'/rubrics/field-sets',
					'/directories',
				),
			),
		),

		'migrations' => array(
			array('id' => '001_create_rubrics_tables', 'file' => 'migrations/001_create_rubrics_tables.sql', 'legacy_checksums' => array('a296e8ea4c3e853e10f71a74c1f0790a21590f3e')),
			array('id' => '002_rubric_field_settings', 'file' => 'migrations/002_rubric_field_settings.sql'),
			array('id' => '003_native_public_document_context', 'file' => 'migrations/003_native_public_document_context.sql'),
			array('id' => '004_native_database_calls', 'file' => 'migrations/004_native_database_calls.sql'),
			array('id' => '005_correct_native_database_escape', 'file' => 'migrations/005_correct_native_database_escape.sql'),
			array('id' => '006_admin_document_views', 'file' => 'migrations/006_admin_document_views.sql'),
			array('id' => '007_rubric_open_graph', 'file' => 'migrations/007_rubric_open_graph.sql'),
			array('id' => '008_normalize_field_layout_width', 'file' => 'migrations/008_normalize_field_layout_width.sql'),
			array('id' => '009_expand_clean_starter_rubric', 'file' => 'migrations/009_expand_clean_starter_rubric.php'),
			array('id' => '010_form_conditions', 'file' => 'migrations/010_form_conditions.php'),
			array('id' => '011_schema_revisions', 'file' => 'migrations/011_schema_revisions.sql'),
			array('id' => '012_linked_field_sets', 'file' => 'migrations/012_linked_field_sets.sql'),
			array('id' => '013_group_form_conditions', 'file' => 'migrations/013_group_form_conditions.php'),
			array('id' => '014_rubric_purpose', 'file' => 'migrations/014_rubric_purpose.php'),
			array('id' => '015_merge_directory_permissions', 'file' => 'migrations/015_merge_directory_permissions.php'),
			array('id' => '016_rubric_trash', 'file' => 'migrations/016_rubric_trash.sql'),
			array('id' => '017_rubric_public_visibility', 'file' => 'migrations/017_rubric_public_visibility.sql'),
		),

		'routes' => array(
			array('GET', '/rubrics', array(\App\Adminx\Rubrics\Controller::class, 'index')),
			array('GET', '/rubrics/field-sets', array(\App\Adminx\Rubrics\Controller::class, 'fieldSets')),
			array('GET', '/rubrics/template-tags', array(\App\Adminx\Rubrics\Controller::class, 'templateTags')),
			array('GET', '/rubrics/alias-check', array(\App\Adminx\Rubrics\Controller::class, 'rubricAliasCheck')),
			array('GET', '/rubrics/fields/alias-check', array(\App\Adminx\Rubrics\Controller::class, 'fieldAliasCheck')),
			array('GET', '/rubrics/documents/picker', array(\App\Adminx\Rubrics\Controller::class, 'documentPicker')),
			array('GET', '/rubrics/field-types/{type}', array(\App\Adminx\Rubrics\Controller::class, 'showFieldType')),
			array('POST', '/rubrics/field-types/{type}/toggle', array(\App\Adminx\Rubrics\Controller::class, 'toggleFieldType')),
			array('POST', '/rubrics/reorder', array(\App\Adminx\Rubrics\Controller::class, 'reorderRubrics')),
			array('POST', '/rubrics', array(\App\Adminx\Rubrics\Controller::class, 'storeRubric'), array('permission' => 'manage_rubrics', 'sensitive' => 'stored_php.write', 'reauth' => true)),
			array('GET', '/rubrics/schema-revisions/{revision}', array(\App\Adminx\Rubrics\Controller::class, 'schemaRevision')),
			array('POST', '/rubrics/schema-revisions/{revision}/restore', array(\App\Adminx\Rubrics\Controller::class, 'restoreSchemaRevision')),
			array('POST', '/rubrics/schema-revisions/{revision}/delete', array(\App\Adminx\Rubrics\Controller::class, 'deleteSchemaRevision')),
			array('GET', '/rubrics/{id}/schema-revisions', array(\App\Adminx\Rubrics\Controller::class, 'schemaRevisions')),
			array('POST', '/rubrics/{id}/schema-revisions/delete', array(\App\Adminx\Rubrics\Controller::class, 'deleteSchemaRevisions')),
			array('GET', '/rubrics/trash', array(\App\Adminx\Rubrics\Controller::class, 'trash')),
			array('POST', '/rubrics/trash/{id}/restore', array(\App\Adminx\Rubrics\Controller::class, 'restoreTrash'), array('permission' => 'manage_rubrics')),
			array('POST', '/rubrics/trash/{id}/purge', array(\App\Adminx\Rubrics\Controller::class, 'purgeTrash'), array('permission' => 'manage_rubrics')),
			array('GET', '/rubrics/{id}', array(\App\Adminx\Rubrics\Controller::class, 'showRubric')),
			array('POST', '/rubrics/{id}', array(\App\Adminx\Rubrics\Controller::class, 'updateRubric'), array('permission' => 'manage_rubrics', 'sensitive' => 'stored_php.write', 'reauth' => true)),
			array('POST', '/rubrics/{id}/delete', array(\App\Adminx\Rubrics\Controller::class, 'destroyRubric')),
			array('GET', '/rubrics/{id}/fields', array(\App\Adminx\Rubrics\Controller::class, 'fields')),
			array('GET', '/rubrics/{id}/templates', array(\App\Adminx\Rubrics\Controller::class, 'templates')),
			array('GET', '/rubrics/{id}/admin-view', array(\App\Adminx\Rubrics\Controller::class, 'adminView')),
			array('POST', '/rubrics/{id}/admin-view', array(\App\Adminx\Rubrics\Controller::class, 'updateAdminView')),
			array('POST', '/rubrics/template/lint', array(\App\Adminx\Rubrics\Controller::class, 'lint')),
			array('POST', '/rubrics/{id}/templates/main', array(\App\Adminx\Rubrics\Controller::class, 'updateMainTemplate'), array('permission' => 'manage_rubrics', 'sensitive' => 'stored_php.write', 'reauth' => true)),
			array('POST', '/rubrics/{id}/templates', array(\App\Adminx\Rubrics\Controller::class, 'storeExtraTemplate'), array('permission' => 'manage_rubrics', 'sensitive' => 'stored_php.write', 'reauth' => true)),
			array('POST', '/rubrics/{id}/fields', array(\App\Adminx\Rubrics\Controller::class, 'storeField')),
			array('POST', '/rubrics/{id}/fields/builder/preview', array(\App\Adminx\Rubrics\Controller::class, 'previewFieldBuilder')),
			array('POST', '/rubrics/{id}/fields/builder', array(\App\Adminx\Rubrics\Controller::class, 'saveFieldBuilder')),
			array('GET', '/rubrics/{id}/field-sets/export', array(\App\Adminx\Rubrics\Controller::class, 'exportFieldSet')),
			array('POST', '/rubrics/{id}/field-sets/import/preview', array(\App\Adminx\Rubrics\Controller::class, 'previewImportedFieldSet')),
			array('POST', '/rubrics/{id}/field-sets/import/apply', array(\App\Adminx\Rubrics\Controller::class, 'applyImportedFieldSet')),
			array('GET', '/rubrics/{id}/field-set-links/{set}/preview', array(\App\Adminx\Rubrics\Controller::class, 'previewFieldSetSync')),
			array('POST', '/rubrics/{id}/field-set-links/{set}/sync', array(\App\Adminx\Rubrics\Controller::class, 'syncFieldSet')),
			array('POST', '/rubrics/{id}/field-set-links/{set}/detach', array(\App\Adminx\Rubrics\Controller::class, 'detachFieldSet')),
			array('GET', '/rubrics/{id}/field-sets/{set}/preview', array(\App\Adminx\Rubrics\Controller::class, 'previewFieldSet')),
			array('POST', '/rubrics/{id}/field-sets/{set}/apply', array(\App\Adminx\Rubrics\Controller::class, 'applyFieldSet')),
			array('POST', '/rubrics/{id}/fields/reorder', array(\App\Adminx\Rubrics\Controller::class, 'reorderFields')),
			array('POST', '/rubrics/{id}/groups', array(\App\Adminx\Rubrics\Controller::class, 'storeGroup')),
			array('POST', '/rubrics/{id}/groups/reorder', array(\App\Adminx\Rubrics\Controller::class, 'reorderGroups')),
			array('GET', '/rubrics/templates/{template}', array(\App\Adminx\Rubrics\Controller::class, 'showExtraTemplate')),
			array('POST', '/rubrics/templates/{template}', array(\App\Adminx\Rubrics\Controller::class, 'updateExtraTemplate'), array('permission' => 'manage_rubrics', 'sensitive' => 'stored_php.write', 'reauth' => true)),
			array('POST', '/rubrics/templates/{template}/delete', array(\App\Adminx\Rubrics\Controller::class, 'destroyExtraTemplate')),
			array('GET', '/rubrics/fields/{field}', array(\App\Adminx\Rubrics\Controller::class, 'showField')),
			array('GET', '/rubrics/fields/{field}/plugin', array(\App\Adminx\Rubrics\Controller::class, 'fieldPlugin')),
			array('POST', '/rubrics/fields/{field}', array(\App\Adminx\Rubrics\Controller::class, 'updateField')),
			array('POST', '/rubrics/fields/{field}/delete', array(\App\Adminx\Rubrics\Controller::class, 'destroyField')),
			array('POST', '/rubrics/groups/{group}', array(\App\Adminx\Rubrics\Controller::class, 'updateGroup')),
			array('POST', '/rubrics/groups/{group}/condition/preview', array(\App\Adminx\Rubrics\Controller::class, 'previewGroupCondition')),
			array('POST', '/rubrics/groups/{group}/delete', array(\App\Adminx\Rubrics\Controller::class, 'destroyGroup')),
		),

		'view_globals' => array(
			'module_code' => 'rubrics',
		),
	);
