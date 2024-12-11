<?php

require_once "../helpers/HeaderAccessControl.php";
require_once "../controllers/LikeController.php";
require_once "../helpers/ResponseHelper.php";
require_once "../config/Database.php";

$database = new Database();
$conn = $database->getConnection(); 

$controller = new LikeController($conn);

$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case "GET":
        if (!empty($_GET['event_id']) && !empty($_GET['user_id'])) {
           $event_id = intval($_GET['event_id']);
           $user_id = intval($_GET['user_id']);
           $controller->getLikeByUserAndEvent($user_id, $event_id);
       } else if (!empty($_GET["id"])) {
            $id = intval($_GET["id"]);
            $controller->getLikeById($id);
        } else {
            $controller->getAllLikes();
        }
        break;
        
    case "POST":
        $controller->createLike();
        break;

    case "PUT":
        if (!empty($_GET['id'])) {
            $id = intval($_GET["id"]);
            parse_str(file_get_contents("php://input"), $_PUT);
            $controller->updateLike($id);
        } else {
            response('error', 'ID is required for PUT request', null, 400);
        }
        break;

    case "DELETE":
        $id = intval($_GET["id"]);
        $controller->deleteLike($id);
        break;

    default:
        response('error', 'Method not allowed.', null, 405);
        break;
}
?>
