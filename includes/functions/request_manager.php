<?php
// Request Management System
class RequestManager {
    private $db;
    private $hasRequestTypes;
    private $hasRequestApprovals;
    private $hasRequestComments;
    private $hasRequestAttachments;
    private $hasRequestNotifications;
    private $employeeRequestColumns;
    
    public function __construct() {
        $this->db = getDB();
        $this->hasRequestTypes = $this->tableExists('request_types');
        $this->hasRequestApprovals = $this->tableExists('request_approvals');
        $this->hasRequestComments = $this->tableExists('request_comments');
        $this->hasRequestAttachments = $this->tableExists('request_attachments');
        $this->hasRequestNotifications = $this->tableExists('request_notifications');
        $this->employeeRequestColumns = $this->getTableColumns('employee_requests');
    }

    private function tableExists($tableName) {
        try {
            $stmt = $this->db->prepare("
                SELECT 1
                FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                LIMIT 1
            ");
            $stmt->execute([$tableName]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }

    private function getTableColumns($tableName) {
        try {
            $stmt = $this->db->prepare("
                SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
            ");
            $stmt->execute([$tableName]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    private function getEnumValues($tableName, $columnName) {
        try {
            $stmt = $this->db->prepare("
                SELECT COLUMN_TYPE
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND COLUMN_NAME = ?
            ");
            $stmt->execute([$tableName, $columnName]);
            $columnType = $stmt->fetchColumn();
            if (!$columnType || strpos($columnType, 'enum(') !== 0) {
                return [];
            }
            $values = trim(substr($columnType, 5), ')');
            $values = array_map(function($val) {
                return trim($val, " '");
            }, explode(',', $values));
            return $values;
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Create new employee request
    public function createRequest($requestData) {
        try {
            $this->db->beginTransaction();

            if ($this->hasRequestTypes) {
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
            } else {
                $requestType = $requestData['request_type'] ?? $requestData['request_type_id'] ?? 'other';
                $requestDate = $requestData['requested_date'] ?? date('Y-m-d');
                $stmt = $this->db->prepare("
                    INSERT INTO employee_requests (employee_id, request_type, title, description, request_date, status)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $requestData['employee_id'],
                    $requestType,
                    $requestData['title'],
                    $requestData['description'],
                    $requestDate,
                    $requestData['status'] ?? 'pending'
                ]);
            }
            
            $requestId = $this->db->lastInsertId();
            
            // If approval is required, create approval records
            if ($requestData['requires_approval'] && $this->hasRequestApprovals) {
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
        if (!$this->hasRequestNotifications) {
            return;
        }
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
                SELECT er.*,
                       " . ($this->hasRequestTypes ? "rt.name as request_type_name, rt.description as request_type_description" : "er.request_type as request_type_name, '' as request_type_description") . ",
                       " . ($this->hasRequestApprovals ? "COUNT(ra.id) as approval_count,
                       COUNT(CASE WHEN ra.status = 'approved' THEN 1 END) as approved_count,
                       COUNT(CASE WHEN ra.status = 'rejected' THEN 1 END) as rejected_count" : "0 as approval_count, 0 as approved_count, 0 as rejected_count") . "
                FROM employee_requests er
                " . ($this->hasRequestTypes ? "JOIN request_types rt ON er.request_type_id = rt.id" : "") . "
                " . ($this->hasRequestApprovals ? "LEFT JOIN request_approvals ra ON er.id = ra.request_id" : "") . "
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
            if ($this->hasRequestTypes) {
                $stmt = $this->db->prepare("
                    SELECT er.*, rt.name as request_type_name, rt.description as request_type_description,
                           u.first_name, u.last_name, u.position, u.department
                    FROM employee_requests er
                    JOIN request_types rt ON er.request_type_id = rt.id
                    JOIN users u ON er.employee_id = u.id
                    WHERE er.id = ?
                ");
            } else {
                $stmt = $this->db->prepare("
                    SELECT er.*, er.request_type as request_type_name, '' as request_type_description,
                           u.first_name, u.last_name, u.position, u.department
                    FROM employee_requests er
                    JOIN users u ON er.employee_id = u.id
                    WHERE er.id = ?
                ");
            }
            $stmt->execute([$requestId]);
            
            $request = $stmt->fetch();
            
            if ($request) {
                // Get approval details
                if ($this->hasRequestApprovals) {
                    $stmt = $this->db->prepare("
                        SELECT ra.*, u.first_name, u.last_name, u.position
                        FROM request_approvals ra
                        JOIN users u ON ra.approver_id = u.id
                        WHERE ra.request_id = ?
                        ORDER BY ra.created_at ASC
                    ");
                    $stmt->execute([$requestId]);
                    $request['approvals'] = $stmt->fetchAll();
                } else {
                    $request['approvals'] = [];
                }
                
                // Get comments
                if ($this->hasRequestComments) {
                    $stmt = $this->db->prepare("
                        SELECT rc.*, u.first_name, u.last_name
                        FROM request_comments rc
                        JOIN users u ON rc.user_id = u.id
                        WHERE rc.request_id = ?
                        ORDER BY rc.created_at ASC
                    ");
                    $stmt->execute([$requestId]);
                    $request['comments'] = $stmt->fetchAll();
                } else {
                    $request['comments'] = [];
                }
                
                // Get attachments
                if ($this->hasRequestAttachments) {
                    $stmt = $this->db->prepare("
                        SELECT ra.*, u.first_name, u.last_name
                        FROM request_attachments ra
                        JOIN users u ON ra.uploaded_by = u.id
                        WHERE ra.request_id = ?
                        ORDER BY ra.uploaded_at ASC
                    ");
                    $stmt->execute([$requestId]);
                    $request['attachments'] = $stmt->fetchAll();
                } else {
                    $request['attachments'] = [];
                }
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
            if ($this->hasRequestTypes) {
                $stmt = $this->db->prepare("
                    SELECT * FROM request_types 
                    WHERE active = TRUE 
                    ORDER BY name ASC
                ");
                $stmt->execute();
                return $stmt->fetchAll();
            }

            $enumValues = $this->getEnumValues('employee_requests', 'request_type');
            $types = [];
            foreach ($enumValues as $value) {
                $types[] = [
                    'id' => $value,
                    'name' => ucwords(str_replace('_', ' ', $value))
                ];
            }
            return $types;
        } catch (PDOException $e) {
            error_log("Error getting request types: " . $e->getMessage());
            return [];
        }
    }
    
    // Get all requests (for admin/HR)
    public function getAllRequests($filters = []) {
        try {
            $sql = "
                SELECT er.*,
                       u.first_name as employee_first_name,
                       u.last_name as employee_last_name,
                       u.position as employee_position,
                       u.department as employee_department,
                       " . ($this->hasRequestTypes ? "rt.name as request_type_name" : "er.request_type as request_type_name") . ",
                       " . ($this->hasRequestApprovals ? "COUNT(ra.id) as approval_count,
                       COUNT(CASE WHEN ra.status = 'approved' THEN 1 END) as approved_count,
                       COUNT(CASE WHEN ra.status = 'rejected' THEN 1 END) as rejected_count" : "0 as approval_count, 0 as approved_count, 0 as rejected_count") . "
                FROM employee_requests er
                " . ($this->hasRequestTypes ? "JOIN request_types rt ON er.request_type_id = rt.id" : "") . "
                JOIN users u ON er.employee_id = u.id
                " . ($this->hasRequestApprovals ? "LEFT JOIN request_approvals ra ON er.id = ra.request_id" : "") . "
            ";
            
            $params = [];
            $whereClauses = [];
            
            if (!empty($filters['status'])) {
                $whereClauses[] = "er.status = ?";
                $params[] = $filters['status'];
            }
            
            if ($this->hasRequestTypes) {
                if (!empty($filters['request_type_id'])) {
                    $whereClauses[] = "er.request_type_id = ?";
                    $params[] = $filters['request_type_id'];
                } elseif (!empty($filters['request_type'])) {
                    $whereClauses[] = "er.request_type_id = ?";
                    $params[] = $filters['request_type'];
                }
            } else {
                if (!empty($filters['request_type'])) {
                    $whereClauses[] = "er.request_type = ?";
                    $params[] = $filters['request_type'];
                } elseif (!empty($filters['request_type_id'])) {
                    $whereClauses[] = "er.request_type = ?";
                    $params[] = $filters['request_type_id'];
                }
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

            if (!empty($filters['search'])) {
                $whereClauses[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR er.title LIKE ? OR er.description LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
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
            
            $stats = $stmt->fetch();
            if (!$stats) {
                return null;
            }
            $stats['total'] = $stats['total_requests'];
            $stats['pending'] = $stats['pending_requests'];
            $stats['approved'] = $stats['approved_requests'];
            $stats['rejected'] = $stats['rejected_requests'];
            $stats['cancelled'] = $stats['cancelled_requests'];
            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting request statistics: " . $e->getMessage());
            return null;
        }
    }

    public function approveRequest($requestId, $approverId, $comments = '') {
        try {
            $this->db->beginTransaction();

            $fields = ["status = 'approved'"];
            $params = [];

            if (in_array('approved_by', $this->employeeRequestColumns, true)) {
                $fields[] = "approved_by = ?";
                $params[] = $approverId;
            }
            if (in_array('approved_at', $this->employeeRequestColumns, true)) {
                $fields[] = "approved_at = NOW()";
            }
            if (in_array('rejection_reason', $this->employeeRequestColumns, true)) {
                $fields[] = "rejection_reason = NULL";
            }
            if (in_array('updated_at', $this->employeeRequestColumns, true)) {
                $fields[] = "updated_at = NOW()";
            }

            $sql = "UPDATE employee_requests SET " . implode(", ", $fields) . " WHERE id = ?";
            $params[] = $requestId;
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            if ($this->hasRequestApprovals) {
                $stmt = $this->db->prepare("
                    UPDATE request_approvals
                    SET status = 'approved', comments = ?, approved_at = NOW()
                    WHERE request_id = ? AND status = 'pending'
                ");
                $stmt->execute([$comments, $requestId]);
            }

            $this->createNotification($requestId, $this->getRequestOwnerId($requestId), 'approved', 'Your request has been approved.');

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("Error approving request: " . $e->getMessage());
            return false;
        }
    }

    public function rejectRequest($requestId, $approverId, $comments = '') {
        try {
            $this->db->beginTransaction();

            $fields = ["status = 'rejected'"];
            $params = [];

            if (in_array('approved_by', $this->employeeRequestColumns, true)) {
                $fields[] = "approved_by = ?";
                $params[] = $approverId;
            }
            if (in_array('approved_at', $this->employeeRequestColumns, true)) {
                $fields[] = "approved_at = NOW()";
            }
            if (in_array('rejection_reason', $this->employeeRequestColumns, true)) {
                $fields[] = "rejection_reason = ?";
                $params[] = $comments;
            }
            if (in_array('updated_at', $this->employeeRequestColumns, true)) {
                $fields[] = "updated_at = NOW()";
            }

            $sql = "UPDATE employee_requests SET " . implode(", ", $fields) . " WHERE id = ?";
            $params[] = $requestId;
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            if ($this->hasRequestApprovals) {
                $stmt = $this->db->prepare("
                    UPDATE request_approvals
                    SET status = 'rejected', comments = ?, approved_at = NOW()
                    WHERE request_id = ? AND status = 'pending'
                ");
                $stmt->execute([$comments, $requestId]);
            }

            $this->createNotification($requestId, $this->getRequestOwnerId($requestId), 'rejected', 'Your request has been rejected.');

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("Error rejecting request: " . $e->getMessage());
            return false;
        }
    }

    private function getRequestOwnerId($requestId) {
        try {
            $stmt = $this->db->prepare("SELECT employee_id FROM employee_requests WHERE id = ?");
            $stmt->execute([$requestId]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
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

