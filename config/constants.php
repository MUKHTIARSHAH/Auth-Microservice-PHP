<?php
// Constants placeholder <?php
// Define base directory
define('BASE_DIR', __DIR__ . '/..');
define('INCLUDES_DIR', BASE_DIR . '/includes');

/**
 * Helper function to require files with proper path handling
 */
function require_from($path) {
    // Normalize path separators for Windows
    $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    
    if (file_exists($normalizedPath)) {
        require_once $normalizedPath;
    } else {
        throw new Exception("Required file not found: " . $normalizedPath);
    }
}

/**
 * Helper function to load config
 */
function require_config() {
    $configPath = INCLUDES_DIR . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
    
    if (file_exists($configPath)) {
        return require $configPath;
    } else {
        throw new Exception("Config file not found: " . $configPath);
    }
}
?>