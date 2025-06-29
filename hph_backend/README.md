# DeDoc Health System - PHP Backend API

This is the PHP backend API for the DeDoc Health System, providing authentication, user management, subscription handling, and health content delivery.

## Features

- **User Authentication**: Registration, login, and JWT token management
- **User Management**: Profile management and user data handling
- **Subscription System**: Plan management and payment processing
- **Health Content**: Dynamic health tips and doctor speeches
- **Payment Processing**: Secure payment handling with transaction tracking
- **Security**: JWT authentication, CORS handling, and input validation

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (for dependency management)

## Installation

### 1. Database Setup

1. Create a MySQL database named `dedoc_health`
2. Import the database schema:
   ```bash
   mysql -u root -p dedoc_health < database/schema.sql
   ```

### 2. Configuration

1. Update database connection settings in `config/database.php`:
   ```php
   private $host = "localhost";
   private $db_name = "dedoc_health";
   private $username = "your_username";
   private $password = "your_password";
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

### 3. Web Server Configuration

#### Apache Configuration
Ensure the following modules are enabled:
- mod_rewrite
- mod_headers

#### Nginx Configuration
Add the following to your nginx configuration:
```nginx
location /hph_backend/ {
    try_files $uri $uri/ /hph_backend/index.php?$query_string;
}
```

## API Endpoints

### Authentication

#### POST `/api/auth/register.php`
Register a new user account.

**Request Body:**
```json
{
    "fullName": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "phoneNumber": "1234567890",
    "dateOfBirth": "1990-01-01",
    "state": "Lagos",
    "city": "Lagos",
    "password": "password123",
    "confirmPassword": "password123"
}
```

**Response:**
```json
{
    "message": "User registered successfully.",
    "token": "jwt_token_here",
    "user": {
        "id": 1,
        "fullName": "John Doe",
        "username": "johndoe",
        "email": "john@example.com",
        // ... other user data
    }
}
```

#### POST `/api/auth/login.php`
Authenticate user and get access token.

**Request Body:**
```json
{
    "username": "johndoe",
    "password": "password123"
}
```

#### GET `/api/auth/user.php`
Get current user profile data.

**Headers:**
```
Authorization: Bearer <jwt_token>
```

### Subscription Management

#### GET `/api/subscription/status.php`
Get current user's subscription status.

**Headers:**
```
Authorization: Bearer <jwt_token>
```

#### GET `/api/subscription/plans.php`
Get all available subscription plans.

#### POST `/api/payment/process.php`
Process subscription payment.

**Headers:**
```
Authorization: Bearer <jwt_token>
```

**Request Body:**
```json
{
    "planId": 2,
    "paymentMethod": "card",
    "amount": 5000.00
}
```

### Health Content

#### GET `/api/health/tips.php`
Get random health tips.

#### GET `/api/health/speeches.php`
Get random doctor speeches.

## Database Schema

### Users Table
- `id` - Primary key
- `full_name` - User's full name
- `username` - Unique username
- `email` - Unique email address
- `phone_number` - Phone number
- `date_of_birth` - Date of birth
- `state` - State of residence
- `city` - City of residence
- `password_hash` - Hashed password
- `blood_type` - Blood type (optional)
- `height` - Height in cm (optional)
- `weight` - Weight in kg (optional)
- `allergies` - Allergies information (optional)
- `emergency_contact_*` - Emergency contact details
- `profile_image` - Profile image path
- `is_active` - Account status
- `email_verified` - Email verification status
- `created_at` - Account creation timestamp
- `updated_at` - Last update timestamp

### Subscription Plans Table
- `id` - Primary key
- `name` - Plan name
- `description` - Plan description
- `price` - Plan price
- `duration_days` - Plan duration in days
- `features` - JSON array of features
- `is_active` - Plan availability status

### User Subscriptions Table
- `id` - Primary key
- `user_id` - Foreign key to users table
- `plan_id` - Foreign key to subscription_plans table
- `status` - Subscription status (active/expired/cancelled/pending)
- `start_date` - Subscription start date
- `end_date` - Subscription end date
- `last_payment_date` - Last payment date
- `next_payment_date` - Next payment due date
- `payment_method` - Payment method used

### Payments Table
- `id` - Primary key
- `user_id` - Foreign key to users table
- `subscription_id` - Foreign key to user_subscriptions table
- `amount` - Payment amount
- `currency` - Payment currency
- `payment_method` - Payment method
- `transaction_id` - Unique transaction ID
- `status` - Payment status
- `payment_data` - JSON payment details

## Security Features

- **JWT Authentication**: Secure token-based authentication
- **Password Hashing**: Bcrypt password hashing
- **Input Validation**: Comprehensive input sanitization and validation
- **CORS Protection**: Cross-origin request handling
- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: Output encoding and sanitization

## Error Handling

The API returns appropriate HTTP status codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `404` - Not Found
- `500` - Internal Server Error

All error responses include a `message` field with descriptive error information.

## Frontend Integration

The frontend files have been updated to connect to this PHP backend:

1. **register.html** - Updated to use `/api/auth/register.php`
2. **login.html** - Updated to use `/api/auth/login.php`
3. **dashboard.html** - Updated to use various API endpoints
4. **payment.html** - Can be updated to use `/api/payment/process.php`

## Development

### Adding New Endpoints

1. Create the endpoint file in the appropriate directory
2. Include necessary configuration and model files
3. Add proper error handling and validation
4. Update this README with endpoint documentation

### Database Changes

1. Update the schema file (`database/schema.sql`)
2. Create migration scripts if needed
3. Update affected model classes
4. Test thoroughly

## Troubleshooting

### Common Issues

1. **CORS Errors**: Ensure CORS headers are properly set in `config/cors.php`
2. **Database Connection**: Verify database credentials in `config/database.php`
3. **JWT Errors**: Check if the JWT library is properly installed via Composer
4. **Permission Issues**: Ensure web server has read/write permissions to the backend directory

### Logs

Error logs are stored in `logs/php_errors.log` (if the logs directory exists and is writable).

## License

This project is part of the DeDoc Health System and is proprietary software. 