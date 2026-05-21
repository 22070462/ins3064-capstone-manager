-- ============================================
-- Capstone Project Management System Database
-- MySQL Database Schema (3NF Compliant)
-- 15 Tables with Foreign Keys and Constraints
-- ============================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS evaluation_scores;
DROP TABLE IF EXISTS evaluation_rubrics;
DROP TABLE IF EXISTS submissions;
DROP TABLE IF EXISTS milestones;
DROP TABLE IF EXISTS topic_assignments;
DROP TABLE IF EXISTS topic_registrations;
DROP TABLE IF EXISTS topic_tags;
DROP TABLE IF EXISTS topics;
DROP TABLE IF EXISTS lecturers;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS system_settings;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- TABLE 1: users
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Lecturer', 'Student') NOT NULL,
    status ENUM('Active', 'Inactive', 'Suspended') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 2: departments
-- ============================================
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 3: students
-- ============================================
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    student_code VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    department_id INT NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    enrollment_year YEAR,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT,
    INDEX idx_student_code (student_code),
    INDEX idx_department (department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 4: lecturers
-- ============================================
CREATE TABLE lecturers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    lecturer_code VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    max_quota INT DEFAULT 5,
    department_id INT NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    specialization VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT,
    INDEX idx_lecturer_code (lecturer_code),
    INDEX idx_department (department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 5: topics
-- ============================================
CREATE TABLE topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('Draft', 'Published', 'Closed', 'Archived') DEFAULT 'Draft',
    department_id INT NOT NULL,
    created_by INT NOT NULL,
    max_students INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES lecturers(id) ON DELETE RESTRICT,
    INDEX idx_status (status),
    INDEX idx_department (department_id),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 6: topic_tags
-- ============================================
CREATE TABLE topic_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic_id INT NOT NULL,
    tag_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
    INDEX idx_topic (topic_id),
    INDEX idx_tag (tag_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 7: topic_registrations
-- ============================================
CREATE TABLE topic_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    topic_id INT NOT NULL,
    lecturer_id INT NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected', 'Withdrawn') DEFAULT 'Pending',
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    rejection_reason TEXT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
    FOREIGN KEY (lecturer_id) REFERENCES lecturers(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_student_topic (student_id, topic_id),
    INDEX idx_status (status),
    INDEX idx_student (student_id),
    INDEX idx_topic (topic_id),
    INDEX idx_lecturer (lecturer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 8: topic_assignments
-- ============================================
CREATE TABLE topic_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL UNIQUE,
    lecturer_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (registration_id) REFERENCES topic_registrations(id) ON DELETE CASCADE,
    FOREIGN KEY (lecturer_id) REFERENCES lecturers(id) ON DELETE RESTRICT,
    INDEX idx_registration (registration_id),
    INDEX idx_lecturer (lecturer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 9: milestones
-- ============================================
CREATE TABLE milestones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    deadline DATE NOT NULL,
    weight_percentage DECIMAL(5,2) NOT NULL,
    sequence_order INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_deadline (deadline),
    INDEX idx_sequence (sequence_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 10: submissions
-- ============================================
CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    milestone_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_late BOOLEAN DEFAULT FALSE,
    comments TEXT,
    FOREIGN KEY (assignment_id) REFERENCES topic_assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (milestone_id) REFERENCES milestones(id) ON DELETE RESTRICT,
    INDEX idx_assignment (assignment_id),
    INDEX idx_milestone (milestone_id),
    INDEX idx_submitted_at (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 11: evaluation_rubrics
-- ============================================
CREATE TABLE evaluation_rubrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    milestone_id INT NOT NULL,
    criteria_name VARCHAR(100) NOT NULL,
    max_score DECIMAL(5,2) NOT NULL,
    description TEXT,
    sequence_order INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (milestone_id) REFERENCES milestones(id) ON DELETE CASCADE,
    INDEX idx_milestone (milestone_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 12: evaluation_scores
-- ============================================
CREATE TABLE evaluation_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    rubric_id INT NOT NULL,
    grader_id INT NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    comments TEXT,
    graded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (rubric_id) REFERENCES evaluation_rubrics(id) ON DELETE RESTRICT,
    FOREIGN KEY (grader_id) REFERENCES lecturers(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_submission_rubric (submission_id, rubric_id),
    INDEX idx_submission (submission_id),
    INDEX idx_rubric (rubric_id),
    INDEX idx_grader (grader_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 13: notifications
-- ============================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    type ENUM('Info', 'Success', 'Warning', 'Error') DEFAULT 'Info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 14: activity_logs
-- ============================================
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_table VARCHAR(50),
    target_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_target (target_table, target_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 15: system_settings
-- ============================================
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    description VARCHAR(255),
    data_type ENUM('string', 'integer', 'boolean', 'date', 'json') DEFAULT 'string',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================
-- DUMMY DATA INSERTION
-- ============================================

-- Insert Users (passwords are hashed using password_hash with PASSWORD_DEFAULT)
-- Plain passwords: admin123, lecturer123, student123
INSERT INTO users (username, password, role, status) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Active'),
('lecturer1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lecturer', 'Active'),
('lecturer2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lecturer', 'Active'),
('lecturer3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lecturer', 'Active'),
('lecturer4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lecturer', 'Active'),
('student1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 'Active'),
('student2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 'Active'),
('student3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 'Active'),
('student4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 'Active'),
('student5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 'Active'),
('student6', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 'Active'),
('student7', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 'Active'),
('student8', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 'Active');

-- Insert Departments
INSERT INTO departments (code, name, description) VALUES
('CS', 'Computer Science', 'Department of Computer Science and Software Engineering'),
('IT', 'Information Technology', 'Department of Information Technology and Systems'),
('IS', 'Information Systems', 'Department of Information Systems and Business Analytics'),
('SE', 'Software Engineering', 'Department of Software Engineering and Development');

-- Insert Students
INSERT INTO students (user_id, student_code, full_name, department_id, email, phone, enrollment_year) VALUES
(6, 'STD2024001', 'John Michael Smith', 1, 'john.smith@university.edu', '+1234567890', 2024),
(7, 'STD2024002', 'Emily Rose Johnson', 1, 'emily.johnson@university.edu', '+1234567891', 2024),
(8, 'STD2024003', 'Michael David Brown', 2, 'michael.brown@university.edu', '+1234567892', 2024),
(9, 'STD2024004', 'Sarah Elizabeth Davis', 2, 'sarah.davis@university.edu', '+1234567893', 2024),
(10, 'STD2024005', 'James Robert Wilson', 3, 'james.wilson@university.edu', '+1234567894', 2024),
(11, 'STD2024006', 'Jessica Marie Martinez', 3, 'jessica.martinez@university.edu', '+1234567895', 2024),
(12, 'STD2024007', 'Daniel Christopher Anderson', 4, 'daniel.anderson@university.edu', '+1234567896', 2024),
(13, 'STD2024008', 'Ashley Nicole Taylor', 4, 'ashley.taylor@university.edu', '+1234567897', 2024);

-- Insert Lecturers
INSERT INTO lecturers (user_id, lecturer_code, full_name, max_quota, department_id, email, phone, specialization) VALUES
(2, 'LEC001', 'Dr. Robert James Anderson', 8, 1, 'r.anderson@university.edu', '+1234560001', 'Artificial Intelligence, Machine Learning'),
(3, 'LEC002', 'Prof. Maria Elena Garcia', 6, 2, 'm.garcia@university.edu', '+1234560002', 'Database Systems, Cloud Computing'),
(4, 'LEC003', 'Dr. William Thomas Lee', 7, 3, 'w.lee@university.edu', '+1234560003', 'Business Intelligence, Data Analytics'),
(5, 'LEC004', 'Dr. Jennifer Anne White', 5, 4, 'j.white@university.edu', '+1234560004', 'Software Architecture, DevOps');

-- Insert Topics
INSERT INTO topics (title, description, status, department_id, created_by, max_students) VALUES
('AI-Powered Chatbot for Customer Service', 'Develop an intelligent chatbot using NLP and machine learning to handle customer inquiries automatically', 'Published', 1, 1, 2),
('Blockchain-Based Supply Chain Management', 'Create a decentralized supply chain tracking system using blockchain technology', 'Published', 1, 1, 1),
('Cloud-Native Microservices Architecture', 'Design and implement a scalable microservices application deployed on Kubernetes', 'Published', 2, 2, 2),
('Real-Time Data Analytics Dashboard', 'Build a real-time analytics platform for processing and visualizing streaming data', 'Published', 2, 2, 1),
('Predictive Analytics for Business Intelligence', 'Develop predictive models for sales forecasting and customer behavior analysis', 'Published', 3, 3, 2),
('E-Commerce Recommendation Engine', 'Create a personalized product recommendation system using collaborative filtering', 'Published', 3, 3, 1),
('DevOps CI/CD Pipeline Automation', 'Implement a complete CI/CD pipeline with automated testing and deployment', 'Published', 4, 4, 2),
('Mobile-First Progressive Web Application', 'Build a responsive PWA with offline capabilities and push notifications', 'Published', 4, 4, 1),
('IoT Smart Home Automation System', 'Design an IoT-based home automation system with sensor integration', 'Draft', 1, 1, 1),
('Cybersecurity Threat Detection System', 'Develop an intrusion detection system using machine learning algorithms', 'Published', 2, 2, 1);

-- Insert Topic Tags
INSERT INTO topic_tags (topic_id, tag_name) VALUES
(1, 'AI'), (1, 'NLP'), (1, 'Python'), (1, 'TensorFlow'),
(2, 'Blockchain'), (2, 'Ethereum'), (2, 'Solidity'), (2, 'Web3'),
(3, 'Microservices'), (3, 'Docker'), (3, 'Kubernetes'), (3, 'Spring Boot'),
(4, 'Big Data'), (4, 'Apache Kafka'), (4, 'React'), (4, 'Node.js'),
(5, 'Machine Learning'), (5, 'Python'), (5, 'Scikit-learn'), (5, 'Tableau'),
(6, 'Recommendation System'), (6, 'Python'), (6, 'Pandas'), (6, 'Flask'),
(7, 'DevOps'), (7, 'Jenkins'), (7, 'Docker'), (7, 'AWS'),
(8, 'PWA'), (8, 'JavaScript'), (8, 'Service Workers'), (8, 'IndexedDB'),
(9, 'IoT'), (9, 'Arduino'), (9, 'MQTT'), (9, 'Raspberry Pi'),
(10, 'Cybersecurity'), (10, 'Machine Learning'), (10, 'Python'), (10, 'Network Security');

-- Insert Topic Registrations
INSERT INTO topic_registrations (student_id, topic_id, lecturer_id, status, registered_at, reviewed_at) VALUES
(1, 1, 1, 'Approved', '2024-09-01 10:00:00', '2024-09-02 14:30:00'),
(2, 1, 1, 'Approved', '2024-09-01 11:00:00', '2024-09-02 14:35:00'),
(3, 3, 2, 'Approved', '2024-09-01 12:00:00', '2024-09-02 15:00:00'),
(4, 4, 2, 'Approved', '2024-09-01 13:00:00', '2024-09-02 15:30:00'),
(5, 5, 3, 'Approved', '2024-09-01 14:00:00', '2024-09-03 09:00:00'),
(6, 6, 3, 'Approved', '2024-09-01 15:00:00', '2024-09-03 09:30:00'),
(7, 7, 4, 'Approved', '2024-09-01 16:00:00', '2024-09-03 10:00:00'),
(8, 8, 4, 'Approved', '2024-09-01 17:00:00', '2024-09-03 10:30:00');

-- Insert Topic Assignments
INSERT INTO topic_assignments (registration_id, lecturer_id, assigned_at, notes) VALUES
(1, 1, '2024-09-02 15:00:00', 'Focus on NLP implementation and model training'),
(2, 1, '2024-09-02 15:05:00', 'Work on chatbot UI and integration'),
(3, 2, '2024-09-02 16:00:00', 'Implement service discovery and API gateway'),
(4, 2, '2024-09-02 16:30:00', 'Focus on real-time data processing pipeline'),
(5, 3, '2024-09-03 09:30:00', 'Develop predictive models using historical data'),
(6, 3, '2024-09-03 10:00:00', 'Implement collaborative filtering algorithm'),
(7, 4, '2024-09-03 10:30:00', 'Set up Jenkins pipeline and Docker containers'),
(8, 4, '2024-09-03 11:00:00', 'Implement service workers and offline functionality');

-- Insert Milestones
INSERT INTO milestones (title, description, deadline, weight_percentage, sequence_order, is_active) VALUES
('Project Proposal', 'Submit detailed project proposal with objectives and methodology', '2024-09-30', 10.00, 1, TRUE),
('Literature Review', 'Complete comprehensive literature review and related work analysis', '2024-10-31', 15.00, 2, TRUE),
('System Design', 'Submit system architecture, database design, and technical specifications', '2024-11-30', 20.00, 3, TRUE),
('Prototype Development', 'Develop working prototype with core functionalities', '2024-12-31', 25.00, 4, TRUE),
('Final Implementation', 'Complete system implementation with all features', '2025-01-31', 30.00, 5, TRUE);


-- Insert Submissions
INSERT INTO submissions (assignment_id, milestone_id, file_url, file_name, submitted_at, is_late, comments) VALUES
(1, 1, '/uploads/submissions/2024/09/proposal_student1.pdf', 'AI_Chatbot_Proposal.pdf', '2024-09-28 14:30:00', FALSE, 'Initial proposal submitted'),
(2, 1, '/uploads/submissions/2024/09/proposal_student2.pdf', 'Chatbot_UI_Proposal.pdf', '2024-09-29 16:45:00', FALSE, 'Proposal with UI mockups'),
(3, 1, '/uploads/submissions/2024/09/proposal_student3.pdf', 'Microservices_Proposal.pdf', '2024-09-30 09:15:00', FALSE, 'Architecture proposal'),
(4, 1, '/uploads/submissions/2024/09/proposal_student4.pdf', 'Analytics_Dashboard_Proposal.pdf', '2024-09-30 11:20:00', FALSE, 'Dashboard design proposal'),
(5, 1, '/uploads/submissions/2024/09/proposal_student5.pdf', 'Predictive_Analytics_Proposal.pdf', '2024-09-29 13:00:00', FALSE, 'ML model proposal'),
(6, 1, '/uploads/submissions/2024/09/proposal_student6.pdf', 'Recommendation_Engine_Proposal.pdf', '2024-09-30 15:30:00', FALSE, 'Algorithm proposal'),
(7, 1, '/uploads/submissions/2024/09/proposal_student7.pdf', 'CICD_Pipeline_Proposal.pdf', '2024-10-01 10:00:00', TRUE, 'Late submission - approved'),
(8, 1, '/uploads/submissions/2024/09/proposal_student8.pdf', 'PWA_Proposal.pdf', '2024-09-30 17:00:00', FALSE, 'PWA architecture proposal'),
(1, 2, '/uploads/submissions/2024/10/literature_student1.pdf', 'AI_Literature_Review.pdf', '2024-10-29 10:00:00', FALSE, 'Comprehensive NLP review'),
(2, 2, '/uploads/submissions/2024/10/literature_student2.pdf', 'Chatbot_Literature.pdf', '2024-10-30 14:00:00', FALSE, 'UI/UX research'),
(3, 2, '/uploads/submissions/2024/10/literature_student3.pdf', 'Microservices_Literature.pdf', '2024-10-31 09:00:00', FALSE, 'Cloud architecture review'),
(4, 2, '/uploads/submissions/2024/10/literature_student4.pdf', 'Analytics_Literature.pdf', '2024-10-30 16:00:00', FALSE, 'Real-time processing review');

-- Insert Evaluation Rubrics
INSERT INTO evaluation_rubrics (milestone_id, criteria_name, max_score, description, sequence_order) VALUES
-- Milestone 1: Project Proposal
(1, 'Problem Statement Clarity', 20.00, 'Clear definition of problem and objectives', 1),
(1, 'Methodology Soundness', 25.00, 'Appropriate research methodology and approach', 2),
(1, 'Feasibility Analysis', 20.00, 'Realistic timeline and resource assessment', 3),
(1, 'Innovation and Originality', 20.00, 'Novel approach or unique contribution', 4),
(1, 'Presentation Quality', 15.00, 'Document structure, grammar, and formatting', 5),
-- Milestone 2: Literature Review
(2, 'Comprehensiveness', 25.00, 'Coverage of relevant literature and sources', 1),
(2, 'Critical Analysis', 30.00, 'Depth of analysis and synthesis', 2),
(2, 'Citation Quality', 20.00, 'Proper citations and reference formatting', 3),
(2, 'Relevance to Project', 25.00, 'Connection to proposed project', 4),
-- Milestone 3: System Design
(3, 'Architecture Design', 30.00, 'System architecture and component design', 1),
(3, 'Database Design', 25.00, 'Database schema and normalization', 2),
(3, 'Technical Specifications', 25.00, 'Detailed technical requirements', 3),
(3, 'Design Documentation', 20.00, 'Quality of diagrams and documentation', 4),
-- Milestone 4: Prototype Development
(4, 'Core Functionality', 35.00, 'Implementation of key features', 1),
(4, 'Code Quality', 25.00, 'Code structure, readability, and standards', 2),
(4, 'User Interface', 20.00, 'UI design and usability', 3),
(4, 'Testing and Debugging', 20.00, 'Test coverage and bug fixes', 4),
-- Milestone 5: Final Implementation
(5, 'Complete Functionality', 30.00, 'All features fully implemented', 1),
(5, 'System Performance', 20.00, 'Efficiency and scalability', 2),
(5, 'Security Implementation', 15.00, 'Security measures and best practices', 3),
(5, 'Documentation', 20.00, 'User manual and technical documentation', 4),
(5, 'Presentation and Demo', 15.00, 'Final presentation quality', 5);

-- Insert Evaluation Scores
INSERT INTO evaluation_scores (submission_id, rubric_id, grader_id, score, comments, graded_at) VALUES
-- Student 1 - Milestone 1
(1, 1, 1, 18.00, 'Excellent problem definition with clear objectives', '2024-09-30 10:00:00'),
(1, 2, 1, 22.00, 'Well-structured methodology, minor improvements needed', '2024-09-30 10:05:00'),
(1, 3, 1, 19.00, 'Realistic timeline with good resource planning', '2024-09-30 10:10:00'),
(1, 4, 1, 17.00, 'Good innovation in NLP approach', '2024-09-30 10:15:00'),
(1, 5, 1, 14.00, 'Professional presentation with minor formatting issues', '2024-09-30 10:20:00'),
-- Student 2 - Milestone 1
(2, 1, 1, 17.00, 'Clear problem statement, good context', '2024-09-30 11:00:00'),
(2, 2, 1, 23.00, 'Strong methodology with detailed steps', '2024-09-30 11:05:00'),
(2, 3, 1, 18.00, 'Feasible plan with contingencies', '2024-09-30 11:10:00'),
(2, 4, 1, 18.00, 'Innovative UI approach', '2024-09-30 11:15:00'),
(2, 5, 1, 15.00, 'Excellent presentation with mockups', '2024-09-30 11:20:00'),
-- Student 3 - Milestone 1
(3, 1, 2, 19.00, 'Very clear problem definition', '2024-10-01 09:00:00'),
(3, 2, 2, 24.00, 'Excellent microservices methodology', '2024-10-01 09:05:00'),
(3, 3, 2, 20.00, 'Highly feasible with good risk assessment', '2024-10-01 09:10:00'),
(3, 4, 2, 19.00, 'Strong innovation in architecture', '2024-10-01 09:15:00'),
(3, 5, 2, 14.00, 'Good presentation quality', '2024-10-01 09:20:00'),
-- Student 4 - Milestone 1
(4, 1, 2, 18.00, 'Clear objectives and scope', '2024-10-01 10:00:00'),
(4, 2, 2, 23.00, 'Solid real-time processing approach', '2024-10-01 10:05:00'),
(4, 3, 2, 19.00, 'Realistic implementation plan', '2024-10-01 10:10:00'),
(4, 4, 2, 18.00, 'Good use of streaming technologies', '2024-10-01 10:15:00'),
(4, 5, 2, 13.00, 'Adequate presentation', '2024-10-01 10:20:00'),
-- Student 1 - Milestone 2
(9, 6, 1, 23.00, 'Comprehensive coverage of NLP literature', '2024-11-01 14:00:00'),
(9, 7, 1, 27.00, 'Excellent critical analysis and synthesis', '2024-11-01 14:05:00'),
(9, 8, 1, 19.00, 'Proper APA citations throughout', '2024-11-01 14:10:00'),
(9, 9, 1, 24.00, 'Strong connection to project goals', '2024-11-01 14:15:00'),
-- Student 2 - Milestone 2
(10, 6, 1, 22.00, 'Good coverage of UI/UX research', '2024-11-01 15:00:00'),
(10, 7, 1, 26.00, 'Strong analysis of chatbot interfaces', '2024-11-01 15:05:00'),
(10, 8, 1, 18.00, 'Mostly correct citations', '2024-11-01 15:10:00'),
(10, 9, 1, 23.00, 'Well connected to project', '2024-11-01 15:15:00');

-- Insert Notifications
INSERT INTO notifications (user_id, message, type, is_read, created_at, read_at) VALUES
(6, 'Your topic registration for "AI-Powered Chatbot for Customer Service" has been approved', 'Success', TRUE, '2024-09-02 14:30:00', '2024-09-02 15:00:00'),
(6, 'New milestone "Project Proposal" is now available. Deadline: 2024-09-30', 'Info', TRUE, '2024-09-05 09:00:00', '2024-09-05 10:00:00'),
(6, 'Your submission for "Project Proposal" has been graded. Score: 90/100', 'Success', TRUE, '2024-09-30 10:30:00', '2024-09-30 11:00:00'),
(6, 'Reminder: "Literature Review" deadline is approaching (7 days left)', 'Warning', FALSE, '2024-10-24 08:00:00', NULL),
(7, 'Your topic registration for "AI-Powered Chatbot for Customer Service" has been approved', 'Success', TRUE, '2024-09-02 14:35:00', '2024-09-02 15:30:00'),
(7, 'Your submission for "Project Proposal" has been graded. Score: 91/100', 'Success', TRUE, '2024-09-30 11:30:00', '2024-09-30 12:00:00'),
(8, 'Your topic registration for "Cloud-Native Microservices Architecture" has been approved', 'Success', TRUE, '2024-09-02 15:00:00', '2024-09-02 16:00:00'),
(8, 'Your submission for "Project Proposal" has been graded. Score: 96/100', 'Success', TRUE, '2024-10-01 09:30:00', '2024-10-01 10:00:00'),
(2, 'New topic registration from John Michael Smith for your topic', 'Info', TRUE, '2024-09-01 10:00:00', '2024-09-01 11:00:00'),
(2, 'You have 2 submissions pending evaluation for Milestone 1', 'Warning', FALSE, '2024-10-02 09:00:00', NULL),
(1, 'System backup completed successfully', 'Success', TRUE, '2024-09-01 02:00:00', '2024-09-01 09:00:00'),
(1, 'New user registration: student8 (Ashley Nicole Taylor)', 'Info', TRUE, '2024-08-30 14:00:00', '2024-08-30 15:00:00');

-- Insert Activity Logs
INSERT INTO activity_logs (user_id, action, target_table, target_id, details, ip_address, user_agent, created_at) VALUES
(1, 'LOGIN', 'users', 1, 'Admin login successful', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2024-09-01 08:00:00'),
(1, 'CREATE', 'milestones', 1, 'Created milestone: Project Proposal', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2024-09-01 09:00:00'),
(2, 'LOGIN', 'users', 2, 'Lecturer login successful', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', '2024-09-01 09:30:00'),
(2, 'CREATE', 'topics', 1, 'Created topic: AI-Powered Chatbot for Customer Service', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', '2024-09-01 09:45:00'),
(6, 'LOGIN', 'users', 6, 'Student login successful', '192.168.1.150', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2024-09-01 10:00:00'),
(6, 'CREATE', 'topic_registrations', 1, 'Registered for topic: AI-Powered Chatbot', '192.168.1.150', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2024-09-01 10:05:00'),
(2, 'UPDATE', 'topic_registrations', 1, 'Approved registration for student: John Michael Smith', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', '2024-09-02 14:30:00'),
(6, 'CREATE', 'submissions', 1, 'Submitted Project Proposal', '192.168.1.150', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2024-09-28 14:30:00'),
(2, 'CREATE', 'evaluation_scores', 1, 'Graded submission for milestone 1', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', '2024-09-30 10:00:00'),
(7, 'LOGIN', 'users', 7, 'Student login successful', '192.168.1.151', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)', '2024-09-01 11:00:00'),
(8, 'CREATE', 'submissions', 3, 'Submitted Project Proposal', '192.168.1.152', 'Mozilla/5.0 (X11; Linux x86_64)', '2024-09-30 09:15:00'),
(3, 'LOGIN', 'users', 3, 'Lecturer login successful', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2024-09-02 08:00:00'),
(4, 'UPDATE', 'topics', 7, 'Updated topic status to Published', '192.168.1.103', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', '2024-09-01 16:00:00'),
(1, 'UPDATE', 'system_settings', 1, 'Updated registration deadline', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2024-09-01 10:00:00'),
(5, 'CREATE', 'evaluation_rubrics', 1, 'Created rubric for Project Proposal', '192.168.1.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2024-09-01 11:00:00');

-- Insert System Settings
INSERT INTO system_settings (setting_key, setting_value, description, data_type) VALUES
('registration_deadline', '2024-09-15', 'Last date for topic registration', 'date'),
('global_max_quota', '10', 'Maximum students per lecturer across all topics', 'integer'),
('submission_grace_period', '24', 'Grace period in hours for late submissions', 'integer'),
('enable_notifications', 'true', 'Enable email notifications', 'boolean'),
('academic_year', '2024-2025', 'Current academic year', 'string'),
('semester', 'Fall 2024', 'Current semester', 'string'),
('min_proposal_words', '1500', 'Minimum word count for project proposal', 'integer'),
('max_file_size_mb', '10', 'Maximum file upload size in MB', 'integer'),
('allowed_file_types', '["pdf","docx","zip","rar"]', 'Allowed file extensions for uploads', 'json'),
('evaluation_deadline_days', '14', 'Days after submission for evaluation deadline', 'integer'),
('system_maintenance_mode', 'false', 'Enable maintenance mode', 'boolean'),
('contact_email', 'support@university.edu', 'System support contact email', 'string'),
('max_topic_registrations_per_student', '3', 'Maximum topic registrations allowed per student', 'integer'),
('auto_approve_registrations', 'false', 'Automatically approve topic registrations', 'boolean'),
('session_timeout_minutes', '30', 'User session timeout in minutes', 'integer');

-- ============================================
-- VERIFICATION QUERIES
-- ============================================

-- Verify table counts
SELECT 'users' AS table_name, COUNT(*) AS record_count FROM users
UNION ALL SELECT 'departments', COUNT(*) FROM departments
UNION ALL SELECT 'students', COUNT(*) FROM students
UNION ALL SELECT 'lecturers', COUNT(*) FROM lecturers
UNION ALL SELECT 'topics', COUNT(*) FROM topics
UNION ALL SELECT 'topic_tags', COUNT(*) FROM topic_tags
UNION ALL SELECT 'topic_registrations', COUNT(*) FROM topic_registrations
UNION ALL SELECT 'topic_assignments', COUNT(*) FROM topic_assignments
UNION ALL SELECT 'milestones', COUNT(*) FROM milestones
UNION ALL SELECT 'submissions', COUNT(*) FROM submissions
UNION ALL SELECT 'evaluation_rubrics', COUNT(*) FROM evaluation_rubrics
UNION ALL SELECT 'evaluation_scores', COUNT(*) FROM evaluation_scores
UNION ALL SELECT 'notifications', COUNT(*) FROM notifications
UNION ALL SELECT 'activity_logs', COUNT(*) FROM activity_logs
UNION ALL SELECT 'system_settings', COUNT(*) FROM system_settings;

-- ============================================
-- END OF DATABASE SCRIPT
-- ============================================
-- 
-- CREDENTIALS FOR TESTING:
-- Username: admin | Password: admin123 | Role: Admin
-- Username: lecturer1 | Password: lecturer123 | Role: Lecturer
-- Username: student1 | Password: student123 | Role: Student
-- 
-- Note: All passwords use bcrypt hashing ($2y$10$...)
-- The hash shown is for demonstration - in production, generate fresh hashes
-- ============================================
