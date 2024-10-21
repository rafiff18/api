<?php

require_once "../helpers/HeaderAccessControl.php";
require_once "../database/Database.php";
require_once "../controllers/SessionController.php";

// Membuat instance dari kelas Database
$database = new Database();
$conn = $database->getConnection(); 

// Membuat instance dari SessionController
$controller = new SessionController($conn);

$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case "GET":
        $controller->getSession();
        break;
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        break;
}
?>
