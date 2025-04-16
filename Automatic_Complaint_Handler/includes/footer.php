</main>

<footer class="main-footer">
    <div class="footer-waves">
        
    </div>
    <div class="footer-content">
        <div class="footer-grid">
            <div class="footer-section">
                <h3><i class="fas fa-info-circle"></i> About Us</h3>
                <p>Modern complaint management system designed for efficient and transparent grievance resolution.</p>
            </div>
            <div class="footer-section">
                <h3><i class="fas fa-link"></i> Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="../index.php"><i class="fas fa-home"></i> Home</a></li>
                    <?php if (Auth::isLoggedIn()): ?>
                        <li><a href="<?php echo Auth::isAdmin() ? '../admin/dashboard.php' : '../user/dashboard.php'; ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a></li>
                    <?php else: ?>
                        <li><a href="../login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                        <li><a href="../register.php"><i class="fas fa-user-plus"></i> Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="footer-section">
                <h3><i class="fas fa-phone-alt"></i> Contact</h3>
                <ul class="contact-info">
                    <li><i class="fas fa-envelope"></i> support@complaintsystem.com</li>
                    <li><i class="fas fa-phone"></i> +1 (123) 456-7890</li>
                    <li><i class="fas fa-map-marker-alt"></i> 123 Main St, City, Country</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="social-links">
                <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> Complaint System. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<style>
    .main-footer {
        position: relative;
        margin-top: auto;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        color: #fff;
        padding: 40px 20px;
    }

    .footer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .footer-section h3 {
        color: rgba(255, 255, 255, 0.95);
        margin-bottom: 20px;
        font-size: 1.2rem;
        font-weight: 600;
    }

    .footer-links, .contact-info {
        list-style: none;
        padding: 0;
    }

    .footer-links li a, .contact-info li {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    .footer-links li a:hover {
        color: #fff;
        transform: translateX(5px);
    }

    .footer-bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        margin-top: 30px;
        padding-top: 20px;
        text-align: center;
    }

    .social-link {
        background: rgba(255, 255, 255, 0.1);
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
    }

    .social-link:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-3px);
    }

    .copyright {
        color: rgba(255, 255, 255, 0.7);
    }

    @media (max-width: 768px) {
        .footer-grid {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .footer-links li a, .contact-info li {
            justify-content: center;
        }

        .social-links {
            padding: 10px 0;
        }
    }
</style>

<script src="../assets/js/script.js"></script>
</body>
</html>