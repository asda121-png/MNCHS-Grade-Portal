-- Migration: Update LRN field to support 12 digits instead of 8
-- Date: December 13, 2025
-- Description: Modifies the lrn generation and validation to use 12-digit LRN numbers
-- Format: YYYY + 8-digit counter (e.g., 202400000001)

USE mnchs_grade_portal;

-- The students table lrn column already uses VARCHAR(50) which supports 12+ digits
-- Update the column comment to document the 12-digit requirement
ALTER TABLE students 
  MODIFY COLUMN lrn VARCHAR(50) UNIQUE COMMENT 'Learner Reference Number - 12 digits (YYYY + 8-digit counter)';

-- Confirm: LRN field now supports 12-digit format (YYYY followed by 8-digit counter)
-- Example valid LRN: 202400000001, 202500000042, etc.
