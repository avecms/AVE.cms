<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Content/Documents/DocumentMutationService.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Content\Documents;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Content\ContentTables;
	use App\Content\DocumentTerms;
	use App\Content\Fields\FieldContext;
	use App\Content\Fields\FieldConditionEvaluator;
	use App\Content\Fields\ComputedFieldEvaluator;
	use App\Content\Fields\FieldLifecycle;
	use App\Content\Fields\FieldRegistry;
	use App\Content\Fields\FieldValidator;
	use App\Content\Fields\FieldValueCodec;
	use App\Common\DatabaseSchema;
	use App\Helpers\Str;
	use DB;

	/** Transactional native writer used by the JSON API. */
	class DocumentMutationService
	{
		public function save($documentId, array $payload, $actorId, $source = 'api')
		{
			return $this->saveInternal($documentId, $payload, $actorId, $source, false);
		}

		/** Trusted partial edits of an existing document; not a public payload option. */
		public function patch($documentId, array $payload, $actorId, $source = 'internal_patch')
		{
			if ((int) $documentId <= 0) { throw new DocumentSaveRejected('Документ не найден'); }
			return $this->saveInternal($documentId, $payload, $actorId, $source, true);
		}

		protected function saveInternal($documentId, array $payload, $actorId, $source, $partial)
		{
			$documentId = (int) $documentId;
			$existing = $documentId > 0 ? $this->document($documentId) : null;
			if ($documentId > 0 && !$existing) { throw new DocumentSaveRejected('Документ не найден'); }
			$operation = $existing ? 'update' : 'create';
			$rubricId = $existing ? (int) $existing['rubric_id'] : (int) $this->pick($payload, array('rubric_id'), 0);
			$rubric = $this->rubric($rubricId);
			if (!$rubric) { throw new DocumentSaveRejected('Рубрика не найдена', array('rubric_id' => 'Выберите существующую рубрику')); }
			if ($existing && isset($payload['rubric_id']) && (int) $payload['rubric_id'] !== $rubricId) {
				throw new DocumentSaveRejected('Рубрику существующего документа менять нельзя', array('rubric_id' => 'Создайте новый документ в другой рубрике'));
			}

			$definitions = $this->fieldDefinitions($rubricId);
			$fields = $this->fieldValues($documentId, $definitions, !$existing);
			$storedFields = $fields;
			$incomingFields = isset($payload['fields']) && is_array($payload['fields']) ? $payload['fields'] : array();
			$actionOverrides = $this->conditionActionOverrides($incomingFields, $definitions);
			$fieldErrors = $this->applyIncomingFields($incomingFields, $definitions, $fields);
			$conditionsEnabled = !empty($rubric['rubric_form_conditions']);
			$actions = $conditionsEnabled
				? FieldConditionEvaluator::valueActions(array_values($definitions), $storedFields, $fields, !$existing)
				: array();
			foreach ($actionOverrides as $fieldId => $override) { unset($actions[$fieldId]); }
			foreach ($actions as $fieldId => $action) {
				$fields[$fieldId] = isset($action['action']) && (string) $action['action'] === 'set'
					? (string) (isset($action['value']) ? $action['value'] : '')
					: '';
			}

			$states = $conditionsEnabled ? FieldConditionEvaluator::states(array_values($definitions), $fields) : array();
			foreach ($states as $fieldId => $state) {
				if ((empty($state['visible']) || !empty($state['locked'])) && !isset($actions[$fieldId])) {
					$fields[$fieldId] = isset($storedFields[$fieldId]) ? $storedFields[$fieldId] : '';
				}
			}

			$data = $this->normalizeData($payload, $existing, $rubric);
			$fields = ComputedFieldEvaluator::apply($definitions, $fields, $data);
			DocumentSaveEvents::before($operation, $source, $documentId, $rubricId, $actorId, $data, $fields, $existing ?: array());
			DocumentRubricCodeRunner::before($rubricId, $data, $fields, $documentId, $actorId, !$existing, $source);
			$data = $this->normalizeData($data, $existing, $rubric, true);
			$fields = ComputedFieldEvaluator::apply($definitions, $fields, $data);
			$validationDefinitions = $partial ? $this->patchDefinitions($definitions, $incomingFields, $storedFields, $fields, $conditionsEnabled) : $definitions;
			$errors = array_merge(
				$fieldErrors,
				$this->validate($data, $documentId, $rubricId),
				FieldValidator::validateValues(array_values($validationDefinitions), $fields, $conditionsEnabled)
			);
			if (!empty($errors)) { throw new DocumentSaveRejected('Проверьте данные документа', $errors); }

			$previousDatabaseExceptionMode = DB::$throw_exception_on_error;
			DB::$throw_exception_on_error = true;
			$externalTransaction = DB::$transaction_in_progress;
			$ownsTransaction = !$externalTransaction;
			if ($ownsTransaction) { DB::startTransaction(); }
			try {
				if ($existing) { $this->captureRevision($documentId, $actorId); }
				$documentId = $this->writeDocument($documentId, $data, $rubric, $actorId, $existing);
				$identityState = $this->document($documentId);
				DocumentRubricCodeRunner::after($rubricId, $data, $fields, $documentId, $actorId, !$existing, $source);
				$data = $this->normalizeData($data, $this->document($documentId), $rubric, true);
				$writeDefinitions = $definitions;
				if ($partial) {
					$fields = ComputedFieldEvaluator::apply($definitions, $fields, $data);
					$validationDefinitions = $this->patchDefinitions($definitions, $incomingFields, $storedFields, $fields, $conditionsEnabled);
					$errors = FieldValidator::validateValues(array_values($validationDefinitions), $fields, $conditionsEnabled);
					if ($errors) { throw new DocumentSaveRejected('Проверьте данные документа', $errors); }
					$writeDefinitions = array_filter($validationDefinitions, function ($field) { return empty($field['_condition_source']); });
				}

				$this->writeDocument($documentId, $data, $rubric, $actorId, $identityState ?: array(), false);
				$this->writeFields($documentId, $data, $writeDefinitions, $fields);
				DocumentTerms::sync($documentId, $rubricId, $data['document_meta_keywords'], $data['document_tags']);
				DocumentSaveEvents::persisted($operation, $source, $documentId, $rubricId, $actorId, $data, $fields, $existing ?: array());
				if ($ownsTransaction) { DB::commit(); }
			} catch (\Throwable $e) {
				if ($ownsTransaction) { DB::rollback(); }
				throw $e;
			} finally {
				DB::$throw_exception_on_error = $previousDatabaseExceptionMode;
			}

			$snapshot = array();
			if ($externalTransaction) {
				DB::afterCommit(function () use ($operation, $source, $documentId, $rubricId, $actorId, $data, $fields, $existing) {
					ContentCacheInvalidator::document($documentId, true);
					$savedSnapshot = (new DocumentSnapshotRepository())->find($documentId);
					$savedSnapshot = is_array($savedSnapshot) ? $savedSnapshot : array();
					DocumentSaveEvents::after($operation, $source, $documentId, $rubricId, $actorId, $data, $fields, $existing ?: array(), $savedSnapshot);
				});
			} else {
				ContentCacheInvalidator::document($documentId, true);
				$snapshot = (new DocumentSnapshotRepository())->find($documentId);
				$snapshot = is_array($snapshot) ? $snapshot : array();
				DocumentSaveEvents::after($operation, $source, $documentId, $rubricId, $actorId, $data, $fields, $existing ?: array(), $snapshot);
			}

			return array(
				'id' => $documentId,
				'operation' => $operation,
				'document_version' => (int) DB::query('SELECT document_version FROM ' . ContentTables::table('documents') . ' WHERE Id=%i', $documentId)->getValue(),
				'snapshot' => $snapshot,
				'snapshot_warning' => ContentCacheInvalidator::consumeError($documentId),
			);
		}

		protected function patchDefinitions(array $definitions, array $incoming, array $stored, array $values, $conditionsEnabled)
		{
			$submitted = array();
			$this->applyIncomingFields($incoming, $definitions, $submitted);
			$before = $conditionsEnabled ? FieldConditionEvaluator::states(array_values($definitions), $stored) : array();
			$after = $conditionsEnabled ? FieldConditionEvaluator::states(array_values($definitions), $values) : array();
			foreach ($definitions as $id => &$field) {
				$changed = array_key_exists($id, $submitted)
					|| (isset($stored[$id]) ? $stored[$id] : '') !== (isset($values[$id]) ? $values[$id] : '')
					|| (isset($before[$id]) ? $before[$id] : null) !== (isset($after[$id]) ? $after[$id] : null);
				// Keep all aliases/group conditions available to the existing validator.
				if (!$changed) { $field['_condition_source'] = true; }
			}

			unset($field);
			return $definitions;
		}

		/**
		 * Move an existing document to another rubric while preserving fields
		 * with the same non-empty alias and compatible type.
		 */
		public function move($documentId, $targetRubricId, $actorId, $source = 'bulk_editor')
		{
			$documentId = (int) $documentId;
			$targetRubricId = (int) $targetRubricId;
			$existing = $this->document($documentId);
			$targetRubric = $this->rubric($targetRubricId);
			if (!$existing) { throw new DocumentSaveRejected('Документ не найден'); }
			if (!$targetRubric) { throw new DocumentSaveRejected('Целевая рубрика не найдена'); }
			if ((int) $existing['rubric_id'] === $targetRubricId) {
				return array('id' => $documentId, 'operation' => 'move', 'moved' => false, 'mapped_fields' => 0, 'skipped_fields' => array());
			}

			$sourceDefinitions = $this->fieldDefinitions((int) $existing['rubric_id']);
			$sourceValues = $this->fieldValues($documentId, $sourceDefinitions, false);
			$sourceByAlias = array();
			foreach ($sourceDefinitions as $fieldId => $field) {
				$alias = trim((string) $field['rubric_field_alias']);
				if ($alias === '') { continue; }
				$sourceByAlias[$alias] = array(
					'type' => (string) $field['rubric_field_type'],
					'value' => isset($sourceValues[$fieldId]) ? $sourceValues[$fieldId] : '',
					'title' => (string) $field['rubric_field_title'],
				);
			}

			$targetDefinitions = $this->fieldDefinitions($targetRubricId);
			$targetFields = $this->fieldValues(0, $targetDefinitions, true);
			$mapped = 0;
			$skipped = array();
			foreach ($targetDefinitions as $fieldId => $field) {
				$alias = trim((string) $field['rubric_field_alias']);
				if ($alias === '' || !isset($sourceByAlias[$alias])) { continue; }
				if ((string) $field['rubric_field_type'] !== $sourceByAlias[$alias]['type']) {
					$skipped[] = $alias;
					continue;
				}

				$targetFields[$fieldId] = $sourceByAlias[$alias]['value'];
				$mapped++;
			}

			$targetTemplateId = (int) DB::query(
				'SELECT id FROM ' . ContentTables::table('rubric_templates')
					. ' WHERE id=%i AND rubric_id=%i LIMIT 1',
				isset($existing['rubric_tmpl_id']) ? (int) $existing['rubric_tmpl_id'] : 0,
				$targetRubricId
			)->getValue();
			$moveInput = $existing;
			$moveInput['rubric_tmpl_id'] = $targetTemplateId > 0 ? $targetTemplateId : 0;
			$data = $this->normalizeData($moveInput, $existing, $targetRubric, true);
			$data['rubric_id'] = $targetRubricId;
			$targetFields = ComputedFieldEvaluator::apply($targetDefinitions, $targetFields, $data);
			DocumentSaveEvents::before('update', $source, $documentId, $targetRubricId, $actorId, $data, $targetFields, $existing);
			DocumentRubricCodeRunner::before($targetRubricId, $data, $targetFields, $documentId, $actorId, false, $source);
			$data = $this->normalizeData($data, $existing, $targetRubric, true);
			$data['rubric_id'] = $targetRubricId;
			$targetFields = ComputedFieldEvaluator::apply($targetDefinitions, $targetFields, $data);
			$errors = array_merge(
				$this->validate($data, $documentId, $targetRubricId),
				FieldValidator::validateValues(array_values($targetDefinitions), $targetFields, !empty($targetRubric['rubric_form_conditions']))
			);
			if ($errors) { throw new DocumentSaveRejected('Проверьте данные документа', $errors); }

			$previousDatabaseExceptionMode = DB::$throw_exception_on_error;
			DB::$throw_exception_on_error = true;
			$externalTransaction = DB::$transaction_in_progress;
			$ownsTransaction = !$externalTransaction;
			if ($ownsTransaction) { DB::startTransaction(); }
			try {
				$this->captureRevision($documentId, $actorId);
				$this->writeDocument($documentId, $data, $targetRubric, $actorId, $existing);
				DB::Delete(ContentTables::table('document_fields_text'), 'document_id=%i', $documentId);
				DB::Delete(ContentTables::table('document_fields'), 'document_id=%i', $documentId);
				\App\Content\Fields\DocumentRelationIndex::removeSource($documentId);
				DocumentRubricCodeRunner::after($targetRubricId, $data, $targetFields, $documentId, $actorId, false, $source);
				$data = $this->normalizeData($data, $this->document($documentId), $targetRubric, true);
				$data['rubric_id'] = $targetRubricId;
				$this->writeDocument($documentId, $data, $targetRubric, $actorId, $this->document($documentId), false);
				$this->writeFields($documentId, $data, $targetDefinitions, $targetFields);
				DocumentTerms::sync($documentId, $targetRubricId, $data['document_meta_keywords'], $data['document_tags']);
				DocumentSaveEvents::persisted('update', $source, $documentId, $targetRubricId, $actorId, $data, $targetFields, $existing);
				if ($ownsTransaction) { DB::commit(); }
			} catch (\Throwable $e) {
				if ($ownsTransaction) { DB::rollback(); }
				throw $e;
			} finally {
				DB::$throw_exception_on_error = $previousDatabaseExceptionMode;
			}

			$dispatch = function () use ($source, $documentId, $targetRubricId, $actorId, $data, $targetFields, $existing) {
				ContentCacheInvalidator::document($documentId, true);
				$snapshot = (new DocumentSnapshotRepository())->find($documentId);
				DocumentSaveEvents::after('update', $source, $documentId, $targetRubricId, $actorId, $data, $targetFields, $existing, is_array($snapshot) ? $snapshot : array());
			};
			if ($externalTransaction) { DB::afterCommit($dispatch); }
			else { $dispatch(); }

			return array(
				'id' => $documentId,
				'operation' => 'move',
				'moved' => true,
				'mapped_fields' => $mapped,
				'skipped_fields' => $skipped,
			);
		}

		/** Permanently remove a package-owned document and all core-owned derivatives. */
		public function hardDelete($documentId, $source = 'content_package')
		{
			$documentId = (int) $documentId;
			if ($documentId <= 2) {
				throw new \RuntimeException('Системные документы #1 и #2 удалять нельзя');
			}

			$document = DB::query('SELECT * FROM ' . ContentTables::table('documents') . ' WHERE Id = %i LIMIT 1', $documentId)->getAssoc();
			if (!$document) {
				return false;
			}

			$event = \App\Common\Lifecycle::event('content.document.deleting', 'document', 'deleting', $documentId, array(
				'document' => $document,
				'source' => (string) $source,
			), null, array('hard_delete' => true), (string) $source);
			if ($event->cancelled()) {
				throw new \RuntimeException($event->message() !== '' ? $event->message() : 'Удаление документа отменено модулем');
			}

			$ownsTransaction = !DB::$transaction_in_progress;
			if ($ownsTransaction) { DB::startTransaction(); }
			try {
				foreach (array(
					'document_fields_text' => 'document_id',
					'document_fields' => 'document_id',
					'document_rev' => 'doc_id',
					'document_alias_history' => 'document_id',
					'document_keywords' => 'document_id',
					'document_tags' => 'document_id',
					'document_remarks' => 'document_id',
					'view_count' => 'document_id',
				) as $suffix => $column) {
					DB::Delete(ContentTables::table($suffix), $column . ' = %i', $documentId);
				}

				DB::Delete(ContentTables::table('documents'), 'Id = %i', $documentId);
				\App\Content\Fields\DocumentRelationIndex::removeDocument($documentId);
				if ($ownsTransaction) { DB::commit(); }
			} catch (\Throwable $e) {
				if ($ownsTransaction) { DB::rollback(); }
				throw $e;
			}

			ContentCacheInvalidator::document($documentId, false);
			\App\Common\Lifecycle::event(
				'content.document.deleted',
				'document',
				'deleted',
				$documentId,
				array('source' => (string) $source),
				true,
				array('hard_delete' => true),
				(string) $source
			);
			return true;
		}

		public function export(array $snapshot)
		{
			$fields = array();
			foreach (isset($snapshot['fields']) && is_array($snapshot['fields']) ? $snapshot['fields'] : array() as $id => $field) {
				$alias = isset($field['alias']) && trim((string) $field['alias']) !== '' ? (string) $field['alias'] : (string) $id;
				$fields[$alias] = array('id' => (int) $id, 'type' => isset($field['type']) ? (string) $field['type'] : '', 'value' => isset($field['value']) ? $field['value'] : null);
				if (isset($field['relation']) && is_array($field['relation'])) { $fields[$alias]['relation'] = $field['relation']; }
			}

			return array(
				'format' => 'ave.document-api', 'version' => 1,
				'document' => isset($snapshot['document']) ? $snapshot['document'] : array(),
				'fields' => $fields,
				'revision' => isset($snapshot['document_revision']) ? (int) $snapshot['document_revision'] : 0,
				'generated_at' => isset($snapshot['generated_at']) ? (int) $snapshot['generated_at'] : time(),
			);
		}

		protected function document($id)
		{
			return DB::query("SELECT * FROM " . ContentTables::table('documents') . " WHERE Id=%i AND document_deleted='0' LIMIT 1", (int) $id)->getAssoc() ?: null;
		}

		protected function rubric($id)
		{
			return (int) $id > 0 ? (DB::query('SELECT * FROM ' . ContentTables::table('rubrics') . ' WHERE Id=%i LIMIT 1', (int) $id)->getAssoc() ?: null) : null;
		}

		protected function fieldDefinitions($rubricId)
		{
			$fieldsTable = ContentTables::table('rubric_fields');
			$groupsTable = ContentTables::table('rubric_fields_group');
			static $hasGroupSettings = null;
			if ($hasGroupSettings === null) {
				$hasGroupSettings = DatabaseSchema::columnExists($groupsTable, 'group_settings');
			}

			$groupSettings = $hasGroupSettings ? 'g.group_settings' : 'NULL AS group_settings';
			$rows = DB::query(
				'SELECT f.*, ' . $groupSettings . ' FROM ' . $fieldsTable . ' f'
				. ' LEFT JOIN ' . $groupsTable . ' g ON g.Id=f.rubric_field_group'
				. ' WHERE f.rubric_id=%i ORDER BY f.rubric_field_position,f.Id',
				(int) $rubricId
			)->getAll();
			$out = array();
			foreach ($rows ?: array() as $row) { $out[(int) $row['Id']] = $row; }
			return $out;
		}

		protected function fieldValues($documentId, array $definitions, $defaults)
		{
			$values = array();
			foreach ($definitions as $id => $field) { $values[$id] = $defaults ? (string) $field['rubric_field_default'] : ''; }
			if ((int) $documentId <= 0) { return $values; }
			$rows = DB::query('SELECT df.rubric_field_id,df.field_value,COALESCE(dft.field_value,\'\') more FROM ' . ContentTables::table('document_fields') . ' df'
				. ' LEFT JOIN ' . ContentTables::table('document_fields_text') . ' dft ON dft.document_id=df.document_id AND dft.rubric_field_id=df.rubric_field_id'
				. ' WHERE df.document_id=%i', (int) $documentId)->getAll();
			foreach ($rows ?: array() as $row) { $values[(int) $row['rubric_field_id']] = (string) $row['field_value'] . (string) $row['more']; }
			return $values;
		}

		protected function applyIncomingFields(array $incoming, array $definitions, array &$values)
		{
			$aliases = array();
			foreach ($definitions as $id => $field) { if (trim((string) $field['rubric_field_alias']) !== '') { $aliases[(string) $field['rubric_field_alias']] = $id; } }
			$errors = array();
			foreach ($incoming as $key => $value) {
				$id = ctype_digit((string) $key) ? (int) $key : (isset($aliases[(string) $key]) ? (int) $aliases[(string) $key] : 0);
				if ($id <= 0 || !isset($definitions[$id])) { $errors['fields.' . $key] = 'Поле не принадлежит выбранной рубрике'; continue; }
				$values[$id] = is_array($value) && array_key_exists('value', $value) ? $value['value'] : $value;
			}

			return $errors;
		}

		protected function conditionActionOverrides(array $incoming, array $definitions)
		{
			$aliases = array();
			foreach ($definitions as $id => $field) {
				$alias = trim((string) $field['rubric_field_alias']);
				if ($alias !== '') { $aliases[$alias] = (int) $id; }
			}

			$overrides = array();
			foreach ($incoming as $key => $value) {
				if (!is_array($value) || (empty($value['condition_action_override']) && empty($value['_condition_action_override']))) { continue; }
				$id = ctype_digit((string) $key) ? (int) $key : (isset($aliases[(string) $key]) ? (int) $aliases[(string) $key] : 0);
				if ($id > 0 && isset($definitions[$id])) { $overrides[$id] = true; }
			}

			return $overrides;
		}

		protected function normalizeData(array $payload, $existing, array $rubric, $legacyInput = false)
		{
			$base = is_array($existing) ? $existing : array();
			$value = function ($canonical, $legacy, $default = '') use ($payload, $base, $legacyInput) {
				if (array_key_exists($canonical, $payload)) { return $payload[$canonical]; }
				if (array_key_exists($legacy, $payload)) { return $payload[$legacy]; }
				$stored = array_key_exists($legacy, $base) ? $base[$legacy] : $default;
				return in_array($legacy, array('document_title', 'document_breadcrumb_title'), true)
					? htmlspecialchars_decode((string) $stored, ENT_QUOTES) : $stored;
			};
			if (array_key_exists('excerpt', $payload)) {
				$excerpt = $payload['excerpt'];
			} elseif (array_key_exists('document_excerpt', $payload)) {
				$excerpt = $payload['document_excerpt'];
			} elseif (array_key_exists('teaser', $payload)) {
				$excerpt = $payload['teaser'];
			} elseif (array_key_exists('document_teaser', $payload)) {
				$excerpt = $payload['document_teaser'];
			} elseif (array_key_exists('document_excerpt', $base)) {
				$excerpt = $base['document_excerpt'];
			} elseif (array_key_exists('document_teaser', $base)) {
				$excerpt = $base['document_teaser'];
			} else {
				$excerpt = '';
			}

			$published = $this->timestamp($value('published_at', 'document_published', time()), time());
			$status = $value('status', 'document_status', 0);
			if (is_string($status)) { $status = in_array(strtolower($status), array('published', 'active', '1', 'true'), true) ? 1 : 0; }
			$title = trim((string) $value('title', 'document_title', ''));
			$parentId = (int) $value('parent_id', 'document_parent', 0);
			$alias = DocumentAliasRegistry::normalize($value('alias', 'document_alias', ''));
			if (empty($base) && ($alias === '' || strpos($alias, '/') === false)) {
				$leaf = $alias !== '' ? $alias : Str::slug($title);
				$alias = DocumentAliasTemplate::compose(
					(string) $rubric['rubric_alias'],
					$leaf !== '' ? $leaf : 'document',
					$published,
					$this->parentAliasPath($parentId)
				);
			}

			return array(
				'rubric_id' => (int) $rubric['Id'], 'rubric_tmpl_id' => (int) $value('template_id', 'rubric_tmpl_id', 0),
				'document_parent' => $parentId, 'document_alias' => DocumentAliasRegistry::normalize($alias),
				'document_alias_header' => in_array((int) $value('redirect_code', 'document_alias_header', 301), array(301,302,307,308), true) ? (int) $value('redirect_code', 'document_alias_header', 301) : 301,
				'document_alias_history' => (string) max(0, min(2, (int) $value('alias_history', 'document_alias_history', (int) $rubric['rubric_alias_history']))),
				'document_short_alias' => substr(trim((string) $value('short_alias', 'document_short_alias', '')), 0, 10),
				'document_title' => $title, 'document_breadcrumb_title' => trim((string) $value('breadcrumb_title', 'document_breadcrumb_title', '')),
				'document_published' => $published, 'document_expire' => $this->timestamp($value('expire_at', 'document_expire', 0), 0),
					'document_author_id' => (int) $value('author_id', 'document_author_id', 1), 'document_in_search' => (string) $this->boolean($value('in_search', 'document_in_search', 1)),
					'document_is_technical' => $this->boolean($value('is_technical', 'document_is_technical', empty($base) && !empty($rubric['rubric_is_technical']) ? 1 : 0)),
				'document_meta_keywords' => trim((string) $value('meta_keywords', 'document_meta_keywords', '')),
				'document_meta_description' => trim((string) $value('meta_description', 'document_meta_description', '')),
				'document_meta_robots' => $this->robots($value('meta_robots', 'document_meta_robots', 'index,follow')),
					'document_sitemap_freq' => max(0, min(6, (int) $value('sitemap_frequency', 'document_sitemap_freq', 3))),
					'document_sitemap_pr' => max(0, min(1, (float) $value('sitemap_priority', 'document_sitemap_pr', 0.5))),
					'document_in_sitemap' => $this->boolean($value('in_sitemap', 'document_in_sitemap', 1)),
				'document_status' => (string) $this->boolean($status), 'document_linked_navi_id' => (int) $value('navigation_id', 'document_linked_navi_id', 0),
				'document_excerpt' => trim((string) $excerpt), 'document_tags' => is_array($value('tags', 'document_tags', '')) ? implode(',', $value('tags', 'document_tags', '')) : (string) $value('tags', 'document_tags', ''),
				'document_property' => (string) $value('property', 'document_property', ''), 'document_position' => (int) $value('position', 'document_position', 0),
				'guid' => substr(trim((string) $value('guid', 'guid', '')), 0, 100),
			);
		}

		protected function validate(array $data, $documentId, $rubricId)
		{
			$errors = array();
			if ($data['document_title'] === '') { $errors['title'] = 'Укажите название документа'; }
			if ($data['document_alias'] !== '' && !preg_match('/^[A-Za-z0-9_\-\.\/]+$/', $data['document_alias'])) { $errors['alias'] = 'Alias содержит недопустимые символы'; }
			elseif ($data['document_alias'] !== '' && DocumentAliasRegistry::conflict($data['document_alias'], (int) $documentId)) { $errors['alias'] = 'Такой alias уже используется'; }
			if ($data['document_short_alias'] !== '' && !preg_match('/^[A-Za-z0-9_\-]+$/', $data['document_short_alias'])) { $errors['short_alias'] = 'Короткий alias содержит недопустимые символы'; }
			elseif ($data['document_short_alias'] !== '' && DocumentAliasRegistry::conflict($data['document_short_alias'], (int) $documentId)) { $errors['short_alias'] = 'Такой короткий alias уже используется'; }
			return $errors;
		}

		protected function parentAliasPath($parentId)
		{
			$parentId = (int) $parentId;
			if ($parentId <= 0) { return ''; }

			return trim((string) DB::query(
				'SELECT document_alias FROM ' . ContentTables::table('documents')
					. " WHERE Id=%i AND document_deleted!='1' LIMIT 1",
				$parentId
			)->getValue(), '/');
		}

		protected function writeDocument($id, array $data, array $rubric, $actorId, $existing, $bumpVersion = true)
		{
			$now = time();
			$row = $data;
			$row['document_title'] = htmlspecialchars($data['document_title'], ENT_QUOTES);
			$row['document_breadcrumb_title'] = htmlspecialchars($data['document_breadcrumb_title'], ENT_QUOTES);
			$row['document_changed'] = $now;
			$row['document_version'] = is_array($existing)
				? max(1, isset($existing['document_version']) ? (int) $existing['document_version'] : 1) + ($bumpVersion ? 1 : 0)
				: 1;
			if ((int) $id > 0) {
				DB::Update(ContentTables::table('documents'), $row, 'Id=%i', (int) $id);
				if (is_array($existing) && !empty($existing['document_alias']) && (string) $existing['document_alias'] !== (string) $data['document_alias'] && (int) $data['document_alias_history'] > 0) {
					DB::Insert(ContentTables::table('document_alias_history'), array('document_id'=>(int)$id,'document_alias'=>(string)$existing['document_alias'],'document_alias_header'=>(int)$data['document_alias_header'],'document_alias_author'=>(int)$actorId,'document_alias_changed'=>$now));
				}

				return (int) $id;
			}

			if ((int) $row['rubric_tmpl_id'] <= 0) { $row['rubric_tmpl_id'] = 0; }
			if ((int) $row['document_position'] <= 0) { $row['document_position'] = (int) DB::query('SELECT COALESCE(MAX(document_position),0)+1 FROM ' . ContentTables::table('documents'))->getValue(); }
			$row['document_author_id'] = (int) $actorId > 0 ? (int) $actorId : max(1, (int) $row['document_author_id']);
			$row['document_deleted'] = '0'; $row['document_count_print'] = 0; $row['document_count_view'] = 0; $row['module_catalog'] = '';
			DB::Insert(ContentTables::table('documents'), $row);
			return (int) DB::insertId();
		}

		protected function writeFields($documentId, array $document, array $definitions, array $values)
		{
			foreach ($definitions as $id => $field) {
				$value = array_key_exists($id, $values) ? $values[$id] : '';
				$type = FieldRegistry::get((string) $field['rubric_field_type']);
				$value = FieldLifecycle::normalizeForStorage($value, $field, $document, 'document_mutation');
				$saving = FieldLifecycle::saving($value, $field, $documentId, 'document_mutation');
				if ($saving->cancelled()) { continue; }
				$value = $saving->result();
				$first = mb_substr((string) $value, 0, 500, 'UTF-8');
				$more = mb_substr((string) $value, 500, null, 'UTF-8');
				$isNumeric = !empty($field['rubric_field_numeric']) || ($type && $type->isNumeric());
				$numeric = $isNumeric
					? ((string) $field['rubric_field_type'] === 'period'
						? \App\Content\Fields\Types\PeriodValue::indexValue($value)
						: FieldValueCodec::numericIndexValue($value))
					: 0;
				$rowId = (int) DB::query('SELECT Id FROM ' . ContentTables::table('document_fields') . ' WHERE document_id=%i AND rubric_field_id=%i LIMIT 1', (int) $documentId, (int) $id)->getValue();
				$row = array('field_value'=>$first,'field_number_value'=>$numeric,'document_in_search'=>(int)$document['document_in_search'] && (int)$field['rubric_field_search'] ? '1' : '0');
				if ($rowId > 0) { DB::Update(ContentTables::table('document_fields'), $row, 'Id=%i', $rowId); }
				else { $row['document_id']=(int)$documentId; $row['rubric_field_id']=(int)$id; DB::Insert(ContentTables::table('document_fields'), $row); }
				$textId = (int) DB::query('SELECT Id FROM ' . ContentTables::table('document_fields_text') . ' WHERE document_id=%i AND rubric_field_id=%i LIMIT 1', (int)$documentId, (int)$id)->getValue();
				if ($more !== '') { if ($textId > 0) { DB::Update(ContentTables::table('document_fields_text'), array('field_value'=>$more), 'Id=%i', $textId); } else { DB::Insert(ContentTables::table('document_fields_text'), array('document_id'=>(int)$documentId,'rubric_field_id'=>(int)$id,'field_value'=>$more)); } }
				elseif ($textId > 0) { DB::Delete(ContentTables::table('document_fields_text'), 'Id=%i', $textId); }
				FieldLifecycle::saved($value, $field, $documentId, 'document_mutation');
			}
		}

		protected function captureRevision($documentId, $actorId)
		{
			$rows = DB::query('SELECT df.rubric_field_id,df.field_value,COALESCE(dft.field_value,\'\') more FROM ' . ContentTables::table('document_fields') . ' df LEFT JOIN ' . ContentTables::table('document_fields_text') . ' dft ON dft.document_id=df.document_id AND dft.rubric_field_id=df.rubric_field_id WHERE df.document_id=%i', (int)$documentId)->getAll();
			$values=array(); foreach($rows?:array() as $row){$values[(int)$row['rubric_field_id']]=(string)$row['field_value'].(string)$row['more'];}
			$document = $this->document($documentId);
			if (!$document) { return; }
			DB::Insert(ContentTables::table('document_rev'), array('doc_id'=>(int)$documentId,'doc_revision'=>time(),'doc_data'=>serialize(DocumentRevisionPayload::create($document, $values)),'user_id'=>(int)$actorId));
		}

		protected function pick(array $data, array $keys, $default) { foreach ($keys as $key) { if (array_key_exists($key, $data)) { return $data[$key]; } } return $default; }
		protected function boolean($value) { return in_array(strtolower(trim((string)$value)), array('1','true','yes','on','published','active'), true) ? 1 : 0; }
		protected function timestamp($value, $default) { if (is_int($value) || ctype_digit((string)$value)) { return max(0,(int)$value); } $time=strtotime((string)$value); return $time ? $time : (int)$default; }
		protected function robots($value) { return in_array((string)$value,array('index,follow','index,nofollow','noindex,nofollow'),true)?(string)$value:'index,follow'; }
	}
