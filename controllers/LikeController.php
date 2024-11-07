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
            response('success', 'Get List of Likes Successfully', $data);
        } catch (PDOException $e) {
            response('error', 'Failed to get likes', null, 500);
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
                response('success', 'Get Like Successfully', $data);
            } else {
                response('error', 'Like not found', null, 404);
            }
        } else {
            response('error', 'Invalid ID', null, 400);
        }
    }

    // Menambahkan like baru
    public function createLike() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            response('error', 'Invalid JSON Format', null, 400);
            return;
        }

        $required_fields = ['event_id', 'users_id'];
        $missing_fields = array_diff($required_fields, array_keys($input));

        if (!empty($missing_fields)) {
            response('error', 'Missing Parameters '.implode(', ', $missing_fields), null, 400);
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

            response('success', 'Like Added Successfully', $new_data);
        } catch (PDOException $e) {
            response('error', 'Failed to Add Like', null, 500);
        }
    }

    // Memperbarui like
    public function updateLike($id) {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            response('success', 'Invalid JSON Format', null, 400);
            return;
        }

        if (empty($input['event_id']) || empty($input['users_id'])) {
            response('error', 'Missing Parameters', null, 400);
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

                response('success', 'Like Updated Successfully', $updated_data);
            } else {
                response('error', 'No changes made', null, 404);
            }
        } catch (PDOException $e) {
            response('error', 'Failed to Update Like', null, 500);
        }
    }

    // Menghapus like
    public function deleteLike($id) {
        if ($id > 0) {
            try {
                $stmt = $this->conn->prepare('DELETE FROM like_event WHERE like_id = ?');
                $stmt->execute([$id]);

                if ($stmt->rowCount() > 0) {
                    response('success', 'Like Deleted Successfully');
                } else {
                    response('error', 'Like not found', null, 404);
                }
            } catch (PDOException $e) {
                response('error', 'Failed to Delete Like', null, 500);
            }
        } else {
            response('error', 'Invalid ID', null, 400);
        }
    }
}
?>
