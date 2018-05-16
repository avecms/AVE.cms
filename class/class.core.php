<?php

	/**
	 * AVE.cms
	 *
	 * Класс, предназначенный для сбора и формирования общей страницы перед показом в Публичной части.
	 * Фактически, данный класс является ядром системы, на который ложится сборка страницы из отдельных компонентов,
	 * замена системных тегов соответствующими функциями, а также разбор url параметров и поиск документов по url.
	 *
	 * @package AVE.cms
	 * @version 3.x
	 * @filesource
	 * @copyright © 2007-2014 AVE.cms, http://www.ave-cms.ru
	 *
	 */

	class AVE_Core
	{
		/**
		 * Текущий документ
		 *
		 * @public object
		 */
		public $curentdoc = null;

		/**
		 * Установленные модули
		 *
		 * @public array
		 */
		public $install_modules = null;

		/**
		 * Сообщение об ошибке, если документ не найден
		 *
		 * @public string
		 */
		public $_doc_not_found = '<h1>HTTP Error 404: Page not found</h1>';

		/**
		 * Сообщение об ошибке, если для рубрики не найден шаблон
		 *
		 * @public string
		 */
		public $_rubric_template_empty = '<h1>Ошибка</h1><br />Не задан шаблон рубрики.';

		/**
		 * Сообщение об ошибке, если документ запрещен к показу
		 *
		 * @public string
		 */
		public $_doc_not_published = 'Запрашиваемый документ запрещен к публикации.';

		/**
		 * Сообщение об ошибке, если модуль не может быть загружен
		 *
		 * @public string
		 */
		public $_module_error = 'Запрашиваемый модуль не может быть загружен.';

		/**
		 * Сообщение об ошибке, если модуль, указанный в шаблоне, не установлен в системе
		 *
		 * @public string
		 */
		public $_module_not_found = 'Запрашиваемый модуль не найден.';


		/**
		 * Получение основных настроек сисблока
		 *
		 * @param string $param параметр настройки, если не указан - все параметры
		 * @return mixed
		 */

		function _sysBlock($id, $param = '')
		{
			global $AVE_DB;

			static $sys_block = null;

			if ($sys_block === null)
			{
				$sys_block = $AVE_DB->Query("
					SELECT
						*
					FROM
						" . PREFIX . "_sysblocks
					WHERE
						" . (is_numeric($id) ? 'id' : 'sysblock_alias') . " = '" . $id . "'
				")->FetchAssocArray();
			}

			if ($param == '')
				return $sys_block;

			return isset($sys_block[$param])
				? $sys_block[$param]
				: null;
		}


		function _getMainTemplate($rubric_id, $template_id)
		{
			global $AVE_DB;

			$return =  null;

			if (is_numeric($template_id))
			{
				$cache_file = BASE_DIR . '/templates/' . DEFAULT_THEME_FOLDER . '/include/templates/' . $template_id . '/template.inc';

				// Если включен DEV MODE, то отключаем кеширование
				if (defined('DEV_MODE') AND DEV_MODE)
					$cache_file = null;

				if (! is_dir(dirname($cache_file)))
					@mkdir(dirname($cache_file), 0766, true);

				// Если файл есть и он не пустой используем его
				if (file_exists($cache_file) && filesize($cache_file))
				{
					$return = file_get_contents($cache_file);
				}
				// Иначе лезем в БД и достаем шаблон
				else
					{
						$return = $AVE_DB->Query("
							SELECT
								template_text
							FROM
								" . PREFIX . "_templates
							WHERE
								Id = '" . $template_id . "'
							LIMIT 1
						")->GetCell();

						$return = stripslashes($return);

						// Сохраняем в файл
						if ($cache_file)
							file_put_contents($cache_file, $return);
					}
			}

			return $return;
		}


		/**
		 * Получение основных настроек сисблока
		 *
		 * @param string $param параметр настройки, если не указан - все параметры
		 * @return mixed
		 */
		function _requestGet($id, $param = '')
		{
			global $AVE_DB;

			static $request = null;

			if ($request === null)
			{
				$request = $AVE_DB->Query("
					SELECT
						*
					FROM
						" . PREFIX . "_request
					WHERE
						" . (is_numeric($id) ? 'Id' : 'request_alias') . " = '" . $id . "'
				")->FetchAssocArray();
			}

			if ($param == '')
				return $request;

			return isset($request[$param])
				? $request[$param]
				: null;
		}

		/**
		 * Метод, предназначенный для получения шаблонов
		 *
		 * @param int $rubric_id идентификатор рубрики
		 * @param string $template шаблон
		 * @param string $fetched шаблон модуля
		 * @return string
		 */
		function _coreDocumentTemplateGet($rubric_id = null, $template = null, $fetched = null, $template_id = null)
		{
			global $AVE_DB;

			// Если выводится только содержимое модуля или это новое окно (например страница для печати),
			// просто возвращаем содержимое.
			if (defined('ONLYCONTENT') || (isset ($_REQUEST['pop']) && $_REQUEST['pop'] == 1))
			{
				$out = '[tag:maincontent]';
			}
			else
			{
				// В противном случае, если в качестве аргумента передан шаблон модуля, возвращаем его.
				if (! empty($fetched))
				{
					$out = $fetched;
				}
				else
				{
					// В противном случае, если в качестве аргумента передан общий шаблон, возвращаем его
					if (! empty($template))
					{
						$out = $template;
					}
					else // В противном случае, если аргументы не определены, тогда проверяем
					{
						// Если для текущего документа в свойстве класса $this->curentdoc определен шаблон, тогда возвращаем его
						if (! empty ($this->curentdoc->template_text))
						{
							$out = stripslashes($this->curentdoc->template_text);
							unset($this->curentdoc->template_text);
						}
						else
						{
							// В противном случае, если не указан идентификатор рубрики
							if (empty ($rubric_id))
							{
								// Получаем id документа из запроса
								$_REQUEST['id'] = (isset ($_REQUEST['id']) && is_numeric($_REQUEST['id']))
									? $_REQUEST['id']
									: 1;

								// Выполняем запрос к БД на получение id рубрики на основании id документа
								$rubric_id = $AVE_DB->Query("
									SELECT
										rubric_id
									FROM
										" . PREFIX . "_documents
									WHERE
										Id = '" . $_REQUEST['id'] . "'
									LIMIT 1
								")->GetCell();

								// Если id рубрики не найден, возвращаем пустую строку
								if (! $rubric_id)
									return '';
							}

							// Выполняем запрос к БД на получение основного шаблона, а также шаблона рубрики
							$tpl = $this->_getMainTemplate($rubric_id, $template_id);

							// Если запрос выполнился с нулевым результатом, возвращаем пустую строку
							$out = $tpl
								? stripslashes($tpl)
								: '';
						}
					}
				}
			}

			// получаем из шаблона системный тег, определяющий название темы дизайна
			$match = array();

			preg_match('/\[tag:theme:(\w+)]/', $out, $match);

			define('THEME_FOLDER', empty ($match[1])
				? DEFAULT_THEME_FOLDER
				: $match[1]);

			$out = preg_replace('/\[tag:theme:(.*?)]/', '', $out);

			// Если пришел вызов на активацию языковых файлов
			$out = preg_replace_callback(
				'/\[tag:language]/',
				function ()
				{
					global $AVE_Template;

					$lang_file = BASE_DIR . '/templates/' . THEME_FOLDER . '/lang/' . $_SESSION['user_language'] . '.txt';

					$AVE_Template->config_load($lang_file);
					$AVE_Template->assign('config_vars', $AVE_Template->get_config_vars());
				},
				$out
			);

			return $out;
		}

		/**
		 * Метод, предназначенный для получения шаблона модуля
		 *
		 * @return string
		 */
		function _coreModuleTemplateGet()
		{
			global $AVE_DB;

			if (isset($_REQUEST['module']) && ! preg_match('/^[A-Za-z0-9-_]{1,20}$/i', $_REQUEST['module']))
				return '';

			// Если папка, с запрашиваемым модулем не существует, выполняем редирект
			// на главную страницу и отображаем сообщение с ошибкой
			if (! is_dir(BASE_DIR . '/modules/' . $_REQUEST['module']))
			{
				echo '<meta http-equiv="Refresh" content="2;URL=' . get_home_link() . '" />';
				$out = $this->_module_not_found;
			}
			// В противном случае
			else
			{
				// Выполняем запрос к БД на получение списка общих шаблонов имеющиюся в системе
				// и шаблоне, который установлен для данного модуля
				// Например, в системе есть шаблоны Template_1 и Template_2, а для модуля установлен Template_3
				$out = $AVE_DB->Query("
					SELECT
						tmpl.template_text
					FROM
						" . PREFIX . "_templates AS tmpl
					LEFT JOIN
						" . PREFIX . "_module AS mdl
						ON tmpl.Id = mdl.ModuleTemplate
					WHERE
						ModuleSysName = '" . $_REQUEST['module'] . "'
				")->GetCell();

				// Если шаблон, установленный для модуля не найден в системе, принудительно устанавливаем для него
				// первый шаблон (id=1)
				if (empty ($out))
				{
					$out = $AVE_DB->Query("
						SELECT
							template_text
						FROM
							" . PREFIX . "_templates
						WHERE
							Id = '1'
						LIMIT 1
					")->GetCell();
				}
			}
			// Возвращаем информацию о полученном шаблоне
			return stripslashes($out);
		}

		/**
		 * Метод, предназначенный для получения прав доступа к документам рубрики
		 *
		 * @param int $rubrik_id идентификатор рубрики
		 */
		function _coreRubricPermissionFetch($rubrik_id = '')
		{
			global $AVE_DB;

			unset ($_SESSION[$rubrik_id . '_docread']);

			// Если для документа уже получены права доступа, тогда
			if (!empty ($this->curentdoc->rubric_permission))
			{
				// Формируем массив с правами доступа
				$rubric_permissions = explode('|', $this->curentdoc->rubric_permission);

				// Циклически обрабатываем сформированный массив и создаем в сессии соответсвующие переменные
				foreach ($rubric_permissions as $rubric_permission)
				{
					if (!empty ($rubric_permission))
					{
						$_SESSION[$rubrik_id . '_' . $rubric_permission] = 1;
					}
				}
			} // В противном случае
			else
			{
				// Выполняем запрос к БД на получение списка прав для данного документа
				$sql = $AVE_DB->Query("
					SELECT
						rubric_permission
					FROM
						" . PREFIX . "_rubric_permissions
					WHERE
						rubric_id = '" . $rubrik_id . "'
					AND
						user_group_id = '" . UGROUP . "'
				");

				// Обрабатываем полученные данные и создаем в сессии соответсвующие переменные
				while ($row = $sql->FetchRow())
				{
					$row->rubric_permission = explode('|', $row->rubric_permission);

					foreach ($row->rubric_permission as $rubric_permission)
					{
						if (! empty($rubric_permission))
						{
							$_SESSION[$rubrik_id . '_' . $rubric_permission] = 1;
						}
					}
				}
			}
		}


		/**
		 * Метод, предназначенный для обработки события 404 Not Found, т.е. когда страница не найдена.
		 *
		 * @return unknown
		 */
		function _coreErrorPage404()
		{
			global $AVE_DB;

			// Выполняем запрос к БД на проверку существования страницы, которая содержит информацию о том, что
			// запрашиваемая страница не найдена
			$available = $AVE_DB->Query("
				SELECT
					COUNT(*)
				FROM
					" . PREFIX . "_documents
				WHERE
					Id = '" . PAGE_NOT_FOUND_ID . "'
				LIMIT 1
			")->GetCell();

			// Если такая страница в БД существует, выполняем переход на страницу с ошибкой
			if ($available)
			{
				header('Location:' . ABS_PATH . 'index.php?id=' . PAGE_NOT_FOUND_ID);
			}
			// Если не существует, тогда просто выводим текст, определенный в свойстве _doc_not_found
			else
				{
					echo $this->_doc_not_found;
				}

			exit;
		}


		/**
		 * Метод, предназначенный для проверки существования документа в БД
		 *
		 * @param int $document_id - id документа
		 * @param int $user_group - группа пользователя
		 * @return boolean
		 */
		function _coreCurrentDocumentFetch($document_id = 1, $user_group = 2)
		{
			global $AVE_DB;

			// Выполняем составной  запрос к БД на получение информации о запрашиваемом документе
			$this->curentdoc = $AVE_DB->Query("
				SELECT
					doc.*,
					rubric_permission,
					rubric_template,
					rubric_header_template,
					rubric_footer_template,
					rubric_meta_gen,
					template_text,
					other.template
				FROM
					" . PREFIX . "_documents AS doc
				JOIN
					" . PREFIX . "_rubrics AS rub
						ON rub.Id = doc.rubric_id
				JOIN
					" . PREFIX . "_templates AS tpl
						ON tpl.Id = rubric_template_id
				JOIN
					" . PREFIX . "_rubric_permissions AS prm
						ON doc.rubric_id = prm.rubric_id
				LEFT JOIN
					" . PREFIX . "_rubric_templates AS other
						ON doc.rubric_id = other.rubric_id	AND doc.rubric_tmpl_id = other.id
				WHERE
					user_group_id = '" . $user_group . "'
				AND
					doc.Id = '" . $document_id . "'
				LIMIT 1
			")->FetchRow();

			if ($this->curentdoc->rubric_tmpl_id != 0)
			{
				$this->curentdoc->rubric_template = (($this->curentdoc->template != '')
					? $this->curentdoc->template
					: $this->curentdoc->rubric_template);

				unset($this->curentdoc->template);
			}

			// Возвращаем 1, если документ найден, либо 0 в противном случае
			return (isset($this->curentdoc->Id) && $this->curentdoc->Id == $document_id);
		}

		/**
		 * Метод, предназначенный для получения содержимого страницы с 404 ошибкой
		 *
		 *
		 * @param int $page_not_found_id
		 * @param int $user_group
		 * @return int/boolean
		 */
		function _corePageNotFoundFetch($page_not_found_id = 2, $user_group = 2)
		{
			global $AVE_DB;

			// Выполняем запрос к БД на получение полной информации о странице с 404 ошибкой, включая
			// права доступа, шаблон рубрики и основной шаблон сайта
			$this->curentdoc = $AVE_DB->Query("
				SELECT
					doc.*,
					rubric_permission,
					rubric_template,
					rubric_header_template,
					rubric_footer_template,
					rubric_meta_gen,
					template_text
				FROM
					" . PREFIX . "_documents AS doc
				JOIN
					" . PREFIX . "_rubrics AS rub
						ON rub.Id = doc.rubric_id
				JOIN
					" . PREFIX . "_templates AS tpl
						ON tpl.Id = rubric_template_id
				JOIN
					" . PREFIX . "_rubric_permissions AS prm
						ON doc.rubric_id = prm.rubric_id
				WHERE
					user_group_id = '" . $user_group . "'
				AND
					doc.Id = '" . $page_not_found_id . "'
				LIMIT 1
			")->FetchRow();

			return (isset($this->curentdoc->Id) && $this->curentdoc->Id == $page_not_found_id);
		}

		/**
		 * Метод, предназначенный для получения МЕТА-тегов для различных модулей.
		 * ToDo
		 * @return boolean
		 */
		function _coreModuleMetatagsFetch()
		{
			global $AVE_DB;

			// Если в запросе не пришел параметр module, заврешаем работу
			if (! isset($_REQUEST['module']))
				return false;

			$this->curentdoc = $AVE_DB->Query("
				SELECT
					1 AS Id,
					0 AS document_published,
					document_meta_robots,
					document_meta_keywords,
					document_meta_description,
					document_title
				FROM
					" . PREFIX . "_documents
				WHERE
					Id = 1
			")->FetchRow();

			return (isset($this->curentdoc->Id) && $this->curentdoc->Id == 1);
		}

		/**
		 * Метод, предназначенный для определения статуса документа (доступен ли он к публикации).
		 *
		 * @return int/boolean
		 */
		function _coreDocumentIsPublished()
		{
			//Контроль даты: Использовать/Не использовать
			if (get_settings('use_doctime') != 0)
			{
				if (!empty ($this->curentdoc)																				// документ есть
					&& $this->curentdoc->Id != PAGE_NOT_FOUND_ID															// документ не сообщение ошибки 404
					&& $this->curentdoc->document_deleted == 1																// пометка удаления
					)
				{
					// Если пользователь авторизован в Панели управления или имеет полные права на просмотр документа, тогда
					if (isset ($_SESSION['adminpanel']) || isset ($_SESSION['alles']))
					{
						// Отображаем информационное окно с сообщением, определенным в свойстве _doc_not_published
						display_notice($this->_doc_not_published);
					}
					else // В противном случае фиксируем ошибку
						{
							$this->curentdoc = false;
						}
				}
			}
			else
			{
				if (! empty($this->curentdoc)																				// документ есть
					&& $this->curentdoc->Id != PAGE_NOT_FOUND_ID															// документ не сообщение ошибки 404
					&& $this->curentdoc->document_deleted == 1																// пометка удаления
					)
				{
					// Если пользователь авторизован в Панели управления или имеет полные права на просмотр документа, тогда
					if (isset ($_SESSION['adminpanel']) || isset ($_SESSION['alles']))
					{
						// Отображаем информационное окно с сообщением, определенным в свойстве _doc_not_published
						display_notice($this->_doc_not_published);
					}
					else // В противном случае фиксируем ошибку
						{
							$this->curentdoc = false;
						}
				}
			}
			return (! empty($this->curentdoc));
		}

		/**
		 * Метод парсинга тега [tag:(css|js):files]
		 * для вывода css/js-файлов в шаблоне через combine.php
		 *
		 * @param array $tag параметры тега
		 * @return string что выводить в шаблоне
		 */
		function _parse_combine($tag)
		{
			// тип тега (css|js)
			$type = $tag[1];
			// имена файлов
			$files = explode(',',$tag[2]);

			// определяем путь. если указан - то считаем от корня, если нет, то в /[tag:mediapath]/css|js/
			if ($tag[3])
			{
				$path = '/' . trim($tag[3],'/') . '/';
			}
			else
				{
					$path = '/templates/' . THEME_FOLDER . '/' . $type . '/';
				}

			// уровень вложенности
			$level = substr_count($path,'/') - 1;

			// копируем combine.php, если он поменялся или отсутствует
			$dest_stat = stat(BASE_DIR . $path . 'combine.php');
			$source_stat = stat(BASE_DIR . '/lib/combine/combine.php');

			if (! file_exists(BASE_DIR . $path . 'combine.php') || $source_stat[9] > $dest_stat[9])
			{
				@copy(BASE_DIR . '/lib/combine/combine.php', BASE_DIR . $path . 'combine.php');
			}

			// удаляем из списка отсутствующие файлы
			foreach($files as $key=>$file)
			{
				if (! file_exists(BASE_DIR . $path . $file))
					unset($files[$key]);
			}

			if ($files)
			{
				$combine = $path . 'combine.php?level=' . $level . '&amp;' . $type . '=' . implode(',', $files);
				$combine = @str_replace('//','/',$combine);
			}

			return $combine;
		}


		function _main_content ($main_content, $id, $rubTmpl)
		{
			global $AVE_DB, $AVE_Template;

			// Проверяем теги полей в шаблоне рубрики на условие != ''
			$main_content = preg_replace("/\[tag:if_notempty:fld:([a-zA-Z0-9-_]+)\]/u", '<'.'?php if((htmlspecialchars(document_get_field(\'$1\'), ENT_QUOTES)) != \'\') { '.'?'.'>', $rubTmpl);
			$main_content = preg_replace("/\[tag:if_empty:fld:([a-zA-Z0-9-_]+)\]/u", '<'.'?php if((htmlspecialchars(document_get_field(\'$1\'), ENT_QUOTES)) == \'\') { '.'?'.'>', $main_content);
			$main_content = str_replace('[tag:if:else]', '<?php }else{ ?>', $main_content);
			$main_content = str_replace('[tag:/if]', '<?php } ?>', $main_content);

			// Парсим элементы полей
			$main_content = preg_replace_callback(
				'/\[tag:fld:([a-zA-Z0-9-_]+)\]\[([0-9]+)]\[([0-9]+)]/',
					create_function(
						'$m',
						'return get_field_element($m[1], $m[2], $m[3], ' . $this->curentdoc->Id . ');'
					),
				$main_content
			);

			// Парсим теги полей документа в шаблоне рубрики
			$main_content = preg_replace_callback('/\[tag:fld:([a-zA-Z0-9-_]+)(|[:(\d)])+?\]/', 'document_get_field', $main_content);

			// Повторно парсим элементы полей
			$main_content = preg_replace_callback(
				'/\[tag:fld:([a-zA-Z0-9-_]+)\]\[([0-9]+)]\[([0-9]+)]/',
					create_function(
						'$m',
						'return get_field_element($m[1], $m[2], $m[3], ' . $this->curentdoc->Id . ');'
					),
				$main_content
			);

			// Повторно парсим теги полей документа в шаблоне рубрики
			$main_content = preg_replace_callback('/\[tag:fld:([a-zA-Z0-9-_]+)(|[:(\d)])+?\]/', 'document_get_field', $main_content);

			// Watermarks
			$main_content = preg_replace_callback('/\[tag:watermark:(.+?):([a-zA-Z]+):([0-9]+)\]/', 'watermarks', $main_content);

			// Thumbnail
			$main_content = preg_replace_callback('/\[tag:([r|c|f|t|s]\d+x\d+r*):(.+?)]/', 'callback_make_thumbnail', $main_content);

			// Возвращаем поле из БД документа
			$main_content = preg_replace_callback('/\[tag:doc:([a-zA-Z0-9-_]+)\]/u',
				function ($match)
				{
					return isset($this->curentdoc->{$match[1]})
						? $this->curentdoc->{$match[1]}
						: null;
				},
				$main_content
			);

			// Если пришел вызов на активацию языковых файлов
			$main_content = preg_replace_callback(
				'/\[tag:langfile:([a-zA-Z0-9-_]+)\]/u',
				function ($match)
				{
					global $AVE_Template;

					return $AVE_Template->get_config_vars($match[1]);
				},
				$main_content
			);

			// парсим теги в шаблоне рубрики
			$main_content = preg_replace_callback(
				'/\[tag:date:([a-zA-Z0-9-. \/]+)\]/',
					create_function('$m','return translate_date(date($m[1], '.$this->curentdoc->document_published.'));
				'),
				$main_content
			);

			$main_content = str_replace('[tag:docdate]', pretty_date(strftime(DATE_FORMAT, $this->curentdoc->document_published)), $main_content);
			$main_content = str_replace('[tag:doctime]', pretty_date(strftime(TIME_FORMAT, $this->curentdoc->document_published)), $main_content);
			$main_content = str_replace('[tag:humandate]', human_date($this->curentdoc->document_published), $main_content);
			$main_content = str_replace('[tag:docauthorid]', $this->curentdoc->document_author_id, $main_content);

			if (preg_match('[tag:docauthor]', $main_content))
				$main_content = str_replace('[tag:docauthor]', get_username_by_id($this->curentdoc->document_author_id), $main_content);

			// Удаляем ошибочные теги полей документа в шаблоне рубрики
			$main_content = preg_replace('/\[tag:watermark:\w*\]/', '', $main_content);
			$main_content = preg_replace('/\[tag:fld:\w*\]/', '', $main_content);
			$main_content = preg_replace('/\[tag:doc:\w*\]/', '', $main_content);
			$main_content = preg_replace('/\[tag:langfile:\w*\]/', '', $main_content);

			//-- Кеширование скомпилированного документа
			$this->setCompileDocument($main_content);

			return $main_content;
		}


		/**
		 * Метод, предназначенный для формирования хэша страницы
		 *
		 * @return string
		 */
		function _get_cache_hash()
		{
			$hash  = 'g-' . UGROUP; // Группа пользователей
			$hash .= 'r-' . RUB_ID; // ID Рубрики
			$hash .= 't-' . $this->curentdoc->rubric_tmpl_id; // Шаблон рубрики
			//$hash .= 'u-' . get_redirect_link(); // ToDo

			return md5($hash);
		}


		function _get_cache_id()
		{
			$cache = array();

			$cache['id'] = (int)$this->curentdoc->Id;

			if (! $cache['id'])
				return false;

			$cache['id'] = (int)$cache['id'];
			$cache['id'] = 'documents/compiled/' . (floor($cache['id'] / 1000)) . '/' . $cache['id'];

			$cache['file'] = $this->_get_cache_hash() . '.compiled';

			if (! $cache['file'])
				return false;

			$cache['dir'] = BASE_DIR . '/tmp/cache/sql/' . (trim($cache['id']) > ''
				? trim($cache['id']) . '/'
				: substr($cache['file'], 0, 2) . '/' . substr($cache['file'], 2, 2) . '/' . substr($cache['file'], 4, 2) . '/');

			return $cache;
		}


		function setCompileDocument ($main_content)
		{
			$cache = $this->_get_cache_id();

			if (! $cache)
				return false;

			//-- Удаляем файл, если существует
			if (file_exists($cache['dir'] . $cache['file']))
				unlink($cache['dir'] . $cache['file']);

			// Если включен DEV MODE, то отключаем кеширование запросов
			if (defined('DEV_MODE') AND DEV_MODE)
				return false;

			//-- Кэширование разрешено
			if (defined('CACHE_DOC_TPL') && CACHE_DOC_TPL)
			{
				//-- Если нет папки, создаем
				if (! is_dir($cache['dir']))
					@mkdir($cache['dir'], 0766, true);

				//-- Сохраняем скомпилированный шаблон в кэш
				file_put_contents($cache['dir'] . $cache['file'], $main_content);
			}

			return true;
		}


		function getCompileDocument ()
		{
			$cache = $this->_get_cache_id();

			if (! $cache)
				return false;

			$content = false;

			//-- Если нет папки, создаем
			if (! is_dir($cache['dir']))
				@mkdir($cache['dir'], 0766, true);

			//-- Получаем сразу поля
			get_document_fields((int)$this->curentdoc->Id);

			// Наличие файла
			if (file_exists($cache['dir'] . $cache['file']))
			{
				// Получаем время создания файла
				$file_time = filemtime($cache['dir'] . $cache['file']);

				// Получаем время для проверки
				$cache_time = $this->curentdoc->rubric_changed;

				if (! $cache_time || $cache_time > $file_time)
				{
					unlink ($cache['dir'] . $cache['file']);
				}
				else if (defined('CACHE_DOC_TPL') && CACHE_DOC_TPL)
					// Извлекаем скомпилированный шаблон документа из кэша
					$content = file_get_contents($cache['dir'] . $cache['file']);
			}
			else
				{
					$content = false;
				}

			return $content;
		}


		/**
		 * Метод, предназначенный для обработки системных тегов модулей. Здесь подключаются только те файлы модулей,
		 * системные теги которых обнаружены в шаблоне при парсинге. Также формирует массив всех установленных модулей
		 * в системе, предварительно проверяя их доступность.
		 *
		 * @param string $template текст шаблона с тегами
		 * @return string текст шаблона с обработанными тегами модулей
		 */
		function coreModuleTagParse($template)
		{
			global $AVE_DB, $AVE_Template, $AVE_Module;

			$pattern = array();  // Массив системных тегов
			$replace = array();  // Массив функций, на которые будут заменены системные теги

			if (null !== $AVE_Module->moduleListGet())
				$this->install_modules = $AVE_Module->moduleListGet();

			// Если уже имеются данные об установленных модулях
			if (null !== $this->install_modules)
			{
				// Циклически обрабатываем каждый модуль
				foreach ($this->install_modules as $row)
				{
					if ($row['ModuleStatus'] != 1)
						continue;

					// Если в запросе пришел вызов модуля или у модуля есть функция вызываемая тегом,
					// который присутствует в шаблоне
					if (
						(isset($_REQUEST['module']) && $_REQUEST['module'] == $row['ModuleSysName'])
						||
							(
								1 == $row['ModuleIsFunction']
								&&
								(isset($row['ModuleAveTag']) && !empty($row['ModuleAveTag']))
								&&
								1 == @preg_match($row['ModuleAveTag'], $template)
							)
						)
					{
						// Проверяем, существует ли для данного модуля функция. Если да,
						// получаем php код функции.
						if (function_exists($row['ModuleFunction']))
						{
							$pattern[] = $row['ModuleAveTag'];
							$replace[] = $row['ModulePHPTag'];
						}
						else // В противном случае
							{
								// Проверяем, существует ли для данного модуля файл module.php в его персональной директории
								$mod_file = BASE_DIR . '/modules/' . $row['ModuleSysName'] . '/module.php';

								if (is_file($mod_file) && include_once($mod_file))
								{
									// Если файл модуля найден, тогда
									if ($row['ModuleAveTag'])
									{
										$pattern[] = $row['ModuleAveTag'];  // Получаем его системный тег

										// Проверяем, существует ли для данного модуля функция. Если да,
										// получаем php код функции, в противном случае формируем сообщение с ошибкой
										$replace[] = function_exists($row['ModuleFunction'])
											? $row['ModulePHPTag']
											: ($this->_module_error . ' &quot;' . $row['ModuleName'] . '&quot;');
									}
								}
								// Если файла module.php не существует, формируем сообщение с ошибкой
								elseif ($row['ModuleAveTag'])
									{	$pattern[] = $row['ModuleAveTag'];
										$replace[] = $this->_module_error . ' &quot;' . $row['ModuleName'] . '&quot;';
									}
							}
					}
				}

				// Выполняем замену систеного тега на php код и возвращаем результат
				return preg_replace($pattern, $replace, $template);
			}
			else  // В противном случае, если список модулей пустой
			{
				$this->install_modules = array();

				// Выполняем запрос к БД на получение информации о всех модулях, которые установлены в системе
				// (именно установлены, а не просто существуют в виде папок)
				$sql = $AVE_DB->Query("
					SELECT *
					FROM
						" . PREFIX. "_module
					WHERE
						ModuleStatus = '1'
				");

				// Циклически обрабатываем полученные данные
				while ($row = $sql->FetchRow())
				{
					// Если в запросе пришел параметр module и для данного названия модуля существует
					// директория или данный модуль имеет функцию и его системный тег указан в каком-либо шаблоне, тогда
					if ((isset($_REQUEST['module']) && $_REQUEST['module'] == $row->ModuleSysName) ||
						(1 == $row->ModuleIsFunction && ! empty($row->ModuleAveTag) && 1 == preg_match($row->ModuleAveTag, $template)))
					{
						// Проверяем, существует ли для данного модуля файл module.php в его персональной директории
						$mod_file = BASE_DIR . '/modules/' . $row->ModuleSysName . '/module.php';

						if (is_file($mod_file) && include_once($mod_file))
						{	// Если файл модуля найден, тогда
							if (! empty($row->ModuleAveTag))
							{
								$pattern[] = $row->ModuleAveTag;  // Получаем его системный тег

								// Проверяем, существует ли для данного модуля функция. Если да,
								// получаем php код функции, в противном случае формируем сообщение с ошибкой
								$replace[] = function_exists($row->ModuleFunction)
									? $row->ModulePHPTag
									: ($this->_module_error . ' &quot;' . $row->ModuleSysName . '&quot;');
							}
							// Сохряняем информацию о модуле
							$this->install_modules[$row->ModuleSysName] = $row;
						}
						elseif ($row->ModuleAveTag) // Если файла module.php не существует, формируем сообщение с ошибкой
							{
								$pattern[] = $row->ModuleAveTag;
								$replace[] = $this->_module_error . ' &quot;' . $row->ModuleSysName . '&quot;';
							}
					}
					else
						{	// Если у модуля нет функции или тег модуля не используется - просто помещаем в массив информацию о модуле
							$this->install_modules[$row->ModuleSysName] = $row;
						}
				}

				// Выполняем замену систеного тега на php код и возвращаем результат
				return preg_replace($pattern, $replace, $template);
			}
		}

		/**
		 * Метод, предназанченный для сборки всей страницы в единое целое.
		 *
		 * @param int $id идентификатор документа
		 * @param int $rub_id идентификатор рубрики
		 */
		function coreSiteFetch($id, $rub_id = '')
		{
			global $AVE_DB;

			$main_content = '';

			// Если происходит вызов модуля, получаем соответствующие мета-теги и получаем шаблон модуля
			if (isset($_REQUEST['module']) && ! empty($_REQUEST['module']))
			{
				$out = $this->_coreModuleMetatagsFetch();
				$out = $this->_coreDocumentTemplateGet('', '', $this->_coreModuleTemplateGet());
			}
			// Если происходит вызов системного блока
			elseif (isset($_REQUEST['sysblock']) && ! empty($_REQUEST['sysblock']))
			{
				if (! is_numeric($_REQUEST['sysblock']) && preg_match('/^[A-Za-z0-9-_]{1,20}$/i', $_REQUEST['sysblock']) !== 1)
				{
					header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true);
					exit;
				}

				// проверяем разрешение на внешнее обращение
				if (! $this->_sysBlock($_REQUEST['sysblock'], 'sysblock_external'))
				{
					header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true);
					exit;
				}

				// проверяем разрешение на обращение только по Ajax
				if ($this->_sysBlock($_REQUEST['sysblock'], 'sysblock_ajax'))
				{
					if (isAjax())
						$out = parse_sysblock($_REQUEST['sysblock']);
					else
						$this->_coreErrorPage404();
				}
				else
				{
					$out = parse_sysblock($_REQUEST['sysblock']);
				}
			}
			// Если происходит вызов запроса
			elseif (isset($_REQUEST['request']) && ! empty($_REQUEST['request']))
			{
				if (! is_numeric($_REQUEST['request']) && preg_match('/^[A-Za-z0-9-_]{1,20}$/i', $_REQUEST['request']) !== 1)
					$this->_coreErrorPage404();

				// Определяем рубрику
				define('RUB_ID', ! empty ($rub_id)
					? $rub_id
					: $this->curentdoc->rubric_id);

				// Проверяем разрешение на внешнее обращение
				if (! $this->_requestGet($_REQUEST['request'], 'request_external'))
					$this->_coreErrorPage404();

				// Проверяем разрешение на обращение только по Ajax
				if ($this->_requestGet($_REQUEST['request'], 'request_ajax'))
				{
					if (isAjax())
						$out = request_parse($_REQUEST['request']);
					else
						$this->_coreErrorPage404();
				}
				else
				{
					$out = request_parse($_REQUEST['request']);
				}
			}
			// В противном случае начинаем вывод документа
			else
			{
				if (! isset($this->curentdoc->Id) && ! $this->_coreCurrentDocumentFetch($id, UGROUP))
				{
					// Определяем документ с 404 ошибкой в случае, если документ не найден
					if ($this->_corePageNotFoundFetch(PAGE_NOT_FOUND_ID, UGROUP))
						$_REQUEST['id'] = $_GET['id'] = $id = PAGE_NOT_FOUND_ID;
				}

				// проверяем параметры публикации документа
				if (! $this->_coreDocumentIsPublished())
					$this->_coreErrorPage404();

				// Определяем права доступа к документам рубрики
				define('RUB_ID', ! empty ($rub_id)
					? $rub_id
					: $this->curentdoc->rubric_id);

				$this->_coreRubricPermissionFetch(RUB_ID);

				// Выполняем Код рубрики До загрузки документа
				ob_start();
				eval(' ?>' . $this->curentdoc->rubric_start_code . '<?php ');
				ob_end_clean();

				// Получаем шаблон
				$out = $this->_coreDocumentTemplateGet(RUB_ID, null, null, $this->curentdoc->rubric_template_id);

				if (! ((isset ($_SESSION[RUB_ID . '_docread']) && $_SESSION[RUB_ID . '_docread'] == 1)
					|| (isset ($_SESSION[RUB_ID . '_alles']) && $_SESSION[RUB_ID . '_alles'] == 1)) )
				{	// читать запрещено - извлекаем ругательство и отдаём вместо контента
					$main_content = get_settings('message_forbidden');
				}
				else
				{
					if (isset ($_REQUEST['print']) && $_REQUEST['print'] == 1)
					{	// Увеличиваем счетчик версий для печати
						$AVE_DB->Query("
							UPDATE
								" . PREFIX . "_documents
							SET
								document_count_print = document_count_print + 1
							WHERE
								Id = '" . $id . "'
						");
					}
					else
					{
						if (! isset ($_SESSION['doc_view'][$id]))
						{	// Увеличиваем счетчик просмотров (1 раз в пределах сессии)
							$AVE_DB->Query("
								UPDATE
									" . PREFIX . "_documents
								SET
									document_count_view = document_count_view + 1
								WHERE
									Id = '" . $id . "'
							");

							$_SESSION['doc_view'][$id] = time();
						}

						$curdate = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

						if (! isset($_SESSION['doc_view_dayly['.$curdate.'][' . $id . ']']))
						{
							// и подневный счетчик просмотров тоже увеличиваем
							$curdate = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

							$AVE_DB->Query("
								UPDATE
									" . PREFIX . "_view_count
								SET
									count = count + 1
								WHERE
									document_id = '" . $id . "' AND
									day_id = '".$curdate."'
							");

							if (! $AVE_DB->getAffectedRows())
							{
								$AVE_DB->Query("
									INSERT INTO " . PREFIX . "_view_count (
										document_id,
										day_id,
										count
									)
									VALUES (
										'" . $id . "',  '".$curdate."', '1'
									)
								");
							}

							$_SESSION['doc_view_dayly['.$curdate.'][' . $id . ']'] = time();
						}
					}

					// Извлекаем скомпилированный шаблон документа из кэша
					if (defined('CACHE_DOC_TPL') && CACHE_DOC_TPL) // && empty ($_POST)
						$main_content = $this->getCompileDocument();
					else
						// Кэширование запрещено
						$main_content = false;

					// Собираем контент
					// Если в кеше нет контента, то
					if (empty($main_content))
					{
						// Кэш пустой или отключен, извлекаем и компилируем шаблон
						if (! empty ($this->curentdoc->rubric_template))
						{
							$rubTmpl = $this->curentdoc->rubric_template;
						}
						else
						{
							// Если документу задан другой шаблон из данной рубрики, то берем его
							if ($this->curentdoc->rubric_tmpl_id != 0)
							{
								$rubTmpl = $AVE_DB->Query("
									SELECT
										template
									FROM
										" . PREFIX . "_rubric_templates
									WHERE
										id = '" . $this->curentdoc->rubric_tmpl_id . "'
									AND
										rubric_id = '" . RUB_ID . "'
									LIMIT 1
								")->GetCell();
							}
							// Иначе берем стандартный шаблон рубрики
							else
							{
								$rubTmpl = $AVE_DB->Query("
									SELECT
										rubric_template
									FROM
										" . PREFIX . "_rubrics
									WHERE
										Id = '" . RUB_ID . "'
									LIMIT 1
								")->GetCell();
							}
						}

						$rubTmpl = trim($rubTmpl);

						// Собираем шаблон рубрики
						if (empty($rubTmpl))
							// Если не задан шаблон рубрики, выводим сообщение
							$main_content = $this->_rubric_template_empty;
						else
							// Обрабатываем основные поля рубрики
							$main_content = $this->_main_content($main_content, $id, $rubTmpl);
					}
				}

				$out = str_replace('[tag:maincontent]', $main_content, $out);

				unset ($this->curentdoc->rubric_template, $this->curentdoc->template);
			}
			//-- Конец вывода документа

			//Работа с условиями
			/*
				$out = preg_replace('/\[tag:if_exp:?(.*)\]/u', '<?php
				$my_exp000=true;
				$my_exp0001=\'$my_exp000=\'. str_replace(\'#var#\',\'$\',<<<BLOCK
			$1;
			BLOCK
			);
				@eval($my_exp0001);
				if($my_exp000==true)
					{
			?>', $out);
					$out = str_replace('[tag:if_exp_else]', '<?php }else{ ?>', $out);
					$out = str_replace('[tag:/if_exp]', '<?php } ?>', $out);

			*/

			// Тут мы вводим в хеадер и футер иньекцию скриптов.
			if (defined('RUB_ID'))
			{
				$replace = array(
					'[tag:rubheader]' => $this->curentdoc->rubric_header_template . '[tag:rubheader]',
					'[tag:rubfooter]' => $this->curentdoc->rubric_footer_template . '[tag:rubfooter]'
				);

				$out = str_replace(array_keys($replace), array_values($replace), $out);

				unset($replace);
			}

			// Парсим поля запроса
			$out = preg_replace_callback(
				'/\[tag:rfld:([a-zA-Z0-9-_]+)]\[(more|esc|img|strip|[0-9-]+)]/',
					create_function(
						'$m',
						'return request_get_document_field($m[1], ' . $id . ', $m[2], ' . (defined('RUB_ID') ? RUB_ID : 0) . ');'
					),
				$out
			);

			// Удаляем ошибочные теги полей документа в шаблоне рубрики
			$out = preg_replace('/\[tag:rfld:\w*\]/', '', $out);

			// Если в GET запросе пришел параметр print, т.е. страница для печати,
			// парсим контент, который обрамлен тегами только для печати
			if (isset ($_REQUEST['print']) && $_REQUEST['print'] == 1)
			{
				$out = str_replace(array('[tag:if_print]', '[/tag:if_print]'), '', $out);
				$out = preg_replace('/\[tag:if_notprint\](.*?)\[\/tag:if_notprint\]/si', '', $out);
			}
			else
				{
					// В противном случае наоборот, парсим только тот контент, который предназначен НЕ для печати
					$out = preg_replace('/\[tag:if_print\](.*?)\[\/tag:if_print\]/si', '', $out);
					$out = str_replace(array('[tag:if_notprint]', '[/tag:if_notprint]'), '', $out);
				}

			// Парсим теги визуальных блоков
			$out = preg_replace_callback('/\[tag:block:([A-Za-z0-9-_]{1,20}+)\]/', 'parse_block', $out);

			// Парсим теги системных блоков
			$out = preg_replace_callback('/\[tag:sysblock:([A-Za-z0-9-_]{1,20}+)\]/', 'parse_sysblock', $out);

			// Парсим теги модулей
			$out = $this->coreModuleTagParse($out);

			// Если в запросе пришел параметр module, т.е. вызов модуля,
			// проверяем установлен и активен ли модуль
			if (isset($_REQUEST['module'])
				&& ! (isset($this->install_modules[$_REQUEST['module']])
					&& '1' == $this->install_modules[$_REQUEST['module']]['ModuleStatus']) )
			{
				// Выводим сообщение о том что такого модуля нет
				display_notice($this->_module_error);
			}

			// Парсим теги системы внутренних запросов
			$out = preg_replace_callback('/\[tag:request:([A-Za-z0-9-_]{1,20}+)\]/', 'request_parse', $out);

			// Парсим теги навигации
			$out = preg_replace_callback('/\[tag:navigation:([A-Za-z0-9-_]{1,20}+):?([0-9,]*)\]/', 'parse_navigation', $out);

			// Парсим теги скрытого текста
			$out = parse_hide($out);

			// Если в запросе пришел параметр sysblock, т.е. вызов сис блока,
			// парсим контент
			if (isset($_REQUEST['sysblock']) && $_REQUEST['sysblock'] != '')
			{
				$search = array(
					'[tag:mediapath]',
					'[tag:path]',
					'[tag:sitename]',
					'[tag:home]',
					'[tag:docid]',
					'[tag:docparent]'
				);

				$replace = array(
					ABS_PATH . 'templates/' . ((defined('THEME_FOLDER') === false) ? DEFAULT_THEME_FOLDER : THEME_FOLDER) . '/',
					ABS_PATH,
					htmlspecialchars(get_settings('site_name'), ENT_QUOTES),
					get_home_link(),
					(isset ($this->curentdoc->Id) ? $this->curentdoc->Id : ''),
					(isset ($this->curentdoc->document_parent) ? $this->curentdoc->document_parent : '')
				);
			}
			// Если в запросе пришел параметр request, т.е. вызов запроса,
			// парсим контент
			elseif (isset($_REQUEST['request']) && $_REQUEST['request'] != '')
			{
				$search = array(
					'[tag:mediapath]',
					'[tag:path]',
					'[tag:sitename]',
					'[tag:home]',
					'[tag:docid]',
					'[tag:docparent]'
				);

				$replace = array(
					ABS_PATH . 'templates/' . ((defined('THEME_FOLDER') === false) ? DEFAULT_THEME_FOLDER : THEME_FOLDER) . '/',
					ABS_PATH,
					htmlspecialchars(get_settings('site_name'), ENT_QUOTES),
					get_home_link(),
					(isset ($this->curentdoc->Id) ? $this->curentdoc->Id : ''),
					(isset ($this->curentdoc->document_parent) ? $this->curentdoc->document_parent : '')
				);
			}
			else
				{
					// В противном случае
					// парсим остальные теги основного шаблона
					$search = array(
						'[tag:mediapath]',
						'[tag:path]',
						'[tag:sitename]',
						'[tag:alias]',
						'[tag:domain]',
						'[tag:home]',
						'[tag:robots]',
						'[tag:canonical]',
						'[tag:docid]',
						'[tag:docparent]'
					);

					$replace = array(
						ABS_PATH . 'templates/' . ((defined('THEME_FOLDER') === false) ? DEFAULT_THEME_FOLDER : THEME_FOLDER) . '/',
						ABS_PATH,
						htmlspecialchars(get_settings('site_name'), ENT_QUOTES),
						(isset($_REQUEST['id'])) ? isset ($this->curentdoc->document_alias) ? $this->curentdoc->document_alias : '' : '',
						getSiteUrl(),
						get_home_link(),
						(isset($this->curentdoc->document_meta_robots) ? $this->curentdoc->document_meta_robots : ''),
						canonical(isset ($this->curentdoc->document_alias) ? ABS_PATH . $this->curentdoc->document_alias : ''),
						(isset($this->curentdoc->Id) ? $this->curentdoc->Id : ''),
						(isset($this->curentdoc->document_parent) ? $this->curentdoc->document_parent : '')
					);
				}

			// Если пришел контент из модуля
			if (defined('MODULE_CONTENT'))
			{
				// парсинг тегов при выводе из модуля
				$search[] 	= '[tag:maincontent]';
				$replace[] 	= MODULE_CONTENT;
				$search[] 	= '[tag:title]';
				$replace[] 	= htmlspecialchars(defined('MODULE_TITLE') ? MODULE_TITLE : '', ENT_QUOTES);
				$search[] 	= '[tag:description]';
				$replace[] 	= htmlspecialchars(defined('MODULE_DESCRIPTION') ? MODULE_DESCRIPTION : '', ENT_QUOTES);
				$search[] 	= '[tag:keywords]';
				$replace[] 	= htmlspecialchars(defined('MODULE_KEYWORDS') ? MODULE_KEYWORDS : '', ENT_QUOTES);
			}
			// Или из системного блока
			elseif (isset($_REQUEST['sysblock']))
				{
					// Убираем пустые теги в сис блоке
					$main_content = preg_replace('/\[tag:(.+?)\]/', '', $main_content);
					$main_content = preg_replace('/\[mod_(.+?)\]/', '', $main_content);
				}
			// Или из запроса
			elseif (isset($_REQUEST['request']))
				{
					// Убираем пустые теги в сис блоке
					$main_content = preg_replace('/\[tag:(.+?)\]/', '', $main_content);
					$main_content = preg_replace('/\[mod_(.+?)\]/', '', $main_content);
				}
				// Иначе
				else
					{
						// Если стоит вкл на генерацию keywords, description
						if ($this->curentdoc->rubric_meta_gen)
						{
							// Генерируем keywords, description на основе
							// данных документа, если позволяет рубрика
							require_once(dirname(__FILE__).'/class.meta.php');
							$meta = new Meta();
							$res = $meta->generateMeta($main_content);
						}

						// Убираем пустые теги
						$main_content = preg_replace('/\[tag:(.+?)\]/', '', $main_content);
						$main_content = preg_replace('/\[mod_(.+?)\]/', '', $main_content);

						// Парсим keywords, description, title
						$search[] = '[tag:keywords]';
						$replace[] = stripslashes(htmlspecialchars((! empty ($this->curentdoc->rubric_meta_gen) ? $res['keywords'] : $this->curentdoc->document_meta_keywords), ENT_QUOTES));
						$search[] = '[tag:description]';
						$replace[] = stripslashes(htmlspecialchars((! empty ($this->curentdoc->rubric_meta_gen) ? $res['description'] : $this->curentdoc->document_meta_description), ENT_QUOTES));
						$search[] = '[tag:title]';
						$replace[] = stripslashes(htmlspecialchars_decode(pretty_chars($this->curentdoc->document_title)));
					}

			// Возвращаем поле из БД документа
			$out = preg_replace_callback('/\[tag:doc:([a-zA-Z0-9-_]+)\]/u',
				function ($match)
				{
					return isset($this->curentdoc->{$match[1]})
						? $this->curentdoc->{$match[1]}
						: null;
				},
				$out
			);

			// Если пришел вызов на активацию языковых файлов
			$out = preg_replace_callback(
				'/\[tag:langfile:([a-zA-Z0-9-_]+)\]/u',
				function ($match)
				{
					global $AVE_Template;

					return $AVE_Template->get_config_vars($match[1]);
				},
				$out
			);

			// Убираем пустые теги
			$out = preg_replace('/\[tag:doc:\d*\]/', '', $out);
			$out = preg_replace('/\[tag:langfile:\d*\]/', '', $out);

			// Убираем дубликат
			$search[] = '[tag:maincontent]';
			$replace[] = '';

			// Парсим линк на версию для печати
			$search[] = '[tag:printlink]';
			$replace[] = get_print_link();

			// Парсим тег версии системы
			$search[] = '[tag:version]';
			$replace[] = APP_NAME . ' v' . APP_VERSION ;

			// Парсим тег кол-ва просмотра данного документа
			$search[] = '[tag:docviews]';
			$replace[] = isset ($this->curentdoc->document_count_view) ? $this->curentdoc->document_count_view : '';

			// Парсим тизер документа
			$out = preg_replace_callback(
				'/\[tag:teaser:(\d+)(|:\[(.*?)\])\]/',
					create_function(
						'$m',
						'return showteaser($m[1], $m[2]);'
					),
				$out
			);

			// Парсим аватар автора документа
			if (defined('RUB_ID'))
				$out = preg_replace_callback(
					'/\[tag:docauthoravatar:(\d+)\]/',
						create_function(
							'$m',
							'return getAvatar('.intval($this->curentdoc->document_author_id).', $m[1]);'
						),
					$out
				);

			// Парсим теги языковых условий
			if (defined('RUB_ID'))
			{
				$out = preg_replace('/\[tag:lang:([a-zA-Z0-9-_]+)\]/', '<?php if ($AVE_Core->curentdoc->document_lang == "$1") { ?>', $out);
			}
			else
				{
					$out = preg_replace('/\[tag:lang:([a-zA-Z0-9-_]+)\]/', '<?php if ($_SESSION["user_language"] == "$1") { ?>', $out);
				}

			$out = str_replace('[tag:/lang]', '<?php } ?>', $out);

			// Парсим хлебные крошки
			if (preg_match('/\[tag:breadcrumb]/u', $out))
			{
				$out = preg_replace_callback('/\[tag:breadcrumb\]/', 'get_breadcrumb', $out);
			}

			// Парсим остальные теги основного шаблона
			$out = str_replace($search, $replace, $out);

			unset ($search, $replace); //Убираем данные

			// Парсим теги для combine.php
			$out = preg_replace_callback('/\[tag:(css|js):([^ :\/]+):?(\S+)*\]/', array($this, '_parse_combine'), $out);

			// ЧПУ
			$out = str_ireplace('"//"','"/"', str_ireplace('///','/', rewrite_link($out)));

			unset($main_content);

			// Выводим собранный документ
			echo $out;
		}

		/**
		 * Метод, предназначенный для формирования ЧПУ, а также для поиска документа и разбора
		 * дополнительных параметров в URL
		 *
		 * @param string $get_url Строка символов
		 *
		 */
		function coreUrlParse($get_url = '')
		{
			global $AVE_DB;

			//-- Если нужны параметры GET, можно отключить
			$get_url = (strpos($get_url, ABS_PATH . '?') === 0
				? ''
				: $get_url);

			if	(substr($get_url, 0, strlen(ABS_PATH . 'index.php')) != ABS_PATH . 'index.php' AND strpos($get_url, '?') !== false)
				$get_url = substr($get_url, 0, strpos($get_url, '?'));

			$get_url = rawurldecode($get_url);
			$get_url = mb_substr($get_url, strlen(ABS_PATH));

			//-- Сохранение старого урла для првоерки использования суффикса
			$check_url = $get_url;

			if (mb_substr($get_url, - strlen(URL_SUFF)) == URL_SUFF)
			{
				$get_url = mb_substr($get_url, 0, - strlen(URL_SUFF));
			}

			//-- Ложный URL
			$fake_url = false;

			//-- Разбиваем строку параметров на отдельные части
			$get_url = explode('/', $get_url);

			if (isset ($get_url['index']))
			{
				unset ($get_url['index']);
			}

			if (isset ($get_url['print']))
			{
				$_GET['print'] = $_REQUEST['print'] = 1;
				unset ($get_url['print']);
			}

			//-- Определяем, используется ли у нас разделение документа по страницам
			$pages = preg_grep('/^(a|art)?page-\d+$/i', $get_url);

			if (! empty ($pages))
			{
				$get_url = implode('/', array_diff($get_url, $pages));
				$pages = implode('/', $pages);

				preg_replace_callback(
					'/(page|apage|artpage)-(\d+)/i',
					create_function(
						'$matches',
						'$_GET[$matches[1]] = $matches[2]; $_REQUEST[$matches[1]] = $matches[2];'
					),
					$pages
				);
			}
			//-- В противном случае формируем окончательную ссылку для документа
			else
				{
					$get_url = implode('/', $get_url);
				}

			//-- Страница тегов
			preg_match('/^tags(|(\/.*))+$/is', $get_url, $match);

			//-- Если есть совпадение с tag
			if ($match)
			{
				//-- Смотрим условие
				if (isset($match[2]))
				{
					//-- Отрезаем лишнее
					$matches = trim($match[2], '/');

					//-- Разбиваем
					$matches = explode('/', $matches);

					//-- Берем первое значение
					$matches = urldecode(array_shift($matches));

					//-- Если значение не равно пусто
					if ($matches != '')
					{
						//-- Передаем в _GET условие tag
						$_GET['tag'] = $_REQUEST['tag'] = $matches;

						//-- Парсим query strings
						parse_str($_SERVER['QUERY_STRING'], $query_string);

						//-- Назначаем условие
						$query_string['tag'] = $matches;

						//-- Пересобираем QUERY_STRING
						$_SERVER['QUERY_STRING'] = http_build_query($query_string);

						//-- Назначаем URL
						$get_url = 'tags';

						//-- Инициализируем ложный URL
						$fake_url = true;
					}
				}
			}

			//-- Экранируем поступающий URL
			$get_url = $AVE_DB->ClearUrl($get_url);

			//-- Проверяем есть ли данный URL в таблице алиасов модулей
			$sql = "
				SELECT
					# MODULE LINK
					document_id,
					module_name,
					module_action,
					module_link
				FROM
					" . PREFIX . "_modules_aliases
				WHERE
					module_url = '" . str_ireplace("'", "\'", $get_url) . "'
			";

			$module = $AVE_DB->Query($sql)->FetchAssocArray();

			if ($module)
			{
				//-- Передаем глобальные перемененные
				$_GET['module'] = $_REQUEST['module'] = $module['module_name'];
				$_GET['action'] = $_REQUEST['action'] = $module['module_action'];

				$get_url = ABS_PATH . $module['module_link'];

				//-- Если есть document_id, назначем его
				if ($module['document_id'])
					$_REQUEST['id'] = (int)$module['document_id'];
			}

			unset ($sql, $module);

			//-- Проверка на наличие id в запросе
			if (! empty($_REQUEST['id']))
			{
				$get_url = $AVE_DB->Query("
					SELECT
						document_alias
					FROM
						" . PREFIX . "_documents
					WHERE
						Id = '" . (int)$_REQUEST['id'] . "'
				")->GetCell();
			}

			// Выполняем запрос к БД на получение всей необходимой
			// информации о документе

			$document_id = (! empty($_REQUEST['id'])
				? intval($_REQUEST['id'])
				: 1);

			//-- Забираем нужные данные
			$sql = "
				SELECT
					# URL FETCH = $get_url
					*
				FROM
					" . PREFIX . "_documents
				WHERE
					" . (! empty ($get_url) && ! isset($_REQUEST['module'])
							? "document_alias = '" . str_ireplace("'", "\'", $get_url) . "'"
							: (! empty($_REQUEST['id'])
								? "Id =" . intval($_REQUEST['id'])
								: "Id = 1")) . "
				LIMIT 1
			";

			$hash_url = md5($get_url);

			$cache_time = 0;

			if (defined('CACHE_DOC_FILE') && CACHE_DOC_FILE)
				$cache_time = -1;
			else
				$AVE_DB->clearCacheUrl('url_' . $hash_url);

			$this->curentdoc = $AVE_DB->Query($sql, $cache_time, 'url_' . $hash_url, true, '.fetch')->FetchRow();

			if ($this->curentdoc)
			{
				// Получить шаблон рубрики
				$sql = "
					SELECT STRAIGHT_JOIN
						# FETCH RUB = " . $this->curentdoc->rubric_id . "
						prm.rubric_permission,
						rub.rubric_template,
						rub.rubric_meta_gen,
						rub.rubric_template_id,
						rub.rubric_header_template,
						rub.rubric_footer_template,
						rub.rubric_start_code,
						rub.rubric_changed,
						rub.rubric_changed_fields,
						other.template
					FROM
						" . PREFIX . "_rubrics AS rub
					LEFT JOIN
						" . PREFIX . "_rubric_permissions AS prm
						ON rub.Id = prm.rubric_id
					LEFT JOIN
						" . PREFIX . "_rubric_templates AS other
						ON (rub.Id = other.rubric_id AND other.id = '" . $this->curentdoc->rubric_tmpl_id . "')
					WHERE
						prm.user_group_id = '" . UGROUP . "'
						AND
						rub.Id = '" . $this->curentdoc->rubric_id . "'
				";

				$query = $AVE_DB->Query($sql, $cache_time, 'rub_' . $this->curentdoc->rubric_id, true, '.fetch')->FetchRow();

				$this->curentdoc = (object) array_merge((array) $query, (array) $this->curentdoc);

				if ($this->curentdoc->rubric_tmpl_id != 0)
				{
					$this->curentdoc->rubric_template = (($this->curentdoc->template != '')
						? $this->curentdoc->template
						: $this->curentdoc->rubric_template);

					unset ($this->curentdoc->template);
				}

				//-- Глобальные переменные
				$_GET['id']  = $_REQUEST['id']  = $this->curentdoc->Id;
				$_GET['doc'] = $_REQUEST['doc'] = $this->curentdoc->document_alias;

				//-- Назначаем язык пользователю, в завивисомтси от языка документа
				if ($this->curentdoc->Id != PAGE_NOT_FOUND_ID OR $this->curentdoc->document_lang == '--')
					$_SESSION['user_language'] = $this->curentdoc->document_lang;

				//-- Если есть ложный URL указываем его
				if ($fake_url)
				{
					$check_url = preg_replace('/\/(a|art)?page-\d/i', '', $check_url);

					$_GET['doc'] = $_REQUEST['doc'] = $check_url;
					$this->curentdoc->document_alias = $check_url;
					$get_url = $check_url;
				}

				//-- Перенаправление на адреса с суффиксом
				if (
					$check_url !== $get_url . URL_SUFF
					&& ! $pages && $check_url
					&& ! $_REQUEST['print']
					&& ! $_REQUEST['module']
					&& ! $_REQUEST['tag']
					&& REWRITE_MODE
				)
				{
					header('HTTP/1.1 301 Moved Permanently');

					if ($this->curentdoc->Id == 1)
						header('Location:' . ABS_PATH);
					else
						header('Location:' . ABS_PATH . $get_url . URL_SUFF);

					exit();
				}
			}
			else
				{
					$AVE_DB->clearCacheUrl('url_' . $hash_url);

					$sql = "
						SELECT
							# REDIRECT = $get_url
							a.document_alias
						FROM
							".PREFIX."_document_alias_history AS h,
							".PREFIX."_documents AS a
						WHERE
							h.document_id = a.Id
						AND
							h.document_alias = '" . $get_url . "'
					";

					$redirect_alias = $AVE_DB->Query($sql)->GetCell();

					if ($redirect_alias && ! empty($redirect_alias))
					{
						$redirect_alias = ABS_PATH . $redirect_alias . URL_SUFF;
						$redirect_alias = str_replace('//', '/', $redirect_alias);

						header('HTTP/1.1 301 Moved Permanently');
						header('Location:' . $redirect_alias);
						exit();
					}

					if (! (! empty($_REQUEST['sysblock']) || ! empty($_REQUEST['module']) || ! empty($_REQUEST['request'])))
						$_GET['id'] = $_REQUEST['id'] = PAGE_NOT_FOUND_ID;
				}

			unset ($sql, $query);
		}
	}
?>