<?php
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/includes/db.php';
require_once dirname(__FILE__) . '/auth.php';

$auth = new Auth();

// Redirect if already logged in
if ($auth::isLoggedIn()) {
    header("Location: " . ($auth::isAdmin() ? "admin/dashboard.php" : "user/dashboard.php"));
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Determine login type and attempt login
    if (strpos($username, '@') !== false) {
        // User login attempt
        $result = $auth->userLogin($username, $password);
        if (isset($result['success'])) {
            header("Location: user/dashboard.php");
            exit();
        }
    } else {
        // Admin login attempt
        $result = $auth->adminLogin($username, $password);
        if (isset($result['success'])) {
            header("Location: admin/dashboard.php");
            exit();
        }
    }
    
    $error = $result['error'] ?? 'Login failed. Please try again.';
}

$pageTitle = "Login";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | Complaint Handler</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
            --accent: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', system-ui, -apple-system, sans-serif;
        }

        body {
            background: linear-gradient(-45deg, #3a0ca3, #4361ee, #4cc9f0, #f72585);
            background-size: 400% 400%;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: gradientBG 15s ease infinite;
            overflow: hidden;
        }

        .home-btn {
            position: absolute;
            left: 20px;
            top: 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 8px 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            font-size: 0.9rem;
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .home-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .login-container {
            position: relative;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            width: 400px;
            max-width: 90%;
            padding: 40px;
            text-align: center;
            z-index: 1;
            animation: fadeIn 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
        }

        .login-header {
            margin-bottom: 30px;
            padding-top: 20px;
        }

        .login-header i {
            font-size: 3rem;
            color: var(--light);
            margin-bottom: 20px;
            animation: iconPulse 2s infinite;
        }

        .login-header h1 {
            color: var(--light);
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .login-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--light);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            font-size: 1rem;
            color: var(--light);
            transition: all 0.3s;
        }

        .form-group input:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: var(--accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(247, 37, 133, 0.3);
        }

        .form-group i {
            position: absolute;
            right: 20px;
            top: 42px;
            color: rgba(255, 255, 255, 0.5);
            transition: all 0.3s;
        }

        .btn-primary {
            width: 100%;
            padding: 15px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
            margin-bottom: 20px;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }

        .btn-link {
            color: var(--light);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-link:hover {
            color: var(--accent);
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            color: white;
        }

        .alert-danger {
            background: rgba(255, 71, 87, 0.9);
        }

        .login-info {
            margin-top: 20px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes iconPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .login-header h1 {
                font-size: 1.8rem;
            }
            
            .login-header i {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="home-btn">
        <i class="fas fa-home"></i> Home
    </a>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1><i class="fas fa-sign-in-alt"></i> Login</h1>
                <p>Enter your credentials to access the system</p>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username/Email</label>
                    <input type="text" id="username" name="username" required>
                    <i class="fas fa-user"></i>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <i class="fas fa-lock"></i>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                    <a href="register.php" class="btn btn-link">Register as User</a>
                </div>
                
                <div class="login-info">
                    <p><strong>User Login:</strong> Use your registered email address</p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>