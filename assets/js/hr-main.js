// HR2 Main JavaScript Functions
class HRSystem {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeComponents();
        this.setupAnimations();
    }

    setupEventListeners() {
        // Theme switcher
        const modeSwitcher = document.getElementById('modeSwitcher');
        if (modeSwitcher) {
            modeSwitcher.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                this.toggleTheme();
            }, true);
        }

        // Sidebar toggle
        const sidebarToggle = document.querySelector('.collapseSidebar');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', this.toggleSidebar.bind(this));
        }

        // Form validation
        this.setupFormValidation();

        // Modal handlers
        this.setupModalHandlers();
    }

    initializeComponents() {
        // Initialize tooltips
        this.initTooltips();
        
        // Initialize dropdowns
        this.initDropdowns();

        // Initialize progress bars
        this.initProgressBars();
    }

    setupAnimations() {
        // Add animation classes to elements
        const animatedElements = document.querySelectorAll('.card, .btn, .table');
        animatedElements.forEach((element, index) => {
            element.style.animationDelay = `${index * 0.1}s`;
            element.classList.add('fade-in');
        });
    }

    toggleTheme() {
        const lightTheme = document.getElementById('lightTheme');
        const darkTheme = document.getElementById('darkTheme');
        const modeSwitcher = document.getElementById('modeSwitcher');
        
        if (lightTheme.disabled) {
            lightTheme.disabled = false;
            darkTheme.disabled = true;
            modeSwitcher.innerHTML = '<i class="fe fe-sun fe-16"></i>';
            localStorage.setItem('mode', 'light');
            document.body.classList.remove('dark');
            document.body.classList.add('light');
        } else {
            lightTheme.disabled = true;
            darkTheme.disabled = false;
            modeSwitcher.innerHTML = '<i class="fe fe-moon fe-16"></i>';
            localStorage.setItem('mode', 'dark');
            document.body.classList.add('dark');
            document.body.classList.remove('light');
        }
    }

    toggleSidebar() {
        const sidebar = document.getElementById('leftSidebar');
        const mainContent = document.querySelector('.main-content');
        
        if (sidebar) {
            sidebar.classList.toggle('collapsed');
        }
        
        if (mainContent) {
            mainContent.classList.toggle('expanded');
        }
    }

    setupFormValidation() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', this.validateForm.bind(this));
        });
    }

    validateForm(event) {
        const form = event.target;
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!input.value.trim()) {
                this.showFieldError(input, 'This field is required');
                isValid = false;
            } else {
                this.clearFieldError(input);
            }
        });

        if (!isValid) {
            event.preventDefault();
            this.showNotification('Please fill in all required fields', 'error');
        }
    }

    showFieldError(input, message) {
        const formGroup = input.closest('.form-group');
        if (formGroup) {
            formGroup.classList.add('has-error');
            
            let errorElement = formGroup.querySelector('.error-message');
            if (!errorElement) {
                errorElement = document.createElement('div');
                errorElement.className = 'error-message text-danger small mt-1';
                formGroup.appendChild(errorElement);
            }
            errorElement.textContent = message;
        }
    }

    clearFieldError(input) {
        const formGroup = input.closest('.form-group');
        if (formGroup) {
            formGroup.classList.remove('has-error');
            const errorElement = formGroup.querySelector('.error-message');
            if (errorElement) {
                errorElement.remove();
            }
        }
    }

    setupModalHandlers() {
        // Auto-focus first input in modals
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.addEventListener('shown.bs.modal', () => {
                const firstInput = modal.querySelector('input, select, textarea');
                if (firstInput) {
                    firstInput.focus();
                }
            });
        });
    }

    initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    initDropdowns() {
        // Initialize Bootstrap dropdowns with proper event handling
        const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        
        dropdownElementList.forEach(function (dropdownToggleEl, index) {
            if (dropdownToggleEl.id === 'profileDropdown' || dropdownToggleEl.id === 'notificationDropdown') {
                return;
            }
            // Remove any existing event listeners
            if (dropdownToggleEl._dropdownHandler) {
                dropdownToggleEl.removeEventListener('click', dropdownToggleEl._dropdownHandler);
            }
            
            // Create new event handler
            dropdownToggleEl._dropdownHandler = function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const dropdownMenu = dropdownToggleEl.nextElementSibling;
                
                if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                    const isOpen = dropdownMenu.classList.contains('show');
                    
                    // Close all other dropdowns
                    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                        if (menu !== dropdownMenu) {
                            menu.classList.remove('show');
                            const prevToggle = menu.previousElementSibling;
                            if (prevToggle) {
                                prevToggle.setAttribute('aria-expanded', 'false');
                            }
                        }
                    });
                    
                    // Toggle current dropdown
                    if (isOpen) {
                        dropdownMenu.classList.remove('show');
                        dropdownToggleEl.setAttribute('aria-expanded', 'false');
                    } else {
                        dropdownMenu.classList.add('show');
                        dropdownToggleEl.setAttribute('aria-expanded', 'true');
                    }
                }
            };
            
            // Add event listener
            dropdownToggleEl.addEventListener('click', dropdownToggleEl._dropdownHandler);
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                    const prevToggle = menu.previousElementSibling;
                    if (prevToggle) {
                        prevToggle.setAttribute('aria-expanded', 'false');
                    }
                });
            }
        });
        
        // Initialize sidebar collapse dropdowns
        this.initSidebarCollapses();
    }
    
    initSidebarCollapses() {
        // Wait a bit for DOM to be fully ready
        setTimeout(() => {
            const layoutRoot = document.querySelector('.vertical');
            const collapseToggles = document.querySelectorAll('[data-toggle="collapse"]');
            let hoverTimeout;

            const isCollapsed = () =>
                layoutRoot && (layoutRoot.classList.contains('collapsed') || layoutRoot.classList.contains('narrow'));

            const closeAll = (exceptTarget) => {
                document.querySelectorAll('.sidebar-left .collapse.show').forEach(panel => {
                    if (panel !== exceptTarget) {
                        panel.classList.remove('show');
                    }
                });
                document.querySelectorAll('.sidebar-left [data-toggle="collapse"]').forEach(t => {
                    if (!exceptTarget || t.getAttribute('href') !== `#${exceptTarget.id}`) {
                        t.setAttribute('aria-expanded', 'false');
                    }
                });
            };

            collapseToggles.forEach((toggle) => {
                toggle.addEventListener('click', (e) => {
                    if (isCollapsed()) {
                        e.preventDefault();
                        e.stopPropagation();
                        return;
                    }

                    e.preventDefault();
                    e.stopPropagation();

                    const targetId = toggle.getAttribute('href');
                    const target = document.querySelector(targetId);

                    if (target) {
                        const isCurrentlyShown = target.classList.contains('show');
                        target.classList.toggle('show');
                        const isExpanded = target.classList.contains('show');
                        toggle.setAttribute('aria-expanded', isExpanded);

                        const parentItem = toggle.closest('.nav-item');
                        if (parentItem) {
                            parentItem.classList.toggle('active', isExpanded);
                        }
                    }
                });

                // Removed hover-open behavior for collapsed sidebar.
            });
        }, 100);
    }

    initProgressBars() {
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach(bar => {
            const width = bar.getAttribute('style').match(/width:\s*(\d+)%/);
            if (width) {
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width[1] + '%';
                }, 500);
            }
        });
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    // Utility methods
    formatDate(date) {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Initialize HR System when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.hrSystem = new HRSystem();
    
    // Load saved mode
    const savedMode = localStorage.getItem('mode');
    if (savedMode === 'dark') {
        document.getElementById('lightTheme').disabled = true;
        document.getElementById('darkTheme').disabled = false;
        document.getElementById('modeSwitcher').innerHTML = '<i class="fe fe-moon fe-16"></i>';
        document.body.classList.add('dark');
        document.body.classList.remove('light');
    } else if (savedMode === 'light') {
        document.getElementById('lightTheme').disabled = false;
        document.getElementById('darkTheme').disabled = true;
        document.getElementById('modeSwitcher').innerHTML = '<i class="fe fe-sun fe-16"></i>';
        document.body.classList.remove('dark');
        document.body.classList.add('light');
    }
    
    // Removed extra dropdown patching to avoid conflicts.
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HRSystem;
}
