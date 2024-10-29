<?php

require_once "../database/Database.php";
require_once "../helpers/ResponseHelper.php";

class EventController {
    private $conn;

    public function __construct($conn) {
        if (!$conn) {
            response(false, 'Database connection failed');
        }
        $this->conn = $conn;
    }

    public function getAllEvent() {
        $query = "SELECT * FROM event_main";
        $data = array();

        $stmt = $this->conn->query($query);

        if ($stmt) {
            while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
                $data[] = $row;
            }
            response(true, 'List of Events Retrieved Successfully', $data);
        } else {
            response(false, 'Failed to Retrieve Events', null, 'Internal server error: ' . $this->conn->errorInfo()[2], 500);
        }
    }

    public function getEventById($id = 0) {
        if ($id != 0) {
            $query = "SELECT * FROM event_main WHERE event_id = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                $data = $stmt->fetch(PDO::FETCH_OBJ);
                response(true, 'Event Retrieved Successfully', $data);
            } else {
                response(false, 'Event not found', null, 'The requested resource could not be found', 404);
            }
        } else {
            response(false, 'Invalid ID', null, [
                'code' => 401,
                'message' => 'Bad request: ID is required'
            ]);
        }
    }

    public function createEvent() {
        $input = json_decode(file_get_contents("php://input"), true);
    
        $requiredFields = ['title', 'date_add', 'category_id', 'desc_event', 
                           'poster', 'location', 'quota', 'date_start', 'date_end'];
    
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                response(false, 'Missing Parameters', null, "Bad request: Missing parameter $field", 402);
                return;
            }
        }
    
        $query = "INSERT INTO event_main (title, date_add, category_id, desc_event, poster, location, quota, date_start, date_end) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
    
        if ($stmt->execute([
            $input['title'], 
            $input['date_add'], 
            $input['category_id'], 
            $input['desc_event'],
            $input['poster'],
            $input['location'],
            $input['quota'],
            $input['date_start'],
            $input['date_end'],
        ])) {
            $insert_id = $this->conn->lastInsertId();
    
            $result_stmt = $this->conn->prepare("SELECT * FROM event_main WHERE event_id = ?");
            $result_stmt->execute([$insert_id]);
            $new_data = $result_stmt->fetch(PDO::FETCH_OBJ);
    
            response(true, 'Event Added Successfully', $new_data);
        } else {
            response(false, 'Failed to Add Event', null, 'Internal server error: ' . $this->conn->errorInfo()[2], 500);
        }
    }
    
    public function updateEvent($id) {
        $input = json_decode(file_get_contents('php://input'), true);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            response(false, 'Invalid JSON Format', null, 'Bad request: JSON parsing error', 400);
            return;
        }
    
        $required_fields = ['title', 'date_add', 'category_id', 'desc_event', 'poster', 'location', 'quota', 'date_start', 'date_end'];
        $missing_fields = array_diff($required_fields, array_keys($input));
    
        if (!empty($missing_fields)) {
            response(false, 'Missing Parameters', null, [
                'code' => 403,
                'message' => 'Missing required parameters: ' . implode(', ', $missing_fields)
            ]);
            return;
        }
    
        $query = 'UPDATE event_main SET title = ?, date_add = ?, category_id = ?, desc_event = ?, poster = ?, location = ?, quota = ?, date_start = ?, date_end = ? WHERE event_id = ?';
        $stmt = $this->conn->prepare($query);
    
        if ($stmt->execute([
            $input['title'], 
            $input['date_add'], 
            $input['category_id'], 
            $input['desc_event'],
            $input['poster'],
            $input['location'],
            $input['quota'],
            $input['date_start'],
            $input['date_end'],
            $id
        ])) {
            $query = "SELECT * FROM event_main WHERE event_id = ?";
            $result_stmt = $this->conn->prepare($query);
            $result_stmt->execute([$id]);
            $updated_data = $result_stmt->fetch(PDO::FETCH_OBJ);
    
            response(true, 'Event Updated Successfully', $updated_data);
        } else {
            response(false, 'Failed to Update Event', null, 'Internal server error: ' . $this->conn->errorInfo()[2], 500);
        }
    }
    
    public function deleteEvent($id) {
        $stmt = $this->conn->prepare('DELETE FROM event_main WHERE event_id = ?');

        if ($stmt->execute([$id])) {
            response(true, 'Event Deleted Successfully');
        } else {
            response(false, 'Failed to Delete Event', null, 'Internal server error: ' . $this->conn->errorInfo()[2], 500);
        }
    }
    public function searchEvent($keyword) {
        $query = "SELECT 
            e.event_id,
            e.title,
            e.date_add,
            e.date_start,
            e.date_end,
            e.poster,
            e.location, 
            c.category_name 
        FROM 
            event_main e
        JOIN 
            category c ON e.category_id = c.category_id
        WHERE 
            e.title LIKE ?
      ";
        $stmt = $this->conn->prepare($query);
        
        $keyword = "%" . $keyword . "%";
        $stmt->execute([$keyword]);
    
        $data = array();
    
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
                $data[] = $row;
            }
            response(true, 'Events Found', $data);
        } else {
            response(false, 'No events found matching the search keyword', null, 'No matching events', 404);
        }
    }
    
    public function filterEventsByDate($filterType) {
        $query = "";
    
        switch ($filterType) {
            case 'latest': // Event terbaru
                $query = "SELECT e.*, c.category_name 
                          FROM event_main e 
                          JOIN category c ON e.category_id = c.category_id 
                          WHERE e.date_add <= NOW() 
                          ORDER BY e.date_add DESC";
                break;
    
            case 'last7days': // Event dalam 7 hari terakhir
                $query = "SELECT e.*, c.category_name 
                          FROM event_main e 
                          JOIN category c ON e.category_id = c.category_id 
                          WHERE e.date_add >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
                          ORDER BY e.date_add DESC";
                break;
    
            case 'last30days': // Event dalam 30 hari terakhir
                $query = "SELECT e.*, c.category_name 
                          FROM event_main e 
                          JOIN category c ON e.category_id = c.category_id 
                          WHERE e.date_add >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                          ORDER BY e.date_add DESC";
                break;
    
            default:
                response(false, 'Invalid filter type', null,  'Invalid filter type provided', 404);
                return;
        }
    
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
    
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
    
            if (count($data) > 0) {
                response(true, 'Events filtered successfully', $data);
            } else {
                response(false, 'No events found for this filter', null, 'No matching events for this filter', 404);
            }
        } catch (PDOException $e) {
            response(false, 'Database error', null, [
                'code' => 500,
                'message' => $e->getMessage()
            ]);
        }
    }
    public function getEventByCategoryId($id) {
        if ($id == 0) {
            response(false, 'Invalid ID', null, 'Bad request: ID is required', 401);
            return;
        }
    
        $query = "SELECT category.*, event_main.* 
                  FROM category 
                  LEFT JOIN event_main ON category.category_id = event_main.category_id 
                  WHERE category.category_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
    
        if ($stmt->rowCount() > 0) {
            $categoryData = null;
            $events = [];
            
            // Ambil data dari hasil query
            while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
                if (!$categoryData) {
                    $categoryData = (object) [
                        'category_id' => $row->category_id,
                        'category_name' => $row->category_name
                    ];
                }
                
                if ($row->event_id !== null) {
                    $events[] = (object) [
                        'event_id' => $row->event_id,
                        'title' => $row->title,
                        'date_add' => $row->date_add,
                        'desc_event' => $row->desc_event,
                        'poster' => $row->poster,
                        'location' => $row->location,
                        'quota' => $row->quota,
                        'date_start' => $row->date_start,
                        'date_end' => $row->date_end
                    ];
                }
            }
    
            $responseData = (object) [
                'category' => $categoryData,
                'events' => $events
            ];
    
            response(true, 'Category and Events Retrieved Successfully', $responseData);
        } else {
            response(false, 'Category not found', null, 'The requested category could not be found', 404);
        }
    }
    
    public function getJoinedEventsByUserId($usersId) {
        if ($usersId == 0) {
            response(false, 'Invalid User ID', null, 'Bad request: User ID is required', 401);
            return;
        }
    
        $query = "SELECT event_main.* 
                  FROM event_main 
                  INNER JOIN event_participants ON event_main.event_id = event_participants.event_id 
                  WHERE event_participants.user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$usersId]);
    
        $data = array();
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
                $data[] = $row;
            }
            response(true, 'List of Joined Events Retrieved Successfully', $data);
        } else {
            response(false, 'No events found for this user', null, 'No matching events for this user', 404);
        }
    }
    public function upcomingEvent() {
        $currentDate = date('Y-m-d H:i:s'); // Tanggal sekarang
    
        // Query untuk memilih event yang tanggal mulai lebih besar dari tanggal sekarang (berarti akan datang)
        $query = "SELECT * FROM event_main WHERE date_start > ? ORDER BY date_start ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$currentDate]);
    
        $data = array();
        
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
                $data[] = $row;
            }
            response(true, 'Upcoming Events Retrieved Successfully', $data);
        } else {
            response(false, 'No upcoming events found', null, 'No events are scheduled for upcoming dates', 404);
        }
    }
}
?>
