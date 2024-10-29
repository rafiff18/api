<?php

require_once "../database/Database.php";
require_once "../helpers/ResponseHelper.php";

class TicketController {
    private $conn;

    public function __construct($conn) {
        if (!$conn) {
            response(false, 'Database connection failed');
            exit;
        }
        $this->conn = $conn;
    }

    // 1. GET ALL TICKETS
    public function getAllTickets() {
        try {
            $query = "SELECT * FROM ticket_event";
            $stmt = $this->conn->query($query);
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            response('success', 'List of Tickets Retrieved Successfully', $data);
        } catch (PDOException $e) {
            response('error', 'Failed to Retrieve Tickets', null, 500);
        }
    }

    // 2. GET TICKET BY ID
    public function getTicketById($id) {
        if ($id > 0) {
            try {
                $query = "SELECT * FROM ticket_event WHERE ticket_id = ? LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$id]);

                if ($stmt->rowCount() > 0) {
                    $data = $stmt->fetch(PDO::FETCH_OBJ);
                    response('success', 'Ticket Retrieved Successfully', $data);
                } else {
                    response('error', 'Ticket not found', null, 404);
                }
            } catch (PDOException $e) {
                response('error', 'Failed to Retrieve Ticket', null, 500);
            }
        } else {
            response('error', 'Invalid Ticket ID', null, 400);
        }
    }

    // 3. CREATE NEW TICKET
    public function createTicket() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            response('error', 'Invalid JSON Format', null, 400);
            return;
        }

        $required_fields = ['users_id', 'barcode_value'];
        $missing_fields = array_diff($required_fields, array_keys($input));

        if (!empty($missing_fields)) {
            response('error', 'Missing Parameters', null, 400);
            return;
        }

        try {
            $query = "INSERT INTO ticket_event (users_id, barcode_value) VALUES (?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$input['users_id'], $input['barcode_value']]);
            
            $insert_id = $this->conn->lastInsertId();
            $result_stmt = $this->conn->prepare("SELECT * FROM ticket_event WHERE ticket_id = ?");
            $result_stmt->execute([$insert_id]);
            $new_data = $result_stmt->fetch(PDO::FETCH_OBJ);

            response('success', 'Ticket Created Successfully', $new_data);
        } catch (PDOException $e) {
            response('error', 'Ticket Creation Failed', null, 500);
        }
    }

    // 4. UPDATE TICKET BY ID
    public function updateTicket($id) {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            response('error', 'Invalid JSON Format', null, 400);
            return;
        }

        if (empty($input['users_id']) || empty($input['barcode_value'])) {
            response('error', 'Missing Parameters', null, 400);
            return;
        }

        try {
            $query = "UPDATE ticket_event SET users_id = ?, barcode_value = ? WHERE ticket_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$input['users_id'], $input['barcode_value'], $id]);

            $result_stmt = $this->conn->prepare("SELECT * FROM ticket_event WHERE ticket_id = ?");
            $result_stmt->execute([$id]);
            $updated_data = $result_stmt->fetch(PDO::FETCH_OBJ);

            response('success', 'Ticket Updated Successfully', $updated_data);
        } catch (PDOException $e) {
            response('error', 'Ticket Update Failed', null, 500);
        }
    }

    // 5. DELETE TICKET BY ID
    public function deleteTicket($id) {
        if ($id > 0) {
            try {
                $query = "DELETE FROM ticket_event WHERE ticket_id = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$id]);

                if ($stmt->rowCount() > 0) {
                    response('success', 'Ticket Deleted Successfully');
                } else {
                    response('error', 'Ticket not found', null, 404);
                }
            } catch (PDOException $e) {
                response('error', 'Failed to Delete Ticket', null, 500);
            }
        } else {
            response('error', 'Invalid Ticket ID', null, 400);
        }
    }
}
?>
