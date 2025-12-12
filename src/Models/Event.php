<?php
/**
 * Event Model
 */

namespace src\Models;

class Event extends BaseModel {
    protected $table = 'school_events';
    protected $hidden = [];
    
    /**
     * Get event with creator details
     */
    public function getWithCreatorDetails($eventId) {
        return $this->db->fetchOne(
            "SELECT e.*, u.first_name, u.last_name 
             FROM {$this->table} e 
             LEFT JOIN users u ON e.created_by = u.id 
             WHERE e.id = ?",
            [$eventId],
            'i'
        );
    }
    
    /**
     * Get all published events
     */
    public function getPublished() {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE is_published = 1 ORDER BY event_date ASC"
        );
    }
    
    /**
     * Get upcoming events
     */
    public function getUpcoming($limit = 10) {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} 
             WHERE event_date >= CURDATE() AND is_published = 1 
             ORDER BY event_date ASC LIMIT ?",
            [$limit],
            'i'
        );
    }
    
    /**
     * Get events by date range
     */
    public function getByDateRange($startDate, $endDate) {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} 
             WHERE event_date >= ? AND event_date <= ? 
             ORDER BY event_date ASC",
            [$startDate, $endDate],
            'ss'
        );
    }
    
    /**
     * Get events by type
     */
    public function getByType($eventType) {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} 
             WHERE event_type = ? ORDER BY event_date ASC",
            [$eventType],
            's'
        );
    }
}
