<?php
// logout.php

// 1. Must start the session to access and destroy session data
session_start();

// 2. Unset all session variables (clears the $_SESSION array)
$_SESSION = array();

// 3. Destroy the session (deletes the session file/data on the server)
session_destroy();

// 4. Clear the session cookie on the client side
// This is important to ensure the browser forgets the old session ID
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 5. Redirect the user back to the login page
header("Location: login.php");
exit();
?>