<?php
// Request Management System
class RequestManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // Create new employee request
    public function createRequest($requestData) {
        try {
            $this->db->beginTransaction();
            
            // Insert the request
            $stmt = $this->db->prepare("
                INSERT INTO employee_requests (employee_id, request_type_id, title, description, status, priority, requested_date, requested_start_date, requested_end_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $requestData['employee_id'],
                $requestData['request_type_id'],
                $requestData['title'],
                $requestData['description'],
                $requestData['status'] ?? 'pending',
                $requestData['priority'] ?? 'medium',
                $requestData['requested_date'],
                $requestData['requested_start_date'] ?? null,
                $requestData['requested_end_date'] ?? null
            ]);
            
            $requestId = $this->db->lastInsertId();
            
            // If approval is required, create approval records
            if ($requestData['requires_approval']) {
                $this->createApprovalRecords($requestId, $requestData['approvers']);
            }
            
            // Create notification for the employee
            $this->createNotification($requestId, $requestData['employee_id'], 'created', 'Your request has been submitted successfully.');
            
            $this->db->commit();
            return $requestId;
            
        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("Error creating request: " . $e->getMessage());
            return false;
        }
    }
    
    // Create approval records for a request
    private function createApprovalRecords($requestId, $approvers) {
        foreach ($approvers as $approverId) {
            $stmt = $this->db->prepare("
                INSERT INTO request_approvals (request_id, approver_id, status) 
                VALUES (?, ?, 'pending')
            ");
            $stmt->execute([$requestId, $approverId]);
            
            // Create notification for approver
            $this->createNotification($requestId, $approverId, 'created', 'You have a new request to approve.');
        }
    }
    
    // Create notification
    private function createNotification($requestId, $userId, $type, $message) {
        $stmt = $this->db->prepare("
            INSERT INTO request_notifications (request_id, user_id, notification_type, message) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$requestId, $userId, $type, $message]);
    }
    
    // Get employee requests
    public function getEmployeeRequests($employeeId, $status = null) {
        try {
            $sql = "
                SELECT er.*, rt.name as request_type_name, rt.description as request_type_description,
                       COUNT(ra.id) as approval_count,
                       COUNT(CASE WHEN ra.status = 'approved' THEN 1 END) as approved_count,
                       COUNT(CASE WHEN ra.status = 'rejected' THEN 1 END) as rejected_count
                FROM employee_requests er
                JOIN request_types rt ON er.request_type_id = rt.id
                LEFT JOIN request_approvals ra ON er.id = ra.request_id
                WHERE er.employee_id = ?
            ";
            
            $params = [$employeeId];
            
            if ($status) {
                $sql .= " AND er.status = ?";
                $params[] = $status;
            }
            
            $sql .= " GROUP BY er.id ORDER BY er.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting employee requests: " . $e->getMessage());
            return [];
        }
    }
    
    // Get pending approvals for a user
    public function getPendingApprovals($approverId) {
        try {
            $stmt = $this->db->prepare("
                SELECT er.*, rt.name as request_type_name, u.first_name, u.last_name, u.position,
                       ra.id as approval_id, ra.comments as approval_comments
                FROM employee_requests er
                JOIN request_types rt ON er.request_type_id = rt.id
                JOIN users u ON er.employee_id = u.id
                JOIN request_approvals ra ON er.id = ra.request_id
                WHERE ra.approver_id = ? AND ra.status = 'pending' AND er.status = 'pending'
                ORDER BY er.created_at ASC
            ");
            $stmt->execute([$approverId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting pending approvals: " . $e->getMessage());
            return [];
        }
    }
    
    // Approve or reject request
    public function processApproval($approvalId, $status, $comments = '') {
        try {
            $this->db->beginTransaction();
            
            // Update approval record
            $stmt = $this->db->prepare("
                UPDATE request_approvals SET status = ?, comments = ?, approved_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$status, $comments, $approvalId]);
            
            // Get request details
            $stmt = $this->db->prepare("
                SELECT ra.request_id, er.employee_id, er.status as request_status
                FROM request_approvals ra
                JOIN employee_requests er ON ra.request_id = er.id
                WHERE ra.id = ?
            ");
            $stmt->execute([$approvalId]);
            $approval = $stmt->fetch();
            
            if ($approval) {
                // Check if all approvals are complete
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as total, 
                           COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
                           COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected
                    FROM request_approvals 
                    WHERE request_id = ?
                ");
                $stmt->execute([$approval['request_id']]);
                $approvalStats = $stmt->fetch();
                
                // Determine overall request status
                $newRequestStatus = 'pending';
                if ($approvalStats['rejected'] > 0) {
                    $newRequestStatus = 'rejected';
                } elseif ($approvalStats['approved'] == $approvalStats['total']) {
                    $newRequestStatus = 'approved';
                }
                
                // Update request status if changed
                if ($newRequestStatus != $approval['request_status']) {
                    $stmt = $this->db->prepare("
                        UPDATE employee_requests SET status = ?, updated_at = NOW() 
                        WHERE id = ?
                    ");
                    $stmt->execute([$newRequestStatus, $approval['request_id']]);
                    
                    // Create notification for employee
                    $notificationType = $newRequestStatus == 'approved' ? 'approved' : 'rejected';
                    $message = $newRequestStatus == 'approved' 
                        ? 'Your request has been approved.' 
                        : 'Your request has been rejected.';
                    
                    $this->createNotification($approval['request_id'], $approval['employee_id'], $notificationType, $message);
                }
            }
            
            $this->db->commit();
            return true;
            
        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("Error processing approval: " . $e->getMessage());
            return false;
        }
    }
    
    // Get request details
    public function getRequestDetails($requestId) {
        try {
            $stmt = $this->db->prepare("
                SELECT er.*, rt.name as request_type_name, rt.description as request_type_description,
                       u.first_name, u.last_name, u.position, u.department
                FROM employee_requests er
                JOIN request_types rt ON er.request_type_id = rt.id
                JOIN users u ON er.employee_id = u.id
                WHERE er.id = ?
            ");
            $stmt->execute([$requestId]);
            
            $request = $stmt->fetch();
            
            if ($request) {
                // Get approval details
                $stmt = $this->db->prepare("
                    SELECT ra.*, u.first_name, u.last_name, u.position
                    FROM request_approvals ra
                    JOIN users u ON ra.approver_id = u.id
                    WHERE ra.request_id = ?
                    ORDER BY ra.created_at ASC
                ");
                $stmt->execute([$requestId]);
                $request['approvals'] = $stmt->fetchAll();
                
                // Get comments
                $stmt = $this->db->prepare("
                    SELECT rc.*, u.first_name, u.last_name
                    FROM request_comments rc
                    JOIN users u ON rc.user_id = u.id
                    WHERE rc.request_id = ?
                    ORDER BY rc.created_at ASC
                ");
                $stmt->execute([$requestId]);
                $request['comments'] = $stmt->fetchAll();
                
                // Get attachments
                $stmt = $this->db->prepare("
                    SELECT ra.*, u.first_name, u.last_name
                    FROM request_attachments ra
                    JOIN users u ON ra.uploaded_by = u.id
                    WHERE ra.request_id = ?
                    ORDER BY ra.uploaded_at ASC
                ");
                $stmt->execute([$requestId]);
                $request['attachments'] = $stmt->fetchAll();
            }
            
            return $request;
            
        } catch (PDOException $e) {
            error_log("Error getting request details: " . $e->getMessage());
            return null;
        }
    }
    
    // Add comment to request
    public function addComment($requestId, $userId, $comment, $isInternal = false) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO request_comments (request_id, user_id, comment, is_internal) 
                VALUES (?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([$requestId, $userId, $comment, $isInternal]);
            
            if ($result) {
                // Create notification for request owner
                $stmt = $this->db->prepare("SELECT employee_id FROM employee_requests WHERE id = ?");
                $stmt->execute([$requestId]);
                $request = $stmt->fetch();
                
                if ($request && $request['employee_id'] != $userId) {
                    $this->createNotification($requestId, $request['employee_id'], 'comment', 'A new comment has been added to your request.');
                }
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error adding comment: " . $e->getMessage());
            return false;
        }
    }
    
    // Get request types
    public function getRequestTypes() {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM request_types 
                WHERE active = TRUE 
                ORDER BY name ASC
            ");
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting request types: " . $e->getMessage());
            return [];
        }
    }
    
    // Get all requests (for admin/HR)
    public function getAllRequests($filters = []) {
        try {
            $sql = "
                SELECT er.*, rt.name as request_type_name, u.first_name, u.last_name, u.position, u.department,
                       COUNT(ra.id) as approval_count,
                       COUNT(CASE WHEN ra.status = 'approved' THEN 1 END) as approved_count,
                       COUNT(CASE WHEN ra.status = 'rejected' THEN 1 END) as rejected_count
                FROM employee_requests er
                JOIN request_types rt ON er.request_type_id = rt.id
                JOIN users u ON er.employee_id = u.id
                LEFT JOIN request_approvals ra ON er.id = ra.request_id
            ";
            
            $params = [];
            $whereClauses = [];
            
            if (!empty($filters['status'])) {
                $whereClauses[] = "er.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['request_type_id'])) {
                $whereClauses[] = "er.request_type_id = ?";
                $params[] = $filters['request_type_id'];
            }
            
            if (!empty($filters['department'])) {
                $whereClauses[] = "u.department = ?";
                $params[] = $filters['department'];
            }
            
            if (!empty($filters['date_from'])) {
                $whereClauses[] = "er.created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $whereClauses[] = "er.created_at <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($whereClauses)) {
                $sql .= " WHERE " . implode(" AND ", $whereClauses);
            }
            
            $sql .= " GROUP BY er.id ORDER BY er.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting all requests: " . $e->getMessage());
            return [];
        }
    }
    
    // Get request statistics
    public function getRequestStatistics($filters = []) {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total_requests,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_requests,
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_requests,
                    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_requests,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_requests,
                    COUNT(CASE WHEN priority = 'urgent' THEN 1 END) as urgent_requests,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as requests_last_30_days
                FROM employee_requests er
                JOIN users u ON er.employee_id = u.id
            ";
            
            $params = [];
            $whereClauses = [];
            
            if (!empty($filters['department'])) {
                $whereClauses[] = "u.department = ?";
                $params[] = $filters['department'];
            }
            
            if (!empty($filters['date_from'])) {
                $whereClauses[] = "er.created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $whereClauses[] = "er.created_at <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($whereClauses)) {
                $sql .= " WHERE " . implode(" AND ", $whereClauses);
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting request statistics: " . $e->getMessage());
            return null;
        }
    }
    
    // Cancel request
    public function cancelRequest($requestId, $userId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE employee_requests SET status = 'cancelled', updated_at = NOW() 
                WHERE id = ? AND employee_id = ?
            ");
            
            $result = $stmt->execute([$requestId, $userId]);
            
            if ($result) {
                // Create notification for approvers
                $stmt = $this->db->prepare("
                    SELECT approver_id FROM request_approvals 
                    WHERE request_id = ? AND status = 'pending'
                ");
                $stmt->execute([$requestId]);
                $approvers = $stmt->fetchAll();
                
                foreach ($approvers as $approver) {
                    $this->createNotification($requestId, $approver['approver_id'], 'cancelled', 'A request you were assigned to approve has been cancelled.');
                }
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error cancelling request: " . $e->getMessage());
            return false;
        }
    }
    
    // Get user notifications
    public function getUserNotifications($userId, $unreadOnly = false) {
        try {
            $sql = "
                SELECT rn.*, er.title as request_title, rt.name as request_type_name
                FROM request_notifications rn
                JOIN employee_requests er ON rn.request_id = er.id
                JOIN request_types rt ON er.request_type_id = rt.id
                WHERE rn.user_id = ?
            ";
            
            $params = [$userId];
            
            if ($unreadOnly) {
                $sql .= " AND rn.is_read = FALSE";
            }
            
            $sql .= " ORDER BY rn.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting user notifications: " . $e->getMessage());
            return [];
        }
    }
    
    // Mark notification as read
    public function markNotificationAsRead($notificationId, $userId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE request_notifications SET is_read = TRUE 
                WHERE id = ? AND user_id = ?
            ");
            
            return $stmt->execute([$notificationId, $userId]);
        } catch (PDOException $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            return false;
        }
    }
}
?>

