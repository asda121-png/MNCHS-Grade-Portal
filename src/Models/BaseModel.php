<?php
/**
 * Base Model Class
 * Provides common functionality for all models
 */

namespace src\Models;

use src\Database\Connection;

abstract class BaseModel {
    protected $db;
    protected $table = '';
    protected $attributes = [];
    protected $fillable = [];
    protected $hidden = [];
    
    public function __construct() {
        $this->db = Connection::getInstance();
    }
    
    /**
     * Set attribute
     */
    public function __set($name, $value) {
        $this->attributes[$name] = $value;
    }
    
    /**
     * Get attribute
     */
    public function __get($name) {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }
    
    /**
     * Find by ID
     */
    public function find($id) {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id],
            'i'
        );
    }
    
    /**
     * Get all records
     */
    public function all() {
        return $this->db->fetchAll("SELECT * FROM {$this->table}");
    }
    
    /**
     * Create new record
     */
    public function create($data) {
        $columns = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $columnNames = implode(', ', $columns);
        
        $query = "INSERT INTO {$this->table} ({$columnNames}) VALUES ({$placeholders})";
        
        $this->db->execute($query, array_values($data));
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Update record
     */
    public function update($id, $data) {
        $sets = [];
        $values = [];
        
        foreach ($data as $column => $value) {
            $sets[] = "{$column} = ?";
            $values[] = $value;
        }
        
        $values[] = $id;
        $setClause = implode(', ', $sets);
        
        $query = "UPDATE {$this->table} SET {$setClause} WHERE id = ?";
        
        $this->db->execute($query, $values);
        
        return true;
    }
    
    /**
     * Delete record
     */
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $this->db->execute($query, [$id], 'i');
        
        return true;
    }
    
    /**
     * Get records by condition
     */
    public function where($column, $operator, $value) {
        $query = "SELECT * FROM {$this->table} WHERE {$column} {$operator} ?";
        
        return $this->db->fetchAll($query, [$value], 's');
    }
    
    /**
     * Convert to array
     */
    public function toArray() {
        $data = $this->attributes;
        
        foreach ($this->hidden as $key) {
            unset($data[$key]);
        }
        
        return $data;
    }
}
