<?php
// core/db.php
$host = 'localhost';
$db   = 'first_api'; // <-- Your actual DB name
$user = 'root';      // <-- Default XAMPP user
$pass = '';          // <-- Default XAMPP password (empty)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $db = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

function localizeDate($my_param) {
    $orignal_dateObj = DateTime::createFromFormat('Y-m-d H:i:s', $my_param);
    
    if ($orignal_dateObj === false) {
        return "Invalid date format"; // Handle invalid date format
    }

    $abcStart = new DateTime();
    $abcStart->setTimestamp($orignal_dateObj->getTimestamp());
    $abcStart->setTimeZone(new DateTimeZone('Asia/Karachi'));
    
    return $abcStart->format('Y-m-d h:i A');
}
?>