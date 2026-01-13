<?php
/**
 * Database Setup Script
 * Run this file once to create the database and tables
 * Access: http://localhost/crud/setup_database.php
 */

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'crud_db';

try {
    // Connect to MySQL (without database)
    $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db_name`");
    
    echo "<h2>Database Setup</h2>";
    echo "<p>Database '$db_name' created/verified successfully!</p>";
    
    // Read and execute schema.sql
    $schema_file = __DIR__ . '/schema.sql';
    
    if (file_exists($schema_file)) {
        $sql = file_get_contents($schema_file);
        
        // Remove comments and split by semicolon
        $sql = preg_replace('/--.*$/m', '', $sql);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        $executed = 0;
        $errors = [];
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $pdo->exec($statement);
                    $executed++;
                } catch (PDOException $e) {
                    // Ignore "table already exists" errors
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        $errors[] = $e->getMessage();
                    }
                }
            }
        }
        
        echo "<p>Executed $executed SQL statements.</p>";
        
        if (!empty($errors)) {
            echo "<h3>Errors (non-critical):</h3>";
            echo "<ul>";
            foreach ($errors as $error) {
                echo "<li>" . htmlspecialchars($error) . "</li>";
            }
            echo "</ul>";
        }
        
        // Verify tables
        $tables = ['employees', 'products', 'employee_products'];
        echo "<h3>Table Verification:</h3>";
        echo "<ul>";
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<li>✓ Table '$table' exists</li>";
            } else {
                echo "<li>✗ Table '$table' NOT found</li>";
            }
        }
        echo "</ul>";
        
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
        echo "<h3>✓ Setup Complete!</h3>";
        echo "<p>You can now <a href='index.php'>access the application</a>.</p>";
        echo "<p><strong>Note:</strong> For security, delete or rename this file after setup.</p>";
        echo "</div>";
        
    } else {
        echo "<p style='color: red;'>Error: schema.sql file not found!</p>";
    }
    
} catch (PDOException $e) {
    echo "<h2>Setup Error</h2>";
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database configuration in this file.</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h2, h3 {
            color: #2c3e50;
        }
        ul {
            margin: 10px 0;
        }
        a {
            color: #3498db;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
</body>
</html>
