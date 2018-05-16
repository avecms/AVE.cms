<?
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
?>