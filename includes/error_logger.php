<?php
require_once __DIR__ . '/constants.php';

class ErrorLogger {
	/**
	 * Post an error log to the central error logging service.
	 * Attempts to extract user_id from Authorization Bearer JWT if present.
	 * This function must never throw; failures are swallowed to avoid impacting API responses.
	 *
	 * @param string $message Error message
	 * @param int $code HTTP status code associated with the error
	 * @param array|null $data Optional extra data (e.g., validation errors)
	 * @param string $status Either 'error' or other status label
	 */
	public static function log(string $message, int $code, ?array $data = null, string $status = 'error'): void {
		try {
			// Try to enrich with authenticated user context from JWT
			$userId = '001';
			$appId = 'my_first';
			try {
				$headers = function_exists('getallheaders') ? getallheaders() : [];
				$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
				if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $m)) {
					$token = trim($m[1]);
					// Lazy-load config and JWT helper only when needed
					$config = require_config();
					require_from(INCLUDES_DIR . '/jwt_helper.php');
					$jwtHelper = new JwtHelper($config);
					$payload = $jwtHelper->validateToken($token);
					if (is_array($payload)) {
						if (!empty($payload['sub'])) {
							$userId = (string) $payload['sub'];
						}
						if (!empty($payload['app_id'])) {
							$appId = (string) $payload['app_id'];
						}
					}
				}
			} catch (\Throwable $jwtIgnored) {
				// Ignore JWT enrichment failures
			}

			// Build payload for error logging service (match required schema types)
			$orgId = 'first_api';
			$productId = 1;
			$userIdInt = is_numeric($userId) ? (int) $userId : 0;
			$payload = [
				// canonical (lowercase) keys matching DB column names
				'user_id' => $userIdInt,
				'organization_id' => (string) $orgId,
				'product_id' => (int) $productId,
				'status' => strtolower($status),
				'message' => $message,
				'code' => (int) $code,
				'data' => $data,
				'timestamp' => date('Y-m-d H:i:s'),
				// uppercase aliases for compatibility if the microservice expects them
				'STATUS' => strtolower($status),
				'MESSAGE' => $message,
				'CODE' => (int) $code,
				'DATA' => $data,
				'TIMESTAMP' => date('Y-m-d H:i:s'),
			];

			// Enrich with minimal request context
			$payload['DATA'] = $payload['DATA'] ?? [];
			if (is_array($payload['DATA'])) {
				$payload['DATA'] = array_merge($payload['DATA'], [
					'request' => [
						'method' => $_SERVER['REQUEST_METHOD'] ?? null,
						'uri' => $_SERVER['REQUEST_URI'] ?? null,
						'query_string' => $_SERVER['QUERY_STRING'] ?? null,
						'client_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
					],
				]);
			}

			$url = 'https://xivra.pk/error_log/api/v1/error_logs/create_log.php';

			// Ensure minimal required schema for the error_logs microservice
			// Flatten DATA if it's deeply nested to avoid rejection by remote service
			if (isset($payload['DATA']) && is_array($payload['DATA'])) {
				// Limit nesting depth and size
				$payload['DATA'] = json_decode(json_encode($payload['DATA'], JSON_PARTIAL_OUTPUT_ON_ERROR), true);
			}

			$jsonBody = json_encode($payload);
			
			// Debug: Log what we're trying to send
			error_log('ErrorLogger attempting to send to: ' . $url);
			error_log('ErrorLogger payload: ' . $jsonBody);

			if (function_exists('curl_init')) {
				// Try JSON first
				$ch = curl_init($url);
				$headers = [
					'Content-Type: application/json',
					'Accept: application/json',
				];
				curl_setopt_array($ch, [
					CURLOPT_POST => true,
					CURLOPT_HTTPHEADER => $headers,
					CURLOPT_POSTFIELDS => $jsonBody,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_CONNECTTIMEOUT => 3,
					CURLOPT_TIMEOUT => 6,
					CURLOPT_USERAGENT => '1st_project-error-logger/1.0',
				]);
				// HTTPS handling: verify by default; allow opt-out via env for testing
				if (stripos($url, 'https://') === 0) {
					$insecure = getenv('ERROR_LOG_INSECURE');
					if ($insecure === '1') {
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					}
				}
				$response = curl_exec($ch);
				$httpCode = $response === false ? 0 : (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
				$curlError = curl_error($ch);
				if ($response === false) {
					error_log('ErrorLogger cURL failed (JSON): ' . $curlError);
				}
				// Log response for diagnostics regardless of code
				error_log('ErrorLogger response (JSON): HTTP ' . $httpCode . ' body: ' . substr((string)$response, 0, 500));
				error_log('ErrorLogger cURL info: ' . json_encode(curl_getinfo($ch)));
				curl_close($ch);

				// Fallback: application/x-www-form-urlencoded if JSON failed or non-2xx
				if ($response === false || $httpCode < 200 || $httpCode >= 300) {
					$form = $payload;
					if (isset($form['DATA']) && is_array($form['DATA'])) {
						$form['DATA'] = json_encode($form['DATA']);
					}
					$ch2 = curl_init($url);
					curl_setopt_array($ch2, [
						CURLOPT_POST => true,
						CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'],
						CURLOPT_POSTFIELDS => http_build_query($form),
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_CONNECTTIMEOUT => 3,
						CURLOPT_TIMEOUT => 6,
						CURLOPT_USERAGENT => '1st_project-error-logger/1.0',
					]);
					if (stripos($url, 'https://') === 0) {
						$insecure2 = getenv('ERROR_LOG_INSECURE');
						if ($insecure2 === '1') {
							curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
							curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, 0);
						}
					}
					$response2 = curl_exec($ch2);
					$curlError2 = curl_error($ch2);
					if ($response2 === false) {
						error_log('ErrorLogger cURL failed (FORM): ' . $curlError2);
					}
					$httpCode2 = $response2 === false ? 0 : (int) curl_getinfo($ch2, CURLINFO_HTTP_CODE);
					// Log response for diagnostics regardless of code
					error_log('ErrorLogger response (FORM): HTTP ' . $httpCode2 . ' body: ' . substr((string)$response2, 0, 500));
					error_log('ErrorLogger cURL info (FORM): ' . json_encode(curl_getinfo($ch2)));
					curl_close($ch2);
				}
			} else {
				// Fallback without cURL: use stream context
				$context = stream_context_create([
					'http' => [
						'method' => 'POST',
						'header' => "Content-Type: application/json\r\nAccept: application/json\r\nUser-Agent: 1st_project-error-logger/1.0\r\n",
						'content' => $jsonBody,
						'timeout' => 6,
					]
				]);
				$resp = @file_get_contents($url, false, $context);
				if ($resp === false) {
					error_log('ErrorLogger fopen failed for URL: ' . $url);
				}
			}
		} catch (\Throwable $e) {
			// Swallow any logging issues
		}
	}
}


