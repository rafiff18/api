<?php

require_once "../database/Database.php";
require_once "../helpers/ResponseHelper.php";

class NewCommentController {
    private $conn;

    public function __construct($conn) {
        if (!$conn) {
            response('error', 'Koneksi database gagal', null, 500);
            exit;
        }
        $this->conn = $conn;
    }

    // 1. AMBIL SEMUA KOMENTAR
    public function getAllComments() {
        $query = "SELECT * FROM comment";
        $stmt = $this->conn->query($query);

        if ($stmt) {
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            response('success', 'Daftar komentar berhasil diambil', $data);
        } else {
            response('error', 'Gagal mengambil daftar komentar', null, 500);
        }
    }

    // 2. AMBIL KOMENTAR BERDASARKAN ID
    public function getCommentById($id) {
        if ($id > 0) {
            $query = "SELECT * FROM comment WHERE comment_id = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                $data = $stmt->fetch(PDO::FETCH_OBJ);
                response('success', 'Komentar berhasil diambil', $data);
            } else {
                response('error', 'Komentar tidak ditemukan', null, 404);
            }
        } else {
            response('error', 'ID tidak valid', 401);
        }
    }

    // 3. AMBIL KOMENTAR BERDASARKAN EVENT ID
    public function getCommentByEventId($event_id) {
        if ($event_id > 0) {
            $query = "SELECT c.comment_id, c.content, c.comment_parent_id, c.created_at, u.username
                      FROM comment c
                      JOIN users u ON c.users_id = u.users_id
                      WHERE c.event_id = ?
                      ORDER BY c.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$event_id]);

            if ($stmt->rowCount() > 0) {
                $data = $stmt->fetchAll(PDO::FETCH_OBJ);
                response('success', 'Komentar berhasil diambil', $data);
            } else {
                response('error', 'Tidak ada komentar untuk event ini', null, 404);
            }
        } else {
            response('error', 'ID event tidak valid', null, 401);
        }
    }

    // 4. TAMBAHKAN KOMENTAR
    public function createComment() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            response('error', 'Format JSON tidak valid', null, 400);
            return;
        }

        $required_fields = ['users_id', 'event_id', 'content'];
        $missing_fields = array_diff($required_fields, array_keys($input));

        if (!empty($missing_fields)) {
            response('error', 'Parameter yang hilang: ' . implode(', ', $missing_fields), null, 402);
            return;
        }

        $query = "INSERT INTO comment (users_id, event_id, content, comment_parent_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        $comment_parent_id = $input['comment_parent_id'] ?? null;

        if ($stmt->execute([$input['users_id'], $input['event_id'], $input['content'], $comment_parent_id])) {
            $insert_id = $this->conn->lastInsertId();
            $result_stmt = $this->conn->prepare("SELECT * FROM comment WHERE comment_id = ?");
            $result_stmt->execute([$insert_id]);
            $new_data = $result_stmt->fetch(PDO::FETCH_OBJ);

            response('success', 'Komentar berhasil ditambahkan', $new_data);
        } else {
            response('error', 'Gagal menambahkan komentar', null, 500);
        }
    }

    // 5. PERBARUI KOMENTAR
    public function updateComment($id) {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            response('error', 'Format JSON tidak valid', null, 400);
            return;
        }

        if (empty($input['content'])) {
            response('error', 'Parameter konten komentar hilang', null, 403);
            return;
        }

        $query = 'UPDATE comment SET content = ? WHERE comment_id = ?';
        $stmt = $this->conn->prepare($query);

        if ($stmt->execute([$input['content'], $id])) {
            $result_stmt = $this->conn->prepare("SELECT * FROM comment WHERE comment_id = ?");
            $result_stmt->execute([$id]);
            $updated_data = $result_stmt->fetch(PDO::FETCH_OBJ);

            response('success', 'Komentar berhasil diperbarui', $updated_data);
        } else {
            response('error', 'Gagal memperbarui komentar', null,  500);
        }
    }

    // 6. HAPUS KOMENTAR
    public function deleteComment($id) {
        $stmt = $this->conn->prepare('DELETE FROM comment WHERE comment_id = ?');

        if ($stmt->execute([$id])) {
            response('success', 'Komentar berhasil dihapus', null);
        } else {
            response('error', 'Gagal menghapus komentar', null, 500);
        }
    }
}

?>
