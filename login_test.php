<?php
session_start();
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';

$auth = new SimpleAuth();
$message = '';
$error = '';

// Handle login
if ($_POST && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if ($auth->login($username, $password)) {
        $message = 'Login successful!';
        header('Location: index.php?page=employee_training_requests');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}

// Check current session status
$isLoggedIn = $auth->isLoggedIn();
$currentUser = $auth->getCurrentUser();
$userRole = $auth->getUserRole();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Test - HR2 System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>HR2 System - Login Test</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <h5>Session Status:</h5>
                        <ul>
                            <li><strong>Logged In:</strong> <?php echo $isLoggedIn ? 'Yes' : 'No'; ?></li>
                            <li><strong>User Role:</strong> <?php echo $userRole ?: 'None'; ?></li>
                            <li><strong>Current User:</strong> <?php echo $currentUser ? $currentUser['username'] : 'None'; ?></li>
                        </ul>
                        
                        <?php if (!$isLoggedIn): ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary">Login</button>
                        </form>
                        
                        <hr>
                        <h6>Test Users:</h6>
                        <ul>
                            <li><strong>Admin:</strong> admin / password</li>
                            <li><strong>HR Manager:</strong> hrmanager / password</li>
                            <li><strong>Employee:</strong> john.doe / password</li>
                        </ul>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <p>You are logged in as: <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong></p>
                            <p>Role: <strong><?php echo htmlspecialchars($currentUser['role']); ?></strong></p>
                        </div>
                        <a href="index.php?page=employee_training_requests" class="btn btn-success">Go to Employee Training Requests</a>
                        <a href="?logout=1" class="btn btn-secondary">Logout</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// Handle logout
if (isset($_GET['logout'])) {
    $auth->logout();
    header('Location: login_test.php');
    exit;
}
?>
