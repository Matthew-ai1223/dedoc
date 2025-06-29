<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../models/User.php';
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

// Initialize user object
$user = new User($db);
$user->id = $user_id;

// Get user data
if ($user->readOne()) {
    $user_data = $user->getUserData();
    
    http_response_code(200);
    echo json_encode($user_data);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "User not found."));
}
?> 