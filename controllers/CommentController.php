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
            response(false, 'Failed to Retrieve Comments', null, [
                'code' => 500,
                'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
            ]);
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
                response(false, 'Comment Not Found', null, [
                    'code' => 404,
                    'message' => 'The requested resource could not be found'
                ]);
            }
        } else {
            response(false, 'Invalid ID', null, [
                'code' => 401,
                'message' => 'Bad request: ID must be greater than 0'
            ]);
        }
    }

    // 3. CREATE COMMENT
    public function createComment() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            response(false, 'Invalid JSON Format', null, [
                'code' => 400,
                'message' => 'JSON parsing error'
            ]);
            return;
        }

        $required_fields = ['users_id', 'event_id', 'content_comment'];
        $missing_fields = array_diff($required_fields, array_keys($input));

        if (!empty($missing_fields)) {
            response(false, 'Missing Parameters', null, [
                'code' => 402,
                'message' => 'Missing required parameters: ' . implode(', ', $missing_fields)
            ]);
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
            response(false, 'Failed to Add Comment', null, [
                'code' => 500,
                'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
            ]);
        }
    }

    // 4. UPDATE COMMENT
    public function updateComment($id) {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            response(false, 'Invalid JSON Format', null, [
                'code' => 400,
                'message' => 'JSON parsing error'
            ]);
            return;
        }

        if (empty($input['content_comment'])) {
            response(false, 'Missing Parameters', null, [
                'code' => 403,
                'message' => 'Missing required parameter: content_comment'
            ]);
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
            response(false, 'Failed to Update Comment', null, [
                'code' => 500,
                'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
            ]);
        }
    }

    // 5. DELETE COMMENT
    public function deleteComment($id) {
        $stmt = $this->conn->prepare('DELETE FROM comment_event WHERE comment_id = ?');

        if ($stmt->execute([$id])) {
            response(true, 'Comment Deleted Successfully');
        } else {
            response(false, 'Failed to Delete Comment', null, [
                'code' => 500,
                'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
            ]);
        }
    }
}
?>
