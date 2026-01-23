<?php
namespace AuthMicroservice\Handlers;
use AuthMicroservice\Helpers\JwtHelper;

class AuthHandler {
    public function login($username, $password) {
        // Logic from your 190+ API experience
        // In a real app, you would verify against the Database class here
        if ($username === 'admin' && $password === 'password123') {
            $token = JwtHelper::generateToken(['uuid' => 'user-123']);
            return ["status" => "success", "token" => $token];
        }
        
        return ["status" => "error", "message" => "Invalid credentials"];
    }
}
