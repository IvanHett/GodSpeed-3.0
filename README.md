# Godspeed Inventory Management System 2.0

A modern, secure, and feature-rich inventory management system built with PHP, MySQL, and Bootstrap 5.

## 🚀 Features

### Core Features
- **User Authentication & Authorization**
  - Secure login/logout system
  - User registration
  - Role-based access control (Admin/User)
  - Session management with timeout
  - Login attempt tracking and account lockout

- **Inventory Management**
  - Add, edit, delete inventory items
  - Real-time stock level monitoring
  - Low stock warnings
  - Category-based organization
  - Supplier and location tracking
  - Price and unit management

- **Dashboard & Analytics**
  - Real-time inventory summary
  - Visual charts and graphs
  - Stock level distribution
  - Top items by quantity
  - Low stock and out-of-stock reports

- **Security Features**
  - Password hashing with bcrypt
  - CSRF protection
  - SQL injection prevention
  - XSS protection
  - Input sanitization
  - Audit trail logging
  - Session security headers

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **UI Framework**: Bootstrap 5.3.0
- **Icons**: Font Awesome 6.4.0
- **Charts**: Chart.js

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

## 🚀 Installation

1. **Clone or download the project**
   ```bash
   git clone <repository-url>
   cd godspeed-inventory-management
   ```

2. **Set up the database**
   - Create a MySQL database
   - Update database credentials in `config.php`
   - The system will automatically create tables on first run

3. **Configure web server**
   - Point your web server to the project directory
   - Ensure PHP has write permissions for session handling

4. **Access the application**
   - Navigate to `http://your-domain/login.html`
   - Create your first account using the signup page

## 📁 File Structure

```
godspeed-inventory-management/
├── index.html              # Main dashboard
├── items.html              # Items management page
├── reports.html            # Reports and analytics
├── login.html              # Login page
├── signup.html             # Registration page
├── app.js                  # Main JavaScript application
├── auth.js                 # Authentication JavaScript
├── styles.css              # Custom CSS styles
├── config.php              # Database and security configuration
├── auth_api.php            # Authentication API endpoints
├── items_api.php           # Items management API endpoints
├── signup.php              # User registration handler
├── logout.php              # Logout handler
├── schema.sql              # Database schema
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
define('HASH_COST', 12);           // Password hashing cost
define('SESSION_TIMEOUT', 3600);    // Session timeout (1 hour)
define('MAX_LOGIN_ATTEMPTS', 5);    // Max failed login attempts
define('LOCKOUT_TIME', 900);        // Account lockout time (15 min)
```

## 🎯 Usage

### Dashboard
- View real-time inventory summary
- Monitor stock levels and alerts
- Access quick actions and reports

### Items Management
- Add new inventory items with detailed information
- Edit existing items
- Delete items (soft delete)
- Search and filter items
- Monitor stock status

### Reports
- View comprehensive inventory reports
- Analyze stock distribution
- Identify low stock items
- Export data (coming soon)

## 🔒 Security Features

### Authentication Security
- Bcrypt password hashing
- Account lockout after failed attempts
- Session timeout management
- Secure session configuration

### Data Protection
- Prepared statements for all database queries
- Input sanitization and validation
- CSRF token protection
- XSS prevention headers

### Audit Trail
- Complete logging of all user actions
- Track changes to inventory items
- Monitor login/logout events
- IP address and user agent tracking

## 📊 Database Schema

### Users Table
- User authentication and profile data
- Role-based access control
- Login attempt tracking
- Account status management

### Items Table
- Complete inventory item information
- Stock level tracking
- Category and supplier data
- Price and location details

### Audit Log Table
- Complete audit trail of all actions
- Change tracking for compliance
- User activity monitoring

### Sessions Table
- Enhanced session management
- Security tracking

## 🎨 UI/UX Features

### Modern Design
- Bootstrap 5 responsive design
- Font Awesome icons
- Clean and intuitive interface
- Mobile-friendly layout

### Interactive Elements
- Real-time search functionality
- Dynamic charts and graphs
- Smooth animations and transitions
- Loading indicators

### User Experience
- Intuitive navigation
- Clear status indicators
- Helpful error messages
- Responsive feedback

## 🔧 API Endpoints

### Authentication API (`auth_api.php`)
- `POST /auth_api.php` - Login, logout, registration, auth check

### Items API (`items_api.php`)
- `GET /items_api.php` - Retrieve all items
- `POST /items_api.php` - Add, update, delete, search items

## 🚀 Future Enhancements

- [ ] Export functionality (CSV, PDF)
- [ ] Email notifications for low stock
- [ ] Barcode scanning integration
- [ ] Multi-location support
- [ ] Advanced reporting
- [ ] User profile management
- [ ] Backup and restore functionality
- [ ] API rate limiting
- [ ] Mobile app development

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in `config.php`
   - Ensure MySQL service is running
   - Check database permissions

2. **Session Issues**
   - Verify PHP session configuration
   - Check file permissions for session storage
   - Ensure cookies are enabled

3. **Permission Errors**
   - Set appropriate file permissions
   - Ensure web server can write to session directory

### Error Logging
The system logs errors to PHP error log. Check your server's error log for detailed information.

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📞 Support

For support and questions:
- Create an issue in the repository
- Check the troubleshooting section
- Review the code comments for implementation details

---

**Godspeed Inventory Management System 2.0** - Modern, Secure, Efficient 