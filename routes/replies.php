<?php

require_once "../helpers/HeaderAccessControl.php";
require_once "../database/Database.php";
require_once "../controllers/ReplayCommentController.php";

$database = new Database();
$conn = $database->getConnection();

$controller = new ReplayCommentController($conn);
$request_method = $_SERVER["REQUEST_METHOD"];
$data = json_decode(file_get_contents("php://input"));

switch ($request_method) {
    case 'POST':
        echo $controller->create($data);
        break;

    case 'GET':
        echo $controller->read();
        break;

    case 'PUT':
        echo $controller->update($data);
        break;

    case 'DELETE':
        echo $controller->delete($data);
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?>
