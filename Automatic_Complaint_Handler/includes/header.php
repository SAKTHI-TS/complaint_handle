<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Complaint System'; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .main-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 30px;
        }

        body {
            background: linear-gradient(-45deg, #3a0ca3, #4361ee, #4cc9f0, #f72585);
            background-size: 400% 400%;
            min-height: 100vh;
            animation: gradientBG 15s ease infinite;
            color: #fff;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo a {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo i {
            font-size: 2rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .main-nav {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 20px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
        }

        .user-greeting {
            color: #fff;
            font-weight: 500;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 20px;
            border-radius: 20px;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .auth-links {
            display: flex;
            gap: 10px;
        }

        .auth-links .btn {
            padding: 8px 20px;
            border-radius: 20px;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .auth-links .btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .btn-outline {
            border: 2px solid #fff;
            color: #fff;
        }

        .btn-primary {
            background: #ffd700;
            color: #1e3c72;
        }
    </style>
</head>
<body>
    <div class="top-bar" style="background: rgba(255, 255, 255, 0.1); padding: 8px 0; font-size: 0.9rem;">
        <div class="header-container">
            <div>
                <span><i class="fas fa-phone-alt"></i> Toll Free: 1800-XXX-XXXX</span>
                <span style="margin: 0 15px">|</span>
                <span><i class="fas fa-envelope"></i> support@complaints.gov.in</span>
            </div>
            <div>
                <select style="background: transparent; border: 1px solid rgba(255, 255, 255, 0.3); color: white;">
                    <option value="en">English</option>
                    
                </select>
                <a href="#" style="color: white; margin-left: 15px;"><i class="fas fa-accessibility"></i> Screen Reader</a>
            </div>
        </div>
    </div>
    <header class="main-header">
        <div class="header-container">
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>">
                    <i class="fas fa-landmark"></i>
                    <div style="display: flex; flex-direction: column;">
                        <span>Complaint Management Portal</span>
                        <small style="font-size: 0.8rem; opacity: 0.9;">Government of India</small>
                    </div>
                </a>
            </div>
            <nav class="main-nav">
                <div style="display: flex; gap: 20px; margin-right: 20px;">
                    <a href="../index.php" class="btn btn-outline">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="../faq.php" class="btn btn-outline">
                        <i class="fas fa-question-circle"></i> FAQ
                    </a>
                </div>
                <?php if (Auth::isLoggedIn()): ?>
                    <div class="user-menu">
                        <span class="user-greeting">
                            <i class="fas fa-user-circle"></i> 
                            <?php 
                                $fullName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User';
                                echo htmlspecialchars($fullName); 
                            ?>
                        </span>
                        <a href="../logout.php" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                <?php else: ?>
                    <div class="auth-links">
                        <a href="../login.php" class="btn btn-outline">Login</a>
                        <a href="../register.php" class="btn btn-primary">Register</a>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container">