<?php
session_start();
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/terms_acceptance.php';

$auth = new SimpleAuth();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Role-based page access control before any processing
// Define allowed pages per role
$roleToPages = [
	'admin' => '*',
	'hr_manager' => '*',
	'competency_manager' => [
		'competency', 'competency_models', 'evaluation_cycles', 'evaluations', 'competency_reports', 'ai_analysis_dashboard', 'dashboard'
	],
	'learning_training_manager' => [
		'learning_management', 'learning_management_enhanced', 'training_management', 'training_requests', 'training_feedback_management', 'hr_learning_requests', 'dashboard'
	],
	'succession_manager' => [
		'succession_planning', 'succession_plans', 'succession_pipeline', 'candidate_details', 'succession_reports', 'dashboard'
	],
	'employee' => [
		'dashboard', 'employee_self_service', 'employee_profile', 'employee_portal', 'my_evaluations', 'evaluation_view', 'my_trainings', 'my_requests', 'employee_learning_materials', 'employee_learning_access', 'employee_training_requests'
	]
];

$role = $_SESSION['role'] ?? 'employee';
if (isset($roleToPages[$role])) {
	$allowed = $roleToPages[$role];
	if ($allowed !== '*' && !in_array($page, $allowed, true)) {
		// Redirect to dashboard if page not allowed for this role
		header('Location: ?page=dashboard');
		exit;
	}
}

// Check terms acceptance
$conn = getDB();
$termsAcceptance = new TermsAcceptance($conn);
$userId = $current_user['id'] ?? null;
$termsAccepted = $userId ? $termsAcceptance->hasUserAcceptedTerms($userId) : false;

// Handle competency models form processing BEFORE any HTML output
if ($page === 'competency_models' && $_POST) {
    require_once 'includes/functions/competency.php';
    
    $competencyManager = new CompetencyManager();
    
    if (isset($_POST['create_model'])) {
        $modelData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'category' => $_POST['category'],
            'target_roles' => explode(',', $_POST['target_roles']),
            'assessment_method' => $_POST['assessment_method'],
            'created_by' => $current_user['id']
        ];
        
        if ($competencyManager->createModel($modelData)) {
            $auth->logActivity('create_competency_model', 'competency_models', null, null, $modelData);
            header('Location: ?page=competency_models&success=model_created');
            exit;
        }
    }
    
    if (isset($_POST['add_competency'])) {
        $competencyData = [
            'model_id' => $_POST['model_id'],
            'name' => $_POST['competency_name'],
            'description' => $_POST['competency_description'],
            'weight' => $_POST['weight'],
            'max_score' => $_POST['max_score']
        ];
        
        if ($competencyManager->addCompetency($competencyData)) {
            $auth->logActivity('add_competency', 'competencies', null, null, $competencyData);
            header('Location: ?page=competency_models&success=competency_added');
            exit;
        }
    }
    
    if (isset($_POST['update_model'])) {
        $modelId = $_POST['model_id'];
        $updateData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'category' => $_POST['category'],
            'target_roles' => explode(',', $_POST['target_roles']),
            'assessment_method' => $_POST['assessment_method'],
            'status' => $_POST['status']
        ];
        
        if ($competencyManager->updateModel($modelId, $updateData, $current_user['first_name'] . ' ' . $current_user['last_name'])) {
            $auth->logActivity('update_competency_model', 'competency_models', $modelId, null, $updateData);
            header('Location: ?page=competency_models&success=model_updated');
            exit;
        }
    }
    
    if (isset($_POST['delete_model'])) {
        $modelId = $_POST['model_id'];
        
        if ($competencyManager->deleteModel($modelId)) {
            $auth->logActivity('delete_competency_model', 'competency_models', $modelId, null, null);
            header('Location: ?page=competency_models&success=model_deleted');
            exit;
        }
    }
    
    if (isset($_POST['update_competency'])) {
        $competencyId = $_POST['competency_id'];
        $updateData = [
            'name' => $_POST['competency_name'],
            'description' => $_POST['competency_description'],
            'weight' => $_POST['weight'],
            'max_score' => $_POST['max_score']
        ];
        
        if ($competencyManager->updateCompetency($competencyId, $updateData)) {
            $auth->logActivity('update_competency', 'competencies', $competencyId, null, $updateData);
            header('Location: ?page=competency_models&success=competency_updated');
            exit;
        }
    }
    
    if (isset($_POST['delete_competency'])) {
        $competencyId = $_POST['competency_id'];
        
        if ($competencyManager->deleteCompetency($competencyId)) {
            $auth->logActivity('delete_competency', 'competencies', $competencyId, null, null);
            header('Location: ?page=competency_models&success=competency_deleted');
            exit;
        }
    }
}

// Handle evaluation cycles form processing BEFORE any HTML output
if ($page === 'evaluation_cycles' && $_POST) {
    require_once 'includes/functions/competency.php';
    
    $competencyManager = new CompetencyManager();
    
    if (isset($_POST['create_cycle'])) {
        $cycleData = [
            'name' => $_POST['name'],
            'type' => $_POST['type'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'created_by' => $current_user['id']
        ];
        
        if ($competencyManager->createEvaluationCycle($cycleData)) {
            $auth->logActivity('create_evaluation_cycle', 'evaluation_cycles', null, null, $cycleData);
            header('Location: ?page=evaluation_cycles&success=cycle_created');
            exit;
        }
    }
    
    if (isset($_POST['update_cycle'])) {
        $cycleId = $_POST['cycle_id'];
        $updateData = [
            'name' => $_POST['name'],
            'type' => $_POST['type'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date']
        ];
        
        if ($competencyManager->updateEvaluationCycle($cycleId, $updateData, $current_user['first_name'] . ' ' . $current_user['last_name'])) {
            $auth->logActivity('update_evaluation_cycle', 'evaluation_cycles', $cycleId, null, $updateData);
            header('Location: ?page=evaluation_cycles&success=cycle_updated');
            exit;
        }
    }
    
    if (isset($_POST['delete_cycle'])) {
        $cycleId = $_POST['cycle_id'];
        
        if ($competencyManager->deleteEvaluationCycle($cycleId, $current_user['first_name'] . ' ' . $current_user['last_name'])) {
            $auth->logActivity('delete_evaluation_cycle', 'evaluation_cycles', $cycleId, null, null);
            header('Location: ?page=evaluation_cycles&success=cycle_deleted');
            exit;
        }
    }
}

// Handle learning management form processing BEFORE any HTML output
if ($page === 'learning_management' && $_POST) {
    require_once 'includes/functions/learning.php';
    
    $learningManager = new LearningManager();
    
    if (isset($_POST['create_course'])) {
        $courseData = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'category' => $_POST['category'],
            'type' => $_POST['type'],
            'duration_hours' => $_POST['duration_hours'],
            'max_participants' => $_POST['max_participants'],
            'prerequisites' => $_POST['prerequisites'],
            'learning_objectives' => $_POST['learning_objectives'],
            'created_by' => $current_user['id']
        ];
        
        if ($learningManager->createTraining($courseData)) {
            $auth->logActivity('create_course', 'training_catalog', null, null, $courseData);
            header('Location: ?page=learning_management&success=course_created');
            exit;
        }
    }
    
    if (isset($_POST['update_course'])) {
        $courseId = $_POST['course_id'];
        $updateData = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'category' => $_POST['category'],
            'type' => $_POST['type'],
            'duration_hours' => $_POST['duration_hours'],
            'max_participants' => $_POST['max_participants'],
            'prerequisites' => $_POST['prerequisites'],
            'learning_objectives' => $_POST['learning_objectives']
        ];
        
        if ($learningManager->updateTraining($courseId, $updateData, $current_user['id'])) {
            $auth->logActivity('update_course', 'training_catalog', $courseId, null, $updateData);
            header('Location: ?page=learning_management&success=course_updated');
            exit;
        }
    }
    
    if (isset($_POST['delete_course'])) {
        $courseId = $_POST['course_id'];
        
        if ($learningManager->deleteTraining($courseId, $current_user['id'])) {
            $auth->logActivity('delete_course', 'training_catalog', $courseId, null, null);
            header('Location: ?page=learning_management&success=course_deleted');
            exit;
        }
    }
}

// Handle training management form processing BEFORE any HTML output
if ($page === 'training_management' && $_POST) {
    require_once 'includes/functions/learning.php';
    
    $learningManager = new LearningManager();
    
    if (isset($_POST['submit_feedback'])) {
        $enrollmentId = $_POST['enrollment_id'];
        $score = $_POST['score'];
        $feedback = $_POST['feedback'];
        $completionStatus = $_POST['completion_status'];
        
        $db = getDB();
        $stmt = $db->prepare("
            UPDATE training_enrollments 
            SET score = ?, feedback = ?, completion_status = ?, completion_date = NOW(), updated_at = NOW()
            WHERE id = ?
        ");
        
        if ($stmt->execute([$score, $feedback, $completionStatus, $enrollmentId])) {
            $auth->logActivity('submit_training_feedback', 'training_enrollments', $enrollmentId, null, [
                'score' => $score,
                'completion_status' => $completionStatus
            ]);
            
            // Send notifications for feedback submission
            try {
                require_once 'includes/functions/notification_manager.php';
                $notificationManager = new NotificationManager();
                
                // Get enrollment details for notifications
                $stmt = $db->prepare("
                    SELECT te.employee_id, te.score as old_score, ts.session_name, tm.title as course_title,
                           u.first_name as employee_first_name, u.last_name as employee_last_name
                    FROM training_enrollments te
                    JOIN training_sessions ts ON te.session_id = ts.id
                    JOIN training_modules tm ON ts.module_id = tm.id
                    JOIN users u ON te.employee_id = u.id
                    WHERE te.id = ?
                ");
                $stmt->execute([$enrollmentId]);
                $enrollment = $stmt->fetch();
                
                if ($enrollment) {
                    // Notify the employee about their feedback/score
                    $notificationManager->createNotification(
                        $enrollment['employee_id'],
                        'feedback_submitted',
                        'Training Feedback Submitted',
                        'Feedback has been submitted for your training "' . $enrollment['course_title'] . '" with a score of ' . $score . '.',
                        $enrollmentId,
                        'enrollment',
                        '?page=my_trainings',
                        true
                    );
                    
                    // Notify learning stakeholders (admins, HR, learning & training managers)
                    $stmt = $db->prepare("SELECT id FROM users WHERE role IN ('admin', 'hr_manager', 'learning_training_manager') AND status = 'active'");
                    $stmt->execute();
                    $hrUsers = $stmt->fetchAll();
                    
                    foreach ($hrUsers as $hrUser) {
                        $notificationManager->createNotification(
                            $hrUser['id'],
                            'feedback_submitted',
                            'Training Feedback Submitted',
                            'Feedback has been submitted for ' . $enrollment['employee_first_name'] . ' ' . $enrollment['employee_last_name'] . '\'s training "' . $enrollment['course_title'] . '" with a score of ' . $score . ' by ' . $current_user['first_name'] . ' ' . $current_user['last_name'] . '.',
                            $enrollmentId,
                            'enrollment',
                            '?page=training_management',
                            true
                        );
                    }
                }
            } catch (Exception $e) {
                // Log notification error but don't fail the feedback submission
                error_log("Notification error for feedback submission: " . $e->getMessage());
            }
            
            header('Location: ?page=training_management&success=feedback_submitted');
            exit;
        }
    }
    
    if (isset($_POST['create_session'])) {
        $sessionData = [
            'training_id' => $_POST['training_id'],
            'session_name' => $_POST['session_name'],
            'session_date' => $_POST['session_date'],
            'start_time' => $_POST['start_time'],
            'end_time' => $_POST['end_time'],
            'location' => $_POST['location'],
            'trainer_id' => $_POST['trainer_id'],
            'max_participants' => $_POST['max_participants'],
            'status' => $_POST['status'],
            'created_by' => $current_user['id']
        ];
        
        if ($learningManager->scheduleSession($sessionData)) {
            $auth->logActivity('create_session', 'training_sessions', null, null, $sessionData);
            header('Location: ?page=training_management&success=session_created');
            exit;
        }
    }
    
    if (isset($_POST['update_session'])) {
        $sessionData = [
            'training_id' => $_POST['edit_training_id'],
            'session_name' => $_POST['edit_session_name'],
            'start_date' => $_POST['edit_start_date'],
            'end_date' => $_POST['edit_end_date'],
            'location' => $_POST['edit_location'],
            'trainer_id' => $_POST['edit_trainer_id'],
            'max_participants' => $_POST['edit_max_participants'],
            'status' => $_POST['edit_status'],
            'updated_by' => $current_user['id']
        ];
        
        $sessionId = $_POST['edit_session_id'];
        
        if ($learningManager->updateSession($sessionId, $sessionData)) {
            $auth->logActivity('update_session', 'training_sessions', $sessionId, null, $sessionData);
            header('Location: ?page=training_management&success=session_updated');
            exit;
        }
    }
    
    if (isset($_POST['enroll_employee'])) {
        $enrollmentData = [
            'session_id' => $_POST['session_id'],
            'employee_id' => $_POST['employee_id'],
            'enrollment_date' => date('Y-m-d H:i:s'),
            'status' => 'enrolled',
            'enrolled_by' => $current_user['id'] // Add the person who is enrolling the employee
        ];
        
        if ($learningManager->enrollEmployee($enrollmentData)) {
            $auth->logActivity('enroll_employee', 'training_enrollments', null, null, $enrollmentData);
            header('Location: ?page=training_management&success=employee_enrolled');
            exit;
        }
    }
}

// Handle succession planning form processing BEFORE any HTML output (PRG-safe)
if ($page === 'succession_planning' && $_POST) {
    require_once 'includes/functions/succession_planning.php';
    require_once 'includes/functions/notification_manager.php';

    $successionManager = new SuccessionPlanning();
    $notificationManager = new NotificationManager();
    $db = getDB();
    $returnUrl = $_POST['return_url'] ?? null;
    $redirectBase = (is_string($returnUrl) && strpos($returnUrl, '?page=') === 0) ? $returnUrl : '?page=succession_planning';

    // Create Critical Role
    if (isset($_POST['create_role'])) {
        $roleData = [
            'position_title' => $_POST['position_title'],
            'department' => $_POST['department'],
            'level' => $_POST['level'] ?? null,
            'description' => $_POST['description'],
            'requirements' => $_POST['requirements'] ?? null,
            'risk_level' => $_POST['risk_level'] ?? 'medium',
            'current_incumbent_id' => $_POST['current_incumbent_id'] ?: null,
            'created_by' => $current_user['id']
        ];

        if ($successionManager->createCriticalRole($roleData)) {
            $auth->logActivity('create_critical_role', 'critical_roles', null, null, $roleData);

            // Notify HR and Succession Managers
            try {
                $notificationManager->notifySuccessionManagers(
                    'model_created',
                    [
                        'role_title' => $roleData['position_title'],
                        'department' => $roleData['department'],
                        'risk_level' => $roleData['risk_level'],
                        'created_by' => $current_user['first_name'] . ' ' . $current_user['last_name']
                    ],
                    null,
                    'model',
                    '?page=succession_planning',
                    true
                );
            } catch (Exception $e) { /* ignore */ }

            header('Location: ?page=succession_planning&success=role_created');
            exit;
        } else {
            header('Location: ?page=succession_planning&error=role_create_failed');
            exit;
        }
    }

    // Assign Candidate
    if (isset($_POST['assign_candidate'])) {
        $candidateData = [
            'role_id' => $_POST['role_id'],
            'employee_id' => $_POST['employee_id'],
            'readiness_level' => $_POST['readiness_level'],
            'development_plan' => $_POST['development_plan'],
            'notes' => $_POST['notes'],
            'assessment_date' => $_POST['assessment_date'],
            'next_review_date' => $_POST['next_review_date'],
            'assigned_by' => $current_user['id']
        ];

        if ($successionManager->assignCandidate($candidateData)) {
            $auth->logActivity('assign_succession_candidate', 'succession_candidates', null, null, $candidateData);

            // Notifications
            try {
                // Employee
                $stmt = $db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
                $stmt->execute([$candidateData['employee_id']]);
                $emp = $stmt->fetch();
                $employeeName = $emp ? ($emp['first_name'] . ' ' . $emp['last_name']) : 'Employee';

                $stmt = $db->prepare("SELECT position_title, department FROM critical_positions WHERE id = ?");
                $stmt->execute([$candidateData['role_id']]);
                $role = $stmt->fetch();
                $roleTitle = $role ? $role['position_title'] : 'Critical Role';
                $roleDept = $role ? $role['department'] : '';

                $notificationManager->createNotification(
                    $candidateData['employee_id'],
                    'evaluation_assigned',
                    'Succession Candidate Assignment',
                    $current_user['first_name'] . ' ' . $current_user['last_name'] . ' assigned you as a succession candidate for "' . $roleTitle . '" ' . ($roleDept ? '(' . $roleDept . ')' : '') . '.',
                    null,
                    null,
                    '?page=succession_planning',
                    true
                );

                // Notify HR and Succession Managers
                $stmt = $db->prepare("SELECT id FROM users WHERE role IN ('admin', 'hr_manager', 'succession_manager') AND status = 'active'");
                $stmt->execute();
                $hrUsers = $stmt->fetchAll();
                
                foreach ($hrUsers as $hrUser) {
                    $notificationManager->createNotification(
                        $hrUser['id'],
                        'evaluation_assigned',
                        'Succession Candidate Assigned',
                        $current_user['first_name'] . ' ' . $current_user['last_name'] . ' assigned ' . $employeeName . ' as a succession candidate for "' . $roleTitle . '" ' . ($roleDept ? '(' . $roleDept . ')' : '') . '.',
                        null,
                        null,
                        '?page=succession_planning',
                        true
                    );
                }
            } catch (Exception $e) { /* ignore */ }

            header('Location: ' . $redirectBase . '&success=candidate_assigned');
            exit;
        } else {
            header('Location: ' . $redirectBase . '&error=candidate_assign_failed');
            exit;
        }
    }

    // Update Critical Role
    if (isset($_POST['update_role'])) {
        $roleId = $_POST['role_id'];
        $updateData = [
            'position_title' => $_POST['position_title'],
            'department' => $_POST['department'],
            'level' => $_POST['level'] ?? null,
            'description' => $_POST['description'] ?? null,
            'requirements' => $_POST['requirements'] ?? null,
            'risk_level' => $_POST['risk_level'] ?? 'medium',
            'current_incumbent_id' => $_POST['current_incumbent_id'] ?: null,
        ];

        if ($successionManager->updateCriticalRole($roleId, $updateData)) {
            $auth->logActivity('update_critical_role', 'critical_positions', $roleId, null, $updateData);
            // Notify HR and Succession Managers about update
            try {
                $notificationManager->notifySuccessionManagers(
                    'model_updated',
                    [
                        'role_title' => $updateData['position_title'],
                        'department' => $updateData['department'],
                        'risk_level' => $updateData['risk_level'],
                        'updated_by' => $current_user['first_name'] . ' ' . $current_user['last_name']
                    ],
                    $roleId,
                    'model',
                    '?page=succession_planning',
                    true
                );
            } catch (Exception $e) { /* ignore */ }
            header('Location: ?page=succession_planning&success=role_updated');
            exit;
        } else {
            header('Location: ?page=succession_planning&error=role_update_failed');
            exit;
        }
    }

    // Update Candidate
    if (isset($_POST['update_candidate'])) {
        $candidateId = $_POST['candidate_id'];
        $updateData = [
            'readiness_level' => $_POST['readiness_level'],
            'development_plan' => $_POST['development_plan'],
            'notes' => $_POST['notes'],
            'assessment_date' => $_POST['assessment_date'],
            'next_review_date' => $_POST['next_review_date']
        ];

        if ($successionManager->updateCandidateReadiness($candidateId, $updateData)) {
            $auth->logActivity('update_candidate_readiness', 'succession_candidates', $candidateId, null, $updateData);

            // Notifications
            try {
                $stmt = $db->prepare("SELECT sc.employee_id, u.first_name, u.last_name, cr.position_title
                                       FROM succession_candidates sc
                                       JOIN users u ON sc.employee_id = u.id
                                       JOIN critical_positions cr ON sc.role_id = cr.id
                                       WHERE sc.id = ?");
                $stmt->execute([$candidateId]);
                if ($row = $stmt->fetch()) {
                    $employeeId = $row['employee_id'];
                    $employeeName = $row['first_name'] . ' ' . $row['last_name'];
                    $roleTitle = $row['position_title'];
                    $msg = 'Succession record updated for "' . $roleTitle . '". Readiness: ' . $updateData['readiness_level'] . '.';

                    $notificationManager->createNotification(
                        $employeeId,
                        'evaluation_completed',
                        'Succession Candidate Updated',
                        $msg,
                        null,
                        'succession',
                        '?page=succession_planning',
                        true
                    );

                    // Notify HR and Succession Managers
                    $notificationManager->notifySuccessionManagers(
                        'evaluation_completed',
                        [
                            'employee_name' => $employeeName,
                            'role_title' => $roleTitle,
                            'readiness_level' => $updateData['readiness_level'],
                            'updated_by' => $current_user['first_name'] . ' ' . $current_user['last_name']
                        ],
                        $candidateId,
                        'evaluation',
                        '?page=succession_planning',
                        true
                    );
                }
            } catch (Exception $e) { /* ignore */ }

            header('Location: ' . $redirectBase . '&success=candidate_updated');
            exit;
        } else {
            header('Location: ' . $redirectBase . '&error=candidate_update_failed');
            exit;
        }
    }

    // Remove Candidate
    if (isset($_POST['remove_candidate'])) {
        $candidateId = $_POST['candidate_id'];
        if ($successionManager->removeCandidate($candidateId)) {
            $auth->logActivity('remove_succession_candidate', 'succession_candidates', $candidateId, null, null);

            try {
                $employeeId = $_POST['employee_id'] ?? null;
                if ($employeeId) {
                    $stmt = $db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
                    $stmt->execute([$employeeId]);
                    $emp = $stmt->fetch();
                    $employeeName = $emp ? ($emp['first_name'] . ' ' . $emp['last_name']) : 'Employee';

                    $notificationManager->createNotification(
                        $employeeId,
                        'model_deleted',
                        'Removed from Succession Planning',
                        $current_user['first_name'] . ' ' . $current_user['last_name'] . ' removed you from a succession pipeline.',
                        null,
                        null,
                        '?page=succession_planning',
                        true
                    );

                    // Notify HR and Succession Managers
                    $stmt = $db->prepare("SELECT id FROM users WHERE role IN ('admin', 'hr_manager', 'succession_manager') AND status = 'active'");
                    $stmt->execute();
                    $hrUsers = $stmt->fetchAll();
                    
                    foreach ($hrUsers as $hrUser) {
                        $notificationManager->createNotification(
                            $hrUser['id'],
                            'model_deleted',
                            'Succession Candidate Removed',
                            $current_user['first_name'] . ' ' . $current_user['last_name'] . ' removed ' . $employeeName . ' from a succession pipeline.',
                            $candidateId,
                            'model',
                            '?page=succession_planning',
                            true
                        );
                    }
                }
            } catch (Exception $e) { /* ignore */ }

            header('Location: ' . $redirectBase . '&success=candidate_removed');
            exit;
        } else {
            header('Location: ' . $redirectBase . '&error=candidate_remove_failed');
            exit;
        }
    }

    // Delete Critical Role
    if (isset($_POST['delete_role'])) {
        $roleId = $_POST['role_id'];
        if ($successionManager->deleteCriticalRole($roleId)) {
            $auth->logActivity('delete_critical_role', 'critical_positions', $roleId, null, null);

            // Notify HR and Succession Managers
            try {
                // Get role details for notification
                $stmt = $db->prepare("SELECT position_title, department FROM critical_positions WHERE id = ?");
                $stmt->execute([$roleId]);
                $role = $stmt->fetch();
                $roleTitle = $role ? $role['position_title'] : 'Critical Role';
                $roleDept = $role ? $role['department'] : '';
                
                $stmt = $db->prepare("SELECT id FROM users WHERE role IN ('admin', 'hr_manager', 'succession_manager') AND status = 'active'");
                $stmt->execute();
                $hrUsers = $stmt->fetchAll();
                
                foreach ($hrUsers as $hrUser) {
                    $notificationManager->createNotification(
                        $hrUser['id'],
                        'model_deleted',
                        'Critical Role Deleted',
                        $current_user['first_name'] . ' ' . $current_user['last_name'] . ' deleted the critical role "' . $roleTitle . '" ' . ($roleDept ? '(' . $roleDept . ')' : '') . '.',
                        $roleId,
                        'model',
                        '?page=succession_planning',
                        true
                    );
                }
            } catch (Exception $e) { /* ignore */ }

            header('Location: ?page=succession_planning&success=role_deleted');
            exit;
        } else {
            header('Location: ?page=succession_planning&error=role_delete_failed');
            exit;
        }
    }
}

// Handle succession plans form processing BEFORE any HTML output (PRG-safe)
if ($page === 'succession_plans' && $_POST) {
    require_once 'includes/functions/succession_planning.php';
    require_once 'includes/functions/notification_manager.php';

    $successionManager = new SuccessionPlanning();
    $notificationManager = new NotificationManager();
    $db = getDB();

    if (isset($_POST['create_plan'])) {
        $planData = [
            'role_id' => $_POST['role_id'],
            'plan_name' => $_POST['plan_name'],
            'status' => $_POST['status'] ?? 'draft',
            'start_date' => $_POST['start_date'] ?: null,
            'end_date' => $_POST['end_date'] ?: null,
            'objectives' => $_POST['objectives'] ?? null,
            'success_metrics' => $_POST['success_metrics'] ?? null,
            'created_by' => $current_user['id']
        ];

        if ($successionManager->createSuccessionPlan($planData)) {
            $auth->logActivity('create_succession_plan', 'succession_plans', null, null, $planData);
            header('Location: ?page=succession_plans&success=plan_created');
            exit;
        }
        header('Location: ?page=succession_plans&error=plan_create_failed');
        exit;
    }

    if (isset($_POST['update_plan'])) {
        $planId = (int)($_POST['plan_id'] ?? 0);
        $updateData = [
            'plan_name' => $_POST['plan_name'],
            'role_id' => $_POST['role_id'],
            'status' => $_POST['status'] ?? 'draft',
            'start_date' => $_POST['start_date'] ?: null,
            'end_date' => $_POST['end_date'] ?: null,
            'objectives' => $_POST['objectives'] ?? null,
            'success_metrics' => $_POST['success_metrics'] ?? null
        ];

        if ($planId && $successionManager->updateSuccessionPlan($planId, $updateData)) {
            $auth->logActivity('update_succession_plan', 'succession_plans', $planId, null, $updateData);
            header('Location: ?page=succession_plans&success=plan_updated');
            exit;
        }
        header('Location: ?page=succession_plans&error=plan_update_failed');
        exit;
    }

    if (isset($_POST['add_plan_candidate'])) {
        $planId = (int)($_POST['plan_id'] ?? 0);
        $candidateId = (int)($_POST['candidate_id'] ?? 0);
        $priorityOrder = (int)($_POST['priority_order'] ?? 1);
        $targetReadinessDate = $_POST['target_readiness_date'] ?: null;
        $developmentFocus = $_POST['development_focus'] ?? null;

        $planRoleId = $planId ? $successionManager->getPlanRoleId($planId) : null;
        $candidateRoleId = $candidateId ? $successionManager->getCandidateRoleId($candidateId) : null;

        if ($planRoleId && $candidateRoleId && (string)$planRoleId !== (string)$candidateRoleId) {
            header('Location: ?page=succession_plans&error=candidate_add_failed');
            exit;
        }

        if ($planId && $candidateId && $successionManager->addPlanCandidate($planId, $candidateId, $priorityOrder, $targetReadinessDate, $developmentFocus)) {
            $auth->logActivity('add_plan_candidate', 'succession_plan_candidates', null, null, [
                'plan_id' => $planId,
                'candidate_id' => $candidateId
            ]);
            header('Location: ?page=succession_plans&success=candidate_added');
            exit;
        }
        header('Location: ?page=succession_plans&error=candidate_add_failed');
        exit;
    }

    if (isset($_POST['remove_plan_candidate'])) {
        $planCandidateId = (int)($_POST['plan_candidate_id'] ?? 0);

        if ($planCandidateId && $successionManager->removePlanCandidate($planCandidateId)) {
            $auth->logActivity('remove_plan_candidate', 'succession_plan_candidates', $planCandidateId, null, null);
            header('Location: ?page=succession_plans&success=candidate_removed');
            exit;
        }
        header('Location: ?page=succession_plans&error=candidate_remove_failed');
        exit;
    }
}

// Handle succession candidate assessment BEFORE any HTML output (PRG-safe)
if ($page === 'candidate_details' && $_POST && isset($_POST['add_assessment'])) {
    require_once 'includes/functions/succession_planning.php';

    $successionManager = new SuccessionPlanning();

    $candidateId = (int)($_POST['candidate_id'] ?? 0);
    $technicalScore = $_POST['technical_readiness_score'] !== '' ? (int)$_POST['technical_readiness_score'] : null;
    $leadershipScore = $_POST['leadership_readiness_score'] !== '' ? (int)$_POST['leadership_readiness_score'] : null;
    $culturalScore = $_POST['cultural_fit_score'] !== '' ? (int)$_POST['cultural_fit_score'] : null;
    $overallScore = $_POST['overall_readiness_score'] !== '' ? (int)$_POST['overall_readiness_score'] : null;
    if ($overallScore === null) {
        $scoreParts = array_filter([$technicalScore, $leadershipScore, $culturalScore], static function($value) {
            return $value !== null;
        });
        if (!empty($scoreParts)) {
            $overallScore = (int)round(array_sum($scoreParts) / count($scoreParts));
        }
    }

    $assessmentData = [
        'candidate_id' => $candidateId,
        'assessor_id' => $current_user['id'],
        'assessment_type' => $_POST['assessment_type'],
        'technical_readiness_score' => $technicalScore,
        'leadership_readiness_score' => $leadershipScore,
        'cultural_fit_score' => $culturalScore,
        'overall_readiness_score' => $overallScore,
        'strengths' => $_POST['strengths'] ?? null,
        'development_areas' => $_POST['development_areas'] ?? null,
        'recommendations' => $_POST['recommendations'] ?? null,
        'assessment_date' => $_POST['assessment_date']
    ];

    if ($candidateId && $successionManager->addAssessment($assessmentData)) {
        $auth->logActivity('add_succession_assessment', 'succession_assessments', null, null, $assessmentData);
        header('Location: ?page=candidate_details&id=' . $candidateId . '&success=assessment_added');
        exit;
    }
    header('Location: ?page=candidate_details&id=' . $candidateId . '&error=assessment_add_failed');
    exit;
}

// Handle evaluation form submissions BEFORE any HTML output
if ($page === 'evaluation_form' && $_POST && isset($_POST['submit_scores'])) {
    require_once 'includes/functions/competency.php';
    
    $competencyManager = new CompetencyManager();
    $evaluation_id = $_GET['id'] ?? 0;
    
    $scores = [];
    foreach ($_POST['scores'] as $competency_id => $score_data) {
        $scores[] = [
            'competency_id' => $competency_id,
            'score' => $score_data['score'],
            'comments' => $score_data['comments'] ?? ''
        ];
    }
    
    if ($competencyManager->submitScores($evaluation_id, $scores)) {
        $auth->logActivity('complete_evaluation', 'evaluations', $evaluation_id, null, ['scores_count' => count($scores)]);
        header('Location: ?page=evaluation_form&id=' . $evaluation_id . '&success=scores_submitted');
        exit;
    }
}

// Handle evaluations form processing BEFORE any HTML output
if ($page === 'evaluations' && $_POST && isset($_POST['assign_evaluation'])) {
    require_once 'includes/functions/competency.php';
    
    $competencyManager = new CompetencyManager();
    
    $evaluationData = [
        'cycle_id' => $_POST['cycle_id'],
        'employee_id' => $_POST['employee_id'],
        'evaluator_id' => $_POST['evaluator_id'],
        'model_id' => $_POST['model_id']
    ];
    
    // Check if evaluation already exists
    $stmt = $conn->prepare("SELECT id FROM evaluations WHERE cycle_id = ? AND employee_id = ? AND evaluator_id = ? AND model_id = ?");
    $stmt->execute([$evaluationData['cycle_id'], $evaluationData['employee_id'], $evaluationData['evaluator_id'], $evaluationData['model_id']]);
    
    if ($stmt->fetch()) {
        // Set error in session and redirect
        $_SESSION['evaluation_error'] = 'This evaluation already exists for the selected employee, cycle, and model.';
        header('Location: ?page=evaluations');
        exit;
    } else {
        if ($competencyManager->assignEvaluation($evaluationData)) {
            $auth->logActivity('assign_evaluation', 'evaluations', null, null, $evaluationData);
            header('Location: ?page=evaluations&success=1');
            exit;
        } else {
            $_SESSION['evaluation_error'] = 'Failed to assign evaluation.';
            header('Location: ?page=evaluations');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="HR2 - Human Resources Management System">
    <meta name="author" content="">
    <link rel="icon" href="assets/images/favicon.ico">
    <title>HR2 - Human Resources Management System</title>
    <!-- Simple bar CSS -->
    <link rel="stylesheet" href="assets/vendor/css/simplebar.css">
    <!-- Fonts CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Overpass:ital,wght@0,100;0,200;0,300;0,400;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet">
    <!-- Icons CSS -->
    <link rel="stylesheet" href="assets/vendor/css/feather.css">
    <link rel="stylesheet" href="assets/vendor/css/select2.css">
    <link rel="stylesheet" href="assets/vendor/css/dropzone.css">
    <link rel="stylesheet" href="assets/vendor/css/uppy.min.css">
    <link rel="stylesheet" href="assets/vendor/css/jquery.steps.css">
    <link rel="stylesheet" href="assets/vendor/css/jquery.timepicker.css">
    <link rel="stylesheet" href="assets/vendor/css/quill.snow.css">
    <!-- Date Range Picker CSS -->
    <link rel="stylesheet" href="assets/vendor/css/daterangepicker.css">
    <script>
    (function() {
        var mode = localStorage.getItem('mode');
        if (mode === 'dark') {
            document.documentElement.classList.add('dark');
        }
    })();
    </script>
    <!-- App CSS -->
    <link rel="stylesheet" href="assets/vendor/css/app-light.css" id="lightTheme">
    <link rel="stylesheet" href="assets/vendor/css/app-dark.css" id="darkTheme" disabled>
    <script>
    (function() {
        var mode = localStorage.getItem('mode');
        var light = document.getElementById('lightTheme');
        var dark = document.getElementById('darkTheme');
        if (mode === 'dark') {
            if (dark) { dark.disabled = false; }
            if (light) { light.disabled = true; }
        }
    })();
    </script>
    <!-- HR2 Custom CSS -->
    <link rel="stylesheet" href="assets/css/hr-main.css">
</head>
<body class="vertical light">
    <div class="wrapper d-flex flex-column min-vh-100">
        <?php include 'partials/header.php'; ?>
        
        <?php include 'partials/sidebar.php'; ?>
        
        <main role="main" class="main-content flex-grow-1">
            <div class="container-fluid">
                <?php
                switch($page) {
                    case 'dashboard':
                        include 'pages/dashboard.php';
                        break;
                    case 'employee_self_service':
                        include 'pages/employee_self_service.php';
                        break;
                    case 'employee_profile':
                        include 'pages/employee_profile.php';
                        break;
                    case 'employee_portal':
                        include 'pages/employee_portal.php';
                        break;
                    case 'learning_management':
                        include 'pages/learning_management.php';
                        break;
                    case 'learning_management_enhanced':
                        include 'pages/learning_management_enhanced.php';
                        break;
                    case 'training_feedback_management':
                        include 'pages/training_feedback_management.php';
                        break;
                    case 'training_management':
                        include 'pages/training_management.php';
                        break;
                    case 'succession_planning':
                        include 'pages/succession_planning.php';
                        break;
                    case 'succession_plans':
                        include 'pages/succession_plans.php';
                        break;
                    case 'succession_pipeline':
                        include 'pages/succession_pipeline.php';
                        break;
                    case 'candidate_details':
                        include 'pages/candidate_details.php';
                        break;
                    case 'succession_reports':
                        include 'pages/succession_reports.php';
                        break;
                    case 'competency':
                        include 'pages/competency.php';
                        break;
                    case 'competency_models':
                        include 'pages/competency_models.php';
                        break;
                    case 'evaluation_cycles':
                        include 'pages/evaluation_cycles.php';
                        break;
                    case 'evaluations':
                        include 'pages/evaluations.php';
                        break;
                    case 'evaluation_form':
                        include 'pages/evaluation_form.php';
                        break;
                    case 'competency_reports':
                        include 'pages/competency_reports.php';
                        break;
                    case 'ai_analysis_dashboard':
                        include 'pages/ai_analysis_dashboard.php';
                        break;
                    case 'my_evaluations':
                        include 'pages/my_evaluations.php';
                        break;
                    case 'my_trainings':
                        include 'pages/my_trainings.php';
                        break;
                    case 'my_requests':
                        include 'pages/my_requests.php';
                        break;
                        case 'employee_learning_materials':
                            include 'pages/employee_learning_materials.php';
                            break;
                        case 'employee_learning_access':
                            include 'pages/employee_learning_access.php';
                            break;
                        case 'hr_learning_requests':
                            include 'pages/hr_learning_requests.php';
                            break;
                    case 'employee_training_requests':
                        include 'pages/employee_training_requests.php';
                        break;
                    case 'training_requests':
                        include 'pages/training_requests.php';
                        break;
                    case 'employee_requests':
                        include 'pages/employee_requests.php';
                        break;
                    case 'user_management':
                        include 'pages/user_management.php';
                        break;
                    case 'system_settings':
                        include 'pages/system_settings.php';
                        break;
                    case 'system_logs':
                        include 'pages/system_logs.php';
                        break;
                    case 'reports':
                        include 'pages/reports.php';
                        break;
                    case 'profile':
                        include 'pages/profile.php';
                        break;
                    case 'settings':
                        include 'pages/settings.php';
                        break;
                    case 'activities':
                        include 'pages/activities.php';
                        break;
                    case 'hr_reports':
                        include 'pages/hr_reports.php';
                        break;
                    case 'hr_notifications':
                        include 'pages/hr_notifications.php';
                        break;
                    case 'hr_employee_management':
                        include 'pages/hr_employee_management.php';
                        break;
                    case 'hr_request_management':
                        include 'pages/hr_request_management.php';
                        break;
                    case 'admin_training_management':
                        include 'pages/admin_training_management.php';
                        break;
                    case 'evaluation_form':
                        include 'pages/evaluation_form.php';
                        break;
                    case 'evaluation_view':
                        include 'pages/evaluation_view.php';
                        break;
                    default:
                        include 'pages/404.php';
                        break;
                }
                ?>
            </div>
        </main>
        
        <?php include 'partials/footer.php'; ?>
    </div>

    <!-- Terms Acceptance Modal -->
    <?php if (!$termsAccepted): ?>
    <div class="modal fade" id="termsAcceptanceModal" tabindex="-1" role="dialog" aria-labelledby="termsAcceptanceModalLabel" aria-hidden="false" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="termsAcceptanceModalLabel">
                        <i class="fe fe-shield mr-2"></i>Terms and Conditions Acceptance Required
                    </h5>
                </div>
                <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                    <div class="alert alert-warning">
                        <i class="fe fe-alert-triangle mr-2"></i>
                        <strong>Important:</strong> You must accept our Terms and Conditions to continue using the HR2 system.
                    </div>
                    
                    <div class="terms-summary">
                        <h6 class="text-primary mb-3">HR2 Human Resources Management System - Terms and Conditions</h6>
                        
                        <div class="mb-3">
                            <h6 class="text-dark">1. Acceptance of Terms</h6>
                            <p class="text-justify small">
                                By accessing and using the HR2 Human Resources Management System, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions. These terms are governed by the laws of the Republic of the Philippines and are subject to the provisions of Republic Act No. 10173 (Data Privacy Act of 2012), Republic Act No. 10175 (Cybercrime Prevention Act of 2012), and other applicable Philippine laws.
                            </p>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-dark">2. Data Privacy and Protection</h6>
                            <p class="text-justify small">
                                In compliance with <strong>Republic Act No. 10173 (Data Privacy Act of 2012)</strong>, we are committed to protecting your personal information. This system collects, processes, and stores employee data including but not limited to personal identification information, employment records, performance data, training records, and system usage logs.
                            </p>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-dark">3. User Responsibilities</h6>
                            <p class="text-justify small">
                                In accordance with <strong>Republic Act No. 10175 (Cybercrime Prevention Act of 2012)</strong>, you are responsible for maintaining the confidentiality of your login credentials, reporting security breaches, and using the system only for authorized business purposes.
                            </p>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-dark">4. Electronic Transactions</h6>
                            <p class="text-justify small">
                                Pursuant to <strong>Republic Act No. 8792 (Electronic Commerce Act of 2000)</strong>, electronic documents, records, and signatures generated within this system have the same legal effect as their paper-based counterparts.
                            </p>
                        </div>

                        <div class="alert alert-info small">
                            <strong>Note:</strong> By clicking "I Accept", you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions. You can view the complete terms at any time by clicking the "Terms & Conditions" link in the footer.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="termsCheckbox" required>
                        <label class="form-check-label" for="termsCheckbox">
                            I have read and agree to the Terms and Conditions
                        </label>
                    </div>
                    <button type="button" class="btn btn-primary" id="acceptTermsBtn" disabled>
                        <i class="fe fe-check mr-2"></i>I Accept
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Scripts -->
    <script src="assets/vendor/js/jquery.min.js"></script>
    <script src="assets/vendor/js/popper.min.js"></script>
    <script src="assets/vendor/js/bootstrap.min.js"></script>
    <script src="assets/vendor/js/simplebar.min.js"></script>
    <script>
    if (typeof window.tinycolor === 'undefined') {
        window.tinycolor = function(color) {
            return {
                _color: color,
                lighten: function() { return this; },
                darken: function() { return this; },
                toString: function() { return this._color; }
            };
        };
    }
    if (typeof window.jQuery !== 'undefined' && typeof jQuery.fn.stickOnScroll !== 'function') {
        jQuery.fn.stickOnScroll = function() { return this; };
    }
    </script>
    <script src="assets/vendor/js/config.js"></script>
    <script src="assets/vendor/js/apps.js"></script>
    <!-- HR2 Custom JavaScript -->
    <script src="assets/js/hr-main.js"></script>
    
    <!-- Terms Acceptance JavaScript -->
    <script>
    $(document).ready(function() {
        // Show terms acceptance modal if user hasn't accepted
        <?php if (!$termsAccepted): ?>
        $('#termsAcceptanceModal').modal('show');
        <?php endif; ?>
        
        // Handle checkbox change
        $('#termsCheckbox').change(function() {
            if ($(this).is(':checked')) {
                $('#acceptTermsBtn').prop('disabled', false);
            } else {
                $('#acceptTermsBtn').prop('disabled', true);
            }
        });
        
        // Handle terms acceptance
        $('#acceptTermsBtn').click(function() {
            if ($('#termsCheckbox').is(':checked')) {
                $(this).prop('disabled', true).html('<i class="fe fe-loader mr-2"></i>Processing...');
                
                $.ajax({
                    url: 'ajax/accept_terms.php',
                    type: 'POST',
                    data: {
                        action: 'accept_terms'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#termsAcceptanceModal').modal('hide');
                            // Show success message
                            showNotification('Terms and Conditions accepted successfully!', 'success');
                        } else {
                            showNotification('Error: ' + response.message, 'error');
                            $('#acceptTermsBtn').prop('disabled', false).html('<i class="fe fe-check mr-2"></i>I Accept');
                        }
                    },
                    error: function() {
                        showNotification('Error accepting terms. Please try again.', 'error');
                        $('#acceptTermsBtn').prop('disabled', false).html('<i class="fe fe-check mr-2"></i>I Accept');
                    }
                });
            }
        });
        
        // Function to show notifications
        function showNotification(message, type) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'fe-check-circle' : 'fe-alert-circle';
            
            const notification = $(`
                <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                     style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                    <i class="fe ${icon} mr-2"></i>${message}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            `);
            
            $('body').append(notification);
            
            // Auto remove after 5 seconds
            setTimeout(function() {
                notification.alert('close');
            }, 5000);
        }
    });
    </script>
</body>
</html>
