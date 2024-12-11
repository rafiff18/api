<?php
require_once '../config/JwtConfig.php';
require_once '../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHelper {
    private $jwtSecret;

    public function __construct() {
        $this->jwtSecret = JWT_SECRET; 
    }

    private function getJWTFromHeader() {
        $headers = getallheaders();

        if (isset($headers['Authorization'])) {
            $matches = [];
            if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                return $matches[1];
            } else {
                response('error', 'Invalid authorization format in header.', null, 401);
            }
        }

        return null;
    }
    
    private function getJWTFromCookie() {
        if (isset($_COOKIE['refresh_token'])) {
            return $_COOKIE['refresh_token'];
        }

        return null; 
    }

    public function getJWT() {
        $jwt = $this->getJWTFromCookie();
        
        if ($jwt) {
            return $jwt;
        }

        $jwt = $this->getJWTFromHeader();

        if ($jwt) {
            return $jwt;
        }

        response('error', 'Authorization token not provided.', null, 401);
    }

    public function decodeJWT() {
        $jwt = $this->getJWT();

        try {
            return (array) JWT::decode($jwt, new Key($this->jwtSecret, 'HS256'));
        } catch (Exception $e) {
            response('error', 'Invalid token: ' . $e->getMessage(), null, 401);
        }
    }

    public function getRoles() {
        $decoded = $this->decodeJWT();
        
        if (isset($decoded['roles'])) {
            if (is_array($decoded['roles'])) {
                return array_map('trim', $decoded['roles']);
            } elseif (is_string($decoded['roles'])) {
                return array_map('trim', explode(',', $decoded['roles']));
            }
        }
        return [];
    }

    public function getUserId() {
        $decoded = $this->decodeJWT();
        return $decoded['user_id'] ?? null;
    }

    public function getUserData() {
        return $this->decodeJWT();
    }
}
