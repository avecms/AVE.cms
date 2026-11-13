<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Settings/Controller.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Adminx\Settings;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Common\AdminAssets;
	use App\Common\Auth;
	use App\Common\Controller as BaseController;
	use App\Common\Permission;
	use App\Common\Navigation;
	use App\Common\AuditLog;
	use App\Common\Cache;
	use App\Common\Settings;
	use App\Adminx\Support\CodeEditor;
	use App\Adminx\Support\AdminLocale;
	use App\Adminx\Support\InterfaceSettings;
	use App\Adminx\Support\ModuleExtensions;
	use App\Adminx\Support\ReAuth;
	use App\Adminx\Dashboard\Widgets;
	use App\Frontend\Media\ThumbnailPresetScanner;
	use App\Helpers\Request;

	class Controller extends BaseController
	{
		public function index(array $params = array())
		{
			$this->redirect($this->base() . '/settings/main');
		}

		public function page(array $params = array())
		{
			$path = trim((string) parse_url(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '', PHP_URL_PATH), '/');
			$section = basename($path);
			$titles = array('main'=>'Основные настройки','interface'=>'Интерфейс','security'=>'Безопасность','constants'=>'Константы','paginations'=>'Шаблоны пагинации','maintenance'=>'Обслуживание','diagnostics'=>'Диагностика','files'=>'Системные файлы');
			if (!isset($titles[$section])) { return $this->renderStatus('@adminx/404.twig', array('title'=>'Раздел настроек не найден'), 404); }
			AdminAssets::addStyle($this->base() . '/modules/Settings/assets/settings.css', 50);
			AdminAssets::addScript($this->base() . '/modules/Settings/assets/settings.js', 50);
			if ($section === 'files') { CodeEditor::useCodeMirror('application/x-httpd-php'); }

			$data = array(
				'settings_page' => $section,
				'settings_title' => AdminLocale::translateMarkup($titles[$section]),
				'can_manage' => Permission::check('manage_settings'),
			);
			if ($section === 'main') {
				$data['groups'] = Schema::groupedFields();
			} elseif ($section === 'interface') {
				$data['navigation_layout'] = InterfaceSettings::navigationCatalog(Navigation::all());
				$data['dashboard_layout'] = Widgets::catalog(ModuleExtensions::dashboardDefinitions());
			} elseif ($section === 'security') {
				$data['reauth_enabled'] = (bool) Settings::get('adminx_reauth_enabled', false);
			} elseif ($section === 'constants') {
				$data['constant_groups'] = Constants::grouped();
				$data['constant_group_labels'] = Constants::groups();
				$data['constant_type_labels'] = Constants::typeLabels();
			} elseif ($section === 'paginations') {
				$data['paginations'] = Model::paginations();
			} elseif ($section === 'maintenance') {
				$data['cache_rows'] = Model::cacheRows();
				$data['maintenance_counts'] = Model::maintenanceCounts();
				$data['public_sessions'] = Model::publicSessionStatus();
			} elseif ($section === 'diagnostics') {
				$data['production_diagnostics'] = ProductionDiagnostics::run();
			} elseif ($section === 'files') {
				$data['system_files'] = Model::systemFiles();
			}

			return $this->render('@settings/index.twig', $data);
		}

		public function save(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			$input = Request::postAll();
			$previousAccessMode = (string) Settings::get('site_access_mode', 'public');
			$result = Model::saveMany($input);
			if (!$result['success']) {
				return $this->error('Проверьте поля формы', $result['errors']);
			}

			if (array_key_exists('site_access_mode', $input)) {
				$currentAccessMode = (string) Settings::get('site_access_mode', 'public');
				if ($currentAccessMode !== $previousAccessMode) {
					AuditLog::record('settings.site_access_changed', array(
						'actor_id' => Auth::id(),
						'target_type' => 'settings',
						'meta' => array('from' => $previousAccessMode, 'to' => $currentAccessMode),
					));
				}
			}

			return $this->success('Настройки сохранены', array(
				'data' => array('saved' => $result['saved']),
			));
		}

		public function saveInterface(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			$navigation = json_decode(Request::postStr('navigation', '[]'), true);
			$dashboard = json_decode(Request::postStr('dashboard', '[]'), true);
			if (!is_array($navigation) || !is_array($dashboard)) {
				return $this->error('Некорректные данные интерфейса');
			}

			$navigationAvailable = InterfaceSettings::navigationCatalog(Navigation::all());
			$dashboardAvailable = Widgets::catalog(ModuleExtensions::dashboardDefinitions());
			InterfaceSettings::saveNavigation($navigation, $navigationAvailable);
			InterfaceSettings::saveDashboard($dashboard, $dashboardAvailable);
			AuditLog::record('settings.interface_updated', array(
				'actor_id' => Auth::id(),
				'target_type' => 'settings',
				'meta' => array('navigation' => count($navigation), 'dashboard' => count($dashboard)),
			));

			return $this->success('Интерфейс обновлен', array('reload' => true));
		}

		public function saveSecurity(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			$enabled = Request::postStr('adminx_reauth_enabled', '0') === '1';
			Settings::set('adminx_reauth_enabled', $enabled, 'bool');
			if (!$enabled) {
				ReAuth::forget();
			}

			AuditLog::record('settings.security_updated', array(
				'actor_id' => Auth::id(),
				'target_type' => 'settings',
				'meta' => array('adminx_reauth_enabled' => $enabled),
			));

			return $this->success('Настройки безопасности сохранены', array('reload' => true));
		}

		public function saveNotificationPreference(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			try {
				$preference = ModuleExtensions::setNotificationPreference(
					Request::postStr('module_code'),
					Request::postStr('key'),
					Request::postBool('enabled')
				);
			} catch (\InvalidArgumentException $e) {
				return $this->error($e->getMessage(), array(), 404);
			}

			Cache::forget('adminx.notifications.' . (int) Auth::id() . '.' . md5((string) ADMINX_BASE));
			AuditLog::record('settings.notification_preference_updated', array(
				'actor_id' => Auth::id(),
				'target_type' => 'settings',
				'target_id' => $preference['module_code'] . ':' . $preference['key'],
				'meta' => array('enabled' => !empty($preference['enabled'])),
			));

			return $this->success('Настройка уведомлений сохранена', array(
				'data' => array('preference' => $preference),
			));
		}

		public function pagination(array $params = array())
		{
			$item = Model::pagination(isset($params['id']) ? (int) $params['id'] : 0);
			if (!$item) {
				return $this->error('Шаблон пагинации не найден', array(), 404);
			}

			return $this->success('', array('data' => $item));
		}

		public function savePagination(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			$id = isset($params['id']) ? (int) $params['id'] : 0;
			$savedId = Model::savePagination($id, Request::postAll());

			return $this->success('Шаблон пагинации сохранён', array(
				'data' => array('id' => $savedId),
				'redirect' => $this->base() . '/settings/paginations',
			));
		}

		public function deletePagination(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			if (!Model::deletePagination(isset($params['id']) ? (int) $params['id'] : 0)) {
				return $this->error('Базовый шаблон удалить нельзя', array(), 422);
			}

			return $this->success('Шаблон пагинации удалён');
		}

		public function clearCache(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			$result = Model::clearCacheSource(isset($params['source']) ? $params['source'] : '');
			if ($result === false) {
				return $this->error('Неизвестный источник кеша', array(), 404);
			}

			return $this->success('Кеш очищен', array('data' => $result));
		}

		public function clearMaintenance(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			try {
				$result = Model::clearMaintenance(isset($params['target']) ? $params['target'] : '', Request::postInt('cutoff', 0));
			} catch (\Throwable $e) {
				return $this->error('Не удалось очистить данные: ' . $e->getMessage(), array(), 422);
			}

			if ($result === false) {
				return $this->error('Неизвестная операция обслуживания', array(), 404);
			}

			if ($result['target'] === 'sessions' && $result['count'] > 0) {
				AuditLog::record('settings.sessions_cleared', array('actor_id' => Auth::id(), 'target_type' => 'public_sessions', 'meta' => array('deleted' => $result['count'], 'cutoff' => $result['cutoff'])));
			}

			return $this->success('Данные очищены', array('data' => $result));
		}

		public function systemFile(array $params = array())
		{
			$file = Model::systemFile(isset($params['code']) ? $params['code'] : '');
			if (!$file) {
				return $this->error('Системный файл не найден', array(), 404);
			}

			return $this->success('', array('data' => $file));
		}

		public function saveSystemFile(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			try {
				$file = Model::saveSystemFile(
					isset($params['code']) ? $params['code'] : '',
					Request::postStr('content', '')
				);
			} catch (\Throwable $e) {
				return $this->error($e->getMessage(), array(), 422);
			}

			return $this->success('Системный файл сохранён', array('data' => $file));
		}

		public function constant(array $params = array())
		{
			$item = Constants::one(isset($params['name']) ? $params['name'] : '');
			if (!$item) {
				return $this->error('Константа не найдена', array(), 404);
			}

			return $this->success('', array('data' => $item));
		}

		public function saveConstant(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			try {
				$name = Constants::save(Request::postAll());
			} catch (\Throwable $e) {
				return $this->error($e->getMessage());
			}

			return $this->success('Константа сохранена', array(
				'data' => array('name' => $name),
				'redirect' => $this->base() . '/settings/constants',
			));
		}

		public function scanThumbnailSizes(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			try {
				$result = ThumbnailPresetScanner::scan();
			} catch (\Throwable $e) {
				return $this->error('Не удалось собрать размеры: ' . $e->getMessage(), array(), 422);
			}

			return $this->success('Размеры собраны и подставлены в поле', array(
				'data' => $result,
			));
		}

		public function deleteConstant(array $params = array())
		{
			if (($err = $this->guard()) !== null) {
				return $err;
			}

			try {
				Constants::delete(isset($params['name']) ? $params['name'] : '');
			} catch (\Throwable $e) {
				return $this->error($e->getMessage());
			}

			return $this->success('Константа удалена');
		}

		protected function guard()
		{
			return $this->guardPermission('manage_settings');
		}
	}
