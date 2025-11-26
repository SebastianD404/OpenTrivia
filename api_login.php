<?php
// php/api_login.php
session_set_cookie_params(0, '/');
session_start();

header('Content-Type: application/json');
require_once 'db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Please enter both email and password.']);
    exit;
}

try {
    // UPDATED: Selecting 'id' and 'password' to match your DB screenshot
    $sql = "SELECT id, email, password, first_name, role FROM staff WHERE email = ?";
    
    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $db->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // UPDATED: accessing $user['password'] instead of password_hash
    if ($user && password_verify($password, $user['password'])) {
        
        // Login Successful
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['id']; // UPDATED: using 'id'
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['user_role'] = $user['role']; 
        
        session_regenerate_id(true); 
        
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful',
            'logged_in' => true,
            'data' => [
                'first_name' => $user['first_name'],
                'role' => $user['role']
            ]
        ]);
        
    } else {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
    }
    
    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}

$db->close();
exit;
?>