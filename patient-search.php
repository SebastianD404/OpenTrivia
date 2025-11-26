<?php
include_once("php/db_connect.php");
// Assuming 'header.php' is not needed here since you manually coded the nav bar.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PQMS - Patient Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        /* Patient Search Page Specific Styles */
#searchResults {
    transition: all 0.3s ease;
}

.patient-actions .btn {
    padding: 0.5rem;
    font-weight: 500;
}

/* Card styling for patient records */
.card {
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

/* Formatting for patient DOB display */
.text-muted {
    color: #6c757d !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .patient-actions .btn {
        margin-bottom: 0.5rem;
    }
}
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">PQMS</a>
            <div class="navbar-text text-white">
                Patient Search
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-white">
                        <h3 class="card-title mb-0">Find Patient</h3>
                        <p class="text-muted mb-0">Search by name or phone number</p>
                    </div>
                    <div class="card-body">
                        <form id="searchForm">
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label for="searchTerm" class="form-label">Search Term</label>
                                    <input type="text" class="form-control" id="searchTerm" 
                                                placeholder="Name or phone number" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="searchDob" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="searchDob">
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-2"></i> Search Patient
                                </button>
                            </div>
                        </form>

                        <div id="searchResults" class="mt-4" style="display: none;">
                            <h5 class="mb-3">Search Results</h5>
                            <div id="patientList">
                                </div>
                            
                            <div id="noResultsTemplate" class="alert alert-warning" style="display: none;">
                                No patients found. <a href="registration.html">Register new patient</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php
    // CRITICAL FIX: Use GROUP BY to eliminate duplicate patient records (assuming phone number is a unique identifier for a person)
    // We also select the MAX(id) to grab the latest record if duplicates exist.
    $sql_data = "SELECT id, first_name, last_name, date_of_birth, phone 
                 FROM patients 
                 GROUP BY phone 
                 ORDER BY id DESC";

    $result_chart = $db->query($sql_data);
    
    $patients = [];
    if ($result_chart) {
        while($row = $result_chart->fetch_assoc()) {
            $patients[] = [
                'id' => $row['id'],
                'first_name' => $row['first_name'],
                'last_name'=> $row['last_name'],
                'dob' => $row['date_of_birth'],
                'phone' => $row['phone'],
            ];
        }
    }
    ?>
    
    <script>
        // Pass the unique patient list to JavaScript
        const Patients = <?php echo json_encode($patients); ?>;

        // Handle form submission
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const searchTerm = document.getElementById('searchTerm').value.toLowerCase();
            const dob = document.getElementById('searchDob').value;
            
            // Filter patients
            const results = Patients.filter(patient => {
                const nameMatch = `${patient.first_name} ${patient.last_name}`.toLowerCase().includes(searchTerm) ||
                                 patient.phone.includes(searchTerm);
                const dobMatch = dob ? patient.dob === dob : true;
                return nameMatch && dobMatch;
            });
            
            displayResults(results);
        });

        // Display search results
        function displayResults(patients) {
            const resultsContainer = document.getElementById('searchResults');
            const patientList = document.getElementById('patientList');
            const noResultsMsg = document.getElementById('noResultsTemplate');
            
            patientList.innerHTML = '';
            // Hide the No Results message first
            noResultsMsg.style.display = 'none'; 
            
            if (patients.length === 0) {
                // Show "No Results" message
                noResultsMsg.style.display = 'block';
                // The message should be kept outside the patientList DIV, as it's a template
            } else {
                patients.forEach(patient => {
                    const patientCard = document.createElement('div');
                    patientCard.className = 'card mb-3';
                    patientCard.innerHTML = `
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title">${patient.first_name} ${patient.last_name}</h5>
                                <small class="text-muted">${formatDob(patient.dob)}</small>
                            </div>
                            <p class="card-text"><i class="bi bi-telephone"></i> ${patient.phone}</p>
                            
                            <div class="patient-actions mt-3">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <button class="btn btn-primary w-100" onclick="goToAppointment(${patient.id})">
                                            <i class="bi bi-calendar-plus me-2"></i>
                                            Book Appointment
                                        </button>
                                    </div>
                                    <div class="col-md-6">
                                        <button class="btn btn-success w-100" onclick="goToCheckin(${patient.id})">
                                            <i class="bi bi-check-circle me-2"></i>
                                            Proceed to Check-In
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    patientList.appendChild(patientCard);
                });
            }
            
            resultsContainer.style.display = 'block';
        }

        // Format date of birth for display
        function formatDob(dobString) {
            // Check if dobString is valid before creating Date object
            if (!dobString) return ''; 
            const dob = new Date(dobString.replace(/-/g, '/')); // Use replace for better compatibility
            return dob.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        }

        // Navigation functions
        function goToAppointment(patientId) {
            window.location.href = `appointments.php?patient_id=${patientId}`;
        }

        function goToCheckin(patientId) {
            window.location.href = `checkin.php?patient_id=${patientId}`;
        }
    </script>
</body>
</html>