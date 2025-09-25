<?php
session_start();
require_once '../includes/data/db.php';
require_once '../includes/functions/simple_auth.php';

$auth = new SimpleAuth();
$error = '';
$success = '';

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

if ($_POST) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $result = $auth->login($username, $password, false);
        
        if ($result === true) {
            // Login successful
            header('Location: ../index.php');
            exit;
        } else {
            // Login failed
            $error = 'Invalid username or password. Please try again.';
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
    <link rel="icon" href="../assets/images/favicon.ico">
    <title>HR1 - Human Resources Management System</title>
    
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="../assets/vendor/css/simplebar.css">
    <link rel="stylesheet" href="../assets/vendor/css/feather.css">
    <link rel="stylesheet" href="../assets/vendor/css/app-light.css">
    
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
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
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
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fe fe-briefcase"></i>
                </div>
                <h1>HR1 System</h1>
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

                <button type="submit" class="btn btn-login" id="loginBtn">
                    <span class="btn-text">Sign In</span>
                </button>
            </form>

        </div>
    </div>

    <script src="../assets/vendor/js/jquery.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const btnText = loginBtn.querySelector('.btn-text');
            
            // Form submission with loading state
            form.addEventListener('submit', function(e) {
                loginBtn.disabled = true;
                loginBtn.classList.add('loading');
                btnText.textContent = 'Signing In...';
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
            document.getElementById('username').focus();
        });
    </script>
</body>
</html>