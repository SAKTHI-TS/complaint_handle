// Handle form submissions with AJAX
document.addEventListener('DOMContentLoaded', function() {
    // Floating elements creation (same as before)
    initFloatingElements();
    
    // Form submission handlers
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm('login', this);
        });
    }
    
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Client-side validation
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                alert('Passwords do not match');
                return;
            }
            
            submitForm('register', this);
        });
        
        // Password strength meter
        document.getElementById('password').addEventListener('input', function() {
            updatePasswordStrength(this.value);
        });
    }
    
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm('forgotPassword', this);
        });
    }
    
    function submitForm(action, form) {
        const formData = new FormData(form);
        formData.append('action', action);
        
        fetch('backend.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                showError(data.error);
            } else if (data.success) {
                if (action === 'login') {
                    window.location.href = 'dashboard.php';
                } else {
                    showSuccess(data.success);
                    if (action === 'forgotPassword') {
                        // In production, this would be handled via email
                        console.log('Reset token:', data.token);
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('An error occurred. Please try again.');
        });
    }
    
    function showError(message) {
        // Find or create error message element
        let errorElement = document.querySelector('.error-message');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'error-message';
            const header = document.querySelector('.auth-header');
            if (header) {
                header.appendChild(errorElement);
            }
        }
        errorElement.textContent = message;
    }
    
    function showSuccess(message) {
        // Find or create success message element
        let successElement = document.querySelector('.success-message');
        if (!successElement) {
            successElement = document.createElement('div');
            successElement.className = 'success-message';
            const header = document.querySelector('.auth-header');
            if (header) {
                header.appendChild(successElement);
            }
        }
        successElement.textContent = message;
    }
    
    function updatePasswordStrength(password) {
        const meter = document.getElementById('strength-meter');
        if (!meter) return;
        
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
        meter.style.width = `${width}%`;
        
        // Update color
        if (strength <= 1) {
            meter.style.background = '#ff4757'; // Red
        } else if (strength <= 3) {
            meter.style.background = '#ffa502'; // Orange
        } else {
            meter.style.background = '#2ed573'; // Green
        }
    }
    
    function initFloatingElements() {
        // Implementation from previous examples
        // ...
    }
});