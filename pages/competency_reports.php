<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/competency.php';
require_once 'includes/functions/redirect_helper.php';

$auth = new SimpleAuth();
checkAuth($auth, 'view_all_data');

$current_user = $auth->getCurrentUser();
$competencyManager = new CompetencyManager();

$message = '';
$error = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['create_report'])) {
        $reportData = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'report_type' => $_POST['report_type'],
            'filters' => json_encode([
                'employee_id' => $_POST['employee_id'] ?? null,
                'department' => $_POST['department'] ?? null,
                'cycle_id' => $_POST['cycle_id'] ?? null,
                'date_from' => $_POST['date_from'] ?? null,
                'date_to' => $_POST['date_to'] ?? null
            ]),
            'created_by' => $current_user['id']
        ];
        
        try {
            $stmt = $db->prepare("INSERT INTO competency_reports (title, description, report_type, filters, created_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            if ($stmt->execute([
                $reportData['title'],
                $reportData['description'],
                $reportData['report_type'],
                $reportData['filters'],
                $reportData['created_by']
            ])) {
                $message = 'Report template created successfully!';
                $auth->logActivity('create_competency_report', 'competency_reports', null, null, $reportData);
            } else {
                $error = 'Failed to create report template.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
    
    if (isset($_POST['update_report'])) {
        $reportId = $_POST['report_id'];
        $updateData = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'report_type' => $_POST['report_type'],
            'filters' => json_encode([
                'employee_id' => $_POST['employee_id'] ?? null,
                'department' => $_POST['department'] ?? null,
                'cycle_id' => $_POST['cycle_id'] ?? null,
                'date_from' => $_POST['date_from'] ?? null,
                'date_to' => $_POST['date_to'] ?? null
            ])
        ];
        
        try {
            $stmt = $db->prepare("UPDATE competency_reports SET title = ?, description = ?, report_type = ?, filters = ?, updated_at = NOW() WHERE id = ?");
            if ($stmt->execute([
                $updateData['title'],
                $updateData['description'],
                $updateData['report_type'],
                $updateData['filters'],
                $reportId
            ])) {
                $message = 'Report template updated successfully!';
                $auth->logActivity('update_competency_report', 'competency_reports', $reportId, null, $updateData);
            } else {
                $error = 'Failed to update report template.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
    
    if (isset($_POST['delete_report'])) {
        $reportId = $_POST['report_id'];
        
        try {
            $stmt = $db->prepare("DELETE FROM competency_reports WHERE id = ?");
            if ($stmt->execute([$reportId])) {
                $message = 'Report template deleted successfully!';
                $auth->logActivity('delete_competency_report', 'competency_reports', $reportId, null, null);
            } else {
                $error = 'Failed to delete report template.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get filter parameters
$employee_id = $_GET['employee_id'] ?? null;
$department = $_GET['department'] ?? null;
$cycle_id = $_GET['cycle_id'] ?? null;

// Get data for filters
$cycles = $competencyManager->getAllCycles();
$db = getDB();

// Get saved report templates
try {
    $stmt = $db->prepare("SELECT * FROM competency_reports ORDER BY created_at DESC");
    $stmt->execute();
    $savedReports = $stmt->fetchAll();
} catch (PDOException $e) {
    $savedReports = [];
}

// Get departments
$stmt = $db->prepare("SELECT DISTINCT department FROM users WHERE department IS NOT NULL AND status = 'active' ORDER BY department");
$stmt->execute();
$departments = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get employees
$stmt = $db->prepare("SELECT id, first_name, last_name, department FROM users WHERE role = 'employee' AND status = 'active' ORDER BY last_name, first_name");
$stmt->execute();
$employees = $stmt->fetchAll();

// Get reports
$reports = $competencyManager->generateCompetencyReport($employee_id, $department, $cycle_id);
?>

<link rel="stylesheet" href="assets/css/competency.css">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Competency Reports</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createReportModal">
            <i class="fe fe-plus fe-16 mr-2"></i>Create Report Template
        </button>
        <button type="button" class="btn btn-success ml-2" onclick="exportReport()">
            <i class="fe fe-download fe-16 mr-2"></i>Export Report
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

<!-- Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Filter Reports</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="employee_id">Employee</label>
                            <select class="form-control" id="employee_id" name="employee_id">
                                <option value="">All Employees</option>
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?php echo $employee['id']; ?>" <?php echo ($employee_id == $employee['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="department">Department</label>
                            <select class="form-control" id="department" name="department">
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo htmlspecialchars($dept); ?>" <?php echo ($department == $dept) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cycle_id">Evaluation Cycle</label>
                            <select class="form-control" id="cycle_id" name="cycle_id">
                                <option value="">All Cycles</option>
                                <?php foreach ($cycles as $cycle): ?>
                                    <option value="<?php echo $cycle['id']; ?>" <?php echo ($cycle_id == $cycle['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cycle['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fe fe-search fe-16 mr-2"></i>Filter
                                </button>
                                <a href="?page=competency_reports" class="btn btn-outline-secondary">
                                    <i class="fe fe-refresh-cw fe-16 mr-2"></i>Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Saved Report Templates -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Saved Report Templates</h5>
            </div>
            <div class="card-body">
                <?php if (empty($savedReports)): ?>
                    <div class="text-center py-4">
                        <i class="fe fe-file-text fe-48 text-muted"></i>
                        <h4 class="text-muted mt-3">No Saved Templates</h4>
                        <p class="text-muted">Create your first report template to save time on future reports.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Template Name</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($savedReports as $report): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($report['title'] ?? 'Untitled'); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?php echo ucfirst($report['report_type'] ?? 'unknown'); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($report['description'] ?? 'No description'); ?></td>
                                        <td><?php echo $report['created_at'] ? date('M d, Y', strtotime($report['created_at'])) : 'N/A'; ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="loadTemplate(<?php echo $report['id']; ?>)">
                                                    <i class="fe fe-play fe-14"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning" onclick="editTemplate(<?php echo htmlspecialchars(json_encode($report)); ?>)">
                                                    <i class="fe fe-edit fe-14"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteTemplate(<?php echo $report['id']; ?>)">
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

<!-- Summary Statistics -->
<?php if (!empty($reports)): ?>
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary"><?php echo count($reports); ?></h3>
                    <p class="text-muted mb-0">Total Evaluations</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success"><?php echo number_format(array_sum(array_column($reports, 'overall_score')) / count($reports), 1); ?></h3>
                    <p class="text-muted mb-0">Average Score</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info"><?php echo count(array_unique(array_column($reports, 'employee_id'))); ?></h3>
                    <p class="text-muted mb-0">Employees Evaluated</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning"><?php echo count(array_unique(array_column($reports, 'cycle_id'))); ?></h3>
                    <p class="text-muted mb-0">Evaluation Cycles</p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Reports -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Competency Reports</h5>
            </div>
            <div class="card-body">
                <?php if (empty($reports)): ?>
                    <div class="text-center py-4">
                        <i class="fe fe-bar-chart-2 fe-48 text-muted"></i>
                        <h4 class="text-muted mt-3">No Reports Found</h4>
                        <p class="text-muted">No completed evaluations match your filter criteria.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="reportsTable">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Cycle</th>
                                    <th>Model</th>
                                    <th>Evaluator</th>
                                    <th>Overall Score</th>
                                    <th>Completed</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $report): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars(($report['employee_first_name'] ?? 'Unknown') . ' ' . ($report['employee_last_name'] ?? 'User')); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($report['employee_department'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($report['employee_position'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge badge-info"><?php echo htmlspecialchars($report['cycle_name'] ?? 'N/A'); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($report['model_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars(($report['evaluator_first_name'] ?? 'Unknown') . ' ' . ($report['evaluator_last_name'] ?? 'User')); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="badge badge-primary mr-2"><?php echo number_format($report['overall_score'], 1); ?></span>
                                                <div class="progress" style="width: 60px; height: 8px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo ($report['overall_score'] / 5) * 100; ?>%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo $report['completed_at'] ? date('M d, Y', strtotime($report['completed_at'])) : 'N/A'; ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewDetailedReport(<?php echo $report['id']; ?>)">
                                                <i class="fe fe-eye fe-14"></i> View Details
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
    </div>
</div>

<!-- Create Report Template Modal -->
<div class="modal fade" id="createReportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Report Template</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Template Name *</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="report_type">Report Type *</label>
                                <select class="form-control" id="report_type" name="report_type" required>
                                    <option value="">Select Type</option>
                                    <option value="summary">Summary Report</option>
                                    <option value="detailed">Detailed Report</option>
                                    <option value="trend">Trend Analysis</option>
                                    <option value="comparison">Comparison Report</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <h6>Default Filters</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="employee_id">Employee</label>
                                <select class="form-control" id="employee_id" name="employee_id">
                                    <option value="">All Employees</option>
                                    <?php foreach ($employees as $employee): ?>
                                        <option value="<?php echo $employee['id']; ?>">
                                            <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="department">Department</label>
                                <select class="form-control" id="department" name="department">
                                    <option value="">All Departments</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo htmlspecialchars($dept); ?>">
                                            <?php echo htmlspecialchars($dept); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cycle_id">Evaluation Cycle</label>
                                <select class="form-control" id="cycle_id" name="cycle_id">
                                    <option value="">All Cycles</option>
                                    <?php foreach ($cycles as $cycle): ?>
                                        <option value="<?php echo $cycle['id']; ?>">
                                            <?php echo htmlspecialchars($cycle['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_from">Date From</label>
                                <input type="date" class="form-control" id="date_from" name="date_from">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_to">Date To</label>
                                <input type="date" class="form-control" id="date_to" name="date_to">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_report" class="btn btn-primary">Create Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Report Template Modal -->
<div class="modal fade" id="editReportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Report Template</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="edit_report_id" name="report_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_title">Template Name *</label>
                                <input type="text" class="form-control" id="edit_title" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_report_type">Report Type *</label>
                                <select class="form-control" id="edit_report_type" name="report_type" required>
                                    <option value="">Select Type</option>
                                    <option value="summary">Summary Report</option>
                                    <option value="detailed">Detailed Report</option>
                                    <option value="trend">Trend Analysis</option>
                                    <option value="comparison">Comparison Report</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    
                    <h6>Default Filters</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_employee_id">Employee</label>
                                <select class="form-control" id="edit_employee_id" name="employee_id">
                                    <option value="">All Employees</option>
                                    <?php foreach ($employees as $employee): ?>
                                        <option value="<?php echo $employee['id']; ?>">
                                            <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_department">Department</label>
                                <select class="form-control" id="edit_department" name="department">
                                    <option value="">All Departments</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo htmlspecialchars($dept); ?>">
                                            <?php echo htmlspecialchars($dept); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_cycle_id">Evaluation Cycle</label>
                                <select class="form-control" id="edit_cycle_id" name="cycle_id">
                                    <option value="">All Cycles</option>
                                    <?php foreach ($cycles as $cycle): ?>
                                        <option value="<?php echo $cycle['id']; ?>">
                                            <?php echo htmlspecialchars($cycle['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_date_from">Date From</label>
                                <input type="date" class="form-control" id="edit_date_from" name="date_from">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_date_to">Date To</label>
                                <input type="date" class="form-control" id="edit_date_to" name="date_to">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_report" class="btn btn-primary">Update Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Report Template Modal -->
<div class="modal fade" id="deleteReportModal" tabindex="-1" role="dialog">
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
                    <input type="hidden" id="delete_report_id" name="report_id">
                    <p>Are you sure you want to delete this report template? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_report" class="btn btn-danger">Delete Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewDetailedReport(evaluationId) {
    window.location.href = '?page=evaluation_view&id=' + evaluationId;
}

function exportReport() {
    // Create CSV content
    const table = document.getElementById('reportsTable');
    if (!table) return;
    
    let csv = 'Employee,Department,Position,Cycle,Model,Evaluator,Overall Score,Completed\n';
    
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const rowData = [];
        cells.forEach((cell, index) => {
            if (index !== 8) { // Skip actions column
                let text = cell.textContent.trim();
                // Remove badge text for cleaner CSV
                text = text.replace(/\d+\.\d+/, '').trim();
                rowData.push('"' + text + '"');
            }
        });
        csv += rowData.join(',') + '\n';
    });
    
    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'competency_reports_' + new Date().toISOString().split('T')[0] + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

function loadTemplate(templateId) {
    // This would load the template filters into the current form
    alert('Load template functionality would be implemented here.');
}

function editTemplate(template) {
    document.getElementById('edit_report_id').value = template.id;
    document.getElementById('edit_title').value = template.title;
    document.getElementById('edit_description').value = template.description;
    document.getElementById('edit_report_type').value = template.report_type;
    
    // Parse filters
    const filters = JSON.parse(template.filters);
    if (filters.employee_id) document.getElementById('edit_employee_id').value = filters.employee_id;
    if (filters.department) document.getElementById('edit_department').value = filters.department;
    if (filters.cycle_id) document.getElementById('edit_cycle_id').value = filters.cycle_id;
    if (filters.date_from) document.getElementById('edit_date_from').value = filters.date_from;
    if (filters.date_to) document.getElementById('edit_date_to').value = filters.date_to;
    
    $('#editReportModal').modal('show');
}

function deleteTemplate(templateId) {
    document.getElementById('delete_report_id').value = templateId;
    $('#deleteReportModal').modal('show');
}
</script>
