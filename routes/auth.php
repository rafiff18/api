<?php
require_once "../helpers/HeaderAccessControl.php";
require_once '../controllers/AuthController.php';
require_once "../config/Database.php";
require_once "../helpers/ResponseHelper.php";

session_start(); 

$database = new Database();
$conn = $database->getConnection();
$authController = new AuthController($conn);

$request_method = $_SERVER["REQUEST_METHOD"];

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");

switch ($request_method) {
    case 'GET':
        $authController->checkLogin();
        break;

    case 'POST':
        $authController->login();
        break;

    case 'DELETE':
        $authController->logout();
        break;
        
    default:
        response('error', 'Method not allowed.', null, 405); 
        break;
}
?>
