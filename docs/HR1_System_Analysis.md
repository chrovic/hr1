# HR1 System - Complete Function Analysis & Data Flow

## 🏗️ SYSTEM ARCHITECTURE OVERVIEW

```
┌─────────────────────────────────────────────────────────────────┐
│                        HR1 SYSTEM                               │
│                    (index.php - Main Router)                    │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    AUTHENTICATION LAYER                         │
│              (SimpleAuth - Role-based Access)                   │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      PAGE ROUTER                                │
│              (Switch Statement - Page Handler)                   │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    MODULE PAGES                                 │
└─────────────────────────────────────────────────────────────────┘
```

## 📊 PAGE FUNCTIONS & DATA FLOW DIAGRAM

```
┌─────────────────────────────────────────────────────────────────┐
│                        DASHBOARD                                │
│                    (pages/dashboard.php)                        │
├─────────────────────────────────────────────────────────────────┤
│ FUNCTION: Central Analytics Hub                                 │
│ ACCESS: All Roles (Admin, HR Manager, Employee)                │
│ DATA: Employee stats, Training stats, Evaluation stats         │
│ INTERACTIONS: Links to all modules, Real-time updates          │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                  COMPETENCY MANAGEMENT MODULE                   │
└─────────────────────────────────────────────────────────────────┘
                                │
        ┌───────────────────────┼───────────────────────┐
        ▼                       ▼                       ▼
┌───────────────┐    ┌───────────────┐    ┌───────────────┐
│   MODELS      │    │    CYCLES     │    │ EVALUATIONS   │
│(competency_   │    │(evaluation_   │    │(evaluations.  │
│ models.php)   │    │ cycles.php)   │    │ php)         │
├───────────────┤    ├───────────────┤    ├───────────────┤
│FUNCTION:      │    │FUNCTION:      │    │FUNCTION:      │
│Create/manage  │    │Manage eval    │    │Assign & track │
│competency     │    │periods        │    │evaluations    │
│frameworks     │    │               │    │               │
│               │    │DATA:          │    │DATA:          │
│DATA:          │    │Cycle details, │    │Assignments,   │
│Model info,    │    │Dates, Status  │    │Status, Users  │
│Competencies   │    │               │    │               │
│               │    │INTERACTIONS:  │    │INTERACTIONS:  │
│INTERACTIONS:  │    │Links to       │    │Assigns evals, │
│Creates models,│    │models & evals │    │Selects users  │
│Adds comps     │    │               │    │               │
└───────────────┘    └───────────────┘    └───────────────┘
        │                       │                       │
        └───────────────────────┼───────────────────────┘
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    EVALUATION FORM                              │
│                  (pages/evaluation_form.php)                    │
├─────────────────────────────────────────────────────────────────┤
│ FUNCTION: Conduct Actual Evaluations                            │
│ ACCESS: Assigned Evaluators Only                               │
│ DATA: Evaluation details, Competencies, Scores, Comments       │
│ INTERACTIONS: Submits scores, Updates status, Validates access  │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    COMPETENCY REPORTS                           │
│                (pages/competency_reports.php)                  │
├─────────────────────────────────────────────────────────────────┤
│ FUNCTION: Generate Analytics & Reports                          │
│ ACCESS: HR Manager, Admin                                       │
│ DATA: Filtered eval data, Trends, Comparisons                 │
│ INTERACTIONS: Filters data, Exports reports, Shows trends      │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    MY EVALUATIONS                              │
│                 (pages/my_evaluations.php)                     │
├─────────────────────────────────────────────────────────────────┤
│ FUNCTION: Employee Self-Service for Evaluations                │
│ ACCESS: Employees Only                                          │
│ DATA: Personal eval history, Performance trends, Charts      │
│ INTERACTIONS: Views history, Displays trends, Links to details│
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                  EMPLOYEE SELF-SERVICE                         │
│              (pages/employee_self_service.php)                 │
├─────────────────────────────────────────────────────────────────┤
│ FUNCTION: Personal Information & Request Management            │
│ ACCESS: Employees Only                                          │
│ DATA: Profile info, Leave balances, Training history           │
│ INTERACTIONS: Updates info, Submits requests, Views history    │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                LEARNING & DEVELOPMENT MODULE                   │
└─────────────────────────────────────────────────────────────────┘
                                │
        ┌───────────────────────┼───────────────────────┐
        ▼                       ▼                       ▼
┌───────────────┐    ┌───────────────┐    ┌───────────────┐
│   LEARNING    │    │   TRAINING    │    │   REQUESTS    │
│ MANAGEMENT    │    │ MANAGEMENT    │    │(training_     │
│(learning_     │    │(training_     │    │ requests.php) │
│ management.   │    │ management.   │    │               │
│ php)         │    │ php)         │    │               │
├───────────────┤    ├───────────────┤    ├───────────────┤
│FUNCTION:      │    │FUNCTION:      │    │FUNCTION:      │
│Manage courses │    │Schedule &     │    │Handle training│
│& programs     │    │track sessions │    │requests       │
│               │    │               │    │               │
│DATA:          │    │DATA:          │    │DATA:          │
│Course catalog,│    │Session sched, │    │Request details│
│Enrollments,   │    │Trainers,      │    │Status,        │
│Progress       │    │Attendance     │    │Approvals      │
│               │    │               │    │               │
│INTERACTIONS:  │    │INTERACTIONS:  │    │INTERACTIONS:  │
│Creates courses│    │Schedules      │    │Approves/denies│
│Manages enroll │    │Tracks progress│    │Links to       │
│Links to train │    │Links to learn │    │training mgmt  │
└───────────────┘    └───────────────┘    └───────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                    SUCCESSION PLANNING                          │
│               (pages/succession_planning.php)                  │
├─────────────────────────────────────────────────────────────────┤
│ FUNCTION: Identify & Develop Future Leaders                     │
│ ACCESS: HR Manager, Admin                                       │
│ DATA: Critical roles, Successors, Readiness, Development plans │
│ INTERACTIONS: Defines positions, Tracks readiness, Manages dev │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                    ADMINISTRATION MODULE                       │
└─────────────────────────────────────────────────────────────────┘
                                │
        ┌───────────────────────┼───────────────────────┐
        ▼                       ▼                       ▼
┌───────────────┐    ┌───────────────┐    ┌───────────────┐
│   USER MGMT   │    │   SETTINGS    │    │    LOGS       │
│(user_         │    │(system_       │    │(system_       │
│ management.   │    │ settings.php) │    │ logs.php)     │
│ php)         │    │               │    │               │
├───────────────┤    ├───────────────┤    ├───────────────┤
│FUNCTION:      │    │FUNCTION:      │    │FUNCTION:      │
│Manage users   │    │Configure      │    │Monitor system │
│& roles        │    │system         │    │activity       │
│               │    │               │    │               │
│DATA:          │    │DATA:          │    │DATA:          │
│User accounts, │    │Company info,  │    │Activity logs, │
│Roles,         │    │Config,        │    │Security events│
│Permissions    │    │Integrations   │    │User actions   │
│               │    │               │    │               │
│INTERACTIONS:  │    │INTERACTIONS:  │    │INTERACTIONS:  │
│Creates users, │    │Updates config │    │Views activity │
│Assigns roles, │    │Manages        │    │Monitors       │
│Manages perms  │    │integrations   │    │security       │
└───────────────┘    └───────────────┘    └───────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      REPORTS                                   │
│                   (pages/reports.php)                          │
├─────────────────────────────────────────────────────────────────┤
│ FUNCTION: Generate Comprehensive System Reports                 │
│ ACCESS: Admin, HR Manager                                       │
│ DATA: Aggregated data, Performance metrics, Compliance          │
│ INTERACTIONS: Generates reports, Exports data, Customizes views │
└─────────────────────────────────────────────────────────────────┘
```

## 🔄 DATA FLOW PATTERNS

### 1. COMPETENCY EVALUATION FLOW
```
Models → Cycles → Evaluations → Forms → Scores → Reports → My Evaluations
```

### 2. LEARNING MANAGEMENT FLOW
```
Learning Mgmt → Training Mgmt → Requests → Sessions → Completion → Reports
```

### 3. EMPLOYEE SELF-SERVICE FLOW
```
Profile → Requests → Approvals → Updates → Notifications
```

### 4. ADMINISTRATION FLOW
```
User Mgmt → Settings → Logs → Reports → System Monitoring
```

## 🗄️ DATABASE INTERACTIONS

### Core Tables Used by Each Module:

**Competency Module:**
- `competency_models` - Framework definitions
- `competencies` - Individual competencies
- `evaluation_cycles` - Evaluation periods
- `evaluations` - Assignment records
- `evaluation_scores` - Actual scores

**Learning Module:**
- `training_modules` - Course definitions
- `training_sessions` - Scheduled sessions
- `enrollments` - Student enrollments
- `training_requests` - Request management

**User Management:**
- `users` - User accounts
- `user_sessions` - Session tracking
- `system_logs` - Activity logging

**Succession Planning:**
- `succession_plans` - Planning data
- `critical_roles` - Key positions
- `candidates` - Successor candidates

## 🔐 ACCESS CONTROL MATRIX

| Page | Admin | HR Manager | Employee |
|------|-------|------------|----------|
| Dashboard | ✅ | ✅ | ✅ |
| Competency Models | ✅ | ✅ | ❌ |
| Evaluation Cycles | ✅ | ✅ | ❌ |
| Evaluations | ✅ | ✅ | ❌ |
| Evaluation Form | ✅ | ✅ | ✅* |
| Competency Reports | ✅ | ✅ | ❌ |
| My Evaluations | ✅ | ✅ | ✅ |
| Employee Self-Service | ✅ | ✅ | ✅ |
| Learning Management | ✅ | ✅ | ❌ |
| Training Management | ✅ | ✅ | ❌ |
| Training Requests | ✅ | ✅ | ❌ |
| Succession Planning | ✅ | ✅ | ❌ |
| User Management | ✅ | ❌ | ❌ |
| System Settings | ✅ | ❌ | ❌ |
| System Logs | ✅ | ❌ | ❌ |
| Reports | ✅ | ✅ | ❌ |

*Only for assigned evaluations

## 🎯 KEY INTERACTIONS SUMMARY

1. **Authentication Flow:** Login → Role Check → Page Access
2. **Data Flow:** User Input → Validation → Database → Response
3. **Navigation Flow:** Sidebar → Page Router → Module Page → Actions
4. **Permission Flow:** Action Request → Role Check → Allow/Deny
5. **Report Flow:** Data Collection → Filtering → Processing → Display

This comprehensive system provides a complete HR management solution with role-based access, data integrity, and seamless user interactions across all modules.
