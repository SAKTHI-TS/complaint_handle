<?php
require_once dirname(__FILE__) . '/../config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

class Database {
    private $connection;
    
    public function __construct() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            die("Error in query preparation: " . $this->connection->error);
        }
        
        if (!empty($params)) {
            $types = '';
            $values = [];
            
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
                $values[] = $param;
            }
            
            $stmt->bind_param($types, ...$values);
        }
        
        $stmt->execute();
        
        if ($stmt->error) {
            die("Error in query execution: " . $stmt->error);
        }
        
        return $stmt;
    }
    
    public function fetchAssoc($stmt) {
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : false;
    }
    
    public function fetchAll($stmt) {
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
    
    public function __destruct() {
        $this->connection->close();
    }
}
?>