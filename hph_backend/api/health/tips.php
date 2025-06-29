<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get health tips
$query = "SELECT * FROM health_tips WHERE is_active = 1 ORDER BY RAND() LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();

$tips = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($tips) {
    http_response_code(200);
    echo json_encode(array(
        "message" => "Health tips retrieved successfully.",
        "tips" => $tips
    ));
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No health tips found."));
}
?> 