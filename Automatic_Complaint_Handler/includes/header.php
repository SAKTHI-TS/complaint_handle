<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Complaint System'; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>">
                    <i class="fas fa-gavel"></i> Complaint System
                </a>
            </div>
            <nav class="main-nav">
                <?php if (Auth::isLoggedIn()): ?>
                    <div class="user-menu">
                        <span class="user-greeting">
                            <i class="fas fa-user"></i> 
                            <?php 
                                $fullName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User';
                                echo htmlspecialchars($fullName); 
                            ?>
                        </span>
                        <a href="../logout.php" class="logout-btn btn btn-outline-light">
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