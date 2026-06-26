<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../includes/constants.php';
require_from(INCLUDES_DIR . '/response_helpers.php');
require_from(INCLUDES_DIR . '/db_handler.php');
require_from(INCLUDES_DIR . '/password_validator.php');
require_from(HANDLERS_DIR . '/auth_handler.php');

$config = require_config();
$db = new DbHandler($config);
$passwordValidator = new PasswordValidator($config);

// Only allow POST requests
ResponseHelper::checkMethod('POST');

// Authenticate user
$user_data = authenticate_request();
$user_id = $user_data['sub'] ?? null;

if (!$user_id) {
    ResponseHelper::sendError('User ID not found in token', 400);
}

$input = ResponseHelper::getJsonInput();
$validation_errors = [];

// Validate each field
foreach ($input as $field => $value) {
    switch ($field) {
        case 'password':
            $errors = $passwordValidator->validate($value);
            if (!empty($errors)) {
                $validation_errors[$field] = $errors;
            }
            break;
        case 'email_address':
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $validation_errors[$field] = 'Invalid email format';
            }
            break;
        case 'phone_no':
            if (!preg_match('/^[0-9]{10}$/', $value)) {
                $validation_errors[$field] = 'Invalid phone number format';
            }
            break;
        case 'date_of_birth':
            if (!preg_match('/^(19|20)\d\d-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/', $value)) {
                $validation_errors[$field] = 'Invalid date format (YYYY-MM-DD)';
            }
            break;
    }
}

if (!empty($validation_errors)) {
    ResponseHelper::sendValidationError($validation_errors);
}

try {
    // Check if email already exists for another user
    if (isset($input['email_address'])) {
        $stmt = $db->query("SELECT 1 FROM info WHERE email_address = ? AND uuid != ? LIMIT 1", [
            $input['email_address'],
            $user_id
        ]);
        if ($stmt->fetchColumn()) {
            ResponseHelper::sendError('Email address already in use', 409);
        }
    }

    // Check if username already exists for another user
    if (isset($input['username'])) {
        $stmt = $db->query("SELECT 1 FROM info WHERE username = ? AND uuid != ? LIMIT 1", [
            $input['username'],
            $user_id
        ]);
        if ($stmt->fetchColumn()) {
            ResponseHelper::sendError('Username already in use', 409);
        }
    }

    // Prepare update data
    $update_data = [];
    foreach ($input as $field => $value) {
        if ($field === 'password') {
            $update_data['password'] = password_hash($value, PASSWORD_DEFAULT);
        } else {
            $update_data[$field] = $value;
        }
    }

    // Update user profile
    if (!empty($update_data)) {
        $db->update('info', $update_data, 'uuid = ?', [$user_id]);
    }

    ResponseHelper::sendSuccess('Profile updated successfully');
} catch (Exception $e) {
    error_log("Update profile error: " . $e->getMessage());
    ResponseHelper::sendError('Database error', 500);
}
