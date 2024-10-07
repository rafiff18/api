<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../database.php';

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    // Membaca semua tiket (GET)
    case 'GET':
        $result = $db->query("SELECT * FROM ticket_event");

        if ($result->num_rows > 0) {
            $ticket_arr = array();
            while ($row = $result->fetch_assoc()) {
                $ticket_item = array(
                    "ticket_id" => $row["ticket_id"],
                    "users_id" => $row["users_id"],
                    "barcode_value" => $row["barcode_value"],
                );
                array_push($ticket_arr, $ticket_item);
            }
            http_response_code(200);
            echo json_encode($ticket_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No tickets found."));
        }
        break;

    // Menambahkan tiket baru (POST)
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if (
            !empty($data->users_id) &&
            !empty($data->barcode_value)
        ) {
            $stmt = $db->prepare("INSERT INTO ticket_event (users_id, barcode_value) VALUES (?, ?)");
            $stmt->bind_param("is", $data->users_id, $data->barcode_value);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(array("message" => "Ticket was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create ticket."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data is incomplete."));
        }
        break;

    // Memperbarui tiket berdasarkan ticket_id (PUT)
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));

        if (
            !empty($data->ticket_id) &&
            !empty($data->barcode_value)
        ) {
            $stmt = $db->prepare("UPDATE ticket_event SET barcode_value = ? WHERE ticket_id = ?");
            $stmt->bind_param("si", $data->barcode_value, $data->ticket_id);

            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Ticket was updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update ticket."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data is incomplete."));
        }
        break;

    // Menghapus tiket berdasarkan ticket_id (DELETE)
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->ticket_id)) {
            $stmt = $db->prepare("DELETE FROM ticket_event WHERE ticket_id = ?");
            $stmt->bind_param("i", $data->ticket_id);

            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Ticket was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete ticket."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data is incomplete."));
        }
        break;

    // Metode yang tidak didukung
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?>
