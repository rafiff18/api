<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../database.php';
$database = new Database();
$db = $database->getConnection();

//request metod
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

switch($method){
    case'POST':
        //create
        if(!empty($data->category_name)){
            $stmt = $db->prepare("INSERT INTO category (category_name) VALUES (?)");
            $stmt->bind_param("s", $data->category_name);

            if($stmt->execute()){
                http_response_code(201);
                echo json_encode(array("message" => "Category was created."));
            }else{
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create category."));
            }
            $stmt->close();
        }else{
            http_response_code(400);
            echo json_encode(array("message" => "Data is incomplete."));
        }
        break;

    case'GET': 
        //read
        $result = $db->query("SELECT * FROM category");
        if($result->num_rows > 0){
            $category_arr = array();
            while($row = $result->fetch_assoc()){
                $category_item = array(
                    "category_id" => $row["category_id"],
                    "category_name" => $row["category_name"]
                );
                array_push($category_arr, $category_item);
            }
            http_response_code(200);
            echo json_encode($category_arr);
        }else{
            http_response_code(404);
            echo json_encode(array("message" => "No category found."));
        }
        break;

    case'PUT':
        //update
        if(!empty($data->category_id) && (!empty($data->category_name))){
            $stmt = $db->prepare("UPDATE category SET category_name = ? WHERE category_id = ?");
            $stmt->bind_param("si", $data->category_name, $data->category_id);
            
            if($stmt->execute()){
                http_response_code(200);
                echo json_encode(array("message" => "Category was updated."));
            }else{
                http_response_code(400);
                echo json_encode(array("message" => "Data is incomplete."));
                break;
            }
        }
    case'DELETE':
        //delete
        if(!empty($data->category_id)){
            $stmt = $db->prepare("DELETE FROM category WHERE category_id = ?");
            $stmt->bind_param("i", $data->category_id);
            if($stmt->execute()){
                http_response_code(200);
                echo json_encode(array("message" => "Category was deleted."));
            }else{
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete category."));
            }
            $stmt->close();
        }else{
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