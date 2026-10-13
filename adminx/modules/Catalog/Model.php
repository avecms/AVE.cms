<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Catalog/Model.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Adminx\Catalog;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use DB;
	use App\Common\AdminLocation;
	use App\Common\Cache;
	use App\Common\CacheKey;
	use App\Common\DatabaseSchema;
	use App\Common\FileCacheInvalidator;
	use App\Common\ModuleManager;
	use App\Common\ModuleSettings;
	use App\Common\Registry;
	use App\Modules\Products\ProductRoles;
	use App\Content\CatalogTables;
	use App\Content\ContentTables;
	use App\Content\Fields\FieldValueCodec;
	use App\Content\Requests\RequestConditionValue;
	use App\Frontend\Feeds\Service as FeedService;
	use App\Helpers\Json;
	use App\Adminx\Rubrics\Model as RubricsModel;
	use App\Adminx\Documents\Model as DocumentsModel;
	use App\Adminx\Packages\Products\ProductIndexer;
	use App\Adminx\Packages\Products\Attributes;

	class Model
	{
		public static function settingsTable() { return CatalogTables::table('module_catalog_settings'); }
		public static function itemsTable() { return CatalogTables::table('module_catalog_items'); }
		public static function fieldsTable() { return ContentTables::table('rubric_fields'); }
		public static function groupsTable() { return ContentTables::table('rubric_fields_group'); }
		public static function rubricsTable() { return ContentTables::table('rubrics'); }
		public static function documentsTable() { return ContentTables::table('documents'); }

		public static function catalogs()
		{
			$rows = DB::query(
				'SELECT f.Id AS field_id, f.rubric_id, f.rubric_field_title, f.rubric_field_alias,'
				. ' r.rubric_title, s.*, COUNT(i.id) AS items_count,'
				. ' SUM(CASE WHEN i.status = 1 THEN 1 ELSE 0 END) AS active_count'
				. ' FROM ' . self::fieldsTable() . ' f'
				. ' INNER JOIN ' . self::rubricsTable() . ' r ON r.Id = f.rubric_id'
				. ' LEFT JOIN ' . self::settingsTable() . ' s ON s.rubric_id = f.rubric_id AND s.field_id = f.Id'
				. ' LEFT JOIN ' . self::itemsTable() . ' i ON i.rubric_id = f.rubric_id AND i.field_id = f.Id'
				. " WHERE f.rubric_field_type = 'catalog'"
				. ' GROUP BY f.Id ORDER BY r.rubric_title ASC, f.rubric_field_position ASC, f.Id ASC'
			)->getAll();
			$out = array();
			foreach ($rows as $row) {
				$out[] = self::catalogRow($row);
			}

			return $out;
		}

		public static function hasCommerceCatalogs()
		{
			return (int) DB::query("SELECT COUNT(*) FROM " . self::settingsTable() . " WHERE purpose='commerce'")->getValue() > 0;
		}

		public static function createCatalog(array $input)
		{
			$rubricId = isset($input['rubric_id']) ? (int) $input['rubric_id'] : 0;
			$title = trim(isset($input['title']) ? (string) $input['title'] : '');
			$alias = strtolower(trim(isset($input['alias']) ? (string) $input['alias'] : ''));
			$alias = trim(preg_replace('/[^a-z0-9_]+/', '_', $alias), '_');
			$purpose = isset($input['purpose']) && $input['purpose'] === 'commerce' ? 'commerce' : 'content';
			if ($purpose === 'commerce' && !self::productsAvailable()) {
				throw new \InvalidArgumentException('Сначала установите и включите модуль «Товары»');
			}

			if ($rubricId < 1 || !DB::query('SELECT Id FROM ' . self::rubricsTable() . ' WHERE Id=%i LIMIT 1', $rubricId)->getValue()) { throw new \InvalidArgumentException('Выберите рубрику'); }
			if ($title === '') { throw new \InvalidArgumentException('Укажите название каталога'); }
			if ($alias === '') { throw new \InvalidArgumentException('Укажите alias латиницей'); }
			if (strlen($alias) > 20) { throw new \InvalidArgumentException('Alias каталога должен быть не длиннее 20 символов'); }
			if (DB::query('SELECT Id FROM ' . self::fieldsTable() . ' WHERE rubric_id=%i AND rubric_field_alias=%s LIMIT 1', $rubricId, $alias)->getValue()) { throw new \InvalidArgumentException('Такой alias уже используется в рубрике'); }
			$fieldId = RubricsModel::saveField(0, $rubricId, array('rubric_field_title'=>$title,'rubric_field_alias'=>$alias,'rubric_field_type'=>'catalog','rubric_field_description'=>'Структура каталога «'.$title.'»'));
			try {
				self::saveSettings($rubricId, $fieldId, array('purpose'=>$purpose,'recursive'=>1,'doc_parent'=>1,'item_parent'=>1,'sort_parent'=>1));
			} catch (\Throwable $e) {
				DB::Delete(self::settingsTable(), 'rubric_id=%i AND field_id=%i', $rubricId, $fieldId);
				RubricsModel::deleteField($fieldId);
				throw $e;
			}

			return array('rubric_id'=>$rubricId,'field_id'=>$fieldId,'purpose'=>$purpose);
		}

		public static function productsAvailable()
		{
			$module = ModuleManager::get('products');
			return is_array($module)
				&& !empty($module['installed'])
				&& !empty($module['enabled'])
				&& is_file(BASEPATH . '/modules/products/app/module.php')
				&& is_file(BASEPATH . '/modules/products/admin/module.php');
		}

		public static function catalog($rubricId, $fieldId)
		{
			foreach (self::catalogs() as $catalog) {
				if ($catalog['rubric_id'] === (int) $rubricId && $catalog['field_id'] === (int) $fieldId) {
					return $catalog;
				}
			}

			return null;
		}

		public static function settings($rubricId, $fieldId)
		{
			$row = DB::query(
				'SELECT * FROM ' . self::settingsTable() . ' WHERE rubric_id = %i AND field_id = %i LIMIT 1',
				(int) $rubricId,
				(int) $fieldId
			)->getAssoc();
			$defaults = array(
				'id' => 0, 'rubric_id' => (int) $rubricId, 'field_id' => (int) $fieldId,
				'purpose' => 'content',
				'product_title_field_id'=>0,'product_article_field_id'=>0,'product_price_field_id'=>0,'product_old_price_field_id'=>0,'product_stock_field_id'=>0,'product_images_field_id'=>0,'product_card_template_id'=>0,
				'filter_template_id'=>0,'filter_template_mode'=>'legacy','filter_template_settings'=>'',
				'navi_id' => 0, 'request_id' => 0, 'doc_fileds' => 0, 'recursive' => 1,
				'doc_parent' => 1, 'item_parent' => 1, 'sort_parent' => 1, 'save_names' => 0,
				'filters_use' => 0, 'rub_cat_id' => 0, 'fields_default' => '',
				'filters_default' => '', 'filters_default_settings' => '',
			);
			$settings = array_merge($defaults, $row ?: array());
			$card = !empty($settings['product_card_settings'])
				? Json::toArray((string) $settings['product_card_settings'])
				: array();
			$settings['product_card'] = self::productCardSettings(is_array($card) ? $card : array());
			$filterPresentation = !empty($settings['filter_template_settings'])
				? Json::toArray((string) $settings['filter_template_settings'])
				: array();
			$settings['filter_template'] = self::filterTemplateSettings(is_array($filterPresentation) ? $filterPresentation : array());
			return $settings;
		}

		public static function saveSettings($rubricId, $fieldId, array $input)
		{
			if (DB::$transaction_in_progress) {
				throw new \LogicException('Catalog settings must be saved outside an existing transaction');
			}

			$throwOnError = DB::$throw_exception_on_error;
			DB::$throw_exception_on_error = true;
			try { return self::saveSettingsOwned($rubricId, $fieldId, $input); }
			finally { DB::$throw_exception_on_error = $throwOnError; }
		}

		protected static function saveSettingsOwned($rubricId, $fieldId, array $input)
		{
			self::requireCatalog($rubricId, $fieldId);
			$existing = self::settings($rubricId, $fieldId);
			$data = array();
			$data['purpose'] = isset($input['purpose']) && $input['purpose'] === 'commerce' ? 'commerce' : 'content';
			if ($data['purpose'] === 'commerce' && !self::productsAvailable()) {
				throw new \InvalidArgumentException('Сначала установите и включите модуль «Товары»');
			}

			if ($data['purpose'] === 'commerce' && DB::query(
				"SELECT id FROM " . self::settingsTable() . " WHERE rubric_id=%i AND field_id!=%i AND purpose='commerce' LIMIT 1",
				(int) $rubricId,
				(int) $fieldId
			)->getValue()) {
				throw new \InvalidArgumentException('У рубрики уже есть товарный каталог');
			}

			foreach (array('product_title_field_id','product_article_field_id','product_price_field_id','product_old_price_field_id','product_stock_field_id','product_images_field_id') as $key) {
				$data[$key] = isset($input[$key]) ? max(0, (int) $input[$key]) : (int) $existing[$key];
				if (isset($input[$key]) && $data[$key] && !DB::query('SELECT Id FROM ' . self::fieldsTable() . ' WHERE Id=%i AND rubric_id=%i LIMIT 1', $data[$key], (int) $rubricId)->getValue()) {
					throw new \InvalidArgumentException('Коммерческое поле не принадлежит рубрике');
				}
			}

			$roleInput = null;
			if ($data['purpose'] === 'commerce' && isset($input['product_roles']) && is_array($input['product_roles'])) {
				$roleFields = ProductRoles::fields($rubricId);
				$roleInput = array_replace(ProductRoles::configured($rubricId), ProductRoles::validate($input['product_roles'], $roleFields));
				$roleSettings = self::settings($rubricId, $fieldId);
				$resolved = ProductRoles::resolve($roleFields, $roleSettings, $roleInput);
				foreach (ProductRoles::definitions() as $role => $definition) {
					if (isset($definition['column'])) { $data[$definition['column']] = $resolved[$role]; }
				}
			}

			$data['product_card_template_id'] = isset($input['product_card_template_id']) ? max(0, (int) $input['product_card_template_id']) : 0;
			if ($data['product_card_template_id'] > 0 && !DB::query(
				'SELECT id FROM ' . CatalogTables::table('catalog_card_templates') . ' WHERE id=%i LIMIT 1',
				$data['product_card_template_id']
			)->getValue()) {
				throw new \InvalidArgumentException('Выбранное представление карточки не найдено');
			}

			if (self::filterTemplateSchemaAvailable()) {
				$data['filter_template_id'] = isset($input['filter_template_id']) ? max(0, (int) $input['filter_template_id']) : 0;
				$data['filter_template_mode'] = isset($input['filter_template_mode']) ? (string) $input['filter_template_mode'] : 'legacy';
				if (!in_array($data['filter_template_mode'], array('legacy', 'preview', 'native'), true)) { $data['filter_template_mode'] = 'legacy'; }
				if ($data['filter_template_id'] > 0 && !DB::query(
					'SELECT id FROM ' . CatalogTables::table('catalog_filter_templates') . ' WHERE id=%i LIMIT 1',
					$data['filter_template_id']
				)->getValue()) { throw new \InvalidArgumentException('Выбранный шаблон фильтров не найден'); }
				if ($data['filter_template_mode'] !== 'legacy' && $data['filter_template_id'] <= 0) {
					throw new \InvalidArgumentException('Для предпросмотра или нового режима выберите шаблон фильтров');
				}

				if ($data['filter_template_mode'] === 'native' && !DB::query(
					'SELECT id FROM ' . CatalogTables::table('catalog_filter_templates') . ' WHERE id=%i AND is_published=1 LIMIT 1',
					$data['filter_template_id']
				)->getValue()) { throw new \InvalidArgumentException('Перед включением нового режима опубликуйте шаблон фильтров'); }
			}

			foreach (array('navi_id', 'request_id', 'rub_cat_id') as $key) {
				$data[$key] = isset($input[$key]) ? max(0, (int) $input[$key]) : 0;
			}

			self::assertOptionExists($data['request_id'], self::requestsTable(), 'Id', 'Выбранный запрос не найден');
			self::assertOptionExists($data['navi_id'], self::navigationTable(), 'navigation_id', 'Выбранная навигация не найдена');
			self::assertOptionExists($data['rub_cat_id'], self::rubricsTable(), 'Id', 'Выбранная рубрика не найдена');
			foreach (array('doc_fileds', 'recursive', 'doc_parent', 'item_parent', 'sort_parent', 'save_names', 'filters_use') as $key) {
				$data[$key] = !empty($input[$key]) ? '1' : '0';
			}

			$data['fields_default'] = self::idsString(isset($input['fields_default']) ? $input['fields_default'] : array());
			$data['filters_default'] = self::idsString(isset($input['filters_default']) ? $input['filters_default'] : array());
			$styles = self::filterStyles(isset($input['filter_style']) ? $input['filter_style'] : array(), self::ids($data['filters_default']));
			$data['filters_default_settings'] = !empty($styles) ? serialize($styles) : '';
			$data['product_card_settings'] = Json::encode(
				self::productCardSettings(isset($input['product_card']) && is_array($input['product_card']) ? $input['product_card'] : array(), true),
				JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
			);
			if (self::filterTemplateSchemaAvailable()) {
				$data['filter_template_settings'] = Json::encode(
					self::filterTemplateSettings(isset($input['filter_template']) && is_array($input['filter_template']) ? $input['filter_template'] : array(), true),
					JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
				);
			}

			$reindex = $data['purpose'] !== $existing['purpose'];
			foreach ($data as $key => $value) {
				if (preg_match('/^product_.*_field_id$/', $key) && (int) $value !== (int) $existing[$key]) { $reindex = true; }
			}

			if ($roleInput !== null && $resolved !== ProductRoles::resolve($roleFields, $existing, ProductRoles::configured($rubricId))) { $reindex = true; }
			$registrySettings = Registry::get('settings');
			DB::startTransaction();
			try {
				$settingsId = (int) $existing['id'];
				if ($settingsId > 0) {
					DB::Update(self::settingsTable(), $data, 'id=%i', $settingsId);
				} else {
					$data['rubric_id'] = (int) $rubricId;
					$data['field_id'] = (int) $fieldId;
					DB::Insert(self::settingsTable(), $data);
					$settingsId = (int) DB::insertId();
				}

				if ($roleInput !== null) { ModuleSettings::set('field_roles_' . (int) $rubricId, $roleInput, 'products', 'json'); }
				if (self::productsAvailable()) { ProductRoles::reset(); }
				if ($reindex && self::productsAvailable()) { ProductIndexer::reindexRubric($rubricId, $existing['purpose'] === 'commerce' && $data['purpose'] === 'commerce'); }
				DB::afterCommit(function () {
					DB::clearTags(array('settings'));
					Cache::forgetTag(CacheKey::tag('catalog'));
					FileCacheInvalidator::publicPresentation();
				});
				DB::commit();
			} catch (\Throwable $e) {
				DB::rollback();
				Registry::set('settings', $registrySettings);
				if (self::productsAvailable()) { ProductRoles::reset(); }
				throw $e;
			}

			return $settingsId;
		}

		protected static function productCardSettings(array $input, $submitted = false)
		{
			$settings = array(
				'max_images' => isset($input['max_images']) ? max(1, min(5, (int) $input['max_images'])) : 5,
				'delivery_threshold' => isset($input['delivery_threshold']) ? max(0, (float) $input['delivery_threshold']) : 50000,
			);
			foreach (array('show_badges', 'show_article', 'show_stock', 'show_category', 'show_delivery', 'show_buy_button', 'show_comparison_price') as $key) {
				$settings[$key] = $submitted ? !empty($input[$key]) : (!array_key_exists($key, $input) || !empty($input[$key]));
			}

			return $settings;
		}

		protected static function filterTemplateSettings(array $input, $submitted = false)
		{
			$zero = isset($input['zero_behavior']) ? (string) $input['zero_behavior'] : 'hide';
			if (!in_array($zero, array('show', 'disable', 'hide'), true)) { $zero = 'hide'; }
			return array(
				'show_counts' => $submitted ? !empty($input['show_counts']) : (!array_key_exists('show_counts', $input) || !empty($input['show_counts'])),
				'zero_behavior' => $zero,
			);
		}

		protected static function filterTemplateSchemaAvailable()
		{
			$table = self::settingsTable();
			return DatabaseSchema::columnExists($table, 'filter_template_id')
				&& DatabaseSchema::columnExists($table, 'filter_template_mode')
				&& DatabaseSchema::columnExists($table, 'filter_template_settings');
		}

		public static function requestOptions()
		{
			$rows = DB::query(
				'SELECT Id, request_title, request_alias, rubric_id FROM ' . self::requestsTable()
				. ' ORDER BY request_title ASC, request_alias ASC, Id ASC'
			)->getAll();
			$out = array();
			foreach ($rows as $row) {
				$out[] = array(
					'id' => (int) $row['Id'],
					'title' => self::decode(isset($row['request_title']) ? $row['request_title'] : ''),
					'alias' => (string) $row['request_alias'],
					'rubric_id' => (int) $row['rubric_id'],
				);
			}

			return $out;
		}

		public static function navigationOptions()
		{
			$rows = DB::query(
				'SELECT navigation_id, title, alias FROM ' . self::navigationTable()
				. ' ORDER BY title ASC, navigation_id ASC'
			)->getAll();
			$out = array();
			foreach ($rows as $row) {
				$out[] = array(
					'id' => (int) $row['navigation_id'],
					'title' => self::decode(isset($row['title']) ? $row['title'] : ''),
					'alias' => (string) $row['alias'],
				);
			}

			return $out;
		}

		public static function rubricOptions()
		{
			$rows = DB::query(
				'SELECT Id, rubric_title, rubric_alias FROM ' . self::rubricsTable()
				. ' ORDER BY rubric_position ASC, rubric_title ASC, Id ASC'
			)->getAll();
			$out = array();
			foreach ($rows as $row) {
				$out[] = array(
					'id' => (int) $row['Id'],
					'title' => self::decode(isset($row['rubric_title']) ? $row['rubric_title'] : ''),
					'alias' => (string) $row['rubric_alias'],
				);
			}

			return $out;
		}

		public static function items($rubricId, $fieldId, $activeOnly = false)
		{
			self::requireCatalog($rubricId, $fieldId);
			$sql = 'SELECT i.*, d.document_title, d.document_breadcrumb_title'
				. ' FROM ' . self::itemsTable() . ' i'
				. ' LEFT JOIN ' . self::documentsTable() . ' d ON d.Id = i.document_id'
				. ' WHERE i.rubric_id = %i AND i.field_id = %i';
			if ($activeOnly) { $sql .= ' AND i.status = 1'; }
			$sql .= ' ORDER BY i.level ASC, i.parent_id ASC, i.position ASC, i.id ASC';
			$rows = DB::query($sql, (int) $rubricId, (int) $fieldId)->getAll();
			$out = array();
			foreach ($rows as $row) { $out[] = self::itemRow($row); }
			return $out;
		}

		public static function tree($rubricId, $fieldId, $activeOnly = false)
		{
			$flat = self::items($rubricId, $fieldId, $activeOnly);
			$map = array();
			foreach ($flat as $item) { $item['children'] = array(); $map[$item['id']] = $item; }
			$tree = array();
			foreach ($map as $id => $item) {
				$parentId = $item['parent_id'];
				if ($parentId > 0 && isset($map[$parentId])) { $map[$parentId]['children'][] = &$map[$id]; }
				else { $tree[] = &$map[$id]; }
			}

			return $tree;
		}

		public static function sourceOptions($rubricId, $fieldId)
		{
			$items = self::items($rubricId, $fieldId);
			$map = array();
			foreach ($items as $item) { $map[(int) $item['id']] = $item; }
			$out = array();
			foreach ($items as $item) {
				$parts = array((string) $item['name']);
				$parentId = (int) $item['parent_id'];
				$visited = array((int) $item['id'] => true);
				while ($parentId > 0 && isset($map[$parentId]) && empty($visited[$parentId])) {
					$visited[$parentId] = true;
					array_unshift($parts, (string) $map[$parentId]['name']);
					$parentId = (int) $map[$parentId]['parent_id'];
				}

				$out[] = array(
					'id' => (int) $item['id'],
					'name' => (string) $item['name'],
					'path' => implode(' / ', $parts),
					'status' => (int) $item['status'],
				);
			}

			return $out;
		}

		public static function item($id)
		{
			$row = DB::query(
				'SELECT i.*, d.document_title, d.document_breadcrumb_title FROM ' . self::itemsTable() . ' i'
				. ' LEFT JOIN ' . self::documentsTable() . ' d ON d.Id = i.document_id WHERE i.id = %i LIMIT 1',
				(int) $id
			)->getAssoc();
			return $row ? self::itemRow($row) : null;
		}

		public static function saveItem($id, $rubricId, $fieldId, array $input)
		{
			self::requireCatalog($rubricId, $fieldId);
			$id = (int) $id;
			$current = $id > 0 ? self::item($id) : null;
			if ($id > 0 && (!$current || $current['rubric_id'] !== (int) $rubricId || $current['field_id'] !== (int) $fieldId)) {
				throw new \RuntimeException('Раздел каталога не найден');
			}

			$name = trim(isset($input['name']) ? (string) $input['name'] : '');
			if ($name === '') { throw new \RuntimeException('Укажите название раздела'); }
			$parentId = isset($input['parent_id']) ? (int) $input['parent_id'] : 0;
			self::assertParent($id, $parentId, $rubricId, $fieldId);
			$documentId = isset($input['document_id']) ? max(0, (int) $input['document_id']) : 0;
			if ($documentId > 0 && !self::documentExists($documentId)) { throw new \RuntimeException('Выбранный документ не найден'); }
			$filterIds = self::ids(isset($input['filters_use']) ? $input['filters_use'] : array());
			$filterIds = self::orderedIds(isset($input['filters_order']) ? $input['filters_order'] : array(), $filterIds);
			$sourceIds = self::validSourceItemIds(
				isset($input['source_item_ids']) ? $input['source_item_ids'] : array(),
				$id,
				$rubricId,
				$fieldId
			);
			$data = array(
				'rubric_id' => (int) $rubricId, 'field_id' => (int) $fieldId,
				'name' => $name, 'parent_id' => $parentId,
				'status' => !empty($input['status']) ? 1 : 0,
				'document_id' => $documentId ?: null,
				'document_alias' => $documentId ? self::documentAlias($documentId) : '',
				'level' => self::parentLevel($parentId) + 1,
				'fields_use' => self::idsString(isset($input['fields_use']) ? $input['fields_use'] : array()),
				'filters_use' => implode(',', $filterIds),
				'filters_settings' => serialize(self::filterStyles(isset($input['filter_style']) ? $input['filter_style'] : array(), $filterIds)),
			);
			if (self::sourceItemsAvailable()) {
				$data['source_item_ids'] = implode(',', $sourceIds);
			} elseif ($sourceIds) {
				throw new \RuntimeException('Сначала примените миграцию каталога для источников товаров');
			}

			if ($id > 0) {
				DB::Update(self::itemsTable(), $data, 'id = %i', $id);
			} else {
				$data['position'] = self::nextPosition($rubricId, $fieldId, $parentId);
				DB::Insert(self::itemsTable(), $data);
				$id = (int) DB::insertId();
			}

			self::recountLevels($rubricId, $fieldId);
			self::syncCategoryDocumentParent($id);
			$filtersChanged = !$current
				|| serialize(isset($current['filters_use']) ? $current['filters_use'] : array()) !== serialize($filterIds)
				|| serialize(isset($current['filter_styles']) ? $current['filter_styles'] : array()) !== serialize(self::filterStyles(isset($input['filter_style']) ? $input['filter_style'] : array(), $filterIds));
			if ($filtersChanged && (int) self::settings($rubricId, $fieldId)['filters_use'] === 1) { self::recompileFilters($rubricId, array($id)); }
			self::clearCache();
			return $id;
		}

		public static function deleteItem($id)
		{
			$item = self::item($id);
			if (!$item) { return false; }
			$ids = self::descendantIds((int) $id);
			$ids[] = (int) $id;
			DB::query('DELETE FROM ' . self::itemsTable() . ' WHERE id IN (' . implode(',', $ids) . ')');
			self::clearCache();
			return $ids;
		}

		public static function reorder($rubricId, $fieldId, array $rows)
		{
			self::requireCatalog($rubricId, $fieldId);
			$changed = array();
			foreach ($rows as $row) {
				if (!is_array($row) || empty($row['id'])) { continue; }
				$id = (int) $row['id'];
				$item = self::item($id);
				if (!$item || $item['rubric_id'] !== (int) $rubricId || $item['field_id'] !== (int) $fieldId) { continue; }
				$parentId = isset($row['parent_id']) ? (int) $row['parent_id'] : 0;
				self::assertParent($id, $parentId, $rubricId, $fieldId);
				DB::Update(self::itemsTable(), array(
					'parent_id' => $parentId,
					'position' => isset($row['position']) ? max(0, (int) $row['position']) : 0,
				), 'id = %i', $id);
				if ($parentId !== (int) $item['parent_id']) { $changed[] = $id; }
			}

			self::recountLevels($rubricId, $fieldId);
			foreach (array_unique($changed) as $id) { self::syncCategoryDocumentParent($id); }
			self::clearCache();
		}

		public static function setItemStatus($id, $rubricId, $fieldId, $active)
		{
			$item = self::item((int) $id);
			if (!$item || $item['rubric_id'] !== (int) $rubricId || $item['field_id'] !== (int) $fieldId) {
				throw new \RuntimeException('Раздел каталога не найден');
			}

			DB::Update(self::itemsTable(), array('status' => $active ? 1 : 0), 'id = %i', (int) $id);
			self::clearCache();
			return $active ? 1 : 0;
		}

		public static function recompileItem($rubricId, $fieldId, $itemId)
		{
			$item = self::item((int) $itemId);
			if (!$item || $item['rubric_id'] !== (int) $rubricId || $item['field_id'] !== (int) $fieldId) {
				throw new \RuntimeException('Раздел каталога не найден');
			}

			return self::runFilterRecompile((int) $rubricId, (int) $itemId);
		}

		public static function rubricFields($rubricId)
		{
			$rows = DB::query(
				'SELECT f.Id, f.rubric_field_title, f.rubric_field_alias, f.rubric_field_type, f.rubric_field_group, g.group_title'
				. ' FROM ' . self::fieldsTable() . ' f LEFT JOIN ' . self::groupsTable() . ' g ON g.Id = f.rubric_field_group'
				. ' WHERE f.rubric_id = %i ORDER BY COALESCE(g.group_position,999999), f.rubric_field_position, f.Id',
				(int) $rubricId
			)->getAll();
			$out = array();
			foreach ($rows as $row) {
				$out[] = array(
					'id' => (int) $row['Id'], 'title' => html_entity_decode((string) $row['rubric_field_title'], ENT_QUOTES, 'UTF-8'),
					'alias' => (string) $row['rubric_field_alias'], 'type' => (string) $row['rubric_field_type'],
					'group_id' => (int) $row['rubric_field_group'],
					'group' => html_entity_decode((string) $row['group_title'], ENT_QUOTES, 'UTF-8'),
				);
			}

			return $out;
		}

		public static function searchDocuments($query, $limit = 30)
		{
			$query = trim((string) $query);
			$limit = max(1, min(50, (int) $limit));
			$sql = 'SELECT d.Id, d.rubric_id, d.document_title, d.document_alias, r.rubric_title'
				. ' FROM ' . self::documentsTable() . ' d'
				. ' LEFT JOIN ' . self::rubricsTable() . ' r ON r.Id = d.rubric_id'
				. " WHERE d.document_deleted != '1'";
			$args = array();
			if ($query !== '') {
				$sql .= ' AND (d.document_title LIKE %ss OR d.document_alias LIKE %ss OR d.Id = %i)';
				$args[] = $query; $args[] = $query; $args[] = (int) $query;
			}

			$sql .= ' ORDER BY d.document_changed DESC, d.Id DESC LIMIT ' . $limit;
			$rows = call_user_func_array(array('DB', 'query'), array_merge(array($sql), $args))->getAll();
			$out = array();
			foreach ($rows as $row) {
				$out[] = array(
					'id' => (int) $row['Id'], 'rubric_id' => (int) $row['rubric_id'],
					'title' => self::decode($row['document_title']), 'alias' => (string) $row['document_alias'],
					'rubric_title' => self::decode(isset($row['rubric_title']) ? $row['rubric_title'] : ''),
				);
			}

			return $out;
		}

		/** Разделы каталога, доступные как родители в редакторе документа. */
		public static function searchParentDocuments($query, $limit = 30)
		{
			$query = trim((string) $query);
			$limit = max(1, min(50, (int) $limit));
			$sql = 'SELECT i.id AS catalog_item_id,i.name,i.status AS catalog_status,i.document_id,d.rubric_id,'
				. ' d.document_title,d.document_alias,r.rubric_title'
				. ' FROM ' . self::itemsTable() . ' i'
				. ' INNER JOIN ' . self::documentsTable() . ' d ON d.Id=i.document_id'
				. ' LEFT JOIN ' . self::rubricsTable() . ' r ON r.Id=d.rubric_id'
				. " WHERE i.document_id>0 AND d.document_deleted!='1'";
			$args = array();
			if ($query !== '') {
				$sql .= ' AND (i.name LIKE %ss OR d.document_title LIKE %ss'
					. ' OR d.document_alias LIKE %ss OR d.Id=%i)';
				$args[] = $query;
				$args[] = $query;
				$args[] = $query;
				$args[] = (int) $query;
			}

			$sql .= ' ORDER BY i.level ASC,i.position ASC,i.name ASC LIMIT ' . $limit;
			$rows = call_user_func_array(array('DB', 'query'), array_merge(array($sql), $args))->getAll();
			$out = array();
			foreach ($rows ?: array() as $row) {
				$out[] = array(
					'id' => (int) $row['document_id'],
					'rubric_id' => (int) $row['rubric_id'],
					'title' => self::decode($row['name']),
					'alias' => (string) $row['document_alias'],
					'status' => (int) $row['catalog_status'],
					'rubric_title' => 'Раздел каталога'
						. ((int) $row['catalog_status'] === 1 ? '' : ' · выключен')
						. ' · ' . self::decode($row['rubric_title']),
					'catalog_item_id' => (int) $row['catalog_item_id'],
				);
			}

			return $out;
		}

		public static function parseValue($value)
		{
			$data = FieldValueCodec::decodeStructured($value, array());
			return array(
				'catalog_ids' => self::ids(isset($data['catalogs']) ? $data['catalogs'] : array()),
				'document_ids' => self::ids(isset($data['documents']) ? $data['documents'] : array()),
				'names' => isset($data['names']) ? (string) $data['names'] : '',
			);
		}

		public static function products(array $filters = array())
		{
			return self::productsAvailable() ? ProductIndexer::products($filters) : array();
		}

		public static function productShippingStats()
		{
			return self::productsAvailable() ? ProductIndexer::shippingStats() : array('total'=>0,'ready'=>0,'incomplete'=>0,'disabled'=>0);
		}

		public static function productStats()
		{
			return self::productsAvailable()
				? ProductIndexer::stats()
				: self::emptyProductStats();
		}

		public static function productQualityStats()
		{
			return self::productsAvailable()
				? ProductIndexer::qualityStats()
				: self::emptyProductStats();
		}

		public static function productStatsSnapshot()
		{
			$stats = self::productsAvailable() ? ProductIndexer::cachedStats() : self::emptyProductStats();
			return array(
				'loaded' => is_array($stats),
				'stats' => array_merge(self::emptyProductStats(), is_array($stats) ? $stats : array()),
			);
		}

		protected static function emptyProductStats()
		{
			return array(
				'total'=>0,'quality_total'=>0,'active'=>0,'inactive'=>0,'without_category'=>0,
				'without_image'=>0,'without_article'=>0,'without_price'=>0,'without_stock'=>0,
				'without_description'=>0,'without_seo'=>0,'stale'=>0,'shipping_incomplete'=>0,
				'shipping_disabled'=>0,'duplicate_articles'=>0,'legacy_attributes'=>0,
				'filter_index_errors'=>0,'registration_incomplete'=>0,'without_video'=>0,
				'variant_errors'=>0,'description_markup'=>0,'sfr'=>0,'issues'=>0,'recommendations'=>0,'indexed_at'=>0,
			);
		}

		public static function productQualityIssues(array $stats)
		{
			return self::productsAvailable() ? ProductIndexer::qualityIssues($stats) : array();
		}

		public static function product($id)
		{
			return self::productsAvailable() ? ProductIndexer::product($id) : null;
		}

		public static function productRubrics()
		{
			return self::productsAvailable() ? ProductIndexer::productRubrics() : array();
		}

		public static function productRubricOptions()
		{
			$allowed = array_flip(self::productRubrics());
			return array_values(array_filter(self::rubricOptions(), function ($rubric) use ($allowed) {
				return isset($allowed[(int) $rubric['id']]);
			}));
		}

		public static function reindexProducts($limit = 0)
		{
			return self::productsAvailable()
				? ProductIndexer::rebuild($limit)
				: array('products' => 0, 'categories' => 0, 'filters' => 0);
		}

		public static function reindexDocument($documentId)
		{
			return self::productsAvailable()
				? ProductIndexer::indexDocument((int) $documentId)
				: array('categories' => 0, 'filters' => 0);
		}

		public static function selection($rubricId, $fieldId, $input)
		{
			$ids = self::ids($input);
			if (empty($ids)) { return array(); }
			$valid = array();
			foreach (self::items($rubricId, $fieldId, false) as $item) { $valid[$item['id']] = true; }
			return array_values(array_filter($ids, function ($id) use ($valid) { return isset($valid[$id]); }));
		}

		public static function allowedFieldIds($rubricId, $fieldId, array $selected)
		{
			$settings = self::settings($rubricId, $fieldId);
			if ((int) $settings['doc_fileds'] !== 1) { return array(); }
			$allowed = array((int) $fieldId);
			$selectedMap = array_fill_keys($selected, true);
			foreach (self::items($rubricId, $fieldId, false) as $item) {
				if (isset($selectedMap[$item['id']])) { $allowed = array_merge($allowed, self::ids($item['fields_use'])); }
			}

			return array_values(array_unique(array_map('intval', $allowed)));
		}

		public static function serializeSelection($rubricId, $fieldId, array $selected)
		{
			$selected = self::selection($rubricId, $fieldId, $selected);
			$settings = self::settings($rubricId, $fieldId);
			$map = array();
			foreach (self::items($rubricId, $fieldId, false) as $item) { $map[$item['id']] = $item; }
			$documents = array();
			$names = array();
			foreach ($selected as $id) {
				$names[] = isset($map[$id]) ? $map[$id]['name'] : '';
				$chain = array();
				$cursor = $id;
				while ($cursor > 0 && isset($map[$cursor])) {
					if ($map[$cursor]['document_id'] > 0) { $chain[] = $map[$cursor]['document_id']; }
					if ((int) $settings['recursive'] !== 1) { break; }
					$cursor = $map[$cursor]['parent_id'];
				}

				$documents = array_merge($documents, array_reverse($chain));
			}

			$documents = array_values(array_unique(array_filter(array_map('intval', $documents))));
			$data = array('catalogs' => implode(',', $selected), 'documents' => !empty($documents) ? '|' . implode('|', $documents) . '|' : '');
			if ((int) $settings['save_names'] === 1) { $data['names'] = implode(',', array_filter($names, 'strlen')); }
			return serialize($data);
		}

		public static function afterDocumentSave($documentId, $rubricId, $fieldId, array $selected)
		{
			$settings = self::settings($rubricId, $fieldId);
			if ((int) $settings['doc_parent'] === 1 && !empty($selected)) {
				$item = self::item((int) reset($selected));
				if ($item && $item['document_id'] > 0) {
					DB::Update(self::documentsTable(), array('document_parent' => $item['document_id']), 'Id = %i', (int) $documentId);
					DocumentsModel::markChanged((int) $documentId);
				}
			}

			if ((int) $settings['filters_use'] === 1) { self::recompileFilters($rubricId, $selected); }
			self::clearCache();
		}

		public static function filterStyleOptions($type)
		{
			$options = array('default' => 'Автоматически');
			$map = array(
				'single_line' => array('single_line_input' => 'Текстовое поле', 'single_line_minmax' => 'Диапазон от и до'),
				'single_line_numeric' => array('single_line_numeric_input' => 'Числовое поле', 'single_line_numeric_minmax' => 'Диапазон от и до'),
				'number' => array('single_line_numeric_input' => 'Числовое поле', 'single_line_numeric_minmax' => 'Диапазон от и до'),
				'drop_down' => array('drop_down_select' => 'Выпадающий список', 'drop_down_checkbox' => 'Флажки'),
				'drop_down_key' => array('drop_down_key_select' => 'Выпадающий список', 'drop_down_key_checkbox' => 'Флажки'),
				'doc_from_rub' => array('doc_from_rub_select' => 'Выпадающий список', 'doc_from_rub_checkbox' => 'Флажки'),
				'checkbox' => array('checkbox_checkbox' => 'Переключатель'),
				'multi_checkbox' => array('multi_checkbox_select' => 'Выпадающий список', 'multi_checkbox_checkbox' => 'Флажки'),
				'checkbox_multi' => array('checkbox_multi_select' => 'Выпадающий список', 'checkbox_multi_checkbox' => 'Флажки'),
			);
			return isset($map[$type]) ? array_merge($options, $map[$type]) : $options;
		}

		public static function conditionContext($itemId)
		{
			$item = self::item((int) $itemId);
			if (!$item) { throw new \RuntimeException('Раздел каталога не найден'); }
			$settings = self::settings($item['rubric_id'], $item['field_id']);
			$requestId = (int) $settings['request_id'];
			$requestTitle = '';
			if ($requestId > 0) {
				$requestTitle = (string) DB::query(
					'SELECT request_title FROM ' . self::requestsTable() . ' WHERE Id = %i LIMIT 1',
					$requestId
				)->getValue();
				$requestTitle = self::decode($requestTitle);
			}

			$conditions = array();
			if ($requestId > 0) {
				foreach (DB::query(
					'SELECT * FROM ' . self::conditionsTable() . ' WHERE request_id = %i ORDER BY condition_position ASC, Id ASC',
					$requestId
				)->getAll() as $condition) {
					$fieldId = (int) $condition['condition_field_id'];
					if (!isset($conditions[$fieldId])) { $conditions[$fieldId] = $condition; }
				}
			}

			$fields = array();
			foreach (self::rubricFields($item['rubric_id']) as $field) { $fields[$field['id']] = $field; }
			$states = array();
			$summary = array('synced' => 0, 'staged' => 0, 'missing' => 0, 'different' => 0, 'unsupported' => 0);
			foreach ($item['filters_use'] as $filterId) {
				if (!isset($fields[$filterId])) { continue; }
				$style = isset($item['filter_styles'][$filterId]) ? $item['filter_styles'][$filterId] : 'default';
				$generated = self::generateCondition($fields[$filterId], $style);
				$existing = isset($conditions[$filterId]) ? $conditions[$filterId] : null;
				$state = 'missing';
				if (!$generated) { $state = 'unsupported'; }
				elseif ($existing && self::conditionMatches($existing, $generated)) { $state = 'synced'; }
				elseif ($existing && self::conditionDescriptorMatches($existing, $generated)) { $state = 'staged'; }
				elseif ($existing) { $state = 'different'; }
				$summary[$state]++;
				$states[$filterId] = array(
					'field_id' => $filterId,
					'state' => $requestId > 0 ? $state : 'no_request',
					'style' => $style,
					'condition_id' => $existing ? (int) $existing['Id'] : 0,
					'generated' => $generated ?: array(
						'compare' => '', 'value' => '', 'source' => '', 'key' => '', 'config' => '',
					),
					'existing' => $existing ? array(
						'compare' => (string) $existing['condition_compare'],
						'value' => (string) $existing['condition_value'],
						'status' => (int) $existing['condition_status'],
					) : null,
				);
			}

			return array(
				'request' => array('id' => $requestId, 'title' => $requestTitle),
				'states' => $states,
				'summary' => $summary,
			);
		}

		public static function syncCondition($itemId, $filterId, $force = false, $rebuild = true)
		{
			$item = self::item((int) $itemId);
			if (!$item || !in_array((int) $filterId, $item['filters_use'], true)) {
				throw new \RuntimeException('Фильтр не включён в этом разделе');
			}

			$context = self::conditionContext($itemId);
			$requestId = (int) $context['request']['id'];
			if ($requestId <= 0) { throw new \RuntimeException('Сначала выберите запрос фильтра в настройках каталога'); }
			if (!isset($context['states'][(int) $filterId])) { throw new \RuntimeException('Поле фильтра не найдено'); }
			$state = $context['states'][(int) $filterId];
			if ($state['state'] === 'unsupported') { throw new \RuntimeException('Для выбранного типа фильтра нельзя создать условие автоматически'); }
			if ($state['state'] === 'different' && !$force) { throw new \RuntimeException('Существующее условие отличается от сгенерированного'); }
			if ($state['state'] === 'synced' || $state['state'] === 'staged') { return $context; }

			$value = $state['generated']['value'];
			if ((int) $state['condition_id'] > 0 && $state['existing']) {
				$legacy = RequestConditionValue::legacyDescriptor(
					$state['existing']['value'],
					$state['existing']['compare']
				);
				if ($legacy !== null && RequestConditionValue::encodeConfig($legacy) === $state['generated']['config']) {
					$value = $state['existing']['value'];
				}
			}

			$data = array(
				'condition_compare' => $state['generated']['compare'],
				'condition_value' => $value,
				'condition_value_source' => $state['generated']['source'],
				'condition_value_key' => $state['generated']['key'],
				'condition_value_config' => $state['generated']['config'],
				'condition_join' => 'AND',
				'condition_status' => '1',
			);
			if ((int) $state['condition_id'] > 0) {
				DB::Update(self::conditionsTable(), $data, 'Id = %i AND request_id = %i', (int) $state['condition_id'], $requestId);
			} else {
				$data['request_id'] = $requestId;
				$data['condition_field_id'] = (int) $filterId;
				$data['condition_position'] = (int) DB::query(
					'SELECT COALESCE(MAX(condition_position), -1) + 1 FROM ' . self::conditionsTable() . ' WHERE request_id = %i',
					$requestId
				)->getValue();
				DB::Insert(self::conditionsTable(), $data);
			}

			if ($rebuild) { self::rebuildRequest($requestId); }
			self::clearCache();
			return self::conditionContext($itemId);
		}

		public static function syncMissingConditions($itemId)
		{
			$context = self::conditionContext($itemId);
			if ((int) $context['request']['id'] <= 0) { throw new \RuntimeException('Сначала выберите запрос фильтра в настройках каталога'); }
			$created = 0;
			foreach ($context['states'] as $fieldId => $state) {
				if ($state['state'] !== 'missing') { continue; }
				self::syncCondition((int) $itemId, (int) $fieldId, false, false);
				$created++;
			}

			if ($created > 0) { self::rebuildRequest((int) $context['request']['id']); }
			$result = self::conditionContext($itemId);
			$result['created'] = $created;
			return $result;
		}

		public static function generateCondition(array $field, $style)
		{
			$fieldId = (int) $field['id'];
			$type = isset($field['type']) ? (string) $field['type'] : '';
			$style = self::resolveFilterStyle($type, (string) $style);
			if ($style === '') { return null; }
			$alias = isset($field['alias']) ? trim((string) $field['alias']) : '';
			$name = preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $alias) ? $alias : 'field_' . $fieldId;
			if ($style === 'checkbox_checkbox' || $style === 'drop_down_key_select') {
				return self::conditionDefinition('==', $name, 'integer', 'scalar');
			}

			if ($style === 'single_line_input' || $style === 'single_line_numeric_input') {
				return self::conditionDefinition('%%', $name, 'string', 'scalar');
			}

			if ($style === 'drop_down_select' || $style === 'doc_from_rub_select') {
				return self::conditionDefinition('==', $name, 'string', 'scalar');
			}

			if ($style === 'single_line_minmax') {
				return self::conditionDefinition('FRE', $name, 'integer', 'range', 'plain', array(
					'range_min_default' => '0',
				));
			}

			if ($style === 'single_line_numeric_minmax' && $type === 'number') {
				return self::conditionDefinition('FRE', $name, 'decimal', 'range');
			}

			if ($style === 'single_line_numeric_minmax') {
				return self::conditionDefinition('FRE', $name, 'integer', 'range', 'plain', array(
					'range_min_default' => '0',
					'range_max_positive' => true,
				));
			}

			if (in_array($style, array('drop_down_checkbox', 'doc_from_rub_checkbox'), true)) {
				return self::conditionDefinition('FRE', $name, 'string', 'list');
			}

			if ($style === 'drop_down_key_checkbox') {
				return self::conditionDefinition('FRE', $name, 'integer', 'list');
			}

			if (in_array($style, array('multi_checkbox_select', 'checkbox_multi_select'), true)) {
				return self::conditionDefinition('%%', $name, 'integer', 'scalar', 'pipe_member');
			}

			if (in_array($style, array('multi_checkbox_checkbox', 'checkbox_multi_checkbox'), true)) {
				return self::conditionDefinition('FRE', $name, 'integer', 'list', 'pipe_member');
			}

			return null;
		}

		protected static function conditionDefinition($compare, $key, $cast, $shape, $transform = 'plain', array $options = array())
		{
			$descriptor = array(
				'source' => 'input',
				'key' => $key,
				'cast' => $cast,
				'shape' => $shape,
				'transform' => $transform,
				'constant' => '',
			);
			$descriptor += $options;
			return array(
				'compare' => $compare,
				'value' => RequestConditionValue::tag($key),
				'source' => 'input',
				'key' => $key,
				'config' => RequestConditionValue::encodeConfig($descriptor),
			);
		}

		protected static function conditionMatches(array $condition, array $generated)
		{
			return (string) $condition['condition_compare'] === (string) $generated['compare']
				&& trim((string) $condition['condition_value']) === trim((string) $generated['value'])
				&& (string) (isset($condition['condition_value_source']) ? $condition['condition_value_source'] : '') === (string) $generated['source']
				&& (string) (isset($condition['condition_value_key']) ? $condition['condition_value_key'] : '') === (string) $generated['key']
				&& RequestConditionValue::decodeConfig(isset($condition['condition_value_config']) ? $condition['condition_value_config'] : '')
					=== RequestConditionValue::decodeConfig($generated['config']);
		}

		protected static function conditionDescriptorMatches(array $condition, array $generated)
		{
			return strpos((string) $condition['condition_value'], '<?') !== false
				&& (string) $condition['condition_compare'] === (string) $generated['compare']
				&& (string) (isset($condition['condition_value_source']) ? $condition['condition_value_source'] : '') === (string) $generated['source']
				&& (string) (isset($condition['condition_value_key']) ? $condition['condition_value_key'] : '') === (string) $generated['key']
				&& RequestConditionValue::decodeConfig(isset($condition['condition_value_config']) ? $condition['condition_value_config'] : '')
					=== RequestConditionValue::decodeConfig($generated['config']);
		}

		protected static function resolveFilterStyle($type, $style)
		{
			$defaults = array(
				'single_line' => 'single_line_input', 'single_line_numeric' => 'single_line_numeric_input', 'number' => 'single_line_numeric_input',
				'drop_down' => 'drop_down_select', 'drop_down_key' => 'drop_down_key_select',
				'doc_from_rub' => 'doc_from_rub_select', 'checkbox' => 'checkbox_checkbox',
				'multi_checkbox' => 'multi_checkbox_select', 'checkbox_multi' => 'checkbox_multi_select',
			);
			if ($style === '' || $style === 'default') { return isset($defaults[$type]) ? $defaults[$type] : ''; }
			$allowed = self::filterStyleOptions($type);
			return isset($allowed[$style]) ? $style : '';
		}

		protected static function requireCatalog($rubricId, $fieldId)
		{
			$exists = (int) DB::query(
				'SELECT Id FROM ' . self::fieldsTable() . " WHERE Id = %i AND rubric_id = %i AND rubric_field_type = 'catalog' LIMIT 1",
				(int) $fieldId, (int) $rubricId
			)->getValue();
			if ($exists <= 0) { throw new \RuntimeException('Каталог не найден'); }
		}

		protected static function assertOptionExists($id, $table, $column, $message)
		{
			if ((int) $id <= 0) { return; }
			if (!preg_match('/^[A-Za-z0-9_]+$/', (string) $table) || !preg_match('/^[A-Za-z0-9_]+$/', (string) $column)) {
				throw new \RuntimeException('Некорректный источник справочника');
			}

			$exists = (int) DB::query('SELECT `' . $column . '` FROM `' . $table . '` WHERE `' . $column . '` = %i LIMIT 1', (int) $id)->getValue();
			if ($exists <= 0) { throw new \RuntimeException((string) $message); }
		}

		protected static function assertParent($id, $parentId, $rubricId, $fieldId)
		{
			if ($parentId <= 0) { return; }
			if ($parentId === (int) $id) { throw new \RuntimeException('Раздел не может быть родителем самого себя'); }
			$parent = self::item($parentId);
			if (!$parent || $parent['rubric_id'] !== (int) $rubricId || $parent['field_id'] !== (int) $fieldId) {
				throw new \RuntimeException('Родительский раздел не найден');
			}

			if ($id > 0 && in_array((int) $parentId, self::descendantIds($id), true)) {
				throw new \RuntimeException('Нельзя переместить раздел внутрь его потомка');
			}
		}

		protected static function descendantIds($id)
		{
			$out = array(); $queue = array((int) $id);
			while (!empty($queue)) {
				$parent = array_shift($queue);
				$rows = DB::query('SELECT id FROM ' . self::itemsTable() . ' WHERE parent_id = %i', $parent)->getAll();
				foreach ($rows as $row) { $child = (int) $row['id']; if (!in_array($child, $out, true)) { $out[] = $child; $queue[] = $child; } }
			}

			return $out;
		}

		protected static function recountLevels($rubricId, $fieldId)
		{
			$items = self::items($rubricId, $fieldId, false); $map = array();
			foreach ($items as $item) { $map[$item['id']] = $item; }
			foreach ($map as $id => $item) {
				$level = 1; $cursor = $item['parent_id']; $seen = array($id => true);
				while ($cursor > 0 && isset($map[$cursor]) && !isset($seen[$cursor])) { $seen[$cursor] = true; $level++; $cursor = $map[$cursor]['parent_id']; }
				DB::Update(self::itemsTable(), array('level' => $level), 'id = %i', $id);
			}
		}

		protected static function syncCategoryDocumentParent($id)
		{
			$item = self::item($id); if (!$item || $item['document_id'] <= 0) { return; }
			$settings = self::settings($item['rubric_id'], $item['field_id']);
			if ((int) $settings['item_parent'] !== 1) { return; }
			$parent = $item['parent_id'] > 0 ? self::item($item['parent_id']) : null;
			DB::Update(self::documentsTable(), array('document_parent' => $parent ? $parent['document_id'] : 0), 'Id = %i', $item['document_id']);
			DocumentsModel::markChanged((int) $item['document_id']);
		}

		protected static function recompileFilters($rubricId, array $selected)
		{
			foreach ($selected as $itemId) {
				try { self::runFilterRecompile((int) $rubricId, (int) $itemId); }
				catch (\Throwable $e) { error_log('Catalog filter recompile failed: ' . $e->getMessage()); }
			}
		}

		protected static function runFilterRecompile($rubricId, $itemId)
		{
			$item = static::item((int) $itemId);
			if (!$item || (int) $item['rubric_id'] !== (int) $rubricId) { throw new \RuntimeException('Раздел каталога не найден'); }
			$settings = static::settings((int) $rubricId, (int) $item['field_id']);
			$native = isset($item['filter_runtime']) && in_array($item['filter_runtime'], array('native', 'shadow'), true);
			$products = static::productsAvailable() && isset($settings['purpose']) && $settings['purpose'] === 'commerce';
			if ($native && !$products) { throw new \RuntimeException('Для нативных фильтров включите модуль «Товары»'); }
			if (!$native && $products && !method_exists(ProductIndexer::class, 'rebuildFilterIndex')) { throw new \RuntimeException('Обновите модуль «Товары» для пересборки индекса фильтров'); }
			$requestId = isset($settings['request_id']) ? (int) $settings['request_id'] : 0;
			if (!$native && !$products && $requestId < 1) { throw new \RuntimeException('Сначала выберите запрос фильтра в настройках каталога'); }
			$owns = !DB::$transaction_in_progress;
			$previousMode = DB::$throw_exception_on_error;
			$savepoint = 'catalog_filters_' . bin2hex(random_bytes(6));
			$started = false;
			DB::$throw_exception_on_error = true;
			try {
				if ($owns) { DB::startTransaction(); } else { DB::query('SAVEPOINT ' . $savepoint); }
				$started = true;
				$result = array('item_id' => (int) $itemId, 'runtime' => $native ? 'native' : 'legacy', 'request_id' => 0, 'products' => 0, 'filters' => 0);
				if ($native) {
					$report = Attributes::rebuildShadowIndex((int) $itemId);
					$result['filters'] = (int) $report['indexed_values'];
					$result['products'] = (int) DB::query('SELECT COUNT(DISTINCT product_id) FROM ' . Attributes::table('attribute_filter_index') . ' WHERE catalog_item_id=%i', (int) $itemId)->getValue();
				} else {
					if ($requestId > 0) {
						$compiled = \App\Content\Requests\RequestConditionCompiler::compile($requestId, true);
						if (!$compiled) { throw new \RuntimeException('Запрос фильтра не найден'); }
						$result['request_id'] = $requestId;
					}

					if ($products) {
						$ids = array_merge(array((int) $itemId), isset($item['source_item_ids']) ? (array) $item['source_item_ids'] : array());
						$result = array_merge($result, ProductIndexer::rebuildFilterIndex($ids));
					}
				}

				if ($owns) { DB::commit(); } else { DB::query('RELEASE SAVEPOINT ' . $savepoint); }
			} catch (\Throwable $e) {
				if ($started && $owns) { DB::rollback(); }
				elseif ($started) { DB::query('ROLLBACK TO SAVEPOINT ' . $savepoint); DB::query('RELEASE SAVEPOINT ' . $savepoint); }
				throw $e;
			} finally { DB::$throw_exception_on_error = $previousMode; }
			DB::afterCommit(function () { static::clearCache(); });
			$result['output'] = $native ? 'Нативный индекс фильтров пересобран.' : 'Условия запроса и индекс фильтров пересобраны.';
			return $result;
		}

		protected static function rebuildRequest($requestId)
		{
			\App\Content\Requests\RequestConditionCompiler::compile((int) $requestId, true);
		}

		protected static function clearCache()
		{
			DB::clearTags(array('documents', 'requests', 'modules', 'catalog'));
			(new FeedService())->clearAll();
			Cache::forgetTag(CacheKey::tag('catalog'));
		}

		protected static function documentAlias($id)
		{
			return (string) DB::query('SELECT document_alias FROM ' . self::documentsTable() . ' WHERE Id = %i LIMIT 1', (int) $id)->getValue();
		}

		protected static function documentExists($id)
		{
			return (bool) DB::query('SELECT Id FROM ' . self::documentsTable() . " WHERE Id = %i AND document_deleted != '1' LIMIT 1", (int) $id)->getValue();
		}

		protected static function requestsTable() { return ContentTables::table('request'); }
		protected static function conditionsTable() { return ContentTables::table('request_conditions'); }
		protected static function navigationTable() { return ContentTables::table('navigation'); }
		protected static function decode($value)
		{
			return html_entity_decode(stripslashes((string) $value), ENT_QUOTES, 'UTF-8');
		}

		protected static function parentLevel($id)
		{
			return $id > 0 ? (int) DB::query('SELECT level FROM ' . self::itemsTable() . ' WHERE id = %i LIMIT 1', (int) $id)->getValue() : 0;
		}

		protected static function nextPosition($rubricId, $fieldId, $parentId)
		{
			return (int) DB::query('SELECT COALESCE(MAX(position), -1) + 1 FROM ' . self::itemsTable() . ' WHERE rubric_id = %i AND field_id = %i AND parent_id = %i', (int) $rubricId, (int) $fieldId, (int) $parentId)->getValue();
		}

		protected static function ids($value)
		{
			if (!is_array($value)) { $value = preg_split('/[^0-9]+/', (string) $value); }
			$out = array(); foreach ($value ?: array() as $id) { $id = (int) $id; if ($id > 0) { $out[] = $id; } }
			return array_values(array_unique($out));
		}

		protected static function idsString($value) { return implode(',', self::ids($value)); }

		public static function sourceItemsAvailable()
		{
			return DatabaseSchema::columnExists(self::itemsTable(), 'source_item_ids');
		}

		protected static function validSourceItemIds($value, $itemId, $rubricId, $fieldId)
		{
			$ids = array_values(array_diff(self::ids($value), array((int) $itemId)));
			if (!$ids) { return array(); }
			$rows = DB::query(
				'SELECT id FROM ' . self::itemsTable()
					. ' WHERE rubric_id=%i AND field_id=%i AND id IN (' . implode(',', $ids) . ')',
				(int) $rubricId,
				(int) $fieldId
			)->getAll() ?: array();
			$allowed = array();
			foreach ($rows as $row) { $allowed[(int) $row['id']] = true; }
			return array_values(array_filter($ids, function ($id) use ($allowed) {
				return isset($allowed[(int) $id]);
			}));
		}

		protected static function orderedIds($order, array $selected)
		{
			$selectedMap = array_fill_keys($selected, true); $out = array();
			foreach (self::ids($order) as $id) { if (isset($selectedMap[$id])) { $out[] = $id; unset($selectedMap[$id]); } }
			foreach ($selected as $id) { if (isset($selectedMap[$id])) { $out[] = $id; } }
			return $out;
		}

		protected static function filterStyles($styles, array $allowed)
		{
			if (!is_array($styles)) { return array(); }
			$out = array(); foreach ($allowed as $id) { $style = isset($styles[$id]) ? preg_replace('/[^a-z0-9_\-]/i', '', (string) $styles[$id]) : ''; if ($style !== '' && $style !== 'default') { $out[$id] = $style; } }
			return $out;
		}

		protected static function catalogRow(array $row)
		{
			return array(
				'rubric_id' => (int) $row['rubric_id'], 'field_id' => (int) $row['field_id'],
				'rubric_title' => html_entity_decode((string) $row['rubric_title'], ENT_QUOTES, 'UTF-8'),
				'field_title' => html_entity_decode((string) $row['rubric_field_title'], ENT_QUOTES, 'UTF-8'),
				'field_alias' => (string) $row['rubric_field_alias'], 'items_count' => (int) $row['items_count'],
				'active_count' => (int) $row['active_count'], 'configured' => !empty($row['id']),
				'filters_use' => isset($row['filters_use']) ? (int) $row['filters_use'] : 0,
				'doc_fileds' => isset($row['doc_fileds']) ? (int) $row['doc_fileds'] : 0,
				'purpose' => isset($row['purpose']) && $row['purpose'] === 'commerce' ? 'commerce' : 'content',
			);
		}

		protected static function itemRow(array $row)
		{
			$styles = @unserialize(isset($row['filters_settings']) ? (string) $row['filters_settings'] : '', array('allowed_classes' => false));
			return array(
				'id' => (int) $row['id'], 'rubric_id' => (int) $row['rubric_id'], 'field_id' => (int) $row['field_id'],
				'name' => (string) $row['name'], 'parent_id' => (int) $row['parent_id'], 'status' => (int) $row['status'],
				'document_id' => (int) $row['document_id'], 'document_alias' => (string) $row['document_alias'],
				'document_title' => html_entity_decode((string) (!empty($row['document_breadcrumb_title']) ? $row['document_breadcrumb_title'] : (isset($row['document_title']) ? $row['document_title'] : '')), ENT_QUOTES, 'UTF-8'),
				'level' => (int) $row['level'], 'position' => (int) $row['position'],
				'fields_use' => self::ids(isset($row['fields_use']) ? $row['fields_use'] : ''),
				'filters_use' => self::ids(isset($row['filters_use']) ? $row['filters_use'] : ''),
				'source_item_ids' => self::ids(isset($row['source_item_ids']) ? $row['source_item_ids'] : ''),
				'filter_styles' => is_array($styles) ? $styles : array(),
				'attribute_set_id' => isset($row['attribute_set_id']) ? (int) $row['attribute_set_id'] : 0,
				'attributes_runtime' => isset($row['attributes_runtime']) ? (string) $row['attributes_runtime'] : 'legacy',
				'filter_runtime' => isset($row['filter_runtime']) ? (string) $row['filter_runtime'] : 'legacy',
			);
		}

	}
