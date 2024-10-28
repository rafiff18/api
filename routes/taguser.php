<?php

require_once "../controllers/TagUserController.php";
require_once "../database/Database.php";

// Inisialisasi database
$database = new Database();
$conn = $database->getConnection();

// Inisialisasi controller
$controller = new TagUserController($conn);

// Mendapatkan request method (GET, POST, PUT, DELETE)
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case "GET":
        if (!empty($_GET["id"])) {
            // Jika ada ID, ambil satu data tag_user
            $id = intval($_GET["id"]);
            $controller->getTagUserById($id);
        } else {
            // Jika tidak ada ID, ambil semua data tag_user
            $controller->getAllTagUsers();
        }
        break;

    case "POST":
        // Buat tag user baru
        $controller->createTagUser();
        break;

    case "PUT":
        // Memperbarui tag user
        if (!empty($_GET["id"])) {
            $id = intval($_GET["id"]);
            $controller->updateTagUser($id);
        } else {
            header("HTTP/1.0 400 Bad Request");
            echo json_encode(array('message' => 'ID is required for PUT request'));
        }
        break;

    case "DELETE":
        // Menghapus tag user berdasarkan ID
        if (!empty($_GET["id"])) {
            $id = intval($_GET["id"]);
            $controller->deleteTagUser($id);
        } else {
            header("HTTP/1.0 400 Bad Request");
            echo json_encode(array('message' => 'ID is required for DELETE request'));
        }
        break;

    default:
        // Jika method tidak dikenali, kirimkan respon 405 (Method Not Allowed)
        header("HTTP/1.0 405 Method Not Allowed");
        echo json_encode(array('message' => 'Method not allowed'));
        break;
}
?>
