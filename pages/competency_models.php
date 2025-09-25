<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/competency.php';
require_once 'includes/data/competency_templates.php';

// AJAX handling moved to standalone endpoint: ajax/get_model_details.php

// Regular authentication check for non-AJAX requests
$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || !$auth->hasPermission('manage_evaluations')) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$competencyManager = new CompetencyManager();
$db = getDB();

$message = '';
$error = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['create_model'])) {
        $modelData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'category' => $_POST['category'],
            'target_roles' => explode(',', $_POST['target_roles']),
            'assessment_method' => $_POST['assessment_method'],
            'created_by' => $current_user['id']
        ];
        
        if ($competencyManager->createModel($modelData)) {
            $message = 'Competency model created successfully!';
            $auth->logActivity('create_competency_model', 'competency_models', null, null, $modelData);
        } else {
            $error = 'Failed to create competency model.';
        }
    }
    
    if (isset($_POST['add_competency'])) {
        $competencyData = [
            'model_id' => $_POST['model_id'],
            'name' => $_POST['competency_name'],
            'description' => $_POST['competency_description'],
            'weight' => $_POST['weight'],
            'max_score' => $_POST['max_score']
        ];
        
        if ($competencyManager->addCompetency($competencyData)) {
            $message = 'Competency added successfully!';
            $auth->logActivity('add_competency', 'competencies', null, null, $competencyData);
        } else {
            $error = 'Failed to add competency.';
        }
    }
    
    if (isset($_POST['update_model'])) {
        $modelId = $_POST['model_id'];
        $updateData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'category' => $_POST['category'],
            'target_roles' => explode(',', $_POST['target_roles']),
            'assessment_method' => $_POST['assessment_method'],
            'status' => $_POST['status']
        ];
        
        if ($competencyManager->updateModel($modelId, $updateData)) {
            $message = 'Competency model updated successfully!';
            $auth->logActivity('update_competency_model', 'competency_models', $modelId, null, $updateData);
        } else {
            $error = 'Failed to update competency model.';
        }
    }
    
    if (isset($_POST['delete_model'])) {
        $modelId = $_POST['model_id'];
        
        if ($competencyManager->deleteModel($modelId)) {
            $message = 'Competency model deleted successfully!';
            $auth->logActivity('delete_competency_model', 'competency_models', $modelId, null, null);
        } else {
            $error = 'Failed to delete competency model.';
        }
    }
    
    if (isset($_POST['update_competency'])) {
        $competencyId = $_POST['competency_id'];
        $updateData = [
            'name' => $_POST['competency_name'],
            'description' => $_POST['competency_description'],
            'weight' => $_POST['weight'],
            'max_score' => $_POST['max_score']
        ];
        
        if ($competencyManager->updateCompetency($competencyId, $updateData)) {
            $message = 'Competency updated successfully!';
            $auth->logActivity('update_competency', 'competencies', $competencyId, null, $updateData);
        } else {
            $error = 'Failed to update competency.';
        }
    }
    
    if (isset($_POST['delete_competency'])) {
        $competencyId = $_POST['competency_id'];
        
        if ($competencyManager->deleteCompetency($competencyId)) {
            $message = 'Competency deleted successfully!';
            $auth->logActivity('delete_competency', 'competencies', $competencyId, null, null);
        } else {
            $error = 'Failed to delete competency.';
        }
    }
}

$models = $competencyManager->getAllModels();
$competencyTemplates = CompetencyTemplates::getTemplatesByCategory();
$quickStartModels = CompetencyTemplates::getQuickStartModels();
?>

<link rel="stylesheet" href="assets/css/competency.css">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Competency Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-success mr-2" data-toggle="modal" data-target="#quickStartModal">
            <i class="fe fe-zap fe-16 mr-2"></i>Quick Start
        </button>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createModelModal">
            <i class="fe fe-plus fe-16 mr-2"></i>Create Model
        </button>
    </div>
</div>

<!-- Quick Start Templates -->
<?php if (empty($models)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fe fe-zap mr-2"></i>Get Started Quickly with Pre-built Templates
                </h5>
            </div>
            <div class="card-body">
                <p class="card-text">Save time by using our pre-built competency models designed for common roles and industries.</p>
                <div class="row">
                    <?php foreach ($quickStartModels as $template): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border-left-success">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo htmlspecialchars($template['name']); ?></h6>
                                <p class="card-text text-muted small"><?php echo htmlspecialchars($template['description']); ?></p>
                                <p class="small mb-2">
                                    <strong>Includes:</strong> <?php echo count($template['competencies']); ?> competencies
                                </p>
                                <button type="button" class="btn btn-sm btn-success" onclick="useTemplate('<?php echo htmlspecialchars(json_encode($template)); ?>')">
                                    <i class="fe fe-download mr-1"></i>Use Template
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

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

<!-- Competency Models -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Competency Models</h5>
            </div>
            <div class="card-body">
                <?php if (empty($models)): ?>
                    <div class="text-center py-4">
                        <i class="fe fe-target fe-48 text-muted"></i>
                        <h4 class="text-muted mt-3">No Competency Models</h4>
                        <p class="text-muted">Create your first competency model to get started.</p>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createModelModal">
                            Create Model
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Model Name</th>
                                    <th>Category</th>
                                    <th>Assessment Method</th>
                                    <th>Competencies</th>
                                    <th>Evaluations</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($models as $model): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($model['name']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($model['description']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary"><?php echo htmlspecialchars($model['category'] ?? 'General'); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?php echo ucfirst($model['assessment_method'] ?? 'self_assessment'); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary"><?php echo $model['competency_count']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge badge-success"><?php echo $model['evaluation_count']; ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars(($model['first_name'] ?? 'Unknown') . ' ' . ($model['last_name'] ?? 'User')); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewModel(<?php echo $model['id']; ?>)">
                                                    <i class="fe fe-eye fe-14"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addCompetency(<?php echo $model['id']; ?>)">
                                                    <i class="fe fe-plus fe-14"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning" onclick="editModel(<?php echo htmlspecialchars(json_encode($model)); ?>)">
                                                    <i class="fe fe-edit fe-14"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteModel(<?php echo $model['id']; ?>)">
                                                    <i class="fe fe-trash fe-14"></i>
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

<!-- Create Model Modal -->
<div class="modal fade" id="createModelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Competency Model</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Model Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category">Category *</label>
                                <select class="form-control" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="Leadership">Leadership</option>
                                    <option value="Technical">Technical</option>
                                    <option value="Sales">Sales</option>
                                    <option value="Communication">Communication</option>
                                    <option value="Management">Management</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="target_roles">Target Roles (comma-separated)</label>
                                <input type="text" class="form-control" id="target_roles" name="target_roles" placeholder="e.g., managers, directors, executives">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="assessment_method">Assessment Method *</label>
                                <select class="form-control" id="assessment_method" name="assessment_method" required>
                                    <option value="self">Self Assessment</option>
                                    <option value="manager">Manager Assessment</option>
                                    <option value="peer">Peer Assessment</option>
                                    <option value="360">360 Degree</option>
                                    <option value="combined">Combined</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_model" class="btn btn-primary">Create Model</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Competency Modal -->
<div class="modal fade" id="addCompetencyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Competency</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="competency_model_id" name="model_id">
                    
                    <!-- Quick Templates Section -->
                    <div class="alert alert-info">
                        <h6><i class="fe fe-lightbulb mr-2"></i>Quick Templates</h6>
                        <p class="mb-2 small">Choose from pre-built competencies or create your own:</p>
                        <div class="btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-outline-info btn-sm active">
                                <input type="radio" name="template_category" value="custom" checked> Custom
                            </label>
                            <?php foreach (array_keys($competencyTemplates) as $category): ?>
                            <label class="btn btn-outline-info btn-sm">
                                <input type="radio" name="template_category" value="<?php echo $category; ?>"> <?php echo $category; ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Template Selection -->
                    <div id="template-selection" style="display: none;">
                        <div class="form-group">
                            <label for="competency_template">Select Template</label>
                            <select class="form-control" id="competency_template">
                                <option value="">Choose a template...</option>
                            </select>
                            <small class="form-text text-muted">Select a template to auto-fill the form</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="competency_name">Competency Name *</label>
                        <input type="text" class="form-control" id="competency_name" name="competency_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="competency_description">Description</label>
                        <textarea class="form-control" id="competency_description" name="competency_description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="weight">Weight</label>
                                <input type="number" class="form-control" id="weight" name="weight" step="0.1" min="0.1" max="5" value="1.0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="max_score">Max Score</label>
                                <input type="number" class="form-control" id="max_score" name="max_score" min="1" max="10" value="5">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_competency" class="btn btn-primary">Add Competency</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Model Modal -->
<div class="modal fade" id="editModelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Competency Model</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="edit_model_id" name="model_id">
                    <div class="form-group">
                        <label for="edit_name">Model Name *</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description *</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_category">Category *</label>
                                <select class="form-control" id="edit_category" name="category" required>
                                    <option value="Technical">Technical</option>
                                    <option value="Leadership">Leadership</option>
                                    <option value="Communication">Communication</option>
                                    <option value="Management">Management</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_status">Status *</label>
                                <select class="form-control" id="edit_status" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="draft">Draft</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_target_roles">Target Roles (comma-separated)</label>
                        <input type="text" class="form-control" id="edit_target_roles" name="target_roles" placeholder="e.g., Manager, Developer, Analyst">
                    </div>
                    <div class="form-group">
                        <label for="edit_assessment_method">Assessment Method *</label>
                        <select class="form-control" id="edit_assessment_method" name="assessment_method" required>
                            <option value="self_assessment">Self Assessment</option>
                            <option value="manager_review">Manager Review</option>
                            <option value="peer_review">Peer Review</option>
                            <option value="360_feedback">360 Feedback</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_model" class="btn btn-primary">Update Model</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Model Modal -->
<div class="modal fade" id="deleteModelModal" tabindex="-1" role="dialog">
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
                    <input type="hidden" id="delete_model_id" name="model_id">
                    <p>Are you sure you want to delete this competency model? This action cannot be undone.</p>
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This will also delete all associated competencies and evaluation data.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_model" class="btn btn-danger">Delete Model</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Model Modal -->
<div class="modal fade" id="viewModelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fe fe-eye me-2"></i>Competency Model Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <!-- Model Header -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h4 class="text-primary mb-2" id="viewModelName">-</h4>
                        <p class="text-muted mb-0" id="viewModelDescription">-</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge badge-success" id="viewModelStatus">-</span>
                    </div>
                </div>

                <!-- Model Information Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-left-primary">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted mb-1">Category</h6>
                                        <h5 class="mb-0" id="viewModelCategory">-</h5>
                                    </div>
                                    <div class="text-primary">
                                        <i class="fe fe-tag fe-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-left-info">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted mb-1">Assessment Method</h6>
                                        <h5 class="mb-0" id="viewModelAssessmentMethod">-</h5>
                                    </div>
                                    <div class="text-info">
                                        <i class="fe fe-clipboard fe-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-left-warning">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted mb-1">Created By</h6>
                                        <h5 class="mb-0" id="viewModelCreatedBy">-</h5>
                                    </div>
                                    <div class="text-warning">
                                        <i class="fe fe-user fe-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-left-success">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted mb-1">Created Date</h6>
                                        <h5 class="mb-0" id="viewModelCreatedAt">-</h5>
                                    </div>
                                    <div class="text-success">
                                        <i class="fe fe-calendar fe-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Target Roles -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fe fe-users me-2"></i>Target Roles
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="viewModelTargetRoles">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Competencies Section -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fe fe-award me-2"></i>Competencies
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="border-0">Competency Name</th>
                                                <th class="border-0">Description</th>
                                                <th class="border-0 text-center">Weight</th>
                                                <th class="border-0 text-center">Max Score</th>
                                            </tr>
                                        </thead>
                                        <tbody id="viewModelCompetencies">
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">
                                                    <i class="fe fe-info fe-24 mb-2"></i><br>
                                                    No competencies defined for this model.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fe fe-x me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 4px solid #007bff !important;
}
.border-left-info {
    border-left: 4px solid #17a2b8 !important;
}
.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}
.border-left-success {
    border-left: 4px solid #28a745 !important;
}
.me-2 {
    margin-right: 0.5rem !important;
}
.mb-2 {
    margin-bottom: 0.5rem !important;
}
.py-4 {
    padding-top: 1.5rem !important;
    padding-bottom: 1.5rem !important;
}
</style>

<script>
function addCompetency(modelId) {
    document.getElementById('competency_model_id').value = modelId;
    $('#addCompetencyModal').modal('show');
}

function viewModel(modelId) {
    console.log('viewModel called with ID:', modelId);
    
    // Store original modal content
    const modal = document.getElementById('viewModelModal');
    const originalContent = modal ? modal.querySelector('.modal-body').innerHTML : null;
    
    // Show loading state
    if (modal) {
        modal.querySelector('.modal-body').innerHTML = '<div class="text-center"><i class="fe fe-loader fe-spin fe-24"></i><br>Loading model details...</div>';
        $('#viewModelModal').modal('show');
    }
    
    // Use the dedicated AJAX endpoint
    const url = `ajax/get_model_details.php?id=${modelId}`;
    
    console.log('Fetching URL:', url);
    
    // Fetch model details and competencies via AJAX
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            // Check if response is actually JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    throw new Error('Response is not JSON. Content: ' + text.substring(0, 200));
                });
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data);
            
            if (data.success) {
                const model = data.model;
                const competencies = data.competencies;
                
                // Restore original modal content first
                if (modal && originalContent) {
                    modal.querySelector('.modal-body').innerHTML = originalContent;
                }
                
                // Wait for modal content to be restored
                setTimeout(() => {
                    // Safely populate the view modal with null checks
                    const elements = {
                        name: document.getElementById('viewModelName'),
                        description: document.getElementById('viewModelDescription'),
                        category: document.getElementById('viewModelCategory'),
                        assessmentMethod: document.getElementById('viewModelAssessmentMethod'),
                        targetRoles: document.getElementById('viewModelTargetRoles'),
                        status: document.getElementById('viewModelStatus'),
                        createdBy: document.getElementById('viewModelCreatedBy'),
                        createdAt: document.getElementById('viewModelCreatedAt'),
                        competenciesTable: document.getElementById('viewModelCompetencies')
                    };
                    
                    // Check if all required elements exist
                    const missingElements = Object.entries(elements).filter(([key, element]) => !element);
                    if (missingElements.length > 0) {
                        console.error('Missing elements:', missingElements.map(([key]) => key));
                        alert('Error: Some modal elements are missing. Please refresh the page and try again.');
                        return;
                    }
                    
                    // Populate the modal
                    elements.name.textContent = model.name || 'N/A';
                    elements.description.textContent = model.description || 'No description provided.';
                    elements.category.textContent = model.category || 'N/A';
                    elements.assessmentMethod.textContent = model.assessment_method_form || model.assessment_method || 'N/A';
                    elements.status.textContent = model.status || 'active';
                    elements.createdBy.textContent = (model.first_name || 'Unknown') + ' ' + (model.last_name || 'User');
                    elements.createdAt.textContent = model.created_at ? new Date(model.created_at).toLocaleDateString() : 'N/A';
                    
                    // Populate target roles
                    if (Array.isArray(model.target_roles) && model.target_roles.length > 0) {
                        elements.targetRoles.innerHTML = model.target_roles.map(role => 
                            `<span class="badge badge-secondary me-2 mb-2">${role}</span>`
                        ).join('');
                    } else {
                        elements.targetRoles.innerHTML = '<span class="text-muted">No target roles specified</span>';
                    }
                    
                    // Populate competencies table
                    if (competencies && competencies.length > 0) {
                        elements.competenciesTable.innerHTML = competencies.map(comp => `
                            <tr>
                                <td><strong>${comp.name || 'Unnamed'}</strong></td>
                                <td>${comp.description || 'No description'}</td>
                                <td class="text-center"><span class="badge badge-info">${(comp.weight * 100).toFixed(1)}%</span></td>
                                <td class="text-center"><span class="badge badge-primary">${comp.max_score || 'N/A'}</span></td>
                            </tr>
                        `).join('');
                    } else {
                        elements.competenciesTable.innerHTML = `
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fe fe-info fe-24 mb-2"></i><br>
                                    No competencies defined for this model.
                                </td>
                            </tr>
                        `;
                    }
                    
                    console.log('Modal populated successfully');
                }, 200); // Increased delay to ensure content is restored
                
            } else {
                console.error('API returned error:', data.message);
                alert('Failed to load model details: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Failed to load model details. Please try again. Error: ' + error.message);
        });
}

function editModel(model) {
    document.getElementById('edit_model_id').value = model.id;
    document.getElementById('edit_name').value = model.name;
    document.getElementById('edit_description').value = model.description;
    document.getElementById('edit_category').value = model.category;
    document.getElementById('edit_status').value = model.status || 'active';
    document.getElementById('edit_target_roles').value = Array.isArray(model.target_roles) ? model.target_roles.join(', ') : (model.target_roles || '');
    document.getElementById('edit_assessment_method').value = model.assessment_method_form || model.assessment_method;
    
    $('#editModelModal').modal('show');
}

function deleteModel(modelId) {
    document.getElementById('delete_model_id').value = modelId;
    $('#deleteModelModal').modal('show');
}

// Template functionality
const competencyTemplates = <?php echo json_encode($competencyTemplates); ?>;

// Handle template category selection
document.addEventListener('DOMContentLoaded', function() {
    const categoryRadios = document.querySelectorAll('input[name="template_category"]');
    const templateSelection = document.getElementById('template-selection');
    const templateSelect = document.getElementById('competency_template');
    
    categoryRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'custom') {
                templateSelection.style.display = 'none';
                clearCompetencyForm();
            } else {
                templateSelection.style.display = 'block';
                populateTemplateOptions(this.value);
            }
        });
    });
    
    // Handle template selection
    templateSelect.addEventListener('change', function() {
        if (this.value) {
            const template = JSON.parse(this.value);
            fillCompetencyForm(template);
        }
    });
});

function populateTemplateOptions(category) {
    const templateSelect = document.getElementById('competency_template');
    templateSelect.innerHTML = '<option value="">Choose a template...</option>';
    
    if (competencyTemplates[category]) {
        competencyTemplates[category].forEach(template => {
            const option = document.createElement('option');
            option.value = JSON.stringify(template);
            option.textContent = template.name;
            templateSelect.appendChild(option);
        });
    }
}

function fillCompetencyForm(template) {
    document.getElementById('competency_name').value = template.name;
    document.getElementById('competency_description').value = template.description;
    document.getElementById('weight').value = template.weight;
    document.getElementById('max_score').value = template.max_score;
}

function clearCompetencyForm() {
    document.getElementById('competency_name').value = '';
    document.getElementById('competency_description').value = '';
    document.getElementById('weight').value = '1.0';
    document.getElementById('max_score').value = '5';
}

function useTemplate(templateJson) {
    const template = JSON.parse(templateJson);
    
    // Fill the create model form
    document.getElementById('name').value = template.name;
    document.getElementById('description').value = template.description;
    document.getElementById('category').value = template.category;
    document.getElementById('target_roles').value = template.target_roles.join(', ');
    document.getElementById('assessment_method').value = template.assessment_method;
    
    // Show the create modal
    $('#createModelModal').modal('show');
    
    // Store competencies for later use
    window.templateCompetencies = template.competencies;
    
    // Show success message
    setTimeout(() => {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show';
        alertDiv.innerHTML = `
            <strong>Template Applied!</strong> Form has been pre-filled. After creating the model, you can quickly add the ${template.competencies.length} included competencies.
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        `;
        document.querySelector('.modal-body').insertBefore(alertDiv, document.querySelector('.modal-body').firstChild);
    }, 500);
}
</script>
