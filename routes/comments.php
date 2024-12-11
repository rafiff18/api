<?php

require_once "../controllers/CommentController.php";
require_once "../helpers/HeaderAccessControl.php";
require_once "../helpers/ResponseHelper.php";
require_once "../config/Database.php";

$database = new Database();
$conn = $database->getConnection(); 

$controller = new CommentController($conn);

$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case "GET":
        if (!empty($_GET["id"])) {
            $id = intval($_GET["id"]);
            $controller->getCommentById($id);
        } else if (!empty($_GET["event_id"])) {
            $event_id = intval($_GET["event_id"]);
            $controller->getCommentByEventId($event_id);
        } else if (!empty($_GET["comment_parent_id"])) {
            $comment_parent_id = intval($_GET["comment_parent_id"]);
            $controller->getCommentByCommentParentId($comment_parent_id);
        } else {
            $controller->getAllComments();
        }
        break;
    case "POST":
        $controller->createComment();
        break;
    case "PUT":
        if (!empty($_GET['id'])) {
            $id = intval($_GET["id"]);
            parse_str(file_get_contents("php://input"), $_PUT);
            $controller->updateComment($id);
        } else {
            response('error', 'ID is required for PUT request', null, 400);
        }
        break;
    case "DELETE":
        if (!empty($_GET["id"])) {
            $id = intval($_GET["id"]);
            $controller->deleteComment($id);
        } else {
            response('error', 'ID is required for DELETE request', null, 400);
        }
        break;
    default:
        response('error', 'Method not allowed.', null, 405); 
        break;
}
?>
