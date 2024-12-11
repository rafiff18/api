<?php
    require_once "../helpers/ResponseHelper.php";
    require_once '../config/JwtConfig.php';
    require_once '../vendor/autoload.php'; 

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    class RegistrationEventController {
        private $conn;

        public function __construct($conn) {
            $this->conn = $conn;
        }

        public function isUserJoined($user_id, $event_id) {
            $query = "SELECT COUNT(*) FROM regist_event WHERE user_id = ? AND event_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$user_id, $event_id]);
            $count = $stmt->fetchColumn();
            
            response('success', $count > 0 ? 'User has joined this event' : 'User hasn\'t joined this event', ['isJoined' => $count > 0]);
        }

        public function checkIsUserJoined($user_id, $event_id) {
            $query = "SELECT COUNT(*) FROM regist_event WHERE user_id = ? AND event_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$user_id, $event_id]);
            $count = $stmt->fetchColumn();
            
            return $count > 0;
        }

        public function register() {
            if (!isset($_COOKIE["refresh_token"])) {
                response('error', "Unauthorized", null, 401);
                exit;
            }
        
            $input = json_decode(file_get_contents("php://input"), true);
        
            $event_id = $input['event_id'];
            $refresh_token = $_COOKIE['refresh_token'] ?? null;
            $decoded = JWT::decode($refresh_token, new Key(JWT_SECRET, 'HS256'));
            $user_id = $decoded->user_id;
        
            if (!$event_id) {
                response('error', 'Event id is required', null, 400);
                exit;
            }
        
            $role_query = "SELECT r.role_name
                FROM user_roles ur
                JOIN roles r ON ur.role_id = r.role_id
                WHERE ur.user_id = ?";
            $role_stmt = $this->conn->prepare($role_query);
            $role_stmt->execute([$user_id]);
            $role = $role_stmt->fetchColumn();
        
            if ($role !== 'Member') {
                response('error', "Only members can join the event", null, 403);
                exit;
            }
            
            if ($this->checkIsUserJoined($user_id, $event_id)) {
                response('error', "User have already join this event!", ['isJoined' => true], 409);
                exit;
            }
            
            try {
                $query = "INSERT INTO regist_event (user_id, event_id) VALUES (?, ?)";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$user_id, $event_id]);
                $new_data = $stmt->fetch(PDO::FETCH_OBJ);
        
                response('success', "Successfully joining event", $new_data);
            } catch (Exception $e) {
                response('error', "Registration failed", null, 500);
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
                    e.event_id, 
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
                    user u ON re.user_id = u.user_id
                JOIN 
                    event_main e ON re.event_id = e.event_id
                JOIN category c ON e.category_id = c.category_id
                WHERE 
                    re.user_id = ?
                    AND re.is_present = 1 -- Hanya pilih data dengan is_present = true
                ORDER BY 
                    re.registration_time DESC;
                ";
                    
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$user_id]);
        
                if ($stmt->rowCount() > 0) {
                    $data = $stmt->fetchAll(PDO::FETCH_OBJ);
                    response('success', 'Events Retrieved Successfully', $data);
                } else {
                    response('error', 'No Events Found for this user', null, 404);
                }
            } else {
                response('error', 'Invalid User ID', null, 401);
            }
        }
        

        public function upcomingEvent($user_id) {
            $query = "SELECT 
                re.regist_id, 
                re.qr_code, 
                re.is_present, 
                re.registration_time,
                u.username, 
                e.event_id, 
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
                user u ON re.user_id = u.user_id
            JOIN 
                event_main e ON re.event_id = e.event_id
            JOIN 
                category c ON e.category_id = c.category_id
            WHERE 
                re.user_id = ?
                AND e.date_start > NOW() -- Menambahkan kondisi untuk hanya menampilkan event yang akan datang
            ORDER BY 
                re.registration_time DESC;
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$user_id]); // Menggunakan $user_id sebagai parameter, bukan $currentDate
            
            $data = array();
            
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
                    $data[] = $row;
                }
                response('success', 'Upcoming Events Retrieved Successfully', $data);
            } else {
                response('error', 'No upcoming events found', null, 404);
            }
        }
        
    }
?>
