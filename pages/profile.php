<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';

$auth = new SimpleAuth();
$current_user = $auth->getCurrentUser();
$db = getDB();

$message = '';
$error = '';

// Handle profile updates
if ($_POST) {
    if (isset($_POST['update_profile'])) {
        $updateData = [
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'department' => $_POST['department'],
            'position' => $_POST['position'],
            'bio' => $_POST['bio']
        ];
        
        try {
            $stmt = $db->prepare("
                UPDATE users SET 
                    first_name = ?, last_name = ?, email = ?, phone = ?, 
                    department = ?, position = ?, bio = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $result = $stmt->execute([
                $updateData['first_name'],
                $updateData['last_name'],
                $updateData['email'],
                $updateData['phone'],
                $updateData['department'],
                $updateData['position'],
                $updateData['bio'],
                $current_user['id']
            ]);
            
            if ($result) {
                $message = 'Profile updated successfully!';
                $auth->logActivity('update_profile', 'users', $current_user['id'], null, $updateData);
                // Refresh user data
                $current_user = $auth->getCurrentUser();
            } else {
                $error = 'Failed to update profile.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if (!password_verify($current_password, $current_user['password_hash'])) {
            $error = 'Current password is incorrect.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters long.';
        } else {
            try {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
                $result = $stmt->execute([$hashed_password, $current_user['id']]);
                
                if ($result) {
                    $message = 'Password changed successfully!';
                    $auth->logActivity('change_password', 'users', $current_user['id'], null, null);
                } else {
                    $error = 'Failed to change password.';
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
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

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">My Profile</h1>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow">
            <div class="card-body text-center">
                <div class="avatar avatar-xl mb-3">
                    <img src="assets/images/avatars/face-1.jpg" alt="<?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?>" class="avatar-img rounded-circle">
                </div>
                <h4 class="card-title"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></h4>
                <p class="text-muted"><?php echo htmlspecialchars($current_user['position']); ?></p>
                <p class="text-muted"><?php echo htmlspecialchars($current_user['department']); ?></p>
                <div class="mt-3">
                    <span class="badge badge-primary"><?php echo ucfirst($current_user['role']); ?></span>
                </div>
            </div>
        </div>
        
        <div class="card shadow mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Stats</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-right">
                            <h6 class="text-muted">Member Since</h6>
                            <h4><?php echo date('M Y', strtotime($current_user['created_at'])); ?></h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <h6 class="text-muted">Last Login</h6>
                        <h4><?php echo date('M j', strtotime($current_user['last_login'] ?? $current_user['created_at'])); ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="true">
                            <i class="fe fe-user fe-16 mr-2"></i>Profile Information
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="password-tab" data-toggle="tab" href="#password" role="tab" aria-controls="password" aria-selected="false">
                            <i class="fe fe-lock fe-16 mr-2"></i>Change Password
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="profileTabsContent">
                    <!-- Profile Information Tab -->
                    <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">First Name *</label>
                                        <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($current_user['first_name']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Last Name *</label>
                                        <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($current_user['last_name']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($current_user['email']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($current_user['phone'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Department</label>
                                        <input type="text" class="form-control" name="department" value="<?php echo htmlspecialchars($current_user['department'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Position</label>
                                        <input type="text" class="form-control" name="position" value="<?php echo htmlspecialchars($current_user['position'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-12">
                    <div class="form-group">
                                        <label class="form-label">Bio</label>
                                        <textarea class="form-control" name="bio" rows="4" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($current_user['bio'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                    </div>
                    <div class="form-group">
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fe fe-save fe-16 mr-2"></i>Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Change Password Tab -->
                    <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Current Password *</label>
                                        <input type="password" class="form-control" name="current_password" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">New Password *</label>
                                        <input type="password" class="form-control" name="new_password" required minlength="6">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Confirm New Password *</label>
                                        <input type="password" class="form-control" name="confirm_password" required minlength="6">
                                    </div>
                                </div>
                    </div>
                    <div class="form-group">
                                <button type="submit" name="change_password" class="btn btn-warning">
                                    <i class="fe fe-key fe-16 mr-2"></i>Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabLinks = document.querySelectorAll('[data-toggle="tab"]');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs and panes
            document.querySelectorAll('.nav-link').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('show', 'active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Show corresponding pane
            const targetId = this.getAttribute('href');
            const targetPane = document.querySelector(targetId);
            if (targetPane) {
                targetPane.classList.add('show', 'active');
            }
        });
    });
});
</script>