<?php
/**
 * Delete Product
 * Soft-delete vs Hard-delete implementation
 * Code Review: Uses prepared statements, validates input, supports both delete types
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

$id = $_GET['id'] ?? 0;
$delete_type = $_GET['type'] ?? 'soft'; // 'soft' or 'hard'

if (empty($id) || !is_numeric($id)) {
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();

// Fetch product to verify existence
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm_delete = $_POST['confirm_delete'] ?? '';
    $delete_type = $_POST['delete_type'] ?? 'soft';
    
    if ($confirm_delete === 'yes') {
        try {
            if ($delete_type === 'hard') {
                // Hard Delete: Permanently remove from database
                $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$id]);
                $success = 'Product permanently deleted from database.';
                
                // Redirect after hard delete
                header('Location: index.php?deleted=hard');
                exit;
            } else {
                // Soft Delete: Update status to 'discontinued'
                $stmt = $conn->prepare("UPDATE products SET status = 'discontinued' WHERE id = ?");
                $stmt->execute([$id]);
                $success = 'Product status updated to discontinued (soft delete).';
                
                // Refresh product data
                $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->execute([$id]);
                $product = $stmt->fetch();
            }
        } catch (PDOException $e) {
            $error = 'Error deleting product: ' . $e->getMessage();
        }
    } else {
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
    <title>Delete Product - YMPH CRUD System</title>
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
        <h1>Delete Product</h1>
        
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
                    <h5 class="mb-0">Product Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> <?= htmlspecialchars($product['name']) ?></p>
                    <p><strong>Product Code:</strong> <?= htmlspecialchars($product['product_code']) ?></p>
                    <p><strong>Category:</strong> <?= htmlspecialchars($product['category']) ?></p>
                    <p><strong>Price:</strong> ₱<?= number_format($product['price'], 2) ?></p>
                    <p><strong>Stock Quantity:</strong> <?= $product['stock_quantity'] ?></p>
                    <p><strong>Current Status:</strong> 
                        <span class="badge badge-<?= $product['status'] ?>">
                            <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $product['status']))) ?>
                        </span>
                    </p>
                </div>
            </div>
            
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Delete Options</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-bold">Soft Delete (Recommended)</h6>
                        <p>Updates the product status to "discontinued" but keeps the record in the database.</p>
                        <ul class="small">
                            <li>✓ Data is preserved for audit/history</li>
                            <li>✓ Can be restored if needed</li>
                            <li>✓ Maintains referential integrity</li>
                            <li>✓ Records remain for reporting</li>
                        </ul>
                    </div>
                    
                    <hr>
                    
                    <div>
                        <h6 class="fw-bold">Hard Delete</h6>
                        <p>Permanently removes the product record from the database.</p>
                        <ul class="small">
                            <li>⚠ Data is permanently lost</li>
                            <li>⚠ Cannot be recovered</li>
                            <li>⚠ May break foreign key relationships</li>
                            <li>⚠ Use with extreme caution</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <form method="POST" action="" class="card p-4 shadow-sm">
                <input type="hidden" name="confirm_delete" value="yes">
                
                <div class="mb-3">
                    <label for="delete_type" class="form-label">Delete Type:</label>
                    <select id="delete_type" name="delete_type" class="form-select" required>
                        <option value="soft" <?= $delete_type === 'soft' ? 'selected' : '' ?>>Soft Delete (Update Status)</option>
                        <option value="hard" <?= $delete_type === 'hard' ? 'selected' : '' ?>>Hard Delete (Permanent Removal)</option>
                    </select>
                </div>
                
                <div class="alert alert-warning" role="alert">
                    <strong>Warning:</strong> 
                    <span id="warning-text">
                        This will update the product status to "discontinued". The record will remain in the database.
                    </span>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger" id="delete-btn">Confirm Delete</button>
                    <a href="view.php?id=<?= $product['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
            
            <script>
                document.getElementById('delete_type').addEventListener('change', function() {
                    const type = this.value;
                    const warningText = document.getElementById('warning-text');
                    const deleteBtn = document.getElementById('delete-btn');
                    
                    if (type === 'hard') {
                        warningText.textContent = 'This will PERMANENTLY DELETE the product record from the database. This action CANNOT be undone!';
                        deleteBtn.textContent = 'Permanently Delete';
                    } else {
                        warningText.textContent = 'This will update the product status to "discontinued". The record will remain in the database.';
                        deleteBtn.textContent = 'Confirm Delete';
                    }
                });
            </script>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
