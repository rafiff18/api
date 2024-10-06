<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../database.php';
$database = new Database();

$result = $database->getConnection()->query("SELECT * FROM ticket_event");

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
?>