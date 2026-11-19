<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Frontend/PageShellRenderer.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Frontend;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Content\Documents\DocumentExcerpt;
	use App\Helpers\Url;

	/** Replaces page-shell, document and SEO tags after nested components render. */
	class PageShellRenderer
	{
		public function render($content, $mainContent, $document)
		{
			$theme = ThemeAssets::currentTheme();
			list($search, $replace) = $this->baseTags($document);
			if (!isset($_REQUEST['sysblock']) && !isset($_REQUEST['request'])) {
				$this->appendMetaTags($search, $replace, $mainContent, $document);
			}

			$content = preg_replace_callback(
				'/\[tag:doc:([a-zA-Z0-9-_]+)\]/u',
				function ($match) use ($document) {
					if ($match[1] === 'document_teaser' || $match[1] === 'document_excerpt') {
						return DocumentExcerpt::explicit($document);
					}

					$key = $match[1];
					return is_object($document) && isset($document->{$key})
						? $document->{$key}
						: '';
				},
				(string) $content
			);
			$content = preg_replace('/\[tag:langfile:[a-zA-Z0-9-_]+\]/u', '', $content);
			$content = preg_replace('/\[tag:doc:\d*\]/', '', $content);
			$content = preg_replace('/\[tag:langfile:\d*\]/', '', $content);

			$search[] = '[tag:maincontent]';
			$replace[] = '';
			$search[] = '[tag:printlink]';
			$replace[] = Url::printLink();
			$search[] = '[tag:version]';
			$replace[] = APP_NAME . ' v' . APP_VERSION;
			$search[] = '[tag:docviews]';
			$replace[] = is_object($document) && isset($document->document_count_view)
				? $document->document_count_view
				: '';

			$requestItems = new RequestItemRenderer();
			$content = preg_replace_callback(
				'/\[tag:teaser:(\d+)(|:\[(.*?)\])\]/',
				function ($match) use ($requestItems) { return $requestItems->render($match[1], '', $match[2]); },
				$content
			);

			if (defined('RUB_ID')) {
				$authorId = is_object($document) && isset($document->document_author_id)
					? (int) $document->document_author_id
					: 0;
				$content = preg_replace_callback(
					'/\[tag:docauthoravatar:(\d+)\]/',
					function ($match) use ($authorId) { return PublicAvatar::url($authorId, $match[1]); },
					$content
				);
			}

			if (strpos($content, '[tag:breadcrumb]') !== false) {
				$content = str_replace('[tag:breadcrumb]', (new BreadcrumbRenderer())->render(), $content);
			}

			$content = str_replace($search, $replace, $content);
			$content = ThemeAssets::replaceTags($content, $theme);

			return str_ireplace('"//"', '"/"', str_ireplace('///', '/', Url::rewrite($content)));
		}

		protected function baseTags($document)
		{
			$theme = defined('THEME_FOLDER') ? THEME_FOLDER : DEFAULT_THEME_FOLDER;
			$search = array(
				'[tag:mediapath]', '[tag:path]', '[tag:sitename]', '[tag:home]',
				'[tag:docid]', '[tag:docparent]', '[tag:excerpt]',
			);
			$replace = array(
				ABS_PATH . 'templates/' . $theme . '/',
				ABS_PATH,
				htmlspecialchars(PublicSettings::get('site_name'), ENT_QUOTES),
				Url::home(),
				is_object($document) && isset($document->Id) ? $document->Id : '',
				is_object($document) && isset($document->document_parent) ? $document->document_parent : '',
				DocumentExcerpt::resolve($document),
			);

			$external = (isset($_REQUEST['sysblock']) && $_REQUEST['sysblock'] !== '')
				|| (isset($_REQUEST['request']) && $_REQUEST['request'] !== '');
			if ($external) {
				return array($search, $replace);
			}

			array_splice($search, 3, 0, array('[tag:alias]', '[tag:domain]'));
			array_splice($replace, 3, 0, array(
				isset($_REQUEST['id']) && is_object($document) && isset($document->document_alias)
					? $document->document_alias
					: '',
				Url::site(),
			));
			array_splice($search, 6, 0, array('[tag:robots]', '[tag:canonical]'));
			$alias = is_object($document) && isset($document->document_alias) ? (string) $document->document_alias : '';
			$documentPath = str_replace('//', '/', ABS_PATH . $alias);
			if (trim($alias, '/') !== '' && defined('URL_SUFF') && URL_SUFF !== '') {
				$documentPath = rtrim($documentPath, '/') . URL_SUFF;
			}

			$canonical = Url::canonical(
				is_object($document) && isset($document->document_alias)
					? Url::site() . $documentPath
					: ''
			);
			array_splice($replace, 6, 0, array(
				is_object($document) && isset($document->document_meta_robots) ? $document->document_meta_robots : '',
				$canonical,
			));

			$title = is_object($document) && isset($document->document_title) ? $document->document_title : '';
			$search = array_merge($search, array('[tag:og:title]', '[tag:og:url]', '[tag:og:sitename]'));
			$replace = array_merge($replace, array(
				htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8'),
				htmlspecialchars((string) $canonical, ENT_QUOTES, 'UTF-8'),
				htmlspecialchars((string) PublicSettings::get('site_name'), ENT_QUOTES, 'UTF-8'),
			));

			return array($search, $replace);
		}

		protected function appendMetaTags(array &$search, array &$replace, $mainContent, $document)
		{
			$generated = array('keywords' => '', 'description' => '');
			$useGenerated = is_object($document) && !empty($document->rubric_meta_gen);
			if ($useGenerated) {
				$generated = (new MetaGenerator())->generate((string) $mainContent);
			}

			$search[] = '[tag:keywords]';
			$replace[] = stripslashes(htmlspecialchars(
				$useGenerated ? $generated['keywords'] : (isset($document->document_meta_keywords) ? $document->document_meta_keywords : ''),
				ENT_QUOTES
			));
			$metaDescription = isset($document->document_meta_description) ? trim((string) $document->document_meta_description) : '';
			$description = stripslashes(htmlspecialchars(
				$useGenerated ? $generated['description'] : ($metaDescription !== '' ? $metaDescription : DocumentExcerpt::explicit($document)),
				ENT_QUOTES,
				'UTF-8'
			));
			$search[] = '[tag:description]';
			$replace[] = $description;
			$search[] = '[tag:og:description]';
			$replace[] = $description;
			$title = is_object($document) && isset($document->document_title) ? $document->document_title : '';
			$pageTitle = stripslashes(htmlspecialchars_decode(str_replace(
				array('©', '®'),
				array('&copy;', '&reg;'),
				$title
			)));
			$search[] = '[tag:title]';
			$replace[] = $pageTitle;
			$search[] = '[tag:pagetitle]';
			$replace[] = $pageTitle;
		}
	}
