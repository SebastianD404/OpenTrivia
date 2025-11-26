<?php
/**
 * Global Security Check - Used for initial page load of all protected pages.
 * 1. Checks for an active PHP session.
 * 2. Prevents the browser from caching the page (solves the "back button" security issue).
 */

--- CRITICAL: CACHING PREVENTION HEADERS ---
These headers tell the browser NOT to store the page in its history or cache.
This forces the browser to check with the server every time a user navigates, 
ensuring the session check below runs even after a logout.
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
----------------------------------------------

CRITICAL STEP 1: Ensure the session is started to access $_SESSION variables.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define the path to the login page
// We assume 'dashboard.php' is your login page in the root directory (../)
$login_page_path = 'dashboard.php';

CRITICAL STEP 2: Check for valid authentication credentials in the session.
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    
    // Check which page the user is currently trying to access
    $current_page = basename($_SERVER['PHP_SELF']);
    
    // If the user is on any page OTHER than the login page, redirect them.
    if ($current_page !== 'dashboard.php' && $current_page !== 'login.php') {
        
        // Use the relative path '../' because the login page is in the root, 
        // but this security file is inside the 'php' folder.
        header("Location: ../" . $login_page_path); 
        exit(); 
    }
}
// If the user is logged in, the script continues and displays the requested page content.
?>