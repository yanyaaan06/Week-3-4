<?php
/**
 * Utility Functions
 * Helper functions for validation and security
 */

/**
 * Sanitize input data
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email
 * @param string $email
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate date format
 * @param string $date
 * @return bool
 */
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Validate decimal/numeric
 * @param string $value
 * @return bool
 */
function validateDecimal($value) {
    return is_numeric($value) && $value >= 0;
}

/**
 * Generate employee code
 * @param PDO $conn
 * @return string
 */
function generateEmployeeCode($conn) {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM employees");
    $result = $stmt->fetch();
    $count = $result['count'] + 1;
    return 'EMP' . str_pad($count, 5, '0', STR_PAD_LEFT);
}

/**
 * Generate product code
 * @param PDO $conn
 * @param string $category
 * @return string
 */
function generateProductCode($conn, $category) {
    $prefix = strtoupper(substr($category, 0, 3));
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category LIKE ?");
    $stmt->execute([$category . '%']);
    $result = $stmt->fetch();
    $count = $result['count'] + 1;
    return $prefix . str_pad($count, 5, '0', STR_PAD_LEFT);
}
