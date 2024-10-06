<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->title) &&
    !empty($data->date_add) &&
    !empty($data->category_id) &&
    !empty($data->desc_event) &&
    !empty($data->poster) &&
    !empty($data->location) &&
    !empty($data->quota) &&
    !empty($data->date_start) &&
    !empty($data->date_end)
){
    $stmt = $db->prepare("INSERT INTO event_main (title, date_add, category_id, desc_event, poster, location, quota, date_start, date_end) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $data->title, $data->date_add, $data->category_id, $data->desc_event, $data->poster, $data->location, $data->quota, $data->date_start, $data->date_end);

    if($stmt->execute()){
        http_response_code(201);
        echo json_encode(array("message" => "Event was created."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to create event."));
    }

    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Data is incomplete."));
}