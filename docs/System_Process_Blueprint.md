# System Process Blueprint (Current HR2)

This document captures the current process, flow, and design structure so a clean rebuild can preserve behavior and UX.

## 1) Entry and Security Flow

Flow:
1. User visits the app root -> `index.php`.
2. If not logged in, redirect to `auth/login.php`.
3. Login uses `includes/functions/simple_auth.php` to authenticate and set session:
   - `user_id`, `username`, `role`, `logged_in`
4. Role-based page access is enforced in `index.php` before any processing.
5. Terms acceptance check runs and can block usage via modal.

Key files:
- `auth/login.php`
- `includes/functions/simple_auth.php`
- `includes/functions/terms_acceptance.php`
- `index.php`

## 2) Front Controller Pattern

- Single entry point: `index.php`
- All page requests are `?page=...`
- `index.php` routes to `pages/*.php`
- Most form handlers either:
  - Post to `index.php` (handled before HTML), or
  - Post back to the same page

## 3) Module Processes

### A) Competency Management + Evaluations

Process:
1. Create competency models and competencies.
2. Create evaluation cycles (probationary, quarterly, annual, etc.).
3. Assign evaluations to employees.
4. Evaluator submits scores and comments.
5. Generate reports.
6. Run AI analysis on completed evaluations.

Key pages:
- `pages/competency_models.php`
- `pages/competency.php`
- `pages/evaluation_cycles.php`
- `pages/evaluations.php`
- `pages/evaluation_form.php`
- `pages/competency_reports.php`
- `pages/ai_analysis_dashboard.php`

Key functions:
- `includes/functions/competency.php`
- `includes/functions/huggingface_ai.php`
- `ajax/run_ai_analysis.php`
- `ajax/bulk_ai_analysis.php`
- `ajax/get_analysis_details.php`

### B) Learning and Training Management

Process:
1. Create training modules (catalog).
2. Schedule training sessions.
3. Enroll employees into sessions.
4. Collect training feedback and mark completion.
5. Manage training requests (approve/reject and optional auto-enroll).

Key pages:
- `pages/learning_management.php`
- `pages/training_management.php`
- `pages/training_feedback_management.php`
- `pages/training_requests.php`

Key functions:
- `includes/functions/learning.php`
- `includes/functions/notification_manager.php`

### C) Learning Materials (ESS Requests)

Process:
1. Employee requests learning materials.
2. HR reviews and approves/rejects.
3. Employee accesses approved materials.

Key pages:
- `pages/employee_learning_materials.php`
- `pages/hr_learning_requests.php`
- `pages/employee_learning_access.php`

### D) Succession Planning

Process:
1. Create critical roles.
2. Assign candidates to roles.
3. Track readiness level and review dates.
4. Update or remove candidates.

Key pages:
- `pages/succession_planning.php`

Key functions:
- `includes/functions/succession_planning.php`

### E) Employee Self-Service (ESS)

Process:
1. Employee views dashboard stats (evaluations, trainings, requests).
2. Updates profile.
3. Views own evaluations and trainings.
4. Submits training and learning material requests.

Key pages:
- `pages/employee_self_service.php`
- `pages/employee_portal.php`
- `pages/my_evaluations.php`
- `pages/my_trainings.php`
- `pages/my_requests.php`
- `pages/employee_training_requests.php`

## 4) Supporting Services and Cross-Cutting Features

- Notifications: `includes/functions/notification_manager.php`
- Activity logging: `SimpleAuth::logActivity()` -> `activity_logs` or `system_logs`
- AI analysis: `includes/functions/huggingface_ai.php`
- Recommendations: `includes/functions/smart_recommendations.php`

## 5) UI and Design Structure (Keep Intact)

- Layout includes:
  - `partials/header.php`
  - `partials/sidebar.php`
  - `partials/footer.php`
- Assets:
  - `assets/vendor/css/app-light.css`
  - `assets/css/hr-main.css`
- UX pattern:
  - Cards, tables, and modal forms
  - Post/Redirect/Get for form submissions

## 6) Core Data Entities (Behavioral Contract)

These tables/functions define expected behavior. Preserve structure or provide a clear migration strategy.

Core tables (representative):
- users, roles, user_sessions
- competency_models, competencies, competency_scores
- evaluation_cycles, evaluations
- training_modules, training_sessions, training_enrollments, training_requests
- employee_requests (learning material requests)
- critical_roles, succession_candidates
- ai_analysis_results
- activity_logs, system_logs

## 7) Page-to-Process Map (Quick Reference)

- Competency: `competency_models`, `competency`, `evaluation_cycles`, `evaluations`, `evaluation_form`, `competency_reports`
- Learning/Training: `learning_management`, `training_management`, `training_feedback_management`, `training_requests`
- Succession: `succession_planning`
- Employee: `employee_self_service`, `employee_portal`, `my_evaluations`, `my_trainings`, `my_requests`, `employee_training_requests`
- AI: `ai_analysis_dashboard`

## 8) Text Swim-Lane Diagrams (Current Behavior)

### 8.1 Competency Evaluation Flow

HR/Manager            System                        Employee
---------             ------                        --------
Create model/cycle -> store model/cycle
Assign evaluation  -> create evaluation record  -> sees assigned eval
Evaluator scores   -> save scores/comments
Run AI analysis    -> store analysis summary/sentiment
View reports       -> show dashboards/reports

### 8.2 Training Request Flow

Employee             System                        HR/Manager
--------             ------                        ----------
Request training -> create training_request
                    notify HR
                                            Review request
                                            Approve/Reject
                    update status + optional enrollment
Employee sees status update

### 8.3 Succession Planning Flow

HR/Manager            System
---------             ------
Create critical role -> save role
Assign candidate     -> save candidate + readiness
Update readiness     -> save updates, next review
Remove candidate     -> remove record

---

If you want, I can also produce a "Clean Rebuild Blueprint" (folder layout + layers + migration plan)
that matches these processes exactly.
