<?php
/**
 * Notification Service
 * Handles notification creation, retrieval, and management
 */

namespace src\Services;

use src\Models\Notification;
use src\Database\Connection;

class NotificationService {
    private $notificationModel;
    private $db;
    
    public function __construct() {
        $this->notificationModel = new Notification();
        $this->db = Connection::getInstance();
    }
    
    /**
     * Create notification for user
     */
    public function create($userId, $message, $type = 'general', $referenceId = null) {
        return $this->notificationModel->create([
            'user_id' => $userId,
            'message' => $message,
            'type' => $type,
            'reference_id' => $referenceId,
            'is_read' => 0
        ]);
    }
    
    /**
     * Notify multiple users
     */
    public function notifyUsers($userIds, $message, $type = 'general', $referenceId = null) {
        $createdIds = [];
        
        foreach ($userIds as $userId) {
            $id = $this->create($userId, $message, $type, $referenceId);
            if ($id) {
                $createdIds[] = $id;
            }
        }
        
        return count($createdIds) > 0;
    }
    
    /**
     * Get unread notifications for user
     */
    public function getUnread($userId) {
        return $this->notificationModel->getUnread($userId);
    }
    
    /**
     * Get all notifications for user
     */
    public function getForUser($userId, $limit = 50) {
        return $this->notificationModel->getForUser($userId, $limit);
    }
    
    /**
     * Get unread count for user
     */
    public function getUnreadCount($userId) {
        return $this->notificationModel->getUnreadCount($userId);
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId) {
        return $this->notificationModel->markAsRead($notificationId);
    }
    
    /**
     * Mark all user notifications as read
     */
    public function markAllAsRead($userId) {
        return $this->notificationModel->markAllAsRead($userId);
    }
    
    /**
     * Delete notification
     */
    public function delete($notificationId) {
        return $this->notificationModel->delete($notificationId);
    }
    
    /**
     * Clean up old notifications
     */
    public function cleanup($days = 30) {
        return $this->notificationModel->deleteOld($days);
    }
    
    /**
     * Notify all teachers about event
     */
    public function notifyTeachersAboutEvent($message, $eventId) {
        $teachers = $this->db->fetchAll(
            "SELECT id FROM users WHERE role = 'teacher'"
        );
        
        $teacherIds = array_column($teachers, 'id');
        return $this->notifyUsers($teacherIds, $message, 'event', $eventId);
    }
    
    /**
     * Notify all students about event
     */
    public function notifyStudentsAboutEvent($message, $eventId) {
        $students = $this->db->fetchAll(
            "SELECT user_id FROM students"
        );
        
        $studentIds = array_unique(array_column($students, 'user_id'));
        return $this->notifyUsers($studentIds, $message, 'event', $eventId);
    }
    
    /**
     * Notify all users about event
     */
    public function notifyAllAboutEvent($message, $eventId) {
        $this->notifyTeachersAboutEvent($message, $eventId);
        return $this->notifyStudentsAboutEvent($message, $eventId);
    }
}
