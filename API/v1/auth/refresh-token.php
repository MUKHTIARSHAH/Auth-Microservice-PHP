<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../includes/constants.php';
require_from(INCLUDES_DIR . '/response_helpers.php');
require_from(INCLUDES_DIR . '/db_handler.php');
require_from(INCLUDES_DIR . '/jwt_helper.php');
require_from(HANDLERS_DIR . '/auth_handler.php');

$config = require_config();
$db = new DbHandler($config);
$jwtHelper = new JwtHelper($config);

// Only allow POST requests
ResponseHelper::checkMethod('POST');

try {
    // Get the refresh token from request body
    $input = ResponseHelper::getJsonInput();
    $refresh_token = $input['refresh_token'] ?? null;
    
    if (!$refresh_token) {
        ResponseHelper::sendError('Refresh token is required', 400);
    }
    
    // Validate the refresh token
    $payload = $jwtHelper->validateToken($refresh_token);
    
    if (!$payload) {
        ResponseHelper::sendError('Invalid or expired refresh token', 401);
    }
    
    // Check if token type is refresh
    if (($payload['type'] ?? '') !== 'refresh') {
        ResponseHelper::sendError('Invalid token type', 401);
    }
    
    $user_id = $payload['sub'] ?? null;
    
    if (!$user_id) {
        ResponseHelper::sendError('User ID not found in token', 400);
    }
    
    // Verify user exists and is active
    $stmt = $db->query("SELECT uuid, username, email_address, full_name FROM info WHERE uuid = ? AND is_active = 1", [$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        ResponseHelper::sendError('User not found or inactive', 401);
    }
    
    // Generate new access token
    $access_token_payload = [
        'sub' => $user['uuid'],
        'username' => $user['username'],
        'email' => $user['email_address'],
        'full_name' => $user['full_name'],
        'roles' => ['user'],
        'app_id' => 'first_api',
        'type' => 'access',
        'iat' => time(),
        'exp' => time() + ($config['security']['jwt_expiration'] ?? 3600)
    ];
    
    $new_access_token = $jwtHelper->generateToken($access_token_payload);
    
    // Generate new refresh token
    $refresh_token_payload = [
        'sub' => $user['uuid'],
        'type' => 'refresh',
        'iat' => time(),
        'exp' => time() + (7 * 24 * 60 * 60) // 7 days
    ];
    
    $new_refresh_token = $jwtHelper->generateToken($refresh_token_payload);
    
    ResponseHelper::sendSuccess('Token refreshed successfully', [
        'access_token' => $new_access_token,
        'refresh_token' => $new_refresh_token,
        'token_type' => 'Bearer',
        'expires_in' => $config['security']['jwt_expiration'] ?? 3600
    ]);
    
} catch (Exception $e) {
    error_log("Refresh token error: " . $e->getMessage());
    ResponseHelper::sendError('Token refresh failed', 500);
} 