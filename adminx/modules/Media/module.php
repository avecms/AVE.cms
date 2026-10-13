<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         adminx/modules/Media/module.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	use App\Adminx\Media\GlobalSearchProvider;

	return array(
		'code' => 'media',
		'name' => 'Медиа',
		'version' => '0.6.5',

		'permissions' => array(
			'key' => 'media',
			'items' => array(
				array(
					'code' => 'view_media',
					'group_code' => 'navigation',
					'name' => 'Медиа: просмотр',
					'description' => 'Просмотр файлового менеджера и папки /uploads.',
					'sort_order' => 10,
				),
				array(
					'code' => 'manage_media',
					'group_code' => 'content',
					'name' => 'Медиа: управление файлами',
					'description' => 'Загрузка, переименование, удаление файлов и папок, редактор изображений.',
					'sort_order' => 20,
				),
			),
			'icon' => 'ti ti-photo',
			'priority' => 30,
		),

		'navigation' => array(
			array(
				'code' => 'media',
				'label' => 'Медиа',
				'url' => '/media',
				'icon' => 'ti ti-photo',
				'permission' => 'view_media',
				'group' => 'Контент',
				'sort_order' => 30,
				'match' => array(
					'/media',
				),
			),
		),

		'migrations' => array(
			array('id' => '001_image_presets', 'file' => 'migrations/001_image_presets.sql'),
			array('id' => '002_media_search_index', 'file' => 'migrations/002_media_search_index.sql'),
		),

		'routes' => array(
			array('GET', '/media', array(\App\Adminx\Media\Controller::class, 'index')),
			array('POST', '/media/saved-views', array(\App\Adminx\Media\Controller::class, 'saveSavedView')),
			array('POST', '/media/saved-views/{id}/delete', array(\App\Adminx\Media\Controller::class, 'deleteSavedView')),
			array('GET', '/media/audit', array(\App\Adminx\Media\Controller::class, 'audit'), array('permission' => 'view_media')),
			array('GET', '/media/trash', array(\App\Adminx\Media\Controller::class, 'trash'), array('permission' => 'manage_media')),
			array('POST', '/media/audit/run', array(\App\Adminx\Media\Controller::class, 'runAudit'), array('permission' => 'manage_media')),
			array('GET', '/media/presets', array(\App\Adminx\Media\Controller::class, 'presets')),
			array('POST', '/media/presets', array(\App\Adminx\Media\Controller::class, 'storePreset'), array('permission' => 'manage_media')),
			array('POST', '/media/presets/{id}', array(\App\Adminx\Media\Controller::class, 'updatePreset'), array('permission' => 'manage_media')),
			array('POST', '/media/presets/{id}/delete', array(\App\Adminx\Media\Controller::class, 'deletePreset'), array('permission' => 'manage_media')),
			array('GET', '/media/picker', array(\App\Adminx\Media\Controller::class, 'picker')),
			array('GET', '/media/folder-files', array(\App\Adminx\Media\Controller::class, 'folderFiles')),
			array('GET', '/media/file', array(\App\Adminx\Media\Controller::class, 'file')),
			array('POST', '/media/upload', array(\App\Adminx\Media\Controller::class, 'upload')),
			array('POST', '/media/folders', array(\App\Adminx\Media\Controller::class, 'createFolder')),
			array('POST', '/media/rename', array(\App\Adminx\Media\Controller::class, 'rename')),
			array('POST', '/media/delete', array(\App\Adminx\Media\Controller::class, 'delete')),
			array('POST', '/media/delete-check', array(\App\Adminx\Media\Controller::class, 'deleteCheck')),
			array('POST', '/media/empty-folder', array(\App\Adminx\Media\Controller::class, 'emptyFolder'), array('permission' => 'manage_media')),
			array('POST', '/media/trash/{token}/restore', array(\App\Adminx\Media\Controller::class, 'restoreTrash'), array('permission' => 'manage_media')),
			array('POST', '/media/trash/{token}/purge', array(\App\Adminx\Media\Controller::class, 'purgeTrash'), array('permission' => 'manage_media')),
			array('POST', '/media/clear-thumbnails', array(\App\Adminx\Media\Controller::class, 'clearThumbnails')),
			array('POST', '/media/transform', array(\App\Adminx\Media\Controller::class, 'transform')),
			array('POST', '/media/preview', array(\App\Adminx\Media\Controller::class, 'preview')),
			array('POST', '/media/preview-clear', array(\App\Adminx\Media\Controller::class, 'previewClear')),
			array('POST', '/media/convert-webp', array(\App\Adminx\Media\Controller::class, 'convertWebp')),
		),

		'view_globals' => array(
			'module_code' => 'media',
		),
		'admin_extension' => array(
			'url' => '/media',
			'feature' => 'Файлы, изображения и превью',
			'icon' => 'ti ti-photo',
			'search' => array('code' => 'media', 'provider' => array(GlobalSearchProvider::class, 'search'), 'permission' => 'view_media', 'priority' => 35, 'limit' => 8),
		),
	);
