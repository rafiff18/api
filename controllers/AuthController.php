<?php
require_once '../config/JwtConfig.php';
require_once '../vendor/autoload.php'; 
require_once '../helpers/ResponseHelper.php';
require_once '../helpers/JwtHelper.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    private function generateJWT($userId, $roles) {
        $issuedAt = time();
        $expirationTime = $issuedAt + JWT_EXPIRATION_TIME; 
        $payload = [
            'user_id' => $userId,
            'roles' => $roles,  
            'iat' => $issuedAt,
            'exp' => $expirationTime,
        ];
        
        return JWT::encode($payload, JWT_SECRET, 'HS256');
    }

    private function generateRefreshToken($userId, $roles) {
        $issuedAt = time();
        $expirationTime = $issuedAt + JWT_REFRESH_EXPIRATION_TIME; 
        $payload = [
            'user_id' => $userId,
            'roles' => $roles,  
            'iat' => $issuedAt,
            'exp' => $expirationTime,
        ];
        
        return JWT::encode($payload, JWT_SECRET, 'HS256');
    }

    private function setRefreshTokenInCookie($refreshToken) {
        $cookieParams = [
            'expires' => time() + JWT_REFRESH_EXPIRATION_TIME, 
            'path' => '/', 
            'secure' => false, 
            'httponly' => true,
            'samesite' => 'Strict',
        ];
        
        setcookie('refresh_token', $refreshToken, $cookieParams);
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            response('error', 'Invalid email format.', null, 400);
            return;
        }
    
        if (empty($password)) {
            response('error', 'Password is required.', null, 400);
            return;
        }

        // Ambil data user dan roles
        $stmt = $this->db->prepare("SELECT u.user_id, u.username, u.email, u.password, GROUP_CONCAT(r.role_name) AS roles
                                    FROM user u
                                    LEFT JOIN user_roles ur ON u.user_id = ur.user_id
                                    LEFT JOIN roles r ON ur.role_id = r.role_id
                                    WHERE u.email = ? GROUP BY u.user_id");
        
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            if (password_verify($password, $result['password'])) {
                $roles = explode(',', $result['roles'] ?? '');
                
                $accessToken = $this->generateJWT($result['user_id'], $roles);
                $refreshToken = $this->generateRefreshToken($result['user_id'], $roles);
                
                $this->setRefreshTokenInCookie($refreshToken);

                response('success', 'Login successful.', ['access_token' => $accessToken, 'refresh_token' => $refreshToken], 200);
            } else {
                response('error', 'Incorrect email or password.', null, 401);
            }
        } else {
            response('error', 'User not found.', null, 404);
        }
    }

    public function verifyJWT($token) {
        try {
            $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
            return (array) $decoded;
        } catch (Exception $e) {
            response('error', $e->getMessage(), null, 401);
            return false;
        }
    }

    public function refreshAccessToken() {
        $refreshToken = $_COOKIE['refresh_token'] ?? null;
    
        if (!$refreshToken) {
            response('error', 'Refresh token not found.', null, 401);
            return;
        }
    
        try {
            $decoded = JWT::decode($refreshToken, new Key(JWT_SECRET, 'HS256'));
            $userId = $decoded->user_id;
            $roles = $decoded->roles;  
    
            $newAccessToken = $this->generateJWT($userId, $roles);
    
            response('success', 'Access token refreshed.', ['access_token' => $newAccessToken], 200);
    
        } catch (Exception $e) {
            response('error', 'Invalid or expired refresh token.', null, 401);
        }
    }    

    public function logout() {
        $jwt = $_COOKIE['jwt'] ?? null;
        $refreshToken = $_COOKIE['refresh_token'] ?? null;

        if ($jwt || $refreshToken) {
            setcookie('jwt', '', time() - 3600, '/');
            setcookie('refresh_token', '', time() - 3600, '/');
            response('success', 'Logout successful.', null, 200);
        } else {
            response('error', 'Invalid token.', null, 401);
        }
    }

    public function forgotPassword() {
        $data = json_decode(file_get_contents("php://input"), true);

        $email = $data['email'] ?? '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            response('error', 'Invalid email format.', null, 400);
            return;
        }

        $stmt = $this->db->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            response('success', 'Reset password link sent.', null, 200);
        } else {
            response('error', 'Email not found.', null, 404);
        }
    }

    public function changePassword($userId) {
        $data = json_decode(file_get_contents("php://input"), true);

        $oldPassword = $data['oldPassword'] ?? '';
        $newPassword = $data['newPassword'] ?? '';

        if (empty($oldPassword) || empty($newPassword)) {
            response('error', 'Both old and new passwords are required.', null, 400);
            return;
        }

        $stmt = $this->db->prepare("SELECT * FROM user WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            if (password_verify($oldPassword, $result['password'])) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $this->db->prepare("UPDATE user SET password = ? WHERE user_id = ?");
                if ($updateStmt->execute([$hashedPassword, $userId])) {
                    response('success', 'Password changed successfully.', null, 200);
                } else {
                    response('error', 'Password change failed.', null, 500);
                }
            } else {
                response('error', 'Old password is incorrect.', null, 401);
            }
        } else {
            response('error', 'User not found.', null, 404);
        }
    }

    public function checkLogin() {
        if (isset($_COOKIE['refresh_token'])) {
            $refresh_token = $_COOKIE['refresh_token'];
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $refresh_token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
        } else {
            $refresh_token = null;
        }
    
        if (!$refresh_token) {
            response('error', 'Token not found. Unauthorized.', null, 401);
            return;
        }
    
        try {
            // Decode the refresh token to get user_id
            $decoded = JWT::decode($refresh_token, new Key(JWT_SECRET, 'HS256'));
    
            $userId = $decoded->user_id;
    
            // Query to get user data along with roles by joining user_roles table
            $stmt = $this->db->prepare("
                SELECT u.*, r.role_id
                FROM user u
                LEFT JOIN user_roles ur ON u.user_id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.role_id
                WHERE u.user_id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if ($user) {
                // Step 1: Get user data without roles
                $userData = $user[0];  // Assuming all rows are for the same user
    
                // Step 2: Combine all role names into an array
                $roles = array_column($user, 'role_id');
    
                // Step 3: Add roles to the user data
                $userData['roles'] = $roles;
    
                // Return response with user data and roles
                response('success', 'User data fetched successfully.', $userData, 200);
            } else {
                response('error', 'User not found.', null, 404);
            }
        } catch (Exception $e) {
            response('error', 'Invalid or expired token.', null, 401);
        }
    }    
}
?>
