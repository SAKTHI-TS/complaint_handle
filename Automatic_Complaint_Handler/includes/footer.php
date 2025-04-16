</main>

<footer class="main-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>Complaint Management System for efficient grievance resolution.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <?php if (Auth::isLoggedIn()): ?>
                        <li><a href="<?php echo Auth::isAdmin() ? '../admin/dashboard.php' : '../user/dashboard.php'; ?>">Dashboard</a></li>
                    <?php else: ?>
                        <li><a href="../login.php">Login</a></li>
                        <li><a href="../register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p><i class="fas fa-envelope"></i> support@complaintsystem.com</p>
                <p><i class="fas fa-phone"></i> +1 (123) 456-7890</p>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; <?php echo date('Y'); ?> Complaint System. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="../assets/js/script.js"></script>
</body>
</html>