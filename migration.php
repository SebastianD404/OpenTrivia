<?php
/**
 * Database Migration Script for PQMS
 * This file creates all necessary tables for the Patient Queue Management System
 * NOTE: This file is designed to be included by db_connect.php.
 */

// We assume $db is available from db_connect.php
global $db;

// Array of table creation queries
$migrations = [
    // Staff table
    "CREATE TABLE IF NOT EXISTS staff (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'doctor', 'nurse', 'receptionist') NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        phone VARCHAR(20) NULL,
        date_hired DATE NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // Checkin type table (The rest of your table definitions are fine, omitted for brevity)
    "CREATE TABLE IF NOT EXISTS checkin_types (
        id TINYINT PRIMARY KEY,
        type_name VARCHAR(20) NOT NULL,
        description VARCHAR(255)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // Patients table
    "CREATE TABLE IF NOT EXISTS patients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        date_of_birth DATE NOT NULL,
        gender VARCHAR(20) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        address VARCHAR(20) NOT NULL,
        consent BOOLEAN DEFAULT FALSE, 
        is_registered BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        reason_for_visit TEXT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // Appointment types table
    "CREATE TABLE IF NOT EXISTS appointment_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        duration INT NOT NULL COMMENT 'Duration in minutes',
        description TEXT,
        is_active BOOLEAN DEFAULT TRUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // CRITICAL: checkins table (for scheduled appointments, required by api_get_queue.php)
    "CREATE TABLE IF NOT EXISTS checkins (
        checkin_id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        appointment_type_id INT NULL,
        scheduled_time TIME NOT NULL,
        appointment_date DATE NOT NULL,
        reason VARCHAR(255) NULL,
        status ENUM('scheduled', 'checked_in', 'cancelled', 'completed') DEFAULT 'scheduled',
        FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // CRITICAL: queue_list table (for walk-ins and checked-in patients, required by api_get_queue.php)
    "CREATE TABLE IF NOT EXISTS queue_list (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        checkin_id INT NULL, -- Links to checkins table if it was an appointment
        token_number INT NOT NULL UNIQUE,
        type ENUM('walk-in', 'appt') NOT NULL,
        status ENUM('waiting', 'consulting', 'done', 'missed') DEFAULT 'waiting',
        priority ENUM('normal', 'high') DEFAULT 'normal',
        check_in_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        assigned_staff_id INT NULL,
        room_number VARCHAR(10) NULL,
        FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
        FOREIGN KEY (assigned_staff_id) REFERENCES staff(id) ON DELETE SET NULL,
        UNIQUE KEY (token_number, assigned_staff_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
];


// Execute migrations
try {
    
    // Execute all CREATE TABLE queries first
    foreach ($migrations as $migration) {
        if (!$db->query($migration)) {
            // Log the error internally but DO NOT output to the browser
            error_log("Migration failed on table creation: " . $db->error);
        }
    }
    
    // Insert initial data (optional)
    $initialData = [
        "INSERT IGNORE INTO staff (first_name, last_name, email, password, role) 
         VALUES ('Admin', 'User', 'admin@clinic.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin')",
        
        "INSERT IGNORE INTO staff (first_name, last_name, email, password, role) 
         VALUES ('Patrick', 'Mvuma', 'mvumapatrick@gmail.com', '" . password_hash('1234554321', PASSWORD_DEFAULT) . "', 'doctor')",

        // Add your other initial data inserts here...
    ];
    
    foreach ($initialData as $data) {
        // Staff insert logic
        if (strpos($data, 'INTO staff') !== false) {
            preg_match("/\('([^']+)', '([^']+)', '([^']+)',/", $data, $matches);
            $email = $matches[3] ?? null;
            if ($email) {
                // Check if staff member already exists by email
                if ($stmt = $db->prepare("SELECT COUNT(*) FROM staff WHERE email = ?")) {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $stmt->bind_result($count);
                    $stmt->fetch();
                    $stmt->close();
                    if ($count == 0) {
                        // Insert only if not found
                        if (!$db->query($data)) {
                            // Log the error internally but DO NOT output to the browser
                            error_log("Migration failed on staff insert: " . $db->error);
                        }
                    }
                } else {
                    error_log("Migration failed to prepare staff check: " . $db->error);
                }
            }
        } else {
            // Execute all other non-staff inserts
            if (!$db->query($data)) {
                 // Log the error internally but DO NOT output to the browser
                 error_log("Migration failed on initial data insert: " . $db->error);
            }
        }
    }
    
} catch(\Throwable $e) { 
    // CRITICAL FIX: DO NOT output plain text with 'die()'. Log the error and allow it to fail gracefully.
    error_log("FATAL Migration failed: " . $e->getMessage());
    // Re-throw the exception so db_connect.php or api_register_patient.php can handle the error.
    throw $e; 
}
// Note: Closing PHP tag is intentionally omitted to prevent accidental whitespace output.