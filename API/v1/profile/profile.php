<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../includes/constants.php';
require_from(INCLUDES_DIR . '/response_helpers.php');
require_from(INCLUDES_DIR . '/db_handler.php');
require_from(HANDLERS_DIR . '/auth_handler.php');

$config = require_config();
$db = new DbHandler($config);

// Only allow GET requests
ResponseHelper::checkMethod('GET');

try {
    // Authenticate user
    $user_data = authenticate_request();
    $user_id = $user_data['sub'] ?? null;
    
    if (!$user_id) {
        ResponseHelper::sendError('User ID not found in token', 400);
    }

    // Fetch profile
    $stmt = $db->query("SELECT uuid, full_name, username, email_address, phone_no, gender, date_of_birth, created_at, updated_at FROM info WHERE uuid = ? AND is_active = 1", [$user_id]);
    $profile = $stmt->fetch();
    
    if (!$profile) {
        ResponseHelper::sendError('User profile not found', 404);
    }
    
    ResponseHelper::sendSuccess('Profile fetched successfully', $profile, 200);

} catch (Exception $e) {
    error_log('Error in profile.php: ' . $e->getMessage());
    ResponseHelper::sendError('An unexpected error occurred', 500);
} 