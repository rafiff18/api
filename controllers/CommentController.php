<?php

require_once "../database/Database.php";
require_once "../helpers/ResponseHelper.php";

class CommentController {
    private $conn;

    public function __construct($conn) {
        if (!$conn) {
            response(false, 'Database connection failed');
            exit;
        }
        $this->conn = $conn;
    }

    // 1. GET ALL COMMENTS
    public function getAllComments() {
        $query = "SELECT * FROM comment_event";
        $stmt = $this->conn->query($query);

        if ($stmt) {
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            response(true, 'List of Comments Retrieved Successfully', $data);
        } else {
            response(false, 'Failed to Retrieve Comments', null, 'Internal server error: ' . $this->conn->errorInfo()[2], 500);
        }
    }

    // 2. GET COMMENT BY ID
    public function getCommentById($id) {
        if ($id > 0) {
            $query = "SELECT * FROM comment_event WHERE comment_id = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                $data = $stmt->fetch(PDO::FETCH_OBJ);
                response(true, 'Comment Retrieved Successfully', $data);
            } else {
                response(false, 'Comment Not Found', null, 'The requested resource could not be found', 404);
            }
        } else {
            response(false, 'Invalid ID', null, 'Bad request: ID must be greater than 0', 401);
        }
    }

    public function getCommentByEventId($event_id) {
        if ($event_id > 0) {
            $query = "SELECT c.comment_id, c.content_comment, u.username
            FROM comment_event c
            JOIN users u ON c.users_id = u.users_id
            WHERE c.event_id = ?
            ORDER BY c.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$event_id]);

            if ($stmt->rowCount() > 0) {
                $data = $stmt->fetchAll(PDO::FETCH_OBJ);
                response(true, 'Comments Retrieved Successfully', $data);
            } else {
                response(false, 'No Comments Found for this Event', null, 'No comments found for the specified event', 404);
            }
        } else {
            response(false, 'Invalid Event ID', null, 'Bad request: Event ID must be greater than 0', 401);
        }
    }

    // 3. CREATE COMMENT
    public function createComment() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            response(false, 'Invalid JSON Format', null, 'JSON parsing error', 400);
            return;
        }

        $required_fields = ['users_id', 'event_id', 'content_comment'];
        $missing_fields = array_diff($required_fields, array_keys($input));

        if (!empty($missing_fields)) {
            response(false, 'Missing Parameters', null, 'Missing required parameters: ' . implode(', ', $missing_fields), 402);
            return;
        }

        $query = "INSERT INTO comment_event (users_id, event_id, content_comment) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        if ($stmt->execute([$input['users_id'], $input['event_id'], $input['content_comment']])) {
            $insert_id = $this->conn->lastInsertId();
            $result_stmt = $this->conn->prepare("SELECT * FROM comment_event WHERE comment_id = ?");
            $result_stmt->execute([$insert_id]);
            $new_data = $result_stmt->fetch(PDO::FETCH_OBJ);

            response(true, 'Comment Added Successfully', $new_data);
        } else {
            response(false, 'Failed to Add Comment', null, 'Internal server error: ' . $this->conn->errorInfo()[2], 500);
        }
    }

    // 4. UPDATE COMMENT
    public function updateComment($id) {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            response(false, 'Invalid JSON Format', null, 'JSON parsing error', 400);
            return;
        }

        if (empty($input['content_comment'])) {
            response(false, 'Missing Parameters', null, 'Missing required parameter: Content Comment', 403);
            return;
        }

        $query = 'UPDATE comment_event SET content_comment = ? WHERE comment_id = ?';
        $stmt = $this->conn->prepare($query);

        if ($stmt->execute([$input['content_comment'], $id])) {
            $result_stmt = $this->conn->prepare("SELECT * FROM comment_event WHERE comment_id = ?");
            $result_stmt->execute([$id]);
            $updated_data = $result_stmt->fetch(PDO::FETCH_OBJ);

            response(true, 'Comment Updated Successfully', $updated_data);
        } else {
            response(false, 'Failed to Update Comment', null, 'Internal server error: ' . $this->conn->errorInfo()[2], 500);
        }
    }

    // 5. DELETE COMMENT
    public function deleteComment($id) {
        $stmt = $this->conn->prepare('DELETE FROM comment_event WHERE comment_id = ?');

        if ($stmt->execute([$id])) {
            response(true, 'Comment Deleted Successfully');
        } else {
            response(false, 'Failed to Delete Comment', null, 'Internal server error: ' . $this->conn->errorInfo()[2], 500);
        }
    }
}
?>
