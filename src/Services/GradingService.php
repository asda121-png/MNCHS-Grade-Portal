<?php
/**
 * Grading Service
 * Handles grade management and calculations
 */

namespace src\Services;

use src\Models\Grade;
use src\Models\GradingPeriod;

class GradingService {
    private $gradeModel;
    private $gradingPeriodModel;
    
    public function __construct() {
        $this->gradeModel = new Grade();
        $this->gradingPeriodModel = new GradingPeriod();
    }
    
    /**
     * Submit grade
     */
    public function submitGrade($studentId, $classId, $quarter, $gradeValue) {
        // Validate grade value (0-100)
        if ($gradeValue < 0 || $gradeValue > 100) {
            return [
                'success' => false,
                'message' => 'Grade must be between 0 and 100'
            ];
        }
        
        // Check if grade already exists
        if ($this->gradeModel->exists($studentId, $classId, $quarter)) {
            $this->gradeModel->update($this->getGradeId($studentId, $classId, $quarter), [
                'grade_value' => $gradeValue
            ]);
        } else {
            $this->gradeModel->create([
                'student_id' => $studentId,
                'class_id' => $classId,
                'quarter' => $quarter,
                'grade_value' => $gradeValue
            ]);
        }
        
        return [
            'success' => true,
            'message' => 'Grade submitted successfully'
        ];
    }
    
    /**
     * Get grades for student in class
     */
    public function getStudentClassGrades($studentId, $classId) {
        return $this->gradeModel->getByStudentAndClass($studentId, $classId);
    }
    
    /**
     * Get student average
     */
    public function getStudentAverage($studentId, $classId) {
        return $this->gradeModel->getStudentAverage($studentId, $classId);
    }
    
    /**
     * Get class grades for quarter
     */
    public function getClassGrades($classId, $quarter) {
        return $this->gradeModel->getByClassAndQuarter($classId, $quarter);
    }
    
    /**
     * Check if grading period is active
     */
    public function isGradingActive() {
        return $this->gradingPeriodModel->isActive();
    }
    
    /**
     * Get active grading period
     */
    public function getActiveGradingPeriod() {
        return $this->gradingPeriodModel->getActive();
    }
    
    /**
     * Get current quarter
     */
    public function getCurrentQuarter() {
        return $this->gradingPeriodModel->getCurrentQuarter();
    }
    
    /**
     * Get helper method for grade ID
     */
    private function getGradeId($studentId, $classId, $quarter) {
        $grade = $this->gradeModel->getByStudentClassQuarter($studentId, $classId, $quarter);
        return $grade ? $grade['id'] : null;
    }
}
