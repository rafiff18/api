<?php

function response($status, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);

    header('Content-Type: application/json');

    $response = [
        "status" => $status,
        "message" => $message,
        "data" => $data,
    ];

    echo json_encode($response);
    exit();
}
?>
