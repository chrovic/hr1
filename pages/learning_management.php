<?php
require_once 'includes/data/db.php';

require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/learning.php';

$auth = new SimpleAuth();

// Check permissions (no redirect needed, handled in index.php)
if (!$auth->hasPermission('manage_training')) {
    $error = 'You do not have permission to access this page.';
    // Don't redirect, just show error
}

$current_user = $auth->getCurrentUser();
$learningManager = new LearningManager();
$db = getDB();

$message = '';
$error = '';

// Form processing is now handled in index.php before any output

// Handle success messages from redirects
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'course_created':
            $message = 'Course created successfully!';
            break;
        case 'course_updated':
            $message = 'Course updated successfully!';
            break;
        case 'course_deleted':
            $message = 'Course deleted successfully!';
            break;
    }
}

// Get data for display
try {
    $courses = $learningManager->getAllTrainings();
    $enrollments = $learningManager->getAllEnrollments();
    
    // Calculate statistics
    $total_courses = count($courses);
    $total_enrollments = count($enrollments);
    $completed_courses = count(array_filter($enrollments, function($e) { return ($e['attendance_status'] ?? '') === 'completed'; }));
    $active_learners = count(array_unique(array_column($enrollments, 'employee_id')));
    
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
    $courses = [];
    $enrollments = [];
    $total_courses = 0;
    $total_enrollments = 0;
    $completed_courses = 0;
    $active_learners = 0;
}
?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Learning Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createCourseModal">
            <i class="fe fe-plus fe-16 mr-2"></i>Create Course
        </button>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Learning Stats -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted mb-2">Active Courses</h6>
                        <span class="h2 mb-0"><?php echo $total_courses; ?></span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-book-open fe-24 text-primary"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted mb-2">Enrolled Learners</h6>
                        <span class="h2 mb-0"><?php echo $active_learners; ?></span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-users fe-24 text-success"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted mb-2">Completed</h6>
                        <span class="h2 mb-0"><?php echo $completed_courses; ?></span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-award fe-24 text-warning"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted mb-2">Completion Rate</h6>
                        <span class="h2 mb-0"><?php echo $total_enrollments > 0 ? round(($completed_courses / $total_enrollments) * 100) : 0; ?>%</span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-trending-up fe-24 text-info"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Course Management -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Course Management</strong>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>Duration</th>
                                <th>Max Participants</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($courses)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No courses found. <a href="#" data-toggle="modal" data-target="#createCourseModal">Create your first course</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($courses as $course): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm mr-3">
                                                    <span class="avatar-title bg-primary rounded"><?php echo strtoupper(substr($course['title'] ?? 'U', 0, 2)); ?></span>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($course['title'] ?? 'Untitled'); ?></strong>
                                                    <div class="text-muted small"><?php echo htmlspecialchars(substr($course['description'] ?? '', 0, 100)) . (strlen($course['description'] ?? '') > 100 ? '...' : ''); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary"><?php echo htmlspecialchars($course['category'] ?? 'Uncategorized'); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($course['type'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($course['duration_hours'] ?? '0'); ?> hours</td>
                                        <td><?php echo $course['max_participants'] ?? 'N/A'; ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo ($course['status'] ?? '') === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($course['status'] ?? 'Unknown'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary" onclick="editCourse(<?php echo htmlspecialchars(json_encode($course)); ?>)">
                                                    <i class="fe fe-edit fe-14"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-info" onclick="viewCourse(<?php echo $course['id']; ?>)">
                                                    <i class="fe fe-eye fe-14"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteCourse(<?php echo $course['id']; ?>)">
                                                    <i class="fe fe-trash fe-14"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Learning Activities -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Recent Learning Activities</strong>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Course</th>
                                <th>Session</th>
                                <th>Enrollment Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($enrollments)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No learning activities found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach (array_slice($enrollments, 0, 10) as $enrollment): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm mr-3">
                                                    <span class="avatar-title bg-primary rounded"><?php echo strtoupper(substr($enrollment['employee_first_name'] ?? 'U', 0, 1) . substr($enrollment['employee_last_name'] ?? 'U', 0, 1)); ?></span>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars(($enrollment['employee_first_name'] ?? 'Unknown') . ' ' . ($enrollment['employee_last_name'] ?? 'User')); ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($enrollment['training_title'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['session_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($enrollment['enrollment_date'])); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo ($enrollment['attendance_status'] ?? '') === 'completed' ? 'success' : (($enrollment['attendance_status'] ?? '') === 'enrolled' ? 'info' : 'warning'); ?>">
                                                <?php echo ucfirst($enrollment['attendance_status'] ?? 'Unknown'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewEnrollment(<?php echo $enrollment['id']; ?>)">
                                                <i class="fe fe-eye fe-14"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Course Modal -->
<div class="modal fade" id="createCourseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Course</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Course Title *</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Category *</label>
                                <select class="form-control" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="Leadership">Leadership</option>
                                    <option value="Technical">Technical</option>
                                    <option value="Soft Skills">Soft Skills</option>
                                    <option value="Compliance">Compliance</option>
                                    <option value="Sales">Sales</option>
                                    <option value="Project Management">Project Management</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Type *</label>
                                <select class="form-control" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="skill_development">Skill Development</option>
                                    <option value="certification">Certification</option>
                                    <option value="compliance">Compliance</option>
                                    <option value="leadership">Leadership</option>
                                    <option value="technical">Technical</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Duration (Hours) *</label>
                                <input type="number" class="form-control" name="duration_hours" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Max Participants</label>
                                <input type="number" class="form-control" name="max_participants" min="1">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Description *</label>
                                <textarea class="form-control" name="description" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Prerequisites</label>
                                <textarea class="form-control" name="prerequisites" rows="2" placeholder="Any prerequisites for this course..."></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Learning Objectives *</label>
                                <textarea class="form-control" name="learning_objectives" rows="3" required placeholder="What will participants learn?"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_course" class="btn btn-primary">Create Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Course</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="course_id" id="edit_course_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Course Title *</label>
                                <input type="text" class="form-control" name="title" id="edit_title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Category *</label>
                                <select class="form-control" name="category" id="edit_category" required>
                                    <option value="">Select Category</option>
                                    <option value="Leadership">Leadership</option>
                                    <option value="Technical">Technical</option>
                                    <option value="Soft Skills">Soft Skills</option>
                                    <option value="Compliance">Compliance</option>
                                    <option value="Sales">Sales</option>
                                    <option value="Project Management">Project Management</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Type *</label>
                                <select class="form-control" name="type" id="edit_type" required>
                                    <option value="">Select Type</option>
                                    <option value="skill_development">Skill Development</option>
                                    <option value="certification">Certification</option>
                                    <option value="compliance">Compliance</option>
                                    <option value="leadership">Leadership</option>
                                    <option value="technical">Technical</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Duration (Hours) *</label>
                                <input type="number" class="form-control" name="duration_hours" id="edit_duration_hours" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Max Participants</label>
                                <input type="number" class="form-control" name="max_participants" id="edit_max_participants" min="1">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Description *</label>
                                <textarea class="form-control" name="description" id="edit_description" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Prerequisites</label>
                                <textarea class="form-control" name="prerequisites" id="edit_prerequisites" rows="2" placeholder="Any prerequisites for this course..."></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Learning Objectives *</label>
                                <textarea class="form-control" name="learning_objectives" id="edit_learning_objectives" rows="3" required placeholder="What will participants learn?"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_course" class="btn btn-primary">Update Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Course Modal -->
<div class="modal fade" id="viewCourseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Course Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4 id="view_course_title" class="mb-3"></h4>
                        <div class="mb-3">
                            <h6 class="text-muted">Description</h6>
                            <p id="view_course_description" class="text-justify"></p>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="text-muted">Category</h6>
                                <span id="view_course_category" class="badge badge-primary"></span>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Type</h6>
                                <span id="view_course_type" class="badge badge-info"></span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="text-muted">Duration</h6>
                                <span id="view_course_duration"></span>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Max Participants</h6>
                                <span id="view_course_max_participants"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Course Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="text-muted">Prerequisites</h6>
                                    <p id="view_course_prerequisites" class="small"></p>
                                </div>
                                <div class="mb-3">
                                    <h6 class="text-muted">Learning Objectives</h6>
                                    <p id="view_course_learning_objectives" class="small"></p>
                                </div>
                                <div class="mb-3">
                                    <h6 class="text-muted">Status</h6>
                                    <span id="view_course_status" class="badge badge-success"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="editCourseFromView()">
                    <i class="fe fe-edit fe-14 mr-1"></i>Edit Course
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function editCourse(course) {
    // Populate edit form with course data
    document.getElementById('edit_course_id').value = course.id;
    document.getElementById('edit_title').value = course.title;
    document.getElementById('edit_description').value = course.description;
    document.getElementById('edit_category').value = course.category;
    document.getElementById('edit_type').value = course.type;
    document.getElementById('edit_duration_hours').value = course.duration_hours;
    document.getElementById('edit_max_participants').value = course.max_participants;
    document.getElementById('edit_prerequisites').value = course.prerequisites;
    document.getElementById('edit_learning_objectives').value = course.learning_objectives;
    
    // Show edit modal
    $('#editCourseModal').modal('show');
}

function deleteCourse(courseId) {
    if (confirm('Are you sure you want to delete this course?')) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="delete_course" value="1"><input type="hidden" name="course_id" value="' + courseId + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function viewCourse(courseId) {
    // Fetch course details via AJAX
    fetch('includes/ajax/ajax_get_course_details.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'course_id=' + courseId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const course = data.course;
            
            // Populate view modal with course data
            document.getElementById('view_course_title').textContent = course.title || 'Untitled Course';
            document.getElementById('view_course_description').textContent = course.description || 'No description available';
            document.getElementById('view_course_category').textContent = course.category || 'Uncategorized';
            document.getElementById('view_course_type').textContent = course.type || 'N/A';
            document.getElementById('view_course_duration').textContent = (course.duration_hours || '0') + ' hours';
            document.getElementById('view_course_max_participants').textContent = course.max_participants || 'N/A';
            document.getElementById('view_course_prerequisites').textContent = course.prerequisites || 'None';
            document.getElementById('view_course_learning_objectives').textContent = course.learning_objectives || 'Not specified';
            document.getElementById('view_course_status').textContent = course.status || 'Active';
            
            // Set badge colors based on type and status
            const typeClass = course.type === 'in_person' ? 'badge-primary' : 
                             course.type === 'virtual' ? 'badge-success' : 
                             course.type === 'hybrid' ? 'badge-warning' : 'badge-info';
            document.getElementById('view_course_type').className = 'badge ' + typeClass;
            
            const statusClass = course.status === 'active' ? 'badge-success' : 
                               course.status === 'inactive' ? 'badge-secondary' : 'badge-warning';
            document.getElementById('view_course_status').className = 'badge ' + statusClass;
            
            // Store course data for edit functionality
            window.currentCourse = course;
            
            // Show view modal
            $('#viewCourseModal').modal('show');
        } else {
            alert('Failed to load course details: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load course details. Please try again.');
    });
}

function editCourseFromView() {
    // Close view modal and open edit modal with current course data
    $('#viewCourseModal').modal('hide');
    if (window.currentCourse) {
        editCourse(window.currentCourse);
    }
}

function viewSession(sessionId) {
    // Show session details in a simple alert for now
    // TODO: Implement proper session details modal
    alert('Session details functionality will be implemented soon. Session ID: ' + sessionId);
}

function manageEnrollments(sessionId) {
    // Redirect to enrollment management page
    window.location.href = '?page=session_enrollments&id=' + sessionId;
}

function approveRequest(requestId) {
    if (confirm('Are you sure you want to approve this training request?')) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="approve_request" value="1"><input type="hidden" name="request_id" value="' + requestId + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function rejectRequest(requestId) {
    const reason = prompt('Please provide a reason for rejection:');
    if (reason !== null) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="reject_request" value="1"><input type="hidden" name="request_id" value="' + requestId + '"><input type="hidden" name="rejection_reason" value="' + reason + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function viewEnrollment(enrollmentId) {
    // Show enrollment details in a simple alert for now
    // TODO: Implement proper enrollment details modal
    alert('Enrollment details functionality will be implemented soon. Enrollment ID: ' + enrollmentId);
}
</script>