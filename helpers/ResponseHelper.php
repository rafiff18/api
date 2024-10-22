<?php

function response($success, $message, $data = null, $error = null, $statusCode = 200) {
    // Set HTTP status code
    http_response_code($statusCode);

    // Set Content-Type ke JSON
    header('Content-Type: application/json');

    // Buat array respons
    $response = [
        "success" => $success,
        "message" => $message,
        "data" => $data,
        "error" => $error,
    ];

    // Kirim respons JSON
    echo json_encode($response);
    exit();
}
?>
