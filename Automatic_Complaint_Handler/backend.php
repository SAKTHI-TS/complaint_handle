<?php
require_once 'config.php';

class AuthBackend {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    // User login
    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                return ['success' => true];
            }
        }
        return ['error' => 'Invalid email or password'];
    }
    
    // User registration
    public function register($firstName, $lastName, $email, $password) {
        // Check if email exists
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            return ['error' => 'Email already registered'];
        }
        
        // Hash password and insert user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $firstName, $lastName, $email, $hashedPassword);
        
        if ($stmt->execute()) {
            return ['success' => 'Registration successful!'];
        }
        return ['error' => 'Registration failed'];
    }
    
    // Password reset request
    public function forgotPassword($email) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $token = bin2hex(random_bytes(16));
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
            
            $stmt = $this->conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user['id'], $token, $expires);
            
            if ($stmt->execute()) {
                return [
                    'success' => 'Password reset link sent',
                    'token' => $token // In production, send this via email
                ];
            }
        }
        return ['error' => 'No account found with that email'];
    }
    
    public function __destruct() {
        $this->conn->close();
    }
}

// Handle AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $backend = new AuthBackend();
    $response = [];
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'login':
                $response = $backend->login($_POST['email'], $_POST['password']);
                break;
                
            case 'register':
                $response = $backend->register(
                    $_POST['firstName'],
                    $_POST['lastName'],
                    $_POST['email'],
                    $_POST['password']
                );
                break;
                
            case 'forgotPassword':
                $response = $backend->forgotPassword($_POST['email']);
                break;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>