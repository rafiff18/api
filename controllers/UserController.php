<?php
require_once '../database/Database.php';
require_once '../helpers/responseHelper.php';

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

    // Ambil semua users
    public function getAllUsers() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        response('success', 'Get users successfully', $users);
    }

    // Ambil user berdasarkan ID
    public function getUserById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE users_id = :users_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':users_id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            response('success', 'Get user by id successfully', $user);
        } else {
            response('error', 'User not found', null, 404);
        }
    }

    // Buat user baru
    public function createUser() {
        // Menerima data JSON dan mendekode
        $input = json_decode(file_get_contents('php://input'), true);
    
        // Pastikan input bukan null dan merupakan array
        if (!is_array($input)) {
            response('error', 'Invalid JSON input', null, 400);
            return;
        }
    
        $requiredFields = ['username', 'email', 'password', 'role'];
        $missingParams = array_diff($requiredFields, array_keys($input));
    
        if (empty($missingParams)) {
            // Mengambil nilai maksimum dari users_id yang ada
            $stmt = $this->conn->query("SELECT MAX(users_id) as max_id FROM users");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $new_id = $result['max_id'] + 1; 
    
            $query = "INSERT INTO users ( username, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
    
            if ($stmt->execute([             
                $input['username'], 
                $input['email'], 
                password_hash($input['password'], PASSWORD_BCRYPT), 
                $input['role']
            ])) {
                $result_stmt = $this->conn->prepare("SELECT * FROM users WHERE users_id = ?");
                $result_stmt->execute([$new_id]);
                $new_data = $result_stmt->fetch(PDO::FETCH_OBJ);

                response('success', 'User Added Successfully', $new_data);
            } else {
                response('error', 'Unable to create user', null, 400);
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
        if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
            parse_str(file_get_contents("php://input"), $_PUT);

            $this->users_id = htmlspecialchars(strip_tags($users_id));
            $this->username = htmlspecialchars(strip_tags($_PUT['username'] ?? ''));
            $this->email = htmlspecialchars(strip_tags($_PUT['email'] ?? ''));
            $this->password = htmlspecialchars(strip_tags($_PUT['password'] ?? ''));
            $this->role = htmlspecialchars(strip_tags($_PUT['role'] ?? ''));

            if ($this->update()) {
                response('success', 'User has been updated');
            } else {
                response('error', 'Failed to update user', null, 400);
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
        if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
            if ($this->delete($users_id)) {
                response('success', 'User has ben delete');
            } else {
                response('error', 'Failed to delete user', null, 400);
            }
        }
    }

    private function delete($users_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE users_id = :users_id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':users_id', htmlspecialchars(strip_tags($users_id)));

        return $stmt->execute();
    }
    // Tambahkan fungsi resetPassword
    public function resetPassword($users_id) {
        // Password default, bisa disesuaikan
        $default_password = "password123";
        
        // Hash password default
        $hashed_password = password_hash($default_password, PASSWORD_BCRYPT);

        // Update password user dengan password default
        $query = "UPDATE " . $this->table_name . " SET password = :password WHERE users_id = :users_id";
        $stmt = $this->conn->prepare($query);

        // Bind parameter
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":users_id", $users_id);

        // Eksekusi query dan cek apakah berhasil
        if ($stmt->execute()) {
            response('success', 'Password has been reset to default:'.$default_password);
        } else {
            response('error', 'Failed to reset password', null, 400);
        }
    }
// Tambahkan fungsi untuk reset password
public function forgotPassword() {
    // Menerima input email atau ID dari request
    $input = json_decode(file_get_contents('php://input'), true);

    // Validasi input apakah email atau ID ada
    if (empty($input['email'])) {
        response('error', 'Email is required', null, 400);
        return;
    }

    // Cari user berdasarkan email
    $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":email", $input['email']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Set password default jika pengguna ditemukan
        $default_password = "password123";
        $hashed_password = password_hash($default_password, PASSWORD_BCRYPT);

        // Update password user di database
        $query = "UPDATE " . $this->table_name . " SET password = :password WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":email", $input['email']);

        if ($stmt->execute()) {
            response('success', 'Password has been reset to default : '.$default_password);
        } else {
            response('error', 'Failed to reset password', null, 400);
        }
    } else {
        response('error', 'Email not found', null, 404);
    }
}

public function changePassword($id) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['old_password']) || !isset($input['new_password'])) {
        response('error', 'Old password and new password are needed');
        return;
    }

    $query = "SELECT password FROM " . $this->table_name . " WHERE users_id = :users_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':users_id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($input['old_password'], $user['password'])) {
        $new_password_hashed = password_hash($input['new_password'], PASSWORD_BCRYPT);

        $update_query = "UPDATE " . $this->table_name . " SET password = :password WHERE users_id = :users_id";
        $update_stmt = $this->conn->prepare($update_query);
        $update_stmt->bindParam(':password', $new_password_hashed);
        $update_stmt->bindParam(':users_id', $id, PDO::PARAM_INT);

        if ($update_stmt->execute()) {
            response('success', 'Password has been updated');
        } else {
            response('error', 'Failed to update password', null, 400);
        }
    } else {
        response('error', 'Old password are not suitable', null, 400);
    }
}

}
?>
