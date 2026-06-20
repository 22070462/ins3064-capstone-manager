# Git Quick Reference Guide

## 🚀 Common Workflows

### Starting New Work

```bash
# 1. Update your main branch
git checkout main
git pull origin main

# 2. Create a new branch
git checkout -b feature/your-feature-name

# 3. Make your changes...

# 4. Check what changed
git status
git diff

# 5. Stage your changes
git add .
# Or stage specific files
git add file1.php file2.js

# 6. Commit with conventional message
git commit -m "feat: add new feature"

# 7. Push to remote
git push origin feature/your-feature-name
```

### Updating Your Branch

```bash
# Keep your branch up to date with main
git checkout main
git pull origin main
git checkout your-branch
git rebase main

# Or use merge (less clean history)
git checkout your-branch
git merge main
```

### Creating a Pull Request

1. Push your branch to GitHub
2. Go to GitHub repository
3. Click "Compare & pull request"
4. Fill in the PR template
5. Request reviewers
6. Wait for approval and merge

## 📝 Commit Message Examples

### Feature
```bash
git commit -m "feat(admin): add user export to CSV"
git commit -m "feat(student): implement project submission"
git commit -m "feat(api): add registration endpoint"
```

### Bug Fix
```bash
git commit -m "fix(auth): resolve login session timeout"
git commit -m "fix(ui): correct responsive layout on mobile"
git commit -m "fix(db): handle null values in query"
```

### Documentation
```bash
git commit -m "docs: update README installation steps"
git commit -m "docs: add API documentation"
git commit -m "docs(contributing): clarify PR process"
```

### Refactor
```bash
git commit -m "refactor(admin): simplify dashboard logic"
git commit -m "refactor: extract duplicate code to helper"
```

### Performance
```bash
git commit -m "perf(db): optimize user query with indexes"
git commit -m "perf(ui): lazy load images"
```

### Style
```bash
git commit -m "style: format code with PSR-12"
git commit -m "style(css): update color scheme"
```

### Chore
```bash
git commit -m "chore: update gitignore"
git commit -m "chore(deps): update Bootstrap to 5.3.2"
```

## 🔧 Useful Commands

### Status & Inspection

```bash
# Check status
git status

# View changes
git diff
git diff --staged

# View commit history
git log
git log --oneline
git log --oneline --graph

# Show specific commit
git show commit-hash
```

### Branching

```bash
# List branches
git branch
git branch -a  # including remote

# Create branch
git branch feature/new-feature
git checkout -b feature/new-feature  # create and switch

# Switch branch
git checkout branch-name

# Delete branch
git branch -d branch-name  # safe delete
git branch -D branch-name  # force delete

# Delete remote branch
git push origin --delete branch-name
```

### Staging & Committing

```bash
# Stage all changes
git add .

# Stage specific files
git add file1.php file2.js

# Unstage files
git restore --staged file.php

# Discard changes
git restore file.php
git checkout -- file.php  # old syntax

# Amend last commit (not pushed yet)
git commit --amend
git commit --amend -m "new message"
```

### Remote Operations

```bash
# View remotes
git remote -v

# Fetch changes
git fetch origin

# Pull changes
git pull origin main

# Push changes
git push origin branch-name

# Force push (use carefully!)
git push -f origin branch-name
```

### Stashing

```bash
# Save changes temporarily
git stash
git stash save "work in progress"

# List stashes
git stash list

# Apply stash
git stash apply
git stash pop  # apply and remove

# Delete stash
git stash drop
git stash clear  # delete all
```

### Undoing Changes

```bash
# Undo last commit (keep changes)
git reset --soft HEAD~1

# Undo last commit (discard changes)
git reset --hard HEAD~1

# Revert a commit (creates new commit)
git revert commit-hash

# Reset to specific commit
git reset --hard commit-hash
```

### Comparing

```bash
# Compare branches
git diff main..feature-branch

# Compare with remote
git diff main origin/main

# Compare specific files
git diff main feature-branch -- file.php
```

## 🐛 Troubleshooting

### Merge Conflicts

```bash
# When you get conflicts:
# 1. Open conflicted files
# 2. Look for conflict markers (<<<<, ====, >>>>)
# 3. Resolve conflicts manually
# 4. Stage resolved files
git add resolved-file.php
# 5. Continue merge/rebase
git rebase --continue
# Or commit if merging
git commit
```

### Accidentally Committed to Wrong Branch

```bash
# Move commit to new branch
git branch feature-branch
git reset --hard HEAD~1
git checkout feature-branch
```

### Need to Change Last Commit Message

```bash
# If not pushed yet
git commit --amend -m "new message"

# If already pushed
git commit --amend -m "new message"
git push -f origin branch-name
```

### Accidentally Deleted Local Branch

```bash
# Find the commit hash
git reflog

# Recreate branch
git checkout -b recovered-branch commit-hash
```

## 📋 Best Practices

### DO ✅

- Commit often with meaningful messages
- Pull before starting work
- Use descriptive branch names
- Test before committing
- Write clear commit messages
- Keep commits focused and atomic
- Review your changes before committing

### DON'T ❌

- Commit directly to main
- Push untested code
- Commit secrets or passwords
- Use vague commit messages
- Commit large files
- Force push to main
- Leave commented-out code

## 🔗 Quick Links

- [Full Contributing Guide](CONTRIBUTING.md)
- [Conventional Commits](https://www.conventionalcommits.org/)
- [GitHub Flow](https://guides.github.com/introduction/flow/)

## 💡 Tips

1. **Use git aliases** for common commands:
```bash
git config --global alias.st status
git config --global alias.co checkout
git config --global alias.br branch
git config --global alias.ci commit
```

2. **Configure your identity:**
```bash
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

3. **See what will be pushed:**
```bash
git log origin/main..HEAD
```

4. **Undo last push (dangerous!):**
```bash
git reset --hard HEAD~1
git push -f origin branch-name
```

---

**Need help?** Check [CONTRIBUTING.md](CONTRIBUTING.md) or ask the team!
