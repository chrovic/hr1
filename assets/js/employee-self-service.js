// Employee Self Service JavaScript Functions
class EmployeeSelfService {
    constructor() {
        this.currentEmployee = null;
        this.init();
    }

    init() {
        this.loadEmployeeData();
        this.setupEventListeners();
        this.initializeComponents();
    }

    loadEmployeeData() {
        // Load employee data from server
        this.currentEmployee = {
            id: '',
            firstName: '',
            lastName: '',
            email: '',
            phone: '',
            dob: '',
            hireDate: '',
            position: '',
            department: '',
            status: '',
            yearsOfService: 0,
            avatar: 'assets/images/avatars/default.jpg'
        };

        this.populateEmployeeProfile();
        this.loadLeaveBalance();
        this.loadRecentRequests();
        this.loadDocuments();
    }

    populateEmployeeProfile() {
        if (!this.currentEmployee) return;

        // Update profile header
        const avatar = document.querySelector('.employee-avatar');
        if (avatar) {
            avatar.src = this.currentEmployee.avatar;
        }

        const nameElement = document.querySelector('.card-title');
        if (nameElement) {
            nameElement.textContent = `${this.currentEmployee.firstName} ${this.currentEmployee.lastName}`;
        }

        const positionElement = document.querySelector('.text-muted');
        if (positionElement) {
            positionElement.textContent = this.currentEmployee.position;
        }

        // Update form fields
        this.updateFormField('input[name="first_name"]', this.currentEmployee.firstName);
        this.updateFormField('input[name="last_name"]', this.currentEmployee.lastName);
        this.updateFormField('input[name="email"]', this.currentEmployee.email);
        this.updateFormField('input[value="+1 (555) 123-4567"]', this.currentEmployee.phone);
        this.updateFormField('input[value="1985-06-15"]', this.currentEmployee.dob);
        this.updateFormField('input[value="2019-03-01"]', this.currentEmployee.hireDate);
    }

    updateFormField(selector, value) {
        const field = document.querySelector(selector);
        if (field) {
            field.value = value;
        }
    }

    setupEventListeners() {
        // Edit profile button
        const editBtn = document.querySelector('.btn-outline-primary.float-right');
        if (editBtn) {
            editBtn.addEventListener('click', this.toggleEditMode.bind(this));
        }

        // Quick action buttons
        const quickActions = document.querySelectorAll('.quick-action-card');
        quickActions.forEach(action => {
            action.addEventListener('click', this.handleQuickAction.bind(this));
        });

        // Form submissions
        this.setupFormSubmissions();

        // Document actions
        this.setupDocumentActions();
    }

    initializeComponents() {
        this.setupProgressBars();
        this.setupTooltips();
    }

    toggleEditMode(event) {
        event.preventDefault();
        const button = event.currentTarget;
        const formFields = document.querySelectorAll('.info-form-control[readonly]');
        
        if (button.textContent.trim() === 'Edit') {
            // Enable editing
            formFields.forEach(field => {
                field.removeAttribute('readonly');
                field.style.backgroundColor = '#fff';
            });
            button.textContent = 'Save';
            button.classList.remove('btn-outline-primary');
            button.classList.add('btn-success');
        } else {
            // Save changes
            this.saveProfileChanges();
            formFields.forEach(field => {
                field.setAttribute('readonly', true);
                field.style.backgroundColor = '#f8f9fa';
            });
            button.textContent = 'Edit';
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-primary');
            
            this.showNotification('Profile updated successfully!', 'success');
        }
    }

    saveProfileChanges() {
        // Collect form data
        const formData = {
            firstName: document.querySelector('input[name="first_name"]')?.value || this.currentEmployee.firstName,
            lastName: document.querySelector('input[name="last_name"]')?.value || this.currentEmployee.lastName,
            email: document.querySelector('input[type="email"]')?.value || this.currentEmployee.email,
            phone: document.querySelector('input[type="tel"]')?.value || this.currentEmployee.phone,
            dob: document.querySelector('input[type="date"]')?.value || this.currentEmployee.dob
        };

        // Update current employee data
        Object.assign(this.currentEmployee, formData);

        // Simulate API call
        console.log('Saving profile changes:', formData);
    }

    handleQuickAction(event) {
        event.preventDefault();
        const action = event.currentTarget.getAttribute('data-action');
        
        switch(action) {
            case 'leave-request':
                this.showLeaveRequestModal();
                break;
            case 'expense-submission':
                this.showExpenseModal();
                break;
            case 'training-request':
                this.showTrainingRequestModal();
                break;
            case 'support-ticket':
                this.showSupportModal();
                break;
        }
    }

    setupFormSubmissions() {
        // Leave request form
        const leaveForm = document.querySelector('#leaveRequestModal form');
        if (leaveForm) {
            leaveForm.addEventListener('submit', this.handleLeaveRequest.bind(this));
        }

        // Expense form
        const expenseForm = document.querySelector('#expenseModal form');
        if (expenseForm) {
            expenseForm.addEventListener('submit', this.handleExpenseSubmission.bind(this));
        }

        // Training request form
        const trainingForm = document.querySelector('#trainingRequestModal form');
        if (trainingForm) {
            trainingForm.addEventListener('submit', this.handleTrainingRequest.bind(this));
        }

        // Support form
        const supportForm = document.querySelector('#supportModal form');
        if (supportForm) {
            supportForm.addEventListener('submit', this.handleSupportTicket.bind(this));
        }
    }

    handleLeaveRequest(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        const leaveData = {
            type: formData.get('leaveType'),
            startDate: formData.get('startDate'),
            endDate: formData.get('endDate'),
            reason: formData.get('reason')
        };

        // Validate dates
        if (new Date(leaveData.startDate) >= new Date(leaveData.endDate)) {
            this.showNotification('End date must be after start date', 'error');
            return;
        }

        // Simulate API call
        console.log('Submitting leave request:', leaveData);
        this.showNotification('Leave request submitted successfully!', 'success');
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('leaveRequestModal'));
        if (modal) {
            modal.hide();
        }

        // Reset form
        event.target.reset();
    }

    handleExpenseSubmission(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        const expenseData = {
            type: formData.get('expenseType'),
            amount: parseFloat(formData.get('amount')),
            date: formData.get('date'),
            description: formData.get('description'),
            receipt: formData.get('receipt')
        };

        // Validate amount
        if (expenseData.amount <= 0) {
            this.showNotification('Amount must be greater than 0', 'error');
            return;
        }

        console.log('Submitting expense:', expenseData);
        this.showNotification('Expense submitted successfully!', 'success');
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('expenseModal'));
        if (modal) {
            modal.hide();
        }

        event.target.reset();
    }

    handleTrainingRequest(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        const trainingData = {
            type: formData.get('trainingType'),
            name: formData.get('trainingName'),
            provider: formData.get('provider'),
            cost: parseFloat(formData.get('cost')),
            justification: formData.get('justification')
        };

        console.log('Submitting training request:', trainingData);
        this.showNotification('Training request submitted successfully!', 'success');
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('trainingRequestModal'));
        if (modal) {
            modal.hide();
        }

        event.target.reset();
    }

    handleSupportTicket(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        const ticketData = {
            category: formData.get('category'),
            priority: formData.get('priority'),
            subject: formData.get('subject'),
            message: formData.get('message')
        };

        console.log('Submitting support ticket:', ticketData);
        this.showNotification('Support ticket submitted successfully!', 'success');
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('supportModal'));
        if (modal) {
            modal.hide();
        }

        event.target.reset();
    }

    loadLeaveBalance() {
        const leaveBalance = {
            annual: 15,
            sick: 5,
            personal: 3,
            used: 10,
            remaining: 13
        };

        // Update leave balance display
        const annualElement = document.querySelector('.h3.text-primary');
        if (annualElement) {
            annualElement.textContent = leaveBalance.annual;
        }

        const sickElement = document.querySelector('.h3.text-success');
        if (sickElement) {
            sickElement.textContent = leaveBalance.sick;
        }

        const personalElement = document.querySelector('.h3.text-info');
        if (personalElement) {
            personalElement.textContent = leaveBalance.personal;
        }

        const usedElement = document.querySelector('.h5.mb-0');
        if (usedElement) {
            usedElement.textContent = `${leaveBalance.used} days`;
        }

        const remainingElements = document.querySelectorAll('.h5.mb-0');
        if (remainingElements[1]) {
            remainingElements[1].textContent = `${leaveBalance.remaining} days`;
        }
    }

    loadRecentRequests() {
        const requests = [
            {
                icon: 'fe-calendar',
                title: 'Annual Leave Request',
                description: 'Dec 20-27, 2024',
                status: 'warning',
                statusText: 'Pending'
            },
            {
                icon: 'fe-dollar-sign',
                title: 'Travel Expense',
                description: '$450.00',
                status: 'success',
                statusText: 'Approved'
            },
            {
                icon: 'fe-book-open',
                title: 'Training Request',
                description: 'Digital Marketing Course',
                status: 'info',
                statusText: 'In Review'
            }
        ];

        const requestsContainer = document.querySelector('.list-group');
        if (requestsContainer) {
            requestsContainer.innerHTML = '';
            requests.forEach(request => {
                const requestElement = this.createRequestElement(request);
                requestsContainer.appendChild(requestElement);
            });
        }
    }

    createRequestElement(request) {
        const element = document.createElement('div');
        element.className = 'list-group-item px-0';
        element.innerHTML = `
            <div class="row align-items-center">
                <div class="col-auto">
                    <span class="fe ${request.icon} fe-16 text-${request.status}"></span>
                </div>
                <div class="col">
                    <strong>${request.title}</strong>
                    <div class="text-muted small">${request.description}</div>
                </div>
                <div class="col-auto">
                    <span class="badge badge-${request.status}">${request.statusText}</span>
                </div>
            </div>
        `;
        return element;
    }

    loadDocuments() {
        const documents = [
            {
                name: 'Employment Contract',
                type: 'Contract',
                date: 'Mar 1, 2019',
                status: 'success',
                statusText: 'Active'
            },
            {
                name: 'Performance Review 2024',
                type: 'Review',
                date: 'Nov 15, 2024',
                status: 'warning',
                statusText: 'Pending'
            },
            {
                name: 'Training Certificate',
                type: 'Certificate',
                date: 'Oct 20, 2024',
                status: 'success',
                statusText: 'Completed'
            }
        ];

        const documentsTable = document.querySelector('.documents-table tbody');
        if (documentsTable) {
            documentsTable.innerHTML = '';
            documents.forEach(doc => {
                const row = this.createDocumentRow(doc);
                documentsTable.appendChild(row);
            });
        }
    }

    createDocumentRow(document) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><strong>${document.name}</strong></td>
            <td>${document.type}</td>
            <td>${document.date}</td>
            <td><span class="badge badge-${document.status}">${document.statusText}</span></td>
            <td><a href="#" class="btn btn-sm btn-outline-primary">Download</a></td>
        `;
        return row;
    }

    setupDocumentActions() {
        const downloadBtns = document.querySelectorAll('.btn-outline-primary');
        downloadBtns.forEach(btn => {
            if (btn.textContent.includes('Download')) {
                btn.addEventListener('click', this.handleDocumentDownload.bind(this));
            }
        });
    }

    handleDocumentDownload(event) {
        event.preventDefault();
        this.showNotification('Document download started...', 'info');
    }

    setupProgressBars() {
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach(bar => {
            const width = bar.style.width || bar.getAttribute('data-width');
            if (width) {
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 500);
            }
        });
    }

    setupTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    showNotification(message, type = 'info') {
        if (window.hrSystem) {
            window.hrSystem.showNotification(message, type);
        }
    }

    // Modal show methods
    showLeaveRequestModal() {
        const modal = new bootstrap.Modal(document.getElementById('leaveRequestModal'));
        modal.show();
    }

    showExpenseModal() {
        const modal = new bootstrap.Modal(document.getElementById('expenseModal'));
        modal.show();
    }

    showTrainingRequestModal() {
        const modal = new bootstrap.Modal(document.getElementById('trainingRequestModal'));
        modal.show();
    }

    showSupportModal() {
        const modal = new bootstrap.Modal(document.getElementById('supportModal'));
        modal.show();
    }
}

// Initialize Employee Self Service when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.employee-profile-header')) {
        window.employeeSelfService = new EmployeeSelfService();
    }
});
