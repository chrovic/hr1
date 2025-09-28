<?php
session_start();
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/terms_acceptance.php';

$auth = new SimpleAuth();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Check terms acceptance
$conn = getDB();
$termsAcceptance = new TermsAcceptance($conn);
$userId = $current_user['id'] ?? null;
$termsAccepted = $userId ? $termsAcceptance->hasUserAcceptedTerms($userId) : false;

// Handle competency models form processing BEFORE any HTML output
if ($page === 'competency_models' && $_POST) {
    require_once 'includes/functions/competency.php';
    
    $competencyManager = new CompetencyManager();
    
    if (isset($_POST['create_model'])) {
        $modelData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'category' => $_POST['category'],
            'target_roles' => explode(',', $_POST['target_roles']),
            'assessment_method' => $_POST['assessment_method'],
            'created_by' => $current_user['id']
        ];
        
        if ($competencyManager->createModel($modelData)) {
            $auth->logActivity('create_competency_model', 'competency_models', null, null, $modelData);
            header('Location: ?page=competency_models&success=model_created');
            exit;
        }
    }
    
    if (isset($_POST['add_competency'])) {
        $competencyData = [
            'model_id' => $_POST['model_id'],
            'name' => $_POST['competency_name'],
            'description' => $_POST['competency_description'],
            'weight' => $_POST['weight'],
            'max_score' => $_POST['max_score']
        ];
        
        if ($competencyManager->addCompetency($competencyData)) {
            $auth->logActivity('add_competency', 'competencies', null, null, $competencyData);
            header('Location: ?page=competency_models&success=competency_added');
            exit;
        }
    }
    
    if (isset($_POST['update_model'])) {
        $modelId = $_POST['model_id'];
        $updateData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'category' => $_POST['category'],
            'target_roles' => explode(',', $_POST['target_roles']),
            'assessment_method' => $_POST['assessment_method'],
            'status' => $_POST['status']
        ];
        
        if ($competencyManager->updateModel($modelId, $updateData, $current_user['first_name'] . ' ' . $current_user['last_name'])) {
            $auth->logActivity('update_competency_model', 'competency_models', $modelId, null, $updateData);
            header('Location: ?page=competency_models&success=model_updated');
            exit;
        }
    }
    
    if (isset($_POST['delete_model'])) {
        $modelId = $_POST['model_id'];
        
        if ($competencyManager->deleteModel($modelId)) {
            $auth->logActivity('delete_competency_model', 'competency_models', $modelId, null, null);
            header('Location: ?page=competency_models&success=model_deleted');
            exit;
        }
    }
    
    if (isset($_POST['update_competency'])) {
        $competencyId = $_POST['competency_id'];
        $updateData = [
            'name' => $_POST['competency_name'],
            'description' => $_POST['competency_description'],
            'weight' => $_POST['weight'],
            'max_score' => $_POST['max_score']
        ];
        
        if ($competencyManager->updateCompetency($competencyId, $updateData)) {
            $auth->logActivity('update_competency', 'competencies', $competencyId, null, $updateData);
            header('Location: ?page=competency_models&success=competency_updated');
            exit;
        }
    }
    
    if (isset($_POST['delete_competency'])) {
        $competencyId = $_POST['competency_id'];
        
        if ($competencyManager->deleteCompetency($competencyId)) {
            $auth->logActivity('delete_competency', 'competencies', $competencyId, null, null);
            header('Location: ?page=competency_models&success=competency_deleted');
            exit;
        }
    }
}

// Handle evaluation cycles form processing BEFORE any HTML output
if ($page === 'evaluation_cycles' && $_POST) {
    require_once 'includes/functions/competency.php';
    
    $competencyManager = new CompetencyManager();
    
    if (isset($_POST['create_cycle'])) {
        $cycleData = [
            'name' => $_POST['name'],
            'type' => $_POST['type'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'created_by' => $current_user['id']
        ];
        
        if ($competencyManager->createEvaluationCycle($cycleData)) {
            $auth->logActivity('create_evaluation_cycle', 'evaluation_cycles', null, null, $cycleData);
            header('Location: ?page=evaluation_cycles&success=cycle_created');
            exit;
        }
    }
    
    if (isset($_POST['update_cycle'])) {
        $cycleId = $_POST['cycle_id'];
        $updateData = [
            'name' => $_POST['name'],
            'type' => $_POST['type'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date']
        ];
        
        if ($competencyManager->updateEvaluationCycle($cycleId, $updateData, $current_user['first_name'] . ' ' . $current_user['last_name'])) {
            $auth->logActivity('update_evaluation_cycle', 'evaluation_cycles', $cycleId, null, $updateData);
            header('Location: ?page=evaluation_cycles&success=cycle_updated');
            exit;
        }
    }
    
    if (isset($_POST['delete_cycle'])) {
        $cycleId = $_POST['cycle_id'];
        
        if ($competencyManager->deleteEvaluationCycle($cycleId, $current_user['first_name'] . ' ' . $current_user['last_name'])) {
            $auth->logActivity('delete_evaluation_cycle', 'evaluation_cycles', $cycleId, null, null);
            header('Location: ?page=evaluation_cycles&success=cycle_deleted');
            exit;
        }
    }
}

// Handle learning management form processing BEFORE any HTML output
if ($page === 'learning_management' && $_POST) {
    require_once 'includes/functions/learning.php';
    
    $learningManager = new LearningManager();
    
    if (isset($_POST['create_course'])) {
        $courseData = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'category' => $_POST['category'],
            'type' => $_POST['type'],
            'duration_hours' => $_POST['duration_hours'],
            'max_participants' => $_POST['max_participants'],
            'prerequisites' => $_POST['prerequisites'],
            'learning_objectives' => $_POST['learning_objectives'],
            'created_by' => $current_user['id']
        ];
        
        if ($learningManager->createTraining($courseData)) {
            $auth->logActivity('create_course', 'training_catalog', null, null, $courseData);
            header('Location: ?page=learning_management&success=course_created');
            exit;
        }
    }
    
    if (isset($_POST['update_course'])) {
        $courseId = $_POST['course_id'];
        $updateData = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'category' => $_POST['category'],
            'type' => $_POST['type'],
            'duration_hours' => $_POST['duration_hours'],
            'max_participants' => $_POST['max_participants'],
            'prerequisites' => $_POST['prerequisites'],
            'learning_objectives' => $_POST['learning_objectives']
        ];
        
        if ($learningManager->updateTraining($courseId, $updateData, $current_user['id'])) {
            $auth->logActivity('update_course', 'training_catalog', $courseId, null, $updateData);
            header('Location: ?page=learning_management&success=course_updated');
            exit;
        }
    }
    
    if (isset($_POST['delete_course'])) {
        $courseId = $_POST['course_id'];
        
        if ($learningManager->deleteTraining($courseId, $current_user['id'])) {
            $auth->logActivity('delete_course', 'training_catalog', $courseId, null, null);
            header('Location: ?page=learning_management&success=course_deleted');
            exit;
        }
    }
}

// Handle training management form processing BEFORE any HTML output
if ($page === 'training_management' && $_POST) {
    require_once 'includes/functions/learning.php';
    
    $learningManager = new LearningManager();
    
    if (isset($_POST['submit_feedback'])) {
        $enrollmentId = $_POST['enrollment_id'];
        $score = $_POST['score'];
        $feedback = $_POST['feedback'];
        $completionStatus = $_POST['completion_status'];
        
        $db = getDB();
        $stmt = $db->prepare("
            UPDATE training_enrollments 
            SET score = ?, feedback = ?, completion_status = ?, completion_date = NOW(), updated_at = NOW()
            WHERE id = ?
        ");
        
        if ($stmt->execute([$score, $feedback, $completionStatus, $enrollmentId])) {
            $auth->logActivity('submit_training_feedback', 'training_enrollments', $enrollmentId, null, [
                'score' => $score,
                'completion_status' => $completionStatus
            ]);
            
            // Send notifications for feedback submission
            try {
                require_once 'includes/functions/notification_manager.php';
                $notificationManager = new NotificationManager();
                
                // Get enrollment details for notifications
                $stmt = $db->prepare("
                    SELECT te.employee_id, te.score as old_score, ts.session_name, tm.title as course_title,
                           u.first_name as employee_first_name, u.last_name as employee_last_name
                    FROM training_enrollments te
                    JOIN training_sessions ts ON te.session_id = ts.id
                    JOIN training_modules tm ON ts.module_id = tm.id
                    JOIN users u ON te.employee_id = u.id
                    WHERE te.id = ?
                ");
                $stmt->execute([$enrollmentId]);
                $enrollment = $stmt->fetch();
                
                if ($enrollment) {
                    // Notify the employee about their feedback/score
                    $notificationManager->createNotification(
                        $enrollment['employee_id'],
                        'feedback_submitted',
                        'Training Feedback Submitted',
                        'Feedback has been submitted for your training "' . $enrollment['course_title'] . '" with a score of ' . $score . '.',
                        $enrollmentId,
                        'enrollment',
                        '?page=my_trainings',
                        true
                    );
                    
                    // Notify HR managers about the feedback submission
                    $stmt = $db->prepare("SELECT id FROM users WHERE role IN ('admin', 'hr_manager') AND status = 'active'");
                    $stmt->execute();
                    $hrUsers = $stmt->fetchAll();
                    
                    foreach ($hrUsers as $hrUser) {
                        $notificationManager->createNotification(
                            $hrUser['id'],
                            'feedback_submitted',
                            'Training Feedback Submitted',
                            'Feedback has been submitted for ' . $enrollment['employee_first_name'] . ' ' . $enrollment['employee_last_name'] . '\'s training "' . $enrollment['course_title'] . '" with a score of ' . $score . ' by ' . $current_user['first_name'] . ' ' . $current_user['last_name'] . '.',
                            $enrollmentId,
                            'enrollment',
                            '?page=training_management',
                            true
                        );
                    }
                }
            } catch (Exception $e) {
                // Log notification error but don't fail the feedback submission
                error_log("Notification error for feedback submission: " . $e->getMessage());
            }
            
            header('Location: ?page=training_management&success=feedback_submitted');
            exit;
        }
    }
    
    if (isset($_POST['create_session'])) {
        $sessionData = [
            'training_id' => $_POST['training_id'],
            'session_name' => $_POST['session_name'],
            'session_date' => $_POST['session_date'],
            'start_time' => $_POST['start_time'],
            'end_time' => $_POST['end_time'],
            'location' => $_POST['location'],
            'trainer_id' => $_POST['trainer_id'],
            'max_participants' => $_POST['max_participants'],
            'status' => $_POST['status'],
            'created_by' => $current_user['id']
        ];
        
        if ($learningManager->scheduleSession($sessionData)) {
            $auth->logActivity('create_session', 'training_sessions', null, null, $sessionData);
            header('Location: ?page=training_management&success=session_created');
            exit;
        }
    }
    
    if (isset($_POST['update_session'])) {
        $sessionData = [
            'training_id' => $_POST['edit_training_id'],
            'session_name' => $_POST['edit_session_name'],
            'start_date' => $_POST['edit_start_date'],
            'end_date' => $_POST['edit_end_date'],
            'location' => $_POST['edit_location'],
            'trainer_id' => $_POST['edit_trainer_id'],
            'max_participants' => $_POST['edit_max_participants'],
            'status' => $_POST['edit_status'],
            'updated_by' => $current_user['id']
        ];
        
        $sessionId = $_POST['edit_session_id'];
        
        if ($learningManager->updateSession($sessionId, $sessionData)) {
            $auth->logActivity('update_session', 'training_sessions', $sessionId, null, $sessionData);
            header('Location: ?page=training_management&success=session_updated');
            exit;
        }
    }
    
    if (isset($_POST['enroll_employee'])) {
        $enrollmentData = [
            'session_id' => $_POST['session_id'],
            'employee_id' => $_POST['employee_id'],
            'enrollment_date' => date('Y-m-d H:i:s'),
            'status' => 'enrolled',
            'enrolled_by' => $current_user['id'] // Add the person who is enrolling the employee
        ];
        
        if ($learningManager->enrollEmployee($enrollmentData)) {
            $auth->logActivity('enroll_employee', 'training_enrollments', null, null, $enrollmentData);
            header('Location: ?page=training_management&success=employee_enrolled');
            exit;
        }
    }
}

// Handle evaluation form submissions BEFORE any HTML output
if ($page === 'evaluation_form' && $_POST && isset($_POST['submit_scores'])) {
    require_once 'includes/functions/competency.php';
    
    $competencyManager = new CompetencyManager();
    $evaluation_id = $_GET['id'] ?? 0;
    
    $scores = [];
    foreach ($_POST['scores'] as $competency_id => $score_data) {
        $scores[] = [
            'competency_id' => $competency_id,
            'score' => $score_data['score'],
            'comments' => $score_data['comments'] ?? ''
        ];
    }
    
    if ($competencyManager->submitScores($evaluation_id, $scores)) {
        $auth->logActivity('complete_evaluation', 'evaluations', $evaluation_id, null, ['scores_count' => count($scores)]);
        header('Location: ?page=evaluation_form&id=' . $evaluation_id . '&success=scores_submitted');
        exit;
    }
}

// Handle evaluations form processing BEFORE any HTML output
if ($page === 'evaluations' && $_POST && isset($_POST['assign_evaluation'])) {
    require_once 'includes/functions/competency.php';
    
    $competencyManager = new CompetencyManager();
    
    $evaluationData = [
        'cycle_id' => $_POST['cycle_id'],
        'employee_id' => $_POST['employee_id'],
        'evaluator_id' => $_POST['evaluator_id'],
        'model_id' => $_POST['model_id']
    ];
    
    // Check if evaluation already exists
    $stmt = $conn->prepare("SELECT id FROM evaluations WHERE cycle_id = ? AND employee_id = ? AND evaluator_id = ? AND model_id = ?");
    $stmt->execute([$evaluationData['cycle_id'], $evaluationData['employee_id'], $evaluationData['evaluator_id'], $evaluationData['model_id']]);
    
    if ($stmt->fetch()) {
        // Set error in session and redirect
        $_SESSION['evaluation_error'] = 'This evaluation already exists for the selected employee, cycle, and model.';
        header('Location: ?page=evaluations');
        exit;
    } else {
        if ($competencyManager->assignEvaluation($evaluationData)) {
            $auth->logActivity('assign_evaluation', 'evaluations', null, null, $evaluationData);
            header('Location: ?page=evaluations&success=1');
            exit;
        } else {
            $_SESSION['evaluation_error'] = 'Failed to assign evaluation.';
            header('Location: ?page=evaluations');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="HR2 - Human Resources Management System">
    <meta name="author" content="">
    <link rel="icon" href="assets/images/favicon.ico">
    <title>HR2 - Human Resources Management System</title>
    <!-- Simple bar CSS -->
    <link rel="stylesheet" href="assets/vendor/css/simplebar.css">
    <!-- Fonts CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Overpass:ital,wght@0,100;0,200;0,300;0,400;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet">
    <!-- Icons CSS -->
    <link rel="stylesheet" href="assets/vendor/css/feather.css">
    <link rel="stylesheet" href="assets/vendor/css/select2.css">
    <link rel="stylesheet" href="assets/vendor/css/dropzone.css">
    <link rel="stylesheet" href="assets/vendor/css/uppy.min.css">
    <link rel="stylesheet" href="assets/vendor/css/jquery.steps.css">
    <link rel="stylesheet" href="assets/vendor/css/jquery.timepicker.css">
    <link rel="stylesheet" href="assets/vendor/css/quill.snow.css">
    <!-- Date Range Picker CSS -->
    <link rel="stylesheet" href="assets/vendor/css/daterangepicker.css">
    <!-- App CSS -->
    <link rel="stylesheet" href="assets/vendor/css/app-light.css" id="lightTheme">
    <link rel="stylesheet" href="assets/vendor/css/app-dark.css" id="darkTheme" disabled>
    <!-- HR2 Custom CSS -->
    <link rel="stylesheet" href="assets/css/hr-main.css">
</head>
<body class="vertical light">
    <div class="wrapper d-flex flex-column min-vh-100">
        <?php include 'partials/header.php'; ?>
        
        <?php include 'partials/sidebar.php'; ?>
        
        <main role="main" class="main-content flex-grow-1">
            <div class="container-fluid">
                <?php
                switch($page) {
                    case 'dashboard':
                        include 'pages/dashboard.php';
                        break;
                    case 'employee_self_service':
                        include 'pages/employee_self_service.php';
                        break;
                    case 'employee_profile':
                        include 'pages/employee_profile.php';
                        break;
                    case 'employee_portal':
                        include 'pages/employee_portal.php';
                        break;
                    case 'learning_management':
                        include 'pages/learning_management.php';
                        break;
                    case 'learning_management_enhanced':
                        include 'pages/learning_management_enhanced.php';
                        break;
                    case 'training_feedback_management':
                        include 'pages/training_feedback_management.php';
                        break;
                    case 'training_management':
                        include 'pages/training_management.php';
                        break;
                    case 'succession_planning':
                        include 'pages/succession_planning.php';
                        break;
                    case 'competency':
                        include 'pages/competency.php';
                        break;
                    case 'competency_models':
                        include 'pages/competency_models.php';
                        break;
                    case 'evaluation_cycles':
                        include 'pages/evaluation_cycles.php';
                        break;
                    case 'evaluations':
                        include 'pages/evaluations.php';
                        break;
                    case 'evaluation_form':
                        include 'pages/evaluation_form.php';
                        break;
                    case 'competency_reports':
                        include 'pages/competency_reports.php';
                        break;
                    case 'ai_analysis_dashboard':
                        include 'pages/ai_analysis_dashboard.php';
                        break;
                    case 'my_evaluations':
                        include 'pages/my_evaluations.php';
                        break;
                    case 'my_trainings':
                        include 'pages/my_trainings.php';
                        break;
                    case 'my_requests':
                        include 'pages/my_requests.php';
                        break;
                        case 'employee_learning_materials':
                            include 'pages/employee_learning_materials.php';
                            break;
                        case 'employee_learning_access':
                            include 'pages/employee_learning_access.php';
                            break;
                        case 'hr_learning_requests':
                            include 'pages/hr_learning_requests.php';
                            break;
                    case 'employee_training_requests':
                        include 'pages/employee_training_requests.php';
                        break;
                    case 'training_requests':
                        include 'pages/training_requests.php';
                        break;
                    case 'employee_requests':
                        include 'pages/employee_requests.php';
                        break;
                    case 'user_management':
                        include 'pages/user_management.php';
                        break;
                    case 'system_settings':
                        include 'pages/system_settings.php';
                        break;
                    case 'system_logs':
                        include 'pages/system_logs.php';
                        break;
                    case 'reports':
                        include 'pages/reports.php';
                        break;
                    case 'profile':
                        include 'pages/profile.php';
                        break;
                    case 'settings':
                        include 'pages/settings.php';
                        break;
                    case 'activities':
                        include 'pages/activities.php';
                        break;
                    case 'hr_reports':
                        include 'pages/hr_reports.php';
                        break;
                    case 'hr_notifications':
                        include 'pages/hr_notifications.php';
                        break;
                    case 'hr_employee_management':
                        include 'pages/hr_employee_management.php';
                        break;
                    case 'hr_request_management':
                        include 'pages/hr_request_management.php';
                        break;
                    case 'admin_training_management':
                        include 'pages/admin_training_management.php';
                        break;
                    case 'evaluation_form':
                        include 'pages/evaluation_form.php';
                        break;
                    case 'evaluation_view':
                        include 'pages/evaluation_view.php';
                        break;
                    default:
                        include 'pages/404.php';
                        break;
                }
                ?>
            </div>
        </main>
        
        <?php include 'partials/footer.php'; ?>
    </div>

    <!-- Terms Acceptance Modal -->
    <?php if (!$termsAccepted): ?>
    <div class="modal fade" id="termsAcceptanceModal" tabindex="-1" role="dialog" aria-labelledby="termsAcceptanceModalLabel" aria-hidden="false" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="termsAcceptanceModalLabel">
                        <i class="fe fe-shield mr-2"></i>Terms and Conditions Acceptance Required
                    </h5>
                </div>
                <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                    <div class="alert alert-warning">
                        <i class="fe fe-alert-triangle mr-2"></i>
                        <strong>Important:</strong> You must accept our Terms and Conditions to continue using the HR2 system.
                    </div>
                    
                    <div class="terms-summary">
                        <h6 class="text-primary mb-3">HR2 Human Resources Management System - Terms and Conditions</h6>
                        
                        <div class="mb-3">
                            <h6 class="text-dark">1. Acceptance of Terms</h6>
                            <p class="text-justify small">
                                By accessing and using the HR2 Human Resources Management System, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions. These terms are governed by the laws of the Republic of the Philippines and are subject to the provisions of Republic Act No. 10173 (Data Privacy Act of 2012), Republic Act No. 10175 (Cybercrime Prevention Act of 2012), and other applicable Philippine laws.
                            </p>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-dark">2. Data Privacy and Protection</h6>
                            <p class="text-justify small">
                                In compliance with <strong>Republic Act No. 10173 (Data Privacy Act of 2012)</strong>, we are committed to protecting your personal information. This system collects, processes, and stores employee data including but not limited to personal identification information, employment records, performance data, training records, and system usage logs.
                            </p>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-dark">3. User Responsibilities</h6>
                            <p class="text-justify small">
                                In accordance with <strong>Republic Act No. 10175 (Cybercrime Prevention Act of 2012)</strong>, you are responsible for maintaining the confidentiality of your login credentials, reporting security breaches, and using the system only for authorized business purposes.
                            </p>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-dark">4. Electronic Transactions</h6>
                            <p class="text-justify small">
                                Pursuant to <strong>Republic Act No. 8792 (Electronic Commerce Act of 2000)</strong>, electronic documents, records, and signatures generated within this system have the same legal effect as their paper-based counterparts.
                            </p>
                        </div>

                        <div class="alert alert-info small">
                            <strong>Note:</strong> By clicking "I Accept", you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions. You can view the complete terms at any time by clicking the "Terms & Conditions" link in the footer.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="termsCheckbox" required>
                        <label class="form-check-label" for="termsCheckbox">
                            I have read and agree to the Terms and Conditions
                        </label>
                    </div>
                    <button type="button" class="btn btn-primary" id="acceptTermsBtn" disabled>
                        <i class="fe fe-check mr-2"></i>I Accept
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Scripts -->
    <script src="assets/vendor/js/jquery.min.js"></script>
    <script src="assets/vendor/js/popper.min.js"></script>
    <script src="assets/vendor/js/bootstrap.min.js"></script>
    <script src="assets/vendor/js/simplebar.min.js"></script>
    <script src="assets/vendor/js/config.js"></script>
    <script src="assets/vendor/js/apps.js"></script>
    <!-- HR2 Custom JavaScript -->
    <script src="assets/js/hr-main.js"></script>
    
    <!-- Terms Acceptance JavaScript -->
    <script>
    $(document).ready(function() {
        // Show terms acceptance modal if user hasn't accepted
        <?php if (!$termsAccepted): ?>
        $('#termsAcceptanceModal').modal('show');
        <?php endif; ?>
        
        // Handle checkbox change
        $('#termsCheckbox').change(function() {
            if ($(this).is(':checked')) {
                $('#acceptTermsBtn').prop('disabled', false);
            } else {
                $('#acceptTermsBtn').prop('disabled', true);
            }
        });
        
        // Handle terms acceptance
        $('#acceptTermsBtn').click(function() {
            if ($('#termsCheckbox').is(':checked')) {
                $(this).prop('disabled', true).html('<i class="fe fe-loader mr-2"></i>Processing...');
                
                $.ajax({
                    url: 'ajax/accept_terms.php',
                    type: 'POST',
                    data: {
                        action: 'accept_terms'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#termsAcceptanceModal').modal('hide');
                            // Show success message
                            showNotification('Terms and Conditions accepted successfully!', 'success');
                        } else {
                            showNotification('Error: ' + response.message, 'error');
                            $('#acceptTermsBtn').prop('disabled', false).html('<i class="fe fe-check mr-2"></i>I Accept');
                        }
                    },
                    error: function() {
                        showNotification('Error accepting terms. Please try again.', 'error');
                        $('#acceptTermsBtn').prop('disabled', false).html('<i class="fe fe-check mr-2"></i>I Accept');
                    }
                });
            }
        });
        
        // Function to show notifications
        function showNotification(message, type) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'fe-check-circle' : 'fe-alert-circle';
            
            const notification = $(`
                <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                     style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                    <i class="fe ${icon} mr-2"></i>${message}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            `);
            
            $('body').append(notification);
            
            // Auto remove after 5 seconds
            setTimeout(function() {
                notification.alert('close');
            }, 5000);
        }
    });
    </script>
</body>
</html>
