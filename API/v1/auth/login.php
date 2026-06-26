<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../../includes/constants.php';
require_from(INCLUDES_DIR . '/response_helpers.php');
require_from(INCLUDES_DIR . '/db_handler.php');
require_from(INCLUDES_DIR . '/password_validator.php');
require_from(INCLUDES_DIR . '/jwt_helper.php');

$config = require_config();
$db = new DbHandler($config);

// Only allow POST requests
ResponseHelper::checkMethod('POST');

$input = ResponseHelper::getJsonInput();

if (!isset($input['username']) || empty($input['username'])) {
    ResponseHelper::sendError('Username is required', 400);
}
if (!isset($input['password']) || empty($input['password'])) {
    ResponseHelper::sendError('Password is required', 400);
}

$username = trim($input['username']);
$password = $input['password'];

// Password policy validation
$passwordValidator = new PasswordValidator($config);
$passwordErrors = $passwordValidator->validate($password);
if (!empty($passwordErrors)) {
    ResponseHelper::sendValidationError(['password' => $passwordErrors]);
}

try {
    // Check if user exists and is active
    $stmt = $db->query('SELECT * FROM info WHERE username = ? AND is_active = 1', [$username]);
    $user = $stmt->fetch();

    if (!$user) {
        ResponseHelper::sendError('User not found or account is inactive. Please register first.', 404);
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        ResponseHelper::sendError('Invalid username or password', 401);
    }

    // Prepare JWT payloads for access and refresh tokens
    $jwtHelper = new JwtHelper($config);
    $accessPayload = [
        'sub' => $user['uuid'],
        'username' => $user['username'],
        'email' => $user['email_address'],
        'roles' => ['user'],
        'app_id' => 'first_api',
        'type' => 'access',
        'iat' => time(),
        'exp' => time() + $config['security']['jwt_expiration']
    ];
    $jwt = $jwtHelper->generateToken($accessPayload);

    // Also issue a refresh token for refresh flow compatibility
    $refreshPayload = [
        'sub' => $user['uuid'],
        'type' => 'refresh',
        'iat' => time(),
        'exp' => time() + (7 * 24 * 60 * 60) // 7 days
    ];
    $refreshToken = $jwtHelper->generateToken($refreshPayload);

    // Prepare response data (exclude sensitive information)
    $user_data = [
        'id' => $user['uuid'],
        'username' => $user['username'],
        'email_address' => $user['email_address'],
        'full_name' => $user['full_name'],
        'roles' => ['user'],
        'app_id' => 'first_api',
        // Backward compatibility fields
        'token' => $jwt,
        'token_type' => 'Bearer',
        'expires_in' => $config['security']['jwt_expiration'],
        // New explicit tokens
        'access_token' => $jwt,
        'refresh_token' => $refreshToken
    ];

    ResponseHelper::sendSuccess('Login successful. User authenticated and token generated.', $user_data, 200);

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    ResponseHelper::sendError('Internal server error occurred during login: ' . $e->getMessage(), 500);
}