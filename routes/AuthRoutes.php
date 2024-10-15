<?php
require_once '../controllers/AuthController.php';
require_once "../database/Database.php";

session_start(); // Pastikan sesi dimulai di awal

// Handle preflight request (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://localhost:3306"); // Ganti dengan origin frontend Anda
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Max-Age: 3600");
    header("HTTP/1.1 204 No Content");
    exit(0); // Stop eksekusi untuk request OPTIONS
}

$database = new Database();
$db = $database->getConnection();
$authController = new AuthController($db);

// Handle HTTP methods
$request_method = $_SERVER["REQUEST_METHOD"];

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");

switch ($request_method) {
    case 'POST':
        // Untuk login
        $authController->login();
        break;

    case 'DELETE':
        // Untuk logout
        $authController->logout();
        break;

    default:
        header("HTTP/1.0 405 Method Not Allowed");
        echo json_encode([
            'status' => 'error',
            'message' => 'Method not allowed.'
        ], JSON_PRETTY_PRINT);
        break;
}
?>
