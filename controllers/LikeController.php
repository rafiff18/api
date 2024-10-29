<?php

require_once "../database/Database.php"; 
require_once "../helpers/ResponseHelper.php";

class LikeController {
    private $conn;

    public function __construct($conn) {
        if (!$conn) {
            response(false, 'Database connection failed');
            exit;
        }
        $this->conn = $conn;
    }

    // Mendapatkan semua likes
    public function getAllLikes() {
        $query = "SELECT * FROM like_event";
        
        try {
            $stmt = $this->conn->query($query);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            response(true, 'Get List of Likes Successfully', $data);
        } catch (PDOException $e) {
            response(false, 'Failed to get likes', null, 'Internal server error: ' . $e->getMessage(), 500);
        }
    }

    // Mendapatkan like berdasarkan ID
    public function getLikeById($id = 0) {
        if ($id > 0) {
            $query = "SELECT * FROM like_event WHERE like_id = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                $data = $stmt->fetch(PDO::FETCH_OBJ);
                response(true, 'Get Like Successfully', $data);
            } else {
                response(false, 'Like not found', null, 'The requested resource could not be found', 404);
            }
        } else {
            response(false, 'Invalid ID', null, 'Bad request: ID is required', 400);
        }
    }

    // Menambahkan like baru
    public function createLike() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            response(false, 'Invalid JSON Format', null, 'JSON parsing error', 400);
            return;
        }

        $required_fields = ['event_id', 'users_id'];
        $missing_fields = array_diff($required_fields, array_keys($input));

        if (!empty($missing_fields)) {
            response(false, 'Missing Parameters', null, 'Bad request: Missing required parameters: ' . implode(', ', $missing_fields), 400);
            return;
        }

        try {
            $query = "INSERT INTO like_event (event_id, users_id) VALUES (?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$input['event_id'], $input['users_id']]);
            
            $insert_id = $this->conn->lastInsertId();
            $result_stmt = $this->conn->prepare("SELECT * FROM like_event WHERE like_id = ?");
            $result_stmt->execute([$insert_id]);
            $new_data = $result_stmt->fetch(PDO::FETCH_OBJ);

            response(true, 'Like Added Successfully', $new_data);
        } catch (PDOException $e) {
            response(false, 'Failed to Add Like', null, 'Internal server error: ' . $e->getMessage(), 500);
        }
    }

    // Memperbarui like
    public function updateLike($id) {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            response(false, 'Invalid JSON Format', null, 'JSON parsing error', 400);
            return;
        }

        if (empty($input['event_id']) || empty($input['users_id'])) {
            response(false, 'Missing Parameters', null, 'Both event id and users id are required', 400);
            return;
        }

        try {
            $query = 'UPDATE like_event SET event_id = ?, users_id = ? WHERE like_id = ?';
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$input['event_id'], $input['users_id'], $id]);

            if ($stmt->rowCount() > 0) {
                $query = "SELECT * FROM like_event WHERE like_id = ?";
                $result_stmt = $this->conn->prepare($query);
                $result_stmt->execute([$id]);
                $updated_data = $result_stmt->fetch(PDO::FETCH_OBJ);

                response(true, 'Like Updated Successfully', $updated_data);
            } else {
                response(false, 'No changes made', null, 'No like found with the provided ID or no changes made', 404);
            }
        } catch (PDOException $e) {
            response(false, 'Failed to Update Like', null, 'Internal server error: ' . $e->getMessage(), 500);
        }
    }

    // Menghapus like
    public function deleteLike($id) {
        if ($id > 0) {
            try {
                $stmt = $this->conn->prepare('DELETE FROM like_event WHERE like_id = ?');
                $stmt->execute([$id]);

                if ($stmt->rowCount() > 0) {
                    response(true, 'Like Deleted Successfully');
                } else {
                    response(false, 'Like not found', null, 'No like found with the provided ID', 404);
                }
            } catch (PDOException $e) {
                response(false, 'Failed to Delete Like', null, 'Internal server error: ' . $e->getMessage(), 500);
            }
        } else {
            response(false, 'Invalid ID', null, 'Like ID must be provided and greater than 0', 400);
        }
    }
}
?>
