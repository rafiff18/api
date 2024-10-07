<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../database.php';

$database = new Database();
$db = $database->getConnection();

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : null;

    if ($user_id) {
        $stmt = $db->prepare("SELECT * FROM like_event WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
    } elseif ($event_id) {
        $stmt = $db->prepare("SELECT * FROM like_event WHERE event_id = ?");
        $stmt->bind_param("i", $event_id);
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "User ID or Event ID is required."));
        exit();
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $likes_arr = array();
        while ($row = $result->fetch_assoc()) {
            $like_item = array(
                "like_id" => $row["like_id"],
                "user_id" => $row["user_id"],
                "event_id" => $row["event_id"],
                "status_like" => $row["status_like"]
            );
            array_push($likes_arr, $like_item);
        }
        http_response_code(200);
        echo json_encode($likes_arr);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "No likes found."));
    }

$stmt->close();
?>
