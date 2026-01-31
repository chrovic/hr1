<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/succession_planning.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || !$auth->hasPermission('manage_succession')) {
    header('Location: auth/login.php');
    exit;
}

$roleId = isset($_GET['role_id']) ? (int)$_GET['role_id'] : 0;
$successionManager = new SuccessionPlanning();

$role = $roleId ? $successionManager->getCriticalRole($roleId) : null;
$pipeline = $roleId ? $successionManager->getSuccessionPipeline($roleId) : [];
$availableEmployees = $roleId ? $successionManager->getAvailableEmployees($roleId) : [];
?>

<div class="d-flex justify-content-between align-items-center flex-wrap pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-1">Succession Pipeline</h1>
        <p class="text-muted mb-0">Role pipeline view with latest readiness and assessment scores.</p>
    </div>
    <div>
        <a href="?page=succession_planning" class="btn btn-outline-secondary">
            <i class="fe fe-arrow-left fe-16 mr-2"></i>Back to Succession Planning
        </a>
        <?php if ($role): ?>
            <button type="button" class="btn btn-primary ml-2" data-toggle="modal" data-target="#assignCandidateModal">
                <i class="fe fe-user-plus fe-16 mr-2"></i>Assign Candidate
            </button>
        <?php endif; ?>
    </div>
</div>

<?php if (!$role): ?>
    <div class="card">
        <div class="card-body text-center">
            <i class="fe fe-briefcase fe-48 text-muted"></i>
            <h4 class="text-muted mt-3">Role not found</h4>
            <p class="text-muted mb-0">Select a valid critical role to view its pipeline.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center">
                <div class="mr-4">
                    <div class="text-muted small">Role</div>
                    <div class="font-weight-bold"><?php echo htmlspecialchars($role['position_title']); ?></div>
                </div>
                <div class="mr-4">
                    <div class="text-muted small">Department</div>
                    <div><?php echo htmlspecialchars($role['department']); ?></div>
                </div>
                <div class="mr-4">
                    <div class="text-muted small">Risk Level</div>
                    <span class="badge badge-<?php echo $role['risk_level'] === 'high' ? 'danger' : ($role['risk_level'] === 'medium' ? 'warning' : 'success'); ?>">
                        <?php echo ucfirst($role['risk_level']); ?>
                    </span>
                </div>
                <div>
                    <div class="text-muted small">Current Incumbent</div>
                    <div><?php echo $role['first_name'] ? htmlspecialchars($role['first_name'] . ' ' . $role['last_name']) : 'Vacant'; ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Pipeline Candidates</h5>
        </div>
        <div class="card-body">
            <?php if (empty($pipeline)): ?>
                <div class="text-center py-4">
                    <i class="fe fe-users fe-32 text-muted"></i>
                    <p class="text-muted mt-2 mb-0">No candidates assigned for this role yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Candidate</th>
                                <th>Readiness</th>
                                <th>Overall Score</th>
                                <th>Last Assessment</th>
                                <th>Next Review</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pipeline as $candidate): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']); ?></strong>
                                        <div class="text-muted small"><?php echo htmlspecialchars($candidate['position'] ?? ''); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?php echo ucfirst(str_replace('_', ' ', $candidate['readiness_level'])); ?></span>
                                    </td>
                                    <td><?php echo $candidate['overall_readiness_score'] !== null ? (int)$candidate['overall_readiness_score'] : '-'; ?></td>
                                    <td><?php echo $candidate['last_assessment'] ? date('M d, Y', strtotime($candidate['last_assessment'])) : 'Not assessed'; ?></td>
                                    <td><?php echo $candidate['next_review_date'] ? date('M d, Y', strtotime($candidate['next_review_date'])) : 'Not scheduled'; ?></td>
                                    <td>
                                        <a href="?page=candidate_details&id=<?php echo (int)$candidate['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-warning" data-toggle="modal" data-target="#editCandidateModal" data-candidate='<?php echo htmlspecialchars(json_encode($candidate), ENT_QUOTES, "UTF-8"); ?>'>
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($role): ?>
<!-- Assign Candidate Modal -->
<div class="modal fade" id="assignCandidateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Succession Candidate</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST" action="?page=succession_planning">
                <div class="modal-body">
                    <input type="hidden" name="role_id" value="<?php echo (int)$roleId; ?>">
                    <input type="hidden" name="return_url" value="<?php echo '?page=succession_pipeline&role_id=' . (int)$roleId; ?>">
                    <div class="form-group">
                        <label for="employee_id">Employee *</label>
                        <select class="form-control" id="employee_id" name="employee_id" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($availableEmployees as $employee): ?>
                                <option value="<?php echo (int)$employee['id']; ?>">
                                    <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name'] . ' - ' . $employee['position']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
                        <textarea class="form-control" id="development_plan" name="development_plan" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
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
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST" action="?page=succession_planning">
                <div class="modal-body">
                    <input type="hidden" id="edit_candidate_id" name="candidate_id">
                    <input type="hidden" name="return_url" value="<?php echo '?page=succession_pipeline&role_id=' . (int)$roleId; ?>">
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
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var dateInput = document.getElementById('assessment_date');
    if (dateInput && !dateInput.value) {
        var today = new Date();
        dateInput.value = today.toISOString().split('T')[0];
    }

    $('#editCandidateModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var candidate = button.data('candidate');
        if (!candidate) {
            return;
        }
        $('#edit_candidate_id').val(candidate.id || '');
        $('#edit_readiness_level').val(candidate.readiness_level || 'development_needed');
        $('#edit_development_plan').val(candidate.development_plan || '');
        $('#edit_notes').val(candidate.notes || '');
        $('#edit_assessment_date').val(candidate.assessment_date || '');
        $('#edit_next_review_date').val(candidate.next_review_date || '');
    });
});
</script>
