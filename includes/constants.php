<?php
// Define project root
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(__DIR__));
}

// Common paths
if (!defined('INCLUDES_DIR')) {
    define('INCLUDES_DIR', PROJECT_ROOT . '/includes');
}
if (!defined('CONFIG_DIR')) {
    define('CONFIG_DIR', PROJECT_ROOT . '/config');
}
if (!defined('HANDLERS_DIR')) {
    define('HANDLERS_DIR', PROJECT_ROOT . '/handlers');
}

// Helper function to require files
function require_from($path) {
    return require_once $path;
}

// Helper function to require config
function require_config($file = 'config.php') {
    return require CONFIG_DIR . '/' . $file;
}
