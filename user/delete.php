<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->users_id)) {
    $stmt = $db->prepare("DELETE FROM users WHERE users_id = ?");
    $stmt->bind_param("i", $data->users_id);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(array("message" => "User was deleted."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to delete user."));
    }

    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Data is incomplete."));
}
?>
