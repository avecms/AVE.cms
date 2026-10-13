<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         setup/core-migrations.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	defined('AVE_SETUP') || die('Direct access to this location is not allowed.');

	// Core migrations already incorporated into setup/schema.sql. New migrations
	// must not be added here until their resulting schema is reflected there.
	return array(
		'blocks' => array('directory' => 'Blocks', 'ids' => array(
			'001_create_sysblocks_tables', '002_create_sysblock_revisions',
			'003_native_public_document_context', '004_native_database_calls',
			'005_correct_native_database_escape', '006_native_public_quick_edit',
			'007_cache_safe_public_quick_edit', '008_deferred_public_quick_edit_id',
			'009_disable_public_php_endpoint', '010_disable_retired_catalog_endpoints',
			'011_sysblock_editor_mode', '012_merge_visual_blocks',
		)),
		'catalog' => array('directory' => 'Catalog', 'ids' => array('005_add_catalog_purpose')),
		'console' => array('directory' => 'Console', 'ids' => array('001_create_console_snippets')),
		'customers' => array('directory' => 'Customers', 'ids' => array(
			'001_normalize_public_groups', '002_public_auth_schema', '003_normalize_last_visit',
			'004_materialize_public_auth_schema', '005_checkout_registration',
			'006_phone_identity', '007_customer_center',
		), 'backup' => array(
			'005_checkout_registration' => array(
				'{{public_user_prefix}}_auth_settings',
				'{{prefix}}_module_events',
				'{{prefix}}_module_migrations',
				'{{prefix}}_modules',
				'{{prefix}}_permissions',
				'{{prefix}}_role_permissions',
				'{{prefix}}_settings',
			),
			'006_phone_identity' => array('{{public_user_prefix}}_users'),
			'007_customer_center' => array(
				'{{system_prefix}}_customer_notes',
				'{{basket_prefix}}_module_basket_history',
			),
		)),
		'database' => array('directory' => 'Database', 'ids' => array(
			'001_mysql_legacy_index_compatibility', '002_module_lifecycle_recovery',
			'003_document_fields_text_composite_index',
		), 'backup' => array('003_document_fields_text_composite_index' => array())),
		'directories' => array('directory' => 'Directories', 'ids' => array(
			'001_create_directories',
		), 'backup' => array('001_create_directories' => array())),
		'documents' => array('directory' => 'Documents', 'ids' => array(
			'001_correct_breadcrumb_title', '002_correct_legacy_breadcrumb_title',
			'003_create_document_api_tokens', '004_optimize_public_document_indexes',
			'005_rename_document_teaser_to_excerpt', '006_configure_product_media_paths',
			'007_index_document_short_alias', '008_seed_clean_not_found_document',
			'009_deduplicate_document_short_aliases', '010_reset_dangling_rubric_templates',
			'011_normalize_document_enum_values', '012_default_legacy_language_columns',
			'013_document_edit_version', '014_document_relation_edges',
			'015_document_creation_presets', '016_reconcile_document_content_columns',
		), 'backup' => array(
			'015_document_creation_presets' => array(),
			'016_reconcile_document_content_columns' => array('{{content_prefix}}_documents'),
		)),
		'events' => array('directory' => 'Events', 'ids' => array('001_materialize_event_logs')),
		'groups' => array('directory' => 'Groups', 'ids' => array(
			'001_register_public_debug_permission', '002_remove_observer_role',
			'003_seed_default_roles', '004_merge_legacy_roles',
			'005_register_development_site_permission', '006_normalize_control_panel_permission',
		), 'backup' => array(
			'005_register_development_site_permission' => array('{{prefix}}_permissions'),
			'006_normalize_control_panel_permission' => array('{{prefix}}_permissions'),
		)),
		'media' => array('directory' => 'Media', 'ids' => array(
			'001_image_presets', '002_media_search_index',
		), 'backup' => array(
			'001_image_presets' => array(),
			'002_media_search_index' => array(),
		)),
		'navigation' => array('directory' => 'Navigation', 'ids' => array(
			'001_create_navigation_tables', '002_navigation_revisions',
		), 'backup' => array(
			'002_navigation_revisions' => array(),
		)),
		'notfound' => array('directory' => 'NotFound', 'optional' => true, 'ids' => array(
			'001_register_notfound_permissions', '002_create_not_found_log',
		)),
		'public_site' => array('directory' => 'PublicSite', 'ids' => array(
			'001_create_presentations',
		), 'backup' => array(
			'001_create_presentations' => array(
				'{{content_prefix}}_presentations',
				'{{content_prefix}}_presentation_revisions',
				'{{content_prefix}}_presentation_assignments',
			),
		)),
		'requests' => array('directory' => 'Requests', 'ids' => array(
			'001_native_database_calls', '002_correct_native_database_escape',
			'003_condition_groups', '004_recompile_condition_groups',
			'005_rebuild_native_condition_cache', '006_sargable_condition_cache',
			'007_result_contract', '008_preview_renderer', '009_native_executor',
			'010_native_audit_result', '011_order_tiebreaker', '012_condition_value_sources',
			'013_sort_rules', '014_request_revisions', '015_request_description_capacity',
		), 'backup' => array(
			'013_sort_rules' => array('{{content_prefix}}_request'),
			'014_request_revisions' => array(),
			'015_request_description_capacity' => array('{{content_prefix}}_request'),
		)),
		'rubrics' => array('directory' => 'Rubrics', 'ids' => array(
			'001_create_rubrics_tables', '002_rubric_field_settings',
			'003_native_public_document_context', '004_native_database_calls',
			'005_correct_native_database_escape', '006_admin_document_views',
			'007_rubric_open_graph', '008_normalize_field_layout_width',
			'009_expand_clean_starter_rubric', '010_form_conditions', '011_schema_revisions',
			'012_linked_field_sets', '013_group_form_conditions', '014_rubric_purpose',
			'015_merge_directory_permissions', '016_rubric_trash',
		), 'backup' => array(
			'012_linked_field_sets' => array(),
			'013_group_form_conditions' => array('{{content_prefix}}_rubric_fields_group'),
			'014_rubric_purpose' => array('{{content_prefix}}_rubrics'),
			'015_merge_directory_permissions' => array(
				'{{prefix}}_permissions',
				'{{prefix}}_role_permissions',
			),
			'016_rubric_trash' => array(),
		)),
		'security' => array('directory' => 'Security', 'ids' => array('001_create_ip_blocks')),
		'settings' => array('directory' => 'Settings', 'ids' => array(
			'001_create_settings_tables', '002_normalize_breadcrumb_separator',
			'003_remove_unused_constants', '004_seed_core_display_defaults',
			'005_order_system_navigation', '006_create_admin_saved_views',
			'007_public_site_url',
		), 'backup' => array(
			'006_create_admin_saved_views' => array(),
			'007_public_site_url' => array('{{prefix}}_constants'),
		)),
		'templates' => array('directory' => 'Templates', 'ids' => array(
			'001_create_templates_tables', '002_create_template_revisions',
		)),
		'themes' => array('directory' => 'Themes', 'ids' => array(
			'001_create_theme_asset_revisions', '002_place_theme_navigation',
		)),
		'users' => array('directory' => 'Users', 'ids' => array(
			'001_normalize_public_session_activity', '002_harden_public_remember_tokens',
			'003_system_user_security', '004_system_session_expiry',
			'005_public_session_capacity',
		), 'backup' => array(
			'002_harden_public_remember_tokens' => array('{{public_user_prefix}}_users_session'),
			'003_system_user_security' => array(),
			'004_system_session_expiry' => array('{{prefix}}_users_session'),
			'005_public_session_capacity' => array('{{public_shell_prefix}}_sessions'),
		)),
	);
