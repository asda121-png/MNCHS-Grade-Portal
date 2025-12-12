# API Security, Authentication, and Integration Guide

## Overview
This document provides a comprehensive guide to the API security, authentication, and integration features implemented in the MNCHS Grade Portal.

## Table of Contents
1. [Security Headers](#security-headers)
2. [Authentication](#authentication)
3. [Rate Limiting](#rate-limiting)
4. [Input Validation](#input-validation)
5. [API Integration](#api-integration)
6. [Webhook Support](#webhook-support)

---

## Security Headers

Security headers are automatically set on all API responses to protect against common web vulnerabilities.

### Headers Implemented
- **X-Frame-Options: DENY** - Prevents clickjacking attacks
- **X-Content-Type-Options: nosniff** - Prevents MIME type sniffing
- **X-XSS-Protection: 1; mode=block** - Enables browser XSS protection
- **Strict-Transport-Security** - Forces HTTPS connections
- **Referrer-Policy** - Controls information passed to external sites
- **Content-Security-Policy** - Restricts resource loading
- **Permissions-Policy** - Controls feature access

### Usage
```php
use src\Security\SecurityHeaders;

// Set all security headers
SecurityHeaders::setSecurityHeaders();

// Set CORS headers (specify allowed origins)
$allowed_origins = ['https://yourdom.com', 'https://app.yourdom.com'];
SecurityHeaders::setCORSHeaders($allowed_origins);

// Generate CSRF token
$token = SecurityHeaders::generateCSRFToken();

// Verify CSRF token
if (SecurityHeaders::verifyCSRFToken($_POST['csrf_token'])) {
    // Process form
}
```

---

## Authentication

### JWT (JSON Web Token) Authentication

JWT tokens are used for stateless API authentication, ideal for mobile apps and SPAs.

#### Token Generation
```php
use src\Security\JWTHandler;

// Generate token from user data
$payload = [
    'user_id' => 123,
    'user_type' => 'teacher',
    'username' => 'john_doe'
];

$token = JWTHandler::generateToken($payload);
// Token expires in 24 hours by default
```

#### Token Verification
```php
// Verify and decode token
$payload = JWTHandler::verifyToken($token);

if ($payload) {
    $user_id = $payload['user_id'];
    $user_type = $payload['user_type'];
} else {
    // Invalid or expired token
}
```

#### Token Refresh
```php
// Generate new token from existing token
$new_token = JWTHandler::refreshToken($old_token);
```

### API Authentication Endpoints

#### Generate Token
```bash
POST /server/api/auth.php?action=generate_token
Authorization: Bearer <session_token>

Response:
{
    "success": true,
    "token": "eyJhbGc...",
    "expires_in": 86400,
    "token_type": "Bearer"
}
```

#### Verify Token
```bash
POST /server/api/auth.php?action=verify_token
Content-Type: application/json

{
    "token": "eyJhbGc..."
}

Response:
{
    "success": true,
    "payload": {
        "user_id": 123,
        "user_type": "teacher",
        "exp": 1702502400
    }
}
```

#### Refresh Token
```bash
POST /server/api/auth.php?action=refresh_token
Content-Type: application/json

{
    "token": "eyJhbGc..."
}

Response:
{
    "success": true,
    "token": "eyJhbGc...",
    "expires_in": 86400
}
```

### Using API Authentication in Requests

Add the JWT token to the Authorization header:
```
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

#### PHP Example
```php
use src\Middleware\APIAuthMiddleware;

$auth = APIAuthMiddleware::verifyAPIToken();

if (!$auth['success']) {
    APIResponse::unauthorized($auth['message']);
}

$user_payload = $auth['payload'];
$user_id = $user_payload['user_id'];
```

#### JavaScript Example
```javascript
// Get token from API
async function getToken() {
    const response = await fetch('/server/api/auth.php?action=generate_token', {
        method: 'POST',
        credentials: 'include'
    });
    const data = await response.json();
    return data.token;
}

// Use token in API requests
async function apiRequest(endpoint, method = 'GET', body = null) {
    const token = await getToken();
    
    const options = {
        method,
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        }
    };
    
    if (body) {
        options.body = JSON.stringify(body);
    }
    
    const response = await fetch(endpoint, options);
    return await response.json();
}

// Example usage
apiRequest('/server/api/teachers.php?action=get', 'GET').then(data => {
    console.log(data);
});
```

---

## Rate Limiting

Rate limiting prevents abuse by limiting the number of requests per user or IP address.

### Configuration
- Default limit: 100 requests per hour
- Identifier: User ID (if authenticated) or IP address

### Usage
```php
use src\Security\RateLimiter;

// Check if request is allowed
if (!RateLimiter::isAllowed()) {
    APIResponse::error('Rate limit exceeded', 429);
}

// Get remaining requests
$remaining = RateLimiter::getRemaining();

// Custom limits
if (!RateLimiter::isAllowed(null, 50, 3600)) {
    // 50 requests per hour
}

// Reset limit
RateLimiter::reset();
```

### Automatic Headers
Rate limit information is included in responses:
```
X-Rate-Limit-Limit: 100
X-Rate-Limit-Remaining: 95
X-Rate-Limit-Reset: 1702502400
```

---

## Input Validation

Input validation prevents injection attacks and ensures data integrity.

### Validation Methods

#### Sanitize String
```php
use src\Security\InputValidator;

$name = InputValidator::sanitizeString($_POST['name']);
// Removes HTML tags, trims whitespace
```

#### Validate Email
```php
$email = $_POST['email'];
if (!InputValidator::validateEmail($email)) {
    APIResponse::validationError(['email' => 'Invalid email format']);
}
```

#### Validate Password Strength
```php
// Requires: 8+ chars, uppercase, lowercase, number, special char
if (!InputValidator::validatePasswordStrength($password)) {
    APIResponse::validationError(['password' => 'Password not strong enough']);
}
```

#### Detect SQL Injection
```php
$input = $_POST['search'];
if (InputValidator::detectSQLInjection($input)) {
    APIResponse::error('Invalid input detected', 400);
}
```

#### Validate Multiple Inputs
```php
$errors = [];

if (!InputValidator::validateEmail($email)) {
    $errors['email'] = 'Invalid email';
}

if (!InputValidator::validateInteger($age)) {
    $errors['age'] = 'Age must be a number';
}

if (!empty($errors)) {
    APIResponse::validationError($errors);
}
```

---

## API Integration

### Making External API Requests

#### GET Request
```php
use src\Utilities\APIIntegration;

$result = APIIntegration::get('https://api.example.com/users', 
    ['Authorization' => 'Bearer token'],
    ['page' => 1, 'limit' => 10]
);

if ($result['success']) {
    $data = $result['data'];
} else {
    $error = $result['error'];
}
```

#### POST Request
```php
$data = [
    'name' => 'John Doe',
    'email' => 'john@example.com'
];

$result = APIIntegration::post('https://api.example.com/users', $data);

if ($result['success']) {
    $created_user = $result['data'];
}
```

#### PUT Request
```php
$result = APIIntegration::put('https://api.example.com/users/123', 
    ['name' => 'Jane Doe']
);
```

#### DELETE Request
```php
$result = APIIntegration::delete('https://api.example.com/users/123');
```

---

## Webhook Support

### Receiving Webhooks

```php
use src\Utilities\APIIntegration;

// Handle incoming webhook
APIIntegration::handleWebhook('grade_update', function($method, $headers, $data) {
    // Process webhook data
    return [
        'success' => true,
        'message' => 'Webhook processed'
    ];
});
```

### Sending Webhooks

```php
$webhook_url = 'https://external-service.com/webhooks/grades';
$data = [
    'event' => 'grade.updated',
    'student_id' => 123,
    'grade' => 95
];

$result = APIIntegration::sendWebhook($webhook_url, $data, 'webhook_secret');

if ($result['success']) {
    // Webhook sent successfully
}
```

### Webhook Signature Verification

Webhooks include a signature for security:
```
X-Webhook-Signature: sha256=abcd1234...
```

The signature is computed using HMAC-SHA256:
```php
$signature = hash_hmac('sha256', $payload, $secret);
```

---

## API Response Standardization

All API responses follow a standard format using `APIResponse` class:

### Success Response
```php
use src\Utilities\APIResponse;

APIResponse::success(
    ['id' => 1, 'name' => 'John'],
    'User created successfully',
    201
);

// Output:
{
    "success": true,
    "message": "User created successfully",
    "data": {"id": 1, "name": "John"},
    "timestamp": 1702502400
}
```

### Error Response
```php
APIResponse::error('Invalid input', 400);

// Output:
{
    "success": false,
    "message": "Invalid input",
    "errors": [],
    "timestamp": 1702502400
}
```

### Paginated Response
```php
$users = [...]; // Array of users
$total = 100;

APIResponse::paginated($users, $total, 1, 10, 'Users retrieved');

// Output:
{
    "success": true,
    "message": "Users retrieved",
    "data": [...],
    "pagination": {
        "total": 100,
        "page": 1,
        "per_page": 10,
        "total_pages": 10
    },
    "timestamp": 1702502400
}
```

---

## Best Practices

1. **Always use HTTPS** in production
2. **Rotate JWT secrets regularly**
3. **Validate all inputs** before processing
4. **Use strong passwords** and enforce complexity
5. **Implement CSRF protection** for form submissions
6. **Monitor rate limits** and adjust as needed
7. **Log security events** for audit trails
8. **Use environment variables** for sensitive data
9. **Implement proper error handling** without exposing details
10. **Keep dependencies updated** for security patches

---

## Environment Variables

Add these to your `.env` file:

```env
JWT_SECRET=your-secret-key-here
WEBHOOK_SECRET=your-webhook-secret
API_RATE_LIMIT=100
API_RATE_WINDOW=3600
ALLOWED_ORIGINS=https://localhost,https://yourdomain.com
```

---

## Support

For issues or questions, contact the development team or check the source code documentation in:
- `/src/Security/` - Security classes
- `/src/Middleware/` - Authentication middleware
- `/src/Utilities/` - Response and integration helpers
- `/server/api/` - API endpoints

