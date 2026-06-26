<?php
// JWT configuration for this microservice
// Set this secret to EXACTLY the same value used by your auth microservice.
// If unsure, copy the value from that service's JWT secret.

if (!defined('JWT_SECRET_KEY')) {
    // Default to existing app config secret to avoid breaking current tokens.
    // Replace this with your shared secret when you know it.
    $app_config = require __DIR__ . '/config.php';
    define('JWT_SECRET_KEY', $app_config['security']['jwt_secret']);
}

if (!defined('JWT_ALGORITHM')) {
    define('JWT_ALGORITHM', 'HS256');
}


