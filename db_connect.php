<?php
// File: php/db_connect.php
// Purpose: Establish database connection ($db) and ensure no output contaminates JSON APIs.

// 1. Global database connection variables
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "db_pqms";

// 2. Establish Main Connection
// Use @ to suppress connection errors, allowing the calling script to handle the error cleanly.
// Connection status is checked via $db->connect_error in the calling script.
$db = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// --- Only proceed with setup if the connection was attempted (not necessarily successful yet) ---
if (isset($db) && !$db->connect_errno) {
    
    // 3. Handle Database/Table Setup
    // Use select_db first, as the database might already exist
    if (!$db->select_db($DB_NAME)) {
        // If selection fails, try to create it
        if (!$db->query("CREATE DATABASE IF NOT EXISTS `$DB_NAME`")) {
            // If creation fails, log the error (but do not output it)
            error_log("Failed to create database: " . $db->error);
        } else {
            // Select the database explicitly after (potential) creation
            $db->select_db($DB_NAME);
        }
    }

    // 4. Include Migration (Only if database is selected)
    // NOTE: __DIR__ is relative to this file's location (php folder)
    // Assuming migration.php handles table creation and is safe (no output).
    if ($db->select_db($DB_NAME)) {
        include_once(__DIR__ . "/migration.php");
    }
}


// 5. Database Functions - executeQuery for Prepared Statements (Ensures no output)

/**
 * Executes a database query, supporting both simple queries and prepared statements.
 * @param string $query The SQL query string.
 * @param array $params An array of parameters for prepared statements.
 * @param bool $returnInsertId True to return the ID of the last inserted row.
 * @return mixed The result object, insert ID, or false on failure.
 */
function executeQuery($query, $params = [], $returnInsertId = false) {
    global $db;
    
    // Check if the connection is active before proceeding
    if ($db->connect_errno) {
        error_log("Attempted to run query on a failed database connection.");
        return false;
    }

    // 1. Simple query if no parameters (e.g., SELECT without user input)
    if (empty($params)) {
        $result = $db->query($query);
        if ($db->error) {
            error_log("Simple Query execution failed: " . $db->error . " Query: " . $query);
            return false;
        }
        return $returnInsertId ? $db->insert_id : $result;
    }
    
    // 2. Prepared Statement for safe data insertion (e.g., Patient Registration)
    if ($stmt = $db->prepare($query)) {
        
        $types = str_repeat('s', count($params)); 
        
        // Use the splat operator to bind parameters dynamically
        $stmt->bind_param($types, ...$params); 
        
        if ($stmt->execute()) {
            $insert_id = $db->insert_id;
            $stmt->close();
            return $returnInsertId ? $insert_id : true;
        } else {
            // Execution failure (e.g., constraint violation)
            error_log("Prepared Statement execution failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
    } else {
        // Prepare failure (e.g., invalid SQL syntax)
        error_log("Failed to prepare query: " . $db->error . " Query: " . $query);
        return false;
    }
}
// Note: Closing PHP tag is intentionally omitted to prevent accidental whitespace output.