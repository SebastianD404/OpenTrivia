// Sample data for the dashboard - Expanded to include new statuses
let patients = {
    waiting: [],            // Status: waiting (Receptionist View)
    pending_triage: [],     // Status: pending_triage (Nurse Triage Queue)
    ready_for_doctor: [],   // Status: ready_for_doctor (Doctor Consultation Queue)
    in_progress: [],        // Status: in_progress (Active Consultations)
    completed: []           // Status: completed
};

// Global interval ID holder for real-time polling
let updateInterval;

// Initialize the dashboard
document.addEventListener('DOMContentLoaded', async function() {
    
    // --- GLOBAL LOGOUT LOGIC ---
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault(); 
            
            // Clear LocalStorage (Frontend)
            localStorage.removeItem("pqms");
            localStorage.removeItem("staff");
            
            // Redirect to PHP Logout (Backend)
            window.location.href = "logout.php";
        });
    }
    // --------------------------------

    // 1. CHECK FOR LOGIN FORM
    if (document.getElementById('loginForm')) {
        setupLoginForm();
    }

    // 2. DASHBOARD LOGIC WITH POLLING
    if (document.querySelector('#waitingList') || document.querySelector('#nurseTriageList')) {
        // Initial fetch and update
        await fetchPatients();
        updateDashboard();
        
        // Set up polling (every 5 seconds) for real-time updates
        if (!updateInterval) {
            updateInterval = setInterval(async () => {
                await fetchPatients();
                updateDashboard();
                // Check and update the next patient info in the modal (for receptionist)
                updateNextPatientModalInfo();
            }, 5000); 
        }
    }

    // Existing: index.php auto-update (registration trigger) - KEEP IF NEEDED FOR SIMULATION
    if (window.location.href.indexOf("index.php") > -1) {
        const formData = new FormData();
        formData.append("auto", "today");
        
        fetch('php/api_register_patient.php', { 
            method: 'POST',
            body: formData 
        })
        .then(response => response.json())
        .then(data => {
            // console.log(data)
        });
    }

    // Existing: Check-in form setup
    if (document.querySelector('#appointmentForm')) {
        setupCheckInForms();
    }

    // Existing: Queue display page simulation
    if (document.querySelector('.queue-display')) {
        simulateQueueUpdates();
    }
});

// ==========================================
// HANDLE LOGIN REDIRECT
// ==========================================
function setupLoginForm() {
    const loginForm = document.getElementById('loginForm');
    
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);

        fetch('php/api_login.php', { 
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                
                if(data.token) {
                    localStorage.setItem("pqms", data.token);
                }

                if(document.getElementById('alertContainer')) {
                    showAlert('success', 'Login successful. Redirecting...');
                }

                setTimeout(() => {
                    window.location.href = 'dashboard.php'; 
                }, 500); 
                
            } else {
                if(document.getElementById('alertContainer')) {
                    showAlert('danger', data.message);
                } else {
                    console.error('Login Error:', data.message);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            console.error('Login failed. Please check console.');
        });
    });
}

// ==========================================
// REVISED: FETCH PATIENTS AND SORT BY STATUS/PRIORITY
// ==========================================
async function fetchPatients() {
    // *** CORRECTED URL TO USE api_get_queue.php ***
    const url = `php/api_get_queue.php?view=all_today`; 

    try {
        const response = await fetch(url, { method: 'GET' });
        const data = await response.json();

        if (data.success && data.queue) {
            // Reset patient buckets
            patients = {
                waiting: [],
                pending_triage: [],
                ready_for_doctor: [],
                in_progress: [],
                completed: []
            };

            // Map patients to the correct bucket based on status
            data.queue.forEach(p => {
                // ... (name combining logic)
                
                let patientStatus = p.status;
                
                // ***THIS MAPS THE SCHEDULED APPOINTMENTS TO THE WAITING ROOM ***
                if (patientStatus === 'scheduled') {
                    patientStatus = 'waiting'; 
                }

                if (patients[patientStatus]) {
                    patients[patientStatus].push(p);
                }
            });

        } else {
            patients = { waiting: [], pending_triage: [], ready_for_doctor: [], in_progress: [], completed: [] };
        }
    } catch (error) {
        console.error('Error fetching queue data:', error);
        patients = { waiting: [], pending_triage: [], ready_for_doctor: [], in_progress: [], completed: [] };
    }
}

/**
 * Helper function to determine the visual styling for a patient card based on priority.
 * @param {string} priority - 'normal', 'priority', or 'critical'
 * @returns {{liClass: string, badgeClass: string, icon: string}}
 */
function getPriorityStyle(priority) {
    switch (priority) {
        case 'critical':
            return {
                liClass: 'list-group-item list-group-item-danger border-left-3 border-danger',
                badgeClass: 'bg-danger',
                icon: '<i class="bi bi-lightning-fill"></i> Critical'
            };
        case 'priority':
            return {
                liClass: 'list-group-item list-group-item-warning border-left-3 border-warning',
                badgeClass: 'bg-warning text-dark',
                icon: '<i class="bi bi-exclamation-triangle-fill"></i> Priority'
            };
        default: // normal or null
            return {
                liClass: 'list-group-item',
                badgeClass: 'bg-secondary',
                icon: '<i class="bi bi-person-fill"></i> Normal'
            };
    }
}

// ==========================================
// FORMAT DOCTOR NAME
// ==========================================
function formatDoctorName(doctorString) {
    if (!doctorString) return 'Assigned';
    
    let name = String(doctorString).trim();
    
    if (name.toLowerCase().startsWith('dr')) {
        let namePart = name.substring(2).trim();

        if (namePart.startsWith('.') || namePart.startsWith(' ')) {
            namePart = namePart.substring(1).trim();
        }

        const properlyCasedName = namePart.split(/\s+/).map(word => {
            if (!word) return '';
            return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase(); 
        }).join(' ');
        
        return `Dr. ${properlyCasedName}`; 
    }

    return name.split(/\s+/).map(word => {
        if (!word) return '';
        return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
    }).join(' ');
}


// ==========================================
// REVISED: UPDATE DASHBOARD (FIXED ALL EMPTY STATES)
// ==========================================
function updateDashboard() {
    // Elements for Receptionist/Admin View
    const waitingList = document.getElementById('waitingList');
    const triageList = document.getElementById('triageList'); 
    const inProgressList = document.getElementById('inProgressList'); // Used by Admin/Receptionist for Consult Queue
    const completedList = document.getElementById('completedList');

    // Elements for Nurse View
    const nurseTriageList = document.getElementById('nurseTriageList'); 
    const readyForDoctorList = document.getElementById('readyForDoctorList'); // Nurse view of consult queue
    
    // Elements for Doctor View
    const doctorConsultList = document.getElementById('doctorConsultList'); // Doctor view of consult queue
    const myInProgressList = document.getElementById('myInProgressList'); // Doctor view for their 'in_progress'

    const isDoctor = (typeof currentUserRole !== 'undefined' && currentUserRole === 'doctor');
    const isNurse = (typeof currentUserRole !== 'undefined' && currentUserRole === 'nurse');
    const isReceptionist = (typeof currentUserRole !== 'undefined' && currentUserRole === 'receptionist');
    const userId = typeof currentUserId !== 'undefined' ? currentUserId : '0';

    // --- 0. Clear all lists ---
    [waitingList, triageList, inProgressList, completedList, nurseTriageList, readyForDoctorList, doctorConsultList, myInProgressList].forEach(el => {
        if (el) el.innerHTML = '';
    });
    
    // --- 1. Waiting List (Status: 'waiting') ---
    const waitingPatients = patients.waiting || [];
    if (waitingList) {
        if (waitingPatients.length === 0) {
            // FIX: Simple list item for empty state
            waitingList.innerHTML = `<li class="list-group-item text-center text-muted"><small><i class="bi bi-person-check-fill me-1"></i> No patients waiting for triage assignment.</small></li>`;
        }
        waitingPatients.forEach(patient => {
            const priorityStyle = getPriorityStyle(patient.priority);
            const li = document.createElement('li');
            li.className = `list-group-item d-flex justify-content-between align-items-center ${priorityStyle.liClass.includes('danger') ? 'list-group-item-danger' : ''}`;
            
            li.innerHTML = `
                <div>
                    <h6 class="mb-1 fw-bold">${patient.name}</h6>
                    <small class="text-muted">${patient.time} - ${patient.reason}</small>
                </div>
                <div class="text-end">
                    <span class="badge ${priorityStyle.badgeClass}">${patient.id}</span>
                </div>
            `;
            waitingList.appendChild(li);
        });
        if(document.getElementById('waitingCount'))
            document.getElementById('waitingCount').textContent = waitingPatients.length;
    }


    // --- 2. Triage Queue (Status: 'pending_triage') ---
    const triagePatients = patients.pending_triage || [];
    const triageTarget = triageList || nurseTriageList; 

    if (triageTarget) {
        if (triagePatients.length === 0) {
             // FIX: Simple list item for empty state
             triageTarget.innerHTML = `<li class="list-group-item text-center text-muted"><small><i class="bi bi-list-check me-1"></i> No patients in the Triage Queue.</small></li>`;
        }

        triagePatients.forEach(patient => {
            const priorityStyle = getPriorityStyle(patient.priority);
            const li = document.createElement('li');
            li.className = `list-group-item list-group-item-action border-triage`; 
            
            let actionButton = '';
            if (isNurse) {
                // Nurse action: Start Triage
                actionButton = `
                    <button class="btn btn-sm btn-warning mt-2" onclick="startTriage(${patient.queue_id})">
                        <i class="bi bi-clock-history"></i> Start Triage
                    </button>
                `;
            }

            li.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-bold">${patient.name}</h6>
                        <small class="text-danger">${patient.reason}</small>
                    </div>
                    <div class="text-end">
                        <span class="badge ${priorityStyle.badgeClass} mb-1">${priorityStyle.icon}</span>
                        <p class="text-muted small mb-0">Called by: ${patient.called_by_name || 'Reception'}</p>
                    </div>
                </div>
                ${actionButton}
            `;
            triageTarget.appendChild(li);
        });
        // Update count element if present (used in Nurse view)
        if(document.getElementById('triageCount'))
            document.getElementById('triageCount').textContent = triagePatients.length;
    }

    // --- 3. Consultation Queue (Status: 'ready_for_doctor') ---
    const consultPatients = patients.ready_for_doctor || [];
    // Target is: Receptionist/Admin (inProgressList) OR Doctor/Nurse (doctorConsultList/readyForDoctorList)
    const consultTarget = inProgressList || doctorConsultList || readyForDoctorList; 

    if (consultTarget) {
        // RENDER EMPTY STATE IF NO PATIENTS
        if (consultPatients.length === 0) {
             // FIX: Simple list item for empty state
             consultTarget.innerHTML = `<li class="list-group-item text-center text-muted"><small><i class="bi bi-hospital-fill me-1"></i> No patients waiting for consultation.</small></li>`;
        }
        
        consultPatients.forEach(patient => {
            const priorityStyle = getPriorityStyle(patient.priority);
            const li = document.createElement('li');
            li.className = `list-group-item list-group-item-action border-consult`; 
            
            let actionButton = '';
            // Determine who called the patient or if they are ready
            let doctorName = patient.assigned_staff_name ? formatDoctorName(patient.assigned_staff_name) : 'Ready';

            if (isDoctor) {
                // Doctor action: Start Consultation
                actionButton = `
                    <button class="btn btn-sm btn-primary mt-2" onclick="startConsult(${patient.queue_id})">
                        <i class="bi bi-play-circle-fill"></i> Start Consult
                    </button>
                `;
            } else if (isReceptionist || isNurse) {
                // Display the staff who called them (usually the triage nurse/receptionist)
                doctorName = patient.assigned_staff_name || 'Ready';
            }

            li.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-bold">${patient.name}</h6>
                        <small class="text-muted">${patient.time} | Room ${patient.room_id || 'N/A'}</small>
                    </div>
                    <div class="text-end">
                        <span class="badge ${priorityStyle.badgeClass} mb-1">${priorityStyle.icon}</span>
                        <p class="text-muted small mb-0">${doctorName}</p>
                    </div>
                </div>
                ${actionButton}
            `;
            consultTarget.appendChild(li);
        });

        if(document.getElementById('consultCount'))
            document.getElementById('consultCount').textContent = consultPatients.length;
    }


    // --- 4. In Progress (Status: 'in_progress') ---
    let progressPatients = patients.in_progress || [];
    const progressTarget = inProgressList || myInProgressList; 

    // Filter for Doctor/Nurse
    if (isDoctor || isNurse) {
        // Filter to only show patients assigned to the current user
        progressPatients = progressPatients.filter(p => String(p.assigned_staff_id) === String(userId));
    }
    
    if (progressTarget) {
        // FIX: Display a simple list item when empty, instead of the large card
        if ((isDoctor || isNurse) && progressPatients.length === 0 && myInProgressList) {
            myInProgressList.innerHTML = `
                <li class="list-group-item text-center text-muted">
                    <small><i class="bi bi-person-check-fill me-1"></i> No active patient assigned to you yet.</small>
                </li>
            `;
        } else if (progressPatients.length === 0 && progressTarget === inProgressList) {
             inProgressList.innerHTML = `<li class="list-group-item text-center text-muted"><small><i class="bi bi-people-fill me-1"></i> No active consultations.</small></li>`;
        } else {
            // Clear the list if it's the filtered one and we have patients (to prevent duplicates)
            if((isDoctor || isNurse) && myInProgressList) myInProgressList.innerHTML = '';
            
            progressPatients.forEach(patient => {
                const doctorDisplay = formatDoctorName(patient.assigned_staff_name);
                const li = document.createElement('li');
                li.className = 'list-group-item';

                if (isDoctor || isNurse) {
                    // For Doctor/Nurse, use the detailed action card (only for MY list)
                    li.className = 'list-group-item list-group-item-warning p-4 shadow-sm'; 
                    li.innerHTML = `
                        <div class="text-center">
                            <h4 class="mb-1 fw-bold text-dark">${patient.name}</h4>
                            <p class="mb-3 text-muted">Token: <span class="badge bg-warning text-dark">${patient.id}</span></p>
                            <p class="mb-4">
                                <small class="text-secondary">${doctorDisplay} | Room ${patient.room_id || 'N/A'}</small>
                            </p>
                            <button class="btn btn-success btn-lg px-4" onclick="completeVisit(${patient.queue_id})">
                                <i class="bi bi-check-circle-fill"></i> Complete Visit
                            </button>
                        </div>
                    `;
                } else {
                    // For Admin/Receptionist, use the standard list item
                    li.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${patient.name}</h6>
                                <small class="text-muted">${doctorDisplay} - Room ${patient.room_id || 'N/A'}</small>
                            </div>
                            <span class="badge bg-warning rounded-pill">${patient.id}</span>
                        </div>
                    `;
                }
                progressTarget.appendChild(li);
            });
        }
        if(document.getElementById('progressCount'))
            document.getElementById('progressCount').textContent = progressPatients.length;
    }
    
// --- 5. Completed List (Status: 'completed') ---
    let completedPatients = patients.completed || [];
    
    // Doctor/Nurse: Filter to only show patients completed by them
    if (isDoctor || isNurse) {
        completedPatients = completedPatients.filter(p => String(p.assigned_staff_id) === String(userId));
    }

    if(completedList) {
        if (completedPatients.length === 0) {
            // FIX: Simple list item for empty state
             completedList.innerHTML = `<li class="list-group-item text-center text-muted"><small><i class="bi bi-clipboard-check-fill me-1"></i> No patients completed today.</small></li>`;
        }
        completedPatients.forEach(patient => {
            const li = document.createElement('li');
            li.className = 'list-group-item';
            li.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">${patient.name}</h6>
                        <small class="text-muted">${patient.time} - ${formatDoctorName(patient.assigned_staff_name)}</small>
                    </div>
                    <span class="badge bg-success rounded-pill">${patient.id}</span>
                </div>
            `;
            completedList.appendChild(li);
        });
        if(document.getElementById('completedCount'))
            document.getElementById('completedCount').textContent = completedPatients.length;
    }
}

// ==========================================
// UTILITY FUNCTIONS 
// ==========================================

// Function to show a dynamic confirmation dialog (replaces native confirm)
function showConfirmation(title, message, onConfirm, onCancel = null) {
    const modalId = 'dynamicConfirmModal';
    let modalEl = document.getElementById(modalId);

    // If the modal doesn't exist, create it (assuming Bootstrap is loaded)
    if (!modalEl) {
        modalEl = document.createElement('div');
        modalEl.id = modalId;
        modalEl.className = 'modal fade';
        modalEl.tabIndex = '-1';
        modalEl.innerHTML = `
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="${modalId}Label"></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="${modalId}Body"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="${modalId}Cancel">Cancel</button>
                        <button type="button" class="btn btn-danger" id="${modalId}Confirm">Confirm</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modalEl);
    }
    
    // Update content
    document.getElementById(`${modalId}Label`).textContent = title;
    document.getElementById(`${modalId}Body`).innerHTML = message;

    const confirmBtn = document.getElementById(`${modalId}Confirm`);
    const cancelBtn = document.getElementById(`${modalId}Cancel`);

    // Clean up old listeners by replacing the elements (Bootstrap standard pattern)
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    
    const newCancelBtn = cancelBtn.cloneNode(true);
    cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);

    // Get or create modal instance
    // NOTE: Requires Bootstrap 5+ to be loaded on the page
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);

    // Set new listener for confirmation
    newConfirmBtn.addEventListener('click', function handler() {
        modalInstance.hide();
        if (typeof onConfirm === 'function') {
            onConfirm();
        }
    }, { once: true });

    // Set new listener for cancellation
    newCancelBtn.addEventListener('click', function handler() {
        modalInstance.hide();
        if (typeof onCancel === 'function') {
            onCancel();
        }
    }, { once: true });

    modalInstance.show();
}


// Set up check-in forms 
function setupCheckInForms() {
    const appointmentForm = document.getElementById('appointmentForm');
    const walkinForm = document.getElementById('walkinForm');
    
    if (appointmentForm) {
        appointmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const inputValue = document.getElementById('appointmentId').value;
            if (!inputValue) return;

            const formData = new FormData(this);
            formData.append('type','appointment');
            
            fetch('php/api_patient_checkin.php', {
                method: 'POST',
                body: formData 
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("queueNumber").innerHTML = data.quenumber
                    // NOTE: Requires checkinSuccessModal element
                    const successModal = new bootstrap.Modal(document.getElementById('checkinSuccessModal'));
                    successModal.show();
                }
            });
        });
    }
    
    if (walkinForm) {
        walkinForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const inputValue = document.getElementById('patientId').value;
            if (!inputValue) return;

            const formData = new FormData(this);
            formData.append('type','walk-in');
            
            fetch('php/api_patient_checkin.php', {
                method: 'POST',
                body: formData 
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("queueNumber").innerHTML = data.quenumber
                    // NOTE: Requires checkinSuccessModal element
                    const successModal = new bootstrap.Modal(document.getElementById('checkinSuccessModal'));
                    successModal.show();
                }
            });
        });
    }
}

// Simulate queue updates on the display screen 
function simulateQueueUpdates() {
    // Placeholder - Polling is handled on dashboard.php itself now.
    setInterval(() => {
        // console.log("Checking for queue updates...");
    }, 30000);
}


// REVISED FUNCTION: Updates the next patient info displayed inside the 'Call Patient' modal.
function updateNextPatientModalInfo() {
    const nextPatientInfoDiv = document.getElementById('nextPatientInfo');
    const callPatientBtn = document.getElementById('callPatientBtn');
    if (!nextPatientInfoDiv || !callPatientBtn) return;

    // Use the waiting queue (status 'waiting')
    const nextPatient = patients.waiting[0];

    if (nextPatient) {
        const priorityStyle = getPriorityStyle(nextPatient.priority);
        nextPatientInfoDiv.className = `alert alert-info ${priorityStyle.liClass.includes('danger') ? 'alert-danger' : priorityStyle.liClass.includes('warning') ? 'alert-warning' : 'alert-info'}`;
        
        nextPatientInfoDiv.innerHTML = `
            <p class='mb-1 fw-bold'>Next Patient:</p>
            <p class='mb-1'>Name: <strong>${nextPatient.name}</strong></p>
            <p class='mb-0'>Queue #: <strong>${nextPatient.id}</strong></p>
            <p class='mb-0'>Reason: ${nextPatient.reason}</p>
        `;
        callPatientBtn.disabled = false;
        // The queueId is now passed via data attribute on the button itself
        callPatientBtn.setAttribute('data-queue-id', nextPatient.queue_id); 
    } else {
        nextPatientInfoDiv.className = 'alert alert-secondary';
        nextPatientInfoDiv.innerHTML = "<p class='mb-0'>Queue is empty. No patient to call.</p>";
        callPatientBtn.disabled = true;
        callPatientBtn.setAttribute('data-queue-id', '');
    }
}

// Call patient functionality (UPDATED: Removed location.reload())
const callPatientBtn = document.getElementById('callPatientBtn');
if (callPatientBtn) {
    callPatientBtn.addEventListener('click', function() {
        const roomNumber = document.getElementById('roomSelect').value;
        const doctorId = document.getElementById('doctorSelect').value;
        // Fetch queueId from the button's data attribute
        const queueId = this.getAttribute('data-queue-id'); 

        if (!roomNumber || !doctorId || !queueId) {
            showAlert('warning', 'Please select a Doctor/Room and ensure the queue is not empty.');
            return;
        }
        
        // This is the original JSON fetch logic for callPatient.
        fetch('php/api_patient_checkin.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                // The API endpoint seems to be a multipurpose one
                roomNumber: roomNumber,
                doctorId: doctorId,
                queueId: queueId, 
                action: 'callPatient'
            })
        })
        .then(res => res.json())
        .then(async data => { 
            const modal = bootstrap.Modal.getInstance(document.getElementById('callPatientModal'));

            if (data.success) {
                if(modal) modal.hide();
                showAlert('success', 'Patient successfully called to Triage.');
                await fetchPatients(); // Update data
                updateDashboard(); // Refresh UI
            } else {
                if(modal) modal.hide();
                showAlert('danger', 'Update failed: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(err => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('callPatientModal'));
            if(modal) modal.hide();
            showAlert('danger', 'Call failed: ' + err.message);
        });
    });
}
// ==========================================
// WORKFLOW ACTIONS (Nurse/Doctor)
// ==========================================

// Nurse Action: Start Triage (UPDATED: Removed location.reload())
function startTriage(queueId) {
    // Sends: queue_id, status='in_progress'
    const fd = new FormData();
    fd.append('queue_id', queueId);
    fd.append('status', 'in_progress'); 

    fetch('php/api_update_status.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(async d => { 
        if(d.status === 'success') {
            showAlert('success', 'Triage started for patient. Moving to active list.');
            await fetchPatients(); 
            updateDashboard();
        } else {
            showAlert('danger', d.message); 
        }
    })
    .catch(err => {
        showAlert('danger', 'Triage start failed: ' + err.message);
    });
}

// Doctor Action: Start Consult (UPDATED: Removed location.reload())
function startConsult(queueId) {
    // Sends: queue_id, status='in_progress'
    const fd = new FormData();
    fd.append('queue_id', queueId);
    fd.append('status', 'in_progress'); 

    fetch('php/api_update_status.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(async d => { 
        if(d.status === 'success') {
            showAlert('success', 'Consultation started for patient. Moving to active list.');
            await fetchPatients(); 
            updateDashboard();
        } else {
            showAlert('danger', d.message); 
        }
    })
    .catch(err => {
        showAlert('danger', 'Consult start failed: ' + err.message);
    });
}

// Doctor/Nurse Action: Complete Visit (UPDATED: Replaced confirm() and location.reload())
function completeVisit(queueId) {
    
    // Use custom confirmation modal instead of native browser confirm()
    showConfirmation("Complete Patient Visit", "Are you sure you want to mark this visit as complete? This action is final.", () => {
        // Confirmation accepted
        // Sends: queue_id, status='completed'
        const fd = new FormData();
        fd.append('queue_id', queueId);
        fd.append('status', 'completed');

        fetch('php/api_update_status.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(async d => { 
            if(d.status === 'success') {
                showAlert('success', 'Visit marked as complete!');
                await fetchPatients(); // Update data
                updateDashboard(); // Refresh UI
            } else {
                showAlert('danger', d.message); 
            }
        })
        .catch(err => {
            showAlert('danger', 'Completion failed: ' + err.message);
        });
    });
}


function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer');
    // Safety check if alertContainer doesn't exist on the page
    if(!alertContainer) {
        console.error(`Alert (${type}): ${message}`);
        return;
    }

    const alertId = 'alert-' + Date.now();
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.role = 'alert';
    // NOTE: Assumes Bootstrap Icons are available (bi-...)
    const iconClass = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
    alert.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi ${iconClass} me-2"></i>
            <div>${message}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    alertContainer.appendChild(alert);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        // NOTE: Requires Bootstrap 5+ JS to be loaded on the page
        const bsAlert = new bootstrap.Alert(alert);
        // Check if the element is still in the DOM before closing
        if (alert.isConnected) {
            bsAlert.close();
        }
    }, 5000);
}