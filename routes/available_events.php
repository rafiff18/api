<?php
require_once '../controllers/EventController.php';
require_once '../config/Database.php';
require_once '../helpers/JwtHelper.php'; 
require_once '../helpers/ResponseHelper.php';

$database = new Database();
$db = $database->getConnection();
$eventController = new EventController($db);

$request_method = $_SERVER["REQUEST_METHOD"];
$jwtHelper = new JWTHelper();
$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : null;


switch ($request_method) {
    case 'GET':
        if (!empty($event_id)) {
            $eventController->getEventById($event_id);
        } else {
            $eventController->getAllEvents();
        }
        break;

    default:
        response('error', 'Method not allowed.', null, 405); 
        break;
}
?>
