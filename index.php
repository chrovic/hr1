<?php
session_start();
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';

$auth = new SimpleAuth();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
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
    <div class="wrapper">
        <?php include 'partials/header.php'; ?>
        
        <?php include 'partials/sidebar.php'; ?>
        
        <main role="main" class="main-content">
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
    </div>

    <!-- Scripts -->
    <script src="assets/vendor/js/jquery.min.js"></script>
    <script src="assets/vendor/js/popper.min.js"></script>
    <script src="assets/vendor/js/bootstrap.min.js"></script>
    <script src="assets/vendor/js/simplebar.min.js"></script>
    <script src="assets/vendor/js/config.js"></script>
    <script src="assets/vendor/js/apps.js"></script>
    <!-- HR1 Custom JavaScript -->
    <script src="assets/js/hr-main.js"></script>
</body>
</html>
