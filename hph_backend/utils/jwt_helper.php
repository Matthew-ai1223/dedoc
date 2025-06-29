<?php
require_once 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHelper {
    private static $secret_key = "dedoc_secret_key_2024_health_system";
    private static $algorithm = 'HS256';

    public static function generateToken($user_data) {
        $issued_at = time();
        $expiration_time = $issued_at + (60 * 60 * 24 * 7); // 7 days

        $payload = array(
            "iat" => $issued_at,
            "exp" => $expiration_time,
            "user" => $user_data
        );

        return JWT::encode($payload, self::$secret_key, self::$algorithm);
    }

    public static function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key(self::$secret_key, self::$algorithm));
            return $decoded;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function getAuthorizationHeader() {
        $headers = null;
        
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        
        return $headers;
    }

    public static function getBearerToken() {
        $headers = self::getAuthorizationHeader();
        
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}
?> 