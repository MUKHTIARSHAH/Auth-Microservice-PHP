<?php
require_once __DIR__ . '/../config/config.php';

class JwtHelper {
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
    }
    
    public function generateToken($payload) {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload['iat'] = time();
        $payload['exp'] = time() + $this->config['security']['jwt_expiration'];
        
        $segments = [
            $this->base64urlEncode(json_encode($header)),
            $this->base64urlEncode(json_encode($payload))
        ];
        
        $signing_input = implode('.', $segments);
        $signature = hash_hmac('sha256', $signing_input, $this->config['security']['jwt_secret'], true);
        $segments[] = $this->base64urlEncode($signature);
        
        return implode('.', $segments);
    }
    
    public function validateToken($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        
        list($header64, $payload64, $signature64) = $parts;
        $header = json_decode($this->base64urlDecode($header64), true);
        $payload = json_decode($this->base64urlDecode($payload64), true);
        
        if (!$header || !$payload) {
            return false;
        }
        
        $valid_signature = hash_hmac('sha256', "$header64.$payload64", $this->config['security']['jwt_secret'], true);
        
        if (!hash_equals($valid_signature, $this->base64urlDecode($signature64))) {
            return false;
        }
        
        if (isset($payload['exp']) && time() > $payload['exp']) {
            return false;
        }
        
        return $payload;
    }
    
    private function base64urlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private function base64urlDecode($data) {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
