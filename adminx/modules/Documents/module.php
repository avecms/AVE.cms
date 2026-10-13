<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Documents/module.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	return array(
		'code' => 'documents',
		'name' => 'Документы',
		'version' => '0.4.10',

		'permissions' => array(
			'key' => 'documents',
			'items' => array(
				array(
					'code' => 'view_documents',
					'group_code' => 'navigation',
					'name' => 'Документы: просмотр',
					'description' => 'Просмотр списка документов и базовых метаданных.',
					'sort_order' => 10,
				),
				array(
					'code' => 'manage_documents',
					'group_code' => 'content',
					'name' => 'Документы: управление',
					'description' => 'Создание, изменение, удаление и восстановление документов.',
					'sort_order' => 20,
				),
				array(
					'code' => 'manage_document_api',
					'group_code' => 'content',
					'name' => 'Документы: API-токены',
					'description' => 'Выпуск и отзыв ключей JSON API документов.',
					'sort_order' => 30,
				),
			),
			'icon' => 'ti ti-files',
			'priority' => 21,
		),

		'navigation' => array(
			array(
				'code' => 'documents',
				'label' => 'Документы',
				'url' => '/documents',
				'icon' => 'ti ti-files',
				'permission' => 'view_documents',
				'group' => 'Контент',
				'sort_order' => 21,
				'match' => array(
					'/documents',
				),
			),
		),

		'routes' => array(
			array('GET', '/documents', array(\App\Adminx\Documents\Controller::class, 'index')),
			array('POST', '/documents/saved-views', array(\App\Adminx\Documents\Controller::class, 'saveSavedView')),
			array('POST', '/documents/saved-views/{id}/delete', array(\App\Adminx\Documents\Controller::class, 'deleteSavedView')),
			array('GET', '/documents/create', array(\App\Adminx\Documents\Controller::class, 'create')),
			array('GET', '/documents/alias-check', array(\App\Adminx\Documents\Controller::class, 'aliasCheck')),
			array('POST', '/documents/slug', array(\App\Adminx\Documents\Controller::class, 'slug')),
			array('POST', '/documents/short-alias', array(\App\Adminx\Documents\Controller::class, 'shortAlias')),
			array('POST', '/documents/preview', array(\App\Adminx\Documents\Controller::class, 'previewPayload')),
			array('POST', '/documents/bulk', array(\App\Adminx\Documents\Controller::class, 'bulk')),
			array('GET', '/documents/bulk-editor', array(\App\Adminx\Documents\Controller::class, 'bulkEditor')),
			array('GET', '/documents/bulk-editor/fields', array(\App\Adminx\Documents\Controller::class, 'bulkEditorFields')),
			array('POST', '/documents/bulk-editor/preview', array(\App\Adminx\Documents\Controller::class, 'bulkEditorPreview')),
			array('POST', '/documents/bulk-editor/run', array(\App\Adminx\Documents\Controller::class, 'bulkEditorRun')),
			array('POST', '/documents/bulk-editor/cancel', array(\App\Adminx\Documents\Controller::class, 'bulkEditorCancel')),
			array('POST', '/documents/snapshots/rebuild', array(\App\Adminx\Documents\Controller::class, 'rebuildSnapshots')),
			array('GET', '/documents/picker', array(\App\Adminx\Documents\Controller::class, 'documentPicker')),
			array('GET', '/documents/terms', array(\App\Adminx\Documents\Controller::class, 'termSuggestions')),
			array('GET', '/documents/views', array(\App\Adminx\Documents\Controller::class, 'views')),
			array('POST', '/documents/views/clear', array(\App\Adminx\Documents\Controller::class, 'clearViews')),
			array('GET', '/documents/redirects', array(\App\Adminx\Documents\Controller::class, 'redirects')),
			array('GET', '/documents/api', array(\App\Adminx\Documents\Controller::class, 'apiTokens')),
			array('POST', '/documents/api/tokens', array(\App\Adminx\Documents\Controller::class, 'issueApiToken')),
			array('POST', '/documents/api/tokens/{id}/revoke', array(\App\Adminx\Documents\Controller::class, 'revokeApiToken')),
			array('GET', '/documents/{id}/revisions', array(\App\Adminx\Documents\Controller::class, 'revisions')),
			array('GET', '/documents/{id}/snapshot', array(\App\Adminx\Documents\Controller::class, 'snapshot')),
			array('POST', '/documents/{id}/snapshot/rebuild', array(\App\Adminx\Documents\Controller::class, 'rebuildSnapshot')),
			array('POST', '/documents/{id}/revisions/delete', array(\App\Adminx\Documents\Controller::class, 'deleteRevisions')),
			array('GET', '/documents/{id}/aliases', array(\App\Adminx\Documents\Controller::class, 'aliases')),
			array('POST', '/documents/{id}/aliases', array(\App\Adminx\Documents\Controller::class, 'saveAlias')),
			array('POST', '/documents/{id}/aliases/{alias}', array(\App\Adminx\Documents\Controller::class, 'saveAlias')),
			array('POST', '/documents/{id}/aliases/{alias}/delete', array(\App\Adminx\Documents\Controller::class, 'deleteAlias')),
			array('GET', '/documents/{id}/remarks', array(\App\Adminx\Documents\Controller::class, 'remarks')),
			array('POST', '/documents/{id}/remarks', array(\App\Adminx\Documents\Controller::class, 'addRemark')),
			array('POST', '/documents/{id}/remarks/{remark}/delete', array(\App\Adminx\Documents\Controller::class, 'deleteRemark')),
			array('GET', '/documents/revisions/{revision}', array(\App\Adminx\Documents\Controller::class, 'revision')),
			array('POST', '/documents/revisions/{revision}/restore', array(\App\Adminx\Documents\Controller::class, 'restoreRevision')),
			array('POST', '/documents/revisions/{revision}/delete', array(\App\Adminx\Documents\Controller::class, 'deleteRevision')),
			array('GET', '/documents/{id}/edit', array(\App\Adminx\Documents\Controller::class, 'edit')),
			array('GET', '/documents/{id}', array(\App\Adminx\Documents\Controller::class, 'show')),
			array('POST', '/documents', array(\App\Adminx\Documents\Controller::class, 'store')),
			array('POST', '/documents/{id}', array(\App\Adminx\Documents\Controller::class, 'update')),
			array('POST', '/documents/{id}/delete', array(\App\Adminx\Documents\Controller::class, 'destroy')),
			array('POST', '/documents/{id}/restore', array(\App\Adminx\Documents\Controller::class, 'restore')),
			array('POST', '/documents/{id}/toggle', array(\App\Adminx\Documents\Controller::class, 'toggle')),
			array('POST', '/documents/{id}/copy', array(\App\Adminx\Documents\Controller::class, 'copy')),
			array('POST', '/documents/{id}/presets', array(\App\Adminx\Documents\Controller::class, 'saveCreationPreset')),
			array('POST', '/documents/presets/{preset}/delete', array(\App\Adminx\Documents\Controller::class, 'deleteCreationPreset')),
			array('POST', '/documents/{id}/purge', array(\App\Adminx\Documents\Controller::class, 'purge')),
		),

		'migrations' => array(
			array('id' => '001_correct_breadcrumb_title', 'file' => 'migrations/001_correct_breadcrumb_title.sql', 'legacy_checksums' => array('0ee53933c726cae4f1f9cac350259c4559471fb4')),
			array('id' => '002_correct_legacy_breadcrumb_title', 'file' => 'migrations/002_correct_legacy_breadcrumb_title.sql', 'legacy_checksums' => array('7384b0522cf8baa2029949517b8215caacaa8ba3')),
			array('id' => '003_create_document_api_tokens', 'file' => 'migrations/003_create_document_api_tokens.sql'),
			array('id' => '004_optimize_public_document_indexes', 'file' => 'migrations/004_optimize_public_document_indexes.sql'),
			array('id' => '005_rename_document_teaser_to_excerpt', 'file' => 'migrations/005_rename_document_teaser_to_excerpt.sql'),
			array('id' => '006_configure_product_media_paths', 'file' => 'migrations/006_configure_product_media_paths.php', 'legacy_checksums' => array('1208cc44ddc47b655159acfb411e6a854a365c49')),
			array('id' => '007_index_document_short_alias', 'file' => 'migrations/007_index_document_short_alias.sql'),
			array('id' => '008_seed_clean_not_found_document', 'file' => 'migrations/008_seed_clean_not_found_document.php', 'legacy_checksums' => array('dd3a795de2b6b152c148c9ee65089e42d83acdfe')),
			array('id' => '009_deduplicate_document_short_aliases', 'file' => 'migrations/009_deduplicate_document_short_aliases.php'),
			array('id' => '010_reset_dangling_rubric_templates', 'file' => 'migrations/010_reset_dangling_rubric_templates.sql'),
			array('id' => '011_normalize_document_enum_values', 'file' => 'migrations/011_normalize_document_enum_values.sql'),
			array('id' => '012_default_legacy_language_columns', 'file' => 'migrations/012_default_legacy_language_columns.php'),
			array('id' => '013_document_edit_version', 'file' => 'migrations/013_document_edit_version.php'),
			array('id' => '014_document_relation_edges', 'file' => 'migrations/014_document_relation_edges.sql'),
			array('id' => '015_document_creation_presets', 'file' => 'migrations/015_document_creation_presets.sql'),
			array('id' => '016_reconcile_document_content_columns', 'file' => 'migrations/016_reconcile_document_content_columns.php'),
			array('id' => '017_document_public_visibility', 'file' => 'migrations/017_document_public_visibility.sql'),
		),

		'view_globals' => array(
			'module_code' => 'documents',
		),
	);
