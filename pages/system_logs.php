<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || !$auth->hasPermission('manage_system')) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$db = getDB();

// Choose logs table dynamically (prefer activity_logs)
try {
    $hasActivity = $db->query("SHOW TABLES LIKE 'activity_logs'")->rowCount() > 0;
} catch (Exception $e) { $hasActivity = false; }
try {
    $hasSystem = $db->query("SHOW TABLES LIKE 'system_logs'")->rowCount() > 0;
} catch (Exception $e) { $hasSystem = false; }

$logTable = $hasActivity ? 'activity_logs' : 'system_logs';

// Filters
$pageNum = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($pageNum - 1) * $perPage;

$userIdFilter = isset($_GET['user_id']) && $_GET['user_id'] !== '' ? (int)$_GET['user_id'] : null;
$actionFilter = trim((string)($_GET['action'] ?? ''));
$tableFilter = trim((string)($_GET['table'] ?? ''));
$dateFrom = trim((string)($_GET['from'] ?? ''));
$dateTo = trim((string)($_GET['to'] ?? ''));

// Build WHERE clause
$where = [];
$params = [];
if ($userIdFilter) { $where[] = 'sl.user_id = ?'; $params[] = $userIdFilter; }
if ($actionFilter !== '') { $where[] = 'sl.action LIKE ?'; $params[] = "%" . $actionFilter . "%"; }
if ($tableFilter !== '') { $where[] = 'sl.table_name LIKE ?'; $params[] = "%" . $tableFilter . "%"; }
if ($dateFrom !== '') { $where[] = 'sl.created_at >= ?'; $params[] = $dateFrom . ' 00:00:00'; }
if ($dateTo !== '') { $where[] = 'sl.created_at <= ?'; $params[] = $dateTo . ' 23:59:59'; }

$whereSql = empty($where) ? '' : ('WHERE ' . implode(' AND ', $where));

// Count total
$countSql = "SELECT COUNT(*) as cnt FROM $logTable sl $whereSql";
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$totalRows = (int)($stmt->fetch()['cnt'] ?? 0);
$totalPages = max(1, (int)ceil($totalRows / $perPage));
if ($pageNum > $totalPages) { $pageNum = $totalPages; $offset = ($pageNum - 1) * $perPage; }

// Fetch logs page
$sql = "
SELECT sl.*, u.username, u.first_name, u.last_name
FROM $logTable sl
LEFT JOIN users u ON u.id = sl.user_id
$whereSql
ORDER BY sl.created_at DESC
LIMIT $perPage OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Fetch users for filter dropdown
$usersStmt = $db->prepare("SELECT id, username, first_name, last_name FROM users WHERE status = 'active' ORDER BY last_name, first_name");
$usersStmt->execute();
$users = $usersStmt->fetchAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">System Logs</h1>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">System Activity Logs</h5>
            </div>
            <div class="card-body">
                <form class="mb-3" method="get">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="user_id">User</label>
                            <select class="form-control" id="user_id" name="user_id">
                                <option value="">All users</option>
                                <?php foreach ($users as $u): ?>
                                    <?php $uid = (int)$u['id']; ?>
                                    <option value="<?php echo $uid; ?>" <?php echo ($userIdFilter === $uid) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(($u['last_name'] ?? '') . ', ' . ($u['first_name'] ?? '') . ' (' . ($u['username'] ?? '') . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="action">Action</label>
                            <input type="text" class="form-control" id="action" name="action" value="<?php echo htmlspecialchars($actionFilter); ?>" placeholder="e.g. update_user">
                        </div>
                        <div class="form-group col-md-2">
                            <label for="table">Table</label>
                            <input type="text" class="form-control" id="table" name="table" value="<?php echo htmlspecialchars($tableFilter); ?>" placeholder="e.g. users">
                        </div>
                        <div class="form-group col-md-2">
                            <label for="from">From</label>
                            <input type="date" class="form-control" id="from" name="from" value="<?php echo htmlspecialchars($dateFrom); ?>">
                        </div>
                        <div class="form-group col-md-2">
                            <label for="to">To</label>
                            <input type="date" class="form-control" id="to" name="to" value="<?php echo htmlspecialchars($dateTo); ?>">
                        </div>
                        <div class="form-group col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-block">Filter</button>
                        </div>
                    </div>
                </form>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="text-muted small">
                        Showing <?php echo $totalRows ? ($offset + 1) : 0; ?>–<?php echo min($offset + $perPage, $totalRows); ?> of <?php echo $totalRows; ?>
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php $baseQuery = $_GET; unset($baseQuery['page']); $base = '?' . http_build_query($baseQuery); ?>
                            <li class="page-item <?php echo ($pageNum <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo $base . ($pageNum > 2 ? '&page=' . ($pageNum - 1) : '&page=1'); ?>">Prev</a>
                            </li>
                            <?php for ($p = max(1, $pageNum - 2); $p <= min($totalPages, $pageNum + 2); $p++): ?>
                                <li class="page-item <?php echo ($p === $pageNum) ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo $base . '&page=' . $p; ?>"><?php echo $p; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($pageNum >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo $base . '&page=' . min($totalPages, $pageNum + 1); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Table</th>
                                <th>Record</th>
                                <th>IP</th>
                                <th>User Agent</th>
                                <th>Changes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($logs)): ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td class="text-nowrap"><?php echo htmlspecialchars($log['created_at'] ?? ''); ?></td>
                                        <td>
                                            <?php
                                                $name = trim((($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? '')));
                                                echo htmlspecialchars($name !== '' ? $name : ($log['username'] ?? 'User #' . ($log['user_id'] ?? '')));
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['action'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($log['table_name'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars((string)($log['record_id'] ?? '')); ?></td>
                                        <td class="text-monospace small"><?php echo htmlspecialchars($log['ip_address'] ?? ''); ?></td>
                                        <td class="small" style="max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($log['user_agent'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($log['user_agent'] ?? ''); ?>
                                        </td>
                                        <td style="min-width:220px;">
                                            <?php
                                                $old = $log['old_values'] ? json_decode($log['old_values'], true) : null;
                                                $new = $log['new_values'] ? json_decode($log['new_values'], true) : null;
                                                if ($old || $new) {
                                                    // Show compact diff-like view
                                                    $keys = array_unique(array_merge(array_keys((array)$old), array_keys((array)$new)));
                                                    echo '<div class="small">';
                                                    $shown = 0;
                                                    foreach ($keys as $k) {
                                                        if ($shown >= 4) { echo '<div class="text-muted">…</div>'; break; }
                                                        $ov = $old[$k] ?? null; $nv = $new[$k] ?? null;
                                                        if ($ov !== $nv) {
                                                            echo '<div><span class="text-muted">' . htmlspecialchars((string)$k) . ':</span> ';
                                                            echo '<span class="text-danger">' . htmlspecialchars(is_scalar($ov) ? (string)$ov : json_encode($ov)) . '</span> → ';
                                                            echo '<span class="text-success">' . htmlspecialchars(is_scalar($nv) ? (string)$nv : json_encode($nv)) . '</span></div>';
                                                            $shown++;
                                                        }
                                                    }
                                                    echo '</div>';
                                                } else {
                                                    echo '<span class="text-muted">—</span>';
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fe fe-activity fe-48 mb-3"></i>
                                        <p>No system logs found.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end">
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php $baseQuery = $_GET; unset($baseQuery['page']); $base = '?' . http_build_query($baseQuery); ?>
                            <li class="page-item <?php echo ($pageNum <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo $base . ($pageNum > 2 ? '&page=' . ($pageNum - 1) : '&page=1'); ?>">Prev</a>
                            </li>
                            <?php for ($p = max(1, $pageNum - 2); $p <= min($totalPages, $pageNum + 2); $p++): ?>
                                <li class="page-item <?php echo ($p === $pageNum) ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo $base . '&page=' . $p; ?>"><?php echo $p; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($pageNum >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo $base . '&page=' . min($totalPages, $pageNum + 1); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>


