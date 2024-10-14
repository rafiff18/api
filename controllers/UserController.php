<?php

require_once "../database/Database.php"; 
require_once "../helpers/ResponseHelper.php";

class UserController {
    private $conn;

    public function __construct($conn) {
        if (!$conn) {
            response(false, 'Database connection failed');
        }
        $this->conn = $conn;
    }

    // Mendapatkan semua user
    public function getAllUsers() {
        $query = "SELECT * FROM users"; // Pastikan nama tabel sesuai
        $data = array();

        $stmt = $this->conn->query($query);

        if ($stmt) {
            while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
                $data[] = $row;
            }
            response(true, 'Get List User Successfully', $data);
        } else {
            response(false, 'Get List User Failure', null, [
                'code' => 500,
                'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
            ]);
        }
    }

    // Mendapatkan user berdasarkan ID
    public function getUserById($id = 0) {
        if ($id != 0) {
            $query = "SELECT * FROM users WHERE users_id = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                $data = $stmt->fetch(PDO::FETCH_OBJ);
                response(true, 'Get User Successfully', $data);
            } else {
                response(false, 'User not found', null, [
                    'code' => 404,
                    'message' => 'The requested resource could be not found'
                ]);
            }
        } else {
            response(false, 'Invalid ID', null, [
                'code' => 400,
                'message' => 'Bad request: ID is required'
            ]);
        }
    }

    // Menambahkan user baru
    public function createUser() {
        // Menerima data JSON dan mendekode
        $input = json_decode(file_get_contents('php://input'), true);
    
        // Pastikan input bukan null dan merupakan array
        if (!is_array($input)) {
            response(false, 'Invalid JSON input', null, [
                'code' => 400,
                'message' => 'Bad request: JSON input is required'
            ]);
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
                $input['password'], 
                $input['role']
            ])) {
                $result_stmt = $this->conn->prepare("SELECT * FROM users WHERE users_id = ?");
                $result_stmt->execute([$new_id]);
                $new_data = $result_stmt->fetch(PDO::FETCH_OBJ);
                response(true, 'User Added Successfully', $new_data);
            } else {
                response(false, 'Failed to Add User', null, [
                    'code' => 500,
                    'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
                ]);
            }
        } else {
            response(false, 'Missing Parameters', null, [
                'code' => 400,
                'message' => 'Bad request: Missing required parameters: ' . implode(', ', $missingParams)
            ]);
        }
    }

    // Memperbarui user
    public function updateUser($id) {
        $input = json_decode(file_get_contents('php://input'), true);
    
        if (!is_array($input)) {
            response(false, 'Invalid JSON input', null, [
                'code' => 400,
                'message' => 'Bad request: JSON input is required'
            ]);
            return;
        }
    
        $requiredFields = ['username', 'email', 'password'];
        $missingParams = array_diff($requiredFields, array_keys($input));
    
        if (empty($missingParams)) {
            $query = 'UPDATE users SET username = ?, email = ?, password = ? WHERE users_id = ?';
            $stmt = $this->conn->prepare($query);
    
            if ($stmt->execute([
                $input['username'],
                $input['email'],
                password_hash($input['password'], PASSWORD_DEFAULT), // Jangan lupa hashing
                $id
            ])) {
                $query = "SELECT * FROM users WHERE users_id = ?";
                $result_stmt = $this->conn->prepare($query);
                $result_stmt->execute([$id]);
                $updated_data = $result_stmt->fetch(PDO::FETCH_OBJ);
    
                response(true, 'User Updated Successfully', $updated_data);
            } else {
                response(false, 'Failed to Update User', null, [
                    'code' => 500,
                    'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
                ]);
            }
        } else {
            response(false, 'Missing Parameters', null, [
                'code' => 400,
                'message' => 'Bad request: Missing required parameters: ' . implode(', ', $missingParams)
            ]);
        }
    }
    
    // Menghapus user
    public function deleteUser($id) {
        $stmt = $this->conn->prepare('DELETE FROM users WHERE users_id = ?');

        if ($stmt->execute([$id])) {
            response(true, 'User Deleted Successfully');
        } else {
            response(false, 'Failed to Delete User', null, [
                'code' => 500,
                'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
            ]);
        }
    }
}
?>
