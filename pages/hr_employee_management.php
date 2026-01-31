<?php
require_once 'includes/data/db.php';

require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/hr_manager.php';

// Get database connection
$db = getDB();

// Initialize authentication
$auth = new SimpleAuth();

// Check if user is logged in and is HR Manager or Admin
if (!$auth->isLoggedIn() || (!$auth->isHRManager() && !$auth->isAdmin())) {
    header('Location: auth/login.php');
    exit;
}

// Initialize HR Manager
$hr_manager = new HRManager($db);

// Get employee data
$search = $_GET['search'] ?? '';
$department = $_GET['department'] ?? '';
$status = $_GET['status'] ?? 'active';

// Detect schema differences
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
    // fallback to default assumptions
}

// Build query
$where_conditions = ["u.role = 'employee'"];
$params = [];

if ($search) {
    $where_conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($department) {
    if ($hasDeptId) {
        $where_conditions[] = "u.department_id = ?";
        $params[] = $department;
    } else {
        $where_conditions[] = "u.department = ?";
        $params[] = $department;
    }
}

if ($status) {
    $where_conditions[] = "u.status = ?";
    $params[] = $status;
}

$where_clause = implode(' AND ', $where_conditions);

// Get employees
try {
    $stmt = $db->prepare("
        SELECT 
            u.*,
            " . ($hasDeptId ? "d.name as department_name" : "u.department as department_name") . ",
            " . ($hasPositionId ? "p.title as position_title" : "u.position as position_title") . ",
            COUNT(e.id) as evaluation_count,
            AVG(e.overall_score) as avg_rating
        FROM users u
        " . ($hasDeptId ? "LEFT JOIN departments d ON u.department_id = d.id" : "") . "
        " . ($hasPositionId ? "LEFT JOIN positions p ON u.position_id = p.id" : "") . "
        LEFT JOIN evaluations e ON u.id = e.employee_id AND e.status = 'completed'
        WHERE $where_clause
        GROUP BY u.id
        ORDER BY u.last_name, u.first_name
    ");
    $stmt->execute($params);
    $employees = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Employee management error: " . $e->getMessage());
    $employees = [];
}

// Get departments for filter
try {
    if ($hasDeptId) {
        $stmt = $db->prepare("SELECT id, name FROM departments ORDER BY name");
        $stmt->execute();
        $departments = $stmt->fetchAll();
    } else {
        $stmt = $db->prepare("
            SELECT DISTINCT department as name
            FROM users
            WHERE department IS NOT NULL AND department <> ''
            ORDER BY department
        ");
        $stmt->execute();
        $departments = array_map(function($row) {
            return ['id' => $row['name'], 'name' => $row['name']];
        }, $stmt->fetchAll());
    }
} catch (PDOException $e) {
    $departments = [];
}

// Handle employee actions
if ($_POST['action'] ?? false) {
    $action = $_POST['action'];
    $employee_id = $_POST['employee_id'] ?? null;
    
    switch ($action) {
        case 'update_employee':
            if ($employee_id) {
                $firstName = $_POST['first_name'] ?? null;
                $lastName = $_POST['last_name'] ?? null;
                $email = $_POST['email'] ?? null;
                $statusValue = $_POST['status'] ?? null;
                $departmentValue = $_POST['department'] ?? null;
                $positionValue = $_POST['position'] ?? null;

                try {
                    $fields = [];
                    $params = [];
                    if ($firstName !== null) { $fields[] = "first_name = ?"; $params[] = $firstName; }
                    if ($lastName !== null) { $fields[] = "last_name = ?"; $params[] = $lastName; }
                    if ($email !== null) { $fields[] = "email = ?"; $params[] = $email; }
                    if ($statusValue !== null) { $fields[] = "status = ?"; $params[] = $statusValue; }
                    if (!$hasDeptId && $departmentValue !== null) { $fields[] = "department = ?"; $params[] = $departmentValue; }
                    if (!$hasPositionId && $positionValue !== null) { $fields[] = "position = ?"; $params[] = $positionValue; }

                    if (!empty($fields)) {
                        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
                        $params[] = $employee_id;
                        $stmt = $db->prepare($sql);
                        $stmt->execute($params);
                        $success_message = "Employee updated successfully.";
                    }
                } catch (PDOException $e) {
                    $error_message = "Failed to update employee.";
                }
            }
            break;
        case 'update_status':
            $new_status = $_POST['status'] ?? '';
            if ($employee_id && $new_status) {
                try {
                    $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
                    $stmt->execute([$new_status, $employee_id]);
                    $success_message = "Employee status updated successfully.";
                } catch (PDOException $e) {
                    $error_message = "Failed to update employee status.";
                }
            }
            break;
            
        case 'send_evaluation':
            if ($employee_id) {
                // Create evaluation for employee
                try {
                    $stmt = $db->prepare("
                        INSERT INTO evaluations (employee_id, evaluator_id, status, created_at, due_date)
                        VALUES (?, ?, 'pending', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY))
                    ");
                    $stmt->execute([$employee_id, $_SESSION['user_id']]);
                    $success_message = "Evaluation scheduled for employee.";
                } catch (PDOException $e) {
                    $error_message = "Failed to schedule evaluation.";
                }
            }
            break;
    }
}
?>

<div class="row">
    <div class="col-12">
        <div class="mb-2">
            <h1 class="h3 mb-1">Employee Management</h1>
            <p class="text-muted">Manage employee information, performance, and development</p>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Search & Filters</strong>
            </div>
            <div class="card-body">
                <form method="GET" class="row">
                    <input type="hidden" name="page" value="hr_employee_management">
                    <div class="col-md-4 mb-3">
                        <label for="search" class="form-label">Search Employees</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Name or email...">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="department" class="form-label">Department</label>
                        <select class="form-control" id="department" name="department">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" 
                                        <?php echo $department == $dept['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="suspended" <?php echo $status === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <span class="fe fe-search fe-16 mr-2"></span>
                                Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <span class="fe fe-check-circle fe-16 mr-2"></span>
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <span class="fe fe-alert-circle fe-16 mr-2"></span>
        <?php echo htmlspecialchars($error_message); ?>
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Employee List -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">
                    Employees (<?php echo count($employees); ?>)
                </strong>
            </div>
            <div class="card-body">
                <?php if (empty($employees)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fe fe-users fe-48 mb-3"></i>
                        <p class="mb-0">No employees found matching your criteria.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                    <th>Performance</th>
                                    <th>Evaluations</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $employee): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm mr-3">
                                                    <img src="assets/images/avatars/face-1.jpg" alt="Avatar" class="avatar-img rounded-circle">
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></strong>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($employee['email']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($employee['department_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($employee['position_title'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php
                                            $status_class = '';
                                            switch ($employee['status']) {
                                                case 'active':
                                                    $status_class = 'success';
                                                    break;
                                                case 'inactive':
                                                    $status_class = 'secondary';
                                                    break;
                                                case 'suspended':
                                                    $status_class = 'danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>">
                                                <?php echo ucfirst($employee['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($employee['avg_rating']): ?>
                                                <div class="d-flex align-items-center">
                                                    <span class="mr-2"><?php echo number_format($employee['avg_rating'], 1); ?></span>
                                                    <div class="progress" style="width: 60px; height: 8px;">
                                                        <div class="progress-bar" style="width: <?php echo ($employee['avg_rating'] / 5) * 100; ?>%"></div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">No rating</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-light"><?php echo $employee['evaluation_count']; ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" 
                                                        onclick="viewEmployee(<?php echo $employee['id']; ?>)">
                                                    <span class="fe fe-eye fe-12"></span>
                                                </button>
                                                <button type="button" class="btn btn-outline-success" 
                                                        onclick="scheduleEvaluation(<?php echo $employee['id']; ?>)">
                                                    <span class="fe fe-target fe-12"></span>
                                                </button>
                                                <button type="button" class="btn btn-outline-info" 
                                                        onclick="editEmployee(<?php echo $employee['id']; ?>)">
                                                    <span class="fe fe-edit fe-12"></span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Employee Actions Modal -->
<div class="modal fade" id="employeeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Employee Actions</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="employeeModalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewEmployee(employeeId) {
    // Load employee details
    document.getElementById('employeeModalBody').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    `;
    $('#employeeModal').modal('show');
    
    fetch('ajax/employee_details.php?id=' + encodeURIComponent(employeeId), { credentials: 'same-origin' })
        .then(function(response) { return response.text(); })
        .then(function(html) {
            document.getElementById('employeeModalBody').innerHTML = html;
        })
        .catch(function() {
            document.getElementById('employeeModalBody').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fe fe-alert-circle fe-16 mr-2"></i>
                    Failed to load employee details.
                </div>
            `;
        });
}

function scheduleEvaluation(employeeId) {
    if (confirm('Schedule evaluation for this employee?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="send_evaluation">
            <input type="hidden" name="employee_id" value="${employeeId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function editEmployee(employeeId) {
    // Load employee edit form
    document.getElementById('employeeModalBody').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    `;
    $('#employeeModal').modal('show');
    
    fetch('ajax/employee_edit.php?id=' + encodeURIComponent(employeeId), { credentials: 'same-origin' })
        .then(function(response) { return response.text(); })
        .then(function(html) {
            document.getElementById('employeeModalBody').innerHTML = html;
        })
        .catch(function() {
            document.getElementById('employeeModalBody').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fe fe-alert-circle fe-16 mr-2"></i>
                    Failed to load employee edit form.
                </div>
            `;
        });
}
</script>

