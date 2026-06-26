<?php
require_once __DIR__ . '/../includes/constants.php';
require_from(INCLUDES_DIR . '/response_helpers.php');
require_from(INCLUDES_DIR . '/jwt_helper.php');

/**
 * Authenticate request using JWT
 * @return array User data from token
 */
function authenticate_request() {
    $config = require_config();
    $jwtHelper = new JwtHelper($config);
    
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        ResponseHelper::sendError('Authorization token required', 401);
    }
    
    $token = $matches[1];
    $payload = $jwtHelper->validateToken($token);
    
    if (!$payload) {
        ResponseHelper::sendError('Invalid or expired token', 401);
    }
    
    return $payload;
}