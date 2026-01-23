<?php
namespace AuthMicroservice\Handlers;
use AuthMicroservice\Helpers\JWTHelper;

class LoginHandler {
    public function handle($input) {
        // 1. Sanitize and Validate
        $username = htmlspecialchars(strip_tags($input['username']));
        
        // 2. Logic to check User (Generic for portfolio)
        // In a real app: $user = $db->query("SELECT * FROM info WHERE username = ?", [$username]);
        
        // 3. Generate Secure Response
        $tokens = JWTHelper::createTokens("user-uuid-1234");
        
        header('Content-Type: application/json');
        echo json_encode([
            "STATUS" => "success",
            "DATA" => $tokens,
            "MESSAGE" => "Authentication successful"
        ]);
    }
}
