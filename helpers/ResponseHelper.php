<?php

function response($status, $message, $data = null, $statusCode = 200) {
    // Set HTTP status code
    http_response_code($statusCode);

    // Set Content-Type ke JSON
    header('Content-Type: application/json');

    // Buat array respons
    $response = [
        "status" => $status,
        "message" => $message,
        "data" => $data,
    ];

    // Kirim respons JSON
    echo json_encode($response);
    exit();
}
?>
