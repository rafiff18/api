<?php

require_once "../helpers/HeaderAccessControl.php";
require_once "../controllers/UserController.php";
require_once "../database/Database.php";

$database = new Database();
$conn = $database->getConnection();

$controller = new UserController($conn);
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case "GET":
       if (!empty($_GET["id"])) {
        $id = intval($_GET["id"]);
        $controller->getUserById($id);
       } else {
        $controller->getAllUsers();
       }
       break;
       case "POST":
        if (isset($_GET['action']) && $_GET['action'] == 'forgot_password') {
            $controller->forgotPassword();
        } else if (isset($_GET['action']) && $_GET['action'] == 'reset_password') {
            if (!empty($_GET['id'])) {
                $id = intval($_GET["id"]);
                $controller->resetPassword($id);
            } else {
                header("HTTP/1.0 400 Bad Request");
                echo json_encode(['message' => 'ID is required for reset password request']);
            }
        } else {
            $controller->createUser();
        }
        break;
    
        break;
    case "PUT":
        if(!empty($_GET['id'])) {
            $id = intval($_GET["id"]);
            
            parse_str(file_get_contents("php://input"), $_PUT);

            $controller->updateUser($id);
        } else {
            header("HTTP/1.0 400 Bad Request");
            echo json_encode(array('message' => 'ID is required for PUT request'));
        }
        break;
    case "DELETE":
        $id = intval($_GET["users_id"]); // 
        $controller->deleteUser($id);
        break;
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        break;
        
}

?>
