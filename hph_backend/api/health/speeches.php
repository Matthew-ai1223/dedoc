<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get doctor speeches
$query = "SELECT * FROM doctor_speeches WHERE is_active = 1 ORDER BY RAND() LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();

$speeches = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($speeches) {
    http_response_code(200);
    echo json_encode(array(
        "message" => "Doctor speeches retrieved successfully.",
        "speeches" => $speeches
    ));
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No doctor speeches found."));
}
?> 