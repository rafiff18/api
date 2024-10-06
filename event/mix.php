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
            !empty($data->title) &&
            !empty($data->date_add) &&
            !empty($data->category_id) &&
            !empty($data->desc_event) &&
            !empty($data->poster) &&
            !empty($data->location) &&
            !empty($data->quota) &&
            !empty($data->date_start) &&
            !empty($data->date_end)
        ) {
            $stmt = $db->prepare("INSERT INTO event_main (title, date_add, category_id, desc_event, poster, location, quota, date_start, date_end) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssss", $data->title, $data->date_add, $data->category_id, $data->desc_event, $data->poster, $data->location, $data->quota, $data->date_start, $data->date_end);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(array("message" => "Event was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create event."));
            }

            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data is incomplete."));
        }
        break;

    case 'GET':
        // READ
        $result = $db->query("SELECT * FROM event_main");
        if ($result->num_rows > 0) {
            $event_main_arr = array();
            while ($row = $result->fetch_assoc()) {
                $event_main_item = array(
                    "event_id" => $row["event_id"],
                    "title" => $row["title"],
                    "date_add" => $row["date_add"],
                    "category_id" => $row["category_id"],
                    "desc_event" => $row["desc_event"],
                    "poster" => $row["poster"],
                    "location" => $row["location"],
                    "quota" => $row["quota"],
                    "date_start" => $row["date_start"],
                    "date_end" => $row["date_end"]
                );
                array_push($event_main_arr, $event_main_item);
            }
            http_response_code(200);
            echo json_encode($event_main_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No events found."));
        }
        break;

    case 'PUT':
        // UPDATE
        if (!empty($data->event_id)) {
            $query = "UPDATE event_main SET title = ?, date_add = ?, category_id = ?, desc_event = ?, poster = ?, location = ?, quota = ?, date_start = ?, date_end = ? WHERE event_id = ?";
            $stmt = $db->prepare($query);

            $stmt->bind_param("sssssssssi", $data->title, $data->date_add, $data->category_id, $data->desc_event, $data->poster, $data->location, $data->quota, $data->date_start, $data->date_end, $data->event_id);

            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Event was updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update event."));
            }

            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data is incomplete."));
        }
        break;

    case 'DELETE':
        // DELETE
        if (!empty($data->event_id)) {
            $stmt = $db->prepare("DELETE FROM event_main WHERE event_id = ?");
            $stmt->bind_param("i", $data->event_id);

            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Event was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete event."));
            }

            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data is incomplete."));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?>
