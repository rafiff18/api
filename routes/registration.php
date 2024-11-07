<?php
require_once "../helpers/HeaderAccessControl.php";
require_once "../database/Database.php";
require_once "../controllers/RegistrationEventController.php";

$database = new Database();
$conn = $database->getConnection();

$controller = new RegistrationEventController($conn);
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case "GET": 
        if (!empty($_GET["users_id"])) {
            $users_id = intval($_GET["users_id"]);
            $controller->getEventByUserId($users_id);
        } else if (!empty($_GET["user_id"]) && ($_GET["event_id"])) {
            $user_id = intval($_GET["user_id"]);
            $event_id = intval($_GET["event_id"]);
            $controller->isUserJoined($user_id, $event_id);
        }
        break;
    case "POST":
        $controller->register();
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
        break;
}

?>