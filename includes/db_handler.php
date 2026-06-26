<?php
require_once __DIR__ . '/../config/config.php';

class DbHandler {
    private $pdo;
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
        $this->connect();
    }
    
    private function connect() {
        try {
            $dsn = "mysql:host={$this->config['database']['host']};dbname={$this->config['database']['dbname']}";
            $this->pdo = new PDO($dsn, $this->config['database']['username'], $this->config['database']['password']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            throw new Exception("Database query failed");
        }
    }
    
    public function insert($table, $data) {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $this->query($sql, array_values($data));
        return $this->pdo->lastInsertId();
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $fields = array_keys($data);
        $set = array_map(function($field) {
            return "{$field} = ?";
        }, $fields);
        $sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE " . $where;
        $params = array_merge(array_values($data), $whereParams);
        return $this->query($sql, $params);
    }
    
    public function __destruct() {
        $this->pdo = null;
    }
}
