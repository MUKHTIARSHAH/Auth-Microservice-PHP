<?php
namespace AuthMicroservice\Helpers;

class RemoteLogger {
    // The URL of your Error Log Microservice
    private static $apiUrl = "http://your-domain/error_logs/api/v1/error_logs/create_log.php";

    public static function send($message, $code = 500, $status = 'error', $extraData = []) {
        $logData = [
            "user_id" => $_SESSION['user_id'] ?? "0", // 0 for guest/anonymous
            "organization_id" => "ORG_AUTH",
            "product_id" => "1", // Auth Service ID
            "STATUS" => $status,
            "MESSAGE" => $message,
            "CODE" => (string)$code,
            "DATA" => $extraData,
            "TIMESTAMP" => date('Y-m-d H:i:s')
        ];

        $ch = curl_init(self::$apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($logData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        // Execute in background or set a short timeout so it doesn't slow down the user
        curl_setopt($ch, CURLOPT_TIMEOUT, 2); 
        
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
