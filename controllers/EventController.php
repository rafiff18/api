<?php
require_once '../vendor/autoload.php'; 
require_once '../helpers/ResponseHelper.php'; 
require_once '../helpers/FileUploadHelpers.php'; 
require_once '../helpers/JwtHelper.php'; 

class EventController {
    private $db;
    private $jwtHelper;

    private $uploadDir;

    public function __construct($db) {
        $this->db = $db;
        $this->uploadDir = realpath(__DIR__ . '/../images') . '/';
        $this->jwtHelper = new JWTHelper(); 
    }

    private function getRoles() {
        return $this->jwtHelper->getRoles(); 
    }

    private function getUserId() {
        return $this->jwtHelper->getUserId(); 
    }

    public function getEventCountsByStatus() {
        $query = "
            SELECT 
                s.status_id, 
                s.status_name, 
                COUNT(e.event_id) AS event_count
            FROM 
                status s
            LEFT JOIN 
                event e ON s.status_id = e.status
            GROUP BY 
                s.status_id, s.status_name
            ORDER BY 
                s.status_id ASC
        ";
    
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        response('success', 'Event counts by status retrieved successfully.', $counts, 200);
    }


    public function getAllEvents() {
        $category = isset($_GET['category']) ? $_GET['category'] : null;
        $dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : null;
        $dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : null;
        $searchTerm = isset($_GET['search']) ? $_GET['search'] : null;
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        $sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'date_add';
        $sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null; // Let front end decide
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $upcoming = isset($_GET['upcoming']) ? $_GET['upcoming'] : null; // Add upcoming parameter
        $mostLikes = isset($_GET['most_likes']) ? $_GET['most_likes'] : null; // Add most_likes parameter
    
        // Base query to fetch events
        $query = "
            SELECT 
                e.event_id, e.title, e.date_add, u.username AS propose_user, u.avatar AS propose_user_avatar,
                c.category_name AS category, e.description, e.poster, e.location,
                e.place, e.quota, e.date_start, e.date_end, e.schedule, e.updated, a.username AS admin_user,
                s.status_name AS status, e.note,
                GROUP_CONCAT(u_inv.username ORDER BY u_inv.username ASC) AS invited_users,
                GROUP_CONCAT(u_inv.avatar ORDER BY u_inv.username ASC) AS invited_avatars,
                COUNT(l.like_id) AS total_likes -- Count likes for each event
            FROM 
                event e
            LEFT JOIN user u ON e.propose_user_id = u.user_id
            LEFT JOIN category c ON e.category_id = c.category_id
            LEFT JOIN user a ON e.admin_user_id = a.user_id
            LEFT JOIN status s ON e.status = s.status_id
            LEFT JOIN invited i ON e.event_id = i.event_id
            LEFT JOIN user u_inv ON i.user_id = u_inv.user_id
            LEFT JOIN likes l ON e.event_id = l.event_id -- Join the likes table
            WHERE s.status_name = 'approved'";
    
        // Add condition for upcoming events if 'upcoming' parameter is passed
        if ($upcoming === 'true') {
            $query .= " AND e.date_start > NOW()"; // Only upcoming events
        }
    
        // Apply other filters
        $params = [];
        if ($category) {
            $query .= " AND c.category_name = :category";
            $params[':category'] = $category;
        }
        if ($dateFrom) {
            $query .= " AND e.date_start >= :dateFrom";
            $params[':dateFrom'] = $dateFrom;
        }
        if ($dateTo) {
            $query .= " AND e.date_end <= :dateTo";
            $params[':dateTo'] = $dateTo;
        }
        if ($searchTerm) {
            $query .= " AND (e.title LIKE :searchTerm OR e.description LIKE :searchTerm)";
            $params[':searchTerm'] = "%$searchTerm%";
        }
        if ($status) {
            $query .= " AND s.status_name = :status";
            $params[':status'] = $status;
        }
    
        // If most_likes is true, order by total_likes in descending order
        if ($mostLikes === 'true') {
            $query .= " GROUP BY e.event_id ORDER BY total_likes DESC"; // Most liked events first
        } else {
            $query .= " GROUP BY e.event_id ORDER BY $sortBy $sortOrder"; // Default sorting
        }
    
        // Pagination
        if ($limit !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
    
        $stmt = $this->db->prepare($query);
    
        // Bind parameters dynamically
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
    
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
    
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Processing invited users and avatars
        foreach ($events as &$event) {
            $usernames = !empty($event['invited_users']) ? explode(',', $event['invited_users']) : [];
            $avatars = !empty($event['invited_avatars']) ? explode(',', $event['invited_avatars']) : [];
    
            $event['invited_users'] = [];
            foreach ($usernames as $index => $username) {
                $avatar = isset($avatars[$index]) ? $avatars[$index] : null;
                $event['invited_users'][] = [
                    'username' => $username,
                    'avatar' => $avatar
                ];
            }
    
            $event['propose_user_avatar'] = !empty($event['propose_user_avatar']) ? $event['propose_user_avatar'] : null;
            $event['schedule'] = !empty($event['schedule']) ? $event['schedule'] : null;
    
            // Unset unused fields
            unset($event['invited_avatars']);
        }
    
        // Return the events response
        response('success', 'Events retrieved successfully.', $events, 200);
    }
    
    
    
    public function getAllEventsProposeUser($userId) {
        $updateQuery = "
        UPDATE event
        SET status = 6
        WHERE date_end < NOW() AND status = 5
        ";

        $this->db->prepare($updateQuery)->execute();
        
        // Retrieve filters and pagination from query parameters
        $category = isset($_GET['category']) ? $_GET['category'] : null;
        $dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : null;
        $dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : null;
        $searchTerm = isset($_GET['search']) ? $_GET['search'] : null;
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        $sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'date_add';
        $sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
        // Start building the query
            $query = "
            SELECT 
                e.event_id, e.title, e.date_add, u.username AS propose_user, u.avatar AS propose_user_avatar,
                c.category_name AS category, e.description, e.poster, e.location,
                e.place, e.quota, e.date_start, e.date_end, e.schedule, e.updated, a.username AS admin_user,
                s.status_name AS status, e.note,
                GROUP_CONCAT(u_inv.username ORDER BY u_inv.username ASC) AS invited_users,
                GROUP_CONCAT(u_inv.avatar ORDER BY u_inv.username ASC) AS invited_avatars
            FROM 
                event e
            LEFT JOIN user u ON e.propose_user_id = u.user_id
            LEFT JOIN category c ON e.category_id = c.category_id
            LEFT JOIN user a ON e.admin_user_id = a.user_id
            LEFT JOIN status s ON e.status = s.status_id
            LEFT JOIN invited i ON e.event_id = i.event_id
            LEFT JOIN user u_inv ON i.user_id = u_inv.user_id
            WHERE e.propose_user_id = :userId";
    
        // Initialize an array for parameters
        $params = [':userId' => $userId];
    
        // Apply filters if set
        if ($category) {
            $query .= " AND c.category_name = :category";
            $params[':category'] = $category;
        }
        if ($dateFrom) {
            $query .= " AND e.date_start >= :dateFrom";
            $params[':dateFrom'] = $dateFrom;
        }
        if ($dateTo) {
            $query .= " AND e.date_end <= :dateTo";
            $params[':dateTo'] = $dateTo;
        }
        if ($searchTerm) {
            $query .= " AND (e.title LIKE :searchTerm OR e.description LIKE :searchTerm)";
            $params[':searchTerm'] = "%$searchTerm%";
        }
        if ($status) {
            $query .= " AND s.status_name = :status";
            $params[':status'] = $status;
        }
    
        // Include the necessary columns in the GROUP BY clause
        $query .= " GROUP BY e.event_id, e.title, e.date_add, e.schedule, e.updated, u.username, c.category_name, 
                    e.description, e.poster, e.location, e.place, e.quota, e.date_start, e.date_end, a.username, 
                    s.status_name, e.note";
    
        // Sorting
        $query .= " ORDER BY $sortBy $sortOrder";
    
        // Pagination
        if ($limit !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
    
        // Prepare and execute the statement
        $stmt = $this->db->prepare($query);
    
        // Bind the parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
    
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
    
        // Execute and fetch the events
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Convert invited_users to array and include their avatars
        foreach ($events as &$event) {
            $usernames = !empty($event['invited_users']) ? explode(',', $event['invited_users']) : [];
            $avatars = !empty($event['invited_avatars']) ? explode(',', $event['invited_avatars']) : [];
            
            // Map usernames and avatars
            $event['invited_users'] = [];
            foreach ($usernames as $index => $username) {
                $avatar = isset($avatars[$index]) ? $avatars[$index] : null;
                $event['invited_users'][] = [
                    'username' => $username,
                    'avatar' => $avatar
                ];
            }
        
            // Handle propose_user_avatar and schedule
            $event['propose_user_avatar'] = !empty($event['propose_user_avatar']) ? $event['propose_user_avatar'] : null;
            $event['schedule'] = !empty($event['schedule']) ? $event['schedule'] : null;
        
            // Unset unused fields
            unset($event['invited_avatars']);
        }
    
        response('success', 'Events for Propose user retrieved successfully.', $events, 200);
    }
    
    public function getAllEventsAdminUser($adminUserId = null) {
        $updateQuery = "
        UPDATE event
        SET status = 6
        WHERE date_end < NOW() AND status = 5
        ";

        $this->db->prepare($updateQuery)->execute();
        

        // Retrieve filters and pagination from query parameters
        $category = isset($_GET['category']) ? $_GET['category'] : null;
        $dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : null;
        $dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : null;
        $searchTerm = isset($_GET['search']) ? $_GET['search'] : null;
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        $sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'date_add';
        $sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        
        // Start building the query
        $query = "
            SELECT 
                e.event_id, e.title, e.date_add, u.username AS propose_user, u.avatar AS propose_user_avatar,
                c.category_name AS category, e.description, e.poster, e.location,
                e.place, e.quota, e.date_start, e.date_end, e.schedule, e.updated, a.username AS admin_user,
                s.status_name AS status, e.note,
                GROUP_CONCAT(u_inv.username ORDER BY u_inv.username ASC) AS invited_users,
                GROUP_CONCAT(u_inv.avatar ORDER BY u_inv.username ASC) AS invited_avatars
            FROM 
                event e
            LEFT JOIN user u ON e.propose_user_id = u.user_id
            LEFT JOIN category c ON e.category_id = c.category_id
            LEFT JOIN user a ON e.admin_user_id = a.user_id
            LEFT JOIN status s ON e.status = s.status_id
            LEFT JOIN invited i ON e.event_id = i.event_id
            LEFT JOIN user u_inv ON i.user_id = u_inv.user_id
            WHERE 1=1";
        
        $params = [];
        
        if ($adminUserId != null) {
            $query .= " AND e.admin_user_id = :admin_user_id";
            $params[':admin_user_id'] = $adminUserId;
        }
        
        if ($category) {
            $query .= " AND c.category_name = :category";
            $params[':category'] = $category;
        }
        
        if ($dateFrom) {
            $query .= " AND e.date_start >= :dateFrom";
            $params[':dateFrom'] = $dateFrom;
        }
        
        if ($dateTo) {
            $query .= " AND e.date_end <= :dateTo";
            $params[':dateTo'] = $dateTo;
        }
        
        if ($searchTerm) {
            $query .= " AND (e.title LIKE :searchTerm OR e.description LIKE :searchTerm)";
            $params[':searchTerm'] = "%$searchTerm%";
        }
        
        if ($status) {
            $query .= " AND s.status_name = :status";
            $params[':status'] = $status;
        }
        
        // Include the necessary columns in the GROUP BY clause
        $query .= " GROUP BY e.event_id, e.title, e.date_add, u.username, c.category_name, e.description, e.poster, 
            e.location, e.place, e.quota, e.date_start, e.date_end, a.username, s.status_name, e.note";
        
        // Sorting
        $query .= " ORDER BY $sortBy $sortOrder";
        
        // Pagination
        if ($limit !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        // Prepare and execute the query
        $stmt = $this->db->prepare($query);
        
        // Bind the parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        
        // Execute and fetch the events
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert invited_users to array and include their avatars
        foreach ($events as &$event) {
            $usernames = !empty($event['invited_users']) ? explode(',', $event['invited_users']) : [];
            $avatars = !empty($event['invited_avatars']) ? explode(',', $event['invited_avatars']) : [];
            
            // Map usernames and avatars
            $event['invited_users'] = [];
            foreach ($usernames as $index => $username) {
                $avatar = isset($avatars[$index]) ? $avatars[$index] : null;
                $event['invited_users'][] = [
                    'username' => $username,
                    'avatar' => $avatar
                ];
            }
        
            // Handle propose_user_avatar and schedule
            $event['propose_user_avatar'] = !empty($event['propose_user_avatar']) ? $event['propose_user_avatar'] : null;
            $event['schedule'] = !empty($event['schedule']) ? $event['schedule'] : null;
        
            // Unset unused fields
            unset($event['invited_avatars']);
        }
        
       
        response('success', 'Events retrieved successfully.', $events, 200);
    }

    public function getEventsByMostLikes($limit = 10) {
        $query = "
            SELECT e.*, COUNT(l.like_id) AS like_count
            FROM event_main e
            LEFT JOIN likes l ON e.event_id = l.event_id
            GROUP BY e.event_id
            ORDER BY like_count DESC
            LIMIT ?
        ";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $limit, PDO::PARAM_INT);
            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_OBJ);
            response('success', 'Get Events by Most Likes Successfully', $data);
        } catch (PDOException $e) {
            response('error', 'Failed to retrieve events by most likes', null, 500);
        }
    }
     
    public function getEventById($eventId) {
        $updateQuery = "
        UPDATE event
        SET status = 6
        WHERE date_end < NOW() AND status = 5
        ";

        $this->db->prepare($updateQuery)->execute();

        // Verify JWT
        $this->jwtHelper->decodeJWT(); 
    
        // Check if event_id is valid
        if (!is_numeric($eventId)) {
            response('error', 'Invalid event ID.', null, 400);
            return;
        }
    
        // Query to retrieve event data
        $stmt = $this->db->prepare("
            SELECT 
                e.event_id, e.title, e.date_add, u.username AS propose_user, u.avatar AS propose_user_avatar,
                c.category_name AS category, e.description, e.poster, e.location,
                e.place, e.quota, e.date_start, e.date_end, e.schedule, e.updated, a.username AS admin_user,
                s.status_name AS status, e.note,
                GROUP_CONCAT(u_inv.username ORDER BY u_inv.username ASC) AS invited_users,
                GROUP_CONCAT(u_inv.avatar ORDER BY u_inv.username ASC) AS invited_avatars
            FROM 
                event e
            LEFT JOIN user u ON e.propose_user_id = u.user_id
            LEFT JOIN category c ON e.category_id = c.category_id
            LEFT JOIN user a ON e.admin_user_id = a.user_id
            LEFT JOIN status s ON e.status = s.status_id
            LEFT JOIN invited i ON e.event_id = i.event_id
            LEFT JOIN user u_inv ON i.user_id = u_inv.user_id
            WHERE e.event_id = ?
            GROUP BY e.event_id
        ");
        
        $stmt->execute([$eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Check if event is found
        if ($event) {
            // Convert invited_users and invited_user_avatars to arrays
            $usernames = !empty($event['invited_users']) ? explode(',', $event['invited_users']) : [];
            $avatars = !empty($event['invited_avatars']) ? explode(',', $event['invited_avatars']) : [];
    
            $event['invited_users'] = array_map(function ($username, $avatar) {
                return [
                    'username' => $username,
                    'avatar' => !empty($avatar) ? $avatar : null // Explicitly set null for empty avatars
                ];
            }, $usernames, $avatars);
    
            // Remove invited_avatars from the response
            unset($event['invited_avatars']);
    
            // Send successful response
            response('success', 'Event retrieved successfully.', $event, 200);
        } else {
            // If event not found
            response('error', 'Event not found.', null, 404);
        }      
    }
    
    public function createEvent() {
        $this->jwtHelper->decodeJWT(); // Verify JWT
        $roles = $this->getRoles(); // Get roles from JWT
        if (!in_array('Propose', $roles)) {
            response('error', 'Unauthorized.', null, 403);
            return;
        }
    
        // Validate and collect data
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $location = $_POST['location'] ?? '';
        $place = $_POST['place'] ?? '';
        $quota = (int)($_POST['quota'] ?? 0);
        $dateStart = $_POST['date_start'] ?? '';
        $dateEnd = $_POST['date_end'] ?? '';
        $schedule = $_POST['schedule'] ?? null;
        $categoryId = $_POST['category_id'] ?? null;
        $proposeUserId = $this->getUserId();
        $dateAdd = date('Y-m-d H:i:s');
        $status = 1;
    
        // Validate required fields
        if (empty($title) || empty($description) || empty($dateStart) || empty($dateEnd) || $quota <= 0) {
            response('error', 'All fields are required and quota must be greater than 0.', null, 400);
            return;
        }
    
        // Upload poster
        $fileUploadHelper = new FileUploadHelper();
        $poster = $fileUploadHelper->uploadFile($_FILES['poster'], 'poster');
    
        // Insert event into the database
        $stmt = $this->db->prepare("
            INSERT INTO event (title, description, poster, location, place, quota, date_start, date_end, schedule, propose_user_id, category_id, date_add, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
    
        if ($stmt->execute([$title, $description, $poster, $location, $place, $quota, $dateStart, $dateEnd, $schedule, $proposeUserId, $categoryId, $dateAdd, $status])) {
            $eventId = $this->db->lastInsertId();
    
            // Process invited users using their IDs
            $invitedUserIds = [];
            if (isset($_POST['invited_users']) && !empty(trim($_POST['invited_users']))) {
                $userIds = array_map('trim', explode(',', $_POST['invited_users']));
                $invitedUserIds = $this->getExistingUserIds($userIds);
    
                // Validate that all provided user IDs exist
                if (count($userIds) !== count($invitedUserIds)) {
                    response('error', 'Some invited users do not exist.', null, 400);
                    return;
                }
    
                foreach ($invitedUserIds as $userId) {
                    $inviteStmt = $this->db->prepare("INSERT INTO invited (event_id, user_id) VALUES (?, ?)");
                    $inviteStmt->execute([$eventId, $userId]);
                }
            }
    
            // Fetch event data
            $eventStmt = $this->db->prepare("SELECT * FROM event WHERE event_id = ?");
            $eventStmt->execute([$eventId]);
            $event = $eventStmt->fetch(PDO::FETCH_ASSOC);
    
            // Fetch invited users data with username and avatar
            $invitedUsersStmt = $this->db->prepare("
                SELECT u.username, u.avatar 
                FROM user u
                JOIN invited i ON u.user_id = i.user_id
                WHERE i.event_id = ?
            ");
            $invitedUsersStmt->execute([$eventId]);
            $invitedUsers = $invitedUsersStmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Prepare response data
            $responseData = [
                'event' => array_merge($event, [
                    'invited_users' => $invitedUsers
                ])
            ];
    
            response('success', 'Event created successfully.', $responseData, 201);
        } else {
            response('error', 'Failed to create event.', null, 500);
        }
    }

    private function getExistingUserIds(array $userIds) {
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $stmt = $this->db->prepare("SELECT user_id FROM user WHERE user_id IN ($placeholders)");
        $stmt->execute($userIds);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }   

    public function updateEvent($eventId) {
        $this->jwtHelper->decodeJWT(); // Verify JWT
        $roles = $this->getRoles(); // Get roles from JWT
        $userIdFromJWT = $this->getUserId(); // Get user ID from JWT
        
        // Validate roles
        if (!array_intersect(['Propose', 'Admin', 'Superadmin'], $roles)) {
            response('error', 'Unauthorized.', null, 403);
            return;
        }
    
        // For 'Admin' or 'Super Admin', only notes and status can be updated
        if (array_intersect(['Admin', 'Superadmin'], $roles)) {
            $note = $_POST['note'] ?? '';
            $status = $_POST['status'] ?? null;
    
            // // Validate note
            // if (empty($note)) {
            //     response('error', 'Note is required for Admin or Superadmin.', null, 400);
            //     return;
            // }
    
            // Validate status (only one status, between 1 and 6)
            if ($status === null || !is_numeric($status) || (int)$status < 1 || (int)$status > 6) {
                response('error', 'Valid status is required for Admin or Superadmin (values: 1-6).', null, 400);
                return;
            }
    
            // Update note and status, and track admin or Superadmin user
            $stmt = $this->db->prepare("
                UPDATE event
                SET note = ?, status = ?, admin_user_id = ?
                WHERE event_id = ?
            ");
    
            if ($stmt->execute([$note, $status, $userIdFromJWT, $eventId])) {
                $updatedEventStmt = $this->db->prepare("SELECT note, status, admin_user_id FROM event WHERE event_id = ?");
                $updatedEventStmt->execute([$eventId]);
                $updatedEvent = $updatedEventStmt->fetch(PDO::FETCH_ASSOC);
    
                response('success', 'Event updated successfully by Admin or Superadmin.', ['event' => $updatedEvent], 200);
            } else {
                response('error', 'Failed to update event.', null, 500);
            }
        } else if (in_array('Propose', $roles)) {
            // Validate all fields except notes for 'Propose'
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $location = $_POST['location'] ?? '';
            $place = $_POST['place'] ?? '';
            $quota = (int)($_POST['quota'] ?? 0);
            $dateStart = $_POST['date_start'] ?? '';
            $dateEnd = $_POST['date_end'] ?? '';
            $schedule = $_POST['schedule'] ?? '';
            $categoryId = $_POST['category_id'] ?? null;
    
            if (empty($title) || empty($description) || empty($dateStart) || empty($dateEnd) || $quota <= 0) {
                response('error', 'All fields are required and quota must be greater than 0.', null, 400);
                return;
            }
    
            $fileUploadHelper = new FileUploadHelper($this->uploadDir);
    
            // Check if a new file has been uploaded
            $poster = null;
            if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
                $oldPoster = $this->getOldPoster($eventId);
                if ($oldPoster) {
                    $fileUploadHelper->deleteFile($oldPoster);
                }
                $poster = $fileUploadHelper->uploadFile($_FILES['poster'], 'poster', $oldPoster);
            }
    
            // If no new file uploaded, maintain the old poster
            if ($poster === null) {
                $poster = $this->getOldPoster($eventId);
            }
    
            $stmt = $this->db->prepare("
                UPDATE event
                SET title = ?, description = ?, location = ?, place = ?, quota = ?, date_start = ?, date_end = ?, schedule = ?, category_id = ?, poster = ?, status = 3
                WHERE event_id = ?
            ");
    
            if ($stmt->execute([$title, $description, $location, $place, $quota, $dateStart, $dateEnd, $schedule, $categoryId, $poster, $eventId])) {
                $invitedUserIds = [];
                if (isset($_POST['invited_users']) && !empty($_POST['invited_users'])) {
                    $invitedid = explode(',', $_POST['invited_users']);
                    $invitedUserIds = $this->getExistingUserIds(array_map('intval', $invitedid)); // Hanya ID pengguna yang ada
    
                    // Validate that all provided invitedid were found
                    if (count($invitedid) !== count($invitedUserIds)) {
                        response('error', 'Some invited users do not exist.', null, 400);
                        return;
                    }
    
                    $deleteStmt = $this->db->prepare("DELETE FROM invited WHERE event_id = ?");
                    $deleteStmt->execute([$eventId]);
    
                    foreach ($invitedUserIds as $userId) {
                        $inviteStmt = $this->db->prepare("INSERT INTO invited (event_id, user_id) VALUES (?, ?)");
                        $inviteStmt->execute([$eventId, $userId]);
                    }
                }
    
                // Fetch the updated event data
                $updatedEventStmt = $this->db->prepare("SELECT * FROM event WHERE event_id = ?");
                $updatedEventStmt->execute([$eventId]);
                $updatedEvent = $updatedEventStmt->fetch(PDO::FETCH_ASSOC);
    
                // Fetch invited users with username and avatar
                $invitedUsersStmt = $this->db->prepare("
                    SELECT u.username, u.avatar 
                    FROM user u
                    JOIN invited i ON u.user_id = i.user_id
                    WHERE i.event_id = ?
                ");
                $invitedUsersStmt->execute([$eventId]);
                $invitedUsers = $invitedUsersStmt->fetchAll(PDO::FETCH_ASSOC);
    
                response('success', 'Event updated successfully.', [
                    'event' => array_merge($updatedEvent, ['invited_users' => $invitedUsers])
                ], 200);
            } else {
                response('error', 'Failed to update event.', null, 500);
            }
        }
    }     
      
    public function deleteEvent($eventId) {
        $this->jwtHelper->decodeJWT(); // Verify JWT
        $roles = $this->getRoles(); // Get roles from JWT
        if (!in_array('Admin', $roles)) {
            response('error', 'Unauthorized.', null, 403);
            return;
        }
    
        // Retrieve the event to get the poster path
        $stmt = $this->db->prepare("SELECT poster FROM event WHERE event_id = ?");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$event) {
            response('error', 'Event not found.', null, 404);
            return;
        }
    
        $posterPath = $event['poster'] ?? null;
    
        // If there is a poster, delete the file using FileUploadHelper
        if ($posterPath) {
            $fileUploadHelper = new FileUploadHelper(); // You may pass any specific upload dir if needed
            $deleteResult = $fileUploadHelper->deleteFile($posterPath);
    
            if ($deleteResult['status'] !== 'success') {
                response('error', 'Failed to delete poster file.', null, 500);
                return;
            }
        }
    
        // Now proceed to delete the event from the database
        $stmt = $this->db->prepare("DELETE FROM event WHERE event_id = ?");
        if ($stmt->execute([$eventId])) {
            response('success', 'Event deleted successfully.', null, 200);
        } else {
            response('error', 'Failed to delete event.', null, 500);
        }
    }

    public function getUserIdsByUsername(array $usernames) {
        $placeholders = str_repeat('?,', count($usernames) - 1) . '?';
        $sql = "SELECT user_id FROM user WHERE username IN ($placeholders)";
        $stmt = $this->db->prepare($sql);
    
        if (!$stmt->execute($usernames)) {
            response('error', 'Failed to fetch user IDs.', null, 500);
            return [];
        }
    
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getOldPoster($eventId) {
        $stmt = $this->db->prepare("SELECT poster FROM event WHERE event_id = ?");
        $stmt->execute([$eventId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['poster'] : null;
    }
}