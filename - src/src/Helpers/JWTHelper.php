<?php
namespace AuthMicroservice\Helpers;

class JWTHelper {
    private static $secretKey = "YOUR_SECURE_KEY"; // Ideally pulled from .env

    public static function createTokens($userId) {
        return [
            "access_token" => self::encode(['sub' => $userId, 'exp' => time() + 3600]), // 1 hour
            "refresh_token" => self::encode(['sub' => $userId, 'exp' => time() + 604800]) // 7 days
        ];
    }

    private static function encode($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", self::$secretKey, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        return "$base64Header.$base64Payload.$base64Signature";
    }
}
