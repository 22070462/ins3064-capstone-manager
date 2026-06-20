# Contributing to Capstone Project Management System

Thank you for your interest in contributing! This document provides guidelines and instructions for contributing to the project.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Workflow](#development-workflow)
- [Commit Message Guidelines](#commit-message-guidelines)
- [Coding Standards](#coding-standards)
- [Pull Request Process](#pull-request-process)
- [Testing](#testing)

## 🤝 Code of Conduct

- Be respectful and inclusive
- Provide constructive feedback
- Focus on what is best for the project
- Show empathy towards other contributors

## 🚀 Getting Started

### Prerequisites

- PHP 8.0+
- MySQL 8.0+
- Git
- Code editor (VS Code recommended)

### Setup Development Environment

1. **Clone the repository**
```bash
git clone https://github.com/22070462/ins3064-capstone-manager.git
cd capstone_project
```

2. **Configure database**
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE capstone_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u root -p capstone_db < database.sql
```

3. **Update configuration**
```bash
# Edit config/Database.php with your credentials
```

4. **Create a new branch**
```bash
git checkout -b feature/your-feature-name
```

## 🔄 Development Workflow

### Branch Naming Convention

- **Feature**: `feature/feature-name`
- **Bug Fix**: `fix/bug-description`
- **Hotfix**: `hotfix/critical-issue`
- **Documentation**: `docs/what-changed`
- **Refactor**: `refactor/what-refactored`

**Examples:**
```bash
git checkout -b feature/add-email-notifications
git checkout -b fix/login-session-timeout
git checkout -b docs/update-api-documentation
```

### Workflow Steps

1. **Update main branch**
```bash
git checkout main
git pull origin main
```

2. **Create feature branch**
```bash
git checkout -b feature/your-feature
```

3. **Make changes and commit**
```bash
git add .
git commit -m "feat: add new feature"
```

4. **Keep branch updated**
```bash
git checkout main
git pull origin main
git checkout feature/your-feature
git rebase main
```

5. **Push to remote**
```bash
git push origin feature/your-feature
```

6. **Create Pull Request** on GitHub

## 📝 Commit Message Guidelines

We follow [Conventional Commits](https://www.conventionalcommits.org/) specification.

### Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- **feat**: New feature
- **fix**: Bug fix
- **docs**: Documentation changes
- **style**: Code style changes (formatting, missing semicolons, etc.)
- **refactor**: Code refactoring
- **perf**: Performance improvements
- **test**: Adding or updating tests
- **chore**: Build process or auxiliary tool changes

### Examples

**Feature:**
```bash
git commit -m "feat(admin): add user export functionality

- Add CSV export for users
- Include role-based filtering
- Add export button to UI"
```

**Bug Fix:**
```bash
git commit -m "fix(auth): resolve session timeout issue

Session was expiring too quickly due to incorrect
timeout configuration in Middleware.php"
```

**Breaking Change:**
```bash
git commit -m "feat(api): update response format

BREAKING CHANGE: API now returns data in 'data' field instead of 'result'"
```

### Scope Examples

- **admin**: Admin dashboard
- **student**: Student features
- **lecturer**: Lecturer features
- **auth**: Authentication
- **api**: API endpoints
- **db**: Database changes
- **ui**: User interface
- **security**: Security updates

## 💻 Coding Standards

### PHP Code Style (PSR-12)

```php
<?php

namespace App\Controllers;

class ExampleController extends Controller
{
    /**
     * Method description
     * 
     * @param int $id
     * @return void
     */
    public function exampleMethod(int $id): void
    {
        // Use camelCase for variables
        $userData = $this->getUserData($id);
        
        // Use descriptive names
        $isActive = $userData['status'] === 'Active';
        
        // Always validate input
        if (!$id || $id <= 0) {
            $this->jsonError('Invalid ID', 400);
            return;
        }
    }
}
```

### JavaScript Code Style

```javascript
/**
 * Function description
 * @param {number} userId - User ID
 * @returns {Promise<Object>} User data
 */
async function fetchUserData(userId) {
    try {
        const response = await fetch(`/api/users/${userId}`);
        const result = await response.json();
        
        if (result.success) {
            return result.data;
        }
        throw new Error(result.message);
    } catch (error) {
        console.error('Fetch error:', error);
        showAlert('Failed to fetch user data', 'danger');
    }
}
```

### CSS/SCSS Code Style

```css
/* Use BEM methodology */
.admin-card {
    background: white;
    border-radius: 10px;
}

.admin-card__header {
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.admin-card__title {
    font-size: 18px;
    font-weight: 600;
}
```

### Best Practices

1. **Security First**
   - Always use prepared statements
   - Validate and sanitize input
   - Escape output
   - Use CSRF tokens

2. **Error Handling**
   - Always handle errors gracefully
   - Log errors for debugging
   - Provide user-friendly messages

3. **Documentation**
   - Add PHPDoc comments
   - Document complex logic
   - Update README when needed

4. **Database**
   - Use transactions for multiple operations
   - Add proper indexes
   - Follow naming conventions

## 🔍 Pull Request Process

### Before Creating PR

- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Comments added for complex code
- [ ] Documentation updated
- [ ] No console.log or debug code
- [ ] Tested in different browsers (if frontend)

### PR Title Format

Same as commit messages:
```
feat(admin): add user export functionality
```

### PR Description Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Changes Made
- Change 1
- Change 2
- Change 3

## Testing Done
- [ ] Tested in Chrome
- [ ] Tested in Firefox
- [ ] Tested on mobile
- [ ] Database migrations tested

## Screenshots (if applicable)
Add screenshots here

## Related Issues
Closes #123
```

### Review Process

1. At least one approval required
2. All discussions must be resolved
3. CI checks must pass (if configured)
4. No merge conflicts

## 🧪 Testing

### Manual Testing Checklist

- [ ] Feature works as expected
- [ ] No console errors
- [ ] Responsive design works
- [ ] Cross-browser compatibility
- [ ] No breaking changes to existing features

### Testing User Accounts

Use these test accounts for testing:

```
Admin:
- Username: admin
- Password: admin123

Lecturer:
- Username: drjohnson
- Password: lecturer123

Student:
- Username: student001
- Password: student123
```

## 🔧 Tools and Resources

### Recommended VS Code Extensions

- PHP Intelephense
- ESLint
- Prettier
- GitLens
- PHP Debug

### Useful Commands

```bash
# Check PHP syntax
php -l file.php

# Format code (if using Prettier)
npx prettier --write "**/*.{js,css,html}"

# Check git status
git status

# View commit history
git log --oneline --graph
```

## 📞 Getting Help

- Check existing documentation
- Search closed issues on GitHub
- Ask in team chat
- Contact project maintainers

## 📄 License

By contributing, you agree that your contributions will be licensed under the same license as the project.

---

Thank you for contributing! 🎉
