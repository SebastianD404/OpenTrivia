<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PQMS - Staff Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
         body {
            background-color: #f8f9fa;
            display: flex;
            min-height: 100vh;
            align-items: center;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
        }
        .auth-container {
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        .auth-header {
            display: flex;
            border-bottom: 1px solid #eee;
        }
        .auth-tab {
            flex: 1;
            text-align: center;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .auth-tab.active {
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
        }
        .auth-tab:not(.active):hover {
            background-color: #f8f9fa;
        }
        .auth-body {
            padding: 2rem;
        }
        .auth-form {
            display: none;
        }
        .auth-form.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 8px;
        }
        .btn-auth {
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 500;
        }
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #6c757d;
        }
        .divider::before, .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #dee2e6;
        }
        .divider::before {
            margin-right: 1rem;
        }
        .divider::after {
            margin-left: 1rem;
        }
        .alert-position {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            width: 350px;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 0rem;
            padding-top: 0.5rem;
        }
        .logo {
            font-weight: 700;
            font-size: 2rem;
            color: #0d6efd;
            margin-bottom: 0.5rem;
        }
        .logo-subtitle {
            color: #5f6468ff;
            font-size: 1rem;
        }
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
    </style>
</head>
<body>
    <div class="alert-position" id="alertContainer"></div>

    <div class="auth-container">
        <div class="logo-container">
            <div class="logo">PQMS</div>
            <div class="logo-subtitle">Staff Portal</div>
        </div>
        
        <div class="auth-header">
            <div class="auth-tab active" id="loginTab" onclick="switchTab('login')">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </div>
            <div class="auth-tab" id="registerTab" onclick="switchTab('register')">
                <i class="bi bi-person-plus me-2"></i>Register
            </div>
        </div>
        
        <div class="auth-body">
            <form id="loginForm" class="auth-form active">
                <div class="mb-3">
                    <label for="loginEmail" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="loginEmail" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="loginPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="loginPassword" name="password" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">Remember me</label>
                    <a href="#" class="float-end text-decoration-none">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-primary btn-auth w-100 mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </button>
                <div class="divider">or</div>
                <button type="button" class="btn btn-outline-secondary btn-auth w-100" onclick="switchTab('register')">
                    Create new account
                </button>
            </form>
            
            <form id="registerForm" class="auth-form">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="regFirstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="regFirstName" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="regLastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="regLastName" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="regEmail" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="regEmail" required>
                </div>
                <div class="mb-3">
                    <label for="regPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="regPassword" required minlength="8">
                </div>
                <div class="mb-3">
                    <label for="regConfirmPassword" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="regConfirmPassword" required>
                </div>
                <div class="mb-3">
                    <label for="regRole" class="form-label">Role</label>
                    <select class="form-select" id="regRole" required>
                        <option value="" disabled selected>Select your role</option>
                        <option value="doctor">Doctor</option>
                        <option value="nurse">Nurse</option>
                        <option value="receptionist">Receptionist</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="agreeTerms" required>
                    <label class="form-check-label" for="agreeTerms">I agree to the <a href="#">terms and conditions</a></label>
                </div>
                <button type="submit" class="btn btn-primary btn-auth w-100 mb-3">
                    <i class="bi bi-person-plus me-2"></i>Register
                </button>
                <div class="divider">or</div>
                <button type="button" class="btn btn-outline-secondary btn-auth w-100" onclick="switchTab('login')">
                    Already have an account? Login
                </button>
            </form>
        </div>
    </div>

    <script>
        // Alert function
        function showAlert(type, message) {
            const alertContainer = document.getElementById('alertContainer');
            
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.role = 'alert';
            alert.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} me-2"></i>
                    <div>${message}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            alertContainer.appendChild(alert);
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        }

        function switchTab(tabName) {
            // Update tabs
            document.getElementById('loginTab').classList.toggle('active', tabName === 'login');
            document.getElementById('registerTab').classList.toggle('active', tabName === 'register');
            
            // Update forms
            document.getElementById('loginForm').classList.toggle('active', tabName === 'login');
            document.getElementById('registerForm').classList.toggle('active', tabName === 'register');
        }

        // --- FIXED LOGIN LOGIC START ---
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const loginButton = this.querySelector('button[type="submit"]');
            const originalText = loginButton.innerHTML;
            
            // 1. Get Values
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;

            // 2. Prepare Data for PHP
            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);

            // 3. UI Loading State
            loginButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging in...';
            loginButton.disabled = true;
            
            // 4. Send to Backend (api_login.php)
            fetch('php/api_login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Check if the response is actually JSON, otherwise throw error
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.message || 'Server Error'); });
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    // SUCCESS: Show message and redirect
                    showAlert('success', 'Login successful! Redirecting...');
                    
                    setTimeout(() => {
                        window.location.href = "dashboard.php"; // Redirects to dashboard
                    }, 1000);
                } else {
                    // ERROR from API logic (e.g. wrong password)
                    throw new Error(data.message || 'Login failed');
                }
            })
            .catch(error => {
                // Handle network errors or API errors
                console.error('Error:', error);
                showAlert('danger', error.message || 'An error occurred while connecting to the server.');
                
                // Reset button
                loginButton.innerHTML = originalText;
                loginButton.disabled = false;
            });
        });
        // --- FIXED LOGIN LOGIC END ---

        // REAL REGISTRATION LOGIC
            document.getElementById('registerForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // 1. Validate Passwords match on client side
                const password = document.getElementById('regPassword').value;
                const confirmPassword = document.getElementById('regConfirmPassword').value;
                
                if (password !== confirmPassword) {
                    showAlert('danger', 'Passwords do not match!');
                    return;
                }
                
                const registerButton = this.querySelector('button[type="submit"]');
                const originalText = registerButton.innerHTML;
                
                // 2. Prepare Data
                // Note: The keys here ('first_name', etc.) must match $_POST in the PHP file
                const formData = new FormData();
                formData.append('first_name', document.getElementById('regFirstName').value);
                formData.append('last_name', document.getElementById('regLastName').value);
                formData.append('email', document.getElementById('regEmail').value);
                formData.append('password', password);
                formData.append('role', document.getElementById('regRole').value);

                // 3. UI Loading State
                registerButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Registering...';
                registerButton.disabled = true;
                
                // 4. Send to Backend
                fetch('php/api_register_staff.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showAlert('success', data.message);
                        // Clear form and switch to login tab
                        document.getElementById('registerForm').reset();
                        setTimeout(() => {
                            switchTab('login');
                        }, 1500);
                    } else {
                        showAlert('danger', data.message || 'Registration failed.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', 'An error occurred while connecting to the server.');
                })
                .finally(() => {
                    // Reset button
                    registerButton.innerHTML = originalText;
                    registerButton.disabled = false;
                });
            });

        // Initialize Bootstrap tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>