<?php
/**
 * Database Connection Class
 * Handles all database connections and queries
 */

namespace src\Database;

use mysqli;

class Connection {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Singleton pattern - get single instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Connect to database
     */
    private function connect() {
        require_once __DIR__ . '/../../config/database.php';
        
        $this->connection = new mysqli(
            DB_HOST,
            DB_USER,
            DB_PASSWORD,
            DB_NAME
        );
        
        if ($this->connection->connect_error) {
            throw new \Exception('Database connection failed: ' . $this->connection->connect_error);
        }
        
        $this->connection->set_charset("utf8mb4");
    }
    
    /**
     * Get raw connection
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Execute a prepared statement
     */
    public function execute($query, $params = [], $types = '') {
        $stmt = $this->connection->prepare($query);
        
        if (!$stmt) {
            throw new \Exception('Prepare failed: ' . $this->connection->error);
        }
        
        if (!empty($params)) {
            if (!is_array($types) && empty($types)) {
                $types = str_repeat('s', count($params));
            }
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new \Exception('Execute failed: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result ?: true;
    }
    
    /**
     * Fetch single row
     */
    public function fetchOne($query, $params = [], $types = '') {
        $result = $this->execute($query, $params, $types);
        
        if ($result instanceof \mysqli_result) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Fetch all rows
     */
    public function fetchAll($query, $params = [], $types = '') {
        $result = $this->execute($query, $params, $types);
        $rows = [];
        
        if ($result instanceof \mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        
        return $rows;
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->connection->insert_id;
    }
    
    /**
     * Close connection
     */
    public function close() {
        if (isset($this->connection)) {
            $this->connection->close();
        }
    }
}
