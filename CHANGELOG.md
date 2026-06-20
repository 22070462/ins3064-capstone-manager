# Changelog

All notable changes to the Capstone Project Management System will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-06-20

### Added - Admin Dashboard Features

#### Dashboard Overview
- Real-time system statistics display
- Interactive charts with Chart.js
- User distribution visualization
- Registration status tracking
- Export functionality for assignments, students, and topics

#### User Management
- Complete CRUD operations for users
- User status management (Active/Inactive)
- Password reset functionality
- Role-based filtering and search
- User details modal with comprehensive information

#### Student Management
- Student list with department information
- Student profile viewing
- Registration history tracking
- Search and filter capabilities

#### Lecturer Management
- Lecturer list with specialization details
- Topic creation tracking
- Student supervision overview
- Workload visualization

#### Topic Management
- Topic CRUD operations
- Status management (Published/Draft/Archived)
- Department categorization
- Registration tracking per topic
- Topic capacity monitoring

#### Registration Management
- Registration list with detailed information
- Status update (Approve/Reject/Pending/Withdrawn)
- Rejection reason support
- Registration details modal
- Student-topic-lecturer relationship display

#### Reports & Analytics
- **Overview Statistics**: System-wide metrics
- **Registration Analysis**: Status distribution, top topics, monthly trends
- **Topic Analysis**: By status, by department, lecturer productivity
- **Department Analysis**: Comparative metrics across departments
- **Timeline Reports**: User growth, topic creation trends
- **Performance Metrics**: Review time, approval rate, capacity utilization
- **Interactive Charts**: 
  - Registration Status Distribution (Pie Chart)
  - Topics by Status (Bar Chart)
  - Monthly Registration Trends (Line Chart)
  - Department Comparison (Multi-Bar Chart)
- **Top Performers**: Top 10 topics and lecturers

#### System Settings
- Settings grouped by category (Academic, Registration, Submission, File Upload, System)
- CRUD operations for settings
- Data type validation (string, integer, boolean, date, json)
- Smart input types based on data type
- Protected critical settings
- Activity logging for all changes

### Added - Lecturer Dashboard Features

#### My Students
- List of supervised students
- Project progress tracking
- Submission status monitoring
- Evaluation interface
- Communication tools

#### My Topics
- Created topics management
- Registration requests handling
- Topic status updates
- Capacity monitoring

### Added - Student Dashboard Features

#### Topic Registration
- Browse available topics
- Filter by department and status
- Topic details viewing
- Registration submission
- Registration status tracking

#### My Project
- Current project overview
- Milestone tracking
- Submission interface
- Progress updates
- Feedback viewing

#### Profile Management
- Personal information updates
- Contact details management
- Academic information display

### Technical Improvements

#### Backend
- RESTful API architecture
- PDO prepared statements for security
- Input validation and sanitization
- Role-based access control
- Session management
- Activity logging system
- Error handling and logging

#### Frontend
- Responsive Bootstrap 5 design
- SPA-like navigation
- Real-time data updates
- Loading states and feedback
- Form validation
- Modal dialogs for actions
- Interactive charts and visualizations

#### Security
- Password hashing with bcrypt
- SQL injection prevention
- XSS protection
- CSRF protection
- Session security
- Input sanitization
- Role-based middleware

#### Database
- Normalized schema design
- Proper indexing
- Foreign key constraints
- Cascade operations
- UTF-8 support

### Fixed

#### Authentication
- Session handling improvements
- Login/logout flow optimization
- Role verification enhancement

#### Dashboard Loading
- API response optimization
- Error handling improvements
- Loading state management

#### Data Consistency
- Repository pattern implementation
- Transaction support
- Data validation

### Changed

#### API Structure
- Unified response format
- Consistent error handling
- RESTful endpoint naming

#### UI/UX
- Modern design updates
- Improved navigation
- Better feedback messages
- Enhanced data visualization

### Security

- Implemented comprehensive input validation
- Added role-based access control throughout
- Enhanced session security
- Protected against common vulnerabilities (SQL injection, XSS, CSRF)

## [0.1.0] - 2024-09-01

### Added
- Initial project setup
- Database schema design
- Basic MVC structure
- User authentication system
- Student registration flow
- Lecturer topic creation
- Admin user management

---

## Version History

- **1.0.0** (2026-06-20): Complete admin dashboard with all management features
- **0.1.0** (2024-09-01): Initial release with basic functionality
