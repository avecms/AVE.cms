<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Content/Documents/DocumentSnapshotBuilder.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Content\Documents;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Content\ContentTables;
	use App\Content\Fields\FieldRegistry;
	use App\Content\Fields\FieldValueCodec;
	use App\Content\Fields\MediaFieldValue;
	use App\Content\Fields\DocumentRelationValue;
	use App\Content\Fields\DocumentRelationIndex;
	use App\Common\Lifecycle;
	use DB;

	/** Builds the canonical document read model from authoritative DB rows. */
	class DocumentSnapshotBuilder
	{
		protected $store;
		protected $schemas;

		public function __construct(DocumentSnapshotStore $store = null, RubricSchemaBuilder $schemas = null)
		{
			$this->store=$store?:new DocumentSnapshotStore();$this->schemas=$schemas?:new RubricSchemaBuilder($this->store);
		}

		public function build($documentId)
		{
			$documentId = (int) $documentId;
			$snapshots = $this->buildMany(array($documentId));

			return isset($snapshots[$documentId]) ? $snapshots[$documentId] : null;
		}

		public function buildMany(array $documentIds)
		{
			$ids = array_values(array_unique(array_filter(array_map('intval', $documentIds), function ($id) {
				return $id > 0;
			})));
			if (!$ids) {
				return array();
			}

			$events = array();
			$snapshots = $this->store->withDocumentLocks($ids, function () use ($ids, &$events) {
				$documents = $this->documents($ids);
				$fieldRows = $this->fieldRows($ids);
				$revisions = $this->revisions($ids);
				$result = array();

				foreach ($ids as $documentId) {
					$document = isset($documents[$documentId]) ? $documents[$documentId] : null;
					if (!$document || !empty($document['document_deleted'])) {
						$this->store->delete($documentId);
						DocumentRelationIndex::removeSource($documentId);
						$result[$documentId] = null;
						continue;
					}

					$schema = $this->schemas->get((int) $document['rubric_id']);
					$fields = array();
					$aliases = array();
					foreach (isset($fieldRows[$documentId]) ? $fieldRows[$documentId] : array() as $row) {
						$fieldId = (int) $row['rubric_field_id'];
						$definition = isset($schema['fields'][(string) $fieldId]) ? $schema['fields'][(string) $fieldId] : array();
						$type = isset($definition['rubric_field_type']) ? (string) $definition['rubric_field_type'] : '';
						$alias = isset($definition['rubric_field_alias']) ? trim((string) $definition['rubric_field_alias']) : '';
						$raw = (string) $row['field_value'] . (string) $row['field_value_more'];
						$fields[(string) $fieldId] = array(
							'id' => $fieldId,
							'row_id' => (int) $row['Id'],
							'alias' => $alias,
							'type' => $type,
							'raw' => $raw,
							'storage_value' => (string) $row['field_value'],
							'text_value' => (string) $row['field_value_more'],
							'value' => $this->normalize($type, $raw, $row),
							'number' => isset($row['field_number_value']) && $row['field_number_value'] !== null
								? (float) $row['field_number_value']
								: null,
						);
						if (DocumentRelationValue::supports($type)) {
							$fields[(string) $fieldId]['relation'] = DocumentRelationValue::normalize($type, $raw);
						}

						if ($alias !== '') {
							$aliases[$alias] = $fieldId;
						}
					}

					$snapshot = array(
						'format' => DocumentSnapshotStore::FORMAT,
						'schema_version' => DocumentSnapshotStore::VERSION,
						'generated_at' => time(),
						'document_revision' => isset($revisions[$documentId]) ? $revisions[$documentId] : 0,
						'rubric_schema_version' => (string) $schema['version'],
						'template_set_version' => FieldTemplateManifest::version(),
						'document' => $this->documentData($document),
						'record' => $document,
						'fields' => $fields,
						'field_aliases' => $aliases,
					);
					$this->store->write($documentId, $snapshot);
					DocumentRelationIndex::sync($documentId, $fields);
					$result[$documentId] = $snapshot;
					$events[$documentId] = $snapshot;
				}

				return $result;
			});

			foreach ($events as $documentId => $snapshot) {
				Lifecycle::event(
					'content.document.snapshot_built',
					'document_snapshot',
					'built',
					$documentId,
					array('document_id' => $documentId),
					$snapshot,
					array(),
					'document_snapshot_builder'
				);
			}

			return $snapshots;
		}

		protected function documents(array $ids)
		{
			$rows = DB::query(
				'SELECT * FROM ' . ContentTables::table('documents') . ' WHERE Id IN %li',
				$ids
			)->getAll();
			$result = array();
			foreach ($rows ?: array() as $row) {
				$result[(int) $row['Id']] = $row;
			}

			return $result;
		}

		protected function fieldRows(array $ids)
		{
			$rows = DB::query(
				'SELECT df.Id,df.document_id,df.rubric_field_id,df.field_value,df.field_number_value,'
				. 'COALESCE(dft.field_value,\'\') field_value_more FROM '
				. ContentTables::table('document_fields') . ' df LEFT JOIN '
				. ContentTables::table('document_fields_text')
				. ' dft ON dft.document_id=df.document_id AND dft.rubric_field_id=df.rubric_field_id'
				. ' WHERE df.document_id IN %li ORDER BY df.document_id,df.rubric_field_id',
				$ids
			)->getAll();
			$result = array();
			foreach ($rows ?: array() as $row) {
				$documentId = (int) $row['document_id'];
				if (!isset($result[$documentId])) {
					$result[$documentId] = array();
				}

				$result[$documentId][] = $row;
			}

			return $result;
		}

		protected function revisions(array $ids)
		{
			$rows = DB::query(
				'SELECT doc_id,COALESCE(MAX(doc_revision),0) revision FROM '
				. ContentTables::table('document_rev') . ' WHERE doc_id IN %li GROUP BY doc_id',
				$ids
			)->getAll();
			$result = array();
			foreach ($rows ?: array() as $row) {
				$result[(int) $row['doc_id']] = (int) $row['revision'];
			}

			return $result;
		}

		protected function normalize($type,$raw,array $row)
		{
			if($type==='image_single'){return MediaFieldValue::imageSingle($raw);}if($type==='image_multi'){return MediaFieldValue::imageMulti($raw);}if($type==='image_mega'){return MediaFieldValue::imageMega($raw);}if(in_array($type,array('download','doc_files'),true)){return $type==='download'?MediaFieldValue::download($raw):MediaFieldValue::imageMulti($raw);}
			$structured=FieldValueCodec::decodeStructured($raw,null);if(is_array($structured)){return $structured;}
			$fieldType=FieldRegistry::get($type);if($fieldType&&$fieldType->isNumeric()){return isset($row['field_number_value'])&&$row['field_number_value']!==null?(float)$row['field_number_value']:(float)str_replace(',','.',trim((string)$raw));}
			if($type==='checkbox'){return in_array(strtolower(trim((string)$raw)),array('1','true','yes','on','да'),true);}return (string)$raw;
		}

		protected function documentData(array $row)
		{
			$excerpt=isset($row['document_excerpt'])?(string)$row['document_excerpt']:(isset($row['document_teaser'])?(string)$row['document_teaser']:'');
			return array('id'=>(int)$row['Id'],'rubric_id'=>(int)$row['rubric_id'],'rubric_template_id'=>(int)$row['rubric_tmpl_id'],'title'=>htmlspecialchars_decode((string)$row['document_title'],ENT_QUOTES),'alias'=>(string)$row['document_alias'],'excerpt'=>$excerpt,'status'=>(int)$row['document_status'],'deleted'=>(int)$row['document_deleted'],'in_search'=>(int)$row['document_in_search'],'is_technical'=>!empty($row['document_is_technical']),'in_sitemap'=>!isset($row['document_in_sitemap'])||!empty($row['document_in_sitemap']),'published_at'=>(int)$row['document_published'],'expire_at'=>(int)$row['document_expire'],'changed_at'=>(int)$row['document_changed'],'author_id'=>(int)$row['document_author_id'],'meta'=>array('title'=>htmlspecialchars_decode((string)$row['document_title'],ENT_QUOTES),'description'=>(string)$row['document_meta_description'],'keywords'=>(string)$row['document_meta_keywords'],'robots'=>(string)$row['document_meta_robots'],'sitemap_frequency'=>(int)$row['document_sitemap_freq'],'sitemap_priority'=>(float)$row['document_sitemap_pr']));
		}
	}
