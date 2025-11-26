<?php
// File: refresh_staff_table.php
include_once("php/db_connect.php");

// Check database connection
if (isset($db)) {
    $outcomes = $db->query("SELECT * FROM staff ORDER BY id DESC"); 
    
    if ($outcomes && $outcomes->num_rows > 0) {
        while ($ot = $outcomes->fetch_assoc()) {
            // Sanitize output
            $staff_id = htmlspecialchars($ot['id']); 
            $names = ($ot['role'] == 'doctor') ? "Dr. " . htmlspecialchars($ot['first_name'] . " " . $ot['last_name']) : htmlspecialchars($ot['first_name'] . " " . $ot['last_name']);
            $role = htmlspecialchars($ot['role']);
            $email = htmlspecialchars($ot['email']);
            $is_active = $ot['is_active'];
            
            // Determine Badge based on status
            $status_badge = $is_active ? 
                '<span class="badge bg-success py-2 px-2">Active</span>' : 
                '<span class="badge bg-warning text-dark py-2 px-2">Deactivated</span>';

            // --- HYBRID UI: Compact View Button + Ellipsis Dropdown ---
            
            // 1. Primary Action (Directly Visible): VIEW Button (Horizontal padding px-2)
            $view_btn = "<button type='button' class='btn btn-sm btn-outline-info me-2 py-1 px-2 view-staff-btn' 
                            data-bs-toggle='modal' data-bs-target='#viewStaffDetailsModal' data-id='{$staff_id}'
                            data-bs-toggle='tooltip' data-bs-placement='top' title='View Staff Details'>
                            <i class='bi bi-eye'></i> View 
                        </button>"; 
                        
            // 2. Secondary Action Link: Edit
            $edit_link = "<a href='#' class='dropdown-item edit-staff-btn' 
                            data-bs-toggle='modal' data-bs-target='#editStaffDetailsModal' data-id='{$staff_id}'>
                            <i class='bi bi-pencil-square me-2 text-primary'></i> Edit Details
                        </a>";
            
            // 3. Contextual Status Link: Activate or Deactivate
            $toggle_status_link = $is_active ? 
                "<a href='#' class='dropdown-item text-danger ajax-action-btn' 
                    data-id='$staff_id' data-action='deactivate' data-name='$names'><i class='bi bi-power me-2'></i> Deactivate Account</a>" : 
                "<a href='#' class='dropdown-item text-success ajax-action-btn' 
                    data-id='$staff_id' data-action='activate' data-name='$names'><i class='bi bi-check-circle me-2'></i> Activate Account</a>";

            // 4. Destructive Action Link: Delete
            $delete_link = "<a href='#' class='dropdown-item text-danger delete-staff-btn' 
                                data-id='$staff_id' data-name='$names'>
                                <i class='bi bi-trash3 me-2 text-danger'></i> Delete Staff
                            </a>";

            // Output the new table row HTML
echo "
<tr>
    <td>{$names}</td>
    <td>{$role}</td>
    <td>{$email}</td>
    <td>{$status_badge}</td>
    <td>
        {$view_btn}
        
        <div class='dropdown d-inline-block'> 
            <button class='btn btn-sm btn-light px-1' type='button' data-bs-toggle='dropdown' aria-expanded='false'
                data-bs-toggle='tooltip' data-bs-placement='top' title='More Actions'>
                <i class='bi bi-three-dots-vertical'></i>
            </button>
            <ul class='dropdown-menu dropdown-menu-end'>
                <li>{$edit_link}</li>
                <li>{$toggle_status_link}</li>
                <li>{$delete_link}</li>
            </ul>
        </div>
    </td>
</tr>";
        }
    } else {
        echo '<tr><td colspan="5" class="text-center">No staff members found.</td></tr>';
    }
} else {
    echo '<tr><td colspan="5" class="text-danger text-center">Database connection error.</td></tr>';
}
?>