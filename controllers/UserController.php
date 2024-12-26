<?php

include_once '../config/Database.php';
require_once '../config/JwtConfig.php';
require_once '../vendor/autoload.php';
require_once '../helpers/ResponseHelper.php';
require_once '../helpers/JwtHelper.php';
require_once '../helpers/FileUploadHelpers.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserController {
    private $db;
    private $table_name = "user";
    private $jwtHelper;

    public $user_id;
    public $username;
    public $email;
    public $password;
    public $about;
    public $avatar; 
    public $roles = [];

    public function __construct($db) {
        $this->db = $db;
        $this->jwtHelper = new JWTHelper();
    }

    private function getRoles() {
        return $this->jwtHelper->getRoles();
    }

    private function getUserId() {
        return $this->jwtHelper->getUserId();
    }

    public function getAllUsers() {
        $roles = $this->getRoles();
    
        if (!in_array('Superadmin', $roles)) {
            response('error', 'Only superadmin can access this resource.', null, 403);
            return;
        }
    
        $search = isset($_GET['search']) ? htmlspecialchars(strip_tags($_GET['search'])) : '';
        $sort = isset($_GET['sort']) ? htmlspecialchars(strip_tags($_GET['sort'])) : 'username';
        $order = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'DESC' : 'ASC';
        $roleFilter = isset($_GET['role']) ? htmlspecialchars(strip_tags($_GET['role'])) : '';
    
        $limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : null; 
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = $limit ? ($page - 1) * $limit : 0; 
    
        $query = "SELECT u.*, GROUP_CONCAT(r.role_name) as roles 
                  FROM " . $this->table_name . " u
                  LEFT JOIN user_roles ur ON u.user_id = ur.user_id
                  LEFT JOIN roles r ON ur.role_id = r.role_id";
    
        $conditions = [];
        if (!empty($search)) {
            $conditions[] = "(u.username LIKE :search OR u.email LIKE :search)";
        }
        if (!empty($roleFilter)) {
            $conditions[] = "r.role_name = :role";
        }
        if ($conditions) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
    
        $query .= " GROUP BY u.user_id ORDER BY $sort $order";
    
        if ($limit) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
    
        $stmt = $this->db->prepare($query);
    
        if (!empty($search)) {
            $searchParam = '%' . $search . '%';
            $stmt->bindParam(':search', $searchParam);
        }
        if (!empty($roleFilter)) {
            $stmt->bindParam(':role', $roleFilter);
        }
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
    
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        foreach ($users as &$user) {
            unset($user['password']);
        }
    
        response(empty($users) ? 'error' : 'success', empty($users) ? 'No users found.' : 'Users retrieved successfully', $users, 200);
    }

    public function getUserById($id) {
        $roles = $this->getRoles();
        $userIdFromJWT = $this->getUserId();
    
        if (!in_array('Superadmin', $roles) && $userIdFromJWT != $id) {
            response('error', 'Unauthorized access.', null, 403);
            return;
        }
    
        $query = "SELECT u.*, GROUP_CONCAT(r.role_name) as roles 
                  FROM " . $this->table_name . " u
                  LEFT JOIN user_roles ur ON u.user_id = ur.user_id
                  LEFT JOIN roles r ON ur.role_id = r.role_id
                  WHERE u.user_id = :user_id
                  GROUP BY u.user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($user) {
            unset($user['password']);
        }
    
        response($user ? 'success' : 'error', $user ? 'User found.' : 'User not found.', $user, $user ? 200 : 404);
    }    

    public function searchUsers($query) {
        $roles = $this->getRoles();
        if (empty($roles)) {
            response('error', 'Unauthorized access.', null, 403);
            return;
        }

        $sql = "SELECT username FROM user WHERE username LIKE :query LIMIT 50";
    
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            response('error', 'Failed to prepare statement.', null, 500);
            return;
        }
    
        $likeQuery = "%" . $query . "%";
        $stmt->bindParam(':query', $likeQuery, PDO::PARAM_STR);
    
        if (!$stmt->execute()) {
            response('error', 'Failed to execute query.', null, 500);
            return;
        }
    
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$users) {
            response('error', 'No results found.', null, 404);
            return;
        }
    
        $usernames = array_column($users, 'username');
        response('success', 'Users found.', $usernames, 200);
    }    
    
    public function createUser() {
        $roles = $this->getRoles();
        if (!in_array('Superadmin', $roles)) {
            response('error', 'Only superadmin can create users.', null, 403);
            return;
        }
    
        $this->username = htmlspecialchars(strip_tags($_POST['username'] ?? ''));
        $this->email = htmlspecialchars(strip_tags($_POST['email'] ?? ''));
        $this->password = htmlspecialchars(strip_tags($_POST['password'] ?? ''));
        $this->about = htmlspecialchars(strip_tags($_POST['about'] ?? ''));
    
        // Convert roles input (e.g., "1,3") to an array
        $rolesInput = $_POST['roles'] ?? '';
        
        // Check if roles are provided
        if (empty($rolesInput)) {
            response('error', 'Roles must be specified.', null, 400);
            return;
        }
    
        $this->roles = array_map('intval', explode(',', $rolesInput)); 
    
        if (isset($_FILES['avatar'])) {
            $fileUploadHelper = new FileUploadHelper();
            $this->avatar = $fileUploadHelper->uploadFile($_FILES['avatar'], 'avatar');
        }
    
        if ($this->create()) {
            $userData = [
                'username' => $this->username,
                'email' => $this->email,
                'about' => $this->about,
                'roles' => $this->roles,
                'avatar' => $this->avatar ?? null, 
            ];
            response('success', 'User created successfully.', $userData, 201);
        } else {
            response('error', 'User creation failed.', null, 400);
        }
    }
    
    
    private function create() {
        $query = "INSERT INTO " . $this->table_name . " (username, email, password, about, avatar) 
                  VALUES(:username, :email, :password, :about, :avatar)";
        $stmt = $this->db->prepare($query);
    
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        
        $hashed_password = password_hash($this->password, PASSWORD_BCRYPT);
        $stmt->bindParam(":password", $hashed_password);
        
        $stmt->bindParam(":about", $this->about);
        $stmt->bindParam(":avatar", $this->avatar);  
    
        if ($stmt->execute()) {
            $this->user_id = $this->db->lastInsertId();
            return $this->assignRoles();
        }
    
        return false;
    }   

    private function assignRoles() {
        if (empty($this->roles)) {
            error_log("No roles to assign for user_id: " . $this->user_id); // Debug roles
            return true; // Tidak ada roles, tidak perlu melanjutkan
        }
    
        if (empty($this->user_id)) {
            error_log("Error: user_id is null in assignRoles."); // Debug user_id
            return false; // Batalkan jika user_id tidak valid
        }
    
        try {
            $this->db->beginTransaction(); // Mulai transaksi
    
            // Ambil roles yang sudah ada untuk user_id ini
            $existingRolesQuery = "SELECT role_id FROM user_roles WHERE user_id = :user_id";
            $stmt = $this->db->prepare($existingRolesQuery);
            $stmt->bindValue(":user_id", $this->user_id, PDO::PARAM_INT);
            $stmt->execute();
            $existingRoles = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
            // Tambahkan roles baru jika belum ada
            $insertQuery = "INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)";
            $stmt = $this->db->prepare($insertQuery);
    
            foreach ($this->roles as $roleId) {
                if (empty($roleId)) {
                    error_log("Skipping empty role_id for user_id: " . $this->user_id);
                    continue; // Abaikan role_id yang kosong
                }
    
                if (in_array($roleId, $existingRoles)) {
                    error_log("Role_id: $roleId already exists for user_id: " . $this->user_id);
                    continue; // Abaikan jika role_id sudah ada
                }
    
                $stmt->bindValue(":user_id", $this->user_id, PDO::PARAM_INT);
                $stmt->bindValue(":role_id", $roleId, PDO::PARAM_INT);
    
                if (!$stmt->execute()) {
                    error_log("Failed to assign role_id: $roleId for user_id: " . $this->user_id);
                    $this->db->rollBack(); // Rollback jika gagal
                    return false;
                }
            }
    
            $this->db->commit(); // Commit transaksi
            return true;
    
        } catch (Exception $e) {
            $this->db->rollBack(); // Rollback jika ada error
            error_log("Transaction failed in assignRoles: " . $e->getMessage());
            return false;
        }
    }  
    

    public function updateUser($id) {
        $roles = $this->getRoles();
        $userIdFromJWT = $this->getUserId();
    
        // Only Superadmin or the user themselves can update their data
        if (!in_array('Superadmin', $roles) && $userIdFromJWT != $id) {
            response('error', 'Unauthorized access.', null, 403);
            return;
        }
    
        // Get current user data
        $currentUserData = $this->getUserDataById($id);
    
        if (!$currentUserData) {
            response('error', 'User not found.', null, 404);
            return;
        }
    
        $this->user_id = $id; // Ensure user_id is set
    
        // Allow Superadmin and the user themselves to update username and bio
        if (in_array('Superadmin', $roles) || $userIdFromJWT == $id) {
            $this->username = htmlspecialchars(strip_tags($_POST['username'] ?? $currentUserData['username']));
            $this->about = htmlspecialchars(strip_tags($_POST['about'] ?? $currentUserData['about']));
            $rolesInput = $_POST['roles'] ?? '';
            $this->roles = array_map('intval', explode(',', $rolesInput));
        } else {
            $this->username = $currentUserData['username']; // Keep the original username
            $this->about = $currentUserData['about'];       // Keep the original bio
        }
    
        // Only Superadmin can update email and roles
        if (in_array('Superadmin', $roles)) {
            $this->email = htmlspecialchars(strip_tags($_POST['email'] ?? $currentUserData['email']));
            $rolesInput = $_POST['roles'] ?? '';
            $this->roles = array_map('intval', explode(',', $rolesInput));
        } else {
            $this->email = $currentUserData['email'];  // Don't change email for members
            $this->roles = [];  // Members cannot update roles
        }
    
        // Password logic
        $this->password = null; // Set to null by default
    
        // Only change the password if a new password is provided
        if (!empty($_POST['password'])) {
            // Hash the new password
            $this->password = htmlspecialchars(strip_tags($_POST['password']));
        }
    
        // Handle avatar file upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $oldAvatar = $currentUserData['avatar'] ?? null;
    
            $fileUploadHelper = new FileUploadHelper();
    
            try {
                $this->avatar = $fileUploadHelper->uploadFile($_FILES['avatar'], 'avatar', $oldAvatar);
    
                if ($oldAvatar) {
                    $fileUploadHelper->deleteFile($oldAvatar);
                }
            } catch (Exception $e) {
                response('error', 'Failed to upload avatar. ' . $e->getMessage(), null, 500);
                return;
            }
        } else {
            $this->avatar = $currentUserData['avatar'];  // Retain the old avatar if no new one is uploaded
        }
    
        // Update user data in the database
        if ($this->update($id)) {
            if (!empty($this->roles) && !$this->assignRoles()) {
                response('error', 'User update failed while updating roles.', null, 400);
            } else {
                $updatedUserData = $this->getUserDataById($id);
    
                unset($updatedUserData['password']);  // Don't return the password in the response
    
                response('success', 'User updated successfully.', $updatedUserData, 200);
            }
        } else {
            response('error', 'User update failed.', null, 400);
        }
    }
    
    private function update($id) {
        $query = "UPDATE " . $this->table_name . " SET username = :username, email = :email, about = :about";
        
        if ($this->avatar) {
            $query .= ", avatar = :avatar"; 
        }
    
        // Only include password in the query if it's set (i.e., if the user has provided a new password)
        if (!empty($this->password)) {
            $query .= ", password = :password";  
        }
        
        $query .= " WHERE user_id = :user_id";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":about", $this->about);
        $stmt->bindParam(":user_id", $id);
        
        // If password is set, hash it and bind to the query
        if (!empty($this->password)) {
            $hashedPassword = password_hash($this->password, PASSWORD_BCRYPT);
            $stmt->bindParam(":password", $hashedPassword);
        }
        
        if ($this->avatar) {
            $stmt->bindParam(":avatar", $this->avatar);
        }
        
        return $stmt->execute();
    }    
    
    private function getUserDataById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":user_id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC); 
    }
    

    public function deleteUser($id) {
        $roles = $this->getRoles();
        if (!in_array('Superadmin', $roles)) {
            response('error', 'Only superadmin can delete users.', null, 403);
            return;
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":user_id", $id);

        if ($stmt->execute()) {
            response('success', 'User deleted successfully.', null, 200);
        } else {
            response('error', 'User deletion failed.', null, 400);
        }
    }
    // private function getOldAvatar($userId) {
    //     $stmt = $this->db->prepare("SELECT avatar FROM users WHERE user_id = ?");
    //     $stmt->execute([$userId]);
    //     $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    //     // Return the avatar filename if it exists
    //     return $result ? $result['avatar'] : null;
    // }
    
}