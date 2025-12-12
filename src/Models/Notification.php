<?php
/**
 * Notification Model
 */

namespace src\Models;

class Notification extends BaseModel {
    protected $table = 'notifications';
    
    /**
     * Get unread notifications for user
     */
    public function getUnread($userId) {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} 
             WHERE user_id = ? AND is_read = 0 
             ORDER BY created_at DESC",
            [$userId],
            'i'
        );
    }
    
    /**
     * Get all notifications for user
     */
    public function getForUser($userId, $limit = 50) {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} 
             WHERE user_id = ? 
             ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit],
            'ii'
        );
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId) {
        return $this->db->execute(
            "UPDATE {$this->table} SET is_read = 1 WHERE id = ?",
            [$notificationId],
            'i'
        );
    }
    
    /**
     * Mark all user notifications as read
     */
    public function markAllAsRead($userId) {
        return $this->db->execute(
            "UPDATE {$this->table} SET is_read = 1 WHERE user_id = ?",
            [$userId],
            'i'
        );
    }
    
    /**
     * Get unread count for user
     */
    public function getUnreadCount($userId) {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM {$this->table} 
             WHERE user_id = ? AND is_read = 0",
            [$userId],
            'i'
        );
        return $result ? $result['count'] : 0;
    }
    
    /**
     * Delete old notifications
     */
    public function deleteOld($days = 30) {
        return $this->db->execute(
            "DELETE FROM {$this->table} 
             WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$days],
            'i'
        );
    }
}
