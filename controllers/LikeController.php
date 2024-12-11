<?php

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

    public function getAllLikes() {
        $query = "SELECT * FROM likes";
        
        try {
            $stmt = $this->conn->query($query);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            response('success', 'Get List of Likes Successfully', $data);
        } catch (PDOException $e) {
            response('error', 'Failed to get likes', null, 500);
        }
    }

    public function getLikeById($id = 0) {
        if ($id > 0) {
            $query = "SELECT * FROM likes WHERE like_id = ? LIMIT 1";
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

    public function getLikeByUserAndEvent($userId, $eventId) {
        try {
            $query = "SELECT like_id FROM likes WHERE user_id = ? AND event_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$userId, $eventId]);
    
            if ($stmt->rowCount() > 0) {
                $likeData = $stmt->fetch(PDO::FETCH_OBJ);
                response('success', 'Like found', array_merge((array)$likeData, ['is_liked' => true]));
            } else {
                response('error', 'No like found for this user and event', ['is_liked'=>false], 404);
            }
        } catch (PDOException $e) {
            response('error', 'Failed to retrieve like', null, 500);
        }
    }
    
    public function createLike() {
        $input = json_decode(file_get_contents('php://input'), true);
    
        if (json_last_error() === JSON_ERROR_NONE) {
            $data = $input;
        } else {
            $data = $_POST;
        }
    
        $required_fields = ['event_id', 'user_id'];
        $missing_fields = array_diff($required_fields, array_keys($data));
    
        if (!empty($missing_fields)) {
            response('error', 'Missing Parameters: ' . implode(', ', $missing_fields), null, 400);
            return;
        }
    
        try {
            if (!$this->conn) {
                response('error', 'Database connection failed', null, 500);
                return;
            }
    
            $checkQuery = "SELECT * FROM likes WHERE event_id = ? AND user_id = ?";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute([$data['event_id'], $data['user_id']]);
    
            if ($checkStmt->rowCount() > 0) {
                response('error', 'You have already liked this event', null, 400);
                return;
            }
    
            $query = "INSERT INTO likes (event_id, user_id) VALUES (?, ?)";
            $stmt = $this->conn->prepare($query);
            if (!$stmt->execute([$data['event_id'], $data['user_id']])) {
                $errorInfo = $stmt->errorInfo();
                response('error', 'Failed to Add Like: ' . $errorInfo[2], null, 500);
                return;
            }
    
            $insert_id = $this->conn->lastInsertId();
            if (empty($insert_id)) {
                response('error', 'Failed to retrieve insert ID', null, 500);
                return;
            }
    
            $result_stmt = $this->conn->prepare("SELECT * FROM likes WHERE like_id = ?");
            $result_stmt->execute([$insert_id]);
            $new_data = $result_stmt->fetch(PDO::FETCH_OBJ);
    
            if ($new_data) {
                response('success', 'Like Added Successfully', $new_data);
            } else {
                response('error', 'Failed to retrieve like data', null, 500);
            }
    
        } catch (PDOException $e) {
            response('error', 'Failed to Add Like: ' . $e->getMessage(), null, 500);
        }
    }    
    

    public function updateLike($id) {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            response('success', 'Invalid JSON Format', null, 400);
            return;
        }

        if (empty($input['event_id']) || empty($input['user_id'])) {
            response('error', 'Missing Parameters', null, 400);
            return;
        }

        try {
            $query = 'UPDATE likes SET event_id = ?, user_id = ? WHERE like_id = ?';
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$input['event_id'], $input['user_id'], $id]);

            if ($stmt->rowCount() > 0) {
                $query = "SELECT * FROM likes WHERE like_id = ?";
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

    public function deleteLike($id) {
        if ($id > 0) {
            try {
                $stmt = $this->conn->prepare('DELETE FROM likes WHERE like_id = ?');
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
