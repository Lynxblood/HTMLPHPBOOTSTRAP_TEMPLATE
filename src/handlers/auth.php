<?php
// src/handlers/auth.php

session_start();
require_once '../config/database.php';

// --- Function to sanitize inputs ---
function sanitize_input($conn, $data) {
    return $conn->real_escape_string(trim($data));
}

// --- LOGIN Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = sanitize_input($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, name, email, password FROM users WHERE email = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $name, $db_email, $hashed_password);
            $stmt->fetch();
            
            if (password_verify($password, $hashed_password)) {
                // Success: Start session
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $id;
                $_SESSION['name'] = $name;
                header("location: ../../index.php");
                exit;
            }
        }
    }
    // Failure: Redirect back to login with error
    header("location: ../../login.php?error=invalid");
    exit;
}

// --- REGISTER Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = sanitize_input($conn, $_POST['name']);
    $email = sanitize_input($conn, $_POST['email']);
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        header("location: ../../register.php?error=exists");
        exit;
    }
    $check_stmt->close();

    // Insert new user
    $insert_sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
    if ($insert_stmt = $conn->prepare($insert_sql)) {
        $insert_stmt->bind_param("sss", $name, $email, $hashed_password);
        if ($insert_stmt->execute()) {
            header("location: ../../login.php?success=registered");
            exit;
        }
    }
    // Failure: Redirect back to register
    header("location: ../../register.php?error=fail");
    exit;
}

// --- LOGOUT Logic ---
if (isset($_GET['logout'])) {
    $_SESSION = array(); // Unset all session variables
    session_destroy(); // Destroy the session
    header("location: ../../login.php?success=logout"); 
    exit;
}

$conn->close();
?>
