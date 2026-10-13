<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Navigation/Model.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Adminx\Navigation;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Common\FileCacheInvalidator;
	use App\Content\ContentTables;
	use App\Content\PublicUserTables;
	use App\Helpers\Dir;
	use App\Helpers\File;
	use DB;

	class Model
	{
		public static function table()
		{
			return ContentTables::table('navigation');
		}

		public static function itemsTable()
		{
			return ContentTables::table('navigation_items');
		}

		public static function all($q = '')
		{
			$sql = 'SELECT n.*, COUNT(i.navigation_item_id) AS items_count'
				. ' FROM ' . self::table() . ' n'
				. ' LEFT JOIN ' . self::itemsTable() . ' i ON i.navigation_id = n.navigation_id'
				. ' WHERE 1=1';
			$args = array();
			$q = trim((string) $q);
			if ($q !== '') {
				$sql .= ' AND (n.title LIKE %ss OR n.alias LIKE %ss OR n.navigation_id = %i)';
				$args[] = $q;
				$args[] = $q;
				$args[] = (int) $q;
			}

			$sql .= ' GROUP BY n.navigation_id ORDER BY n.navigation_id ASC';
			$rows = call_user_func_array(array('DB', 'query'), array_merge(array($sql), $args))->getAll();
			$out = array();
			foreach ($rows as $row) {
				$out[] = self::row($row);
			}

			return $out;
		}

		public static function one($id)
		{
			$row = DB::query(
				'SELECT n.*, COUNT(i.navigation_item_id) AS items_count'
				. ' FROM ' . self::table() . ' n'
				. ' LEFT JOIN ' . self::itemsTable() . ' i ON i.navigation_id = n.navigation_id'
				. ' WHERE n.navigation_id = %i'
				. ' GROUP BY n.navigation_id LIMIT 1',
				(int) $id
			)->getAssoc();
			return $row ? self::row($row, true) : null;
		}

		public static function raw($id)
		{
			$row = DB::query('SELECT * FROM ' . self::table() . ' WHERE navigation_id = %i LIMIT 1', (int) $id)->getAssoc();
			return $row ? $row : null;
		}

		public static function snapshot($id)
		{
			$row = self::raw($id);
			if (!$row) { return array(); }
			unset($row['navigation_id']);
			return $row;
		}

		public static function applySnapshot($id, array $snapshot)
		{
			$current = self::raw($id);
			if (!$current) { throw new \RuntimeException('Навигация не найдена'); }
			$data = array();
			foreach (array_merge(array('alias', 'title', 'user_group', 'expand_ext'), self::templateFields()) as $key) {
				if (array_key_exists($key, $snapshot)) { $data[$key] = $snapshot[$key]; }
			}

			if (!$data) { throw new \RuntimeException('Ревизия не содержит данных навигации'); }
			if (isset($data['alias']) && self::aliasExists((string) $data['alias'], (int) $id)) {
				throw new \RuntimeException('Алиас из ревизии уже занят другой навигацией');
			}

			DB::Update(self::table(), $data, 'navigation_id=%i', (int) $id);
			self::clearCache((int) $id, isset($current['alias']) ? $current['alias'] : '');
			self::clearCache((int) $id, isset($data['alias']) ? $data['alias'] : '');
			return (int) $id;
		}

		public static function itemsTree($navigationId)
		{
			$rows = DB::query(
				'SELECT * FROM ' . self::itemsTable()
				. ' WHERE navigation_id = %i'
				. ' ORDER BY level ASC, parent_id ASC, position ASC, navigation_item_id ASC',
				(int) $navigationId
			)->getAll();

			$items = array();
			foreach ($rows as $row) {
				$item = self::itemRow($row);
				$item['children'] = array();
				$items[$item['navigation_item_id']] = $item;
			}

			$tree = array();
			foreach ($items as $id => $item) {
				$parentId = (int) $item['parent_id'];
				if ($parentId > 0 && isset($items[$parentId])) {
					$items[$parentId]['children'][] = &$items[$id];
				} else {
					$tree[] = &$items[$id];
				}
			}

			unset($item);
			return $tree;
		}

		public static function flatItems($navigationId)
		{
			$rows = DB::query(
				'SELECT navigation_item_id, parent_id, level, title, alias, position, status'
				. ' FROM ' . self::itemsTable()
				. ' WHERE navigation_id = %i'
				. ' ORDER BY level ASC, parent_id ASC, position ASC, navigation_item_id ASC',
				(int) $navigationId
			)->getAll();
			$out = array();
			foreach ($rows as $row) {
				$out[] = self::itemRow($row);
			}

			return $out;
		}

		public static function item($id)
		{
			$row = DB::query(
				'SELECT i.*, d.document_title, d.document_alias'
				. ' FROM ' . self::itemsTable() . ' i'
				. ' LEFT JOIN ' . ContentTables::table('documents') . ' d ON d.Id = i.document_id'
				. ' WHERE i.navigation_item_id = %i LIMIT 1',
				(int) $id
			)->getAssoc();
			return $row ? self::itemRow($row) : null;
		}

		public static function documentPicker($q = '', $limit = 30)
		{
			return (new \App\Content\Documents\DocumentPickerRepository())->search($q, array(), $limit);
		}

		public static function stats()
		{
			return array(
				'total' => (int) DB::query('SELECT COUNT(*) FROM ' . self::table())->getValue(),
				'items' => (int) DB::query('SELECT COUNT(*) FROM ' . self::itemsTable())->getValue(),
				'aliases' => (int) DB::query('SELECT COUNT(*) FROM ' . self::table() . ' WHERE alias != %s', '')->getValue(),
				'restricted' => (int) DB::query('SELECT COUNT(*) FROM ' . self::table() . ' WHERE user_group != %s', '')->getValue(),
			);
		}

		public static function accessGroups()
		{
			$rows = DB::query(
				'SELECT user_group AS id, user_group_name AS name, status'
				. ' FROM ' . PublicUserTables::table('user_groups')
				. ' ORDER BY user_group ASC'
			)->getAll() ?: array();

			foreach ($rows as &$row) {
				$row['id'] = (int) $row['id'];
				$row['name'] = self::decode(isset($row['name']) ? $row['name'] : '');
				$row['active'] = isset($row['status']) && (string) $row['status'] === '1';
			}

			unset($row);
			return $rows;
		}

		public static function aliasExists($alias, $excludeId = 0)
		{
			return (bool) DB::query(
				'SELECT navigation_id FROM ' . self::table() . ' WHERE alias = %s AND navigation_id != %i LIMIT 1',
				(string) $alias,
				(int) $excludeId
			)->getValue();
		}

		public static function save($id, array $input)
		{
			$data = self::input($input);
			if ((int) $id > 0) {
				$current = self::raw($id);
				DB::Update(self::table(), $data, 'navigation_id = %i', (int) $id);
				self::clearCache((int) $id, isset($current['alias']) ? $current['alias'] : '');
				self::clearCache((int) $id, $data['alias']);
				return (int) $id;
			}

			DB::Insert(self::table(), $data);
			$newId = (int) DB::insertId();
			self::clearCache($newId, $data['alias']);
			return $newId;
		}

		public static function saveItem($id, $navigationId, array $input)
		{
			$data = self::itemInput($input);
			if ((int) $id > 0) {
				$current = self::item($id);
				if (!$current) {
					throw new \RuntimeException('Пункт навигации не найден');
				}

				$navigationId = (int) $current['navigation_id'];
				if (!array_key_exists('status', $input)) {
					$data['status'] = (string) $current['status'];
				}

				self::assertItemParent($navigationId, (int) $id, (int) $data['parent_id']);
				$data['navigation_id'] = $navigationId;
				DB::startTransaction();
				try {
					DB::Update(self::itemsTable(), $data, 'navigation_item_id = %i', (int) $id);
					self::synchronizeItemLevels($navigationId);
					DB::commit();
				} catch (\Throwable $e) {
					DB::rollback();
					throw $e;
				}

				self::clearCache($navigationId, self::navigationAlias($navigationId));
				return (int) $id;
			}

			$navigationId = (int) $navigationId;
			self::assertItemParent($navigationId, 0, (int) $data['parent_id']);
			$data['navigation_id'] = $navigationId;
			$data['position'] = self::nextItemPosition($navigationId, (int) $data['parent_id']);
			DB::startTransaction();
			try {
				DB::Insert(self::itemsTable(), $data);
				$newId = (int) DB::insertId();
				self::synchronizeItemLevels($navigationId);
				DB::commit();
			} catch (\Throwable $e) {
				DB::rollback();
				throw $e;
			}

			self::clearCache($navigationId, self::navigationAlias($navigationId));
			return $newId;
		}

		public static function toggleItem($id)
		{
			$item = self::item($id);
			if (!$item) {
				return null;
			}

			$status = (string) $item['status'] === '1' ? '0' : '1';
			DB::Update(self::itemsTable(), array('status' => $status), 'navigation_item_id = %i', (int) $id);
			self::clearCache((int) $item['navigation_id'], self::navigationAlias((int) $item['navigation_id']));
			return $status;
		}

		public static function deleteItem($id)
		{
			$item = self::item($id);
			if (!$item) {
				return false;
			}

			$children = (int) DB::query('SELECT COUNT(*) FROM ' . self::itemsTable() . ' WHERE parent_id = %i', (int) $id)->getValue();
			if ($children > 0) {
				DB::Update(self::itemsTable(), array('status' => '0'), 'navigation_item_id = %i', (int) $id);
			} else {
				DB::Delete(self::itemsTable(), 'navigation_item_id = %i', (int) $id);
			}

			self::clearCache((int) $item['navigation_id'], self::navigationAlias((int) $item['navigation_id']));
			return true;
		}

		public static function reorderItems($navigationId, array $order)
		{
			$navigationId = (int) $navigationId;
			$rows = DB::query(
				'SELECT navigation_item_id FROM ' . self::itemsTable() . ' WHERE navigation_id=%i',
				$navigationId
			)->getAll();
			$known = array();
			foreach ($rows ?: array() as $row) {
				$known[(int) $row['navigation_item_id']] = true;
			}

			$submitted = array();
			$positions = array();
			foreach ($order as $row) {
				if (!is_array($row) || empty($row['id'])) { continue; }
				$id = (int) $row['id'];
				$parentId = isset($row['parent_id']) ? (int) $row['parent_id'] : 0;
				if (!isset($known[$id]) || isset($submitted[$id])) {
					throw new \InvalidArgumentException('Порядок содержит неизвестный или повторяющийся пункт');
				}

				if ($parentId > 0 && !isset($known[$parentId])) {
					throw new \InvalidArgumentException('Родитель пункта не принадлежит этой навигации');
				}

				$positionKey = (string) $parentId;
				$positions[$positionKey] = isset($positions[$positionKey]) ? $positions[$positionKey] + 1 : 1;
				$submitted[$id] = array('parent_id' => $parentId, 'position' => $positions[$positionKey]);
			}

			if (count($submitted) !== count($known)) {
				throw new \InvalidArgumentException('Передан неполный порядок пунктов');
			}

			$levels = array();
			$visiting = array();
			foreach (array_keys($submitted) as $id) {
				self::submittedItemLevel($id, $submitted, $levels, $visiting);
			}

			DB::startTransaction();
			try {
				foreach ($submitted as $id => $row) {
					DB::Update(self::itemsTable(), array(
						'parent_id' => $row['parent_id'],
						'level' => (string) $levels[$id],
						'position' => $row['position'],
					), 'navigation_item_id = %i AND navigation_id = %i', $id, $navigationId);
				}

				DB::commit();
			} catch (\Throwable $e) {
				DB::rollback();
				throw $e;
			}

			self::clearCache($navigationId, self::navigationAlias($navigationId));
			return true;
		}

		public static function copy($id, $title)
		{
			$row = self::raw($id);
			if (!$row) {
				throw new \RuntimeException('Навигация не найдена');
			}

			unset($row['navigation_id']);
			$title = trim((string) $title);
			$row['title'] = $title !== '' ? $title : self::uniqueTitle($row['title'] . ' (копия)');
			$row['alias'] = '';
			DB::Insert(self::table(), $row);
			return (int) DB::insertId();
		}

		public static function delete($id)
		{
			$id = (int) $id;
			if ($id === 1) {
				throw new \RuntimeException('Навигация #1 является системной и не может быть удалена');
			}

			$row = self::raw($id);
			if (!$row) {
				return false;
			}

			$dependencies = \App\Content\ContentTagDependencies::navigation(
				$id,
				isset($row['alias']) ? (string) $row['alias'] : ''
			);
			if ($dependencies) {
				throw new \RuntimeException(
					'Навигация используется: ' . implode(', ', $dependencies) . '. Сначала уберите эти связи.'
				);
			}

			self::clearCache($id, isset($row['alias']) ? $row['alias'] : '');
			DB::Delete(self::itemsTable(), 'navigation_id = %i', $id);
			DB::Delete(self::table(), 'navigation_id = %i', $id);
			return true;
		}

		public static function clearCache($id, $alias = '')
		{
			$id = (int) $id;
			$keys = array((string) (int) $id);
			$alias = trim((string) $alias);
			if ($alias !== '') {
				$keys[] = $alias;
			}

			foreach (array_unique($keys) as $key) {
				self::removeFile(self::cacheDir($key) . '/template.cache');
				self::removeFile(self::cacheDir($key) . '/items.cache');
				self::removeDir(self::cacheDir($key));
			}

			FileCacheInvalidator::navigation($id, $alias);
		}

		protected static function input(array $input)
		{
			$title = trim(isset($input['title']) ? (string) $input['title'] : '');
			$expand = isset($input['expand_ext']) ? (string) $input['expand_ext'] : '1';
			if (!in_array($expand, array('0', '1', '2'), true)) {
				$expand = '1';
			}

			$defaults = self::defaultTemplates();
			if ($title === '') {
				$title = 'title';
			}

			foreach ($defaults as $key => $value) {
				if (!array_key_exists($key, $input)) {
					$input[$key] = $value;
				}
			}

			return array(
				'alias' => trim(isset($input['alias']) ? (string) $input['alias'] : ''),
				'title' => $title,
				'level1' => (string) $input['level1'],
				'level2' => isset($input['level2']) ? (string) $input['level2'] : '',
				'level3' => isset($input['level3']) ? (string) $input['level3'] : '',
				'level1_active' => (string) $input['level1_active'],
				'level2_active' => isset($input['level2_active']) ? (string) $input['level2_active'] : '',
				'level3_active' => isset($input['level3_active']) ? (string) $input['level3_active'] : '',
				'level1_begin' => isset($input['level1_begin']) ? (string) $input['level1_begin'] : '',
				'level1_end' => isset($input['level1_end']) ? (string) $input['level1_end'] : '',
				'level2_begin' => isset($input['level2_begin']) ? (string) $input['level2_begin'] : '',
				'level2_end' => isset($input['level2_end']) ? (string) $input['level2_end'] : '',
				'level3_begin' => isset($input['level3_begin']) ? (string) $input['level3_begin'] : '',
				'level3_end' => isset($input['level3_end']) ? (string) $input['level3_end'] : '',
				'begin' => isset($input['begin']) ? (string) $input['begin'] : '',
				'end' => isset($input['end']) ? (string) $input['end'] : '',
				'user_group' => self::normalizeUserGroups(isset($input['user_group']) ? $input['user_group'] : ''),
				'expand_ext' => $expand,
			);
		}

		protected static function defaultTemplates()
		{
			return array(
				'begin' => '<nav class="site-navigation" aria-label="Основная навигация">',
				'end' => '</nav>',
				'level1_begin' => '<ul class="site-navigation-list">[tag:content]',
				'level1' => '<li class="site-navigation-item"><a target="[tag:target]" href="[tag:link]">[tag:linkname]</a></li>',
				'level1_active' => '<li class="site-navigation-item is-active"><a target="[tag:target]" href="[tag:link]" aria-current="page">[tag:linkname]</a></li>',
				'level1_end' => '</ul>',
			);
		}

		protected static function itemInput(array $input)
		{
			$alias = isset($input['alias']) ? (string) $input['alias'] : '';
			if (strpos($alias, 'javascript') !== false) {
				$alias = str_replace(array(' ', '%'), '-', $alias);
			}

			$target = isset($input['target']) ? (string) $input['target'] : '_self';
			if (!in_array($target, array('_self', '_blank', '_parent', '_top'), true)) {
				$target = '_self';
			}

			$parentId = isset($input['parent_id']) ? (int) $input['parent_id'] : 0;
			$cssClass = isset($input['css_class']) ? (string) $input['css_class'] : '';
			if (array_key_exists('panel_source', $input)) {
				$cssClass = self::applyPanelSource($cssClass, (string) $input['panel_source']);
			}

			return array(
				'document_id' => isset($input['document_id']) && (int) $input['document_id'] > 0 ? (int) $input['document_id'] : null,
				'alias' => $alias,
				'title' => trim(isset($input['title']) ? (string) $input['title'] : ''),
				'description' => isset($input['description']) ? (string) $input['description'] : '',
				'target' => $target,
				'image' => isset($input['image']) ? (string) $input['image'] : '',
				'css_style' => isset($input['css_style']) ? (string) $input['css_style'] : '',
				'css_id' => isset($input['css_id']) ? (string) $input['css_id'] : '',
				'css_class' => $cssClass,
				'parent_id' => $parentId,
				'level' => '1',
				'position' => isset($input['position']) ? (int) $input['position'] : 1,
				'status' => trim($alias) === '' ? '0' : '1',
			);
		}

		protected static function applyPanelSource($cssClass, $source)
		{
			$classes = preg_split('/\s+/', trim((string) $cssClass)) ?: array();
			$classes = array_values(array_filter($classes, function ($class) {
				return $class !== 'catalog-menu-link'
					&& $class !== 'catalog-menu-panel'
					&& strpos($class, 'catalog-menu-source-') !== 0;
			}));

			$source = strtolower(trim((string) $source));
			if ($source === 'children') {
				$classes[] = 'catalog-menu-panel';
			} elseif (preg_match('/^[a-z][a-z0-9_-]{1,31}$/', $source)) {
				$classes[] = 'catalog-menu-source-' . $source;
			}

			return implode(' ', array_values(array_unique(array_filter($classes))));
		}

		protected static function row(array $row, $withCode = false)
		{
			$id = (int) $row['navigation_id'];
			$alias = trim((string) $row['alias']);
			$key = $alias !== '' ? $alias : (string) $id;
			$row['navigation_id'] = $id;
			$row['id'] = $id;
			$row['alias'] = $alias;
			$row['items_count'] = isset($row['items_count']) ? (int) $row['items_count'] : self::itemsCount($id);
			$row['tag'] = '[tag:navigation:' . $key . ']';
			$row['expand_label'] = self::expandLabel(isset($row['expand_ext']) ? (string) $row['expand_ext'] : '1');
			$row['has_groups'] = trim((string) $row['user_group']) !== '';
			$row['can_delete'] = $id !== 1;
			if (!$withCode) {
				foreach (self::templateFields() as $field) {
					unset($row[$field]);
				}
			}

			return $row;
		}

		protected static function itemRow(array $row)
		{
			$row['navigation_item_id'] = (int) $row['navigation_item_id'];
			$row['id'] = $row['navigation_item_id'];
			$row['navigation_id'] = isset($row['navigation_id']) ? (int) $row['navigation_id'] : 0;
			$row['document_id'] = isset($row['document_id']) ? (int) $row['document_id'] : 0;
			$row['panel_source'] = self::panelSource(isset($row['css_class']) ? $row['css_class'] : '');
			$row['parent_id'] = isset($row['parent_id']) ? (int) $row['parent_id'] : 0;
			$row['level'] = isset($row['level']) ? (string) $row['level'] : '1';
			$row['position'] = isset($row['position']) ? (int) $row['position'] : 1;
			$row['status'] = isset($row['status']) ? (string) $row['status'] : '1';
			if (array_key_exists('document_title', $row)) {
				$row['document_title'] = self::decode($row['document_title']);
			}

			return $row;
		}

		protected static function panelSource($cssClass)
		{
			$cssClass = trim((string) $cssClass);
			if (preg_match('/(?:^|\s)catalog-menu-source-([a-z][a-z0-9_-]{1,31})(?:\s|$)/', $cssClass, $match)) {
				return (string) $match[1];
			}

			return preg_match('/(?:^|\s)catalog-menu-panel(?:\s|$)/', $cssClass) ? 'children' : 'link';
		}

		protected static function decode($value)
		{
			return html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');
		}

		protected static function normalizeUserGroups($value)
		{
			$values = is_array($value) ? $value : explode(',', (string) $value);
			$selected = array();
			foreach ($values as $groupId) {
				$groupId = (int) trim((string) $groupId);
				if ($groupId > 0) {
					$selected[$groupId] = true;
				}
			}

			$valid = array();
			foreach (self::accessGroups() as $group) {
				if (isset($selected[(int) $group['id']])) {
					$valid[] = (int) $group['id'];
				}
			}

			return implode(',', $valid);
		}

		protected static function resolveLevel($parentId)
		{
			$parentId = (int) $parentId;
			if ($parentId <= 0) {
				return '1';
			}

			$parent = self::item($parentId);
			if (!$parent) {
				return '1';
			}

			$level = min(3, max(1, (int) $parent['level'] + 1));
			return (string) $level;
		}

		protected static function assertItemParent($navigationId, $itemId, $parentId)
		{
			if ($parentId <= 0) { return; }
			$rows = DB::query(
				'SELECT navigation_item_id,parent_id FROM ' . self::itemsTable() . ' WHERE navigation_id=%i',
				(int) $navigationId
			)->getAll();
			$parents = array();
			foreach ($rows ?: array() as $row) {
				$parents[(int) $row['navigation_item_id']] = (int) $row['parent_id'];
			}

			if (!isset($parents[$parentId])) {
				throw new \InvalidArgumentException('Выбранный родитель не принадлежит этой навигации');
			}

			$seen = array();
			$cursor = $parentId;
			$level = 1;
			while ($cursor > 0) {
				if ($cursor === (int) $itemId || isset($seen[$cursor])) {
					throw new \InvalidArgumentException('Нельзя переместить пункт внутрь собственной ветки');
				}

				$seen[$cursor] = true;
				$level++;
				if ($level > 3) {
					throw new \InvalidArgumentException('Меню поддерживает не более трёх уровней');
				}

				$cursor = isset($parents[$cursor]) ? (int) $parents[$cursor] : 0;
			}
		}

		protected static function synchronizeItemLevels($navigationId)
		{
			$rows = DB::query(
				'SELECT navigation_item_id,parent_id FROM ' . self::itemsTable() . ' WHERE navigation_id=%i',
				(int) $navigationId
			)->getAll();
			$submitted = array();
			foreach ($rows ?: array() as $row) {
				$submitted[(int) $row['navigation_item_id']] = array('parent_id' => (int) $row['parent_id']);
			}

			$levels = array();
			$visiting = array();
			foreach (array_keys($submitted) as $id) {
				self::submittedItemLevel($id, $submitted, $levels, $visiting);
			}

			foreach ($levels as $id => $level) {
				DB::Update(self::itemsTable(), array('level' => (string) $level), 'navigation_item_id=%i', (int) $id);
			}
		}

		protected static function submittedItemLevel($id, array $items, array &$levels, array &$visiting)
		{
			$id = (int) $id;
			if (isset($levels[$id])) { return $levels[$id]; }
			if (isset($visiting[$id])) {
				throw new \InvalidArgumentException('В структуре меню обнаружен цикл');
			}

			if (!isset($items[$id])) {
				throw new \InvalidArgumentException('Пункт меню не найден');
			}

			$visiting[$id] = true;
			$parentId = isset($items[$id]['parent_id']) ? (int) $items[$id]['parent_id'] : 0;
			if ($parentId > 0 && !isset($items[$parentId])) {
				throw new \InvalidArgumentException('Родитель пункта меню не найден');
			}

			$level = $parentId > 0 ? self::submittedItemLevel($parentId, $items, $levels, $visiting) + 1 : 1;
			unset($visiting[$id]);
			if ($level > 3) {
				throw new \InvalidArgumentException('Меню поддерживает не более трёх уровней');
			}

			$levels[$id] = $level;
			return $level;
		}

		protected static function nextItemPosition($navigationId, $parentId)
		{
			return (int) DB::query(
				'SELECT COALESCE(MAX(position), 0) + 1 FROM ' . self::itemsTable() . ' WHERE navigation_id = %i AND parent_id = %i',
				(int) $navigationId,
				(int) $parentId
			)->getValue();
		}

		protected static function navigationAlias($navigationId)
		{
			return (string) DB::query('SELECT alias FROM ' . self::table() . ' WHERE navigation_id = %i LIMIT 1', (int) $navigationId)->getValue();
		}

		protected static function itemsCount($id)
		{
			return (int) DB::query('SELECT COUNT(*) FROM ' . self::itemsTable() . ' WHERE navigation_id = %i', (int) $id)->getValue();
		}

		protected static function templateFields()
		{
			return array('level1', 'level2', 'level3', 'level1_active', 'level2_active', 'level3_active', 'level1_begin', 'level1_end', 'level2_begin', 'level2_end', 'level3_begin', 'level3_end', 'begin', 'end');
		}

		protected static function expandLabel($value)
		{
			if ((string) $value === '0') {
				return 'Активная ветка';
			}

			if ((string) $value === '2') {
				return 'Текущий уровень';
			}

			return 'Все уровни';
		}

		protected static function uniqueTitle($base)
		{
			$base = trim((string) $base);
			if ($base === '') {
				$base = 'Новая навигация';
			}

			$title = $base;
			$i = 2;
			while (DB::query('SELECT navigation_id FROM ' . self::table() . ' WHERE title = %s LIMIT 1', $title)->getValue()) {
				$title = $base . ' #' . $i;
				$i++;
			}

			return $title;
		}

		protected static function cacheDir($key)
		{
			return BASEPATH . '/tmp/cache/sql/navigations/' . trim((string) $key, '/');
		}

		protected static function removeFile($file)
		{
			if (is_file($file)) {
				File::delete($file);
			}
		}

		protected static function removeDir($dir)
		{
			if (!is_dir($dir)) {
				return;
			}

			Dir::delete($dir);
		}
	}
