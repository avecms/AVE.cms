<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Requests/migrations/015_request_description_capacity.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Content\ContentTables;

	return function (array $context) {
		$table = ContentTables::table('request');
		$column = \DB::query('SHOW COLUMNS FROM %b LIKE %s', $table, 'request_description')->getAssoc();
		if (!$column || strtolower((string) $column['Type']) !== 'tinytext') { return 0; }
		\DB::query('ALTER TABLE %b MODIFY COLUMN `request_description` TEXT NOT NULL', $table);
		return 1;
	};
