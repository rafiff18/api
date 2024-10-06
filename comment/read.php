<?php   
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../database.php';

$database = new Database();
$db = $database->getConnection();   

$result = $db->query("SELECT * FROM comment_event");

if ($result->num_rows > 0) {
        $comments_arr = array();
        while ($row = $result->fetch_assoc()) {
            $comment_item = array(
                "comment_id" => $row["comment_id"],
                "users_id" => $row["users_id"],
                "event_id" => $row["event_id"],
                "content_comment" => $row["content_comment"]
            );
            array_push($comments_arr, $comment_item);
        }
        http_response_code(200);
        echo json_encode($comments_arr);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "No comments found."));
    }
    ?>