<?php
/**
 * Delete Employee
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

$id = $_GET['id'] ?? 0;

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

// Fetch employee to verify existence
$stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->execute([$id]);
$employee = $stmt->fetch();

if (!$employee) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm_delete = $_POST['confirm_delete'] ?? '';
    
    if ($confirm_delete === 'yes') {
        try {
            // Delete employee
            $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
            $stmt->execute([$id]);
            $success = 'Employee deleted successfully.';
            
            // If AJAX request, return JSON response
            if ($isAjax) {
                header('Content-Type: application/json');
                $response = json_encode([
                    'success' => true,
                    'message' => $success
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                if ($response === false) {
                    echo json_encode(['success' => false, 'error' => 'JSON encoding error: ' . json_last_error_msg()]);
                } else {
                    echo $response;
                }
                exit;
            }
            
            // Redirect after delete
            header('Location: index.php?deleted=1');
            exit;
        } catch (PDOException $e) {
            $error = 'Error deleting employee: ' . $e->getMessage();
            
            // If AJAX request, return JSON response
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => $error
                ]);
                exit;
            }
        }
    } else {
        if ($isAjax) {
            header('Content-Type: application/json');
            $response = json_encode(['success' => false, 'error' => 'Delete not confirmed'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Employee - CRUD System</title>
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
        <h1>Delete Employee</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success" role="alert"><?= htmlspecialchars($success) ?></div>
            <div class="mt-3">
                <a href="index.php" class="btn btn-danger">Back to List</a>
            </div>
        <?php else: ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Employee Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></p>
                    <p><strong>Employee Code:</strong> <?= htmlspecialchars($employee['employee_code']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($employee['email']) ?></p>
                    <p><strong>Position:</strong> <?= htmlspecialchars($employee['position']) ?></p>
                </div>
            </div>
            
            <form method="POST" action="" class="card p-4 shadow-sm">
                <input type="hidden" name="confirm_delete" value="yes">
                
                <div class="alert alert-warning" role="alert">
                    <strong>Warning:</strong> This will permanently delete the employee record. This action cannot be undone.
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger">Confirm Delete</button>
                    <a href="view.php?id=<?= $employee['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
