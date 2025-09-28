<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/competency.php';

// Handle AJAX requests FIRST - before any authentication checks
if (isset($_GET['action']) && $_GET['action'] === 'get_cycle_details') {
    // Suppress error reporting for clean JSON output
    error_reporting(0);
    ini_set('display_errors', 0);
    
    // Start output buffering to catch any warnings/notices
    ob_start();
    
    $cycleId = $_GET['id'] ?? 0;
    
    try {
        $auth = new SimpleAuth();
        $competencyManager = new CompetencyManager();
        $db = getDB();
        
        // Get cycle details
        $stmt = $db->prepare("
            SELECT ec.*, u.first_name, u.last_name
            FROM evaluation_cycles ec
            LEFT JOIN users u ON ec.created_by = u.id
            WHERE ec.id = ?
        ");
        $stmt->execute([$cycleId]);
        $cycle = $stmt->fetch();
        
        if ($cycle) {
            // Get evaluations for this cycle
            $stmt = $db->prepare("
                SELECT e.*, u1.first_name as employee_first_name, u1.last_name as employee_last_name,
                       u2.first_name as evaluator_first_name, u2.last_name as evaluator_last_name,
                       cm.name as model_name
                FROM evaluations e
                LEFT JOIN users u1 ON e.employee_id = u1.id
                LEFT JOIN users u2 ON e.evaluator_id = u2.id
                LEFT JOIN competency_models cm ON e.model_id = cm.id
                WHERE e.cycle_id = ?
                ORDER BY e.created_at DESC
            ");
            $stmt->execute([$cycleId]);
            $evaluations = $stmt->fetchAll();
            
            // Clean output buffer and set headers
            ob_clean();
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
            
            echo json_encode([
                'success' => true,
                'cycle' => $cycle,
                'evaluations' => $evaluations
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            ob_clean();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Cycle not found'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    } catch (PDOException $e) {
        error_log("Database error in get_cycle_details: " . $e->getMessage());
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Database error occurred'], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        error_log("General error in get_cycle_details: " . $e->getMessage());
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'An error occurred while loading cycle details'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Regular authentication check for non-AJAX requests
$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || !$auth->hasPermission('manage_evaluations')) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$competencyManager = new CompetencyManager();

$message = '';
$error = '';

// Form processing is now handled in index.php before any output

// Handle success messages from redirects
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'cycle_created':
            $message = 'Evaluation cycle created successfully!';
            break;
        case 'cycle_updated':
            $message = 'Evaluation cycle updated successfully!';
            break;
        case 'cycle_deleted':
            $message = 'Evaluation cycle deleted successfully!';
            break;
    }
}

$cycles = $competencyManager->getAllCycles();
?>

<link rel="stylesheet" href="assets/css/competency.css">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Evaluation Cycles</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createCycleModal">
            <i class="fe fe-plus fe-16 mr-2"></i>Create Cycle
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

<!-- Evaluation Cycles -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Evaluation Cycles</h5>
            </div>
            <div class="card-body">
                <?php if (empty($cycles)): ?>
                    <div class="text-center py-4">
                        <i class="fe fe-calendar fe-48 text-muted"></i>
                        <h4 class="text-muted mt-3">No Evaluation Cycles</h4>
                        <p class="text-muted">Create your first evaluation cycle to start competency assessments.</p>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createCycleModal">
                            Create Cycle
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Cycle Name</th>
                                    <th>Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <th>Evaluations</th>
                                    <th>Completed</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cycles as $cycle): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($cycle['name']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?php echo ucfirst($cycle['type']); ?></span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($cycle['start_date'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($cycle['end_date'])); ?></td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            switch($cycle['status']) {
                                                case 'draft': $statusClass = 'badge-secondary'; break;
                                                case 'active': $statusClass = 'badge-success'; break;
                                                case 'completed': $statusClass = 'badge-primary'; break;
                                                case 'cancelled': $statusClass = 'badge-danger'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($cycle['status']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary"><?php echo $cycle['evaluation_count']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge badge-success"><?php echo $cycle['completed_count']; ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($cycle['first_name'] . ' ' . $cycle['last_name']); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewCycle(<?php echo $cycle['id']; ?>)">
                                                    <i class="fe fe-eye fe-14"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success" onclick="assignEvaluations(<?php echo $cycle['id']; ?>)">
                                                    <i class="fe fe-users fe-14"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning" onclick="editCycle(<?php echo htmlspecialchars(json_encode($cycle)); ?>)">
                                                    <i class="fe fe-edit fe-14"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCycle(<?php echo $cycle['id']; ?>)">
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

<!-- Create Cycle Modal -->
<div class="modal fade" id="createCycleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Evaluation Cycle</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Cycle Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type">Cycle Type *</label>
                                <select class="form-control" id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="annual">Annual</option>
                                    <option value="project_based">Project Based</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Start Date *</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">End Date *</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_cycle" class="btn btn-primary">Create Cycle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Cycle Modal -->
<div class="modal fade" id="editCycleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Evaluation Cycle</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="edit_cycle_id" name="cycle_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_name">Cycle Name *</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_type">Cycle Type *</label>
                                <select class="form-control" id="edit_type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="annual">Annual</option>
                                    <option value="project_based">Project Based</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_start_date">Start Date *</label>
                                <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_end_date">End Date *</label>
                                <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_cycle" class="btn btn-primary">Update Cycle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Cycle Modal -->
<div class="modal fade" id="deleteCycleModal" tabindex="-1" role="dialog">
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
                    <input type="hidden" id="delete_cycle_id" name="cycle_id">
                    <p>Are you sure you want to delete this evaluation cycle? This action cannot be undone.</p>
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This will also delete all associated evaluations and scores.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_cycle" class="btn btn-danger">Delete Cycle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Cycle Modal -->
<div class="modal fade" id="viewCycleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fe fe-calendar me-2"></i>Evaluation Cycle Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <!-- Cycle Header -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h4 class="text-primary mb-2" id="viewCycleName">-</h4>
                        <p class="text-muted mb-0" id="viewCycleDescription">-</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge badge-success" id="viewCycleStatus">-</span>
                    </div>
                </div>

                <!-- Cycle Information Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-left-primary">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted mb-1">Type</h6>
                                        <h5 class="mb-0" id="viewCycleType">-</h5>
                                    </div>
                                    <div class="text-primary">
                                        <i class="fe fe-tag fe-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-left-info">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted mb-1">Start Date</h6>
                                        <h5 class="mb-0" id="viewCycleStartDate">-</h5>
                                    </div>
                                    <div class="text-info">
                                        <i class="fe fe-calendar fe-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-left-warning">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted mb-1">End Date</h6>
                                        <h5 class="mb-0" id="viewCycleEndDate">-</h5>
                                    </div>
                                    <div class="text-warning">
                                        <i class="fe fe-calendar fe-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-left-success">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted mb-1">Created By</h6>
                                        <h5 class="mb-0" id="viewCycleCreatedBy">-</h5>
                                    </div>
                                    <div class="text-success">
                                        <i class="fe fe-user fe-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Evaluations Section -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fe fe-clipboard me-2"></i>Evaluations in this Cycle
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="border-0">Employee</th>
                                                <th class="border-0">Evaluator</th>
                                                <th class="border-0">Model</th>
                                                <th class="border-0 text-center">Status</th>
                                                <th class="border-0 text-center">Score</th>
                                                <th class="border-0">Created</th>
                                            </tr>
                                        </thead>
                                        <tbody id="viewCycleEvaluations">
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    <i class="fe fe-info fe-24 mb-2"></i><br>
                                                    No evaluations found in this cycle.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fe fe-x me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 4px solid #007bff !important;
}
.border-left-info {
    border-left: 4px solid #17a2b8 !important;
}
.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}
.border-left-success {
    border-left: 4px solid #28a745 !important;
}
.me-2 {
    margin-right: 0.5rem !important;
}
.mb-2 {
    margin-bottom: 0.5rem !important;
}
.py-4 {
    padding-top: 1.5rem !important;
    padding-bottom: 1.5rem !important;
}
</style>

<script>
function viewCycle(cycleId) {
    console.log('viewCycle called with ID:', cycleId);
    
    // Store original modal content
    const modal = document.getElementById('viewCycleModal');
    const originalContent = modal ? modal.querySelector('.modal-body').innerHTML : null;
    
    // Show loading state
    if (modal) {
        modal.querySelector('.modal-body').innerHTML = '<div class="text-center"><i class="fe fe-loader fe-spin fe-24"></i><br>Loading cycle details...</div>';
        $('#viewCycleModal').modal('show');
    }
    
    // Use the dedicated AJAX endpoint
        const url = `?page=evaluation_cycles&action=get_cycle_details&id=${cycleId}`;
    
    console.log('Fetching URL:', url);
    
    // Fetch cycle details and evaluations via AJAX
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data);
            
            if (data.success) {
                const cycle = data.cycle;
                const evaluations = data.evaluations;
                
                // Restore original modal content first
                if (modal && originalContent) {
                    modal.querySelector('.modal-body').innerHTML = originalContent;
                }
                
                // Wait for modal content to be restored
                setTimeout(() => {
                    // Safely populate the view modal with null checks
                    const elements = {
                        name: document.getElementById('viewCycleName'),
                        description: document.getElementById('viewCycleDescription'),
                        status: document.getElementById('viewCycleStatus'),
                        type: document.getElementById('viewCycleType'),
                        startDate: document.getElementById('viewCycleStartDate'),
                        endDate: document.getElementById('viewCycleEndDate'),
                        createdBy: document.getElementById('viewCycleCreatedBy'),
                        evaluationsTable: document.getElementById('viewCycleEvaluations')
                    };
                    
                    // Check if all required elements exist
                    const missingElements = Object.entries(elements).filter(([key, element]) => !element);
                    if (missingElements.length > 0) {
                        console.error('Missing elements:', missingElements.map(([key]) => key));
                        alert('Error: Some modal elements are missing. Please refresh the page and try again.');
                        return;
                    }
                    
                    // Populate the modal
                    elements.name.textContent = cycle.name || 'N/A';
                    elements.description.textContent = cycle.description || 'No description provided.';
                    elements.type.textContent = cycle.type ? cycle.type.charAt(0).toUpperCase() + cycle.type.slice(1) : 'N/A';
                    elements.startDate.textContent = cycle.start_date ? new Date(cycle.start_date).toLocaleDateString() : 'N/A';
                    elements.endDate.textContent = cycle.end_date ? new Date(cycle.end_date).toLocaleDateString() : 'N/A';
                    elements.createdBy.textContent = (cycle.first_name || 'Unknown') + ' ' + (cycle.last_name || 'User');
                    
                    // Set status badge
                    const statusClass = {
                        'draft': 'badge-secondary',
                        'active': 'badge-success',
                        'completed': 'badge-primary',
                        'cancelled': 'badge-danger'
                    }[cycle.status] || 'badge-secondary';
                    elements.status.className = `badge ${statusClass}`;
                    elements.status.textContent = cycle.status ? cycle.status.charAt(0).toUpperCase() + cycle.status.slice(1) : 'N/A';
                    
                    // Populate evaluations table
                    if (evaluations && evaluations.length > 0) {
                        elements.evaluationsTable.innerHTML = evaluations.map(eval => {
                            const statusClass = {
                                'pending': 'badge-warning',
                                'in_progress': 'badge-info',
                                'completed': 'badge-success',
                                'cancelled': 'badge-danger'
                            }[eval.status] || 'badge-secondary';
                            
                            return `
                                <tr>
                                    <td><strong>${eval.employee_first_name || 'Unknown'} ${eval.employee_last_name || 'User'}</strong></td>
                                    <td>${eval.evaluator_first_name || 'Unknown'} ${eval.evaluator_last_name || 'User'}</td>
                                    <td>${eval.model_name || 'N/A'}</td>
                                    <td class="text-center"><span class="badge ${statusClass}">${eval.status ? eval.status.charAt(0).toUpperCase() + eval.status.slice(1) : 'N/A'}</span></td>
                                    <td class="text-center">${eval.overall_score ? eval.overall_score : '-'}</td>
                                    <td>${eval.created_at ? new Date(eval.created_at).toLocaleDateString() : 'N/A'}</td>
                                </tr>
                            `;
                        }).join('');
                    } else {
                        elements.evaluationsTable.innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fe fe-info fe-24 mb-2"></i><br>
                                    No evaluations found in this cycle.
                                </td>
                            </tr>
                        `;
                    }
                    
                    console.log('Modal populated successfully');
                }, 200); // Increased delay to ensure content is restored
                
            } else {
                console.error('API returned error:', data.message);
                alert('Failed to load cycle details: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Failed to load cycle details. Please try again. Error: ' + error.message);
        });
}

function assignEvaluations(cycleId) {
    // Redirect to evaluations page with cycle filter
    window.location.href = '?page=evaluations&cycle_id=' + cycleId;
}

function editCycle(cycle) {
    document.getElementById('edit_cycle_id').value = cycle.id;
    document.getElementById('edit_name').value = cycle.name;
    document.getElementById('edit_type').value = cycle.type;
    document.getElementById('edit_start_date').value = cycle.start_date;
    document.getElementById('edit_end_date').value = cycle.end_date;
    
    $('#editCycleModal').modal('show');
}

function deleteCycle(cycleId) {
    document.getElementById('delete_cycle_id').value = cycleId;
    $('#deleteCycleModal').modal('show');
}

// Set default dates
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    const nextMonth = new Date(today.getFullYear(), today.getMonth() + 1, today.getDate());
    const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 2, 0);
    
    document.getElementById('start_date').value = today.toISOString().split('T')[0];
    document.getElementById('end_date').value = endOfMonth.toISOString().split('T')[0];
});
</script>
