<?php
require_once "../controllers/RegistrationEventController.php";
require_once "../helpers/ResponseHelper.php";
require_once "../config/Database.php";

$database = new Database();
$conn = $database->getConnection();

$controller = new RegistrationEventController($conn);
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case "GET":
        if (!empty($_GET["user_id"]) && !empty($_GET["event_id"])) {
            $user_id = intval($_GET["user_id"]);
            $event_id = intval($_GET["event_id"]);
            $controller->checkIsUserJoined($user_id, $event_id);
        } else if (!empty($_GET["user_id"]) && !empty($_GET['upcoming']) ) {
            $user_id = intval($_GET["user_id"]);
            $controller->getUpcomingEventUserRegist($user_id);
        } else if (!empty($_GET["user_id"])) {
            $user_id = intval($_GET["user_id"]);
            $controller->getEventUserRegist($user_id);
        } 
        break;

    case "POST":
        $controller->register();
        break;

    default:
        response('error', 'Method not allowed.', null, 405);
        break;
}
?>
