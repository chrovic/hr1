# Learning Management System - Implementation Guide

## Overview

The Learning Management System (LMS) has been enhanced with comprehensive features for managing training catalogs, skills, certifications, and learning paths. This system allows employees to request trainings, HR/Admin to approve/deny requests, and employees to enroll in approved trainings.

## Key Features Implemented

### 1. Training Catalog Management
- **Skills & Certifications Tracking**: Maintain a comprehensive catalog of skills and certifications
- **Training Modules**: Create and manage training courses with detailed information
- **Learning Paths**: Create structured learning journeys for different roles
- **Cost Management**: Track training costs and budget approvals

### 2. Employee Training Requests
- **Enhanced Request Form**: Employees can submit detailed training requests with:
  - Training module selection
  - Priority level (Low, Medium, High, Urgent)
  - Reason for request
  - Estimated cost
  - Manager assignment
  - Session preferences
- **Request Tracking**: View all submitted requests with status updates
- **Skills & Certifications Display**: View assigned skills and earned certifications
- **Learning Path Progress**: Track progress through assigned learning paths

### 3. HR/Admin Approval Workflow
- **Comprehensive Dashboard**: Analytics showing request statistics
- **Enhanced Approval Process**: Approve/reject requests with detailed information
- **Session Assignment**: Assign approved requests to specific training sessions
- **Manager Integration**: Include manager approval workflow
- **Cost Tracking**: Monitor training costs and budget impact

### 4. Employee Enrollment System
- **Automatic Enrollment**: Approved requests automatically create enrollments
- **Session Management**: Track attendance and completion status
- **Progress Tracking**: Monitor learning progress and completion rates
- **Skills Acquisition**: Track skills gained from completed trainings

## Database Schema Enhancements

### New Tables Added

#### Skills Catalog
```sql
CREATE TABLE skills_catalog (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    skill_level ENUM('beginner', 'intermediate', 'advanced', 'expert'),
    status ENUM('active', 'inactive', 'archived'),
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Certifications Catalog
```sql
CREATE TABLE certifications_catalog (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    issuing_body VARCHAR(200),
    validity_period_months INT DEFAULT 24,
    renewal_required BOOLEAN DEFAULT TRUE,
    cost DECIMAL(10,2) DEFAULT 0.00,
    exam_required BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'archived'),
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Employee Skills
```sql
CREATE TABLE employee_skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    skill_id INT NOT NULL,
    proficiency_level ENUM('beginner', 'intermediate', 'advanced', 'expert'),
    acquired_date DATE,
    verified_by INT,
    verification_date DATE,
    status ENUM('active', 'expired', 'suspended'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Employee Certifications
```sql
CREATE TABLE employee_certifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    certification_id INT NOT NULL,
    issue_date DATE NOT NULL,
    expiry_date DATE,
    certificate_number VARCHAR(100),
    issuing_body VARCHAR(200),
    verification_status ENUM('pending', 'verified', 'rejected'),
    verified_by INT,
    verification_date DATE,
    status ENUM('active', 'expired', 'suspended', 'revoked'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Learning Paths
```sql
CREATE TABLE learning_paths (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    target_role VARCHAR(100),
    estimated_duration_days INT DEFAULT 30,
    prerequisites TEXT,
    learning_objectives TEXT,
    status ENUM('draft', 'active', 'inactive', 'archived'),
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Enhanced Existing Tables

#### Training Requests
- Added `reason` field for employee justification
- Added `priority` field (low, medium, high, urgent)
- Added `manager_approval` and `manager_id` fields
- Added `budget_approved` and `estimated_cost` fields
- Added `session_preference` field

#### Training Enrollments
- Added `attendance_status` field
- Added `completion_status` field
- Added `completion_score` and `feedback` fields
- Added `skills_gained` and `certifications_earned` JSON fields

## File Structure

### New Files Created
- `database/learning_management_enhancements.sql` - Database schema enhancements
- `pages/learning_management_enhanced.php` - Enhanced learning management dashboard
- `pages/employee_training_requests.php` - Updated employee training requests page
- `pages/training_requests.php` - Enhanced HR/Admin approval workflow

### Enhanced Files
- `includes/functions/learning.php` - Added comprehensive learning management methods

## Key Methods Added to LearningManager Class

### Skills Management
- `getAllSkills($status)` - Get all skills from catalog
- `getEmployeeSkills($employee_id)` - Get employee's skills
- `addEmployeeSkill($skillData)` - Add skill to employee

### Certifications Management
- `getAllCertifications($status)` - Get all certifications from catalog
- `getEmployeeCertifications($employee_id)` - Get employee's certifications
- `addEmployeeCertification($certData)` - Add certification to employee

### Learning Paths Management
- `getAllLearningPaths($status)` - Get all learning paths
- `getLearningPathDetails($path_id)` - Get detailed learning path information
- `assignLearningPath($assignmentData)` - Assign learning path to employee
- `getEmployeeLearningPaths($employee_id)` - Get employee's learning paths
- `updateLearningPathProgress($employee_id, $path_id, $progress_percentage)` - Update progress

### Enhanced Training Requests
- `submitTrainingRequest($requestData)` - Submit enhanced training request
- `getEnhancedTrainingRequests($status, $employee_id, $manager_id)` - Get detailed training requests

### Analytics and Reporting
- `getLearningAnalytics($date_from, $date_to)` - Get comprehensive learning analytics
- `getEmployeeLearningSummary($employee_id)` - Get employee learning summary

## Usage Instructions

### For Employees

1. **View Learning Dashboard**
   - Access `pages/employee_training_requests.php`
   - View skills, certifications, learning paths, and training requests
   - Track progress and completion status

2. **Request Training**
   - Click "Request Training" button
   - Fill out comprehensive request form including:
     - Training module selection
     - Priority level
     - Reason for request
     - Estimated cost
     - Manager assignment
     - Session preferences

3. **Track Requests**
   - View all submitted requests with status updates
   - See approval/rejection details
   - Track enrollment status

### For HR/Admin

1. **Manage Learning Catalog**
   - Access `pages/learning_management_enhanced.php`
   - Add/edit skills, certifications, and learning paths
   - Manage training modules

2. **Process Training Requests**
   - Access `pages/training_requests.php`
   - View analytics dashboard
   - Approve/reject requests with detailed information
   - Assign sessions to approved requests

3. **Monitor Learning Progress**
   - Track employee skills and certifications
   - Monitor learning path completion
   - Generate learning analytics reports

## Installation Steps

1. **Database Setup**
   ```bash
   mysql -u root < database/learning_management_enhancements.sql
   ```

2. **File Deployment**
   - Copy new PHP files to appropriate directories
   - Ensure proper file permissions

3. **Access Control**
   - Verify user permissions for learning management features
   - Test employee and admin access

## Features Summary

✅ **Training Catalog Management** - Complete skills and certifications catalog
✅ **Employee Training Requests** - Enhanced request system with detailed information
✅ **HR/Admin Approval Workflow** - Comprehensive approval process with analytics
✅ **Employee Enrollment System** - Automatic enrollment for approved requests
✅ **Skills & Certifications Tracking** - Complete tracking and verification system
✅ **Learning Paths** - Structured learning journeys for different roles
✅ **Analytics Dashboard** - Comprehensive learning analytics and reporting

## Future Enhancements

- **AI-Powered Recommendations** - Suggest trainings based on competency gaps
- **Mobile App Integration** - Mobile access to learning management
- **Gamification** - Points, badges, and leaderboards for learning
- **Integration with External LMS** - Connect with external learning platforms
- **Advanced Reporting** - Detailed learning analytics and insights
- **Notification System** - Automated notifications for approvals, deadlines, etc.

## Support

For technical support or questions about the Learning Management System implementation, contact the development team.


