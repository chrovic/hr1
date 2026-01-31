<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/competency.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$competencyManager = new CompetencyManager();

// Get employee's evaluations
$evaluations = $competencyManager->getEmployeeEvaluations($current_user['id']);
$trends = $competencyManager->getEmployeeCompetencyTrends($current_user['id']);
?>

<link rel="stylesheet" href="assets/css/competency.css">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">My Evaluations</h1>
</div>

<!-- Evaluation Trends Chart -->
<?php if (!empty($trends)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Performance Trends</h5>
                </div>
                <div class="card-body">
                    <div style="height:260px;">
                        <canvas id="trendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- My Evaluations -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Evaluation History</h5>
            </div>
            <div class="card-body">
                <?php if (empty($evaluations)): ?>
                    <div class="text-center py-4">
                        <i class="fe fe-clipboard fe-48 text-muted"></i>
                        <h4 class="text-muted mt-3">No Evaluations Yet</h4>
                        <p class="text-muted">You haven't been evaluated yet. Evaluations will appear here once completed.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Cycle</th>
                                    <th>Type</th>
                                    <th>Model</th>
                                    <th>Evaluator</th>
                                    <th>Overall Score</th>
                                    <th>Status</th>
                                    <th>Completed</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($evaluations as $evaluation): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($evaluation['cycle_name']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?php echo ucfirst($evaluation['cycle_type']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($evaluation['model_name']); ?></td>
                                        <td><?php echo htmlspecialchars($evaluation['evaluator_first_name'] . ' ' . $evaluation['evaluator_last_name']); ?></td>
                                        <td>
                                            <?php if ($evaluation['overall_score']): ?>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge badge-primary mr-2"><?php echo number_format($evaluation['overall_score'], 1); ?></span>
                                                    <div class="progress progress-mini" style="width: 60px;">
                                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo ($evaluation['overall_score'] / 5) * 100; ?>%"></div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            switch($evaluation['status']) {
                                                case 'pending': $statusClass = 'badge-warning'; break;
                                                case 'in_progress': $statusClass = 'badge-info'; break;
                                                case 'completed': $statusClass = 'badge-success'; break;
                                                case 'cancelled': $statusClass = 'badge-danger'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($evaluation['status']); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($evaluation['completed_at']): ?>
                                                <?php echo date('M d, Y', strtotime($evaluation['completed_at'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($evaluation['status'] === 'completed'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewEvaluation(<?php echo $evaluation['id']; ?>)">
                                                    <i class="fe fe-eye fe-14"></i> View Details
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">Pending</span>
                                            <?php endif; ?>
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

<!-- Performance Summary -->
<?php if (!empty($evaluations)): ?>
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary"><?php echo count($evaluations); ?></h3>
                    <p class="text-muted mb-0">Total Evaluations</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">
                        <?php 
                        $completed = array_filter($evaluations, function($e) { return $e['status'] === 'completed'; });
                        echo count($completed);
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info">
                        <?php 
                        $scores = array_column(array_filter($evaluations, function($e) { return $e['overall_score']; }), 'overall_score');
                        echo !empty($scores) ? number_format(array_sum($scores) / count($scores), 1) : '0.0';
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Average Score</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning">
                        <?php 
                        $pending = array_filter($evaluations, function($e) { return $e['status'] === 'pending'; });
                        echo count($pending);
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Pending</p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="assets/vendor/js/Chart.min.js"></script>
<script>
function viewEvaluation(evaluationId) {
    window.location.href = '?page=evaluation_view&id=' + evaluationId;
}

// Trends Chart
<?php if (!empty($trends)): ?>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('trendsChart').getContext('2d');
    const trendsData = <?php echo json_encode($trends); ?>;
    
    const filtered = trendsData
        .map(trend => ({
            label: trend.cycle_name,
            score: parseFloat(trend.overall_score)
        }))
        .filter(item => !isNaN(item.score));

    const labels = filtered.map(item => item.label);
    const scores = filtered.map(item => item.score);

    if (!labels.length) {
        const chartContainer = document.getElementById('trendsChart').parentElement;
        chartContainer.innerHTML = '<div class="text-center text-muted py-4">No trend data available.</div>';
        return;
    }
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Overall Score',
                data: scores,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        max: 5,
                        stepSize: 0.5
                    }
                }],
                xAxes: [{
                    gridLines: {
                        display: false
                    }
                }]
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
<?php endif; ?>
</script>
