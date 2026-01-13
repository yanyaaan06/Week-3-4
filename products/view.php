<?php
/**
 * View Product Details
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

$id = $_GET['id'] ?? 0;

if (empty($id) || !is_numeric($id)) {
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT p.*, e.first_name, e.last_name, e.employee_code 
                        FROM products p 
                        LEFT JOIN employees e ON p.created_by = e.id 
                        WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Product - CRUD System</title>
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
                <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width:72px;height:72px;border-radius:12px;">
                    <i class="fa fa-box fa-2x text-muted"></i>
                </div>
                <div>
                    <h4 class="mb-0"><?= htmlspecialchars($product['name']) ?></h4>
                    <small class="text-muted"><?= htmlspecialchars($product['product_code']) ?></small>
                </div>
                <div class="ms-auto text-end">
                    <span class="badge bg-secondary">Category: <?= htmlspecialchars($product['category']) ?></span>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="fw-600 text-muted small">Price</div>
                    <div>₱<?= number_format($product['price'], 2) ?></div>
                </div>
                <div class="col-md-6">
                    <div class="fw-600 text-muted small">Cost</div>
                    <div><?= $product['cost'] ? '₱' . number_format($product['cost'], 2) : '-' ?></div>
                </div>

                <div class="col-md-6">
                    <div class="fw-600 text-muted small">Stock</div>
                    <div>
                        <?= $product['stock_quantity'] ?> <?= htmlspecialchars($product['unit']) ?>
                        <?php if ($product['stock_quantity'] <= $product['min_stock_level']): ?>
                            <span class="badge bg-warning ms-2">Low Stock</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fw-600 text-muted small">Min Stock</div>
                    <div><?= $product['min_stock_level'] ?> <?= htmlspecialchars($product['unit']) ?></div>
                </div>

                <div class="col-12">
                    <div class="fw-600 text-muted small">Description</div>
                    <div><?= htmlspecialchars($product['description'] ?: '-') ?></div>
                </div>

                <div class="col-md-6">
                    <div class="fw-600 text-muted small">Status</div>
                    <div>
                        <span class="badge badge-<?= $product['status'] ?>">
                            <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $product['status']))) ?>
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fw-600 text-muted small">Created By</div>
                    <div>
                        <?php if ($product['employee_code']): ?>
                            <?= htmlspecialchars($product['employee_code'] . ' - ' . $product['first_name'] . ' ' . $product['last_name']) ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-12">
                    <div class="fw-600 text-muted small">Created</div>
                    <div><?= htmlspecialchars($product['created_at']) ?> · Last updated: <?= htmlspecialchars($product['updated_at']) ?></div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" onclick="if(document.getElementById('viewModal')){var vm=bootstrap.Modal.getInstance(document.getElementById('viewModal')); if(vm) vm.hide();} loadEditModal(<?= $product['id'] ?>)"><i class="fa fa-pen"></i> Edit</button>
                <a href="index.php" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
