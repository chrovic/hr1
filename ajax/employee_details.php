<?php
require_once '../includes/data/db.php';
require_once '../includes/functions/simple_auth.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || (!$auth->isHRManager() && !$auth->isAdmin())) {
    http_response_code(403);
    echo '<div class="alert alert-danger">Unauthorized.</div>';
    exit;
}

$employeeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$employeeId) {
    echo '<div class="alert alert-warning">Invalid employee.</div>';
    exit;
}

$db = getDB();

$hasDeptId = false;
$hasPositionId = false;
try {
    $stmt = $db->prepare("
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME IN ('department_id', 'position_id')
    ");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $hasDeptId = in_array('department_id', $columns, true);
    $hasPositionId = in_array('position_id', $columns, true);
} catch (PDOException $e) {
}

try {
    $stmt = $db->prepare("
        SELECT u.*,
               " . ($hasDeptId ? "d.name as department_name" : "u.department as department_name") . ",
               " . ($hasPositionId ? "p.title as position_title" : "u.position as position_title") . "
        FROM users u
        " . ($hasDeptId ? "LEFT JOIN departments d ON u.department_id = d.id" : "") . "
        " . ($hasPositionId ? "LEFT JOIN positions p ON u.position_id = p.id" : "") . "
        WHERE u.id = ?
        LIMIT 1
    ");
    $stmt->execute([$employeeId]);
    $employee = $stmt->fetch();
} catch (PDOException $e) {
    $employee = null;
}

if (!$employee) {
    echo '<div class="alert alert-warning">Employee not found.</div>';
    exit;
}
?>

<div class="mb-3">
    <div class="text-muted small">Employee</div>
    <div class="font-weight-bold"><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></div>
    <div class="text-muted small"><?php echo htmlspecialchars($employee['email'] ?? ''); ?></div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="text-muted small">Department</div>
        <div><?php echo htmlspecialchars($employee['department_name'] ?? 'N/A'); ?></div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="text-muted small">Position</div>
        <div><?php echo htmlspecialchars($employee['position_title'] ?? 'N/A'); ?></div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="text-muted small">Status</div>
        <div><?php echo ucfirst($employee['status'] ?? 'active'); ?></div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="text-muted small">Hire Date</div>
        <div><?php echo !empty($employee['hire_date']) ? date('M d, Y', strtotime($employee['hire_date'])) : 'N/A'; ?></div>
    </div>
</div>

<div class="text-muted small">Actions</div>
<div>
    <form method="POST" class="d-inline">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="employee_id" value="<?php echo (int)$employeeId; ?>">
        <input type="hidden" name="status" value="active">
        <button type="submit" class="btn btn-sm btn-outline-success">Set Active</button>
    </form>
    <form method="POST" class="d-inline">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="employee_id" value="<?php echo (int)$employeeId; ?>">
        <input type="hidden" name="status" value="inactive">
        <button type="submit" class="btn btn-sm btn-outline-secondary">Set Inactive</button>
    </form>
    <form method="POST" class="d-inline">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="employee_id" value="<?php echo (int)$employeeId; ?>">
        <input type="hidden" name="status" value="suspended">
        <button type="submit" class="btn btn-sm btn-outline-danger">Suspend</button>
    </form>
</div>
