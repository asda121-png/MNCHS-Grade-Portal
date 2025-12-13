# Forgot Password Implementation - Complete Guide

## ✅ Implementation Complete

Your forgot password system has been fully implemented with **user-type aware** flow:

## 🔄 Password Reset Flow

### For Students:

1. **Step 1**: User selects "Student"
2. **Step 2**: Enter LRN (Learner Reference Number)
3. **Step 3**: System finds their email and sends reset link
4. **Step 4**: User clicks link and resets password

### For Teachers/Parents/Admins:

1. **Step 1**: User selects their account type
2. **Step 2**: Enter email address
3. **Step 3**: System sends reset link
4. **Step 4**: User clicks link and resets password

## 📋 Database Setup

Run this SQL command in your database:

```sql
-- Add password reset columns if not already present
ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE users ADD COLUMN reset_token_expiry DATETIME NULL DEFAULT NULL;
CREATE INDEX idx_reset_token ON users(reset_token);
```

Or visit: **`http://localhost:3000/setup_password_reset.php`**

## 🧪 Testing

### Test with a Student Account:

1. Go to: `http://localhost:3000/forgot_password.php`
2. Click "Student"
3. Enter a student's LRN
4. See the reset link
5. Click the link and set new password

### Test with Teacher/Parent/Admin:

1. Go to: `http://localhost:3000/forgot_password.php`
2. Select the account type
3. Enter their email
4. See the reset link
5. Click the link and set new password

## 🔒 Security Features

✓ **Secure Tokens**: Random 64-character hex tokens  
✓ **Token Expiration**: Tokens expire in 1 hour  
✓ **Password Hashing**: Uses `password_hash()` function  
✓ **Validation**: Passwords must be 8+ characters  
✓ **User Type Verification**: Different flows for different user types  
✓ **LRN Validation**: Students identified by LRN for extra security

## 📁 Files Modified

- `forgot_password.php` - Complete password reset system
- `index.php` - Updated "Forgot password?" link
- `setup_password_reset.php` - Database setup helper
- `migrate_add_password_reset.sql` - SQL migration file

## 🚀 How to Use

### Step 1: Set up database

```bash
Visit: http://localhost:3000/setup_password_reset.php
OR
Run the SQL commands above in phpMyAdmin
```

### Step 2: Test the system

```bash
1. Go to: http://localhost:3000/index.php
2. Click "Forgot password?"
3. Follow the prompts
```

### Step 3: For Production

- Disable error reporting in `forgot_password.php` (line 5)
- Configure email sending (optional - currently displays link in dev mode)
- Test with real accounts

## 🔧 Configuration

### Email Sending (Optional)

Currently, the reset link is displayed on-screen for testing.  
To enable email sending, install PHPMailer and configure SMTP in `forgot_password.php`.

### Token Expiration

Default: **1 hour**  
Edit line in code: `strtotime('+1 hour')` to change duration

### Password Requirements

Default: **Minimum 8 characters**  
Edit line in code: `strlen($new_password) < 8` to change

## ✅ Verification Checklist

- [ ] Database columns added via setup script
- [ ] Test with student account (LRN)
- [ ] Test with teacher account (email)
- [ ] Test with parent account (email)
- [ ] Test with admin account (email)
- [ ] Verify reset link works
- [ ] Verify token expiration (wait 1 hour)
- [ ] Verify password requirements
- [ ] Test "Back" buttons
- [ ] Test invalid/expired tokens

## 📊 Database Schema

**Users Table Additional Columns:**

```sql
reset_token VARCHAR(255) NULL DEFAULT NULL      -- Unique reset token
reset_token_expiry DATETIME NULL DEFAULT NULL   -- When token expires
```

**Index:**

```sql
idx_reset_token  -- For fast token lookups
```

## 🆘 Troubleshooting

### "Column doesn't exist" error

→ Run `setup_password_reset.php` or execute the SQL migration

### "No student found" error

→ Verify the LRN exists in the `students` table

### "Invalid reset link"

→ Token may have expired (1 hour limit) or URL was modified

### Form not appearing

→ Check browser console (F12) for JavaScript errors
→ Verify database connection is working

## 📞 Support

All files are self-contained and require only:

- PHP 7.0+
- MySQL/MariaDB
- Tailwind CSS (already loaded via CDN)

Good luck! 🎉
