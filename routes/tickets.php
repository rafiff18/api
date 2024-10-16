<?php

require_once "../helpers/HeaderAccessControl.php";
require_once "../controllers/TicketController.php";
require_once "../database/Database.php";

$database = new Database();
$conn = $database->getConnection();
$ticketController = new TicketController($conn);

$request_method = $_SERVER["REQUEST_METHOD"];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

switch ($request_method) {
    case 'GET':
        if ($id > 0) {
            $ticketController->getTicketById($id);
        } else {
            $ticketController->getAllTickets();
        }
        break;

    case 'POST':
        $ticketController->createTicket();
        break;

    case 'PUT':
        parse_str(file_get_contents("php://input"), $_PUT);
        $_POST = $_PUT; // Assigning PUT data to $_POST
        $ticketController->updateTicket($id);
        break;

    case 'DELETE':
        $ticketController->deleteTicket($id);
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?>
