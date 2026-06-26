<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../includes/constants.php';
require_from(INCLUDES_DIR . '/response_helpers.php');
require_from(INCLUDES_DIR . '/db_handler.php');

$config = require_config();
$db = new DbHandler($config);

// Only allow POST requests
ResponseHelper::checkMethod('POST');

try {
    $input = ResponseHelper::getJsonInput();
    $email = $input['email_address'] ?? null;
    
    if (!$email) {
        ResponseHelper::sendError('Email address is required', 400);
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        ResponseHelper::sendError('Invalid email format', 400);
    }
    
    // Check if user exists
    $stmt = $db->query("SELECT uuid, username, full_name FROM info WHERE email_address = ? AND is_active = 1", [$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Don't reveal if email exists or not for security
        ResponseHelper::sendSuccess('If the email exists, a password reset link has been sent');
    }
    
    // Generate reset token
    $reset_token = bin2hex(random_bytes(32));
    $reset_expires = date('Y-m-d H:i:s', time() + (60 * 60)); // 1 hour from now
    
    // Note: In a production environment, you would:
    // 1. Add password_reset_token and password_reset_expires_at columns to the info table
    // 2. Store the reset token in the database
    // 3. Send an actual email with the reset link
    
    // For now, we'll just return the token for testing purposes
    // In production, remove this and implement actual email sending
    
    ResponseHelper::sendSuccess('Password reset link sent successfully', [
        'message' => 'Password reset link has been sent to your email',
        'reset_token' => $reset_token, // Remove this in production
        'expires_at' => $reset_expires  // Remove this in production
    ]);
    
} catch (Exception $e) {
    error_log("Forgot password error: " . $e->getMessage());
    ResponseHelper::sendError('Password reset request failed', 500);
} 