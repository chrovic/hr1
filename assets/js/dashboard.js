// Dashboard JavaScript Functions
class DashboardManager {
    constructor() {
        this.charts = {};
        this.init();
    }

    init() {
        this.initializeCharts();
        this.setupDashboardInteractions();
        this.loadDashboardData();
    }

    initializeCharts() {
        // Employee Growth Chart
        this.initEmployeeChart();
        
        // Department Distribution Chart
        this.initDepartmentChart();
    }

    initEmployeeChart() {
        const ctx = document.getElementById('employeeChart');
        if (!ctx) return;

        this.charts.employee = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Total Employees',
                    data: [1200, 1210, 1225, 1230, 1240, 1245, 1250, 1248, 1255, 1260, 1265, 1247],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                },
                elements: {
                    point: {
                        radius: 4,
                        hoverRadius: 6
                    }
                }
            }
        });
    }

    initDepartmentChart() {
        const ctx = document.getElementById('departmentChart');
        if (!ctx) return;

        this.charts.department = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Engineering', 'Marketing', 'Sales', 'HR', 'Finance', 'Operations'],
                datasets: [{
                    data: [35, 15, 20, 8, 12, 10],
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }

    setupDashboardInteractions() {
        // Quick action buttons
        const quickActionBtns = document.querySelectorAll('.quick-action-btn');
        quickActionBtns.forEach(btn => {
            btn.addEventListener('click', this.handleQuickAction.bind(this));
        });

        // Stats cards hover effects
        const statsCards = document.querySelectorAll('.card.shadow');
        statsCards.forEach(card => {
            card.addEventListener('mouseenter', this.animateCard.bind(this));
            card.addEventListener('mouseleave', this.resetCard.bind(this));
        });

        // Real-time updates
        this.setupRealTimeUpdates();
    }

    handleQuickAction(event) {
        event.preventDefault();
        const action = event.currentTarget.getAttribute('data-action');
        
        switch(action) {
            case 'add-employee':
                this.showAddEmployeeModal();
                break;
            case 'schedule-training':
                this.showScheduleTrainingModal();
                break;
            case 'post-job':
                this.showPostJobModal();
                break;
            case 'generate-report':
                this.showGenerateReportModal();
                break;
            default:
                console.log('Quick action:', action);
        }
    }

    animateCard(event) {
        const card = event.currentTarget;
        card.style.transform = 'translateY(-5px)';
        card.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
        card.style.transition = 'all 0.3s ease';
    }

    resetCard(event) {
        const card = event.currentTarget;
        card.style.transform = 'translateY(0)';
        card.style.boxShadow = '0 0.125rem 0.25rem rgba(0, 0, 0, 0.075)';
    }

    setupRealTimeUpdates() {
        // Update stats every 30 seconds
        setInterval(() => {
            this.updateStats();
        }, 30000);

        // Update activities every 60 seconds
        setInterval(() => {
            this.updateActivities();
        }, 60000);
    }

    updateStats() {
        // Simulate real-time data updates
        const statsElements = document.querySelectorAll('.h2.mb-0');
        statsElements.forEach(element => {
            const currentValue = parseInt(element.textContent.replace(/,/g, ''));
            const change = Math.floor(Math.random() * 10) - 5; // Random change between -5 and +5
            const newValue = Math.max(0, currentValue + change);
            
            element.textContent = newValue.toLocaleString();
            
            // Add animation
            element.style.color = change > 0 ? '#28a745' : change < 0 ? '#dc3545' : '#6c757d';
            setTimeout(() => {
                element.style.color = '';
            }, 1000);
        });
    }

    updateActivities() {
        const activitiesContainer = document.querySelector('.list-group');
        if (!activitiesContainer) return;

        const activities = [
            {
                icon: 'fe-user-plus',
                title: 'New Employee Onboarded',
                description: 'New employee joined the team',
                time: '5 minutes ago',
                color: 'success'
            },
            {
                icon: 'fe-award',
                title: 'Training Completed',
                description: 'Employee completed training program',
                time: '15 minutes ago',
                color: 'warning'
            },
            {
                icon: 'fe-calendar',
                title: 'Performance Review Scheduled',
                description: 'Quarterly reviews for Q1 2025',
                time: '1 hour ago',
                color: 'info'
            }
        ];

        const randomActivity = activities[Math.floor(Math.random() * activities.length)];
        const activityElement = this.createActivityElement(randomActivity);
        
        // Add to top of activities list
        activitiesContainer.insertBefore(activityElement, activitiesContainer.firstChild);
        
        // Remove oldest activity if more than 5
        const activityItems = activitiesContainer.querySelectorAll('.list-group-item');
        if (activityItems.length > 5) {
            activityItems[activityItems.length - 1].remove();
        }
    }

    createActivityElement(activity) {
        const element = document.createElement('div');
        element.className = 'list-group-item px-0';
        element.innerHTML = `
            <div class="row align-items-center">
                <div class="col-auto">
                    <span class="fe ${activity.icon} fe-16 text-${activity.color}"></span>
                </div>
                <div class="col">
                    <strong>${activity.title}</strong>
                    <div class="text-muted small">${activity.description}</div>
                </div>
                <div class="col-auto">
                    <small class="text-muted">${activity.time}</small>
                </div>
            </div>
        `;
        
        // Add fade-in animation
        element.style.opacity = '0';
        element.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            element.style.transition = 'all 0.5s ease';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, 100);
        
        return element;
    }

    loadDashboardData() {
        // Simulate loading dashboard data
        this.showLoadingState();
        
        setTimeout(() => {
            this.hideLoadingState();
            this.animateDashboardElements();
        }, 1000);
    }

    showLoadingState() {
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.style.opacity = '0.5';
        });
    }

    hideLoadingState() {
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.style.opacity = '1';
        });
    }

    animateDashboardElements() {
        const elements = document.querySelectorAll('.card, .btn');
        elements.forEach((element, index) => {
            element.style.animationDelay = `${index * 0.1}s`;
            element.classList.add('dashboard-card-animate');
        });
    }

    // Modal handlers
    showAddEmployeeModal() {
        // Implementation for add employee modal
        console.log('Opening add employee modal');
    }

    showScheduleTrainingModal() {
        // Implementation for schedule training modal
        console.log('Opening schedule training modal');
    }

    showPostJobModal() {
        // Implementation for post job modal
        console.log('Opening post job modal');
    }

    showGenerateReportModal() {
        // Implementation for generate report modal
        console.log('Opening generate report modal');
    }

    // Chart update methods
    updateEmployeeChart(newData) {
        if (this.charts.employee) {
            this.charts.employee.data.datasets[0].data = newData;
            this.charts.employee.update();
        }
    }

    updateDepartmentChart(newData) {
        if (this.charts.department) {
            this.charts.department.data.datasets[0].data = newData;
            this.charts.department.update();
        }
    }
}

// Initialize Dashboard Manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('#employeeChart') || document.querySelector('#departmentChart')) {
        window.dashboardManager = new DashboardManager();
    }
});
