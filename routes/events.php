<?php
require_once '../controllers/EventController.php';
require_once '../helpers/ResponseHelper.php'; 
require_once '../helpers/JwtHelper.php'; 
require_once '../config/Database.php';

$database = new Database();
$db = $database->getConnection();
$eventController = new EventController($db);

$request_method = $_SERVER["REQUEST_METHOD"];

$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : null;

$jwtHelper = new JWTHelper();
$user_roles = [];

if (in_array($request_method, ['GET', 'POST', 'DELETE'])) {
    $user_roles = $jwtHelper->getRoles(); 
}

switch ($request_method) {
    case 'GET':
        if ($event_id) {
            $eventController->getEventById($event_id);
        } elseif (in_array('Admin', $user_roles) || in_array('Superadmin', $user_roles)) {
            $adminUserId = isset($_GET['admin_user_id']) ? (int)$_GET['admin_user_id'] : null;
            if ($adminUserId) {
                $eventController->getAllEventsAdminUser($adminUserId);  
            } else {
                $eventController->getAllEventsAdminUser(); 
            }
        } elseif (in_array('Propose', $user_roles)) {
            $user_id = $jwtHelper->getUserId();  
            $eventController->getAllEventsProposeUser($user_id);  
        } else {
            response('error', 'Unauthorized to access events.', null, 403);
        }
        break;
    

    case 'POST':
        if ($event_id) {
            if (in_array('Admin', $user_roles) || in_array('Propose', $user_roles)) {
                $eventController->updateEvent($event_id);
            } else {
                response('error', 'Unauthorized to update events.', null, 403); 
            }
        } else {
            // No event_id provided, so create a new event
            if (in_array('Propose', $user_roles)) {
                $eventController->createEvent();
            } else {
                response('error', 'Unauthorized to create events.', null, 403);
            }
        }
        break;

    case 'DELETE':
        if ($event_id) {
            if (in_array('Admin', $user_roles)) {
                $eventController->deleteEvent($event_id);
            } else {
                response('error', 'Unauthorized to delete events.', null, 403); 
            }
        } else {
            response('error', 'Missing event_id.', null, 400); 
        }
        break;

    default:
        response('error', 'Method not allowed.', null, 405); 
        break;
}
?>