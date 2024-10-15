<?php
class Database {
    private $host = "localhost";
    private $db_name = "event_management"; 
    private $username = "root";    
    private $password = "";       
    public $conn;

    // Fungsi untuk mendapatkan koneksi ke database
    public function getConnection() {
        $this->conn = null;

        try {
            // Membuat koneksi baru dengan PDO
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db_name}", $this->username, $this->password);
            // Set atribut untuk menampilkan error sebagai Exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Menampilkan pesan error jika koneksi gagal
            echo "Connection failed: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>
