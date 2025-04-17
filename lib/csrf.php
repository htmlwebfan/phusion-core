<?php

class CSRF {
    public static function generateToken() {
        if (!session_id()) session_start();
        if (!isset($_SESSION['csrf_token'])) {
            $token = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $token;
            error_log("Generated CSRF token: " . $token); // Debug
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyToken($token) {
        if (!session_id()) session_start();
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        error_log("Verifying CSRF token: $token against session: $sessionToken"); // Debug
        return !empty($token) && hash_equals($sessionToken, $token);
    }

    public static function clearToken() {
        if (!session_id()) session_start();
        unset($_SESSION['csrf_token']);
    }
}