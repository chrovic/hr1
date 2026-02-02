<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/learning.php';
require_once 'includes/functions/redirect_helper.php';
require_once 'includes/functions/learning_materials.php';

$auth = new SimpleAuth();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$learningManager = new LearningManager();
$learningMaterials = new LearningMaterials();

$message = '';
$error = '';

// Get employee's approved learning material requests
try {
    $db = getDB();
    // Ensure learning material tables exist before querying
    $db->exec("
        CREATE TABLE IF NOT EXISTS learning_materials (
            id INT AUTO_INCREMENT PRIMARY KEY,
            request_id INT NOT NULL,
            material_type ENUM('file','link','text') NOT NULL DEFAULT 'file',
            file_path VARCHAR(255) DEFAULT NULL,
            link_url VARCHAR(500) DEFAULT NULL,
            notes TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (request_id) REFERENCES employee_requests(id) ON DELETE CASCADE
        )
    ");
    $db->exec("
        CREATE TABLE IF NOT EXISTS learning_material_progress (
            id INT AUTO_INCREMENT PRIMARY KEY,
            request_id INT NOT NULL,
            employee_id INT NOT NULL,
            status ENUM('not_started','completed') DEFAULT 'not_started',
            completed_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (request_id) REFERENCES employee_requests(id) ON DELETE CASCADE,
            FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    $db->exec("
        CREATE TABLE IF NOT EXISTS learning_material_certificates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            request_id INT NOT NULL,
            employee_id INT NOT NULL,
            certificate_path VARCHAR(255) NOT NULL,
            issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (request_id) REFERENCES employee_requests(id) ON DELETE CASCADE,
            FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    $stmt = $db->prepare("
        SELECT er.*, 
               approver.first_name as approver_first_name, 
               approver.last_name as approver_last_name,
               lm.material_type, lm.file_path, lm.link_url, lm.notes,
               lmp.status as progress_status, lmp.completed_at,
               lmc.certificate_path
        FROM employee_requests er
        LEFT JOIN users approver ON er.approved_by = approver.id
        LEFT JOIN learning_materials lm ON er.id = lm.request_id
        LEFT JOIN learning_material_progress lmp ON er.id = lmp.request_id AND lmp.employee_id = ?
        LEFT JOIN learning_material_certificates lmc ON er.id = lmc.request_id AND lmc.employee_id = ?
        WHERE er.employee_id = ? 
        AND er.request_type = 'other' 
        AND er.status = 'approved'
        ORDER BY er.approved_at DESC
    ");
    $stmt->execute([$current_user['id'], $current_user['id'], $current_user['id']]);
    $approved_requests = $stmt->fetchAll();
    
    // Get available learning paths for reference
    $learningPaths = $learningManager->getAllLearningPaths();
    
} catch (Exception $e) {
    $error = 'Error loading approved materials: ' . $e->getMessage();
    $approved_requests = [];
    $learningPaths = [];
}
?>

<!-- Employee Learning Access Page Content -->
<div class="content">
    <div class="page-header">
        <div class="add-list">
            <h4 class="page-title">My Learning Materials</h4>
            <p class="text-muted">Access your approved learning materials and resources</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fe fe-check-circle mr-2"></i><?php echo htmlspecialchars($message); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fe fe-alert-triangle mr-2"></i><?php echo htmlspecialchars($error); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-fill">
                            <h6 class="text-muted mb-1">Approved Materials</h6>
                            <h3 class="mb-0 text-success"><?php echo count($approved_requests); ?></h3>
                        </div>
                        <div class="flex-fill text-right">
                            <i class="fe fe-book-open fe-24 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-fill">
                            <h6 class="text-muted mb-1">Learning Paths</h6>
                            <h3 class="mb-0 text-info"><?php echo count($learningPaths); ?></h3>
                        </div>
                        <div class="flex-fill text-right">
                            <i class="fe fe-map fe-24 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-fill">
                            <h6 class="text-muted mb-1">Available Resources</h6>
                            <h3 class="mb-0 text-primary"><?php echo count($approved_requests) + count($learningPaths); ?></h3>
                        </div>
                        <div class="flex-fill text-right">
                            <i class="fe fe-download fe-24 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approved Learning Materials -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">My Approved Learning Materials</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($approved_requests)): ?>
                        <div class="row">
                            <?php foreach ($approved_requests as $request): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-success">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h6 class="card-title text-success"><?php echo htmlspecialchars($request['title']); ?></h6>
                                                <span class="badge badge-success">Approved</span>
                                            </div>
                                            <p class="card-text text-muted small"><?php echo htmlspecialchars($request['description']); ?></p>
                                            <div class="mb-3">
                                                <small class="text-muted">
                                                    <strong>Approved by:</strong> <?php echo htmlspecialchars($request['approver_first_name'] . ' ' . $request['approver_last_name']); ?><br>
                                                    <strong>Date:</strong> <?php echo date('M d, Y', strtotime($request['approved_at'])); ?>
                                                </small>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="button"
                                                        class="btn btn-success btn-sm"
                                                        onclick="accessMaterial(this)"
                                                        data-request-id="<?php echo (int)$request['id']; ?>"
                                                        data-title="<?php echo htmlspecialchars($request['title']); ?>"
                                                        data-description="<?php echo htmlspecialchars($request['description']); ?>"
                                                        data-approved-by="<?php echo htmlspecialchars(trim(($request['approver_first_name'] ?? '') . ' ' . ($request['approver_last_name'] ?? ''))); ?>"
                                                        data-approved-at="<?php echo htmlspecialchars($request['approved_at'] ?? ''); ?>"
                                                        data-material-type="<?php echo htmlspecialchars($request['material_type'] ?? ''); ?>"
                                                        data-file-path="<?php echo htmlspecialchars($request['file_path'] ?? ''); ?>"
                                                        data-link-url="<?php echo htmlspecialchars($request['link_url'] ?? ''); ?>"
                                                        data-notes="<?php echo htmlspecialchars($request['notes'] ?? ''); ?>"
                                                        data-progress-status="<?php echo htmlspecialchars($request['progress_status'] ?? ''); ?>"
                                                        data-certificate-path="<?php echo htmlspecialchars($request['certificate_path'] ?? ''); ?>">
                                                    <i class="fe fe-external-link"></i> Access Material
                                                </button>
                                                <button type="button"
                                                        class="btn btn-outline-info btn-sm"
                                                        onclick="viewDetails(this)"
                                                        data-title="<?php echo htmlspecialchars($request['title']); ?>"
                                                        data-description="<?php echo htmlspecialchars($request['description']); ?>"
                                                        data-approved-by="<?php echo htmlspecialchars(trim(($request['approver_first_name'] ?? '') . ' ' . ($request['approver_last_name'] ?? ''))); ?>"
                                                        data-approved-at="<?php echo htmlspecialchars($request['approved_at'] ?? ''); ?>"
                                                        data-progress-status="<?php echo htmlspecialchars($request['progress_status'] ?? ''); ?>"
                                                        data-notes="<?php echo htmlspecialchars($request['notes'] ?? ''); ?>"
                                                        data-certificate-path="<?php echo htmlspecialchars($request['certificate_path'] ?? ''); ?>">
                                                    <i class="fe fe-eye"></i> Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fe fe-book-open fe-48 text-muted"></i>
                            <p class="text-muted mt-3">No approved learning materials yet.</p>
                            <p class="text-muted">Request learning materials from the <a href="?page=employee_learning_materials">Learning Materials</a> page.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Learning Paths -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Available Learning Paths</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($learningPaths)): ?>
                        <div class="row">
                            <?php 
                            $displayedPaths = [];
                            foreach ($learningPaths as $path): 
                                if (!in_array($path['id'], $displayedPaths)):
                                    $displayedPaths[] = $path['id'];
                            ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border">
                                        <div class="card-body">
                                            <h6 class="card-title"><?php echo htmlspecialchars($path['name']); ?></h6>
                                            <p class="card-text text-muted small"><?php echo htmlspecialchars($path['description']); ?></p>
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="badge badge-info"><?php echo htmlspecialchars($path['target_role']); ?></span>
                                                <span class="badge badge-secondary"><?php echo $path['estimated_duration_days']; ?> days</span>
                                            </div>
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="accessLearningPath(<?php echo $path['id']; ?>, '<?php echo htmlspecialchars($path['name']); ?>')">
                                                <i class="fe fe-play"></i> Start Learning Path
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php 
                                endif;
                            endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fe fe-map fe-48 text-muted"></i>
                            <p class="text-muted mt-3">No learning paths available at the moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Material Access Modal -->
<div class="modal fade" id="accessModal" tabindex="-1" role="dialog" aria-labelledby="accessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="accessModalLabel">Access Learning Material</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center py-4">
                    <i class="fe fe-download fe-48 text-primary mb-3"></i>
                    <h5 id="material_title">Loading...</h5>
                    <p class="text-muted">Your approved learning material is ready for access.</p>
                    
                    <div class="alert alert-info">
                        <i class="fe fe-info mr-2"></i>
                        <strong>Access Instructions:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Click the download button below to access your material</li>
                            <li>Save the file to your preferred location</li>
                            <li>Contact your supervisor if you need additional support</li>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <button type="button" class="btn btn-primary btn-lg" id="downloadBtn" onclick="downloadMaterial()">
                            <i class="fe fe-download mr-2"></i>Download Material
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-lg ml-2" id="completeBtn" onclick="markAsCompleted()">
                            <i class="fe fe-check mr-2"></i>Mark as Completed
                        </button>
                        <button type="button" class="btn btn-outline-success btn-lg ml-2" id="certificateBtn" onclick="downloadCertificate()" style="display: none;">
                            <i class="fe fe-award mr-2"></i>Download Certificate
                        </button>
                    </div>
                    <div class="small text-muted mt-3" id="materialStatus"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Material Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Material Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="text-muted small">Title</div>
                        <div id="detailsTitle"></div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="text-muted small">Approved By</div>
                        <div id="detailsApprovedBy"></div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="text-muted small">Approved At</div>
                        <div id="detailsApprovedAt"></div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="text-muted small">Status</div>
                        <div id="detailsStatus"></div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="text-muted small">Certificate</div>
                        <div id="detailsCertificate"></div>
                    </div>
                    <div class="col-12 mb-2">
                        <div class="text-muted small">Description</div>
                        <div id="detailsDescription"></div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Notes</div>
                        <div id="detailsNotes"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentRequest = null;

function accessMaterial(button) {
    if (!button) return;
    const data = button.dataset;
    currentRequest = {
        requestId: data.requestId,
        title: data.title,
        materialType: data.materialType,
        filePath: data.filePath,
        linkUrl: data.linkUrl,
        progressStatus: data.progressStatus,
        certificatePath: data.certificatePath
    };

    document.getElementById('material_title').textContent = data.title || 'Learning Material';
    const statusText = data.progressStatus === 'completed' ? 'Completed' : 'Not completed';
    let statusLine = 'Status: ' + statusText;
    if (!data.filePath && !data.linkUrl) {
        statusLine += ' | Material not attached yet';
    }
    document.getElementById('materialStatus').textContent = statusLine;

    const downloadBtn = document.getElementById('downloadBtn');
    const certificateBtn = document.getElementById('certificateBtn');
    if (!data.filePath && !data.linkUrl) {
        downloadBtn.disabled = true;
    } else {
        downloadBtn.disabled = false;
    }
    if (data.certificatePath) {
        certificateBtn.style.display = 'inline-block';
    } else {
        certificateBtn.style.display = 'none';
    }

    $('#accessModal').modal('show');
}

function accessLearningPath(pathId, title) {
    alert('Learning Path: ' + title + '\n\nThis would open the learning path content. In a real implementation, this would redirect to the learning management system or open the course content.');
}

function downloadMaterial() {
    if (!currentRequest || !currentRequest.requestId) {
        return;
    }
    window.location.href = 'ajax/learning_material_download.php?request_id=' + encodeURIComponent(currentRequest.requestId);
}

function markAsCompleted() {
    if (!currentRequest || !currentRequest.requestId) {
        return;
    }
    const formData = new FormData();
    formData.append('request_id', currentRequest.requestId);

    fetch('ajax/learning_material_complete.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data && data.success) {
            document.getElementById('materialStatus').textContent = 'Status: Completed';
            const certificateBtn = document.getElementById('certificateBtn');
            if (data.certificate) {
                currentRequest.certificatePath = data.certificate;
                certificateBtn.style.display = 'inline-block';
            }
        } else {
            alert(data.message || 'Unable to mark as completed.');
        }
    })
    .catch(() => {
        alert('Unable to mark as completed.');
    });
}

function downloadCertificate() {
    if (!currentRequest || !currentRequest.requestId) {
        return;
    }
    window.location.href = 'ajax/learning_material_certificate.php?request_id=' + encodeURIComponent(currentRequest.requestId);
}

function viewDetails(button) {
    if (!button) return;
    const data = button.dataset;
    document.getElementById('detailsTitle').textContent = data.title || 'Learning Material';
    document.getElementById('detailsApprovedBy').textContent = data.approvedBy || 'TBD';
    document.getElementById('detailsApprovedAt').textContent = data.approvedAt ? new Date(data.approvedAt).toLocaleString() : 'TBD';
    document.getElementById('detailsStatus').textContent = data.progressStatus === 'completed' ? 'Completed' : 'Not completed';
    document.getElementById('detailsDescription').textContent = data.description || 'N/A';
    document.getElementById('detailsNotes').textContent = data.notes || 'N/A';
    document.getElementById('detailsCertificate').textContent = data.certificatePath ? 'Available' : 'Not available';
    $('#detailsModal').modal('show');
}
</script>
