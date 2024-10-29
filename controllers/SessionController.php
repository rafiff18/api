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
                'users_id' => $_SESSION['users_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
            ];
            response('success', "Successfully get session", $response);
        } else {
            response('error', "Session not found or user not logged in", null, 404);
        }
    }
}

?>