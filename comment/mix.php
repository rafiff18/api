<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../database.php';

$database = new Database();
$db = $database->getConnection();

// Mendapatkan request method
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

switch ($method) {
    case 'POST':
        // CREATE
        if (
            !empty($data->users_id) &&
            !empty($data->event_id) &&
            !empty($data->comment)
        ) {
            $stmt = $db->prepare("INSERT INTO comment_event (users_id, event_id, comment) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $data->users_id, $data->event_id, $data->comment);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(array("message" => "Comment was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create comment."));
            }
            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data is incomplete."));
        }
        break;

    case 'GET':
        // READ
        $result = $db->query("SELECT * FROM comment_event");
        if ($result->num_rows > 0) {
            $comment_arr = array();
            while ($row = $result->fetch_assoc()) {
                $comment_item = array(
                    "comment_id" => $row["comment_id"],
                    "users_id" => $row["users_id"],
                    "event_id" => $row["event_id"],
                    "content_comment" => $row["content_comment"]
                );
                array_push($comment_arr, $comment_item);
            }
            http_response_code(200);
            echo json_encode($comment_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No comments found."));
        }
        break;

    case 'PUT':
        // UPDATE
        if (!empty($data->comment_id)) {
            $query = "UPDATE comment_event SET users_id = ?, event_id = ?, comment = ? WHERE comment_id = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param("iisi", $data->users_id, $data->event_id, $data->comment, $data->comment_id);
            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Comment was updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update comment."));
            }
            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data is incomplete."));
        }
        break;

    case 'DELETE':
        // DELETE
        if (!empty($data->comment_id)) {
            $query = "DELETE FROM comment_event WHERE comment_id = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param("i", $data->comment_id);
            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Comment was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete comment."));
            }
            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data is incomplete."));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Invalid request method."));
        break;
}
?>
