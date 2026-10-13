-- AVE.cms 3.3 core schema.
-- The installer replaces {{prefix}} with the validated project prefix.
-- This file intentionally contains no module tables and no project data.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Framework and control-panel identity -----------------------------------------

CREATE TABLE IF NOT EXISTS `{{prefix}}_settings` (
  `param` VARCHAR(190) NOT NULL,
  `value` MEDIUMTEXT DEFAULT NULL,
  `type` VARCHAR(20) NOT NULL DEFAULT 'string',
  PRIMARY KEY (`param`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_admin_saved_views` (
  `id` CHAR(12) CHARACTER SET ascii NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `scope` VARCHAR(64) CHARACTER SET ascii NOT NULL,
  `title` VARCHAR(60) NOT NULL,
  `filters_json` TEXT NOT NULL,
  `updated_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_scope_title` (`user_id`,`scope`,`title`),
  KEY `idx_user_scope` (`user_id`,`scope`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) CHARACTER SET ascii NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'moderator',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  `login` VARCHAR(150) DEFAULT NULL,
  `first_name` VARCHAR(100) DEFAULT NULL,
  `last_name` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(35) DEFAULT NULL,
  `last_login_at` DATETIME DEFAULT NULL,
  `password_changed_at` DATETIME DEFAULT NULL,
  `must_change_password` TINYINT(1) NOT NULL DEFAULT 0,
  `legacy_id` INT UNSIGNED DEFAULT NULL,
  `legacy_password` VARCHAR(64) DEFAULT NULL,
  `legacy_salt` VARCHAR(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_email` (`email`),
  UNIQUE KEY `uniq_login` (`login`),
  UNIQUE KEY `uniq_legacy_id` (`legacy_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_users_session` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `agent` VARCHAR(255) DEFAULT NULL,
  `ip` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `last_active` DATETIME NOT NULL,
	`expires_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_token` (`token_hash`),
  KEY `idx_user` (`user_id`),
	KEY `idx_user_active` (`user_id`,`last_active`),
	KEY `idx_expiry` (`expires_at`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL,
  `name` VARCHAR(150) NOT NULL DEFAULT '',
  `is_system` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_code` (`code`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_permissions` (
  `code` VARCHAR(100) NOT NULL,
  `module` VARCHAR(50) NOT NULL DEFAULT '',
  `group_code` VARCHAR(50) NOT NULL DEFAULT '',
  `name` VARCHAR(200) NOT NULL DEFAULT '',
  `description` VARCHAR(255) NOT NULL DEFAULT '',
  `sort_order` INT NOT NULL DEFAULT 100,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_role_permissions` (
  `role_id` INT UNSIGNED NOT NULL,
  `permission_code` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`role_id`, `permission_code`),
  KEY `idx_role` (`role_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_api_tokens` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(190) NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `token_prefix` VARCHAR(20) NOT NULL,
  `scopes` VARCHAR(255) NOT NULL,
  `last_used_at` DATETIME DEFAULT NULL,
  `expires_at` DATETIME DEFAULT NULL,
  `revoked_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_token_hash` (`token_hash`),
  KEY `idx_user` (`user_id`),
  KEY `idx_active` (`revoked_at`, `expires_at`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_audit_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `actor_id` INT UNSIGNED DEFAULT NULL,
  `actor_name` VARCHAR(190) NOT NULL DEFAULT '',
  `action` VARCHAR(100) NOT NULL,
  `target_type` VARCHAR(50) NOT NULL DEFAULT '',
  `target_id` INT UNSIGNED DEFAULT NULL,
  `meta` MEDIUMTEXT DEFAULT NULL,
  `ip` VARCHAR(45) NOT NULL DEFAULT '',
  `user_agent` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_created` (`created_at`),
  KEY `idx_action` (`action`),
  KEY `idx_actor` (`actor_id`),
  KEY `idx_target_action` (`target_id`,`action`,`created_at`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_module_migrations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `module` VARCHAR(80) CHARACTER SET ascii NOT NULL,
  `migration` VARCHAR(190) CHARACTER SET ascii NOT NULL,
  `checksum` CHAR(40) NOT NULL,
  `applied_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_module_migration` (`module`, `migration`),
  KEY `idx_module` (`module`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_module_migration_attempts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `module` VARCHAR(80) CHARACTER SET ascii NOT NULL,
  `migration` VARCHAR(190) CHARACTER SET ascii NOT NULL,
  `checksum` CHAR(40) NOT NULL,
  `operation` VARCHAR(20) CHARACTER SET ascii NOT NULL DEFAULT '',
  `status` VARCHAR(20) CHARACTER SET ascii NOT NULL,
  `queries_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `error_message` TEXT DEFAULT NULL,
  `actor_id` INT UNSIGNED DEFAULT NULL,
  `started_at` DATETIME NOT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_module_started` (`module`, `started_at`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_media_image_presets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(64) CHARACTER SET ascii NOT NULL DEFAULT '',
  `title` VARCHAR(190) NOT NULL DEFAULT '',
  `group_label` VARCHAR(120) NOT NULL DEFAULT '',
  `mode` VARCHAR(16) CHARACTER SET ascii NOT NULL DEFAULT 'cover',
  `width` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `height` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `format` VARCHAR(16) CHARACTER SET ascii NOT NULL DEFAULT 'original',
  `quality` TINYINT UNSIGNED NOT NULL DEFAULT 82,
  `webp_twin` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 100,
  `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_code` (`code`),
  KEY `idx_sort` (`sort_order`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_media_search_index` (
  `path_hash` CHAR(40) CHARACTER SET ascii NOT NULL,
  `path` TEXT NOT NULL,
  `name` VARCHAR(190) NOT NULL DEFAULT '',
  `extension` VARCHAR(20) CHARACTER SET ascii NOT NULL DEFAULT '',
  `is_image` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  `size` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `modified_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`path_hash`),
  KEY `idx_modified` (`modified_at`),
  KEY `idx_type` (`is_image`,`extension`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_customer_notes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `author_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `note` TEXT NOT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_customer_notes` (`user_id`,`created_at`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_modules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(100) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `version` VARCHAR(50) NOT NULL DEFAULT '',
  `schema_version` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` VARCHAR(30) NOT NULL DEFAULT 'available',
  `base_path` VARCHAR(500) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `installed_at` DATETIME DEFAULT NULL,
  `enabled_at` DATETIME DEFAULT NULL,
  `disabled_at` DATETIME DEFAULT NULL,
  `previous_status` VARCHAR(30) NOT NULL DEFAULT '',
  `operation` VARCHAR(20) NOT NULL DEFAULT '',
  `operation_started_at` DATETIME DEFAULT NULL,
  `failed_at` DATETIME DEFAULT NULL,
  `last_error` TEXT DEFAULT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_code` (`code`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_module_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `module_code` VARCHAR(100) NOT NULL,
  `event_type` VARCHAR(50) NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'ok',
  `message` TEXT DEFAULT NULL,
  `context` LONGTEXT DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_module_created` (`module_code`, `created_at`),
  KEY `idx_type` (`event_type`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_constants` (
  `name` VARCHAR(80) NOT NULL,
  `value` MEDIUMTEXT DEFAULT NULL,
  `type` VARCHAR(20) NOT NULL DEFAULT 'string',
  `group_code` VARCHAR(80) NOT NULL DEFAULT 'custom',
  `label` VARCHAR(190) NOT NULL DEFAULT '',
  `description` TEXT DEFAULT NULL,
  `options` TEXT DEFAULT NULL,
  `is_system` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 100,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`name`),
  KEY `idx_group_code` (`group_code`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_paginations` (
  `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pagination_name` TINYTEXT DEFAULT NULL,
  `pagination_box` VARCHAR(255) NOT NULL DEFAULT '',
  `pagination_start_label` VARCHAR(255) NOT NULL DEFAULT '',
  `pagination_end_label` VARCHAR(255) NOT NULL DEFAULT '',
  `pagination_separator_box` VARCHAR(255) NOT NULL DEFAULT '',
  `pagination_separator_label` VARCHAR(255) NOT NULL DEFAULT '',
  `pagination_next_label` VARCHAR(255) NOT NULL DEFAULT '',
  `pagination_prev_label` VARCHAR(255) NOT NULL DEFAULT '',
  `pagination_link_box` VARCHAR(255) NOT NULL DEFAULT '',
  `pagination_active_link_box` VARCHAR(255) NOT NULL DEFAULT '',
  `pagination_link_template` VARCHAR(255) DEFAULT NULL,
  `pagination_link_active_template` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_ip_blocks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(45) NOT NULL,
  `reason` VARCHAR(500) NOT NULL DEFAULT '',
  `expires_at` INT UNSIGNED DEFAULT NULL,
  `actor_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ip` (`ip`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_user_agent_blocks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pattern_hash` CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `pattern` VARCHAR(500) NOT NULL,
  `match_type` VARCHAR(16) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'contains',
  `reason` VARCHAR(500) NOT NULL DEFAULT '',
  `expires_at` INT UNSIGNED DEFAULT NULL,
  `actor_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_agent_pattern` (`pattern_hash`),
  KEY `idx_user_agent_expires` (`expires_at`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_referrer_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `log_date` DATE NOT NULL,
  `visitor_hash` CHAR(32) NOT NULL,
  `source_type` VARCHAR(24) NOT NULL,
  `source_name` VARCHAR(190) NOT NULL,
  `referer_host` VARCHAR(190) NOT NULL DEFAULT '',
  `referer_url` VARCHAR(1000) NOT NULL DEFAULT '',
  `landing_path` VARCHAR(1000) NOT NULL,
  `utm_source` VARCHAR(255) NOT NULL DEFAULT '',
  `utm_medium` VARCHAR(255) NOT NULL DEFAULT '',
  `utm_campaign` VARCHAR(255) NOT NULL DEFAULT '',
  `utm_term` VARCHAR(255) NOT NULL DEFAULT '',
  `utm_content` VARCHAR(255) NOT NULL DEFAULT '',
  `tracking_json` TEXT DEFAULT NULL,
  `user_agent` VARCHAR(500) NOT NULL DEFAULT '',
  `first_seen_at` INT UNSIGNED NOT NULL,
  `last_seen_at` INT UNSIGNED NOT NULL,
  `hits` INT UNSIGNED NOT NULL DEFAULT 1,
  `dedupe_hash` CHAR(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dedupe` (`dedupe_hash`),
  KEY `idx_last_seen` (`last_seen_at`),
  KEY `idx_log_date` (`log_date`),
  KEY `idx_source` (`source_type`, `source_name`(167))
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_not_found_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `path_hash` CHAR(64) NOT NULL,
  `path` VARCHAR(512) NOT NULL,
  `query_string` VARCHAR(512) NOT NULL DEFAULT '',
  `referer` VARCHAR(512) NOT NULL DEFAULT '',
  `user_agent` VARCHAR(255) NOT NULL DEFAULT '',
  `request_ip` VARCHAR(45) NOT NULL DEFAULT '',
  `hits` INT UNSIGNED NOT NULL DEFAULT 1,
  `resolved` TINYINT(1) NOT NULL DEFAULT 0,
  `first_seen_at` INT UNSIGNED NOT NULL,
  `last_seen_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_path` (`path_hash`),
  KEY `idx_last_seen` (`last_seen_at`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_console_snippets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(190) NOT NULL,
  `code` MEDIUMTEXT NOT NULL,
  `author_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_updated` (`updated_at`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

-- Public shell and accounts ----------------------------------------------------

CREATE TABLE IF NOT EXISTS `{{prefix}}_public_sessions` (
  `sesskey` VARCHAR(256) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `expiry` INT UNSIGNED NOT NULL DEFAULT 0,
  `value` MEDIUMTEXT NOT NULL,
  `Ip` VARCHAR(45) NOT NULL DEFAULT '',
  `expire_datum` VARCHAR(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`sesskey`),
  KEY `idx_expiry` (`expiry`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_public_settings` (
  `Id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `site_name` VARCHAR(255) NOT NULL,
  `mail_type` ENUM('mail','smtp','sendmail') NOT NULL DEFAULT 'mail',
  `mail_content_type` ENUM('text/plain','text/html') NOT NULL DEFAULT 'text/plain',
  `mail_port` SMALLINT UNSIGNED NOT NULL DEFAULT 25,
  `mail_host` VARCHAR(255) NOT NULL DEFAULT '',
  `mail_smtp_login` VARCHAR(255) NOT NULL DEFAULT '',
  `mail_smtp_pass` VARCHAR(255) NOT NULL DEFAULT '',
  `mail_smtp_encrypt` VARCHAR(255) DEFAULT NULL,
  `mail_sendmail_path` VARCHAR(255) NOT NULL DEFAULT '/usr/sbin/sendmail',
  `mail_word_wrap` SMALLINT NOT NULL DEFAULT 80,
  `mail_from` VARCHAR(255) NOT NULL DEFAULT '',
  `mail_from_name` VARCHAR(255) NOT NULL DEFAULT '',
  `mail_new_user` TEXT NOT NULL,
  `mail_signature` TEXT NOT NULL,
  `page_not_found_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `message_forbidden` TEXT NOT NULL,
  `navi_box` VARCHAR(255) NOT NULL,
  `start_label` VARCHAR(255) NOT NULL,
  `end_label` VARCHAR(255) NOT NULL,
  `separator_label` VARCHAR(255) NOT NULL,
  `next_label` VARCHAR(255) NOT NULL,
  `prev_label` VARCHAR(255) NOT NULL,
  `total_label` VARCHAR(255) NOT NULL,
  `link_box` VARCHAR(255) NOT NULL,
  `total_box` VARCHAR(255) NOT NULL,
  `active_box` VARCHAR(255) NOT NULL,
  `separator_box` VARCHAR(255) NOT NULL,
  `bread_box` VARCHAR(500) NOT NULL DEFAULT '',
  `bread_show_main` ENUM('1','0') NOT NULL DEFAULT '1',
  `bread_show_host` ENUM('1','0') NOT NULL DEFAULT '0',
  `bread_separator` VARCHAR(255) NOT NULL,
  `bread_separator_use` ENUM('1','0') NOT NULL DEFAULT '0',
  `bread_link_box` VARCHAR(500) NOT NULL DEFAULT '',
  `bread_link_template` VARCHAR(500) NOT NULL DEFAULT '',
  `bread_self_box` VARCHAR(500) NOT NULL DEFAULT '',
  `bread_link_box_last` ENUM('1','0') NOT NULL DEFAULT '1',
  `date_format` VARCHAR(25) NOT NULL DEFAULT '%d.%m.%Y',
  `time_format` VARCHAR(25) NOT NULL DEFAULT '%d.%m.%Y, %H:%M',
  `default_country` CHAR(2) NOT NULL DEFAULT 'RU',
  `use_doctime` ENUM('1','0') NOT NULL DEFAULT '0',
  `hidden_text` TEXT NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_public_users` (
  `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(190) NULL DEFAULT NULL,
  `email_verified_at` INT UNSIGNED NOT NULL DEFAULT 0,
  `street` VARCHAR(100) NOT NULL DEFAULT '',
  `street_nr` VARCHAR(10) NOT NULL DEFAULT '',
  `zipcode` VARCHAR(15) NOT NULL DEFAULT '',
  `city` VARCHAR(100) NOT NULL DEFAULT '',
  `phone` VARCHAR(35) NOT NULL DEFAULT '',
  `phone_normalized` VARCHAR(20) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL,
  `phone_verified_at` INT UNSIGNED NOT NULL DEFAULT 0,
  `telefax` VARCHAR(35) NOT NULL DEFAULT '',
  `description` VARCHAR(255) NOT NULL DEFAULT '',
  `firstname` VARCHAR(50) NOT NULL DEFAULT '',
  `lastname` VARCHAR(50) NOT NULL DEFAULT '',
  `user_name` VARCHAR(50) NOT NULL,
  `user_group` SMALLINT UNSIGNED NOT NULL DEFAULT 4,
  `user_group_extra` VARCHAR(255) NOT NULL DEFAULT '',
  `reg_time` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('1','0') NOT NULL DEFAULT '1',
  `last_visit` INT UNSIGNED NOT NULL DEFAULT 0,
  `country` CHAR(2) NOT NULL DEFAULT 'RU',
  `birthday` CHAR(10) NOT NULL DEFAULT '',
  `deleted` ENUM('0','1') NOT NULL DEFAULT '0',
  `del_time` INT UNSIGNED NOT NULL DEFAULT 0,
  `emc` CHAR(32) NOT NULL DEFAULT '',
  `reg_ip` VARCHAR(45) NOT NULL DEFAULT '',
  `new_pass` CHAR(32) NOT NULL DEFAULT '',
  `company` VARCHAR(255) NOT NULL DEFAULT '',
  `taxpay` ENUM('0','1') NOT NULL DEFAULT '0',
  `salt` VARCHAR(64) NOT NULL DEFAULT '',
  `new_salt` CHAR(16) NOT NULL DEFAULT '',
  `user_ip` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `uniq_email` (`email`),
  UNIQUE KEY `uniq_name` (`user_name`),
  UNIQUE KEY `uniq_phone_normalized` (`phone_normalized`),
  KEY `idx_group` (`user_group`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_public_users_session` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `hash` CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `token_version` TINYINT UNSIGNED NOT NULL DEFAULT 2,
  `ip` INT UNSIGNED NOT NULL DEFAULT 0,
  `agent` VARCHAR(255) NOT NULL DEFAULT '',
  `last_active` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
  `expires_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_remember_hash` (`hash`),
  KEY `idx_remember_expiry` (`expires_at`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_public_user_groups` (
  `user_group` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_group_name` VARCHAR(50) NOT NULL,
  `status` ENUM('1','0') NOT NULL DEFAULT '1',
  `set_default_avatar` ENUM('1','0') NOT NULL DEFAULT '0',
  `default_avatar` VARCHAR(255) NOT NULL DEFAULT '',
  `user_group_permission` LONGTEXT NOT NULL,
  PRIMARY KEY (`user_group`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_public_auth_tokens` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `purpose` VARCHAR(32) NOT NULL,
  `channel` VARCHAR(24) NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `expires_at` INT UNSIGNED NOT NULL,
  `used_at` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_token_hash` (`token_hash`),
  KEY `idx_user_purpose` (`user_id`, `purpose`),
  KEY `idx_expiry` (`expires_at`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_public_auth_settings` (
  `id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `registration_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `registration_mode` VARCHAR(16) NOT NULL DEFAULT 'email',
  `registration_gate` VARCHAR(24) NOT NULL DEFAULT 'email',
  `default_group` SMALLINT UNSIGNED NOT NULL DEFAULT 4,
  `password_min_length` TINYINT UNSIGNED NOT NULL DEFAULT 8,
  `verification_ttl` INT UNSIGNED NOT NULL DEFAULT 86400,
  `reset_ttl` INT UNSIGNED NOT NULL DEFAULT 3600,
  `password_reset_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `require_firstname` TINYINT(1) NOT NULL DEFAULT 1,
  `show_lastname` TINYINT(1) NOT NULL DEFAULT 1,
  `require_lastname` TINYINT(1) NOT NULL DEFAULT 0,
  `show_phone` TINYINT(1) NOT NULL DEFAULT 0,
  `require_phone` TINYINT(1) NOT NULL DEFAULT 0,
  `show_company` TINYINT(1) NOT NULL DEFAULT 0,
  `require_company` TINYINT(1) NOT NULL DEFAULT 0,
  `deny_domains` TEXT DEFAULT NULL,
  `deny_emails` TEXT DEFAULT NULL,
  `checkout_registration_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `checkout_policy_url` VARCHAR(255) NOT NULL DEFAULT '/privacy-policy',
  `checkout_policy_label` VARCHAR(300) NOT NULL DEFAULT '',
  `checkout_access_subject` VARCHAR(190) NOT NULL DEFAULT 'Доступ к личному кабинету',
  `checkout_access_template` MEDIUMTEXT DEFAULT NULL,
  `pages_json` MEDIUMTEXT DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_public_auth_templates` (
  `page_key` VARCHAR(32) NOT NULL,
  `template_text` MEDIUMTEXT NOT NULL,
  `updated_by` INT UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`page_key`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_public_user_profile_fields` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(64) NOT NULL,
  `name` VARCHAR(160) NOT NULL,
  `type` VARCHAR(24) NOT NULL DEFAULT 'text',
  `options` TEXT DEFAULT NULL,
  `is_required` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `show_registration` TINYINT(1) NOT NULL DEFAULT 0,
  `show_profile` TINYINT(1) NOT NULL DEFAULT 1,
  `position` INT NOT NULL DEFAULT 0,
  `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_code` (`code`),
  KEY `idx_active_position` (`is_active`, `position`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_public_user_profile_values` (
  `user_id` INT UNSIGNED NOT NULL,
  `field_id` INT UNSIGNED NOT NULL,
  `value` TEXT DEFAULT NULL,
  `updated_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`, `field_id`),
  KEY `idx_field` (`field_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_public_user_identities` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `provider` VARCHAR(24) CHARACTER SET ascii NOT NULL,
  `provider_user_id` VARCHAR(190) CHARACTER SET ascii NOT NULL,
  `email` VARCHAR(190) NOT NULL DEFAULT '',
  `profile_json` TEXT DEFAULT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  `updated_at` INT UNSIGNED NOT NULL,
  `last_login_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_provider_identity` (`provider`, `provider_user_id`),
  KEY `idx_identity_user` (`user_id`),
  KEY `idx_identity_email` (`email`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

-- Templates, rubrics and documents --------------------------------------------

CREATE TABLE IF NOT EXISTS `{{prefix}}_templates` (
  `Id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_title` VARCHAR(255) NOT NULL DEFAULT '',
  `template_text` LONGTEXT NOT NULL,
  `template_author_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `template_created` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`Id`),
  KEY `idx_template_title` (`template_title`(191)),
  KEY `idx_template_author` (`template_author_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_template_revisions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_id` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `action` VARCHAR(32) NOT NULL DEFAULT 'update',
  `snapshot_hash` CHAR(40) NOT NULL DEFAULT '',
  `text_hash` CHAR(40) NOT NULL DEFAULT '',
  `snapshot_json` LONGTEXT NOT NULL,
  `comment` VARCHAR(255) NOT NULL DEFAULT '',
  `author_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `author_name` VARCHAR(255) NOT NULL DEFAULT '',
  `source_revision_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_template_created` (`template_id`, `created_at`),
  KEY `idx_action` (`action`),
  KEY `idx_snapshot_hash` (`snapshot_hash`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_theme_asset_revisions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `theme` VARCHAR(64) CHARACTER SET ascii NOT NULL,
  `asset_path` VARCHAR(500) NOT NULL,
  `action` VARCHAR(32) CHARACTER SET ascii NOT NULL DEFAULT 'update',
  `checksum` CHAR(64) CHARACTER SET ascii NOT NULL,
  `content` LONGTEXT NOT NULL,
  `content_size` INT UNSIGNED NOT NULL DEFAULT 0,
  `author_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `author_name` VARCHAR(255) NOT NULL DEFAULT '',
  `source_revision_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_theme_created` (`theme`,`created_at`),
  KEY `idx_theme_path` (`theme`,`asset_path`(120),`created_at`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_rubrics` (
  `Id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rubric_title` VARCHAR(255) NOT NULL DEFAULT '',
  `rubric_alias` VARCHAR(255) NOT NULL DEFAULT '',
  `rubric_alias_history` ENUM('0','1') NOT NULL DEFAULT '0',
  `rubric_template` LONGTEXT NOT NULL,
  `rubric_template_id` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `rubric_author_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `rubric_created` INT UNSIGNED NOT NULL DEFAULT 0,
  `rubric_docs_active` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `rubric_is_technical` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `rubric_start_code` TEXT NOT NULL,
  `rubric_code_start` TEXT NOT NULL,
  `rubric_code_end` TEXT NOT NULL,
  `rubric_teaser_template` TEXT NOT NULL,
  `rubric_header_template` TEXT NOT NULL,
  `rubric_og_template` TEXT NOT NULL,
  `rubric_footer_template` TEXT NOT NULL,
  `rubric_linked_rubric` VARCHAR(255) NOT NULL DEFAULT '0',
  `rubric_description` TEXT NOT NULL,
  `rubric_purpose` VARCHAR(20) CHARACTER SET ascii NOT NULL DEFAULT 'content',
  `rubric_meta_gen` ENUM('0','1') NOT NULL DEFAULT '0',
  `rubric_form_conditions` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `rubric_position` INT UNSIGNED NOT NULL DEFAULT 100,
  `rubric_changed` INT NOT NULL DEFAULT 0,
  `rubric_changed_fields` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`Id`),
  KEY `idx_rubric_position` (`rubric_position`),
  KEY `idx_rubric_purpose` (`rubric_purpose`),
  KEY `idx_rubric_alias` (`rubric_alias`(191))
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_rubric_fields_group` (
  `Id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rubric_id` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `group_title` VARCHAR(255) NOT NULL DEFAULT '',
  `group_description` TEXT NOT NULL,
  `group_position` INT NOT NULL DEFAULT 100,
  `group_settings` MEDIUMTEXT NULL,
  PRIMARY KEY (`Id`),
  KEY `idx_rubric_position` (`rubric_id`, `group_position`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_rubric_fields` (
  `Id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rubric_id` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `rubric_field_group` SMALLINT DEFAULT NULL,
  `rubric_field_alias` VARCHAR(20) NOT NULL DEFAULT '',
  `rubric_field_title` VARCHAR(255) NOT NULL DEFAULT '',
  `rubric_field_type` VARCHAR(75) NOT NULL DEFAULT '',
  `rubric_field_numeric` ENUM('0','1') NOT NULL DEFAULT '0',
  `rubric_field_position` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `rubric_field_default` TEXT NOT NULL,
  `rubric_field_settings` LONGTEXT DEFAULT NULL,
  `rubric_field_search` ENUM('0','1') NOT NULL DEFAULT '1',
  `rubric_field_template` TEXT NOT NULL,
  `rubric_field_template_request` TEXT NOT NULL,
  `rubric_field_description` TEXT NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `idx_rubric` (`rubric_id`, `rubric_field_position`),
  KEY `idx_group` (`rubric_field_group`),
  KEY `idx_alias` (`rubric_id`, `rubric_field_alias`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_rubric_schema_revisions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rubric_id` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `action` VARCHAR(32) CHARACTER SET ascii NOT NULL DEFAULT 'update',
  `snapshot_hash` CHAR(40) CHARACTER SET ascii NOT NULL DEFAULT '',
  `snapshot_json` LONGTEXT NOT NULL,
  `comment` VARCHAR(500) NOT NULL DEFAULT '',
  `author_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `author_name` VARCHAR(255) NOT NULL DEFAULT '',
  `source_revision_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_rubric_created` (`rubric_id`, `created_at`),
  KEY `idx_action` (`action`),
  KEY `idx_snapshot_hash` (`snapshot_hash`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_rubric_trash` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rubric_id` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `rubric_title` VARCHAR(255) NOT NULL DEFAULT '',
  `rubric_alias` VARCHAR(255) NOT NULL DEFAULT '',
  `fields_count` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `groups_count` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `payload_json` LONGTEXT NOT NULL,
  `author_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `author_name` VARCHAR(255) NOT NULL DEFAULT '',
  `deleted_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_deleted_at` (`deleted_at`),
  KEY `idx_rubric_id` (`rubric_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_rubric_field_sets` (
  `code` VARCHAR(64) CHARACTER SET ascii NOT NULL,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL,
  `definition_json` LONGTEXT NOT NULL,
  `fingerprint` CHAR(64) CHARACTER SET ascii NOT NULL DEFAULT '',
  `source` VARCHAR(32) CHARACTER SET ascii NOT NULL DEFAULT 'import',
  `active` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `updated_by` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`code`),
  KEY `idx_active_title` (`active`,`title`(120))
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_rubric_field_set_links` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rubric_id` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `set_code` VARCHAR(64) CHARACTER SET ascii NOT NULL,
  `applied_fingerprint` CHAR(64) CHARACTER SET ascii NOT NULL DEFAULT '',
  `applied_definition_json` LONGTEXT NOT NULL,
  `field_map_json` LONGTEXT NOT NULL,
  `updated_by` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_rubric_set` (`rubric_id`,`set_code`),
  KEY `idx_set_code` (`set_code`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_directories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(64) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `name` VARCHAR(190) NOT NULL,
  `description` TEXT NULL,
  `settings_json` TEXT NULL,
  `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_directory_code` (`code`),
  KEY `idx_directory_active_name` (`is_active`, `name`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_directory_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `directory_id` INT UNSIGNED NOT NULL,
  `item_key` VARCHAR(120) NOT NULL,
  `label` VARCHAR(255) NOT NULL,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  `parent_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `data_json` TEXT NULL,
  `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_directory_item_key` (`directory_id`, `item_key`(120)),
  KEY `idx_directory_item_order` (`directory_id`, `sort_order`, `id`),
  KEY `idx_directory_item_parent` (`directory_id`, `parent_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_rubric_templates` (
  `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rubric_id` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `template` LONGTEXT NOT NULL,
  `author_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `created` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_rubric` (`rubric_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_rubric_permissions` (
  `Id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rubric_id` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `user_group_id` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `rubric_permission` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`Id`),
  UNIQUE KEY `uniq_rubric_group` (`rubric_id`, `user_group_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_rubric_admin_views` (
  `rubric_id` SMALLINT UNSIGNED NOT NULL,
  `mode` VARCHAR(20) NOT NULL DEFAULT 'standard',
  `config_json` LONGTEXT NOT NULL,
  `updated_by` INT UNSIGNED DEFAULT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`rubric_id`),
  KEY `idx_updated_at` (`updated_at`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_documents` (
  `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rubric_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
  `rubric_tmpl_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
  `document_parent` INT UNSIGNED NOT NULL DEFAULT 0,
  `document_alias` VARCHAR(255) CHARACTER SET utf8 NOT NULL,
  `document_alias_header` SMALLINT NOT NULL DEFAULT 301,
  `document_alias_history` ENUM('0','1','2') NOT NULL DEFAULT '0',
  `document_short_alias` VARCHAR(10) NOT NULL DEFAULT '',
  `document_title` VARCHAR(255) NOT NULL,
  `document_breadcrumb_title` VARCHAR(255) NOT NULL DEFAULT '',
  `document_published` INT UNSIGNED NOT NULL DEFAULT 0,
  `document_expire` INT UNSIGNED NOT NULL DEFAULT 0,
  `document_changed` INT UNSIGNED NOT NULL DEFAULT 0,
  `document_version` INT UNSIGNED NOT NULL DEFAULT 1,
  `document_author_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT 1,
  `document_in_search` ENUM('1','0') NOT NULL DEFAULT '1',
  `document_is_technical` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `document_meta_keywords` TEXT NOT NULL,
  `document_meta_description` TEXT NOT NULL,
  `document_meta_robots` ENUM('index,follow','index,nofollow','noindex,nofollow') NOT NULL DEFAULT 'index,follow',
  `document_sitemap_freq` TINYINT NOT NULL DEFAULT 3,
  `document_sitemap_pr` FLOAT DEFAULT 0.5,
  `document_in_sitemap` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `document_status` ENUM('1','0') NOT NULL DEFAULT '1',
  `document_deleted` ENUM('0','1') NOT NULL DEFAULT '0',
  `document_count_print` INT UNSIGNED NOT NULL DEFAULT 0,
  `document_count_view` INT UNSIGNED NOT NULL DEFAULT 0,
  `document_linked_navi_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
  `document_excerpt` TEXT NOT NULL,
  `document_tags` TEXT NOT NULL,
  `document_property` TEXT NOT NULL,
  `document_position` INT NOT NULL DEFAULT 0,
  `module_catalog` LONGTEXT NOT NULL,
  `guid` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `document_alias` (`document_alias`),
	KEY `idx_short_alias` (`document_short_alias`),
  KEY `idx_rubric` (`rubric_id`),
  KEY `idx_status` (`document_status`),
  KEY `idx_published` (`document_published`),
  KEY `idx_expire` (`document_expire`),
  KEY `idx_public_visibility` (`document_is_technical`,`document_in_sitemap`),
  KEY `idx_request` (`Id`, `rubric_id`, `document_status`, `document_deleted`),
  KEY `idx_public_listing` (`rubric_id`, `document_status`, `document_deleted`, `document_published`, `Id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_document_alias_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `document_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `document_alias` VARCHAR(255) CHARACTER SET utf8 NOT NULL,
  `document_alias_header` SMALLINT NOT NULL DEFAULT 301,
  `document_alias_author` MEDIUMINT UNSIGNED NOT NULL DEFAULT 1,
  `document_alias_changed` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_alias` (`document_alias`),
  KEY `idx_document` (`document_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_document_fields` (
  `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rubric_field_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
  `document_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `field_number_value` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
  `field_value` VARCHAR(500) NOT NULL DEFAULT '',
  `document_in_search` ENUM('1','0') NOT NULL DEFAULT '1',
  PRIMARY KEY (`Id`),
  KEY `idx_document` (`document_id`),
  KEY `idx_field_search` (`rubric_field_id`, `document_in_search`),
  KEY `idx_field_value` (`field_value`(191)),
  KEY `idx_document_field` (`document_id`, `rubric_field_id`),
  KEY `idx_number` (`field_number_value`),
  KEY `idx_request` (`rubric_field_id`, `field_number_value`),
  KEY `idx_public_text` (`rubric_field_id`, `field_value`(100), `document_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_document_fields_text` (
  `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rubric_field_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
  `document_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `field_value` LONGTEXT NOT NULL,
	PRIMARY KEY (`Id`),
	KEY `idx_document` (`document_id`),
	KEY `idx_field` (`rubric_field_id`),
	KEY `idx_document_field` (`document_id`, `rubric_field_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_document_relation_edges` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_document_id` INT UNSIGNED NOT NULL,
  `source_field_id` MEDIUMINT UNSIGNED NOT NULL,
  `target_document_id` INT UNSIGNED NOT NULL,
  `relation_type` VARCHAR(64) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `position` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_relation_edge` (`source_document_id`,`source_field_id`,`target_document_id`),
  KEY `idx_relation_outgoing` (`source_document_id`,`source_field_id`,`position`),
  KEY `idx_relation_incoming` (`target_document_id`,`relation_type`,`source_document_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_document_creation_presets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rubric_id` MEDIUMINT UNSIGNED NOT NULL,
  `title` VARCHAR(191) NOT NULL,
  `description` TEXT NOT NULL,
  `document_json` MEDIUMTEXT NOT NULL,
  `fields_json` MEDIUMTEXT NOT NULL,
  `source_document_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_by` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_preset_rubric` (`rubric_id`,`is_active`,`sort_order`,`id`),
  KEY `idx_preset_source` (`source_document_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_document_keywords` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `document_id` INT UNSIGNED NOT NULL,
  `keyword` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_document` (`document_id`),
  KEY `idx_keyword` (`keyword`(191))
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_document_tags` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rubric_id` INT UNSIGNED NOT NULL,
  `document_id` INT UNSIGNED NOT NULL,
  `tag` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rubric` (`rubric_id`),
  KEY `idx_document` (`document_id`),
  KEY `idx_tag` (`tag`(191))
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_document_remarks` (
  `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `document_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `remark_first` ENUM('0','1') NOT NULL DEFAULT '0',
  `remark_title` VARCHAR(255) NOT NULL DEFAULT '',
  `remark_text` TEXT NOT NULL,
  `remark_author_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `remark_published` INT UNSIGNED NOT NULL DEFAULT 0,
  `remark_status` ENUM('1','0') NOT NULL DEFAULT '1',
  `remark_author_email` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`Id`),
  KEY `idx_document` (`document_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_document_rev` (
  `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `doc_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `doc_revision` INT UNSIGNED NOT NULL DEFAULT 0,
  `doc_data` LONGTEXT NOT NULL,
  `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`Id`),
  KEY `idx_document` (`doc_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_view_count` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `document_id` INT UNSIGNED NOT NULL,
  `day_id` INT UNSIGNED NOT NULL,
  `count` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_document_day` (`document_id`, `day_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

-- Requests, navigation and blocks ---------------------------------------------

CREATE TABLE IF NOT EXISTS `{{prefix}}_request` (
  `Id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rubric_id` SMALLINT UNSIGNED NOT NULL,
  `request_alias` VARCHAR(20) NOT NULL,
  `request_items_per_page` SMALLINT UNSIGNED NOT NULL DEFAULT 10,
  `request_title` VARCHAR(255) NOT NULL,
  `request_template_item` TEXT NOT NULL,
  `request_template_main` TEXT NOT NULL,
  `request_order_by` VARCHAR(255) NOT NULL,
  `request_order_by_nat` INT NOT NULL DEFAULT 0,
  `request_author_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `request_created` INT UNSIGNED NOT NULL DEFAULT 0,
  `request_description` TEXT NOT NULL,
  `request_result_contract` MEDIUMTEXT DEFAULT NULL,
  `request_preview_renderer` VARCHAR(64) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'data_cards',
  `request_executor_mode` VARCHAR(16) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'legacy',
  `request_native_plan` MEDIUMTEXT DEFAULT NULL,
  `request_native_verified_hash` CHAR(64) CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL,
  `request_native_audit_status` VARCHAR(24) CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL,
  `request_native_audit_details` MEDIUMTEXT DEFAULT NULL,
  `request_asc_desc` ENUM('ASC','DESC') NOT NULL DEFAULT 'DESC',
  `request_order_tiebreaker` VARCHAR(4) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT '',
  `request_sort_rules` MEDIUMTEXT DEFAULT NULL,
  `request_show_pagination` ENUM('0','1') NOT NULL DEFAULT '0',
  `request_pagination` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `request_use_query` ENUM('0','1') NOT NULL DEFAULT '0',
  `request_count_items` ENUM('0','1') NOT NULL DEFAULT '0',
  `request_where_cond` TEXT DEFAULT NULL,
  `request_hide_current` ENUM('0','1') NOT NULL DEFAULT '1',
  `request_only_owner` ENUM('0','1') NOT NULL DEFAULT '0',
  `request_cache_lifetime` INT NOT NULL DEFAULT 0,
  `request_cache_elements` ENUM('0','1') NOT NULL DEFAULT '0',
  `request_show_statistic` ENUM('0','1') NOT NULL DEFAULT '0',
  `request_external` ENUM('0','1') NOT NULL DEFAULT '0',
  `request_ajax` ENUM('0','1') NOT NULL DEFAULT '0',
  `request_show_sql` ENUM('0','1') NOT NULL DEFAULT '0',
  `request_changed` INT UNSIGNED NOT NULL DEFAULT 0,
  `request_changed_elements` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`Id`),
  KEY `idx_rubric` (`rubric_id`),
  KEY `idx_alias` (`request_alias`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_request_condition_groups` (
  `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` SMALLINT UNSIGNED NOT NULL,
  `parent_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `group_title` VARCHAR(100) NOT NULL DEFAULT '',
  `group_operator` ENUM('AND','OR') NOT NULL DEFAULT 'AND',
  `group_position` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`Id`),
  KEY `idx_request_parent` (`request_id`, `parent_id`, `group_position`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_request_conditions` (
  `Id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` SMALLINT UNSIGNED NOT NULL,
  `condition_group_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `condition_compare` VARCHAR(30) NOT NULL,
  `condition_field_id` INT NOT NULL,
  `condition_value` VARCHAR(1000) NOT NULL,
  `condition_value_source` VARCHAR(16) NOT NULL DEFAULT '',
  `condition_value_key` VARCHAR(64) NOT NULL DEFAULT '',
  `condition_value_config` TEXT NULL,
  `condition_join` ENUM('OR','AND') NOT NULL DEFAULT 'AND',
  `condition_position` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `condition_status` ENUM('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`Id`),
  KEY `idx_request` (`request_id`),
  KEY `idx_request_group_position` (`request_id`, `condition_group_id`, `condition_position`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_request_revisions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `action` VARCHAR(32) NOT NULL DEFAULT 'update',
  `snapshot_hash` CHAR(40) NOT NULL DEFAULT '',
  `snapshot_json` LONGTEXT NOT NULL,
  `comment` VARCHAR(255) NOT NULL DEFAULT '',
  `author_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `author_name` VARCHAR(255) NOT NULL DEFAULT '',
  `source_revision_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_request_created` (`request_id`,`created_at`),
  KEY `idx_snapshot_hash` (`snapshot_hash`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_presentations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(190) NOT NULL,
  `code` VARCHAR(80) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `kind` VARCHAR(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'card',
  `description` TEXT NULL,
  `draft_item_markup` LONGTEXT NOT NULL,
  `draft_wrapper_markup` LONGTEXT NOT NULL,
  `draft_empty_markup` LONGTEXT NOT NULL,
  `draft_css` LONGTEXT NOT NULL,
  `draft_settings_json` TEXT NULL,
  `published_item_markup` LONGTEXT NOT NULL,
  `published_wrapper_markup` LONGTEXT NOT NULL,
  `published_empty_markup` LONGTEXT NOT NULL,
  `published_css` LONGTEXT NOT NULL,
  `published_settings_json` TEXT NULL,
  `is_published` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  `version` INT UNSIGNED NOT NULL DEFAULT 0,
  `author_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` INT UNSIGNED NOT NULL DEFAULT 0,
  `published_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_content_presentation_code` (`code`),
  KEY `idx_content_presentation_kind` (`kind`, `is_published`),
  KEY `idx_content_presentation_updated` (`updated_at`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_presentation_revisions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `presentation_id` INT UNSIGNED NOT NULL,
  `action` VARCHAR(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `snapshot_hash` CHAR(40) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `snapshot_json` LONGTEXT NOT NULL,
  `comment` VARCHAR(255) NOT NULL DEFAULT '',
  `author_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `author_name` VARCHAR(190) NOT NULL DEFAULT '',
  `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_presentation_revision_owner` (`presentation_id`, `created_at`, `id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_presentation_assignments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `presentation_id` INT UNSIGNED NOT NULL,
  `context_code` VARCHAR(64) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `target_type` VARCHAR(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `target_key` VARCHAR(96) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT '0',
  `mode` VARCHAR(16) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'legacy',
  `settings_json` TEXT NULL,
  `author_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_presentation_assignment_target` (`context_code`, `target_type`, `target_key`),
  KEY `idx_presentation_assignment_owner` (`presentation_id`, `mode`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_navigation` (
  `navigation_id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `alias` VARCHAR(20) NOT NULL DEFAULT '',
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `level1` TEXT NOT NULL,
  `level2` TEXT NOT NULL,
  `level3` TEXT NOT NULL,
  `level1_active` TEXT NOT NULL,
  `level2_active` TEXT NOT NULL,
  `level3_active` TEXT NOT NULL,
  `level1_begin` TEXT NOT NULL,
  `level1_end` TEXT NOT NULL,
  `level2_begin` TEXT NOT NULL,
  `level2_end` TEXT NOT NULL,
  `level3_begin` TEXT NOT NULL,
  `level3_end` TEXT NOT NULL,
  `begin` TEXT NOT NULL,
  `end` TEXT NOT NULL,
  `user_group` TEXT NOT NULL,
  `expand_ext` ENUM('0','1','2') DEFAULT '1',
  PRIMARY KEY (`navigation_id`),
  KEY `idx_alias` (`alias`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_navigation_items` (
  `navigation_item_id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `navigation_id` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `document_id` INT DEFAULT NULL,
  `alias` VARCHAR(255) NOT NULL DEFAULT '',
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL,
  `target` ENUM('_blank','_self','_parent','_top') NOT NULL DEFAULT '_self',
  `image` VARCHAR(255) NOT NULL DEFAULT '',
  `css_style` VARCHAR(255) DEFAULT NULL,
  `css_id` VARCHAR(50) DEFAULT NULL,
  `css_class` VARCHAR(50) DEFAULT NULL,
  `parent_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
  `level` ENUM('1','2','3') NOT NULL DEFAULT '1',
  `position` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `status` ENUM('1','0') NOT NULL DEFAULT '1',
  PRIMARY KEY (`navigation_item_id`),
  KEY `idx_navigation_parent` (`navigation_id`, `parent_id`),
  KEY `idx_navigation_position` (`navigation_id`, `position`),
  KEY `idx_document` (`document_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_navigation_revisions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `navigation_id` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `action` VARCHAR(32) NOT NULL DEFAULT 'update',
  `snapshot_hash` CHAR(40) NOT NULL DEFAULT '',
  `snapshot_json` LONGTEXT NOT NULL,
  `comment` VARCHAR(255) NOT NULL DEFAULT '',
  `author_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `author_name` VARCHAR(255) NOT NULL DEFAULT '',
  `source_revision_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_navigation_created` (`navigation_id`,`created_at`),
  KEY `idx_snapshot_hash` (`snapshot_hash`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_sysblocks_groups` (
  `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `position` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_position` (`position`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_sysblocks` (
  `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sysblock_group_id` INT NOT NULL DEFAULT 0,
  `sysblock_name` VARCHAR(255) NOT NULL DEFAULT '',
  `sysblock_description` VARCHAR(255) DEFAULT NULL,
  `sysblock_alias` VARCHAR(20) NOT NULL DEFAULT '',
  `sysblock_text` LONGTEXT NOT NULL,
  `sysblock_active` ENUM('0','1') NOT NULL DEFAULT '1',
  `sysblock_eval` ENUM('0','1') NOT NULL DEFAULT '1',
  `sysblock_external` ENUM('0','1') NOT NULL DEFAULT '0',
  `sysblock_ajax` ENUM('0','1') NOT NULL DEFAULT '0',
  `sysblock_visual` ENUM('0','1') NOT NULL DEFAULT '0',
  `sysblock_editor` VARCHAR(16) NOT NULL DEFAULT 'php',
  `sysblock_author_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `sysblock_created` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_alias` (`sysblock_alias`),
  KEY `idx_group` (`sysblock_group_id`),
  KEY `idx_active` (`sysblock_active`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_sysblock_revisions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `block_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
  `action` VARCHAR(32) NOT NULL DEFAULT 'update',
  `snapshot_hash` CHAR(40) NOT NULL DEFAULT '',
  `text_hash` CHAR(40) NOT NULL DEFAULT '',
  `snapshot_json` LONGTEXT NOT NULL,
  `comment` VARCHAR(255) NOT NULL DEFAULT '',
  `author_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `author_name` VARCHAR(255) NOT NULL DEFAULT '',
  `source_revision_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_block_created` (`block_id`, `created_at`),
  KEY `idx_action` (`action`),
  KEY `idx_snapshot_hash` (`snapshot_hash`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

-- Universal catalog (product functionality belongs to installable modules) ----

CREATE TABLE IF NOT EXISTS `{{prefix}}_module_catalog_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rubric_id` INT NOT NULL DEFAULT 0,
  `field_id` INT NOT NULL DEFAULT 0,
  `name` VARCHAR(255) NOT NULL,
  `parent_id` INT NOT NULL DEFAULT 0,
  `status` INT NOT NULL DEFAULT 0,
  `document_id` INT DEFAULT NULL,
  `document_alias` VARCHAR(255) NOT NULL DEFAULT '',
  `navi_id` INT DEFAULT NULL,
  `level` INT NOT NULL DEFAULT 0,
  `fields_use` TEXT DEFAULT NULL,
  `filters_use` TEXT DEFAULT NULL,
  `filters_settings` TEXT DEFAULT NULL,
  `source_item_ids` TEXT DEFAULT NULL,
  `attribute_set_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `attributes_runtime` VARCHAR(20) NOT NULL DEFAULT 'legacy',
  `filter_runtime` VARCHAR(20) NOT NULL DEFAULT 'legacy',
  `position` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_parent` (`parent_id`),
  KEY `idx_catalog_runtime` (`attributes_runtime`, `filter_runtime`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}_module_catalog_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rubric_id` INT DEFAULT NULL,
  `field_id` INT DEFAULT NULL,
  `purpose` VARCHAR(20) NOT NULL DEFAULT 'content',
  `navi_id` INT DEFAULT NULL,
  `request_id` INT DEFAULT NULL,
  `doc_fileds` ENUM('0','1') NOT NULL DEFAULT '0',
  `recursive` ENUM('0','1') NOT NULL DEFAULT '1',
  `doc_parent` ENUM('0','1') NOT NULL DEFAULT '1',
  `item_parent` ENUM('0','1') NOT NULL DEFAULT '1',
  `sort_parent` ENUM('0','1') NOT NULL DEFAULT '1',
  `save_names` ENUM('0','1') NOT NULL DEFAULT '0',
  `filters_use` ENUM('0','1') NOT NULL DEFAULT '0',
  `filters_count` ENUM('0','1') NOT NULL DEFAULT '0',
  `rub_cat_id` INT DEFAULT NULL,
  `fields_default` LONGTEXT DEFAULT NULL,
  `filters_default` LONGTEXT DEFAULT NULL,
  `filters_default_settings` LONGTEXT DEFAULT NULL,
  `product_title_field_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `product_article_field_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `product_price_field_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `product_old_price_field_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `product_stock_field_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `product_images_field_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `product_card_settings` LONGTEXT DEFAULT NULL,
  `product_card_template_id` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_rubric` (`rubric_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
