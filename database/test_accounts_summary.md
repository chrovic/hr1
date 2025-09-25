# Test Accounts Summary

## Login Credentials

All test accounts use the password: `password`

### 1. Employee Account
- **Username:** `john.doe`
- **Email:** `john.doe@company.com`
- **Password:** `password`
- **Role:** Employee
- **Department:** IT
- **Position:** Software Developer
- **Employee ID:** EMP001
- **Phone:** 555-0101
- **Hire Date:** 2023-01-15

### 2. HR Manager Account
- **Username:** `jane.smith`
- **Email:** `jane.smith@company.com`
- **Password:** `password`
- **Role:** HR Manager
- **Department:** Human Resources
- **Position:** HR Manager
- **Employee ID:** HRM001
- **Phone:** 555-0102
- **Hire Date:** 2022-06-01

### 3. Admin Account
- **Username:** `admin.test`
- **Email:** `admin.test@company.com`
- **Password:** `password`
- **Role:** Admin
- **Department:** IT
- **Position:** System Administrator
- **Employee ID:** ADM002
- **Phone:** 555-0103
- **Hire Date:** 2022-01-01

### 4. System Admin Account (Existing)
- **Username:** `admin`
- **Email:** `system.admin@company.com`
- **Password:** `password`
- **Role:** Admin
- **Department:** IT
- **Position:** System Administrator
- **Employee ID:** ADM001
- **Phone:** 555-0000
- **Hire Date:** 2022-01-01

## Account Roles and Permissions

### Employee Role
- Can view own profile and training records
- Can request training sessions
- Can view available training modules
- Can access employee-specific pages

### HR Manager Role
- Can manage employee records
- Can approve/deny training requests
- Can manage training sessions and enrollments
- Can access HR management pages
- Can provide feedback and scores to trainees

### Admin Role
- Full system access
- Can manage all users and roles
- Can access all system features
- Can manage system settings
- Can view all reports and analytics

## Testing Scenarios

1. **Employee Login:** Test training requests, view enrollments, access personal dashboard
2. **HR Manager Login:** Test request approval, training management, feedback system
3. **Admin Login:** Test full system functionality, user management, system settings

## Notes

- All accounts are set to 'active' status
- Password hash is for 'password' (bcrypt)
- Accounts are ready for immediate testing
- Employee account has HR Manager as manager (Jane Smith)


