<?php
require_once "../database/Database.php";
require_once "../helpers/ResponseHelper.php";

class ReplyCommentController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Create replay comment
    public function create($data) {
        // Memeriksa apakah semua data yang diperlukan tersedia
        if (isset($data->users_id, $data->comment_id, $data->content_replay) && 
            !empty($data->users_id) && 
            !empty($data->comment_id) && 
            !empty($data->content_replay)) {
            
            $query = "INSERT INTO replay_comment (users_id, comment_id, content_replay) VALUES (:users_id, :comment_id, :content_replay)";
            $stmt = $this->db->prepare($query);

            $stmt->bindParam(":users_id", $data->users_id);
            $stmt->bindParam(":comment_id", $data->comment_id);
            $stmt->bindParam(":content_replay", $data->content_replay);

            if ($stmt->execute()) {
                response('success', "Reply comment was creatd successfully.", $data, 201);
            } else {
                response('error', "Unable to create replay comment.", null, 503);
            }
        } else {
            response('error', "Data is incomplete.", null, 400);
        }
    }

    // Read all replay comments
    public function read() {
        $query = "SELECT * FROM replay_comment";
        $stmt = $this->db->query($query);
        $replay_comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($replay_comments) > 0) {
            $formatted_comments = array();

            foreach ($replay_comments as $row) {
                $formatted_comments[] = array(
                    "replay_id" => (int)$row["replay_id"], 
                    "users_id" => (int)$row["users_id"],   
                    "comment_id" => (int)$row["comment_id"],
                    "content_replay" => trim($row["content_replay"])
                );
            }

            response('success', 'Get reply comments successfully.', $formatted_comments);
        } else {
            response('error', 'No reply comments found.', null, 404);
        }
    }

    // Update replay comment
    public function update($data) {
        // Memeriksa apakah replay_id dan content_replay tidak kosong
        if (isset($data->replay_id, $data->content_replay) && 
            !empty($data->replay_id) && 
            !empty($data->content_replay)) {
            
            $query = "UPDATE replay_comment SET content_replay = :content_replay WHERE replay_id = :replay_id";
            $stmt = $this->db->prepare($query);

            $stmt->bindParam(":content_replay", $data->content_replay);
            $stmt->bindParam(":replay_id", $data->replay_id);

            if ($stmt->execute()) {
                response('success', 'Reply comment was updated successfully.', $data);
            } else {
                response('error', 'Unable to update replay comment', null, 503);
            }
        } else {
            response('error', 'Data is incomplete.', null, 400);
        }
    }

    // Delete replay comment
    public function delete($data) {
        // Memeriksa apakah replay_id ada dan tidak kosong
        if (isset($data->replay_id) && !empty($data->replay_id)) {
            $query = "DELETE FROM replay_comment WHERE replay_id = :replay_id";
            $stmt = $this->db->prepare($query);

            $stmt->bindParam(":replay_id", $data->replay_id);

            if ($stmt->execute()) {
                http_response_code(200);
                return json_encode(array("message" => "Replay comment was deleted."));
                response('success', 'Reply comment was deleted.');
            } else {
                response('error', 'Unable to delete replay comment.', null, 503);
            }
        } else {
            response('error', 'Data is incomplete.', null, 400);
        }
    }
}
