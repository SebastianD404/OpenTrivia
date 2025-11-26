<?php
// Ensure this path is correct for your DB connection
include_once("php/db_connect.php"); 
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'No staff ID provided']);
    exit();
}

$staff_id = $db->real_escape_string($_GET['id']);
$result = $db->query("SELECT * FROM staff WHERE id = '$staff_id'");

if ($result && $result->num_rows > 0) {
    $staff_details = $result->fetch_assoc();
    
    // Prepare the data for JSON output
    $response = [
        'success' => true,
        'full_name' => ($staff_details['role'] == 'doctor') ? "Dr. " . htmlspecialchars($staff_details['first_name'] . " " . $staff_details['last_name']) : htmlspecialchars($staff_details['first_name'] . " " . $staff_details['last_name']),
        'role' => htmlspecialchars($staff_details['role']),
        'email' => htmlspecialchars($staff_details['email']),
        'phone' => htmlspecialchars($staff_details['phone'] ?? 'N/A'),
        'date_hired' => htmlspecialchars($staff_details['date_hired'] ?? 'N/A'),
        'status' => $staff_details['is_active'] ? 'Active' : 'Deactivated',
        'id' => $staff_id 
    ];
    echo json_encode($response);
} else {
    echo json_encode(['error' => 'Staff member not found']);
}
?>