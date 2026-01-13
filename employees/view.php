<?php
/**
 * View Employee Details
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

$id = $_GET['id'] ?? 0;

if (empty($id) || !is_numeric($id)) {
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->execute([$id]);
$employee = $stmt->fetch();

if (!$employee) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Employee - YMPH CRUD System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a href="../index.php" class="navbar-brand mb-0 h1">YMPH CRUD</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card shadow-sm p-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width:72px;height:72px;">
                    <i class="fa fa-user fa-2x text-muted"></i>
                </div>
                <div>
                    <h4 class="mb-0"><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></h4>
                    <small class="text-muted"><?= htmlspecialchars($employee['employee_code']) ?></small>
                </div>
                <div class="ms-auto text-end">
                    <span class="badge bg-secondary">Joined: <?= htmlspecialchars($employee['hire_date']) ?></span>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="fw-600 text-muted small">Email</div>
                    <div><?= htmlspecialchars($employee['email']) ?></div>
                </div>
                <div class="col-md-6">
                    <div class="fw-600 text-muted small">Phone</div>
                    <div><?= htmlspecialchars($employee['phone'] ?: '-') ?></div>
                </div>

                <div class="col-md-6">
                    <div class="fw-600 text-muted small">Position</div>
                    <div><?= htmlspecialchars($employee['position']) ?></div>
                </div>
                <div class="col-md-6">
                    <div class="fw-600 text-muted small">Department</div>
                    <div><?= htmlspecialchars($employee['department']) ?></div>
                </div>

                <div class="col-md-6">
                    <div class="fw-600 text-muted small">Salary</div>
                    <div><?= $employee['salary'] ? '₱' . number_format($employee['salary'], 2) : '-' ?></div>
                </div>
                <div class="col-md-6">
                    <div class="fw-600 text-muted small">Status</div>
                    <div>
                        <span class="badge badge-<?= $employee['status'] ?>">
                            <?= htmlspecialchars(ucfirst($employee['status'])) ?>
                        </span>
                    </div>
                </div>

                <div class="col-12">
                    <div class="fw-600 text-muted small">Created</div>
                    <div><?= htmlspecialchars($employee['created_at']) ?> · Last updated: <?= htmlspecialchars($employee['updated_at']) ?></div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" onclick="if(document.getElementById('viewModal')){var vm=bootstrap.Modal.getInstance(document.getElementById('viewModal')); if(vm) vm.hide();} loadEditModal(<?= $employee['id'] ?>)"><i class="fa fa-pen"></i> Edit</button>
                <a href="index.php" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    </div>
</body>
</html>
