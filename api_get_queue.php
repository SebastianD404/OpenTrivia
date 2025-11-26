<?php
// php/api_get_queue.php - Fetches today's active queue and scheduled patients

// CRITICAL FIX 1: Suppress errors to prevent PHP warnings/notices from corrupting JSON output.
error_reporting(0);

// --- CRITICAL FIX 2: Session Locking Prevention ---
// If you are using session_start() for authentication, you must release the lock
// immediately to prevent simultaneous AJAX calls from hanging (Pending state).

// If you started a session in a preceding file (like db_connect.php or an auth handler), 
// ensure you close it here as soon as you are done reading session data.
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}
// --- END Session Locking Prevention ---


// 1. Set headers to ensure the response is treated as JSON
header('Content-Type: application/json');

// 2. Include database connection file
require_once 'db_connect.php'; 

// 3. Initialize response structure
$response = [
    'success' => false,
    'data' => [],
    'message' => 'An unknown error occurred.'
];

// Check for an active database connection, using the $db variable
if (!isset($db) || $db->connect_error) {
    $response['message'] = 'Database connection failed: ' . ($db->connect_error ?? 'Connection variable not set.');
    echo json_encode($response);
    exit;
}

try {
    // Get today's date in 'YYYY-MM-DD' format for the database query
    $today = date('Y-m-d');
    
    // --- CRITICAL: We pull ALL active patients (queue_list) AND scheduled patients (checkins) ---
    $sql = "
        -- SECTION 1: Patients who have CHECKED IN (from queue_list)
        SELECT 
            ql.id AS queue_id,           
            ql.token_number AS token_number,          
            ql.patient_id,
            -- Map the status to universal dashboard statuses
            CASE ql.status
                WHEN 'consulting' THEN 'in_progress'
                WHEN 'done' THEN 'completed'
                ELSE 'waiting' -- Assumes any other status (like 'waiting' or 'missed') is waiting 
            END AS status,
            ql.type AS type, -- Use the type from queue_list (walk-in or appt)
            ql.priority,
            ql.check_in_time AS entry_time,
            DATE_FORMAT(ql.check_in_time, '%h:%i %p') AS time_display, 
            ql.room_number AS room_id,
            ql.assigned_staff_id,
            p.first_name, 
            p.last_name,
            p.reason_for_visit AS reason, 
            s.first_name AS staff_first_name,
            s.last_name AS staff_last_name
        FROM 
            queue_list ql
        JOIN 
            patients p ON ql.patient_id = p.id
        LEFT JOIN
            staff s ON ql.assigned_staff_id = s.id 
        WHERE 
            DATE(ql.check_in_time) = ? 
            -- Only fetch active and completed today
            AND (ql.status IN ('waiting', 'consulting') OR (ql.status = 'done'))

        UNION ALL
        
        -- --- SECTION 2: Patients who are SCHEDULED but NOT YET CHECKED IN (from checkins table)
        SELECT
            a.checkin_id AS queue_id,   
            a.checkin_id AS token_number,          
            a.patient_id,
            'scheduled' AS status, -- Use 'scheduled' for un-checked appointments
            'appt' AS type, -- Explicitly mark as Appointment
            'normal' AS priority,
            a.scheduled_time AS entry_time,
            DATE_FORMAT(a.scheduled_time, '%h:%i %p') AS time_display,
            NULL AS room_id,
            NULL AS assigned_staff_id,
            p.first_name,
            p.last_name,
            a.reason AS reason, 
            NULL AS staff_first_name,
            NULL AS staff_last_name
        FROM
            checkins a
        JOIN
            patients p ON a.patient_id = p.id
        WHERE
            a.appointment_date = ? 
            -- Exclusion line: Only show scheduled appointments that have NOT been checked in today
            AND a.checkin_id NOT IN (SELECT checkin_id FROM queue_list WHERE DATE(check_in_time) = ?)
            
        ORDER BY 
            entry_time ASC
    ";

    // Prepare statement using the global $db connection
    $stmt = $db->prepare($sql);
    
    // Bind three parameters (all are $today)
    $stmt->bind_param("sss", $today, $today, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $queue_data = [];
    while ($row = $result->fetch_assoc()) {
        // Build the full name fields
        $row['name'] = trim($row['first_name'] . ' ' . $row['last_name']);
        $row['assigned_staff_name'] = trim(($row['staff_first_name'] ?? '') . ' ' . ($row['staff_last_name'] ?? ''));
        
        // Determine the display token format
        if ($row['status'] === 'scheduled') {
            $row['display_token'] = 'APPT-' . $row['queue_id']; // Use queue_id (which is checkin_id)
            $row['status'] = 'waiting'; // Map 'scheduled' to 'waiting' for display purposes
        } else {
            // For checked-in patients (walk-in or appt)
            $type_prefix = (strtoupper($row['type']) === 'APPT') ? 'APPT' : 'WALK';
            $row['display_token'] = $type_prefix . '-' . $row['token_number'];
        }

        // Standardize field names for the frontend
        $row['queue_number'] = $row['token_number']; 
        $row['created_at'] = $row['entry_time']; 
        $row['patient_full_name'] = $row['name']; 
        $row['staff_full_name'] = $row['assigned_staff_name']; 

        // Remove redundant/old database fields
        unset(
            $row['first_name'], $row['last_name'], $row['staff_first_name'], 
            $row['staff_last_name'], $row['entry_time'], $row['name'], 
            $row['assigned_staff_name'], $row['token_number']
        );
        
        $queue_data[] = $row;
    }

    $response['success'] = true;
    $response['data'] = $queue_data; 
    $response['message'] = 'Queue data fetched successfully.';

    } catch (Exception $e) {
        // This catch block handles SQL errors or other runtime issues
        $response['message'] = 'Database error: ' . $e->getMessage(); 
    }


// 4. Output the final JSON response
echo json_encode($response);
exit;