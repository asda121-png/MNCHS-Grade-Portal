# 🔐 MNCHS Grade Portal - API Security System

## Overview

A comprehensive **API Security, Authentication, and Integration** system has been implemented for the MNCHS Grade Portal. This system provides enterprise-grade security for all API endpoints, protecting against common web vulnerabilities while enabling secure external integrations.

---

## 🚀 Quick Start (5 Minutes)

### 1. Setup Configuration
```bash
# Copy configuration template
cp .env.example .env

# Edit .env and set your values
nano .env
```

**Required settings:**
```env
JWT_SECRET=your-secret-key-here-minimum-32-characters
```

### 2. Get Your First Token
```bash
# After logging in, generate a JWT token
curl -X POST http://localhost/server/api/auth.php?action=generate_token \
  -H "Cookie: PHPSESSID=your_session_id"

# Response:
{
  "success": true,
  "token": "eyJhbGc...",
  "expires_in": 86400
}
```

### 3. Use Token in API Requests
```bash
# Use the token to authenticate API requests
curl -X GET http://localhost/server/api/teachers.php \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## 📁 What's Included

### Security Classes (4 files)
| Class | Purpose |
|-------|---------|
| **JWTHandler** | JWT token generation, verification, refresh |
| **SecurityHeaders** | Security headers, CORS, CSRF protection |
| **InputValidator** | Input validation, SQL injection detection |
| **RateLimiter** | API request rate limiting |

### Middleware (1 file)
| Class | Purpose |
|-------|---------|
| **APIAuthMiddleware** | JWT and API key verification for protected endpoints |

### Utilities (2 files)
| Class | Purpose |
|-------|---------|
| **APIResponse** | Standardized API response formatting |
| **APIIntegration** | External API calls and webhook support |

### API Endpoints (2 files)
| Endpoint | Purpose |
|----------|---------|
| **auth.php** | Authentication - token generation, verification, refresh |
| **secure_grades_example.php** | Example showing best practices for secure endpoints |

---

## 🔒 Security Features

### ✅ Protection Against Common Attacks

| Attack Type | Protection | How |
|------------|-----------|-----|
| **XSS** | Content-Security-Policy | HTTP headers + input sanitization |
| **CSRF** | CSRF Tokens | Token generation and verification |
| **SQL Injection** | Input Validation | Pattern detection + sanitization |
| **Clickjacking** | X-Frame-Options | Prevents embedding in iframes |
| **API Abuse** | Rate Limiting | 100 requests/hour per user/IP |
| **Weak Passwords** | Validation Rules | 8+ chars, mixed case, numbers, special chars |
| **Token Tampering** | JWT Verification | HMAC-SHA256 signatures |
| **Unauthorized Access** | JWT Authentication | Token expiration + verification |

### ✅ Advanced Features

- **JWT Tokens** - Stateless, expiring tokens for scalable authentication
- **Token Refresh** - Automatic token renewal without re-logging in
- **Pagination** - For efficient handling of large datasets
- **Webhooks** - Send and receive webhooks with cryptographic signatures
- **External APIs** - Easy integration with third-party services
- **CORS Control** - Restrict API access to configured domains only
- **Rate Limiting** - Configurable limits per user/IP

---

## 📚 Documentation

Read these in order:

1. **[QUICK_API_REFERENCE.md](QUICK_API_REFERENCE.md)** ⚡
   - Quick start in 5 minutes
   - Code snippets for common tasks
   - Integration checklist
   - Testing commands

2. **[API_SECURITY_GUIDE.md](API_SECURITY_GUIDE.md)** 📖
   - Complete reference documentation
   - Detailed feature explanations
   - Code examples for all classes
   - Best practices

3. **[IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md)** 🎯
   - Overview of what was implemented
   - File structure
   - Configuration options
   - Migration guide

4. **[secure_grades_example.php](server/api/secure_grades_example.php)** 💡
   - Complete, working example
   - Shows all security features
   - CRUD operations
   - Error handling

---

## 🔧 Configuration

### Basic Setup
```env
# Required - set this first!
JWT_SECRET=your-secret-key-here-minimum-32-characters

# Optional - defaults shown
API_RATE_LIMIT=100              # Max requests per window
API_RATE_WINDOW=3600            # Time window in seconds (1 hour)
JWT_EXPIRATION=86400            # Token lifetime in seconds (24 hours)
ALLOWED_ORIGINS=https://yourdomain.com
WEBHOOK_SECRET=your-webhook-secret
```

See **.env.example** for all available options.

---

## 💻 API Endpoints

### Authentication
```
POST /server/api/auth.php?action=generate_token
  → Get JWT token from session

POST /server/api/auth.php?action=verify_token
  → Verify and decode JWT token

POST /server/api/auth.php?action=refresh_token
  → Refresh expired token
```

### Example (Secure Grades)
```
GET  /server/api/secure_grades_example.php?action=get_grades
  → Get list of grades (paginated)

POST /server/api/secure_grades_example.php?action=create_grade
  → Create new grade entry

PUT  /server/api/secure_grades_example.php?action=update_grade
  → Update existing grade

DELETE /server/api/secure_grades_example.php?action=delete_grade
  → Delete grade entry
```

---

## 🧪 Testing

### Test JWT Token Flow
```bash
# 1. Generate token (after login)
TOKEN=$(curl -s -X POST http://localhost/server/api/auth.php?action=generate_token \
  -H "Cookie: PHPSESSID=session_id" | jq -r '.token')

# 2. Verify token
curl -X POST http://localhost/server/api/auth.php?action=verify_token \
  -H "Content-Type: application/json" \
  -d "{\"token\":\"$TOKEN\"}"

# 3. Use token in API request
curl -X GET http://localhost/server/api/secure_grades_example.php?action=get_grades \
  -H "Authorization: Bearer $TOKEN"
```

### Test Input Validation
```bash
# Invalid grade (should fail)
curl -X POST http://localhost/server/api/secure_grades_example.php?action=create_grade \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": "invalid",
    "grade": 150,
    "subject": "Math"
  }'

# Valid grade (should succeed)
curl -X POST http://localhost/server/api/secure_grades_example.php?action=create_grade \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": 1,
    "grade": 95,
    "subject": "Math"
  }'
```

### Test Rate Limiting
```bash
# Make 101+ requests to trigger rate limit
for i in {1..105}; do
  curl -H "Authorization: Bearer $TOKEN" \
    http://localhost/server/api/teachers.php
  echo "Request $i"
done
```

---

## 🏗️ File Structure

```
src/
├── Security/
│   ├── JWTHandler.php
│   ├── SecurityHeaders.php
│   ├── InputValidator.php
│   └── RateLimiter.php
├── Middleware/
│   └── APIAuthMiddleware.php
└── Utilities/
    ├── APIResponse.php
    └── APIIntegration.php

server/api/
├── auth.php
└── secure_grades_example.php

Documentation/
├── API_SECURITY_GUIDE.md
├── QUICK_API_REFERENCE.md
├── IMPLEMENTATION_SUMMARY.md
├── .env.example
└── README.md (this file)
```

---

## 💡 Usage Examples

### Use in Your Own Endpoint

```php
<?php
header('Content-Type: application/json');
session_start();

require_once '../../src/Security/SecurityHeaders.php';
require_once '../../src/Security/RateLimiter.php';
require_once '../../src/Middleware/APIAuthMiddleware.php';
require_once '../../src/Utilities/APIResponse.php';

use src\Security\SecurityHeaders;
use src\Security\RateLimiter;
use src\Middleware\APIAuthMiddleware;
use src\Utilities\APIResponse;

// 1. Set security headers
SecurityHeaders::setSecurityHeaders();

// 2. Check rate limit
if (!RateLimiter::isAllowed()) {
    APIResponse::error('Rate limit exceeded', 429);
}

// 3. Verify authentication
$auth = APIAuthMiddleware::verifyAPIToken();
if (!$auth['success']) {
    APIResponse::unauthorized($auth['message']);
}

$user_id = $auth['payload']['user_id'];

// 4. Your API logic here
APIResponse::success(['message' => 'Success'], 'Operation completed');
?>
```

### Validate Input

```php
use src\Security\InputValidator;

$email = $_POST['email'];
if (!InputValidator::validateEmail($email)) {
    APIResponse::validationError(['email' => 'Invalid email format']);
}

$password = $_POST['password'];
if (!InputValidator::validatePasswordStrength($password)) {
    APIResponse::validationError([
        'password' => 'Must contain uppercase, lowercase, number, and special char'
    ]);
}
```

### Call External API

```php
use src\Utilities\APIIntegration;

$result = APIIntegration::get('https://api.example.com/users', 
    ['Authorization' => 'Bearer token'],
    ['page' => 1]
);

if ($result['success']) {
    $users = $result['data'];
} else {
    $error = $result['error'];
}
```

### Send Webhook

```php
use src\Utilities\APIIntegration;

APIIntegration::sendWebhook(
    'https://webhook.example.com/grades',
    [
        'event' => 'grade.updated',
        'student_id' => 123,
        'grade' => 95
    ],
    'webhook_secret_key'
);
```

---

## ⚙️ Configuration Details

### JWT Configuration
```env
JWT_SECRET=your-secret-key-here        # Required
JWT_EXPIRATION=86400                   # 24 hours
```
- **Security Note**: Change JWT_SECRET in production!
- Minimum 32 characters recommended
- Use a strong, random string

### Rate Limiting
```env
API_RATE_LIMIT=100                     # Requests per window
API_RATE_WINDOW=3600                   # Seconds (1 hour)
```
- Can be overridden per endpoint
- Tracks by user ID or IP address
- Automatically cleaned up

### CORS Configuration
```env
ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com
```
- Comma-separated list of allowed origins
- Prevents unauthorized cross-origin requests
- Set to production domain in production

### Additional Options
```env
FORCE_HTTPS=true                       # Require HTTPS
SESSION_TIMEOUT=1800                   # 30 minutes
DEBUG_MODE=false                       # Never true in production!
```

---

## 🚨 Important Security Notes

### ⚠️ DO NOT
- ❌ Use default JWT_SECRET in production
- ❌ Enable DEBUG_MODE in production
- ❌ Commit .env to version control
- ❌ Share API tokens or secrets
- ❌ Trust client-side validation only
- ❌ Disable HTTPS in production

### ✅ DO
- ✅ Use strong, random secrets (32+ characters)
- ✅ Rotate secrets periodically
- ✅ Keep dependencies updated
- ✅ Monitor rate limit usage
- ✅ Log security events
- ✅ Validate all inputs server-side
- ✅ Use HTTPS in production
- ✅ Test security regularly

---

## 🔄 Migration Guide

To add security to existing endpoints:

1. **Add security headers** (top of file)
   ```php
   SecurityHeaders::setSecurityHeaders();
   ```

2. **Check rate limit**
   ```php
   if (!RateLimiter::isAllowed()) {
       APIResponse::error('Rate limit exceeded', 429);
   }
   ```

3. **Verify authentication**
   ```php
   $auth = APIAuthMiddleware::verifyAPIToken();
   if (!$auth['success']) {
       APIResponse::unauthorized($auth['message']);
   }
   ```

4. **Validate inputs**
   ```php
   if (!InputValidator::validateEmail($email)) {
       APIResponse::validationError(['email' => 'Invalid']);
   }
   ```

5. **Use standardized responses**
   ```php
   APIResponse::success($data, 'Message', 200);
   ```

See **secure_grades_example.php** for complete example.

---

## 📊 Feature Comparison

| Feature | Status | Details |
|---------|--------|---------|
| JWT Authentication | ✅ | Token generation & verification |
| Token Refresh | ✅ | Automatic renewal support |
| Rate Limiting | ✅ | 100/hour, configurable |
| Input Validation | ✅ | Email, password, SQL injection |
| Security Headers | ✅ | HSTS, CSP, XSS Protection |
| CORS Protection | ✅ | Configurable origins |
| CSRF Protection | ✅ | Token-based |
| Error Handling | ✅ | Standardized responses |
| Pagination | ✅ | For large datasets |
| Webhooks | ✅ | With HMAC signatures |
| External APIs | ✅ | GET/POST/PUT/DELETE |

---

## 🆘 Troubleshooting

### "Invalid token" error
- Check JWT_SECRET is set in .env
- Verify token hasn't expired (24 hours default)
- Ensure Authorization header format: `Bearer YOUR_TOKEN`

### "Rate limit exceeded" error
- Check API_RATE_LIMIT and API_RATE_WINDOW in .env
- Verify user/IP identification
- Use `RateLimiter::reset()` to clear limits

### Validation errors
- Check input meets requirements
- Email must be valid format
- Password must have: uppercase, lowercase, number, special char
- Grade must be 0-100

### CORS errors
- Add your domain to ALLOWED_ORIGINS in .env
- Format: `https://yourdomain.com` (no trailing slash)
- Multiple origins: comma-separated

---

## 📞 Getting Help

1. **Review documentation**
   - API_SECURITY_GUIDE.md (complete reference)
   - QUICK_API_REFERENCE.md (quick start)
   - IMPLEMENTATION_SUMMARY.md (overview)

2. **Check examples**
   - secure_grades_example.php (complete working example)
   - Code comments in source files

3. **Review configuration**
   - .env.example (all options)
   - Check logs in storage/logs/

4. **Test endpoints**
   - Use curl commands (see Testing section)
   - Check HTTP response codes
   - Verify JSON response format

---

## 📝 License

MNCHS Grade Portal - API Security System
Implementation Date: December 2024
Version: 1.0.0

---

## ✨ What's Next?

1. ✅ Copy .env.example to .env
2. ✅ Configure JWT_SECRET
3. ✅ Review API_SECURITY_GUIDE.md
4. ✅ Test authentication endpoints
5. ✅ Update existing API endpoints
6. ✅ Implement webhooks
7. ✅ Monitor and adjust

Happy Coding! 🚀

