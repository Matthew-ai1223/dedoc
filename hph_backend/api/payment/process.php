<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../models/Subscription.php';
require_once '../../models/Payment.php';
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

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Validate required fields
if (empty($data->planId) || empty($data->paymentMethod) || empty($data->amount)) {
    http_response_code(400);
    echo json_encode(array("message" => "Plan ID, payment method, and amount are required."));
    exit();
}

// Initialize subscription object
$subscription = new Subscription($db);

// Get plan details
$plan = $subscription->getPlanById($data->planId);

if (!$plan) {
    http_response_code(404);
    echo json_encode(array("message" => "Subscription plan not found."));
    exit();
}

// Validate amount
if ($data->amount != $plan['price']) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid payment amount."));
    exit();
}

// Start transaction
$db->beginTransaction();

try {
    // Create subscription
    $subscription->user_id = $user_id;
    $subscription->plan_id = $data->planId;
    $subscription->status = 'active';
    $subscription->start_date = date('Y-m-d H:i:s');
    $subscription->end_date = date('Y-m-d H:i:s', strtotime('+' . $plan['duration_days'] . ' days'));
    $subscription->payment_method = $data->paymentMethod;
    
    $subscription_id = $subscription->create();
    
    if (!$subscription_id) {
        throw new Exception("Failed to create subscription");
    }
    
    // Create payment record
    $payment = new Payment($db);
    $payment->user_id = $user_id;
    $payment->subscription_id = $subscription_id;
    $payment->amount = $data->amount;
    $payment->currency = 'NGN';
    $payment->payment_method = $data->paymentMethod;
    $payment->transaction_id = 'TXN_' . time() . '_' . $user_id;
    $payment->status = 'completed';
    $payment->payment_data = json_encode($data);
    
    if (!$payment->create()) {
        throw new Exception("Failed to create payment record");
    }
    
    // Update subscription with payment dates
    $subscription->updatePaymentDates($subscription_id, date('Y-m-d H:i:s'), $subscription->end_date);
    
    // Commit transaction
    $db->commit();
    
    // Get updated subscription data
    $current_subscription = $subscription->getCurrentSubscription($user_id);
    
    http_response_code(200);
    echo json_encode(array(
        "message" => "Payment processed successfully.",
        "subscription" => $current_subscription,
        "transactionId" => $payment->transaction_id
    ));
    
} catch (Exception $e) {
    // Rollback transaction
    $db->rollback();
    
    http_response_code(500);
    echo json_encode(array("message" => "Payment processing failed: " . $e->getMessage()));
}
?> 