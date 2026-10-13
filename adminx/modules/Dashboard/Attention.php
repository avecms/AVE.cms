<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Dashboard/Attention.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Adminx\Dashboard;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Adminx\Support\AdminLocale;
	use App\Adminx\Support\ModuleExtensions;

	class Attention
	{
		protected static $summary;

		public static function summary($base)
		{
			if (self::$summary !== null) { return self::$summary; }
			$items = array();
			foreach (ModuleExtensions::attentionQueues() as $queue) {
				$data = isset($queue['data']) ? $queue['data'] : array();
				$item = self::normalize($data, $queue['layout_code'], $base, time());
				if ($item !== null) { $items[] = $item; }
			}

			return self::$summary = array('items' => $items, 'updated_at' => time());
		}

		public static function normalize(array $data, $key, $base, $now)
		{
			$url = isset($data['url']) ? (string) $data['url'] : '';
			if (empty($data['title']) || !preg_match('~^/(?!/)[a-zA-Z0-9/_?=&.%-]+$~D', $url)) { return null; }
			$available = !isset($data['available']) || $data['available'];
			$count = max(0, (int) (isset($data['count']) ? $data['count'] : 0));
			$oldest = max(0, (int) (isset($data['oldest_at']) ? $data['oldest_at'] : 0));
			$wait = '';
			if (!$available) {
				$wait = AdminLocale::text('attention_unavailable', 'Не удалось обновить');
			} elseif ($count === 0) {
				$wait = AdminLocale::text('attention_empty', 'Новых нет');
			} elseif ($oldest > 0) {
				$minutes = max(1, (int) floor(($now - $oldest) / 60));
				$wait = sprintf(AdminLocale::text('attention_wait', 'Самое старое ждёт %s'),
					$minutes < 60 ? $minutes . ' ' . AdminLocale::text('attention_minutes', 'мин')
					: ($minutes < 1440 ? (int) floor($minutes / 60) . ' ' . AdminLocale::text('attention_hours', 'ч')
						: (int) floor($minutes / 1440) . ' ' . AdminLocale::text('attention_days', 'дн')));
			} else {
				$wait = AdminLocale::text('attention_waiting', 'Ожидают обработки');
			}

			return array('key' => (string) $key, 'title' => AdminLocale::translateMarkup($data['title']),
				'icon' => isset($data['icon']) && preg_match('/^ti ti-[a-z0-9-]+$/D', $data['icon']) ? $data['icon'] : 'ti ti-bell',
				'tone' => isset($data['tone']) && in_array($data['tone'], array('blue', 'green'), true) ? $data['tone'] : 'amber',
				'count' => $count, 'available' => (bool) $available, 'oldest_at' => $oldest,
				'latest_id' => max(0, (int) (isset($data['latest_id']) ? $data['latest_id'] : 0)),
				'url' => rtrim((string) $base, '/') . $url, 'wait' => $wait);
		}
	}
