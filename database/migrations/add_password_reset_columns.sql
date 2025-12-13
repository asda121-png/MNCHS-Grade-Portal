-- Add password reset columns to users table if they don't exist
ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE users ADD COLUMN reset_token_expiry DATETIME NULL DEFAULT NULL;

-- Create an index on reset_token for faster lookups
CREATE INDEX idx_reset_token ON users(reset_token);
