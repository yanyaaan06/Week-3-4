<?php
/**
 * Read Employees
 * PHP select & display with basic security
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

$conn = getDBConnection();
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$department_filter = $_GET['department'] ?? '';
$position_filter = $_GET['position'] ?? '';

// Build query with prepared statements for security
$sql = "SELECT * FROM employees WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR employee_code LIKE ?)";
    $searchParam = '%' . $search . '%';
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
}

if (!empty($department_filter)) {
    $sql .= " AND department = ?";
    $params[] = $department_filter;
}

if (!empty($position_filter)) {
    $sql .= " AND position = ?";
    $params[] = $position_filter;
}

if (!empty($status_filter)) {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$employees = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees - YMPH CRUD System</title>
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
            <h1>Employees List</h1>
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#createModal">Add New Employee</button>
        </div>
        
        <!-- Search and Filter -->
        <div class="card mb-4 shadow-sm no-hover">
            <div class="card-body">
                <form method="GET" action="" class="row g-2" id="searchForm">
                    <div class="col-md-3">
                        <input type="text" name="search" placeholder="Search by name or code" 
                               value="<?= htmlspecialchars($search) ?>" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <select name="department" class="form-select">
                            <option value="">All Departments</option>
                            <?php 
                            $depts = $conn->query("SELECT DISTINCT department FROM employees ORDER BY department")->fetchAll();
                            foreach ($depts as $dept): 
                            ?>
                                <option value="<?= htmlspecialchars($dept['department']) ?>" <?= $department_filter === $dept['department'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['department']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="position" class="form-select">
                            <option value="">All Positions</option>
                            <?php 
                            // Get unique positions
                            $positions = $conn->query("SELECT DISTINCT position FROM employees ORDER BY position")->fetchAll();
                            foreach ($positions as $pos): 
                            ?>
                                <option value="<?= htmlspecialchars($pos['position']) ?>" <?= $position_filter === $pos['position'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($pos['position']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                         
                        </select>
                    </div>
                    <div class="col-md-3">
                        <a href="index.php" class="btn btn-outline-secondary w-100">Clear</a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="table-responsive shadow-sm">
            <table id="employeesTable" class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Position</th>
                        <th>Department</th>
                        <th>Hire Date</th>
                        <th>Salary</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($employees) > 0): ?>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?= htmlspecialchars($employee['employee_code']) ?></td>
                                <td><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></td>
                                <td><?= htmlspecialchars($employee['email']) ?></td>
                                <td><?= htmlspecialchars($employee['position']) ?></td>
                                <td><?= htmlspecialchars($employee['department']) ?></td>
                                <td><?= htmlspecialchars($employee['hire_date']) ?></td>
                                <td><?= $employee['salary'] ? '₱' . number_format($employee['salary'], 2) : '-' ?></td>
                                <td>
                                    <span class="badge badge-<?= $employee['status'] ?>">
                                        <?= htmlspecialchars(ucfirst($employee['status'])) ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal" 
                                            onclick="loadViewModal(<?= $employee['id'] ?>)">View</button>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" 
                                            onclick="loadEditModal(<?= $employee['id'] ?>)">Edit</button>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" 
                                            onclick="loadDeleteModal(<?= $employee['id'] ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="alert alert-info mb-0">No employees found.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 mb-4">
            <a href="../index.php" class="btn btn-link">← Back to Home</a>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="create.php" id="createForm">
                    <div class="modal-body">
                        <div id="createAlert"></div>
                        <div class="mb-3">
                            <label for="employee_code" class="form-label">Employee Code (auto-generated if empty)</label>
                            <input type="text" id="employee_code" name="employee_code" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" id="phone" name="phone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="position" class="form-label">Position *</label>
                            <input type="text" id="position" name="position" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="department" class="form-label">Department *</label>
                            <input type="text" id="department" name="department" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="hire_date" class="form-label">Hire Date *</label>
                            <input type="date" id="hire_date" name="hire_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="salary" class="form-label">Salary</label>
                            <input type="number" id="salary" name="salary" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select id="status" name="status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="terminated">Terminated</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Create Employee</button>
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
                    <h5 class="modal-title">Employee Details</h5>
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
                    <h5 class="modal-title">Edit Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="edit.php" id="editForm">
                    <div class="modal-body">
                        <div id="editAlert"></div>
                        <input type="hidden" id="editId" name="id">
                        <div class="mb-3">
                            <label for="edit_employee_code" class="form-label">Employee Code *</label>
                            <input type="text" id="edit_employee_code" name="employee_code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_first_name" class="form-label">First Name *</label>
                            <input type="text" id="edit_first_name" name="first_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_last_name" class="form-label">Last Name *</label>
                            <input type="text" id="edit_last_name" name="last_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email *</label>
                            <input type="email" id="edit_email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_phone" class="form-label">Phone</label>
                            <input type="text" id="edit_phone" name="phone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="edit_position" class="form-label">Position *</label>
                            <input type="text" id="edit_position" name="position" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_department" class="form-label">Department *</label>
                            <input type="text" id="edit_department" name="department" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_hire_date" class="form-label">Hire Date *</label>
                            <input type="date" id="edit_hire_date" name="hire_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_salary" class="form-label">Salary</label>
                            <input type="number" id="edit_salary" name="salary" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="mb-3">
                            <label for="edit_status" class="form-label">Status *</label>
                            <select id="edit_status" name="status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="terminated">Terminated</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Employee</button>
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
                    <h5 class="modal-title">Delete Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="deleteForm" method="POST" action="delete.php">
                    <div class="modal-body">
                        <div id="deleteContent">
                            <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
                        </div>
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
        // jQuery basics: selectors, events, DOM manipulation
        
        let currentDeleteId = null;  // Track which item is being deleted
        let employeesTable = null;  // DataTable instance

        // Function to load employee data via AJAX GET and display in view modal
        function loadViewModal(id) {
            const $viewContent = $('#viewContent');
            $viewContent.html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
            
            // AJAX GET request
            $.ajax({
                url: 'api.php',
                method: 'GET',
                data: { action: 'get_employee', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        const emp = response.data;
                        const html = `
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width:72px;height:72px;">
                                    <i class="fa fa-user fa-2x text-muted"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0">${escapeHtml(emp.first_name + ' ' + emp.last_name)}</h4>
                                    <small class="text-muted">${escapeHtml(emp.employee_code)}</small>
                                </div>
                                <div class="ms-auto text-end">
                                    <span class="badge bg-secondary">Joined: ${escapeHtml(emp.hire_date)}</span>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="fw-600 text-muted small">Email</div>
                                    <div>${escapeHtml(emp.email)}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fw-600 text-muted small">Phone</div>
                                    <div>${escapeHtml(emp.phone || '-')}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fw-600 text-muted small">Position</div>
                                    <div>${escapeHtml(emp.position)}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fw-600 text-muted small">Department</div>
                                    <div>${escapeHtml(emp.department)}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fw-600 text-muted small">Salary</div>
                                    <div>${emp.salary ? '₱' + parseFloat(emp.salary).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-'}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fw-600 text-muted small">Status</div>
                                    <div>
                                        <span class="badge badge-${emp.status}">${escapeHtml(emp.status.charAt(0).toUpperCase() + emp.status.slice(1))}</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="fw-600 text-muted small">Created</div>
                                    <div>${escapeHtml(emp.created_at)} · Last updated: ${escapeHtml(emp.updated_at)}</div>
                                </div>
                            </div>
                        `;
                        $viewContent.html(html);
                    } else {
                        $viewContent.html('<p class="text-danger">Error loading data: ' + (response.error || 'Unknown error') + '</p>');
                    }
                },
                error: function(xhr, status, error) {
                    $viewContent.html('<p class="text-danger">Error loading data: ' + error + '</p>');
                }
            });
        }

        // Function to load employee data for edit modal via AJAX GET
        function loadEditModal(id) {
            $.ajax({
                url: 'api.php',
                method: 'GET',
                data: { action: 'get_employee', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        const emp = response.data;
                        // jQuery DOM manipulation
                        $('#editId').val(id);
                        $('#edit_employee_code').val(emp.employee_code || '');
                        $('#edit_first_name').val(emp.first_name || '');
                        $('#edit_last_name').val(emp.last_name || '');
                        $('#edit_email').val(emp.email || '');
                        $('#edit_phone').val(emp.phone || '');
                        $('#edit_position').val(emp.position || '');
                        $('#edit_department').val(emp.department || '');
                        // Set hire date - database should already be in YYYY-MM-DD format
                        if (emp.hire_date) {
                            // If date is in different format, convert it
                            let dateValue = emp.hire_date;
                            // Check if date needs conversion (e.g., from "2023-04-05 00:00:00" to "2023-04-05")
                            if (dateValue.includes(' ')) {
                                dateValue = dateValue.split(' ')[0];
                            }
                            // Check if date is in MM/DD/YYYY format and convert
                            if (dateValue.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                                const parts = dateValue.split('/');
                                dateValue = parts[2] + '-' + parts[0].padStart(2, '0') + '-' + parts[1].padStart(2, '0');
                            }
                            $('#edit_hire_date').val(dateValue);
                        } else {
                            $('#edit_hire_date').val('');
                        }
                        $('#edit_salary').val(emp.salary || '');
                        $('#edit_status').val(emp.status || 'active');
                        console.log('Edit modal loaded for employee:', emp);
                    } else {
                        console.error('Error loading edit data:', response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading edit data:', error);
                }
            });
        }

        // Function to load delete modal content
        function loadDeleteModal(id) {
            currentDeleteId = id;
            const $deleteContent = $('#deleteContent');
            $deleteContent.html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
            
            $.ajax({
                url: 'api.php',
                method: 'GET',
                data: { action: 'get_employee', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        const emp = response.data;
                        const html = `
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Employee Information</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Name:</strong> ${escapeHtml(emp.first_name + ' ' + emp.last_name)}</p>
                                <p><strong>Employee Code:</strong> ${escapeHtml(emp.employee_code)}</p>
                                <p><strong>Email:</strong> ${escapeHtml(emp.email)}</p>
                                <p><strong>Position:</strong> ${escapeHtml(emp.position)}</p>
                            </div>
                        `;
                        $deleteContent.html(html);
                    } else {
                        $deleteContent.html('<p class="text-danger">Error loading data</p>');
                    }
                },
                error: function(xhr, status, error) {
                    $deleteContent.html('<p class="text-danger">Error loading data</p>');
                }
            });
        }

        // Helper function to escape HTML
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        // Function to update employees table dynamically
        function updateEmployeesTable(employees) {
            if (!employeesTable) {
                return;
            }
            
            // Clear existing data
            employeesTable.clear();
            
            if (employees.length === 0) {
                employeesTable.draw();
                return;
            }
            
            // Add new data
            employees.forEach(function(emp) {
                employeesTable.row.add([
                    escapeHtml(emp.employee_code),
                    escapeHtml(emp.first_name + ' ' + emp.last_name),
                    escapeHtml(emp.email),
                    escapeHtml(emp.position),
                    escapeHtml(emp.department),
                    escapeHtml(emp.hire_date),
                    emp.salary ? '₱' + parseFloat(emp.salary).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-',
                    `<span class="badge badge-${emp.status}">${escapeHtml(emp.status.charAt(0).toUpperCase() + emp.status.slice(1))}</span>`,
                    `<button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal" onclick="loadViewModal(${emp.id})">View</button> ` +
                    `<button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" onclick="loadEditModal(${emp.id})">Edit</button> ` +
                    `<button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" onclick="loadDeleteModal(${emp.id})">Delete</button>`
                ]);
            });
            
            // Draw the table
            employeesTable.draw();
        }

        // AJAX GET: Search/Filter functionality with dynamic UI updates
        function performSearch() {
            const searchParams = {
                action: 'get_employees',
                search: $('input[name="search"]').val(),
                department: $('select[name="department"]').val(),
                position: $('select[name="position"]').val(),
                status: $('select[name="status"]').val()
            };
            
            $.ajax({
                url: 'api.php',
                method: 'GET',
                data: searchParams,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update employees table dynamically
                        updateEmployeesTable(response.data);
                        
                        // Update filter dropdowns if needed
                        if (response.filters) {
                            // Update department dropdown
                            const $deptSelect = $('select[name="department"]');
                            const currentDept = $deptSelect.val();
                            $deptSelect.find('option:not(:first)').remove();
                            response.filters.departments.forEach(function(dept) {
                                $deptSelect.append($('<option>', {
                                    value: dept,
                                    text: dept,
                                    selected: dept === currentDept
                                }));
                            });
                            
                            // Update position dropdown
                            const $posSelect = $('select[name="position"]');
                            const currentPos = $posSelect.val();
                            $posSelect.find('option:not(:first)').remove();
                            response.filters.positions.forEach(function(pos) {
                                $posSelect.append($('<option>', {
                                    value: pos,
                                    text: pos,
                                    selected: pos === currentPos
                                }));
                            });
                        }
                    } else {
                        console.error('Error fetching employees:', response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });
        }

        // Initialize DataTables
        $(document).ready(function() {
            // Initialize DataTables
            employeesTable = $('#employeesTable').DataTable({
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                order: [[0, 'desc']], // Sort by first column descending
                columnDefs: [
                    { orderable: false, targets: 8 } // Disable sorting on Actions column
                ],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)"
                }
            });

            // Search input event (with debounce)
            let searchTimeout;
            $('input[name="search"]').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 300);
            });
            
            // Filter dropdowns change event
            $('select[name="department"], select[name="position"], select[name="status"]').on('change', function() {
                performSearch();
            });
            
            // Clear button
            $('a[href="index.php"]').on('click', function(e) {
                e.preventDefault();
                $('input[name="search"]').val('');
                $('select[name="department"]').val('');
                $('select[name="position"]').val('');
                $('select[name="status"]').val('');
                performSearch();
            });
        });

        // AJAX POST: Create form submission
        $('#createForm').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $alert = $('#createAlert');
            $alert.empty();
            
            const formData = new FormData(this);
            
            $.ajax({
                url: 'create.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response && response.success) {
                        // Show success message before closing modal
                        $alert.html('<div class="alert alert-success">' + (response.message || 'Employee created successfully!') + '</div>');
                        
                        // Reset form
                        $form[0].reset();
                        
                        // Refresh employees list
                        performSearch();
                        
                        // Close modal after a short delay
                        setTimeout(function() {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('createModal'));
                            if (modal) {
                                modal.hide();
                            }
                            $alert.empty();
                        }, 1000);
                    } else {
                        $alert.html('<div class="alert alert-danger">' + (response.error || 'Error creating employee') + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    let errorMsg = 'Error creating employee';
                    // Try to parse JSON response
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    } else if (xhr.responseText) {
                        // Try to parse response text as JSON
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.error) {
                                errorMsg = response.error;
                            }
                        } catch (e) {
                            // If not JSON, show a generic error
                            errorMsg = 'Error creating employee. Please check your inputs.';
                            console.error('Server response:', xhr.responseText);
                        }
                    }
                    $alert.html('<div class="alert alert-danger">' + errorMsg + '</div>');
                }
            });
        });

        // AJAX POST: Edit form submission
        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $alert = $('#editAlert');
            $alert.empty();
            
            // Validate that ID exists
            const employeeId = $('#editId').val();
            if (!employeeId || employeeId === '') {
                $alert.html('<div class="alert alert-danger">Employee ID is missing. Please refresh and try again.</div>');
                return;
            }
            
            const formData = new FormData(this);
            // Ensure ID is in form data
            if (!formData.has('id')) {
                formData.append('id', employeeId);
            }
            
            // Debug: Log all form values
            console.log('Submitting edit form for employee ID:', employeeId);
            for (let [key, value] of formData.entries()) {
                console.log(key + ':', value);
            }
            
            $.ajax({
                url: 'edit.php?id=' + encodeURIComponent(employeeId),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    console.log('Edit response:', response);
                    if (response && response.success) {
                        // Show success message before closing modal
                        $alert.html('<div class="alert alert-success">' + (response.message || 'Employee updated successfully!') + '</div>');
                        
                        // Refresh employees list
                        performSearch();
                        
                        // Close modal after a short delay
                        setTimeout(function() {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                            if (modal) {
                                modal.hide();
                            }
                            $alert.empty();
                        }, 1500);
                    } else {
                        $alert.html('<div class="alert alert-danger">' + (response.error || 'Error updating employee') + '</div>');
                        console.error('Edit failed:', response);
                    }
                },
                error: function(xhr, status, error) {
                    let errorMsg = 'Error updating employee';
                    console.error('Edit error:', {xhr, status, error});
                    // Try to parse JSON response
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    } else if (xhr.responseText) {
                        // Try to parse response text as JSON
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.error) {
                                errorMsg = response.error;
                            }
                        } catch (e) {
                            // If not JSON, show a generic error
                            errorMsg = 'Error updating employee. Please check your inputs.';
                            console.error('Server response:', xhr.responseText);
                            console.error('Parse error:', e);
                        }
                    }
                    $alert.html('<div class="alert alert-danger">' + errorMsg + '</div>');
                }
            });
        });

        // AJAX POST: Delete form submission
        $('#deleteForm').on('submit', function(e) {
            e.preventDefault();
            
            const id = currentDeleteId;
            if (!id) {
                console.error('No employee ID to delete');
                return;
            }
            
            const formData = new FormData(this);
            formData.append('confirm_delete', 'yes');
            
            $.ajax({
                url: 'delete.php?id=' + encodeURIComponent(id),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                        if (modal) {
                            modal.hide();
                        }
                        // Refresh employees list
                        performSearch();
                    } else {
                        $('#deleteContent').prepend('<div class="alert alert-danger">' + (response.error || 'Error deleting employee') + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    let errorMsg = 'Error deleting employee';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    }
                    $('#deleteContent').prepend('<div class="alert alert-danger">' + errorMsg + '</div>');
                }
            });
        });
    </script>
</body>
</html>
