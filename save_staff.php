<?php
// File: php/save_staff.php
ob_start(); 
session_start();
include_once("db_connect.php"); // Assuming db_connect.php is in the same folder

header('Content-Type: application/json');
ob_clean(); // Clean buffer for clean JSON output

$response = array("success" => false, "message" => "An unknown error occurred.");

// Security checks
if ($_SESSION['user_role'] !== 'admin' || !isset($db)) {
    $response["message"] = "Authorization or database error.";
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize form data
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    $phone = $_POST['phone'] ?? null;
    $date_hired = $_POST['date_hired'] ?? date('Y-m-d');
    $password = $_POST['password'] ?? '';
    $is_active = 1;

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($role) || empty($password)) {
        $response["message"] = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["message"] = "Invalid email address.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $db->prepare("INSERT INTO staff (first_name, last_name, email, password, role, phone, date_hired, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        if ($stmt) {
            $stmt->bind_param("sssssssi", $first_name, $last_name, $email, $hashed_password, $role, $phone, $date_hired, $is_active);

            if ($stmt->execute()) {
                $response["success"] = true;
                $response["message"] = "New staff member **{$first_name}** added successfully!";
            } else {
                // Check for duplicate entry error (e.g., email unique constraint)
                if ($db->errno == 1062) {
                     $response["message"] = "Error: Email address already exists.";
                } else {
                    $response["message"] = "Database error: " . $stmt->error;
                }
            }
            $stmt->close();
        } else {
            $response["message"] = "Database query preparation failed.";
        }
    }
} else {
    $response["message"] = "Invalid request method.";
}

$db->close();
echo json_encode($response);
exit();
?>