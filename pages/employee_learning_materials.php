<?php
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/learning.php';

// Initialize authentication
$auth = new SimpleAuth();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

// Get current user
$current_user = $auth->getCurrentUser();
$learningManager = new LearningManager();

$message = '';
$error = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['request_learning_material'])) {
        // Validate required fields
        if (empty($_POST['material_type']) || empty($_POST['title']) || empty($_POST['description'])) {
            $error = 'Please fill in all required fields.';
        } else {
            $requestData = [
                'employee_id' => $current_user['id'],
                'request_type' => 'other',
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'material_type' => $_POST['material_type'],
                'priority' => $_POST['priority'] ?? 'medium',
                'request_date' => date('Y-m-d'),
                'learning_path_id' => $_POST['learning_path_id'] ?? null
            ];
            
            try {
                // Insert into employee_requests table
                $db = getDB();
                $stmt = $db->prepare("
                    INSERT INTO employee_requests (employee_id, request_type, title, description, request_date, status) 
                    VALUES (?, ?, ?, ?, ?, 'pending')
                ");
                
                if ($stmt->execute([
                    $requestData['employee_id'],
                    $requestData['request_type'],
                    $requestData['title'],
                    $requestData['description'],
                    $requestData['request_date']
                ])) {
                    $message = 'Learning material request submitted successfully!';
                    $auth->logActivity('request_learning_material', 'employee_requests', null, null, $requestData);
                } else {
                    $error = 'Failed to submit learning material request.';
                }
            } catch (Exception $e) {
                $error = 'Error submitting request: ' . $e->getMessage();
            }
        }
    }
}

// Get available learning paths
try {
    $learningPaths = $learningManager->getAllLearningPaths();
    // Remove duplicates based on ID and name
    $uniquePaths = [];
    $seenIds = [];
    $seenNames = [];
    foreach ($learningPaths as $path) {
        $pathId = $path['id'];
        $pathName = strtolower(trim($path['name']));
        
        if (!in_array($pathId, $seenIds) && !in_array($pathName, $seenNames)) {
            $uniquePaths[] = $path;
            $seenIds[] = $pathId;
            $seenNames[] = $pathName;
        }
    }
    $learningPaths = $uniquePaths;
    
    $employeeRequests = $learningManager->getEmployeeRequests($current_user['id']);
} catch (Exception $e) {
    $error = 'Error loading data: ' . $e->getMessage();
    $learningPaths = [];
    $employeeRequests = [];
}
?>

<!-- Learning Materials Request Page Content -->
            <div class="content">
                <div class="page-header">
                    <div class="add-list">
                        <h4 class="page-title">Learning Materials</h4>
                        <p class="text-muted">Browse and select from available learning paths</p>
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

                <!-- Search and Filter Section -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="searchInput">Search Learning Paths</label>
                            <input type="text" class="form-control" id="searchInput" placeholder="Search by name, description, or role...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="categoryFilter">Filter by Role</label>
                            <select class="form-control" id="categoryFilter">
                                <option value="">All Roles</option>
                                <option value="Customer Experience Manager">Customer Experience Manager</option>
                                <option value="Digital Marketing Manager">Digital Marketing Manager</option>
                                <option value="E-Commerce Analyst">E-Commerce Analyst</option>
                                <option value="Supply Chain Manager">Supply Chain Manager</option>
                                <option value="Data Analyst">Data Analyst</option>
                                <option value="Product Manager">Product Manager</option>
                                <option value="Sales Manager">Sales Manager</option>
                                <option value="Operations Manager">Operations Manager</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-outline-secondary btn-block" onclick="clearFilters()">
                                <i class="fe fe-refresh-cw"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Available Learning Paths</h5>
                                <div class="card-options">
                                    <span class="badge badge-info" id="resultCount">0 paths found</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($learningPaths)): ?>
                                    <div class="row">
                                        <?php 
                                        $displayedPaths = [];
                                        foreach ($learningPaths as $path): 
                                            $pathId = $path['id'];
                                            $pathName = strtolower(trim($path['name']));
                                            
                                            if (!in_array($pathId, $displayedPaths) && !in_array($pathName, $displayedPaths)):
                                                $displayedPaths[] = $pathId;
                                                $displayedPaths[] = $pathName;
                                        ?>
                                            <div class="col-md-6 col-lg-4 mb-4 learning-path-card" 
                                                 data-name="<?php echo htmlspecialchars(strtolower($path['name'])); ?>"
                                                 data-description="<?php echo htmlspecialchars(strtolower($path['description'])); ?>"
                                                 data-role="<?php echo htmlspecialchars($path['target_role']); ?>"
                                                 data-duration="<?php echo $path['estimated_duration_days']; ?>">
                                                <div class="card border">
                                                    <div class="card-body">
                                                        <h6 class="card-title"><?php echo htmlspecialchars($path['name']); ?></h6>
                                                        <p class="card-text text-muted small"><?php echo htmlspecialchars($path['description']); ?></p>
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <span class="badge badge-info"><?php echo htmlspecialchars($path['target_role']); ?></span>
                                                            <span class="badge badge-secondary"><?php echo $path['estimated_duration_days']; ?> days</span>
                                                        </div>
                                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectLearningPath(<?php echo $path['id']; ?>, '<?php echo htmlspecialchars($path['name']); ?>')">
                                                            <i class="fe fe-plus"></i> Select This Path
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
                                        <i class="fe fe-book-open fe-48 text-muted"></i>
                                        <p class="text-muted mt-3">No learning paths available at the moment.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- My Learning Material Requests -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">My Learning Material Requests</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($employeeRequests)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Type</th>
                                                    <th>Request Date</th>
                                                    <th>Status</th>
                                                    <th>Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($employeeRequests as $request): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($request['title']); ?></td>
                                                        <td>
                                                            <span class="badge badge-primary"><?php echo htmlspecialchars($request['request_type']); ?></span>
                                                        </td>
                                                        <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                                        <td>
                                                            <?php
                                                            $statusClass = '';
                                                            switch($request['status']) {
                                                                case 'pending': $statusClass = 'badge-warning'; break;
                                                                case 'approved': $statusClass = 'badge-success'; break;
                                                                case 'rejected': $statusClass = 'badge-danger'; break;
                                                                case 'cancelled': $statusClass = 'badge-secondary'; break;
                                                            }
                                                            ?>
                                                            <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($request['status']); ?></span>
                                                        </td>
                                                        <td><?php echo htmlspecialchars(substr($request['description'], 0, 100)) . (strlen($request['description']) > 100 ? '...' : ''); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No learning material requests found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Request Learning Material Modal -->
            <div class="modal fade" id="requestMaterialModal" tabindex="-1" role="dialog" aria-labelledby="requestMaterialModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="requestMaterialModalLabel">Request Learning Material</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" id="modal_learning_path_id" name="learning_path_id" value="">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="modal_material_type">Material Type <span class="text-danger">*</span></label>
                                            <select class="form-control" id="modal_material_type" name="material_type" required>
                                                <option value="">Select Material Type</option>
                                                <option value="book">Book</option>
                                                <option value="online_course">Online Course</option>
                                                <option value="ebook">E-Book</option>
                                                <option value="video_tutorial">Video Tutorial</option>
                                                <option value="software_license">Software License</option>
                                                <option value="certification_exam">Certification Exam</option>
                                                <option value="conference_ticket">Conference Ticket</option>
                                                <option value="workshop">Workshop</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="modal_priority">Priority</label>
                                            <select class="form-control" id="modal_priority" name="priority">
                                                <option value="low">Low</option>
                                                <option value="medium" selected>Medium</option>
                                                <option value="high">High</option>
                                                <option value="urgent">Urgent</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="modal_title">Title/Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="modal_title" name="title" 
                                           placeholder="e.g., 'Advanced JavaScript Programming', 'AWS Solutions Architect Guide'" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="modal_description">Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="modal_description" name="description" rows="4" 
                                              placeholder="Describe the learning material, why you need it, and how it will help your professional development" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" name="request_learning_material" class="btn btn-primary">
                                    <i class="fe fe-send mr-2"></i>Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                function selectLearningPath(pathId, pathName) {
                    // Pre-fill the modal with the selected learning path
                    document.getElementById('modal_learning_path_id').value = pathId;
                    document.getElementById('modal_title').value = pathName;
                    document.getElementById('modal_description').value = 'I would like to request materials for the learning path: ' + pathName;
                    document.getElementById('modal_material_type').value = 'online_course';
                    document.getElementById('modal_priority').value = 'medium';
                    
                    // Open the modal
                    $('#requestMaterialModal').modal('show');
                }

                // Search and filter functionality
                function filterLearningPaths() {
                    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
                    const categoryFilter = document.getElementById('categoryFilter').value;
                    const cards = document.querySelectorAll('.learning-path-card');
                    let visibleCount = 0;

                    cards.forEach(card => {
                        const name = card.getAttribute('data-name');
                        const description = card.getAttribute('data-description');
                        const role = card.getAttribute('data-role');
                        
                        const matchesSearch = name.includes(searchTerm) || description.includes(searchTerm) || role.toLowerCase().includes(searchTerm);
                        const matchesCategory = categoryFilter === '' || role === categoryFilter;
                        
                        if (matchesSearch && matchesCategory) {
                            card.style.display = 'block';
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    // Update result count
                    document.getElementById('resultCount').textContent = visibleCount + ' paths found';
                }

                function clearFilters() {
                    document.getElementById('searchInput').value = '';
                    document.getElementById('categoryFilter').value = '';
                    filterLearningPaths();
                }

                // Add event listeners
                document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('searchInput').addEventListener('input', filterLearningPaths);
                    document.getElementById('categoryFilter').addEventListener('change', filterLearningPaths);
                    
                    // Initial count
                    filterLearningPaths();
                });
            </script>
