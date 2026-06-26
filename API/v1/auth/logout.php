<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../includes/constants.php';
require_from(INCLUDES_DIR . '/response_helpers.php');
require_from(INCLUDES_DIR . '/db_handler.php');
require_from(HANDLERS_DIR . '/auth_handler.php');

$config = require_config();
$db = new DbHandler($config);

// Only allow POST requests
ResponseHelper::checkMethod('POST');

// Authenticate user
$user_data = authenticate_request();
$user_id = $user_data['sub'] ?? null;

if (!$user_id) {
    ResponseHelper::sendError('User ID not found in token', 400);
}

try {
    // Get the token from headers
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        ResponseHelper::sendError('Authorization header missing or invalid', 401);
    }
    
    $token = $matches[1];
    
    // Store the token in a blacklist table (optional - for immediate invalidation)
    // You can create a token_blacklist table if you want to track invalidated tokens
    // For now, we'll just return success since JWT tokens have expiration
    
    // Note: In a production environment, you might want to:
    // 1. Add a last_logout column to the info table
    // 2. Create a token_blacklist table to track invalidated tokens
    // 3. Implement proper session management
    
    ResponseHelper::sendSuccess('Logout successful');
    
} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
    ResponseHelper::sendError('Logout failed', 500);
} 