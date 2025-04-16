<?php
require_once 'config.php';

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    require_once 'backend.php';
    $backend = new AuthBackend();
    $result = $backend->forgotPassword($_POST['email']);
    
    if (isset($result['error'])) {
        $error = $result['error'];
    } else {
        $success = $result['success'];
        // In production, you would send an email with the token
        // $token = $result['token'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Recovery | Your App</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Same CSS variables and base styles as login.html */
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

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .floating-icons {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .floating-icon {
            position: absolute;
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.3);
            animation: floatIcon linear infinite;
        }

        @keyframes floatIcon {
            0% { 
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% { opacity: 0.3; }
            90% { opacity: 0.3; }
            100% { 
                transform: translateY(-10vh) rotate(360deg);
                opacity: 0;
            }
        }

        .auth-container {
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
            z-index: 1;
            animation: fadeIn 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
        }

        @keyframes fadeIn {
            from { 
                opacity: 0;
                transform: scale(0.95);
            }
            to { 
                opacity: 1;
                transform: scale(1);
            }
        }

        .auth-header {
            margin-bottom: 30px;
            position: relative;
        }

        .auth-header i {
            font-size: 3.5rem;
            color: var(--light);
            margin-bottom: 20px;
            display: inline-block;
            animation: iconPulse 2s infinite;
        }

        @keyframes iconPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .auth-header h1 {
            color: var(--light);
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .auth-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .input-group {
            margin-bottom: 25px;
            text-align: left;
            position: relative;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--light);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .input-group input {
            width: 100%;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            font-size: 1rem;
            color: var(--light);
            transition: all 0.3s ease;
        }

        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .input-group input:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: var(--accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(247, 37, 133, 0.3);
        }

        .input-group i {
            position: absolute;
            right: 20px;
            top: 42px;
            color: rgba(255, 255, 255, 0.5);
            transition: all 0.3s;
        }

        .input-group input:focus + i {
            color: var(--accent);
            transform: scale(1.2);
        }

        .auth-button {
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
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .auth-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }

        .auth-button:active {
            transform: translateY(1px);
        }

        .auth-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .auth-button:hover::before {
            left: 100%;
        }

        .auth-footer {
            margin-top: 20px;
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .auth-footer a {
            color: var(--light);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            position: relative;
        }

        .auth-footer a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent);
            transition: width 0.3s;
        }

        .auth-footer a:hover {
            color: var(--accent);
        }

        .auth-footer a:hover::after {
            width: 100%;
        }

        .success-message {
            display: none;
            animation: fadeIn 0.5s ease-out;
        }

        .success-message i {
            font-size: 4rem;
            color: #2ecc71;
            margin-bottom: 20px;
            display: inline-block;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .auth-container {
                padding: 30px 20px;
            }
            
            .auth-header h1 {
                font-size: 1.8rem;
            }
            
            .auth-header i {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="floating-icons" id="floating-icons"></div>

    <div class="auth-container">
        <div class="auth-form" id="auth-form">
            <div class="auth-header">
                <i class="fas fa-key"></i>
                <h1>Forgot Password?</h1>
                <p>Enter your email address and we'll send you a link to reset your password</p>
            </div>

            <form id="password-reset-form">
                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" placeholder="your@email.com" required>
                    <i class="fas fa-envelope"></i>
                </div>

                <button type="submit" class="auth-button">Send Reset Link</button>
            </form>

            <div class="auth-footer">
                Remember your password? <a href="login.php">Back to login</a>
            </div>
        </div>

        <div class="success-message" id="success-message">
            <div class="auth-header">
                <i class="fas fa-check-circle"></i>
                <h1>Email Sent!</h1>
                <p>We've sent a password reset link to your email address. Please check your inbox.</p>
            </div>

            <div class="auth-footer">
                <a href="login.php">Back to login page</a>
            </div>
        </div>
    </div>

    <script>
        // Create floating icons
        const iconsContainer = document.getElementById('floating-icons');
        const icons = ['fa-envelope', 'fa-key', 'fa-lock', 'fa-shield-alt', 'fa-user-shield'];
        const iconCount = 12;
        
        for (let i = 0; i < iconCount; i++) {
            const icon = document.createElement('i');
            icon.classList.add('floating-icon', 'fas', icons[Math.floor(Math.random() * icons.length)]);
            
            // Random size between 20px and 40px
            const size = Math.random() * 20 + 20;
            icon.style.fontSize = `${size}px`;
            
            // Random position
            icon.style.left = `${Math.random() * 100}%`;
            
            // Random animation duration between 15s and 30s
            const duration = Math.random() * 15 + 15;
            icon.style.animationDuration = `${duration}s`;
            
            // Random delay
            icon.style.animationDelay = `${Math.random() * 5}s`;
            
            iconsContainer.appendChild(icon);
        }

        // Form submission handling
        const recoveryForm = document.getElementById('password-reset-form');
        const recoverySection = document.getElementById('auth-form');
        const successSection = document.getElementById('success-message');
        
        recoveryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simulate form submission
            setTimeout(() => {
                recoverySection.style.display = 'none';
                successSection.style.display = 'block';
            }, 1000);
        });
    </script>
</body>
</html>