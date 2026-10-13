<?php

	/*
	|--------------------------------------------------------------------------------------
	| AVE.cms
	|--------------------------------------------------------------------------------------
	| @package      AVE.cms
	| @file         system/App/Common/OutboundHttpClient.php
	| @author       AVE.cms <support@ave-cms.ru>
	| @copyright    2007-2026 (c) AVE.cms
	| @link         https://ave-cms.ru
	| @version      3.3
	*/

	namespace App\Common;

	defined('BASEPATH') || die('Direct access to this location is not allowed.');

	/** DNS-pinned HTTP client for server-side requests to untrusted URLs. */
	class OutboundHttpClient
	{
		const DEFAULT_MAX_BYTES = 2097152;
		protected static $dnsCache = array();

		public static function get($url, array $options = array())
		{
			return self::request('GET', $url, null, $options);
		}

		public static function post($url, $body, array $options = array())
		{
			return self::request('POST', $url, $body, $options);
		}

		public static function head($url, array $options = array())
		{
			return self::request('HEAD', $url, null, $options);
		}

		/** Download a checked response without keeping it in PHP memory. */
		public static function download($url, $destination, array $options = array())
		{
			$destination = (string) $destination;
			if ($destination === '' || !is_dir(dirname($destination))) {
				throw new \InvalidArgumentException('Некорректный путь для сохранения удалённого файла');
			}

			$options['download_path'] = $destination;
			@unlink($destination);
			try {
				$response = self::request('GET', $url, null, $options);
				if (!is_file($destination) || (int) filesize($destination) !== (int) $response['bytes']) {
					throw new \RuntimeException('Удалённый файл сохранён не полностью');
				}

				return $response;
			} catch (\Throwable $e) {
				@unlink($destination);
				throw $e;
			}
		}

		public static function inspectUrl($url, array $options = array())
		{
			$options = self::options($options);
			return self::resolve((string) $url, $options);
		}

		public static function isPublicIp($ip)
		{
			return filter_var(
				(string) $ip,
				FILTER_VALIDATE_IP,
				FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
			) !== false;
		}

		/** Private ranges allowed only for an explicitly trusted local endpoint. */
		public static function isPrivateNetworkIp($ip)
		{
			$ip = (string) $ip;
			if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
				$long = ip2long($ip);
				if ($long === false) { return false; }
				$long = (float) sprintf('%u', $long);
				return ($long >= 167772160 && $long <= 184549375)
					|| ($long >= 2886729728 && $long <= 2887778303)
					|| ($long >= 3232235520 && $long <= 3232301055);
			}

			if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
				$packed = @inet_pton($ip);
				return is_string($packed) && strlen($packed) === 16
					&& (ord($packed[0]) & 0xfe) === 0xfc;
			}

			return false;
		}

		protected static function request($method, $url, $body, array $options)
		{
			if (!function_exists('curl_init')) {
				throw new \RuntimeException('Для исходящих HTTP-запросов требуется расширение cURL');
			}

			$options = self::options($options);
			$method = strtoupper((string) $method);
			if (!in_array($method, array('GET', 'POST', 'HEAD'), true)) {
				throw new \InvalidArgumentException('Неподдерживаемый метод исходящего HTTP-запроса');
			}

			$currentUrl = (string) $url;
			$currentMethod = $method;
			$currentBody = $body;
			$currentHeaders = $options['headers'];
			$redirectCookies = array();
			$started = microtime(true);
			$requestId = substr(hash('sha256', $method . '|' . $currentUrl . '|' . microtime(true)), 0, 24);
			self::observe('http.outbound.requesting', 'requesting', $requestId, array(
				'method' => $method,
				'url' => self::safeUrl($currentUrl),
				'body_bytes' => $body === null ? 0 : strlen((string) $body),
				'max_bytes' => $options['max_bytes'],
			), null, $options['source']);
			try {
				for ($redirect = 0; $redirect <= $options['max_redirects']; $redirect++) {
					$target = self::resolve($currentUrl, $options);
					$response = self::execute($currentMethod, $target, $currentBody, $currentHeaders, $options);
					if ($response['status'] < 300 || $response['status'] >= 400) {
					if (!$options['allow_http_errors'] && ($response['status'] < 200 || $response['status'] >= 300)) {
							throw new \RuntimeException('Удалённый сервер вернул HTTP ' . $response['status']);
						}

						$response['url'] = $target['url'];
						self::observe('http.outbound.responded', 'responded', $requestId, array(
							'method' => $currentMethod,
							'url' => self::safeUrl($target['url']),
							'status' => (int) $response['status'],
							'bytes' => (int) $response['bytes'],
							'redirects' => $redirect,
							'duration_ms' => (int) round((microtime(true) - $started) * 1000),
						), array('status' => (int) $response['status'], 'bytes' => (int) $response['bytes']), $options['source']);
						unset($response['set_cookies']);
						return $response;
					}

					if ($redirect >= $options['max_redirects'] || empty($response['headers']['location'])) {
						throw new \RuntimeException('Удалённый сервер вернул запрещённое перенаправление');
					}

					$nextUrl = self::redirectUrl($target['url'], (string) $response['headers']['location']);
					$nextTarget = self::resolve($nextUrl, $options);
					$sameOrigin = self::origin($target) === self::origin($nextTarget);
					if (!$sameOrigin && $options['same_origin_redirects']) {
						throw new \RuntimeException('Удалённый сервер перенаправил запрос на другой хост');
					}

					if (!$sameOrigin) {
						$currentHeaders = self::withoutSensitiveHeaders($currentHeaders);
						$redirectCookies = array();
					} elseif ($options['redirect_cookies']) {
						foreach (self::redirectCookiePairs($response['set_cookies']) as $name => $value) {
							$redirectCookies[$name] = $value;
						}

						if ($redirectCookies) {
							$currentHeaders = self::withCookieHeader($currentHeaders, $redirectCookies);
						}
					}

					$currentUrl = $nextTarget['url'];
					if ($response['status'] === 303 || (($response['status'] === 301 || ($response['status'] === 302 && !$options['preserve_post_on_302'])) && $currentMethod === 'POST')) {
						$currentMethod = 'GET';
						$currentBody = null;
					}
				}

				throw new \RuntimeException('Превышен лимит перенаправлений');
			} catch (\Throwable $e) {
				self::observe('http.outbound.failed', 'failed', $requestId, array(
					'method' => $currentMethod,
					'url' => self::safeUrl($currentUrl),
					'error' => get_class($e),
					'message' => self::safeMessage($e->getMessage()),
					'duration_ms' => (int) round((microtime(true) - $started) * 1000),
				), null, $options['source']);
				throw $e;
			}
		}

		protected static function execute($method, array $target, $body, array $headers, array $options)
		{
			$buffer = '';
			$bytes = 0;
			$overflow = false;
			$responseHeaders = array();
			$setCookies = array();
			$headerOverflow = false;
			$file = null;
			if ($options['download_path'] !== '' && $method !== 'HEAD') {
				$file = @fopen($options['download_path'], 'wb');
				if (!$file) { throw new \RuntimeException('Не удалось создать временный файл'); }
			}

			$handle = curl_init($target['url']);
			$resolveIp = strpos($target['ip'], ':') !== false ? '[' . $target['ip'] . ']' : $target['ip'];
			$curlOptions = array(
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_CONNECTTIMEOUT => $options['connect_timeout'],
				CURLOPT_TIMEOUT => $options['timeout'],
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_SSL_VERIFYHOST => 2,
				CURLOPT_FAILONERROR => false,
				CURLOPT_ENCODING => '',
				CURLOPT_NOSIGNAL => true,
				CURLOPT_PROXY => '',
				CURLOPT_USERAGENT => $options['user_agent'],
				CURLOPT_HTTPHEADER => $headers,
				CURLOPT_RESOLVE => array($target['host'] . ':' . $target['port'] . ':' . $resolveIp),
				CURLOPT_HEADERFUNCTION => function ($curl, $line) use (&$responseHeaders, &$setCookies, &$headerOverflow, $options) {
					$length = strlen($line);
					if (stripos($line, 'HTTP/') === 0) {
						$responseHeaders = array();
						$setCookies = array();
						return $length;
					}

					$position = strpos($line, ':');
					if ($position === false) {
						return $length;
					}

					$name = strtolower(trim(substr($line, 0, $position)));
					$value = trim(substr($line, $position + 1));
					if ($name === 'set-cookie') { $setCookies[] = $value; }
					if ($name === 'content-length' && ctype_digit($value) && (int) $value > $options['max_bytes']) {
						$headerOverflow = true;
						return 0;
					}

					$responseHeaders[$name] = $value;
					return $length;
				},
				CURLOPT_WRITEFUNCTION => function ($curl, $chunk) use (&$buffer, &$bytes, &$overflow, $options, $file) {
					if (!is_resource($file)) {
						return self::appendChunk($buffer, $bytes, $overflow, $chunk, $options['max_bytes']);
					}

					$length = strlen($chunk);
					$bytes += $length;
					if ($bytes > $options['max_bytes']) { $overflow = true; return 0; }
					return fwrite($file, $chunk);
				},
			);
			if (defined('CURLOPT_PROTOCOLS')) {
				$protocols = 0;
				if (in_array('http', $options['allowed_schemes'], true)) { $protocols |= CURLPROTO_HTTP; }
				if (in_array('https', $options['allowed_schemes'], true)) { $protocols |= CURLPROTO_HTTPS; }
				$curlOptions[CURLOPT_PROTOCOLS] = $protocols;
			}

			if ($method === 'POST') {
				$curlOptions[CURLOPT_POST] = true;
				$curlOptions[CURLOPT_POSTFIELDS] = (string) $body;
			}

			if ($method === 'HEAD') { $curlOptions[CURLOPT_NOBODY] = true; }

			curl_setopt_array($handle, $curlOptions);
			$result = curl_exec($handle);
			$status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
			$primaryIp = (string) curl_getinfo($handle, CURLINFO_PRIMARY_IP);
			$error = curl_error($handle);
			curl_close($handle);
			if (is_resource($file)) { fclose($file); }
			if ($overflow || $headerOverflow) {
				throw new \RuntimeException('Удалённый ответ превышает допустимый размер');
			}

			if ($result === false) {
				throw new \RuntimeException('Не удалось получить удалённый ответ' . ($error !== '' ? ': ' . $error : ''));
			}

			if (!self::sameIp($primaryIp, $target['ip'])) {
				throw new \RuntimeException('Удалённый сервер изменил проверенный сетевой адрес');
			}

			return array('status' => $status, 'headers' => $responseHeaders, 'set_cookies' => $setCookies, 'body' => $buffer, 'bytes' => $bytes);
		}

		protected static function resolve($url, array $options)
		{
			$url = trim((string) $url);
			if ($url === '' || strlen($url) > 2048 || preg_match('/[\x00-\x20\x7f]/', $url)) {
				throw new \InvalidArgumentException('Некорректный URL исходящего запроса');
			}

			$parts = parse_url($url);
			if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])) {
				throw new \InvalidArgumentException('Некорректный URL исходящего запроса');
			}

			$scheme = strtolower((string) $parts['scheme']);
			if (!in_array($scheme, $options['allowed_schemes'], true)) {
				throw new \InvalidArgumentException('Схема удалённого URL запрещена');
			}

			$host = self::asciiHost((string) $parts['host']);
			$port = isset($parts['port']) ? (int) $parts['port'] : ($scheme === 'https' ? 443 : 80);
			if (!in_array($port, $options['allowed_ports'], true)) {
				throw new \InvalidArgumentException('Порт удалённого URL запрещён');
			}

			$ips = self::hostAddresses($host);
			if (!$ips) {
				throw new \RuntimeException('Удалённый хост не имеет доступного адреса');
			}

			$trustedPrivateHost = in_array($host, $options['allowed_private_hosts'], true);
			foreach ($ips as $ip) {
				if (!self::isPublicIp($ip) && (!$trustedPrivateHost || !self::isPrivateNetworkIp($ip))) {
					throw new \RuntimeException('Удалённый хост указывает на private или reserved сеть');
				}
			}

			$path = isset($parts['path']) && $parts['path'] !== '' ? (string) $parts['path'] : '/';
			$query = isset($parts['query']) && $parts['query'] !== '' ? '?' . (string) $parts['query'] : '';
			$hostForUrl = strpos($host, ':') !== false ? '[' . $host . ']' : $host;
			$portPart = ($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80) ? '' : ':' . $port;
			return array(
				'url' => $scheme . '://' . $hostForUrl . $portPart . $path . $query,
				'scheme' => $scheme,
				'host' => $host,
				'port' => $port,
				'ip' => $ips[0],
			);
		}

		protected static function hostAddresses($host)
		{
			if (filter_var($host, FILTER_VALIDATE_IP)) {
				return array($host);
			}

			if (isset(self::$dnsCache[$host])) {
				return self::$dnsCache[$host];
			}

			$addresses = array();
			if (function_exists('dns_get_record')) {
				$records = @dns_get_record($host, DNS_A | DNS_AAAA);
				foreach (is_array($records) ? $records : array() as $record) {
					if (!empty($record['ip'])) { $addresses[] = (string) $record['ip']; }
					if (!empty($record['ipv6'])) { $addresses[] = (string) $record['ipv6']; }
				}
			}

			if (!$addresses) {
				$ipv4 = @gethostbynamel($host);
				$addresses = is_array($ipv4) ? $ipv4 : array();
			}

			$addresses = array_values(array_unique(array_filter($addresses, function ($ip) {
				return filter_var($ip, FILTER_VALIDATE_IP) !== false;
			})));
			usort($addresses, function ($first, $second) {
				return (strpos($first, ':') === false ? 0 : 1) - (strpos($second, ':') === false ? 0 : 1);
			});
			self::$dnsCache[$host] = $addresses;
			return $addresses;
		}

		protected static function appendChunk(&$buffer, &$bytes, &$overflow, $chunk, $maxBytes)
		{
			$length = strlen((string) $chunk);
			if ((int) $bytes + $length > (int) $maxBytes) {
				$overflow = true;
				return 0;
			}

			$bytes += $length;
			$buffer .= $chunk;
			return $length;
		}

		protected static function asciiHost($host)
		{
			$host = strtolower(rtrim(trim((string) $host), '.'));
			if ($host === '' || strlen($host) > 253) {
				throw new \InvalidArgumentException('Некорректный хост удалённого URL');
			}

			if (filter_var($host, FILTER_VALIDATE_IP)) {
				return $host;
			}

			if (preg_match('/[^\x20-\x7e]/', $host)) {
				if (!function_exists('idn_to_ascii')) {
					throw new \InvalidArgumentException('IDN-хост не поддерживается сервером');
				}

				$variant = defined('INTL_IDNA_VARIANT_UTS46') ? INTL_IDNA_VARIANT_UTS46 : 0;
				$host = idn_to_ascii($host, 0, $variant);
			}

			if (!is_string($host) || !preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/', $host)) {
				throw new \InvalidArgumentException('Некорректный хост удалённого URL');
			}

			return $host;
		}

		protected static function redirectUrl($base, $location)
		{
			$location = trim((string) $location);
			if ($location === '' || preg_match('/[\x00-\x20\x7f]/', $location)) {
				throw new \RuntimeException('Удалённый сервер вернул некорректное перенаправление');
			}

			if (preg_match('#^https?://#i', $location)) {
				return $location;
			}

			$parts = parse_url($base);
			if (strpos($location, '//') === 0) {
				return $parts['scheme'] . ':' . $location;
			}

			$origin = $parts['scheme'] . '://' . (strpos($parts['host'], ':') !== false ? '[' . $parts['host'] . ']' : $parts['host']);
			if (isset($parts['port'])) { $origin .= ':' . (int) $parts['port']; }
			if ($location[0] === '/') {
				return $origin . $location;
			}

			if ($location[0] === '?') {
				return $origin . (isset($parts['path']) ? $parts['path'] : '/') . $location;
			}

			$directory = isset($parts['path']) ? dirname($parts['path']) : '/';
			$segments = explode('/', trim($directory . '/' . $location, '/'));
			$normalized = array();
			foreach ($segments as $segment) {
				if ($segment === '' || $segment === '.') { continue; }
				if ($segment === '..') { array_pop($normalized); continue; }
				$normalized[] = $segment;
			}

			return $origin . '/' . implode('/', $normalized);
		}

		protected static function options(array $options)
		{
			$options = array_merge(array(
				'allowed_schemes' => array('https'),
				'allowed_ports' => array(443),
				'connect_timeout' => 5,
				'timeout' => 20,
				'max_redirects' => 0,
				'max_bytes' => self::DEFAULT_MAX_BYTES,
				'headers' => array(),
				'user_agent' => 'AVE.cms/' . (defined('APP_VERSION') ? APP_VERSION : '3.3') . ' OutboundHttpClient',
				'source' => 'runtime',
				'download_path' => '',
				'allow_http_errors' => false,
				'preserve_post_on_302' => false,
				'same_origin_redirects' => false,
				'redirect_cookies' => false,
				'allowed_private_hosts' => array(),
			), $options);
			$options['allowed_schemes'] = array_values(array_intersect(array('http', 'https'), array_map('strtolower', (array) $options['allowed_schemes'])));
			$options['allowed_ports'] = array_values(array_unique(array_map('intval', (array) $options['allowed_ports'])));
			$options['connect_timeout'] = max(1, min(30, (int) $options['connect_timeout']));
			$options['timeout'] = max($options['connect_timeout'], min(120, (int) $options['timeout']));
			$options['max_redirects'] = max(0, min(5, (int) $options['max_redirects']));
			$options['max_bytes'] = max(1, min(104857600, (int) $options['max_bytes']));
			$options['download_path'] = isset($options['download_path']) ? (string) $options['download_path'] : '';
			$options['allow_http_errors'] = !empty($options['allow_http_errors']);
			$options['preserve_post_on_302'] = !empty($options['preserve_post_on_302']);
			$options['same_origin_redirects'] = !empty($options['same_origin_redirects']);
			$options['redirect_cookies'] = !empty($options['redirect_cookies']);
			$options['allowed_private_hosts'] = array_values(array_unique(array_filter(array_map(function ($host) {
				try { return self::asciiHost($host); } catch (\Throwable $e) { return ''; }
			}, (array) $options['allowed_private_hosts']))));
			$options['headers'] = self::headers((array) $options['headers']);
			$options['source'] = substr(
				preg_replace('/[^a-z0-9_.-]+/', '_', strtolower(trim((string) $options['source']))),
				0,
				64
			);
			if ($options['source'] === '') { $options['source'] = 'runtime'; }
			if (!$options['allowed_schemes'] || !$options['allowed_ports']) {
				throw new \InvalidArgumentException('Не заданы разрешённые схемы или порты исходящего запроса');
			}

			return $options;
		}

		protected static function safeUrl($url)
		{
			$parts = parse_url((string) $url);
			if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
				return '';
			}

			$port = isset($parts['port']) ? ':' . (int) $parts['port'] : '';
			return strtolower((string) $parts['scheme']) . '://' . strtolower((string) $parts['host']) . $port;
		}

		protected static function safeMessage($message)
		{
			$message = preg_replace('#https?://[^\s]+#i', '[remote-url]', (string) $message);
			return function_exists('mb_substr')
				? mb_substr(trim($message), 0, 300, 'UTF-8')
				: substr(trim($message), 0, 300);
		}

		protected static function observe($name, $operation, $identifier, array $data, $result, $source)
		{
			try {
				Lifecycle::event(
					$name,
					'outbound_http',
					$operation,
					$identifier,
					$data,
					$result,
					array('sensitive_data_stored' => false),
					$source
				);
			} catch (\Throwable $e) {
				error_log('Outbound HTTP observer: ' . self::safeMessage($e->getMessage()));
			}
		}

		protected static function headers(array $headers)
		{
			$result = array();
			foreach ($headers as $header) {
				$header = trim((string) $header);
				if ($header !== '' && strpos($header, ':') !== false && !preg_match('/[\r\n]/', $header)) {
					$result[] = $header;
				}
			}

			return $result;
		}

		protected static function withoutSensitiveHeaders(array $headers)
		{
			return array_values(array_filter($headers, function ($header) {
				return !preg_match('/^(?:authorization|cookie|proxy-authorization)\s*:/i', (string) $header);
			}));
		}

		protected static function redirectCookiePairs(array $headers)
		{
			$pairs = array();
			foreach ($headers as $header) {
				$part = trim(strtok((string) $header, ';'));
				$position = strpos($part, '=');
				if ($position === false) { continue; }
				$name = trim(substr($part, 0, $position));
				$value = trim(substr($part, $position + 1));
				if (!preg_match('/^[A-Za-z0-9!#$%&\x27*+.^_`|~-]+$/D', $name) || preg_match('/[\r\n;]/', $value)) { continue; }
				$pairs[$name] = $value;
			}

			return $pairs;
		}

		protected static function withCookieHeader(array $headers, array $cookies)
		{
			$headers = array_values(array_filter($headers, function ($header) {
				return !preg_match('/^cookie\s*:/i', (string) $header);
			}));
			$pairs = array();
			foreach ($cookies as $name => $value) { $pairs[] = $name . '=' . $value; }
			$headers[] = 'Cookie: ' . implode('; ', $pairs);
			return $headers;
		}

		protected static function origin(array $target)
		{
			return $target['scheme'] . '://' . $target['host'] . ':' . $target['port'];
		}

		protected static function sameIp($first, $second)
		{
			$first = @inet_pton((string) $first);
			$second = @inet_pton((string) $second);
			return $first !== false && $second !== false && hash_equals($first, $second);
		}
	}
