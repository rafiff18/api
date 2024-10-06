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
    !empty($data->category_name)
){
    $stmt = $db->prepare("INSERT INTO category (category_name) VALUES (?)");
    $stmt->bind_param("s", $data->category_name);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(array("message" => "Category was created."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to create category."));
    }
}
?>