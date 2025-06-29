<?php
/**
 * DeDoc Health System - Setup Script
 * Run this script to set up the database and check system requirements
 */

echo "=== DeDoc Health System Setup ===\n\n";

// Check PHP version
echo "Checking PHP version...\n";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo "✓ PHP version " . PHP_VERSION . " is compatible\n";
} else {
    echo "✗ PHP version " . PHP_VERSION . " is too old. Please upgrade to PHP 7.4 or higher\n";
    exit(1);
}

// Check required PHP extensions
echo "\nChecking required PHP extensions...\n";
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'openssl'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ $ext extension is loaded\n";
    } else {
        echo "✗ $ext extension is missing\n";
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    echo "\nPlease install the missing extensions: " . implode(', ', $missing_extensions) . "\n";
    exit(1);
}

// Check if composer is available
echo "\nChecking Composer...\n";
if (file_exists('vendor/autoload.php')) {
    echo "✓ Composer dependencies are installed\n";
} else {
    echo "✗ Composer dependencies are not installed\n";
    echo "Please run: composer install\n";
    exit(1);
}

// Test database connection
echo "\nTesting database connection...\n";
require_once 'config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✓ Database connection successful\n";
        
        // Check if tables exist
        $tables = ['users', 'subscription_plans', 'user_subscriptions', 'payments', 'health_tips', 'doctor_speeches'];
        $missing_tables = [];
        
        foreach ($tables as $table) {
            $stmt = $conn->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            
            if ($stmt->rowCount() > 0) {
                echo "✓ Table '$table' exists\n";
            } else {
                echo "✗ Table '$table' is missing\n";
                $missing_tables[] = $table;
            }
        }
        
        if (!empty($missing_tables)) {
            echo "\nMissing tables: " . implode(', ', $missing_tables) . "\n";
            echo "Please import the database schema from database/schema.sql\n";
        } else {
            echo "\n✓ All database tables are present\n";
        }
        
    } else {
        echo "✗ Database connection failed\n";
        echo "Please check your database configuration in config/database.php\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "✗ Database connection error: " . $e->getMessage() . "\n";
    echo "Please check your database configuration in config/database.php\n";
    exit(1);
}

// Check directory permissions
echo "\nChecking directory permissions...\n";
$directories = ['logs', 'uploads'];
$missing_dirs = [];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✓ Created directory '$dir'\n";
        } else {
            echo "✗ Failed to create directory '$dir'\n";
            $missing_dirs[] = $dir;
        }
    } else {
        if (is_writable($dir)) {
            echo "✓ Directory '$dir' is writable\n";
        } else {
            echo "✗ Directory '$dir' is not writable\n";
            $missing_dirs[] = $dir;
        }
    }
}

if (!empty($missing_dirs)) {
    echo "\nPlease ensure the following directories are writable: " . implode(', ', $missing_dirs) . "\n";
}

// Check .htaccess
echo "\nChecking .htaccess file...\n";
if (file_exists('.htaccess')) {
    echo "✓ .htaccess file exists\n";
} else {
    echo "✗ .htaccess file is missing\n";
    echo "Please ensure the .htaccess file is present for proper URL rewriting\n";
}

echo "\n=== Setup Complete ===\n";
echo "\nNext steps:\n";
echo "1. Ensure your web server is configured to serve PHP files\n";
echo "2. Test the API endpoints using a tool like Postman or curl\n";
echo "3. Update the frontend files to point to the correct API URLs\n";
echo "4. Configure your payment gateway integration if needed\n";
echo "\nFor more information, see README.md\n";
?> 