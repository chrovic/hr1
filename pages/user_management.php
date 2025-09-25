<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || !$auth->hasPermission('manage_users')) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$db = getDB();

$message = '';
$error = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['create_user'])) {
        $userData = [
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'role' => $_POST['role'],
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'employee_id' => $_POST['employee_id'],
            'department' => $_POST['department'],
            'position' => $_POST['position'],
            'phone' => $_POST['phone'],
            'hire_date' => $_POST['hire_date']
        ];
        
        if ($auth->createUser($userData)) {
            $message = 'User created successfully!';
            $auth->logActivity('create_user', 'users', null, null, $userData);
        } else {
            $error = 'Failed to create user.';
        }
    }
    
    if (isset($_POST['update_user'])) {
        $userId = $_POST['user_id'];
        $userData = [
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'role' => $_POST['role'],
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'employee_id' => $_POST['employee_id'],
            'department' => $_POST['department'],
            'position' => $_POST['position'],
            'phone' => $_POST['phone'],
            'status' => $_POST['status']
        ];
        
        if ($auth->updateUser($userId, $userData)) {
            $message = 'User updated successfully!';
            $auth->logActivity('update_user', 'users', $userId, null, $userData);
        } else {
            $error = 'Failed to update user.';
        }
    }
    
    if (isset($_POST['delete_user'])) {
        $userId = $_POST['user_id'];
        if ($auth->deleteUser($userId)) {
            $message = 'User deactivated successfully!';
            $auth->logActivity('delete_user', 'users', $userId, null, null);
        } else {
            $error = 'Failed to deactivate user.';
        }
    }
}

// Get all users
$users = $auth->getAllUsers();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">User Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createUserModal">
            <i class="fe fe-plus fe-16 mr-2"></i>Create User
        </button>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">Users</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fe fe-users fe-48 mb-3"></i>
                                        <p>No users found.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="assets/images/avatars/face-1.jpg" alt="Avatar" class="rounded-circle mr-2" width="32" height="32">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($user['employee_id']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'hr_manager' ? 'warning' : 'info'); ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['department']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($user['last_login']): ?>
                                                <?php echo date('M j, Y g:i A', strtotime($user['last_login'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Never</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                                    <i class="fe fe-edit fe-14"></i>
                                                </button>
                                                <?php if ($user['id'] != $current_user['id']): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                                        <i class="fe fe-trash fe-14"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New User</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username">Username *</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role">Role *</label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="hr_manager">HR Manager</option>
                                    <option value="employee">Employee</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="employee_id">Employee ID</label>
                                <input type="text" class="form-control" id="employee_id" name="employee_id">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="department">Department</label>
                                <input type="text" class="form-control" id="department" name="department">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="position">Position</label>
                                <input type="text" class="form-control" id="position" name="position">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="hire_date">Hire Date</label>
                                <input type="date" class="form-control" id="hire_date" name="hire_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_user" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_username">Username *</label>
                                <input type="text" class="form-control" id="edit_username" name="username" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_email">Email *</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_role">Role *</label>
                                <select class="form-control" id="edit_role" name="role" required>
                                    <option value="admin">Admin</option>
                                    <option value="hr_manager">HR Manager</option>
                                    <option value="employee">Employee</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_status">Status *</label>
                                <select class="form-control" id="edit_status" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="terminated">Terminated</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_first_name">First Name *</label>
                                <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_last_name">Last Name *</label>
                                <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_employee_id">Employee ID</label>
                                <input type="text" class="form-control" id="edit_employee_id" name="employee_id">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_department">Department</label>
                                <input type="text" class="form-control" id="edit_department" name="department">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_position">Position</label>
                                <input type="text" class="form-control" id="edit_position" name="position">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_phone">Phone</label>
                                <input type="text" class="form-control" id="edit_phone" name="phone">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="delete_user_id" name="user_id">
                    <p>Are you sure you want to deactivate this user? This action can be reversed later.</p>
                    <div class="alert alert-warning">
                        <strong>Note:</strong> The user will be marked as inactive and will not be able to login.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_user" class="btn btn-danger">Deactivate User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editUser(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_status').value = user.status;
    document.getElementById('edit_first_name').value = user.first_name;
    document.getElementById('edit_last_name').value = user.last_name;
    document.getElementById('edit_employee_id').value = user.employee_id || '';
    document.getElementById('edit_department').value = user.department || '';
    document.getElementById('edit_position').value = user.position || '';
    document.getElementById('edit_phone').value = user.phone || '';
    
    $('#editUserModal').modal('show');
}

function deleteUser(userId) {
    document.getElementById('delete_user_id').value = userId;
    $('#deleteUserModal').modal('show');
}
</script>
