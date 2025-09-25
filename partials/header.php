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
            <a class="nav-link text-muted my-2" href="#" data-toggle="modal" data-target=".modal-notif">
                <span class="fe fe-bell fe-16"></span>
                <span class="dot dot-md bg-success"></span>
            </a>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-muted pr-0" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="toggleUserDropdown(event)">
                <span class="avatar avatar-sm mt-2">
                    <img src="assets/images/avatars/face-1.jpg" alt="<?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?>" class="avatar-img rounded-circle">
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" id="userDropdownMenu" aria-labelledby="navbarDropdownMenuLink">
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
function toggleUserDropdown(event) {
    event.preventDefault();
    event.stopPropagation();
    
    console.log('User dropdown clicked!');
    
    const dropdownMenu = document.getElementById('userDropdownMenu');
    const dropdownToggle = document.getElementById('navbarDropdownMenuLink');
    
    if (dropdownMenu && dropdownToggle) {
        const isOpen = dropdownMenu.classList.contains('show');
        
        // Close all other dropdowns first
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            if (menu !== dropdownMenu) {
                menu.classList.remove('show');
                const prevToggle = menu.previousElementSibling;
                if (prevToggle) {
                    prevToggle.setAttribute('aria-expanded', 'false');
                }
            }
        });
        
        if (isOpen) {
            dropdownMenu.classList.remove('show');
            dropdownToggle.setAttribute('aria-expanded', 'false');
            console.log('Closing user dropdown');
        } else {
            dropdownMenu.classList.add('show');
            dropdownToggle.setAttribute('aria-expanded', 'true');
            console.log('Opening user dropdown');
        }
    } else {
        console.error('Dropdown elements not found!');
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dropdownMenu = document.getElementById('userDropdownMenu');
    const dropdownToggle = document.getElementById('navbarDropdownMenuLink');
    
    if (dropdownMenu && dropdownToggle) {
        if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.remove('show');
            dropdownToggle.setAttribute('aria-expanded', 'false');
        }
    }
});
</script>
