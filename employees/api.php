<?php
/**
 * API Endpoints for Employees
 * Returns JSON responses for AJAX requests
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

/**
 * Helper function to safely encode JSON with error handling
 */
function safeJsonEncode($data) {
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        $error = json_last_error_msg();
        return json_encode(['success' => false, 'error' => 'JSON encoding error: ' . $error]);
    }
    return $json;
}

$action = $_GET['action'] ?? '';
$conn = getDBConnection();

try {
    switch ($action) {
        case 'get_employee':
            // GET: Fetch single employee by ID
            $id = $_GET['id'] ?? 0;
            if (empty($id) || !is_numeric($id)) {
                echo safeJsonEncode(['success' => false, 'error' => 'Invalid employee ID']);
                exit;
            }
            
            $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
            $stmt->execute([$id]);
            $employee = $stmt->fetch();
            
            if ($employee) {
                echo safeJsonEncode(['success' => true, 'data' => $employee]);
            } else {
                echo safeJsonEncode(['success' => false, 'error' => 'Employee not found']);
            }
            break;
            
        case 'get_employees':
            // GET: Fetch employees list with filters
            $search = $_GET['search'] ?? '';
            $status_filter = $_GET['status'] ?? '';
            $department_filter = $_GET['department'] ?? '';
            $position_filter = $_GET['position'] ?? '';
            
            $sql = "SELECT * FROM employees WHERE 1=1";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR employee_code LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
            }
            
            if (!empty($department_filter)) {
                $sql .= " AND department = ?";
                $params[] = $department_filter;
            }
            
            if (!empty($position_filter)) {
                $sql .= " AND position = ?";
                $params[] = $position_filter;
            }
            
            if (!empty($status_filter)) {
                $sql .= " AND status = ?";
                $params[] = $status_filter;
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $employees = $stmt->fetchAll();
            
            // Get filter options
            $depts = $conn->query("SELECT DISTINCT department FROM employees ORDER BY department")->fetchAll(PDO::FETCH_COLUMN);
            $positions = $conn->query("SELECT DISTINCT position FROM employees ORDER BY position")->fetchAll(PDO::FETCH_COLUMN);
            
            echo safeJsonEncode([
                'success' => true,
                'data' => $employees,
                'filters' => [
                    'departments' => $depts,
                    'positions' => $positions
                ]
            ]);
            break;
            
        default:
            echo safeJsonEncode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
} catch (PDOException $e) {
    echo safeJsonEncode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo safeJsonEncode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
}
