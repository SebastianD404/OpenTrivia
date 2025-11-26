<?php 
session_start(); 

// 2. If the user is already logged in, redirect them away from the public page
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: dashboard.php");
    exit(); // Always use exit() after header()
}
include_once("php/db_connect.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PQMS - Patient Queue Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="landing-page">
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-md-6">
                <div class="text-center text-md-start">
                    <h1 class="display-4 fw-bold mb-3">SEB Hospital QMS</h1>
                    <p class="lead mb-4">Streamline patient flow, reduce wait times, and improve clinic efficiency with our digital queue management solution.</p>
                    <div class="d-flex gap-3 justify-content-center justify-content-md-start">
                        <a href="visit.php" class="btn btn-primary btn-lg px-4">Patient Check-In</a>
                        <a href="login.php" class="btn btn-outline-primary btn-lg px-4">Staff Login</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 d-none d-md-block">
                <img src="images/profile.png" alt="Patients waiting in clinic queue with mobile phones" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>