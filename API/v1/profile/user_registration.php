<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../includes/constants.php';
require_from(INCLUDES_DIR . '/response_helpers.php');
require_from(INCLUDES_DIR . '/db_handler.php');
require_from(INCLUDES_DIR . '/uuid_helper.php');
require_from(INCLUDES_DIR . '/password_validator.php');

$config = require_config();
$db = new DbHandler($config);
$passwordValidator = new PasswordValidator($config);

// Only allow POST requests
ResponseHelper::checkMethod('POST');

$input = ResponseHelper::getJsonInput();

$required = ['full_name', 'username', 'email_address', 'phone_no', 'gender', 'date_of_birth', 'password'];
$validation_errors = [];

foreach ($required as $field) {
    if (empty($input[$field])) {
        $validation_errors[$field] = "{$field} is required";
    }
}

// Validate password
$passwordErrors = $passwordValidator->validate($input['password']);
if (!empty($passwordErrors)) {
    $validation_errors['password'] = $passwordErrors;
}

// Email format validation
if (!empty($input['email_address']) && !filter_var($input['email_address'], FILTER_VALIDATE_EMAIL)) {
    $validation_errors['email_address'] = 'Email format is invalid';
}

if (!empty($validation_errors)) {
    ResponseHelper::sendValidationError($validation_errors);
}

$username = $input['username'];
$email = $input['email_address'];

try {
    // Check if user already exists
    $stmt = $db->query("SELECT 1 FROM info WHERE username = ? OR email_address = ? LIMIT 1", [$username, $email]);
    $exists = $stmt->fetchColumn();
    
    if ($exists) {
        ResponseHelper::sendError('User already exists with this username or email.', 409);
    }
    
    // Insert new user
    $uuid = generate_uuid();
    $user_data = [
        'uuid' => $uuid,
        'full_name' => $input['full_name'],
        'username' => $username,
        'email_address' => $email,
        'phone_no' => $input['phone_no'],
        'gender' => $input['gender'],
        'date_of_birth' => $input['date_of_birth'],
        'password' => password_hash($input['password'], PASSWORD_DEFAULT)
    ];
    
    $db->insert('info', $user_data);
    
    // Return basic user data without sensitive information
    $response_data = [
        'id' => $uuid,
        'username' => $username,
        'email_address' => $email
    ];
    
    ResponseHelper::sendSuccess('User registered successfully', $response_data, 201);
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    ResponseHelper::sendError('Database error', 500);
}
