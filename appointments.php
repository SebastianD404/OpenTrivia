<?php
include_once("php/db_connect.php");
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PQMS - Schedule Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .patient-dropdown {
            max-height: 200px;
            overflow-y: auto;
        }
        .patient-option {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        .patient-option:hover {
            background-color: #f8f9fa;
        }
        .patient-option.selected {
            background-color: #007bff;
            color: white;
        }
        .dropdown-container {
            position: relative;
        }
        .custom-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ced4da;
            border-top: none;
            border-radius: 0 0 0.375rem 0.375rem;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        .custom-dropdown.show {
            display: block;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">PQMS</a>
            <div class="navbar-text text-white">
                Schedule Appointment
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-white">
                        <h3 class="card-title mb-0">Book Your Appointment</h3>
                        <p class="text-muted mb-0">Please fill out the form below</p>
                    </div>
                    <div class="card-body">
                        <form id="appointmentForm">
                            <div class="row mb-4" style="display: none;">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="patientType" id="existingPatient" checked>
                                        <label class="form-check-label" for="existingPatient">
                                            Registered Patient
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6" >
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="patientType" id="newPatient">
                                        <label class="form-check-label" for="newPatient">
                                            New Patient
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Existing Patient Fields (shown by default) -->
                            <div id="existingPatientFields">
                                <!--<div class="mb-3">
                                    <label for="patientSearch" class="form-label">Patient <span class="text-danger">*</span></label>
                                    <div class="dropdown-container">
                                        <input type="text" class="form-control" id="patientSearch" placeholder="Search patient by name or ID..." autocomplete="off">
                                        <input type="hidden" id="selectedPatientId" name="patientId">
                                        <div id="patientDropdown" class="custom-dropdown">
                                          
                                        </div>
                                    </div>
                                    <div class="form-text">Start typing to search for a patient</div>
                                </div>-->
                             
                                <div class="mb-3">
                                    <label for="appointmentType" class="form-label">Appointment Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="appointmentType" name="appointmentType" required>
                                        <option value="" selected disabled>Select appointment type</option>
                                        <?php
                                          $outcomes = $db->query("SELECT * FROM appointment_types where is_active = 1 LIMIT 10");
                                          while($ot = $outcomes->fetch_assoc()): 
                                         $name = $ot['name']; $id = $ot['id'];
                                        ?>
                                     <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                                <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="preferredDoctor" class="form-label">Preferred Doctor</label>
                                    <select class="form-select" id="preferredDoctor" name="preferredDoctor">
                                        <option value="">Any available doctor</option>
                                        <?php
                                          $outcomes = $db->query("SELECT * FROM staff where role = 'doctor' LIMIT 10");
                                          while($ot = $outcomes->fetch_assoc()): 
                                         $name = "Dr.".$ot['first_name']." ".$ot['last_name']; $id = $ot['id'];
                                        ?>
                                     <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                                <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="appointmentDate" class="form-label">Preferred Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="appointmentDate" name="appointmentDate" placeholder="Select date" required>
                                </div>

                                <div class="mb-3">
                                    <label for="appointmentTime" class="form-label">Preferred Time <span class="text-danger">*</span></label>
                                    <select class="form-select" id="appointmentTime" name="appointmentTime" required>
                                        <option value="" selected disabled>Select time slot</option>
                                        <option value="08:00">8:00 AM</option>
                                        <option value="08:30">8:30 AM</option>
                                        <option value="09:00">9:00 AM</option>
                                        <option value="09:30">9:30 AM</option>
                                        <option value="10:00">10:00 AM</option>
                                        <option value="10:30">10:30 AM</option>
                                        <option value="11:00">11:00 AM</option>
                                        <option value="11:30">11:30 AM</option>
                                        <option value="13:00">1:00 PM</option>
                                        <option value="13:30">1:30 PM</option>
                                        <option value="14:00">2:00 PM</option>
                                        <option value="14:30">2:30 PM</option>
                                        <option value="15:00">3:00 PM</option>
                                        <option value="15:30">3:30 PM</option>
                                        <option value="16:00">4:00 PM</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="reason" class="form-label">Reason for Visit</label>
                                    <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Briefly describe the reason for your appointment"></textarea>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="termsAgreement" name="termsAgreement" required>
                                        <label class="form-check-label" for="termsAgreement">
                                            I agree to the clinic's <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">terms and conditions</a> <span class="text-danger">*</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="button" class="btn btn-outline-secondary me-md-2" onclick="window.location.href='index.php'">
                                        Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        Schedule Appointment
                                    </button>
                                </div>
                            </div>

                            <!-- New Patient Fields (hidden by default) -->
                            <div id="newPatientFields" style="display: none;">
                                <div class="row justify-content-center">
                                    <div class="card shadow">
                                        <div class="card-body text-center py-5">
                                            <div class="mb-4">
                                                <i class="bi bi-question-circle display-4 text-primary mb-3"></i>
                                                <h3 class="fw-bold mb-3">Since this is not registered patient?</h3>
                                                <p class="text-muted">Please register the patient below!</p>
                                            </div>
                                            
                                            <div class="d-grid gap-3 col-md-8 mx-auto">
                                                <a href="registration.php" class="btn btn-outline-primary btn-lg py-3">
                                                    <i class="bi bi-person-plus me-2"></i>
                                                    Registration
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
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

    <!-- Appointment Confirmation Modal -->
    <div class="modal fade" id="appointmentConfirmationModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-4">
                        <div class="icon-circle bg-success text-white mb-3 mx-auto">
                            <i class="bi bi-calendar-check" style="font-size: 2rem;"></i>
                        </div>
                        <h4 class="fw-bold">Appointment Scheduled!</h4>
                        <div class="confirmation-details mt-4 text-start mx-auto" style="max-width: 300px;">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Appointment ID:</span>
                                <strong id="confirmationID">APT202306001</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Patient:</span>
                                <strong id="confirmationPatient">John Doe</strong>
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
                        </div>
                    </div>
                    <div class="alert alert-info text-start mt-4">
                        <i class="bi bi-info-circle me-2"></i>
                        A confirmation has been sent to your email. Please bring your ID and insurance card to your appointment.
                    </div>
                </div>
                <div class="modal-footer border-top-0 justify-content-center">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="window.location.href='checkin.php'">Done</button>
                    <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                        <i class="bi bi-printer me-2"></i>Print
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php
    
    $sql_data = "SELECT * FROM patients ORDER BY DATE(created_at) DESC ";
$result_chart = $db->query($sql_data);
$patients = [];
while($row = $result_chart->fetch_assoc()) {
    $patients[] = [
        'id' => $row['id'],
        'name' => $row['first_name']." ".$row['last_name'],
        'gender' => $row['gender'],
        'dob' => $row['date_of_birth'],
    ];
}?>
   <script>
   var patients = <?php echo json_encode($patients); ?>;
   </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>

       // patients = patients.concat(patients1);
        // Initialize date picker
        flatpickr("#appointmentDate", {
            minDate: "today",
            maxDate: new Date().fp_incr(30),
            dateFormat: "Y-m-d",
            disable: [
                function(date) {
                    return (date.getDay() === 0 || date.getDay() === 6);
                }
            ]
        });

        // Patient search functionality
        /*const patientSearch = document.getElementById('patientSearch');
        const patientDropdown = document.getElementById('patientDropdown');
        const selectedPatientId = document.getElementById('selectedPatientId');
        let selectedPatientName = '';

        function formatPatientOption(patient) {
            return `${patient.name} (${patient.gender} / ${patient.dob})`;
        }

        function showPatientDropdown(filteredPatients) {
            patientDropdown.innerHTML = '';
            
            if (filteredPatients.length === 0) {
                patientDropdown.innerHTML = '<div class="patient-option text-muted">No patients found</div>';
            } else {
                filteredPatients.forEach(patient => {
                    const option = document.createElement('div');
                    option.className = 'patient-option';
                    option.textContent = formatPatientOption(patient);
                    option.dataset.patientId = patient.id;
                    option.dataset.patientName = patient.name;
                    
                    option.addEventListener('click', function() {
                        selectedPatientId.value = patient.id;
                        selectedPatientName = patient.name;
                        patientSearch.value = formatPatientOption(patient);
                        patientDropdown.classList.remove('show');
                    });
                    
                    patientDropdown.appendChild(option);
                });
            }
            
            patientDropdown.classList.add('show');
        }

        function hidePatientDropdown() {
            setTimeout(() => {
                patientDropdown.classList.remove('show');
            }, 200);
        }

        patientSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();            
            if (searchTerm.length === 0) {
                patientDropdown.classList.remove('show');
                selectedPatientId.value = '';
                selectedPatientName = '';
                return;
            }
            
            const filteredPatients = patients.filter(patient => {
                const patientText = formatPatientOption(patient).toLowerCase();
                return patientText.includes(searchTerm) || patient.id.toString().includes(searchTerm);
            });
            
            showPatientDropdown(filteredPatients);
        });

        patientSearch.addEventListener('focus', function() {
            if (this.value.length > 0) {
                const searchTerm = this.value.toLowerCase();
                const filteredPatients = patients.filter(patient => {
                    const patientText = formatPatientOption(patient).toLowerCase();
                    return patientText.includes(searchTerm) || patient.id.toString().includes(searchTerm);
                });
                showPatientDropdown(filteredPatients);
            }
        });

        patientSearch.addEventListener('blur', hidePatientDropdown);

        // Click outside to close dropdown
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.dropdown-container')) {
                patientDropdown.classList.remove('show');
            }
        });

        // Toggle between existing and new patient fields
        document.querySelectorAll('input[name="patientType"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.id === 'existingPatient') {
                    document.getElementById('existingPatientFields').style.display = 'block';
                    document.getElementById('newPatientFields').style.display = 'none';
                } else {
                    document.getElementById('existingPatientFields').style.display = 'none';
                    document.getElementById('newPatientFields').style.display = 'block';
                }
            });
        }); */

        // Handle form submission
        document.getElementById('appointmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let patient_id = "<?php echo $_GET['patient_id']; ?>"
            // Validate patient selection
            if (!patient_id) {
                alert('Please select a patient');
                patientSearch.focus();
                return;
            }

            const form = document.getElementById('appointmentForm');
             const formData = new FormData(form);
             formData.append('patient_id', patient_id);
               fetch('php/api_patient_schedule.php', {
                method: 'POST',
                  body: formData // Send directly
              })
           .then(response => response.json())
           .then(data => {
          if (data.success) {
                    
                         // Update confirmation details
            const dateInput = document.getElementById('appointmentDate');
            const timeSelect = document.getElementById('appointmentTime');
            const doctorSelect = document.getElementById('preferredDoctor');



            document.getElementById('confirmationID').textContent = data.appointmentID;
            document.getElementById('confirmationPatient').textContent = data.patient[0].fullname;
            
            if (dateInput.value) {
                const date = new Date(dateInput.value);
                document.getElementById('confirmationDate').textContent = date.toLocaleDateString('en-US', { 
                    year: 'numeric', month: 'long', day: 'numeric' 
                });
            }
            
            if (timeSelect.value) {
                const timeText = timeSelect.options[timeSelect.selectedIndex].text;
                document.getElementById('confirmationTime').textContent = timeText;
            }
            
            if (doctorSelect.value && doctorSelect.value !== '') {
                const doctorText = doctorSelect.options[doctorSelect.selectedIndex].text;
                document.getElementById('confirmationDoctor').textContent = doctorText;
            } else {
                document.getElementById('confirmationDoctor').textContent = 'First available';
            }
            
            const confirmationModal = new bootstrap.Modal(document.getElementById('appointmentConfirmationModal'));
            confirmationModal.show();

           } else {
                   alert('Registration failed: ' + (data.error || 'Unknown error'));
                }
        });
            
            
        });
    </script>
</body>
</html>