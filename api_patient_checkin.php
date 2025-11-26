<?php
header('Content-Type: application/json');
require_once 'db_connect.php';
require_once '../vendor/autoload.php'; // Include the JWT library (e.g., Firebase JWT library)
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
// Secret key for JWT (change this to a long and secure random string)
$secretKey = 'pamzey@7877881825419880518';
$algorithm = "HS256";

// Helper function assumed to be in db_connect.php or functions.php:
// function executeQuery($query, $params, $return_insert_id = false) { ... }

// --- LOGIC FOR APPOINTMENT CHECK-IN ---
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['appointmentId']))) {
    
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing fields: $field"]);
            exit;
        }
    }

    // 1. Fetch Patient/Appointment Data
    $appointmentId = $_POST['appointmentId'];
    $data = getPatientAppointment($appointmentId);

    if (empty($data)) {
        http_response_code(404);
        echo json_encode(['error' => 'Appointment ID not found or is invalid.']);
        exit;
    }
    
    // Get the internal checkin_id from the appointment data
    $checkin_id = $data[0]['id'];

    // 2. --- CHECK FOR DUPLICATE QUEUE ENTRY (WITH FEEDBACK) ---
    // Check if an entry for this checkin_id already exists in the queue today
    $check_duplicate_query = "
        SELECT queue_number
        FROM queue 
        WHERE checkin_id = ? 
        AND DATE(created_at) = CURDATE() 
        LIMIT 1";

    global $db;
    $stmt_check = $db->prepare($check_duplicate_query);
    $stmt_check->bind_param("i", $checkin_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Duplicate check-in found
        $existing_queue = $result_check->fetch_assoc();
        $queue_number = $existing_queue['queue_number'];

        // Send a detailed error response with the existing queue number
        http_response_code(409); // 409 Conflict is appropriate for duplicate resource creation
        echo json_encode([
            'success' => false, 
            'message' => "You have already checked in for this appointment today.",
            'quenumber' => $queue_number // Send the existing queue number
        ]);
        exit;
    }
    // --- END DUPLICATE CHECK ---

    // 3. Generate Queue Number
    $sqlarb = mysqli_query($db, "SELECT * FROM queue");
    $lastid = mysqli_num_rows($sqlarb);
    // Determine queue number based on the type of ID (assuming APT prefix means Appointment)
    $queue_number = (strpos($appointmentId, "APT") !== false) ? 'APPT-'.($lastid + 1) : 'WALK-'.($lastid + 1);

    // 4. Prepare and execute insert into the queue
    $query = "INSERT INTO queue (checkin_id,queue_number) VALUES (?, ?)";
    $params = [
        $checkin_id,
        $queue_number
    ];

    $insertId = executeQuery($query, $params, true);

    if ($insertId) {
        // Success
        echo json_encode(['success' => true, 'quenumber' => $queue_number]);
        exit;
    } else {
        // Failed queue insertion
        http_response_code(500);
        echo json_encode(['error' => 'Failed to insert into queue']);
        exit;
    }
}

// --- LOGIC FOR WALK-IN CHECK-IN ---
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['type']) == 'walk-in')) {

    // Validate required fields
    $required = ['patientId','appointmentType','additionalinfo','termsAgreement'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing field: $field"]);
            exit;
        }
    }

    $idnumber = getCheckinCount() + 1;
    $appReferenceNumber = 'WALK' . date("Ymd", strtotime(date('Y-m-d'))) . $idnumber;

    $query = "INSERT INTO checkins (patient_id, app_type_id,reason, consent, reference_number,type_id) VALUES (?, ?, ?, ?,?,?)";
    $params = [ 
        intval($_POST['patientId']),
        intval($_POST['appointmentType']),
        $_POST['additionalinfo'],
        $_POST['termsAgreement'] == "on" ? 1 : 0,
        $appReferenceNumber,
        2
    ];

    $insertId = executeQuery($query, $params, true);
    
    // --- NOTE on WALK-IN DUPLICATION: ---
    // Walk-in duplication is implicitly handled by the user flow. 
    // If the patient is registered here, they receive a *new* reference_number,
    // so pressing back and re-submitting will generate a new valid check-in
    // and queue number. If you wanted to prevent a patient from having 
    // multiple walk-ins on the same day, you would need another check here.

    if($insertId){
        // Fetch queue count for the new queue number
        global $db;
        $sqlarb = mysqli_query($db, "SELECT * FROM queue");
        $lastid = mysqli_num_rows($sqlarb);
        $queue_number = 'WALK-'.($lastid + 1);

        // Prepare and execute insert into the queue
        $query = "INSERT INTO queue (checkin_id,queue_number) VALUES (?, ?)";
        $params = [
            $insertId, // This is the new checkin_id from the previous insert
            $queue_number
        ];

        $queueInsertId = executeQuery($query, $params, true);

        if ($queueInsertId) {
            // Success
            echo json_encode(['success' => true, 'quenumber' => $queue_number]);
            exit;
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to queue patient']);
            exit;
        }
        
    }
    else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to register patient (Checkins insertion failed)']);
        exit;
    }

}
// --- LOGIC FOR GET APPOINTMENT DETAILS (likely used for initial lookup) ---
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
    $appointmentId = $_GET['appointmentID'] ?? null;
    if ($appointmentId) {
        // Fetch the saved record
        $data = getPatientAppointment($appointmentId);
        
        if (!empty($data)) {
             echo json_encode(['success' => true, 'appointment' => $data]);
             exit;
        } else {
             http_response_code(404);
             echo json_encode(['error' => 'Appointment ID not found.']);
             exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing appointmentID parameter']);
        exit;
    }

}
// --- LOGIC FOR STAFF CALLING A PATIENT (JSON POST) ---
elseif (($_SERVER['REQUEST_METHOD'] === 'POST') && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    $required = ['doctorId', 'roomNumber', 'queueId'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing field: $field"]);
            exit;
        }
    }

    $query = "UPDATE queue SET called_by = ?, room_id = ?, status = ?, updated_at =?, called_at=? WHERE id = ?";
    $params = [
        intval($input['doctorId']),
        intval($input['roomNumber']),
        'in_progress',
        date("Y-m-d H:i:s"),
        date("Y-m-d H:i:s"),
        intval($input['queueId'])
    ];

    $updateResult = executeQuery($query, $params);

    if ($updateResult) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update queue',"data" => $updateResult]);
        exit;
    }
}
// --- CATCH ALL FOR UNHANDLED METHODS ---
else {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// --- HELPER FUNCTIONS ---

function getPatientAppointment($appid){
    global $db;
    $patientrecod = [];
    // NOTE: It is critical to use prepared statements to prevent SQL Injection, especially for GET requests.
    $stmt = $db->prepare("SELECT a.*, s.first_name AS fname,s.last_name AS lname,
                             p.first_name,p.last_name,t.name AS appoint_name
                             FROM checkins a 
                             INNER JOIN patients p ON p.id = a.patient_id
                             LEFT JOIN staff s ON s.id = a.doctor_id 
                             LEFT JOIN appointment_types t ON t.id = a.app_type_id 
                             WHERE a.reference_number = ?");
    $stmt->bind_param("s", $appid);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = mysqli_fetch_assoc($res)) {

             $patientrecod [] = [ 'fullname' => $row['first_name']." ".$row['last_name'],
                                  "appointment_date" => $row['appointment_date'],
                                  "doctor" => $row['fname']." ".$row['lname'],
                                  "appointment" => $row['appoint_name'],
                                  "time" => $row['scheduled_time'],
                                  "reason" => $row['reason'],
                                  "appnumber" => $row['reference_number'],
                                  "id" => $row['checkin_id']
                                 ];
        
    }

   return $patientrecod;
}

function getCheckinCount(){
    global $db;
    $sqlarb = mysqli_query($db, "SELECT MAX(checkin_id) AS last_id FROM checkins");
    $row = mysqli_fetch_assoc($sqlarb);
    $lastid = $row['last_id'];
    return $lastid;
}

function verifyJWT($token,$algorithm) {
    global $secretKey;
    try {
          $decoded = JWT::decode($token, new Key($secretKey,$algorithm));
          return $decoded->user_id;
    } catch (Exception $e) {
        return null; // Token is invalid or expired
    }
}


function ValidateToken(){

    global $algorithm;
    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;

    if (!$authHeader) {

        http_response_code(401);
        echo json_encode(['message' => 'Invalid Authorization header']);
        exit;                     
    }

        $token = str_replace('Peer','', $authHeader);
        $user_id = verifyJWT($token,$algorithm);
        if ($user_id == null) {

            http_response_code(401); // Unauthorized
            echo json_encode(['message' => 'Unauthorized']);
            exit;
        }  

    return $user_id;

}
?>