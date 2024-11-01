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
        if (!empty($_GET['user_id']) && !empty($_GET['event_id'])) {
            $user_id = intval($_GET["user_id"]);
            $event_id = intval($_GET["event_id"]);
            $controller->isUserJoined($user_id, $event_id);
        } elseif (!empty($_GET['upcoming']) && !empty($_GET['user_id'])) {
            $user_id = intval($_GET['user_id']);
            $controller->upcomingEvent($user_id);
        } elseif (!empty($_GET["user_id"])) {
            $user_id = intval($_GET["user_id"]);
            $controller->getEventByUserId($user_id);
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