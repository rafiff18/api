<?php
require_once "../database/Database.php";
require_once "../vendor/autoload.php"; // Include Composer's autoloader
use \Firebase\JWT\JWT; // Use the JWT class from the Firebase\JWT namespace

//yg coba tarisa
class AuthController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }
    // Generate JWT
    private function generateJWT($userId, $roles) {
        $issuedAt = time();
        $expirationTime = $issuedAt + JWT_EXPIRATION_TIME; // Token valid for the configured expiration time
        $payload = [
            'user_id' => $userId,
            'roles' => $roles,
            'iat' => $issuedAt,
            'exp' => $expirationTime,
        ];
        
        return JWT::encode($payload, JWT_SECRET, 'HS256');
    }

    // Set JWT in cookie
    private function setJWTInCookie($jwt, $expiration = null) {
        // Set cookie parameters
        $cookieParams = [
            'path' => '/', // Cookie path
            'domain' => '', // Set domain if needed
            'secure' => false, // Set to true if using HTTPS
            'httponly' => true, // Prevent JavaScript access to the cookie
            'samesite' => 'Strict', // Set SameSite attribute for CSRF protection
        ];
    
        // If expiration is provided, set it; otherwise, it will default to a session cookie
        if ($expiration) {
            $cookieParams['expires'] = time() + $expiration;
        }
    
        // Set the cookie
        setcookie('jwt', $jwt, $cookieParams);
    }
    // User login
    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("HTTP/1.0 400 Bad Request");
            echo json_encode(['status' => 'error', 'message' => 'Format email tidak valid.'], JSON_PRETTY_PRINT);
            return;
        }

        if (empty($password)) {
            header("HTTP/1.0 400 Bad Request");
            echo json_encode(['status' => 'error', 'message' => 'Password wajib diisi.'], JSON_PRETTY_PRINT);
            return;
        }

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            if (password_verify($password, $result['password'])) {
                $_SESSION['users_id'] = $result['users_id'];
                $_SESSION['username'] = $result['username'];
                $_SESSION['email'] = $result['email'];
                $_SESSION['role'] = $result['role'];

                echo json_encode(['status' => 'success', 'message' => 'Login berhasil.'], JSON_PRETTY_PRINT);
            } else {
                header("HTTP/1.0 401 Unauthorized");
                echo json_encode(['status' => 'error', 'message' => 'Email atau password salah.'], JSON_PRETTY_PRINT);
            }
        } 
    }

    // User logout
    public function logout() {
        session_destroy();
        echo json_encode(['status' => 'success', 'message' => 'Logout berhasil.'], JSON_PRETTY_PRINT);
    }

    // Ubah password
    public function changePassword($usersId) {
        $data = json_decode(file_get_contents("php://input"), true);

        $oldPassword = $data['oldPassword'] ?? '';
        $newPassword = $data['newPassword'] ?? '';

        if (empty($oldPassword) || empty($newPassword)) {
            header("HTTP/1.0 400 Bad Request");
            echo json_encode(['status' => 'error', 'message' => 'Password lama dan baru wajib diisi.'], JSON_PRETTY_PRINT);
            return;
        }

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE users_id = ?");
        $stmt->execute([$usersId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            if (password_verify($oldPassword, $result['password'])) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $this->conn->prepare("UPDATE users SET password = ? WHERE users_id = ?");
                $updateStmt->execute([$hashedPassword, $usersId]);

                echo json_encode(['status' => 'success', 'message' => 'Password berhasil diubah.'], JSON_PRETTY_PRINT);
            } else {
                header("HTTP/1.0 401 Unauthorized");
                echo json_encode(['status' => 'error', 'message' => 'Password lama salah.'], JSON_PRETTY_PRINT);
            }
        } else {
            header("HTTP/1.0 404 Not Found");
            echo json_encode(['status' => 'error', 'message' => 'Pengguna tidak ditemukan.'], JSON_PRETTY_PRINT);
        }
    }
}
?>
