<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/competency.php';
require_once 'includes/functions/form_protection.php';
require_once 'includes/data/competency_templates.php';

// Start session for form protection
session_start();

// Create form submissions table if it doesn't exist
createFormSubmissionsTable();

// Regular authentication check for non-AJAX requests
$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || !$auth->hasPermission('manage_evaluations')) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$competencyManager = new CompetencyManager();
$db = getDB();

// Get flash messages
$flash = FormProtection::getFlashMessage();
$message = $flash['message'];
$error = '';

// Handle form submissions with protection
if ($_POST) {
    $isDuplicate = false;
    
    // Method 1: Token-based protection
    if (isset($_POST['form_token'])) {
        $formName = $_POST['form_name'] ?? 'default';
        if (!FormProtection::validateAndConsumeToken($formName, $_POST['form_token'])) {
            $error = 'Invalid or expired form token. Please try again.';
            $isDuplicate = true;
        }
    }
    
    // Method 2: Session-based duplicate prevention
    if (!$isDuplicate && isset($_POST['create_model'])) {
        $modelData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'category' => $_POST['category'],
            'target_roles' => explode(',', $_POST['target_roles']),
            'assessment_method' => $_POST['assessment_method'],
            'created_by' => $current_user['id']
        ];
        
        if (FormProtection::preventDuplicateSession('create_model', $modelData)) {
            $error = 'Duplicate submission detected. Please wait before submitting again.';
            $isDuplicate = true;
        } else {
            if ($competencyManager->createModel($modelData)) {
                $message = 'Competency model created successfully!';
                $auth->logActivity('create_competency_model', 'competency_models', null, null, $modelData);
                
                // Method 3: POST-Redirect-GET pattern
                FormProtection::redirectAfterPost('?page=competency_models&action=list', $message);
            } else {
                $error = 'Failed to create competency model.';
            }
        }
    }
    
    if (!$isDuplicate && isset($_POST['add_competency'])) {
        $competencyData = [
            'model_id' => $_POST['model_id'],
            'name' => $_POST['competency_name'],
            'description' => $_POST['competency_description'],
            'weight' => $_POST['weight'],
            'max_score' => $_POST['max_score']
        ];
        
        // Method 4: Database-based duplicate checking
        if (FormProtection::checkDuplicateSubmission('add_competency', $competencyData, $current_user['id'])) {
            $error = 'This competency was recently added. Please wait before adding again.';
            $isDuplicate = true;
        } else {
            if ($competencyManager->addCompetency($competencyData)) {
                $message = 'Competency added successfully!';
                $auth->logActivity('add_competency', 'competencies', null, null, $competencyData);
                FormProtection::redirectAfterPost('?page=competency_models&action=view&id=' . $competencyData['model_id'], $message);
            } else {
                $error = 'Failed to add competency.';
            }
        }
    }
    
    if (!$isDuplicate && isset($_POST['update_model'])) {
        $modelId = $_POST['model_id'];
        $updateData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'category' => $_POST['category'],
            'target_roles' => explode(',', $_POST['target_roles']),
            'assessment_method' => $_POST['assessment_method'],
            'status' => $_POST['status']
        ];
        
        if (FormProtection::preventDuplicateSession('update_model_' . $modelId, $updateData)) {
            $error = 'Duplicate submission detected. Please wait before updating again.';
        } else {
            if ($competencyManager->updateModel($modelId, $updateData)) {
                $message = 'Competency model updated successfully!';
                $auth->logActivity('update_competency_model', 'competency_models', $modelId, null, $updateData);
                FormProtection::redirectAfterPost('?page=competency_models&action=view&id=' . $modelId, $message);
            } else {
                $error = 'Failed to update competency model.';
            }
        }
    }
    
    if (!$isDuplicate && isset($_POST['delete_model'])) {
        $modelId = $_POST['model_id'];
        
        if ($competencyManager->deleteModel($modelId)) {
            $message = 'Competency model deleted successfully!';
            $auth->logActivity('delete_competency_model', 'competency_models', $modelId, null, null);
            FormProtection::redirectAfterPost('?page=competency_models&action=list', $message);
        } else {
            $error = 'Failed to delete competency model.';
        }
    }
    
    if (!$isDuplicate && isset($_POST['update_competency'])) {
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
            FormProtection::redirectAfterPost('?page=competency_models&action=view&id=' . $_POST['model_id'], $message);
        } else {
            $error = 'Failed to update competency.';
        }
    }
    
    if (!$isDuplicate && isset($_POST['delete_competency'])) {
        $competencyId = $_POST['competency_id'];
        
        if ($competencyManager->deleteCompetency($competencyId)) {
            $message = 'Competency deleted successfully!';
            $auth->logActivity('delete_competency', 'competencies', $competencyId, null, null);
            FormProtection::redirectAfterPost('?page=competency_models&action=view&id=' . $_POST['model_id'], $message);
        } else {
            $error = 'Failed to delete competency.';
        }
    }
}

$models = $competencyManager->getAllModels();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competency Models - HR System</title>
    <link rel="stylesheet" href="assets/vendor/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/vendor/css/feather.min.css">
    <link rel="stylesheet" href="assets/css/hr-main.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Competency Models</h3>
                        <div class="card-options">
                            <button class="btn btn-primary" data-toggle="modal" data-target="#createModelModal">
                                <i class="fe fe-plus"></i> Create Model
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body">
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
                        
                        <!-- Models List -->
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Competencies</th>
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
                                            <td><?php echo htmlspecialchars($model['category']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $model['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst($model['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $model['competency_count']; ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="?page=competency_models&action=view&id=<?php echo $model['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">View</a>
                                                    <button class="btn btn-sm btn-outline-secondary" 
                                                            onclick="editModel(<?php echo htmlspecialchars(json_encode($model)); ?>)">Edit</button>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteModel(<?php echo $model['id']; ?>)">Delete</button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Model Modal -->
    <div class="modal fade" id="createModelModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="createModelForm">
                    <input type="hidden" name="form_name" value="create_model">
                    <input type="hidden" name="form_token" value="<?php echo FormProtection::generateToken(); ?>">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Create Competency Model</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Model Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category">Category *</label>
                                    <select class="form-control" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="technical">Technical</option>
                                        <option value="behavioral">Behavioral</option>
                                        <option value="leadership">Leadership</option>
                                        <option value="communication">Communication</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="assessment_method">Assessment Method *</label>
                                    <select class="form-control" id="assessment_method" name="assessment_method" required>
                                        <option value="">Select Method</option>
                                        <option value="self">Self Assessment</option>
                                        <option value="manager">Manager Review</option>
                                        <option value="peer">Peer Review</option>
                                        <option value="360">360 Feedback</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="target_roles">Target Roles (comma-separated)</label>
                            <input type="text" class="form-control" id="target_roles" name="target_roles" 
                                   placeholder="e.g., Manager, Team Lead, Developer">
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_model" class="btn btn-primary" 
                                data-original-value="Create Model">Create Model</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Protection -->
    <?php echo FormProtection::getJavaScriptProtection(); ?>
    
    <script src="assets/vendor/js/jquery.min.js"></script>
    <script src="assets/vendor/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Store original button values for restoration
    document.addEventListener('DOMContentLoaded', function() {
        const submitButtons = document.querySelectorAll('button[type="submit"], input[type="submit"]');
        submitButtons.forEach(btn => {
            btn.setAttribute('data-original-value', btn.value || btn.textContent);
        });
    });
    
    function editModel(model) {
        // Implementation for editing model
        console.log('Edit model:', model);
    }
    
    function deleteModel(modelId) {
        if (confirm('Are you sure you want to delete this model?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="form_name" value="delete_model">
                <input type="hidden" name="form_token" value="<?php echo FormProtection::generateToken(); ?>">
                <input type="hidden" name="model_id" value="${modelId}">
                <input type="hidden" name="delete_model" value="1">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</body>
</html>


