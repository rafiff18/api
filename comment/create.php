<?php   
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../database.php';

$databsae = new Database();
$db = $databsae->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(

    !empty($data->users_id) &&
    !empty($data->event_id)&&    
    !empty($data->content_comment) 
){
    $stmt = $db->prepare("INSERT INTO comment_event (users_id, event_id, content_comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $data->users_id, $data->event_id, $data->content_comment);

    if($stmt->execute()){
        http_response_code(201);
        echo json_encode(array("message" => "Comment was created."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to create comment."));
    }
}else {
    http_response_code(400);
    echo json_encode(array("message" => "Data is incomplete."));
}