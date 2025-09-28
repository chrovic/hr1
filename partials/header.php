<nav class="topnav navbar navbar-light">
    <button type="button" class="navbar-toggler text-muted mt-2 p-0 mr-3 collapseSidebar">
        <i class="fe fe-menu navbar-toggler-icon"></i>
    </button>
    <form class="form-inline mr-auto searchform text-muted">
        <input class="form-control mr-sm-2 bg-transparent border-0 pl-4 text-muted" type="search" placeholder="Search employees, departments..." aria-label="Search">
    </form>
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link text-muted my-2" href="#" id="modeSwitcher" data-mode="light">
                <i class="fe fe-sun fe-16"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-muted my-2" href="#" data-toggle="modal" data-target=".modal-shortcut">
                <span class="fe fe-grid fe-16"></span>
            </a>
        </li>
        <li class="nav-item nav-notif">
            <?php include 'partials/notification_dropdown_fixed.php'; ?>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-muted pr-0" href="#" id="profileDropdown" role="button" aria-haspopup="true" aria-expanded="false">
                <span class="avatar avatar-sm mt-2">
                    <img src="assets/images/avatars/face-1.jpg" alt="<?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?>" class="avatar-img rounded-circle">
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" id="profileDropdownMenu" aria-labelledby="profileDropdown">
                <div class="dropdown-header">
                    <strong><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></strong>
                    <div class="text-muted small"><?php echo ucfirst($current_user['role']); ?></div>
                </div>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="?page=profile">
                    <i class="fe fe-user fe-16 mr-2"></i>Profile
                </a>
                <a class="dropdown-item" href="?page=settings">
                    <i class="fe fe-settings fe-16 mr-2"></i>Settings
                </a>
                <a class="dropdown-item" href="?page=activities">
                    <i class="fe fe-activity fe-16 mr-2"></i>Activities
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="auth/logout.php">
                    <i class="fe fe-log-out fe-16 mr-2"></i>Logout
                </a>
            </div>
        </li>
    </ul>
</nav>

<script>
// Custom dropdown handling without Bootstrap conflicts
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing dropdowns...');
    
    // Handle profile dropdown
    const profileDropdown = document.getElementById('profileDropdown');
    const profileMenu = document.getElementById('profileDropdownMenu');
    
    console.log('Profile dropdown element:', profileDropdown);
    console.log('Profile menu element:', profileMenu);
    
    if (profileDropdown && profileMenu) {
        console.log('Adding profile dropdown event listener');
        profileDropdown.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Profile dropdown clicked');
            
            // Close notification dropdown
            const notificationMenu = document.getElementById('notificationDropdownMenu');
            if (notificationMenu) {
                notificationMenu.classList.remove('show');
                document.getElementById('notificationDropdown').setAttribute('aria-expanded', 'false');
            }
            
            // Toggle profile dropdown
            const isOpen = profileMenu.classList.contains('show');
            if (isOpen) {
                profileMenu.classList.remove('show');
                profileDropdown.setAttribute('aria-expanded', 'false');
                console.log('Profile dropdown closed');
            } else {
                profileMenu.classList.add('show');
                profileDropdown.setAttribute('aria-expanded', 'true');
                console.log('Profile dropdown opened');
            }
        });
    } else {
        console.error('Profile dropdown elements not found!');
        console.error('profileDropdown:', profileDropdown);
        console.error('profileMenu:', profileMenu);
    }
    
    // Handle notification dropdown
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationMenu = document.getElementById('notificationDropdownMenu');
    
    if (notificationDropdown && notificationMenu) {
        notificationDropdown.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Notification dropdown clicked');
            
            // Close profile dropdown
            if (profileMenu) {
                profileMenu.classList.remove('show');
                profileDropdown.setAttribute('aria-expanded', 'false');
            }
            
            // Toggle notification dropdown
            const isOpen = notificationMenu.classList.contains('show');
            if (isOpen) {
                notificationMenu.classList.remove('show');
                notificationDropdown.setAttribute('aria-expanded', 'false');
                console.log('Notification dropdown closed');
            } else {
                notificationMenu.classList.add('show');
                notificationDropdown.setAttribute('aria-expanded', 'true');
                console.log('Notification dropdown opened');
            }
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        const isProfileClick = profileDropdown && profileDropdown.contains(e.target);
        const isNotificationClick = notificationDropdown && notificationDropdown.contains(e.target);
        const isProfileMenuClick = profileMenu && profileMenu.contains(e.target);
        const isNotificationMenuClick = notificationMenu && notificationMenu.contains(e.target);
        
        if (!isProfileClick && !isNotificationClick && !isProfileMenuClick && !isNotificationMenuClick) {
            // Close both dropdowns
            if (profileMenu) {
                profileMenu.classList.remove('show');
                profileDropdown.setAttribute('aria-expanded', 'false');
            }
            if (notificationMenu) {
                notificationMenu.classList.remove('show');
                notificationDropdown.setAttribute('aria-expanded', 'false');
            }
            console.log('Dropdowns closed by outside click');
        }
    });
    
    console.log('Dropdown initialization complete');
    
    // Test profile dropdown with a simple approach
    setTimeout(function() {
        const testProfileDropdown = document.getElementById('profileDropdown');
        if (testProfileDropdown) {
            console.log('Profile dropdown found, adding test click handler');
            testProfileDropdown.onclick = function(e) {
                e.preventDefault();
                console.log('Profile dropdown clicked via test handler');
                const menu = document.getElementById('profileDropdownMenu');
                if (menu) {
                    menu.classList.toggle('show');
                    console.log('Profile menu toggled');
                }
            };
        } else {
            console.error('Profile dropdown still not found after timeout');
        }
    }, 1000);
});
</script>
