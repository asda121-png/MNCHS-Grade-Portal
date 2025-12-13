# API Security Implementation - Quick Reference

## Files Created

### Security Classes
1. **JWTHandler.php** - JWT token generation and validation
2. **SecurityHeaders.php** - Security headers and CSRF protection
3. **InputValidator.php** - Input validation and sanitization
4. **RateLimiter.php** - Rate limiting by user/IP

### Authentication & Integration
5. **APIAuthMiddleware.php** - JWT and API key verification
6. **APIResponse.php** - Standardized API responses
7. **APIIntegration.php** - External API and webhook integration

### API Endpoints
8. **auth.php** - Authentication endpoint for token generation/refresh
9. **secure_grades_example.php** - Example of secure API endpoint

### Documentation
10. **API_SECURITY_GUIDE.md** - Complete security documentation

---

## Quick Start Guide

### 1. Implement Security Headers in All APIs

```php
header('Content-Type: application/json');
session_start();

require_once '../../src/Security/SecurityHeaders.php';
use src\Security\SecurityHeaders;

// Set security headers
SecurityHeaders::setSecurityHeaders();
SecurityHeaders::setCORSHeaders(['https://yourdomain.com']);
```

### 2. Add JWT Authentication

```php
require_once '../../src/Middleware/APIAuthMiddleware.php';
use src\Middleware\APIAuthMiddleware;

$auth = APIAuthMiddleware::verifyAPIToken();
if (!$auth['success']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => $auth['message']]);
    exit;
}

$user_id = $auth['payload']['user_id'];
```

### 3. Validate All Inputs

```php
require_once '../../src/Security/InputValidator.php';
use src\Security\InputValidator;

$errors = [];

if (!InputValidator::validateEmail($_POST['email'])) {
    $errors['email'] = 'Invalid email format';
}

if (!InputValidator::validatePasswordStrength($_POST['password'])) {
    $errors['password'] = 'Password must contain uppercase, lowercase, number, and special char';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}
```

### 4. Rate Limit Requests

```php
require_once '../../src/Security/RateLimiter.php';
use src\Security\RateLimiter;

if (!RateLimiter::isAllowed()) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Rate limit exceeded']);
    exit;
}
```

### 5. Use Standard Responses

```php
require_once '../../src/Utilities/APIResponse.php';
use src\Utilities\APIResponse;

// Success
APIResponse::success(['id' => 1, 'name' => 'John'], 'User created', 201);

// Error
APIResponse::error('Validation failed', 400);

// Validation error
APIResponse::validationError(['email' => 'Invalid email']);

// Unauthorized
APIResponse::unauthorized('Invalid token');

// Not found
APIResponse::notFound('User not found');
```

### 6. Integrate External APIs

```php
require_once '../../src/Utilities/APIIntegration.php';
use src\Utilities\APIIntegration;

// GET request
$result = APIIntegration::get('https://api.example.com/users');

// POST request
$result = APIIntegration::post('https://api.example.com/users', ['name' => 'John']);

// Send webhook
APIIntegration::sendWebhook('https://webhook.example.com', ['event' => 'grade_updated']);
```

---

## Integration Checklist

- [ ] Create .env file with `JWT_SECRET`, `WEBHOOK_SECRET`
- [ ] Update existing API endpoints with security headers
- [ ] Add JWT authentication to protected endpoints
- [ ] Implement input validation on all user inputs
- [ ] Set up rate limiting for public APIs
- [ ] Configure CORS for your domain
- [ ] Test authentication endpoints
- [ ] Review API_SECURITY_GUIDE.md documentation
- [ ] Implement webhook handlers for integrations
- [ ] Monitor rate limit usage

---

## Testing API Endpoints

### Generate JWT Token
```bash
curl -X POST http://localhost/server/api/auth.php?action=generate_token \
  -H "Cookie: PHPSESSID=your_session_id"
```

### Verify Token
```bash
curl -X POST http://localhost/server/api/auth.php?action=verify_token \
  -H "Content-Type: application/json" \
  -d '{"token":"eyJhbGc..."}'
```

### Use Token in API Request
```bash
curl -X GET http://localhost/server/api/secure_grades_example.php?action=get_grades \
  -H "Authorization: Bearer eyJhbGc..."
```

### Create Grade (Secure Endpoint)
```bash
curl -X POST http://localhost/server/api/secure_grades_example.php?action=create_grade \
  -H "Authorization: Bearer eyJhbGc..." \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": 1,
    "grade": 95,
    "subject": "Math"
  }'
```

---

## Environment Variables (.env file)

```env
# JWT Configuration
JWT_SECRET=your-secret-key-here-min-32-chars
JWT_EXPIRATION=86400

# API Configuration
API_RATE_LIMIT=100
API_RATE_WINDOW=3600
ALLOWED_ORIGINS=https://localhost,https://yourdomain.com

# Webhook Configuration
WEBHOOK_SECRET=webhook-secret-here

# Database (existing)
DB_HOST=localhost
DB_USERNAME=root
DB_PASSWORD=password
DB_NAME=mnchs_grade_portal
```

---

## Security Best Practices

✅ **DO:**
- Use HTTPS in production
- Validate all inputs
- Use parameterized queries
- Implement rate limiting
- Log security events
- Keep JWT secret safe
- Use strong passwords
- Implement proper error handling
- Monitor API usage

❌ **DON'T:**
- Expose error details to clients
- Store passwords in plain text
- Use weak secrets
- Trust client-side validation
- Share API keys/tokens
- Disable HTTPS
- Skip input validation
- Log sensitive data

---

## Support & Resources

- See **API_SECURITY_GUIDE.md** for detailed documentation
- Check **secure_grades_example.php** for implementation example
- Review source files in `/src/Security/`, `/src/Middleware/`, `/src/Utilities/`

