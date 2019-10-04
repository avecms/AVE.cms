<?php
	/* -------------------------------------------------------------------------------------------------------------- */
	/* -------------------------------------------------------3.24-------------------------------------------------- */
	/* -------------------------------------------------------------------------------------------------------------- */


	$check = $AVE_DB->Query("
		SHOW COLUMNS
		FROM
			" . PREFIX . "_rubrics
		LIKE
			'rubric_changed'
	")->NumRows();

	$exist = ($check) ? true : false;

	if ($exist === false)
	{
		$AVE_DB->Real_Query("
			ALTER TABLE
				" . PREFIX . "_rubrics
			ADD
				`rubric_changed` int(10) NOT NULL DEFAULT '0'
			AFTER
				`rubric_position`
		");

		$AVE_DB->Real_Query("
			UPDATE
				" . PREFIX . "_rubrics
			SET
				`rubric_changed` = UNIX_TIMESTAMP()
		");
	}

	// ----------------------------------------------------------------------------------------

	$check = $AVE_DB->Query("
		SHOW COLUMNS
		FROM
			" . PREFIX . "_rubrics
		LIKE
			'rubric_changed_fields'
	")->NumRows();

	$exist = ($check) ? true : false;

	if ($exist === false)
	{
		$AVE_DB->Real_Query("
			ALTER TABLE
				" . PREFIX . "_rubrics
			ADD
				`rubric_changed_fields` int(10) NOT NULL DEFAULT '0'
			AFTER
				`rubric_changed`
		");

		$AVE_DB->Real_Query("
			UPDATE
				" . PREFIX . "_rubrics
			SET
				`rubric_changed_fields` = UNIX_TIMESTAMP()
		");
	}

	// ----------------------------------------------------------------------------------------

	$check = $AVE_DB->Query("
		SHOW TABLES
		LIKE
			'" . PREFIX . "_rubric_breadcrumb'
	")->NumRows();

	$exist = ($check) ? true : false;

	if ($exist === false)
	{
		$AVE_DB->Real_Query("
			CREATE TABLE `" . PREFIX . "_rubric_breadcrumb` (
				`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`rubric_id` smallint(3) unsigned NOT NULL,
				`box` varchar(500) NOT NULL DEFAULT '',
				`show_main` enum('1','0') NOT NULL DEFAULT '1',
				`show_host` enum('1','0') NOT NULL DEFAULT '1',
				`sepparator` varchar(255) NOT NULL,
				`sepparator_use` enum('1','0') NOT NULL DEFAULT '1',
				`link_box` varchar(500) NOT NULL DEFAULT '',
				`link_template` varchar(500) NOT NULL DEFAULT '',
				`self_box` varchar(500) NOT NULL DEFAULT '',
				`link_box_last` enum('1','0') NOT NULL DEFAULT '1',
				PRIMARY KEY (`id`),
				KEY `rubric_id` (`rubric_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=0;
		");
	}

	// ----------------------------------------------------------------------------------------

	$check = $AVE_DB->Query("
		SHOW COLUMNS
		FROM
			" . PREFIX . "_request
		LIKE
			'request_changed'
	")->NumRows();

	$exist = ($check) ? true : false;

	if ($exist === false)
	{
		$AVE_DB->Real_Query("
			ALTER TABLE
				" . PREFIX . "_request
			ADD
				`request_changed` int(10) unsigned NOT NULL DEFAULT '0'
			AFTER
				`request_show_sql`
		");

		$AVE_DB->Real_Query("
			UPDATE
				" . PREFIX . "_request
			SET
				`request_changed` = UNIX_TIMESTAMP()
		");
	}

	// ----------------------------------------------------------------------------------------

	$check = $AVE_DB->Query("
		SHOW COLUMNS
		FROM
			" . PREFIX . "_request
		LIKE
			'request_changed_elements'
	")->NumRows();

	$exist = ($check) ? true : false;

	if ($exist === false)
	{
		$AVE_DB->Real_Query("
			ALTER TABLE
				" . PREFIX . "_request
			ADD
				`request_changed_elements` int(10) unsigned NOT NULL DEFAULT '0'
			AFTER
				`request_changed`
		");

		$AVE_DB->Real_Query("
			UPDATE
				" . PREFIX . "_request
			SET
				`request_changed_elements` = UNIX_TIMESTAMP()
		");
	}

	// ----------------------------------------------------------------------------------------

	$check = $AVE_DB->Query("
		SHOW COLUMNS
		FROM
			" . PREFIX . "_documents
		LIKE
			'document_short_alias'
	")->NumRows();

	$exist = ($check) ? true : false;

	if ($exist === false)
	{
		$AVE_DB->Real_Query("
			ALTER TABLE
				" . PREFIX . "_documents
			ADD
				`document_short_alias` VARCHAR(10) NOT NULL DEFAULT ''
			AFTER
				`document_alias_history`
		");
	}

	// ----------------------------------------------------------------------------------------

	$check = $AVE_DB->Query("
		SHOW COLUMNS
		FROM
			" . PREFIX . "_document_tags
		LIKE
			'rubric_id'
	")->NumRows();

	$exist = ($check) ? true : false;

	if ($exist === false)
	{
		$AVE_DB->Real_Query("
			ALTER TABLE
				" . PREFIX . "_document_tags
			ADD
				`rubric_id` int(3) NOT NULL
			AFTER
				`id`
		");
	}

	// ----------------------------------------------------------------------------------------

	$check = $AVE_DB->Query("
		SHOW COLUMNS
		FROM
			" . PREFIX . "_modules_aliases
		LIKE
			'document_id'
	")->NumRows();

	$exist = ($check) ? true : false;

	if ($exist === false)
	{
		$AVE_DB->Real_Query("
			ALTER TABLE
				" . PREFIX . "_modules_aliases
			ADD
				`document_id` int(10) NOT NULL DEFAULT '0'
			AFTER
				`id`
		");
	}

	// ----------------------------------------------------------------------------------------

	$check = $AVE_DB->Query("
		SHOW COLUMNS
		FROM
			" . PREFIX . "_request
		LIKE
			'request_count_items'
	")->NumRows();

	$exist = ($check) ? true : false;

	if ($exist === false)
	{
		$AVE_DB->Real_Query("
			ALTER TABLE
				" . PREFIX . "_request
			ADD
				`request_count_items` enum('0','1') NOT NULL DEFAULT '0'
			AFTER
				`request_use_query`
		");

		$AVE_DB->Real_Query("
			UPDATE
				" . PREFIX . "_request
			SET
				`request_changed_elements` = UNIX_TIMESTAMP()
		");
	}


	/* -------------------------------------------------------------------------------------------------------------- */
	/* -------------------------------------------------------3.25---------------------------------------------------- */
	/* -------------------------------------------------------------------------------------------------------------- */


	$check = $AVE_DB->Query("
		SELECT COUNT(1)
		FROM INFORMATION_SCHEMA.STATISTICS
		WHERE
			TABLE_SCHEMA = DATABASE()
		AND TABLE_NAME = '" . PREFIX . "_document_fields'
		AND INDEX_NAME = 'queries';
	")->GetCell();

	$exist = ($check > 0) ? true : false;

	if ($exist === false)
	{
		$AVE_DB->Real_Query("
			CREATE INDEX queries ON " . PREFIX . "_document_fields(document_id, rubric_field_id)
		");
	}

	// ----------------------------------------------------------------------------------------

	$check = $AVE_DB->Query("
		SELECT 1
		FROM INFORMATION_SCHEMA.COLUMNS
		WHERE
			TABLE_SCHEMA = DATABASE()
		AND TABLE_NAME = '" . PREFIX . "_settings'
		AND COLUMN_NAME = 'use_editor';
	")->GetCell();

	$exist = ($check) ? true : false;

	if ($exist === true)
	{
		$AVE_DB->Real_Query("
			ALTER TABLE " . PREFIX . "_settings
			DROP use_editor;
		");
	}

	// ----------------------------------------------------------------------------------------

	$check = $AVE_DB->Query("
		SELECT COUNT(1)
		FROM INFORMATION_SCHEMA.TABLES
		WHERE
			TABLE_SCHEMA = DATABASE()
		AND TABLE_NAME = '" . PREFIX . "_rubric_template_cache';
	")->GetCell();

	$exist = ($check > 0) ? true : false;

	if ($exist === true)
	{
		$AVE_DB->Real_Query("
			DROP TABLE " . PREFIX . "_rubric_template_cache;
		");
	}

	// ----------------------------------------------------------------------------------------

	$check = $AVE_DB->Query("
		SHOW COLUMNS
		FROM
			" . PREFIX . "_sysblocks
		LIKE
			'sysblock_eval'
	")->NumRows();

	$exist = ($check) ? true : false;

	if ($exist === false)
	{
		$AVE_DB->Real_Query("
			ALTER TABLE
				" . PREFIX . "_sysblocks
			ADD
				`sysblock_eval` enum('0','1') NOT NULL DEFAULT '1'
			AFTER
				`sysblock_active`
		");
	}

	// ----------------------------------------------------------------------------------------

	$check = $AVE_DB->Query("
		SHOW TABLES
		LIKE
			'" . PREFIX . "_sysblocks_groups'
	")->NumRows();
	
	$exist = ($check > 0) ? true : false;

	if ($exist === false)
	{
		$AVE_DB->Real_Query("
			CREATE TABLE `" . PREFIX . "_sysblocks_groups` (
				`id` mediumint(5) unsigned NOT NULL AUTO_INCREMENT,
				`position` smallint(3) unsigned NOT NULL,
				`title` varchar(255) NOT NULL DEFAULT '',
				`description` text NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=0;
		");
	}

	// ----------------------------------------------------------------------------------------

	$check = $AVE_DB->Query("
		SHOW COLUMNS
		FROM
			" . PREFIX . "_sysblocks
		LIKE
			'sysblock_group_id'
	")->NumRows();

	$exist = ($check) ? true : false;

	if ($exist === false)
	{
		$AVE_DB->Real_Query("
			ALTER TABLE
				" . PREFIX . "_sysblocks
			ADD
				`sysblock_group_id` int(3) NOT NULL DEFAULT '0'
			AFTER
				`id`
		");
	}

	// ----------------------------------------------------------------------------------------

	$check = $AVE_DB->Query("
		SHOW COLUMNS
		FROM
			" . PREFIX . "_documents
		LIKE
			'document_position'
	")->NumRows();

	$exist = ($check) ? true : false;

	if ($exist === false)
	{
		$AVE_DB->Real_Query("
			ALTER TABLE
				" . PREFIX . "_documents
			ADD
				`document_position` int(10) NOT NULL DEFAULT '0'
			AFTER
				`document_property`
		");
	}

	// ----------------------------------------------------------------------------------------

	$check = $AVE_DB->Query("
		SHOW COLUMNS
		FROM
			" . PREFIX . "_document_alias_history
		LIKE
			'document_alias_header'
	")->NumRows();

	$exist = ($check) ? true : false;

	if ($exist === false)
	{
		$AVE_DB->Real_Query("
			ALTER TABLE
				" . PREFIX . "_documents
			ADD
				`document_alias_header` int(3) NOT NULL DEFAULT '301'
			AFTER
				`document_alias`
		");
	}
?>