<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "Database connection successful!\n";
        
        // Test if tables exist
        $tables = ['users', 'subscription_plans', 'user_subscriptions', 'payments'];
        foreach ($tables as $table) {
            $query = "SHOW TABLES LIKE '$table'";
            $stmt = $db->prepare($query);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                echo "Table '$table' exists\n";
            } else {
                echo "Table '$table' does not exist\n";
            }
        }
    } else {
        echo "Failed to connect to database\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 