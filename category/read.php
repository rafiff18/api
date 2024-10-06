<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../database.php';

$database = new Database();
$db = $database->getConnection();

$result = $db->query("SELECT * FROM category");
if ($result->num_rows > 0) {
    $categories_arr = array();
    while ($row = $result->fetch_assoc()) {
        $category_item = array(
            "category_id" => $row["category_id"],
            "category_name" => $row["category_name"]
        );
        array_push($categories_arr, $category_item);
    }
    http_response_code(200);
    echo json_encode($categories_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No categories found."));
}
?>