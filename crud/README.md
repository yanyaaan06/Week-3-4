# CRUD System - Employees & Products Management

A complete PHP/MySQL CRUD (Create, Read, Update, Delete) application for managing employees and products with security best practices.

## Features

- ✅ **Create**: Forms with PHP using prepared statements
- ✅ **Read**: Display data with search and filter functionality
- ✅ **Update**: Edit forms with input validation
- ✅ **Delete**: Soft-delete (status update) and Hard-delete (permanent removal) options
- ✅ **Security**: Prepared statements, input sanitization, and validation
- ✅ **Modern UI**: Responsive design with clean styling

## Requirements

- WAMP Server (or XAMPP/LAMP)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser

## Installation

1. **Copy the project** to your WAMP `www` directory:
   ```
   C:\wamp64\www\crud
   ```

2. **Create the database**:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `crud_db`
   - Import the `schema.sql` file or run the SQL commands

3. **Configure database connection** (if needed):
   - Edit `config/database.php`
   - Update database credentials if different from defaults:
     ```php
     define('DB_NAME', 'crud_db');
     define('DB_USER', 'root');
     define('DB_PASS', ''); // Your MySQL password
     ```

4. **Start WAMP Server** and access the application:
   ```
   http://localhost/crud/
   ```

## Database Setup

### Option 1: Using phpMyAdmin
1. Go to http://localhost/phpmyadmin
2. Create database: `crud_db`
3. Select the database
4. Go to "Import" tab
5. Choose `schema.sql` file
6. Click "Go"

### Option 2: Using MySQL Command Line
```sql
CREATE DATABASE crud_db;
USE crud_db;
SOURCE C:/wamp64/www/crud/schema.sql;
```

## Project Structure

```
crud/
├── assets/
│   └── css/
│       └── style.css          # Main stylesheet
├── config/
│   └── database.php           # Database configuration
├── includes/
│   └── functions.php          # Utility functions
├── employees/
│   ├── index.php              # List employees (Read)
│   ├── create.php             # Create employee
│   ├── view.php               # View employee details
│   ├── edit.php               # Edit employee (Update)
│   └── delete.php             # Delete employee
├── products/
│   ├── index.php              # List products (Read)
│   ├── create.php             # Create product
│   ├── view.php               # View product details
│   ├── edit.php               # Edit product (Update)
│   └── delete.php             # Delete product
├── schema.sql                 # Database schema
├── erd.md                     # Entity-Relationship Diagram
├── index.php                  # Home page
└── README.md                  # This file
```

## Security Features

1. **Prepared Statements**: All database queries use PDO prepared statements to prevent SQL injection
2. **Input Sanitization**: All user inputs are sanitized using `htmlspecialchars()` and custom functions
3. **Input Validation**: Server-side validation for all required fields
4. **Error Handling**: Proper error handling with user-friendly messages
5. **XSS Protection**: Output escaping to prevent cross-site scripting attacks

## Usage

### Employees Management

1. **View Employees**: Navigate to Employees section
2. **Create Employee**: Click "Add New Employee", fill the form
3. **Edit Employee**: Click "Edit" on any employee record
4. **Delete Employee**: Click "Delete" - choose Soft Delete (recommended) or Hard Delete
5. **Search/Filter**: Use search box and status filter to find employees

### Products Management

1. **View Products**: Navigate to Products section
2. **Create Product**: Click "Add New Product", fill the form
3. **Edit Product**: Click "Edit" on any product record
4. **Delete Product**: Click "Delete" - choose Soft Delete (recommended) or Hard Delete
5. **Search/Filter**: Use search, category, and status filters

## Delete Types

### Soft Delete (Recommended)
- Updates status field (employees: 'terminated', products: 'discontinued')
- Data remains in database
- Can be restored if needed
- Maintains referential integrity
- Preserves audit trail

### Hard Delete
- Permanently removes record from database
- **Warning**: Cannot be recovered
- May break foreign key relationships
- Use only when absolutely necessary

## Database Schema

See `schema.sql` for complete table definitions and `erd.md` for Entity-Relationship Diagram.

### Tables:
- **employees**: Employee information and employment details
- **products**: Product information, pricing, and inventory
- **employee_products**: Junction table for employee-product assignments (optional)

## Code Review Highlights

- ✅ All SQL queries use prepared statements
- ✅ Input validation on all forms
- ✅ Proper error handling
- ✅ Security best practices implemented
- ✅ Clean, maintainable code structure
- ✅ Responsive design
- ✅ User-friendly interface

## Troubleshooting

### Database Connection Error
- Check if MySQL is running in WAMP
- Verify database credentials in `config/database.php`
- Ensure database `crud_db` exists

### Page Not Found
- Ensure WAMP Apache server is running
- Check URL: `http://localhost/crud/`
- Verify file structure matches project structure

### SQL Errors
- Ensure database schema is imported correctly
- Check MySQL version compatibility
- Verify table names match in code

## License

This is a sample project for educational purposes.
