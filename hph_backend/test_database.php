<?php
/**
 * DeDoc Health System - Database Test Script
 * This script tests if the database tables exist and have data
 */

echo "=== DeDoc Health System Database Test ===\n\n";

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

// Test if tables exist
echo "\n2. Testing if tables exist...\n";
$tables = ['users', 'subscription_plans', 'user_subscriptions', 'health_tips', 'doctor_speeches'];

foreach ($tables as $table) {
    try {
        $query = "SHOW TABLES LIKE '$table'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "✗ Table '$table' does not exist\n";
        }
    } catch (Exception $e) {
        echo "✗ Error checking table '$table': " . $e->getMessage() . "\n";
    }
}

// Test subscription_plans table data
echo "\n3. Testing subscription_plans data...\n";
try {
    $query = "SELECT COUNT(*) as count FROM subscription_plans";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "✓ subscription_plans table has " . $result['count'] . " records\n";
        
        if ($result['count'] > 0) {
            $query = "SELECT * FROM subscription_plans LIMIT 3";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "Sample plans:\n";
            foreach ($plans as $plan) {
                echo "  - " . $plan['name'] . " (₦" . $plan['price'] . ")\n";
            }
        }
    } else {
        echo "✗ Could not count subscription_plans records\n";
    }
} catch (Exception $e) {
    echo "✗ Error checking subscription_plans: " . $e->getMessage() . "\n";
}

// Test users table
echo "\n4. Testing users table...\n";
try {
    $query = "SELECT COUNT(*) as count FROM users";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "✓ users table has " . $result['count'] . " records\n";
    } else {
        echo "✗ Could not count users records\n";
    }
} catch (Exception $e) {
    echo "✗ Error checking users: " . $e->getMessage() . "\n";
}

// Test health_tips table
echo "\n5. Testing health_tips table...\n";
try {
    $query = "SELECT COUNT(*) as count FROM health_tips";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "✓ health_tips table has " . $result['count'] . " records\n";
    } else {
        echo "✗ Could not count health_tips records\n";
    }
} catch (Exception $e) {
    echo "✗ Error checking health_tips: " . $e->getMessage() . "\n";
}

// Test doctor_speeches table
echo "\n6. Testing doctor_speeches table...\n";
try {
    $query = "SELECT COUNT(*) as count FROM doctor_speeches";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "✓ doctor_speeches table has " . $result['count'] . " records\n";
    } else {
        echo "✗ Could not count doctor_speeches records\n";
    }
} catch (Exception $e) {
    echo "✗ Error checking doctor_speeches: " . $e->getMessage() . "\n";
}

echo "\n=== Database Test Complete ===\n";
echo "\nIf any tables are missing or empty, run the following command:\n";
echo "mysql -u root -p dedoc_health < database/schema.sql\n";
?> 