<?php
require_once 'includes/data/db.php';

require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/succession_planning.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || !$auth->hasPermission('manage_evaluations')) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$successionManager = new SuccessionPlanning();

$message = '';
$error = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['create_role'])) {
        $roleData = [
            'position_title' => $_POST['position_title'],
            'department' => $_POST['department'],
            'level' => $_POST['level'],
            'description' => $_POST['description'],
            'requirements' => $_POST['requirements'],
            'risk_level' => $_POST['risk_level'],
            'current_incumbent_id' => $_POST['current_incumbent_id'] ?: null,
            'created_by' => $current_user['id']
        ];
        
        if ($successionManager->createCriticalRole($roleData)) {
            $message = 'Critical role created successfully!';
            $auth->logActivity('create_critical_role', 'critical_roles', null, null, $roleData);
        } else {
            $error = 'Failed to create critical role.';
        }
    }
    
    if (isset($_POST['assign_candidate'])) {
        $candidateData = [
            'role_id' => $_POST['role_id'],
            'employee_id' => $_POST['employee_id'],
            'readiness_level' => $_POST['readiness_level'],
            'development_plan' => $_POST['development_plan'],
            'notes' => $_POST['notes'],
            'assessment_date' => $_POST['assessment_date'],
            'next_review_date' => $_POST['next_review_date'],
            'assigned_by' => $current_user['id']
        ];
        
        if ($successionManager->assignCandidate($candidateData)) {
            $message = 'Succession candidate assigned successfully!';
            $auth->logActivity('assign_succession_candidate', 'succession_candidates', null, null, $candidateData);
        } else {
            $error = 'Failed to assign succession candidate.';
        }
    }
    
    if (isset($_POST['update_candidate'])) {
        $candidateId = $_POST['candidate_id'];
        $updateData = [
            'readiness_level' => $_POST['readiness_level'],
            'development_plan' => $_POST['development_plan'],
            'notes' => $_POST['notes'],
            'assessment_date' => $_POST['assessment_date'],
            'next_review_date' => $_POST['next_review_date']
        ];
        
        if ($successionManager->updateCandidateReadiness($candidateId, $updateData)) {
            $message = 'Candidate readiness updated successfully!';
            $auth->logActivity('update_candidate_readiness', 'succession_candidates', $candidateId, null, $updateData);
        } else {
            $error = 'Failed to update candidate readiness.';
        }
    }
    
    if (isset($_POST['remove_candidate'])) {
        $candidateId = $_POST['candidate_id'];
        
        if ($successionManager->removeCandidate($candidateId)) {
            $message = 'Candidate removed from succession planning successfully!';
            $auth->logActivity('remove_succession_candidate', 'succession_candidates', $candidateId, null, null);
        } else {
            $error = 'Failed to remove candidate.';
        }
    }
}

$criticalRoles = $successionManager->getAllCriticalRoles();
$successionCandidates = $successionManager->getAllCandidates();
$availableEmployees = $successionManager->getAvailableEmployees();
?>

<link rel="stylesheet" href="assets/css/competency.css">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Succession Planning</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createRoleModal">
            <i class="fe fe-plus fe-16 mr-2"></i>Create Critical Role
        </button>
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#assignCandidateModal">
            <i class="fe fe-users fe-16 mr-2"></i>Assign Candidate
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

<!-- Critical Roles Overview -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Critical Roles Overview</h5>
            </div>
            <div class="card-body">
                <?php if (empty($criticalRoles)): ?>
                    <div class="text-center py-4">
                        <i class="fe fe-target fe-48 text-muted"></i>
                        <h4 class="text-muted mt-3">No Critical Roles</h4>
                        <p class="text-muted">Create your first critical role to start succession planning.</p>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createRoleModal">
                            Create Critical Role
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Position</th>
                                    <th>Department</th>
                                    <th>Level</th>
                                    <th>Risk Level</th>
                                    <th>Current Incumbent</th>
                                    <th>Candidates</th>
                                    <th>Ready Now</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($criticalRoles as $role): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($role['position_title']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary"><?php echo htmlspecialchars($role['department']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?php echo ucfirst($role['level']); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $riskClass = '';
                                            switch($role['risk_level']) {
                                                case 'low': $riskClass = 'badge-success'; break;
                                                case 'medium': $riskClass = 'badge-warning'; break;
                                                case 'high': $riskClass = 'badge-danger'; break;
                                                case 'critical': $riskClass = 'badge-dark'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $riskClass; ?>"><?php echo ucfirst($role['risk_level']); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($role['first_name']): ?>
                                                <?php echo htmlspecialchars($role['first_name'] . ' ' . $role['last_name']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Vacant</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary"><?php echo $role['candidate_count']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge badge-success"><?php echo $role['ready_now_count']; ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewRole(<?php echo $role['id']; ?>)">
                                                    <i class="fe fe-eye fe-14"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success" onclick="viewCandidates(<?php echo $role['id']; ?>)">
                                                    <i class="fe fe-users fe-14"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning" onclick="editRole(<?php echo htmlspecialchars(json_encode($role)); ?>)">
                                                    <i class="fe fe-edit fe-14"></i>
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

<!-- Succession Candidates -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Succession Candidates</h5>
            </div>
            <div class="card-body">
                <?php if (empty($successionCandidates)): ?>
                    <div class="text-center py-4">
                        <i class="fe fe-users fe-48 text-muted"></i>
                        <h4 class="text-muted mt-3">No Succession Candidates</h4>
                        <p class="text-muted">Assign candidates to critical roles to start building your succession pipeline.</p>
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#assignCandidateModal">
                            Assign Candidate
                        </button>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                    <th>Candidate</th>
                                <th>Position</th>
                                    <th>Target Role</th>
                                    <th>Readiness Level</th>
                                    <th>Assessment Date</th>
                                    <th>Next Review</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                                <?php foreach ($successionCandidates as $candidate): ?>
                            <tr>
                                <td>
                                            <strong><?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($candidate['position']); ?></small>
                                </td>
                                <td>
                                            <span class="badge badge-secondary"><?php echo htmlspecialchars($candidate['department']); ?></span>
                                </td>
                                <td>
                                            <strong><?php echo htmlspecialchars($candidate['position_title']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($candidate['role_department']); ?></small>
                                </td>
                                <td>
                                            <?php
                                            $readinessClass = '';
                                            switch($candidate['readiness_level']) {
                                                case 'ready_now': $readinessClass = 'badge-success'; break;
                                                case 'ready_soon': $readinessClass = 'badge-warning'; break;
                                                case 'development_needed': $readinessClass = 'badge-info'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $readinessClass; ?>"><?php echo ucfirst(str_replace('_', ' ', $candidate['readiness_level'])); ?></span>
                                </td>
                                <td>
                                            <?php echo $candidate['assessment_date'] ? date('M d, Y', strtotime($candidate['assessment_date'])) : 'Not assessed'; ?>
                                </td>
                                <td>
                                            <?php echo $candidate['next_review_date'] ? date('M d, Y', strtotime($candidate['next_review_date'])) : 'Not scheduled'; ?>
                                </td>
                                <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewCandidate(<?php echo $candidate['id']; ?>)">
                                                    <i class="fe fe-eye fe-14"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning" onclick="editCandidate(<?php echo htmlspecialchars(json_encode($candidate)); ?>)">
                                                    <i class="fe fe-edit fe-14"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCandidate(<?php echo $candidate['id']; ?>)">
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

<!-- Create Critical Role Modal -->
<div class="modal fade" id="createRoleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Critical Role</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="position_title">Position Title *</label>
                                <input type="text" class="form-control" id="position_title" name="position_title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="department">Department *</label>
                                <input type="text" class="form-control" id="department" name="department" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="level">Level *</label>
                                <select class="form-control" id="level" name="level" required>
                                    <option value="">Select Level</option>
                                    <option value="entry">Entry Level</option>
                                    <option value="mid">Mid Level</option>
                                    <option value="senior">Senior Level</option>
                                    <option value="executive">Executive Level</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="risk_level">Risk Level *</label>
                                <select class="form-control" id="risk_level" name="risk_level" required>
                                    <option value="">Select Risk Level</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Role Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="requirements">Requirements</label>
                        <textarea class="form-control" id="requirements" name="requirements" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="current_incumbent_id">Current Incumbent (Optional)</label>
                        <select class="form-control" id="current_incumbent_id" name="current_incumbent_id">
                            <option value="">Select Current Incumbent</option>
                            <?php foreach ($availableEmployees as $employee): ?>
                                <option value="<?php echo $employee['id']; ?>">
                                    <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name'] . ' - ' . $employee['position']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_role" class="btn btn-primary">Create Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Candidate Modal -->
<div class="modal fade" id="assignCandidateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Succession Candidate</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
            <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role_id">Target Role *</label>
                                <select class="form-control" id="role_id" name="role_id" required>
                                    <option value="">Select Critical Role</option>
                                    <?php foreach ($criticalRoles as $role): ?>
                                        <option value="<?php echo $role['id']; ?>">
                                            <?php echo htmlspecialchars($role['position_title'] . ' - ' . $role['department']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="employee_id">Employee *</label>
                                <select class="form-control" id="employee_id" name="employee_id" required>
                                    <option value="">Select Employee</option>
                                    <?php foreach ($availableEmployees as $employee): ?>
                                        <option value="<?php echo $employee['id']; ?>">
                                            <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name'] . ' - ' . $employee['position']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="readiness_level">Readiness Level *</label>
                                <select class="form-control" id="readiness_level" name="readiness_level" required>
                                    <option value="">Select Readiness Level</option>
                                    <option value="ready_now">Ready Now</option>
                                    <option value="ready_soon">Ready Soon</option>
                                    <option value="development_needed">Development Needed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="assessment_date">Assessment Date</label>
                                <input type="date" class="form-control" id="assessment_date" name="assessment_date">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="development_plan">Development Plan</label>
                        <textarea class="form-control" id="development_plan" name="development_plan" rows="3" placeholder="Describe the development plan for this candidate..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Additional notes about this candidate..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="next_review_date">Next Review Date</label>
                        <input type="date" class="form-control" id="next_review_date" name="next_review_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="assign_candidate" class="btn btn-primary">Assign Candidate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Candidate Modal -->
<div class="modal fade" id="editCandidateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Candidate Readiness</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="edit_candidate_id" name="candidate_id">
                    
                    <div class="form-group">
                        <label for="edit_readiness_level">Readiness Level *</label>
                        <select class="form-control" id="edit_readiness_level" name="readiness_level" required>
                            <option value="ready_now">Ready Now</option>
                            <option value="ready_soon">Ready Soon</option>
                            <option value="development_needed">Development Needed</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_development_plan">Development Plan</label>
                        <textarea class="form-control" id="edit_development_plan" name="development_plan" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_notes">Notes</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_assessment_date">Assessment Date</label>
                                <input type="date" class="form-control" id="edit_assessment_date" name="assessment_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_next_review_date">Next Review Date</label>
                                <input type="date" class="form-control" id="edit_next_review_date" name="next_review_date">
                            </div>
                        </div>
                            </div>
                        </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_candidate" class="btn btn-primary">Update Candidate</button>
                </div>
            </form>
                            </div>
                        </div>
                    </div>

<!-- Remove Candidate Modal -->
<div class="modal fade" id="removeCandidateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Removal</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="remove_candidate_id" name="candidate_id">
                    <p>Are you sure you want to remove this candidate from succession planning?</p>
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This action cannot be undone and will remove all associated assessment data.
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="remove_candidate" class="btn btn-danger">Remove Candidate</button>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewRole(roleId) {
    window.location.href = '?page=critical_role_details&id=' + roleId;
}

function viewCandidates(roleId) {
    window.location.href = '?page=succession_candidates&role_id=' + roleId;
}

function viewCandidate(candidateId) {
    window.location.href = '?page=candidate_details&id=' + candidateId;
}

function editRole(role) {
    // Populate edit role modal with role data
    document.getElementById('edit_role_id').value = role.id;
    document.getElementById('edit_position_title').value = role.position_title;
    document.getElementById('edit_department').value = role.department;
    document.getElementById('edit_level').value = role.level;
    document.getElementById('edit_risk_level').value = role.risk_level;
    document.getElementById('edit_description').value = role.description;
    document.getElementById('edit_requirements').value = role.requirements;
    document.getElementById('edit_current_incumbent_id').value = role.current_incumbent_id;
    
    $('#editRoleModal').modal('show');
}

function editCandidate(candidate) {
    document.getElementById('edit_candidate_id').value = candidate.id;
    document.getElementById('edit_readiness_level').value = candidate.readiness_level;
    document.getElementById('edit_development_plan').value = candidate.development_plan;
    document.getElementById('edit_notes').value = candidate.notes;
    document.getElementById('edit_assessment_date').value = candidate.assessment_date;
    document.getElementById('edit_next_review_date').value = candidate.next_review_date;
    
    $('#editCandidateModal').modal('show');
}

function removeCandidate(candidateId) {
    document.getElementById('remove_candidate_id').value = candidateId;
    $('#removeCandidateModal').modal('show');
}

// Set default dates
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    const nextMonth = new Date(today.getFullYear(), today.getMonth() + 1, today.getDate());
    
    document.getElementById('assessment_date').value = today.toISOString().split('T')[0];
    document.getElementById('next_review_date').value = nextMonth.toISOString().split('T')[0];
});
</script>