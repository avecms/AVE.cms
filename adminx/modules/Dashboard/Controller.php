<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Dashboard/Controller.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Adminx\Dashboard;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Common\AdminAssets;
	use App\Common\Controller as BaseController;
	use App\Common\ModuleManager;
	use App\Common\SystemTables;
	use App\Content\ContentTables;
	use App\Adminx\Support\ModuleExtensions;
	use App\Adminx\Support\AdminLocale;
	use App\Adminx\Support\SystemHealth;
	use DB;

	/**
	 * Главная страница ядра. Бизнес-показатели добавляют владельцы модулей.
	 */
	class Controller extends BaseController
	{
		public function attention(array $params = array())
		{
			if (!\App\Common\Auth::user() || !\App\Common\Permission::checkAcp('admin_panel')) {
				return $this->error('Недостаточно прав', array(), 403);
			}

			\App\Helpers\Request::setHeader('Cache-Control: private, no-store', true);
			return $this->success('', array('data' => Attention::summary($this->base())));
		}

		/** GET / и /dashboard */
		public function index(array $params = [])
		{
			AdminAssets::addStyle($this->base() . '/modules/Dashboard/assets/dashboard.css', 50);
			AdminAssets::addScript($this->base() . '/modules/Dashboard/assets/dashboard.js', 50);

			$catalog = Widgets::catalog(ModuleExtensions::dashboardDefinitions());
			$visibleCodes = array();
			foreach ($catalog as $widget) {
				if (!empty($widget['layout_visible'])) {
					$visibleCodes[] = (string) $widget['layout_code'];
				}
			}

			$stats = in_array('core.system_kpis', $visibleCodes, true) ? $this->stats() : array();
			$recentDocuments = in_array('core.recent_documents', $visibleCodes, true) ? $this->recentDocuments() : array();

			return $this->render('@dashboard/index.twig', [
				'greeting'         => $this->greeting(),
				'dashboard_widgets' => Widgets::compose($stats, $recentDocuments, ModuleExtensions::dashboardWidgets($visibleCodes)),
			]);
		}

		/** Приветствие по времени суток. */
		protected function greeting()
		{
			$h = (int) date('G');
			if ($h < 6) {
				return AdminLocale::text('dashboard_greeting_night', 'Доброй ночи');
			}

			if ($h < 12) {
				return AdminLocale::text('dashboard_greeting_morning', 'Доброе утро');
			}

			if ($h < 18) {
				return AdminLocale::text('dashboard_greeting_day', 'Добрый день');
			}

			return AdminLocale::text('dashboard_greeting_evening', 'Добрый вечер');
		}

		/** Сводка для KPI-карточек с динамикой за 30 дней (безопасно к отсутствию таблиц). */
		protected function stats()
		{
			$notFound = SystemHealth::notFoundStats();
			$notFoundModule = ModuleManager::get('notfound');
			return [
				'users'         => $this->countTable(SystemTables::table('users'), 'is_active = 1'),
				'documents'     => $this->countTable($this->table('documents'), "document_status = '1' AND document_deleted != '1'"),
				'not_found'     => isset($notFound['unresolved']) ? (int) $notFound['unresolved'] : 0,
				'not_found_url' => $notFoundModule && !empty($notFoundModule['enabled'])
					? '/notfound?unresolved=1'
					: '/events?source=404',
				'sql_errors'    => SystemHealth::sqlErrorCount(),
			];
		}

		protected function recentDocuments()
		{
			try {
				return DB::query('SELECT d.Id,d.document_title,d.document_alias,d.document_changed,d.document_status,'
					. 'r.rubric_title FROM ' . $this->table('documents') . ' d LEFT JOIN ' . $this->table('rubrics')
					. " r ON r.Id=d.rubric_id WHERE d.document_deleted != '1'"
					. ' ORDER BY d.document_changed DESC,d.Id DESC LIMIT 8')->getAll() ?: [];
			} catch (\Throwable $e) {
				return [];
			}
		}

		protected function countTable($table, $where = '')
		{
			try {
				$sql = 'SELECT COUNT(*) FROM ' . $table;
				if ($where !== '') {
					$sql .= ' WHERE ' . $where;
				}

				return (int) \DB::query($sql)->getValue();
			} catch (\Throwable $e) {
				return null;
			}
		}

		protected function table($suffix)
		{
			return ContentTables::table($suffix);
		}
	}
