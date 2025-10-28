<?php
// src/handlers/crud_ajax.php

session_start();
// Basic authentication check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => "Access denied."]);
    exit;
}

require_once '../config/database.php';

// --- Function to sanitize inputs ---
function sanitize_input($conn, $data) {
    return $conn->real_escape_string(trim($data));
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

// --- READ Operation (DataTables Server-Side Processing) ---
if ($action === 'read') {
    $draw = $_POST['draw'];
    $start = $_POST['start'];
    $length = $_POST['length'];
    $search = sanitize_input($conn, $_POST['search']['value']);
    $order_column_index = $_POST['order'][0]['column'];
    $order_dir = $_POST['order'][0]['dir'];

    $columns = array('id', 'product_name', 'price', 'stock'); 
    $order_column = $columns[$order_column_index];

    // Total Records without filtering
    $total_sql = "SELECT COUNT(*) FROM products";
    $total_result = $conn->query($total_sql);
    $total_records = $total_result->fetch_row()[0];

    // Main Query
    $sql = "SELECT id, product_name, price, stock, description FROM products";
    $where = '';

    // Search Filter
    if (!empty($search)) {
        $where .= " WHERE product_name LIKE '%$search%' OR description LIKE '%$search%'";
    }

    // Filtered Count
    $filtered_sql = "SELECT COUNT(*) FROM products" . $where;
    $filtered_result = $conn->query($filtered_sql);
    $total_filtered = $filtered_result->fetch_row()[0];

    // Add ORDER BY and LIMIT
    $sql .= $where;
    $sql .= " ORDER BY $order_column $order_dir";
    $sql .= " LIMIT $start, $length";

    $result = $conn->query($sql);

    $data = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Format price as currency string (optional)
            $row['price_formatted'] = '$' . number_format($row['price'], 2);
            $data[] = $row;
        }
    }

    // DataTables required JSON response
    echo json_encode(array(
        "draw" => intval($draw),
        "recordsTotal" => intval($total_records),
        "recordsFiltered" => intval($total_filtered),
        "data" => $data
    ));
}

// --- CREATE and UPDATE Operation ---
if ($action === 'create' || $action === 'update') {
    $id = isset($_POST['id']) ? sanitize_input($conn, $_POST['id']) : null;
    $name = sanitize_input($conn, $_POST['product_name']);
    $price = sanitize_input($conn, $_POST['price']);
    $stock = sanitize_input($conn, $_POST['stock']);
    $description = sanitize_input($conn, $_POST['description']);
    
    if ($action === 'create') {
        $sql = "INSERT INTO products (product_name, price, stock, description) VALUES ('$name', '$price', '$stock', '$description')";
    } else { // update
        $sql = "UPDATE products SET product_name='$name', price='$price', stock='$stock', description='$description' WHERE id=$id";
    }

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Record saved successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "SQL Error: " . $conn->error]);
    }
}

// --- DELETE Operation ---
if ($action === 'delete') {
    $id = sanitize_input($conn, $_POST['id']);
    $sql = "DELETE FROM products WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Record deleted successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "SQL Error: " . $conn->error]);
    }
}

$conn->close();
?>
