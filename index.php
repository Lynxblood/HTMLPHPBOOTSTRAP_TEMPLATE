<?php
// index.php
session_start();
// Security check: if not logged in, redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Dashboard | Welcome <?php echo htmlspecialchars($_SESSION['name']); ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css">
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>
    <link rel="stylesheet" href="public/assets/css/style.css">
    <style>
        body { background-color: #f4f7f6; }
        .container { margin-top: 30px; }
        .dataTables_wrapper .dataTables_filter input { border-radius: 5px; border: 1px solid #ccc; padding: 5px; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center bg-white p-3 mb-4 shadow-sm rounded">
        <h3 class="mb-0 text-dark">
            <span class="text-primary">CRUD</span> Dashboard
        </h3>
        <div>
            <span class="me-3 text-secondary">Hello, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
            <a href="src/handlers/auth.php?logout=true" class="btn btn-sm btn-outline-danger">Logout</a>
        </div>
    </div>

    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Product Inventory</h5>
            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#productModal" id="addProductBtn">
                <i class="fas fa-plus-circle me-1"></i> Add New Product
            </button>
        </div>
        <div class="card-body">
            <!-- The Data Table -->
            <table id="productTable" class="table table-striped table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data loaded by DataTables AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Bootstrap Modal for Create and Update -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="productModalLabel">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <form id="productForm">
                    <input type="hidden" id="product_id" name="id">
                    <input type="hidden" name="action" value="save"> <!-- Will be dynamically set by JS -->

                    <div class="mb-3">
                        <label for="product_name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="product_name" name="product_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price ($)</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                    </div>
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock Quantity</label>
                        <input type="number" class="form-control" id="stock" name="stock" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="productForm" class="btn btn-primary" id="saveProductBtn">Save Product</button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery (must be loaded before Bootstrap and DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.js"></script>
<!-- Alertify for Icons -->
<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
<!-- Font Awesome for Icons -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

<!-- Custom CRUD logic -->
<script src="public/assets/js/crud.js"></script>

</body>
</html>
