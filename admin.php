<?php
session_start();

// PREVENT CACHING
header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 

// SECURITY CHECKS
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
if ($_SESSION['user_role'] !== 'admin') { header("Location: dashboard.php"); exit(); }

include_once("php/db_connect.php");
include("header.php");

// --- SYSTEM STATUS CALCULATIONS (Assuming these variables are available) ---
$db_status_text = isset($db) ? "Connected" : "Disconnected";
$db_status_color = isset($db) ? "text-success" : "text-danger";
$db_icon = isset($db) ? "bi-check-circle-fill" : "bi-x-circle-fill";

$queue_count = 0;
if(isset($db)) {
    $q_query = $db->query("SELECT COUNT(*) as count FROM queue WHERE status = 'waiting'");
    if($q_query) $queue_count = $q_query->fetch_assoc()['count'];
}
$queue_status = ($queue_count > 0) ? "Active ($queue_count waiting)" : "Idle";
$queue_color_icon = ($queue_count > 0) ? "text-success" : "text-secondary";

$patients_today = 0;
if(isset($db)) {
    $today = date('Y-m-d');
    $p_query = $db->query("SELECT COUNT(*) as count FROM checkins WHERE DATE(created_at) = '$today'");
    if($p_query) $patients_today = $p_query->fetch_assoc()['count'];
}

$active_docs = 0;
if(isset($db)) {
    $d_query = $db->query("SELECT COUNT(DISTINCT called_by) as count FROM queue WHERE status = 'in_progress'");
    if($d_query) $active_docs = $d_query->fetch_assoc()['count'];
}

$sys_version = "2.1.4"; 
$server_time = date("h:i A"); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PQMS - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
</head>
<body>
    
    <div class="container my-5">
        <div class="row mb-4"><div class="col"><h2 class="fw-bold">System Administration</h2></div></div>
        
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card h-100 admin-card">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-primary mb-3 mx-auto"><i class="bi bi-people text-white" style="font-size: 1.5rem;"></i></div>
                        <h5 class="card-title">Staff Management</h5>
                        <p class="card-text text-muted">Add, edit, or remove staff members and their permissions</p>
                        <a href="#" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#staffModal">Manage</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card h-100 admin-card">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-success mb-3 mx-auto"><i class="bi bi-calendar-check text-white" style="font-size: 1.5rem;"></i></div>
                        <h5 class="card-title">Appointment Settings</h5>
                        <p class="card-text text-muted">Configure appointment types, durations, and availability</p>
                        <a href="#" class="btn btn-outline-success">Configure</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card h-100 admin-card">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-info mb-3 mx-auto"><i class="bi bi-tv text-white" style="font-size: 1.5rem;"></i></div>
                        <h5 class="card-title">Display Settings</h5>
                        <p class="card-text text-muted">Customize queue display screens and notifications</p>
                        <a href="#" class="btn btn-outline-info">Customize</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card h-100 admin-card">
                    <div class="card-body text-center">
                        <div class="icon-circle bg-warning mb-3 mx-auto"><i class="bi bi-graph-up text-white" style="font-size: 1.5rem;"></i></div>
                        <h5 class="card-title">Reports & Analytics</h5>
                        <p class="card-text text-muted">View clinic performance metrics and patient flow data</p>
                        <a href="#" class="btn btn-outline-warning">View Reports</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <div class="card">
                    <div class="card-header bg-white"><h5 class="card-title mb-0">System Status</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="system-status-item"><i class="bi bi-people-fill <?php echo $queue_color_icon; ?> me-2"></i><span>Queue Status: <strong><?php echo $queue_status; ?></strong></span></div>
                                <div class="system-status-item"><i class="bi <?php echo $db_icon; ?> <?php echo $db_status_color; ?> me-2"></i><span>Database: <strong><?php echo $db_status_text; ?></strong></span></div>
                                <div class="system-status-item"><i class="bi bi-person-check-fill text-primary me-2"></i><span>Patients Today: <strong><?php echo $patients_today; ?></strong></span></div>
                            </div>
                            <div class="col-md-6">
                                <div class="system-status-item"><i class="bi bi-activity text-success me-2"></i><span>Active Doctors: <strong><?php echo $active_docs; ?></strong></span></div>
                                <div class="system-status-item"><i class="bi bi-cpu text-secondary me-2"></i><span>Version: <strong><?php echo $sys_version; ?></strong></span></div>
                                <div class="system-status-item"><i class="bi bi-clock text-info me-2"></i><span>Server Time: <strong><?php echo $server_time; ?></strong></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="staffModal" tabindex="-1">
        <div class="modal-dialog modal-xl"> 
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Staff Management</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr><th>Name</th><th>Role</th><th>Email</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody id="staffTableBody">
                                </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">Add New Staff</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="viewStaffDetailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn btn-link text-secondary p-0 me-2 back-arrow-btn" id="backToStaffListIcon" aria-label="Back to Staff List">
                        <i class="bi bi-arrow-left" style="font-size: 1.5rem;"></i> 
                    </button>
                    <h5 class="modal-title ms-auto">Staff Details</h5> 
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="staffDetailsContent">
                    <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
                </div>
                <div class="modal-footer">
                    <a href="#" id="editStaffLinkView" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editStaffDetailsModal">Edit Staff</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editStaffDetailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Staff Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="editStaffContent">
                    <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="backToStaffListFromEdit">Go Back</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addStaffModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Add New Staff</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="addStaffContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2">Loading form...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- ELEMENT REFERENCES ---
            const staffModal = document.getElementById('staffModal');
            const viewStaffModal = document.getElementById('viewStaffDetailsModal');
            const editStaffModal = document.getElementById('editStaffDetailsModal');
            const addStaffModal = document.getElementById('addStaffModal'); 
            
            // EXISTING REFERENCE: Back Icon Button (from Details Modal)
            const backToStaffListIcon = document.getElementById('backToStaffListIcon'); 

            // NEW REFERENCE: Back Button (from Edit Modal)
            const backToStaffListFromEdit = document.getElementById('backToStaffListFromEdit'); 

            const staffDetailsContent = document.getElementById('staffDetailsContent');
            const editStaffContent = document.getElementById('editStaffContent');
            const addStaffContent = document.getElementById('addStaffContent'); 
            const staffTableBody = document.getElementById('staffTableBody');

            // 1. REFRESH TABLE FUNCTION
            function refreshStaffTable() {
                fetch('refresh_staff_table.php')
                    .then(response => response.text())
                    .then(html => { staffTableBody.innerHTML = html; })
                    .catch(err => {
                        staffTableBody.innerHTML = '<tr><td colspan="5" class="text-danger text-center">Error loading staff data. Check refresh_staff_table.php path.</td></tr>';
                        console.error('Refresh error:', err);
                    });
            }

            // Load table when main modal opens
            staffModal.addEventListener('show.bs.modal', refreshStaffTable);
            
            // --- EXISTING LOGIC: BACK ICON (Details to List) ---
            backToStaffListIcon.addEventListener('click', function() {
                // 1. Hide the current 'Staff Details' modal
                const viewModalInstance = bootstrap.Modal.getInstance(viewStaffModal);
                if (viewModalInstance) viewModalInstance.hide();
                
                // 2. Show the parent 'Staff Management' modal
                const staffModalInstance = new bootstrap.Modal(staffModal);
                staffModalInstance.show();

                // 3. Since we are coming back, ensure the staff list is fresh
                refreshStaffTable(); 
            });
            // --- END EXISTING LOGIC ---

            // --- NEW LOGIC: GO BACK BUTTON (Edit to List) ---
            if (backToStaffListFromEdit) {
                backToStaffListFromEdit.addEventListener('click', function() {
                    // 1. Hide the current 'Edit Staff Details' modal
                    const editModalInstance = bootstrap.Modal.getInstance(editStaffModal);
                    if (editModalInstance) editModalInstance.hide();
                    
                    // 2. Show the parent 'Staff Management' modal
                    const staffModalInstance = new bootstrap.Modal(staffModal);
                    staffModalInstance.show();

                    // 3. Since we are coming back, ensure the staff list is fresh
                    refreshStaffTable(); 
                });
            }
            // --- END NEW LOGIC ---


            // 2. VIEW DETAILS LOGIC
            viewStaffModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget; 
                const staffId = button.getAttribute('data-id');
                // Ensure the Edit Staff link inside the details modal has the correct data-id
                const editLink = document.getElementById('editStaffLinkView');
                if (editLink) {
                    editLink.setAttribute('data-id', staffId);
                } else {
                    console.error("Edit Staff Link not found in Details Modal.");
                }
                
                staffDetailsContent.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p>Loading...</p></div>';
                
                fetch(`fetch_staff_details.php?id=${staffId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            staffDetailsContent.innerHTML = `
                                <p><strong>Name:</strong> ${data.full_name}</p>
                                <p><strong>Role:</strong> ${data.role}</p>
                                <p><strong>Email:</strong> ${data.email}</p>
                                <p><strong>Phone:</strong> ${data.phone || 'N/A'}</p>
                                <p><strong>Date Hired:</strong> ${data.date_hired || 'N/A'}</p>
                                <p><strong>Status:</strong> <span class="badge ${data.status === 'Active' ? 'bg-success' : 'bg-warning'}">${data.status}</span></p>`;
                        } else {
                            staffDetailsContent.innerHTML = `<div class="alert alert-danger">Error: ${data.error}</div>`;
                        }
                    })
                    .catch(err => {
                        staffDetailsContent.innerHTML = `<div class="alert alert-danger">Network error loading staff details.</div>`;
                        console.error('Fetch Details Error:', err);
                    });
            });

            // 3. EDIT FORM LOGIC
            editStaffModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget; 
                // Determine staffId from the button that triggered the modal
                const staffId = button.getAttribute('data-id'); 
                
                editStaffContent.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
                
                fetch(`load_edit_form.php?id=${staffId}`)
                    .then(response => response.text())
                    .then(html => {
                        editStaffContent.innerHTML = html;
                        const form = document.getElementById('editStaffForm');
                        if (form) form.addEventListener('submit', handleEditSubmission);
                    });
            });

            // 4. HANDLE EDIT SUBMISSION (AJAX - STAYS OPEN & REFRESHES TABLE)
            function handleEditSubmission(event) {
                event.preventDefault(); 
                const form = event.target;
                const btn = form.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.textContent = 'Saving...';

                const formData = new FormData(form);
                
                fetch('php/update_staff.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    // Create and display success/error message
                    const msgDiv = document.createElement("div");
                    msgDiv.className = `alert ${data.success ? 'alert-success' : 'alert-danger'} temp-alert mb-3`;
                    msgDiv.textContent = data.message;
                    
                    // Clean old alerts
                    editStaffContent.querySelectorAll('.temp-alert').forEach(e => e.remove());
                    editStaffContent.prepend(msgDiv);

                    btn.disabled = false;
                    btn.textContent = "Save Changes";

                    if (data.success) {
                        // FIX: Scroll to the top to see the success message
                        editStaffContent.scrollTop = 0;

                        // REFRESH THE BACKGROUND TABLE
                        refreshStaffTable(); 
                        
                        // Auto-hide the alert after 3 seconds (keeping the modal open)
                        setTimeout(() => {
                            const currentMsg = document.querySelector('.temp-alert'); 
                            if (currentMsg) {
                                currentMsg.classList.add("fade");
                                currentMsg.style.opacity = "0";
                                setTimeout(() => currentMsg.remove(), 500);
                            }
                        }, 3000);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Submission failed. Check network response for errors.");
                    btn.disabled = false;
                    btn.textContent = "Save Changes";
                });
            }

            // 5. DEACTIVATE/ACTIVATE LOGIC (AJAX - Instant Refresh)
            staffModal.addEventListener('click', function(event) {
                const btn = event.target.closest('.ajax-action-btn');
                if (btn) {
                    event.preventDefault();
                    const id = btn.getAttribute('data-id');
                    const action = btn.getAttribute('data-action');
                    const name = btn.getAttribute('data-name');
                    
                    if (confirm(`Are you sure you want to ${action.toUpperCase()} ${name}?`)) {
                        btn.disabled = true;
                        btn.textContent = '...';
                        
                        fetch(`deactivate_staff.php?id=${id}&action=${action}`)
                            .then(response => response.json())
                            .then(data => {
                                if(data.success) {
                                    refreshStaffTable(); // Update table instantly
                                } else {
                                    alert(data.message);
                                    btn.disabled = false;
                                    btn.textContent = action;
                                }
                            })
                            .catch(err => {
                                console.error('Deactivate/Activate Failed:', err);
                                alert('An error occurred during status change. Check server logs.');
                                btn.disabled = false;
                                btn.textContent = action; // Reset button text on error
                            });
                    }
                }
            });
            
            // 6. DELETE LOGIC (AJAX - Instant Refresh)
            staffModal.addEventListener('click', function(event) {
                const btn = event.target.closest('.delete-staff-btn');
                if (btn) {
                    event.preventDefault();
                    const id = btn.getAttribute('data-id');
                    const name = btn.getAttribute('data-name');
                    
                    if (confirm(`WARNING: Are you sure you want to PERMANENTLY delete staff member ${name}? This cannot be undone.`)) {
                        btn.disabled = true;
                        btn.textContent = '...';
                        
                        fetch(`php/delete_staff.php?id=${id}`)
                            .then(response => response.json())
                            .then(data => {
                                if(data.success) {
                                    alert(data.message); // Show confirmation message
                                    refreshStaffTable(); // Update table instantly
                                } else {
                                    alert(`Deletion failed: ${data.message}`);
                                    btn.disabled = false;
                                    btn.textContent = 'Delete';
                                }
                            })
                            .catch(err => {
                                console.error('Delete Failed:', err);
                                alert('An error occurred during deletion. Check server logs.');
                                btn.disabled = false;
                                btn.textContent = 'Delete'; // Reset button text on error
                            });
                    }
                }
            });

            // 7. NEW ADD STAFF LOGIC 
            // Load the form when the Add Modal opens
            addStaffModal.addEventListener('show.bs.modal', function () {
                addStaffContent.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading form...</p></div>';

                fetch(`load_add_form.php`)
                    .then(response => response.text())
                    .then(html => {
                        addStaffContent.innerHTML = html;
                        const form = document.getElementById('addStaffForm');
                        if (form) form.addEventListener('submit', handleAddSubmission);
                    })
                    .catch(err => {
                        addStaffContent.innerHTML = `<div class='alert alert-danger'>Error loading form. Check load_add_form.php path.</div>`;
                        console.error('Add Form Load Error:', err);
                    });
            });

            // 8. HANDLE ADD SUBMISSION (AJAX)
            function handleAddSubmission(event) {
                event.preventDefault(); 
                const form = event.target;
                const btn = form.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.textContent = 'Saving...';

                const formData = new FormData(form);
                
                fetch('php/save_staff.php', { method: 'POST', body: formData })
                .then(response => {
                    // Check if the response is not ok (e.g., 404, 500)
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP error! status: ${response.status}. Response text: ${text.substring(0, 100)}...`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    // Create alert message
                    const alertType = data.success ? "alert-success" : "alert-danger";
                    const msgDiv = document.createElement("div");
                    msgDiv.className = `alert ${alertType} temp-alert mb-3`;
                    msgDiv.textContent = data.message;
                    
                    // Clean old alerts
                    addStaffContent.querySelectorAll('.temp-alert').forEach(e => e.remove());
                    addStaffContent.prepend(msgDiv);

                    btn.disabled = false;
                    btn.textContent = "Save New Staff Member";

                    if (data.success) {
                        // Scroll to the top to show the success message
                        addStaffContent.scrollTop = 0;
                        
                        // Refresh the background Staff List
                        refreshStaffTable(); 
                        
                        // Clear the form fields for another entry
                        form.reset(); 

                        // Auto-hide the alert
                        setTimeout(() => {
                            const currentMsg = addStaffContent.querySelector('.temp-alert');
                            if (currentMsg) currentMsg.remove();
                        }, 3000);
                    }
                })
                .catch(err => {
                    console.error('Add Staff Submission Failed:', err);
                    btn.disabled = false;
                    btn.textContent = "Save New Staff Member";
                    
                    let errorMsg = "Staff addition failed. Check console for details (Network or JSON Parse Error).";
                    if (err.message && err.message.includes("Unexpected token '<'")) {
                        errorMsg = "Staff addition failed. The server returned a PHP error/warning instead of a JSON response. **Action: Check 'php/save_staff.php' for missing includes or fatal errors.**";
                    }
                    if (err.message && err.message.includes("HTTP error!")) {
                        errorMsg = `Staff addition failed: ${err.message}`;
                    }
                    
                    // Display the error message in the modal body
                    const msgDiv = document.createElement("div");
                    msgDiv.className = `alert alert-danger temp-alert mb-3`;
                    msgDiv.textContent = errorMsg;
                    addStaffContent.querySelectorAll('.temp-alert').forEach(e => e.remove());
                    addStaffContent.prepend(msgDiv);
                    addStaffContent.scrollTop = 0; // Scroll to show the error
                });
            }
            // --- END NEW ADD STAFF LOGIC ---
        });
    </script>
</body>
</html>