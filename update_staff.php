<?php
// update_staff.php
// This script handles the AJAX submission to update staff details in the database securely.

// Set content type to JSON
header('Content-Type: application/json');

// Include database connection (path must be correct relative to this file's location in 'php/')
// Use require_once to stop script if the file is missing
require_once 'db_connect.php'; 

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// 1. Validate mandatory ID (Ensures we know who to update)
$staff_id = $_POST['staff_id'] ?? null;
if (!is_numeric($staff_id)) {
    $response['message'] = 'Error: Staff ID is missing or invalid. Update failed.';
    echo json_encode($response);
    exit;
}

// 2. Collect and validate data
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$role = trim($_POST['role'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$dateHired = trim($_POST['date_hired'] ?? '');
$newPassword = $_POST['password'] ?? ''; 

// --- FIX: Added 'nurse' to the list of valid roles ---
$valid_roles = ['admin', 'doctor', 'receptionist', 'nurse'];
// --------------------------------------------------------

if (empty($firstName) || empty($lastName) || empty($email)) {
    $response['message'] = 'Please fill in all required fields (Name, Email).';
    echo json_encode($response);
    exit;
}

if (!in_array($role, $valid_roles)) {
    // This error message will no longer appear when 'nurse' is selected
    $response['message'] = 'Invalid role selected.';
    echo json_encode($response);
    exit;
}

// 3. Construct the query using Prepared Statements
if (!empty($newPassword)) {
    // Include password update
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $sql = "UPDATE staff SET 
                first_name = ?, 
                last_name = ?, 
                role = ?, 
                email = ?, 
                phone = ?, 
                date_hired = ?, 
                password = ? 
            WHERE id = ?";
    
    $stmt = $db->prepare($sql);
    // 's' for string, 'i' for integer. All parameters are strings
    $stmt->bind_param(
        "ssssssss", 
        $firstName, 
        $lastName, 
        $role, 
        $email, 
        $phone, 
        $dateHired, 
        $hashedPassword,
        $staff_id
    );
} else {
    // Exclude password update
    $sql = "UPDATE staff SET 
                first_name = ?, 
                last_name = ?, 
                role = ?, 
                email = ?, 
                phone = ?, 
                date_hired = ? 
            WHERE id = ?";
            
    $stmt = $db->prepare($sql);
    // sssssss = 7 strings
    $stmt->bind_param(
        "sssssss", 
        $firstName, 
        $lastName, 
        $role, 
        $email, 
        $phone,  
        $dateHired,
        $staff_id
    );
}

// 4. Execute the update
if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Staff details updated successfully!';
} else {
    // Execution failed - provide the specific database error
    $response['message'] = 'Database update failed: ' . $stmt->error;
}

$stmt->close();
$db->close();

echo json_encode($response);
exit;
?>