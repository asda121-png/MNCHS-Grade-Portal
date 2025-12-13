# Forgot Password Feature Setup

## Quick Start

### Option 1: Automatic Setup (Recommended)

1. Go to: `http://localhost:3000/setup_password_reset.php`
2. The script will automatically add the required database columns
3. You'll see "Setup Complete!" when done

### Option 2: Manual SQL Setup

Run this SQL in your MySQL database:

```sql
ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE users ADD COLUMN reset_token_expiry DATETIME NULL DEFAULT NULL;
CREATE INDEX idx_reset_token ON users(reset_token);
```

Or run the migration file:

- Import `migrate_add_password_reset.sql` in phpMyAdmin

## How It Works

### User Forgot Password Flow:

1. User clicks "Forgot password?" on login page
2. User enters their email address
3. System generates a reset token and sends link (or displays it in development mode)
4. User clicks the reset link
5. User enters new password
6. Password is reset and they can log in

### Key Features:

- **Secure tokens**: Random 64-character hex tokens
- **Token expiration**: Tokens expire in 1 hour
- **Password hashing**: Uses PHP's `password_hash()` function
- **Validation**: Passwords must be at least 8 characters

## Testing

### Test with this demo account:

- Email: (any user email in your database)
- Username: (any user username in your database)

### Development Mode:

- The reset link is displayed directly on the page
- No email sending required for testing

## File Structure

- `forgot_password.php` - Main forgot password page
- `setup_password_reset.php` - Database setup script
- `migrate_add_password_reset.sql` - SQL migration file
- `index.php` - Links to forgot password page

## Troubleshooting

### "Column doesn't exist" error

- Run `setup_password_reset.php` to add the columns
- Or run the SQL migration file

### "Invalid or expired reset link"

- Token may have expired (1 hour limit)
- Request a new reset link

### Form not submitting

- Check browser console for errors (F12)
- Ensure database connection is working
- Verify email field is not empty

## Production Checklist

- [ ] Database columns added via `setup_password_reset.php`
- [ ] Test with a real account
- [ ] Configure email sending (optional - currently shows link in dev mode)
- [ ] Update forgot_password.php line 1 to disable `display_errors` in production
- [ ] Test reset link functionality
- [ ] Verify tokens expire after 1 hour
- [ ] Test with wrong token
- [ ] Test password validation (must be 8+ characters)
