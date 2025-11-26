<?php 
include_once("php/db_connect.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PQMS - Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">PQMS</a>
            <div class="navbar-text text-white">
             Welcome! Patient
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-question-circle display-4 text-primary mb-3"></i>
                            <h3 class="fw-bold mb-3">Have you been here before, or is this your first visit?</h3>
                            <p class="text-muted">Please select one of the options below</p>
                        </div>
                        
                        <div class="d-grid gap-3 col-md-8 mx-auto">
                            <a href="patient-search.php" class="btn btn-primary btn-lg py-3">
                                <i class="bi bi-check-circle me-2"></i>
                                Yes, I've been here before
                            </a>
                            <a href="registration.php" class="btn btn-outline-primary btn-lg py-3">
                                <i class="bi bi-person-plus me-2"></i>
                                No, this is my first visit
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>