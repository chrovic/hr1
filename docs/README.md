# HR1 System - File Structure

## Root Directory
- `index.php` - Main application entry point (ONLY PHP file in root)

## Directory Structure
```
hr1/
├── index.php              # Main application (ONLY PHP file in root)
├── assets/                # All CSS, JS, images, fonts
├── auth/                  # Authentication files
│   ├── login.php         # Login page
│   ├── 2fa.php           # Two-factor authentication
│   └── logout.php        # Logout handler
├── includes/             # PHP includes and functions
│   ├── data/
│   │   └── db.php       # Database connection
│   └── functions/       # All system functions
├── pages/               # Page content files
├── partials/           # Header and sidebar components
├── database.sql        # Database schema
└── sample_data.sql     # Sample data for testing
```

## Access Points
- **Main Application**: `http://yoursite.com/` (redirects to login if not authenticated)
- **Login**: `http://yoursite.com/auth/login.php`
- **2FA**: `http://yoursite.com/auth/2fa.php`
- **Logout**: `http://yoursite.com/auth/logout.php`

## Demo Credentials
- **Admin**: admin / password
- **HR Manager**: hr@company.com / password  
- **Employee**: employee@company.com / password

## Setup Instructions
1. Import `database.sql` to create the database structure
2. Import `sample_data.sql` to add test data
3. Update database connection in `includes/data/db.php`
4. Access the system via `http://yoursite.com/`
