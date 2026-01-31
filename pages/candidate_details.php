<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/succession_planning.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || !$auth->hasPermission('manage_succession')) {
    header('Location: auth/login.php');
    exit;
}

$candidateId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$successionManager = new SuccessionPlanning();

$message = '';
$error = '';
if (isset($_GET['success']) && $_GET['success'] === 'assessment_added') {
    $message = 'Assessment added successfully!';
}
if (isset($_GET['error']) && $_GET['error'] === 'assessment_add_failed') {
    $error = 'Failed to add assessment.';
}

$candidate = $candidateId ? $successionManager->getCandidateDetails($candidateId) : null;
$assessments = $candidateId ? $successionManager->getCandidateAssessments($candidateId) : [];

$readinessLabel = $candidate && $candidate['readiness_level']
    ? ucfirst(str_replace('_', ' ', $candidate['readiness_level']))
    : 'Not Set';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-1">Candidate Details</h1>
        <p class="text-muted mb-0">Review readiness, assessments, and development notes.</p>
    </div>
    <div>
        <a href="?page=succession_planning" class="btn btn-outline-secondary">
            <i class="fe fe-arrow-left fe-16 mr-2"></i>Back to Succession Planning
        </a>
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

<?php if (!$candidate): ?>
    <div class="card">
        <div class="card-body text-center">
            <i class="fe fe-user-x fe-48 text-muted"></i>
            <h4 class="text-muted mt-3">Candidate not found</h4>
            <p class="text-muted mb-0">The selected candidate record could not be located.</p>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Candidate Profile</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="text-muted small">Name</div>
                                <div class="font-weight-bold">
                                    <?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']); ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="text-muted small">Current Position</div>
                                <div><?php echo htmlspecialchars($candidate['position'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="mb-3">
                                <div class="text-muted small">Department</div>
                                <div><?php echo htmlspecialchars($candidate['department'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="text-muted small">Target Role</div>
                                <div class="font-weight-bold">
                                    <?php echo htmlspecialchars($candidate['position_title']); ?>
                                </div>
                                <div class="text-muted small">
                                    <?php echo htmlspecialchars($candidate['role_department']); ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="text-muted small">Readiness Level</div>
                                <div>
                                    <span class="badge badge-info"><?php echo htmlspecialchars($readinessLabel); ?></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="text-muted small">Assigned By</div>
                                <div><?php echo htmlspecialchars($candidate['assigned_by_first_name'] . ' ' . $candidate['assigned_by_last_name']); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="text-muted small">Assessment Date</div>
                                <div><?php echo $candidate['assessment_date'] ? date('M d, Y', strtotime($candidate['assessment_date'])) : 'Not assessed'; ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="text-muted small">Next Review Date</div>
                                <div><?php echo $candidate['next_review_date'] ? date('M d, Y', strtotime($candidate['next_review_date'])) : 'Not scheduled'; ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Development Plan</div>
                        <div><?php echo nl2br(htmlspecialchars($candidate['development_plan'] ?: 'No development plan provided.')); ?></div>
                    </div>

                    <div>
                        <div class="text-muted small">Notes</div>
                        <div><?php echo nl2br(htmlspecialchars($candidate['notes'] ?: 'No notes added.')); ?></div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Assessment History</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($assessments)): ?>
                        <div class="text-center py-4">
                            <i class="fe fe-clipboard fe-32 text-muted"></i>
                            <p class="text-muted mt-2 mb-0">No assessments recorded yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Assessor</th>
                                        <th>Overall Score</th>
                                        <th>Key Strengths</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assessments as $assessment): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($assessment['assessment_date'])); ?></td>
                                            <td><?php echo ucfirst($assessment['assessment_type']); ?></td>
                                            <td><?php echo htmlspecialchars($assessment['assessor_first_name'] . ' ' . $assessment['assessor_last_name']); ?></td>
                                            <td><?php echo $assessment['overall_readiness_score'] !== null ? (int)$assessment['overall_readiness_score'] : '-'; ?></td>
                                            <td><?php echo htmlspecialchars($assessment['strengths'] ?: '-'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Add Assessment</h5>
                </div>
                <form method="POST">
                    <div class="card-body">
                        <input type="hidden" name="candidate_id" value="<?php echo (int)$candidateId; ?>">
                        <div class="form-group">
                            <label for="assessment_type">Assessment Type</label>
                            <select class="form-control" id="assessment_type" name="assessment_type" required>
                                <option value="initial">Initial</option>
                                <option value="progress">Progress</option>
                                <option value="final">Final</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="technical_readiness_score">Technical Readiness (0-100)</label>
                            <input type="number" class="form-control" id="technical_readiness_score" name="technical_readiness_score" min="0" max="100">
                        </div>
                        <div class="form-group">
                            <label for="leadership_readiness_score">Leadership Readiness (0-100)</label>
                            <input type="number" class="form-control" id="leadership_readiness_score" name="leadership_readiness_score" min="0" max="100">
                        </div>
                        <div class="form-group">
                            <label for="cultural_fit_score">Cultural Fit (0-100)</label>
                            <input type="number" class="form-control" id="cultural_fit_score" name="cultural_fit_score" min="0" max="100">
                        </div>
                        <div class="form-group">
                            <label for="overall_readiness_score">Overall Readiness (0-100)</label>
                            <input type="number" class="form-control" id="overall_readiness_score" name="overall_readiness_score" min="0" max="100">
                        </div>
                        <div class="form-group">
                            <label for="strengths">Strengths</label>
                            <textarea class="form-control" id="strengths" name="strengths" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="development_areas">Development Areas</label>
                            <textarea class="form-control" id="development_areas" name="development_areas" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="recommendations">Recommendations</label>
                            <textarea class="form-control" id="recommendations" name="recommendations" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="assessment_date">Assessment Date</label>
                            <input type="date" class="form-control" id="assessment_date" name="assessment_date" required>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" name="add_assessment" class="btn btn-primary btn-block">
                            Add Assessment
                        </button>
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
});
</script>
