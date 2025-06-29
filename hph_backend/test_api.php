<?php
/**
 * DeDoc Health System - API Test Script
 * This script tests the basic functionality of the API endpoints
 */

echo "=== DeDoc Health System API Test ===\n\n";

// Test database connection
echo "1. Testing database connection...\n";
require_once 'config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✓ Database connection successful\n";
    } else {
        echo "✗ Database connection failed\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "✗ Database connection error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test health tips endpoint
echo "\n2. Testing health tips endpoint...\n";
$tips_url = 'http://localhost/new%20files/DeDoc/hph_backend/api/health/tips.php';
$tips_response = file_get_contents($tips_url);

if ($tips_response !== false) {
    $tips_data = json_decode($tips_response, true);
    if ($tips_data && isset($tips_data['tips'])) {
        echo "✓ Health tips endpoint working (" . count($tips_data['tips']) . " tips found)\n";
    } else {
        echo "✗ Health tips endpoint returned invalid data\n";
    }
} else {
    echo "✗ Health tips endpoint failed\n";
}

// Test doctor speeches endpoint
echo "\n3. Testing doctor speeches endpoint...\n";
$speeches_url = 'http://localhost/new%20files/DeDoc/hph_backend/api/health/speeches.php';
$speeches_response = file_get_contents($speeches_url);

if ($speeches_response !== false) {
    $speeches_data = json_decode($speeches_response, true);
    if ($speeches_data && isset($speeches_data['speeches'])) {
        echo "✓ Doctor speeches endpoint working (" . count($speeches_data['speeches']) . " speeches found)\n";
    } else {
        echo "✗ Doctor speeches endpoint returned invalid data\n";
    }
} else {
    echo "✗ Doctor speeches endpoint failed\n";
}

// Test subscription plans endpoint
echo "\n4. Testing subscription plans endpoint...\n";
$plans_url = 'http://localhost/new%20files/DeDoc/hph_backend/api/subscription/plans.php';
$plans_response = file_get_contents($plans_url);

if ($plans_response !== false) {
    $plans_data = json_decode($plans_response, true);
    if ($plans_data && isset($plans_data['plans'])) {
        echo "✓ Subscription plans endpoint working (" . count($plans_data['plans']) . " plans found)\n";
    } else {
        echo "✗ Subscription plans endpoint returned invalid data\n";
    }
} else {
    echo "✗ Subscription plans endpoint failed\n";
}

// Test registration endpoint (without actual registration)
echo "\n5. Testing registration endpoint structure...\n";
$register_url = 'http://localhost/new%20files/DeDoc/hph_backend/api/auth/register.php';
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode([
            'fullName' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'phoneNumber' => '1234567890',
            'dateOfBirth' => '1990-01-01',
            'state' => 'Test State',
            'city' => 'Test City',
            'password' => 'password123',
            'confirmPassword' => 'password123'
        ])
    ]
]);

$register_response = file_get_contents($register_url, false, $context);

if ($register_response !== false) {
    $register_data = json_decode($register_response, true);
    if ($register_data) {
        if (isset($register_data['message'])) {
            echo "✓ Registration endpoint responding (message: " . $register_data['message'] . ")\n";
        } else {
            echo "✓ Registration endpoint responding\n";
        }
    } else {
        echo "✗ Registration endpoint returned invalid JSON\n";
    }
} else {
    echo "✗ Registration endpoint failed\n";
}

echo "\n=== API Test Complete ===\n";
echo "\nNote: Some endpoints may return errors if the database is not properly set up\n";
echo "or if required data is missing. This is normal for a fresh installation.\n";
echo "\nTo complete the setup:\n";
echo "1. Import the database schema: mysql -u root -p dedoc_health < database/schema.sql\n";
echo "2. Install Composer dependencies: composer install\n";
echo "3. Configure your web server to serve the PHP files\n";
echo "4. Test with actual user registration and login\n";
?> 