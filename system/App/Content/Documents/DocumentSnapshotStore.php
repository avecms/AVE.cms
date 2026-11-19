<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Content/Documents/DocumentSnapshotStore.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Content\Documents;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Common\Lock;
	use App\Common\RuntimeDirectoryGuard;
	use App\Helpers\Dir;
	use App\Helpers\File;
	use App\Helpers\Json;

	/** Atomic JSON storage outside the public content tree. */
	class DocumentSnapshotStore
	{
		const FORMAT = 'ave.document-snapshot';
		const VERSION = 5;
		const MAX_BYTES = 16777216;

		public function read($documentId)
		{
			return $this->readPath($this->documentPath($documentId));
		}

		public function write($documentId, array $snapshot)
		{
			return $this->writePath($this->documentPath($documentId), $snapshot);
		}

		public function delete($documentId)
		{
			$path = $this->documentPath($documentId);
			return !is_file($path) || File::delete($path);
		}

		public function readSchema($rubricId)
		{
			return $this->readPath($this->schemaPath($rubricId));
		}

		public function writeSchema($rubricId, array $schema)
		{
			return $this->writePath($this->schemaPath($rubricId), $schema);
		}

		public function deleteSchema($rubricId)
		{
			$path = $this->schemaPath($rubricId);
			return !is_file($path) || File::delete($path);
		}

		public function clear()
		{
			$root = $this->root();
			if (is_dir($root)) {
				Dir::delete($root);
			}

			return !is_dir($root);
		}

		public function withDocumentLock($documentId, callable $callback)
		{
			return Lock::run('document-snapshot:' . (int) $documentId, $callback);
		}

		public function withDocumentLocks(array $documentIds, callable $callback)
		{
			$ids = array_values(array_unique(array_filter(array_map('intval', $documentIds), function ($id) {
				return $id > 0;
			})));
			sort($ids, SORT_NUMERIC);

			$lock = function ($offset) use (&$lock, $ids, $callback) {
				if (!isset($ids[$offset])) {
					return $callback();
				}

				return Lock::run('document-snapshot:' . $ids[$offset], function () use (&$lock, $offset) {
					return $lock($offset + 1);
				});
			};

			return $lock(0);
		}

		public function status($documentId)
		{
			$path = $this->documentPath($documentId);
			$data = $this->readPath($path);
			return array(
				'exists' => is_file($path),
				'valid' => is_array($data),
				'path' => $path,
				'size' => is_file($path) ? (int) filesize($path) : 0,
				'generated_at' => is_array($data) && isset($data['generated_at']) ? (int) $data['generated_at'] : 0,
				'rubric_schema_version' => is_array($data) && isset($data['rubric_schema_version']) ? (string) $data['rubric_schema_version'] : '',
				'template_set_version' => is_array($data) && isset($data['template_set_version']) ? (string) $data['template_set_version'] : '',
			);
		}

		protected function readPath($path)
		{
			if (!is_file($path) || filesize($path) > self::MAX_BYTES) { return null; }
			$raw = File::getContent($path);
			if ($raw === false) { return null; }
			$data = Json::toArray($raw);
			return $data !== array() || trim((string) $raw) === '[]' ? $data : null;
		}

		protected function writePath($path, array $data)
		{
			$json = Json::encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			if (strlen($json) > self::MAX_BYTES || strpos($json, '["jsonError",') === 0) {
				throw new \RuntimeException('JSON-снимок документа превышает допустимый размер или содержит некорректные данные');
			}

			$directory = dirname($path);
			if (!RuntimeDirectoryGuard::protect($directory)) {
				throw new \RuntimeException('Не удалось подготовить каталог JSON-снимков');
			}

			if (!File::putAtomic($path, $json)) {
				throw new \RuntimeException('Не удалось атомарно записать JSON-снимок');
			}

			return true;
		}

		protected function documentPath($documentId)
		{
			$id = max(0, (int) $documentId);
			return $this->root() . '/documents/' . str_pad((string) ($id % 1000), 3, '0', STR_PAD_LEFT) . '/' . $id . '.json';
		}

		protected function schemaPath($rubricId)
		{
			return $this->root() . '/rubrics/' . max(0, (int) $rubricId) . '.json';
		}

		protected function root()
		{
			return rtrim(str_replace('\\', '/', BASEPATH), '/') . '/tmp/cache/content-snapshots';
		}
	}
