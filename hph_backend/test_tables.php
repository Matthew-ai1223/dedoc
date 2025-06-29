<?php
require_once 'config/database.php';

echo "=== Database Tables Test ===\n\n";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✓ Database connection successful\n\n";
        
        // Test if tables exist
        $tables = ['users', 'subscription_plans', 'user_subscriptions', 'health_tips', 'doctor_speeches'];
        
        foreach ($tables as $table) {
            try {
                $query = "SHOW TABLES LIKE '$table'";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    echo "✓ Table '$table' exists\n";
                    
                    // Count records
                    $countQuery = "SELECT COUNT(*) as count FROM $table";
                    $countStmt = $conn->prepare($countQuery);
                    $countStmt->execute();
                    $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
                    echo "  - Records: " . $countResult['count'] . "\n";
                } else {
                    echo "✗ Table '$table' does not exist\n";
                }
            } catch (Exception $e) {
                echo "✗ Error checking table '$table': " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n=== Recommendations ===\n";
        echo "If any tables are missing, run the database schema:\n";
        echo "mysql -u root -p dedoc_health < database/schema.sql\n";
        
    } else {
        echo "✗ Database connection failed\n";
    }
} catch (Exception $e) {
    echo "✗ Database connection error: " . $e->getMessage() . "\n";
}
?> 