# Troubleshooting Guide - Cannot Access CRM System

## Issue: Cannot access https://www.forbixindia.com/software/crm

### Step 1: Test PHP is Working
1. Access: `https://www.forbixindia.com/software/crm/test.php`
   - If this works, PHP is functioning
   - If you see errors, note them down
   - If you get 404, files might not be uploaded correctly

### Step 2: Test Debug Version
1. Access: `https://www.forbixindia.com/software/crm/debug_index.php`
   - This will show detailed error messages
   - Check what errors appear

### Step 3: Common Issues and Solutions

#### Issue A: Blank Page / Nothing Shows
**Possible Causes:**
- PHP errors are hidden
- `.htaccess` file causing issues
- Missing files

**Solutions:**
1. Check if `test.php` works (from Step 1)
2. Temporarily rename `.htaccess` to `.htaccess.bak` and try again
3. Check FileZilla upload logs to ensure all files uploaded
4. Verify file structure matches:
   ```
   /software/crm/
   ├── index.php
   ├── api.php
   ├── config/
   │   └── db.php
   ├── modules/
   │   ├── auth.php
   │   ├── admin.php
   │   ├── customer.php
   │   └── backup.php
   ├── pages/
   │   ├── login.php
   │   ├── agent.php
   │   └── admin.php
   ├── css/
   │   └── style.css
   ├── js/
   │   └── script.js
   └── data/ (directory should exist)
   ```

#### Issue B: 500 Internal Server Error
**Possible Causes:**
- `.htaccess` syntax error
- PHP directives not allowed on shared hosting

**Solutions:**
1. Rename `.htaccess` to `.htaccess.bak`
2. Try accessing the site again
3. If it works, the `.htaccess` was the issue
4. Use the simplified `.htaccess` (I've updated it)

#### Issue C: 403 Forbidden Error
**Possible Causes:**
- File permissions too restrictive
- Directory permissions incorrect

**Solutions:**
1. Set directory permissions to 755:
   - Via FileZilla: Right-click folder → File Permissions → 755
   - Or via SSH: `chmod 755 /path/to/crm`
2. Set file permissions to 644:
   - Via FileZilla: Right-click file → File Permissions → 644
   - Or via SSH: `chmod 644 /path/to/crm/*.php`

#### Issue D: 404 Not Found
**Possible Causes:**
- Files not uploaded correctly
- Wrong directory path
- Case sensitivity issues (Linux servers)

**Solutions:**
1. Verify files are in: `/software/crm/` (not `/software/crm/crm/`)
2. Check case sensitivity - ensure exact case match
3. Verify `index.php` exists in the root CRM directory
4. Try accessing: `https://www.forbixindia.com/software/crm/index.php` directly

#### Issue E: Database Connection Errors
**Possible Causes:**
- `data/` directory doesn't exist or not writable
- PDO SQLite not enabled

**Solutions:**
1. Create `data/` and `data/backups/` directories via FileZilla
2. Set permissions to 755 on both directories
3. Run `install_check.php` to verify PHP extensions

### Step 4: Verify File Upload
In FileZilla, ensure:
- ✅ Transfer mode is set to "Binary" or "Auto"
- ✅ All files uploaded (not failed transfers)
- ✅ Directory structure is maintained
- ✅ No files are corrupted (file size matches)

### Step 5: Check Server Error Logs
1. Access your hosting control panel (cPanel/Plesk)
2. Check error logs for PHP errors
3. Look for any error messages related to `/software/crm/`

### Step 6: Quick Fixes to Try

**Fix 1: Simplified .htaccess**
If `.htaccess` is causing issues, use this minimal version:
```apache
DirectoryIndex index.php
Options -Indexes
```

**Fix 2: Check index.php is Default**
Some servers require explicit index file. Try:
- `https://www.forbixindia.com/software/crm/index.php`

**Fix 3: Verify PHP Version**
Run `test.php` and check PHP version. Requires PHP 7.4+

### Step 7: Still Not Working?

1. **Run Installation Check:**
   - Access: `https://www.forbixindia.com/software/crm/install_check.php`
   - This will show all issues

2. **Enable Error Display Temporarily:**
   Add this to the top of `index.php` (first line):
   ```php
   <?php error_reporting(E_ALL); ini_set('display_errors', 1); ?>
   ```

3. **Check File Paths:**
   - Some servers have different document root
   - Verify actual server path matches your upload

### Quick Checklist:
- [ ] Can access `test.php`?
- [ ] Can access `debug_index.php`?
- [ ] All files uploaded correctly?
- [ ] Directory permissions set to 755?
- [ ] File permissions set to 644?
- [ ] `data/` directory exists and is writable?
- [ ] PHP version is 7.4+?
- [ ] PDO SQLite extension enabled?

### Contact Your Hosting Provider If:
- PHP version is too old (< 7.4)
- PDO SQLite extension is not installed
- You cannot set directory permissions
- Error logs show server-level issues

