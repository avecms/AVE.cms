<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Documents/Model.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Adminx\Documents;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use DB;
	use App\Helpers\Str;
	use App\Content\Fields\FieldValidator;
	use App\Content\Fields\FieldConditionEvaluator;
	use App\Content\Fields\FieldValueCodec;
	use App\Content\Fields\FieldContext;
	use App\Content\Fields\HtmlSanitizer;
	use App\Content\Fields\FieldRegistry;
	use App\Content\Fields\FieldLifecycle;
	use App\Content\Fields\FieldSettings;
	use App\Content\ContentTables;
	use App\Content\DocumentTerms;
	use App\Content\Documents\ContentCacheInvalidator;
	use App\Content\Documents\DocumentAliasRegistry;
	use App\Content\Documents\DocumentAliasTemplate;
	use App\Content\Documents\DocumentMediaDraft;
	use App\Content\Documents\DocumentMediaPath;
	use App\Content\Documents\DocumentSearch;
	use App\Common\Settings;
	use App\Common\SystemTables;
	use App\Common\Lifecycle;
	use App\Common\DatabaseSchema;
	use App\Adminx\Support\AdminLocale;
	use App\Adminx\Rubrics\FieldAdminEditors;
	use App\Adminx\Catalog\Model as CatalogModel;

	class Model
	{
		public static function documentsTable() { return ContentTables::table('documents'); }
		public static function rubricsTable() { return ContentTables::table('rubrics'); }
		public static function fieldsTable() { return ContentTables::table('rubric_fields'); }
		public static function groupsTable() { return ContentTables::table('rubric_fields_group'); }
		public static function docFieldsTable() { return ContentTables::table('document_fields'); }
		public static function docFieldsTextTable() { return ContentTables::table('document_fields_text'); }
		public static function revisionsTable() { return ContentTables::table('document_rev'); }
		public static function aliasHistoryTable() { return ContentTables::table('document_alias_history'); }
		public static function keywordsTable() { return ContentTables::table('document_keywords'); }
		public static function tagsTable() { return ContentTables::table('document_tags'); }
		public static function remarksTable() { return ContentTables::table('document_remarks'); }
		public static function viewCountTable() { return ContentTables::table('view_count'); }

		public static function items(array $filters)
		{
			$page = max(1, isset($filters['page']) ? (int) $filters['page'] : 1);
			$allowedPerPage = array(25, 50, 100, 300);
			$perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 25;
			$limit = in_array($perPage, $allowedPerPage, true) ? $perPage : 25;
			$offset = ($page - 1) * $limit;
			$where = ' WHERE 1=1';
			$args = array();
			$score = '0';
			$scoreArgs = array();

			$rubricId = isset($filters['rubric_id']) ? (int) $filters['rubric_id'] : 0;
			$q = trim(isset($filters['q']) ? (string) $filters['q'] : '');
			$field = self::normalizeSearchField(isset($filters['field']) ? (string) $filters['field'] : '', $rubricId);
			if ($q !== '') {
				if ($field !== '') {
					$criteria = DocumentSearch::valueCriteria($q, array('df.field_value', 'dft.field_value'));
					// Поиск по значению выбранного поля рубрики (напр. «Артикул»).
					$where .= ' AND EXISTS (SELECT 1 FROM ' . self::docFieldsTable() . ' df'
						. ' INNER JOIN ' . self::fieldsTable() . ' rf ON rf.Id = df.rubric_field_id'
						. ' LEFT JOIN ' . self::docFieldsTextTable() . ' dft ON dft.document_id = df.document_id AND dft.rubric_field_id = df.rubric_field_id'
						. ' WHERE df.document_id = d.Id AND rf.rubric_field_title = %s'
						. ' AND ' . $criteria['where'] . ')';
					$args[] = $field;
					$args = array_merge($args, $criteria['where_args']);
				} else {
					$criteria = DocumentSearch::criteria($q, 'd');
					$where .= ' AND ' . $criteria['where'];
					$args = array_merge($args, $criteria['where_args']);
					$score = $criteria['score'];
					$scoreArgs = $criteria['score_args'];
				}
			}

			if ($rubricId > 0) {
				$where .= ' AND d.rubric_id = %i';
				$args[] = $rubricId;
			}

			$state = isset($filters['state']) ? (string) $filters['state'] : '';
			if ($state === 'active') {
				$where .= " AND d.document_status = '1' AND d.document_deleted != '1'";
			} elseif ($state === 'draft') {
				$where .= " AND d.document_status = '0' AND d.document_deleted != '1'";
			} elseif ($state === 'deleted') {
				$where .= " AND d.document_deleted = '1'";
			} else {
				$where .= " AND d.document_deleted != '1'";
			}

			$countArgs = array_merge(array('SELECT COUNT(*) FROM ' . self::documentsTable() . ' d' . $where), $args);
			$total = (int) call_user_func_array(array('DB', 'query'), $countArgs)->getValue();

			$sql = 'SELECT d.*, r.rubric_title, r.rubric_alias,COALESCE(r.rubric_is_technical,0) rubric_is_technical,(' . $score . ') AS search_relevance'
				. ' FROM ' . self::documentsTable() . ' d'
				. ' LEFT JOIN ' . self::rubricsTable() . ' r ON r.Id = d.rubric_id'
				. $where
				. ' ORDER BY search_relevance DESC,d.document_changed DESC,d.Id DESC'
				. ' LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
			$rows = call_user_func_array(
				array('DB', 'query'),
				array_merge(array($sql), $scoreArgs, $args)
			)->getAll();

			$items = array();
			foreach ($rows as $row) {
				$items[] = self::row($row);
			}

			return array(
				'items' => $items,
				'pagination' => array(
					'page' => $page,
					'limit' => $limit,
					'total' => $total,
					'pages' => max(1, (int) ceil($total / $limit)),
					'from' => $total > 0 ? $offset + 1 : 0,
					'to' => min($total, $offset + count($items)),
				),
			);
		}

		/**
		 * Поля рубрик (по названию) для выбора «искать по полю». Если задана
		 * рубрика — только её поля (у каждой рубрики свой набор), иначе все.
		 * Идентифицируем по названию, т.к. alias есть не у всех полей.
		 */
		public static function searchableFields($rubricId = 0)
		{
			$rubricId = (int) $rubricId;
			$sql = 'SELECT rubric_field_title AS title'
				. ' FROM ' . self::fieldsTable()
				. " WHERE TRIM(rubric_field_title) <> ''";
			$args = array();
			if ($rubricId > 0) {
				$sql .= ' AND rubric_id = %i';
				$args[] = $rubricId;
			}

			$sql .= ' GROUP BY rubric_field_title ORDER BY rubric_field_title ASC';
			$rows = call_user_func_array(array('DB', 'query'), array_merge(array($sql), $args))->getAll();

			$fields = array();
			foreach ($rows as $row) {
				$title = trim((string) $row['title']);
				if ($title === '') { continue; }
				$fields[] = array('value' => $title, 'title' => $title);
			}

			return $fields;
		}

		/** Валидирует выбранное поле поиска по списку доступных полей рубрики. */
		public static function normalizeSearchField($field, $rubricId = 0)
		{
			$field = trim((string) $field);
			if ($field === '') {
				return '';
			}

			foreach (self::searchableFields($rubricId) as $item) {
				if ($item['value'] === $field) {
					return $field;
				}
			}

			return '';
		}

		public static function one($id)
		{
			$row = DB::query(
					'SELECT d.*, r.rubric_title, r.rubric_alias,COALESCE(r.rubric_is_technical,0) rubric_is_technical FROM ' . self::documentsTable() . ' d'
				. ' LEFT JOIN ' . self::rubricsTable() . ' r ON r.Id = d.rubric_id'
				. ' WHERE d.Id = %i LIMIT 1',
				(int) $id
			)->getAssoc();
			return $row ? self::row($row, true) : null;
		}

		public static function blank($rubricId = 0)
		{
			$rubricId = self::blankRubricId((int) $rubricId);
			$rubric = $rubricId > 0 ? self::rubric($rubricId) : null;
			return array(
				'Id' => 0,
				'rubric_id' => $rubricId,
				'document_title' => '',
				'document_alias' => '',
				'document_alias_header' => 301,
				'document_alias_history' => 0,
				'document_short_alias' => '',
				'document_breadcrumb_title' => '',
				'document_excerpt' => '',
				'document_meta_keywords' => '',
				'document_meta_description' => '',
				'document_meta_robots' => '',
				'document_sitemap_freq' => 3,
				'document_sitemap_pr' => 0.5,
				'document_tags' => '',
				'document_property' => '',
				'guid' => '',
				'document_status' => 1,
				'document_in_search' => 1,
				'document_is_technical' => $rubric && !empty($rubric['rubric_is_technical']) ? 1 : 0,
				'document_in_sitemap' => 1,
				'document_author_id' => 1,
				'document_parent' => 0,
				'rubric_tmpl_id' => 0,
				'document_linked_navi_id' => 0,
				'document_position' => 0,
				'document_deleted' => 0,
				'document_version' => 1,
				'parent_info' => null,
				'published_input' => date('Y-m-d\\TH:i'),
				'expire_input' => '',
				'changed_label' => 'ещё не сохранён',
				'published_label' => 'после сохранения',
				'state_label' => 'Новый',
				'state_badge' => 'blue',
				'rubric_title' => $rubric ? self::decode($rubric['rubric_title']) : '',
				'fields_count' => 0,
			);
		}

		public static function blankRubricId($preferred = 0)
		{
			if ((int) $preferred > 0 && self::rubric((int) $preferred)) {
				return (int) $preferred;
			}

			$rubrics = self::rubrics();
			return !empty($rubrics) ? (int) $rubrics[0]['Id'] : 0;
		}

		public static function fieldsForDocument($documentId, $mediaDraftToken = '')
		{
			$doc = self::one((int) $documentId);
			if (!$doc) {
				return array();
			}

			return self::fieldsForRubric((int) $doc['rubric_id'], (int) $documentId, $mediaDraftToken);
		}

		public static function fieldsForRubric($rubricId, $documentId = 0, $mediaDraftToken = '', array $initialValues = array())
		{
			$conditionsEnabled = self::rubricConditionsEnabled((int) $rubricId);
			$groupSettings = $conditionsEnabled && self::hasGroupSettingsColumn() ? ', g.group_settings' : ', NULL AS group_settings';
			$rows = DB::query(
				'SELECT f.*, g.group_title, g.group_description, g.group_position' . $groupSettings . ','
				. ' df.Id AS document_field_id, df.field_value, df.field_number_value, df.document_in_search AS field_in_search,'
				. ' dft.field_value AS field_value_more'
				. ' FROM ' . self::fieldsTable() . ' f'
				. ' LEFT JOIN ' . self::groupsTable() . ' g ON g.Id = f.rubric_field_group'
				. ' LEFT JOIN ' . self::docFieldsTable() . ' df ON df.rubric_field_id = f.Id AND df.document_id = %i'
				. ' LEFT JOIN ' . self::docFieldsTextTable() . ' dft ON dft.rubric_field_id = f.Id AND dft.document_id = %i'
				. ' WHERE f.rubric_id = %i'
				. ' ORDER BY COALESCE(g.group_position, 999999) ASC, f.rubric_field_position ASC, f.Id ASC',
				(int) $documentId,
				(int) $documentId,
				(int) $rubricId
			)->getAll();

			if ((int) $documentId <= 0 && $initialValues) {
				foreach ($rows as &$initialRow) {
					$alias = (string) $initialRow['rubric_field_alias'];
					if (array_key_exists($alias, $initialValues) && (is_scalar($initialValues[$alias]) || $initialValues[$alias] === null)) {
						$initialRow['__initial_value'] = (string) $initialValues[$alias];
					}
				}

				unset($initialRow);
			}

			$catalogAllowed = array();
			foreach ($rows as $catalogRow) {
				$catalogSettings = FieldSettings::effective($catalogRow);
				if (array_key_exists('placed', $catalogSettings) && empty($catalogSettings['placed'])) { continue; }
				if ((string) $catalogRow['rubric_field_type'] !== 'catalog') { continue; }
				$catalogRaw = array_key_exists('__initial_value', $catalogRow)
					? (string) $catalogRow['__initial_value']
					: ($catalogRow['document_field_id'] ? (string) $catalogRow['field_value'] . (string) $catalogRow['field_value_more'] : (string) $catalogRow['rubric_field_default']);
				$selected = CatalogModel::parseValue($catalogRaw);
				$allowed = CatalogModel::allowedFieldIds((int) $rubricId, (int) $catalogRow['Id'], $selected['catalog_ids']);
				if (!empty($allowed)) { $catalogAllowed = array_values(array_unique(array_merge($catalogAllowed, $allowed))); }
			}

			$conditionReferences = $conditionsEnabled ? self::conditionReferences($rows) : array();
			$conditionSources = array();
			$groups = array();
			foreach ($rows as $row) {
				$settings = FieldSettings::effective($row);
				if (array_key_exists('placed', $settings) && empty($settings['placed'])) {
					$idReference = 'id:' . (int) $row['Id'];
					$aliasReference = 'alias:' . strtolower(trim((string) $row['rubric_field_alias']));
					if (isset($conditionReferences[$idReference]) || isset($conditionReferences[$aliasReference])) {
						$conditionSources[(int) $row['Id']] = self::conditionSourceRow($row);
					}

					continue;
				}

				$groupId = (int) $row['rubric_field_group'];
				if (!isset($groups[$groupId])) {
					$groups[$groupId] = array(
						'id' => $groupId,
						'title' => $groupId > 0 && !empty($row['group_title']) ? self::decode($row['group_title']) : 'Без группы',
						'description' => $groupId > 0 && !empty($row['group_description']) ? self::decode($row['group_description']) : '',
						'condition' => $conditionsEnabled && $groupId > 0 ? FieldConditionEvaluator::groupCondition($row) : array(),
						'items' => array(),
					);
				}

				$field = self::fieldRow($row, (int) $documentId, $mediaDraftToken, $conditionsEnabled);
				$field['catalog_hidden'] = !empty($catalogAllowed) && !in_array((int) $row['Id'], $catalogAllowed, true);
				$groups[$groupId]['items'][] = $field;
			}

			$groups = array_values($groups);
			if (!empty($groups) && !empty($conditionSources)) {
				$groups[0]['condition_sources'] = array_values($conditionSources);
			}

			return $groups;
		}

		/**
		 * Валидация значений полей документа по JSON-настройкам (rubric_field_settings.rules).
		 * Возвращает ошибки в формате ['fields[<Id>]' => сообщение]. Пусто, если правил нет.
		 */
		public static function validateFieldValues($rubricId, array $values, $documentId = 0, $fieldGroups = null)
		{
			$rubricId = (int) $rubricId;
			if ($rubricId <= 0) {
				return array();
			}

			$fieldGroups = self::resolveFieldGroups($rubricId, (int) $documentId, $fieldGroups);
			$fields = self::conditionAwareFields($fieldGroups);
			$effectiveValues = array();
			foreach ($fields as $field) {
				$effectiveValues[(int) $field['Id']] = isset($field['field_value']) ? $field['field_value'] : '';
			}

			foreach ($values as $id => $value) {
				$effectiveValues[(int) $id] = $value;
			}

			return FieldValidator::validateValues($fields, $effectiveValues, self::rubricConditionsEnabled($rubricId));
		}

		public static function computedFieldValues($rubricId, array $values, array $document = array(), $documentId = 0, $fieldGroups = null)
		{
			$fieldGroups = self::resolveFieldGroups((int) $rubricId, (int) $documentId, $fieldGroups);
			$fields = self::conditionAwareFields($fieldGroups);
			$definitions = array();
			$effectiveValues = array();
			foreach ($fields as $field) {
				$fieldId = (int) $field['Id'];
				$definitions[$fieldId] = $field;
				$effectiveValues[$fieldId] = isset($field['field_value']) ? $field['field_value'] : '';
			}

			foreach ($values as $fieldId => $value) {
				$effectiveValues[(int) $fieldId] = $value;
			}

			$computedValues = \App\Content\Fields\ComputedFieldEvaluator::apply($definitions, $effectiveValues, $document);
			foreach ($definitions as $fieldId => $field) {
				if ((string) $field['rubric_field_type'] === 'computed' && array_key_exists($fieldId, $computedValues)) {
					$values[$fieldId] = $computedValues[$fieldId];
				}
			}

			return $values;
		}

		/** Remove values of fields hidden or locked by rubric form conditions. */
		public static function conditionalFieldValues($rubricId, array $values, $documentId = 0, $fieldGroups = null)
		{
			if (!self::rubricConditionsEnabled((int) $rubricId)) {
				return $values;
			}

			$fieldGroups = self::resolveFieldGroups((int) $rubricId, (int) $documentId, $fieldGroups);
			$fields = self::conditionAwareFields($fieldGroups);
			$fieldMap = array();
			$storedValues = array();
			$effectiveValues = array();
			foreach ($fields as $field) {
					$fieldId = (int) $field['Id'];
					if (empty($field['_condition_source'])) {
					$fieldMap[$fieldId] = $field;
					}

					$storedValues[$fieldId] = (int) $documentId > 0 && isset($field['field_value']) ? $field['field_value'] : '';
					$effectiveValues[$fieldId] = isset($field['field_value']) ? $field['field_value'] : '';
			}

			foreach ($values as $id => $value) {
				$effectiveValues[(int) $id] = $value;
			}

			$actions = FieldConditionEvaluator::valueActions($fields, $storedValues, $effectiveValues, (int) $documentId <= 0);
			foreach ($values as $fieldId => $value) {
				if (is_array($value) && !empty($value['_condition_action_override'])) {
					unset($actions[(int) $fieldId]);
				}
			}

			foreach ($actions as $fieldId => $action) {
				if (!isset($fieldMap[$fieldId])) { continue; }
				$values[$fieldId] = self::conditionActionInput($fieldMap[$fieldId], $action);
				$effectiveValues[$fieldId] = $values[$fieldId];
			}

			$states = FieldConditionEvaluator::states($fields, $effectiveValues);
			foreach ($states as $id => $state) {
				if ((empty($state['visible']) || !empty($state['locked'])) && !isset($actions[$id])) {
					unset($values[$id], $values[(string) $id]);
				}
			}

			return $values;
		}

		protected static function conditionActionInput(array $field, array $action)
		{
			$value = isset($action['action']) && (string) $action['action'] === 'set'
				? (string) (isset($action['value']) ? $action['value'] : '') : '';
			$kind = isset($field['editor_kind']) ? (string) $field['editor_kind'] : 'text';
			if ($kind === 'boolean') { return array('checked' => in_array(strtolower($value), array('1', 'true', 'yes', 'on'), true)); }
			if ($kind === 'date') { return array('date' => $value); }
			if ($kind === 'datetime') { return array('datetime' => $value); }
			if ($kind === 'number') { return array('number' => $value); }
			if ($kind === 'choice') { return array('selected' => $value); }

			return array('raw' => $value);
		}

		public static function saveFields($documentId, array $values, $fieldGroups = null)
		{
			$documentId = (int) $documentId;
			$doc = self::one($documentId);
			if (!$doc) {
				throw new \RuntimeException('Документ не найден');
			}

			$fields = self::resolveFieldGroups((int) $doc['rubric_id'], $documentId, $fieldGroups);
			$values = self::conditionalFieldValues((int) $doc['rubric_id'], $values, $documentId, $fields);
			$values = self::computedFieldValues((int) $doc['rubric_id'], $values, $doc, $documentId, $fields);
			$catalogAllowed = array();
			$catalogFields = array();
			foreach ($fields as $group) {
				foreach ($group['items'] as $field) {
					$fieldId = (int) $field['Id'];
					if ((string) $field['rubric_field_type'] !== 'catalog' || !array_key_exists($fieldId, $values)) { continue; }
					// A partial/legacy payload may contain the field container without
					// catalog_ids. Treat it as absent instead of erasing existing links.
					if (!is_array($values[$fieldId]) || !array_key_exists('catalog_ids', $values[$fieldId])) { continue; }
					$selected = CatalogModel::selection((int) $doc['rubric_id'], $fieldId, isset($values[$fieldId]['catalog_ids']) ? $values[$fieldId]['catalog_ids'] : array());
					$raw = CatalogModel::serializeSelection((int) $doc['rubric_id'], $fieldId, $selected);
					self::saveFieldValue($documentId, $field, $raw, (int) $doc['document_in_search'] && (int) $field['rubric_field_search']);
					CatalogModel::afterDocumentSave($documentId, (int) $doc['rubric_id'], $fieldId, $selected);
					$allowed = CatalogModel::allowedFieldIds((int) $doc['rubric_id'], $fieldId, $selected);
					if (!empty($allowed)) { $catalogAllowed = array_values(array_unique(array_merge($catalogAllowed, $allowed))); }
					$catalogFields[$fieldId] = true;
				}
			}

			foreach ($fields as $group) {
				foreach ($group['items'] as $field) {
					$fieldId = (int) $field['Id'];
					if (isset($catalogFields[$fieldId])) { continue; }
					if (!empty($catalogAllowed) && !in_array($fieldId, $catalogAllowed, true)) { continue; }
					if (!array_key_exists($fieldId, $values)) {
						continue;
					}

					$raw = self::fieldInputValue($field, $values[$fieldId]);
					self::saveFieldValue($documentId, $field, $raw, (int) $doc['document_in_search'] && (int) $field['rubric_field_search']);
				}
			}

			self::clearDocumentCache($documentId);
			CatalogModel::reindexDocument($documentId);
			self::buildDocumentSnapshot($documentId);
		}

		public static function currentFieldValues($documentId)
		{
			$rows = DB::query(
				'SELECT df.rubric_field_id, df.field_value, dft.field_value AS field_value_more'
				. ' FROM ' . self::docFieldsTable() . ' df'
				. ' LEFT JOIN ' . self::docFieldsTextTable() . ' dft ON dft.rubric_field_id = df.rubric_field_id AND dft.document_id = df.document_id'
				. ' WHERE df.document_id = %i ORDER BY df.rubric_field_id ASC',
				(int) $documentId
			)->getAll();
			$out = array();
			foreach ($rows as $row) {
				$out[(int) $row['rubric_field_id']] = (string) $row['field_value'] . (string) $row['field_value_more'];
			}

			return $out;
		}

		/** Значения полей для управляемых пакетных операций: JSON уже декодирован. */
		public static function runtimeFieldValues($documentId)
		{
			$values = self::currentFieldValues((int) $documentId);
			foreach ($values as $fieldId => $value) {
				$decoded = FieldValueCodec::decodeStructured($value, null);
				$values[$fieldId] = is_array($decoded) ? $decoded : $value;
			}

			return $values;
		}

		/** Сохранить runtime-значения через те же native field-типы, что и редактор. */
		public static function saveRuntimeFieldValues($documentId, array $values)
		{
			$documentId = (int) $documentId;
			$doc = self::one($documentId);
			if (!$doc) {
				throw new \RuntimeException('Документ не найден');
			}

			$fieldGroups = self::fieldsForRubric((int) $doc['rubric_id'], $documentId);
			$values = self::conditionalFieldValues((int) $doc['rubric_id'], $values, $documentId, $fieldGroups);
			$values = self::computedFieldValues((int) $doc['rubric_id'], $values, $doc, $documentId, $fieldGroups);
			$errors = self::validateFieldValues((int) $doc['rubric_id'], $values, $documentId, $fieldGroups);
			if ($errors) {
				throw new \RuntimeException('Поля документа не прошли проверку: ' . implode('; ', array_values($errors)));
			}

			$fieldMap = array();
			foreach ($fieldGroups as $group) {
				foreach ($group['items'] as $field) {
					$fieldMap[(int) $field['Id']] = $field;
				}
			}

			foreach ($values as $fieldId => $value) {
				$fieldId = (int) $fieldId;
				if (!isset($fieldMap[$fieldId])) {
					continue;
				}

				$field = $fieldMap[$fieldId];
				self::saveFieldValue(
					$documentId,
					$field,
					$value,
					(int) $doc['document_in_search'] && (int) $field['rubric_field_search']
				);
			}

			self::touchDocument($documentId);
			self::clearDocumentCache($documentId);
			CatalogModel::reindexDocument($documentId);
			self::buildDocumentSnapshot($documentId);
		}

		public static function restoreFieldValues($documentId, array $values)
		{
			$documentId = (int) $documentId;
			$doc = self::one($documentId);
			if (!$doc) {
				throw new \RuntimeException('Документ не найден');
			}

			$fieldMap = array();
			foreach (self::fieldsForRubric((int) $doc['rubric_id'], $documentId) as $group) {
				foreach ($group['items'] as $field) {
					$fieldMap[(int) $field['Id']] = $field;
				}
			}

			foreach ($values as $fieldId => $value) {
				$fieldId = (int) $fieldId;
				if (!isset($fieldMap[$fieldId])) {
					continue;
				}

				self::saveFieldValue(
					$documentId,
					$fieldMap[$fieldId],
					(string) $value,
					(int) $doc['document_in_search'] && (int) $fieldMap[$fieldId]['rubric_field_search']
				);
			}

			self::touchDocument($documentId);
			self::clearDocumentCache($documentId);
			CatalogModel::reindexDocument($documentId);
			self::buildDocumentSnapshot($documentId);
		}

		public static function restoreDocumentState($documentId, array $state, $authorId)
		{
			$documentId = (int) $documentId;
			$current = self::one($documentId);
			if (!$current) { throw new \RuntimeException('Документ не найден'); }
			$input = $current;
			foreach (array(
				'document_title', 'document_alias', 'document_alias_header', 'document_alias_history',
				'document_short_alias', 'document_breadcrumb_title', 'document_excerpt', 'document_meta_keywords',
					'document_meta_description', 'document_meta_robots', 'document_sitemap_freq', 'document_sitemap_pr',
					'document_in_sitemap', 'document_tags', 'document_property', 'guid', 'document_status', 'document_in_search',
					'document_is_technical',
				'document_parent', 'rubric_tmpl_id', 'document_linked_navi_id', 'document_position',
				'document_author_id',
			) as $key) {
				if (array_key_exists($key, $state)) { $input[$key] = $state[$key]; }
			}

			$input['document_published'] = !empty($state['document_published']) ? date('Y-m-d H:i:s', (int) $state['document_published']) : '';
			$input['document_expire'] = !empty($state['document_expire']) ? date('Y-m-d H:i:s', (int) $state['document_expire']) : '';
			$input['rubric_id'] = (int) $current['rubric_id'];
			return self::save($documentId, $input, (int) $authorId);
		}

		public static function revisionCount($documentId)
		{
			return (int) DB::query('SELECT COUNT(*) FROM ' . self::revisionsTable() . ' WHERE doc_id = %i', (int) $documentId)->getValue();
		}

		public static function save($id, array $input, $authorId)
		{
			$id = (int) $id;
			$existing = $id > 0 ? self::one($id) : null;
			$rubricId = isset($input['rubric_id']) ? (int) $input['rubric_id'] : 0;
			$rubric = self::rubric($rubricId);
			$now = time();
			$data = self::input($input);
			if ($id === 1) {
				$data['document_alias'] = '/';
				$data['document_short_alias'] = '';
				$data['document_alias_history'] = '0';
				$data['document_alias_header'] = 301;
			}

			if ($id > 0 && self::isProtectedDocument($id)) {
				$data['document_is_technical'] = 0;
			}

			$data['rubric_id'] = $rubricId;
			$data['document_changed'] = $now;
			$data['document_version'] = $existing ? max(1, (int) $existing['document_version']) + 1 : 1;
			// Language is a site-wide setting. Keep legacy columns normalized only
			// because the public engine still expects them to exist.
			if (isset($input['document_author_id'])) {
				$data['document_author_id'] = self::validLegacyAuthorId((int) $input['document_author_id']);
			}

			if ($id > 0) {
				DB::Update(self::documentsTable(), $data, 'Id = %i', $id);
				self::syncAliasHistory($id, $existing, $data, (int) $authorId, $rubric);
				self::syncSearchTerms($id, $rubricId, $data['document_meta_keywords'], $data['document_tags']);
				self::clearDocumentCache($id);
				return $id;
			}

			if ((int) $data['rubric_tmpl_id'] <= 0) {
				$data['rubric_tmpl_id'] = 0;
			}

			if ((int) $data['document_position'] <= 0) {
				$data['document_position'] = self::nextPosition();
			}

			if (!isset($data['document_author_id'])) {
				$data['document_author_id'] = self::legacyAuthorId($authorId);
			}

			$data['document_deleted'] = '0';
			$data['document_count_print'] = 0;
			$data['document_count_view'] = 0;
			$data['module_catalog'] = '';
			DB::Insert(self::documentsTable(), $data);
			$newId = (int) DB::insertId();
			self::removeOwnedHistoryAlias($newId, $data['document_alias']);
			self::syncSearchTerms($newId, $rubricId, $data['document_meta_keywords'], $data['document_tags']);
			self::seedFields($newId, $rubricId);
			self::clearDocumentCache($newId);
			return $newId;
		}

		public static function assertEditVersion($id, $expectedVersion)
		{
			$current = DB::query(
				'SELECT document_version FROM ' . self::documentsTable() . ' WHERE Id=%i LIMIT 1 FOR UPDATE',
				(int) $id
			)->getValue();
			if ($current === null || $current === false) { throw new \RuntimeException('Документ не найден'); }
			$current = max(1, (int) $current);
			if ($current !== max(1, (int) $expectedVersion)) { throw new EditConflict($current); }

			return $current;
		}

		public static function documentVersion($id)
		{
			return max(1, (int) DB::query(
				'SELECT document_version FROM ' . self::documentsTable() . ' WHERE Id=%i LIMIT 1',
				(int) $id
			)->getValue());
		}

		public static function editorState($id)
		{
			$row = DB::query('SELECT Id,document_title,document_status,document_deleted,document_changed,document_version FROM '
				. self::documentsTable() . ' WHERE Id=%i LIMIT 1', (int) $id)->getAssoc();
			if (!$row) { return null; }
			$label = $row['document_deleted'] ? 'Удалён' : ($row['document_status'] ? 'Опубликован' : 'Черновик');
			$label = \App\Common\Language::translateSource($label);
			return array(
				'title' => self::decode($row['document_title']),
				'version' => max(1, (int) $row['document_version']),
				'state_label' => $label,
				'state_badge' => $row['document_deleted'] ? 'gray' : ($row['document_status'] ? 'green' : 'amber'),
				'meta' => 'ID ' . (int) $row['Id'] . ', ' . $label . \App\Common\Language::translateSource(', изменён:')
					. ' ' . date('d.m.Y H:i', (int) $row['document_changed']),
			);
		}

		public static function markChanged($id)
		{
			self::touchDocument((int) $id);
			return self::documentVersion((int) $id);
		}

		protected static function touchDocument($id)
		{
			DB::query(
				'UPDATE ' . self::documentsTable()
				. ' SET document_changed=%i, document_version=GREATEST(1, document_version)+1 WHERE Id=%i',
				time(),
				(int) $id
			);
		}

		public static function previewPayload($id, array $input, array $values, $authorId, $fieldGroups = null)
		{
			$id = (int) $id;
			$existing = $id > 0 ? self::one($id) : null;
			$rubricId = $existing ? (int) $existing['rubric_id'] : (isset($input['rubric_id']) ? (int) $input['rubric_id'] : 0);
			$rubric = self::rubric($rubricId);
			$data = self::input($input);
			$data['rubric_id'] = $rubricId;
			$data['document_changed'] = time();
			if (isset($input['document_author_id'])) {
				$data['document_author_id'] = self::validLegacyAuthorId((int) $input['document_author_id']);
			}

			if ($id <= 0) {
				if ((int) $data['rubric_tmpl_id'] <= 0) {
					$data['rubric_tmpl_id'] = 0;
				}

				if ((int) $data['document_position'] <= 0) {
					$data['document_position'] = self::nextPosition();
				}

				if (!isset($data['document_author_id'])) {
					$data['document_author_id'] = self::legacyAuthorId($authorId);
				}

				$data['document_deleted'] = 0;
				$data['document_count_print'] = 0;
				$data['document_count_view'] = 0;
			}

			$fields = array();
			$skippedFields = array();
			$fieldRows = self::resolveFieldGroups($rubricId, $id, $fieldGroups);
			$flatFields = array();
			foreach ($fieldRows as $group) {
				foreach ($group['items'] as $field) {
					$flatFields[(int) $field['Id']] = $field;
				}
			}

			$append = function (array $field, $storageValue) use (&$fields, $values) {
				$fieldId = (int) $field['Id'];
				$storageValue = FieldLifecycle::normalizeForStorage($storageValue, $field, array(), 'adminx_preview');
				$fields[] = array(
					'id' => $fieldId,
					'alias' => (string) $field['rubric_field_alias'],
					'title' => self::decode($field['rubric_field_title']),
					'type' => (string) $field['rubric_field_type'],
					'input' => $values[$fieldId],
					'storage_value' => $storageValue,
					'storage_length' => mb_strlen($storageValue),
				);
			};

			$catalogAllowed = array();
			$catalogFields = array();
			foreach ($flatFields as $fieldId => $field) {
				if ((string) $field['rubric_field_type'] !== 'catalog' || !array_key_exists($fieldId, $values)) { continue; }
				if (!is_array($values[$fieldId]) || !array_key_exists('catalog_ids', $values[$fieldId])) {
					$skippedFields[] = array('id' => $fieldId, 'reason' => 'catalog_ids не переданы');
					continue;
				}

				$selected = CatalogModel::selection($rubricId, $fieldId, $values[$fieldId]['catalog_ids']);
				$append($field, CatalogModel::serializeSelection($rubricId, $fieldId, $selected));
				$allowed = CatalogModel::allowedFieldIds($rubricId, $fieldId, $selected);
				if (!empty($allowed)) { $catalogAllowed = array_values(array_unique(array_merge($catalogAllowed, $allowed))); }
				$catalogFields[$fieldId] = true;
			}

			foreach ($flatFields as $fieldId => $field) {
				if (isset($catalogFields[$fieldId]) || (string) $field['rubric_field_type'] === 'catalog') { continue; }
				if (!array_key_exists($fieldId, $values)) { continue; }
				if (!empty($catalogAllowed) && !in_array($fieldId, $catalogAllowed, true)) {
					$skippedFields[] = array('id' => $fieldId, 'reason' => 'поле не включено в выбранных разделах каталога');
					continue;
				}

				$append($field, self::fieldInputValue($field, $values[$fieldId]));
			}

			return array(
				'mode' => $id > 0 ? 'update' : 'create',
				'document_id' => $id ?: null,
				'document' => $data,
				'fields' => $fields,
				'skipped_fields' => $skippedFields,
				'lifecycle_hooks_executed' => false,
			);
		}

		protected static function resolveFieldGroups($rubricId, $documentId, $fieldGroups)
		{
			return is_array($fieldGroups)
				? $fieldGroups
				: self::fieldsForRubric((int) $rubricId, (int) $documentId);
		}

		protected static function conditionAwareFields(array $fieldGroups)
		{
			$fields = array();
			$seen = array();
			foreach ($fieldGroups as $group) {
				foreach (isset($group['condition_sources']) && is_array($group['condition_sources']) ? $group['condition_sources'] : array() as $field) {
					$fieldId = isset($field['Id']) ? (int) $field['Id'] : 0;
					if ($fieldId <= 0 || isset($seen[$fieldId])) { continue; }
					$seen[$fieldId] = true;
					$fields[] = $field;
				}

				foreach (isset($group['items']) && is_array($group['items']) ? $group['items'] : array() as $field) {
					$fieldId = isset($field['Id']) ? (int) $field['Id'] : 0;
					if ($fieldId <= 0 || isset($seen[$fieldId])) { continue; }
					$seen[$fieldId] = true;
					$fields[] = $field;
				}
			}

			return $fields;
		}

		public static function delete($id)
		{
			$id = (int) $id;
			if (self::isProtectedDocument($id)) {
				throw new \RuntimeException(self::protectedDocumentMessage($id));
			}

			if (!self::one($id)) {
				return false;
			}

			$deleting = Lifecycle::event('content.document.deleting', 'document', 'deleting', $id, array(
				'document' => self::one($id),
			), null, array(), 'adminx_documents');
			if ($deleting->cancelled()) {
				throw new \RuntimeException($deleting->message() !== '' ? $deleting->message() : 'Удаление документа отменено модулем');
			}

			DB::query('UPDATE ' . self::documentsTable() . " SET document_deleted='1', document_changed=%i, document_version=GREATEST(1, document_version)+1 WHERE Id=%i", time(), $id);
			self::clearDocumentCache($id);
			CatalogModel::reindexDocument($id);
			Lifecycle::event('content.document.deleted', 'document', 'deleted', $id, array(), true, array('soft_delete' => true), 'adminx_documents');
			return true;
		}

		public static function restore($id)
		{
			$id = (int) $id;
			if (!self::one($id)) {
				return false;
			}

			DB::query('UPDATE ' . self::documentsTable() . " SET document_deleted='0', document_changed=%i, document_version=GREATEST(1, document_version)+1 WHERE Id=%i", time(), $id);
			self::clearDocumentCache($id);
			CatalogModel::reindexDocument($id);
			self::buildDocumentSnapshot($id);
			return true;
		}

		public static function toggleStatus($id)
		{
			$id = (int) $id;
			$document = DB::query('SELECT * FROM ' . self::documentsTable() . ' WHERE Id=%i LIMIT 1', $id)->getAssoc();
			if (!$document) {
				return false;
			}

			if (self::isProtectedDocument($id)) {
				throw new \RuntimeException('Статус системного документа менять нельзя');
			}

			$status = !empty($document['document_status']) ? '0' : '1';
			DB::query('UPDATE ' . self::documentsTable() . ' SET document_status=%s, document_changed=%i, document_version=GREATEST(1, document_version)+1 WHERE Id=%i', $status, time(), $id);
			self::clearDocumentCache($id);
			CatalogModel::reindexDocument($id);
			self::buildDocumentSnapshot($id);
			return $status;
		}

		public static function copy($id, $authorId)
		{
			$id = (int) $id;
			if (self::isProtectedDocument($id)) {
				throw new \RuntimeException('Системный документ копировать нельзя');
			}

			$source = DB::query('SELECT * FROM ' . self::documentsTable() . ' WHERE Id = %i LIMIT 1', $id)->getAssoc();
			if (!$source) {
				throw new \RuntimeException('Документ не найден');
			}

			unset($source['Id']);
			$source['document_title'] = htmlspecialchars('Копия: ' . self::decode($source['document_title']), ENT_QUOTES);
			$source['document_alias'] = self::generateAlias(
				self::decode($source['document_title']),
				0,
				0,
				(int) $source['rubric_id'],
				time()
			);
			$source['document_short_alias'] = '';
			$source['document_status'] = '0';
			$source['document_deleted'] = '0';
			$source['document_count_print'] = 0;
			$source['document_count_view'] = 0;
			$source['module_catalog'] = '';
			$source['document_author_id'] = self::legacyAuthorId($authorId);
			$source['document_changed'] = time();
			$source['document_version'] = 1;
			$source['document_published'] = 0;
			$source['document_expire'] = 0;
			DB::Insert(self::documentsTable(), $source);
			$newId = (int) DB::insertId();

			foreach (DB::query('SELECT * FROM ' . self::docFieldsTable() . ' WHERE document_id = %i', $id)->getAll() as $field) {
				unset($field['Id']);
				$field['document_id'] = $newId;
				DB::Insert(self::docFieldsTable(), $field);
			}

			foreach (DB::query('SELECT * FROM ' . self::docFieldsTextTable() . ' WHERE document_id = %i', $id)->getAll() as $field) {
				unset($field['Id']);
				$field['document_id'] = $newId;
				DB::Insert(self::docFieldsTextTable(), $field);
			}

			self::syncSearchTerms($newId, (int) $source['rubric_id'], $source['document_meta_keywords'], $source['document_tags']);
			self::clearDocumentCache($newId);
			CatalogModel::reindexDocument($newId);
			self::buildDocumentSnapshot($newId);
			return $newId;
		}

		public static function purge($id)
		{
			$id = (int) $id;
			if (self::isProtectedDocument($id)) {
				throw new \RuntimeException(self::protectedDocumentMessage($id));
			}

			$document = DB::query('SELECT * FROM ' . self::documentsTable() . ' WHERE Id=%i LIMIT 1', $id)->getAssoc();
			if (!$document) {
				return false;
			}

			if (empty($document['document_deleted'])) {
				throw new \RuntimeException('Сначала переместите документ в корзину');
			}

			$ownsTransaction = !DB::$transaction_in_progress;
			if ($ownsTransaction) { DB::startTransaction(); }
			try {
				DB::Delete(self::docFieldsTextTable(), 'document_id = %i', $id);
				DB::Delete(self::docFieldsTable(), 'document_id = %i', $id);
				DB::Delete(self::revisionsTable(), 'doc_id = %i', $id);
				DB::Delete(self::aliasHistoryTable(), 'document_id = %i', $id);
				DB::Delete(self::keywordsTable(), 'document_id = %i', $id);
				DB::Delete(self::tagsTable(), 'document_id = %i', $id);
				DB::Delete(self::remarksTable(), 'document_id = %i', $id);
				DB::Delete(self::viewCountTable(), 'document_id = %i', $id);
				\App\Content\Fields\DocumentRelationIndex::removeDocument($id);
				DB::Delete(self::documentsTable(), 'Id = %i', $id);
				if ($ownsTransaction) { DB::commit(); }
			} catch (\Throwable $e) {
				if ($ownsTransaction) { DB::rollback(); }
				throw $e;
			}

			self::clearDocumentCache($id);
			CatalogModel::reindexDocument($id);
			Lifecycle::event(
				'content.document.deleted',
				'document',
				'deleted',
				$id,
				array(),
				true,
				array('hard_delete' => true),
				'adminx_documents'
			);
			return true;
		}

		public static function stats()
		{
			$table = self::documentsTable();
			return array(
				'total' => (int) DB::query('SELECT COUNT(*) FROM ' . $table)->getValue(),
				'active' => (int) DB::query("SELECT COUNT(*) FROM " . $table . " WHERE document_status = '1' AND document_deleted != '1'")->getValue(),
				'draft' => (int) DB::query("SELECT COUNT(*) FROM " . $table . " WHERE document_status = '0' AND document_deleted != '1'")->getValue(),
				'deleted' => (int) DB::query("SELECT COUNT(*) FROM " . $table . " WHERE document_deleted = '1'")->getValue(),
			);
		}

		public static function viewStats($days = 30)
		{
			$days = max(1, min(365, (int) $days));
			$from = strtotime('-' . ($days - 1) . ' days', mktime(0, 0, 0));
			$dailyRows = DB::query(
				'SELECT day_id, SUM(count) AS views, COUNT(DISTINCT document_id) AS documents'
				. ' FROM ' . self::viewCountTable() . ' WHERE day_id >= %i GROUP BY day_id ORDER BY day_id ASC',
				(int) $from
			)->getAll();
			$daily = array();
			$total = 0;
			foreach ($dailyRows as $row) {
				$views = (int) $row['views'];
				$total += $views;
				$daily[] = array(
					'day' => (int) $row['day_id'],
					'date_label' => date('d.m.Y', (int) $row['day_id']),
					'views' => $views,
					'documents' => (int) $row['documents'],
				);
			}

			$topRows = DB::query(
				'SELECT v.document_id, SUM(v.count) AS views, d.document_title, d.document_alias'
				. ' FROM ' . self::viewCountTable() . ' v'
				. ' LEFT JOIN ' . self::documentsTable() . ' d ON d.Id = v.document_id'
				. ' WHERE v.day_id >= %i GROUP BY v.document_id, d.document_title, d.document_alias'
				. ' ORDER BY views DESC, v.document_id ASC LIMIT 25',
				(int) $from
			)->getAll();
			$top = array();
			foreach ($topRows as $row) {
				$top[] = array(
					'id' => (int) $row['document_id'],
					'title' => !empty($row['document_title']) ? self::decode($row['document_title']) : 'Удалённый документ',
					'alias' => isset($row['document_alias']) ? (string) $row['document_alias'] : '',
					'views' => (int) $row['views'],
				);
			}

			return array(
				'days' => $days,
				'from' => (int) $from,
				'total' => $total,
				'average' => !empty($daily) ? round($total / count($daily), 1) : 0,
				'tracked_documents' => (int) DB::query('SELECT COUNT(DISTINCT document_id) FROM ' . self::viewCountTable() . ' WHERE day_id >= %i', (int) $from)->getValue(),
				'rows' => (int) DB::query('SELECT COUNT(*) FROM ' . self::viewCountTable())->getValue(),
				'daily' => $daily,
				'top' => $top,
			);
		}

		public static function clearViewStats()
		{
			$count = (int) DB::query('SELECT COUNT(*) FROM ' . self::viewCountTable())->getValue();
			DB::query('TRUNCATE TABLE ' . self::viewCountTable());
			return $count;
		}

		public static function rubrics()
		{
			$rows = DB::query(
				'SELECT r.Id, r.rubric_title, r.rubric_alias, r.rubric_template_id, COUNT(f.Id) AS fields_count'
				. ' FROM ' . self::rubricsTable() . ' r'
				. ' LEFT JOIN ' . self::fieldsTable() . ' f ON f.rubric_id = r.Id'
				. ' GROUP BY r.Id, r.rubric_title, r.rubric_alias, r.rubric_template_id, r.rubric_position'
				. ' ORDER BY r.rubric_position ASC, r.Id ASC'
			)->getAll();
			$out = array();
			foreach ($rows as $row) {
				$out[] = array(
					'Id' => (int) $row['Id'],
					'rubric_title' => self::decode($row['rubric_title']),
					'rubric_alias' => (string) $row['rubric_alias'],
					'rubric_template_id' => (int) $row['rubric_template_id'],
					'fields_count' => (int) $row['fields_count'],
				);
			}

			return $out;
		}

		public static function authors()
		{
			$rows = DB::query('SELECT id, legacy_id, name, email, is_active FROM ' . SystemTables::table('users') . ' ORDER BY name ASC, id ASC')->getAll();
			$out = array();
			$seen = array();
			foreach ($rows as $row) {
				$id = !empty($row['legacy_id']) ? (int) $row['legacy_id'] : (int) $row['id'];
				if ($id <= 0 || isset($seen[$id])) {
					continue;
				}

				$seen[$id] = true;
				$out[] = array(
					'id' => $id,
					'name' => trim((string) $row['name']) !== '' ? (string) $row['name'] : (string) $row['email'],
					'email' => (string) $row['email'],
					'active' => !empty($row['is_active']),
				);
			}

			return $out;
		}

		public static function remarks($documentId)
		{
			$rows = DB::query(
				'SELECT * FROM ' . self::remarksTable() . ' WHERE document_id = %i ORDER BY remark_first DESC, remark_published ASC, Id ASC',
				(int) $documentId
			)->getAll();
			$out = array();
			foreach ($rows as $row) {
				$out[] = array(
					'id' => (int) $row['Id'],
					'title' => self::decode($row['remark_title']),
					'text' => self::decode($row['remark_text']),
					'author_id' => (int) $row['remark_author_id'],
					'email' => (string) $row['remark_author_email'],
					'published' => (int) $row['remark_published'],
					'published_label' => !empty($row['remark_published']) ? date('d.m.Y H:i', (int) $row['remark_published']) : '-',
					'first' => (int) $row['remark_first'],
					'status' => (int) $row['remark_status'],
				);
			}

			return $out;
		}

		public static function addRemark($documentId, array $input, $authorId)
		{
			$documentId = (int) $documentId;
			if (!self::one($documentId)) {
				throw new \RuntimeException('Документ не найден');
			}

			$title = trim(isset($input['title']) ? (string) $input['title'] : '');
			$text = trim(isset($input['text']) ? (string) $input['text'] : '');
			if ($text === '') {
				throw new \RuntimeException('Введите текст заметки');
			}

			$first = (int) DB::query('SELECT COUNT(*) FROM ' . self::remarksTable() . ' WHERE document_id = %i', $documentId)->getValue() === 0 ? 1 : 0;
			DB::Insert(self::remarksTable(), array(
				'document_id' => $documentId,
				'remark_first' => $first,
				'remark_title' => htmlspecialchars($title, ENT_QUOTES),
				'remark_text' => htmlspecialchars(function_exists('mb_substr') ? mb_substr($text, 0, 500, 'UTF-8') : substr($text, 0, 500), ENT_QUOTES),
				'remark_author_id' => self::legacyAuthorId($authorId),
				'remark_published' => time(),
				'remark_status' => 1,
				'remark_author_email' => self::adminEmail($authorId),
			));
			return (int) DB::insertId();
		}

		public static function deleteRemark($documentId, $remarkId)
		{
			$exists = (int) DB::query('SELECT Id FROM ' . self::remarksTable() . ' WHERE Id = %i AND document_id = %i LIMIT 1', (int) $remarkId, (int) $documentId)->getValue();
			if ($exists <= 0) {
				return false;
			}

			DB::Delete(self::remarksTable(), 'Id = %i AND document_id = %i', (int) $remarkId, (int) $documentId);
			return true;
		}

		public static function rubric($id)
		{
			return DB::query('SELECT * FROM ' . self::rubricsTable() . ' WHERE Id = %i LIMIT 1', (int) $id)->getAssoc();
		}

		protected static function rubricConditionsEnabled($id)
		{
			static $cache = array();
			$id = (int) $id;
			if (!array_key_exists($id, $cache)) {
				$rubric = self::rubric($id);
				$cache[$id] = $rubric && !empty($rubric['rubric_form_conditions']);
			}

			return $cache[$id];
		}

		public static function aliasAvailable($alias, $exceptId)
		{
			return self::aliasConflict($alias, $exceptId) === null;
		}

		public static function aliasConflict($alias, $exceptId = 0)
		{
			return DocumentAliasRegistry::conflict($alias, $exceptId);
		}

		public static function aliasHistory($documentId)
		{
			$rows = DB::query(
				'SELECT h.*, u.name AS author_name FROM ' . self::aliasHistoryTable() . ' h'
				. ' LEFT JOIN ' . SystemTables::table('users') . ' u ON u.id = h.document_alias_author'
				. ' WHERE h.document_id = %i ORDER BY h.document_alias_changed DESC, h.id DESC',
				(int) $documentId
			)->getAll();
			$out = array();
			foreach ($rows as $row) {
				$out[] = array(
					'id' => (int) $row['id'],
					'document_id' => (int) $row['document_id'],
					'alias' => (string) $row['document_alias'],
					'header' => (int) $row['document_alias_header'],
					'author_id' => (int) $row['document_alias_author'],
					'author_name' => isset($row['author_name']) ? (string) $row['author_name'] : '',
					'changed' => (int) $row['document_alias_changed'],
					'changed_label' => !empty($row['document_alias_changed']) ? date('d.m.Y H:i', (int) $row['document_alias_changed']) : '-',
				);
			}

			return $out;
		}

		public static function allAliasHistory(array $filters)
		{
			$page = max(1, isset($filters['page']) ? (int) $filters['page'] : 1);
			$limit = 50;
			$where = ' WHERE 1=1';
			$args = array();
			$q = trim(isset($filters['q']) ? (string) $filters['q'] : '');
			if ($q !== '') {
				$where .= ' AND (h.document_alias LIKE %ss OR d.document_alias LIKE %ss OR d.document_title LIKE %ss OR d.Id = %i)';
				$args[] = $q; $args[] = $q; $args[] = $q; $args[] = (int) $q;
			}

			$header = isset($filters['header']) ? (int) $filters['header'] : 0;
			if (in_array($header, array(301, 302, 307, 308), true)) {
				$where .= ' AND h.document_alias_header = %i';
				$args[] = $header;
			}

			$from = ' FROM ' . self::aliasHistoryTable() . ' h'
				. ' INNER JOIN ' . self::documentsTable() . ' d ON d.Id = h.document_id'
				. ' LEFT JOIN ' . self::rubricsTable() . ' r ON r.Id = d.rubric_id'
				. ' LEFT JOIN ' . SystemTables::table('users') . ' u ON u.id = h.document_alias_author';
			$count = (int) call_user_func_array(array('DB', 'query'), array_merge(array('SELECT COUNT(*)' . $from . $where), $args))->getValue();
			$pages = max(1, (int) ceil($count / $limit));
			$page = min($page, $pages);
			$offset = ($page - 1) * $limit;
			$sql = 'SELECT h.*, d.document_title, d.document_alias AS target_alias, d.document_deleted,'
				. ' r.rubric_title, u.name AS author_name' . $from . $where
				. ' ORDER BY h.document_alias_changed DESC, h.id DESC LIMIT ' . $offset . ', ' . $limit;
			$rows = call_user_func_array(array('DB', 'query'), array_merge(array($sql), $args))->getAll();
			$items = array();
			foreach ($rows as $row) { $items[] = self::aliasHistoryRow($row); }
			return array('items' => $items, 'pagination' => array(
				'page' => $page, 'pages' => $pages, 'total' => $count,
				'from' => $count ? $offset + 1 : 0, 'to' => min($offset + $limit, $count),
			));
		}

		public static function aliasHistoryStats()
		{
			$table = self::aliasHistoryTable();
			return array(
				'total' => (int) DB::query('SELECT COUNT(*) FROM ' . $table)->getValue(),
				'documents' => (int) DB::query('SELECT COUNT(DISTINCT document_id) FROM ' . $table)->getValue(),
				'permanent' => (int) DB::query('SELECT COUNT(*) FROM ' . $table . ' WHERE document_alias_header IN (301, 308)')->getValue(),
				'temporary' => (int) DB::query('SELECT COUNT(*) FROM ' . $table . ' WHERE document_alias_header IN (302, 307)')->getValue(),
			);
		}

		protected static function aliasHistoryRow(array $row)
		{
			return array(
				'id' => (int) $row['id'], 'document_id' => (int) $row['document_id'],
				'alias' => (string) $row['document_alias'], 'header' => self::redirectHeader($row['document_alias_header']),
				'author_id' => (int) $row['document_alias_author'],
				'author_name' => isset($row['author_name']) ? (string) $row['author_name'] : '',
				'changed' => (int) $row['document_alias_changed'],
				'changed_label' => !empty($row['document_alias_changed']) ? date('d.m.Y H:i', (int) $row['document_alias_changed']) : '-',
				'document_title' => isset($row['document_title']) ? (string) $row['document_title'] : '',
				'target_alias' => isset($row['target_alias']) ? (string) $row['target_alias'] : '',
				'rubric_title' => isset($row['rubric_title']) ? (string) $row['rubric_title'] : '',
				'document_deleted' => !empty($row['document_deleted']),
			);
		}

		public static function saveAliasHistory($documentId, $historyId, array $input, $authorId)
		{
			$documentId = (int) $documentId;
			$historyId = (int) $historyId;
			if (!self::one($documentId)) {
				throw new \RuntimeException('Документ не найден');
			}

			$alias = trim(isset($input['alias']) ? (string) $input['alias'] : '', '/ ');
			if ($alias === '' || !preg_match('/^[a-zA-Z0-9_\-\/\.]+$/', $alias)) {
				throw new \RuntimeException('Укажите корректный alias');
			}

			if (!self::historyAliasAvailable($alias, $documentId, $historyId)) {
				throw new \RuntimeException('Такой alias уже используется документом или другим редиректом');
			}

			$data = array(
				'document_id' => $documentId,
				'document_alias' => $alias,
				'document_alias_header' => self::redirectHeader(isset($input['header']) ? $input['header'] : 301),
				'document_alias_author' => (int) $authorId > 0 ? (int) $authorId : 1,
				'document_alias_changed' => time(),
			);
			if ($historyId > 0) {
				DB::Update(self::aliasHistoryTable(), $data, 'id = %i AND document_id = %i', $historyId, $documentId);
				return $historyId;
			}

			DB::Insert(self::aliasHistoryTable(), $data);
			return (int) DB::insertId();
		}

		public static function deleteAliasHistory($documentId, $historyId)
		{
			$exists = (int) DB::query(
				'SELECT id FROM ' . self::aliasHistoryTable() . ' WHERE id = %i AND document_id = %i LIMIT 1',
				(int) $historyId,
				(int) $documentId
			)->getValue();
			if ($exists <= 0) {
				return false;
			}

			DB::Delete(self::aliasHistoryTable(), 'id = %i AND document_id = %i', (int) $historyId, (int) $documentId);
			return true;
		}

		public static function documentPicker($q, $rubricId = 0, $limit = 20)
		{
			return (new \App\Content\Documents\DocumentPickerRepository())->search($q, $rubricId, $limit);
		}

		public static function parentDocumentPicker($q, $limit = 20, $excludeId = 0)
		{
			$limit = max(1, min(50, (int) $limit));
			$excludeId = max(0, (int) $excludeId);
			$items = CatalogModel::searchParentDocuments($q, $limit);
			$seen = array();
			$result = array();
			foreach ($items as $item) {
				$id = (int) $item['id'];
				if ($id <= 0 || $id === $excludeId || isset($seen[$id])) { continue; }
				$seen[$id] = true;
				$result[] = $item;
				if (count($result) >= $limit) { return $result; }
			}

			$documents = (new \App\Content\Documents\DocumentPickerRepository())->search($q, array(), $limit);
			foreach ($documents as $item) {
				$id = (int) $item['id'];
				if ($id <= 0 || $id === $excludeId || isset($seen[$id])) { continue; }
				$seen[$id] = true;
				$result[] = $item;
				if (count($result) >= $limit) { break; }
			}

			return $result;
		}

		/** Existing keywords/tags for the searchable tag inputs in document editor. */
		public static function termSuggestions($kind, $q = '', $rubricId = 0, $limit = 12)
		{
			$kind = $kind === 'keywords' ? 'keywords' : 'tags';
			$q = trim((string) $q);
			$rubricId = max(0, (int) $rubricId);
			$limit = max(5, min(30, (int) $limit));
			$termTable = $kind === 'keywords' ? self::keywordsTable() : self::tagsTable();
			$termColumn = $kind === 'keywords' ? 'keyword' : 'tag';

			$selectRubric = $rubricId > 0
				? 'SUM(CASE WHEN d.rubric_id = %i THEN 1 ELSE 0 END) AS rubric_count'
				: '0 AS rubric_count';
			$sql = 'SELECT x.' . $termColumn . ' AS term, COUNT(*) AS usage_count, ' . $selectRubric
				. ' FROM ' . $termTable . ' x'
				. ' INNER JOIN ' . self::documentsTable() . ' d ON d.Id = x.document_id'
				. " WHERE d.document_deleted != '1' AND TRIM(x." . $termColumn . ") <> ''";
			$args = array();
			if ($rubricId > 0) {
				$args[] = $rubricId;
			}

			if ($q !== '') {
				$sql .= ' AND x.' . $termColumn . ' LIKE %ss';
				$args[] = $q;
			}

			$sql .= ' GROUP BY x.' . $termColumn
				. ' ORDER BY rubric_count DESC, usage_count DESC, x.' . $termColumn . ' ASC'
				. ' LIMIT ' . $limit;
			$rows = call_user_func_array(array('DB', 'query'), array_merge(array($sql), $args))->getAll();

			$items = array();
			foreach ($rows ?: array() as $row) {
				$term = trim((string) $row['term']);
				if ($term === '') { continue; }
				$items[] = array(
					'value' => $term,
					'count' => (int) $row['usage_count'],
					'rubric_count' => (int) $row['rubric_count'],
				);
			}

			return $items;
		}

		protected static function input(array $input)
		{
			$get = function ($key) use ($input) {
				return isset($input[$key]) ? (string) $input[$key] : '';
			};
			return array(
				'document_title' => htmlspecialchars(trim($get('document_title')), ENT_QUOTES),
				'document_alias' => DocumentAliasRegistry::normalize($get('document_alias')),
				'document_alias_header' => self::redirectHeader($get('document_alias_header')),
				'document_alias_history' => (string) self::aliasHistoryMode($get('document_alias_history')),
				'document_short_alias' => substr(trim($get('document_short_alias')), 0, 10),
				'document_breadcrumb_title' => htmlspecialchars(trim($get('document_breadcrumb_title')), ENT_QUOTES),
				'document_excerpt' => trim($get('document_excerpt')),
				'document_meta_keywords' => trim($get('document_meta_keywords')),
				'document_meta_description' => trim($get('document_meta_description')),
				'document_meta_robots' => self::robotsValue($get('document_meta_robots')),
					'document_sitemap_freq' => self::sitemapFreqValue($get('document_sitemap_freq')),
					'document_sitemap_pr' => self::sitemapPrValue($get('document_sitemap_pr')),
					'document_in_sitemap' => (int) $get('document_in_sitemap') === 1 ? 1 : 0,
				'document_tags' => trim($get('document_tags')),
				'document_property' => trim($get('document_property')),
				'guid' => substr(trim($get('guid')), 0, 100),
					'document_status' => (int) $get('document_status') === 1 ? '1' : '0',
					'document_in_search' => (int) $get('document_in_search') === 1 ? '1' : '0',
					'document_is_technical' => (int) $get('document_is_technical') === 1 ? 1 : 0,
				'document_parent' => (int) $get('document_parent'),
				'rubric_tmpl_id' => (int) $get('rubric_tmpl_id'),
				'document_linked_navi_id' => (int) $get('document_linked_navi_id'),
				'document_position' => (int) $get('document_position'),
				'document_published' => self::parseDate($get('document_published')),
				'document_expire' => self::parseDate($get('document_expire')),
			);
		}

		protected static function sitemapFreqValue($value)
		{
			$v = (int) $value;
			return ($v >= 0 && $v <= 6) ? $v : 3;
		}

		protected static function sitemapPrValue($value)
		{
			$v = round((float) $value, 1);
			if ($v < 0) { $v = 0.0; }
			if ($v > 1) { $v = 1.0; }
			return $v;
		}

		/** Транслитерация заголовка в ЧПУ-алиас (как в sample/other). */
		public static function slugify($value)
		{
			$map = array(
				'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
				'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
				'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
				'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
				'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
			);
			$value = function_exists('mb_strtolower') ? mb_strtolower((string) $value, 'UTF-8') : strtolower((string) $value);
			$value = strtr($value, $map);
			$value = preg_replace('/[^a-z0-9]+/u', '-', $value);
			$value = preg_replace('/-{2,}/', '-', $value);
			return trim((string) $value, '-');
		}

		/** Уникальный алиас из заголовка (добавляет -2, -3… при конфликте). */
		/** Короткий алиас свободен (не занят ни коротким, ни обычным алиасом). */
		public static function shortAliasAvailable($code, $exceptId)
		{
			return DocumentAliasRegistry::available($code, $exceptId);
		}

		/** Случайный уникальный короткий алиас (URL-shortener, как в legacy gen_short_link). */
		public static function generateShortAlias($excludeId = 0, $length = 6)
		{
			$length = max(4, min(10, (int) $length));
			for ($i = 0; $i < 50; $i++) {
				$code = Str::random($length);
				if (self::shortAliasAvailable($code, (int) $excludeId)) {
					return $code;
				}
			}

			return Str::random($length);
		}

		/** Путь алиаса родителя без крайних слэшей (иерархия ЧПУ). */
		public static function parentAliasPath($parentId)
		{
			$parentId = (int) $parentId;
			if ($parentId <= 0) {
				return '';
			}

			$alias = (string) DB::query(
				'SELECT document_alias FROM ' . self::documentsTable() . ' WHERE Id = %i LIMIT 1',
				$parentId
			)->getValue();
			return trim($alias, '/');
		}

		/** Уникальный иерархический алиас: <алиас-родителя>/<slug>, с -2/-3 при конфликте. */
		public static function generateAlias($title, $excludeId = 0, $parentId = 0, $rubricId = 0, $publishedAt = 0)
		{
			$base = self::slugify($title);
			if ($base === '') {
				$base = 'document';
			}

			$parentPath = self::parentAliasPath($parentId);
			$rubric = (int) $rubricId > 0 ? self::rubric((int) $rubricId) : null;
			$pattern = $rubric && isset($rubric['rubric_alias']) ? (string) $rubric['rubric_alias'] : '';
			$build = function ($leaf) use ($parentPath, $pattern, $publishedAt) {
				return DocumentAliasTemplate::compose($pattern, $leaf, (int) $publishedAt, $parentPath);
			};
			$alias = $build($base);
			$n = 2;
			while (!self::aliasAvailable($alias, (int) $excludeId)) {
				$alias = $build($base . '-' . $n);
				$n++;
				if ($n > 200) {
					break;
				}
			}

			return $alias;
		}

		/** Дополняет короткий alias шаблоном рубрики при создании документа. */
		public static function prepareAliasInput(array $input, $id = 0)
		{
			if ((int) $id > 0) {
				return $input;
			}

			$rubricId = isset($input['rubric_id']) ? (int) $input['rubric_id'] : 0;
			$rubric = self::rubric($rubricId);
			$pattern = $rubric && isset($rubric['rubric_alias']) ? (string) $rubric['rubric_alias'] : '';
			$publishedAt = self::parseDate(isset($input['document_published']) ? $input['document_published'] : '');
			$parentId = isset($input['document_parent']) ? (int) $input['document_parent'] : 0;
			$alias = trim(isset($input['document_alias']) ? (string) $input['document_alias'] : '', '/ ');
			if ($alias === '') {
				$title = isset($input['document_title']) ? self::decode($input['document_title']) : '';
				$input['document_alias'] = self::generateAlias($title, 0, $parentId, $rubricId, $publishedAt);
				return $input;
			}

			$input['document_alias'] = DocumentAliasTemplate::compose(
				$pattern,
				$alias,
				$publishedAt,
				self::parentAliasPath($parentId)
			);
			return $input;
		}

		/** Шаблоны рубрики для выбора шаблона документа. */
		public static function rubricTemplates($rubricId)
		{
			$rows = DB::query(
				'SELECT id, title FROM ' . self::rubricTemplatesTable() . ' WHERE rubric_id = %i ORDER BY title ASC, id ASC',
				(int) $rubricId
			)->getAll();
			$out = array();
			foreach ($rows as $row) {
				$out[] = array('id' => (int) $row['id'], 'title' => self::decode($row['title']));
			}

			return $out;
		}

		/** Пункты навигации для привязки документа. */
		public static function navigationItems()
		{
			$rows = DB::query(
				'SELECT navigation_item_id AS id, title, navigation_id FROM ' . self::navigationItemsTable()
				. " WHERE status = '1' ORDER BY navigation_id ASC, position ASC, title ASC LIMIT 500"
			)->getAll();
			$out = array();
			foreach ($rows as $row) {
				$out[] = array('id' => (int) $row['id'], 'title' => self::decode($row['title']));
			}

			return $out;
		}

		/** Короткая карточка документа-родителя. */
		public static function parentInfo($id)
		{
			$id = (int) $id;
			if ($id <= 0) {
				return null;
			}

			$row = DB::query(
				'SELECT Id, document_title, document_alias FROM ' . self::documentsTable() . ' WHERE Id = %i LIMIT 1',
				$id
			)->getAssoc();
			if (!$row) {
				return null;
			}

			return array(
				'id' => (int) $row['Id'],
				'title' => self::decode($row['document_title']),
				'alias' => (string) $row['document_alias'],
			);
		}

		public static function rubricTemplatesTable() { return ContentTables::table('rubric_templates'); }

		public static function navigationItemsTable() { return ContentTables::table('navigation_items'); }

		protected static function row(array $row, $full = false)
		{
			$row['Id'] = (int) $row['Id'];
			$row['rubric_id'] = (int) $row['rubric_id'];
			$row['document_title'] = self::decode($row['document_title']);
			$row['document_breadcrumb_title'] = self::decode($row['document_breadcrumb_title']);
			$row['document_status'] = (int) $row['document_status'];
			$row['document_deleted'] = (int) $row['document_deleted'];
			$row['document_in_search'] = (int) $row['document_in_search'];
			$row['document_is_technical'] = isset($row['document_is_technical']) ? (int) $row['document_is_technical'] : 0;
			$row['document_in_sitemap'] = isset($row['document_in_sitemap']) ? (int) $row['document_in_sitemap'] : 1;
			$row['rubric_is_technical'] = isset($row['rubric_is_technical']) ? (int) $row['rubric_is_technical'] : 0;
			$row['is_technical_effective'] = $row['document_is_technical'] || $row['rubric_is_technical'];
			$row['document_published'] = (int) $row['document_published'];
			$row['document_expire'] = (int) $row['document_expire'];
			$row['document_changed'] = (int) $row['document_changed'];
			$row['document_version'] = isset($row['document_version']) ? max(1, (int) $row['document_version']) : 1;
			$row['document_author_id'] = isset($row['document_author_id']) ? (int) $row['document_author_id'] : 1;
			$row['document_count_view'] = isset($row['document_count_view']) ? (int) $row['document_count_view'] : 0;
			$row['document_count_print'] = isset($row['document_count_print']) ? (int) $row['document_count_print'] : 0;
			$row['document_short_alias'] = isset($row['document_short_alias']) ? (string) $row['document_short_alias'] : '';
			$row['document_alias_header'] = isset($row['document_alias_header']) ? (int) $row['document_alias_header'] : 301;
			$row['document_alias_history'] = isset($row['document_alias_history']) ? (int) $row['document_alias_history'] : 0;
			$row['document_excerpt'] = isset($row['document_excerpt']) ? self::decode($row['document_excerpt']) : '';
			$row['document_property'] = isset($row['document_property']) ? self::decode($row['document_property']) : '';
			$row['guid'] = isset($row['guid']) ? (string) $row['guid'] : '';
			$row['document_parent'] = isset($row['document_parent']) ? (int) $row['document_parent'] : 0;
			$row['rubric_tmpl_id'] = isset($row['rubric_tmpl_id']) ? (int) $row['rubric_tmpl_id'] : 0;
			$row['document_linked_navi_id'] = isset($row['document_linked_navi_id']) ? (int) $row['document_linked_navi_id'] : 0;
			$row['document_sitemap_freq'] = isset($row['document_sitemap_freq']) ? (int) $row['document_sitemap_freq'] : 3;
			$row['document_sitemap_pr'] = isset($row['document_sitemap_pr']) ? (float) $row['document_sitemap_pr'] : 0.5;
			$row['document_position'] = isset($row['document_position']) ? (int) $row['document_position'] : 0;
			$row['parent_info'] = $full && $row['document_parent'] > 0 ? self::parentInfo($row['document_parent']) : null;
			$row['published_input'] = self::dateInput($row['document_published']);
			$row['expire_input'] = self::dateInput($row['document_expire']);
			$row['published_label'] = $row['document_published'] > 0 ? date('d.m.Y H:i', $row['document_published']) : 'не задано';
			$row['changed_label'] = $row['document_changed'] > 0 ? date('d.m.Y H:i', $row['document_changed']) : 'не задано';
			$row['state_label'] = $row['document_deleted'] ? 'Удалён' : ($row['document_status'] ? 'Опубликован' : 'Черновик');
			$row['state_badge'] = $row['document_deleted'] ? 'gray' : ($row['document_status'] ? 'green' : 'amber');
			$row['rubric_title'] = isset($row['rubric_title']) ? self::decode($row['rubric_title']) : '';
			$row['rubric_alias'] = isset($row['rubric_alias']) ? (string) $row['rubric_alias'] : '';
			$row['is_protected'] = self::isProtectedDocument($row['Id']);
			$row['can_delete'] = !$row['is_protected'] && !$row['document_deleted'];
			$row['can_restore'] = !$row['is_protected'] && $row['document_deleted'] === 1;
			$row['can_toggle'] = !$row['is_protected'] && !$row['document_deleted'];
			$row['can_copy'] = !$row['is_protected'] && !$row['document_deleted'];
			$row['can_purge'] = !$row['is_protected'] && $row['document_deleted'] === 1;
			if ($full) {
				$row['fields_count'] = (int) DB::query('SELECT COUNT(*) FROM ' . self::docFieldsTable() . ' WHERE document_id = %i', $row['Id'])->getValue();
				$row['revisions_count'] = self::revisionCount($row['Id']);
				$row['aliases_count'] = (int) DB::query('SELECT COUNT(*) FROM ' . self::aliasHistoryTable() . ' WHERE document_id = %i', $row['Id'])->getValue();
				$row['remarks_count'] = (int) DB::query('SELECT COUNT(*) FROM ' . self::remarksTable() . ' WHERE document_id = %i', $row['Id'])->getValue();
			}

			return $row;
		}

		public static function isProtectedDocument($id)
		{
			$id = (int) $id;
			if ($id === 1) {
				return true;
			}

			return $id > 0 && $id === (int) Settings::get('page_not_found_id', 0);
		}

		protected static function protectedDocumentMessage($id)
		{
			return (int) $id === 1
				? 'Главная страница является системной и не может быть удалена'
				: 'Документ назначен страницей 404 и не может быть удалён';
		}

		protected static function seedFields($documentId, $rubricId)
		{
			$fields = DB::query('SELECT Id FROM ' . self::fieldsTable() . ' WHERE rubric_id = %i', (int) $rubricId)->getAll();
			foreach ($fields as $field) {
				DB::Insert(self::docFieldsTable(), array(
					'rubric_field_id' => (int) $field['Id'],
					'document_id' => (int) $documentId,
					'field_value' => '',
					'field_number_value' => 0,
					'document_in_search' => '1',
				));
			}
		}

		protected static function fieldRow(array $row, $documentId, $mediaDraftToken = '', $conditionsEnabled = false)
		{
			$type = (string) $row['rubric_field_type'];
			$hasInitialValue = array_key_exists('__initial_value', $row);
			$raw = self::rawFieldValue($row);
			$editor = FieldAdminEditors::describe($type);
			$kind = self::documentEditorKind($editor);
			$settings = FieldSettings::effective($row);
			$description = self::decode($row['rubric_field_description']);
			if ($type === 'choice') {
				$kind = isset($settings['mode']) && (string) $settings['mode'] === 'multiple' ? 'choice_multi' : 'choice';
			}

			$parsed = FieldAdminEditors::parseValue($type, $raw);
			$plugin = FieldRegistry::get($type);
			$isDocumentMedia = $plugin instanceof \App\Content\Fields\DocumentMediaFieldType;
			$mediaTargetDir = $isDocumentMedia ? self::mediaTargetDir($row, $documentId) : '';
			$field = array(
				'Id' => (int) $row['Id'],
				'rubric_id' => (int) $row['rubric_id'],
				'rubric_field_group' => (int) $row['rubric_field_group'],
				'rubric_field_title' => self::decode($row['rubric_field_title']),
				'rubric_field_alias' => (string) $row['rubric_field_alias'],
				'rubric_field_type' => $type,
				'rubric_field_numeric' => (int) $row['rubric_field_numeric'],
				'rubric_field_search' => (int) $row['rubric_field_search'],
				'rubric_field_description' => $description,
				'rubric_field_description_html' => HtmlSanitizer::clean($description),
				'rubric_field_default' => (string) $row['rubric_field_default'],
				'rubric_field_settings' => isset($row['rubric_field_settings']) ? (string) $row['rubric_field_settings'] : '',
				'group_settings' => isset($row['group_settings']) ? (string) $row['group_settings'] : '',
				'settings' => $settings,
				'condition' => $conditionsEnabled ? FieldConditionEvaluator::condition($row) : array(),
				'document_field_id' => (int) $row['document_field_id'],
				'field_value' => $raw,
				'field_number_value' => isset($row['field_number_value']) ? (string) $row['field_number_value'] : '0',
				'field_in_search' => isset($row['field_in_search']) ? (int) $row['field_in_search'] : 1,
				'editor' => $editor,
				'editor_kind' => $kind,
				'parsed' => $type === 'youtube' ? $parsed : self::viewValue($kind, $parsed, $raw),
				'options' => self::fieldOptions($row['rubric_field_default'], $type, $settings),
				'media_upload_dir' => $isDocumentMedia ? self::mediaUploadDir($row, $documentId, $mediaDraftToken) : '',
				'media_target_dir' => $mediaTargetDir,
				'media_picker_dir' => $isDocumentMedia && (int) $documentId > 0 ? $mediaTargetDir : '/uploads',
				'relation_rubric_ids' => isset($settings['rubric']) && is_array($settings['rubric']) ? array_values($settings['rubric']) : array(),
				'tag' => '[tag:fld:' . (int) $row['Id'] . ']',
			);
			if ($kind === 'relation_list') {
				$field['relation_items'] = self::relationDocuments(
					isset($parsed['document_ids']) && is_array($parsed['document_ids']) ? $parsed['document_ids'] : array()
				);
			}

			// Одиночный «Документ из рубрики»: резолвим заголовок выбранного документа,
			// чтобы в поле показывать наименование, а не только ID.
			if ($kind === 'relation' && $type === 'doc_from_rub') {
				$selectedId = (((int) $row['document_field_id'] > 0 || $hasInitialValue) && is_array($parsed))
					? (int) (isset($parsed['document_id']) ? $parsed['document_id'] : 0)
					: 0;
				if ($selectedId > 0) {
					$docs = self::relationDocuments(array($selectedId));
					$field['relation_selected'] = !empty($docs)
						? $docs[0]
						: array('id' => $selectedId, 'title' => 'Документ #' . $selectedId, 'alias' => '', 'rubric_title' => '');
				}
			}

			// Оформление поля в документе из rubric_field_settings (adminx): ширина,
			// обязательность и приставка/суффикс (составное поле, напр. цена + ₽).
			$field['required'] = !empty($settings['required']);
			// Приставка/суффикс имеют смысл только для простых однострочных полей
			// (одиночный <input>), иначе аддон не встроить в контрол плагина.
			$affixTypes = array('single_line', 'single_line_numeric', 'number');
			$affixOk = in_array($type, $affixTypes, true);
			$field['prefix'] = ($affixOk && !empty($settings['prefix'])) ? (string) $settings['prefix'] : '';
			$field['suffix'] = ($affixOk && !empty($settings['suffix'])) ? (string) $settings['suffix'] : '';
			// Числовому полю без явного суффикса подставляем единицу по названию/алиасу
			// (Цена → ₽, Скидка → %, Вес → кг и т.п.), как в референсе sample/other.
			if ($field['suffix'] === '' && $type === 'single_line_numeric') {
				$field['suffix'] = self::autoUnit($field['rubric_field_title'], $field['rubric_field_alias']);
			}

			if ($field['suffix'] === '' && $type === 'number') {
				$format = isset($settings['format']) ? (string) $settings['format'] : 'decimal';
				if ($format === 'money') {
					$currencies = array('RUB' => '₽', 'USD' => '$', 'EUR' => '€');
					$currency = isset($settings['currency']) ? (string) $settings['currency'] : 'RUB';
					$field['suffix'] = isset($currencies[$currency]) ? $currencies[$currency] : '';
				} elseif ($format === 'percent') {
					$field['suffix'] = '%';
				} elseif ($format === 'measurement') {
					$field['suffix'] = isset($settings['unit']) ? trim((string) $settings['unit']) : '';
				}
			}

			// Плагин типа поля сам рисует контрол (см. Rubrics\FieldEditors\*::renderEdit).
			$field['has_plugin'] = isset($editor['status']) && $editor['status'] === 'native';
			$field['width_class'] = self::fieldWidthClass($kind, $type, isset($settings['width']) ? (string) $settings['width'] : '');
			$field['html'] = $type === 'catalog'
				? self::renderCatalogField($field, CatalogModel::parseValue($raw))
				: FieldAdminEditors::renderEdit($type, $field);
			return $field;
		}

		protected static function rawFieldValue(array $row)
		{
			if (array_key_exists('__initial_value', $row)) { return (string) $row['__initial_value']; }
			if (!empty($row['document_field_id'])) {
				return (string) $row['field_value'] . (string) $row['field_value_more'];
			}

			return self::initialFieldValue((string) $row['rubric_field_type'], (string) $row['rubric_field_default']);
		}

		protected static function conditionSourceRow(array $row)
		{
			return array(
				'Id' => (int) $row['Id'],
				'rubric_id' => (int) $row['rubric_id'],
				'rubric_field_group' => (int) $row['rubric_field_group'],
				'rubric_field_title' => self::decode($row['rubric_field_title']),
				'rubric_field_alias' => (string) $row['rubric_field_alias'],
				'rubric_field_type' => (string) $row['rubric_field_type'],
				'rubric_field_settings' => isset($row['rubric_field_settings']) ? (string) $row['rubric_field_settings'] : '',
				'group_settings' => isset($row['group_settings']) ? (string) $row['group_settings'] : '',
				'field_value' => self::rawFieldValue($row),
				'_condition_source' => true,
			);
		}

		protected static function conditionReferences(array $rows)
		{
			$references = array();
			foreach ($rows as $row) {
				foreach (array(FieldConditionEvaluator::condition($row), FieldConditionEvaluator::groupCondition($row)) as $condition) {
					if (!empty($condition['tree']) && is_array($condition['tree'])) {
						self::collectConditionReferences($condition['tree'], $references);
					}
				}
			}

			return $references;
		}

		protected static function collectConditionReferences(array $node, array &$references)
		{
			if (isset($node['items']) && is_array($node['items'])) {
				foreach ($node['items'] as $item) {
					if (is_array($item)) { self::collectConditionReferences($item, $references); }
				}

				return;
			}

			$reference = isset($node['field']) ? strtolower(trim((string) $node['field'])) : '';
			if ($reference !== '') { $references[$reference] = true; }
		}

		protected static function hasGroupSettingsColumn()
		{
			static $has = null;
			if ($has === null) {
				$has = DatabaseSchema::columnExists(self::groupsTable(), 'group_settings');
			}

			return $has;
		}

		/** Ширина поля в 12-колоночной сетке документа. $pref — явный пресет из настроек. */
		protected static function fieldWidthClass($kind, $type, $pref = '')
		{
			switch ($pref) {
				case 'full':    return 'is-wide';
				case 'half':    return 'is-half';
				case 'third':   return 'is-third';
				case 'quarter': return 'is-quarter';
			}

			// Авто: узкие типы — половина, остальные — на всю ширину.
			$half = array('boolean', 'date', 'datetime', 'number', 'range', 'numeric_parts', 'choice', 'relation', 'url');
			if (in_array($type, array('contact', 'color'), true)) {
				return 'is-half';
			}

			if (in_array($kind, $half, true)) {
				return 'is-half';
			}

			return 'is-wide';
		}

		/** Единица измерения числового поля по названию/алиасу (₽/%/кг/см/°/мес). */
		protected static function autoUnit($title, $alias)
		{
			$text = $title . ' ' . $alias;
			$text = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
			if (preg_match('/(^|[^a-zа-я])(price|old|цена|стоим)/u', $text)) { return '₽'; }
			if (preg_match('/(discount|скидк)/u', $text)) { return '%'; }
			if (preg_match('/(вес|нагруз|weight|load)/u', $text)) { return 'кг'; }
			if (preg_match('/(ширин|высот|глубин|длин|размер|колес|поролон|width|height|depth|length|diameter)/u', $text)) { return 'см'; }
			if (preg_match('/(угол|наклон|поворот|тренделенбург|angle|turn)/u', $text)) { return '°'; }
			if (preg_match('/(гарант|warranty)/u', $text)) { return 'мес'; }
			return '';
		}

		protected static function documentEditorKind(array $editor)
		{
			$type = isset($editor['type']) ? (string) $editor['type'] : '';
			if (in_array($type, array('dropdown', 'drop_down', 'drop_down_key'), true)) {
				return 'choice';
			}

			if (in_array($type, array('multi_select', 'checkbox_multi', 'multi_checkbox'), true)) {
				return 'choice_multi';
			}

			if ($type === 'multi_list_single') {
				return 'list_single';
			}

			if ($type === 'multi_list_triple') {
				return 'list_triple';
			}

			if (in_array($type, array('multi_list', 'multi_links'), true)) {
				return 'list_pair';
			}

			if (in_array($type, array('single_line_numeric_two', 'single_line_numeric_three'), true)) {
				return 'numeric_parts';
			}

			// В describe() default_kind описывает редактор значения документа. Для
			// типов, чьи legacy defaults были конфигурацией (медиа, связи), control
			// значения по умолчанию намеренно удалён, но сам editor kind не меняется.
			if (isset($editor['default_kind']) && (string) $editor['default_kind'] !== '') {
				return (string) $editor['default_kind'];
			}

			$controls = isset($editor['controls']) && is_array($editor['controls']) ? $editor['controls'] : array();
			foreach ($controls as $control) {
				if (isset($control['name']) && $control['name'] === 'rubric_field_default') {
					return isset($control['kind']) ? (string) $control['kind'] : 'textarea';
				}
			}

			return 'textarea';
		}

		protected static function viewValue($kind, array $parsed, $raw)
		{
			if ($kind === 'boolean') {
				return !empty($parsed['checked']) ? '1' : '0';
			}

			if ($kind === 'date') {
				return isset($parsed['date']) ? (string) $parsed['date'] : '';
			}

			if ($kind === 'datetime') {
				return isset($parsed['datetime']) ? (string) $parsed['datetime'] : '';
			}

			if ($kind === 'period') {
				return array(
					'start' => isset($parsed['start']) ? (string) $parsed['start'] : '',
					'end' => isset($parsed['end']) ? (string) $parsed['end'] : '',
				);
			}

			if ($kind === 'number') {
				return isset($parsed['number']) ? (string) $parsed['number'] : (string) $raw;
			}

			if ($kind === 'range') {
				return array(
					'min' => isset($parsed['min']) ? (string) $parsed['min'] : '',
					'max' => isset($parsed['max']) ? (string) $parsed['max'] : '',
				);
			}

			if ($kind === 'dimensions') {
				return array(
					'length' => isset($parsed['length']) ? (string) $parsed['length'] : '',
					'width' => isset($parsed['width']) ? (string) $parsed['width'] : '',
					'height' => isset($parsed['height']) ? (string) $parsed['height'] : '',
				);
			}

			if ($kind === 'address') {
				return array(
					'postal_code' => isset($parsed['postal_code']) ? (string) $parsed['postal_code'] : '',
					'region' => isset($parsed['region']) ? (string) $parsed['region'] : '',
					'city' => isset($parsed['city']) ? (string) $parsed['city'] : '',
					'street' => isset($parsed['street']) ? (string) $parsed['street'] : '',
					'building' => isset($parsed['building']) ? (string) $parsed['building'] : '',
					'unit' => isset($parsed['unit']) ? (string) $parsed['unit'] : '',
					'latitude' => isset($parsed['latitude']) ? (string) $parsed['latitude'] : '',
					'longitude' => isset($parsed['longitude']) ? (string) $parsed['longitude'] : '',
				);
			}

			if ($kind === 'numeric_parts') {
				return isset($parsed['parts']) && is_array($parsed['parts']) ? $parsed['parts'] : array();
			}

			if ($kind === 'choice') {
				return isset($parsed['selected']) ? (string) $parsed['selected'] : (string) $raw;
			}

			if ($kind === 'choice_multi') {
				return isset($parsed['selected']) && is_array($parsed['selected']) ? $parsed['selected'] : array();
			}

			if ($kind === 'media') {
				return array(
					'url' => isset($parsed['url']) ? (string) $parsed['url'] : '',
					'description' => isset($parsed['description']) ? (string) $parsed['description'] : '',
				);
			}

			if ($kind === 'relation') {
				return array(
					'document_id' => isset($parsed['document_id']) ? (string) $parsed['document_id'] : (string) $raw,
				);
			}

			if (in_array($kind, array('lines', 'relation_list'), true)) {
				$items = isset($parsed['document_ids']) ? $parsed['document_ids'] : (isset($parsed['tags']) ? $parsed['tags'] : array());
				return is_array($items) ? implode("\n", $items) : (string) $raw;
			}

			if (in_array($kind, array('key_value', 'pipe_list'), true)) {
				return self::itemsToLines(isset($parsed['items']) ? $parsed['items'] : array(), $raw);
			}

			if ($kind === 'media_list') {
				return array(
					'raw' => (string) $raw,
					'items' => isset($parsed['items']) && is_array($parsed['items']) ? $parsed['items'] : array(),
				);
			}

			if (in_array($kind, array('list_single', 'list_pair', 'list_triple', 'packages'), true)) {
				return array(
					'raw' => (string) $raw,
					'items' => isset($parsed['items']) && is_array($parsed['items']) ? $parsed['items'] : array(),
				);
			}

			if ($kind === 'pipe') {
				return isset($parsed['parts']) && is_array($parsed['parts']) ? implode('|', $parsed['parts']) : (string) $raw;
			}

			return (string) $raw;
		}

		protected static function fieldInputValue(array $field, $value)
		{
			$kind = (string) $field['editor_kind'];
			$type = (string) $field['rubric_field_type'];
			if (!is_array($value)) {
				$value = array('raw' => (string) $value);
			}

			if ($type === 'catalog') {
				$selected = CatalogModel::selection((int) $field['rubric_id'], (int) $field['Id'], isset($value['catalog_ids']) ? $value['catalog_ids'] : array());
				return CatalogModel::serializeSelection((int) $field['rubric_id'], (int) $field['Id'], $selected);
			}

			if ($type === 'youtube') {
				return FieldAdminEditors::serializeValue($type, array(
					'url' => isset($value['url']) ? (string) $value['url'] : '',
					'width' => isset($value['width']) ? (string) $value['width'] : '',
					'height' => isset($value['height']) ? (string) $value['height'] : '',
					'fullscreen' => isset($value['fullscreen']) ? (string) $value['fullscreen'] : '',
					'source' => isset($value['source']) ? (string) $value['source'] : '',
				));
			}

			if ($kind === 'boolean') {
				return FieldAdminEditors::serializeValue($type, array('checked' => !empty($value['checked'])));
			}

			if ($kind === 'date') {
				return FieldAdminEditors::serializeValue($type, array('date' => isset($value['date']) ? (string) $value['date'] : ''));
			}

			if ($kind === 'datetime') {
				return FieldAdminEditors::serializeValue($type, array('datetime' => isset($value['datetime']) ? (string) $value['datetime'] : ''));
			}

			if ($kind === 'period') {
				return FieldAdminEditors::serializeValue($type, array(
					'start' => isset($value['start']) ? (string) $value['start'] : '',
					'end' => isset($value['end']) ? (string) $value['end'] : '',
				));
			}

			if ($kind === 'number') {
				return FieldAdminEditors::serializeValue($type, array('number' => isset($value['number']) ? (string) $value['number'] : ''));
			}

			if ($kind === 'range') {
				return FieldAdminEditors::serializeValue($type, array(
					'min' => isset($value['min']) ? (string) $value['min'] : '',
					'max' => isset($value['max']) ? (string) $value['max'] : '',
				));
			}

			if ($kind === 'dimensions') {
				return FieldAdminEditors::serializeValue($type, array(
					'length' => isset($value['length']) ? (string) $value['length'] : '',
					'width' => isset($value['width']) ? (string) $value['width'] : '',
					'height' => isset($value['height']) ? (string) $value['height'] : '',
				));
			}

			if ($kind === 'address') {
				return FieldAdminEditors::serializeValue($type, array(
					'postal_code' => isset($value['postal_code']) ? (string) $value['postal_code'] : '',
					'region' => isset($value['region']) ? (string) $value['region'] : '',
					'city' => isset($value['city']) ? (string) $value['city'] : '',
					'street' => isset($value['street']) ? (string) $value['street'] : '',
					'building' => isset($value['building']) ? (string) $value['building'] : '',
					'unit' => isset($value['unit']) ? (string) $value['unit'] : '',
					'latitude' => isset($value['latitude']) ? (string) $value['latitude'] : '',
					'longitude' => isset($value['longitude']) ? (string) $value['longitude'] : '',
				));
			}

			if ($kind === 'numeric_parts') {
				return FieldAdminEditors::serializeValue($type, array('parts' => isset($value['parts']) && is_array($value['parts']) ? $value['parts'] : array()));
			}

			if ($kind === 'choice') {
				return FieldAdminEditors::serializeValue($type, array('selected' => isset($value['selected']) ? (string) $value['selected'] : ''));
			}

			if ($kind === 'choice_multi') {
				return FieldAdminEditors::serializeValue($type, array('selected' => isset($value['selected']) && is_array($value['selected']) ? $value['selected'] : array()));
			}

			if ($kind === 'media') {
				return FieldAdminEditors::serializeValue($type, array(
					'url' => isset($value['url']) ? (string) $value['url'] : '',
					'description' => isset($value['description']) ? (string) $value['description'] : '',
				));
			}

			if ($kind === 'relation') {
				return FieldAdminEditors::serializeValue($type, array('document_id' => isset($value['document_id']) ? (int) $value['document_id'] : 0));
			}

			if ($kind === 'relation_list') {
				$ids = isset($value['document_ids']) && is_array($value['document_ids'])
					? $value['document_ids']
					: self::linesArray(isset($value['lines']) ? $value['lines'] : '');
				return FieldAdminEditors::serializeValue($type, array('document_ids' => $ids));
			}

			if ($kind === 'lines') {
				if ($type === 'tags') {
					return FieldAdminEditors::serializeValue($type, array('tags' => self::linesArray(isset($value['lines']) ? $value['lines'] : '')));
				}

				return isset($value['raw']) ? (string) $value['raw'] : (isset($value['lines']) ? (string) $value['lines'] : '');
			}

			if ($kind === 'media_list') {
				return FieldAdminEditors::serializeValue($type, array(
					'items' => isset($value['items']) && is_array($value['items']) ? $value['items'] : array(),
				));
			}

			if (in_array($kind, array('list_single', 'list_pair', 'list_triple', 'packages'), true)) {
				return FieldAdminEditors::serializeValue($type, array(
					'items' => isset($value['items']) && is_array($value['items']) ? $value['items'] : array(),
				));
			}

			if (in_array($kind, array('key_value', 'pipe_list'), true)) {
				return self::serializeLinesByKind($type, $kind, isset($value['lines']) ? (string) $value['lines'] : '');
			}

			if ($kind === 'pipe') {
				return FieldAdminEditors::serializeValue($type, array('parts' => preg_split('/[|,\r\n]+/', isset($value['raw']) ? (string) $value['raw'] : '')));
			}

			return isset($value['raw']) ? (string) $value['raw'] : '';
		}

		protected static function saveFieldValue($documentId, array $field, $value, $inSearch)
		{
			$documentId = (int) $documentId;
			$fieldId = (int) $field['Id'];
			$type = isset($field['rubric_field_type']) ? (string) $field['rubric_field_type'] : '';
			$value = FieldLifecycle::normalizeForStorage($value, $field, array('Id' => $documentId), 'adminx_document');
			$saving = FieldLifecycle::saving($value, $field, $documentId, 'adminx_document');
			if ($saving->cancelled()) { return; }
			$value = $saving->result();
			$substr = mb_substr($value, 501, 1) ? 499 : 500;
			$first = mb_substr($value, 0, $substr);
			$slash = mb_substr($first, 498, 1) === '\\';
			if ($slash) {
				$first = rtrim($first, '\\');
			}

			$data = array(
				'field_value' => $first,
				'field_number_value' => ((int) $field['rubric_field_numeric'] === 1)
					? ($type === 'period' ? \App\Content\Fields\Types\PeriodValue::indexValue($value) : FieldValueCodec::numericIndexValue($value))
					: 0,
				'document_in_search' => (int) $inSearch ? '1' : '0',
			);
			$exists = (int) DB::query('SELECT Id FROM ' . self::docFieldsTable() . ' WHERE document_id = %i AND rubric_field_id = %i LIMIT 1', $documentId, $fieldId)->getValue();
			if ($exists > 0) {
				DB::Update(self::docFieldsTable(), $data, 'Id = %i', $exists);
			} else {
				$data['document_id'] = $documentId;
				$data['rubric_field_id'] = $fieldId;
				DB::Insert(self::docFieldsTable(), $data);
			}

			if (mb_strlen($value) > $substr) {
				$more = mb_substr($value, $substr);
				if ($slash) {
					$more = '\\' . $more;
				}

				$textId = (int) DB::query('SELECT Id FROM ' . self::docFieldsTextTable() . ' WHERE document_id = %i AND rubric_field_id = %i LIMIT 1', $documentId, $fieldId)->getValue();
				if ($textId > 0) {
					DB::Update(self::docFieldsTextTable(), array('field_value' => $more), 'Id = %i', $textId);
				} else {
					DB::Insert(self::docFieldsTextTable(), array('document_id' => $documentId, 'rubric_field_id' => $fieldId, 'field_value' => $more));
				}
			} else {
				DB::Delete(self::docFieldsTextTable(), 'document_id = %i AND rubric_field_id = %i', $documentId, $fieldId);
			}

			FieldLifecycle::saved($value, $field, $documentId, 'adminx_document');
		}

		protected static function relationDocuments(array $ids)
		{
			$ordered = array();
			foreach ($ids as $id) {
				$id = (int) $id;
				if ($id > 0 && !isset($ordered[$id])) { $ordered[$id] = null; }
			}

			if (empty($ordered)) { return array(); }

			$rows = DB::query(
				'SELECT d.Id, d.document_title, d.document_alias, r.rubric_title'
				. ' FROM ' . self::documentsTable() . ' d'
				. ' LEFT JOIN ' . self::rubricsTable() . ' r ON r.Id = d.rubric_id'
				. ' WHERE d.Id IN (' . implode(',', array_keys($ordered)) . ')'
			)->getAll();
			foreach ($rows as $row) {
				$id = (int) $row['Id'];
				$ordered[$id] = array(
					'id' => $id,
					'title' => self::decode($row['document_title']),
					'alias' => (string) $row['document_alias'],
					'rubric_title' => self::decode(isset($row['rubric_title']) ? $row['rubric_title'] : ''),
				);
			}

			$out = array();
			foreach ($ordered as $id => $item) {
				$out[] = $item ?: array('id' => (int) $id, 'title' => 'Документ недоступен', 'alias' => '', 'rubric_title' => '');
			}

			return $out;
		}

		protected static function renderCatalogField(array $field, array $parsed)
		{
			$selectedIds = array();
			foreach (isset($parsed['catalog_ids']) ? $parsed['catalog_ids'] : array() as $cid) {
				$selectedIds[] = (int) $cid;
			}

			$selected = array_fill_keys($selectedIds, true);
			$tree = CatalogModel::tree((int) $field['rubric_id'], (int) $field['Id'], false);
			$settings = CatalogModel::settings((int) $field['rubric_id'], (int) $field['Id']);
			$id = (int) $field['Id'];

			$flat = array();
			self::flattenCatalog($tree, $flat);
			$tokens = '';
			foreach ($selectedIds as $cid) {
				if (isset($flat[$cid])) {
					$tokens .= self::catalogTokenHtml($id, $cid, $flat[$cid]['name'], $flat[$cid]['fields']);
				}
			}

			$emptyLabel = AdminLocale::translateMarkup('Разделы не выбраны.');
			$addLabel = AdminLocale::translateMarkup('Добавить раздел');
			$searchLabel = AdminLocale::translateMarkup('Найти раздел');
			$html = '<div class="documents-catalog-field" data-document-catalog-field="' . $id . '" data-catalog-limits-fields="' . ((int) $settings['doc_fileds'] === 1 ? '1' : '0') . '">'
				. '<input type="hidden" name="fields[' . $id . '][catalog_ids][]" value="">'
				. '<div class="documents-catalog-tokens" data-document-catalog-tokens>' . $tokens . '</div>'
				. '<p class="documents-catalog-empty"' . (!empty($selectedIds) ? ' hidden' : '') . ' data-document-catalog-empty>' . $emptyLabel . '</p>'
				. '<div class="dropdown documents-catalog-dropdown">'
				. '<button class="btn btn-secondary btn-sm" type="button" data-dropdown data-document-catalog-add><i class="ti ti-plus"></i>' . $addLabel . '</button>'
				. '<div class="dropdown-menu documents-catalog-menu">'
				. '<label class="input-wrap documents-catalog-search"><i class="ti ti-search"></i><input class="input" type="search" placeholder="' . $searchLabel . '" data-document-catalog-search></label>'
				. '<ol class="documents-catalog-tree">' . self::renderCatalogNodes($tree, $id, $selected) . '</ol>'
				. '</div></div></div>';
			return $html;
		}

		protected static function flattenCatalog(array $items, array &$flat)
		{
			foreach ($items as $item) {
				$flat[(int) $item['id']] = array(
					'name' => (string) $item['name'],
					'fields' => implode(',', isset($item['fields_use']) ? $item['fields_use'] : array()),
				);
				if (!empty($item['children'])) {
					self::flattenCatalog($item['children'], $flat);
				}
			}
		}

		protected static function catalogTokenHtml($fieldId, $catalogId, $name, $fields)
		{
			$removeLabel = AdminLocale::translateMarkup('Убрать раздел');
			return '<span class="documents-catalog-token" data-document-catalog-token data-catalog-id="' . (int) $catalogId
				. '" data-catalog-fields="' . self::e($fields) . '">'
				. '<i class="ti ti-folder documents-catalog-token-icon" aria-hidden="true"></i>'
				. '<span class="documents-catalog-token-name">' . self::e($name) . '</span>'
				. '<input type="hidden" name="fields[' . (int) $fieldId . '][catalog_ids][]" value="' . (int) $catalogId . '">'
				. '<button class="documents-catalog-token-remove" type="button" data-document-catalog-remove aria-label="' . $removeLabel . '"><i class="ti ti-x"></i></button>'
				. '</span>';
		}

		protected static function renderCatalogNodes(array $items, $fieldId, array $selected)
		{
			$html = '';
			$documentLabel = AdminLocale::translateMarkup('документ #');
			$hiddenLabel = AdminLocale::translateMarkup('скрыт');
			foreach ($items as $item) {
				$fields = implode(',', isset($item['fields_use']) ? $item['fields_use'] : array());
				$isSelected = isset($selected[$item['id']]);
				$html .= '<li data-document-catalog-node data-search="' . self::e($item['name']) . '">'
					. '<button class="documents-catalog-option' . ($isSelected ? ' is-picked' : '') . '" type="button" data-document-catalog-option'
					. ' data-catalog-id="' . (int) $item['id'] . '" data-catalog-name="' . self::e($item['name']) . '" data-catalog-fields="' . self::e($fields) . '">'
					. '<span class="documents-catalog-option-main"><b>' . self::e($item['name']) . '</b><small>#' . (int) $item['id']
					. ($item['document_id'] > 0 ? ' · ' . $documentLabel . (int) $item['document_id'] : '') . '</small></span>'
					. ((int) $item['status'] === 1 ? '' : '<span class="badge badge-gray">' . $hiddenLabel . '</span>') . '</button>';
				if (!empty($item['children'])) { $html .= '<ol>' . self::renderCatalogNodes($item['children'], $fieldId, $selected) . '</ol>'; }
				$html .= '</li>';
			}

			return $html;
		}

		protected static function e($value)
		{
			return Str::escape((string) $value);
		}

		/**
		 * Legacy field default is usually type configuration, not a document value.
		 * Only scalar/content fields use it as the initial value of a new document.
		 */
		protected static function initialFieldValue($type, $default)
		{
			$type = (string) $type;
			$default = (string) $default;
			if (in_array($type, array(
				'single_line', 'single_line_numeric', 'single_line_numeric_two', 'single_line_numeric_three', 'number', 'range', 'dimensions', 'packages', 'address', 'date_time', 'period', 'choice',
				'multi_line', 'multi_line_simple', 'multi_line_slim', 'content', 'text_to_html', 'text_to_image', 'checkbox', 'date', 'link', 'contact', 'color', 'code'
			), true)) {
				return $default;
			}

			if (in_array($type, array('dropdown', 'drop_down'), true)) {
				foreach (preg_split('/\r\n|\r|\n|,/', $default) ?: array() as $option) {
					$option = trim((string) $option);
					if ($option !== '') {
						return $option;
					}
				}
			}

			return '';
		}

		protected static function fieldOptions($default, $type = '', array $settings = array())
		{
			$default = (string) $default;
			$type = (string) $type;
			if (!empty($settings['options']) && is_array($settings['options'])) {
				$out = array();
				foreach ($settings['options'] as $key => $option) {
					if (is_array($option)) {
						$value = isset($option['value']) ? (string) $option['value'] : (isset($option['key']) ? (string) $option['key'] : (string) $key);
						$label = isset($option['label']) ? (string) $option['label'] : (isset($option['title']) ? (string) $option['title'] : $value);
					} else {
						$value = is_int($key) ? (string) $option : (string) $key;
						$label = (string) $option;
					}

					if ($value !== '' && $label !== '') {
						$out[] = array('value' => $value, 'label' => $label);
					}
				}

				return $out;
			}

			$lines = preg_split('/\r\n|\r|\n/', $default);
			if (count($lines) <= 1) { $lines = explode(',', $default); }
			$indexed = in_array($type, array('drop_down_key', 'checkbox_multi', 'multi_checkbox'), true);
			$out = array();
			foreach ($lines ?: array() as $index => $line) {
				$line = trim((string) $line);
				if ($line === '') {
					continue;
				}

				$parts = explode('|', $line, 2);
				$label = isset($parts[1]) && trim($parts[1]) !== '' ? trim($parts[1]) : trim($parts[0]);
				$value = trim($parts[0]);
				if ($indexed) {
					$value = (string) ((int) $index + ($type === 'drop_down_key' ? 0 : 1));
					$label = $line;
				}

				$out[] = array('value' => $value, 'label' => $label);
			}

			return $out;
		}

		protected static function mediaUploadDir(array $field, $documentId, $mediaDraftToken = '')
		{
			$type = isset($field['rubric_field_type']) ? (string) $field['rubric_field_type'] : '';
			$plugin = FieldRegistry::get($type);
			if (!$plugin instanceof \App\Content\Fields\DocumentMediaFieldType) {
				return '/uploads';
			}

			if ($mediaDraftToken !== '') {
				return DocumentMediaDraft::directory($mediaDraftToken, (int) $field['Id']);
			}

			return (int) $documentId > 0 ? self::mediaTargetDir($field, $documentId) : '/uploads';
		}

		protected static function mediaTargetDir(array $field, $documentId)
		{
			$rubric = self::rubric(isset($field['rubric_id']) ? (int) $field['rubric_id'] : 0);
			return (int) $documentId > 0
				? DocumentMediaPath::resolve($field, (int) $documentId, is_array($rubric) ? $rubric : array())
				: DocumentMediaPath::describe($field, is_array($rubric) ? $rubric : array());
		}

		protected static function linesArray($value)
		{
			$items = preg_split('/\r\n|\r|\n|,/', (string) $value);
			$out = array();
			foreach ($items ?: array() as $item) {
				$item = trim((string) $item);
				if ($item !== '') {
					$out[] = $item;
				}
			}

			return $out;
		}

		protected static function serializeLinesByKind($type, $kind, $lines)
		{
			$items = array();
			foreach (preg_split('/\r\n|\r|\n/', (string) $lines) ?: array() as $line) {
				$line = trim((string) $line);
				if ($line === '') {
					continue;
				}

				$parts = explode('|', $line);
				if ($kind === 'media_list') {
					$items[] = array('url' => isset($parts[0]) ? $parts[0] : '', 'description' => isset($parts[1]) ? implode('|', array_slice($parts, 1)) : '');
				} elseif ($type === 'multi_list_single') {
					$items[] = array('value' => $line);
				} elseif ($type === 'multi_list_triple') {
					$items[] = array('param' => isset($parts[0]) ? $parts[0] : '', 'value' => isset($parts[1]) ? $parts[1] : '', 'value2' => isset($parts[2]) ? $parts[2] : '');
				} else {
					$items[] = array('param' => isset($parts[0]) ? $parts[0] : '', 'value' => isset($parts[1]) ? implode('|', array_slice($parts, 1)) : '');
				}
			}

			return FieldAdminEditors::serializeValue($type, array('items' => $items));
		}

		protected static function itemsToLines(array $items, $raw)
		{
			if (empty($items)) {
				return (string) $raw;
			}

			$out = array();
			foreach ($items as $item) {
				if (!is_array($item)) {
					continue;
				}

				if (isset($item['url'])) {
					$out[] = (string) $item['url'] . (!empty($item['description']) ? '|' . $item['description'] : '');
				} elseif (isset($item['value2'])) {
					$out[] = (string) $item['param'] . '|' . (string) $item['value'] . '|' . (string) $item['value2'];
				} elseif (isset($item['param'])) {
					$out[] = (string) $item['param'] . (isset($item['value']) && $item['value'] !== '' ? '|' . $item['value'] : '');
				} elseif (isset($item['value'])) {
					$out[] = (string) $item['value'];
				}
			}

			return implode("\n", $out);
		}

		protected static function nextPosition()
		{
			return (int) DB::query('SELECT COALESCE(MAX(document_position), 0) + 1 FROM ' . self::documentsTable())->getValue();
		}

		protected static function systemLanguage()
		{
			if (defined('DEFAULT_LANGUAGE') && trim((string) DEFAULT_LANGUAGE) !== '') {
				return trim((string) DEFAULT_LANGUAGE);
			}

			return 'ru';
		}

		protected static function legacyAuthorId($adminId)
		{
			$adminId = (int) $adminId;
			if ($adminId <= 0) {
				return 1;
			}

			$legacyId = 0;
			try {
				$hasLegacyId = DB::query('SHOW COLUMNS FROM ' . SystemTables::table('users') . ' LIKE %s', 'legacy_id')->numRows() > 0;
				if ($hasLegacyId) { $legacyId = (int) DB::query('SELECT legacy_id FROM ' . SystemTables::table('users') . ' WHERE id = %i LIMIT 1', $adminId)->getValue(); }
			} catch (\Throwable $e) { $legacyId = 0; }
			return $legacyId > 0 ? $legacyId : $adminId;
		}

		protected static function validLegacyAuthorId($id)
		{
			$id = (int) $id;
			foreach (self::authors() as $author) {
				if ((int) $author['id'] === $id) {
					return $id;
				}
			}

			return 1;
		}

		protected static function adminEmail($adminId)
		{
			return (string) DB::query('SELECT email FROM ' . SystemTables::table('users') . ' WHERE id = %i LIMIT 1', (int) $adminId)->getValue();
		}

		protected static function aliasHistoryMode($value)
		{
			$value = (int) $value;
			return in_array($value, array(0, 1, 2), true) ? $value : 0;
		}

		protected static function redirectHeader($value)
		{
			$value = (int) $value;
			return in_array($value, array(301, 302, 307, 308), true) ? $value : 301;
		}

		protected static function syncAliasHistory($documentId, $existing, array $data, $authorId, $rubric)
		{
			if (!$existing) {
				return;
			}

			$oldAlias = trim(isset($existing['document_alias']) ? (string) $existing['document_alias'] : '', '/ ');
			$newAlias = trim(isset($data['document_alias']) ? (string) $data['document_alias'] : '', '/ ');
			self::removeOwnedHistoryAlias($documentId, $newAlias);
			if ($oldAlias === '' || $oldAlias === $newAlias) {
				return;
			}

			$mode = isset($data['document_alias_history']) ? (int) $data['document_alias_history'] : 0;
			$rubricEnabled = $rubric && !empty($rubric['rubric_alias_history']);
			if ($mode === 2 || ($mode === 0 && !$rubricEnabled)) {
				return;
			}

			if (!self::historyAliasAvailable($oldAlias, $documentId, 0)) {
				return;
			}

			DB::Insert(self::aliasHistoryTable(), array(
				'document_id' => (int) $documentId,
				'document_alias' => $oldAlias,
				'document_alias_header' => self::redirectHeader(isset($existing['document_alias_header']) ? $existing['document_alias_header'] : 301),
				'document_alias_author' => (int) $authorId > 0 ? (int) $authorId : 1,
				'document_alias_changed' => time(),
			));
		}

		protected static function historyAliasAvailable($alias, $documentId, $exceptHistoryId)
		{
			$document = (int) DB::query(
				'SELECT Id FROM ' . self::documentsTable() . ' WHERE document_alias = %s AND Id != %i LIMIT 1',
				(string) $alias,
				(int) $documentId
			)->getValue();
			if ($document > 0) {
				return false;
			}

			$history = (int) DB::query(
				'SELECT id FROM ' . self::aliasHistoryTable() . ' WHERE document_alias = %s AND id != %i LIMIT 1',
				(string) $alias,
				(int) $exceptHistoryId
			)->getValue();
			return $history <= 0;
		}

		protected static function removeOwnedHistoryAlias($documentId, $alias)
		{
			$alias = trim((string) $alias, '/ ');
			if ($alias !== '') {
				DB::Delete(self::aliasHistoryTable(), 'document_id = %i AND document_alias = %s', (int) $documentId, $alias);
			}
		}

		protected static function syncSearchTerms($documentId, $rubricId, $keywords, $tags)
		{
			DocumentTerms::sync($documentId, $rubricId, $keywords, $tags);
		}

		protected static function parseDate($value)
		{
			$value = trim((string) $value);
			if ($value === '') {
				return 0;
			}

			$time = strtotime($value);
			return $time ? (int) $time : 0;
		}

		protected static function robotsValue($value)
		{
			$value = trim((string) $value);
			return in_array($value, array('index,follow', 'index,nofollow', 'noindex,nofollow'), true) ? $value : 'index,follow';
		}

		protected static function dateInput($time)
		{
			return (int) $time > 0 ? date('Y-m-d\\TH:i', (int) $time) : '';
		}

		protected static function decode($value)
		{
			return htmlspecialchars_decode(stripslashes((string) $value), ENT_QUOTES);
		}

		protected static function clearDocumentCache($documentId)
		{
			DB::afterCommit(function () use ($documentId) { ContentCacheInvalidator::document($documentId, false); });
		}

		protected static function buildDocumentSnapshot($documentId)
		{
			if (DB::$transaction_in_progress) {
				DB::afterCommit(function () use ($documentId) { ContentCacheInvalidator::document($documentId, true); });
				return null;
			}

			return ContentCacheInvalidator::document($documentId, true);
		}

		protected static function tableExists($table)
		{
			return (bool) DB::query('SHOW TABLES LIKE %s', (string) $table)->getValue();
		}
	}
