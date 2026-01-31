<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/employee.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$db = getDB();

$message = '';
$error = '';

// Handle profile update
if ($_POST && isset($_POST['update_profile'])) {
    $profileData = [
        'first_name' => trim($_POST['first_name']),
        'last_name' => trim($_POST['last_name']),
        'email' => trim($_POST['email']),
        'phone' => trim($_POST['phone']),
        'department' => trim($_POST['department']),
        'position' => trim($_POST['position'])
    ];
    
    $employeeManager = new EmployeeManager();
    if ($employeeManager->updateEmployeeProfile($current_user['id'], $profileData)) {
        $message = 'Profile updated successfully!';
        $auth->logActivity('update_profile', 'users', $current_user['id'], null, $profileData);
        // Refresh user data
        $current_user = $auth->getCurrentUser();
    } else {
        $error = 'Failed to update profile.';
    }
}

// Calculate profile completeness
$profileFields = ['first_name', 'last_name', 'email', 'phone', 'department', 'position'];
$completedFields = 0;
foreach ($profileFields as $field) {
    if (!empty($current_user[$field])) {
        $completedFields++;
    }
}
$profileCompleteness = round(($completedFields / count($profileFields)) * 100);
?>

<!-- Enhanced CSS for Employee Profile -->
<style>
.profile-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px;
    padding: 3rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}

.profile-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
    animation: float 30s infinite linear;
}

@keyframes float {
    0% { transform: translateX(-100px) translateY(-100px) rotate(0deg); }
    100% { transform: translateX(100px) translateY(100px) rotate(360deg); }
}

.profile-avatar {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: bold;
    color: #333;
    border: 6px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
    position: relative;
    z-index: 2;
    margin: 0 auto 2rem;
}

.profile-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.05);
    overflow: hidden;
    position: relative;
}

.profile-card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 2rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    position: relative;
}

.profile-card-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2);
}

.profile-form-group {
    margin-bottom: 2rem;
}

.profile-form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
    display: block;
}

.profile-form-control {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.profile-form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    background: white;
}

.profile-completeness {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    text-align: center;
    position: relative;
    overflow: hidden;
}

.profile-completeness::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #28a745, #20c997);
}

.completeness-circle {
    width: 100px;
    height: 100px;
    margin: 0 auto 1rem;
    position: relative;
}

.completeness-circle svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.completeness-circle circle {
    fill: none;
    stroke-width: 8;
}

.completeness-bg {
    stroke: #e9ecef;
}

.completeness-fill {
    stroke: #28a745;
    stroke-linecap: round;
    transition: stroke-dashoffset 0.8s ease;
}

.profile-info-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.profile-info-item {
    display: flex;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.profile-info-item:last-child {
    border-bottom: none;
}

.profile-info-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 1.2rem;
}

.profile-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 50px;
    padding: 1rem 3rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.profile-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
}

.profile-btn-secondary {
    background: transparent;
    border: 2px solid #6c757d;
    border-radius: 50px;
    padding: 1rem 3rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
}

.profile-btn-secondary:hover {
    background: #6c757d;
    color: white;
    transform: translateY(-2px);
}

body.dark .profile-card,
.dark .profile-card,
body.dark .profile-completeness,
.dark .profile-completeness,
body.dark .profile-info-card,
.dark .profile-info-card {
    background: #1e2428;
    border-color: rgba(255, 255, 255, 0.08);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
    color: #e9ecef;
}

body.dark .profile-card-header,
.dark .profile-card-header {
    background: linear-gradient(135deg, #232b30 0%, #1d2428 100%);
    border-bottom-color: rgba(255, 255, 255, 0.08);
}

body.dark .profile-form-label,
.dark .profile-form-label {
    color: rgba(255, 255, 255, 0.85);
}

body.dark .profile-form-control,
.dark .profile-form-control {
    background: rgba(255, 255, 255, 0.06);
    border-color: rgba(255, 255, 255, 0.12);
    color: #ffffff;
}

body.dark .profile-form-control:focus,
.dark .profile-form-control:focus {
    background: rgba(255, 255, 255, 0.1);
}

body.dark .profile-info-item,
.dark .profile-info-item {
    border-bottom-color: rgba(255, 255, 255, 0.08);
}

@media (max-width: 768px) {
    .profile-hero {
        padding: 2rem;
        text-align: center;
    }
    
    .profile-avatar {
        width: 100px;
        height: 100px;
        font-size: 2.5rem;
    }
    
    .profile-card-header,
    .profile-completeness,
    .profile-info-card {
        padding: 1.5rem;
    }
}
</style>

<!-- Success/Error Messages -->
<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fe fe-check-circle fe-16 mr-2"></i><?php echo htmlspecialchars($message); ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fe fe-alert-circle fe-16 mr-2"></i><?php echo htmlspecialchars($error); ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Profile Hero Section -->
<div class="profile-hero text-center">
    <div class="profile-avatar">
        <?php echo strtoupper(substr($current_user['first_name'] ?? 'E', 0, 1) . substr($current_user['last_name'] ?? 'M', 0, 1)); ?>
    </div>
    <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">
        <?php echo htmlspecialchars(($current_user['first_name'] ?? 'Employee') . ' ' . ($current_user['last_name'] ?? 'Name')); ?>
    </h1>
    <p style="font-size: 1.2rem; opacity: 0.9; margin-bottom: 0.5rem;">
        <i class="fe fe-briefcase fe-16 mr-2"></i>
        <?php echo htmlspecialchars($current_user['position'] ?? 'Employee'); ?>
    </p>
    <p style="font-size: 1.1rem; opacity: 0.8;">
        <i class="fe fe-users fe-16 mr-2"></i>
        <?php echo htmlspecialchars($current_user['department'] ?? 'Department'); ?>
    </p>
</div>

<!-- Main Content -->
<div class="row">
    <!-- Profile Form -->
    <div class="col-lg-8 mb-4">
        <div class="profile-card">
            <div class="profile-card-header">
                <h4 class="mb-0">
                    <i class="fe fe-user text-primary mr-3"></i>Personal Information
                </h4>
                <p class="text-muted mb-0 mt-2">Update your personal details and contact information</p>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label" for="first_name">First Name *</label>
                                <input type="text" class="form-control profile-form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($current_user['first_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label" for="last_name">Last Name *</label>
                                <input type="text" class="form-control profile-form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($current_user['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-form-group">
                        <label class="profile-form-label" for="email">Email Address *</label>
                        <input type="email" class="form-control profile-form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($current_user['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="profile-form-group">
                        <label class="profile-form-label" for="phone">Phone Number</label>
                        <input type="text" class="form-control profile-form-control" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($current_user['phone'] ?? ''); ?>" 
                               placeholder="e.g., +63 912 345 6789">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label" for="department">Department</label>
                                <input type="text" class="form-control profile-form-control" id="department" name="department" 
                                       value="<?php echo htmlspecialchars($current_user['department'] ?? ''); ?>" 
                                       placeholder="e.g., Marketing, IT, Sales">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label" for="position">Position</label>
                                <input type="text" class="form-control profile-form-control" id="position" name="position" 
                                       value="<?php echo htmlspecialchars($current_user['position'] ?? ''); ?>" 
                                       placeholder="e.g., Software Developer, Manager">
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" name="update_profile" class="profile-btn-primary mr-3">
                            <i class="fe fe-save mr-2"></i>Update Profile
                        </button>
                        <a href="?page=dashboard" class="btn profile-btn-secondary">
                            <i class="fe fe-arrow-left mr-2"></i>Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Profile Sidebar -->
    <div class="col-lg-4">
        <!-- Profile Completeness -->
        <div class="profile-completeness mb-4">
            <h5 class="mb-3">Profile Completeness</h5>
            <div class="completeness-circle">
                <svg>
                    <circle class="completeness-bg" cx="50" cy="50" r="40"></circle>
                    <circle class="completeness-fill" cx="50" cy="50" r="40" 
                            style="stroke-dasharray: <?php echo 2 * pi() * 40; ?>; 
                                   stroke-dashoffset: <?php echo 2 * pi() * 40 * (1 - $profileCompleteness / 100); ?>;"></circle>
                </svg>
                <div class="position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">
                    <h3 class="mb-0"><?php echo $profileCompleteness; ?>%</h3>
                </div>
            </div>
            <p class="text-muted mb-0">
                <?php if ($profileCompleteness >= 80): ?>
                    <i class="fe fe-check-circle text-success mr-1"></i>Your profile is complete!
                <?php elseif ($profileCompleteness >= 60): ?>
                    <i class="fe fe-alert-circle text-warning mr-1"></i>Almost there! Add more details.
                <?php else: ?>
                    <i class="fe fe-info text-info mr-1"></i>Please complete your profile.
                <?php endif; ?>
            </p>
        </div>
        
        <!-- Profile Information -->
        <div class="profile-info-card">
            <h5 class="mb-3">
                <i class="fe fe-info text-primary mr-2"></i>Profile Information
            </h5>
            
            <div class="profile-info-item">
                <div class="profile-info-icon" style="background: rgba(102, 126, 234, 0.1); color: #667eea;">
                    <i class="fe fe-user"></i>
                </div>
                <div>
                    <h6 class="mb-0">Employee ID</h6>
                    <small class="text-muted"><?php echo htmlspecialchars($current_user['employee_id'] ?? 'Not assigned'); ?></small>
                </div>
            </div>
            
            <div class="profile-info-item">
                <div class="profile-info-icon" style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                    <i class="fe fe-calendar"></i>
                </div>
                <div>
                    <h6 class="mb-0">Hire Date</h6>
                    <small class="text-muted">
                        <?php echo $current_user['hire_date'] ? date('M d, Y', strtotime($current_user['hire_date'])) : 'Not specified'; ?>
                    </small>
                </div>
            </div>
            
            <div class="profile-info-item">
                <div class="profile-info-icon" style="background: rgba(23, 162, 184, 0.1); color: #17a2b8;">
                    <i class="fe fe-shield"></i>
                </div>
                <div>
                    <h6 class="mb-0">Account Status</h6>
                    <small class="text-success">
                        <i class="fe fe-check-circle mr-1"></i><?php echo ucfirst($current_user['status'] ?? 'active'); ?>
                    </small>
                </div>
            </div>
            
            <div class="profile-info-item">
                <div class="profile-info-icon" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
                    <i class="fe fe-clock"></i>
                </div>
                <div>
                    <h6 class="mb-0">Last Updated</h6>
                    <small class="text-muted">
                        <?php echo $current_user['updated_at'] ? date('M d, Y g:i A', strtotime($current_user['updated_at'])) : 'Never'; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Profile page interactions
document.addEventListener('DOMContentLoaded', function() {
    // Animate completeness circle
    const completenessCircle = document.querySelector('.completeness-fill');
    if (completenessCircle) {
        const percentage = <?php echo $profileCompleteness; ?>;
        const circumference = 2 * Math.PI * 40;
        const offset = circumference - (percentage / 100) * circumference;
        
        setTimeout(() => {
            completenessCircle.style.strokeDashoffset = offset;
        }, 500);
    }
    
    // Add focus animations to form inputs
    document.querySelectorAll('.profile-form-control').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateY(-2px)';
            this.parentElement.style.transition = 'all 0.3s ease';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'translateY(0)';
        });
    });
    
    // Add hover effects to info items
    document.querySelectorAll('.profile-info-item').forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
            this.style.transition = 'all 0.3s ease';
            this.style.background = 'rgba(102, 126, 234, 0.05)';
            this.style.borderRadius = '10px';
            this.style.margin = '0 -1rem';
            this.style.padding = '1rem';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
            this.style.background = 'transparent';
            this.style.margin = '0';
            this.style.padding = '1rem 0';
        });
    });
});
</script>
