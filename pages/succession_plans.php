<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/succession_planning.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || !$auth->hasPermission('manage_succession')) {
    header('Location: auth/login.php');
    exit;
}

$successionManager = new SuccessionPlanning();

$message = '';
$error = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'plan_created':
            $message = 'Succession plan created successfully!';
            break;
        case 'plan_updated':
            $message = 'Succession plan updated successfully!';
            break;
        case 'candidate_added':
            $message = 'Candidate added to plan successfully!';
            break;
        case 'candidate_removed':
            $message = 'Candidate removed from plan successfully!';
            break;
    }
}
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'plan_create_failed':
            $error = 'Failed to create succession plan.';
            break;
        case 'plan_update_failed':
            $error = 'Failed to update succession plan.';
            break;
        case 'candidate_add_failed':
            $error = 'Failed to add candidate to plan.';
            break;
        case 'candidate_remove_failed':
            $error = 'Failed to remove candidate from plan.';
            break;
    }
}

$plans = $successionManager->getSuccessionPlans();
$criticalRoles = $successionManager->getAllCriticalRoles();
$successionCandidates = $successionManager->getAllCandidates();

$planCandidatesMap = [];
foreach ($plans as $plan) {
    $planCandidatesMap[$plan['id']] = $successionManager->getPlanCandidates($plan['id']);
}
?>

<div class="d-flex justify-content-between align-items-center flex-wrap pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-1">Succession Plans</h1>
        <p class="text-muted mb-0">Create and manage succession plans by role.</p>
    </div>
    <div>
        <a href="?page=succession_planning" class="btn btn-outline-secondary mr-2">
            <i class="fe fe-arrow-left fe-16 mr-2"></i>Back to Succession Planning
        </a>
        <a href="?page=succession_reports" class="btn btn-outline-primary mr-2">
            <i class="fe fe-bar-chart-2 fe-16 mr-2"></i>Reports
        </a>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createPlanModal">
            <i class="fe fe-plus fe-16 mr-2"></i>Create Plan
        </button>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Active Plans</h5>
    </div>
    <div class="card-body">
        <?php if (empty($plans)): ?>
            <div class="text-center py-4">
                <i class="fe fe-clipboard fe-48 text-muted"></i>
                <h4 class="text-muted mt-3">No Succession Plans</h4>
                <p class="text-muted">Create a plan to start building role-specific succession strategies.</p>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createPlanModal">
                    Create Plan
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Timeline</th>
                            <th>Candidates</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plans as $plan): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($plan['plan_name']); ?></strong>
                                    <div class="text-muted small">Created by <?php echo htmlspecialchars($plan['created_by_first_name'] . ' ' . $plan['created_by_last_name']); ?></div>
                                </td>
                                <td>
                                    <div class="font-weight-bold"><?php echo htmlspecialchars($plan['position_title']); ?></div>
                                    <div class="text-muted small"><?php echo htmlspecialchars($plan['department']); ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $plan['status'] === 'active' ? 'success' : ($plan['status'] === 'completed' ? 'primary' : ($plan['status'] === 'cancelled' ? 'danger' : 'secondary')); ?>">
                                        <?php echo ucfirst($plan['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($plan['start_date'] || $plan['end_date']): ?>
                                        <?php echo $plan['start_date'] ? date('M d, Y', strtotime($plan['start_date'])) : 'N/A'; ?>
                                        -
                                        <?php echo $plan['end_date'] ? date('M d, Y', strtotime($plan['end_date'])) : 'N/A'; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not set</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?php echo (int)($plan['candidate_count'] ?? 0); ?></span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#viewPlanModal-<?php echo (int)$plan['id']; ?>">
                                            <i class="fe fe-eye fe-14"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success" data-toggle="modal" data-target="#addPlanCandidateModal" data-plan-id="<?php echo (int)$plan['id']; ?>" data-plan-name="<?php echo htmlspecialchars($plan['plan_name']); ?>" data-plan-role-id="<?php echo (int)$plan['role_id']; ?>">
                                            <i class="fe fe-user-plus fe-14"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning" data-toggle="modal" data-target="#editPlanModal" data-plan='<?php echo htmlspecialchars(json_encode($plan), ENT_QUOTES, "UTF-8"); ?>'>
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

<?php foreach ($plans as $plan): ?>
<div class="modal fade" id="viewPlanModal-<?php echo (int)$plan['id']; ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Plan Details</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="text-muted small">Plan Name</div>
                    <div class="font-weight-bold"><?php echo htmlspecialchars($plan['plan_name']); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="text-muted small">Role</div>
                        <div><?php echo htmlspecialchars($plan['position_title']); ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Status</div>
                        <div><?php echo ucfirst($plan['status']); ?></div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-muted small">Objectives</div>
                    <div><?php echo nl2br(htmlspecialchars($plan['objectives'] ?: 'No objectives provided.')); ?></div>
                </div>
                <div class="mt-3">
                    <div class="text-muted small">Success Metrics</div>
                    <div><?php echo nl2br(htmlspecialchars($plan['success_metrics'] ?: 'No success metrics provided.')); ?></div>
                </div>

                <hr>
                <h6>Plan Candidates</h6>
                <?php $planCandidates = $planCandidatesMap[$plan['id']] ?? []; ?>
                <?php if (empty($planCandidates)): ?>
                    <div class="text-muted">No candidates assigned to this plan.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Candidate</th>
                                    <th>Readiness</th>
                                    <th>Priority</th>
                                    <th>Target Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($planCandidates as $pc): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($pc['first_name'] . ' ' . $pc['last_name']); ?>
                                            <div class="text-muted small"><?php echo htmlspecialchars($pc['position']); ?></div>
                                        </td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $pc['readiness_level'])); ?></td>
                                        <td><?php echo (int)$pc['priority_order']; ?></td>
                                        <td><?php echo $pc['target_readiness_date'] ? date('M d, Y', strtotime($pc['target_readiness_date'])) : 'N/A'; ?></td>
                                        <td>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Remove this candidate from the plan?');">
                                                <input type="hidden" name="plan_candidate_id" value="<?php echo (int)$pc['id']; ?>">
                                                <button type="submit" name="remove_plan_candidate" class="btn btn-sm btn-outline-danger">
                                                    <i class="fe fe-trash fe-12"></i>
                                                </button>
                                            </form>
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
<?php endforeach; ?>

<!-- Create Plan Modal -->
<div class="modal fade" id="createPlanModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Succession Plan</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="plan_name">Plan Name *</label>
                        <input type="text" class="form-control" id="plan_name" name="plan_name" required>
                    </div>
                    <div class="form-group">
                        <label for="plan_role_id">Critical Role *</label>
                        <select class="form-control" id="plan_role_id" name="role_id" required>
                            <option value="">Select Role</option>
                            <?php foreach ($criticalRoles as $role): ?>
                                <option value="<?php echo (int)$role['id']; ?>">
                                    <?php echo htmlspecialchars($role['position_title'] . ' - ' . $role['department']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="plan_status">Status</label>
                                <select class="form-control" id="plan_status" name="status">
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="plan_start_date">Start Date</label>
                                <input type="date" class="form-control" id="plan_start_date" name="start_date">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="plan_end_date">End Date</label>
                                <input type="date" class="form-control" id="plan_end_date" name="end_date">
                            </div>
                        </div>
                        <div class="col-md-6"></div>
                    </div>
                    <div class="form-group">
                        <label for="plan_objectives">Objectives</label>
                        <textarea class="form-control" id="plan_objectives" name="objectives" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="plan_success_metrics">Success Metrics</label>
                        <textarea class="form-control" id="plan_success_metrics" name="success_metrics" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_plan" class="btn btn-primary">Create Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Plan Modal -->
<div class="modal fade" id="editPlanModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Succession Plan</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="edit_plan_id" name="plan_id">
                    <div class="form-group">
                        <label for="edit_plan_name">Plan Name *</label>
                        <input type="text" class="form-control" id="edit_plan_name" name="plan_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_role_id">Critical Role *</label>
                        <select class="form-control" id="edit_role_id" name="role_id" required>
                            <?php foreach ($criticalRoles as $role): ?>
                                <option value="<?php echo (int)$role['id']; ?>">
                                    <?php echo htmlspecialchars($role['position_title'] . ' - ' . $role['department']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_status">Status</label>
                                <select class="form-control" id="edit_status" name="status">
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_start_date">Start Date</label>
                                <input type="date" class="form-control" id="edit_start_date" name="start_date">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_end_date">End Date</label>
                                <input type="date" class="form-control" id="edit_end_date" name="end_date">
                            </div>
                        </div>
                        <div class="col-md-6"></div>
                    </div>
                    <div class="form-group">
                        <label for="edit_objectives">Objectives</label>
                        <textarea class="form-control" id="edit_objectives" name="objectives" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_success_metrics">Success Metrics</label>
                        <textarea class="form-control" id="edit_success_metrics" name="success_metrics" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_plan" class="btn btn-primary">Update Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Plan Candidate Modal -->
<div class="modal fade" id="addPlanCandidateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Candidate to Plan</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="plan_candidate_plan_id" name="plan_id">
                    <div class="form-group">
                        <label for="plan_candidate">Candidate *</label>
                        <select class="form-control" id="plan_candidate" name="candidate_id" required>
                            <option value="">Select Candidate</option>
                            <?php foreach ($successionCandidates as $candidate): ?>
                                <option value="<?php echo (int)$candidate['id']; ?>" data-role-id="<?php echo (int)$candidate['role_id']; ?>">
                                    <?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name'] . ' - ' . $candidate['position_title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="priority_order">Priority Order</label>
                                <input type="number" class="form-control" id="priority_order" name="priority_order" value="1" min="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="target_readiness_date">Target Readiness Date</label>
                                <input type="date" class="form-control" id="target_readiness_date" name="target_readiness_date">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="development_focus">Development Focus</label>
                        <textarea class="form-control" id="development_focus" name="development_focus" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_plan_candidate" class="btn btn-primary">Add Candidate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#addPlanCandidateModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var planId = button.data('plan-id');
        var planRoleId = button.data('plan-role-id');
        $('#plan_candidate_plan_id').val(planId || '');
        var select = document.getElementById('plan_candidate');
        if (select) {
            var options = Array.prototype.slice.call(select.options);
            var visibleCount = 0;
            options.forEach(function(option, index) {
                if (index === 0) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }
                var roleId = option.getAttribute('data-role-id');
                var matches = planRoleId && roleId && roleId === String(planRoleId);
                option.hidden = !matches;
                option.disabled = !matches;
                if (matches) {
                    visibleCount += 1;
                }
            });
            if (visibleCount === 0) {
                if (!select.querySelector('option[data-empty="1"]')) {
                    var emptyOption = document.createElement('option');
                    emptyOption.textContent = 'No candidates for this role';
                    emptyOption.disabled = true;
                    emptyOption.setAttribute('data-empty', '1');
                    select.appendChild(emptyOption);
                }
            } else {
                var existingEmpty = select.querySelector('option[data-empty="1"]');
                if (existingEmpty) {
                    existingEmpty.remove();
                }
            }
            select.value = '';
        }
    });

    $('#editPlanModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var plan = button.data('plan');
        if (!plan) {
            return;
        }
        $('#edit_plan_id').val(plan.id || '');
        $('#edit_plan_name').val(plan.plan_name || '');
        $('#edit_role_id').val(plan.role_id || '');
        $('#edit_status').val(plan.status || 'draft');
        $('#edit_start_date').val(plan.start_date || '');
        $('#edit_end_date').val(plan.end_date || '');
        $('#edit_objectives').val(plan.objectives || '');
        $('#edit_success_metrics').val(plan.success_metrics || '');
    });
});
</script>
