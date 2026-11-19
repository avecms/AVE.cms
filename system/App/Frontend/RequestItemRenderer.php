<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Frontend/RequestItemRenderer.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Frontend;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Helpers\Url;
	use App\Frontend\Debug\PublicLayoutInspector;
	use App\Frontend\Media\ThumbnailUrl;
	use App\Frontend\Media\WatermarkService;
	use App\Common\AdminLocation;
	use App\Common\StoredPhpRuntime;
	use App\Content\ContentTables;
	use App\Content\Documents\DocumentExcerpt;
	use App\Helpers\Debug;
	use App\Helpers\File;
	use DB;

	/** Renders one document through a request or rubric teaser template. */
	class RequestItemRenderer
	{
		/** Bump when request-item rendering semantics change to retire incompatible HTML caches. */
		const CACHE_VERSION = 7;

		public function render($mixed, $template = '', $tparams = '', $context = null)
		{
			$context = $context instanceof RequestItemContext ? $context : new RequestItemContext();
			$teaserParams = array();
			$fieldRenderer = new RequestFieldRenderer();
			$documentFieldRenderer = new DocumentFieldRenderer();
			$blockRenderer = new BlockRenderer();

			if (is_array($mixed))
				$row = (int)$mixed[1];
			else if (is_numeric($mixed))
				$row = (int)$mixed;

			$row = (is_object($mixed)
				? $mixed
				: (new DocumentRepository())->find($row));

			if (! $row)
				return '';

			$tparams_id = '';

			if ($tparams != '')
			{
				$tparams_id = $row->Id . md5($tparams);								 // Создаем уникальный id для каждого набора параметров
				$teaserParams[$tparams_id] = [];							     // Для отмены лишних ворнингов
				$tparams = trim($tparams,'[]:');							 // Удаляем: слева ':[', справа ']'
				$teaserParams[$tparams_id] = explode('|',$tparams);	 // Заносим параметры в массив уникального id
			}

			$sql = "
			SELECT
				rubric_teaser_template
			FROM
				" . ContentTables::table('rubrics') . "
			WHERE
				Id = '" . intval($row->rubric_id) . "'
		";

			$template = ($template > ''
				? $template
				: DB::query($sql)->getValue());

			$cachefile_docid = self::cacheFile((int) $row->Id, $context);
			$cacheEnabled = $context->cacheEnabled() && !PublicLayoutInspector::enabled();

			if (PublicLayoutInspector::enabled())
			{
				$cachefile_docid = null;
			}
			else if (is_file($cachefile_docid) && $cacheEnabled)
			{
				$check_file = $context->changedElementsAt();

				if ($check_file > filemtime($cachefile_docid))
					File::delete($cachefile_docid);
			}
			else
				{
					if (is_file($cachefile_docid))
						File::delete($cachefile_docid);
				}

			// Если включен DEV MODE, то отключаем кеширование запросов
			if (defined('DEV_MODE') AND DEV_MODE)
				$cachefile_docid = null;

			if ($cachefile_docid === null || !is_file($cachefile_docid))
			{
				// Если включено в настройках, проверять поле по содержимому
				if (defined('USE_GET_FIELDS') && USE_GET_FIELDS)
				{
					$template = preg_replace("/\[tag:if_notempty:rfld:([a-zA-Z0-9-_]+)]\[(more|esc|img|strip|[0-9-]+)]/u", '<'.'?php if((htmlspecialchars(\\App\\Frontend\\PublicFieldApi::raw(\'$1\', '.$row->Id.'), ENT_QUOTES)) != \'\') { '.'?'.'>', $template);
					$template = preg_replace("/\[tag:if_empty:rfld:([a-zA-Z0-9-_]+)]\[(more|esc|img|strip|[0-9-]+)]/u", '<'.'?php if((htmlspecialchars(\\App\\Frontend\\PublicFieldApi::raw(\'$1\', '.$row->Id.'), ENT_QUOTES)) == \'\') { '.'?'.'>', $template);
				}
				else
					{
						$template = preg_replace("/\[tag:if_notempty:rfld:([a-zA-Z0-9-_]+)]\[(more|esc|img|strip|[0-9-]+)]/u", '<'.'?php if((htmlspecialchars(\\App\\Frontend\\PublicFieldApi::renderRequest(\'$1\', '.$row->Id.', \'$2\', '.(int)$row->rubric_id.'), ENT_QUOTES)) != \'\') { '.'?'.'>', $template);
						$template = preg_replace("/\[tag:if_empty:rfld:([a-zA-Z0-9-_]+)]\[(more|esc|img|strip|[0-9-]+)]/u", '<'.'?php if((htmlspecialchars(\\App\\Frontend\\PublicFieldApi::renderRequest(\'$1\', '.$row->Id.', \'$2\', '.(int)$row->rubric_id.'), ENT_QUOTES)) == \'\') { '.'?'.'>', $template);
					}

				$template = str_replace('[tag:if:else]', '<?php }else{ ?>', $template);
				$template = str_replace('[tag:/if]', '<?php } ?>', $template);

				// Совместимый тег block использует единый реестр блоков.
				$item = preg_replace_callback('/\[tag:block:([A-Za-z0-9-_]{1,20}+)\]/', function ($match) use ($blockRenderer) {
					return $blockRenderer->visual($match[1]);
				}, $template);

				// Парсим теги системных блоков
				$item = preg_replace_callback('/\[tag:sysblock:([A-Za-z0-9-_]{1,20}+)(|:\{(.*?)\})\]/',
					function ($m) use ($blockRenderer)
					{
						return $blockRenderer->system($m[1], $m[2]);
					},
					$item);

				// Парсим элементы полей
				$item = preg_replace_callback('/\[tag:rfld:([a-zA-Z0-9-_]+)\]\[([0-9]+)]\[([0-9]+)]/',
					function ($m) use ($row, $documentFieldRenderer)
					{
						return $documentFieldRenderer->element($m[1], $m[2], $m[3], (int)$row->Id);
					},
					$item);

				// Парсим теги полей
				$item = preg_replace_callback('/\[tag:rfld:([a-zA-Z0-9-_]+)]\[(more|esc|img|strip|[0-9-]+)]/',
					function ($match) use ($row, $fieldRenderer)
					{
						return $fieldRenderer->render($match[1], (int)$row->Id, $match[2], (int)$row->rubric_id);
					},
					$item);

				// Повторно парсим теги полей
				$item = preg_replace_callback('/\[tag:rfld:([a-zA-Z0-9-_]+)]\[(more|esc|img|strip|[0-9-]+)]/',
					function ($m) use ($row, $fieldRenderer)
					{
						return $fieldRenderer->render($m[1], (int)$row->Id, $m[2], (int)$row->rubric_id);
					},
					$item);

				// Возвращаем поле документа из БД (document_***)
				$item = preg_replace_callback('/\[tag:doc:([a-zA-Z0-9-_]+)\]/u',
					function ($m) use ($row)
					{
						if ($m[1] === 'document_teaser' || $m[1] === 'document_excerpt') {
							return DocumentExcerpt::explicit($row);
						}

						$key = $m[1];
						return isset($row->{$key})
							? $row->{$key}
							: null;
					},
					$item
				);
				$item = str_replace('[tag:excerpt]', DocumentExcerpt::resolve($row), $item);

				// Public runtime is single-language; legacy Smarty language files are retired.
				$item = preg_replace('/\[tag:langfile:[a-zA-Z0-9-_]+\]/u', '', $item);

				// Абсолютный путь
				$item = str_replace('[tag:path]', ABS_PATH, $item);

				// Путь к папке шаблона
				$item = str_replace('[tag:mediapath]', ABS_PATH . 'templates/' . ((defined('THEME_FOLDER') === false)
					? DEFAULT_THEME_FOLDER
					: THEME_FOLDER)
				. '/', $item);

				// Watermarks
				$watermarks = new WatermarkService();
				$item = preg_replace_callback(
					'/\[tag:watermark:(.+?):([a-zA-Z]+):([0-9]+)\]/',
					array($watermarks, 'fromTag'),
					$item
				);

				// Удаляем ошибочные теги полей документа и языковые, в шаблоне рубрики
				$item = preg_replace('/\[tag:doc:\d*\]/', '', $item);
				$item = preg_replace('/\[tag:langfile:\d*\]/', '', $item);

				// Делаем линки на миниатюры
				$item = preg_replace_callback('/\[tag:([rcfts]\d+x\d+r*):(.*?)]/', array(ThumbnailUrl::class, 'fromTag'), $item);

				// Если был вызов тизера, ищем параметры
				if ($tparams != '')
				{
					// Заменяем tparam в тизере
					$item = preg_replace_callback('/\[tparam:([0-9]+)\]/',
						function ($m) use ($tparams_id, &$teaserParams)
						{
							return isset($teaserParams[$tparams_id][$m[1]]) ? $teaserParams[$tparams_id][$m[1]] : '';
						},
						$item);
				}
				else
					{
						// Если чистый запрос тизера, просто вытираем tparam
						$item = preg_replace('/\[tparam:([0-9]+)\]/', '', $item);
					}

				// Блок для проверки передачи параметров тизеру
				/*
					if (count($teaserParams[$tparams_id]))
					{
						Debug::_echo($teaserParams);
						Debug::_echo($row_Id_mas);
						Debug::_echo($item, true);
					}

				*/

				$item = str_replace('[tag:domain]', Url::site(), $item);

				$link = Url::rewrite('index.php?id=' . $row->Id . '&amp;doc=' . (empty($row->document_alias) ? Url::prepare($row->document_title) : $row->document_alias));
				$item = str_replace('[tag:link]', $link, $item);
				$item = str_replace('[tag:docid]', $row->Id, $item);
				$item = str_replace('[tag:itemid]', $row->Id, $item);
				$item = str_replace('[tag:docitemnum]', $context->itemNumber(), $item);
				$adminLink = AdminLocation::url('documents/' . (int) $row->Id . '/edit') . '?quick_edit=1&pop=1';
				$item = str_replace('[tag:adminlink]', $adminLink, $item);
				$item = str_replace('[tag:doctitle:attr]', htmlspecialchars(htmlspecialchars_decode((string) $row->document_title, ENT_QUOTES), ENT_QUOTES, 'UTF-8'), $item);
				$item = str_replace('[tag:doctitle]', stripslashes(htmlspecialchars_decode($row->document_title)), $item);
				$item = str_replace('[tag:docparent]', $row->document_parent, $item);
				$item = str_replace('[tag:docdate]', \App\Helpers\Locales::translateDate(strftime(DATE_FORMAT, $row->document_published)), $item);
				$item = str_replace('[tag:doctime]', \App\Helpers\Locales::translateDate(strftime(TIME_FORMAT, $row->document_published)), $item);
				$item = str_replace('[tag:humandate]', \App\Helpers\Locales::humanDate($row->document_published), $item);

				$item = preg_replace_callback('/\[tag:date:([a-zA-Z0-9-. \/]+)\]/',
					function ($m) use ($row)
					{
						return \App\Helpers\Locales::translateDate(date($m[1], $row->document_published));
					},
					$item);

				if (preg_match('/\[tag:docauthor]/u', $item))
					$item = str_replace('[tag:docauthor]', \App\Common\Auth\PublicUserNames::byId($row->document_author_id), $item);

				$item = str_replace('[tag:docauthorid]', $row->document_author_id, $item);

				$item = preg_replace_callback('/\[tag:docauthoravatar:(\d+)\]/',
					function ($m) use ($row)
					{
						return PublicAvatar::url((int) $row->document_author_id, $m[1]);
					},
					$item);

				if ($cacheEnabled && $cachefile_docid !== null)
				{
					File::putAtomic($cachefile_docid, $item);
				}
			}
			else
				{
				$item = (string) File::getContent($cachefile_docid);
				}

			// Кол-во просмотров
			$item = str_replace('[tag:docviews]', $row->document_count_view, $item);

			unset($row, $template);

			return StoredPhpRuntime::normalizeDataPrefixes($item);
		}

		public static function requiresDocumentData($documentId, RequestItemContext $context)
		{
			if ((defined('DEV_MODE') && DEV_MODE) || !$context->cacheEnabled() || PublicLayoutInspector::enabled()) {
				return true;
			}

			$cacheFile = self::cacheFile($documentId, $context);
			return !is_file($cacheFile) || $context->changedElementsAt() > (int) @filemtime($cacheFile);
		}

		protected static function cacheFile($documentId, RequestItemContext $context)
		{
			$documentId = (int) $documentId;
			$hash = 'g-' . UGROUP;
			$hash .= 'r-' . $context->requestId();
			$hash .= 't-' . $documentId;
			$hash .= 'v-' . self::CACHE_VERSION;
			$cacheId = 'requests/elements/' . floor($documentId / 1000) . '/' . $documentId;

			return BASEPATH . '/tmp/cache/sql/' . $cacheId . '/' . md5($hash) . '.element';
		}

		public static function decorateSequence($item, $documentId, $itemNumber, $lastItem)
		{
			$item = str_replace('[tag:item_num]', $itemNumber, (string) $item);
			$item = '<?php $item_num=' . var_export((int) $itemNumber, true)
				. '; $last_item=' . var_export((bool) $lastItem, true) . '?>' . $item;
			$item = '<?php $req_item_id = ' . (int) $documentId . '; ?>' . $item;
			$item = str_replace('[tag:if_first]', '<?php if(isset($item_num) && $item_num===1) { ?>', $item);
			$item = str_replace('[tag:if_not_first]', '<?php if(isset($item_num) && $item_num!==1) { ?>', $item);
			$item = str_replace('[tag:if_last]', '<?php if(isset($last_item) && $last_item) { ?>', $item);
			$item = str_replace('[tag:if_not_last]', '<?php if(isset($item_num) && !$last_item) { ?>', $item);
			$item = preg_replace('/\[tag:if_every:([0-9-]+)\]/u', '<?php if(isset($item_num) && !($item_num % $1)){ ?>', $item);
			$item = preg_replace('/\[tag:if_not_every:([0-9-]+)\]/u', '<?php if(isset($item_num) && ($item_num % $1)){ ?>', $item);
			$item = str_replace('[tag:/if]', '<?php  } ?>', $item);
			return str_replace('[tag:if_else]', '<?php  }else{ ?>', $item);
		}
	}
