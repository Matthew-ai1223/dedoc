<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../models/Subscription.php';
require_once '../../utils/jwt_helper.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get JWT token
$token = JWTHelper::getBearerToken();

if (!$token) {
    http_response_code(401);
    echo json_encode(array("message" => "Access token is required."));
    exit();
}

// Validate token
$decoded = JWTHelper::validateToken($token);

if (!$decoded) {
    http_response_code(401);
    echo json_encode(array("message" => "Invalid or expired token."));
    exit();
}

// Get user ID from token
$user_id = $decoded->user->id;

// Initialize subscription object
$subscription = new Subscription($db);

// Get current subscription
$current_subscription = $subscription->getCurrentSubscription($user_id);

if ($current_subscription) {
    http_response_code(200);
    echo json_encode($current_subscription);
} else {
    // Return default free subscription
    http_response_code(200);
    echo json_encode(array(
        "subscription" => array(
            "id" => null,
            "plan" => "free",
            "status" => "inactive",
            "startDate" => null,
            "endDate" => null,
            "lastPaymentDate" => null,
            "nextPaymentDate" => null,
            "paymentMethod" => null,
            "timeRemaining" => null
        ),
        "plan" => array(
            "id" => 1,
            "name" => "Basic",
            "price" => "0.00",
            "durationDays" => 30,
            "features" => array("Health Tips", "Basic Dashboard", "Limited Support")
        )
    ));
}
?> 