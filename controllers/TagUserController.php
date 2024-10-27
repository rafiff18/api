<?php

require_once "../helpers/ResponseHelper.php"; // Helper untuk merespons JSON

class TagUserController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Mendapatkan semua tag users
    public function getAllTagUsers() {
        $query = "SELECT * FROM tag_user";
        $stmt = $this->conn->query($query);

        $data = array();
        if ($stmt) {
            while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
                $data[] = $row;
            }
            response(true, 'Tag users retrieved successfully', $data);
        } else {
            response(false, 'Failed to retrieve tag users', null, [
                'code' => 500,
                'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
            ]);
        }
    }

    // Mendapatkan tag user berdasarkan ID
    public function getTagUserById($id) {
        $query = "SELECT * FROM tag_user WHERE tag_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);
            response(true, 'Tag user retrieved successfully', $data);
        } else {
            response(false, 'Tag user not found', null, [
                'code' => 404,
                'message' => 'The requested tag user could not be found'
            ]);
        }
    }

    // Menambahkan tag user baru
    public function createTagUser() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            response(false, 'Invalid JSON input', null);
            return;
        }

        // Pastikan input berisi replay_id dan content_taguser
        $requiredFields = ['replay_id', 'content_taguser'];
        $missingParams = array_diff($requiredFields, array_keys($input));

        if (empty($missingParams)) {
            // Query untuk menambahkan tag user
            $query = "INSERT INTO tag_user (replay_id, content_taguser) VALUES (?, ?)";
            $stmt = $this->conn->prepare($query);

            if ($stmt->execute([$input['replay_id'], $input['content_taguser']])) {
                $new_id = $this->conn->lastInsertId(); // Ambil ID terbaru
                $result_stmt = $this->conn->prepare("SELECT * FROM tag_user WHERE tag_id = ?");
                $result_stmt->execute([$new_id]);
                $new_data = $result_stmt->fetch(PDO::FETCH_OBJ);
                response(true, 'Tag user added successfully', $new_data);
            } else {
                response(false, 'Failed to add tag user', null, [
                    'code' => 400,
                    'message' => 'Error: ' . $this->conn->errorInfo()[2]
                ]);
            }
        } else {
            response(false, 'Missing required fields', null, [
                'code' => 400,
                'message' => 'Missing required parameters: ' . implode(', ', $missingParams)
            ]);
        }
    }

    // Memperbarui tag user
    public function updateTagUser($id) {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            response(false, 'Invalid JSON input', null);
            return;
        }

        // Pastikan input berisi content_taguser
        $requiredFields = ['content_taguser'];
        $missingParams = array_diff($requiredFields, array_keys($input));

        if (empty($missingParams)) {
            // Query untuk memperbarui tag user
            $query = "UPDATE tag_user SET content_taguser = ? WHERE tag_id = ?";
            $stmt = $this->conn->prepare($query);

            if ($stmt->execute([$input['content_taguser'], $id])) {
                $result_stmt = $this->conn->prepare("SELECT * FROM tag_user WHERE tag_id = ?");
                $result_stmt->execute([$id]);
                $updated_data = $result_stmt->fetch(PDO::FETCH_OBJ);
                response(true, 'Tag user updated successfully', $updated_data);
            } else {
                response(false, 'Failed to update tag user', null, [
                    'code' => 500,
                    'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
                ]);
            }
        } else {
            response(false, 'Missing required fields', null, [
                'code' => 400,
                'message' => 'Missing required parameters: ' . implode(', ', $missingParams)
            ]);
        }
    }

    // Menghapus tag user
    public function deleteTagUser($id) {
        $stmt = $this->conn->prepare("DELETE FROM tag_user WHERE tag_id = ?");

        if ($stmt->execute([$id])) {
            response(true, 'Tag user deleted successfully');
        } else {
            response(false, 'Failed to delete tag user', null, [
                'code' => 500,
                'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
            ]);
        }
    }
}
