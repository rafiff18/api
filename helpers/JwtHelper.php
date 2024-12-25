<?php
require_once '../config/JwtConfig.php';
require_once '../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHelper {
    private $jwtSecret;

    public function __construct() {
        // Pastikan bahwa secret key JWT didefinisikan di config file
        $this->jwtSecret = JWT_SECRET;
    }

    // Fungsi untuk mengambil token dari header Authorization
    public function getJWTFromHeader() {
        // Ambil semua header dari permintaan
        $headers = getallheaders(); 
        
        // Cek apakah ada header Authorization
        if (isset($headers['Authorization'])) {
            // Ekstrak token dari header Authorization
            $matches = [];
            preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches);
            if (isset($matches[1])) {
                return $matches[1]; // Mengembalikan token JWT jika ditemukan
            }
        }
        
        return null; // Jika token tidak ditemukan di header
    }

    // Fungsi untuk mengambil token JWT dari cookie
    private function getJWTFromCookie() {
        // Cek apakah token ada di cookie refresh_token
        if (isset($_COOKIE['refresh_token'])) {
            return $_COOKIE['refresh_token'];
        }

        return null; // Jika tidak ada token di cookie
    }

    // Fungsi utama untuk mendapatkan token JWT (dari cookie atau header)
    public function getJWT() {
        $jwt = $this->getJWTFromCookie(); // Pertama coba ambil dari cookie
        
        if ($jwt) {
            return $jwt; // Jika token ditemukan di cookie, kembalikan
        }

        $jwt = $this->getJWTFromHeader(); // Jika tidak, coba ambil dari header

        if ($jwt) {
            return $jwt; // Jika token ditemukan di header, kembalikan
        }

        // Jika token tidak ditemukan di cookie atau header, kirimkan error Unauthorized
        response('error', 'Authorization token not provided.', null, 401);
    }

    // Fungsi untuk mendekode JWT
    public function decodeJWT() {
        $jwt = $this->getJWT(); // Dapatkan token JWT

        try {
            // Dekode token menggunakan secret key dan algoritma HS256
            return (array) JWT::decode($jwt, new Key($this->jwtSecret, 'HS256'));
        } catch (Exception $e) {
            // Jika token tidak valid (misalnya expired), kembalikan error
            response('error', 'Invalid token: ' . $e->getMessage(), null, 401);
        }
    }

    // Fungsi untuk mendapatkan roles dari token JWT
    public function getRoles() {
        $decoded = $this->decodeJWT(); // Dekode token JWT
        
        if (isset($decoded['roles'])) {
            // Jika roles ada, pastikan dalam bentuk array
            if (is_array($decoded['roles'])) {
                return array_map('trim', $decoded['roles']);
            } elseif (is_string($decoded['roles'])) {
                return array_map('trim', explode(',', $decoded['roles']));
            }
        }
        return [];
    }

    // Fungsi untuk mendapatkan user_id dari token JWT
    public function getUserId() {
        $decoded = $this->decodeJWT(); // Dekode token JWT
        return $decoded['user_id'] ?? null; // Ambil user_id jika ada
    }

    // Fungsi untuk mendapatkan data pengguna dari token JWT
    public function getUserData() {
        return $this->decodeJWT(); // Kembalikan seluruh data yang didekode dari token
    }
}
