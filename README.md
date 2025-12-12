# CRM System - libSQL Version

A modular CRM system built with PHP, libSQL (SQLite fork), HTML, CSS, and JavaScript. Features separate admin and agent interfaces with dynamic configuration management.

## Features

- **Dual Login System**: Separate admin and agent authentication
- **Admin Panel**: Manage agents, product categories, customer types, and statuses
- **Agent Interface**: Customer data entry with dynamic form fields
- **libSQL Database**: File-based database (SQLite compatible)
- **Modular Architecture**: Separate PHP modules for easy maintenance
- **Multi-Agent Support**: Multiple agents can work simultaneously
- **Dynamic Configuration**: Product categories, customer types, and statuses managed by admin
- **Backup System**: CSV backup functionality
- **Responsive Design**: Modern, mobile-friendly UI

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
- **Windows/XAMPP**: Usually works by default

### 3. Access the CRM

Navigate to:
- **Localhost**: `http://localhost/forbixindia.com-v2/software/crm/`
- **Internet**: `http://your-domain.com/crm/`

The database will be automatically created on first access in the `data/` directory.

## Login Credentials

### Admin
- **Username**: admin
- **Password**: admin
- **Access**: Admin panel for managing system configuration

### Agent (Default)
- **Username**: agent1
- **Password**: agent1
- **Access**: Agent interface for customer data entry

## Module Structure

### Core Modules
- `modules/auth.php` - Authentication functions (admin/agent)
- `modules/db.php` - libSQL database connection
- `modules/db_setup.php` - Database schema creation
- `modules/login_handler.php` - Login processing
- `modules/login_popup.php` - Login popup UI
- `modules/logout.php` - Logout handler

### Agent Modules
- `modules/form_handler.php` - Form submission handler
- `modules/comments_handler.php` - Comments save/update handler
- `modules/backup.php` - Backup functionality
- `modules/customer_id_generator.php` - Auto-generate customer IDs

### Admin Modules
- `modules/admin_agents.php` - Agent management
- `modules/admin_categories.php` - Product categories management
- `modules/admin_types.php` - Customer types management
- `modules/admin_statuses.php` - Statuses management

## Database Schema

### users table
- id, username, password, role (admin/agent)
- created_at, updated_at

### product_categories table
- id, name, display_order
- created_at

### customer_types table
- id, name, display_order
- created_at

### statuses table
- id, name, display_order
- created_at

### customers table
- id, customer_id (UNIQUE), date, time
- customer_name, company_name
- primary_contact, secondary_contact
- email_id, city
- product_category, requirement
- source, assigned_to
- customer_type, status
- created_at, updated_at

### customer_comments table
- id, customer_id (FOREIGN KEY)
- comment_text, comment_order
- created_at, updated_at

## Features

### Admin Features

1. **Agent Management**
   - Create new agent accounts
   - Edit existing agents (username/password)
   - View all agents
   - Delete agents

2. **Product Categories Management**
   - Add new product categories
   - Edit existing categories
   - Delete categories
   - Categories appear dynamically in agent form

3. **Customer Types Management**
   - Add new customer types
   - Edit existing types
   - Delete types
   - Types appear dynamically in agent form

4. **Statuses Management**
   - Add new statuses
   - Edit existing statuses
   - Delete statuses
   - Statuses appear dynamically in agent form

### Agent Features

1. **Customer Data Entry**
   - Auto-generated Customer ID (CID20001+)
   - Date (calendar, default: today)
   - Time (manual, default: current IST)
   - Customer Name, Company Name (multiline)
   - Primary/Secondary Contact (multiline)
   - Email ID (multiline, validates @ and .)
   - City (multiline)
   - Product Category (multiple checkboxes, dynamic from admin)
   - Requirement (multiline)
   - Customer Type (radio, dynamic from admin)
   - Source (dropdown: Phone Call, Whatsup, Website, Email, Social Media, Not Sure)
   - Status (dropdown, dynamic from admin)
   - Comments (multiple, dynamic)

2. **Comments Management**
   - Add multiple comments
   - Save/Update Comments button (saves independently)
   - Remove individual comments

3. **Other Features**
   - Save button (saves all customer data)
   - Cancel button (clears form)
   - Backup button (creates CSV backup)
   - Search box (placeholder)
   - Short/Expanded Report buttons (placeholders)

## Multi-Agent Support

- Multiple agents can log in and work simultaneously
- Each customer record tracks which agent created it (assigned_to field)
- No conflicts - libSQL handles concurrent access

## Advantages of libSQL

1. **No Server Required**: Works without a separate database server
2. **Portable**: Single database file (`data/crm.db`)
3. **Easy Backup**: Just copy the database file
4. **Works Everywhere**: Compatible with XAMPP and internet servers
5. **Zero Configuration**: No database setup needed
6. **SQLite Compatible**: Uses standard SQLite syntax

## File Structure

```
crm/
├── index.php                 # Agent interface
├── admin.php                 # Admin panel
├── config.php                # Configuration
├── modules/                  # PHP modules
│   ├── auth.php
│   ├── db.php
│   ├── db_setup.php
│   ├── form_handler.php
│   ├── comments_handler.php
│   ├── backup.php
│   ├── customer_id_generator.php
│   ├── login_handler.php
│   ├── login_popup.php
│   ├── logout.php
│   ├── admin_agents.php
│   ├── admin_categories.php
│   ├── admin_types.php
│   └── admin_statuses.php
├── assets/
│   ├── css/
│   │   ├── style.css
│   │   └── admin.css
│   └── js/
│       ├── main.js
│       └── admin.js
├── data/                      # libSQL database (auto-created)
│   └── crm.db
└── backups/                   # CSV backups
    └── crm_*.csv
```

## Security Notes

- Change default login credentials in production
- Use prepared statements (already implemented)
- Enable HTTPS in production
- Set proper file permissions for `data/` directory
- Disable error display in production (`display_errors = 0`)
- Consider password hashing for production use

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

### Dynamic Fields Not Updating
- Clear browser cache
- Check admin panel to verify items are created
- Refresh the agent page

## License

This is a custom CRM system for internal use.
