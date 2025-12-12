<?php
/**
 * Student Model
 */

namespace src\Models;

class Student extends BaseModel {
    protected $table = 'students';
    
    /**
     * Get student with user details
     */
    public function getWithUserDetails($studentId) {
        return $this->db->fetchOne(
            "SELECT s.*, u.first_name, u.last_name, u.email 
             FROM {$this->table} s 
             JOIN users u ON s.user_id = u.id 
             WHERE s.id = ?",
            [$studentId],
            'i'
        );
    }
    
    /**
     * Get all students with user details
     */
    public function getAllWithUserDetails() {
        return $this->db->fetchAll(
            "SELECT s.*, u.first_name, u.last_name, u.email 
             FROM {$this->table} s 
             JOIN users u ON s.user_id = u.id"
        );
    }
    
    /**
     * Get student by user ID
     */
    public function getByUserId($userId) {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE user_id = ?",
            [$userId],
            'i'
        );
    }
    
    /**
     * Get students by grade level
     */
    public function getByGradeLevel($gradeLevel) {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE grade_level = ?",
            [$gradeLevel],
            's'
        );
    }
    
    /**
     * Get students in a class
     */
    public function getByClassId($classId) {
        return $this->db->fetchAll(
            "SELECT s.* FROM {$this->table} s 
             JOIN class_students cs ON s.id = cs.student_id 
             WHERE cs.class_id = ?",
            [$classId],
            'i'
        );
    }
}
