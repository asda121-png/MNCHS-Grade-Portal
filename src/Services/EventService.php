<?php
/**
 * Event Service
 * Handles event management and related operations
 */

namespace src\Services;

use src\Models\Event;

class EventService {
    private $eventModel;
    private $notificationService;
    
    public function __construct() {
        $this->eventModel = new Event();
        $this->notificationService = new NotificationService();
    }
    
    /**
     * Create new event
     */
    public function create($title, $eventDate, $endDate, $eventType, $createdBy, $isPublished = true) {
        $eventId = $this->eventModel->create([
            'title' => $title,
            'event_date' => $eventDate,
            'end_date' => $endDate,
            'event_type' => $eventType,
            'created_by' => $createdBy,
            'is_published' => $isPublished ? 1 : 0
        ]);
        
        if ($eventId && $isPublished) {
            $message = "📅 New event: $title";
            $this->notificationService->notifyAllAboutEvent($message, $eventId);
        }
        
        return $eventId;
    }
    
    /**
     * Update event
     */
    public function update($eventId, $data) {
        $result = $this->eventModel->update($eventId, $data);
        
        if ($result) {
            $event = $this->eventModel->find($eventId);
            if ($event && $event['is_published']) {
                $message = "✏️ Event updated: {$event['title']}";
                $this->notificationService->notifyAllAboutEvent($message, $eventId);
            }
        }
        
        return $result;
    }
    
    /**
     * Delete event
     */
    public function delete($eventId) {
        $event = $this->eventModel->find($eventId);
        
        $result = $this->eventModel->delete($eventId);
        
        if ($result && $event && $event['is_published']) {
            $message = "❌ Event deleted: {$event['title']}";
            $this->notificationService->notifyAllAboutEvent($message, $eventId);
        }
        
        return $result;
    }
    
    /**
     * Get event details
     */
    public function getDetails($eventId) {
        return $this->eventModel->getWithCreatorDetails($eventId);
    }
    
    /**
     * Get all published events
     */
    public function getPublished() {
        return $this->eventModel->getPublished();
    }
    
    /**
     * Get upcoming events
     */
    public function getUpcoming($limit = 10) {
        return $this->eventModel->getUpcoming($limit);
    }
    
    /**
     * Get events by date range
     */
    public function getByDateRange($startDate, $endDate) {
        return $this->eventModel->getByDateRange($startDate, $endDate);
    }
    
    /**
     * Get events by type
     */
    public function getByType($eventType) {
        return $this->eventModel->getByType($eventType);
    }
}
