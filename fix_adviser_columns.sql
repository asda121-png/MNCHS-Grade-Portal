-- Fix adviser role support to teachers table
-- This migration adds support for the new adviser/subject-teacher role system

-- Add is_adviser column to track if teacher is a class adviser (if not already exists)
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS is_adviser BOOLEAN DEFAULT 0;

-- Add adviser_class_id column to store the class ID for which the teacher is an adviser (if not already exists)
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS adviser_class_id INT NULL;

-- Add foreign key constraint for adviser_class_id (if not already exists)
ALTER TABLE teachers ADD CONSTRAINT fk_adviser_class
FOREIGN KEY (adviser_class_id) REFERENCES classes(id) ON DELETE SET NULL;

-- Create index for efficient lookups
CREATE INDEX idx_adviser_class ON teachers(adviser_class_id);
CREATE INDEX idx_is_adviser ON teachers(is_adviser);
