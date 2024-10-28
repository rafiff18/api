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
            response(false, 'Failed to Retrieve Events', null, [
                'code' => 500,
                'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
            ]);
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
                response(false, 'Event not found', null, [
                    'code' => 404,
                    'message' => 'The requested resource could not be found'
                ]);
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
                response(false, 'Missing Parameters', null, [
                    'code' => 402,
                    'message' => "Bad request: Missing parameter $field"
                ]);
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
            response(false, 'Failed to Add Event', null, [
                'code' => 500,
                'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
            ]);
        }
    }
    
    public function updateEvent($id) {
        $input = json_decode(file_get_contents('php://input'), true);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            response(false, 'Invalid JSON Format', null, [
                'code' => 400,
                'message' => 'Bad request: JSON parsing error'
            ]);
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
            response(false, 'Failed to Update Event', null, [
                'code' => 500,
                'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
            ]);
        }
    }
    
    public function deleteEvent($id) {
        $stmt = $this->conn->prepare('DELETE FROM event_main WHERE event_id = ?');

        if ($stmt->execute([$id])) {
            response(true, 'Event Deleted Successfully');
        } else {
            response(false, 'Failed to Delete Event', null, [
                'code' => 500,
                'message' => 'Internal server error: ' . $this->conn->errorInfo()[2]
            ]);
        }
    }
    public function searchEvent($keyword) {
        $query = "SELECT * FROM event_main WHERE title LIKE ? OR location LIKE ? OR desc_event LIKE ?";
        $stmt = $this->conn->prepare($query);
        
        $keyword = "%" . $keyword . "%";
        $stmt->execute([$keyword, $keyword, $keyword]);
    
        $data = array();
    
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
                $data[] = $row;
            }
            response(true, 'Events Found', $data);
        } else {
            response(false, 'No events found matching the search keyword', null, [
                'code' => 404,
                'message' => 'No matching events'
            ]);
        }
    }
    public function filterEventsByDate($filterType) {
        $currentDate = date('Y-m-d H:i:s');
        $query = "";
        
        switch ($filterType) {
            case 'latest': // Event terbaru
                $query = "SELECT * FROM event_main ORDER BY date_add DESC";
                break;
            
            case 'last7days': // 7 hari terakhir
                $query = "SELECT * FROM event_main WHERE date_add >= DATE_SUB(?, INTERVAL 7 DAY) ORDER BY date_add DESC";
                break;
                
            case 'last30days': // 1 bulan terakhir
                $query = "SELECT * FROM event_main WHERE date_add >= DATE_SUB(?, INTERVAL 30 DAY) ORDER BY date_add DESC";
                break;
            
            default:
                response(false, 'Invalid filter type', null, [
                    'code' => 400,
                    'message' => 'Invalid filter type provided'
                ]);
                return;
        }

        $stmt = $this->conn->prepare($query);

        // Jika menggunakan filter 7 hari atau 30 hari terakhir, masukkan $currentDate sebagai parameter
        if ($filterType == 'last7days' || $filterType == 'last30days') {
            $stmt->execute([$currentDate]);
        } else {
            $stmt->execute();
        }

        $data = array();
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
                $data[] = $row;
            }
            response(true, 'Events filtered successfully', $data);
        } else {
            response(false, 'No events found for this filter', null, [
                'code' => 404,
                'message' => 'No matching events for this filter'
            ]);
        }
    }
    public function getCategoryById($id) {
        if ($id == 0) {
            response(false, 'Invalid ID', null, [
                'code' => 401,
                'message' => 'Bad request: ID is required'
            ]);
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
            response(false, 'Category not found', null, [
                'code' => 404,
                'message' => 'The requested category could not be found'
            ]);
        }
    }
    
    
}

    

?>
