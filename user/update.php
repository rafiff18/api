re<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->users_id) &&
    (!empty($data->username) || !empty($data->email) || !empty($data->password) || !empty($data->role))
) {
    $query = "UPDATE users SET username = ?, email = ?, password = ?, role = ? WHERE users_id = ?";
    $stmt = $db->prepare($query);
    
    $username = !empty($data->username) ? $data->username : null;
    $email = !empty($data->email) ? $data->email : null;
    $password = !empty($data->password) ? password_hash($data->password, PASSWORD_BCRYPT) : null;
    $role = !empty($data->role) ? $data->role : null;

    $stmt->bind_param("ssssi", $username, $email, $password, $role, $data->users_id);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(array("message" => "User was updated."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to update user."));
    }

    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Data is incomplete."));
}
?>
