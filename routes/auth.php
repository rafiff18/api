<?php
require_once "../helpers/HeaderAccessControl.php";
require_once '../controllers/AuthController.php';
require_once "../database/Database.php";

session_start(); // Pastikan sesi dimulai di awal

$database = new Database();
$conn = $database->getConnection();
$authController = new AuthController($conn);

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
