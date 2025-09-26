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
            modeSwitcher.addEventListener('click', this.toggleTheme.bind(this));
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
            localStorage.setItem('theme', 'light');
        } else {
            lightTheme.disabled = true;
            darkTheme.disabled = false;
            modeSwitcher.innerHTML = '<i class="fe fe-moon fe-16"></i>';
            localStorage.setItem('theme', 'dark');
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
        console.log('Initializing dropdowns...');
        
        // Initialize Bootstrap dropdowns with proper event handling
        const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        console.log('Found dropdown elements:', dropdownElementList.length);
        
        dropdownElementList.forEach(function (dropdownToggleEl, index) {
            console.log(`Setting up dropdown ${index}:`, dropdownToggleEl);
            
            // Remove any existing event listeners
            if (dropdownToggleEl._dropdownHandler) {
                dropdownToggleEl.removeEventListener('click', dropdownToggleEl._dropdownHandler);
            }
            
            // Create new event handler
            dropdownToggleEl._dropdownHandler = function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Dropdown clicked:', dropdownToggleEl);
                
                const dropdownMenu = dropdownToggleEl.nextElementSibling;
                console.log('Dropdown menu:', dropdownMenu);
                
                if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                    const isOpen = dropdownMenu.classList.contains('show');
                    console.log('Dropdown is currently open:', isOpen);
                    
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
                        console.log('Closing dropdown');
                    } else {
                        dropdownMenu.classList.add('show');
                        dropdownToggleEl.setAttribute('aria-expanded', 'true');
                        console.log('Opening dropdown');
                    }
                } else {
                    console.error('Dropdown menu not found or invalid');
                }
            };
            
            // Add event listener
            dropdownToggleEl.addEventListener('click', dropdownToggleEl._dropdownHandler);
            console.log('Event listener added to dropdown', index);
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
            const collapseToggles = document.querySelectorAll('[data-toggle="collapse"]');
            console.log('Found collapse toggles:', collapseToggles.length);
            
            if (collapseToggles.length === 0) {
                console.log('No collapse toggles found, trying alternative selectors...');
                // Try alternative selectors
                const altToggles = document.querySelectorAll('.dropdown-toggle');
                console.log('Found dropdown toggles:', altToggles.length);
            }
            
            collapseToggles.forEach((toggle, index) => {
                console.log(`Setting up toggle ${index}:`, toggle);
                
                toggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    console.log('Collapse toggle clicked:', toggle);
                    
                    const targetId = toggle.getAttribute('href');
                    const target = document.querySelector(targetId);
                    console.log('Target element:', target);
                    
                    if (target) {
                        // Toggle the collapse
                        const isCurrentlyShown = target.classList.contains('show');
                        target.classList.toggle('show');
                        
                        // Update aria-expanded
                        const isExpanded = target.classList.contains('show');
                        toggle.setAttribute('aria-expanded', isExpanded);
                        
                        // Add/remove active class to parent
                        const parentItem = toggle.closest('.nav-item');
                        if (parentItem) {
                            if (isExpanded) {
                                parentItem.classList.add('active');
                            } else {
                                parentItem.classList.remove('active');
                            }
                        }
                        
                        console.log('Collapse toggled, isExpanded:', isExpanded);
                    } else {
                        console.error('Target element not found for:', targetId);
                    }
                });
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
    
    // Load saved theme
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.getElementById('lightTheme').disabled = true;
        document.getElementById('darkTheme').disabled = false;
        document.getElementById('modeSwitcher').innerHTML = '<i class="fe fe-moon fe-16"></i>';
    }
    
    // Simple dropdown fix - direct approach
    setTimeout(function() {
        console.log('Setting up simple dropdown fix...');
        
        const dropdownToggle = document.querySelector('#navbarDropdownMenuLink');
        const dropdownMenu = document.querySelector('.dropdown-menu');
        
        console.log('Dropdown toggle:', dropdownToggle);
        console.log('Dropdown menu:', dropdownMenu);
        
        if (dropdownToggle && dropdownMenu) {
            dropdownToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Dropdown clicked!');
                
                const isOpen = dropdownMenu.classList.contains('show');
                
                if (isOpen) {
                    dropdownMenu.classList.remove('show');
                    dropdownToggle.setAttribute('aria-expanded', 'false');
                    console.log('Closing dropdown');
                } else {
                    dropdownMenu.classList.add('show');
                    dropdownToggle.setAttribute('aria-expanded', 'true');
                    console.log('Opening dropdown');
                }
            });
            
            // Close when clicking outside
            document.addEventListener('click', function(e) {
                if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                    dropdownMenu.classList.remove('show');
                    dropdownToggle.setAttribute('aria-expanded', 'false');
                }
            });
            
            console.log('Simple dropdown fix applied!');
        } else {
            console.error('Dropdown elements not found!');
        }
    }, 1000);
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HRSystem;
}
