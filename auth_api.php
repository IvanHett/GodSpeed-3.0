<?php
// Enhanced Authentication API for Godspeed Inventory Management System

// Include config file
require_once __DIR__ . '/config.php';

// Start session with enhanced security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);
session_start();

// Set content type
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Get global PDO instance
global $pdo;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['action'])) {
        switch ($data['action']) {
            case 'login':
                handleLogin($data);
                break;
                
            case 'logout':
                handleLogout();
                break;
                
            case 'check':
                handleAuthCheck();
                break;
                
            case 'register':
                handleRegister($data);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                break;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No action specified']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

function handleLogin($data) {
    global $pdo;
    
    try {
        $username = sanitizeInput($data['username'] ?? '');
        $password = $data['password'] ?? '';
        
        // Validate input
        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Username and password are required']);
            return;
        }
        
        // Check if account is locked
        if (!checkLoginAttempts($username)) {
            echo json_encode(['success' => false, 'message' => 'Account is temporarily locked due to too many failed attempts. Please try again later.']);
            return;
        }
        
        // Get user data
        $stmt = $pdo->prepare("SELECT id, username, password, role, is_active FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            incrementLoginAttempts($username);
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            return;
        }
        
        // Check if user is active
        if (!$user['is_active']) {
            echo json_encode(['success' => false, 'message' => 'Account is deactivated. Please contact administrator.']);
            return;
        }
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Reset login attempts on successful login
            resetLoginAttempts($username);
            
            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['isLoggedIn'] = true;
            $_SESSION['login_time'] = time();
            
            // Update last login time
            $stmt = $pdo->prepare("UPDATE users SET last_login_attempt = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            // Log successful login
            logAuditTrail($user['id'], 'LOGIN', 'users', $user['id']);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            incrementLoginAttempts($username);
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }
        
    } catch(PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    } catch(Exception $e) {
        error_log("Login error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred during login']);
    }
}

function handleLogout() {
    try {
        // Log logout if user was logged in
        if (isset($_SESSION['user_id'])) {
            logAuditTrail($_SESSION['user_id'], 'LOGOUT', 'users', $_SESSION['user_id']);
        }
        
        // Destroy session
        session_destroy();
        
        echo json_encode(['success' => true, 'message' => 'Logout successful']);
    } catch(Exception $e) {
        error_log("Logout error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error during logout']);
    }
}

function handleAuthCheck() {
    try {
        $isLoggedIn = isset($_SESSION['isLoggedIn']) && $_SESSION['isLoggedIn'] === true;
        
        if ($isLoggedIn) {
            // Check session timeout
            if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > SESSION_TIMEOUT) {
                session_destroy();
                echo json_encode(['loggedIn' => false, 'message' => 'Session expired']);
                return;
            }
            
            // Update session time
            $_SESSION['login_time'] = time();
            
            echo json_encode([
                'loggedIn' => true,
                'user' => [
                    'id' => $_SESSION['user_id'] ?? null,
                    'username' => $_SESSION['username'] ?? null,
                    'role' => $_SESSION['role'] ?? 'user'
                ]
            ]);
        } else {
            echo json_encode(['loggedIn' => false]);
        }
    } catch(Exception $e) {
        error_log("Auth check error: " . $e->getMessage());
        echo json_encode(['loggedIn' => false, 'message' => 'Error checking authentication']);
    }
}

function handleRegister($data) {
    global $pdo;
    
    try {
        $username = sanitizeInput($data['username'] ?? '');
        $password = $data['password'] ?? '';
        $email = sanitizeInput($data['email'] ?? '');
        
        // Validate input
        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Username and password are required']);
            return;
        }
        
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
            return;
        }
        
        if (!empty($email) && !validateEmail($email)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            return;
        }
        
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Username already exists']);
            return;
        }
        
        // Check if email already exists (if provided)
        if (!empty($email)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Email already exists']);
                return;
            }
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
        
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$username, $hashedPassword, $email]);
        
        $userId = $pdo->lastInsertId();
        
        // Log user creation
        logAuditTrail($userId, 'CREATE', 'users', $userId, null, [
            'username' => $username,
            'email' => $email,
            'role' => 'user'
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Account created successfully']);
        
    } catch(PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    } catch(Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred during registration']);
    }
}
?> 