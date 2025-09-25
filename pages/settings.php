<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';

$auth = new SimpleAuth();
$current_user = $auth->getCurrentUser();
$db = getDB();

$message = '';
$error = '';

// Handle settings updates
if ($_POST) {
    if (isset($_POST['update_notifications'])) {
        $notificationSettings = [
            'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
            'sms_notifications' => isset($_POST['sms_notifications']) ? 1 : 0,
            'push_notifications' => isset($_POST['push_notifications']) ? 1 : 0,
            'training_reminders' => isset($_POST['training_reminders']) ? 1 : 0,
            'evaluation_reminders' => isset($_POST['evaluation_reminders']) ? 1 : 0,
            'announcement_notifications' => isset($_POST['announcement_notifications']) ? 1 : 0
        ];
        
        try {
            // Store in user preferences (you might want to create a user_preferences table)
            $stmt = $db->prepare("UPDATE users SET preferences = ?, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([json_encode($notificationSettings), $current_user['id']]);
            
            if ($result) {
                $message = 'Notification settings updated successfully!';
                $auth->logActivity('update_notifications', 'users', $current_user['id'], null, $notificationSettings);
            } else {
                $error = 'Failed to update notification settings.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
    
    if (isset($_POST['update_preferences'])) {
        $preferences = [
            'theme' => $_POST['theme'],
            'language' => $_POST['language'],
            'timezone' => $_POST['timezone'],
            'date_format' => $_POST['date_format'],
            'items_per_page' => $_POST['items_per_page']
        ];
        
        try {
            $stmt = $db->prepare("UPDATE users SET preferences = ?, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([json_encode($preferences), $current_user['id']]);
            
            if ($result) {
                $message = 'Preferences updated successfully!';
                $auth->logActivity('update_preferences', 'users', $current_user['id'], null, $preferences);
            } else {
                $error = 'Failed to update preferences.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get current preferences
$preferences = json_decode($current_user['preferences'] ?? '{}', true);
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
    <h1 class="h2">Settings</h1>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="notifications-tab" data-toggle="tab" href="#notifications" role="tab" aria-controls="notifications" aria-selected="true">
                            <i class="fe fe-bell fe-16 mr-2"></i>Notifications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="preferences-tab" data-toggle="tab" href="#preferences" role="tab" aria-controls="preferences" aria-selected="false">
                            <i class="fe fe-settings fe-16 mr-2"></i>Preferences
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="settingsTabsContent">
                    <!-- Notifications Tab -->
                    <div class="tab-pane fade show active" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
                        <form method="POST">
                            <h5 class="mb-4">Notification Preferences</h5>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="email_notifications" name="email_notifications" <?php echo ($preferences['email_notifications'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="email_notifications">
                                        <strong>Email Notifications</strong>
                                        <div class="text-muted small">Receive notifications via email</div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="sms_notifications" name="sms_notifications" <?php echo ($preferences['sms_notifications'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="sms_notifications">
                                        <strong>SMS Notifications</strong>
                                        <div class="text-muted small">Receive notifications via SMS</div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="push_notifications" name="push_notifications" <?php echo ($preferences['push_notifications'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="push_notifications">
                                        <strong>Push Notifications</strong>
                                        <div class="text-muted small">Receive browser push notifications</div>
                                    </label>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <h6 class="mb-3">Specific Notifications</h6>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="training_reminders" name="training_reminders" <?php echo ($preferences['training_reminders'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="training_reminders">
                                        <strong>Training Reminders</strong>
                                        <div class="text-muted small">Get reminded about upcoming training sessions</div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="evaluation_reminders" name="evaluation_reminders" <?php echo ($preferences['evaluation_reminders'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="evaluation_reminders">
                                        <strong>Evaluation Reminders</strong>
                                        <div class="text-muted small">Get reminded about pending evaluations</div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="announcement_notifications" name="announcement_notifications" <?php echo ($preferences['announcement_notifications'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="announcement_notifications">
                                        <strong>Announcement Notifications</strong>
                                        <div class="text-muted small">Get notified about company announcements</div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" name="update_notifications" class="btn btn-primary">
                                    <i class="fe fe-save fe-16 mr-2"></i>Save Notification Settings
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Preferences Tab -->
                    <div class="tab-pane fade" id="preferences" role="tabpanel" aria-labelledby="preferences-tab">
                        <form method="POST">
                            <h5 class="mb-4">General Preferences</h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Theme</label>
                                        <select class="form-control" name="theme">
                                            <option value="light" <?php echo ($preferences['theme'] ?? 'light') === 'light' ? 'selected' : ''; ?>>Light</option>
                                            <option value="dark" <?php echo ($preferences['theme'] ?? 'light') === 'dark' ? 'selected' : ''; ?>>Dark</option>
                                            <option value="auto" <?php echo ($preferences['theme'] ?? 'light') === 'auto' ? 'selected' : ''; ?>>Auto</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Language</label>
                                        <select class="form-control" name="language">
                                            <option value="en" <?php echo ($preferences['language'] ?? 'en') === 'en' ? 'selected' : ''; ?>>English</option>
                                            <option value="es" <?php echo ($preferences['language'] ?? 'en') === 'es' ? 'selected' : ''; ?>>Spanish</option>
                                            <option value="fr" <?php echo ($preferences['language'] ?? 'en') === 'fr' ? 'selected' : ''; ?>>French</option>
                                            <option value="de" <?php echo ($preferences['language'] ?? 'en') === 'de' ? 'selected' : ''; ?>>German</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Timezone</label>
                                        <select class="form-control" name="timezone">
                                            <option value="UTC" <?php echo ($preferences['timezone'] ?? 'UTC') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                            <option value="America/New_York" <?php echo ($preferences['timezone'] ?? 'UTC') === 'America/New_York' ? 'selected' : ''; ?>>Eastern Time</option>
                                            <option value="America/Chicago" <?php echo ($preferences['timezone'] ?? 'UTC') === 'America/Chicago' ? 'selected' : ''; ?>>Central Time</option>
                                            <option value="America/Denver" <?php echo ($preferences['timezone'] ?? 'UTC') === 'America/Denver' ? 'selected' : ''; ?>>Mountain Time</option>
                                            <option value="America/Los_Angeles" <?php echo ($preferences['timezone'] ?? 'UTC') === 'America/Los_Angeles' ? 'selected' : ''; ?>>Pacific Time</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Date Format</label>
                                        <select class="form-control" name="date_format">
                                            <option value="Y-m-d" <?php echo ($preferences['date_format'] ?? 'Y-m-d') === 'Y-m-d' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                                            <option value="m/d/Y" <?php echo ($preferences['date_format'] ?? 'Y-m-d') === 'm/d/Y' ? 'selected' : ''; ?>>MM/DD/YYYY</option>
                                            <option value="d/m/Y" <?php echo ($preferences['date_format'] ?? 'Y-m-d') === 'd/m/Y' ? 'selected' : ''; ?>>DD/MM/YYYY</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Items Per Page</label>
                                        <select class="form-control" name="items_per_page">
                                            <option value="10" <?php echo ($preferences['items_per_page'] ?? '10') === '10' ? 'selected' : ''; ?>>10</option>
                                            <option value="25" <?php echo ($preferences['items_per_page'] ?? '10') === '25' ? 'selected' : ''; ?>>25</option>
                                            <option value="50" <?php echo ($preferences['items_per_page'] ?? '10') === '50' ? 'selected' : ''; ?>>50</option>
                                            <option value="100" <?php echo ($preferences['items_per_page'] ?? '10') === '100' ? 'selected' : ''; ?>>100</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" name="update_preferences" class="btn btn-primary">
                                    <i class="fe fe-save fe-16 mr-2"></i>Save Preferences
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">Account Information</h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($current_user['username']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <input type="text" class="form-control" value="<?php echo ucfirst($current_user['role']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Member Since</label>
                    <input type="text" class="form-control" value="<?php echo date('F j, Y', strtotime($current_user['created_at'])); ?>" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Updated</label>
                    <input type="text" class="form-control" value="<?php echo date('F j, Y', strtotime($current_user['updated_at'] ?? $current_user['created_at'])); ?>" readonly>
                </div>
            </div>
        </div>
        
        <div class="card shadow mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Security</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Keep your account secure by regularly updating your password and reviewing your login activity.</p>
                <a href="?page=profile" class="btn btn-outline-primary btn-block">
                    <i class="fe fe-key fe-16 mr-2"></i>Change Password
                </a>
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