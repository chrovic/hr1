# Clean Rebuild Blueprint (Process-Compatible)

This blueprint proposes a clean architecture that preserves the existing HR2 processes and UI behavior.

## 1) Goals
- Preserve current processes and UX while improving structure and maintainability.
- Separate routing, controllers, services, and data access.
- Keep current DB contract unless explicitly migrated.
- Enable gradual rebuild and phased replacement.
- Keep SQL out of controllers/views and UI out of services.

## 2) Proposed Architecture

Pattern:
- Front Controller + Router + MVC-style layers
- Services encapsulate business logic
- Repositories handle DB queries
- Views contain templates only (no heavy logic)

Dependency flow:
- Controller -> Service -> Repository -> DB

Key layers:
- Controllers: request handling and validation
- Services: process workflows (business logic)
- Repositories: DB access and queries
- Views: HTML templates (components/partials)
- Middleware: auth, RBAC, terms acceptance

## 3) Recommended Folder Structure

```
app/
  Controllers/
    AuthController.php
    DashboardController.php
    Competency/
    Learning/
    Succession/
    Employee/
  Services/
    AuthService.php
    CompetencyService.php
    LearningService.php
    SuccessionService.php
    EmployeeService.php
    NotificationService.php
    AIService.php
    AuditLogService.php
  Repositories/
    UserRepository.php
    CompetencyRepository.php
    EvaluationRepository.php
    TrainingRepository.php
    SuccessionRepository.php
    RequestRepository.php
  Models/ (optional DTOs)
  Middleware/
    AuthMiddleware.php
    RoleMiddleware.php
    TermsMiddleware.php
  Support/
    Validator.php
    Response.php
    View.php
    DB.php
config/
  app.php
  database.php
  huggingface.php
routes/
  web.php
database/
  migrations/
  seeders/
public/
  index.php  (single entry)
views/
  layouts/
    main.php
  partials/
    header.php
    sidebar.php
    footer.php
  pages/
    dashboard/
    competency/
    learning/
    succession/
    employee/
assets/
  css/
  js/
storage/
  logs/
tests/
```

## 4) Routing Map (Keep Process)

Example `routes/web.php`:
- `/login` -> AuthController@login
- `/logout` -> AuthController@logout
- `/dashboard` -> DashboardController@index

Competency:
- `/competency/models`
- `/competency/cycles`
- `/competency/evaluations`
- `/competency/evaluations/{id}`
- `/competency/reports`
- `/competency/ai-analysis`

Learning/Training:
- `/learning/catalog`
- `/learning/sessions`
- `/learning/requests`
- `/learning/feedback`

Succession:
- `/succession/roles`
- `/succession/candidates`

Employee (ESS):
- `/employee/self-service`
- `/employee/evaluations`
- `/employee/trainings`
- `/employee/requests`
- `/employee/learning-materials`

## 5) Module Service Contracts (Process Intact)

CompetencyService:
- createModel(), addCompetency()
- createCycle(), assignEvaluation()
- submitScores()
- generateReports()
- runAIAnalysis()

LearningService:
- createModule(), updateModule(), deleteModule()
- scheduleSession(), updateSession()
- enrollEmployee(), submitFeedback()
- approveRequest(), rejectRequest()

SuccessionService:
- createCriticalRole()
- assignCandidate()
- updateReadiness()
- removeCandidate()

EmployeeService:
- updateProfile()
- listEvaluations(), listTrainings(), listRequests()
- requestTraining(), requestLearningMaterial()

AIService:
- analyzeSentiment()
- summarizeText()
- recommendTraining()
- (adapter to Hugging Face or stub)

## 6) Middleware Rules (Same Security Model)

AuthMiddleware:
- redirect to login if not authenticated

RoleMiddleware:
- enforce same role-based page access as current `index.php` map

TermsMiddleware:
- block and require acceptance before using system

## 7) Data Layer (Contract Preservation)

Keep tables:
- users, evaluations, competency_models, competencies, competency_scores
- training_modules, training_sessions, training_enrollments, training_requests
- employee_requests, critical_roles, succession_candidates
- ai_analysis_results, activity_logs/system_logs

Repositories should map to these tables with explicit query methods.

## 8) Migration Strategy (Phased Rebuild)

Phase 0: Freeze
- Capture DB schema and behavior (current process blueprint).

Phase 1: Skeleton App
- Create new structure with routing, middleware, layout, and auth.
- Keep existing CSS and partials to preserve UI.

Phase 2: Core Modules
- Implement modules in priority order:
  1) Auth + Dashboard
  2) Competency (models/cycles/evaluations)
  3) Learning/Training
  4) Succession
  5) ESS

Phase 3: Integrations
- AI analysis service (HF adapter with fallback)
- Notifications + logging

Phase 4: Cutover
- Parallel run old vs new pages
- Replace routes incrementally
- Verify processes and role access

## 9) UI Preservation Plan

Keep:
- `assets/vendor/css/app-light.css`
- `assets/css/hr-main.css`
- `partials/header.php`, `partials/sidebar.php`, `partials/footer.php`

Views in `views/pages/*` should reuse current markup to keep design intact.

## 10) Minimal Tests to Protect Process

- Auth login/logout
- Role access matrix (page vs role)
- Competency lifecycle: create model -> assign -> submit -> report
- Training lifecycle: create module -> schedule -> enroll -> complete
- Succession lifecycle: create role -> assign candidate -> update readiness
- AI analysis fallback when external API is unavailable

---

If you want, I can also generate:
- a database schema export + entity map
- a runnable skeleton in `public/` + `app/`
- a migration checklist with verification steps
