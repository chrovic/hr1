<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="HR2 - Two-Factor Authentication">
    <meta name="author" content="">
    <link rel="icon" href="../assets/images/favicon.ico">
    <title>HR2 - Two-Factor Authentication</title>
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="../assets/vendor/css/simplebar.css">
    <link rel="stylesheet" href="../assets/vendor/css/feather.css">
    <link rel="stylesheet" href="../assets/vendor/css/app-light.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/hr-main.css">
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            color: #333;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .login-header p {
            color: #666;
            margin: 0;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-control {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            text-align: center;
            letter-spacing: 0.5em;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            width: 100%;
            transition: transform 0.2s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            color: white;
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .qr-code {
            text-align: center;
            margin-bottom: 1rem;
        }
        .qr-code img {
            max-width: 200px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .back-link {
            text-align: center;
            margin-top: 1rem;
        }
        .back-link a {
            color: #667eea;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Two-Factor Authentication</h1>
                <p>Enter the 6-digit code from your authenticator app</p>
            </div>

            <?php
require_once '../includes/data/db.php';
require_once '../includes/functions/auth.php';
            
            $auth = new Auth();
            $error = '';
            
            // Check if user is logged in
            if (!$auth->isLoggedIn() && !isset($_SESSION['pending_2fa'])) {
                header('Location: login.php');
                exit;
            }
            
            if ($_POST) {
                $code = $_POST['code'] ?? '';
                
                if (empty($code)) {
                    $error = 'Please enter the verification code.';
                } elseif (strlen($code) !== 6 || !is_numeric($code)) {
                    $error = 'Please enter a valid 6-digit code.';
                } else {
                    $user_id = $_SESSION['user_id'];
                    
        if ($auth->verify2FA($user_id, $code)) {
            unset($_SESSION['pending_2fa']);
            header('Location: ../index.php');
            exit;
                    } else {
                        $error = 'Invalid verification code. Please try again.';
                    }
                }
            }
            ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="code" class="form-label">Verification Code</label>
                    <input type="text" class="form-control" id="code" name="code" maxlength="6" placeholder="000000" required>
                </div>

                <button type="submit" class="btn btn-login">Verify</button>
            </form>

            <div class="back-link">
                <a href="auth/login.php">← Back to Login</a>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/js/jquery.min.js"></script>
    <script>
        // Auto-focus on code input
        document.getElementById('code').focus();
        
        // Auto-submit when 6 digits are entered
        document.getElementById('code').addEventListener('input', function() {
            if (this.value.length === 6) {
                this.form.submit();
            }
        });
        
        // Only allow numbers
        document.getElementById('code').addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key)) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
