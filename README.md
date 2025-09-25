# HR1 Human Resources Management System

A comprehensive HR management system built with PHP, MySQL, and Bootstrap.

## 🚀 Quick Start

1. **Database Setup:**
   ```bash
   mysql -u root < database/HR1_Complete_Schema.sql
   ```

2. **Access the System:**
   - URL: `http://localhost/hr1`
   - Admin: `admin` / `admin123`
   - HR Manager: `hrmanager` / `hr123`
   - Employee: `employee1` / `emp123`

## 📁 Project Structure

```
hr1/
├── assets/                 # Static assets (CSS, JS, images)
├── auth/                   # Authentication pages
├── database/               # Database schemas and scripts
├── docs/                   # Documentation
├── includes/               # Core PHP includes
│   ├── data/              # Database connection
│   └── functions/         # Business logic functions
├── pages/                  # Main application pages
├── partials/              # Reusable UI components
└── index.php              # Main application entry point
```

## 🎯 Features

- **User Management** - Complete user lifecycle management
- **Competency Management** - Create and manage competency models
- **Training Management** - Training modules, sessions, and enrollments
- **Evaluation System** - Performance evaluations and scoring
- **Succession Planning** - Succession plans and candidate management
- **Employee Self-Service** - Employee portal for requests and information
- **System Administration** - Announcements, settings, and logs
- **Reports & Analytics** - Comprehensive reporting system

## 🔧 System Requirements

- PHP 7.4+
- MySQL 8.0+
- Apache/Nginx web server
- Bootstrap 4+ (included)

## 📚 Documentation

- [Complete Database Schema](docs/HR1_Final_Database_Schema.md)
- [System Analysis](docs/HR1_System_Analysis.md)
- [Implementation Guide](docs/Implementation_Guide.md)

## 🗄️ Database

The system uses a comprehensive database schema with 16 tables covering:
- User management and authentication
- Competency models and evaluations
- Training management and enrollments
- Succession planning
- System administration and logging

See `database/HR1_Complete_Schema.sql` for the complete schema.

## 🔐 Security Features

- Role-based access control
- Password hashing
- Activity logging
- Input validation and sanitization
- SQL injection prevention

## 🎨 UI/UX

- Responsive Bootstrap design
- Modern, clean interface
- Mobile-friendly
- Accessible components
- Intuitive navigation

## 📊 Admin Features

- Complete CRUD operations
- User management
- System settings
- Announcements management
- Activity monitoring
- Data export capabilities

## 🚀 Deployment

1. Upload files to web server
2. Import database schema
3. Configure database connection in `includes/data/db.php`
4. Set proper file permissions
5. Access via web browser

## 📝 License

This project is proprietary software. All rights reserved.

## 🤝 Support

For support and questions, contact the development team.