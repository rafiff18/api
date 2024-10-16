<?php

require_once "../helpers/ResponseHelper.php";

class CategoryController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Mendapatkan semua kategori
    public function getAllCategories() {
        $query = "SELECT * FROM category";
        $stmt = $this->conn->query($query);

        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $data[] = $row;
        }

        response(true, 'List of Categories Retrieved Successfully', $data);
    }

    // Mendapatkan kategori berdasarkan ID
    public function getCategoryById($id) {
        $query = "SELECT * FROM category WHERE category_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);
            response(true, 'Category Retrieved Successfully', $data);
        } else {
            response(false, 'Category Not Found', null, [
                'code' => 404,
                'message' => 'The requested resource could not be found'
            ]);
        }
    }

    // Menambahkan kategori baru
    public function createCategory() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (is_null($input) || !isset($input['category_name'])) {
            response(false, 'Invalid JSON or Missing category_name', null, [
                'code' => 400,
                'message' => 'Bad request: category_name is required'
            ]);
            return;
        }

        $query = "INSERT INTO category (category_name) VALUES (?)";
        $stmt = $this->conn->prepare($query);

        if ($stmt->execute([$input['category_name']])) {
            $id = $this->conn->lastInsertId();
            $this->getCategoryById($id); // Menampilkan data yang baru ditambahkan
        } else {
            response(false, 'Failed to Create Category', null, [
                'code' => 500,
                'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
            ]);
        }
    }

    // Memperbarui kategori
    public function updateCategory($id) {
        $input = json_decode(file_get_contents('php://input'), true);

        if (is_null($input) || !isset($input['category_name'])) {
            response(false, 'Invalid JSON or Missing category_name', null, [
                'code' => 400,
                'message' => 'Bad request: category_name is required'
            ]);
            return;
        }

        $query = "UPDATE category SET category_name = ? WHERE category_id = ?";
        $stmt = $this->conn->prepare($query);

        if ($stmt->execute([$input['category_name'], $id])) {
            $this->getCategoryById($id); // Menampilkan data yang diperbarui
        } else {
            response(false, 'Failed to Update Category', null, [
                'code' => 500,
                'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
            ]);
        }
    }

    // Menghapus kategori
    public function deleteCategory($id) {
        $stmt = $this->conn->prepare("DELETE FROM category WHERE category_id = ?");

        if ($stmt->execute([$id])) {
            response(true, 'Category Deleted Successfully');
        } else {
            response(false, 'Failed to Delete Category', null, [
                'code' => 500,
                'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
            ]);
        }
    }
}
?>