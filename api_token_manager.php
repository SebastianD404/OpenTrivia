<?php
header('Content-Type: application/json');
require_once 'db_connect.php';
require_once '../vendor/autoload.php'; // Include the JWT library (e.g., Firebase JWT library)
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
// Secret key for JWT (change this to a long and secure random string)
$secretKey = 'pamzey@7877881825419880518';
$algorithm = "HS256";

// Only allow POST requests
if (($_SERVER['REQUEST_METHOD'] == 'POST')) {
    //http_response_code(405);
    //echo json_encode(['error' => 'Method Not Allowed']);
    //exit;
   $data = ValidateToken();
   echo json_encode(['userId' => $data]);
   exit;
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