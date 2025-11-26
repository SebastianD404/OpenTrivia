<?php
// load_edit_form.php
// This script fetches a single staff member's details and generates the pre-filled form HTML securely.

// IMPORTANT: Ensure the path to db_connect.php is correct based on your file structure.
// If your db_connect.php contains fatal errors, this line will cause the script to stop.
require_once 'php/db_connect.php'; 

header('Content-Type: text/html');

// --- Input Validation (Ensuring ID is present and numeric) ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="alert alert-danger">Invalid staff ID provided.</div>';
    exit;
}

$staff_id = $_GET['id'];

// --- Database Connection Check (Ensuring $db exists and is connected) ---
if (!isset($db) || $db->connect_error) {
    echo '<div class="alert alert-danger">Database connection failed. Cannot load data. (Check db_connect.php)</div>';
    exit;
}

try {
    // 1. Prepare and execute the secure SELECT query using prepared statements
    $stmt = $db->prepare("SELECT id, first_name, last_name, role, email, phone, date_hired FROM staff WHERE id = ?");
    
    // Check if preparation failed (e.g., table name is wrong)
    if ($stmt === false) {
        throw new Exception("SQL statement preparation failed: " . $db->error);
    }
    
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $staff_details = $result->fetch_assoc();
    $stmt->close();
    
    // Note: If you get the 'Unknown column phone' error again, you must run the ALTER TABLE SQL command.
    if (!$staff_details) {
        echo '<div class="alert alert-danger">Staff member not found.</div>';
        exit;
    }

    // 2. Generate the HTML form with data pre-filled
    $firstName = htmlspecialchars($staff_details['first_name']);
    $lastName = htmlspecialchars($staff_details['last_name']);
    // Note: Database roles are typically lowercase ('admin', 'doctor', 'receptionist', 'nurse')
    $role = htmlspecialchars(strtolower($staff_details['role'])); 
    $email = htmlspecialchars($staff_details['email']);
    // Use null coalescing operator ?? to safely handle missing keys
    $phone = htmlspecialchars($staff_details['phone'] ?? '');
    $dateHired = htmlspecialchars($staff_details['date_hired'] ?? '');
    $staff_id_html = htmlspecialchars($staff_details['id']);

    ?>
    <form id="editStaffForm" method="POST">
        <input type="hidden" name="staff_id" value="<?php echo $staff_id_html; ?>"> 
        
        <div class="mb-3">
            <label for="first_name" class="form-label">First Name</label>
            <input type="text" class="form-control" id="first_name" name="first_name" 
                value="<?php echo $firstName; ?>" required>
        </div>

        <div class="mb-3">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="last_name" name="last_name" 
                value="<?php echo $lastName; ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select class="form-select" id="role" name="role" required>
                <option value="admin" <?php echo ($role == 'admin' ? 'selected' : ''); ?>>Admin</option>
                <option value="doctor" <?php echo ($role == 'doctor' ? 'selected' : ''); ?>>Doctor</option>
                <option value="nurse" <?php echo ($role == 'nurse' ? 'selected' : ''); ?>>Nurse</option>
                <option value="receptionist" <?php echo ($role == 'receptionist' ? 'selected' : ''); ?>>Receptionist</option>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" 
                value="<?php echo $email; ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="phone" name="phone" 
                value="<?php echo $phone; ?>">
        </div>

        <div class="mb-3">
            <label for="date_hired" class="form-label">Date Hired</label>
            <input type="date" class="form-control" id="date_hired" name="date_hired" 
                value="<?php echo $dateHired; ?>">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">New Password (Leave blank to keep current)</label>
            <input type="password" class="form-control" id="password" name="password">
            <div class="form-text">Only enter a new password if you wish to change it.</div>
        </div>

        <div class="text-end pt-3">
            <button type="submit" class="btn btn-success">Save Changes</button>
        </div>
    </form>
    <?php

} catch (Exception $e) {
    // Log error for debugging, but show generic message to user
    error_log("Load Staff Form Error: " . $e->getMessage());
    echo '<div class="alert alert-danger">An unexpected server error occurred while loading the form. (' . htmlspecialchars($e->getMessage()) . ')</div>';
}
?>