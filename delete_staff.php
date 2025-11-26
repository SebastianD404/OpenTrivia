<?php
// File: php/delete_staff.php
ob_start(); 
session_start();
include_once("db_connect.php"); 

header('Content-Type: application/json');
ob_clean(); 

$response = array("success" => false, "message" => "An unknown error occurred.");

if ($_SESSION['user_role'] !== 'admin' || !isset($db)) {
    $response["message"] = "Authorization or database error.";
    echo json_encode($response);
    exit();
}

$staff_id = $_GET['id'] ?? null;

if (!$staff_id || !is_numeric($staff_id)) {
    $response["message"] = "Invalid staff ID.";
    echo json_encode($response);
    exit();
}

// Ensure the staff member is not trying to delete themselves (optional, but good practice)
if ($staff_id == $_SESSION['user_id']) {
    $response["message"] = "You cannot delete your own active account.";
    echo json_encode($response);
    exit();
}

// Use DELETE query
$stmt = $db->prepare("DELETE FROM staff WHERE id = ?");

if ($stmt) {
    $stmt->bind_param("i", $staff_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response["success"] = true;
            $response["message"] = "Staff member successfully deleted.";
        } else {
            $response["message"] = "No staff member found with that ID.";
        }
    } else {
        $response["message"] = "Database deletion failed: " . $stmt->error;
    }
    $stmt->close();
} else {
    $response["message"] = "Query preparation failed.";
}

$db->close();
echo json_encode($response);
exit();
?>