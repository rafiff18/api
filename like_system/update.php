<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->like_id) && isset($data->status_like)) {
    $stmt = $db->prepare("UPDATE like_event SET status_like = ? WHERE like_id = ?");
    $stmt->bind_param("ii", $data->status_like, $data->like_id);
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(array("message" => "Like was updated."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to update like."));
    }

    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Data is incomplete."));
}
?>
