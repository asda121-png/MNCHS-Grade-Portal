-- Create Grading Periods Table
CREATE TABLE IF NOT EXISTS grading_periods (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quarter INT NOT NULL CHECK(quarter >= 1 AND quarter <= 4),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_quarter_year (quarter, YEAR(start_date)),
    INDEX idx_start_date (start_date),
    INDEX idx_end_date (end_date)
);

-- Sample Grading Periods for School Year 2024-2025
INSERT INTO grading_periods (quarter, start_date, end_date, created_by) VALUES
(1, '2024-06-01', '2024-08-31', 1),
(2, '2024-09-01', '2024-11-30', 1),
(3, '2024-12-01', '2025-02-28', 1),
(4, '2025-03-01', '2025-05-31', 1)
ON DUPLICATE KEY UPDATE quarter=quarter;
