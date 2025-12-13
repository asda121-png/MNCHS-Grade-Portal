# API Security, Authentication, and Integration - Implementation Summary

## Overview

A comprehensive security and authentication system has been added to the MNCHS Grade Portal. This system provides:

1. **API Security** - Protection against common web vulnerabilities
2. **Authentication** - JWT-based stateless authentication for APIs
3. **Integration** - Support for external API calls and webhooks

---

## What Was Implemented

### 1. Security Layer

#### Security Headers (`src/Security/SecurityHeaders.php`)
- **X-Frame-Options** - Prevents clickjacking attacks
- **Content-Security-Policy** - Restricts resource loading
- **X-XSS-Protection** - Enables browser XSS protection
- **Strict-Transport-Security** - Forces HTTPS
- **CORS Support** - Configurable cross-origin requests

#### Input Validation (`src/Security/InputValidator.php`)
- Email validation
- Password strength requirements (8+ chars, mixed case, numbers, special chars)
- Phone number validation
- URL validation
- SQL injection detection
- String sanitization

#### JWT Authentication (`src/Security/JWTHandler.php`)
- Token generation with user payload
- Token verification and validation
- Automatic expiration (24 hours default)
- Token refresh functionality
- HMAC-SHA256 signing

#### Rate Limiting (`src/Security/RateLimiter.php`)
- Prevents API abuse
- 100 requests/hour default limit
- Per-user or per-IP tracking
- Configurable time windows

### 2. Authentication Middleware

#### API Authentication (`src/Middleware/APIAuthMiddleware.php`)
- JWT token verification from Authorization header
- API key validation support
- Rate limit checking
- Payload extraction for authenticated requests

### 3. API Integration & Response Standardization

#### API Response Handler (`src/Utilities/APIResponse.php`)
Standardized responses:
- Success responses with data
- Error responses with messages
- Validation error responses
- Paginated responses
- Specific error types (401, 403, 404, 429)

#### External API Integration (`src/Utilities/APIIntegration.php`)
- GET, POST, PUT, DELETE requests
- Webhook sending with signatures
- Webhook receiving and validation
- Custom header support

### 4. API Endpoints

#### Authentication Endpoint (`server/api/auth.php`)
- **POST /server/api/auth.php?action=generate_token** - Get JWT token
- **POST /server/api/auth.php?action=verify_token** - Verify JWT token
- **POST /server/api/auth.php?action=refresh_token** - Refresh expired token

#### Example Secure Endpoint (`server/api/secure_grades_example.php`)
Demonstrates best practices:
- CRUD operations (Create, Read, Update, Delete)
- Input validation on all fields
- JWT authentication
- Rate limiting
- SQL injection prevention
- Standardized responses
- Pagination support

---

## File Structure

```
src/
├── Security/
│   ├── JWTHandler.php              # JWT token generation/validation
│   ├── SecurityHeaders.php         # Security headers & CSRF
│   ├── InputValidator.php          # Input validation & sanitization
│   └── RateLimiter.php             # Rate limiting
├── Middleware/
│   ├── APIAuthMiddleware.php       # JWT & API key verification
│   └── AuthMiddleware.php          # Existing auth middleware
└── Utilities/
    ├── APIResponse.php             # Standardized responses
    └── APIIntegration.php          # External API integration

server/api/
├── auth.php                        # Authentication endpoint
└── secure_grades_example.php       # Example secure endpoint

.env.example                        # Configuration template
API_SECURITY_GUIDE.md              # Complete documentation
QUICK_API_REFERENCE.md             # Quick reference guide
```

---

## How to Use

### 1. Setup Configuration

```bash
# Copy the example configuration
cp .env.example .env

# Edit .env with your settings
JWT_SECRET=your-secret-key-here-32-chars
ALLOWED_ORIGINS=https://yourdomain.com
```

### 2. Generate JWT Token

```php
// From existing session, get a JWT token
POST /server/api/auth.php?action=generate_token

// Returns:
{
    "success": true,
    "token": "eyJhbGc...",
    "expires_in": 86400,
    "token_type": "Bearer"
}
```

### 3. Use Token in API Requests

```bash
curl -X GET http://localhost/server/api/teachers.php \
  -H "Authorization: Bearer eyJhbGc..."
```

### 4. Implement Security in Your Endpoints

```php
// Add to top of any API endpoint
require_once '../../src/Security/SecurityHeaders.php';
require_once '../../src/Security/RateLimiter.php';
require_once '../../src/Middleware/APIAuthMiddleware.php';

use src\Security\SecurityHeaders;
use src\Security\RateLimiter;
use src\Middleware\APIAuthMiddleware;

// Set security headers
SecurityHeaders::setSecurityHeaders();

// Check rate limit
if (!RateLimiter::isAllowed()) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Rate limit exceeded']);
    exit;
}

// Verify JWT token
$auth = APIAuthMiddleware::verifyAPIToken();
if (!$auth['success']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => $auth['message']]);
    exit;
}

$user_id = $auth['payload']['user_id'];
```

---

## Security Features

### ✅ Protected Against

- **XSS Attacks** - Content-Security-Policy, input sanitization
- **CSRF Attacks** - CSRF tokens, SameSite cookies
- **SQL Injection** - Parameterized queries, input validation
- **Clickjacking** - X-Frame-Options header
- **MIME Sniffing** - X-Content-Type-Options header
- **API Abuse** - Rate limiting, JWT expiration
- **Man-in-the-Middle** - HTTPS enforcement, Strict-Transport-Security

### 🔐 Key Features

- **Stateless Authentication** - JWT tokens don't require server-side sessions
- **Automatic Token Expiration** - Tokens expire after 24 hours
- **Rate Limiting** - 100 requests/hour per user/IP
- **Input Validation** - All user inputs validated before processing
- **Error Isolation** - Error details not exposed to clients
- **CORS Control** - Only configured origins allowed
- **Webhook Signing** - Webhooks signed with HMAC-SHA256

---

## Configuration Options

### JWT Configuration
```env
JWT_SECRET=your-secret-key-here
JWT_EXPIRATION=86400  # 24 hours
```

### API Configuration
```env
API_RATE_LIMIT=100        # Requests per window
API_RATE_WINDOW=3600      # Window in seconds (1 hour)
ALLOWED_ORIGINS=https://yourdomain.com
```

### Security
```env
FORCE_HTTPS=true
SESSION_TIMEOUT=1800      # 30 minutes
DEBUG_MODE=false          # Never true in production!
```

---

## Testing

### Test JWT Authentication

```bash
# 1. Generate token
curl -X POST http://localhost/server/api/auth.php?action=generate_token \
  -H "Cookie: PHPSESSID=your_session_id"

# 2. Verify token
curl -X POST http://localhost/server/api/auth.php?action=verify_token \
  -H "Content-Type: application/json" \
  -d '{"token":"eyJhbGc..."}'

# 3. Use token in request
curl -X GET http://localhost/server/api/secure_grades_example.php?action=get_grades \
  -H "Authorization: Bearer eyJhbGc..."
```

### Test Rate Limiting

```bash
# Make 101+ requests to see rate limit in action
for i in {1..105}; do
  curl -H "Authorization: Bearer $TOKEN" \
    http://localhost/server/api/teachers.php
done
```

### Test Input Validation

```bash
# Invalid email
curl -X POST http://localhost/server/api/secure_grades_example.php?action=create_grade \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": "invalid",
    "grade": 150,
    "subject": "Math"
  }'

# Returns validation errors
```

---

## Migration Guide

### For Existing API Endpoints

To add security to existing endpoints:

1. Add security headers at the top
2. Add rate limiting check
3. Add JWT authentication
4. Add input validation
5. Use APIResponse for standardized responses

See `secure_grades_example.php` for complete implementation example.

### For New Endpoints

Use the provided security classes and follow the example implementation.

---

## Documentation

- **API_SECURITY_GUIDE.md** - Complete API documentation
- **QUICK_API_REFERENCE.md** - Quick reference guide
- **.env.example** - Configuration template
- **secure_grades_example.php** - Implementation example
- **Source code comments** - Detailed inline documentation

---

## Next Steps

1. ✅ Copy `.env.example` to `.env` and configure
2. ✅ Review `API_SECURITY_GUIDE.md` for detailed usage
3. ✅ Update existing API endpoints with security features
4. ✅ Test JWT authentication endpoints
5. ✅ Implement webhooks for external integrations
6. ✅ Monitor rate limit usage and adjust if needed
7. ✅ Set up API logging and monitoring

---

## Support

For issues or questions:
1. Review the API_SECURITY_GUIDE.md
2. Check the example implementation in secure_grades_example.php
3. Review source code documentation in `/src/Security/`, `/src/Middleware/`, `/src/Utilities/`
4. Contact the development team

---

## Version

- **System Version**: 1.0.0
- **Implementation Date**: December 2024
- **JWT Algorithm**: HS256
- **Rate Limit**: 100 requests/hour (configurable)
- **Token Expiration**: 24 hours (configurable)

