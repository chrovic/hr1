<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || !$auth->hasPermission('manage_system')) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$db = getDB();

$message = '';
$error = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['update_settings'])) {
        $settings = [
            'company_name' => $_POST['company_name'],
            'company_email' => $_POST['company_email'],
            'timezone' => $_POST['timezone'],
            'evaluation_cycle_days' => $_POST['evaluation_cycle_days'],
            'training_request_approval_required' => $_POST['training_request_approval_required'],
            'max_evaluation_cycles' => $_POST['max_evaluation_cycles']
        ];
        
        try {
            foreach ($settings as $key => $value) {
                $stmt = $db->prepare("
                    INSERT INTO system_settings (setting_key, setting_value, updated_by) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                    setting_value = VALUES(setting_value), 
                    updated_by = VALUES(updated_by), 
                    updated_at = NOW()
                ");
                $stmt->execute([$key, $value, $current_user['id']]);
            }
            
            $message = 'System settings updated successfully!';
            $auth->logActivity('update_system_settings', 'system_settings', null, null, $settings);
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get current settings
$current_settings = [];
try {
    $stmt = $db->prepare("SELECT setting_key, setting_value FROM system_settings");
    $stmt->execute();
    $settings_data = $stmt->fetchAll();
    
    foreach ($settings_data as $setting) {
        $current_settings[$setting['setting_key']] = $setting['setting_value'];
    }
} catch (PDOException $e) {
    // Use default values if table doesn't exist
    $current_settings = [
        'company_name' => 'Your Company Name',
        'company_email' => 'hr@yourcompany.com',
        'timezone' => 'UTC',
        'evaluation_cycle_days' => '90',
        'training_request_approval_required' => 'true',
        'max_evaluation_cycles' => '4'
    ];
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">System Settings</h1>
</div>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">General Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label for="company_name">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" 
                               value="<?php echo htmlspecialchars($current_settings['company_name'] ?? 'Your Company Name'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="company_email">Company Email</label>
                        <input type="email" class="form-control" id="company_email" name="company_email" 
                               value="<?php echo htmlspecialchars($current_settings['company_email'] ?? 'hr@yourcompany.com'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="timezone">Timezone</label>
                        <select class="form-control" id="timezone" name="timezone">
                            <option value="UTC" <?php echo ($current_settings['timezone'] ?? 'UTC') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                            <option value="America/New_York" <?php echo ($current_settings['timezone'] ?? '') === 'America/New_York' ? 'selected' : ''; ?>>Eastern Time</option>
                            <option value="America/Chicago" <?php echo ($current_settings['timezone'] ?? '') === 'America/Chicago' ? 'selected' : ''; ?>>Central Time</option>
                            <option value="America/Denver" <?php echo ($current_settings['timezone'] ?? '') === 'America/Denver' ? 'selected' : ''; ?>>Mountain Time</option>
                            <option value="America/Los_Angeles" <?php echo ($current_settings['timezone'] ?? '') === 'America/Los_Angeles' ? 'selected' : ''; ?>>Pacific Time</option>
                        </select>
                    </div>
                    
                    <hr>
                    <h6>Evaluation Settings</h6>
                    <div class="form-group">
                        <label for="evaluation_cycle_days">Default Evaluation Cycle Duration (Days)</label>
                        <input type="number" class="form-control" id="evaluation_cycle_days" name="evaluation_cycle_days" 
                               value="<?php echo htmlspecialchars($current_settings['evaluation_cycle_days'] ?? '90'); ?>" min="1">
                    </div>
                    <div class="form-group">
                        <label for="max_evaluation_cycles">Maximum Active Evaluation Cycles</label>
                        <input type="number" class="form-control" id="max_evaluation_cycles" name="max_evaluation_cycles" 
                               value="<?php echo htmlspecialchars($current_settings['max_evaluation_cycles'] ?? '4'); ?>" min="1">
                    </div>
                    
                    <hr>
                    <h6>Training Settings</h6>
                    <div class="form-group">
                        <label for="training_request_approval_required">Training Request Approval Required</label>
                        <select class="form-control" id="training_request_approval_required" name="training_request_approval_required">
                            <option value="true" <?php echo ($current_settings['training_request_approval_required'] ?? 'true') === 'true' ? 'selected' : ''; ?>>Yes</option>
                            <option value="false" <?php echo ($current_settings['training_request_approval_required'] ?? '') === 'false' ? 'selected' : ''; ?>>No</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="update_settings" class="btn btn-primary">Save Settings</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">System Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>PHP Version:</strong><br>
                    <span class="text-muted"><?php echo PHP_VERSION; ?></span>
                </div>
                <div class="mb-3">
                    <strong>Database:</strong><br>
                    <span class="text-muted">MySQL/MariaDB</span>
                </div>
                <div class="mb-3">
                    <strong>Server:</strong><br>
                    <span class="text-muted"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
                </div>
                <div class="mb-3">
                    <strong>Last Updated:</strong><br>
                    <span class="text-muted"><?php echo date('Y-m-d H:i:s'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="card shadow mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <button type="button" class="btn btn-outline-primary btn-block mb-2" onclick="exportSettings()">
                    <i class="fe fe-download fe-16 mr-2"></i>Export Settings
                </button>
                <button type="button" class="btn btn-outline-warning btn-block mb-2" onclick="resetSettings()">
                    <i class="fe fe-refresh-cw fe-16 mr-2"></i>Reset to Defaults
                </button>
                <button type="button" class="btn btn-outline-info btn-block" onclick="viewLogs()">
                    <i class="fe fe-file-text fe-16 mr-2"></i>View System Logs
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function exportSettings() {
    // Export settings functionality
    alert('Settings export functionality would be implemented here');
}

function resetSettings() {
    if (confirm('Are you sure you want to reset all settings to default values?')) {
        // Reset settings functionality
        alert('Settings reset functionality would be implemented here');
    }
}

function viewLogs() {
    window.location.href = '?page=system_logs';
}
</script>
