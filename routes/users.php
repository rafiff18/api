<?php
require_once '../controllers/UserController.php';
require_once "../helpers/ResponseHelper.php";
require_once '../config/Database.php';

$database = new Database();
$db = $database->getConnection();

$userController = new UserController($db);

$request_method = $_SERVER["REQUEST_METHOD"];
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$query = isset($_GET['query']) ? $_GET['query'] : null;

switch ($request_method) {
    case 'GET':
        if ($query !== null) {
            $userController->searchUsers($query);  
        } elseif ($user_id !== null) {
            $userController->getUserById($user_id); 
        } else {
            $userController->getAllUsers(); 
        }
        break;

    case 'POST':
        if ($user_id) {
            $userController->updateUser($user_id);  
        } else {
            $userController->createUser();
        }
        break;

    case 'DELETE':
        if ($user_id) {
            $userController->deleteUser($user_id);  
        } else {
            response('error', 'User ID is required.', null, 400);  
        }
        break;

    default:
        response('error', 'Method not allowed.', null, 405); 
        break;
}
?>