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

$department = $_GET['department'] ?? '';
$riskLevel = $_GET['risk_level'] ?? '';

$filters = [];
if ($department !== '') {
    $filters['department'] = $department;
}
if ($riskLevel !== '') {
    $filters['risk_level'] = $riskLevel;
}

$reportData = $successionManager->generateSuccessionReport($filters);
?>

<div class="d-flex justify-content-between align-items-center flex-wrap pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-1">Succession Report</h1>
        <p class="text-muted mb-0">Summary of candidate readiness by critical role.</p>
    </div>
    <div>
        <a href="?page=succession_plans" class="btn btn-outline-secondary mr-2">
            <i class="fe fe-arrow-left fe-16 mr-2"></i>Back to Succession Plans
        </a>
        <button type="button" class="btn btn-primary" id="exportCsvBtn" data-export-url="ajax/succession_reports_export.php?<?php echo $department !== '' ? 'department=' . urlencode($department) . '&' : ''; ?><?php echo $riskLevel !== '' ? 'risk_level=' . urlencode($riskLevel) : ''; ?>">
            <i class="fe fe-download fe-16 mr-2"></i>Export CSV
        </button>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="form-inline">
            <input type="hidden" name="page" value="succession_reports">
            <div class="form-group mr-3">
                <label class="mr-2" for="department">Department</label>
                <input type="text" class="form-control" id="department" name="department" value="<?php echo htmlspecialchars($department); ?>">
            </div>
            <div class="form-group mr-3">
                <label class="mr-2" for="risk_level">Risk Level</label>
                <select class="form-control" id="risk_level" name="risk_level">
                    <option value="">All</option>
                    <option value="low" <?php echo $riskLevel === 'low' ? 'selected' : ''; ?>>Low</option>
                    <option value="medium" <?php echo $riskLevel === 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="high" <?php echo $riskLevel === 'high' ? 'selected' : ''; ?>>High</option>
                    <option value="critical" <?php echo $riskLevel === 'critical' ? 'selected' : ''; ?>>Critical</option>
                </select>
            </div>
            <button type="submit" class="btn btn-outline-primary">Apply Filters</button>
        </form>
    </div>
</div>

<!-- Export Confirmation Modal -->
<div class="modal fade" id="exportConfirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Export</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Please enter your password to export the succession report.</p>
                <div class="form-group mb-0">
                    <label for="export_password">Password</label>
                    <input type="password" class="form-control" id="export_password" placeholder="Enter your password" required>
                    <div class="invalid-feedback">Password is required.</div>
                </div>
                <div class="text-danger small mt-2 d-none" id="export_error">Incorrect password.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmExportBtn">Verify & Export</button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Report Results</h5>
    </div>
    <div class="card-body">
        <?php if (empty($reportData)): ?>
            <div class="text-center py-4">
                <i class="fe fe-bar-chart-2 fe-32 text-muted"></i>
                <p class="text-muted mt-2 mb-0">No data available for the selected filters.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Risk Level</th>
                            <th>Total Candidates</th>
                            <th>Ready Now</th>
                            <th>Ready Soon</th>
                            <th>Development Needed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['position_title'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['department'] ?? ''); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo ($row['risk_level'] ?? '') === 'high' ? 'danger' : (($row['risk_level'] ?? '') === 'medium' ? 'warning' : 'success'); ?>">
                                        <?php echo ucfirst($row['risk_level'] ?? ''); ?>
                                    </span>
                                </td>
                                <td><?php echo (int)($row['total_candidates'] ?? 0); ?></td>
                                <td><?php echo (int)($row['ready_now'] ?? 0); ?></td>
                                <td><?php echo (int)($row['ready_soon'] ?? 0); ?></td>
                                <td><?php echo (int)($row['development_needed'] ?? 0); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var exportBtn = document.getElementById('exportCsvBtn');
    var exportModal = $('#exportConfirmModal');
    var confirmBtn = document.getElementById('confirmExportBtn');
    var passwordInput = document.getElementById('export_password');
    var errorMsg = document.getElementById('export_error');

    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            if (passwordInput) {
                passwordInput.value = '';
                passwordInput.classList.remove('is-invalid');
            }
            if (errorMsg) {
                errorMsg.classList.add('d-none');
            }
            exportModal.modal('show');
        });
    }

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            var password = passwordInput ? passwordInput.value.trim() : '';
            if (!password) {
                if (passwordInput) {
                    passwordInput.classList.add('is-invalid');
                }
                return;
            }
            if (passwordInput) {
                passwordInput.classList.remove('is-invalid');
            }
            if (errorMsg) {
                errorMsg.classList.add('d-none');
            }

            fetch('ajax/verify_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'password=' + encodeURIComponent(password)
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data && data.success) {
                    exportModal.modal('hide');
                    var exportUrl = exportBtn ? exportBtn.getAttribute('data-export-url') : '';
                    if (exportUrl) {
                        window.location.href = exportUrl;
                    }
                } else {
                    if (errorMsg) {
                        errorMsg.textContent = (data && data.message) ? data.message : 'Incorrect password.';
                        errorMsg.classList.remove('d-none');
                    }
                }
            })
            .catch(function() {
                if (errorMsg) {
                    errorMsg.textContent = 'Verification failed. Please try again.';
                    errorMsg.classList.remove('d-none');
                }
            });
        });
    }
});
</script>
