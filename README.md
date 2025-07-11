# Godspeed Inventory Management System 3.0

A modern, secure, and feature-rich inventory management system built with PHP, MySQL, and Bootstrap 5.3.0. This version represents a major upgrade with enhanced security, modern UI/UX, and comprehensive analytics.

## 🚀 Features

### Core Features
- **User Authentication & Authorization**
  - Secure login/logout system with bcrypt password hashing
  - User registration with email validation
  - Role-based access control (Admin/User)
  - Session management with configurable timeout (1 hour)
  - Login attempt tracking with account lockout (5 attempts, 15-minute lockout)
  - Secure session configuration with httponly cookies

- **Inventory Management**
  - Add, edit, delete inventory items with soft delete
  - Real-time stock level monitoring with alerts
  - Low stock warnings with configurable thresholds
  - Category-based organization with supplier tracking
  - Price and unit management with location tracking
  - Comprehensive item descriptions and metadata

- **Dashboard & Analytics**
  - Real-time inventory summary with interactive cards
  - Visual charts using Chart.js (stock distribution, top items)
  - Stock level distribution pie chart
  - Top items by quantity bar chart
  - Low stock and out-of-stock reports with timestamps
  - Complete inventory table with sorting capabilities

- **Advanced Security Features**
  - Bcrypt password hashing with configurable cost (12)
  - CSRF token protection framework
  - SQL injection prevention with prepared statements
  - XSS protection with content-type headers
  - Input sanitization and validation
  - Complete audit trail logging for all user actions
  - IP address and user agent tracking
  - Secure session configuration with security headers

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ with UTF-8 support
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **UI Framework**: Bootstrap 5.3.0 with responsive design
- **Icons**: Font Awesome 6.4.0
- **Charts**: Chart.js for data visualization
- **Security**: Bcrypt, CSRF protection, prepared statements

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser with JavaScript enabled

## 🚀 Installation

1. **Clone or download the project**
   ```bash
   git clone <repository-url>
   cd godspeed-inventory-management-3.0
   ```

2. **Set up the database**
   - Create a MySQL database (or let the system create it automatically)
   - Update database credentials in `config.php`
   - The system will automatically create all required tables on first run

3. **Configure web server**
   - Point your web server to the project directory
   - Ensure PHP has write permissions for session handling
   - Enable PHP PDO and MySQL extensions

4. **Access the application**
   - Navigate to `http://your-domain/login.html`
   - Create your first account using the signup page
   - The system will automatically set up the database schema

## 📁 File Structure

```
godspeed-inventory-management-3.0/
├── index.html              # Main dashboard with analytics
├── items.html              # Items management page
├── reports.html            # Reports and analytics page
├── login.html              # Login page with security features
├── signup.html             # Registration page
├── app.js                  # Main JavaScript application with API services
├── auth.js                 # Authentication JavaScript
├── styles.css              # Modern CSS with Bootstrap 5 styling
├── config.php              # Enhanced database and security configuration
├── auth_api.php            # Authentication API endpoints
├── items_api.php           # Items management API endpoints
├── signup.php              # User registration handler
├── logout.php              # Logout handler
├── schema.sql              # Database schema reference
├── VERSION_LOG.md          # Detailed version history
├── Sprint_1_Task_Schedule_Report.md  # Project documentation
└── README.md               # This file
```

## 🔧 Configuration

### Database Configuration
Edit `config.php` to configure your database connection:

```php
$host = 'localhost';
$dbname = 'inventory_db';
$username = 'your_username';
$password = 'your_password';
```

### Security Settings
The system includes configurable security parameters:

```php
define('HASH_COST', 12);           // Password hashing cost (higher = more secure)
define('SESSION_TIMEOUT', 3600);    // Session timeout (1 hour)
define('MAX_LOGIN_ATTEMPTS', 5);    // Max failed login attempts
define('LOCKOUT_TIME', 900);        // Account lockout time (15 minutes)
```

## 🎯 Usage

### Dashboard
- View real-time inventory summary with interactive cards
- Monitor stock levels with color-coded status indicators
- Access quick action buttons for common tasks
- View interactive charts for stock distribution and top items
- Real-time data updates without page refresh

### Items Management
- Add new inventory items with comprehensive information
- Edit existing items with full audit trail
- Delete items (soft delete for data preservation)
- Advanced search and filtering capabilities
- Monitor stock status with visual indicators
- Category and supplier management

### Reports & Analytics
- View comprehensive inventory reports with charts
- Analyze stock distribution across categories
- Identify low stock items with detailed listings
- Export functionality framework (ready for implementation)
- Complete inventory table with sorting and filtering

## 🔒 Security Features

### Authentication Security
- Bcrypt password hashing with cost factor 12
- Account lockout after 5 failed login attempts
- 15-minute lockout period for security
- Session timeout after 1 hour of inactivity
- Secure session configuration with httponly cookies

### Data Protection
- Prepared statements for all database queries
- Input sanitization and validation for all user inputs
- CSRF token protection framework
- XSS prevention with content-type headers
- SQL injection prevention with parameterized queries

### Audit Trail & Compliance
- Complete audit trail logging for all user actions
- Track changes to inventory items with before/after values
- Monitor login/logout events with timestamps
- IP address and user agent tracking for security
- JSON storage of old and new values for compliance

## 📊 Database Schema

### Enhanced Users Table
- User authentication with email and role management
- Role-based access control (admin/user)
- Login attempt tracking and account lockout
- Account status management with active/inactive states
- Comprehensive audit trail integration

### Comprehensive Items Table
- Complete inventory item information with descriptions
- Stock level tracking with min/max quantities
- Category and supplier data management
- Price and location details with unit management
- Soft delete functionality for data preservation

### Audit Log Table
- Complete audit trail of all system actions
- Change tracking for compliance and security
- User activity monitoring with detailed metadata
- JSON storage for flexible data tracking

### Sessions Table
- Enhanced session management with security tracking
- IP address and user agent logging
- Automatic cleanup of expired sessions

## 🎨 UI/UX Features

### Modern Design
- Bootstrap 5.3.0 responsive design
- Font Awesome 6.4.0 icons throughout
- Modern color scheme with CSS custom properties
- Gradient backgrounds and enhanced visual appeal
- Smooth animations and hover effects

### Interactive Elements
- Real-time search with debounced functionality
- Dynamic charts and graphs using Chart.js
- Smooth animations and transitions
- Loading indicators and toast notifications
- Password visibility toggle functionality

### User Experience
- Mobile-first responsive design
- Intuitive navigation with active state indicators
- Clear status indicators with color-coded badges
- Helpful error messages and validation feedback
- Responsive tables with horizontal scrolling

## 🔧 API Endpoints

### Authentication API (`auth_api.php`)
- `POST /auth_api.php` - Login, logout, registration, auth check
- Enhanced security with login attempt tracking
- Session management with timeout handling

### Items API (`items_api.php`)
- `GET /items_api.php` - Retrieve all items with filtering
- `POST /items_api.php` - Add, update, delete, search items
- Complete CRUD operations with audit trail
- Advanced search and filtering capabilities

## 🚀 Performance Optimizations

### Frontend Performance
- Optimized JavaScript with modular architecture
- Debounced search to reduce API calls
- Efficient DOM manipulation
- Ready for CSS/JS minification in production

### Backend Performance
- Database indexing for faster queries
- Prepared statements for query optimization
- Connection pooling ready
- Error logging without performance impact

## 🔮 Future Enhancements

- [ ] Export functionality (CSV, PDF) - Framework ready
- [ ] Email notifications for low stock
- [ ] Barcode scanning integration
- [ ] Multi-location support
- [ ] Advanced reporting with custom filters
- [ ] User profile management
- [ ] Backup and restore functionality
- [ ] API rate limiting implementation
- [ ] Mobile app development
- [ ] Multi-language support framework

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in `config.php`
   - Ensure MySQL service is running
   - Check database permissions
   - The system will auto-create database if it doesn't exist

2. **Session Issues**
   - Verify PHP session configuration
   - Check file permissions for session storage
   - Ensure cookies are enabled
   - Check session timeout settings

3. **Permission Errors**
   - Set appropriate file permissions
   - Ensure web server can write to session directory
   - Check PHP PDO and MySQL extensions

### Error Logging
The system logs errors to PHP error log with detailed information. Check your server's error log for troubleshooting.

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request. The project follows modern development practices with comprehensive documentation.

## 📚 Documentation

- **VERSION_LOG.md** - Detailed version history and feature updates
- **Sprint_1_Task_Schedule_Report.md** - Project development documentation
- **API Documentation** - Available in the respective API files
- **Database Schema** - Documented in schema.sql and config.php

---

**Version 3.0** - Major upgrade with enhanced security, modern UI/UX, and comprehensive analytics. Built with Bootstrap 5.3.0 and modern web standards. 
