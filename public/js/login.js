// Login Page Management
document.addEventListener('DOMContentLoaded', function() {
    // Tilt effect untuk logo
    if (typeof $ !== 'undefined' && $('.js-tilt').length) {
        $('.js-tilt').tilt({
            scale: 1.1
        });
    }
});

// Function untuk toggle password visibility di login
function togglePasswordLogin() {
    const input = document.getElementById('password');
    const eyeIcon = document.getElementById('password-eye-login');
    
    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}
