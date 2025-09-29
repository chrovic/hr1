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

$roleId = isset($_GET['role_id']) ? (int)$_GET['role_id'] : 0;
$manager = new SuccessionPlanning();
$candidates = $roleId ? $manager->getRoleCandidates($roleId) : [];

if (empty($candidates)) {
	echo '<div class="p-3 text-muted">No candidates assigned to this role.</div>';
	exit;
}
?>
<div class="table-responsive">
	<table class="table table-striped table-hover mb-0">
		<thead>
			<tr>
				<th><i class="fe fe-user mr-1"></i>Employee</th>
				<th><i class="fe fe-briefcase mr-1"></i>Position</th>
				<th><i class="fe fe-activity mr-1"></i>Readiness</th>
				<th><i class="fe fe-shield mr-1"></i>Assigned By</th>
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
