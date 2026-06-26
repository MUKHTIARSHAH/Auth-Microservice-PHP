<?php
/**
 * Database configuration and connection
 * Establishes PDO connection with error handling
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name'); // <-- set your DB name
define('DB_USER', 'your_db_user');       // <-- set your DB user
define('DB_PASS', 'your_db_password');   // <-- set your DB password
define('DB_CHARSET', 'utf8mb4');

/**
 * Establish database connection using PDO
 * @return PDO Database connection object
 * @throws Exception If connection fails
 */
function get_database_connection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception("Database connection failed");
    }
}