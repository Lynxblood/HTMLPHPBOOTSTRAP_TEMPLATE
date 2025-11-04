// public/assets/js/crud.js

    
    // Configure Alertify (Optional but recommended for better look/feel)
    alertify.set('notifier','position', 'top-right');
    alertify.set('notifier','delay', 3);

    /**
     * Reusable Alertify Confirmation function using Bootstrap themes.
     * @param {string} title - The title of the dialog.
     * @param {string} message - The message content (can contain HTML/Markdown).
     * @param {string} style - Bootstrap color variant ('primary', 'success', 'danger', 'warning', 'info', etc.)
     * @param {function} onOk - Callback for OK/Confirm.
     * @param {function} onCancel - Optional callback for Cancel.
     */
    function customAlertifyConfirm(title, message, style, onOk, onCancel) {
        const cancelCallback = onCancel || function() { alertify.message('Operation cancelled.'); };
    
        // determine ok label
        let okLabel = 'Confirm';
        if (style === 'danger') okLabel = 'Yes, Delete';
        else if (style === 'warning') okLabel = 'Yes, Proceed';
    
        // --- Save current theme defaults (if available) ---
        const hasDefaults = !!(alertify && alertify.defaults && alertify.defaults.theme);
        const prevOk = hasDefaults ? alertify.defaults.theme.ok : undefined;
        const prevCancel = hasDefaults ? alertify.defaults.theme.cancel : undefined;
    
        // --- Set temporary theme classes to be used when dialog is created ---
        if (hasDefaults) {
            alertify.defaults.theme.ok = `ajs-ok btn btn-${style}`;          // ensures .ajs-ok exists and has Bootstrap btn
            alertify.defaults.theme.cancel = 'ajs-cancel btn btn-secondary me-2';
        } else {
            // Fallback for older/different versions: set glossary labels (keeps text only)
            // We'll still attempt to set classes after creation if needed.
        }
    
        // Create the dialog (Alertify will use the theme we just set)
        const dialog = alertify.confirm(title, message, onOk, cancelCallback)
            .set('labels', { ok: okLabel, cancel: 'Cancel' })
            .set({ reverseButtons: true, movable: false });
    
        // Restore previous defaults immediately so other dialogs aren't affected
        if (hasDefaults) {
            alertify.defaults.theme.ok = prevOk;
            alertify.defaults.theme.cancel = prevCancel;
        }
    
        // Optional: ensure header/body classes — try immediate patch (should be OK because buttons already have classes)
        const root = dialog && dialog.elements && dialog.elements.root;
        if (root) {
            const header = root.querySelector('.ajs-header');
            const body = root.querySelector('.ajs-body');
            if (header) header.classList.add('text-start');
            if (body) body.classList.add('text-start');
        } else {
            // If root isn't present synchronously (rare), attach a small observer that patches only header/body,
            // but buttons are already themed so visual flicker won't occur:
            const mo = new MutationObserver((mutations, obs) => {
                if (dialog && dialog.elements && dialog.elements.root) {
                    const r = dialog.elements.root;
                    const header = r.querySelector('.ajs-header');
                    const body = r.querySelector('.ajs-body');
                    if (header) header.classList.add('text-start');
                    if (body) body.classList.add('text-start');
                    obs.disconnect();
                }
            });
            mo.observe(document.body, { childList: true, subtree: true });
            // disconnect on close just in case
            dialog.set('onclose', function() { mo.disconnect(); });
        }
    
        // return dialog if caller needs its
        return dialog;
    }
$(document).ready(function() {
    // 1. Initialize DataTables
    const productTable = $('#productTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "src/handlers/crud_ajax.php",
            "type": "POST",
            "data": { action: 'read' } // Specify the action for READ
        },
        "columns": [
            { "data": "id" },
            { "data": "product_name" },
            { "data": "price_formatted" }, // Using the formatted price from PHP
            { "data": "stock" },
            { 
                "data": null,
                "defaultContent": `
                    <button class='edit btn btn-sm btn-info me-1' data-bs-toggle='modal' data-bs-target='#productModal'><i class="fas fa-edit"></i> Edit</button>
                    <button class='delete btn btn-sm btn-danger'><i class="fas fa-trash"></i> Delete</button>`
            }
        ]
    });

    // Get the Bootstrap Modal instance
    const productModal = new bootstrap.Modal(document.getElementById('productModal'));


    // --- C (Create) Setup ---
    $('#addProductBtn').on('click', function() {
        $('#productForm')[0].reset(); // Clear form
        $('#product_id').val(''); // Clear ID for CREATE mode
        $('#productModalLabel').text('Add New Product');
        $('#productForm input[name="action"]').val('create'); // Set action to CREATE
    });

    // --- U (Update) Setup - Load data into modal ---
    $('#productTable tbody').on('click', '.edit', function() {
        const data = productTable.row($(this).parents('tr')).data();
        
        // Populate form fields
        $('#product_id').val(data.id);
        $('#product_name').val(data.product_name);
        $('#price').val(data.price);
        $('#stock').val(data.stock);
        $('#description').val(data.description);
        
        $('#productModalLabel').text('Edit Product (ID: ' + data.id + ')');
        $('#productForm input[name="action"]').val('update'); // Set action to UPDATE
    });

    // --- C & U (Save) Handler ---
    $('#productForm').on('submit', function(e) {
        e.preventDefault();
        const action = $('#productForm input[name="action"]').val();
        const formData = $(this).serializeArray();
        formData.push({ name: 'action', value: action }); // Ensure action is always included

        $.ajax({
            url: 'src/handlers/crud_ajax.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    productModal.hide(); // Close modal
                    productTable.ajax.reload(null, false); // Reload DataTables without changing page
                    alertify.success(response.message); 
                } else {
                    alertify.error('Error saving data: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error, xhr.responseText);
                alertify.error('An application error occurred during save.'); 
            }
        });
    });


    // --- D (Delete) Handler ---
    $('#productTable tbody').on('click', '.delete', function() {
        const data = productTable.row($(this).parents('tr')).data();
        const productId = data.id;
        const productName = data.product_name;

        const title = 'Delete Product: ' + productName;
        const message = 'Are you sure you want to **permanently delete** "' + productName + '" (ID: ' + productId + ')? This action cannot be undone.';

        // Using the new custom function
        customAlertifyConfirm(
            title, 
            message,
            'danger', // Setting the style to 'danger' (red)
            function() { // OK (Confirm) button action
                // User confirmed deletion, proceed with AJAX
                $.ajax({
                    url: 'src/handlers/crud_ajax.php',
                    type: 'POST',
                    data: { id: productId, action: 'delete' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            productTable.ajax.reload(null, false);
                            alertify.success(response.message);
                        } else {
                            alertify.error('Error deleting data: ' + response.message);
                        }
                    },
                    error: function() {
                        alertify.error('An application error occurred during delete.');
                    }
                });
            }
            // onCancel function is optional here, defaults to alertify.message('Operation cancelled.')
        );
    });
});
