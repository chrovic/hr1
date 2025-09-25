<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/hr_manager.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || !$auth->hasPermission('view_reports')) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$db = getDB();

// Initialize HR Manager
$hr_manager = new HRManager($db);

// Get dashboard data
$dashboard_stats = $hr_manager->getDashboardStats();
$performance_summary = $hr_manager->getEmployeePerformanceSummary();
$learning_summary = $hr_manager->getLearningProgressSummary();
$department_overview = $hr_manager->getDepartmentPerformanceOverview();
$recent_activities = $hr_manager->getRecentHRActivities(20);

// Handle report generation
$report_type = $_GET['report'] ?? 'overview';
$date_range = $_GET['date_range'] ?? '30';
?>

<div class="row">
    <div class="col-12">
        <div class="mb-2">
            <h1 class="h3 mb-1">HR Reports & Analytics</h1>
            <p class="text-muted">Comprehensive HR analytics and reporting dashboard</p>
        </div>
    </div>
</div>

<!-- Report Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Report Filters</strong>
            </div>
            <div class="card-body">
                <form method="GET" class="row">
                    <input type="hidden" name="page" value="hr_reports">
                    <div class="col-md-4 mb-3">
                        <label for="report_type" class="form-label">Report Type</label>
                        <select class="form-control" id="report_type" name="report">
                            <option value="overview" <?php echo $report_type === 'overview' ? 'selected' : ''; ?>>Overview Dashboard</option>
                            <option value="performance" <?php echo $report_type === 'performance' ? 'selected' : ''; ?>>Performance Analytics</option>
                            <option value="learning" <?php echo $report_type === 'learning' ? 'selected' : ''; ?>>Learning Progress</option>
                            <option value="succession" <?php echo $report_type === 'succession' ? 'selected' : ''; ?>>Succession Planning</option>
                            <option value="requests" <?php echo $report_type === 'requests' ? 'selected' : ''; ?>>Employee Requests</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="date_range" class="form-label">Date Range</label>
                        <select class="form-control" id="date_range" name="date_range">
                            <option value="7" <?php echo $date_range === '7' ? 'selected' : ''; ?>>Last 7 Days</option>
                            <option value="30" <?php echo $date_range === '30' ? 'selected' : ''; ?>>Last 30 Days</option>
                            <option value="90" <?php echo $date_range === '90' ? 'selected' : ''; ?>>Last 90 Days</option>
                            <option value="365" <?php echo $date_range === '365' ? 'selected' : ''; ?>>Last Year</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <span class="fe fe-refresh-cw fe-16 mr-2"></span>
                                Generate Report
                            </button>
                            <button type="button" class="btn btn-outline-secondary ml-2" onclick="exportReport()">
                                <span class="fe fe-download fe-16 mr-2"></span>
                                Export
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if ($report_type === 'overview'): ?>
<!-- Overview Dashboard -->
<div class="row">
    <!-- Key Metrics -->
    <div class="col-md-3 mb-4">
        <div class="card shadow">
            <div class="card-body text-center">
                <div class="display-4 font-weight-bold text-primary"><?php echo $dashboard_stats['total_employees'] ?? 0; ?></div>
                <div class="text-muted">Total Employees</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card shadow">
            <div class="card-body text-center">
                <div class="display-4 font-weight-bold text-warning"><?php echo $dashboard_stats['pending_evaluations'] ?? 0; ?></div>
                <div class="text-muted">Pending Evaluations</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card shadow">
            <div class="card-body text-center">
                <div class="display-4 font-weight-bold text-success"><?php echo $dashboard_stats['active_learning_sessions'] ?? 0; ?></div>
                <div class="text-muted">Active Learning</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card shadow">
            <div class="card-body text-center">
                <div class="display-4 font-weight-bold text-info"><?php echo $dashboard_stats['pending_requests'] ?? 0; ?></div>
                <div class="text-muted">Pending Requests</div>
            </div>
        </div>
    </div>
</div>

<!-- Performance Summary -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Performance Summary</strong>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h4 text-primary"><?php echo number_format($performance_summary['avg_rating'] ?? 0, 1); ?></div>
                        <div class="text-muted">Average Rating</div>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-success"><?php echo $performance_summary['completed_evaluations'] ?? 0; ?></div>
                        <div class="text-muted">Completed Evaluations</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Learning Progress</strong>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h4 text-success"><?php echo $learning_summary['completed'] ?? 0; ?></div>
                        <div class="text-muted">Completed</div>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-warning"><?php echo $learning_summary['in_progress'] ?? 0; ?></div>
                        <div class="text-muted">In Progress</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Department Performance -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Department Performance Overview</strong>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Employees</th>
                                <th>Evaluations</th>
                                <th>Average Rating</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($department_overview as $dept): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($dept['department_name']); ?></td>
                                <td><?php echo $dept['employee_count']; ?></td>
                                <td><?php echo $dept['evaluation_count']; ?></td>
                                <td><?php echo number_format($dept['avg_rating'] ?? 0, 1); ?></td>
                                <td>
                                    <?php 
                                    $rating = $dept['avg_rating'] ?? 0;
                                    if ($rating >= 4.0) {
                                        echo '<span class="badge badge-success">Excellent</span>';
                                    } elseif ($rating >= 3.0) {
                                        echo '<span class="badge badge-warning">Good</span>';
                                    } else {
                                        echo '<span class="badge badge-danger">Needs Improvement</span>';
                                    }
                                    ?>
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

<?php elseif ($report_type === 'performance'): ?>
<!-- Performance Analytics -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Performance Analytics</strong>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fe fe-info fe-16 mr-2"></i>
                    Performance analytics charts and detailed metrics will be displayed here.
                </div>
            </div>
        </div>
    </div>
</div>

<?php elseif ($report_type === 'learning'): ?>
<!-- Learning Progress Report -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Learning Progress Report</strong>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fe fe-info fe-16 mr-2"></i>
                    Learning progress analytics and training effectiveness metrics will be displayed here.
                </div>
            </div>
        </div>
    </div>
</div>

<?php elseif ($report_type === 'succession'): ?>
<!-- Succession Planning Report -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Succession Planning Report</strong>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fe fe-info fe-16 mr-2"></i>
                    Succession planning analytics and talent pipeline metrics will be displayed here.
                </div>
            </div>
        </div>
    </div>
</div>

<?php elseif ($report_type === 'requests'): ?>
<!-- Employee Requests Report -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Employee Requests Report</strong>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fe fe-info fe-16 mr-2"></i>
                    Employee requests analytics and approval metrics will be displayed here.
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recent Activities -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Recent HR Activities</strong>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php if (empty($recent_activities)): ?>
                        <div class="list-group-item px-0 text-center text-muted">
                            <i class="fe fe-activity fe-48 mb-3"></i>
                            <p>No recent activities found.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="list-group-item px-0">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="fe fe-<?php echo getActivityIcon($activity['action']); ?> fe-16 text-primary"></span>
                                    </div>
                                    <div class="col">
                                        <strong><?php echo htmlspecialchars($activity['action']); ?></strong>
                                        <div class="text-muted small">
                                            <?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?>
                                            <?php if (isset($activity['table_name']) && $activity['table_name']): ?>
                                                - <?php echo htmlspecialchars($activity['table_name']); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <small class="text-muted"><?php echo timeAgo($activity['created_at']); ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportReport() {
    // Export functionality will be implemented here
    alert('Export functionality will be implemented');
}
</script>

