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
        if (!empty($data->username) && !empty($data->email) && !empty($data->password) && !empty($data->role)) {
            $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $data->username, $data->email, password_hash($data->password, PASSWORD_BCRYPT), $data->role);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(array("message" => "User was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create user."));
            }

            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data is incomplete."));
        }
        break;

    case 'GET':
        // READ
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
        break;

    case 'PUT':
        // UPDATE
        if (!empty($data->users_id) && (!empty($data->username) || !empty($data->email) || !empty($data->password) || !empty($data->role))) {
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
        break;

    case 'DELETE':
        // DELETE
        if (!empty($data->users_id)) {
            $stmt = $db->prepare("DELETE FROM users WHERE users_id = ?");
            $stmt->bind_param("i", $data->users_id);

            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "User was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete user."));
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
