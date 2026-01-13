<?php
/**
 * Read Products
 * PHP select & display with basic security
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

$conn = getDBConnection();
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$category_filter = $_GET['category'] ?? '';

// Build query with prepared statements for security
$sql = "SELECT p.*, e.first_name, e.last_name, e.employee_code 
        FROM products p 
        LEFT JOIN employees e ON p.created_by = e.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.product_code LIKE ? OR p.description LIKE ?)";
    $searchParam = '%' . $search . '%';
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
}

if (!empty($status_filter)) {
    $sql .= " AND p.status = ?";
    $params[] = $status_filter;
}

if (!empty($category_filter)) {
    $sql .= " AND p.category = ?";
    $params[] = $category_filter;
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$stmt = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - YMPH CRUD System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a href="../index.php" class="navbar-brand mb-0 h1">YMPH CRUD</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Products List</h1>
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#createModal">Add New Product</button>
        </div>
        
        <!-- Search and Filter -->
        <div class="card mb-4 shadow-sm no-hover">
            <div class="card-body">
                <form method="GET" action="" class="row g-2">
                    <div class="col-md-3">
                        <input type="text" name="search" placeholder="Search by name, code, or description" 
                               value="<?= htmlspecialchars($search) ?>" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= $category_filter === $cat ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="discontinued" <?= $status_filter === 'discontinued' ? 'selected' : '' ?>>Discontinued</option>
                            <option value="out_of_stock" <?= $status_filter === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <a href="index.php" class="btn btn-outline-secondary w-100">Clear</a>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if (count($products) > 0): ?>
            <div class="table-responsive shadow-sm">
                <table id="productsTable" class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product['product_code']) ?></td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td><?= htmlspecialchars($product['category']) ?></td>
                                <td>₱<?= number_format($product['price'], 2) ?></td>
                                <td>
                                    <?= $product['stock_quantity'] ?>
                                    <?php if ($product['stock_quantity'] <= $product['min_stock_level']): ?>
                                        <span class="badge bg-warning">Low Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $product['status'] ?>">
                                        <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $product['status']))) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($product['employee_code']): ?>
                                        <?= htmlspecialchars($product['employee_code'] . ' - ' . $product['first_name'] . ' ' . $product['last_name']) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal" 
                                            onclick="loadViewModal(<?= $product['id'] ?>)">View</button>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" 
                                            onclick="loadEditModal(<?= $product['id'] ?>)">Edit</button>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" 
                                            onclick="loadDeleteModal(<?= $product['id'] ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info" role="alert">No products found.</div>
        <?php endif; ?>
        
        <div class="mt-4 mb-4">
            <a href="../index.php" class="btn btn-link">← Back to Home</a>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="create.php" id="createForm">
                    <div class="modal-body">
                        <div id="createAlert"></div>
                        <div class="mb-3">
                            <label for="product_code" class="form-label">Product Code (auto-generated if empty)</label>
                            <input type="text" id="product_code" name="product_code" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name *</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category *</label>
                                <input type="text" id="category" name="category" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Price *</label>
                                <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cost" class="form-label">Cost</label>
                                <input type="number" id="cost" name="cost" class="form-control" step="0.01" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="unit" class="form-label">Unit</label>
                                <input type="text" id="unit" name="unit" class="form-control" value="unit">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="stock_quantity" class="form-label">Stock Quantity</label>
                                <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" min="0" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="min_stock_level" class="form-label">Minimum Stock Level</label>
                                <input type="number" id="min_stock_level" name="min_stock_level" class="form-control" min="0" value="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select id="status" name="status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="discontinued">Discontinued</option>
                                <option value="out_of_stock">Out of Stock</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="created_by" class="form-label">Created By (Employee)</label>
                            <select id="created_by" name="created_by" class="form-select">
                                <option value="">-- Select Employee (Optional) --</option>
                                <?php 
                                $empStmt = $conn->query("SELECT id, first_name, last_name, employee_code FROM employees WHERE status = 'active' ORDER BY last_name, first_name");
                                $employees = $empStmt->fetchAll();
                                foreach ($employees as $emp): 
                                ?>
                                    <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['employee_code'] . ' - ' . $emp['first_name'] . ' ' . $emp['last_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Create Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Product Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewContent">
                    <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="edit.php" id="editForm">
                    <div class="modal-body">
                        <div id="editAlert"></div>
                        <input type="hidden" id="editId" name="id">
                        <div class="mb-3">
                            <label for="edit_product_code" class="form-label">Product Code *</label>
                            <input type="text" id="edit_product_code" name="product_code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Product Name *</label>
                            <input type="text" id="edit_name" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea id="edit_description" name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_category" class="form-label">Category *</label>
                                <input type="text" id="edit_category" name="category" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_price" class="form-label">Price *</label>
                                <input type="number" id="edit_price" name="price" class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_cost" class="form-label">Cost</label>
                                <input type="number" id="edit_cost" name="cost" class="form-control" step="0.01" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_unit" class="form-label">Unit</label>
                                <input type="text" id="edit_unit" name="unit" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_stock_quantity" class="form-label">Stock Quantity</label>
                                <input type="number" id="edit_stock_quantity" name="stock_quantity" class="form-control" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_min_stock_level" class="form-label">Minimum Stock Level</label>
                                <input type="number" id="edit_min_stock_level" name="min_stock_level" class="form-control" min="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_status" class="form-label">Status *</label>
                            <select id="edit_status" name="status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="discontinued">Discontinued</option>
                                <option value="out_of_stock">Out of Stock</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_created_by" class="form-label">Created By (Employee)</label>
                            <select id="edit_created_by" name="created_by" class="form-select">
                                <option value="">-- Select Employee (Optional) --</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['employee_code'] . ' - ' . $emp['first_name'] . ' ' . $emp['last_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="delete.php" id="deleteForm">
                    <div class="modal-body" id="deleteContent">
                        <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        let currentDeleteId = null;  // Track which item is being deleted
        let productsTable = null;  // DataTable instance

        // Initialize DataTables
        $(document).ready(function() {
            // Initialize DataTables
            productsTable = $('#productsTable').DataTable({
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                order: [[0, 'desc']], // Sort by first column descending
                columnDefs: [
                    { orderable: false, targets: 7 } // Disable sorting on Actions column
                ],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)"
                }
            });
        });

        function loadViewModal(id) {
            const viewContent = document.getElementById('viewContent');
            viewContent.innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>';
            
            fetch('view.php?id=' + id)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const content = doc.querySelector('.card.shadow-sm.p-4');
                    viewContent.innerHTML = content ? content.innerHTML : '<p>Error loading data</p>';
                })
                .catch(error => {
                    viewContent.innerHTML = '<p class="text-danger">Error loading data</p>';
                });
        }

        function loadEditModal(id) {
            // Fetch the same edit page used by the non-modal flow and parse its inputs
            fetch('edit.php?id=' + id)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    document.getElementById('editId').value = id;

                    // helper to safely get value from the returned edit page
                    const valueOf = (elId) => {
                        const el = doc.getElementById(elId);
                        if (!el) return '';
                        if (el.tagName.toLowerCase() === 'select') {
                            const sel = el.querySelector('option[selected]');
                            return sel ? sel.value : el.value;
                        }
                        return el.value ?? el.textContent ?? '';
                    };

                    document.getElementById('edit_product_code').value = valueOf('product_code');
                    document.getElementById('edit_name').value = valueOf('name');
                    document.getElementById('edit_description').value = valueOf('description');
                    document.getElementById('edit_category').value = valueOf('category');
                    document.getElementById('edit_price').value = valueOf('price');
                    document.getElementById('edit_cost').value = valueOf('cost');
                    document.getElementById('edit_stock_quantity').value = valueOf('stock_quantity');
                    document.getElementById('edit_min_stock_level').value = valueOf('min_stock_level');
                    document.getElementById('edit_unit').value = valueOf('unit');

                    // status and created_by are select elements in the modal; set their values
                    const statusVal = valueOf('status');
                    if (statusVal) {
                        document.getElementById('edit_status').value = statusVal;
                    }

                    const createdByVal = valueOf('created_by');
                    if (createdByVal) {
                        document.getElementById('edit_created_by').value = createdByVal;
                    }
                })
                .catch(error => console.error('Error loading edit data:', error));
        }

        function loadDeleteModal(id) {
            currentDeleteId = id;  // Store id for later use in submit handler
            const deleteContent = document.getElementById('deleteContent');
            deleteContent.innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>';
            
            fetch('delete.php?id=' + id)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const content = doc.querySelector('.card.mb-4.shadow-sm');
                    deleteContent.innerHTML = content ? content.innerHTML : '<p>Error loading data</p>';
                })
                .catch(error => {
                    deleteContent.innerHTML = '<p class="text-danger">Error loading data</p>';
                });
        }

        function getTextContent(doc, label) {
            const labels = Array.from(doc.querySelectorAll('label'));
            const labelEl = labels.find(l => l.textContent.trim().startsWith(label));
            if (labelEl && labelEl.nextElementSibling) {
                return labelEl.nextElementSibling.textContent.trim().replace(/Low Stock/g, '').trim();
            }
            return '';
        }

        document.getElementById('createForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('create.php', { method: 'POST', body: formData })
                .then(response => response.text())
                .then(html => {
                    if (html.includes('successfully')) {
                        location.reload();
                    } else {
                        document.getElementById('createAlert').innerHTML = '<div class="alert alert-danger">Error creating product</div>';
                    }
                })
                .catch(error => {
                    document.getElementById('createAlert').innerHTML = '<div class="alert alert-danger">Error: ' + error + '</div>';
                });
        });

        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('id', document.getElementById('editId').value);
            fetch('edit.php?id=' + encodeURIComponent(document.getElementById('editId').value), { method: 'POST', body: formData })
                .then(response => response.text())
                .then(html => {
                    if (html.includes('successfully')) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                        if (modal) modal.hide();
                        setTimeout(() => location.reload(), 300);
                    } else {
                        document.getElementById('editAlert').innerHTML = '<div class="alert alert-danger">' + (html || 'Error updating product') + '</div>';
                    }
                })
                .catch(error => {
                    document.getElementById('editAlert').innerHTML = '<div class="alert alert-danger">Error: ' + error + '</div>';
                });
        });

        document.getElementById('deleteForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const id = currentDeleteId;
            const deleteUrl = 'delete.php?id=' + encodeURIComponent(id);
            const formData = new FormData(this);
            formData.append('confirm_delete', 'yes');
            formData.append('delete_type', 'hard');
            
            fetch(deleteUrl, { 
                method: 'POST', 
                body: formData,
                redirect: 'follow'  // Follow redirects
            })
                .then(response => {
                    // Check if we got redirected (successful deletion)
                    if (response.url.includes('deleted=hard') || response.url.includes('index.php')) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                        if (modal) modal.hide();
                        setTimeout(() => location.reload(), 300);
                    } else {
                        return response.text();
                    }
                })
                .then(html => {
                    if (html && (html.includes('permanently deleted') || html.includes('successfully'))) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                        if (modal) modal.hide();
                        setTimeout(() => location.reload(), 300);
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    </script>
</body>
</html>
