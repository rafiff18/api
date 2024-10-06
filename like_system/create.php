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

if (!empty($data->user_id) && !empty($data->event_id)) {
    $query = "INSERT INTO like_event (user_id, event_id, status_like) VALUES (?, ?, ?)";
    $stmt = $db->prepare($query);
    
    // Set status_like menjadi 1 untuk menandakan event disukai
    $status_like = 1; 
    $stmt->bind_param("iii", $data->user_id, $data->event_id, $status_like);
    
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(array("message" => "Like was created."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to create like."));
    }

    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Data is incomplete."));
}
?>
