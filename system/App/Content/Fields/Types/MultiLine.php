<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Content/Fields/Types/MultiLine.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Content\Fields\Types;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Content\Fields\AbstractFieldType;
	use App\Content\Fields\FieldContext;
	use App\Content\Fields\HtmlSanitizer;

	/**
	 * Многострочный текст (AVE multi_line). Хранится в longtext.
	 *
	 * Rich-режим очищается сервером при сохранении изменённого поля.
	 */
	class MultiLine extends AbstractFieldType
	{
		public function code()
		{
			return 'multi_line';
		}

		public function name()
		{
			return 'Многострочное';
		}

		public function storage()
		{
			return 'text';
		}

		public function settingsSchema()
		{
			return array_merge(array(
				array('key' => 'editor', 'type' => 'select', 'label' => 'Редактор',
					  'options' => array('rich', 'plain'), 'default' => 'rich'),
				array('key' => 'height', 'type' => 'int', 'label' => 'Высота редактора, px', 'default' => 300),
			), \App\Content\Fields\RichEditorSettings::schema());
		}

		public function renderEdit(FieldContext $ctx)
		{
			$height = $this->editorHeight($ctx);
			if ($this->isRich($ctx)) {
				return '<textarea class="textarea" rows="' . $this->rows() . '" data-rich data-editor-height="'
					. $height . '" name="' . $this->attr($ctx->inputName()) . '">' . $this->e($ctx->value) . '</textarea>';
			}

			return '<textarea class="textarea" rows="' . $this->rows() . '" style="--field-editor-height:'
				. $height . 'px" name="' . $this->attr($ctx->inputName()) . '">' . $this->e($ctx->value) . '</textarea>';
		}

		public function renderView(FieldContext $ctx)
		{
			if ($this->isRich($ctx)) {
				return $this->normalizeLegacyHtml($ctx->value);
			}

			return nl2br($this->e($ctx->value));
		}

		public function save(FieldContext $ctx)
		{
			return $this->isRich($ctx) ? HtmlSanitizer::clean($ctx->value) : (string) $ctx->value;
		}

		protected function isRich(FieldContext $ctx)
		{
			return $ctx->setting('editor', 'rich') !== 'plain';
		}

		protected function rows()
		{
			return 8;
		}

		protected function editorHeight(FieldContext $ctx)
		{
			$height = (int) $ctx->setting('height', 300);
			if ($height < 140) {
				return 140;
			}

			if ($height > 900) {
				return 900;
			}

			return $height;
		}

		protected function normalizeLegacyHtml($value)
		{
			$value = (string) $value;
			return preg_replace('/(^|[\s>])br\s*\/?>/i', '$1<br />', $value);
		}
	}
