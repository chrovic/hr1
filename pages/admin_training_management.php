<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || !$auth->hasPermission('manage_training')) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$db = getDB();

$message = '';
$error = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['create_module'])) {
        $moduleData = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'category' => $_POST['category'],
            'duration_hours' => $_POST['duration_hours'],
            'difficulty_level' => $_POST['difficulty_level'],
            'prerequisites' => $_POST['prerequisites'],
            'learning_objectives' => $_POST['learning_objectives'],
            'created_by' => $current_user['id']
        ];
        
        try {
            $stmt = $db->prepare("
                INSERT INTO training_modules (title, description, category, duration_hours, difficulty_level, prerequisites, learning_objectives, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $result = $stmt->execute([
                $moduleData['title'],
                $moduleData['description'],
                $moduleData['category'],
                $moduleData['duration_hours'],
                $moduleData['difficulty_level'],
                $moduleData['prerequisites'],
                $moduleData['learning_objectives'],
                $moduleData['created_by']
            ]);
            
            if ($result) {
                $message = 'Training module created successfully!';
                $auth->logActivity('create_training_module', 'training_modules', null, null, $moduleData);
            } else {
                $error = 'Failed to create training module.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
    
    if (isset($_POST['update_module'])) {
        $moduleId = $_POST['module_id'];
        $moduleData = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'category' => $_POST['category'],
            'duration_hours' => $_POST['duration_hours'],
            'difficulty_level' => $_POST['difficulty_level'],
            'prerequisites' => $_POST['prerequisites'],
            'learning_objectives' => $_POST['learning_objectives'],
            'status' => $_POST['status']
        ];
        
        try {
            $stmt = $db->prepare("
                UPDATE training_modules SET 
                    title = ?, description = ?, category = ?, duration_hours = ?, 
                    difficulty_level = ?, prerequisites = ?, learning_objectives = ?, 
                    status = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $result = $stmt->execute([
                $moduleData['title'],
                $moduleData['description'],
                $moduleData['category'],
                $moduleData['duration_hours'],
                $moduleData['difficulty_level'],
                $moduleData['prerequisites'],
                $moduleData['learning_objectives'],
                $moduleData['status'],
                $moduleId
            ]);
            
            if ($result) {
                $message = 'Training module updated successfully!';
                $auth->logActivity('update_training_module', 'training_modules', $moduleId, null, $moduleData);
            } else {
                $error = 'Failed to update training module.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
    
    if (isset($_POST['delete_module'])) {
        $moduleId = $_POST['module_id'];
        
        try {
            $stmt = $db->prepare("UPDATE training_modules SET status = 'archived' WHERE id = ?");
            $result = $stmt->execute([$moduleId]);
            
            if ($result) {
                $message = 'Training module archived successfully!';
                $auth->logActivity('delete_training_module', 'training_modules', $moduleId, null, null);
            } else {
                $error = 'Failed to archive training module.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get all training modules
try {
    $stmt = $db->prepare("
        SELECT tm.*, u.first_name, u.last_name,
               COUNT(DISTINCT ts.id) as session_count,
               COUNT(DISTINCT te.id) as enrollment_count
        FROM training_modules tm
        LEFT JOIN users u ON tm.created_by = u.id
        LEFT JOIN training_sessions ts ON tm.id = ts.module_id
        LEFT JOIN training_enrollments te ON ts.id = te.session_id
        WHERE tm.status != 'archived'
        GROUP BY tm.id
        ORDER BY tm.created_at DESC
    ");
    $stmt->execute();
    $modules = $stmt->fetchAll();
} catch (PDOException $e) {
    $modules = [];
    $error = 'Failed to load training modules: ' . $e->getMessage();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Training Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createModuleModal">
            <i class="fe fe-plus fe-16 mr-2"></i>Create Module
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
                <h5 class="card-title mb-0">Training Modules</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Duration</th>
                                <th>Difficulty</th>
                                <th>Sessions</th>
                                <th>Enrollments</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($modules)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="fe fe-book-open fe-48 mb-3"></i>
                                        <p>No training modules found.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($modules as $module): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($module['title']); ?></strong>
                                                <div class="text-muted small"><?php echo htmlspecialchars(substr($module['description'], 0, 50)) . '...'; ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?php echo htmlspecialchars($module['category']); ?></span>
                                        </td>
                                        <td><?php echo $module['duration_hours']; ?> hours</td>
                                        <td>
                                            <span class="badge badge-<?php echo $module['difficulty_level'] === 'beginner' ? 'success' : ($module['difficulty_level'] === 'intermediate' ? 'warning' : 'danger'); ?>">
                                                <?php echo ucfirst($module['difficulty_level']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $module['session_count']; ?></td>
                                        <td><?php echo $module['enrollment_count']; ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $module['status'] === 'active' ? 'success' : ($module['status'] === 'draft' ? 'warning' : 'secondary'); ?>">
                                                <?php echo ucfirst($module['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($module['first_name'] . ' ' . $module['last_name']); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editModule(<?php echo htmlspecialchars(json_encode($module)); ?>)">
                                                    <i class="fe fe-edit fe-14"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteModule(<?php echo $module['id']; ?>)">
                                                    <i class="fe fe-trash fe-14"></i>
                                                </button>
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

<!-- Create Module Modal -->
<div class="modal fade" id="createModuleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Training Module</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="title">Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="category">Category *</label>
                                <select class="form-control" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="Leadership">Leadership</option>
                                    <option value="Technical">Technical</option>
                                    <option value="Soft Skills">Soft Skills</option>
                                    <option value="Management">Management</option>
                                    <option value="Communication">Communication</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="duration_hours">Duration (Hours) *</label>
                                <input type="number" class="form-control" id="duration_hours" name="duration_hours" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="difficulty_level">Difficulty Level *</label>
                                <select class="form-control" id="difficulty_level" name="difficulty_level" required>
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="prerequisites">Prerequisites</label>
                        <textarea class="form-control" id="prerequisites" name="prerequisites" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="learning_objectives">Learning Objectives</label>
                        <textarea class="form-control" id="learning_objectives" name="learning_objectives" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_module" class="btn btn-primary">Create Module</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Module Modal -->
<div class="modal fade" id="editModuleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Training Module</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="edit_module_id" name="module_id">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="edit_title">Title *</label>
                                <input type="text" class="form-control" id="edit_title" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_category">Category *</label>
                                <select class="form-control" id="edit_category" name="category" required>
                                    <option value="Leadership">Leadership</option>
                                    <option value="Technical">Technical</option>
                                    <option value="Soft Skills">Soft Skills</option>
                                    <option value="Management">Management</option>
                                    <option value="Communication">Communication</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description *</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_duration_hours">Duration (Hours) *</label>
                                <input type="number" class="form-control" id="edit_duration_hours" name="duration_hours" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_difficulty_level">Difficulty Level *</label>
                                <select class="form-control" id="edit_difficulty_level" name="difficulty_level" required>
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_status">Status *</label>
                                <select class="form-control" id="edit_status" name="status" required>
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_prerequisites">Prerequisites</label>
                        <textarea class="form-control" id="edit_prerequisites" name="prerequisites" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_learning_objectives">Learning Objectives</label>
                        <textarea class="form-control" id="edit_learning_objectives" name="learning_objectives" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_module" class="btn btn-primary">Update Module</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Module Modal -->
<div class="modal fade" id="deleteModuleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Archive</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="delete_module_id" name="module_id">
                    <p>Are you sure you want to archive this training module? This action can be reversed later.</p>
                    <div class="alert alert-warning">
                        <strong>Note:</strong> The module will be marked as archived and will not be available for new enrollments.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_module" class="btn btn-danger">Archive Module</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editModule(module) {
    document.getElementById('edit_module_id').value = module.id;
    document.getElementById('edit_title').value = module.title;
    document.getElementById('edit_description').value = module.description;
    document.getElementById('edit_category').value = module.category;
    document.getElementById('edit_duration_hours').value = module.duration_hours;
    document.getElementById('edit_difficulty_level').value = module.difficulty_level;
    document.getElementById('edit_status').value = module.status;
    document.getElementById('edit_prerequisites').value = module.prerequisites || '';
    document.getElementById('edit_learning_objectives').value = module.learning_objectives || '';
    
    $('#editModuleModal').modal('show');
}

function deleteModule(moduleId) {
    document.getElementById('delete_module_id').value = moduleId;
    $('#deleteModuleModal').modal('show');
}
</script>





