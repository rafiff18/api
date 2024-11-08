<?php

require_once "../database/Database.php";
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

    // 1. GET ALL COMMENTS
    public function getAllComments() {
        $query = "SELECT * FROM comment_event";
        $stmt = $this->conn->query($query);

        if ($stmt) {
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            response('success', 'List of Comments Retrieved Successfully', $data);
        } else {
            response('error', 'Failed to Retrieve Comments', null, 500);
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
                response('success', 'Comment Retrieved Successfully', $data);
            } else {
                response('error', 'Comment Not Found', null, 404);
            }
        } else {
            response('error', 'Invalid ID', 401);
        }
    }

    public function getCommentByEventId($event_id) {
        if ($event_id > 0) {
            $query = "SELECT c.comment_id, c.content_comment, c.created_at, u.username, u.users_id
            FROM comment_event c
            JOIN users u ON c.users_id = u.users_id
            WHERE c.event_id = ?
            ORDER BY c.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$event_id]);

            if ($stmt->rowCount() > 0) {
                $data = $stmt->fetchAll(PDO::FETCH_OBJ);
                response('success', 'Comments Retrieved Successfully', $data);
            } else {
                response('error', 'No Comments Found for this Event', null, 404);
            }
        } else {
            response('error', 'Invalid Event ID', null, 401);
        }
    }

    // 3. CREATE COMMENT
    public function createComment() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            response('error', 'Invalid JSON Format', null, 400);
            return;
        }

        $required_fields = ['users_id', 'event_id', 'content_comment'];
        $missing_fields = array_diff($required_fields, array_keys($input));

        if (!empty($missing_fields)) {
            response('error', 'Missing Parameters', null, 402);
            return;
        }

        $query = "INSERT INTO comment_event (users_id, event_id, content_comment) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        if ($stmt->execute([$input['users_id'], $input['event_id'], $input['content_comment']])) {
            $insert_id = $this->conn->lastInsertId();
            $result_stmt = $this->conn->prepare("SELECT * FROM comment_event WHERE comment_id = ?");
            $result_stmt->execute([$insert_id]);
            $new_data = $result_stmt->fetch(PDO::FETCH_OBJ);

            response('success', 'Comment Added Successfully', $new_data);
        } else {
            response('error', 'Failed to Add Comment', null, 500);
        }
    }

    // 4. UPDATE COMMENT
    public function updateComment($id) {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            response('error', 'Invalid JSON Format', null, 400);
            return;
        }

        if (empty($input['content_comment'])) {
            response('error', 'Missing Parameters', null, 403);
            return;
        }

        $query = 'UPDATE comment_event SET content_comment = ? WHERE comment_id = ?';
        $stmt = $this->conn->prepare($query);

        if ($stmt->execute([$input['content_comment'], $id])) {
            $result_stmt = $this->conn->prepare("SELECT * FROM comment_event WHERE comment_id = ?");
            $result_stmt->execute([$id]);
            $updated_data = $result_stmt->fetch(PDO::FETCH_OBJ);

            response('success', 'Comment Updated Successfully', $updated_data);
        } else {
            response('error', 'Failed to Update Comment', null,  500);
        }
    }

    // 5. DELETE COMMENT
    public function deleteComment($id) {
        $stmt = $this->conn->prepare('DELETE FROM comment_event WHERE comment_id = ?');

        if ($stmt->execute([$id])) {
            response('success', 'Comment Deleted Successfully', null);
        } else {
            response('error', 'Failed to Delete Comment', null, 500);
        }
    }
}
?>
