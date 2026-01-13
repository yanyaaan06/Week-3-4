# GitHub Setup Guide

## ✅ Step 1: Configure Git (Optional but Recommended)

If you want to set your Git identity, run these commands:

```bash
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

## ✅ Step 2: Create a GitHub Repository

1. **Go to GitHub**: https://github.com
2. **Sign in** to your account (or create one if you don't have it)
3. **Click the "+" icon** in the top right corner
4. **Select "New repository"**
5. **Fill in the details**:
   - Repository name: `crud-system` (or any name you prefer)
   - Description: "PHP/MySQL CRUD application for managing employees and products"
   - Visibility: Choose **Public** or **Private**
   - **DO NOT** initialize with README, .gitignore, or license (we already have these)
6. **Click "Create repository"**

## ✅ Step 3: Connect Local Repository to GitHub

After creating the repository on GitHub, you'll see a page with setup instructions. Use these commands:

### Option A: If you haven't created the repository yet
```bash
cd c:\wamp64\www\crud
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
git branch -M main
git push -u origin main
```

### Option B: If your GitHub repo uses "master" branch
```bash
cd c:\wamp64\www\crud
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
git push -u origin master
```

**Replace:**
- `YOUR_USERNAME` with your GitHub username
- `YOUR_REPO_NAME` with your repository name

## ✅ Step 4: Push Your Code

Run the push command (from Option A or B above). You'll be prompted for:
- **Username**: Your GitHub username
- **Password**: Use a **Personal Access Token** (not your GitHub password)

### How to Create a Personal Access Token:

1. Go to GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Click "Generate new token (classic)"
3. Give it a name (e.g., "CRUD Project")
4. Select scopes: Check **`repo`** (full control of private repositories)
5. Click "Generate token"
6. **Copy the token immediately** (you won't see it again!)
7. Use this token as your password when pushing

## ✅ Step 5: Verify

After pushing, refresh your GitHub repository page. You should see all your files!

## 🔄 Future Updates

Whenever you make changes, use these commands:

```bash
cd c:\wamp64\www\crud
git add .
git commit -m "Description of your changes"
git push
```

## 📝 Quick Reference Commands

```bash
# Check status
git status

# Add all changes
git add .

# Commit changes
git commit -m "Your commit message"

# Push to GitHub
git push

# Pull latest changes (if working with others)
git pull

# View commit history
git log
```

## 🔒 Security Note

**IMPORTANT**: Make sure `config/database.php` doesn't contain sensitive credentials if your repo is public. Consider:
- Using environment variables
- Adding `config/database.php` to `.gitignore` and creating a `config/database.example.php` template
- Using GitHub Secrets for sensitive data
