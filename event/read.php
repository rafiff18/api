<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../database.php';

$database = new Database();
$db = $database->getConnection();

$resulst = $db->query("SELECT * FROM event_main");

if($resulst->num_rows > 0){
    $event_main_arr = array();
    while($row = $resulst->fetch_assoc()){
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
            "date_end" => $row["date_end"],
        );
        array_push($event_main_arr, $event_main_item);
    }
    echo json_encode($event_main_arr);
} else {
    echo json_encode(array("message" => "No event_main found."));
}
?>