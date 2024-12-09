<?php

require_once "../helpers/HeaderAccessControl.php";
require_once "../database/Database.php";
require_once "../controllers/NewCommentController.php";

// Membuat instance dari kelas Database
$databaseInstance = new Database();
$databaseConnection = $databaseInstance->getConnection(); 

// Membuat instance dari NewCommentController
$newCommentController = new NewCommentController($databaseConnection);

$requestMethod = $_SERVER["REQUEST_METHOD"];

switch ($requestMethod) {
    case "GET":
        if (!empty($_GET["id"])) {
            $commentId = intval($_GET["id"]);
            $newCommentController->getCommentById($commentId);
        } else if (!empty($_GET["event_id"])) {
            $eventId = intval($_GET["event_id"]);
            $newCommentController->getCommentByEventId($eventId);
        } else {
            $newCommentController->getAllComments();
        }
        break;
    case "POST":
        $newCommentController->createComment();
        break;
    case "PUT":
        if (!empty($_GET['id'])) {
            $commentId = intval($_GET["id"]);
            parse_str(file_get_contents("php://input"), $_PUT);
            $newCommentController->updateComment($commentId);
        } else {
            header("HTTP/1.0 400 Bad Request");
            echo json_encode(array('message' => 'ID diperlukan untuk permintaan PUT'));
        }
        break;
    case "DELETE":
        if (!empty($_GET["id"])) {
            $commentId = intval($_GET["id"]);
            $newCommentController->deleteComment($commentId);
        } else {
            header("HTTP/1.0 400 Bad Request");
            echo json_encode(array('message' => 'ID diperlukan untuk permintaan DELETE'));
        }
        break;
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        echo json_encode(array('message' => 'Metode Tidak Diizinkan'));
        break;
}
?>
