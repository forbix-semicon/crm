# CRM System

A modular CRM (Customer Relationship Management) system built with PHP, HTML, CSS, JavaScript, and libSQL (SQLite fork).

## Features

### Agent Features
- Login with agent credentials
- Customer entry form with all required fields
- Auto-generated Customer IDs (starting from CID20001)
- Multiple product category selection
- Email validation (supports multiple emails)
- Save/Update customer information
- Comments management
- Database backup functionality
- List all customers with editable cells
- Search functionality (placeholder)

### Admin Features
- Login with admin credentials
- Create and manage agent accounts
- Manage product categories (add/delete)
- Manage customer types (add/delete)
- Manage statuses (add/delete)

## Installation

1. Upload all files to your web server in the `/crm` directory (or your preferred path)

2. Ensure the following directories have write permissions (755 or 775):
   - `data/` - for database storage
   - `data/backups/` - for backup files

3. Access the system via: `https://yourdomain.com/crm` (or your configured path)

## Default Login Credentials

### Admin
- Username: `admin`
- Password: `admin`

### Agent
- Username: `agent1`
- Password: `agent1`

**⚠️ IMPORTANT: Change default passwords after first login!**

## Database

The system uses libSQL (SQLite fork) for data storage. The database file is automatically created at:
- `data/crm.db`

Backups are stored in:
- `data/backups/crm_YYMMDD_HHMMSS.db`

## File Structure

```
crm/
├── config/
│   └── db.php              # Database configuration and initialization
├── modules/
│   ├── auth.php            # Authentication functions
│   ├── admin.php           # Admin operations
│   ├── customer.php        # Customer operations
│   └── backup.php          # Backup functionality
├── pages/
│   ├── login.php           # Login page
│   ├── agent.php           # Agent dashboard
│   └── admin.php           # Admin dashboard
├── css/
│   └── style.css           # Stylesheet
├── js/
│   └── script.js           # JavaScript functionality
├── data/                   # Database storage (auto-created)
│   └── backups/            # Backup storage (auto-created)
├── index.php               # Main entry point
├── api.php                 # API handler for AJAX requests
├── .htaccess               # Apache configuration
└── README.md               # This file
```

## Requirements

- PHP 7.4 or higher
- PDO with SQLite support (libSQL/SQLite)
- Web server (Apache/Nginx)
- Write permissions for data directory

## Security Notes

- The `.htaccess` file protects database files from direct access
- Passwords are hashed using PHP's `password_hash()` function
- Session management is used for authentication
- SQL injection protection via prepared statements

## Support

For issues or questions, please contact your system administrator.

