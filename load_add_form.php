<?php
// load_add_form.php
// Outputs the HTML form for adding a new staff member.
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    exit('Unauthorized access.');
}
// Set default date for the Date Hired field
$default_date = date('Y-m-d');
?>

<form id="addStaffForm">
    
    <div class="mb-3">
        <label for="firstName" class="form-label">First Name</label>
        <input type="text" class="form-control" id="firstName" name="first_name" required>
    </div>

    <div class="mb-3">
        <label for="lastName" class="form-label">Last Name</label>
        <input type="text" class="form-control" id="lastName" name="last_name" required>
    </div>
    
    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" class="form-control" id="email" name="email" required>
        <div class="form-text">This will also be used for login.</div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Temporary Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
        <div class="form-text">The staff member must change this immediately upon first login.</div>
    </div>
    
    <div class="mb-3">
        <label for="role" class="form-label">Role / Permission Level</label>
        <select class="form-select" id="role" name="role" required>
            <option value="" disabled selected>Select Role</option>
            <option value="admin">Admin</option>
            <option value="doctor">Doctor</option>
            <option value="receptionist">Receptionist</option>
            </select>
    </div>
    
    <div class="mb-3">
        <label for="phone" class="form-label">Phone Number (Optional)</label>
        <input type="text" class="form-control" id="phone" name="phone">
    </div>

    <div class="mb-3">
        <label for="dateHired" class="form-label">Date Hired</label>
        <input type="date" class="form-control" id="dateHired" name="date_hired" value="<?php echo $default_date; ?>" required>
    </div>

    <div class="d-grid mt-4">
        <button type="submit" class="btn btn-primary">
            Save New Staff Member
        </button>
    </div>
</form>