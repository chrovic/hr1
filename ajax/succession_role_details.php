<?php
require_once '../includes/data/db.php';
require_once '../includes/functions/simple_auth.php';
require_once '../includes/functions/succession_planning.php';

header('Content-Type: text/html; charset=UTF-8');

$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || !$auth->hasPermission('manage_evaluations')) {
	echo '<div class="alert alert-danger m-3">Unauthorized</div>';
	exit;
}

$roleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$manager = new SuccessionPlanning();
$role = $roleId ? $manager->getCriticalRole($roleId) : null;

if (!$role) {
	echo '<div class="alert alert-warning m-3">Role not found.</div>';
	exit;
}
?>
<?php 
    $risk = strtolower((string)($role['risk_level'] ?? 'unknown'));
    $riskClass = 'badge-secondary';
    if ($risk === 'low') $riskClass = 'badge-success';
    elseif ($risk === 'medium') $riskClass = 'badge-warning';
    elseif ($risk === 'high') $riskClass = 'badge-danger';
    elseif ($risk === 'critical') $riskClass = 'badge-dark';
?>
<div class="container-fluid p-0">
    <div class="px-3 pt-3 pb-2 border-bottom mb-3">
        <h4 class="mb-1" style="font-weight:600;">
            <i class="fe fe-briefcase mr-2"></i><?php echo htmlspecialchars($role['position_title']); ?>
        </h4>
        <div>
            <span class="badge badge-primary mr-2"><i class="fe fe-layers mr-1"></i><?php echo htmlspecialchars($role['department']); ?></span>
            <span class="badge <?php echo $riskClass; ?> mr-2"><i class="fe fe-activity mr-1"></i><?php echo htmlspecialchars(ucfirst($risk)); ?></span>
            <span class="badge badge-light"><i class="fe fe-user mr-1"></i><?php echo $role['first_name'] ? htmlspecialchars($role['first_name'].' '.$role['last_name']) : 'Vacant'; ?></span>
        </div>
    </div>

    <div class="row px-3 pb-3">
        <div class="col-md-6 mb-3">
            <h6 class="text-uppercase text-muted mb-2">Description</h6>
            <div class="card shadow-sm">
                <div class="card-body py-3">
                    <div class="text-muted" style="white-space:pre-wrap;"><?php echo nl2br(htmlspecialchars((string)($role['description'] ?? 'No description provided.'))); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <h6 class="text-uppercase text-muted mb-2">Requirements</h6>
            <div class="card shadow-sm">
                <div class="card-body py-3">
                    <div class="text-muted" style="white-space:pre-wrap;"><?php echo nl2br(htmlspecialchars((string)($role['requirements'] ?? 'No requirements listed.'))); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
