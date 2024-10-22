<?php
    session_start();
    require_once "../database/Database.php";
    require_once "../helpers/ResponseHelper.php";

    
    class RegistrationEventController {
        private $conn;

        public function __construct($conn) {
            $this->conn = $conn;
        }

        public function isUserJoined($user_id, $event_id) {
            $query = "SELECT COUNT(*) FROM regist_event WHERE users_id = ? AND event_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$user_id, $event_id]);
            $count = $stmt->fetchColumn();
            
            return $count > 0;
        }

        public function register() {
            if (!isset($_SESSION["users_id"])) {
                response(false, "User not logged in", null, "Unauthorized", 401);
                exit;
            }
        
            $input = json_decode(file_get_contents("php://input"), true);
        
            $event_id = $input['event_id'];
            $user_id = $_SESSION['users_id'];
        
            if (!$event_id) {
                header("HTTP/1.0 400 Bad Request");
                response(false, 'Event id is required', null, "Invalid Id", 400);
                exit;
            }
        
            // Periksa role user
            $role_query = "SELECT role FROM users WHERE users_id = ?";
            $role_stmt = $this->conn->prepare($role_query);
            $role_stmt->execute([$user_id]);
            $role = $role_stmt->fetchColumn();
        
            if ($role !== 'member') {
                response(false, "Only members can join the event", null, "Forbidden", 403);
                exit;
            }
        
            if ($this->isUserJoined($user_id, $event_id)) {
                response(false, "Kamu telah bergabung pada event ini!", null, "Failed joining event", 409);
                exit;
            }
        
            try {
                $query = "INSERT INTO regist_event (users_id, event_id) VALUES (?, ?)";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$user_id, $event_id]);
                $new_data = $stmt->fetch(PDO::FETCH_OBJ);
        
                response(true, "Successfully joining event", $new_data, "Success");
            } catch (Exception $e) {
                response(false, "Registration failed", null,  $e, $e->getCode());
            }
        }        

        public function getEventByUserId($user_id) {
            if ($user_id > 0) {
                $query = "SELECT 
                re.regist_id, 
                re.qr_code, 
                re.is_present, 
                re.registration_time,
                u.username, 
                e.title, 
                e.poster,
                e.desc_event,
                e.date_start,
                e.date_end,
                e.location, 
                e.category_id,
                c.category_name,
                e.quota
            FROM 
                regist_event re
            JOIN 
                users u ON re.users_id = u.users_id
            JOIN 
                event_main e ON re.event_id = e.event_id
            JOIN category c ON e.category_id = c.category_id
            WHERE re.users_id = ?
            ORDER BY 
                re.registration_time DESC;
            ";
                
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$user_id]);
    
                if ($stmt->rowCount() > 0) {
                    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
                    response(true, 'Events Retrieved Successfully', $data);
                } else {
                    response(false, 'No Events Found for this user', null, [
                        'code' => 404,
                        'message' => 'No Events found for the specified user'
                    ]);
                }
            } else {
                response(false, 'Invalid Event ID', null, [
                    'code' => 401,
                    'message' => 'Bad request: Event ID must be greater than 0'
                ]);
            }
        }
    }
?>
