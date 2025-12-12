<?php
/**
 * Grade Model
 */

namespace src\Models;

class Grade extends BaseModel {
    protected $table = 'grades';
    
    /**
     * Get grades for student in a class
     */
    public function getByStudentAndClass($studentId, $classId) {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} 
             WHERE student_id = ? AND class_id = ? 
             ORDER BY quarter ASC",
            [$studentId, $classId],
            'ii'
        );
    }
    
    /**
     * Get grade for student in class for specific quarter
     */
    public function getByStudentClassQuarter($studentId, $classId, $quarter) {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} 
             WHERE student_id = ? AND class_id = ? AND quarter = ?",
            [$studentId, $classId, $quarter],
            'iii'
        );
    }
    
    /**
     * Get all grades for a class in quarter
     */
    public function getByClassAndQuarter($classId, $quarter) {
        return $this->db->fetchAll(
            "SELECT g.*, s.first_name, s.last_name 
             FROM {$this->table} g 
             JOIN students s ON g.student_id = s.id 
             WHERE g.class_id = ? AND g.quarter = ? 
             ORDER BY s.last_name, s.first_name ASC",
            [$classId, $quarter],
            'ii'
        );
    }
    
    /**
     * Get student average for class
     */
    public function getStudentAverage($studentId, $classId) {
        $result = $this->db->fetchOne(
            "SELECT AVG(grade_value) as average 
             FROM {$this->table} 
             WHERE student_id = ? AND class_id = ?",
            [$studentId, $classId],
            'ii'
        );
        return $result ? $result['average'] : 0;
    }
    
    /**
     * Check if grade exists
     */
    public function exists($studentId, $classId, $quarter) {
        $result = $this->db->fetchOne(
            "SELECT id FROM {$this->table} 
             WHERE student_id = ? AND class_id = ? AND quarter = ?",
            [$studentId, $classId, $quarter],
            'iii'
        );
        return $result !== null && $result !== false;
    }
}
