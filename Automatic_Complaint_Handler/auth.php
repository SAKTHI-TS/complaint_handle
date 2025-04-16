<?php
require_once 'config.php';
require_once 'includes/db.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Admin login
    public function adminLogin($username, $password) {
        $stmt = $this->db->query(
            "SELECT a.*, d.name as department_name, d.id as department_id 
             FROM admins a 
             JOIN departments d ON a.department_id = d.id 
             WHERE username = ?", 
            [$username]
        );
        
        $admin = $stmt->get_result()->fetch_assoc();
        
        if ($admin) {
            // Default admin password is 12345678
            if ($password === '12345678' || password_verify($password, $admin['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['user_type'] = 'admin';
                $_SESSION['department_id'] = $admin['department_id'];
                $_SESSION['department'] = $admin['department_name'];
                $_SESSION['full_name'] = $admin['full_name'];
                $_SESSION['last_activity'] = time();
                
                $this->trackSession($admin['id'], null, 'admin');
                return ['success' => true];
            }
        }
        return ['error' => 'Invalid username or password'];
    }
    
    // User registration
    public function registerUser($data) {
        $existing = $this->db->query(
            "SELECT id FROM users WHERE email = ?", 
            [$data['email']]
        )->get_result()->fetch_assoc();
        
        if ($existing) {
            return ['error' => 'Email already registered'];
        }
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $this->db->query(
            "INSERT INTO users (first_name, last_name, email, phone, password) 
             VALUES (?, ?, ?, ?, ?)",
            [$data['first_name'], $data['last_name'], $data['email'], $data['phone'], $hashedPassword]
        );
        
        return ['success' => 'Registration successful! You can now login.'];
    }
    
    // User login
    public function userLogin($email, $password) {
        $stmt = $this->db->query(
            "SELECT * FROM users WHERE email = ? AND is_active = 1",
            [$email]
        );
        
        $user = $stmt->get_result()->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true); // Prevent session fixation
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = 'user';
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['last_activity'] = time();
            
            $this->trackSession(null, $user['id'], 'user');
            return ['success' => true];
        }
        return ['error' => 'Invalid email or password'];
    }
    
    // Track session
    private function trackSession($adminId, $userId, $userType) {
        $sessionId = session_id();
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        
        $existing = $this->db->query(
            "SELECT id FROM sessions WHERE session_id = ?", 
            [$sessionId]
        )->get_result()->fetch_assoc();
        
        if ($existing) {
            $this->db->query(
                "UPDATE sessions SET last_activity = NOW() WHERE session_id = ?",
                [$sessionId]
            );
        } else {
            $this->db->query(
                "INSERT INTO sessions (admin_id, user_id, user_type, session_id, ip_address, user_agent) 
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$adminId, $userId, $userType, $sessionId, $ipAddress, $userAgent]
            );
        }
    }
    
    // Check if logged in
    public static function isLoggedIn() {
        return isset($_SESSION['user_type']);
    }
    
    // Check if admin
    public static function isAdmin() {
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
    }
    
    // Check if user
    public static function isUser() {
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'user';
    }
    
    // Get department
    public static function getDepartment() {
        return $_SESSION['department'] ?? null;
    }
    
    // Check session timeout
    public static function checkSessionTimeout() {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
            self::logout();
        }
        $_SESSION['last_activity'] = time();
    }
    
    // Logout
    public static function logout() {
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }
    
    public static function requireAdmin() {
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
            $_SESSION['error_message'] = "Access denied. Admin privileges required.";
            header("Location: ../login.php");
            exit();
        }
    }
    
    public static function requireUser() {
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'user') {
            $_SESSION['error_message'] = "Access denied. User login required.";
            header("Location: ../login.php");
            exit();
        }
    }
}
?>