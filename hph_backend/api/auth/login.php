<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../models/User.php';
require_once '../../utils/jwt_helper.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize user object
$user = new User($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Validate required fields
if (empty($data->username) || empty($data->password)) {
    http_response_code(400);
    echo json_encode(array("message" => "Username and password are required."));
    exit();
}

// Set username to check
$user->username = $data->username;

// Check if username exists and password is correct
if ($user->usernameExists() && password_verify($data->password, $user->password_hash)) {
    // Get user data
    $user->readOne();
    
    // Check if user is active
    if (!$user->is_active) {
        http_response_code(401);
        echo json_encode(array("message" => "Account is deactivated. Please contact support."));
        exit();
    }
    
    // Generate JWT token
    $user_data = $user->getUserData();
    $token = JWTHelper::generateToken($user_data);
    
    // Return success response
    http_response_code(200);
    echo json_encode(array(
        "message" => "Login successful.",
        "token" => $token,
        "user" => $user_data
    ));
} else {
    http_response_code(401);
    echo json_encode(array("message" => "Invalid username or password."));
}
?> 