<?php
require_once '../controllers/EventController.php';
require_once '../helpers/ResponseHelper.php'; 
require_once '../helpers/JwtHelper.php'; 
require_once '../config/Database.php';
require_once '../helpers/HeaderAccessControl.php';

$database = new Database();
$db = $database->getConnection();
$eventController = new EventController($db);

$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        $eventController->getEventCountsByStatus();
        break;

    default:
        response('error', 'Method not allowed.', null, 405); 
        break;
}
?>