<?php
require_once "../database/Database.php";

class AuthController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
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
                echo json_encode(['status' => 'error', 'message' => 'Password salah.'], JSON_PRETTY_PRINT);
            }
        } else {
            header("HTTP/1.0 404 Not Found");
            echo json_encode(['status' => 'error', 'message' => 'Pengguna tidak ditemukan.'], JSON_PRETTY_PRINT);
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
