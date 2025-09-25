<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/learning.php';

$auth = new SimpleAuth();

// Check permissions
if (!$auth->hasPermission('manage_training')) {
    $error = 'You do not have permission to access this page.';
}

$current_user = $auth->getCurrentUser();
$learningManager = new LearningManager();
$db = getDB();

$message = '';
$error = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['create_skill'])) {
        $skillData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'category' => $_POST['category'],
            'skill_level' => $_POST['skill_level'],
            'created_by' => $current_user['id']
        ];
        
        $stmt = $db->prepare("INSERT INTO skills_catalog (name, description, category, skill_level, created_by) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$skillData['name'], $skillData['description'], $skillData['category'], $skillData['skill_level'], $skillData['created_by']])) {
            $message = 'Skill added successfully!';
        } else {
            $error = 'Failed to add skill.';
        }
    }
    
    if (isset($_POST['create_certification'])) {
        $certData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'issuing_body' => $_POST['issuing_body'],
            'validity_period_months' => $_POST['validity_period_months'],
            'renewal_required' => isset($_POST['renewal_required']) ? 1 : 0,
            'cost' => $_POST['cost'],
            'exam_required' => isset($_POST['exam_required']) ? 1 : 0,
            'created_by' => $current_user['id']
        ];
        
        $stmt = $db->prepare("INSERT INTO certifications_catalog (name, description, issuing_body, validity_period_months, renewal_required, cost, exam_required, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$certData['name'], $certData['description'], $certData['issuing_body'], $certData['validity_period_months'], $certData['renewal_required'], $certData['cost'], $certData['exam_required'], $certData['created_by']])) {
            $message = 'Certification added successfully!';
        } else {
            $error = 'Failed to add certification.';
        }
    }
    
    if (isset($_POST['create_learning_path'])) {
        $pathData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'target_role' => $_POST['target_role'],
            'estimated_duration_days' => $_POST['estimated_duration_days'],
            'learning_objectives' => $_POST['learning_objectives'],
            'created_by' => $current_user['id']
        ];
        
        $stmt = $db->prepare("INSERT INTO learning_paths (name, description, target_role, estimated_duration_days, learning_objectives, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$pathData['name'], $pathData['description'], $pathData['target_role'], $pathData['estimated_duration_days'], $pathData['learning_objectives'], $pathData['created_by']])) {
            $message = 'Learning path created successfully!';
        } else {
            $error = 'Failed to create learning path.';
        }
    }
}

// Get data for display
try {
    $trainings = $learningManager->getAllTrainings();
    $skills = $learningManager->getAllSkills();
    $certifications = $learningManager->getAllCertifications();
    $learningPaths = $learningManager->getAllLearningPaths();
    $analytics = $learningManager->getLearningAnalytics();
    
    // Calculate statistics
    $total_trainings = count($trainings);
    $total_skills = count($skills);
    $total_certifications = count($certifications);
    $total_learning_paths = count($learningPaths);
    
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
    $trainings = [];
    $skills = [];
    $certifications = [];
    $learningPaths = [];
    $analytics = [];
    $total_trainings = 0;
    $total_skills = 0;
    $total_certifications = 0;
    $total_learning_paths = 0;
}
?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Learning Management System</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group mr-2">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createSkillModal">
                <i class="fe fe-plus fe-16 mr-2"></i>Add Skill
            </button>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#createCertificationModal">
                <i class="fe fe-award fe-16 mr-2"></i>Add Certification
            </button>
            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#createLearningPathModal">
                <i class="fe fe-map fe-16 mr-2"></i>Create Learning Path
            </button>
        </div>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Learning Analytics Dashboard -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted mb-2">Training Courses</h6>
                        <span class="h2 mb-0"><?php echo $total_trainings; ?></span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-book-open fe-24 text-primary"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted mb-2">Skills Catalog</h6>
                        <span class="h2 mb-0"><?php echo $total_skills; ?></span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-zap fe-24 text-success"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted mb-2">Certifications</h6>
                        <span class="h2 mb-0"><?php echo $total_certifications; ?></span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-award fe-24 text-warning"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted mb-2">Learning Paths</h6>
                        <span class="h2 mb-0"><?php echo $total_learning_paths; ?></span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-map fe-24 text-info"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Skills Management -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Skills Catalog</strong>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Skill Name</th>
                                <th>Category</th>
                                <th>Level</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($skills)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No skills found. <a href="#" data-toggle="modal" data-target="#createSkillModal">Add your first skill</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($skills as $skill): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($skill['name']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary"><?php echo htmlspecialchars($skill['category']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $skill['skill_level'] === 'expert' ? 'danger' : ($skill['skill_level'] === 'advanced' ? 'warning' : ($skill['skill_level'] === 'intermediate' ? 'info' : 'success')); ?>">
                                                <?php echo ucfirst($skill['skill_level']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars(substr($skill['description'] ?? '', 0, 100)) . (strlen($skill['description'] ?? '') > 100 ? '...' : ''); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $skill['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($skill['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary" onclick="editSkill(<?php echo htmlspecialchars(json_encode($skill)); ?>)">
                                                    <i class="fe fe-edit fe-14"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteSkill(<?php echo $skill['id']; ?>)">
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

<!-- Certifications Management -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Certifications Catalog</strong>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Certification</th>
                                <th>Issuing Body</th>
                                <th>Validity</th>
                                <th>Cost</th>
                                <th>Exam Required</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($certifications)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No certifications found. <a href="#" data-toggle="modal" data-target="#createCertificationModal">Add your first certification</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($certifications as $cert): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($cert['name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars(substr($cert['description'] ?? '', 0, 80)) . (strlen($cert['description'] ?? '') > 80 ? '...' : ''); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($cert['issuing_body']); ?></td>
                                        <td>
                                            <?php echo $cert['validity_period_months']; ?> months
                                            <?php if ($cert['renewal_required']): ?>
                                                <br><small class="text-warning">Renewal required</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>$<?php echo number_format($cert['cost'], 2); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $cert['exam_required'] ? 'warning' : 'success'; ?>">
                                                <?php echo $cert['exam_required'] ? 'Yes' : 'No'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $cert['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($cert['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary" onclick="editCertification(<?php echo htmlspecialchars(json_encode($cert)); ?>)">
                                                    <i class="fe fe-edit fe-14"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteCertification(<?php echo $cert['id']; ?>)">
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

<!-- Learning Paths Management -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Learning Paths</strong>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Learning Path</th>
                                <th>Target Role</th>
                                <th>Duration</th>
                                <th>Modules</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($learningPaths)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No learning paths found. <a href="#" data-toggle="modal" data-target="#createLearningPathModal">Create your first learning path</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($learningPaths as $path): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($path['name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars(substr($path['description'] ?? '', 0, 100)) . (strlen($path['description'] ?? '') > 100 ? '...' : ''); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?php echo htmlspecialchars($path['target_role']); ?></span>
                                        </td>
                                        <td><?php echo $path['estimated_duration_days']; ?> days</td>
                                        <td>
                                            <span class="badge badge-secondary"><?php echo $path['module_count']; ?> modules</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $path['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($path['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewLearningPath(<?php echo $path['id']; ?>)">
                                                    <i class="fe fe-eye fe-14"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-info" onclick="editLearningPath(<?php echo htmlspecialchars(json_encode($path)); ?>)">
                                                    <i class="fe fe-edit fe-14"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteLearningPath(<?php echo $path['id']; ?>)">
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

<!-- Create Skill Modal -->
<div class="modal fade" id="createSkillModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Skill</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Skill Name *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Category *</label>
                                <select class="form-control" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="Technical">Technical</option>
                                    <option value="Management">Management</option>
                                    <option value="Soft Skills">Soft Skills</option>
                                    <option value="Business">Business</option>
                                    <option value="Creative">Creative</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Skill Level *</label>
                                <select class="form-control" name="skill_level" required>
                                    <option value="">Select Level</option>
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                    <option value="expert">Expert</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Description *</label>
                                <textarea class="form-control" name="description" rows="3" required></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_skill" class="btn btn-primary">Add Skill</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Certification Modal -->
<div class="modal fade" id="createCertificationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Certification</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Certification Name *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Issuing Body *</label>
                                <input type="text" class="form-control" name="issuing_body" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Validity Period (Months) *</label>
                                <input type="number" class="form-control" name="validity_period_months" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Cost ($)</label>
                                <input type="number" class="form-control" name="cost" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="renewal_required" id="renewal_required">
                                    <label class="form-check-label" for="renewal_required">Renewal Required</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="exam_required" id="exam_required">
                                    <label class="form-check-label" for="exam_required">Exam Required</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Description *</label>
                                <textarea class="form-control" name="description" rows="3" required></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_certification" class="btn btn-success">Add Certification</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Learning Path Modal -->
<div class="modal fade" id="createLearningPathModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Learning Path</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Learning Path Name *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Target Role *</label>
                                <input type="text" class="form-control" name="target_role" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Estimated Duration (Days) *</label>
                                <input type="number" class="form-control" name="estimated_duration_days" min="1" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Description *</label>
                                <textarea class="form-control" name="description" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Learning Objectives *</label>
                                <textarea class="form-control" name="learning_objectives" rows="3" required placeholder="What will participants learn?"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_learning_path" class="btn btn-info">Create Learning Path</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editSkill(skill) {
    // TODO: Implement skill editing
    alert('Skill editing functionality will be implemented soon.');
}

function deleteSkill(skillId) {
    if (confirm('Are you sure you want to delete this skill?')) {
        // TODO: Implement skill deletion
        alert('Skill deletion functionality will be implemented soon.');
    }
}

function editCertification(certification) {
    // TODO: Implement certification editing
    alert('Certification editing functionality will be implemented soon.');
}

function deleteCertification(certificationId) {
    if (confirm('Are you sure you want to delete this certification?')) {
        // TODO: Implement certification deletion
        alert('Certification deletion functionality will be implemented soon.');
    }
}

function viewLearningPath(pathId) {
    // TODO: Implement learning path viewing
    alert('Learning path viewing functionality will be implemented soon.');
}

function editLearningPath(path) {
    // TODO: Implement learning path editing
    alert('Learning path editing functionality will be implemented soon.');
}

function deleteLearningPath(pathId) {
    if (confirm('Are you sure you want to delete this learning path?')) {
        // TODO: Implement learning path deletion
        alert('Learning path deletion functionality will be implemented soon.');
    }
}
</script>


