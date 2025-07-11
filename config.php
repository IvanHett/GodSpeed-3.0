<?php
// Enhanced configuration for Godspeed Inventory Management System

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$dbname = 'inventory_db';
$username = 'root';
$password = '';

// Security settings
define('HASH_COST', 12); // Password hashing cost
define('SESSION_TIMEOUT', 3600); // Session timeout in seconds
define('MAX_LOGIN_ATTEMPTS', 5); // Maximum login attempts
define('LOCKOUT_TIME', 900); // Lockout time in seconds (15 minutes)

// Create database connection with enhanced error handling
try {
    // First try to connect to MySQL without selecting database
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Check if database exists, if not create it
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Now connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Create tables if they don't exist with enhanced structure
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        is_active BOOLEAN DEFAULT TRUE,
        login_attempts INT DEFAULT 0,
        last_login_attempt TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_role (role),
        INDEX idx_is_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT NULL,
        quantity INT NOT NULL DEFAULT 0,
        min_quantity INT DEFAULT 0,
        max_quantity INT NULL,
        category VARCHAR(50) NULL,
        unit VARCHAR(20) DEFAULT 'pcs',
        price DECIMAL(10,2) NULL,
        supplier VARCHAR(100) NULL,
        location VARCHAR(100) NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_name (name),
        INDEX idx_category (category),
        INDEX idx_quantity (quantity),
        INDEX idx_is_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Create audit log table for tracking changes
    $pdo->exec("CREATE TABLE IF NOT EXISTS audit_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        action VARCHAR(50) NOT NULL,
        table_name VARCHAR(50) NOT NULL,
        record_id INT NULL,
        old_values JSON NULL,
        new_values JSON NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_action (action),
        INDEX idx_table_name (table_name),
        INDEX idx_created_at (created_at),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Create sessions table for better session management
    $pdo->exec("CREATE TABLE IF NOT EXISTS sessions (
        id VARCHAR(128) PRIMARY KEY,
        user_id INT NOT NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        payload TEXT NOT NULL,
        last_activity INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_last_activity (last_activity),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please check your configuration.");
}

// Security functions
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function logAuditTrail($userId, $action, $tableName, $recordId = null, $oldValues = null, $newValues = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO audit_log (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $userId,
            $action,
            $tableName,
            $recordId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } catch (Exception $e) {
        error_log("Failed to log audit trail: " . $e->getMessage());
    }
}

function checkLoginAttempts($username) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT login_attempts, last_login_attempt FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && $user['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
            $lastAttempt = strtotime($user['last_login_attempt']);
            if (time() - $lastAttempt < LOCKOUT_TIME) {
                return false; // Account is locked
            } else {
                // Reset login attempts after lockout period
                $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0 WHERE username = ?");
                $stmt->execute([$username]);
            }
        }
        return true;
    } catch (Exception $e) {
        error_log("Failed to check login attempts: " . $e->getMessage());
        return true; // Allow login if check fails
    }
}

function incrementLoginAttempts($username) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET login_attempts = login_attempts + 1, last_login_attempt = NOW() WHERE username = ?");
        $stmt->execute([$username]);
    } catch (Exception $e) {
        error_log("Failed to increment login attempts: " . $e->getMessage());
    }
}

function resetLoginAttempts($username) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0 WHERE username = ?");
        $stmt->execute([$username]);
    } catch (Exception $e) {
        error_log("Failed to reset login attempts: " . $e->getMessage());
    }
}

// Make $pdo available globally
global $pdo;
?> 