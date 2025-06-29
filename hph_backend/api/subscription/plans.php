<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../models/Subscription.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize subscription object
$subscription = new Subscription($db);

// Get all subscription plans
$plans = $subscription->getAllPlans();

if ($plans) {
    http_response_code(200);
    echo json_encode(array(
        "message" => "Subscription plans retrieved successfully.",
        "plans" => $plans
    ));
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No subscription plans found."));
}
?> 