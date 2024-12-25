<?php
require_once "../helpers/ResponseHelper.php";
require_once "../helpers/JwtHelper.php";

class RegistrationEventController {
    private $conn;
    private $jwtHelper;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->jwtHelper = new JWTHelper();
    }

    public function checkIsUserJoined($user_id, $event_id) {
        $query = "SELECT COUNT(*) FROM regist_event WHERE user_id = ? AND event_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $event_id]);
        $count = $stmt->fetchColumn();
        
        response('success', $count > 0 ? 'User has joined this event' : 'User hasn\'t joined this event', ['isJoined' => $count > 0]);
    }

    public function checkIsUserJoinedBoolean($user_id, $event_id) {
        $query = "SELECT COUNT(*) FROM regist_event WHERE user_id = ? AND event_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $event_id]);
        $count = $stmt->fetchColumn();
        
        return $count > 0;
    }

    public function register() {
      $this->jwtHelper->decodeJWT();
      $roles = $this->jwtHelper->getRoles();
  
      if (!in_array('Member', $roles)) {
          response('error', 'Unauthorized. Only members can join event.', null, 403);
          return;
      }
  
      $input = json_decode(file_get_contents("php://input"), true);
      $event_id = (int) $input['event_id'];
      $user_id = (int) $this->jwtHelper->getUserId();
  
      if (!$event_id) {
          response('error', 'Event id is required', null, 400);
          return;
      }
  
      if ($this->checkIsUserJoinedBoolean($user_id, $event_id)) {
          response('error', "User has already joined this event!", ['isJoined' => true], 409);
          return;
      }
  
      $query = "SELECT quota FROM event WHERE event_id = ?";
      $stmt = $this->conn->prepare($query);
      $stmt->execute([$event_id]);
      $event = $stmt->fetch(PDO::FETCH_ASSOC);
  
      if (!$event) {
          response('error', 'Event not found', null, 404);
          return;
      }
  
      if ($event['quota'] <= 0) {
          response('error', 'No available quota for this event', null, 409);
          return;
      }
  
      $newQuota = $event['quota'] - 1;
      $updateQuery = "UPDATE event SET quota = ? WHERE event_id = ?";
      $updateStmt = $this->conn->prepare($updateQuery);
      $updateStmt->execute([$newQuota, $event_id]);
  
      try {
          $query = "INSERT INTO regist_event (user_id, event_id) VALUES (?, ?)";
          $stmt = $this->conn->prepare($query);
          $stmt->execute([$user_id, $event_id]);
          response('success', "Successfully joined the event", null, 200);
      } catch (Exception $e) {
          response('error', "Registration failed: " . $e->getMessage(), null, 500);
      }
  }  

    // Mendapatkan event yang diikuti oleh user
    public function getEventUserRegist($user_id) {
      $search = isset($_GET['search']) ? $_GET['search'] : null;
      $not_present = isset($_GET['not_present']) ? $_GET['not_present'] : null;
      $has_present = isset($_GET['has_present']) ? $_GET['has_present'] : null;
      $limit = isset($_GET['limit']) ? $_GET['limit'] : null;
      $sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'registration_time';
      $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : null;
      $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : null;
  
      // Mulai query dasar
      $query = "SELECT 
                  re.regist_id, 
                  re.qr_code, 
                  re.is_present, 
                  re.registration_time,
                  u.username, 
                  e.event_id, 
                  e.title, 
                  e.poster,
                  e.description,
                  e.date_start,
                  e.date_end,
                  e.location, 
                  e.place,
                  e.category_id,
                  c.category_name,
                  e.quota
                FROM 
                  regist_event re
                JOIN 
                  user u ON re.user_id = u.user_id
                JOIN 
                  event e ON re.event_id = e.event_id
                JOIN category c ON e.category_id = c.category_id
                WHERE 
                  re.user_id = ?";  // Kondisi dasar untuk filter berdasarkan user_id
  
      // Menambahkan filter pencarian berdasarkan parameter
      if ($search) {
          $query .= " AND (e.title LIKE ? OR e.description LIKE ?)";
      }
  
      // Menambahkan filter berdasarkan status hadir
      if ($has_present !== null) {
          $query .= " AND re.is_present = 1";
      }

      if ($not_present !== null) {
          $query .= " AND re.is_present IS NULL";
      }
  
      // Menambahkan filter berdasarkan rentang tanggal
      if ($date_from) {
          $query .= " AND e.date_start >= ?";
      }
  
      if ($date_to) {
          $query .= " AND e.date_end <= ?";
      }
  
      // Menambahkan pengurutan
      $query .= " ORDER BY " . $sortBy;
  
      // Menambahkan limit jika ada
      if ($limit) {
          $query .= " LIMIT ".$limit;
      }
  
      // Persiapkan dan eksekusi query
      $stmt = $this->conn->prepare($query);
      $params = [$user_id];
  
      // Menambahkan parameter ke dalam array $params
      if ($search) {
          $params[] = "%$search%";
          $params[] = "%$search%";
      }
  
      if ($date_from) {
          $params[] = $date_from;
      }
  
      if ($date_to) {
          $params[] = $date_to;
      }
  
      // Menjalankan query
      $stmt->execute($params);
  
      // Mengecek apakah ada hasil
      if ($stmt->rowCount() > 0) {
          $data = $stmt->fetchAll(PDO::FETCH_OBJ);
          response('success', 'Events Retrieved Successfully', $data);
      } else {
          response('error', 'No Events Found', null, 404);
      }
  }
  

    // Mendapatkan upcoming events yang diikuti oleh user
    public function getUpcomingEventUserRegist($user_id) {
      // Ambil parameter dari query string
      $search = isset($_GET['search']) ? $_GET['search'] : null;
      $not_present = isset($_GET['not_present']) ? $_GET['not_present'] : null;
      $has_present = isset($_GET['has_present']) ? $_GET['has_present'] : null;
      $limit = isset($_GET['limit']) ? $_GET['limit'] : null;
      $sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'registration_time';
      $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : null;
      $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : null;
  
      // Mulai query dasar
      $query = "SELECT 
                  re.regist_id, 
                  re.qr_code, 
                  re.is_present, 
                  re.registration_time,
                  u.username, 
                  e.event_id, 
                  e.title, 
                  e.poster,
                  e.description,
                  e.date_start,
                  e.date_end,
                  e.location, 
                  e.place,
                  e.category_id,
                  c.category_name,
                  e.quota
                FROM 
                  regist_event re
                JOIN 
                  user u ON re.user_id = u.user_id
                JOIN 
                  event e ON re.event_id = e.event_id
                JOIN category c ON e.category_id = c.category_id
                WHERE 
                  re.user_id = ? 
                  AND e.date_start > NOW()";  // Kondisi dasar
  
      // Menambahkan filter pencarian berdasarkan parameter
      if ($search) {
          $query .= " AND (e.title LIKE ? OR e.description LIKE ?)";
      }
      
      if ($not_present !== null) {
          $query .= " AND re.is_present IS NULL";
      }

      if ($has_present !== null) {
          $query .= " AND re.is_present = 1";
      }
  
      if ($date_from) {
          $query .= " AND e.date_start >= ?";
      }
  
      if ($date_to) {
          $query .= " AND e.date_end <= ?";
      }
  
      // Menambahkan pengurutan
      $query .= " ORDER BY " . $sortBy;
  
      // Menambahkan limit jika ada
      if ($limit) {
          $query .= " LIMIT ".$limit;
      }
  
      // Persiapkan dan eksekusi query
      $stmt = $this->conn->prepare($query);
      $params = [$user_id];
  
      // Menambahkan parameter ke dalam array $params
      if ($search) {
          $params[] = "%$search%";
          $params[] = "%$search%";
      }
  
      if ($date_from) {
          $params[] = $date_from;
      }
  
      if ($date_to) {
          $params[] = $date_to;
      }
  
      // Menjalankan query
      $stmt->execute($params);
  
      // Mengecek apakah ada hasil
      if ($stmt->rowCount() > 0) {
          $data = $stmt->fetchAll(PDO::FETCH_OBJ);
          response('success', 'Upcoming Events Retrieved Successfully', $data);
      } else {
          response('error', 'No Upcoming Events Found', null, 404);
      }
  }  

   
}
?>
