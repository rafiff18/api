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

switch($method) {
    case 'POST':
        // CREATE
        if (!empty($data->users_id)&&
            !empty($data->event_id)&&
            !empty($data->regist_date   )
            ) {
                $stmp = $db->prepare("INSERT INTO regist_event (users_id, event_id, regist_date) VALUES (?, ?, ?)");
                $stmp->bind_param("iis", $data->users_id, $data->event_id, $data->regist_date);
                if ($stmp->execute()) {
                    http_response_code(201);
                    echo json_encode(array("message" => "Regist Event was created."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Unable to create regist event."));
            }
            $stmt->close();
        }   else {
            http_response_code(400);
            echo json_encode(array("message" => "Data is incomplete."));
        }
        break;

        case 'GET':
            // READ
            $result = $db->query("SELECT * FROM regist_event");
            if ($result->num_rows > 0) {
                $regist_event_arr = array();
                while ($row = $result->fetch_assoc()) {
                    $regist_event_item = array(
                        "regist_id" => $row["regist_id"],
                        "users_id" => $row["users_id"],
                        "event_id" => $row["event_id"],
                        "regist_date" => $row["regist_date"]
                    );
                    array_push($regist_event_arr, $regist_event_item);
                }
                http_response_code(200);
                echo json_encode($regist_event_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "No regist event found."));
            }
            break;

        case 'PUT':
            // UPDATE
            if (!empty($data->regist_id)) {
                    $query = "UPDATE regist_event SET users_id = ?, event_id = ?, regist_date = ? WHERE regist_id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->bind_param("iiis", $data->users_id, $data->event_id, $data->regist_date, $data->regist_id);
                    if ($stmt->execute()) {
                        http_response_code(200);
                        echo json_encode(array("message" => "Regist Event was updated."));
                    } else {
                        http_response_code(503);
                        echo json_encode(array("message" => "Unable to update regist event."));
                    }
                    $stmt->close();
                }else {
                    http_response_code(400);
                    echo json_encode(array("message" => "Data is incomplete."));
                }
                break;
        
        case 'DELETE':
            // DELETE
            if (!empty($data->regist_id)) {
                        $stmt = $db->prepare("DELETE FROM regist_event WHERE regist_id = ?");
                        $stmt->bind_param("i", $data->regist_id);
                        if ($stmt->execute()) {
                            http_response_code(200);
                            echo json_encode(array("message" => "Regist Event was deleted."));
                        } else {
                            http_response_code(503);
                            echo json_encode(array("message" => "Unable to delete regist event."));
                        }
                        $stmt->close();
                    }else {
                        http_response_code(400);
                        echo json_encode(array("message" => "Data is incomplete."));
                    }
                    break;

                    default:    
                    http_response_code(405);
                    echo json_encode(array("message" => "Method not allowed."));
                    break;
    }