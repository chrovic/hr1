<?php
// Competency Notification Management System
class NotificationManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Create a competency notification
     */
    public function createNotification($userId, $notificationType, $title, $message, $relatedId = null, $relatedType = null, $actionUrl = null, $isImportant = false) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO competency_notifications 
                (user_id, notification_type, title, message, related_id, related_type, action_url, is_important) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            return $stmt->execute([
                $userId,
                $notificationType,
                $title,
                $message,
                $relatedId,
                $relatedType,
                $actionUrl,
                $isImportant ? 1 : 0
            ]);
        } catch (PDOException $e) {
            error_log("Error creating notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create notification using template
     */
    public function createNotificationFromTemplate($userId, $notificationType, $variables = [], $relatedId = null, $relatedType = null, $actionUrl = null, $isImportant = false) {
        try {
            // Get template
            $template = $this->getNotificationTemplate($notificationType);
            if (!$template) {
                return false;
            }
            
            // Replace variables in template
            $title = $this->replaceTemplateVariables($template['title_template'], $variables);
            $message = $this->replaceTemplateVariables($template['message_template'], $variables);
            
            return $this->createNotification($userId, $notificationType, $title, $message, $relatedId, $relatedType, $actionUrl, $isImportant);
        } catch (Exception $e) {
            error_log("Error creating notification from template: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get notification template
     */
    private function getNotificationTemplate($notificationType) {
        try {
            $stmt = $this->db->prepare("
                SELECT title_template, message_template 
                FROM notification_templates 
                WHERE notification_type = ? AND is_active = TRUE
            ");
            $stmt->execute([$notificationType]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting notification template: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Replace variables in template
     */
    private function replaceTemplateVariables($template, $variables) {
        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }
    
    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $unreadOnly = false, $limit = 50) {
        try {
            $sql = "SELECT * FROM competency_notifications WHERE user_id = ?";
            $params = [$userId];
            
            if ($unreadOnly) {
                $sql .= " AND is_read = FALSE";
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            if ($limit) {
                $sql .= " LIMIT " . (int)$limit;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetchAll();
            
            // Debug logging
            error_log("getUserNotifications: userId=$userId, unreadOnly=$unreadOnly, limit=$limit, resultCount=" . count($result));
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error getting user notifications: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get unread notification count
     */
    public function getUnreadCount($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM competency_notifications 
                WHERE user_id = ? AND is_read = FALSE
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            $count = $result['count'];
            
            // Debug logging
            error_log("getUnreadCount: userId=$userId, count=$count");
            
            return $count;
        } catch (PDOException $e) {
            error_log("Error getting unread count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE competency_notifications 
                SET is_read = TRUE, read_at = NOW() 
                WHERE id = ? AND user_id = ?
            ");
            return $stmt->execute([$notificationId, $userId]);
        } catch (PDOException $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead($userId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE competency_notifications 
                SET is_read = TRUE, read_at = NOW() 
                WHERE user_id = ? AND is_read = FALSE
            ");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Error marking all notifications as read: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete notification
     */
    public function deleteNotification($notificationId, $userId) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM competency_notifications 
                WHERE id = ? AND user_id = ?
            ");
            return $stmt->execute([$notificationId, $userId]);
        } catch (PDOException $e) {
            error_log("Error deleting notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get notification preferences for user
     */
    public function getUserPreferences($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM notification_preferences WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $preferences = $stmt->fetch();
            
            // If no preferences exist, create default ones
            if (!$preferences) {
                $this->createDefaultPreferences($userId);
                $stmt->execute([$userId]);
                $preferences = $stmt->fetch();
            }
            
            return $preferences;
        } catch (PDOException $e) {
            error_log("Error getting user preferences: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update notification preferences
     */
    public function updateUserPreferences($userId, $preferences) {
        try {
            $stmt = $this->db->prepare("
                UPDATE notification_preferences SET 
                    email_notifications = ?, in_app_notifications = ?, sms_notifications = ?,
                    competency_model_created = ?, competency_model_updated = ?, competency_model_deleted = ?,
                    competency_added = ?, competency_updated = ?, competency_deleted = ?,
                    cycle_created = ?, cycle_updated = ?, cycle_deleted = ?,
                    evaluation_assigned = ?, evaluation_completed = ?, evaluation_overdue = ?,
                    score_submitted = ?, report_generated = ?, updated_at = NOW()
                WHERE user_id = ?
            ");
            
            return $stmt->execute([
                $preferences['email_notifications'] ?? false,
                $preferences['in_app_notifications'] ?? true,
                $preferences['sms_notifications'] ?? false,
                $preferences['competency_model_created'] ?? true,
                $preferences['competency_model_updated'] ?? true,
                $preferences['competency_model_deleted'] ?? true,
                $preferences['competency_added'] ?? true,
                $preferences['competency_updated'] ?? true,
                $preferences['competency_deleted'] ?? true,
                $preferences['cycle_created'] ?? true,
                $preferences['cycle_updated'] ?? true,
                $preferences['cycle_deleted'] ?? true,
                $preferences['evaluation_assigned'] ?? true,
                $preferences['evaluation_completed'] ?? true,
                $preferences['evaluation_overdue'] ?? true,
                $preferences['score_submitted'] ?? true,
                $preferences['report_generated'] ?? false,
                $userId
            ]);
        } catch (PDOException $e) {
            error_log("Error updating user preferences: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create default preferences for user
     */
    private function createDefaultPreferences($userId) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notification_preferences 
                (user_id, email_notifications, in_app_notifications, sms_notifications,
                 competency_model_created, competency_model_updated, competency_model_deleted,
                 competency_added, competency_updated, competency_deleted,
                 cycle_created, cycle_updated, cycle_deleted,
                 evaluation_assigned, evaluation_completed, evaluation_overdue,
                 score_submitted, report_generated)
                VALUES (?, TRUE, TRUE, FALSE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, FALSE)
            ");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Error creating default preferences: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user should receive notification type
     */
    public function shouldReceiveNotification($userId, $notificationType) {
        try {
            $preferences = $this->getUserPreferences($userId);
            if (!$preferences) {
                return false;
            }
            
            $preferenceField = $this->getPreferenceFieldForType($notificationType);
            return $preferences[$preferenceField] ?? false;
        } catch (Exception $e) {
            error_log("Error checking notification preference: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get preference field name for notification type
     */
    private function getPreferenceFieldForType($notificationType) {
        $fieldMap = [
            'model_created' => 'competency_model_created',
            'model_updated' => 'competency_model_updated',
            'model_deleted' => 'competency_model_deleted',
            'model_archived' => 'competency_model_deleted',
            'competency_added' => 'competency_added',
            'competency_updated' => 'competency_updated',
            'competency_deleted' => 'competency_deleted',
            'cycle_created' => 'cycle_created',
            'cycle_updated' => 'cycle_updated',
            'cycle_deleted' => 'cycle_deleted',
            'evaluation_assigned' => 'evaluation_assigned',
            'evaluation_completed' => 'evaluation_completed',
            'evaluation_overdue' => 'evaluation_overdue',
            'score_submitted' => 'score_submitted',
            'report_generated' => 'report_generated'
        ];
        
        return $fieldMap[$notificationType] ?? 'in_app_notifications';
    }
    
    /**
     * Notify all HR managers and admins
     */
    public function notifyHRManagers($notificationType, $variables = [], $relatedId = null, $relatedType = null, $actionUrl = null, $isImportant = false) {
        try {
            // Get all HR managers and admins
            $stmt = $this->db->prepare("
                SELECT id FROM users 
                WHERE role IN ('admin', 'hr_manager') AND status = 'active'
            ");
            $stmt->execute();
            $users = $stmt->fetchAll();
            
            $successCount = 0;
            foreach ($users as $user) {
                if ($this->shouldReceiveNotification($user['id'], $notificationType)) {
                    if ($this->createNotificationFromTemplate($user['id'], $notificationType, $variables, $relatedId, $relatedType, $actionUrl, $isImportant)) {
                        $successCount++;
                    }
                }
            }
            
            return $successCount;
        } catch (Exception $e) {
            error_log("Error notifying HR managers: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Notify specific user
     */
    public function notifyUser($userId, $notificationType, $variables = [], $relatedId = null, $relatedType = null, $actionUrl = null, $isImportant = false) {
        if ($this->shouldReceiveNotification($userId, $notificationType)) {
            return $this->createNotificationFromTemplate($userId, $notificationType, $variables, $relatedId, $relatedType, $actionUrl, $isImportant);
        }
        return false;
    }
    
    /**
     * Get notification statistics
     */
    public function getNotificationStats($userId = null) {
        try {
            $sql = "
                SELECT 
                    notification_type,
                    COUNT(*) as total_count,
                    COUNT(CASE WHEN is_read = FALSE THEN 1 END) as unread_count,
                    COUNT(CASE WHEN is_important = TRUE THEN 1 END) as important_count
                FROM competency_notifications
            ";
            
            $params = [];
            if ($userId) {
                $sql .= " WHERE user_id = ?";
                $params[] = $userId;
            }
            
            $sql .= " GROUP BY notification_type ORDER BY total_count DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting notification stats: " . $e->getMessage());
            return [];
        }
    }
}
?>
