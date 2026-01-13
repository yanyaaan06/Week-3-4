<?php
/**
 * Database Repair Script
 * Fixes AUTO_INCREMENT issues and resets ID numbers to be sequential
 * Access: http://localhost/crud/repair_database.php
 */

require_once 'config/database.php';

$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repair_confirm'])) {
    try {
        $conn = getDBConnection();
        
        // Repair employees table
        $messages[] = "Repairing employees table...";
        
        // Get the highest existing ID
        $result = $conn->query("SELECT MAX(id) as max_id FROM employees")->fetch();
        $next_id = ($result['max_id'] ?? 0) + 1;
        
        // Reset AUTO_INCREMENT
        $conn->exec("ALTER TABLE employees AUTO_INCREMENT = $next_id");
        $messages[] = "✓ Employees AUTO_INCREMENT reset to $next_id";
        
        // Repair products table
        $messages[] = "Repairing products table...";
        
        $result = $conn->query("SELECT MAX(id) as max_id FROM products")->fetch();
        $next_id = ($result['max_id'] ?? 0) + 1;
        
        $conn->exec("ALTER TABLE products AUTO_INCREMENT = $next_id");
        $messages[] = "✓ Products AUTO_INCREMENT reset to $next_id";
        
        // Repair employee_products table
        $messages[] = "Repairing employee_products table...";
        
        $result = $conn->query("SELECT MAX(id) as max_id FROM employee_products")->fetch();
        $next_id = ($result['max_id'] ?? 0) + 1;
        
        $conn->exec("ALTER TABLE employee_products AUTO_INCREMENT = $next_id");
        $messages[] = "✓ Employee_products AUTO_INCREMENT reset to $next_id";
        
        $messages[] = "<strong style='color: green;'>✓ Database repair completed successfully!</strong>";
        $messages[] = "New records will now have sequential IDs.";
        
    } catch (Exception $e) {
        $errors[] = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Repair - YMPH CRUD System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            color: #34495e;
            margin-top: 20px;
        }
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .info-box {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .error-box {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            color: #721c24;
        }
        .success-box {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            color: #155724;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px 5px 5px 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-primary:hover {
            background: #2980b9;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .message-list {
            list-style: none;
            padding: 0;
        }
        .message-list li {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .message-list li:last-child {
            border-bottom: none;
        }
        form {
            margin-top: 20px;
        }
        input[type="hidden"] {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Database Repair Tool</h1>
        
        <?php if (!empty($messages)): ?>
            <div class="success-box">
                <h2>Repair Results</h2>
                <ul class="message-list">
                    <?php foreach ($messages as $msg): ?>
                        <li><?= $msg ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <a href="index.php" class="btn btn-primary">← Back to Application</a>
        <?php elseif (!empty($errors)): ?>
            <div class="error-box">
                <h2>Error</h2>
                <ul class="message-list">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <div class="info-box">
                <h2>What This Tool Does</h2>
                <p>This tool fixes AUTO_INCREMENT counter issues in your database. This happens when:</p>
                <ul>
                    <li>Records are deleted, leaving gaps in IDs</li>
                    <li>IDs jump from 5 to 31 or other non-sequential numbers</li>
                    <li>New records get assigned high ID numbers instead of sequential ones</li>
                </ul>
            </div>
            
            <div class="warning-box">
                <h2>⚠️ Important Notes</h2>
                <ul>
                    <li><strong>This will NOT delete any data</strong> - only fixes AUTO_INCREMENT values</li>
                    <li><strong>Existing IDs will NOT change</strong> - only future IDs will be sequential</li>
                    <li><strong>Backup your database</strong> before proceeding (recommended)</li>
                </ul>
            </div>
            
            <form method="POST">
                <input type="hidden" name="repair_confirm" value="yes">
                <button type="submit" class="btn btn-danger">Repair Database</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
