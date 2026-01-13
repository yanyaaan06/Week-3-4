<?php
/**
 * Create Product
 * Form + PHP with prepared statements
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

$error = '';
$success = '';

// Get employees for created_by dropdown
$conn = getDBConnection();
$stmt = $conn->query("SELECT id, first_name, last_name, employee_code FROM employees WHERE status = 'active' ORDER BY last_name, first_name");
$employees = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $product_code = sanitizeInput($_POST['product_code'] ?? '');
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $category = sanitizeInput($_POST['category'] ?? '');
    $price = $_POST['price'] ?? '';
    $cost = $_POST['cost'] ?? '';
    $stock_quantity = $_POST['stock_quantity'] ?? 0;
    $min_stock_level = $_POST['min_stock_level'] ?? 0;
    $unit = sanitizeInput($_POST['unit'] ?? 'unit');
    $status = $_POST['status'] ?? 'active';
    $created_by = $_POST['created_by'] ?? null;
    
    // Validation
    if (empty($name) || empty($category) || empty($price)) {
        $error = 'Please fill in all required fields (Name, Category, Price).';
    } elseif (!validateDecimal($price)) {
        $error = 'Price must be a valid number.';
    } elseif (!empty($cost) && !validateDecimal($cost)) {
        $error = 'Cost must be a valid number.';
    } elseif (!is_numeric($stock_quantity) || $stock_quantity < 0) {
        $error = 'Stock quantity must be a valid non-negative number.';
    } elseif (!is_numeric($min_stock_level) || $min_stock_level < 0) {
        $error = 'Minimum stock level must be a valid non-negative number.';
    } else {
        // Generate product code if not provided
        if (empty($product_code)) {
            $product_code = generateProductCode($conn, $category);
        }
        
        try {
            // Use prepared statement to prevent SQL injection
            $stmt = $conn->prepare("
                INSERT INTO products (product_code, name, description, category, price, cost, stock_quantity, min_stock_level, unit, status, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $product_code,
                $name,
                $description,
                $category,
                $price,
                $cost ?: null,
                $stock_quantity,
                $min_stock_level,
                $unit,
                $status,
                $created_by ?: null
            ]);
            
            $success = 'Product created successfully!';
            $_POST = [];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Product code already exists.';
            } else {
                $error = 'Error creating product: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product - YMPH CRUD System</title>
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
        <h1>Create New Product</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success" role="alert"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label for="product_code" class="form-label">Product Code (auto-generated if empty)</label>
                <input type="text" id="product_code" name="product_code" class="form-control"
                       value="<?= htmlspecialchars($_POST['product_code'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="name" class="form-label">Product Name *</label>
                <input type="text" id="name" name="name" class="form-control" required
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="category" class="form-label">Category *</label>
                <input type="text" id="category" name="category" class="form-control" required
                       value="<?= htmlspecialchars($_POST['category'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="price" class="form-label">Price *</label>
                <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required
                       value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="cost" class="form-label">Cost</label>
                <input type="number" id="cost" name="cost" class="form-control" step="0.01" min="0"
                       value="<?= htmlspecialchars($_POST['cost'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="stock_quantity" class="form-label">Stock Quantity</label>
                <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" min="0" value="<?= htmlspecialchars($_POST['stock_quantity'] ?? 0) ?>">
            </div>
            
            <div class="mb-3">
                <label for="min_stock_level" class="form-label">Minimum Stock Level</label>
                <input type="number" id="min_stock_level" name="min_stock_level" class="form-control" min="0" value="<?= htmlspecialchars($_POST['min_stock_level'] ?? 0) ?>">
            </div>
            
            <div class="mb-3">
                <label for="unit" class="form-label">Unit</label>
                <input type="text" id="unit" name="unit" class="form-control" value="<?= htmlspecialchars($_POST['unit'] ?? 'unit') ?>">
            </div>
            
            <div class="mb-3">
                <label for="status" class="form-label">Status *</label>
                <select id="status" name="status" class="form-select" required>
                    <option value="active" <?= ($_POST['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="discontinued" <?= ($_POST['status'] ?? '') === 'discontinued' ? 'selected' : '' ?>>Discontinued</option>
                    <option value="out_of_stock" <?= ($_POST['status'] ?? '') === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="created_by" class="form-label">Created By (Employee)</label>
                <select id="created_by" name="created_by" class="form-select">
                    <option value="">-- Select Employee (Optional) --</option>
                    <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= ($_POST['created_by'] ?? '') == $emp['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($emp['employee_code'] . ' - ' . $emp['first_name'] . ' ' . $emp['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-danger">Create Product</button>
                <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
