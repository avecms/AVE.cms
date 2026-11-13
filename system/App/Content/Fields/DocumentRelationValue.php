<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Content/Fields/DocumentRelationValue.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Content\Fields;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	/** Canonical read model and legacy-storage adapter for document relation fields. */
	class DocumentRelationValue
	{
		const FORMAT = 'ave.document-relation';
		const VERSION = 1;

		protected static $singleTypes = array('doc_from_rub');
		protected static $multipleTypes = array(
			'doc_from_rub_all', 'doc_from_rub_check', 'doc_from_rub_multi',
			'doc_from_rub_search', 'analoque', 'teasers',
		);

		public static function supports($type)
		{
			$type = (string) $type;
			return in_array($type, self::$singleTypes, true) || in_array($type, self::$multipleTypes, true);
		}

		public static function isMultiple($type)
		{
			return in_array((string) $type, self::$multipleTypes, true);
		}

		public static function normalize($type, $value)
		{
			$type = (string) $type;
			$ids = self::ids($value);
			if (!self::isMultiple($type) && $ids) { $ids = array($ids[0]); }
			$items = array();
			foreach ($ids as $position => $documentId) {
				$items[] = array('document_id' => (int) $documentId, 'position' => (int) $position);
			}

			return array(
				'format' => self::FORMAT,
				'version' => self::VERSION,
				'multiple' => self::isMultiple($type),
				'items' => $items,
			);
		}

		public static function ids($value)
		{
			$decoded = FieldValueCodec::decodeStructured($value, null);
			$source = is_array($decoded) ? $decoded : $value;
			$ids = array();
			self::collect($source, $ids);
			return array_values($ids);
		}

		public static function encodeStorage($type, $value)
		{
			$ids = self::ids($value);
			if (!self::isMultiple($type)) { return $ids ? (string) $ids[0] : ''; }
			return $ids ? FieldValueCodec::normalizeForStorage($ids) : '';
		}

		protected static function collect($value, array &$ids, $key = null)
		{
			if (is_array($value)) {
				if (isset($value['format']) && (string) $value['format'] === self::FORMAT && isset($value['items'])) {
					self::collect($value['items'], $ids);
					return;
				}

				foreach (array('document_id', 'id', 'Id') as $idKey) {
					if (isset($value[$idKey])) {
						self::collect($value[$idKey], $ids, $idKey);
						return;
					}
				}

				foreach ($value as $itemKey => $item) { self::collect($item, $ids, $itemKey); }
				return;
			}

			if (is_object($value)) {
				self::collect((array) $value, $ids, $key);
				return;
			}

			$raw = trim((string) $value);
			if ($raw === '') { return; }
			$candidates = array();
			if (in_array((string) $key, array('document_id', 'id', 'Id'), true) && ctype_digit($raw)) {
				$candidates[] = (int) $raw;
			} elseif (ctype_digit($raw)) {
				$candidates[] = (int) $raw;
			} elseif (preg_match('/^[0-9|,;\s]+$/D', $raw)) {
				foreach (preg_split('/[|,;\s]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) as $part) {
					$candidates[] = (int) $part;
				}
			} else {
				foreach (preg_split('/[,;\s]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) as $part) {
					if (preg_match('/(?:^|\|)(\d+)(?:\|)?$/', trim((string) $part), $match)) {
						$candidates[] = (int) $match[1];
					}
				}
			}

			foreach ($candidates as $documentId) {
				if ($documentId > 0 && !isset($ids[$documentId])) { $ids[$documentId] = $documentId; }
			}
		}
	}
