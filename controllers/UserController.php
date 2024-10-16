<?php
include_once '../config/database.php';

class UserController {
    private $conn;
    private $table_name = "users";

    public $users_id;
    public $username;
    public $email;
    public $password;
    public $role;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function setHeaders() {
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Access-Control-Max-Age: 3600");
    }

    // Ambil semua users
    public function getAllUsers() {
        $this->setHeaders();
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => $users
        ], JSON_PRETTY_PRINT);
    }

    // Ambil user berdasarkan ID
    public function getUserById($id) {
        $this->setHeaders();
        $query = "SELECT * FROM " . $this->table_name . " WHERE users_id = :users_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':users_id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode([
                'status' => 'success',
                'data' => $user
            ], JSON_PRETTY_PRINT);
        } else {
            header("HTTP/1.0 404 Not Found");
            echo json_encode([
                'status' => 'error',
                'message' => 'User tidak ditemukan.'
            ], JSON_PRETTY_PRINT);
        }
    }

    // Buat user baru
    public function createUser() {
        $this->setHeaders();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Validasi role
            if (empty($data['role'])) {
                header("HTTP/1.0 400 Bad Request");
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Role harus diisi.'
                ], JSON_PRETTY_PRINT);
                return;
            }

            $this->users_id = uniqid();
            $this->username = htmlspecialchars(strip_tags($data['username'] ?? ''));
            $this->email = htmlspecialchars(strip_tags($data['email'] ?? ''));
            $this->password = htmlspecialchars(strip_tags($data['password'] ?? ''));
            $this->role = htmlspecialchars(strip_tags($data['role'] ?? ''));

            if ($this->create()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'User berhasil dibuat.'
                ], JSON_PRETTY_PRINT);
            } else {
                header("HTTP/1.0 400 Bad Request");
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Gagal membuat user.'
                ], JSON_PRETTY_PRINT);
            }
        }
    }

    private function create() {
        $valid_roles = ['porpose', 'admin', 'superadmin', 'member'];
        if (!in_array($this->role, $valid_roles)) {
            throw new Exception("Role tidak valid: " . $this->role);
        }

        $query = "INSERT INTO " . $this->table_name . " (username, email, password, role) 
                  VALUES(:username, :email, :password, :role)";
        $stmt = $this->conn->prepare($query);

        $password_hashed = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $password_hashed);
        $stmt->bindParam(":role", $this->role);

        return $stmt->execute();
    }

    // Update user
    public function updateUser($users_id) {
        $this->setHeaders();
        if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
            parse_str(file_get_contents("php://input"), $_PUT);

            $this->users_id = htmlspecialchars(strip_tags($users_id));
            $this->username = htmlspecialchars(strip_tags($_PUT['username'] ?? ''));
            $this->email = htmlspecialchars(strip_tags($_PUT['email'] ?? ''));
            $this->password = htmlspecialchars(strip_tags($_PUT['password'] ?? ''));
            $this->role = htmlspecialchars(strip_tags($_PUT['role'] ?? ''));

            if ($this->update()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'User berhasil diperbarui.'
                ], JSON_PRETTY_PRINT);
            } else {
                header("HTTP/1.0 400 Bad Request");
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Gagal memperbarui user.'
                ], JSON_PRETTY_PRINT);
            }
        }
    }

    private function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET username = :username, email = :email, password = :password, role = :role 
                  WHERE users_id = :users_id";
        $stmt = $this->conn->prepare($query);

        $password_hashed = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(":users_id", $this->users_id);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $password_hashed);
        $stmt->bindParam(":role", $this->role);

        return $stmt->execute();
    }

    // Hapus user
    public function deleteUser($users_id) {
        $this->setHeaders();
        if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
            if ($this->delete($users_id)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'User berhasil dihapus.'
                ], JSON_PRETTY_PRINT);
            } else {
                header("HTTP/1.0 400 Bad Request");
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Gagal menghapus user.'
                ], JSON_PRETTY_PRINT);
            }
        }
    }

    private function delete($users_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE users_id = :users_id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':users_id', htmlspecialchars(strip_tags($users_id)));

        return $stmt->execute();
    }
}
?>
