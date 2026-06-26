<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../includes/constants.php';
require_from(INCLUDES_DIR . '/response_helpers.php');
require_from(INCLUDES_DIR . '/jwt_helper.php');
require_from(HANDLERS_DIR . '/auth_handler.php');

$config = require_config();
$jwtHelper = new JwtHelper($config);

// Allow GET or POST requests
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($method, ['GET', 'POST'], true)) {
    ResponseHelper::sendError("Method not allowed. Expected: GET or POST", 405);
}

try {
    // Get Authorization header if available
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    $token = null;

    // Prefer Authorization: Bearer <token>
    if (!empty($authHeader)) {
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = trim($matches[1]);
        } elseif (substr_count($authHeader, '.') === 2) {
            // Fallback: header contains raw JWT without Bearer
            $token = trim($authHeader);
        }
    }

    // Fallbacks: query param, form-data, or JSON body
    if (!$token) {
        if ($method === 'GET') {
            if (!empty($_GET['token'])) {
                $token = trim((string)$_GET['token']);
            }
        } else { // POST
            if (!empty($_POST['token'])) {
                $token = trim((string)$_POST['token']);
            } else {
                $raw = file_get_contents('php://input');
                if ($raw) {
                    $json = json_decode($raw, true);
                    if (json_last_error() === JSON_ERROR_NONE && !empty($json['token'])) {
                        $token = trim((string)$json['token']);
                    }
                }
            }
        }
    }

    if (empty($token)) {
        ResponseHelper::sendError('Token is required. Provide via Authorization: Bearer <token> header, ?token= query param, form-data token, or JSON {"token": "..."}.', 401);
    }

    // Validate the token
    $payload = $jwtHelper->validateToken($token);

    if (!$payload) {
        ResponseHelper::sendError('Invalid or expired token', 401);
    }

    // Check if token has required fields
    if (!isset($payload['sub']) || !isset($payload['username'])) {
        ResponseHelper::sendError('Token payload is invalid', 401);
    }

    // Check if token is expired
    if (isset($payload['exp']) && time() > $payload['exp']) {
        ResponseHelper::sendError('Token has expired', 401);
    }

    // Prepare response data (exclude sensitive information)
    $token_info = [
        'valid' => true,
        'user_id' => $payload['sub'],
        'username' => $payload['username'],
        'email' => $payload['email'] ?? null,
        'roles' => $payload['roles'] ?? ['user'],
        'app_id' => $payload['app_id'] ?? 'first_api',
        'issued_at' => isset($payload['iat']) ? date('Y-m-d H:i:s', $payload['iat']) : null,
        'expires_at' => isset($payload['exp']) ? date('Y-m-d H:i:s', $payload['exp']) : null,
        'token_type' => 'Bearer'
    ];

    ResponseHelper::sendSuccess('Token is valid and active', $token_info, 200);

} catch (Exception $e) {
    error_log("Token validation error: " . $e->getMessage());
    ResponseHelper::sendError('Internal server error occurred during token validation', 500);
}