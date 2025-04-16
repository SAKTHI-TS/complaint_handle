</main>

<footer class="main-footer">
    <div class="footer-content">
        <div class="footer-grid">
            <div class="footer-section">
                <h3><i class="fas fa-landmark"></i> About Portal</h3>
                <p>The Public Grievance Portal is an initiative under Digital India to provide citizens with a platform to file complaints and track their resolution status. Available 24x7 for public service.</p>
            </div>
            <div class="footer-section">
                <h3><i class="fas fa-file-alt"></i> Important Documents</h3>
                <ul class="footer-links">
                    <li><a href="../rti.php"><i class="fas fa-arrow-right"></i> RTI Information</a></li>
                    <li><a href="../policies.php"><i class="fas fa-arrow-right"></i> Policies & Guidelines</a></li>
                    <li><a href="../citizen-charter.php"><i class="fas fa-arrow-right"></i> Citizen's Charter</a></li>
                    <li><a href="../notifications.php"><i class="fas fa-arrow-right"></i> Notifications</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3><i class="fas fa-shield-alt"></i> Legal</h3>
                <ul class="footer-links">
                    <li><a href="../privacy.php"><i class="fas fa-lock"></i> Privacy Policy</a></li>
                    <li><a href="../terms.php"><i class="fas fa-gavel"></i> Terms of Service</a></li>
                    <li><a href="../disclaimer.php"><i class="fas fa-exclamation-circle"></i> Disclaimer</a></li>
                    <li><a href="../accessibility.php"><i class="fas fa-universal-access"></i> Accessibility</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="govt-logos">
                <div class="logo-item">
                    <i class="fas fa-om"></i>
                    <span>सत्यमेव जयते</span>
                </div>
                <div class="logo-item">
                    <i class="fas fa-landmark"></i>
                    <span>Government of India</span>
                </div>
                <div class="logo-item">
                    <i class="fas fa-digital-tachograph"></i>
                    <span>Digital India</span>
                </div>
                <div class="logo-item">
                    <i class="fas fa-certificate"></i>
                    <span>E-Governance</span>
                </div>
            </div>
            <div class="copyright">
                <p>Website Content Managed by Department of Administrative Reforms & Public Grievances</p>
                <p>&copy; <?php echo date('Y'); ?> - Ministry of Personnel, Public Grievances & Pensions</p>
                
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

    .govt-logos {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 30px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .logo-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 15px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        min-width: 120px;
        transition: all 0.3s ease;
    }

    .logo-item:hover {
        transform: translateY(-5px);
        background: rgba(255, 255, 255, 0.15);
    }

    .logo-item i {
        font-size: 2rem;
        margin-bottom: 8px;
        color: #ffd700;
    }

    .logo-item span {
        font-size: 0.8rem;
        text-align: center;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.9);
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

        .govt-logos {
            gap: 15px;
        }
        
        .logo-item {
            min-width: 100px;
            padding: 10px;
        }
    }
</style>

<script src="../assets/js/script.js"></script>
</body>
</html>