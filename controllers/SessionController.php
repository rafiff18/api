<?php
session_start();
require_once "../database/Database.php";
require_once "../helpers/ResponseHelper.php";

class SessionController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getSession() {
        if (isset($_SESSION['users_id'])) {
            $response = [
                'status' => 'success',
                'users_id' => $_SESSION['users_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
            ];
            response(true, "Successfully get session", $response, null);
        } else {
            response(false, "Session not found or user not logged in", null, "User not logged in", 404);
        }
    }
}

?>