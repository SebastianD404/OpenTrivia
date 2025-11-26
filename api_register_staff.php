<?php
// php/api_register_staff.php

header('Content-Type: application/json');

// This file creates the $db variable
require_once 'db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS);
$lastName  = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_SPECIAL_CHARS);
$email     = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password  = $_POST['password'] ?? '';
$role      = $_POST['role'] ?? '';

$errors = [];
if (empty($firstName)) $errors[] = "First name is required.";
if (empty($lastName))  $errors[] = "Last name is required.";
if (empty($email))     $errors[] = "Email is required.";
if (empty($password) || strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
if (empty($role))      $errors[] = "Role is required.";

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    exit;
}

try {
    // UPDATED: Using $db instead of $conn
    $checkStmt = $db->prepare("SELECT id FROM staff WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'This email is already registered.']);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // UPDATED: Using $db instead of $conn
    $sql = "INSERT INTO staff (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    
    $stmt->bind_param("sssss", $firstName, $lastName, $email, $passwordHash, $role);

    if ($stmt->execute()) {
        http_response_code(201); 
        echo json_encode([
            'status' => 'success', 
            'message' => 'Registration successful! You can now login.'
        ]);
    } else {
        throw new Exception("Database insert failed: " . $stmt->error);
    }

    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}

// UPDATED: Using $db instead of $conn
$db->close();
exit;
?>