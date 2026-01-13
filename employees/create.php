<?php
/**
 * Create Employee
 * Form + PHP with prepared statements
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

$error = '';
$success = '';

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $employee_code = sanitizeInput($_POST['employee_code'] ?? '');
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $last_name = sanitizeInput($_POST['last_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $position = sanitizeInput($_POST['position'] ?? '');
    $department = sanitizeInput($_POST['department'] ?? '');
    $hire_date = $_POST['hire_date'] ?? '';
    $salary = $_POST['salary'] ?? '';
    $status = $_POST['status'] ?? 'active';
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($position) || empty($department) || empty($hire_date)) {
        $error = 'Please fill in all required fields.';
    } elseif (!validateEmail($email)) {
        $error = 'Invalid email format.';
    } elseif (!validateDate($hire_date)) {
        $error = 'Invalid date format.';
    } elseif (!empty($salary) && !validateDecimal($salary)) {
        $error = 'Salary must be a valid number.';
    } else {
        // Generate employee code if not provided
        if (empty($employee_code)) {
            $employee_code = generateEmployeeCode($conn);
        }
        
        try {
            // Use prepared statement to prevent SQL injection
            $stmt = $conn->prepare("
                INSERT INTO employees (employee_code, first_name, last_name, email, phone, position, department, hire_date, salary, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $employee_code,
                $first_name,
                $last_name,
                $email,
                $phone,
                $position,
                $department,
                $hire_date,
                $salary ?: null,
                $status
            ]);
            
            $success = 'Employee created successfully!';
            $employeeId = $conn->lastInsertId();
            
            // If AJAX request, return JSON response
            if ($isAjax) {
                header('Content-Type: application/json');
                $response = json_encode([
                    'success' => true,
                    'message' => $success,
                    'employee_id' => $employeeId
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                if ($response === false) {
                    echo json_encode(['success' => false, 'error' => 'JSON encoding error: ' . json_last_error_msg()]);
                } else {
                    echo $response;
                }
                exit;
            }
            
            // Clear form data
            $_POST = [];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Employee code or email already exists.';
            } else {
                $error = 'Error creating employee: ' . $e->getMessage();
            }
            
            // If AJAX request, return JSON response immediately
            if ($isAjax) {
                header('Content-Type: application/json');
                $response = json_encode([
                    'success' => false,
                    'error' => $error
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                if ($response === false) {
                    echo json_encode(['success' => false, 'error' => 'JSON encoding error: ' . json_last_error_msg()]);
                } else {
                    echo $response;
                }
                exit;
            }
        }
    }
    
    // If AJAX request and validation failed, return JSON response
    if ($isAjax && $error) {
        header('Content-Type: application/json');
        $response = json_encode([
            'success' => false,
            'error' => $error
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($response === false) {
            echo json_encode(['success' => false, 'error' => 'JSON encoding error: ' . json_last_error_msg()]);
        } else {
            echo $response;
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Employee - YMPH CRUD System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a href="../index.php" class="navbar-brand mb-0 h1">YMPH CRUD</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h1>Create New Employee</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success" role="alert"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label for="employee_code" class="form-label">Employee Code (auto-generated if empty)</label>
                <input type="text" id="employee_code" name="employee_code" class="form-control"
                       value="<?= htmlspecialchars($_POST['employee_code'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name *</label>
                <input type="text" id="first_name" name="first_name" class="form-control" required
                       value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name *</label>
                <input type="text" id="last_name" name="last_name" class="form-control" required
                       value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" id="email" name="email" class="form-control" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" id="phone" name="phone" class="form-control"
                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="position" class="form-label">Position *</label>
                <input type="text" id="position" name="position" class="form-control" required
                       value="<?= htmlspecialchars($_POST['position'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="department" class="form-label">Department *</label>
                <input type="text" id="department" name="department" class="form-control" required
                       value="<?= htmlspecialchars($_POST['department'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="hire_date" class="form-label">Hire Date *</label>
                <input type="date" id="hire_date" name="hire_date" class="form-control" required
                       value="<?= htmlspecialchars($_POST['hire_date'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="salary" class="form-label">Salary</label>
                <input type="number" id="salary" name="salary" class="form-control" step="0.01" min="0"
                       value="<?= htmlspecialchars($_POST['salary'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="status" class="form-label">Status *</label>
                <select id="status" name="status" class="form-select" required>
                    <option value="active" <?= ($_POST['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($_POST['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="terminated" <?= ($_POST['status'] ?? '') === 'terminated' ? 'selected' : '' ?>>Terminated</option>
                </select>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Create Employee</button>
                <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
