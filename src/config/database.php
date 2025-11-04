<?php
// src/config/database.php

// Define database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); 
define('DB_PASSWORD', ''); // <-- CHANGE THIS
define('DB_NAME', 'crud_auth_db');

// CHANGE THIS VALUE TO true OR false
$maintenance_mode = false; 
$maintenance_page = __DIR__ . '/maintenance_page.html'; 

if ($maintenance_mode === true) {
    // 1. Send 503 Service Unavailable header
    header('HTTP/1.1 503 Service Temporarily Unavailable');
    header('Retry-After: 3600'); // Optional: Tell search engines to retry in 1 hour (3600 seconds)
    
    // 2. Output the maintenance page content
    if (file_exists($maintenance_page)) {
        readfile($maintenance_page);
    } else {
        echo '<h1>Website is down for maintenance.</h1>';
    }

    // 3. Stop the script immediately
    exit();
}
// $useURL = "https://domainname.com/"; // change this part once deployed on hosting for images links e.g https://basc.edu.ph/
$useURL = "http://localhost:3000/";
 
// Attempt to connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
