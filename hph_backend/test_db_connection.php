<?php
require_once 'config/database.php';

echo "=== Database Connection Test ===\n\n";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✓ Database connection successful\n";
        
        // Test a simple query
        $stmt = $conn->query("SELECT 1 as test");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            echo "✓ Database query test successful\n";
        } else {
            echo "✗ Database query test failed\n";
        }
    } else {
        echo "✗ Database connection failed\n";
    }
} catch (Exception $e) {
    echo "✗ Database connection error: " . $e->getMessage() . "\n";
}
?> 