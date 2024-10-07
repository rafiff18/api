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
        if (!empty($data->user_id) && !empty($data->event_id)) {
            $query = "INSERT INTO like_event (user_id, event_id, status_like) VALUES (?, ?, ?)";
            $stmt = $db->prepare($query);
            
            // Set status_like menjadi 1 untuk menandakan event disukai
            $status_like = 1; 
            $stmt->bind_param("iii", $data->user_id, $data->event_id, $status_like);
            
            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(array("message" => "Like was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create like."));
            }
        
            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data is incomplete."));
        }
        break;
    
    case 'GET':
        // READ
        $result = $db->query("SELECT * FROM like_event");
        if ($result->num_rows > 0) {
                $like_event_arr = array();
                while ($row = $result->fetch_assoc()) {
                    $like_event_item = array(
                        "like_id" => $row["like_id"],
                        "users_id" => $row["users_id"],
                        "event_id" => $row["event_id"],
                        "status_like" => $row["status_like"]
                    );
                    array_push($like_event_arr, $like_event_item);
                }
                http_response_code(200);
                echo json_encode($like_event_arr);
            }else {
                http_response_code(404);
                echo json_encode(array("message" => "No likes found."));
            }
            break;
        
    case 'PUT':
        // UPDATE
        if (!empty($data->like_id) && !empty($data->status_like)) {
            $query = "UPDATE like_event SET status_like = ? WHERE like_id = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param("ii", $data->status_like, $data->like_id);
            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Like was updated."));
            }else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update like."));
            }
            $stmt->close();
        }else {
            http_response_code(400);
            echo json_encode(array("message" => "Data is incomplete."));
        }
        break;

    case 'DELETE':
        // DELETE
        if (!empty($data->like_id)) {
            $query = "DELETE FROM like_event WHERE like_id = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param("i", $data->like_id);
            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Like was deleted."));
            }else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete like."));
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