cond<aside class="sidebar-left border-right bg-white shadow" id="leftSidebar" data-simplebar>
    <a href="#" class="btn collapseSidebar toggle-btn d-lg-none text-muted ml-2 mt-3" data-toggle="toggle">
        <i class="fe fe-x"><span class="sr-only"></span></i>
    </a>
    <nav class="vertnav navbar navbar-light">
        <!-- nav bar -->
        <div class="w-100 mb-4 d-flex">
            <a class="navbar-brand mx-auto mt-2 flex-fill text-center" href="index.php">
                <svg version="1.1" id="logo" class="navbar-brand-img brand-sm" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 120 120" xml:space="preserve">
                    <g>
                        <polygon class="st0" points="78,105 15,105 24,87 87,87 	" />
                        <polygon class="st0" points="96,69 33,69 42,51 105,51 	" />
                        <polygon class="st0" points="78,33 15,33 24,15 87,15 	" />
                    </g>
                </svg>
                <span class="text-primary font-weight-bold">HR1</span>
            </a>
        </div>
        <ul class="navbar-nav flex-fill w-100 mb-2">
            <li class="nav-item">
                <a href="?page=dashboard" class="nav-link <?php echo ($page == 'dashboard') ? 'active' : ''; ?>">
                    <i class="fe fe-home fe-16"></i>
                    <span class="ml-3 item-text">Dashboard</span>
                </a>
            </li>
            
            <?php if ($current_user['role'] === 'employee'): ?>
            <!-- Employee Navigation -->
            
            <!-- My Profile & Settings -->
            <li class="nav-item dropdown">
                <a href="#emp_profile" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-link">
                    <i class="fe fe-user fe-16"></i>
                    <span class="ml-3 item-text">My Profile</span>
                </a>
                <ul class="collapse list-unstyled pl-4 w-100" id="emp_profile">
                    <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'employee_self_service') ? 'active' : ''; ?>" href="?page=employee_self_service">
                            <span class="ml-1 item-text">Employee Portal</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'employee_profile') ? 'active' : ''; ?>" href="?page=employee_profile">
                            <span class="ml-1 item-text">Edit Profile</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Performance & Development -->
            <li class="nav-item dropdown">
                <a href="#emp_performance" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-link">
                    <i class="fe fe-trending-up fe-16"></i>
                    <span class="ml-3 item-text">My Performance</span>
                </a>
                <ul class="collapse list-unstyled pl-4 w-100" id="emp_performance">
                    <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'my_evaluations') ? 'active' : ''; ?>" href="?page=my_evaluations">
                            <span class="ml-1 item-text">My Evaluations</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Learning & Training -->
            <li class="nav-item dropdown">
                <a href="#emp_learning" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-link">
                    <i class="fe fe-book-open fe-16"></i>
                    <span class="ml-3 item-text">Learning & Training</span>
                </a>
                <!-- Updated: Learning Materials added -->
                <ul class="collapse list-unstyled pl-4 w-100" id="emp_learning">
                    <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'employee_training_requests') ? 'active' : ''; ?>" href="?page=employee_training_requests">
                            <span class="ml-1 item-text">Request Training</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'employee_learning_materials') ? 'active' : ''; ?>" href="?page=employee_learning_materials">
                            <span class="ml-1 item-text">Learning Materials</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'employee_learning_access') ? 'active' : ''; ?>" href="?page=employee_learning_access">
                            <span class="ml-1 item-text">My Learning Access</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'my_trainings') ? 'active' : ''; ?>" href="?page=my_trainings">
                            <span class="ml-1 item-text">My Trainings</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- My Requests -->
            <li class="nav-item">
                <a href="?page=my_requests" class="nav-link <?php echo ($page == 'my_requests' || $page == 'employee_requests') ? 'active' : ''; ?>">
                    <i class="fe fe-file-text fe-16"></i>
                    <span class="ml-3 item-text">My Requests</span>
                </a>
            </li>
            <?php elseif ($current_user['role'] === 'hr_manager'): ?>
            <!-- HR Manager Navigation -->
            
            <!-- Competency Management -->
            <li class="nav-item dropdown">
                <a href="#hr_competency" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-link">
                    <i class="fe fe-target fe-16"></i>
                    <span class="ml-3 item-text">Competency Management</span>
                </a>
                <ul class="collapse list-unstyled pl-4 w-100" id="hr_competency">
                    <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'competency_models') ? 'active' : ''; ?>" href="?page=competency_models">
                            <span class="ml-1 item-text">Competency Models</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'evaluation_cycles') ? 'active' : ''; ?>" href="?page=evaluation_cycles">
                            <span class="ml-1 item-text">Evaluation Cycles</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'evaluations') ? 'active' : ''; ?>" href="?page=evaluations">
                            <span class="ml-1 item-text">Evaluations</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'competency_reports') ? 'active' : ''; ?>" href="?page=competency_reports">
                            <span class="ml-1 item-text">Competency Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'ai_analysis_dashboard') ? 'active' : ''; ?>" href="?page=ai_analysis_dashboard">
                            <span class="ml-1 item-text">AI Analysis Dashboard</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Learning & Training Management -->
            <li class="nav-item dropdown">
                <a href="#hr_learning" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-link">
                    <i class="fe fe-book-open fe-16"></i>
                    <span class="ml-3 item-text">Learning & Training</span>
                </a>
                <ul class="collapse list-unstyled pl-4 w-100" id="hr_learning">
                    <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'learning_management_enhanced') ? 'active' : ''; ?>" href="?page=learning_management_enhanced">
                            <span class="ml-1 item-text">Learning Management</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'training_management') ? 'active' : ''; ?>" href="?page=training_management">
                            <span class="ml-1 item-text">Training Sessions</span>
                </a>
            </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'training_requests') ? 'active' : ''; ?>" href="?page=training_requests">
                            <span class="ml-1 item-text">Training Requests</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'hr_learning_requests') ? 'active' : ''; ?>" href="?page=hr_learning_requests">
                            <span class="ml-1 item-text">Learning Material Requests</span>
                        </a>
                    </li>
            <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'training_feedback_management') ? 'active' : ''; ?>" href="?page=training_feedback_management">
                            <span class="ml-1 item-text">Training Feedback</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Employee Management -->
            <li class="nav-item dropdown">
                <a href="#hr_employees" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-link">
                    <i class="fe fe-users fe-16"></i>
                    <span class="ml-3 item-text">Employee Management</span>
                </a>
                <ul class="collapse list-unstyled pl-4 w-100" id="hr_employees">
                    <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'hr_employee_management') ? 'active' : ''; ?>" href="?page=hr_employee_management">
                            <span class="ml-1 item-text">Employee Directory</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'succession_planning') ? 'active' : ''; ?>" href="?page=succession_planning">
                            <span class="ml-1 item-text">Succession Planning</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'hr_request_management') ? 'active' : ''; ?>" href="?page=hr_request_management">
                            <span class="ml-1 item-text">Employee Requests</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Reports & Analytics -->
            <li class="nav-item">
                <a href="?page=hr_reports" class="nav-link <?php echo ($page == 'hr_reports') ? 'active' : ''; ?>">
                    <i class="fe fe-pie-chart fe-16"></i>
                    <span class="ml-3 item-text">HR Reports & Analytics</span>
                </a>
            </li>
            
            <!-- Notifications -->
            <li class="nav-item">
                <a href="?page=hr_notifications" class="nav-link <?php echo ($page == 'hr_notifications') ? 'active' : ''; ?>">
                    <i class="fe fe-bell fe-16"></i>
                    <span class="ml-3 item-text">Notifications</span>
                </a>
            </li>
            <?php else: ?>
            
            <!-- HR Management -->
            <li class="nav-item dropdown">
                <a href="#competency" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-link" onmouseenter="showDropdown(this)" onmouseleave="hideDropdown(this)">
                    <i class="fe fe-target fe-16"></i>
                    <span class="ml-3 item-text">Competency Management</span>
                </a>
                <ul class="collapse list-unstyled pl-4 w-100" id="competency">
                    <li class="nav-item">
                        <a class="nav-link pl-3" href="?page=competency_models">
                            <span class="ml-1 item-text">Competency Models</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3" href="?page=evaluation_cycles">
                            <span class="ml-1 item-text">Evaluation Cycles</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3" href="?page=evaluations">
                            <span class="ml-1 item-text">Evaluations</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3" href="?page=competency_reports">
                            <span class="ml-1 item-text">Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3" href="?page=ai_analysis_dashboard">
                            <span class="ml-1 item-text">AI Analysis</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item dropdown">
                <a href="#learning" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-link" onmouseenter="showDropdown(this)" onmouseleave="hideDropdown(this)">
                    <i class="fe fe-book-open fe-16"></i>
                    <span class="ml-3 item-text">Learning & Development</span>
                </a>
                <ul class="collapse list-unstyled pl-4 w-100" id="learning">
                    <li class="nav-item">
                        <a class="nav-link pl-3" href="?page=learning_management">
                            <span class="ml-1 item-text">Learning Management</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3" href="?page=training_management">
                            <span class="ml-1 item-text">Training Management</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3" href="?page=training_requests">
                            <span class="ml-1 item-text">Training Requests</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3 <?php echo ($page == 'hr_learning_requests') ? 'active' : ''; ?>" href="?page=hr_learning_requests">
                            <span class="ml-1 item-text">Learning Material Requests</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item">
                <a href="?page=succession_planning" class="nav-link <?php echo ($page == 'succession_planning') ? 'active' : ''; ?>">
                    <i class="fe fe-trending-up fe-16"></i>
                    <span class="ml-3 item-text">Succession Planning</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="?page=employee_requests" class="nav-link <?php echo ($page == 'employee_requests') ? 'active' : ''; ?>">
                    <i class="fe fe-file-text fe-16"></i>
                    <span class="ml-3 item-text">Employee Requests</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if ($current_user['role'] === 'admin'): ?>
            <!-- System Administration -->
            <li class="nav-item dropdown">
                <a href="#admin" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-link" onmouseenter="showDropdown(this)" onmouseleave="hideDropdown(this)">
                    <i class="fe fe-settings fe-16"></i>
                    <span class="ml-3 item-text">System Administration</span>
                </a>
                <ul class="collapse list-unstyled pl-4 w-100" id="admin">
                    <li class="nav-item">
                        <a class="nav-link pl-3" href="?page=user_management">
                            <span class="ml-1 item-text">User Management</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3" href="?page=system_settings">
                            <span class="ml-1 item-text">System Settings</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pl-3" href="?page=admin_training_management">
                            <span class="ml-1 item-text">Training Management</span>
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</aside>

<script>
let hoverTimeout;

function showDropdown(element) {
    // Clear any existing timeout
    if (hoverTimeout) {
        clearTimeout(hoverTimeout);
        hoverTimeout = null;
    }
    
    console.log('Hovering over dropdown:', element);
    
    const targetId = element.getAttribute('href');
    const target = document.querySelector(targetId);
    
    console.log('Target element:', target);
    
    if (target) {
        // Show the dropdown
        target.classList.add('show');
        
        // Update aria-expanded
        element.setAttribute('aria-expanded', 'true');
        
        // Add active class to parent
        const parentItem = element.closest('.nav-item');
        if (parentItem) {
            parentItem.classList.add('active');
        }
        
        console.log('Dropdown shown');
    } else {
        console.error('Target element not found for:', targetId);
    }
}

function hideDropdown(element) {
    // Add a small delay before hiding to prevent flickering
    hoverTimeout = setTimeout(() => {
        console.log('Hiding dropdown:', element);
        
        const targetId = element.getAttribute('href');
        const target = document.querySelector(targetId);
        
        if (target) {
            // Hide the dropdown
            target.classList.remove('show');
            
            // Update aria-expanded
            element.setAttribute('aria-expanded', 'false');
            
            // Remove active class from parent
            const parentItem = element.closest('.nav-item');
            if (parentItem) {
                parentItem.classList.remove('active');
            }
            
            console.log('Dropdown hidden');
        }
    }, 150); // 150ms delay
}

// Also handle hover on the dropdown content to keep it open
document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.collapse');
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('mouseenter', function() {
            if (hoverTimeout) {
                clearTimeout(hoverTimeout);
                hoverTimeout = null;
            }
        });
        
        dropdown.addEventListener('mouseleave', function() {
            const parentLink = document.querySelector(`[href="#${this.id}"]`);
            if (parentLink) {
                hideDropdown(parentLink);
            }
        });
    });
});
</script>
