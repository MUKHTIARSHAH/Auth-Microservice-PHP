<?php
/**
 * Standardized API response functions
 * All API endpoints must use these functions for consistent responses
 */

// Load constants first
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/error_logger.php';

// Load configuration
$config = require_config();

/**
 * Response helper class for standardized API responses
 */
class ResponseHelper {
    /**
     * Send success response
     * @param string $message Success message
     * @param mixed $data Response data
     * @param int $code HTTP status code
     */
    public static function sendSuccess($message, $data = [], $code = 200) {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode([
            'STATUS' => 'success',
            'MESSAGE' => $message,
            'DATA' => $data,
            'CODE' => $code,
            'TIMESTAMP' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Send error response
     * @param string $message Error message
     * @param int $code HTTP status code
     */
    public static function sendError($message, $code = 400) {
        // Fire-and-forget external error logging
        try { ErrorLogger::log($message, (int)$code, null, 'error'); } catch (\Throwable $e) {}
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode([
            'STATUS' => 'error',
            'MESSAGE' => $message,
            'CODE' => $code,
            'TIMESTAMP' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Send validation error response
     * @param array $errors Validation errors
     */
    public static function sendValidationError($errors) {
        // Fire-and-forget external error logging with validation details
        try { ErrorLogger::log('Validation failed', 400, is_array($errors) ? $errors : ['errors' => $errors], 'error'); } catch (\Throwable $e) {}
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'STATUS' => 'error',
            'MESSAGE' => 'Validation failed',
            'DATA' => $errors,
            'CODE' => 400,
            'TIMESTAMP' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Check if request method is allowed
     * @param string $allowedMethod Allowed HTTP method (GET, POST, etc.)
     */
    public static function checkMethod($allowedMethod) {
        if ($_SERVER['REQUEST_METHOD'] !== $allowedMethod) {
            self::sendError("Method not allowed. Expected: {$allowedMethod}", 405);
        }
    }

    /**
     * Get JSON input from request body
     * @return array Parsed JSON input
     */
    public static function getJsonInput() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            self::sendError('Invalid JSON input', 400);
        }
        
        return $data;
    }
}