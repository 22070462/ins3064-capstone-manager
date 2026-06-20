# Capstone Project Management System

A comprehensive web-based system for managing capstone projects, built with PHP and MySQL.

## 🎯 Features

### For Students
- 📋 Browse and register for topics
- 📝 Submit project milestones and deliverables
- 📊 Track project progress
- 👤 Manage student profile
- 🔔 View notifications and feedback

### For Lecturers
- 📚 Create and manage topics
- 👥 Supervise assigned students
- ✅ Review and approve registrations
- 📈 Evaluate student submissions
- 💬 Provide feedback and grades

### For Administrators
- 👥 User management (students, lecturers, admins)
- 📊 System reports and analytics
- ⚙️ System settings configuration
- 📈 Registration management
- 📁 Data export (CSV)

## 🛠️ Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **CSS Framework**: Bootstrap 5.3
- **Charts**: Chart.js 4.4
- **Icons**: Bootstrap Icons 1.11
- **Architecture**: MVC Pattern

## 📋 Requirements

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)

## 🚀 Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd capstone_project
```

### 2. Configure Database

1. Create a new MySQL database:
```sql
CREATE DATABASE capstone_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import the database schema:
```bash
mysql -u your_username -p capstone_db < database.sql
```

3. Update database configuration in `config/Database.php`:
```php
private $host = "localhost";
private $db_name = "capstone_db";
private $username = "your_username";
private $password = "your_password";
```

### 3. Configure Web Server

#### Apache (.htaccess is included)
- Ensure mod_rewrite is enabled
- DocumentRoot should point to the project directory

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 4. Set Permissions

```bash
chmod -R 755 public/assets/uploads
chmod -R 755 logs
```

## 🔐 Default Test Accounts

After database import, you can login with:

**Admin**
- Username: `admin`
- Password: `admin123`

**Lecturer**
- Username: `drjohnson`
- Password: `lecturer123`

**Student**
- Username: `student001`
- Password: `student123`

> ⚠️ **Important**: Change these passwords in production!

## 📁 Project Structure

```
capstone_project/
├── app/
│   ├── controllers/     # Controller classes
│   ├── models/         # Model classes
│   ├── views/          # View templates
│   └── core/           # Core framework classes
├── config/             # Configuration files
├── public/            # Public assets (CSS, JS, images)
├── logs/              # Application logs
├── database.sql       # Database schema
└── index.php          # Application entry point
```

## 🔒 Security Features

- ✅ Password hashing (bcrypt)
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ XSS protection (output escaping)
- ✅ CSRF protection
- ✅ Session security
- ✅ Role-based access control
- ✅ Input validation and sanitization

## 📊 API Endpoints

### Authentication
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout
- `GET /api/auth/check` - Check session status

### Student
- `GET /api/student/dashboard` - Dashboard data
- `GET /api/student/topics` - Available topics
- `POST /api/student/register-topic` - Register for topic
- `GET /api/student/my-project` - Current project details
- `POST /api/student/submit` - Submit deliverable

### Lecturer
- `GET /api/lecturer/dashboard` - Dashboard data
- `GET /api/lecturer/my-students` - Supervised students
- `GET /api/lecturer/my-topics` - Created topics
- `POST /api/lecturer/create-topic` - Create new topic
- `PUT /api/lecturer/approve-registration/{id}` - Approve registration

### Admin
- `GET /api/admin/dashboard/stats` - System statistics
- `GET /api/admin/users` - All users
- `GET /api/admin/reports` - System reports
- `GET /api/admin/settings` - System settings
- `PUT /api/admin/settings/{id}` - Update setting

## 🎨 UI Features

- 📱 Fully responsive design
- 🎯 Modern, clean interface
- 📊 Interactive charts and graphs
- 🔍 Real-time search and filtering
- ⚡ Fast page transitions
- 🎭 Professional color schemes

## 🧪 Testing

Access the test accounts page (development only):
```
http://localhost/capstone_project/show_test_accounts.php
```

## 📝 Development

### Code Style
- PSR-12 coding standard for PHP
- Camelcase for JavaScript
- BEM methodology for CSS

### Git Workflow
```bash
# Create feature branch
git checkout -b feature/new-feature

# Make changes and commit
git add .
git commit -m "feat: add new feature"

# Push to remote
git push origin feature/new-feature
```

### Commit Message Convention
- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation changes
- `style:` Code style changes
- `refactor:` Code refactoring
- `test:` Test updates
- `chore:` Build/config changes

## 🐛 Troubleshooting

### Database Connection Issues
```php
// Check config/Database.php
// Verify MySQL service is running
// Check credentials
```

### Session Issues
```php
// Clear sessions
session_start();
session_destroy();
```

### Permission Issues
```bash
# On Linux/Mac
sudo chmod -R 755 public/assets/uploads
sudo chmod -R 755 logs
```

## 📄 License

This project is for educational purposes.

## 👥 Contributors

Capstone Project Team - 2024

## 📞 Support

For issues and questions, please create an issue in the repository.

---

**Last Updated**: June 2026
**Version**: 1.0.0
