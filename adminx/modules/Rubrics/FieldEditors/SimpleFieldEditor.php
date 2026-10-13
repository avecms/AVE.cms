<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Rubrics/FieldEditors/SimpleFieldEditor.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Adminx\Rubrics\FieldEditors;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Helpers\Json;

	class SimpleFieldEditor extends AbstractFieldEditor
	{
		public function parseValue($type, $value)
		{
			$type = (string) $type;
			$value = (string) $value;

			if ($type === 'checkbox') {
				return array('raw' => $value, 'checked' => (int) $value === 1);
			}

			if ($type === 'date') {
				return array(
					'raw' => $value,
					'timestamp' => preg_match('/^\d+$/', $value) ? (int) $value : 0,
					'date' => preg_match('/^\d+$/', $value) && (int) $value > 0 ? date('Y-m-d', (int) $value) : '',
				);
			}

			if ($type === 'date_time') {
				$timestamp = \App\Content\Fields\Types\DateTimeValue::normalize($value);
				$timestamp = $timestamp === '' ? 0 : (int) $timestamp;
				return array(
					'raw' => $value,
					'timestamp' => $timestamp,
					'date' => $timestamp > 0 ? date('Y-m-d', $timestamp) : '',
					'datetime' => $timestamp > 0 ? date('Y-m-d\TH:i', $timestamp) : '',
				);
			}

			if ($type === 'period') {
				$period = \App\Content\Fields\Types\PeriodValue::parseValue($value);
				return array('raw' => $value, 'start' => $period['start'], 'end' => $period['end']);
			}

			if ($type === 'range') {
				$range = \App\Content\Fields\Types\RangeValue::parseValue($value);
				return array('raw' => $value, 'min' => $range['min'], 'max' => $range['max']);
			}

			if ($type === 'dimensions') {
				$dimensions = \App\Content\Fields\Types\DimensionsValue::parseValue($value);
				return array('raw' => $value) + $dimensions;
			}

			if ($type === 'address') {
				$address = \App\Content\Fields\Types\AddressValue::parseValue($value);
				return array('raw' => $value) + $address;
			}

			if (in_array($type, array('single_line_numeric_two', 'single_line_numeric_three'), true)) {
				return array('raw' => $value, 'parts' => $this->cleanItems(explode('|', $value)));
			}

			if ($type === 'single_line_numeric') {
				return array('raw' => $value, 'number' => $value === '' ? '' : 0 + $value);
			}

			if ($type === 'number') {
				return array('raw' => $value, 'number' => $value);
			}

			return parent::parseValue($type, $value);
		}

		public function serializeValue($type, $value)
		{
			$type = (string) $type;
			if (!is_array($value)) {
				return (string) $value;
			}

			if ($type === 'checkbox') {
				return !empty($value['checked']) ? '1' : '';
			}

			if ($type === 'date') {
				if (!empty($value['timestamp'])) {
					return (string) (int) $value['timestamp'];
				}

				if (!empty($value['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $value['date'])) {
					$time = strtotime($value['date'] . ' 00:00:00');
					return $time !== false ? (string) $time : '';
				}

				return '';
			}

			if ($type === 'date_time') {
				$input = isset($value['datetime']) ? (string) $value['datetime'] : (isset($value['date']) ? (string) $value['date'] : '');
				return \App\Content\Fields\Types\DateTimeValue::normalize($input);
			}

			if ($type === 'period') {
				return \App\Content\Fields\Types\PeriodValue::encodeValue($value);
			}

			if ($type === 'range') {
				$range = \App\Content\Fields\Types\RangeValue::parseValue($value);
				return ($range['min'] === '' && $range['max'] === '')
					? ''
					: Json::encode($range, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			}

			if ($type === 'dimensions') {
				$dimensions = \App\Content\Fields\Types\DimensionsValue::parseValue($value);
				return count(array_filter($dimensions, 'strlen')) === 0
					? ''
					: Json::encode($dimensions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			}

			if ($type === 'address') {
				return \App\Content\Fields\Types\AddressValue::encodeValue($value);
			}

			if (in_array($type, array('single_line_numeric_two', 'single_line_numeric_three'), true)) {
				$parts = isset($value['parts']) && is_array($value['parts']) ? $value['parts'] : array();
				return implode('|', $this->cleanItems($parts));
			}

			if ($type === 'single_line_numeric') {
				return isset($value['number']) && $value['number'] !== '' ? (string) (0 + $value['number']) : '';
			}

			if ($type === 'number') {
				return isset($value['number']) ? trim((string) $value['number']) : '';
			}

			return parent::serializeValue($type, $value);
		}

		public function normalizeDefault($type, $value)
		{
			$type = (string) $type;
			$value = (string) $value;

			if ($type === 'checkbox') {
				$normalized = trim(function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value));
				return in_array($normalized, array('1', 'true', 'yes', 'on', 'да', 'вкл', 'включено'), true) ? '1' : '0';
			}

			if ($type === 'number') {
				return \App\Content\Fields\Types\NumberValue::normalize($value);
			}

			if ($type === 'color') {
				return \App\Content\Fields\Types\ColorValue::normalize($value);
			}

			if ($type === 'range') {
				$range = \App\Content\Fields\Types\RangeValue::parseValue($value);
				return ($range['min'] === '' && $range['max'] === '')
					? ''
					: Json::encode($range, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			}

			if ($type === 'dimensions') {
				$dimensions = \App\Content\Fields\Types\DimensionsValue::parseValue($value);
				return count(array_filter($dimensions, 'strlen')) === 0
					? ''
					: Json::encode($dimensions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			}

			if ($type === 'address') {
				return \App\Content\Fields\Types\AddressValue::encodeValue($value);
			}

			if (in_array($type, array('single_line_numeric', 'doc_from_rub'), true)) {
				$value = trim($value);
				return $value === '' ? '' : (string) (0 + $value);
			}

			if (in_array($type, array('single_line_numeric_two', 'single_line_numeric_three'), true)) {
				$parts = preg_split('/[|,\r\n]+/', $value);
				$out = array();
				foreach ($parts ?: array() as $part) {
					$part = trim((string) $part);
					if ($part !== '') {
						$out[] = (string) (0 + $part);
					}
				}

				return implode('|', $out);
			}

			if ($type === 'date') {
				$value = trim($value);
				if ($value === '' || preg_match('/^\d+$/', $value)) {
					return $value;
				}

				if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
					$time = strtotime($value . ' 00:00:00');
					return $time !== false ? (string) $time : '';
				}
			}

			if ($type === 'date_time') {
				return \App\Content\Fields\Types\DateTimeValue::normalize($value);
			}

			if ($type === 'period') {
				return \App\Content\Fields\Types\PeriodValue::encodeValue($value);
			}

			return $value;
		}

		public function renderEdit(array $field)
		{
			$kind = isset($field['editor_kind']) ? (string) $field['editor_kind'] : 'text';
			$type = isset($field['rubric_field_type']) ? (string) $field['rubric_field_type'] : '';
			$parsed = $this->value($field);
			$settings = isset($field['settings']) && is_array($field['settings']) ? $field['settings'] : array();
			$editorHeight = $this->editorHeight($settings, $kind === 'code' ? 280 : 300);

			if ($type === 'multi_line' && isset($settings['editor']) && (string) $settings['editor'] === 'plain') {
				$kind = 'textarea';
			}

			if ($type === 'content') {
				$mode = isset($settings['mode']) ? (string) $settings['mode'] : 'rich';
				$kind = $mode === 'code' ? 'code' : ($mode === 'plain' ? 'textarea' : 'rich');
			}

			if ($type === 'computed') {
				$value = is_array($parsed) ? '' : (string) $parsed;
				$name = $this->e($this->fname($field, '[raw]'));
				return '<input type="hidden" name="' . $name . '" value="' . $this->e($value) . '">'
					. '<input class="input" type="text" value="' . $this->e($value) . '" readonly'
					. ' aria-label="Вычисляемое значение">'
					. '<span class="field-hint">Значение пересчитывается при сохранении документа.</span>';
			}

			if ($type === 'date_time') {
				$mode = isset($settings['mode']) ? (string) $settings['mode'] : 'date';
				$timestamp = isset($field['field_value']) ? (int) $field['field_value'] : 0;
				$isDate = $mode !== 'datetime';
				return '<input class="input" type="' . ($isDate ? 'date' : 'datetime-local') . '" name="'
					. $this->e($this->fname($field, '[datetime]')) . '" value="'
					. $this->e($timestamp > 0 ? date($isDate ? 'Y-m-d' : 'Y-m-d\TH:i', $timestamp) : '') . '">';
			}

			if ($type === 'period') {
				$period = \App\Content\Fields\Types\PeriodValue::parseValue(isset($field['field_value']) ? $field['field_value'] : '');
				$dateOnly = !isset($settings['mode']) || (string) $settings['mode'] !== 'datetime';
				$inputType = $dateOnly ? 'date' : 'datetime-local';
				$inputFormat = $dateOnly ? 'Y-m-d' : 'Y-m-d\TH:i';
				$name = $this->e($this->fname($field, ''));
				$html = '<div class="documents-period-control">';
				foreach (array('start' => 'Начало', 'end' => 'Окончание') as $key => $label) {
					$timestamp = isset($period[$key]) ? (int) $period[$key] : 0;
					$html .= '<label class="field"><span class="field-label">' . $this->e($label) . '</span>'
						. '<input class="input" type="' . $inputType . '" name="' . $name . '[' . $key . ']" value="'
						. $this->e($timestamp > 0 ? date($inputFormat, $timestamp) : '') . '"></label>';
				}

				return $html . '</div>';
			}

			if ($type === 'contact') {
				$mode = isset($settings['mode']) ? (string) $settings['mode'] : 'email';
				$inputType = $mode === 'phone' ? 'tel' : ($mode === 'url' ? 'url' : 'email');
				$placeholder = $mode === 'phone' ? '+7 999 000-00-00' : ($mode === 'url' ? 'https://example.com' : 'name@example.com');
				return '<input class="input" type="' . $inputType . '" autocomplete="off" placeholder="' . $this->e($placeholder)
					. '" name="' . $this->e($this->fname($field, '[raw]')) . '" value="' . $this->e($parsed) . '">';
			}

			if ($type === 'color') {
				$value = \App\Content\Fields\Types\ColorValue::normalize($parsed);
				return '<div class="documents-color-control" data-document-color-control>'
					. '<input type="color" value="' . $this->e($value !== '' ? $value : '#000000') . '" data-document-color-picker aria-label="Выбрать цвет">'
					. '<input class="input mono" type="text" placeholder="#rrggbb" name="' . $this->e($this->fname($field, '[raw]'))
					. '" value="' . $this->e($value) . '" data-document-color-value></div>';
			}

			if ($type === 'range') {
				$range = \App\Content\Fields\Types\RangeValue::parseValue(isset($field['field_value']) ? $field['field_value'] : '');
				$unit = isset($settings['unit']) ? trim((string) $settings['unit']) : '';
				$minLabel = isset($settings['min_label']) && trim((string) $settings['min_label']) !== '' ? (string) $settings['min_label'] : 'От';
				$maxLabel = isset($settings['max_label']) && trim((string) $settings['max_label']) !== '' ? (string) $settings['max_label'] : 'До';
				$html = '<div class="documents-range-control">';
				foreach (array('min' => $minLabel, 'max' => $maxLabel) as $key => $label) {
					$html .= '<label class="field"><span class="field-label">' . $this->e($label) . '</span><span class="input-group">'
						. '<input class="input" type="text" inputmode="decimal" name="' . $this->e($this->fname($field, '[' . $key . ']'))
						. '" value="' . $this->e($range[$key]) . '">'
						. ($unit !== '' ? '<span class="input-addon">' . $this->e($unit) . '</span>' : '') . '</span></label>';
				}

				return $html . '</div>';
			}

			if ($type === 'dimensions') {
				$dimensions = \App\Content\Fields\Types\DimensionsValue::parseValue(isset($field['field_value']) ? $field['field_value'] : '');
				$units = array('mm' => 'мм', 'cm' => 'см', 'm' => 'м');
				$unitKey = isset($settings['unit']) ? (string) $settings['unit'] : 'cm';
				$unit = isset($units[$unitKey]) ? $units[$unitKey] : $units['cm'];
				$html = '<div class="documents-dimensions-control">';
				foreach (array('length' => 'Длина', 'width' => 'Ширина', 'height' => 'Высота') as $key => $label) {
					$html .= '<label class="field"><span class="field-label">' . $this->e($label) . '</span><span class="input-group">'
						. '<input class="input" type="text" inputmode="decimal" name="' . $this->e($this->fname($field, '[' . $key . ']'))
						. '" value="' . $this->e($dimensions[$key]) . '"><span class="input-addon">' . $this->e($unit) . '</span></span></label>';
				}

				return $html . '</div>';
			}

			if ($type === 'address') {
				$address = \App\Content\Fields\Types\AddressValue::parseValue(isset($field['field_value']) ? $field['field_value'] : '');
				$name = $this->e($this->fname($field, ''));
				$html = '<div class="documents-address-control">';
				foreach (array(
					'postal_code' => 'Индекс',
					'region' => 'Регион',
					'city' => 'Город / населённый пункт',
					'street' => 'Улица',
					'building' => 'Дом / строение',
					'unit' => 'Квартира / офис',
				) as $key => $label) {
					$html .= '<label class="field documents-address-' . $key . '"><span class="field-label">' . $this->e($label) . '</span>'
						. '<input class="input" type="text" name="' . $name . '[' . $key . ']" value="' . $this->e($address[$key]) . '"></label>';
				}

				if (!empty($settings['coordinates'])) {
					foreach (array('latitude' => 'Широта', 'longitude' => 'Долгота') as $key => $label) {
						$html .= '<label class="field documents-address-' . $key . '"><span class="field-label">' . $this->e($label) . '</span>'
							. '<input class="input" type="text" inputmode="decimal" name="' . $name . '[' . $key . ']" value="' . $this->e($address[$key]) . '"></label>';
					}
				} else {
					$html .= '<input type="hidden" name="' . $name . '[latitude]" value="' . $this->e($address['latitude']) . '">'
						. '<input type="hidden" name="' . $name . '[longitude]" value="' . $this->e($address['longitude']) . '">';
				}

				return $html . '</div>';
			}

			if ($kind === 'boolean') {
				$on = (string) $parsed === '1';
				$name = $this->e($this->fname($field, '[checked]'));
				return '<div class="documents-boolean-control">'
					. '<input type="hidden" name="' . $name . '" value="0">'
					. '<label class="switch"><input type="checkbox" name="' . $name . '" value="1" data-document-boolean'
					. ($on ? ' checked' : '') . '><span data-document-boolean-label>' . ($on ? 'Включено' : 'Выключено') . '</span></label>'
					. '</div>';
			}

			if ($kind === 'date') {
				return '<input class="input" type="date" name="' . $this->e($this->fname($field, '[date]'))
					. '" value="' . $this->e($parsed) . '">';
			}

			if ($kind === 'number') {
				return '<input class="input" type="text" inputmode="decimal" pattern="[+\\-]?[0-9]*([.,][0-9]*)?" spellcheck="false" name="'
					. $this->e($this->fname($field, '[number]')) . '" value="' . $this->e($parsed) . '">';
			}

			if ($kind === 'numeric_parts') {
				$count = $type === 'single_line_numeric_three' ? 3 : 2;
				$parts = is_array($parsed) ? $parsed : array();
				$html = '<div class="documents-inline-grid documents-numeric-parts" style="--parts-count:' . $count . '">';
				for ($i = 0; $i < $count; $i++) {
					$val = isset($parts[$i]) ? $parts[$i] : '';
					$html .= '<label class="field"><span class="field-label">Значение ' . ($i + 1) . '</span>'
						. '<input class="input" type="text" inputmode="decimal" pattern="[+\\-]?[0-9]*([.,][0-9]*)?" spellcheck="false" name="'
						. $this->e($this->fname($field, '[parts][' . $i . ']')) . '" value="' . $this->e($val) . '"></label>';
				}

				return $html . '</div>';
			}

			if ($kind === 'url') {
				return '<input class="input" type="url" name="' . $this->e($this->fname($field, '[raw]'))
					. '" value="' . $this->e($parsed) . '">';
			}

			if ($kind === 'code') {
				$language = isset($settings['language']) ? (string) $settings['language'] : 'html';
				return '<textarea class="textarea mono" name="' . $this->e($this->fname($field, '[raw]'))
					. '" rows="8" data-code-editor data-mode="' . $this->e($language) . '" data-height="' . $editorHeight
					. '" spellcheck="false">' . $this->e($parsed) . '</textarea>';
			}

			if ($kind === 'rich' || $kind === 'rich_simple' || $kind === 'rich_slim') {
				$preset = $kind === 'rich' ? 'full' : ($kind === 'rich_simple' ? 'simple' : 'slim');
				return '<textarea class="textarea documents-rich-editor" name="' . $this->e($this->fname($field, '[raw]'))
					. '" data-rich-editor data-rich-preset="' . $preset . '" data-editor-height="' . $editorHeight
					. '" data-rich-options="' . $this->e(json_encode(\App\Content\Fields\RichEditorSettings::options($settings), JSON_UNESCAPED_UNICODE))
					. '" spellcheck="true">' . $this->e($parsed) . '</textarea>';
			}

			if ($kind === 'textarea') {
				return '<textarea class="textarea" name="' . $this->e($this->fname($field, '[raw]'))
					. '" rows="6" style="height:' . $editorHeight . 'px">' . $this->e($parsed) . '</textarea>';
			}

			if ($kind === 'text') {
				$options = isset($field['options']) && is_array($field['options']) ? $field['options'] : array();
				if (!empty($options)) {
					$html = '<select class="select" name="' . $this->e($this->fname($field, '[raw]')) . '">'
						. '<option value="">Не выбрано</option>';
					foreach ($options as $option) {
						$ov = isset($option['value']) ? (string) $option['value'] : '';
						$ol = isset($option['label']) ? (string) $option['label'] : $ov;
						$html .= '<option value="' . $this->e($ov) . '"' . ((string) $parsed === $ov ? ' selected' : '') . '>' . $this->e($ol) . '</option>';
					}

					return $html . '</select>';
				}

				return '<input class="input" type="text" name="' . $this->e($this->fname($field, '[raw]'))
					. '" value="' . $this->e($parsed) . '">';
			}

			return $this->renderGeneric($field);
		}

		protected function definitions()
		{
			return array(
				'single_line' => array(
					'title' => 'Строка',
					'icon' => 'forms',
					'summary' => 'Обычное текстовое поле для короткого значения.',
					'default_label' => 'Текст по умолчанию',
					'default_hint' => 'Можно оставить пустым. Значение будет подставлено при создании документа.',
					'default_kind' => 'text',
					'template_hint' => 'HTML/PHP-шаблон вывода значения поля на сайте.',
					'entity_fields' => array('value'),
				),
				'single_line_numeric' => array(
					'title' => 'Число',
					'icon' => 'number',
					'summary' => 'Одно числовое значение.',
					'default_label' => 'Число по умолчанию',
					'default_hint' => 'Можно оставить пустым или указать число.',
					'default_kind' => 'number',
					'entity_fields' => array('number'),
				),
				'number' => array(
					'title' => 'Числовое значение',
					'icon' => 'number',
					'summary' => 'Число с настраиваемым публичным форматом: сумма, процент, величина или рейтинг.',
					'default_label' => 'Число по умолчанию',
					'default_hint' => 'Можно использовать точку или запятую. В хранилище значение нормализуется автоматически.',
					'default_kind' => 'number',
					'entity_fields' => array('number'),
				),
				'date_time' => array(
					'title' => 'Дата и время',
					'icon' => 'calendar-time',
					'summary' => 'Дата или дата со временем в одном типе с настраиваемым публичным форматом.',
					'default_label' => 'Дата по умолчанию',
					'default_hint' => 'Хранится как Unix timestamp и редактируется нативным контролом браузера.',
					'default_kind' => 'datetime',
					'entity_fields' => array('timestamp'),
				),
				'period' => array(
					'title' => 'Период',
					'icon' => 'calendar-event',
					'summary' => 'Дата начала и окончания для событий, публикаций и временных блоков.',
					'default_label' => 'Период по умолчанию',
					'default_hint' => 'Формат: начало|окончание. Поддерживаются YYYY-MM-DD и дата со временем.',
					'default_kind' => 'period',
					'entity_fields' => array('start', 'end'),
				),
				'single_line_numeric_two' => array(
					'title' => 'Два числа',
					'icon' => 'numbers',
					'summary' => 'Две числовые части, которые legacy хранит через вертикальную черту.',
					'default_label' => 'Числа по умолчанию',
					'default_hint' => 'Формат: значение1|значение2.',
					'default_kind' => 'pipe',
					'entity_fields' => array('parts[]'),
				),
				'single_line_numeric_three' => array(
					'title' => 'Три числа',
					'icon' => 'numbers',
					'summary' => 'Три числовые части, которые legacy хранит через вертикальную черту.',
					'default_label' => 'Числа по умолчанию',
					'default_hint' => 'Формат: значение1|значение2|значение3.',
					'default_kind' => 'pipe',
					'entity_fields' => array('parts[]'),
				),
				'multi_line' => array(
					'title' => 'Многострочный текст',
					'icon' => 'align-left',
					'summary' => 'Полноценный визуальный HTML-редактор.',
					'default_label' => 'Текст по умолчанию',
					'default_hint' => 'Сохраняется как HTML, совместимый с legacy multi_line.',
					'default_kind' => 'rich',
					'entity_fields' => array('value'),
				),
				'content' => array(
					'title' => 'Текстовое содержимое',
					'icon' => 'file-text',
					'summary' => 'Единое поле с выбором обычного текста, Tiptap или CodeMirror.',
					'default_label' => 'Содержимое по умолчанию',
					'default_hint' => 'Режим редактора и публичного вывода задаётся в настройках типа.',
					'default_kind' => 'rich',
					'entity_fields' => array('value'),
				),
				'richtext' => array(
					'title' => 'Форматированный текст',
					'icon' => 'text-format',
					'summary' => 'Визуальный HTML-редактор с полным набором инструментов.',
					'default_label' => 'Текст по умолчанию',
					'default_hint' => 'Сохраняется как HTML и редактируется через общий rich-text редактор.',
					'default_kind' => 'rich',
					'entity_fields' => array('value'),
				),
				'multi_line_simple' => array(
					'title' => 'Простой многострочный текст',
					'icon' => 'align-left',
					'summary' => 'Визуальный HTML-редактор средней высоты.',
					'default_label' => 'Текст по умолчанию',
					'default_kind' => 'rich_simple',
					'entity_fields' => array('value'),
				),
				'multi_line_slim' => array(
					'title' => 'Компактный многострочный текст',
					'icon' => 'align-left',
					'summary' => 'Компактный визуальный HTML-редактор.',
					'default_label' => 'Текст по умолчанию',
					'default_kind' => 'rich_slim',
					'entity_fields' => array('value'),
				),
				'checkbox' => array(
					'title' => 'Флажок',
					'icon' => 'checkbox',
					'summary' => 'Булево значение: включено или выключено.',
					'default_label' => 'Значение по умолчанию',
					'default_hint' => 'Используйте 1 для включенного состояния или 0 для выключенного.',
					'default_kind' => 'boolean',
					'entity_fields' => array('checked'),
				),
				'date' => array(
					'title' => 'Дата',
					'icon' => 'calendar',
					'summary' => 'Дата или дата-время в документе.',
					'default_label' => 'Дата по умолчанию',
					'default_hint' => 'Поддерживаются YYYY-MM-DD, timestamp или пустое значение.',
					'default_kind' => 'date',
					'entity_fields' => array('timestamp', 'date'),
				),
				'link' => array(
					'title' => 'Ссылка',
					'icon' => 'link',
					'summary' => 'URL или относительная ссылка.',
					'default_label' => 'Ссылка по умолчанию',
					'default_hint' => 'Например /catalog или https://example.com.',
					'default_kind' => 'url',
					'entity_fields' => array('value'),
				),
				'contact' => array(
					'title' => 'Контакт',
					'icon' => 'address-book',
					'summary' => 'Email, телефон или URL с режимной валидацией и безопасной публичной ссылкой.',
					'default_label' => 'Значение по умолчанию',
					'default_hint' => 'Формат значения определяется настройкой типа контакта.',
					'default_kind' => 'text',
					'entity_fields' => array('value'),
				),
				'color' => array(
					'title' => 'Цвет',
					'icon' => 'palette',
					'summary' => 'HEX-цвет с нативным picker и синхронным ручным вводом.',
					'default_label' => 'Цвет по умолчанию',
					'default_hint' => 'Формат #rgb или #rrggbb.',
					'default_kind' => 'text',
					'entity_fields' => array('value'),
				),
				'range' => array(
					'title' => 'Числовой диапазон',
					'icon' => 'arrows-horizontal',
					'summary' => 'Нижняя и верхняя граница с единицей измерения и JSON-хранением.',
					'default_label' => 'Диапазон по умолчанию',
					'default_hint' => 'Формат: минимум|максимум. Одну границу можно оставить пустой.',
					'default_kind' => 'range',
					'entity_fields' => array('min', 'max'),
				),
				'dimensions' => array(
					'title' => 'Габариты',
					'icon' => 'box-model-2',
					'summary' => 'Длина, ширина и высота с единицей измерения и JSON-хранением.',
					'default_label' => 'Габариты по умолчанию',
					'default_hint' => 'Формат: длина|ширина|высота.',
					'default_kind' => 'dimensions',
					'entity_fields' => array('length', 'width', 'height'),
				),
				'address' => array(
					'title' => 'Адрес',
					'icon' => 'map-pin',
					'summary' => 'Структурированный почтовый адрес с необязательными координатами.',
					'default_label' => 'Адрес по умолчанию',
					'default_hint' => 'Формат: индекс|регион|город|улица|дом|помещение|широта|долгота.',
					'default_kind' => 'address',
					'entity_fields' => array('postal_code', 'region', 'city', 'street', 'building', 'unit', 'latitude', 'longitude'),
				),
				'code' => array(
					'title' => 'Код',
					'icon' => 'code-dots',
					'summary' => 'Редактор исходного кода.',
					'default_label' => 'Код по умолчанию',
					'default_kind' => 'code',
					'entity_fields' => array('value'),
				),
				'computed' => array(
					'title' => 'Вычисляемое поле',
					'icon' => 'math-function',
					'summary' => 'Значение автоматически рассчитывается по формуле из других полей документа.',
					'default_label' => 'Резервное значение',
					'default_hint' => 'Используется только когда формулу невозможно вычислить.',
					'default_kind' => 'text',
					'entity_fields' => array('value'),
				),
			);
		}

		protected function cleanItems(array $items)
		{
			$out = array();
			foreach ($items as $item) {
				$item = trim((string) $item);
				if ($item !== '') {
					$out[] = $item;
				}
			}

			return array_values($out);
		}

		protected function editorHeight(array $settings, $default)
		{
			$height = isset($settings['height']) ? (int) $settings['height'] : (int) $default;
			return max(140, min(900, $height));
		}
	}
