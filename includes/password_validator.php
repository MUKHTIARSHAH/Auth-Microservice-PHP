<?php
// Define the project root
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/../config/config.php';
require_from(INCLUDES_DIR . '/password_validator.php');

class PasswordValidator {
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
    }
    
    public function validate($password) {
        $errors = [];
        
        if (strlen($password) < $this->config['security']['password_min_length']) {
            $errors[] = "Password must be at least {$this->config['security']['password_min_length']} characters long";
        }
        
        if (strlen($password) > $this->config['security']['password_max_length']) {
            $errors[] = "Password cannot be longer than {$this->config['security']['password_max_length']} characters";
        }
        
        if ($this->config['security']['password_requirements']['uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        if ($this->config['security']['password_requirements']['lowercase'] && !preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        if ($this->config['security']['password_requirements']['number'] && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        if ($this->config['security']['password_requirements']['special_char'] && !preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }
        
        return $errors;
    }
}
