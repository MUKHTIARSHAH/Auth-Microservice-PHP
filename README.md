# PHP REST API with JWT Authentication

A robust, secure, and well-structured PHP REST API project featuring JWT-based authentication, user management, and comprehensive security features. Built with modern PHP practices and follows RESTful principles.

## 🚀 Features

- **JWT Authentication** - Secure token-based authentication with access and refresh tokens
- **User Management** - Complete user registration, login, profile management
- **Password Security** - Strong password validation with configurable requirements
- **Database Abstraction** - Clean database layer using EasyDB and PDO
- **Error Logging** - Comprehensive error logging with user context extraction
- **CORS Support** - Cross-origin resource sharing configured
- **Input Validation** - Server-side validation for all endpoints
- **Rate Limiting** - Built-in rate limiting protection
- **Security Headers** - CSRF protection and security headers
- **API Documentation** - Complete Postman examples and endpoint documentation

## 📋 Requirements

- **PHP**: 8.0 or higher
- **Database**: MySQL/MariaDB
- **Web Server**: Apache (with mod_rewrite) or Nginx
- **Composer**: For dependency management

## 🛠️ Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd 1st_project
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Database Setup
Import the provided SQL file to create the database and table:
```sql
-- Import the info_table_uuid.sql file in your MySQL client
-- This will create the 'first_api' database and 'info' table
```

### 4. Configuration
Update your database credentials in:
- `config/config.php` - Main configuration file
- `config/database.php` - Database connection settings
- `core/db.php` - Core database connection

### 5. Web Server Configuration

#### Apache (.htaccess already included)
The project includes a comprehensive `.htaccess` file with:
- CORS headers
- Compression
- Cache control
- MIME types
- OPTIONS request handling

#### Nginx Configuration
Add this to your Nginx server block:
```nginx
location /1st_project/ {
    try_files $uri $uri/ /1st_project/index.php?$query_string;
    
    # CORS headers
    add_header Access-Control-Allow-Origin "*" always;
    add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;
    add_header Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With" always;
    
    # Handle preflight requests
    if ($request_method = 'OPTIONS') {
        return 204;
    }
}
```

## 📁 Project Structure

```
1st_project/
├── API/
│   └── v1/
│       ├── auth/
│       │   ├── forgot-password.php      # Password reset
│       │   ├── login.php                # User login
│       │   ├── logout.php               # User logout
│       │   ├── refresh-token.php        # Token refresh
│       │   └── token_validation.php     # Token validation
│       └── profile/
│           ├── profile.php              # Get user profile
│           ├── update.php               # Update user profile
│           └── user_registration.php    # User registration
├── config/
│   ├── config.php                       # Main configuration
│   ├── constants.php                    # Application constants
│   ├── database.php                     # Database configuration
│   └── jwt_config.php                   # JWT configuration
├── core/
│   └── db.php                          # Core database connection
├── handlers/
│   ├── auth_handler.php                # Authentication handler
│   └── response_handler.php             # Response handler
├── includes/
│   ├── constants.php                   # Path constants
│   ├── cors.php                        # CORS handling
│   ├── db_handler.php                  # Database handler class
│   ├── error_logger.php                # Error logging system
│   ├── jwt_helper.php                  # JWT helper class
│   ├── password_validator.php         # Password validation
│   ├── response_helpers.php            # Response helper class
│   ├── uuid_helper.php                 # UUID generation
│   └── validation_helpers.php         # Validation helpers
├── tests/
│   └── e2e_test.ps1                    # End-to-end tests
├── vendor/                             # Composer dependencies
├── .htaccess                           # Apache configuration
├── composer.json                       # Composer configuration
├── info_table_uuid.sql                 # Database schema
├── example.text                        # API examples
├── postman_examples.txt                # Postman collection
└── README.md                           # This file
```

## 🔐 Security Features

### Authentication & Authorization
- **JWT Tokens**: Secure token-based authentication
- **Access Tokens**: Short-lived (1 hour) access tokens
- **Refresh Tokens**: Long-lived (7 days) refresh tokens
- **Token Validation**: Comprehensive token validation middleware

### Password Security
- **Strong Requirements**: Configurable password policies
  - Minimum 8 characters
  - Uppercase and lowercase letters
  - Numbers and special characters
- **Hashing**: Secure password hashing using `PASSWORD_DEFAULT`
- **Reset Functionality**: Secure password reset with tokens

### Input Validation & Sanitization
- **Server-side Validation**: All inputs validated server-side
- **SQL Injection Protection**: Prepared statements throughout
- **XSS Protection**: Proper output encoding
- **CSRF Protection**: CSRF token implementation

### Rate Limiting & Security Headers
- **Rate Limiting**: Configurable request rate limits
- **CORS**: Proper cross-origin resource sharing
- **Security Headers**: Essential security headers included
- **Error Logging**: Comprehensive error logging with context

## 📚 API Endpoints

### Authentication Endpoints

#### User Registration
```http
POST /API/v1/profile/user_registration.php
Content-Type: application/json

{
    "full_name": "John Doe",
    "username": "johndoe",
    "email_address": "john@example.com",
    "phone_no": "1234567890",
    "gender": "Male",
    "date_of_birth": "1990-01-01",
    "password": "SecurePassword123!"
}
```

#### User Login
```http
POST /API/v1/auth/login.php
Content-Type: application/json

{
    "username": "johndoe",
    "password": "SecurePassword123!"
}
```

#### Token Validation
```http
GET /API/v1/auth/token_validation.php
Authorization: Bearer <access_token>
```

#### Refresh Token
```http
POST /API/v1/auth/refresh-token.php
Content-Type: application/json

{
    "refresh_token": "<refresh_token>"
}
```

#### Logout
```http
POST /API/v1/auth/logout.php
Authorization: Bearer <access_token>
```

#### Forgot Password
```http
POST /API/v1/auth/forgot-password.php
Content-Type: application/json

{
    "email_address": "john@example.com"
}
```

### Profile Endpoints

#### Get User Profile
```http
GET /API/v1/profile/profile.php
Authorization: Bearer <access_token>
```

#### Update User Profile
```http
POST /API/v1/profile/update.php
Authorization: Bearer <access_token>
Content-Type: application/json

{
    "full_name": "John Updated",
    "email_address": "john.updated@example.com",
    "phone_no": "0987654321",
    "gender": "Male",
    "date_of_birth": "1990-01-01"
}
```

## 📊 Response Format

All API responses follow a consistent format:

### Success Response
```json
{
    "STATUS": "success",
    "MESSAGE": "Operation completed successfully",
    "DATA": {
        // Response data here
    },
    "CODE": 200,
    "TIMESTAMP": "2024-01-01 12:00:00"
}
```

### Error Response
```json
{
    "STATUS": "error",
    "MESSAGE": "Error description",
    "CODE": 400,
    "TIMESTAMP": "2024-01-01 12:00:00"
}
```

### Validation Error Response
```json
{
    "STATUS": "error",
    "MESSAGE": "Validation failed",
    "DATA": {
        "field_name": ["Validation error message"]
    },
    "CODE": 400,
    "TIMESTAMP": "2024-01-01 12:00:00"
}
```

## 🧪 Testing

### Postman Collection
Complete Postman examples are provided in `postman_examples.txt` and `example.text`. Import these into Postman for easy testing.

### End-to-End Tests
Run the PowerShell test script:
```powershell
cd tests
.\e2e_test.ps1
```

### Manual Testing
1. **Registration**: Create a new user account
2. **Login**: Authenticate and receive JWT tokens
3. **Token Validation**: Verify token validity
4. **Profile Operations**: Test profile retrieval and updates
5. **Token Refresh**: Test token refresh mechanism
6. **Password Reset**: Test forgot password functionality

## 🔧 Configuration Options

### Database Configuration
Update these files with your database credentials:
- `config/config.php`
- `config/database.php`
- `core/db.php`

### Security Configuration
In `config/config.php`:
```php
'security' => [
    'jwt_secret' => 'your-secret-key-here',
    'jwt_expiration' => 3600, // 1 hour
    'password_min_length' => 8,
    'password_max_length' => 128,
    'password_requirements' => [
        'uppercase' => true,
        'lowercase' => true,
        'number' => true,
        'special_char' => true,
    ],
],
```

### Rate Limiting
```php
'rate_limit' => [
    'max_requests' => 100,
    'time_window' => 3600, // seconds
],
```

## 📝 Database Schema

The `info` table structure:
```sql
CREATE TABLE `info` (
    `uuid` CHAR(36) NOT NULL PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email_address` VARCHAR(255) NOT NULL UNIQUE,
    `phone_no` VARCHAR(20) NOT NULL,
    `gender` VARCHAR(10) NOT NULL,
    `date_of_birth` DATE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `password_reset_token` VARCHAR(255) NULL,
    `password_reset_expires_at` DATETIME NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 🐛 Error Handling

The API includes comprehensive error handling:
- **Validation Errors**: Detailed field-specific validation messages
- **Authentication Errors**: Clear authentication failure messages
- **Database Errors**: Generic database error messages (security)
- **Server Errors**: Internal server error handling
- **Error Logging**: All errors logged with context

## 🔄 Token Flow

1. **Registration**: User creates account
2. **Login**: User receives access and refresh tokens
3. **API Calls**: Use access token for authenticated requests
4. **Token Refresh**: Use refresh token to get new access token
5. **Logout**: Invalidate current session

## 📈 Performance Features

- **Database Optimization**: Efficient queries with proper indexing
- **Compression**: GZIP compression enabled
- **Caching**: Browser caching headers configured
- **Connection Pooling**: Efficient database connection handling

## 🛡️ Security Best Practices

- **Environment Variables**: Sensitive data in environment variables
- **Input Validation**: All inputs validated and sanitized
- **SQL Injection Prevention**: Prepared statements used throughout
- **XSS Protection**: Output encoding and CSP headers
- **HTTPS**: Recommended for production (SSL/TLS)
- **Rate Limiting**: Protection against brute force attacks

## 📞 Support

For issues and questions:
1. Check the error logs in `logs/api.log`
2. Review the Postman examples for correct request formats
3. Ensure database configuration is correct
4. Verify PHP and web server requirements

## 📄 License

This project is open-source and available under the MIT License.

---

**Note**: This API is designed for educational and development purposes. For production use, ensure proper security auditing and additional hardening measures are implemented.
