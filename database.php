<?php
class Database {
    private $host = "localhost";
    private $db_name = "event_db";
    private $username = "root"; 
    private $password = "";     
    public $conn;

    // Fungsi untuk mendapatkan koneksi ke database
    public function getConnection() {
        $this->conn = null;

        try {
            // Membuat koneksi baru dengan MySQL menggunakan mysqli
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            
            // Cek apakah ada error koneksi
            if ($this->conn->connect_errno) {
                throw new Exception("Gagal terhubung ke MySQL: " . $this->conn->connect_error);
            }
        } catch (Exception $e) {
            echo "Connection error: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>
