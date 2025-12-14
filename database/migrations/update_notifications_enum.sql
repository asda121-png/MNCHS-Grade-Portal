-- Update the notifications table to support event_update and event_delete notification types
ALTER TABLE notifications MODIFY type ENUM('grade', 'event', 'event_update', 'event_delete', 'message', 'system') DEFAULT 'system';
