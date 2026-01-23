public function login($username, $password) {
    // ... verification logic ...

    if (!$userFound) {
        // Log the failure to your Error Log Microservice
        RemoteLogger::send(
            "Failed login attempt for username: $username", 
            401, 
            "warning", 
            ["ip" => $_SERVER['REMOTE_ADDR']]
        );
        return ["status" => "error", "message" => "Invalid credentials"];
    }
}
