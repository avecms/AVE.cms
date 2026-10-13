<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         setup/index.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	define('AVE_SETUP', true);
	define('BASE_PATH', str_replace('\\', '/', dirname(__DIR__)));

	@ini_set('display_errors', '0');
	@ini_set('default_charset', 'UTF-8');

	$setupSessionError = '';
	$setupSessionPath = BASE_PATH . '/tmp/sessions';
	if (!is_dir($setupSessionPath) && !@mkdir($setupSessionPath, 0775, true)) {
		$setupSessionError = 'Не удалось создать каталог tmp/sessions для сессии установщика.';
	} elseif (!is_writable($setupSessionPath)) {
		$setupSessionError = 'Каталог tmp/sessions недоступен для записи.';
	}

	if ($setupSessionError === '') {
		@ini_set('session.save_handler', 'files');
		if (strtolower((string) ini_get('session.save_handler')) === 'files') {
			@session_save_path($setupSessionPath);
		}
	}

	@ini_set('session.use_cookies', '1');
	@ini_set('session.use_only_cookies', '1');
	@ini_set('session.use_strict_mode', '1');
	@ini_set('session.cookie_httponly', '1');
	@ini_set('session.cookie_samesite', 'Lax');
	$setupScriptDirectory = rtrim(str_replace('\\', '/', dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/setup/index.php')), '/');
	$setupCookiePath = preg_replace('#/setup$#', '', $setupScriptDirectory) . '/';
	$setupCookiePath = $setupCookiePath === '//' ? '/' : $setupCookiePath;
	$setupCookieSecure = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
		|| (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
	session_set_cookie_params(array(
		'lifetime' => 0,
		'path' => $setupCookiePath,
		'domain' => '',
		'secure' => $setupCookieSecure,
		'httponly' => true,
		'samesite' => 'Lax',
	));
	session_name('AVE_SETUP');
	if (!@session_start() && $setupSessionError === '') {
		$setupSessionError = 'Не удалось запустить сессию установщика в tmp/sessions. Проверьте права на каталог tmp.';
	}

	header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
	header('Pragma: no-cache');
	header('Expires: 0');
	if (empty($_SESSION['setup_csrf'])) {
		$_SESSION['setup_csrf'] = bin2hex(random_bytes(32));
	}

	require_once __DIR__ . '/Localization.php';
	$requestedSetupLanguage = isset($_POST['setup_language'])
		? (string) $_POST['setup_language']
		: (isset($_GET['lang']) ? (string) $_GET['lang'] : '');
	AveSetupLocale::boot($requestedSetupLanguage);
	$setupLanguage = AveSetupLocale::current();

	require_once __DIR__ . '/PackageExtractor.php';
	$packageExtractor = new AvePackageExtractor(BASE_PATH);
	if ($packageExtractor->available()) {
		$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['extract_runtime'])) {
			header('Content-Type: application/json; charset=UTF-8');
			try {
				$token = isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : '';
				if (!hash_equals((string) $_SESSION['setup_csrf'], $token)) {
					throw new RuntimeException('Сессия распаковки устарела. Обновите страницу.');
				}

				echo json_encode(
					AveSetupLocale::translatePayload($packageExtractor->step()),
					JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
				);
			} catch (Throwable $e) {
				http_response_code(422);
				echo json_encode(
					array('success' => false, 'message' => AveSetupLocale::translate($e->getMessage())),
					JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
				);
			}

			exit;
		}

		$css = __DIR__ . '/assets/installer.css';
			$js = __DIR__ . '/assets/installer.js';
			$escape = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
			ob_start();
			?>
<!doctype html>
<html lang="<?= $escape($setupLanguage) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Подготовка AVE.cms</title>
  <link rel="stylesheet" href="<?= $escape($base) ?>/assets/installer.css?v=<?= substr(hash_file('sha256', $css), 0, 12) ?>">
	  <script>window.AveSetupI18n=<?= json_encode(AveSetupLocale::clientTranslations()) ?>;</script>
	  <script src="<?= $escape($base) ?>/assets/installer.js?v=<?= substr(hash_file('sha256', $js), 0, 12) ?>" defer></script>
</head>
<body>
  <main class="installer-shell">
	    <header class="installer-header"><strong class="installer-wordmark">AVE.cms</strong><div class="installer-header-actions"><div class="installer-version">Подготовка системы</div><nav class="installer-language" aria-label="Язык установщика"><?php foreach (AveSetupLocale::locales() as $code => $label): ?><a class="<?= $code === $setupLanguage ? 'is-active' : '' ?>" href="?lang=<?= $escape($code) ?>"><?= $escape(strtoupper($code)) ?></a><?php endforeach; ?></nav></div></header>
	    <section class="installer-card installer-package" data-package-extract data-endpoint="<?= $escape($base) ?>/" data-csrf="<?= $escape($_SESSION['setup_csrf']) ?>" data-language="<?= $escape($setupLanguage) ?>">
      <div class="installer-package-icon">⇩</div>
      <div class="installer-package-body">
        <p class="installer-eyebrow">Компактная поставка</p>
        <h1 data-package-title>Распаковываем AVE.cms</h1>
        <p data-package-detail>Проверяем архив и готовим файлы системы. Не закрывайте эту страницу.</p>
        <div class="installer-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" data-package-track><span data-package-bar></span></div>
        <div class="installer-package-status"><span data-package-file>Подготовка проверки</span><strong data-package-percent>0%</strong></div>
        <div class="installer-error" data-package-error hidden><b>Распаковка остановлена</b><span data-package-error-message></span><button class="button button-secondary" type="button" data-package-retry>Повторить</button></div>
      </div>
    </section>
  </main>
</body>
</html>
			<?php
			echo AveSetupLocale::translate(ob_get_clean());
			exit;
		}

	require_once __DIR__ . '/Installer.php';

	$installer = new AveInstaller(BASE_PATH, $setupLanguage);
	$officialRepository = $installer->officialRepositoryProfile();
	$release = is_file(BASE_PATH . '/system/release.php') ? require BASE_PATH . '/system/release.php' : array();
	$releaseVersion = isset($release['version']) ? (string) $release['version'] : '3.3';
	$releaseBuild = isset($release['build']) ? (string) $release['build'] : '0.20';
	$adminPasswordMinLength = AveInstaller::ADMIN_PASSWORD_MIN_LENGTH;
	$requirements = $installer->requirements();
	if ($setupSessionError !== '') {
		array_unshift($requirements, array(
			'label' => 'Сессия установщика',
			'passed' => false,
			'detail' => $setupSessionError,
			'required' => true,
		));
	}

	$requirementsPassed = $installer->requirementsPassed($requirements);
	$state = $installer->installationState();
	$existingConfig = $installer->existingDatabaseConfig();
	$adminDirectory = $installer->configuredAdminDirectory();
	$error = '';
	$success = null;
	$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
	$rootUrl = preg_replace('#/setup$#', '', $base);
	$requestHost = isset($_SERVER['HTTP_HOST']) ? strtolower(trim((string) $_SERVER['HTTP_HOST'])) : '';
	if (!preg_match('/^[a-z0-9.\-\[\]:]+$/', $requestHost)) { $requestHost = 'localhost'; }
	$requestScheme = $setupCookieSecure ? 'https' : 'http';
	$defaultSiteUrl = $requestScheme . '://' . $requestHost . ($rootUrl !== '' ? $rootUrl : '');
	$progressRequest = $_SERVER['REQUEST_METHOD'] === 'POST'
		&& isset($_SERVER['HTTP_X_SETUP_PROGRESS'])
		&& $_SERVER['HTTP_X_SETUP_PROGRESS'] === '1';
	$emitProgress = function (array $payload) use ($progressRequest) {
		if (!$progressRequest) { return; }
		echo json_encode(
			AveSetupLocale::translatePayload($payload),
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		) . "\n";
		@ob_flush();
		flush();
	};
	if ($progressRequest) {
		header('Content-Type: application/x-ndjson; charset=UTF-8');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		header('X-Accel-Buffering: no');
		@ini_set('output_buffering', '0');
		@ini_set('zlib.output_compression', '0');
		while (ob_get_level() > 0) { @ob_end_flush(); }
		ob_implicit_flush(true);
	}

	$defaults = array(
		'dbhost' => $existingConfig && isset($existingConfig['dbhost']) ? $existingConfig['dbhost'] : '127.0.0.1',
		'dbport' => $existingConfig && isset($existingConfig['dbport']) && $existingConfig['dbport'] ? $existingConfig['dbport'] : '3306',
		'dbname' => $existingConfig && isset($existingConfig['dbname']) ? $existingConfig['dbname'] : '',
		'dbuser' => $existingConfig && isset($existingConfig['dbuser']) ? $existingConfig['dbuser'] : '',
		'dbpref' => $existingConfig && isset($existingConfig['dbpref']) ? $existingConfig['dbpref'] : 'ave',
		'site_name' => AveSetupLocale::translate('Новый сайт на AVE.cms'),
		'admin_name' => AveSetupLocale::translate('Администратор'),
		'admin_login' => 'admin',
		'admin_email' => '',
		'admin_directory' => 'adminx',
		'site_url' => $defaultSiteUrl,
	);
	$resetPrefixRequested = $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['reset_prefix']);
	$resetPrefixConfirmation = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_prefix_confirmation'])
		? (string) $_POST['reset_prefix_confirmation']
		: '';
	$repositoryMode = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repository_mode'])
		? strtolower(trim((string) $_POST['repository_mode']))
		: (!empty($officialRepository['available']) ? 'official' : 'disabled');
	$customRepositoryProfile = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repository_profile'])
		? (string) $_POST['repository_profile']
		: '';

	if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($state['installed'])) {
		try {
			if ($setupSessionError !== '') {
				throw new RuntimeException($setupSessionError);
			}

			$token = isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : '';
			if (!hash_equals((string) $_SESSION['setup_csrf'], $token)) {
				throw new RuntimeException('Сессия установки устарела. Обновите страницу.');
			}

			if (!$requirementsPassed) {
				throw new RuntimeException('Сначала устраните обязательные ошибки окружения.');
			}

			$database = $existingConfig ?: array(
				'dbhost' => isset($_POST['dbhost']) ? $_POST['dbhost'] : '',
				'dbport' => isset($_POST['dbport']) ? $_POST['dbport'] : 3306,
				'dbname' => isset($_POST['dbname']) ? $_POST['dbname'] : '',
				'dbuser' => isset($_POST['dbuser']) ? $_POST['dbuser'] : '',
				'dbpass' => isset($_POST['dbpass']) ? $_POST['dbpass'] : '',
				'dbpref' => isset($_POST['dbpref']) ? $_POST['dbpref'] : 'ave',
			);
			$site = array(
				'site_name' => isset($_POST['site_name']) ? $_POST['site_name'] : '',
				'admin_name' => isset($_POST['admin_name']) ? $_POST['admin_name'] : '',
				'admin_login' => isset($_POST['admin_login']) ? $_POST['admin_login'] : '',
				'admin_email' => isset($_POST['admin_email']) ? $_POST['admin_email'] : '',
				'admin_password' => isset($_POST['admin_password']) ? $_POST['admin_password'] : '',
				'admin_directory' => isset($_POST['admin_directory']) ? $_POST['admin_directory'] : 'adminx',
				'admin_language' => $setupLanguage,
				'site_url' => isset($_POST['site_url']) ? $_POST['site_url'] : $defaultSiteUrl,
			);
			$repositories = $installer->validateRepositorySelection($repositoryMode, $customRepositoryProfile);
			$success = $installer->install($database, $site, array(
				'reset_prefix' => $resetPrefixRequested,
				'reset_prefix_confirmation' => $resetPrefixConfirmation,
				'repositories' => $repositories,
				'progress' => function (array $progress) use ($emitProgress) {
					$progress['type'] = 'progress';
					$emitProgress($progress);
				},
			));
			$_SESSION['setup_csrf'] = bin2hex(random_bytes(32));
			$adminDirectory = isset($success['admin_directory']) ? $success['admin_directory'] : 'adminx';
			$defaults = array_merge($defaults, array_intersect_key($site, $defaults));
			if ($progressRequest) {
				$emitProgress(array(
					'type' => 'complete',
					'summary' => $success,
					'admin_url' => $rootUrl . '/' . rawurlencode($adminDirectory) . '/login',
					'site_url' => $rootUrl . '/',
				));
				exit;
			}
		} catch (Throwable $e) {
			$error = $e->getMessage();
			if ($progressRequest) {
				$emitProgress(array('type' => 'error', 'message' => $error));
				exit;
			}

			foreach ($defaults as $key => $value) {
				if (isset($_POST[$key])) { $defaults[$key] = (string) $_POST[$key]; }
			}
		}
	}

	function setup_escape($value)
	{
		return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
	}

	function setup_asset_version($path)
	{
		$hash = is_file($path) ? hash_file('sha256', $path) : false;

		return $hash ? substr($hash, 0, 12) : 'missing';
	}

	$installerCssVersion = setup_asset_version(__DIR__ . '/assets/installer.css');
	$installerJsVersion = setup_asset_version(__DIR__ . '/assets/installer.js');

	ob_start();
	?>
<!doctype html>
<html lang="<?= setup_escape($setupLanguage) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Установка AVE.cms</title>
  <link rel="stylesheet" href="<?= setup_escape($base) ?>/assets/installer.css?v=<?= setup_escape($installerCssVersion) ?>">
	<script>window.AveSetupI18n=<?= json_encode(AveSetupLocale::clientTranslations()) ?>;</script>
	<script src="<?= setup_escape($base) ?>/assets/installer.js?v=<?= setup_escape($installerJsVersion) ?>" defer></script>
</head>
<body>
  <main class="installer-shell">
	    <header class="installer-header">
		  <img src="<?= setup_escape($rootUrl) ?>/<?= setup_escape(rawurlencode($adminDirectory)) ?>/assets/img/logo.svg" alt="AVE.cms" width="132" height="42">
	      <div class="installer-header-actions">
	        <div class="installer-version">AVE.cms <?= setup_escape($releaseVersion) ?> <span>build <?= setup_escape($releaseBuild) ?></span></div>
	        <nav class="installer-language" aria-label="Язык установщика"><?php foreach (AveSetupLocale::locales() as $code => $label): ?><a class="<?= $code === $setupLanguage ? 'is-active' : '' ?>" href="?lang=<?= setup_escape($code) ?>"><?= setup_escape(strtoupper($code)) ?></a><?php endforeach; ?></nav>
	      </div>
	    </header>

    <?php if (!empty($state['installed']) && !$success): ?>
      <section class="installer-card installer-complete">
        <span class="installer-state-icon">✓</span>
        <div>
          <p class="installer-eyebrow">Система настроена</p>
          <h1>Установщик заблокирован</h1>
		  <p><?= setup_escape($state['reason']) ?> Для повторной установки удалите локальные <code>configs/db.config.php</code> и <code>storage/installed.lock</code>. После этого можно выбрать свободный префикс либо очистить таблицы прежнего префикса в форме.</p>
          <div class="installer-actions">
			<a class="button button-primary" href="<?= setup_escape($rootUrl) ?>/<?= setup_escape(rawurlencode($adminDirectory)) ?>/">Открыть панель управления</a>
            <a class="button button-secondary" href="<?= setup_escape($rootUrl) ?>/">Перейти на сайт</a>
          </div>
        </div>
      </section>
    <?php elseif ($success): ?>
      <section class="installer-card installer-complete">
        <span class="installer-state-icon">✓</span>
        <div>
          <p class="installer-eyebrow">Готово</p>
          <h1>AVE.cms установлена</h1>
		  <p>Создано <?= (int) $success['tables'] ?> системных таблиц, один администратор, стартовая страница и служебная страница 404. Установленных модулей: <?= (int) $success['modules'] ?>.</p>
          <div class="installer-summary">
			<span><b>1</b> шаблон</span><span><b>1</b> рубрика</span><span><b>2</b> поля</span><span><b>2</b> документа</span>
          </div>
          <div class="installer-actions">
			<a class="button button-primary" href="<?= setup_escape($rootUrl) ?>/<?= setup_escape(rawurlencode($adminDirectory)) ?>/login">Войти в панель управления</a>
            <a class="button button-secondary" href="<?= setup_escape($rootUrl) ?>/">Проверить сайт</a>
          </div>
        </div>
      </section>
    <?php else: ?>
      <div class="installer-intro">
        <p class="installer-eyebrow">Чистая установка</p>
        <h1>Базовая система без модулей</h1>
        <p>Установщик создаст только ядро AVE.cms, первого администратора и минимальную публичную страницу. Товары, заказы, оплаты, формы, RSS и остальные пакеты останутся доступными для отдельной установки.</p>
      </div>

      <?php if ($error !== ''): ?>
        <div class="installer-alert installer-alert-error"><b>Установка остановлена</b><span><?= setup_escape($error) ?></span></div>
      <?php endif; ?>

	  <section class="installer-card installer-progress" hidden data-installer-progress aria-live="polite">
		<div class="installer-progress-head">
		  <span class="installer-progress-icon" data-progress-icon><i></i></span>
		  <div><p class="installer-eyebrow">Установка</p><h2 data-progress-title>Подготовка</h2><p data-progress-detail>Проверяем введённые данные</p></div>
		  <strong data-progress-percent>0%</strong>
		</div>
		<div class="installer-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" data-progress-track><span data-progress-bar></span></div>
		<div class="installer-progress-steps">
		  <div data-progress-step="8"><span>1</span><div><b>Проверка</b><small>Окружение и параметры</small></div></div>
		  <div data-progress-step="18"><span>2</span><div><b>Подключение</b><small>Доступ к базе данных</small></div></div>
		  <div data-progress-step="38"><span>3</span><div><b>Структура</b><small>Таблицы и индексы</small></div></div>
		  <div data-progress-step="70"><span>4</span><div><b>Данные</b><small>Настройки и документы</small></div></div>
		  <div data-progress-step="90"><span>5</span><div><b>Завершение</b><small>Конфигурация и кеш</small></div></div>
		</div>
		<div class="installer-progress-error" hidden data-progress-error><b>Установка остановлена</b><span data-progress-error-message></span><button class="button button-secondary" type="button" data-progress-back>Вернуться к форме</button></div>
	  </section>

	  <form method="post" class="installer-layout" autocomplete="off" data-installer-form>
        <input type="hidden" name="_csrf" value="<?= setup_escape($_SESSION['setup_csrf']) ?>">
        <input type="hidden" name="setup_language" value="<?= setup_escape($setupLanguage) ?>">

        <section class="installer-card installer-main">
          <div class="installer-section-head"><span>1</span><div><h2>Окружение</h2><p>Обязательные компоненты и права записи</p></div></div>
          <div class="requirements-list">
            <?php foreach ($requirements as $check): ?>
              <div class="requirement-row <?= $check['passed'] ? 'is-ok' : ($check['required'] ? 'is-error' : 'is-warning') ?>">
                <span class="requirement-mark"><?= $check['passed'] ? '✓' : ($check['required'] ? '×' : '!') ?></span>
                <span><?= setup_escape($check['label']) ?></span>
                <?php if ($check['detail'] !== ''): ?><small><?= setup_escape($check['detail']) ?></small><?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="installer-divider"></div>
          <div class="installer-section-head"><span>2</span><div><h2>База данных</h2><p>Пустой префикс в существующей или новой базе</p></div></div>
          <?php if ($existingConfig): ?>
            <div class="installer-alert installer-alert-info"><b>Подключение уже подготовлено</b><span><?= setup_escape($existingConfig['dbhost']) ?> / <?= setup_escape($existingConfig['dbname']) ?> / префикс <?= setup_escape($existingConfig['dbpref']) ?></span></div>
          <?php else: ?>
            <div class="form-grid">
              <label><span>Сервер</span><input name="dbhost" value="<?= setup_escape($defaults['dbhost']) ?>" required></label>
              <label><span>Порт</span><input name="dbport" type="number" value="<?= setup_escape($defaults['dbport']) ?>" min="1" max="65535" required></label>
              <label><span>База данных</span><input name="dbname" value="<?= setup_escape($defaults['dbname']) ?>" required></label>
              <label><span>Префикс</span><input name="dbpref" value="<?= setup_escape($defaults['dbpref']) ?>" pattern="[A-Za-z][A-Za-z0-9_]{0,39}" required></label>
              <label><span>Пользователь</span><input name="dbuser" value="<?= setup_escape($defaults['dbuser']) ?>" required></label>
              <label><span>Пароль БД</span><input name="dbpass" type="password" value=""></label>
            </div>
		  <?php endif; ?>

		  <div class="installer-danger-option<?= $resetPrefixRequested ? ' is-active' : '' ?>" data-prefix-reset data-database-name="<?= setup_escape($existingConfig ? $existingConfig['dbname'] : $defaults['dbname']) ?>" data-prefix="<?= setup_escape($existingConfig ? $existingConfig['dbpref'] : $defaults['dbpref']) ?>">
			<label class="installer-danger-toggle">
			  <input type="checkbox" name="reset_prefix" value="1" data-prefix-reset-toggle<?= $resetPrefixRequested ? ' checked' : '' ?>>
			  <span class="installer-danger-mark">!</span>
			  <span><b>Очистить таблицы префикса перед установкой</b><small>Удалить объекты <code data-prefix-reset-pattern><?= setup_escape(($existingConfig ? $existingConfig['dbpref'] : $defaults['dbpref']) . '_*') ?></code>, не затрагивая остальные таблицы базы.</small></span>
			</label>
			<div class="installer-danger-confirm" data-prefix-reset-confirm<?= $resetPrefixRequested ? '' : ' hidden' ?>>
			  <div class="installer-danger-warning"><b>Данные префикса будут удалены безвозвратно</b><p>В базе <code data-prefix-reset-database><?= setup_escape($existingConfig ? $existingConfig['dbname'] : $defaults['dbname']) ?></code> останутся все таблицы и представления, имена которых не начинаются с выбранного префикса.</p></div>
			  <label><span>Введите точный префикс <code data-prefix-reset-name><?= setup_escape($existingConfig ? $existingConfig['dbpref'] : $defaults['dbpref']) ?></code></span><input name="reset_prefix_confirmation" value="<?= setup_escape($resetPrefixConfirmation) ?>" autocomplete="off" spellcheck="false" data-prefix-reset-input></label>
			</div>
		  </div>

		  <div class="installer-divider"></div>
          <div class="installer-section-head"><span>3</span><div><h2>Сайт и администратор</h2><p>Единственная учётная запись новой системы</p></div></div>
          <div class="form-grid">
			<label class="form-span-2"><span>Название сайта</span><input name="site_name" value="<?= setup_escape($defaults['site_name']) ?>" required></label>
			<label class="form-span-2"><span>Публичный адрес сайта</span><input name="site_url" type="url" value="<?= setup_escape($defaults['site_url']) ?>" required><small>Полный адрес с https://. Он используется для проверки домена и защищённых ссылок в письмах.</small></label>
            <label><span>Имя администратора</span><input name="admin_name" value="<?= setup_escape($defaults['admin_name']) ?>" required></label>
            <label><span>Логин</span><input name="admin_login" value="<?= setup_escape($defaults['admin_login']) ?>" required></label>
			<label><span>Email</span><input name="admin_email" type="email" value="<?= setup_escape($defaults['admin_email']) ?>" required></label>
			<label><span>Пароль</span><input name="admin_password" type="password" minlength="<?= (int) $adminPasswordMinLength ?>" required><small><?= setup_escape(sprintf(AveSetupLocale::translate('Не менее %d символов'), (int) $adminPasswordMinLength)) ?></small></label>
			<label class="form-span-2"><span>Папка панели управления</span><input name="admin_directory" value="<?= setup_escape($defaults['admin_directory']) ?>" pattern="[A-Za-z][A-Za-z0-9_\-]{0,47}" maxlength="48" required><small>По умолчанию adminx. Можно изменить до установки; системные имена использовать нельзя.</small></label>
          </div>

		  <div class="installer-divider"></div>
		  <div class="installer-section-head"><span>4</span><div><h2>Обновления и каталог модулей</h2><p>Источник можно изменить после установки; автоматическая установка выключена</p></div></div>
		  <div class="installer-repository-options" data-repository-options>
			<label class="installer-repository-option<?= $repositoryMode === 'official' ? ' is-selected' : '' ?><?= empty($officialRepository['available']) ? ' is-disabled' : '' ?>">
			  <input type="radio" name="repository_mode" value="official"<?= $repositoryMode === 'official' ? ' checked' : '' ?><?= empty($officialRepository['available']) ? ' disabled' : '' ?>>
			  <span class="installer-repository-icon" aria-hidden="true">A</span>
			  <span class="installer-repository-copy"><b>Официальный канал AVE.cms</b><small>Обновления ядра и подписанные модули. URL и открытые ключи уже находятся в сборке.</small><?php if (empty($officialRepository['available'])): ?><small class="installer-repository-error"><?= setup_escape($officialRepository['error']) ?></small><?php endif; ?></span>
			  <em class="installer-repository-badge">Рекомендуется</em>
			</label>
			<label class="installer-repository-option<?= $repositoryMode === 'custom' ? ' is-selected' : '' ?>">
			  <input type="radio" name="repository_mode" value="custom"<?= $repositoryMode === 'custom' ? ' checked' : '' ?>>
			  <span class="installer-repository-icon" aria-hidden="true">JSON</span>
			  <span class="installer-repository-copy"><b>Другой источник</b><small>Вставьте единый профиль подключения, который предоставил издатель системы.</small></span>
			</label>
			<label class="installer-repository-option<?= $repositoryMode === 'disabled' ? ' is-selected' : '' ?>">
			  <input type="radio" name="repository_mode" value="disabled"<?= $repositoryMode === 'disabled' ? ' checked' : '' ?>>
			  <span class="installer-repository-icon" aria-hidden="true">×</span>
			  <span class="installer-repository-copy"><b>Не подключать</b><small>Система останется автономной. Локальная установка ZIP-пакетов продолжит работать.</small></span>
			</label>
		  </div>
		  <div class="installer-repository-profile" data-repository-profile<?= $repositoryMode === 'custom' ? '' : ' hidden' ?>>
			<label><span>Профиль подключения JSON</span><textarea name="repository_profile" rows="11" spellcheck="false" placeholder='{"format":"ave-repository-profile-v1","name":"Мой источник","core_updates":{"url":"https://…/index.json","public_key":"-----BEGIN PUBLIC KEY-----…"},"modules":{"url":"https://…/index.json","public_key":"-----BEGIN PUBLIC KEY-----…"}}'><?= setup_escape($customRepositoryProfile) ?></textarea><small>Публичные ключи не являются паролями. Они нужны только для проверки подписи скачанных каталогов.</small></label>
		  </div>

          <footer class="installer-footer">
            <div><b>Модули не устанавливаются</b><span>Пакеты останутся в состоянии «Доступен».</span></div>
			<button class="button button-primary" type="submit" <?= $requirementsPassed ? '' : 'disabled' ?> data-installer-submit>Установить AVE.cms</button>
          </footer>
        </section>

        <aside class="installer-card installer-aside">
          <p class="installer-eyebrow">Результат</p>
          <h2>Минимальный каркас</h2>
          <ul class="installer-checklist">
            <li><span>✓</span> Схема ядра InnoDB</li>
            <li><span>✓</span> Один системный администратор</li>
            <li><span>✓</span> Пустые публичные аккаунты</li>
            <li><span>✓</span> Основной шаблон</li>
			<li><span>✓</span> Рубрика с заголовком и richtext-полем</li>
            <li><span>✓</span> Проверочный документ</li>
			<li><span>✓</span> Служебная страница 404</li>
            <li><span>✓</span> Ноль установленных модулей</li>
          </ul>
          <div class="installer-note"><b>После установки</b><p>Установщик создаст lock-файл. Повторный запуск не сможет изменить рабочую систему.</p></div>
        </aside>
      </form>

	  <template data-installer-complete-template>
		<section class="installer-card installer-complete">
		  <span class="installer-state-icon">✓</span>
		  <div>
			<p class="installer-eyebrow">Готово</p>
			<h1>AVE.cms установлена</h1>
			<p>Созданы системные таблицы, один администратор, стартовая страница и служебная страница 404. Модули не устанавливались.</p>
			<p class="installer-complete-repository" data-complete-repository></p>
			<div class="installer-summary"><span><b>1</b> шаблон</span><span><b>1</b> рубрика</span><span><b>2</b> поля</span><span><b>2</b> документа</span></div>
			<div class="installer-actions"><a class="button button-primary" data-complete-admin>Войти в панель управления</a><a class="button button-secondary" data-complete-site>Проверить сайт</a></div>
		  </div>
		</section>
	  </template>
    <?php endif; ?>
  </main>
</body>
</html>
<?php
	echo AveSetupLocale::translate(ob_get_clean());
