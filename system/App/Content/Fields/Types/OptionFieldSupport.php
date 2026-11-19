<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Content/Fields/Types/OptionFieldSupport.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Content\Fields\Types;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Content\Fields\FieldContext;
	use App\Content\Directories\DirectoryRepository;
	use App\Helpers\Json;

	/**
	 * Разбор опций для option-based полей (dropdown/checkbox/multi_select…).
	 *
	 * Опции хранятся в settings.options. Числовые legacy-ключи представлены
	 * явными парами value/label и не зависят от позиции в JSON-массиве.
	 */
	trait OptionFieldSupport
	{
		protected function optionList(FieldContext $ctx)
		{
			if ($this->usesDirectory($ctx)) {
				return array_values($this->directoryOptionMap($ctx));
			}

			return $this->normalizeOptionList($ctx->setting('options', array()));
		}

		public function optionMap(FieldContext $ctx)
		{
			if ($this->usesDirectory($ctx)) {
				$options = $this->directoryOptionMap($ctx);
				if (!empty($options)) {
					return $options;
				}
			}

			return $this->normalizeOptionMap($ctx->setting('options', array()));
		}

		protected function optionSettingsSchema(array $options)
		{
			return array(
				array(
					'key' => 'option_source',
					'type' => 'select',
					'label' => 'Источник значений',
					'options' => array(
						'local' => 'Список только для этого поля',
						'directory' => 'Общий справочник',
					),
					'default' => 'local',
				),
				array(
					'key' => 'directory_id',
					'type' => 'directory',
					'label' => 'Справочник',
					'hint' => 'Изменения значений сразу применяются ко всем полям, использующим этот справочник.',
				),
				$options,
			);
		}

		protected function selectedList($value)
		{
			if (is_array($value)) {
				return $this->cleanOptionValues($value);
			}

			$value = trim((string) $value);
			if ($value === '') {
				return array();
			}

			$decoded = Json::toArray($value, array());
			if (!empty($decoded) || $value === '[]' || $value === '{}') {
				return $this->cleanOptionValues($decoded);
			}

			if (preg_match('/^a:\d+:/', $value)) {
				$data = @unserialize($value, array('allowed_classes' => false));
				if (is_array($data)) {
					return $this->cleanOptionValues($data);
				}
			}

			if (strpos($value, '|') !== false) {
				return $this->cleanOptionValues(explode('|', $value));
			}

			return $this->cleanOptionValues(preg_split('/\s*,\s*/', $value));
		}

		protected function optionLabel(array $map, $value)
		{
			$key = (string) $value;
			return array_key_exists($key, $map) ? (string) $map[$key] : $key;
		}

		protected function hasAssocKeys(array $data)
		{
			$i = 0;
			foreach ($data as $key => $value) {
				if ((string) $key !== (string) $i) {
					return true;
				}

				$i++;
			}

			return false;
		}

		private function usesDirectory(FieldContext $ctx)
		{
			return (string) $ctx->setting('option_source', 'local') === 'directory'
				&& (int) $ctx->setting('directory_id', 0) > 0;
		}

		private function directoryOptionMap(FieldContext $ctx)
		{
			$directoryId = (int) $ctx->setting('directory_id', 0);
			$includeInactive = $ctx->mode !== 'edit' && $ctx->mode !== 'filter';
			$options = DirectoryRepository::optionMap($directoryId, $includeInactive);
			if (!$includeInactive) {
				$all = DirectoryRepository::optionMap($directoryId, true);
				foreach ($this->selectedList($ctx->value) as $selected) {
					if (isset($all[$selected])) {
						$options[$selected] = $all[$selected];
					}
				}
			}

			return $options;
		}

		private function normalizeOptionList($raw)
		{
			$data = $this->rawOptionsToArray($raw);
			$out = array();
			foreach ($data as $key => $value) {
				if (is_array($value)) {
					$value = isset($value['label']) ? $value['label'] : (isset($value['value']) ? $value['value'] : '');
				}

				$value = trim((string) $value);
				if ($value !== '') {
					$out[] = $value;
				}
			}

			return array_values(array_unique($out));
		}

		private function normalizeOptionMap($raw)
		{
			$data = $this->rawOptionsToArray($raw);
			$assoc = $this->hasAssocKeys($data);
			$out = array();
			$pos = 0;

			foreach ($data as $key => $value) {
				if (is_array($value)) {
					$label = isset($value['label']) ? $value['label'] : (isset($value['title']) ? $value['title'] : '');
					$val = isset($value['value']) ? $value['value'] : (isset($value['key']) ? $value['key'] : $key);
					$out[(string) $val] = (string) $label;
					continue;
				}

				$value = trim((string) $value);
				if ($value === '') {
					$pos++;
					continue;
				}

				if (!$assoc && preg_match('/^([^=:|]+)\s*(=>|=|:|\|)\s*(.+)$/u', $value, $m)) {
					$out[trim($m[1])] = trim($m[3]);
				} elseif ($assoc) {
					$out[(string) $key] = $value;
				} else {
					$out[$value] = $value;
				}

				$pos++;
			}

			return $out;
		}

		private function rawOptionsToArray($raw)
		{
			if (is_array($raw)) {
				return $raw;
			}

			$raw = trim((string) $raw);
			if ($raw === '') {
				return array();
			}

			$json = Json::toArray($raw, array());
			if (!empty($json) || $raw === '[]' || $raw === '{}') {
				return $json;
			}

			if (preg_match('/^a:\d+:/', $raw)) {
				$data = @unserialize($raw, array('allowed_classes' => false));
				if (is_array($data)) {
					return $data;
				}
			}

			$delimiter = strpos($raw, "\n") !== false ? '/\r\n|\r|\n/' : '/\s*,\s*/';
			return preg_split($delimiter, $raw);
		}

		private function cleanOptionValues(array $values)
		{
			$out = array();
			array_walk_recursive($values, function ($value) use (&$out) {
				if (is_scalar($value)) {
					$value = trim((string) $value);
					if ($value !== '') {
						$out[] = $value;
					}
				}
			});
			return array_values(array_unique($out));
		}
	}
