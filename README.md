# CRM System - SQLite Version

A modular CRM system built with PHP, SQLite, HTML, CSS, and JavaScript.

## Features

- **Login System**: Agent authentication with popup login
- **Customer Management**: Complete customer data entry form
- **SQLite Database**: File-based database (no server required)
- **Modular Architecture**: Separate PHP modules for easy maintenance
- **Backup System**: CSV backup functionality
- **Responsive Design**: Modern, mobile-friendly UI
- **Works Everywhere**: Compatible with XAMPP (localhost) and internet servers

## Requirements

- PHP 7.4 or higher
- PDO SQLite extension (enabled by default in most PHP installations)
- Web server (Apache/Nginx) or XAMPP

## Installation

### 1. Upload Files

Upload all files to your web server directory:
- For XAMPP: `C:\xampp\htdocs\forbixindia.com-v2\software\crm\`
- For internet server: Upload to your web hosting directory

### 2. Set Permissions

Ensure the `data/` directory is writable:
- **Linux/Internet Server**: `chmod 755 data` or `chmod 777 data`
- **Windows/XAMPP**: Usually works by default, but ensure the folder has write permissions

### 3. Access the CRM

Navigate to:
- **Localhost**: `http://localhost/forbixindia.com-v2/software/crm/`
- **Internet**: `http://your-domain.com/crm/`

The database will be automatically created on first access in the `data/` directory.

## Login Credentials

- **Username**: agent1
- **Password**: agent1

## Module Structure

- `modules/auth.php` - Authentication functions
- `modules/db.php` - SQLite database connection
- `modules/db_setup.php` - Database schema creation
- `modules/form_handler.php` - Form submission handler
- `modules/backup.php` - Backup functionality
- `modules/customer_id_generator.php` - Auto-generate customer IDs
- `modules/login_handler.php` - Login processing
- `modules/login_popup.php` - Login popup UI
- `modules/logout.php` - Logout handler

## Database

- **Location**: `data/crm.db` (SQLite database file)
- **Auto-created**: Database and tables are created automatically on first access
- **Backup**: Use the Backup button to create CSV backups in `backups/` directory

## Database Schema

### customers table
- id (INTEGER PRIMARY KEY)
- customer_id (VARCHAR, UNIQUE)
- date, time
- customer_name, company_name
- primary_contact, secondary_contact
- email_id, city
- product_category, requirement
- source, assigned_to
- customer_type, status
- created_at, updated_at

### customer_comments table
- id (INTEGER PRIMARY KEY)
- customer_id (FOREIGN KEY)
- comment_text (TEXT)
- comment_order (INTEGER)
- created_at (DATETIME)

## Features

### Customer ID Generation
- Auto-generates 5-digit customer IDs starting from CID20001
- Format: CID20001, CID20002, etc.

### Form Fields
- Customer ID (auto-generated, readonly)
- Date (calendar, default: today)
- Time (manual, default: current time IST)
- Customer Name (multiline)
- Company Name (multiline)
- Primary/Secondary Contact (multiline)
- Email ID (multiline, validated for @ symbol)
- City (multiline)
- Product Category (multiple checkboxes)
- Requirement (multiline)
- Customer Type (radio: New/Existing)
- Source (dropdown)
- Status (dropdown)
- Comments (multiple, dynamic)

### Backup
- Creates CSV backup files in `backups/` directory
- Format: `crm_yymmdd_hhmm.csv`
- Includes all customer data and comments

## Advantages of SQLite

1. **No Server Required**: Works without a separate database server
2. **Portable**: Single database file (`data/crm.db`)
3. **Easy Backup**: Just copy the database file
4. **Works Everywhere**: Compatible with XAMPP and internet servers
5. **Zero Configuration**: No database setup needed

## File Structure

```
crm/
├── index.php                 # Main CRM page
├── config.php                # Configuration
├── modules/                   # PHP modules
│   ├── auth.php
│   ├── db.php
│   ├── db_setup.php
│   ├── form_handler.php
│   ├── backup.php
│   ├── customer_id_generator.php
│   ├── login_handler.php
│   ├── login_popup.php
│   └── logout.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── data/                      # SQLite database (auto-created)
│   └── crm.db
└── backups/                   # CSV backups
    └── crm_*.csv
```

## Future Features (Placeholders)

- Search functionality (Customer ID, Phone, Email)
- Short Report
- Expanded Report

## Security Notes

- Change default login credentials in production
- Use prepared statements (already implemented)
- Enable HTTPS in production
- Set proper file permissions for `data/` directory
- Disable error display in production (`display_errors = 0`)

## Troubleshooting

### Database Permission Error
- Check `data/` directory permissions
- Ensure web server user can write to `data/` directory
- Linux: `chmod 755 data` or `chmod 777 data`

### Customer ID Not Generating
- Check database file exists: `data/crm.db`
- Verify file permissions
- Check PHP error logs

### Backup Not Working
- Check `backups/` directory exists and is writable
- Verify file permissions

## License

This is a custom CRM system for internal use.
