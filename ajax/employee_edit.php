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

<form method="POST">
    <input type="hidden" name="action" value="update_employee">
    <input type="hidden" name="employee_id" value="<?php echo (int)$employeeId; ?>">
    <div class="form-group">
        <label for="edit_first_name">First Name</label>
        <input type="text" class="form-control" id="edit_first_name" name="first_name" value="<?php echo htmlspecialchars($employee['first_name'] ?? ''); ?>">
    </div>
    <div class="form-group">
        <label for="edit_last_name">Last Name</label>
        <input type="text" class="form-control" id="edit_last_name" name="last_name" value="<?php echo htmlspecialchars($employee['last_name'] ?? ''); ?>">
    </div>
    <div class="form-group">
        <label for="edit_email">Email</label>
        <input type="email" class="form-control" id="edit_email" name="email" value="<?php echo htmlspecialchars($employee['email'] ?? ''); ?>">
    </div>
    <div class="form-group">
        <label for="edit_department">Department</label>
        <input type="text" class="form-control" id="edit_department" name="department" value="<?php echo htmlspecialchars($employee['department_name'] ?? ''); ?>">
    </div>
    <div class="form-group">
        <label for="edit_position">Position</label>
        <input type="text" class="form-control" id="edit_position" name="position" value="<?php echo htmlspecialchars($employee['position_title'] ?? ''); ?>">
    </div>
    <div class="form-group">
        <label for="edit_status">Status</label>
        <select class="form-control" id="edit_status" name="status">
            <option value="active" <?php echo ($employee['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?php echo ($employee['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            <option value="suspended" <?php echo ($employee['status'] ?? '') === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
        </select>
    </div>
    <div class="modal-footer px-0">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
</form>
