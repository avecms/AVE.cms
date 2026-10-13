<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Documents/BulkEditor.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Adminx\Documents;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Common\AuditLog;
	use App\Common\DatabaseSchema;
	use App\Common\Lock;
	use App\Content\CatalogTables;
	use App\Content\ContentTables;
	use App\Content\Documents\DocumentMutationService;
	use App\Content\Documents\DocumentSearch;
	use App\Helpers\File;
	use DB;

	/**
	 * Server-side plans for safe, previewable document batch mutations.
	 *
	 * A plan freezes matching IDs before execution. The browser only sends the
	 * opaque token back and cannot replace the document list after preview.
	 */
	class BulkEditor
	{
		const VERSION = 2;
		const MAX_DOCUMENTS = 5000;
		const CHUNK_SIZE = 20;
		const EXPIRES_AFTER = 7200;

		protected static $documentFields = array(
			'document_title' => array(
				'label' => 'Заголовок документа',
				'payload' => 'title',
				'help' => 'Основной заголовок документа в AVE.cms. У товара название на сайте может браться из отдельного поля рубрики.',
			),
			'document_excerpt' => array('label' => 'Тизер (анонс)', 'payload' => 'excerpt'),
			'document_tags' => array('label' => 'Теги', 'payload' => 'tags'),
			'document_meta_keywords' => array('label' => 'Meta keywords', 'payload' => 'meta_keywords'),
			'document_meta_description' => array('label' => 'Meta description', 'payload' => 'meta_description'),
			'document_property' => array('label' => 'Свойство / артикул', 'payload' => 'property'),
		);

		protected static $stateOperations = array(
			'search_include' => array('column'=>'document_in_search','payload'=>'in_search','value'=>'1','before'=>array('0'=>'Вне поиска','1'=>'В поиске')),
			'search_exclude' => array('column'=>'document_in_search','payload'=>'in_search','value'=>'0','before'=>array('0'=>'Вне поиска','1'=>'В поиске')),
			'robots_index' => array('column'=>'document_meta_robots','payload'=>'meta_robots','value'=>'index,follow','before'=>array()),
			'robots_noindex' => array('column'=>'document_meta_robots','payload'=>'meta_robots','value'=>'noindex,nofollow','before'=>array()),
			'sitemap_include' => array('column'=>'document_in_sitemap','payload'=>'in_sitemap','value'=>'1','before'=>array('0'=>'Не добавляется','1'=>'Добавляется')),
			'sitemap_exclude' => array('column'=>'document_in_sitemap','payload'=>'in_sitemap','value'=>'0','before'=>array('0'=>'Не добавляется','1'=>'Добавляется')),
			'technical' => array('column'=>'document_is_technical','payload'=>'is_technical','value'=>'1','before'=>array('0'=>'Обычный','1'=>'Служебный')),
			'public' => array('column'=>'document_is_technical','payload'=>'is_technical','value'=>'0','before'=>array('0'=>'Обычный','1'=>'Служебный')),
			'sitemap_frequency' => array('column'=>'document_sitemap_freq','payload'=>'sitemap_frequency','value_input'=>'sitemap_frequency','before'=>array('0'=>'always','1'=>'hourly','2'=>'daily','3'=>'weekly','4'=>'monthly','5'=>'yearly','6'=>'never')),
			'sitemap_priority' => array('column'=>'document_sitemap_pr','payload'=>'sitemap_priority','value_input'=>'sitemap_priority','before'=>array()),
		);

		public static function options()
		{
			return array(
				'rubrics' => Model::rubrics(),
				'document_fields' => self::$documentFields,
				'products_available' => self::productsAvailable(),
				'product_descriptions_available' => self::productDescriptionsAvailable(),
			);
		}

		public static function preview(array $input, $actorId)
		{
			$filters = self::filters($input);
			$operation = self::operation($input, $filters);
			if ($operation['type'] === 'strip_description_styles') { $filters['scope'] = 'products'; }
			$matches = self::matches($filters);
			if (!$matches['ids']) {
				throw new \InvalidArgumentException('По выбранным условиям документы не найдены');
			}

			if ($matches['total'] > self::MAX_DOCUMENTS) {
				throw new \InvalidArgumentException(
					'Найдено ' . $matches['total'] . ' документов. Уточните фильтр: за один запуск можно обработать до ' . self::MAX_DOCUMENTS
				);
			}

			$matchedTotal = (int) $matches['total'];
			$changingRows = self::changingRows($matches['rows'], $operation);
			if (!$changingRows) {
				throw new \InvalidArgumentException('В найденных документах нет значений, которые изменит выбранное действие');
			}

			$sample = array();
			foreach (array_slice($changingRows, 0, 20) as $row) {
				$change = self::previewChange($row, $operation);
				$sample[] = array(
					'id' => (int) $row['Id'],
					'title' => html_entity_decode((string) $row['document_title'], ENT_QUOTES, 'UTF-8'),
					'rubric' => html_entity_decode((string) $row['rubric_title'], ENT_QUOTES, 'UTF-8'),
					'state' => (int) $row['document_status'] === 1 ? 'Опубликован' : 'Черновик',
					'before' => $operation['type'] === 'strip_description_styles' ? mb_substr($change['before'], 0, 1000, 'UTF-8') : self::shortValue($change['before']),
					'after' => $operation['type'] === 'strip_description_styles' ? mb_substr($change['after'], 0, 1000, 'UTF-8') : self::shortValue($change['after']),
					'changed' => $change['changed'],
					'note' => \App\Common\Language::translateSource($change['note']),
				);
			}

			$token = bin2hex(random_bytes(20));
			$plan = array(
				'version' => self::VERSION,
				'token' => $token,
				'actor_id' => (int) $actorId,
				'status' => 'prepared',
				'created_at' => time(),
				'expires_at' => time() + self::EXPIRES_AFTER,
				'filters' => $filters,
				'operation' => $operation,
				'ids' => array_values(array_map(function ($row) { return (int) $row['Id']; }, $changingRows)),
				'total' => count($changingRows),
				'matched_total' => $matchedTotal,
				'unchanged_total' => max(0, $matchedTotal - count($changingRows)),
				'cursor' => 0,
				'done' => 0,
				'skipped' => 0,
				'errors' => array(),
				'sample' => $sample,
			);
			self::save($plan);
			return self::publicPlan($plan);
		}

		public static function runChunk($token, $actorId)
		{
			return Lock::run('document-bulk:' . (string) $token, function () use ($token, $actorId) {
				$plan = self::load($token, $actorId);
				if (in_array($plan['status'], array('completed', 'cancelled'), true)) {
					return self::publicPlan($plan);
				}

				$plan['status'] = 'running';
				$service = new DocumentMutationService();
				$end = min((int) $plan['total'], (int) $plan['cursor'] + self::CHUNK_SIZE);
				while ((int) $plan['cursor'] < $end) {
					$id = (int) $plan['ids'][$plan['cursor']];
					try {
						$result = self::apply($service, $id, $plan['operation'], (int) $actorId);
						if ($result) { $plan['done']++; }
						else { $plan['skipped']++; }
					} catch (\Throwable $e) {
						$plan['errors'][] = '#' . $id . ': ' . $e->getMessage();
						if (count($plan['errors']) > 100) {
							$plan['errors'] = array_slice($plan['errors'], -100);
						}
					}

					$plan['cursor']++;
				}

				if ((int) $plan['cursor'] >= (int) $plan['total']) {
					$plan['status'] = 'completed';
					$plan['completed_at'] = time();
					AuditLog::record('document.bulk_editor_completed', array(
						'actor_id' => (int) $actorId,
						'target_type' => 'document_batch',
						'meta' => array(
							'operation' => $plan['operation'],
							'filters' => $plan['filters'],
							'total' => $plan['total'],
							'done' => $plan['done'],
							'skipped' => $plan['skipped'],
							'errors' => count($plan['errors']),
						),
					));
				}

				self::save($plan);
				return self::publicPlan($plan);
			}, 5.0, true);
		}

		public static function cancel($token, $actorId)
		{
			return Lock::run('document-bulk:' . (string) $token, function () use ($token, $actorId) {
				$plan = self::load($token, $actorId);
				if ($plan['status'] !== 'completed') {
					$plan['status'] = 'cancelled';
					$plan['cancelled_at'] = time();
					self::save($plan);
				}

				return self::publicPlan($plan);
			}, 5.0, true);
		}

		public static function status($token, $actorId)
		{
			return self::publicPlan(self::load($token, $actorId));
		}

		protected static function apply(DocumentMutationService $service, $documentId, array $operation, $actorId)
		{
			$row = DB::query(
				"SELECT * FROM " . ContentTables::table('documents') . " WHERE Id=%i AND document_deleted='0' LIMIT 1",
				(int) $documentId
			)->getAssoc();
			if (!$row) { return false; }

			$type = $operation['type'];
			if ($type === 'strip_description_styles') {
				if (!self::productDescriptionsAvailable()) { throw new \InvalidArgumentException('Нет доступа к описаниям товаров'); }
				$fieldId = self::productDescriptionField($row);
				if (!$fieldId) { return false; }
				$current = self::currentValue($row, array('target_type' => 'field', 'field_id' => $fieldId));
				$next = self::changedValue($current, $operation);
				if (!$next['changed']) { return false; }
				$service->patch($documentId, array('fields' => array($fieldId => $next['value'])), $actorId, 'bulk_editor');
				return true;
			}

			if (isset($operation['target_type']) && $operation['target_type'] === 'state') {
				if ($type === 'technical' && Model::isProtectedDocument($documentId)) { return false; }
				$current = (string) $row[$operation['target']];
				if ($current === (string) $operation['value']) { return false; }
				$service->save($documentId, array($operation['payload'] => $operation['value']), $actorId, 'bulk_editor');
				return true;
			}

			if ($type === 'publish' || $type === 'unpublish') {
				if (Model::isProtectedDocument($documentId)) { return false; }
				$status = $type === 'publish' ? 1 : 0;
				if ((int) $row['document_status'] === $status) { return false; }
				$service->save($documentId, array('status' => $status), $actorId, 'bulk_editor');
				return true;
			}

			if ($type === 'recalculate') {
				$service->save($documentId, array('fields' => array()), $actorId, 'bulk_editor');
				return true;
			}

			if ($type === 'move') {
				if (Model::isProtectedDocument($documentId)) { return false; }
				$result = $service->move($documentId, $operation['target_rubric_id'], $actorId, 'bulk_editor');
				return !empty($result['moved']);
			}

			$current = self::currentValue($row, $operation);
			$next = self::changedValue($current, $operation);
			if (!$next['changed']) { return false; }
			if ($operation['target_type'] === 'document') {
				$payloadKey = self::$documentFields[$operation['target']]['payload'];
				$service->save($documentId, array($payloadKey => $next['value']), $actorId, 'bulk_editor');
			} else {
				$service->save($documentId, array('fields' => array($operation['field_id'] => $next['value'])), $actorId, 'bulk_editor');
			}

			return true;
		}

		protected static function filters(array $input)
		{
			$scope = isset($input['scope']) ? (string) $input['scope'] : 'all';
			if (!in_array($scope, array('all', 'documents', 'products'), true) || ($scope !== 'all' && !self::productsAvailable())) {
				$scope = 'all';
			}

			$state = isset($input['state']) ? (string) $input['state'] : '';
			if (!in_array($state, array('', 'active', 'draft'), true)) { $state = ''; }
			return array(
				'q' => trim(isset($input['q']) ? (string) $input['q'] : ''),
				'rubric_id' => max(0, isset($input['rubric_id']) ? (int) $input['rubric_id'] : 0),
				'state' => $state,
				'scope' => $scope,
			);
		}

		protected static function operation(array $input, array $filters)
		{
			$type = isset($input['operation']) ? (string) $input['operation'] : '';
			$allowed = array_merge(
				array('fill', 'set', 'clear', 'replace', 'move', 'publish', 'unpublish', 'recalculate', 'strip_description_styles'),
				array_keys(self::$stateOperations)
			);
			if (!in_array($type, $allowed, true)) {
				throw new \InvalidArgumentException('Выберите действие');
			}

			$operation = array('type' => $type, 'label' => self::operationLabel($type));
			if ($type === 'strip_description_styles') {
				if (!self::productDescriptionsAvailable()) { throw new \InvalidArgumentException('Нет доступа к описаниям товаров'); }
				return array_merge($operation, array('target_type' => 'product_description', 'target_label' => 'Описание товара'));
			}

			if (isset(self::$stateOperations[$type])) {
				$config = self::$stateOperations[$type];
				$value = isset($config['value']) ? (string) $config['value'] : trim(isset($input[$config['value_input']]) ? (string) $input[$config['value_input']] : '');
				if ($type === 'sitemap_frequency' && !array_key_exists($value, $config['before'])) {
					throw new \InvalidArgumentException('Выберите частоту обновления sitemap');
				}

				if ($type === 'sitemap_priority' && (!is_numeric($value) || (float) $value < 0 || (float) $value > 1)) {
					throw new \InvalidArgumentException('Приоритет sitemap должен быть от 0 до 1');
				}

				return array_merge($operation, $config, array(
					'target_type' => 'state', 'target' => $config['column'], 'value' => $value,
					'target_label' => self::operationTargetLabel($type),
				));
			}

			if (in_array($type, array('move', 'publish', 'unpublish', 'recalculate'), true)) {
				if ($type === 'move') {
					$targetRubricId = isset($input['target_rubric_id']) ? (int) $input['target_rubric_id'] : 0;
					if ($targetRubricId <= 0 || !Model::rubric($targetRubricId)) {
						throw new \InvalidArgumentException('Выберите целевую рубрику');
					}

					if ($filters['rubric_id'] > 0 && $targetRubricId === $filters['rubric_id']) {
						throw new \InvalidArgumentException('Исходная и целевая рубрики совпадают');
					}

					$operation['target_rubric_id'] = $targetRubricId;
					$operation['target_rubric_title'] = Model::rubric($targetRubricId)['rubric_title'];
				}

				return $operation;
			}

			$target = isset($input['target']) ? trim((string) $input['target']) : '';
			if (isset(self::$documentFields[$target])) {
				$operation['target_type'] = 'document';
				$operation['target'] = $target;
				$operation['target_label'] = self::$documentFields[$target]['label'];
			} elseif (strpos($target, 'field:') === 0) {
				$fieldId = (int) substr($target, 6);
				if ($filters['rubric_id'] <= 0) {
					throw new \InvalidArgumentException('Для изменения поля сначала выберите одну рубрику');
				}

				$field = self::field($fieldId, $filters['rubric_id']);
				if (!$field) { throw new \InvalidArgumentException('Поле не принадлежит выбранной рубрике'); }
				$operation['target_type'] = 'field';
				$operation['target'] = $target;
				$operation['field_id'] = $fieldId;
				$operation['target_label'] = html_entity_decode((string) $field['rubric_field_title'], ENT_QUOTES, 'UTF-8');
			} else {
				throw new \InvalidArgumentException('Выберите поле для изменения');
			}

			$operation['value'] = isset($input['value']) ? (string) $input['value'] : '';
			$operation['search'] = isset($input['search']) ? (string) $input['search'] : '';
			if ($type === 'replace' && $operation['search'] === '') {
				throw new \InvalidArgumentException('Укажите текст, который нужно заменить');
			}

			return $operation;
		}

		protected static function matches(array $filters)
		{
			$where = " WHERE d.document_deleted='0'";
			$args = array();
			if ($filters['rubric_id'] > 0) {
				$where .= ' AND d.rubric_id=%i';
				$args[] = $filters['rubric_id'];
			}

			if ($filters['state'] === 'active') { $where .= " AND d.document_status='1'"; }
			elseif ($filters['state'] === 'draft') { $where .= " AND d.document_status='0'"; }

			$productTable = CatalogTables::table('catalog_product_index');
			if (self::productsAvailable() && $filters['scope'] === 'products') {
				$where .= ' AND EXISTS (SELECT 1 FROM ' . $productTable . ' bp WHERE bp.product_id=d.Id)';
			} elseif (self::productsAvailable() && $filters['scope'] === 'documents') {
				$where .= ' AND NOT EXISTS (SELECT 1 FROM ' . $productTable . ' bp WHERE bp.product_id=d.Id)';
			}

			$search = DocumentSearch::criteria($filters['q'], 'd');
			if ($search['where'] !== '') {
				$where .= ' AND ' . $search['where'];
				$args = array_merge($args, $search['where_args']);
			}

			$sql = 'SELECT d.Id,d.document_title,d.document_status,d.rubric_id,r.rubric_title'
				. ' FROM ' . ContentTables::table('documents') . ' d'
				. ' LEFT JOIN ' . ContentTables::table('rubrics') . ' r ON r.Id=d.rubric_id'
				. $where . ' ORDER BY d.Id ASC LIMIT ' . (self::MAX_DOCUMENTS + 1);
			$rows = call_user_func_array(array('DB', 'query'), array_merge(array($sql), $args))->getAll() ?: array();
			$ids = array();
			foreach ($rows as $row) { $ids[] = (int) $row['Id']; }
			return array('ids' => $ids, 'rows' => $rows, 'total' => count($rows));
		}

		protected static function changingRows(array $rows, array $operation)
		{
			if (!$rows || $operation['type'] === 'recalculate') { return $rows; }

			$currentValues = array();
			$ids = array();
			foreach ($rows as $row) { $ids[] = (int) $row['Id']; }
			if (isset($operation['target_type']) && in_array($operation['target_type'], array('document', 'state'), true)) {
				$column = (string) $operation['target'];
				if ($operation['target_type'] === 'document' && !isset(self::$documentFields[$column])) {
					throw new \InvalidArgumentException('Поле документа недоступно для массового изменения');
				}

				$valueRows = DB::query(
					'SELECT Id,`' . $column . '` current_value FROM ' . ContentTables::table('documents') . ' WHERE Id IN %li',
					$ids
				)->getAll();
				foreach ($valueRows ?: array() as $valueRow) {
					$currentValues[(int) $valueRow['Id']] = html_entity_decode((string) $valueRow['current_value'], ENT_QUOTES, 'UTF-8');
				}
			} elseif (isset($operation['target_type']) && $operation['target_type'] === 'product_description') {
				foreach (array_chunk($ids, 500) as $chunk) {
					$valueRows = DB::query('SELECT p.product_id,' . \App\Modules\Products\ProductRoles::valueSql('description')
						. ' current_value FROM ' . CatalogTables::table('catalog_product_index') . ' p WHERE p.product_id IN %li AND p.is_deleted=0', $chunk)->getAll();
					foreach ($valueRows ?: array() as $valueRow) { $currentValues[(int) $valueRow['product_id']] = (string) $valueRow['current_value']; }
				}
			} elseif (isset($operation['target_type']) && $operation['target_type'] === 'field') {
				$valueRows = DB::query(
					'SELECT df.document_id,CONCAT(COALESCE(df.field_value,\'\'),COALESCE(dft.field_value,\'\')) current_value'
						. ' FROM ' . ContentTables::table('document_fields') . ' df'
						. ' LEFT JOIN ' . ContentTables::table('document_fields_text') . ' dft'
						. ' ON dft.document_id=df.document_id AND dft.rubric_field_id=df.rubric_field_id'
						. ' WHERE df.document_id IN %li AND df.rubric_field_id=%i',
					$ids,
					(int) $operation['field_id']
				)->getAll();
				foreach ($valueRows ?: array() as $valueRow) {
					$currentValues[(int) $valueRow['document_id']] = (string) $valueRow['current_value'];
				}
			}

			$changing = array();
			foreach ($rows as $row) {
				if (isset($operation['target_type'])) {
					$row['__bulk_current'] = isset($currentValues[(int) $row['Id']])
						? $currentValues[(int) $row['Id']]
						: '';
				}

				if (self::previewChange($row, $operation)['changed']) { $changing[] = $row; }
			}

			return $changing;
		}

		protected static function previewChange(array $row, array $operation)
		{
			if ($operation['type'] === 'publish') {
				return array('before' => (int) $row['document_status'] ? 'Опубликован' : 'Черновик', 'after' => 'Опубликован', 'changed' => !(int) $row['document_status'], 'note' => '');
			}

			if ($operation['type'] === 'unpublish') {
				return array('before' => (int) $row['document_status'] ? 'Опубликован' : 'Черновик', 'after' => 'Черновик', 'changed' => (bool) $row['document_status'], 'note' => '');
			}

			if ($operation['type'] === 'recalculate') {
				return array('before' => 'Текущие значения', 'after' => 'Пересчитать поля и индексы', 'changed' => true, 'note' => '');
			}

			if ($operation['type'] === 'move') {
				return array('before' => (string) $row['rubric_title'], 'after' => (string) $operation['target_rubric_title'], 'changed' => (int) $row['rubric_id'] !== (int) $operation['target_rubric_id'], 'note' => 'Переносятся только поля с одинаковыми системными именами и типами. Значения остальных полей не сохранятся в новой рубрике.');
			}

			if ($operation['target_type'] === 'state') {
				if ($operation['type'] === 'technical' && Model::isProtectedDocument((int) $row['Id'])) {
					return array('before'=>'Системный документ','after'=>'Без изменений','changed'=>false,'note'=>'Главную и страницу 404 нельзя сделать служебными');
				}

				$current = self::currentValue($row, $operation);
				$before = isset($operation['before'][$current]) ? $operation['before'][$current] : $current;
				$after = isset($operation['before'][(string) $operation['value']]) ? $operation['before'][(string) $operation['value']] : (string) $operation['value'];
				return array('before'=>$before,'after'=>$after,'changed'=>$current !== (string) $operation['value'],'note'=>'');
			}

			$current = self::currentValue($row, $operation);
			$next = self::changedValue($current, $operation);
			return array('before' => $current, 'after' => $next['value'], 'changed' => $next['changed'], 'note' => isset($next['removed']) ? 'Удалится атрибутов style: ' . $next['removed'] : ($next['changed'] ? '' : 'Значение уже соответствует действию'));
		}

		protected static function currentValue(array $row, array $operation)
		{
			if (array_key_exists('__bulk_current', $row)) {
				return (string) $row['__bulk_current'];
			}

			if ($operation['target_type'] === 'product_description') {
				$fieldId = self::productDescriptionField($row);
				return $fieldId ? self::currentValue($row, array('target_type' => 'field', 'field_id' => $fieldId)) : '';
			}

			if ($operation['target_type'] === 'document' || $operation['target_type'] === 'state') {
				if (!array_key_exists($operation['target'], $row)) {
					$value = DB::query(
						'SELECT `' . $operation['target'] . '` FROM ' . ContentTables::table('documents') . ' WHERE Id=%i',
						(int) $row['Id']
					)->getValue();
					return html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');
				}

				return html_entity_decode((string) $row[$operation['target']], ENT_QUOTES, 'UTF-8');
			}

			return (string) DB::query(
				'SELECT CONCAT(COALESCE(df.field_value,\'\'),COALESCE(dft.field_value,\'\'))'
					. ' FROM ' . ContentTables::table('document_fields') . ' df'
					. ' LEFT JOIN ' . ContentTables::table('document_fields_text') . ' dft'
					. ' ON dft.document_id=df.document_id AND dft.rubric_field_id=df.rubric_field_id'
					. ' WHERE df.document_id=%i AND df.rubric_field_id=%i LIMIT 1',
				(int) $row['Id'],
				(int) $operation['field_id']
			)->getValue();
		}

		protected static function changedValue($current, array $operation)
		{
			$current = (string) $current;
			$type = $operation['type'];
			if ($type === 'strip_description_styles') {
				$result = \App\Helpers\Html::removeInlineStyles($current);
				return array('value' => $result['html'], 'changed' => $result['removed'] > 0, 'removed' => $result['removed']);
			}

			if ($type === 'fill') {
				$value = trim($current) === '' ? (string) $operation['value'] : $current;
			} elseif ($type === 'clear') {
				$value = '';
			} elseif ($type === 'replace') {
				$value = str_replace((string) $operation['search'], (string) $operation['value'], $current);
			} else {
				$value = (string) $operation['value'];
			}

			return array('value' => $value, 'changed' => $value !== $current);
		}

		protected static function field($fieldId, $rubricId)
		{
			return DB::query(
				'SELECT Id,rubric_id,rubric_field_title,rubric_field_alias,rubric_field_type'
					. ' FROM ' . ContentTables::table('rubric_fields') . ' WHERE Id=%i AND rubric_id=%i LIMIT 1',
				(int) $fieldId,
				(int) $rubricId
			)->getAssoc() ?: null;
		}

		public static function fields($rubricId)
		{
			$commerce = self::commerceFieldUsage((int) $rubricId);
			$rows = DB::query(
				'SELECT Id,rubric_field_title,rubric_field_alias,rubric_field_type'
					. ' FROM ' . ContentTables::table('rubric_fields')
					. ' WHERE rubric_id=%i ORDER BY rubric_field_position ASC,Id ASC',
				(int) $rubricId
			)->getAll();
			$out = array();
			foreach ($rows ?: array() as $row) {
				$fieldId = (int) $row['Id'];
				$usage = isset($commerce[$fieldId]) ? $commerce[$fieldId] : '';
				$out[] = array(
					'id' => $fieldId,
					'title' => html_entity_decode((string) $row['rubric_field_title'], ENT_QUOTES, 'UTF-8'),
					'alias' => (string) $row['rubric_field_alias'],
					'type' => (string) $row['rubric_field_type'],
					'usage' => $usage,
					'help' => $usage !== '' ? 'Это поле используется товарным представлением как ' . $usage . '. Изменение обновит витрину и товарный индекс.' : '',
				);
			}

			return $out;
		}

		protected static function commerceFieldUsage($rubricId)
		{
			if ($rubricId <= 0 || !self::productsAvailable()) { return array(); }

			$row = DB::query(
				'SELECT product_title_field_id,product_article_field_id,product_price_field_id,'
					. 'product_old_price_field_id,product_stock_field_id,product_images_field_id'
					. ' FROM ' . CatalogTables::table('module_catalog_settings')
					. " WHERE rubric_id=%i AND purpose='commerce' ORDER BY id ASC LIMIT 1",
				(int) $rubricId
			)->getAssoc();
			if (!$row) { return array(); }

			$labels = array(
				'product_title_field_id' => 'название товара на сайте',
				'product_article_field_id' => 'артикул товара',
				'product_price_field_id' => 'текущая цена',
				'product_old_price_field_id' => 'старая цена',
				'product_stock_field_id' => 'остаток / наличие',
				'product_images_field_id' => 'изображения товара',
			);
			$usage = array();
			foreach ($labels as $column => $label) {
				$fieldId = isset($row[$column]) ? (int) $row[$column] : 0;
				if ($fieldId > 0) { $usage[$fieldId] = $label; }
			}

			return $usage;
		}

		protected static function productsAvailable()
		{
			try {
				return DatabaseSchema::tableExists(CatalogTables::table('catalog_product_index'));
			} catch (\Throwable $e) {
				return false;
			}
		}

		protected static function productDescriptionsAvailable()
		{
			$module = \App\Common\ModuleManager::get('products');
			return $module && !empty($module['enabled']) && \App\Common\Permission::check('manage_products')
				&& class_exists('App\\Modules\\Products\\ProductRoles') && self::productsAvailable();
		}

		protected static function productDescriptionField(array $row)
		{
			$product = DB::query('SELECT rubric_id FROM ' . CatalogTables::table('catalog_product_index')
				. ' WHERE product_id=%i AND is_deleted=0', (int) $row['Id'])->getAssoc();
			if (!$product || (int) $product['rubric_id'] !== (int) $row['rubric_id']) { return 0; }
			$roles = \App\Modules\Products\ProductRoles::forRubric((int) $row['rubric_id']);
			return isset($roles['description']) ? (int) $roles['description'] : 0;
		}

		protected static function operationLabel($type)
		{
			$labels = array(
				'fill' => 'Заполнить пустые', 'set' => 'Установить значение', 'clear' => 'Очистить',
				'strip_description_styles' => 'Удалить стили из описаний товаров',
				'replace' => 'Найти и заменить', 'move' => 'Перенести в рубрику',
					'publish' => 'Опубликовать', 'unpublish' => 'Снять с публикации',
					'recalculate' => 'Пересчитать поля и индексы',
					'search_include' => 'Включить во внутренний поиск', 'search_exclude' => 'Исключить из внутреннего поиска',
					'robots_index' => 'Разрешить индексацию поисковиками', 'robots_noindex' => 'Запретить индексацию поисковиками',
					'sitemap_include' => 'Добавить в sitemap', 'sitemap_exclude' => 'Исключить из sitemap',
					'technical' => 'Сделать служебными', 'public' => 'Сделать обычными',
					'sitemap_frequency' => 'Изменить частоту sitemap', 'sitemap_priority' => 'Изменить приоритет sitemap',
				);
			return isset($labels[$type]) ? $labels[$type] : $type;
		}

		protected static function operationTargetLabel($type)
		{
			$labels = array(
				'search_include'=>'Внутренний поиск','search_exclude'=>'Внутренний поиск',
				'robots_index'=>'Meta robots','robots_noindex'=>'Meta robots',
				'sitemap_include'=>'Участие в sitemap','sitemap_exclude'=>'Участие в sitemap',
				'technical'=>'Публичный доступ','public'=>'Публичный доступ',
				'sitemap_frequency'=>'Частота sitemap','sitemap_priority'=>'Приоритет sitemap',
			);
			return isset($labels[$type]) ? $labels[$type] : '';
		}

		protected static function shortValue($value)
		{
			$value = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $value)));
			if ($value === '') { return 'Пусто'; }
			return mb_strlen($value, 'UTF-8') > 180 ? mb_substr($value, 0, 177, 'UTF-8') . '...' : $value;
		}

		protected static function publicPlan(array $plan)
		{
			$total = max(1, (int) $plan['total']);
			return array(
				'token' => $plan['token'],
				'status' => $plan['status'],
				'total' => (int) $plan['total'],
				'matched_total' => isset($plan['matched_total']) ? (int) $plan['matched_total'] : (int) $plan['total'],
				'unchanged_total' => isset($plan['unchanged_total']) ? (int) $plan['unchanged_total'] : 0,
				'processed' => (int) $plan['cursor'],
				'done' => (int) $plan['done'],
				'skipped' => (int) $plan['skipped'],
				'errors' => $plan['errors'],
				'progress' => (int) floor(((int) $plan['cursor'] / $total) * 100),
				'operation' => $plan['operation'],
				'filters' => $plan['filters'],
				'sample' => $plan['sample'],
			);
		}

		protected static function load($token, $actorId)
		{
			if (!preg_match('/^[a-f0-9]{40}$/', (string) $token)) {
				throw new \InvalidArgumentException('Некорректный идентификатор плана');
			}

			$path = self::path($token);
			$data = is_file($path) ? json_decode((string) File::getContent($path), true) : null;
			if (!is_array($data) || (int) $data['version'] !== self::VERSION || !hash_equals((string) $data['token'], (string) $token)) {
				throw new \RuntimeException('План массового изменения не найден');
			}

			if ((int) $data['actor_id'] !== (int) $actorId) {
				throw new \RuntimeException('План создан другим пользователем');
			}

			if ((int) $data['expires_at'] < time() && !in_array($data['status'], array('completed', 'cancelled'), true)) {
				throw new \RuntimeException('Срок действия предпросмотра истёк. Выполните проверку ещё раз');
			}

			return $data;
		}

		protected static function save(array $plan)
		{
			$directory = dirname(self::path($plan['token']));
			if (!is_dir($directory) && !@mkdir($directory, 0750, true) && !is_dir($directory)) {
				throw new \RuntimeException('Не удалось создать каталог заданий');
			}

			$raw = json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
			if (!File::putAtomic(self::path($plan['token']), $raw, 0640)) {
				throw new \RuntimeException('Не удалось сохранить план массового изменения');
			}
		}

		protected static function path($token)
		{
			return rtrim(BASEPATH, '/\\') . '/storage/jobs/document-bulk/' . (string) $token . '.json';
		}
	}
