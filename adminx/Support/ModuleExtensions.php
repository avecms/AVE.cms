<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/Support/ModuleExtensions.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Adminx\Support;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Common\ModuleManager;
	use App\Common\ModuleSettings;
	use App\Common\Navigation;
	use App\Common\Permission;

	/**
	 * UI-реестр adminx-модулей.
	 *
	 * Ядро хранит neutral descriptor, а этот слой подключает только adminx-вклады:
	 * меню, действия шапки, dashboard-виджеты и уведомления.
	 */
	class ModuleExtensions
	{
		protected static $booted = false;
		protected static $extensions = array();

		public static function boot()
		{
			if (self::$booted) {
				return;
			}

			self::$booted = true;

			foreach (ModuleManager::all() as $module) {
				if (empty($module['admin_extension']) || !is_array($module['admin_extension'])) {
					continue;
				}

				self::$extensions[$module['code']] = array(
					'module' => $module,
					'config' => $module['admin_extension'],
				);
				if (!empty($module['installed']) && !empty($module['enabled'])) {
					self::registerMenu($module['admin_extension']);
					self::registerSearch($module['code'], $module['admin_extension']);
				}
			}

		}

		public static function headerActions()
		{
			return self::contributions('header');
		}

		/** Small, permission-scoped work queues; providers must not mutate their records. */
		public static function attentionQueues()
		{
			return self::contributions('attention');
		}

		public static function dashboardWidgets(array $visibleCodes = null)
		{
			return self::contributions('dashboard', $visibleCodes);
		}

		/** Dashboard descriptors without running providers; used by interface settings. */
		public static function dashboardDefinitions()
		{
			self::boot();
			$out = array();
			foreach (self::$extensions as $code => $extension) {
				$module = self::currentModule($code, $extension['module']);
				if (empty($module['installed']) || empty($module['enabled'])) {
					continue;
				}

				$config = $extension['config'];
				if (empty($config['dashboard'])) {
					continue;
				}

				foreach (self::normalizeItems($config['dashboard']) as $index => $item) {
					if (!is_array($item)) {
						continue;
					}

					$out[] = array(
						'layout_code' => self::contributionCode($code, 'dashboard', $item, $index),
						'module_code' => $code,
						'label' => AdminLocale::translateMarkup(isset($item['label']) ? (string) $item['label'] : (isset($module['name']) ? (string) $module['name'] : $code)),
						'description' => AdminLocale::translateMarkup(isset($item['description']) ? (string) $item['description'] : (isset($module['description']) ? (string) $module['description'] : '')),
						'icon' => isset($item['icon']) ? (string) $item['icon'] : (isset($config['icon']) ? (string) $config['icon'] : 'ti ti-layout-dashboard'),
						'sort_order' => 100 + (isset($item['sort_order']) ? (int) $item['sort_order'] : 100),
						'default_visible' => self::presentationEnabled($code, 'dashboard'),
					);
				}
			}

			return $out;
		}

		public static function notifications($base, array $summary)
		{
			foreach (self::contributions('notifications') as $contribution) {
				$moduleCode = isset($contribution['module_code']) ? (string) $contribution['module_code'] : '';
				$data = isset($contribution['data']) ? $contribution['data'] : array();
				$items = isset($data['items']) && is_array($data['items']) ? $data['items'] : (self::isList($data) ? $data : array());
				foreach ($items as $item) {
					if (!is_array($item) || empty($item['title'])) {
						continue;
					}

					$key = isset($item['key']) ? trim((string) $item['key']) : '';
					if ($key !== '' && !self::notificationEnabled($moduleCode, $key)) {
						continue;
					}

					$count = isset($item['count']) ? max(0, (int) $item['count']) : 1;
					if ($count < 1) {
						continue;
					}

					$url = isset($item['url']) ? (string) $item['url'] : '';
					if ($url !== '' && $url[0] === '/') {
						$url = rtrim((string) $base, '/') . $url;
					}

					$summary['items'][] = array_merge(array(
						'icon' => 'ti ti-bell',
						'bg' => '#e8f1ff',
						'fg' => '#2563eb',
						'text' => '',
						'url' => $url,
						'count' => $count,
					), $item, array(
						'url' => $url,
						'count' => $count,
						'text' => AdminLocale::translateMarkup(isset($item['text']) ? (string) $item['text'] : ''),
						'title' => AdminLocale::translateMarkup(isset($item['title']) ? (string) $item['title'] : ''),
					));
					$summary['total'] += $count;
					if ($key !== '') {
						$summary[$key] = isset($summary[$key]) ? (int) $summary[$key] + $count : $count;
					}
				}
			}

			return $summary;
		}

		/** Настраиваемые элементы колокольчика без запуска их провайдеров. */
		public static function notificationPreferences()
		{
			self::boot();
			$out = array();
			foreach (self::$extensions as $code => $extension) {
				$module = self::currentModule($code, $extension['module']);
				if (empty($module['installed']) || empty($module['enabled'])) {
					continue;
				}

				$config = $extension['config'];
				if (empty($config['notifications'])) {
					continue;
				}

				foreach (self::normalizeItems($config['notifications']) as $item) {
					if (!is_array($item) || !self::allowed($item) || empty($item['preferences']) || !is_array($item['preferences'])) {
						continue;
					}

					foreach ($item['preferences'] as $preference) {
						if (!is_array($preference)) {
							continue;
						}

						$key = isset($preference['key']) ? trim((string) $preference['key']) : '';
						$label = isset($preference['label']) ? trim((string) $preference['label']) : '';
						if (!preg_match('/^[a-z][a-z0-9_]{0,63}$/', $key) || $label === '') {
							continue;
						}

						$default = !empty($preference['default']);
						$out[] = array(
							'module_code' => (string) $code,
							'key' => $key,
							'label' => AdminLocale::translateMarkup($label),
							'description' => AdminLocale::translateMarkup(isset($preference['description']) ? (string) $preference['description'] : ''),
							'enabled' => (bool) ModuleSettings::get(self::notificationSettingKey($key), $default, $code),
							'default' => $default,
							'sort_order' => isset($preference['sort_order']) ? (int) $preference['sort_order'] : 100,
						);
					}
				}
			}

			usort($out, function ($left, $right) {
				if ($left['sort_order'] === $right['sort_order']) {
					return strcmp($left['label'], $right['label']);
				}

				return $left['sort_order'] <=> $right['sort_order'];
			});
			return $out;
		}

		/** Сохранить один объявленный модулем переключатель уведомлений. */
		public static function setNotificationPreference($moduleCode, $key, $enabled)
		{
			$moduleCode = strtolower(trim((string) $moduleCode));
			$key = strtolower(trim((string) $key));
			foreach (self::notificationPreferences() as $preference) {
				if ($preference['module_code'] !== $moduleCode || $preference['key'] !== $key) {
					continue;
				}

				ModuleSettings::set(self::notificationSettingKey($key), (bool) $enabled, $moduleCode, 'bool');
				$preference['enabled'] = (bool) $enabled;
				return $preference;
			}

			throw new \InvalidArgumentException('Настройка уведомления не найдена');
		}

		public static function catalog()
		{
			self::boot();
			$items = array();
			foreach (self::$extensions as $code => $extension) {
				$module = self::currentModule($code, $extension['module']);
				$config = $extension['config'];
				$items[] = array(
					'code' => $code,
					'name' => AdminLocale::translateMarkup(isset($module['name']) ? $module['name'] : $code),
					'description' => AdminLocale::translateMarkup(isset($module['description']) ? $module['description'] : ''),
					'author' => isset($module['author']) ? $module['author'] : '',
					'admin_url' => isset($config['url']) ? (string) $config['url'] : '',
					'feature' => isset($config['feature']) ? (string) $config['feature'] : '',
					'icon' => isset($config['icon']) ? (string) $config['icon'] : 'ti ti-puzzle',
					'module' => $module,
				);
			}

			return $items;
		}

		/**
		 * Missing hard and recommended dependencies for the module owning a route.
		 */
		public static function dependencyNotices($path)
		{
			$module = self::moduleForPath($path);
			if (!$module) {
				return array();
			}

			$descriptor = ModuleManager::descriptor($module['code']);
			$required = is_array($descriptor) && isset($descriptor['requires'])
				? self::dependencyDefinitions($descriptor['requires'])
				: array();
			$recommended = is_array($descriptor) && isset($descriptor['recommends'])
				? self::dependencyDefinitions($descriptor['recommends'])
				: array();
			$requiredCodes = array();
			$recommendedReasons = array();
			foreach ($required as $definition) {
				$requiredCodes[] = $definition['code'];
			}

			foreach ($recommended as $definition) {
				$recommendedReasons[$definition['code']] = $definition['reason'];
			}

			$notices = array();
			foreach (array(
				'required' => $requiredCodes,
				'recommended' => array_keys($recommendedReasons),
			) as $level => $codes) {
				foreach ($codes as $code) {
					$dependency = ModuleManager::get($code);
					if ($dependency && !empty($dependency['installed']) && !empty($dependency['enabled'])) {
						continue;
					}

					$name = $dependency && !empty($dependency['name']) ? (string) $dependency['name'] : (string) $code;
					$state = !$dependency ? 'missing' : (empty($dependency['installed']) ? 'available' : 'disabled');
					$reason = $level === 'recommended' && isset($recommendedReasons[$code])
						? $recommendedReasons[$code]
						: '';
					$notices[] = array(
						'level' => $level,
						'code' => (string) $code,
						'name' => $name,
						'state' => $state,
						'reason' => $reason,
						'action' => $state === 'disabled' ? 'Включить модуль' : ($state === 'missing' ? 'Найти в каталоге' : 'Установить модуль'),
						'url' => '/modules?' . ($state === 'missing' ? 'tab=catalog&' : '') . 'focus=' . rawurlencode((string) $code),
					);
				}
			}

			return $notices;
		}

		protected static function contributions($type, array $visibleCodes = null)
		{
			self::boot();
			$out = array();
			$visible = $visibleCodes === null ? null : array_fill_keys($visibleCodes, true);
			foreach (self::$extensions as $code => $extension) {
				$module = self::currentModule($code, $extension['module']);
				if (empty($module['installed']) || empty($module['enabled'])) {
					continue;
				}

				$config = $extension['config'];
				if (empty($config[$type])) {
					continue;
				}

				if (($type === 'header' || $type === 'dashboard') && !self::presentationEnabled($code, $type)) {
					continue;
				}

				foreach (self::normalizeItems($config[$type]) as $index => $item) {
					if (!is_array($item) || !self::allowed($item)) {
						continue;
					}

					$layoutCode = self::contributionCode($code, $type, $item, $index);
					if ($visible !== null && !isset($visible[$layoutCode])) {
						continue;
					}

					try {
						$data = isset($item['provider']) && is_callable($item['provider'])
							? call_user_func($item['provider'])
							: (isset($item['data']) ? $item['data'] : array());
					} catch (\Throwable $e) {
						$data = array();
					}

					$item['module_code'] = $code;
					$item['layout_code'] = $layoutCode;
					$item['label'] = AdminLocale::translateMarkup(isset($item['label']) ? (string) $item['label'] : (isset($module['name']) ? (string) $module['name'] : $code));
					if (isset($item['description'])) {
						$item['description'] = AdminLocale::translateMarkup((string) $item['description']);
					}

					$item['icon'] = isset($item['icon']) ? (string) $item['icon'] : (isset($config['icon']) ? (string) $config['icon'] : 'ti ti-layout-dashboard');
					$item['data'] = is_array($data) ? $data : array();
					$item['sort_order'] = isset($item['sort_order']) ? (int) $item['sort_order'] : 100;
					$out[] = $item;
				}
			}

			usort($out, function ($a, $b) {
				return $a['sort_order'] <=> $b['sort_order'];
			});
			return $out;
		}

		protected static function contributionCode($moduleCode, $type, array $item, $index)
		{
			$suffix = isset($item['code']) && trim((string) $item['code']) !== ''
				? trim((string) $item['code'])
				: (string) ((int) $index + 1);
			return 'module.' . (string) $moduleCode . '.' . (string) $type . '.' . $suffix;
		}

		protected static function registerMenu(array $config)
		{
			if (empty($config['menu'])) {
				return;
			}

			$items = is_callable($config['menu']) ? call_user_func($config['menu']) : $config['menu'];
			if (is_array($items) && !self::isList($items)) {
				$items = array($items);
			}

			if (is_array($items)) {
				Navigation::addMany($items);
			}
		}

		protected static function registerSearch($moduleCode, array $config)
		{
			if (empty($config['search'])) {
				return;
			}

			foreach (self::normalizeItems($config['search']) as $index => $definition) {
				if (!is_array($definition)) {
					continue;
				}

				$code = isset($definition['code']) && trim((string) $definition['code']) !== ''
					? (string) $definition['code']
					: (string) ((int) $index + 1);
				GlobalSearchRegistry::register($moduleCode . '.' . $code, $definition);
			}
		}

		protected static function normalizeItems($items)
		{
			if (!is_array($items)) {
				return array();
			}

			return self::isList($items) ? $items : array($items);
		}

		protected static function isList(array $items)
		{
			return empty($items) || array_keys($items) === range(0, count($items) - 1);
		}

		protected static function allowed(array $item)
		{
			$permission = isset($item['permission']) ? (string) $item['permission'] : '';
			if ($permission !== '' && !Permission::check($permission)) {
				return false;
			}

			return empty($item['visible']) || !is_callable($item['visible']) || (bool) call_user_func($item['visible']);
		}

		protected static function currentModule($code, array $fallback)
		{
			foreach (ModuleManager::all() as $module) {
				if ($module['code'] === (string) $code) {
					return $module;
				}
			}

			return $fallback;
		}

		protected static function moduleForPath($path)
		{
			$path = '/' . trim((string) $path, '/');
			$match = null;
			$matchLength = -1;
			foreach (ModuleManager::all() as $module) {
				$extension = isset($module['admin_extension']) && is_array($module['admin_extension'])
					? $module['admin_extension']
					: array();
				$url = isset($extension['url']) ? '/' . trim((string) $extension['url'], '/') : '';
				if ($url === '' || ($path !== $url && strpos($path, $url . '/') !== 0) || strlen($url) <= $matchLength) {
					continue;
				}

				$match = $module;
				$matchLength = strlen($url);
			}

			return $match;
		}

		protected static function dependencyDefinitions($dependencies)
		{
			$result = array();
			foreach (is_array($dependencies) ? $dependencies : array() as $dependency) {
				$code = is_array($dependency) && isset($dependency['code'])
					? strtolower(trim((string) $dependency['code']))
					: strtolower(trim((string) $dependency));
				if ($code === '') {
					continue;
				}

				$result[] = array(
					'code' => $code,
					'reason' => is_array($dependency) && isset($dependency['reason'])
						? trim((string) $dependency['reason'])
						: '',
				);
			}

			return $result;
		}

		/** Управляемая администратором видимость UI-вклада модуля. */
		protected static function presentationEnabled($code, $type)
		{
			return (bool) ModuleSettings::get('adminx_' . $type . '_enabled', true, $code);
		}

		protected static function notificationEnabled($moduleCode, $key)
		{
			foreach (self::notificationPreferences() as $preference) {
				if ($preference['module_code'] === (string) $moduleCode && $preference['key'] === (string) $key) {
					return !empty($preference['enabled']);
				}
			}

			return true;
		}

		protected static function notificationSettingKey($key)
		{
			return 'adminx_notification_' . preg_replace('/[^a-z0-9_]+/', '_', strtolower((string) $key)) . '_enabled';
		}
	}
