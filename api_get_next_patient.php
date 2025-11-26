<?php
// php/api_get_next_patient.php - Fetches the single next scheduled patient not yet in queue

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 

require_once 'db_connect.php'; 

$response = [
    'status' => 'error',
    'message' => 'An unknown error occurred.',
    'data' => null
];

if (!isset($db) || $db->connect_error) {
    $response['message'] = 'Database connection failed.';
    echo json_encode($response);
    exit;
}

try {
    $today = date('Y-m-d');
    
    // SQL uses a LEFT JOIN to find the first scheduled appointment (checkins) that is NOT in the queue table.
    $sql = "
        SELECT 
            a.checkin_id AS id, 
            a.patient_id,
            a.scheduled_time, 
            CONCAT(p.first_name, ' ', p.last_name) AS full_name,
            -- Create the display queue number based on the checkin ID
            CONCAT('APPT-', a.checkin_id) AS queue_number
        FROM
            checkins a
        JOIN
            patients p ON a.patient_id = p.id
        LEFT JOIN
            queue q ON a.checkin_id = q.checkin_id
        WHERE
            a.appointment_date = ? 
            -- CRITICAL: Only select records that are NOT present in the 'queue' table
            AND q.checkin_id IS NULL
        ORDER BY 
            a.scheduled_time ASC
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);
    // Bind the today's date parameter
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $patient = $result->fetch_assoc();
        
        // Format the time for display
        $patient['scheduled_time'] = date('h:i A', strtotime($patient['scheduled_time']));

        $response['status'] = 'success';
        $response['data'] = $patient;
        $response['message'] = 'Next patient fetched successfully.';

    } else {
        $response['status'] = 'success'; // Treat 'no patients' as a successful query result
        $response['message'] = 'No patients waiting.';
        $response['data'] = null;
    }

} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage(); 
}

echo json_encode($response);
exit;
?>