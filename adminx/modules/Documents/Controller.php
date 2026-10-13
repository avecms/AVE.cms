<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Documents/Controller.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Adminx\Documents;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Common\AdminAssets;
	use App\Common\ApiTokenRepository;
	use App\Common\AuditLog;
	use App\Common\Auth;
	use App\Common\Controller as BaseController;
	use App\Adminx\Support\BulkActionExecutor;
	use App\Common\ErrorReport;
	use App\Common\Permission;
	use App\Adminx\Support\CodeEditor;
	use App\Adminx\Support\SavedViews;
	use App\Adminx\Rubrics\AdminView;
	use App\Content\Documents\DocumentHookException;
	use App\Content\Documents\ContentCacheInvalidator;
	use App\Content\Documents\DocumentCreationPresetRepository;
	use App\Content\Documents\DocumentMediaDraft;
	use App\Content\Documents\DocumentMediaFinalizer;
	use App\Content\Documents\DocumentSaveEvents;
	use App\Content\Documents\DocumentSaveRejected;
	use App\Content\Documents\DocumentSnapshotRepository;
	use App\Content\Documents\DocumentSnapshotStore;
	use App\Content\Documents\FieldTemplateManifest;
	use App\Content\Documents\RubricSchemaBuilder;
	use App\Content\Fields\DocumentRelationIndex;
	use App\Frontend\DocumentRevisionPreview;
	use App\Helpers\Request;
	use DB;

	class Controller extends BaseController
	{
		protected function savedViewFields() { return array('q', 'field', 'rubric_id', 'state', 'per_page'); }
		protected function savedViewGuard() { if (($error = $this->csrfGuard()) !== null) { return $error; } return Permission::check('view_documents') ? null : $this->error('Недостаточно прав', array(), 403); }

		public function index(array $params = array())
		{
			AdminAssets::addStyle($this->base() . '/modules/Documents/assets/documents.css', 50);
			AdminAssets::addScript($this->base() . '/modules/Documents/assets/documents.js', 50);
			CodeEditor::useCodeMirror('application/x-httpd-php');

			$filters = array(
				'q' => Request::getStr('q', ''),
				'field' => Request::getStr('field', ''),
				'rubric_id' => Request::getInt('rubric_id', 0),
				'state' => Request::getStr('state', ''),
				'page' => max(1, Request::getInt('page', 1)),
				'per_page' => Request::getInt('per_page', 25),
			);
			//-- Поля поиска зависят от выбранной рубрики; сбрасываем поле, если
			//   оно не входит в её набор.
			$searchableFields = Model::searchableFields($filters['rubric_id']);
			$filters['field'] = Model::normalizeSearchField($filters['field'], $filters['rubric_id']);
			$result = Model::items($filters);
			$adminView = AdminView::forDocuments($filters['rubric_id'], $result['items']);
			$result['items'] = $adminView['documents'];
			$stateLabels = array(
				'' => 'Рабочие',
				'active' => 'Опубликованные',
				'draft' => 'Черновики',
				'deleted' => 'Удалённые',
			);

			return $this->render('@documents/index.twig', array(
				'documents' => $result['items'],
				'pagination' => $result['pagination'],
				'stats' => Model::stats(),
				'rubrics' => $this->creationRubrics(),
				'searchable_fields' => $searchableFields,
				'filters' => $filters,
				'filter_state_label' => isset($stateLabels[$filters['state']]) ? $stateLabels[$filters['state']] : $stateLabels[''],
				'admin_view' => $adminView,
				'table_name' => Model::documentsTable(),
				'can_manage' => Permission::check('manage_documents'),
				'open_create' => Request::getBool('create', false),
				'saved_views' => SavedViews::all('documents', Auth::id(), $this->savedViewFields()),
			));
		}

		public function saveSavedView(array $params = array())
		{
			if (($error = $this->savedViewGuard()) !== null) { return $error; }
			$filters = json_decode(Request::postStr('filters', '{}'), true);
			if (!is_array($filters)) { return $this->error('Некорректный набор фильтров', array(), 422); }
			try { $views = SavedViews::save('documents', Auth::id(), Request::postStr('title', ''), $filters, $this->savedViewFields()); }
			catch (\InvalidArgumentException $e) { return $this->error($e->getMessage(), array(), 422); }
			return $this->success('Представление сохранено', array('data' => array('views' => $views)));
		}

		public function deleteSavedView(array $params = array())
		{
			if (($error = $this->savedViewGuard()) !== null) { return $error; }
			try { $views = SavedViews::delete('documents', Auth::id(), isset($params['id']) ? $params['id'] : '', $this->savedViewFields()); }
			catch (\InvalidArgumentException $e) { return $this->error($e->getMessage(), array(), 404); }
			return $this->success('Представление удалено', array('data' => array('views' => $views)));
		}

		public function create(array $params = array())
		{
			if (!Permission::check('manage_documents')) {
				return $this->renderStatus('@adminx/404.twig', array('title' => 'Недостаточно прав'), 403);
			}

			AdminAssets::addStyle($this->base() . '/modules/Documents/assets/documents.css', 50);
			AdminAssets::addScript($this->base() . '/modules/Documents/assets/documents.js', 50);
			CodeEditor::useRichEditor();

			$rubricId = Request::getInt('rubric_id', 0);
			if ($rubricId <= 0 || !Model::rubric($rubricId)) {
				$this->redirect($this->base() . '/documents?create=1');
				return '';
			}

			$document = Model::blank($rubricId);
			$presetRepository = new DocumentCreationPresetRepository();
			$preset = null;
			$presetId = Request::getInt('preset', 0);
			if ($presetId > 0) {
				$preset = $presetRepository->find($presetId);
				if (!$preset || (int) $preset['rubric_id'] !== $rubricId) {
					return $this->renderStatus('@adminx/404.twig', array('title' => 'Пресет документа не найден'), 404);
				}

				$document = $presetRepository->applyToDocument($document, $preset);
			}

			$mediaDraftToken = DocumentMediaDraft::issue(Auth::id(), 0);

			return $this->render('@documents/edit.twig', array(
				'document' => $document,
				'field_groups' => Model::fieldsForRubric(
					(int) $document['rubric_id'],
					0,
					$mediaDraftToken,
					$preset ? $presetRepository->fieldDefaults($preset) : array()
				),
				'media_draft_token' => $mediaDraftToken,
				'rubrics' => Model::rubrics(),
				'templates' => Model::rubricTemplates((int) $document['rubric_id']),
				'navigation_items' => Model::navigationItems(),
				'authors' => Model::authors(),
				'is_new' => true,
				'creation_preset' => $preset,
				'can_manage' => true,
				'actor_id' => Auth::id(),
			));
		}

		public function views(array $params = array())
		{
			AdminAssets::addStyle($this->base() . '/modules/Documents/assets/documents.css', 50);
			AdminAssets::addScript($this->base() . '/modules/Documents/assets/documents.js', 50);
			$days = Request::getInt('days', 30);
			return $this->render('@documents/views.twig', array(
				'view_stats' => Model::viewStats($days),
				'can_manage' => Permission::check('manage_documents'),
			));
		}

		public function redirects(array $params = array())
		{
			AdminAssets::addStyle($this->base() . '/modules/Documents/assets/documents.css', 50);
			AdminAssets::addScript($this->base() . '/modules/Documents/assets/redirects.js', 50);
			$filters = array(
				'q' => Request::getStr('q', ''),
				'header' => Request::getInt('header', 0),
				'page' => max(1, Request::getInt('page', 1)),
			);
			$result = Model::allAliasHistory($filters);
			return $this->render('@documents/redirects.twig', array(
				'redirects' => $result['items'],
				'pagination' => $result['pagination'],
				'stats' => Model::aliasHistoryStats(),
				'filters' => $filters,
				'can_manage' => Permission::check('manage_documents'),
			));
		}

		public function apiTokens(array $params = array())
		{
			if (!Permission::check('manage_document_api')) {
				return $this->renderStatus('@adminx/404.twig', array('title' => 'Недостаточно прав'), 403);
			}

			AdminAssets::addStyle($this->base() . '/modules/Documents/assets/documents.css', 50);
			AdminAssets::addScript($this->base() . '/modules/Documents/assets/documents.js', 50);
			$tokens = array_values(array_filter(ApiTokenRepository::all(), function ($token) {
				return ApiTokenRepository::hasScope($token, 'documents:read')
					|| ApiTokenRepository::hasScope($token, 'documents:write');
			}));
			return $this->render('@documents/api.twig', array('tokens' => $tokens, 'can_manage_api' => true));
		}

		public function issueApiToken(array $params = array())
		{
			if (($err = $this->csrfGuard()) !== null) { return $err; }
			if (!Permission::check('manage_document_api')) { return $this->error('Недостаточно прав', array(), 403); }
			try {
				$scopes = Request::post('scopes', array());
				$scopes = is_array($scopes) ? $scopes : array();
				$result = ApiTokenRepository::issueRestricted(
					Auth::id(),
					Request::postStr('name', ''),
					$scopes,
					array('documents:read', 'documents:write', 'documents:*'),
					Request::postStr('expires_at', '')
				);
				AuditLog::record('document.api_token_created', array('actor_id'=>Auth::id(),'target_type'=>'api_token','target_id'=>$result['id'],'meta'=>array('scopes'=>$result['scopes'])));
				return $this->success('API-токен создан', array('data' => $result));
			} catch (\Throwable $e) { return $this->error($e->getMessage(), array(), 422); }
		}

		public function revokeApiToken(array $params = array())
		{
			if (($err = $this->csrfGuard()) !== null) { return $err; }
			if (!Permission::check('manage_document_api')) { return $this->error('Недостаточно прав', array(), 403); }
			$id = isset($params['id']) ? (int) $params['id'] : 0;
			if (!ApiTokenRepository::revokeRestricted($id, array('documents:read', 'documents:write'))) {
				return $this->error('Токен документов не найден или уже отозван', array(), 404);
			}

			AuditLog::record('document.api_token_revoked', array('actor_id'=>Auth::id(),'target_type'=>'api_token','target_id'=>$id));
			return $this->success('API-токен отозван');
		}

		public function clearViews(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			$count = Model::clearViewStats();
			return $this->success('Подневная статистика очищена', array('data' => array('count' => $count)));
		}

		public function edit(array $params = array())
		{
			if (!Permission::check('manage_documents')) {
				return $this->renderStatus('@adminx/404.twig', array('title' => 'Недостаточно прав'), 403);
			}

			AdminAssets::addStyle($this->base() . '/modules/Documents/assets/documents.css', 50);
			AdminAssets::addScript($this->base() . '/modules/Documents/assets/documents.js', 50);
			CodeEditor::useRichEditor();

			$item = Model::one(isset($params['id']) ? (int) $params['id'] : 0);
			if (!$item) {
				return $this->renderStatus('@adminx/404.twig', array('title' => 'Документ не найден'), 404);
			}

			$mediaDraftToken = DocumentMediaDraft::issue(Auth::id(), (int) $item['Id']);

			return $this->render('@documents/edit.twig', array(
				'document' => $item,
				'field_groups' => Model::fieldsForDocument((int) $item['Id'], $mediaDraftToken),
				'media_draft_token' => $mediaDraftToken,
				'rubrics' => Model::rubrics(),
				'templates' => Model::rubricTemplates((int) $item['rubric_id']),
				'navigation_items' => Model::navigationItems(),
				'authors' => Model::authors(),
				'is_new' => false,
				'is_homepage' => (int) $item['Id'] === 1,
				'can_manage' => true,
				'quick_edit' => Request::getBool('quick_edit', false),
				'actor_id' => Auth::id(),
				'document_relations' => DocumentRelationIndex::describe((int) $item['Id']),
			));
		}

		public function show(array $params = array())
		{
			$item = Model::one(isset($params['id']) ? (int) $params['id'] : 0);
			if (!$item) {
				return $this->error('Документ не найден', array(), 404);
			}

			return $this->success('', array('data' => $item));
		}

		public function aliasCheck(array $params = array())
		{
			if (!Permission::check('manage_documents')) {
				return $this->error('Недостаточно прав', array(), 403);
			}

			$alias = trim(Request::getStr('alias', ''));
			$kind = Request::getStr('kind', 'alias') === 'short' ? 'short' : 'alias';
			$id = Request::getInt('id', 0);
			if ($alias === '') {
				$message = ($kind === 'short' ? 'Короткий alias' : 'Alias') . ' можно оставить пустым';
				return $this->success('Alias не указан', array('data' => array('ok' => true, 'state' => 'empty', 'message' => $message)));
			}

			$pattern = $kind === 'short' ? '/^[a-zA-Z0-9_\-]+$/' : '/^[a-zA-Z0-9_\\-\\/\\.]+$/';
			if (!preg_match($pattern, $alias)) {
				$message = $kind === 'short'
					? 'Допустимы латиница, цифры, дефис и подчёркивание'
					: 'Допустимы латиница, цифры, точка, дефис, подчёркивание и слэш';
				return $this->success('Некорректный alias', array('data' => array('ok' => false, 'state' => 'error', 'message' => $message)));
			}

			$conflict = Model::aliasConflict($alias, $id);
			if ($conflict) {
				$label = isset($conflict['label']) ? (string) $conflict['label'] : 'другой раздел';
				return $this->success('Alias занят', array('data' => array('ok' => false, 'state' => 'error', 'message' => 'URL уже занят: ' . $label)));
			}

			return $this->success('Alias свободен', array('data' => array('ok' => true, 'state' => 'ok', 'message' => 'Alias свободен')));
		}

		public function slug(array $params = array())
		{
			if (!Permission::check('manage_documents')) {
				return $this->error('Недостаточно прав', array(), 403);
			}

			$title = trim(Request::postStr('title', ''));
			$id = Request::postInt('id', 0);
			$parent = Request::postInt('parent', 0);
			$rubricId = Request::postInt('rubric_id', 0);
			$published = Request::postStr('published', '');
			$publishedAt = $published !== '' ? strtotime($published) : 0;
			if ($title === '') {
				return $this->error('Укажите название документа', array(), 422);
			}

			return $this->success('', array('data' => array(
				'ok' => true,
				'alias' => Model::generateAlias($title, $id, $parent, $rubricId, $publishedAt),
			)));
		}

		public function shortAlias(array $params = array())
		{
			if (!Permission::check('manage_documents')) {
				return $this->error('Недостаточно прав', array(), 403);
			}

			$id = Request::postInt('id', 0);
			return $this->success('', array('data' => array('ok' => true, 'alias' => Model::generateShortAlias($id))));
		}

		public function documentPicker(array $params = array())
		{
			if (!Permission::check('manage_documents')) {
				return $this->error('Недостаточно прав', array(), 403);
			}

			$scope = Request::getStr('scope', 'document');
			$items = $scope === 'parent'
				? Model::parentDocumentPicker(
					Request::getStr('q', ''),
					Request::getInt('limit', 20),
					Request::getInt('exclude_id', 0)
				)
				: Model::documentPicker(
					Request::getStr('q', ''),
					Request::getStr('rubric_id', ''),
					Request::getInt('limit', 20)
				);

			return $this->success('', array(
				'data' => array(
					'items' => $items,
				),
			));
		}

		public function termSuggestions(array $params = array())
		{
			if (!Permission::check('manage_documents')) {
				return $this->error('Недостаточно прав', array(), 403);
			}

			$kind = Request::getStr('kind', 'tags');
			if (!in_array($kind, array('keywords', 'tags'), true)) {
				return $this->error('Неизвестный тип значений', array(), 422);
			}

			return $this->success('', array('data' => array(
				'items' => Model::termSuggestions(
					$kind,
					Request::getStr('q', ''),
					Request::getInt('rubric_id', 0),
					Request::getInt('limit', 12)
				),
			)));
		}

		public function bulk(array $params = array())
		{
			if (($err = $this->guard()) !== null) { return $err; }
			$action = Request::postStr('action', '');
			$handler = function ($id) use ($action) {
					$doc = Model::one($id);
					if (!$doc) { return false; }
					if ($action === 'publish' && empty($doc['document_status'])) { Model::toggleStatus($id); }
					elseif ($action === 'unpublish' && !empty($doc['document_status'])) { Model::toggleStatus($id); }
					elseif ($action === 'delete') { Model::delete($id); }
					elseif ($action === 'restore') { Model::restore($id); }
					elseif ($action === 'purge') { Model::purge($id); }
					return true;
			};

			try {
				$result = BulkActionExecutor::execute($action, Request::post('ids', array()), array(
					'publish' => $handler,
					'unpublish' => $handler,
					'delete' => $handler,
					'restore' => $handler,
					'purge' => $handler,
				), 200);
			} catch (\InvalidArgumentException $e) {
				return $this->error($e->getMessage(), array(), 422);
			}

			return $this->success('Пакетное действие выполнено', array('data' => $result));
		}

		public function bulkEditor(array $params = array())
		{
			if (!Permission::check('manage_documents')) {
				return $this->renderStatus('@adminx/404.twig', array('title' => 'Недостаточно прав'), 403);
			}

			AdminAssets::addStyle($this->base() . '/modules/Documents/assets/documents.css', 50);
			AdminAssets::addScript($this->base() . '/modules/Documents/assets/documents.js', 50);
			return $this->render('@documents/bulk-editor.twig', array(
				'bulk_options' => BulkEditor::options(),
				'bulk_clean_descriptions' => Request::getStr('operation', '') === 'strip_description_styles',
			));
		}

		public function bulkEditorFields(array $params = array())
		{
			if (!Permission::check('manage_documents')) {
				return $this->error('Недостаточно прав', array(), 403);
			}

			return $this->success('', array('data' => array(
				'items' => BulkEditor::fields(Request::getInt('rubric_id', 0)),
			)));
		}

		public function bulkEditorPreview(array $params = array())
		{
			if (($err = $this->guard()) !== null) { return $err; }
			try {
				$plan = BulkEditor::preview(Request::postAll(), Auth::id());
			} catch (\InvalidArgumentException $e) {
				return $this->error($e->getMessage(), array(), 422);
			} catch (\Throwable $e) {
				return $this->error(ErrorReport::publicMessage('Не удалось подготовить массовое изменение', $e, 'DOCBULKPREVIEW'), array(), 500);
			}

			return $this->success('Предпросмотр подготовлен', array('data' => array('plan' => $plan)));
		}

		public function bulkEditorRun(array $params = array())
		{
			if (($err = $this->guard()) !== null) { return $err; }
			try {
				$plan = BulkEditor::runChunk(Request::postStr('token', ''), Auth::id());
			} catch (\InvalidArgumentException $e) {
				return $this->error($e->getMessage(), array(), 422);
			} catch (\Throwable $e) {
				return $this->error(ErrorReport::publicMessage('Не удалось выполнить пакет документов', $e, 'DOCBULKRUN'), array(), 500);
			}

			return $this->success($plan['status'] === 'completed' ? 'Массовое изменение завершено' : 'Пакет обработан', array('data' => array('plan' => $plan)));
		}

		public function bulkEditorCancel(array $params = array())
		{
			if (($err = $this->guard()) !== null) { return $err; }
			try {
				$plan = BulkEditor::cancel(Request::postStr('token', ''), Auth::id());
			} catch (\Throwable $e) {
				return $this->error($e->getMessage(), array(), 422);
			}

			return $this->success('Выполнение остановлено', array('data' => array('plan' => $plan)));
		}

		public function store(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			$input = Model::prepareAliasInput(Request::postAll(), 0);
			$fieldValues = isset($input['fields']) && is_array($input['fields']) ? $input['fields'] : array();
			$rubricId = isset($input['rubric_id']) ? (int) $input['rubric_id'] : 0;
			$fieldGroups = Model::fieldsForRubric($rubricId, 0);
			$fieldValues = Model::conditionalFieldValues($rubricId, $fieldValues, 0, $fieldGroups);
			$fieldValues = Model::computedFieldValues($rubricId, $fieldValues, $input, 0, $fieldGroups);
			$input['fields'] = $fieldValues;
			try {
				DocumentSaveEvents::before('create', 'adminx', 0, $rubricId, Auth::id(), $input, $fieldValues);
				DocumentHookRunner::before($rubricId, $input, $fieldValues, 0, Auth::id(), true, 'adminx');
			} catch (DocumentSaveRejected $e) {
				return $this->error($e->getMessage(), $e->errors(), 422);
			} catch (DocumentHookException $e) {
				return $this->error('Код рубрики остановил создание документа', array('rubric_code_start' => $e->getMessage()), 422);
			}

			$fieldValues = Model::conditionalFieldValues($rubricId, $fieldValues, 0, $fieldGroups);
			$fieldValues = Model::computedFieldValues($rubricId, $fieldValues, $input, 0, $fieldGroups);
			$input['fields'] = $fieldValues;
			$input = Model::prepareAliasInput($input, 0);
			$errors = $this->validate($input, 0);
			$errors = array_merge($errors, Model::validateFieldValues($rubricId, $fieldValues, 0, $fieldGroups));
			if (!empty($errors)) {
				return $this->error('Проверьте поля документа', $errors, 422);
			}

			$id = 0;
			$mediaFinalization = null;
			$previousDatabaseExceptionMode = DB::$throw_exception_on_error;
			DB::$throw_exception_on_error = true;
			DB::startTransaction();
			try {
				$id = Model::save(0, $input, Auth::id());
				$mediaFinalization = DocumentMediaFinalizer::finalize(
					Request::postStr('media_draft_token', ''),
					Auth::id(),
					$id,
					$rubricId,
					$fieldValues
				);
				$afterHook = DocumentHookRunner::after($rubricId, $input, $fieldValues, $id, Auth::id(), true, 'adminx');
				if ($afterHook['document_changed'] || $afterHook['save_requested']) {
					Model::save($id, $input, Auth::id());
				}

				Model::saveFields($id, $fieldValues, $fieldGroups);
				DocumentSaveEvents::persisted('create', 'adminx', $id, $rubricId, Auth::id(), $input, $fieldValues);
				DB::commit();
				$mediaFinalization->commit();
			} catch (DocumentHookException $e) {
				DB::rollback();
				if ($mediaFinalization) { $mediaFinalization->rollback(); }
				if ($id > 0) { ContentCacheInvalidator::document($id, false); }
				if (ErrorReport::isDatabase($e)) {
					return $this->error(ErrorReport::publicMessage(
						'Код рубрики после сохранения не выполнен из-за ошибки базы данных',
						$e,
						'DOCREATE',
						array('rubric_id' => $rubricId, 'document_id' => $id)
					), array(), 500);
				}

				return $this->error(
					'Код рубрики после сохранения остановил создание документа',
					array('rubric_code_end' => $e->getMessage()),
					422
				);
			} catch (\Throwable $e) {
				DB::rollback();
				if ($mediaFinalization) { $mediaFinalization->rollback(); }
				if ($id > 0) { ContentCacheInvalidator::document($id, false); }
				return $this->error(ErrorReport::publicMessage(
					'Не удалось создать документ',
					$e,
					'DOCREATE',
					array('rubric_id' => $rubricId, 'document_id' => $id)
				), array(), 500);
			} finally {
				DB::$throw_exception_on_error = $previousDatabaseExceptionMode;
			}

			$snapshotError = ContentCacheInvalidator::consumeError($id);
			$snapshot = (new DocumentSnapshotRepository())->find($id);
			DocumentSaveEvents::after('create', 'adminx', $id, $rubricId, Auth::id(), $input, $fieldValues, array(), is_array($snapshot) ? $snapshot : array());
			$nextMediaDraftToken = DocumentMediaDraft::issue(Auth::id(), $id);
			$editorState = Model::editorState($id);
			return $this->success('Документ создан', array('data' => array(
				'id' => $id,
				'document_version' => $editorState ? $editorState['version'] : Model::documentVersion($id),
				'editor_state' => $editorState,
				'snapshot_warning' => $snapshotError,
				'media_draft_token' => $nextMediaDraftToken,
			), 'redirect' => $this->base() . '/documents/' . $id . '/edit'));
		}

		public function previewPayload(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			$id = Request::postInt('id', 0);
			$document = $id > 0 ? Model::one($id) : null;
			if ($id > 0 && !$document) {
				return $this->error('Документ не найден', array(), 404);
			}

			$input = Model::prepareAliasInput(Request::postAll(), $id);
			$rubricId = $document ? (int) $document['rubric_id'] : (isset($input['rubric_id']) ? (int) $input['rubric_id'] : 0);
			$fieldValues = isset($input['fields']) && is_array($input['fields']) ? $input['fields'] : array();
			$fieldGroups = Model::fieldsForRubric($rubricId, $id);
			$fieldValues = Model::conditionalFieldValues($rubricId, $fieldValues, $id, $fieldGroups);
			$fieldValues = Model::computedFieldValues($rubricId, $fieldValues, $input, $id, $fieldGroups);
			$input['fields'] = $fieldValues;
			$errors = $this->validate($input, $id);
			$errors = array_merge($errors, Model::validateFieldValues($rubricId, $fieldValues, $id, $fieldGroups));

			return $this->success('Данные сформированы без сохранения', array('data' => array(
				'payload' => Model::previewPayload($id, $input, $fieldValues, Auth::id(), $fieldGroups),
				'validation_errors' => $errors,
			)));
		}

		public function update(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			$id = isset($params['id']) ? (int) $params['id'] : 0;
			$doc = Model::one($id);
			if (!$doc) {
				return $this->error('Документ не найден', array(), 404);
			}

			$input = Request::postAll();
			$expectedVersion = isset($input['document_version']) ? (int) $input['document_version'] : 0;
			if ($expectedVersion <= 0 || $expectedVersion !== (int) $doc['document_version']) {
				return $this->editConflict($id, (int) $doc['document_version']);
			}

			$fieldValues = isset($input['fields']) && is_array($input['fields']) ? $input['fields'] : array();
			$fieldGroups = Model::fieldsForRubric((int) $doc['rubric_id'], $id);
			$fieldValues = Model::conditionalFieldValues((int) $doc['rubric_id'], $fieldValues, $id, $fieldGroups);
			$fieldValues = Model::computedFieldValues((int) $doc['rubric_id'], $fieldValues, $input, $id, $fieldGroups);
			$input['fields'] = $fieldValues;
			try {
				DocumentSaveEvents::before('update', 'adminx', $id, (int) $doc['rubric_id'], Auth::id(), $input, $fieldValues, $doc);
				DocumentHookRunner::before((int) $doc['rubric_id'], $input, $fieldValues, $id, Auth::id(), false, 'adminx');
			} catch (DocumentSaveRejected $e) {
				return $this->error($e->getMessage(), $e->errors(), 422);
			} catch (DocumentHookException $e) {
				return $this->error('Код рубрики остановил сохранение документа', array('rubric_code_start' => $e->getMessage()), 422);
			}

			$fieldValues = Model::conditionalFieldValues((int) $doc['rubric_id'], $fieldValues, $id, $fieldGroups);
			$fieldValues = Model::computedFieldValues((int) $doc['rubric_id'], $fieldValues, $input, $id, $fieldGroups);
			$input['fields'] = $fieldValues;
			$errors = $this->validate($input, $id);
			$errors = array_merge($errors, Model::validateFieldValues((int) $doc['rubric_id'], $fieldValues, $id, $fieldGroups));
			if (!empty($errors)) {
				return $this->error('Проверьте поля документа', $errors, 422);
			}

			$mediaReplacement = DocumentMediaReplacement::capture(
				$fieldGroups,
				$fieldValues,
				DocumentMediaReplacement::requestedFieldIds(Request::post('media_replace_fields', array()))
			);

			$mediaFinalization = null;
			$previousDatabaseExceptionMode = DB::$throw_exception_on_error;
			DB::$throw_exception_on_error = true;
			DB::startTransaction();
			try {
				Model::assertEditVersion($id, $expectedVersion);
				Revisions::capture($id, Auth::id());
				Model::save($id, $input, Auth::id());
				$mediaFinalization = DocumentMediaFinalizer::finalize(
					Request::postStr('media_draft_token', ''),
					Auth::id(),
					$id,
					(int) $doc['rubric_id'],
					$fieldValues
				);
				$afterHook = DocumentHookRunner::after((int) $doc['rubric_id'], $input, $fieldValues, $id, Auth::id(), false, 'adminx');
				if ($afterHook['document_changed'] || $afterHook['save_requested']) {
					Model::save($id, $input, Auth::id());
				}

				Model::saveFields($id, $fieldValues, $fieldGroups);
				DocumentSaveEvents::persisted('update', 'adminx', $id, (int) $doc['rubric_id'], Auth::id(), $input, $fieldValues, $doc);
				DB::commit();
				$mediaFinalization->commit();
			} catch (EditConflict $e) {
				DB::rollback();
				if ($mediaFinalization) { $mediaFinalization->rollback(); }
				return $this->editConflict($id, $e->currentVersion());
			} catch (DocumentHookException $e) {
				DB::rollback();
				if ($mediaFinalization) { $mediaFinalization->rollback(); }
				ContentCacheInvalidator::document($id, false);
				if (ErrorReport::isDatabase($e)) {
					return $this->error(ErrorReport::publicMessage(
						'Код рубрики после сохранения не выполнен из-за ошибки базы данных',
						$e,
						'DOCUPDATE',
						array('rubric_id' => (int) $doc['rubric_id'], 'document_id' => $id)
					), array(), 500);
				}

				return $this->error(
					'Код рубрики после сохранения остановил сохранение документа',
					array('rubric_code_end' => $e->getMessage()),
					422
				);
			} catch (\Throwable $e) {
				DB::rollback();
				if ($mediaFinalization) { $mediaFinalization->rollback(); }
				ContentCacheInvalidator::document($id, false);
				return $this->error(ErrorReport::publicMessage(
					'Не удалось сохранить документ',
					$e,
					'DOCUPDATE',
					array('rubric_id' => (int) $doc['rubric_id'], 'document_id' => $id)
				), array(), 500);
			} finally {
				DB::$throw_exception_on_error = $previousDatabaseExceptionMode;
			}

			$snapshotError = ContentCacheInvalidator::consumeError($id);
			$snapshot = (new DocumentSnapshotRepository())->find($id);
			DocumentSaveEvents::after('update', 'adminx', $id, (int) $doc['rubric_id'], Auth::id(), $input, $fieldValues, $doc, is_array($snapshot) ? $snapshot : array());
			$mediaCleanup = DocumentMediaReplacement::cleanup($mediaReplacement, $fieldValues);
			$editorState = Model::editorState($id);
			return $this->success('Документ сохранён', array('data' => array(
				'id' => $id,
				'document_version' => $editorState ? $editorState['version'] : Model::documentVersion($id),
				'editor_state' => $editorState,
				'snapshot_warning' => $snapshotError,
				'media_cleanup' => $mediaCleanup,
				'media_draft_token' => DocumentMediaDraft::issue(Auth::id(), $id),
			), 'redirect' => $this->base() . '/documents/' . $id . '/edit'));
		}

		public function destroy(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			try {
				if (!Model::delete(isset($params['id']) ? (int) $params['id'] : 0)) {
					return $this->error('Документ не найден', array(), 404);
				}
			} catch (\Throwable $e) {
				return $this->error($e->getMessage(), array(), 422);
			}

			return $this->success('Документ помечен удалённым');
		}

		public function revisions(array $params = array())
		{
			$id = isset($params['id']) ? (int) $params['id'] : 0;
			$item = Model::one($id);
			if (!$item) {
				return $this->error('Документ не найден', array(), 404);
			}

			return $this->success('', array(
				'data' => array(
					'document' => array('id' => $item['Id'], 'title' => $item['document_title']),
					'revisions' => Revisions::listForDocument($id),
				),
			));
		}

		public function snapshot(array $params = array())
		{
			if (!Permission::check('manage_documents')) {
				return $this->error('Недостаточно прав', array(), 403);
			}

			$id = isset($params['id']) ? (int) $params['id'] : 0;
			if (!Model::one($id)) {
				return $this->error('Документ не найден', array(), 404);
			}

			return $this->success('', array('data' => $this->snapshotInfo($id)));
		}

		public function rebuildSnapshot(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			$id = isset($params['id']) ? (int) $params['id'] : 0;
			$document = Model::one($id);
			if (!$document) {
				return $this->error('Документ не найден', array(), 404);
			}

			if (!empty($document['document_deleted'])) {
				ContentCacheInvalidator::document($id, false);
				return $this->error('Для удалённого документа JSON-снимок не создаётся', array(), 422);
			}

			if (!ContentCacheInvalidator::document($id, true)) {
				return $this->error(ContentCacheInvalidator::consumeError($id) ?: 'Не удалось пересобрать JSON-снимок', array(), 500);
			}

			return $this->success('JSON-снимок пересобран', array('data' => $this->snapshotInfo($id)));
		}

		public function rebuildSnapshots(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			$after = max(0, Request::postInt('after', 0));
			$rubricId = max(0, Request::postInt('rubric_id', 0));
			$limit = max(1, min(100, Request::postInt('limit', 50)));
			$sql = 'SELECT Id FROM ' . Model::documentsTable() . " WHERE Id > %i AND document_deleted != '1'";
			$args = array($after);
			if ($rubricId > 0) {
				$sql .= ' AND rubric_id = %i';
				$args[] = $rubricId;
			}

			$sql .= ' ORDER BY Id ASC LIMIT %i';
			$args[] = $limit + 1;
			array_unshift($args, $sql);
			$rows = call_user_func_array(array('DB', 'query'), $args)->getAll();
			$hasMore = count($rows) > $limit;
			if ($hasMore) {
				array_pop($rows);
			}

			$done = 0;
			$failed = array();
			$next = $after;
			foreach ($rows as $row) {
				$id = (int) $row['Id'];
				$next = $id;
				if (ContentCacheInvalidator::document($id, true)) {
					$done++;
				} else {
					$failed[] = array('id' => $id, 'error' => ContentCacheInvalidator::consumeError($id) ?: 'Неизвестная ошибка');
				}
			}

			return $this->success('Пакет JSON-снимков обработан', array('data' => array(
				'done' => $done,
				'failed' => $failed,
				'next_after' => $next,
				'has_more' => $hasMore,
			)));
		}

		public function aliases(array $params = array())
		{
			$id = isset($params['id']) ? (int) $params['id'] : 0;
			$item = Model::one($id);
			if (!$item) {
				return $this->error('Документ не найден', array(), 404);
			}

			return $this->success('', array('data' => array(
				'document' => array('id' => $id, 'title' => $item['document_title'], 'alias' => $item['document_alias']),
				'aliases' => Model::aliasHistory($id),
			)));
		}

		public function saveAlias(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			try {
				$id = Model::saveAliasHistory(
					isset($params['id']) ? (int) $params['id'] : 0,
					isset($params['alias']) ? (int) $params['alias'] : 0,
					Request::postAll(),
					Auth::id()
				);
			} catch (\Throwable $e) {
				return $this->error($e->getMessage(), array(), 422);
			}

			return $this->success('Редирект сохранён', array('data' => array('id' => $id)));
		}

		public function deleteAlias(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			if (!Model::deleteAliasHistory(
				isset($params['id']) ? (int) $params['id'] : 0,
				isset($params['alias']) ? (int) $params['alias'] : 0
			)) {
				return $this->error('Редирект не найден', array(), 404);
			}

			return $this->success('Редирект удалён');
		}

		public function remarks(array $params = array())
		{
			$id = isset($params['id']) ? (int) $params['id'] : 0;
			if (!Model::one($id)) {
				return $this->error('Документ не найден', array(), 404);
			}

			return $this->success('', array('data' => array('remarks' => Model::remarks($id))));
		}

		public function addRemark(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			try {
				$id = Model::addRemark(isset($params['id']) ? (int) $params['id'] : 0, Request::postAll(), Auth::id());
			} catch (\Throwable $e) {
				return $this->error($e->getMessage(), array(), 422);
			}

			return $this->success('Заметка добавлена', array('data' => array('id' => $id)));
		}

		public function deleteRemark(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			if (!Model::deleteRemark(
				isset($params['id']) ? (int) $params['id'] : 0,
				isset($params['remark']) ? (int) $params['remark'] : 0
			)) {
				return $this->error('Заметка не найдена', array(), 404);
			}

			return $this->success('Заметка удалена');
		}

		public function revision(array $params = array())
		{
			if (!Permission::check('manage_documents')) {
				return $this->error('Недостаточно прав', array(), 403);
			}

			$revision = Revisions::one(isset($params['revision']) ? (int) $params['revision'] : 0);
			if (!$revision) {
				return $this->error('Ревизия не найдена', array(), 404);
			}

			$document = Model::one((int) $revision['doc_id']);
			if (!$document) {
				return $this->error('Документ не найден', array(), 404);
			}

			$alias = trim(isset($document['document_alias']) ? (string) $document['document_alias'] : '', '/ ');
			$publicUrl = $alias === ''
				? (defined('ABS_PATH') ? (string) ABS_PATH : '/')
				: (defined('ABS_PATH') ? (string) ABS_PATH : '/') . $alias . (defined('URL_SUFF') ? (string) URL_SUFF : '');
			$publicUrl .= (strpos($publicUrl, '?') === false ? '?' : '&') . http_build_query(array(
				'revision_preview' => (int) $revision['id'],
				'preview_token' => DocumentRevisionPreview::issue((int) $revision['doc_id'], (int) $revision['id'], Auth::id()),
			));
			$revision['preview_url'] = $publicUrl;

			return $this->success('', array('data' => $revision));
		}

		public function restoreRevision(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			try {
				$selected = Request::postStr('selection_mode', '') === 'selected';
				$documentKeys = Request::post('document_keys', array());
				$fieldIds = Request::post('field_ids', array());
				$documentId = $selected
					? Revisions::restoreSelected(
						isset($params['revision']) ? (int) $params['revision'] : 0,
						is_array($documentKeys) ? $documentKeys : array(),
						is_array($fieldIds) ? $fieldIds : array(),
						Auth::id()
					)
					: Revisions::restore(isset($params['revision']) ? (int) $params['revision'] : 0, Auth::id());
			} catch (\Throwable $e) {
				return $this->error($e->getMessage(), array(), 422);
			}

			return $this->success($selected ? 'Выбранные данные восстановлены из ревизии' : 'Документ восстановлен из ревизии', array(
				'data' => array('id' => $documentId),
				'redirect' => $this->base() . '/documents/' . $documentId . '/edit',
			));
		}

		public function deleteRevision(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			$documentId = Revisions::delete(isset($params['revision']) ? (int) $params['revision'] : 0);
			if (!$documentId) {
				return $this->error('Ревизия не найдена', array(), 404);
			}

			return $this->success('Ревизия удалена', array('data' => array('id' => $documentId)));
		}

		public function deleteRevisions(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			$id = isset($params['id']) ? (int) $params['id'] : 0;
			if (!Model::one($id)) {
				return $this->error('Документ не найден', array(), 404);
			}

			$count = Revisions::deleteForDocument($id);
			return $this->success('Ревизии удалены', array('data' => array('id' => $id, 'count' => $count)));
		}

		public function restore(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			if (!Model::restore(isset($params['id']) ? (int) $params['id'] : 0)) {
				return $this->error('Документ не найден', array(), 404);
			}

			return $this->success('Документ восстановлен');
		}

		public function toggle(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			try {
				$status = Model::toggleStatus(isset($params['id']) ? (int) $params['id'] : 0);
			} catch (\Throwable $e) {
				return $this->error($e->getMessage(), array(), 422);
			}

			if ($status === false) {
				return $this->error('Документ не найден', array(), 404);
			}

			return $this->success($status ? 'Документ опубликован' : 'Документ снят с публикации', array('data' => array('status' => $status)));
		}

		public function copy(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			try {
				$id = Model::copy(isset($params['id']) ? (int) $params['id'] : 0, Auth::id());
			} catch (\Throwable $e) {
				return $this->error($e->getMessage(), array(), 422);
			}

			return $this->success('Копия документа создана', array(
				'data' => array('id' => $id),
				'redirect' => $this->base() . '/documents/' . $id . '/edit',
			));
		}

		public function saveCreationPreset(array $params = array())
		{
			if (($err = $this->guard()) !== null) { return $err; }
			$documentId = isset($params['id']) ? (int) $params['id'] : 0;
			try {
				$preset = (new DocumentCreationPresetRepository())->createFromDocument(
					$documentId,
					Request::postAll(),
					Auth::id()
				);
			} catch (\InvalidArgumentException $e) {
				return $this->error('Проверьте настройки пресета', array('title' => $e->getMessage()), 422);
			} catch (\Throwable $e) {
				return $this->error($e->getMessage(), array(), 422);
			}

			AuditLog::record('document.creation_preset_created', array(
				'actor_id' => Auth::id(),
				'target_type' => 'document_creation_preset',
				'target_id' => (int) $preset['id'],
				'meta' => array(
					'rubric_id' => (int) $preset['rubric_id'],
					'source_document_id' => $documentId,
					'fields_count' => (int) $preset['fields_count'],
				),
			));
			return $this->success('Пресет создания сохранён', array('data' => array('preset' => $preset)));
		}

		public function deleteCreationPreset(array $params = array())
		{
			if (($err = $this->guard()) !== null) { return $err; }
			$preset = (new DocumentCreationPresetRepository())->delete(
				isset($params['preset']) ? (int) $params['preset'] : 0
			);
			if (!$preset) { return $this->error('Пресет не найден', array(), 404); }

			AuditLog::record('document.creation_preset_deleted', array(
				'actor_id' => Auth::id(),
				'target_type' => 'document_creation_preset',
				'target_id' => (int) $preset['id'],
				'meta' => array('rubric_id' => (int) $preset['rubric_id'], 'title' => (string) $preset['title']),
			));
			return $this->success('Пресет удалён');
		}

		public function purge(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			try {
				if (!Model::purge(isset($params['id']) ? (int) $params['id'] : 0)) {
					return $this->error('Документ не найден', array(), 404);
				}
			} catch (\Throwable $e) {
				return $this->error($e->getMessage(), array(), 422);
			}

			return $this->success('Документ и связанные данные удалены окончательно');
		}

		protected function guard()
		{
			return $this->guardPermission('manage_documents');
		}

		protected function creationRubrics()
		{
			$rubrics = Model::rubrics();
			$presets = (new DocumentCreationPresetRepository())->all();
			$byRubric = array();
			foreach ($presets as $preset) {
				$byRubric[(int) $preset['rubric_id']][] = $preset;
			}

			foreach ($rubrics as &$rubric) {
				$rubric['creation_presets'] = isset($byRubric[(int) $rubric['Id']])
					? $byRubric[(int) $rubric['Id']]
					: array();
			}

			unset($rubric);
			return $rubrics;
		}

		protected function editConflict($documentId, $currentVersion)
		{
			return $this->json(array(
				'success' => false,
				'message' => 'Документ уже изменён в другой вкладке или другим пользователем.',
				'data' => array(
					'conflict' => true,
					'current_version' => max(1, (int) $currentVersion),
					'reload_url' => $this->base() . '/documents/' . (int) $documentId . '/edit',
				),
				'html' => new \stdClass(),
				'redirect' => null,
				'errors' => new \stdClass(),
			), 409);
		}

		protected function snapshotInfo($documentId)
		{
			$store = new DocumentSnapshotStore();
			$status = $store->status($documentId);
			$snapshot = $store->read($documentId);
			$document = Model::one($documentId);
			$current = false;
			if (is_array($snapshot) && $document) {
				try {
					$schema = (new RubricSchemaBuilder($store))->get((int) $document['rubric_id']);
					$current = isset($snapshot['format'], $snapshot['schema_version'], $snapshot['rubric_schema_version'], $snapshot['template_set_version'])
						&& (string) $snapshot['format'] === DocumentSnapshotStore::FORMAT
						&& (int) $snapshot['schema_version'] === DocumentSnapshotStore::VERSION
						&& (string) $snapshot['rubric_schema_version'] === (string) $schema['version']
						&& (string) $snapshot['template_set_version'] === FieldTemplateManifest::version();
				} catch (\Throwable $e) {
					$current = false;
				}
			}

			$status['current'] = $current;
			$status['fields_count'] = is_array($snapshot) && isset($snapshot['fields']) ? count($snapshot['fields']) : 0;
			$status['generated_label'] = !empty($status['generated_at']) ? date('d.m.Y H:i:s', $status['generated_at']) : 'ещё не создан';
			$status['size_label'] = $this->bytes(isset($status['size']) ? (int) $status['size'] : 0);
			$status['path'] = str_replace(rtrim(BASEPATH, '/\\') . '/', '', (string) $status['path']);
			return $status;
		}

		protected function bytes($bytes)
		{
			if ($bytes < 1024) { return $bytes . ' Б'; }
			if ($bytes < 1048576) { return number_format($bytes / 1024, 1, ',', ' ') . ' КБ'; }
			return number_format($bytes / 1048576, 1, ',', ' ') . ' МБ';
		}

		protected function validate(array $input, $id)
		{
			$errors = array();
			$title = trim(isset($input['document_title']) ? (string) $input['document_title'] : '');
			$rubricId = isset($input['rubric_id']) ? (int) $input['rubric_id'] : 0;
			$alias = trim(isset($input['document_alias']) ? (string) $input['document_alias'] : '');
			$shortAlias = trim(isset($input['document_short_alias']) ? (string) $input['document_short_alias'] : '');

			if ($title === '') {
				$errors['document_title'] = 'Укажите название документа';
			}

			if ($rubricId <= 0 || !Model::rubric($rubricId)) {
				$errors['rubric_id'] = 'Выберите рубрику';
			}

			if ($alias !== '' && !preg_match('/^[a-zA-Z0-9_\\-\\/\\.]+$/', $alias)) {
				$errors['document_alias'] = 'Допустимы латиница, цифры, точка, дефис, подчёркивание и слэш';
			} elseif ($alias !== '' && ($conflict = Model::aliasConflict($alias, (int) $id))) {
				$errors['document_alias'] = 'URL уже занят: ' . (isset($conflict['label']) ? $conflict['label'] : 'другой раздел');
			}

			if ($shortAlias !== '' && !preg_match('/^[a-zA-Z0-9_\-]+$/', $shortAlias)) {
				$errors['document_short_alias'] = 'Допустимы латиница, цифры, дефис и подчёркивание';
			} elseif ($shortAlias !== '' && ($conflict = Model::aliasConflict($shortAlias, (int) $id))) {
				$errors['document_short_alias'] = 'URL уже занят: ' . (isset($conflict['label']) ? $conflict['label'] : 'другой раздел');
			}

			return $errors;
		}
	}
