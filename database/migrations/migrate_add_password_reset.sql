-- Add password reset columns to users table
-- Run this if you get errors about reset_token column not existing

ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE users ADD COLUMN reset_token_expiry DATETIME NULL DEFAULT NULL;

-- Create an index for faster lookups
CREATE INDEX IF NOT EXISTS idx_reset_token ON users(reset_token);

-- Verify the columns were added
-- SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'users' AND TABLE_SCHEMA = 'mnchs_grade_portal';
