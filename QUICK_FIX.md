# Quick Fix Guide - Cannot See CRM Page

## Immediate Steps to Diagnose:

### 1. Test if PHP is Working
Visit: `https://www.forbixindia.com/software/crm/test.php`

**If this shows an error or blank page:**
- PHP might not be working
- Contact your hosting provider

**If this works:**
- Continue to step 2

### 2. Try Debug Version
Visit: `https://www.forbixindia.com/software/crm/index_debug.php`

**This will show any PHP errors.** Note down any error messages.

### 3. Check File Structure
In FileZilla, verify you have this structure:
```
/software/crm/
├── index.php          ← MUST BE HERE
├── config/
├── modules/
├── pages/
├── css/
├── js/
└── data/              ← Create this folder if missing
```

### 4. Common Quick Fixes

#### Fix A: .htaccess Issue
1. In FileZilla, rename `.htaccess` to `.htaccess.old`
2. Try accessing: `https://www.forbixindia.com/software/crm/`
3. If it works now, the `.htaccess` was the problem
4. Replace `.htaccess` with `.htaccess.minimal` (rename it to `.htaccess`)

#### Fix B: Missing Data Directory
1. In FileZilla, create folder `data` inside `crm`
2. Create folder `backups` inside `data`
3. Right-click `data` folder → Properties → Set permissions to 755
4. Right-click `backups` folder → Properties → Set permissions to 755

#### Fix C: Try Direct Access
Instead of: `https://www.forbixindia.com/software/crm`
Try: `https://www.forbixindia.com/software/crm/index.php`

#### Fix D: Check File Permissions
All `.php` files should be 644
All directories should be 755

In FileZilla:
- Right-click file/folder → File Permissions
- Set numeric value: 644 for files, 755 for folders

### 5. What to Check in FileZilla

1. **Transfer Mode:**
   - Edit → Settings → Transfers
   - Set to "Binary" or "Auto"

2. **File Upload Status:**
   - Check all files uploaded successfully (green checkmarks)
   - No failed uploads (red X marks)

3. **Directory Structure:**
   - Make sure you're uploading TO: `/software/crm/`
   - NOT to: `/software/crm/crm/` (double nested)

### 6. Still Not Working?

Run the installation check:
`https://www.forbixindia.com/software/crm/install_check.php`

This will tell you exactly what's wrong.

### Most Common Issues:

1. **Blank page** = PHP error (use `index_debug.php` to see it)
2. **404 error** = Wrong path or files not uploaded
3. **500 error** = `.htaccess` problem or permission issue
4. **403 error** = Permission problem

