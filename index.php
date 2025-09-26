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
    <meta name="description" content="HR1 - Human Resources Management System">
    <meta name="author" content="">
    <link rel="icon" href="assets/images/favicon.ico">
    <title>HR1 - Human Resources Management System</title>
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
    <!-- HR1 Custom CSS -->
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
                        <strong>Important:</strong> You must accept our Terms and Conditions to continue using the HR1 system.
                    </div>
                    
                    <div class="terms-summary">
                        <h6 class="text-primary mb-3">HR1 Human Resources Management System - Terms and Conditions</h6>
                        
                        <div class="mb-3">
                            <h6 class="text-dark">1. Acceptance of Terms</h6>
                            <p class="text-justify small">
                                By accessing and using the HR1 Human Resources Management System, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions. These terms are governed by the laws of the Republic of the Philippines and are subject to the provisions of Republic Act No. 10173 (Data Privacy Act of 2012), Republic Act No. 10175 (Cybercrime Prevention Act of 2012), and other applicable Philippine laws.
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
    <!-- HR1 Custom JavaScript -->
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
