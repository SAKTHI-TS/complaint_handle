<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'complaint_system');

try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($db->connect_error) {
        throw new Exception('Connection failed: ' . $db->connect_error);
    }
    
    $db->set_charset('utf8mb4');
} catch (Exception $e) {
    die('Database connection failed. Please make sure the database is properly set up.');
}

// Session configuration
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('BASE_URL', 'http://localhost/complaint-system');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start secure session
function secureSessionStart() {
    $sessionName = 'secure_session';
    $secure = true;
    $httponly = true;
    
    ini_set('session.use_only_cookies', 1);
    
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params(
        $cookieParams["lifetime"],
        $cookieParams["path"],
        $cookieParams["domain"],
        $secure,
        $httponly
    );
    
    session_name($sessionName);
    session_start();
    session_regenerate_id(true);
}

secureSessionStart();
?>