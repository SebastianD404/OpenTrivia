<?php
//dashboard.php
session_set_cookie_params(0, '/');

session_start();



// PREVENT CACHING

header("Cache-Control: no-cache, no-store, must-revalidate");

header("Pragma: no-cache");

header("Expires: 0");



// SECURITY CHECK

if (!isset($_SESSION['user_id'])) {

    header("Location: index.php");

    exit();

}



include_once("php/db_connect.php");

include("header.php");



$role = $_SESSION['user_role'] ?? 'guest';

$my_id = $_SESSION['user_id'] ?? 0;

$my_name = $_SESSION['first_name'] ?? 'Staff';

?>



<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PQMS - <?php echo ucfirst($role); ?> Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="css/style.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">



   



</head>

<body>



<script>

    if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {

        window.location.reload();

    }

    const currentUserRole = '<?php echo $role; ?>';

    const currentUserId = '<?php echo $my_id; ?>';

</script>



<div class="alert-position" id="alertContainer"></div>



<div class="container my-5">



    <div class="row mb-4 align-items-center">

        <div class="col">

            <h2 class="fw-bold">

                <?php

                    if($role == 'doctor') echo "👨‍⚕️ Physician Dashboard";

                    elseif($role == 'nurse') echo "🩺 Triage Station";

                    elseif($role == 'receptionist') echo "👋 Front Desk Console";

                    else echo "📊 Master Dashboard";

                ?>

            </h2>

            <p class="text-muted">

                Logged in as: <strong><?php echo $my_name; ?></strong> | <?php echo date("l, F j, Y"); ?>

            </p>

        </div>



        <?php if ($role == 'receptionist'): ?>

        <div class="col-auto">

            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#callPatientModal" onclick="loadNextPatientInfo()">

                <i class="bi bi-megaphone-fill"></i> Call Next for Triage

            </button>

        </div>

        <?php endif; ?>

    </div>



    <div class="row">



        <?php

        // =================================================================

        // VIEW 1: RECEPTIONIST DASHBOARD

        // =================================================================

        if ($role === 'receptionist'):

        ?>

            <div class="col-md-3 mb-4">

                <div class="card h-100 border-0 shadow-sm column-wrapper">

                    <div class="card-header bg-secondary text-white">

                        <h5 class="card-title mb-0"><i class="bi bi-people"></i> Waiting Room</h5>
                    
                    </div>

                    <div class="card-body p-0">

                        <ul class="list-group list-group-flush queue-list-container" id="waitingList"></ul>

                    </div>

                    <div class="card-footer text-muted"><small>Total: <span id="waitingCount">0</span></small></div>

                </div>

            </div>



            <div class="col-md-3 mb-4">

                <div class="card h-100 border-0 shadow-sm column-wrapper">

                    <div class="card-header bg-warning text-dark">

                        <h5 class="card-title mb-0"><i class="bi bi-clipboard-pulse"></i> In Triage (Nurse)</h5>

                    </div>

                    <div class="card-body p-0">

                        <ul class="list-group list-group-flush queue-list-container" id="triageList"></ul>

                    </div>

                    <div class="card-footer text-muted"><small>Active in Triage</small></div>

                </div>

            </div>



            <div class="col-md-3 mb-4">

                <div class="card h-100 border-0 shadow-sm column-wrapper">

                    <div class="card-header bg-primary text-white">

                        <h5 class="card-title mb-0"><i class="bi bi-heart-pulse"></i> In Consultation</h5>

                    </div>

                    <div class="card-body p-0">

                        <ul class="list-group list-group-flush queue-list-container" id="inProgressList"></ul>

                    </div>

                    <div class="card-footer text-muted"><small>With Doctor</small></div>

                </div>

            </div>

           

            <div class="col-md-3 mb-4">

                <div class="card h-100 border-0 shadow-sm column-wrapper">

                    <div class="card-header bg-success text-white">

                        <h5 class="card-title mb-0"><i class="bi bi-check-circle"></i> Completed</h5>

                    </div>

                    <div class="card-body p-0">

                        <ul class="list-group list-group-flush queue-list-container" id="completedList"></ul>

                    </div>

                    <div class="card-footer text-muted"><small>Total: <span id="completedCount">0</span></small></div>

                </div>

            </div>





        <?php

        // =================================================================

        // VIEW 2: NURSE DASHBOARD

        // =================================================================

        elseif ($role === 'nurse'):

        ?>

            <div class="col-md-3 mb-4">

                <div class="card h-100 border-0 shadow-sm column-wrapper">

                    <div class="card-header bg-secondary text-white">

                        <h5 class="card-title mb-0"><i class="bi bi-people"></i> Waiting Room</h5>

                    </div>

                    <div class="card-body p-0">

                        <ul class="list-group list-group-flush queue-list-container" id="waitingList"></ul>

                    </div>

                    <div class="card-footer text-muted"><small>Total: <span id="waitingCount">0</span></small></div>

                </div>

            </div>



            <div class="col-md-3 mb-4">

                <div class="card h-100 border-0 shadow-sm column-wrapper">

                    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">

                        <h5 class="card-title mb-0"><i class="bi bi-clipboard-data"></i> Triage Queue (Mine)</h5>

                    </div>

                    <div class="card-body p-0">

                        <div id="nurseTriageList" class="list-group list-group-flush queue-list-container p-2"></div>

                    </div>

                     <div class="card-footer text-muted"><small>Patients in my queue: <span id="triageCount">0</span></small></div>

                </div>

            </div>



            <div class="col-md-3 mb-4">

                <div class="card h-100 border-0 shadow-sm column-wrapper">

                    <div class="card-header bg-primary text-white">

                        <h5 class="card-title mb-0"><i class="bi bi-heart-pulse"></i> In Consultation</h5>

                    </div>

                    <div class="card-body p-0">

                        <ul class="list-group list-group-flush queue-list-container" id="inProgressList"></ul>

                    </div>

                    <div class="card-footer text-muted"><small>With Doctor</small></div>

                </div>

            </div>

           

            <div class="col-md-3 mb-4">

                <div class="card h-100 border-0 shadow-sm opacity-75 column-wrapper">

                    <div class="card-header bg-success text-white">

                        <h5 class="card-title mb-0"><i class="bi bi-check-circle"></i> Ready for Doctor</h5>

                    </div>

                    <div class="card-body p-0">

                        <ul class="list-group list-group-flush queue-list-container" id="readyForDoctorList"></ul>

                    </div>

                    <div class="card-footer text-muted"><small>Total: <span id="readyForDoctorCount">0</span></small></div>

                </div>

            </div>





        <?php

        // =================================================================

        // VIEW 3: DOCTOR DASHBOARD (Updated with Empty State Messages)

        // =================================================================

        elseif ($role === 'doctor'):
        ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm column-wrapper">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="bi bi-stethoscope"></i> Waiting for Consultation</h5>
                    </div>
                    <div class="card-body p-0 bg-light d-flex flex-column justify-content-center align-items-center">
                        <div id="doctorConsultList" class="list-group list-group-flush queue-list-container w-100 h-100 p-2">

                             <!-- Empty State Message - REMOVED style="display: none;" -->
                            <div id="emptyConsult" class="text-center text-muted p-5 small empty-message">
                                <i class="bi bi-file-earmark-person" style="font-size: 2rem;"></i><br>

                                No patients waiting for consultation.
                            </div>
                        </div>

                    </div>
                    <div class="card-footer text-muted"><small>Total Patients: <span id="consultCount">0</span></small></div>
                </div>

            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm column-wrapper">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0"><i class="bi bi-play-circle"></i> In Progress (Mine)</h5>

                    </div>
                    <div class="card-body p-0 d-flex flex-column justify-content-center align-items-center">
                        <ul class="list-group list-group-flush queue-list-container w-100 h-100" id="myInProgressList">
                            <!-- Empty State Message - REMOVED style="display: none;" -->
                            <div id="emptyInProgress" class="text-center text-muted p-5 small empty-message">
                                <i class="bi bi-person-check" style="font-size: 2rem;"></i><br>

                                No patient in progress.
                            </div>
                        </ul>
                    </div>
                    <div class="card-footer text-muted"><small>Currently Consulting</small></div>
                </div>
            </div>
        <?php

        // =================================================================

        // VIEW 4: ADMIN / DEFAULT

        // =================================================================

        else: // Default Admin Dashboard

        ?>

            <div class="col-md-3 mb-4">
                <div class="card h-100 border-0 shadow-sm column-wrapper">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0"><i class="bi bi-people"></i> Waiting Room</h5>
                    </div>

                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush queue-list-container" id="waitingList"></ul>

                    </div>
                    <div class="card-footer text-muted"><small>Total: <span id="waitingCount">0</span></small></div>
                </div>

            </div>

            <div class="col-md-3 mb-4">
                <div class="card h-100 border-0 shadow-sm column-wrapper">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0"><i class="bi bi-clipboard-pulse"></i> In Triage</h5>

                    </div>

                    <div class="card-body p-0">
                        <ul id="triageList" class="list-group list-group-flush queue-list-container"></ul>

                    </div>
                     <div class="card-footer text-muted"><small>Total: <span id="triageCount">0</span></small></div>
                </div>

            </div>

            <div class="col-md-3 mb-4">
                <div class="card h-100 border-0 shadow-sm column-wrapper">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="bi bi-heart-pulse"></i> In Consultation</h5>

                    </div>

                    <div class="card-body p-0">
                        <ul id="inProgressList" class="list-group list-group-flush queue-list-container"></ul>

                    </div>

                     <div class="card-footer text-muted"><small>Total: <span id="consultCount">0</span></small></div>

                </div>

            </div>

            <div class="col-md-3 mb-4">
                <div class="card h-100 border-0 shadow-sm column-wrapper">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0"><i class="bi bi-check-circle"></i> Completed</h5>

                    </div>

                    <div class="card-body p-0">
                        <ul id="completedList" class="list-group list-group-flush queue-list-container"></ul>

                    </div>

                     <div class="card-footer text-muted"><small>Total: <span id="completedCount">0</span></small></div>

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>

<?php if ($role == 'receptionist'): ?>

<div class="modal fade" id="callPatientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-megaphone"></i> Call Patient for Triage</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <div id="nextPatientInfo" class="alert alert-secondary">Fetching next patient details...</div>

                <div class="mb-3">

                    <label class="form-label fw-bold">Triage Priority Level:</label>
                    <div class="d-flex gap-2">

                        <input type="radio" class="btn-check" name="priorityOption" id="prio_normal" value="normal" checked>
                        <label class="btn btn-outline-success flex-fill" for="prio_normal">

                            <i class="bi bi-person"></i> Normal

                        </label>

                        <input type="radio" class="btn-check" name="priorityOption" id="prio_priority" value="priority">
                        <label class="btn btn-outline-warning flex-fill" for="prio_priority">

                            <i class="bi bi-exclamation-triangle"></i> Priority

                        </label>

                        <input type="radio" class="btn-check" name="priorityOption" id="prio_critical" value="critical">
                        <label class="btn btn-outline-danger flex-fill" for="prio_critical">

                            <i class="bi bi-lightning-fill"></i> Critical

                        </label>

                    </div>

                    <div class="form-text text-muted mt-1" id="prioHelpText">Standard queueing order.</div>

                </div>
                <p class="text-muted small mt-3">Clicking "Send to Triage" will notify the Nurse station.</p>
            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="callPatientBtn" data-queue-id="">

                    <i class="bi bi-box-arrow-right"></i> Send to Triage Queue

                </button>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/script.js"></script>

<script>
// =================================================================

// LOGIC FOR RECEPTIONIST CALLING PATIENT TO TRIAGE

// =================================================================
function fetchNextPatient() {

    // This PHP file should return the next patient with status 'waiting'
     return fetch('php/api_get_next_patient.php').then(r => r.json()).catch(e => console.error(e));

}

function renderNextPatientInfo(data) {

    const infoDiv = document.getElementById('nextPatientInfo');
    const callBtn = document.getElementById('callPatientBtn');

    if (data && data.status === 'success' && data.data) {

        const p = data.data;
        infoDiv.className = 'alert alert-info';
        infoDiv.innerHTML = `<strong>Next:</strong> ${p.first_name} ${p.last_name} <br> <strong>Queue:</strong> ${p.queue_number}`;
        callBtn.disabled = false;
        callBtn.setAttribute('data-queue-id', p.id);

    } else {

        infoDiv.className = 'alert alert-warning';
        infoDiv.innerHTML = "No patients waiting.";
        callBtn.disabled = true;
    }
}

function loadNextPatientInfo() {

    fetchNextPatient().then(renderNextPatientInfo);

}
// Wait for DOM to load to attach listeners

document.addEventListener('DOMContentLoaded', function() {

    // --- PRIORITY RADIO BUTTON LOGIC ---

    const radios = document.querySelectorAll('input[name="priorityOption"]');
    const helpText = document.getElementById('prioHelpText');

    if(radios.length > 0) {

        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                if(this.value === 'critical') {
                    helpText.innerHTML = "<span class='text-danger fw-bold'>⚠️ IMMEDIATE ACTION: Flags Nurse.</span>";
                } else if(this.value === 'priority') {
                    helpText.innerHTML = "<span class='text-warning fw-bold'>⚡ High Priority: Placed at top of list.</span>";
                } else {
                    helpText.innerHTML = "Standard queueing order.";

                }
            });
        });
    }

    // --- CALL BUTTON CLICK LOGIC ---

    const callBtn = document.getElementById('callPatientBtn');

    if(callBtn){

        callBtn.addEventListener('click', function() {

            const queueId = this.getAttribute('data-queue-id');
            if(!queueId) return;

            // Get the selected priority
            let selectedPriority
            const selectedRadio = document.querySelector('input[name="priorityOption"]:checked');

            if (selectedRadio) {
                selectedPriority = selectedRadio.value;
            } else {

                selectedPriority = 'normal'; // Default safety
            }

            // AJAX call to send patient to triage
            fetch('php/api_update_status.php', {

                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({

                    id: queueId,
                    new_status: 'triage',
                    priority: selectedPriority,
                    user_id: currentUserId,
                    user_role: currentUserRole

                })
            })

            .then(response => response.json())
            .then(data => {

                if (data.status === 'success') {
                    showCustomAlert('success', `Patient Q${data.queue_number} sent to Triage!`);

                    // Close modal and refresh patient info
                    const modalElement = document.getElementById('callPatientModal');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    modal.hide();

                } else {
                    showCustomAlert('danger', `Error sending patient: ${data.message}`);
                }
                loadNextPatientInfo(); // Load next patient regardless of success/fail

            })

            .catch(error => {

                console.error('Error:', error);
                showCustomAlert('danger', 'Network error or internal server issue.');
                loadNextPatientInfo();

            });
        });
    }
});
</script>