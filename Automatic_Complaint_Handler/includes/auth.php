<?php
require_once dirname(__FILE__) . '/db.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_type']);
    }

    public static function isAdmin() {
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
    }

    public static function checkSessionTimeout() {
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
                session_unset();
                session_destroy();
                header("Location: ../login.php?timeout=1");
                exit();
            }
        }
        $_SESSION['last_activity'] = time();
    }

    public function userLogin($email, $password) {
        $stmt = $this->db->query(
            "SELECT id, password FROM users WHERE email = ?",
            [$email]
        );
        $user = $this->db->fetchAssoc($stmt);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['is_admin'] = false;
            $_SESSION['last_activity'] = time();
            return ['success' => true];
        }
        return ['error' => 'Invalid email or password'];
    }

    public static function getDepartment() {
        return isset($_SESSION['admin_department']) ? $_SESSION['admin_department'] : null;
    }

    public function adminLogin($username, $password) {
        $stmt = $this->db->query(
            "SELECT a.*, d.name as department_name 
             FROM admins a 
             LEFT JOIN departments d ON a.department_id = d.id 
             WHERE a.username = ?",
            [$username]
        );
        $admin = $this->db->fetchAssoc($stmt);
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['user_type'] = 'admin';
            $_SESSION['admin_department'] = $admin['department_id'];
            $_SESSION['department_name'] = $admin['department_name'];
            $_SESSION['full_name'] = $admin['full_name'];
            $_SESSION['last_activity'] = time();
            return ['success' => true, 'admin' => $admin];
        }
        return ['error' => 'Invalid username or password'];
    }

    public static function requireUser() {
        if (!self::isLoggedIn() || self::isAdmin()) {
            header("Location: ../login.php");
            exit();
        }
    }

    public static function requireAdmin() {
        if (!self::isLoggedIn() || !self::isAdmin()) {
            header("Location: ../login.php");
            exit();
        }
        self::checkSessionTimeout();
    }
}
