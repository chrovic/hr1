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

$roleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$role = $roleId ? $successionManager->getCriticalRole($roleId) : null;
$candidates = $roleId ? $successionManager->getRoleCandidates($roleId) : [];
?>

<link rel="stylesheet" href="assets/css/competency.css">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
	<h1 class="h2">Critical Role Details</h1>
	<div class="btn-toolbar mb-2 mb-md-0">
		<a href="?page=succession_planning" class="btn btn-secondary">
			<i class="fe fe-arrow-left fe-16 mr-2"></i>Back to Succession Planning
		</a>
	</div>
</div>

<?php if (!$role): ?>
	<div class="alert alert-warning">Role not found.</div>
<?php else: ?>
	<div class="row mb-4">
		<div class="col-12">
			<div class="card">
				<div class="card-header"><h5 class="card-title mb-0">Role Information</h5></div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<p><strong>Position:</strong> <?php echo htmlspecialchars($role['position_title']); ?></p>
							<p><strong>Department:</strong> <?php echo htmlspecialchars($role['department']); ?></p>
							<p><strong>Risk Level:</strong> <?php echo htmlspecialchars(ucfirst((string)($role['risk_level'] ?? 'unknown'))); ?></p>
						</div>
						<div class="col-md-6">
							<p><strong>Incumbent:</strong> <?php echo $role['first_name'] ? htmlspecialchars($role['first_name'].' '.$role['last_name']) : '<span class="text-muted">Vacant</span>'; ?></p>
							<p><strong>Created By:</strong> <?php echo htmlspecialchars(($role['created_by_first_name'] ?? '').' '.($role['created_by_last_name'] ?? '')); ?></p>
						</div>
					</div>
					<p class="mb-1"><strong>Description</strong></p>
					<p class="text-muted"><?php echo nl2br(htmlspecialchars((string)($role['description'] ?? ''))); ?></p>
					<p class="mb-1"><strong>Requirements</strong></p>
					<p class="text-muted"><?php echo nl2br(htmlspecialchars((string)($role['requirements'] ?? ''))); ?></p>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header"><h5 class="card-title mb-0">Succession Candidates</h5></div>
				<div class="card-body">
					<?php if (empty($candidates)): ?>
						<p class="text-muted mb-0">No candidates assigned to this role.</p>
					<?php else: ?>
						<div class="table-responsive">
							<table class="table table-hover">
								<thead>
									<tr>
										<th>Employee</th>
										<th>Position</th>
										<th>Readiness</th>
										<th>Assigned By</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($candidates as $c): ?>
										<tr>
											<td><?php echo htmlspecialchars($c['first_name'].' '.$c['last_name']); ?></td>
											<td><?php echo htmlspecialchars((string)($c['position'] ?? '')); ?></td>
											<td><span class="badge badge-info"><?php echo htmlspecialchars(ucfirst((string)$c['readiness_level'])); ?></span></td>
											<td><?php echo htmlspecialchars($c['assigned_by_first_name'].' '.$c['assigned_by_last_name']); ?></td>
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
<?php endif; ?>
