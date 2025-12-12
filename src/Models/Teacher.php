<?php
/**
 * Teacher Model
 */

namespace src\Models;

class Teacher extends BaseModel {
    protected $table = 'teachers';
    
    /**
     * Get teacher with user details
     */
    public function getWithUserDetails($teacherId) {
        return $this->db->fetchOne(
            "SELECT t.*, u.first_name, u.last_name, u.email 
             FROM {$this->table} t 
             JOIN users u ON t.user_id = u.id 
             WHERE t.id = ?",
            [$teacherId],
            'i'
        );
    }
    
    /**
     * Get all teachers with user details
     */
    public function getAllWithUserDetails() {
        return $this->db->fetchAll(
            "SELECT t.*, u.first_name, u.last_name, u.email 
             FROM {$this->table} t 
             JOIN users u ON t.user_id = u.id"
        );
    }
    
    /**
     * Get teacher by user ID
     */
    public function getByUserId($userId) {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE user_id = ?",
            [$userId],
            'i'
        );
    }
}
