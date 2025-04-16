<?php
require_once 'config.php';
require_once 'auth.php';

$auth = new Auth();

// Redirect if already logged in
if ($auth::isLoggedIn()) {
    header("Location: " . ($auth::isAdmin() ? "admin/dashboard.php" : "user/dashboard.php"));
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'password' => $_POST['password'],
        'confirm_password' => $_POST['confirm_password']
    ];
    
    // Validate inputs
    if ($data['password'] !== $data['confirm_password']) {
        $error = "Passwords do not match";
    } else {
        $result = $auth->registerUser($data);
        
        if (isset($result['error'])) {
            $error = $result['error'];
        } else {
            $success = $result['success'];
        }
    }
}

$pageTitle = "Register";

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
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: gradientBG 15s ease infinite;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .register-container {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            width: 450px;
            max-width: 90%;
            padding: 40px;
            text-align: center;
            animation: fadeInUp 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
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
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: var(--accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(247, 37, 133, 0.3);
        }

        .btn {
            padding: 15px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s;
        }

        .btn-primary {
            width: 100%;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border: none;
            color: white;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
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

        .alert-success {
            background: rgba(46, 213, 115, 0.9);
        }

        @keyframes fadeInUp {
            from { 
                opacity: 0;
                transform: translateY(30px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <div class="register-header">
                <h1><i class="fas fa-user-plus"></i> Register</h1>
                <p>Create a new user account</p>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                    <a href="login.php" class="btn btn-link">Already have an account? Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
