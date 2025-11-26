<?php
// php/api_update_status.php

// Start session to access staff ID
session_start();

require_once 'db_connect.php'; // Ensures $db is available
header('Content-Type: application/json');

// Get the current staff ID from the session (Crucial for Start Triage/Consult)
// IMPORTANT: Adjust 'staff_id' key if your session uses a different name.
$current_staff_id = $_SESSION['staff_id'] ?? null; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Get Inputs
    $queue_id = $_POST['queue_id'] ?? null;
    $new_status = $_POST['status'] ?? null;
    
    // Values that might be sent by the Receptionist's "Call Patient" action
    $priority_from_post = $_POST['priority'] ?? null; 
    $room_id_from_post = $_POST['roomNumber'] ?? null; 

    // Reset Assigned Staff ID for this script based on the action
    $assigned_staff_id = null;
    $called_by_staff_id = null;

    if ($queue_id && $new_status) {
        
        // 2. Build Query & Dynamic Parameters
        $query = "UPDATE queue_list SET status = ?, updated_at = NOW()"; 
        $types = "s";
        $params = [$new_status];
        
        // Logic based on the new status:

        if ($new_status === 'in_progress' && $current_staff_id) {
            // ACTION: Start Triage or Start Consult
            // The person clicking the button becomes the assigned staff.
            $query .= ", assigned_staff_id = ?";
            $types .= "i";
            $params[] = $current_staff_id;
        }

        if ($new_status === 'ready_for_doctor') {
            // ACTION: Receptionist "Call Patient" - Assigns Doctor and Room.
            // Note: The JS calls a different endpoint (api_patient_checkin.php) for this, 
            // but including the logic here for completeness/re-routing.
            $called_by_staff_id = $_POST['doctorId'] ?? null; // Receptionist sets this as the doctor ID
            
            if ($called_by_staff_id) {
                 $query .= ", assigned_staff_id = ?"; // Assigns the doctor to the patient
                 $types .= "i";
                 $params[] = $called_by_staff_id; 
            }
            if ($room_id_from_post) {
                 $query .= ", room_number = ?";
                 $types .= "s"; // Assuming room_number is a string (e.g., "A-101")
                 $params[] = $room_id_from_post;
            }
            // Add the person who called them into a different field (optional but helpful)
            // If you have a 'called_by_staff_id' field:
            // $query .= ", called_by_staff_id = ?"; $types .= "i"; $params[] = $current_staff_id;

        }
        
        // If priority is set on POST (e.g., from Triage/Receptionist forms), update it.
        if ($priority_from_post) {
            $query .= ", priority = ?";
            $types .= "s";
            $params[] = $priority_from_post;
        }

        // --- Finalize Query ---
        $query .= " WHERE id = ?";
        $types .= "i";
        $params[] = $queue_id;

        // 3. Execute
        if ($stmt = $db->prepare($query)) {
            // Use splat operator (...) for dynamic parameter binding
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Status updated']);
            } else {
                error_log("SQL Error: " . $stmt->error);
                echo json_encode(['status' => 'error', 'message' => 'Execute failed.']);
            }
            $stmt->close();
        } else {
            error_log("Prepare Error: " . $db->error);
            echo json_encode(['status' => 'error', 'message' => 'Prepare failed.']);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing Queue ID or Status']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
}
$db->close();
?>