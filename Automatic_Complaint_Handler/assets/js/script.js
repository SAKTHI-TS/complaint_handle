document.addEventListener('DOMContentLoaded', function() {
    // Password strength indicator
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strengthMeter = document.getElementById('strength-meter');
            
            if (!strengthMeter) return;
            
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 1;
            if (password.length >= 12) strength += 1;
            
            // Character variety checks
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Update meter
            const width = (strength / 5) * 100;
            strengthMeter.style.width = `${width}%`;
            
            // Update color
            if (strength <= 1) {
                strengthMeter.style.background = '#ff4757'; // Red
            } else if (strength <= 3) {
                strengthMeter.style.background = '#ffa502'; // Orange
            } else {
                strengthMeter.style.background = '#2ed573'; // Green
            }
        });
    }
    
    // Form validation for registration
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    }
    
    // Modal functionality for admin complaints
    const updateBtns = document.querySelectorAll('.update-status-btn');
    const modal = document.getElementById('statusModal');
    
    if (updateBtns.length > 0 && modal) {
        const closeBtn = document.querySelector('.close-modal');
        const statusForm = document.getElementById('statusForm');
        
        updateBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const complaintId = this.getAttribute('data-id');
                statusForm.action = `complaints.php?action=update_status&id=${complaintId}`;
                modal.style.display = 'flex';
            });
        });
        
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });
});