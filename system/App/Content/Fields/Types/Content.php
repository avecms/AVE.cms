<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Content/Fields/Types/Content.php
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
	 * Unified text content for new fields. Legacy multi_line/richtext/code types
	 * remain independent compatibility contracts and are not migrated to it.
	 */
	class Content extends AbstractFieldType
	{
		public function code()
		{
			return 'content';
		}

		public function name()
		{
			return 'Текстовое содержимое';
		}

		public function storage()
		{
			return 'text';
		}

		public function settingsSchema()
		{
			return array_merge(array(
				array(
					'key' => 'mode',
					'type' => 'select',
					'label' => 'Режим редактора',
					'options' => array(
						'plain' => 'Обычный текст',
						'rich' => 'Форматированный текст',
						'code' => 'Код / HTML',
					),
					'default' => 'rich',
					'hint' => 'Режим одновременно определяет редактор и безопасный способ публичного вывода.',
				),
				array(
					'key' => 'language',
					'type' => 'select',
					'label' => 'Язык CodeMirror',
					'options' => array(
						'html' => 'HTML',
						'twig' => 'Twig',
						'smarty' => 'Smarty',
						'php' => 'PHP',
						'css' => 'CSS',
						'javascript' => 'JavaScript',
						'json' => 'JSON',
						'sql' => 'SQL',
						'text' => 'Текст',
					),
					'default' => 'html',
					'hint' => 'Используется только в режиме «Код / HTML».',
				),
				array(
					'key' => 'height',
					'type' => 'int',
					'label' => 'Высота редактора, px',
					'default' => 300,
				),
			), \App\Content\Fields\RichEditorSettings::schema());
		}

		public function renderEdit(FieldContext $ctx)
		{
			$height = $this->editorHeight($ctx);
			$mode = $this->mode($ctx);
			if ($mode === 'code') {
				return '<textarea class="textarea mono" rows="10" spellcheck="false" data-code-editor data-mode="'
					. $this->attr($this->language($ctx)) . '" data-height="' . $height . '" name="'
					. $this->attr($ctx->inputName()) . '">' . $this->e($ctx->value) . '</textarea>';
			}

			if ($mode === 'plain') {
				return '<textarea class="textarea" rows="8" style="height:' . $height . 'px" name="'
					. $this->attr($ctx->inputName()) . '">' . $this->e($ctx->value) . '</textarea>';
			}

			return '<textarea class="textarea" data-rich data-editor-height="' . $height . '" name="'
				. $this->attr($ctx->inputName()) . '">' . $this->e($ctx->value) . '</textarea>';
		}

		public function renderView(FieldContext $ctx)
		{
			$mode = $this->mode($ctx);
			if ($mode === 'code') {
				return (string) $ctx->value;
			}

			if ($mode === 'plain') {
				return nl2br($this->e($ctx->value));
			}

			return HtmlSanitizer::clean($ctx->value);
		}

		public function save(FieldContext $ctx)
		{
			return $this->mode($ctx) === 'rich'
				? HtmlSanitizer::clean($ctx->value)
				: (string) $ctx->value;
		}

		protected function mode(FieldContext $ctx)
		{
			$mode = (string) $ctx->setting('mode', 'rich');
			return in_array($mode, array('plain', 'rich', 'code'), true) ? $mode : 'rich';
		}

		protected function language(FieldContext $ctx)
		{
			$language = (string) $ctx->setting('language', 'html');
			$allowed = array('html', 'twig', 'smarty', 'php', 'css', 'javascript', 'js', 'json', 'sql', 'text');
			return in_array($language, $allowed, true) ? $language : 'html';
		}

		protected function editorHeight(FieldContext $ctx)
		{
			return max(140, min(900, (int) $ctx->setting('height', 300)));
		}
	}
