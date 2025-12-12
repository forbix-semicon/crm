# CRM System - Quick Setup Guide

## Installation Steps

1. **Upload Files**
   - Upload all files to your web server in the `/crm` directory (or your preferred path)

2. **Set Permissions**
   ```bash
   chmod 755 data/
   chmod 755 data/backups/
   ```
   Or ensure the web server has write permissions to these directories

3. **Run Installation Check**
   - Visit: `https://yourdomain.com/crm/install_check.php`
   - This will verify all requirements are met

4. **Access the System**
   - Visit: `https://yourdomain.com/crm/` or `https://yourdomain.com/crm/index.php`

## Default Login Credentials

### Admin Account
- **Username:** `admin`
- **Password:** `admin`
- **Access:** Full system administration

### Agent Account
- **Username:** `agent1`
- **Password:** `agent1`
- **Access:** Customer entry and management

⚠️ **IMPORTANT:** Change these passwords immediately after first login!

## Features Overview

### Agent Dashboard
- Customer entry form with all required fields
- Auto-generated Customer IDs (CID20001+)
- Product category selection (multiple)
- Email validation (supports multiple emails)
- Customer search and list functionality
- Database backup
- Comments management

### Admin Dashboard
- Create and manage agent accounts
- Manage product categories
- Manage customer types
- Manage statuses
- View all users

## Database Location
- **Database:** `data/crm.db`
- **Backups:** `data/backups/crm_YYMMDD_HHMMSS.db`

## System Requirements
- PHP 7.4 or higher
- PDO SQLite extension
- Web server (Apache/Nginx)
- Write permissions for data directory

## Troubleshooting

### Database Errors
- Ensure `data/` directory is writable
- Check PHP has PDO SQLite extension enabled
- Verify file permissions (755 recommended)

### Login Issues
- Clear browser cookies/session
- Check session directory is writable
- Verify database was created successfully

### Permission Errors
- Set directory permissions: `chmod 755 data data/backups`
- Ensure web server user can write to these directories

## Security Notes
- Database files are protected via `.htaccess`
- Passwords are hashed using PHP's password_hash()
- Sessions are used for authentication
- SQL injection protection via prepared statements

## Support
For issues, check the installation check script first: `install_check.php`

