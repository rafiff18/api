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

if (
    !empty($data->users_id) &&
    !empty($data->comment_id) &&
    !empty($data->content_replay)
){
    $stmt = $db->prepare("INSERT INTO replay_comment (users_id, comment_id, content_replay) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $data->users_id, $data->comment_id, $data->content_replay);

    if($stmt->execute()){
        http_response_code(201);
        echo json_encode(array("message" => "Replay was created."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to create replay."));
    }
 } else {
        http_response_code(400);
        echo json_encode(array("message" => "Data is incomplete."));
    }

?>