<?php
/**
 * Update Employee
 * Edit form + PHP with input validation
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

$id = $_GET['id'] ?? 0;
$error = '';
$success = '';

if (empty($id) || !is_numeric($id)) {
        if ($isAjax) {
            header('Content-Type: application/json');
            $response = json_encode(['success' => false, 'error' => 'Invalid employee ID'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($response === false) {
                echo json_encode(['success' => false, 'error' => 'JSON encoding error: ' . json_last_error_msg()]);
            } else {
                echo $response;
            }
            exit;
        }
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();

// Fetch existing employee data
$stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->execute([$id]);
$employee = $stmt->fetch();

if (!$employee) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $employee_code = sanitizeInput($_POST['employee_code'] ?? '');
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $last_name = sanitizeInput($_POST['last_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $position = sanitizeInput($_POST['position'] ?? '');
    $department = sanitizeInput($_POST['department'] ?? '');
    $hire_date = trim($_POST['hire_date'] ?? '');
    $salary = trim($_POST['salary'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    // Validation with detailed error messages
    if (empty($first_name)) {
        $error = 'First name is required.';
    } elseif (empty($last_name)) {
        $error = 'Last name is required.';
    } elseif (empty($email)) {
        $error = 'Email is required.';
    } elseif (!validateEmail($email)) {
        $error = 'Invalid email format.';
    } elseif (empty($position)) {
        $error = 'Position is required.';
    } elseif (empty($department)) {
        $error = 'Department is required.';
    } elseif (empty($hire_date)) {
        $error = 'Hire date is required.';
    } elseif (!validateDate($hire_date)) {
        // Try to convert date format if it's in a different format
        $converted_date = '';
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $hire_date, $matches)) {
            // Convert MM/DD/YYYY to YYYY-MM-DD
            $converted_date = $matches[3] . '-' . $matches[1] . '-' . $matches[2];
            if (validateDate($converted_date)) {
                $hire_date = $converted_date;
                // Continue validation - date is now valid
            } else {
                $error = 'Invalid date format. Expected format: YYYY-MM-DD. Received: ' . htmlspecialchars($hire_date);
            }
        } else {
            $error = 'Invalid date format. Expected format: YYYY-MM-DD. Received: ' . htmlspecialchars($hire_date);
        }
    }
    
    // Continue with remaining validations if date is valid
    if (empty($error)) {
        if (!empty($salary) && !validateDecimal($salary)) {
            $error = 'Salary must be a valid number.';
        } elseif (empty($employee_code)) {
            $error = 'Employee code is required.';
        }
    }
    
    // Only proceed with update if no validation errors
    if (empty($error)) {
        try {
            // Use prepared statement to prevent SQL injection
            $stmt = $conn->prepare("
                UPDATE employees 
                SET employee_code = ?, first_name = ?, last_name = ?, email = ?, 
                    phone = ?, position = ?, department = ?, hire_date = ?, 
                    salary = ?, status = ?
                WHERE id = ?
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
                $status,
                $id
            ]);
            
            $success = 'Employee updated successfully!';
            
            // If AJAX request, return JSON response
            if ($isAjax) {
                header('Content-Type: application/json');
                $response = json_encode([
                    'success' => true,
                    'message' => $success,
                    'employee_id' => $id
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                if ($response === false) {
                    echo json_encode(['success' => false, 'error' => 'JSON encoding error: ' . json_last_error_msg()]);
                } else {
                    echo $response;
                }
                exit;
            }
            
            // Refresh employee data
            $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
            $stmt->execute([$id]);
            $employee = $stmt->fetch();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Employee code or email already exists.';
            } else {
                $error = 'Error updating employee: ' . $e->getMessage();
            }
            
            // If AJAX request, return JSON response
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
    <title>Edit Employee - YMPH CRUD System</title>
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
        <h1>Edit Employee</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success" role="alert"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" class="card p-4 shadow-sm">
            <div class="form-group">
                <label for="employee_code">Employee Code *</label>
                <input type="text" id="employee_code" name="employee_code" required
                       value="<?= htmlspecialchars($employee['employee_code']) ?>">
            </div>
            
            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" id="first_name" name="first_name" required
                       value="<?= htmlspecialchars($employee['first_name']) ?>">
            </div>
            
            <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input type="text" id="last_name" name="last_name" required
                       value="<?= htmlspecialchars($employee['last_name']) ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required
                       value="<?= htmlspecialchars($employee['email']) ?>">
            </div>
            
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone"
                       value="<?= htmlspecialchars($employee['phone'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="position">Position *</label>
                <input type="text" id="position" name="position" required
                       value="<?= htmlspecialchars($employee['position']) ?>">
            </div>
            
            <div class="form-group">
                <label for="department">Department *</label>
                <input type="text" id="department" name="department" required
                       value="<?= htmlspecialchars($employee['department']) ?>">
            </div>
            
            <div class="form-group">
                <label for="hire_date">Hire Date *</label>
                <input type="date" id="hire_date" name="hire_date" required
                       value="<?= htmlspecialchars($employee['hire_date']) ?>">
            </div>
            
            <div class="form-group">
                <label for="salary">Salary</label>
                <input type="number" id="salary" name="salary" step="0.01" min="0"
                       value="<?= htmlspecialchars($employee['salary'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" name="status" required>
                    <option value="active" <?= $employee['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $employee['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="terminated" <?= $employee['status'] === 'terminated' ? 'selected' : '' ?>>Terminated</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Employee</button>
                <a href="view.php?id=<?= $employee['id'] ?>" class="btn btn-secondary">View Details</a>
                <a href="index.php" class="btn btn-secondary">Back to List</a>
            </div>
        </form>
    </div>
</body>
</html>
