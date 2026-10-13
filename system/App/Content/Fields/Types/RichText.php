<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Content/Fields/Types/RichText.php
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
	 * Форматированный текст (richtext). HTML в document_fields_text.
	 *
	 * Новый HTML очищается сервером при сохранении изменённого поля.
	 */
	class RichText extends AbstractFieldType
	{
		public function code()
		{
			return 'richtext';
		}

		public function name()
		{
			return 'Форматированный текст';
		}

		public function storage()
		{
			return 'text';
		}

		public function settingsSchema()
		{
			return array_merge(array(
				array('key' => 'height', 'type' => 'int', 'label' => 'Высота редактора, px', 'default' => 300),
			), \App\Content\Fields\RichEditorSettings::schema());
		}

		public function renderEdit(FieldContext $ctx)
		{
			return '<textarea class="textarea" data-rich data-editor-height="' . $this->editorHeight($ctx)
				. '" name="' . $this->attr($ctx->inputName()) . '">' . $this->e($ctx->value) . '</textarea>';
		}

		public function renderView(FieldContext $ctx)
		{
			return HtmlSanitizer::clean($ctx->value);
		}

		public function save(FieldContext $ctx)
		{
			return (string) $ctx->value;
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
	}
