<?php
session_start();
require_once '../includes/data/db.php';
require_once '../includes/functions/simple_auth.php';
require_once '../includes/functions/otpv2.php';
require_once '../config/recaptcha.php';

$auth = new SimpleAuth();
$otpManager = new OTPManager();
$error = '';
$success = '';
$isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'], true);
$forceRecaptcha = defined('FORCE_RECAPTCHA') && FORCE_RECAPTCHA === true;
$recaptchaEnabled = $forceRecaptcha ? true : !$isLocal;
if (!getDB()) {
    $error = 'Database connection failed. Please check your database configuration.';
}
if (!empty($_SESSION['session_timeout'])) {
    $success = 'Your session expired due to inactivity. Please sign in again.';
    unset($_SESSION['session_timeout']);
}
if (isset($_GET['reset_otp'])) {
    unset($_SESSION['otp_user_id']);
}

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

if ($_POST && !$error) {
    $otpStep = isset($_POST['otp_step']) && $_POST['otp_step'] === '1';
    $resendOtp = isset($_POST['resend_otp']) && $_POST['resend_otp'] === '1';

    if ($otpStep) {
        $otpCode = trim($_POST['otp_code'] ?? '');
        $otpUserId = $_SESSION['otp_user_id'] ?? null;

        if (!$otpUserId) {
            $error = 'OTP session expired. Please login again.';
        } elseif ($resendOtp) {
            $stmt = getDB()->prepare("SELECT email FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$otpUserId]);
            $row = $stmt->fetch();
            if (!$row || empty($row['email'])) {
                $error = 'No email is set for this account. Please contact administrator.';
            } else {
                $sent = $otpManager->generateOtp($otpUserId, $row['email']);
                if (!empty($sent['success'])) {
                    $success = 'A new OTP has been sent to your email.';
                } else {
                    $error = $sent['message'] ?? 'Unable to send OTP.';
                }
            }
        } elseif ($otpCode === '') {
            $error = 'Please enter the OTP code.';
        } else {
            $result = $otpManager->verifyOtp($otpUserId, $otpCode);
            if (!empty($result['success'])) {
                unset($_SESSION['otp_user_id']);
                if ($auth->loginWithUserId($otpUserId)) {
                    header('Location: ../index.php');
                    exit;
                }
                $error = 'Login failed. Please try again.';
            } else {
                $error = $result['message'] ?? 'Invalid OTP.';
            }
        }
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
    } elseif (empty($recaptchaResponse) && $recaptchaEnabled) {
        $error = 'Please complete the reCAPTCHA verification.';
    } else {
        $verifyOk = !$recaptchaEnabled;
        if ($recaptchaEnabled) {
            $secretKey = '6LeZV1wsAAAAAK07SuJYRE7NtyrAVt0uJ5cWndhG';
                $verifyResponse = null;
                try {
                    $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
                    $postData = http_build_query([
                        'secret' => $secretKey,
                        'response' => $recaptchaResponse,
                        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
                    ]);
                    $context = stream_context_create([
                        'http' => [
                            'method' => 'POST',
                            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                            'content' => $postData,
                            'timeout' => 10
                        ]
                    ]);
                    $verifyRaw = file_get_contents($verifyUrl, false, $context);
                    $verifyResponse = $verifyRaw ? json_decode($verifyRaw, true) : null;
                } catch (Exception $e) {
                    $verifyResponse = null;
                }
            $verifyOk = !empty($verifyResponse['success']);
        }

            if (!$verifyOk) {
                $error = 'reCAPTCHA verification failed. Please try again.';
            } else {
                $user = $auth->authenticateUser($username, $password);
                if ($user) {
                    if (empty($user['email'])) {
                        $error = 'No email is set for this account. Please contact administrator.';
                    } else {
                        $_SESSION['otp_user_id'] = $user['id'];
                        $sent = $otpManager->generateOtp($user['id'], $user['email']);
                        if (!empty($sent['success'])) {
                            $success = 'OTP sent to your email. Please enter the code below.';
                        } else {
                            $error = $sent['message'] ?? 'Unable to send OTP. Please check email settings.';
                        }
                    }
                } else {
                    $error = 'Invalid username or password. Please try again.';
                }
            }
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
    <link rel="icon" href="../assets/images/favicon.ico">
    <title>HR2 - Human Resources Management System</title>
    
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="../assets/vendor/css/simplebar.css">
    <link rel="stylesheet" href="../assets/vendor/css/feather.css">
    <link rel="stylesheet" href="../assets/vendor/css/app-light.css">
    <?php if ($recaptchaEnabled): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/hr-main.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }
        
        /* Animated Background */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            animation: float 30s infinite linear;
            z-index: 1;
        }
        
        @keyframes float {
            0% { transform: translateX(-100px) translateY(-100px) rotate(0deg); }
            100% { transform: translateX(100px) translateY(100px) rotate(360deg); }
        }
        
        .login-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 450px;
            padding: 2rem;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }
        
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .login-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .login-logo i {
            font-size: 2.5rem;
            color: white;
        }
        
        .login-header h1 {
            color: #2c3e50;
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }
        
        .login-header p {
            color: #6c757d;
            font-size: 1.1rem;
            margin: 0;
            font-weight: 400;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .form-control {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
            color: #495057;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
            transform: translateY(-1px);
        }
        
        .form-control::placeholder {
            color: #adb5bd;
        }
        
        
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            color: white;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 1rem;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert {
            border-radius: 12px;
            margin-bottom: 1.5rem;
            padding: 1rem 1.25rem;
            border: none;
            font-weight: 500;
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border-left: 4px solid #dc3545;
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border-left: 4px solid #28a745;
        }
        
        
        /* Responsive Design */
        @media (max-width: 576px) {
            .login-container {
            padding: 1rem;
            }
            
            .login-card {
                padding: 2rem;
            }
            
            .login-header h1 {
                font-size: 1.75rem;
            }
            
            .login-logo {
                width: 70px;
                height: 70px;
            }
            
            .login-logo i {
                font-size: 2rem;
            }
        }
        
        /* Loading Animation */
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .btn-login.loading {
            position: relative;
        }
        
        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Focus States */
        .form-group:focus-within .form-label {
            color: #667eea;
        }
        
        /* Hover Effects */
        .form-control:hover {
            border-color: #ced4da;
            background: white;
        }
        
        /* Footer Alignment Fixes */
        .footer {
            margin-top: auto;
            border-top: 2px solid #dee2e6;
            background-color: #ffffff;
            font-size: 0.875rem;
            width: 100%;
            box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .footer .container-fluid {
            padding: 0;
            max-width: 100%;
        }
        
        .footer .row {
            margin: 0;
        }
        
        .footer .col-12 {
            padding: 0;
        }
        
        .footer .small {
            color: #495057 !important;
            font-weight: 500;
        }
        
        .footer a {
            color: #007bff !important;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }
        
        .footer a:hover {
            color: #0056b3 !important;
            background-color: rgba(0, 123, 255, 0.1);
            text-decoration: none;
        }
        
        .footer .mx-2 {
            color: #6c757d;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="login-wrapper" style="flex: 1; display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 100px);">
        <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fe fe-briefcase"></i>
                </div>
                <h1>HR2 System</h1>
                <p>Human Resources Management</p>
            </div>


            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fe fe-alert-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fe fe-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <?php $otpPending = isset($_SESSION['otp_user_id']); ?>
                <?php if ($otpPending): ?>
                    <div class="form-group">
                        <label for="otp_code" class="form-label">Enter OTP</label>
                        <input type="text" class="form-control" id="otp_code" name="otp_code"
                               placeholder="6-digit code" required autocomplete="one-time-code">
                    </div>
                    <input type="hidden" name="otp_step" value="1">
                    <input type="hidden" name="resend_otp" id="resend_otp" value="0">
                <?php else: ?>
                    <div class="form-group">
                        <label for="username" class="form-label">Username or Email</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Enter your username or email" required autocomplete="username">
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Enter your password" required autocomplete="current-password">
                    </div>

                    <?php if ($recaptchaEnabled): ?>
                    <div class="form-group" style="display:flex; justify-content:center;">
                        <div class="g-recaptcha" data-sitekey="6LeZV1wsAAAAAJYIiez6oL4YuH1y9K6S_XAUJrFI"></div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>

                <button type="submit" class="btn btn-login" id="loginBtn">
                    <span class="btn-text"><?php echo $otpPending ? 'Verify OTP' : 'Sign In'; ?></span>
                </button>
                <?php if ($otpPending): ?>
                    <button type="button" class="btn btn-link mt-2" id="resendBtn" style="width: 100%; text-align:center;">
                        Resend OTP
                    </button>
                    <a href="login.php?reset_otp=1" class="btn btn-link mt-1" style="width: 100%; text-align:center;">
                        Back to Login
                    </a>
                <?php endif; ?>
            </form>

        </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer bg-white border-top" style="position: relative; z-index: 2; margin-top: auto;">
        <div class="container-fluid px-0">
            <div class="row no-gutters justify-content-center">
                <div class="col-12 text-center">
                    <div class="py-3 px-3">
                        <div class="small mb-2">
                            <span>&copy; <?php echo date('Y'); ?> HR2 Human Resources Management System. All rights reserved.</span>
                        </div>
                        <div class="small">
                            <a href="#" data-toggle="modal" data-target="#termsModal" style="color: #007bff; text-decoration: none; font-weight: 500;">
                                Terms & Conditions
                            </a>
                            <span class="mx-2" style="color: #6c757d; font-weight: 600;">|</span>
                            <a href="#" data-toggle="modal" data-target="#privacyModal" style="color: #007bff; text-decoration: none; font-weight: 500;">
                                Privacy Policy
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" role="dialog" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="terms-content">
                        <h6 class="text-primary mb-3">HR2 Human Resources Management System - Terms and Conditions</h6>
                        
                        <p class="text-muted small mb-4">
                            <strong>Effective Date:</strong> <?php echo date('F d, Y'); ?><br>
                            <strong>Last Updated:</strong> <?php echo date('F d, Y'); ?>
                        </p>

                        <div class="mb-4">
                            <h6 class="text-dark">1. Acceptance of Terms</h6>
                            <p class="text-justify small">
                                By accessing and using the HR2 Human Resources Management System, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions. These terms are governed by the laws of the Republic of the Philippines and are subject to the provisions of Republic Act No. 10173 (Data Privacy Act of 2012), Republic Act No. 10175 (Cybercrime Prevention Act of 2012), and other applicable Philippine laws.
                            </p>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-dark">2. Data Privacy and Protection</h6>
                            <p class="text-justify small">
                                In compliance with <strong>Republic Act No. 10173 (Data Privacy Act of 2012)</strong>, we are committed to protecting your personal information. This system collects, processes, and stores employee data including but not limited to:
                            </p>
                            <ul class="small">
                                <li>Personal identification information</li>
                                <li>Employment records and performance data</li>
                                <li>Training and competency assessments</li>
                                <li>System usage logs and activities</li>
                            </ul>
                            <p class="text-justify small">
                                All data processing activities are conducted in accordance with the principles of transparency, legitimate purpose, and proportionality as mandated by the Data Privacy Act.
                            </p>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-dark">3. User Responsibilities and Prohibited Activities</h6>
                            <p class="text-justify small">
                                In accordance with <strong>Republic Act No. 10175 (Cybercrime Prevention Act of 2012)</strong>, users are strictly prohibited from:
                            </p>
                            <ul class="small">
                                <li>Unauthorized access to system resources or other users' data</li>
                                <li>Interfering with system operations or data integrity</li>
                                <li>Distributing malicious software or engaging in cyber attacks</li>
                                <li>Sharing login credentials or allowing unauthorized access</li>
                                <li>Using the system for any illegal or unauthorized purposes</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-dark">4. Electronic Transactions and Signatures</h6>
                            <p class="text-justify small">
                                Pursuant to <strong>Republic Act No. 8792 (Electronic Commerce Act of 2000)</strong>, electronic documents, records, and signatures generated within this system have the same legal effect as their paper-based counterparts. Users consent to the electronic processing of HR transactions and acknowledge the validity of digital signatures and electronic approvals.
                            </p>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-dark">5. System Access and Security</h6>
                            <p class="text-justify small">
                                Access to this system is granted based on your role and organizational requirements. You are responsible for:
                            </p>
                            <ul class="small">
                                <li>Maintaining the confidentiality of your login credentials</li>
                                <li>Reporting any security breaches or suspicious activities</li>
                                <li>Using the system only for authorized business purposes</li>
                                <li>Complying with all applicable company policies and procedures</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-dark">6. Data Retention and Disposal</h6>
                            <p class="text-justify small">
                                Employee data will be retained in accordance with applicable Philippine labor laws and company policies. Upon termination of employment or as required by law, data will be disposed of securely in compliance with the Data Privacy Act's requirements for data disposal.
                            </p>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-dark">7. Limitation of Liability</h6>
                            <p class="text-justify small">
                                The system is provided "as is" without warranties of any kind. The organization shall not be liable for any indirect, incidental, or consequential damages arising from the use of this system, except as required by applicable Philippine law.
                            </p>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-dark">8. Governing Law and Jurisdiction</h6>
                            <p class="text-justify small">
                                These Terms and Conditions are governed by the laws of the Republic of the Philippines. Any disputes arising from the use of this system shall be subject to the exclusive jurisdiction of the Philippine courts.
                            </p>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-dark">9. Contact Information</h6>
                            <p class="text-justify small">
                                For questions regarding these Terms and Conditions or data privacy concerns, please contact the HR Department or the Data Protection Officer at your organization.
                            </p>
                        </div>

                        <div class="alert alert-info small">
                            <strong>Note:</strong> These Terms and Conditions may be updated periodically to reflect changes in applicable laws or system functionality. Users will be notified of any material changes through the system or other appropriate means.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Privacy Policy Modal -->
    <div class="modal fade" id="privacyModal" tabindex="-1" role="dialog" aria-labelledby="privacyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="privacyModalLabel">Privacy Policy</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="privacy-content">
                        <h6 class="text-primary mb-3">HR2 Human Resources Management System - Privacy Policy</h6>
                        
                        <p class="text-muted small mb-4">
                            <strong>Effective Date:</strong> <?php echo date('F d, Y'); ?><br>
                            <strong>Last Updated:</strong> <?php echo date('F d, Y'); ?>
                        </p>

                        <div class="mb-4">
                            <h6 class="text-dark">Our Commitment to Privacy</h6>
                            <p class="text-justify small">
                                This Privacy Policy explains how we collect, use, disclose, and protect your personal information in compliance with <strong>Republic Act No. 10173 (Data Privacy Act of 2012)</strong> and other applicable Philippine laws.
                            </p>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-dark">Information We Collect</h6>
                            <p class="text-justify small">We collect the following types of personal information:</p>
                            <ul class="small">
                                <li><strong>Personal Information:</strong> Name, employee ID, contact details, and identification documents</li>
                                <li><strong>Employment Information:</strong> Job title, department, employment history, and performance records</li>
                                <li><strong>System Usage Data:</strong> Login times, system activities, and user interactions</li>
                                <li><strong>Training Records:</strong> Competency assessments, training history, and development plans</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-dark">How We Use Your Information</h6>
                            <p class="text-justify small">We use your personal information for:</p>
                            <ul class="small">
                                <li>Human resources management and administration</li>
                                <li>Performance evaluation and competency assessment</li>
                                <li>Training and development planning</li>
                                <li>Compliance with legal and regulatory requirements</li>
                                <li>System security and fraud prevention</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-dark">Data Security</h6>
                            <p class="text-justify small">
                                We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction, in accordance with the Data Privacy Act's security requirements.
                            </p>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-dark">Your Rights</h6>
                            <p class="text-justify small">Under the Data Privacy Act, you have the right to:</p>
                            <ul class="small">
                                <li>Be informed about the processing of your personal data</li>
                                <li>Access your personal data and request corrections</li>
                                <li>Object to the processing of your personal data</li>
                                <li>Request the erasure or blocking of your personal data</li>
                                <li>Data portability and damages for violations</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-dark">Data Sharing and Disclosure</h6>
                            <p class="text-justify small">
                                We may share your personal information only with authorized personnel within the organization and third parties as required by law or with your explicit consent. We do not sell or rent your personal information to third parties.
                            </p>
                        </div>

                        <div class="alert alert-warning small">
                            <strong>Important:</strong> This Privacy Policy is subject to the provisions of Republic Act No. 10173 and other applicable Philippine laws. For any privacy concerns or to exercise your rights, please contact our Data Protection Officer.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/js/jquery.min.js"></script>
    <script src="../assets/vendor/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const btnText = loginBtn.querySelector('.btn-text');
            const resendBtn = document.getElementById('resendBtn');
            const resendField = document.getElementById('resend_otp');

            const otpField = document.getElementById('otp_code');
            if (resendBtn && resendField) {
                resendBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    resendField.value = '1';
                    if (otpField) {
                        otpField.required = false;
                    }
                    form.submit();
                });
            }
            
            // Form submission with loading state
            form.addEventListener('submit', function(e) {
                loginBtn.disabled = true;
                loginBtn.classList.add('loading');
                if (resendField && resendField.value === '1') {
                    btnText.textContent = 'Sending OTP...';
                } else {
                    btnText.textContent = 'Signing In...';
                }
            });
            
            // Enhanced form interactions
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                // Add focus/blur effects
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'translateY(-2px)';
                    this.parentElement.style.transition = 'all 0.3s ease';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'translateY(0)';
                });
                
                // Add typing animation
                input.addEventListener('input', function() {
                    if (this.value.length > 0) {
                        this.style.borderColor = '#667eea';
                    } else {
                        this.style.borderColor = '#e9ecef';
                    }
            });
        });

            // Add ripple effect to login button
            loginBtn.addEventListener('click', function(e) {
                const ripple = document.createElement('div');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.3);
                    pointer-events: none;
                    transform: scale(0);
                    animation: ripple 0.6s ease-out;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                `;
                
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
            
            // Add CSS for ripple animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(2);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
            
            // Auto-focus on username field
            const usernameField = document.getElementById('username');
            if (usernameField) {
                usernameField.focus();
            }
        });
    </script>
</body>
</html>
