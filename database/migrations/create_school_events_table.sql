-- Create school_events table if it doesn't exist
-- Run this SQL in phpMyAdmin or MySQL command line

CREATE TABLE IF NOT EXISTS school_events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME,
    end_date DATE,
    end_time TIME,
    event_type ENUM('holiday', 'examination', 'deadline', 'celebration', 'meeting', 'other') NOT NULL DEFAULT 'other',
    location VARCHAR(255),
    created_by INT NOT NULL,
    is_published BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_date (event_date),
    INDEX idx_event_type (event_type),
    INDEX idx_created_by (created_by)
);

-- If the table already exists but is missing columns, alter it:
-- ALTER TABLE school_events ADD COLUMN IF NOT EXISTS end_date DATE AFTER event_time;
-- ALTER TABLE school_events ADD COLUMN IF NOT EXISTS is_published BOOLEAN DEFAULT TRUE AFTER location;
