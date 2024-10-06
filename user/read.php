<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../database.php';

$database = new Database();
$db = $database->getConnection();

$result = $db->query("SELECT * FROM users");

if ($result->num_rows > 0) {
    $users_arr = array();
    while ($row = $result->fetch_assoc()) {
        $user_item = array(
            "users_id" => $row["users_id"],
            "username" => $row["username"],
            "email" => $row["email"],
            "role" => $row["role"]
        );
        array_push($users_arr, $user_item);
    }
    http_response_code(200);
    echo json_encode($users_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No users found."));
}
?>
