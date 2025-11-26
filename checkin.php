<?php
include_once("php/db_connect.php");

// CRITICAL: Retrieve patient_id from the URL query parameter
$patient_id = isset($_GET['patient_id']) ? htmlspecialchars($_GET['patient_id']) : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PQMS - Patient Check-In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">PQMS</a>
            <div class="navbar-text text-white">
                Patient Check-In
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-white border-bottom-0 pb-0">
                        <h3 class="card-title text-center mb-0">Welcome to Our Clinic</h3>
                        <p class="text-center text-muted">Please complete your check-in process</p>
                    </div>
                    <div class="card-body pt-0">
                        <ul class="nav nav-tabs nav-fill mb-4" id="checkinTabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#appointmentTab">Appointment</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#walkinTab">Walk-In</a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="appointmentTab">
                                <form id="appointmentForm">
                                    <div class="mb-3">
                                        <label for="appointmentId" class="form-label">Appointment ID</label>
                                        <input type="text" class="form-control" id="appointmentId" name="appointmentId" placeholder="Enter your appointment ID">
                                        <span id="error_message" style="color:red;display:none">No appointment record available</span>
                                    </div>
                                    <div class="confirmation-details mt-4 text-start mx-auto" style="display:none" id="patientData">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Patient:</span>
                                    <strong id="confirmationPatient">John Doe</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Appointment:</span>
                                    <strong id="confirmationType"></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Date:</span>
                                    <strong id="confirmationDate">June 30, 2025</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Time:</span>
                                    <strong id="confirmationTime">10:30 AM</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Doctor:</span>
                                    <strong id="confirmationDoctor">Dr. Smith</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Reason:</span>
                                    <strong id="confirmationReason"></strong>
                                </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary px-4">Check In</button>
                                    </div>
                                </form>
                                
                                <p class="text-center text-muted mt-4 pt-3 border-top">
                                    No Appointment yet? 
                                    <a href="appointments.php?patient_id=<?php echo $patient_id; ?>">Book Here.</a>
                                </p>
                                </div>

                            <div class="tab-pane fade" id="walkinTab">
                                <form id="walkinForm">
                                    <div class="mb-3">
                                        <label for="reason" class="form-label">Reason for Visit</label>
                                        <select class="form-select" id="appointmentType" name="appointmentType">
                                            <option selected disabled>Select reason</option>
                                            <?php
                                            // NOTE: Using a prepared statement is safer than direct query execution for non-trivial queries, but maintaining your current style for simplicity here.
                                            // The $db object is assumed to be available from db_connect.php
                                            if (isset($db)):
                                                $outcomes = $db->query("SELECT id, name FROM appointment_types WHERE is_active = 1 LIMIT 10");
                                                while($ot = $outcomes->fetch_assoc()): 
                                                $name = htmlspecialchars($ot['name']); 
                                                $id = htmlspecialchars($ot['id']);
                                            ?>
                                            <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                                            <?php 
                                                endwhile;
                                            endif;
                                            ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="additionalinfo" class="form-label">Additional Info</label>
                                         <textarea class="form-control" id="additionalinfo" name="additionalinfo" required></textarea>
                                         <input type="hidden" id="patientId" name="patientId" value="<?php echo $patient_id;?>">
                                    </div>
                                    <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="termsAgreement" name="termsAgreement" required>
                                        <label class="form-check-label" for="termsAgreement">
                                            I agree to the clinic's <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">terms and conditions</a> <span class="text-danger">*</span>
                                        </label>
                                    </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary px-4">Check In</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
      <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>Appointment Policy</h6>
                    <p>1. Please arrive 15 minutes before your scheduled appointment time.</p>
                    <p>2. Cancellations require at least 24 hours notice.</p>
                    <p>3. Late arrivals may be rescheduled at the clinic's discretion.</p>
                    
                    <h6 class="mt-4">Privacy Policy</h6>
                    <p>1. Your personal information will be kept confidential.</p>
                    <p>2. We may contact you regarding your appointment via phone or email.</p>
                    <p>3. Medical information will only be shared with your consent.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
        
    </div>                                      
    <div class="modal fade" id="checkinSuccessModal" tabindex="-1" data-bs-backdrop="static">
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
                        <h4 class="fw-bold">Check-In Successful!</h4>
                        <p class="text-muted">Your queue number is</p>
                        <div class="queue-number mb-3" id="queueNumber">A105</div>
                        <p>Estimated wait time: <strong>15-20 minutes</strong></p>
                    </div>
                    <div class="alert alert-info text-start">
                        <i class="bi bi-info-circle me-2"></i>
                        Please have a seat in the waiting area. We'll call your number when it's your turn.
                    </div>
                </div>
                <div class="modal-footer border-top-0 justify-content-center">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="window.location.href='queue.php'">Done</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const input = document.getElementById('appointmentId');
        let typingTimer;              // Timer identifier
        const typingDelay = 500;      // Time in ms (e.g., 500ms after typing stops)
        let appointmentID = "";

        // Function to extract patient_id from URL query string
        function getPatientIdFromUrl() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('patient_id');
        }

        // Set the hidden patient ID input value on page load
        document.addEventListener('DOMContentLoaded', function() {
            const pid = getPatientIdFromUrl();
            if (pid) {
                document.getElementById('patientId').value = pid;
            }
        });


        input.addEventListener('input', function () {
            clearTimeout(typingTimer);  // Clear previous timer
            document.getElementById('error_message').style.display = "none";
            appointmentID = "";
            typingTimer = setTimeout(() => {
                searchRecord();            // Call function when typing stops
            }, typingDelay);
        });
            

        function searchRecord() {
            const inputValue = document.getElementById('appointmentId').value;
            if (!inputValue) {
                document.getElementById('patientData').style.display = "none";
                return;
            }
            
            // Build URL with query string
            const url = `php/api_patient_schedule.php?appointmentID=${encodeURIComponent(inputValue)}`;

            fetch(url, {
                method: 'GET'
            })
            .then(response => response.json())
            .then(data => {

                if (data.success && data.appointment.length >= 1) {
                    document.getElementById('patientData').style.display = "block";
                    appointmentID = inputValue;
                    document.getElementById('confirmationPatient').innerHTML = data.appointment[0].fullname
                    document.getElementById('confirmationDate').innerHTML = data.appointment[0].appointment_date
                    document.getElementById('confirmationTime').innerHTML = data.appointment[0].time
                    document.getElementById('confirmationDoctor').innerHTML = data.appointment[0].doctor
                    document.getElementById('confirmationReason').innerHTML = data.appointment[0].reason
                    document.getElementById('confirmationType').innerHTML = data.appointment[0].appointment
                } else {
                    document.getElementById('error_message').style.display = "block";
                    document.getElementById('patientData').style.display = "none";
                }
            })
            .catch(error => {
                console.error('Error fetching appointment data:', error);
                document.getElementById('error_message').innerHTML = "An error occurred while searching.";
                document.getElementById('error_message').style.display = "block";
                document.getElementById('patientData').style.display = "none";
            });
        }


    </script>
    <script src="js/script.js"></script>
</body>
</html>