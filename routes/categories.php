<?php

require_once "../controllers/CategoryController.php";
require_once "../config/Database.php";
require_once '../helpers/JwtHelper.php'; 
require_once '../helpers/ResponseHelper.php'; 

$db = new Database();
$conn = $db->getConnection();

$categoryController = new CategoryController($conn);

$request_method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

$jwtHelper = new JWTHelper();
$user_roles = [];

if (in_array($request_method, ['POST', 'PUT', 'DELETE'])) {
    $user_roles = $jwtHelper->getRoles(); 
}

switch ($request_method) {
    case 'GET':
        if ($id) {
            $categoryController->getCategoryById($id);
        } else {
            $categoryController->getAllCategories();
        }
        break;

    case 'POST':
        if (in_array('Admin', $user_roles) || in_array('Superadmin', $user_roles)) {
            $categoryController->createCategory();
        } else {
            response('Failed', 'Unauthorized to create category', null, 403);
        }
        break;

    case 'PUT':
        if ($id) {
            if (in_array('Admin', $user_roles) || in_array('Superadmin', $user_roles)) {
                $categoryController->updateCategory($id);
            } else {
                response('Failed', 'Unauthorized to update category', null, 403);
            }
        } else {
            response('Failed', 'Bad request: ID is required', null, 400);
        }
        break;

    case 'DELETE':
        if ($id) {
            if (!in_array('Admin', $user_roles) && !in_array('Superadmin', $user_roles)) {
                response('Failed', 'Unauthorized to delete category', null, 403);
            }
        } else {
            response('Failed', 'Bad request: ID is required', null, 400);
        }
        break;

    default:
        response('error', 'Method not allowed.', null, 405); 
        break;
}
?>