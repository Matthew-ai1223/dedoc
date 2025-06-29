<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure no output before headers
ob_start();

// Allow specific origins
header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Log request details
$logMessage = date('Y-m-d H:i:s') . "\n";
$logMessage .= "Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
$logMessage .= "Request Headers: " . print_r(getallheaders(), true) . "\n";
$logMessage .= "Raw Input: " . file_get_contents("php://input") . "\n";
file_put_contents('../../debug.log', $logMessage, FILE_APPEND);

try {
    require_once '../../config/database.php';
    require_once '../../models/User.php';
    require_once '../../utils/jwt_helper.php';

    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method is allowed');
    }

    // Get database connection
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Database connection failed");
    }

    // Initialize user object
    $user = new User($db);

    // Get posted data
    $rawData = file_get_contents("php://input");
    if (empty($rawData)) {
        throw new Exception("No data received");
    }

    file_put_contents('../../debug.log', date('Y-m-d H:i:s') . " - Raw input: " . $rawData . "\n", FILE_APPEND);
    
    $data = json_decode($rawData);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON input: " . json_last_error_msg());
    }

    // Log received data
    file_put_contents('../../debug.log', date('Y-m-d H:i:s') . " - Parsed data: " . print_r($data, true) . "\n", FILE_APPEND);

    // Validate required fields
    if (
        empty($data->fullName) ||
        empty($data->username) ||
        empty($data->email) ||
        empty($data->phoneNumber) ||
        empty($data->dateOfBirth) ||
        empty($data->state) ||
        empty($data->city) ||
        empty($data->password) ||
        empty($data->confirmPassword)
    ) {
        throw new Exception("All fields are required.");
    }

    // Validate password match
    if ($data->password !== $data->confirmPassword) {
        throw new Exception("Passwords do not match.");
    }

    // Validate password strength
    if (strlen($data->password) < 8) {
        throw new Exception("Password must be at least 8 characters long.");
    }

    // Validate email format
    if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format.");
    }

    // Validate age (must be 18 or older)
    $birthDate = new DateTime($data->dateOfBirth);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;

    if ($age < 18) {
        throw new Exception("You must be at least 18 years old to register.");
    }

    // Validate phone number (10 digits)
    if (!preg_match('/^\d{10}$/', $data->phoneNumber)) {
        throw new Exception("Please enter a valid 10-digit phone number.");
    }

    // Validate username length
    if (strlen($data->username) < 4) {
        throw new Exception("Username must be at least 4 characters long.");
    }

    // Check if username already exists
    $user->username = $data->username;
    if ($user->usernameExists()) {
        throw new Exception("Username already exists.");
    }

    // Check if email already exists
    $user->email = $data->email;
    if ($user->emailExists()) {
        throw new Exception("Email already exists.");
    }

    // Set user properties
    $user->full_name = $data->fullName;
    $user->username = $data->username;
    $user->email = $data->email;
    $user->phone_number = $data->phoneNumber;
    $user->date_of_birth = $data->dateOfBirth;
    $user->state = $data->state;
    $user->city = $data->city;
    $user->password_hash = password_hash($data->password, PASSWORD_DEFAULT);

    // Create the user
    if ($user->create()) {
        // Get the created user data
        $user->id = $db->lastInsertId();
        $user->readOne();
        
        // Generate JWT token
        $user_data = $user->getUserData();
        $token = JWTHelper::generateToken($user_data);
        
        // Return success response
        echo json_encode(array(
            "status" => "success",
            "message" => "User registered successfully.",
            "token" => $token,
            "user" => $user_data
        ));
    } else {
        throw new Exception("Unable to register user.");
    }

} catch (Exception $e) {
    // Log any errors
    file_put_contents('../../debug.log', date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", FILE_APPEND);
    
    // Clear any output
    ob_clean();
    
    // Send error response
    http_response_code(500);
    echo json_encode(array(
        "status" => "error",
        "message" => $e->getMessage()
    ));
}

// Flush output buffer
ob_end_flush();
?> 