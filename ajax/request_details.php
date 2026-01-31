<?php
require_once '../includes/data/db.php';
require_once '../includes/functions/simple_auth.php';
require_once '../includes/functions/request_manager.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || (!$auth->isHRManager() && !$auth->isAdmin())) {
    http_response_code(403);
    echo '<div class="alert alert-danger">Unauthorized.</div>';
    exit;
}

$requestId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$requestId) {
    echo '<div class="alert alert-warning">Invalid request.</div>';
    exit;
}

$requestManager = new RequestManager();
$request = $requestManager->getRequestDetails($requestId);

if (!$request) {
    echo '<div class="alert alert-warning">Request not found.</div>';
    exit;
}
?>

<div class="mb-3">
    <div class="text-muted small">Employee</div>
    <div class="font-weight-bold"><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></div>
    <div class="text-muted small"><?php echo htmlspecialchars($request['department'] ?? 'N/A'); ?> · <?php echo htmlspecialchars($request['position'] ?? 'N/A'); ?></div>
</div>

<div class="mb-3">
    <div class="text-muted small">Request Type</div>
    <div><?php echo htmlspecialchars($request['request_type_name'] ?? 'N/A'); ?></div>
</div>

<div class="mb-3">
    <div class="text-muted small">Title</div>
    <div class="font-weight-bold"><?php echo htmlspecialchars($request['title']); ?></div>
</div>

<div class="mb-3">
    <div class="text-muted small">Description</div>
    <div><?php echo nl2br(htmlspecialchars($request['description'] ?? '')); ?></div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="text-muted small">Status</div>
        <div><?php echo ucfirst($request['status']); ?></div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="text-muted small">Requested Date</div>
        <div>
            <?php
            $requestedDate = $request['requested_date'] ?? $request['request_date'] ?? null;
            echo $requestedDate ? date('M d, Y', strtotime($requestedDate)) : 'N/A';
            ?>
        </div>
    </div>
</div>

<?php if (!empty($request['comments'])): ?>
    <hr>
    <h6>Comments</h6>
    <div class="list-group">
        <?php foreach ($request['comments'] as $comment): ?>
            <div class="list-group-item">
                <strong><?php echo htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']); ?></strong>
                <div class="text-muted small"><?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?></div>
                <div><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($request['approvals'])): ?>
    <hr>
    <h6>Approvals</h6>
    <div class="list-group">
        <?php foreach ($request['approvals'] as $approval): ?>
            <div class="list-group-item">
                <strong><?php echo htmlspecialchars($approval['first_name'] . ' ' . $approval['last_name']); ?></strong>
                <div class="text-muted small"><?php echo ucfirst($approval['status']); ?></div>
                <?php if (!empty($approval['comments'])): ?>
                    <div><?php echo nl2br(htmlspecialchars($approval['comments'])); ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
