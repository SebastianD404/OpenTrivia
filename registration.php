<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PQMS - New Patient Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">PQMS</a>
            <div class="navbar-text text-white">
                New Patient Registration
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-white">
                        <h3 class="card-title mb-0">Patient Information</h3>
                        <p class="text-muted mb-0">Please fill out all required fields</p>
                    </div>
                    <div class="card-body">
                        <form id="registrationForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="dob" name="dob" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="" selected disabled>Select gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Area<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="address" name="address" required>
                            </div>

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="consent" name="consent" required>
                                    <label class="form-check-label" for="consent">
                                        I consent to the storage and processing of my personal data <span class="text-danger">*</span>
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" class="btn btn-outline-secondary me-md-2" onclick="window.location.href='visit.php'">
                                    Back
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    Complete Registration and Check In
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="registrationSuccessModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-4">
                        <div class="icon-circle bg-success text-white mb-3 mx-auto">
                            <i class="bi bi-check-lg" style="font-size: 2rem;"></i>
                        </div>
                        <h4 class="fw-bold">Registration Complete!</h4>
                        <p class="text-muted">Thank you for registering with our clinic.</p>
                        </div>
                    <div class="alert alert-info text-start">
                        <i class="bi bi-info-circle me-2"></i>
                        Click the button below to proceed to checkin to see the doctor or book an appointment.
                    </div>
                </div>
                <div class="modal-footer border-top-0 justify-content-center">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="goToCheckin()">Done</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let patientid = null;
        // Handle form submission
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
             const form = document.getElementById('registrationForm');
             const formData = new FormData(form);
                fetch('php/api_register_patient.php', {
                method: 'POST',
                  body: formData // Send directly
              })
            .then(response => {
                // Check if response is NOT valid JSON (e.g., PHP error output)
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    return response.json();
                } else {
                    // Log the non-JSON response text for debugging
                    response.text().then(text => console.error("Non-JSON response:", text));
                    // Throw a specific error to handle the non-JSON case
                    throw new Error("Server returned non-JSON response. Check console for details.");
                }
            })
            .then(data => {
            if (data.success) {
                 // document.getElementById('queueNumber').innerHTML = "G"+data.patient
                 patientid = data.patient;
                  const successModal = new bootstrap.Modal(document.getElementById('registrationSuccessModal'));
                 successModal.show();
            } else {
                 // The alert text now includes the error message from PHP
                 alert('Registration failed: ' + (data.error || 'Unknown error'));
             }
        })
        .catch(error => {
            // Catches network errors or the custom non-JSON error thrown above
            alert('Registration failed: A server or network error occurred. Check the browser console.');
            console.error('Fetch error:', error);
        });
 
        });

        function goToCheckin() {
            // In a real app, you might pass the patient ID as a URL parameter
            window.location.href = `checkin.php?patient_id=${patientid}`;
        }
    </script>
     <script src="js/script.js"></script>
    
</body>
</html>