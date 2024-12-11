<?php

require_once "../helpers/ResponseHelper.php";

class CommentController {
    private $conn;

    public function __construct($conn) {
        if (!$conn) {
            response('error', 'Database connection failed', null, 500);
            exit;
        }
        $this->conn = $conn;
    }

    public function getAllComments() {
        $query = "SELECT c.*, u.username, u.avatar 
                  FROM comment c
                  INNER JOIN user u ON c.user_id = u.user_id";
        
        $stmt = $this->conn->query($query);
    
        if ($stmt) {
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            response('success', 'Comments list retrieved successfully', $data);
        } else {
            response('error', 'Failed to retrieve comments list', null, 500);
        }
    }
    

    public function getCommentById($id) {
        if ($id > 0) {
            $query = "SELECT * FROM comment WHERE comment_id = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                $data = $stmt->fetch(PDO::FETCH_OBJ);
                response('success', 'Comment retrieved successfully', $data);
            } else {
                response('error', 'Comment not found', null, 404);
            }
        } else {
            response('error', 'Invalid ID', 401);
        }
    }

    public function getCommentByEventId($event_id) {
        if ($event_id > 0) {
            $query = "SELECT c.comment_id, c.content, c.comment_parent_id, c.created_at, u.username, u.user_id, u.avatar, c.event_id
                      FROM comment c
                      JOIN user u ON c.user_id = u.user_id
                      WHERE c.event_id = ?
                      ORDER BY c.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$event_id]);

            if ($stmt->rowCount() > 0) {
                $data = $stmt->fetchAll(PDO::FETCH_OBJ);
                response('success', 'Comments retrieved successfully', $data);
            } else {
                response('error', 'No comments for this event', null, 404);
            }
        } else {
            response('error', 'Invalid event ID', null, 401);
        }
    }

    public function getCommentByCommentParentId($comment_parent_id) {
        if ($comment_parent_id !== null) {
            $query = "SELECT c.comment_id, c.content, c.comment_parent_id, c.created_at, u.username, u.user_id, u.avatar, c.event_id
                      FROM comment c
                      JOIN user u ON c.user_id = u.user_id
                      WHERE c.comment_parent_id = ?
                      ORDER BY c.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$comment_parent_id]);
    
            if ($stmt->rowCount() > 0) {
                $data = $stmt->fetchAll(PDO::FETCH_OBJ);
                response('success', 'Replies retrieved successfully', $data);
            } else {
                response('error', 'No replies found for this comment', null, 404);
            }
        } else {
            response('error', 'Invalid comment parent ID', null, 401);
        }
    }
    

    public function createComment() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            response('error', 'Invalid JSON format', null, 400);
            return;
        }

        $required_fields = ['user_id', 'event_id', 'content'];
        $missing_fields = array_diff($required_fields, array_keys($input));

        if (!empty($missing_fields)) {
            response('error', 'Missing parameters: ' . implode(', ', $missing_fields), null, 402);
            return;
        }

        $query = "INSERT INTO comment (user_id, event_id, content, comment_parent_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        $comment_parent_id = $input['comment_parent_id'] ?? null;

        if ($stmt->execute([$input['user_id'], $input['event_id'], $input['content'], $comment_parent_id])) {
            $insert_id = $this->conn->lastInsertId();
            $result_stmt = $this->conn->prepare("SELECT * FROM comment WHERE comment_id = ?");
            $result_stmt->execute([$insert_id]);
            $new_data = $result_stmt->fetch(PDO::FETCH_OBJ);

            response('success', 'Comment added successfully', $new_data);
        } else {
            response('error', 'Failed to add comment', null, 500);
        }
    }

    public function updateComment($id) {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            response('error', 'Invalid JSON format', null, 400);
            return;
        }

        if (empty($input['content'])) {
            response('error', 'Comment content is missing', null, 403);
            return;
        }

        $query = 'UPDATE comment SET content = ? WHERE comment_id = ?';
        $stmt = $this->conn->prepare($query);

        if ($stmt->execute([$input['content'], $id])) {
            $result_stmt = $this->conn->prepare("SELECT * FROM comment WHERE comment_id = ?");
            $result_stmt->execute([$id]);
            $updated_data = $result_stmt->fetch(PDO::FETCH_OBJ);

            response('success', 'Comment updated successfully', $updated_data);
        } else {
            response('error', 'Failed to update comment', null, 500);
        }
    }

    public function deleteComment($id) {
        $stmt = $this->conn->prepare('DELETE FROM comment WHERE comment_id = ?');

        if ($stmt->execute([$id])) {
            response('success', 'Comment deleted successfully', null);
        } else {
            response('error', 'Failed to delete comment', null, 500);
        }
    }
}

?>
