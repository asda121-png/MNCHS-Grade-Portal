<?php
/**
 * GradingPeriod Model
 */

namespace src\Models;

class GradingPeriod extends BaseModel {
    protected $table = 'grading_periods';
    
    /**
     * Get active grading period
     */
    public function getActive() {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} 
             WHERE start_date <= CURDATE() AND end_date >= CURDATE()"
        );
    }
    
    /**
     * Get current quarter
     */
    public function getCurrentQuarter() {
        return $this->db->fetchOne(
            "SELECT quarter FROM {$this->table} 
             WHERE start_date <= CURDATE() AND end_date >= CURDATE()"
        );
    }
    
    /**
     * Check if grading period is active
     */
    public function isActive() {
        $active = $this->getActive();
        return $active !== null && $active !== false;
    }
    
    /**
     * Get grading periods by school year
     */
    public function getBySchoolYear($schoolYear) {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} 
             WHERE YEAR(start_date) = ? OR YEAR(end_date) = ? 
             ORDER BY quarter ASC",
            [$schoolYear, $schoolYear],
            'ii'
        );
    }
    
    /**
     * Get all active periods (current and future)
     */
    public function getActivePeriods() {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} 
             WHERE end_date >= CURDATE() 
             ORDER BY quarter ASC"
        );
    }
    
    /**
     * Get grading period by quarter
     */
    public function getByQuarter($quarter) {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE quarter = ?",
            [$quarter],
            'i'
        );
    }
}
