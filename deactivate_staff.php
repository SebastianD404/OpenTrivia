<?php
// File: deactivate_staff.php
// Prevent any stray text/whitespace from breaking the JSON response
ob_start(); 
session_start();
include_once("php/db_connect.php");

// Set header strictly to JSON
header('Content-Type: application/json');

// Clean the output buffer to remove any previous PHP warnings/spaces
ob_clean(); 

$response = array();

// check connection
if (!isset($db) || $db->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit();
}

$staff_id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if (!$staff_id || !in_array($action, ['activate', 'deactivate'])) {
    echo json_encode(["success" => false, "message" => "Invalid request parameters."]);
    exit();
}

$new_status = ($action === 'activate') ? 1 : 0;
$action_word = ($new_status === 1) ? "Activated" : "Deactivated";

$stmt = $db->prepare("UPDATE staff SET is_active = ? WHERE id = ?");

if ($stmt) {
    $stmt->bind_param("ii", $new_status, $staff_id);
    
    if ($stmt->execute()) {
        $response["success"] = true;
        $response["message"] = "Staff member successfully {$action_word}.";
    } else {
        $response["success"] = false;
        $response["message"] = "Database update failed: " . $stmt->error;
    }
    $stmt->close();
} else {
    $response["success"] = false;
    $response["message"] = "Query preparation failed.";
}

$db->close();

// Send the final clean JSON response
echo json_encode($response);
exit();
?>