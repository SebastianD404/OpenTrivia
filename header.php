<?php
// Ensure session is started, as this file needs session data
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fetch session variables safely
$current_page = basename($_SERVER['PHP_SELF']);
// Use 'guest' as the default role if not set in the session
$current_role = $_SESSION['user_role'] ?? 'guest'; 
$user_name = $_SESSION['first_name'] ?? 'Guest';

// Define if the user is staff for simpler conditional checks in HTML
$is_staff = ($current_role !== 'guest');

// Determine the URL for the PQMS logo/brand
if ($is_staff) {
    // Staff/Logged-in users go to the staff dashboard
    $pqms_home_url = "dashboard.php";
} else {
    // Guests (public view) go to the main landing/index page
    $pqms_home_url = "index.php"; // Assuming index.php is the public landing page
}


// Format the name: capitalize first letter, rest lowercase
$formatted_name = ucfirst(strtolower($user_name));

// Determine the prefix (Dr. or none) based on role
$prefix = ($current_role === 'doctor') ? 'Dr. ' : '';


function isActive($page_name, $current_page_var) {
    if ($page_name === $current_page_var) {
        return 'active';
    }
    return '';
}

?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container d-flex align-items-center">
        
        <a class="navbar-brand fw-bold" href="<?php echo $pqms_home_url; ?>">PQMS</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav me-auto d-flex align-items-center">
                
                <?php if ($is_staff): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo isActive('dashboard.php', $current_page); ?>" 
                    href="dashboard.php">Dashboard</a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link <?php echo isActive('queue.php', $current_page); ?>" 
                    href="queue.php">Queue Display</a>
                </li>

                <?php if ($current_role === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo isActive('admin.php', $current_page); ?>" 
                    href="admin.php">Admin</a>
                </li>
                <?php endif; ?>
            </ul>

            <?php if ($is_staff): ?>
            <div class="d-flex align-items-center">
                <span class="text-white me-3 fw-semibold text-nowrap">
                    Hello, <?php echo $prefix . $formatted_name; ?>
                </span>
                <button class="btn btn-outline-light btn-sm text-nowrap" id="logoutBtn">Logout</button>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
</nav>