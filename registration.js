$(document).ready(function() {
    // Updated pattern for new format S3725.0187.2020
    const regNumberPattern = /^S\d{4}\.\d{4}\.\d{4}$/;
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // Full Name validation
    $('#fullName').on('input', function() {
        const $error = $(this).siblings('.error-message');
        const value = $(this).val().trim();
        
        if (value.length < 3) {
            $error.text('Name must be at least 3 characters long').show();
            return false;
        }
        if (!/^[a-zA-Z\s]+$/.test(value)) {
            $error.text('Name should only contain letters and spaces').show();
            return false;
        }
        $error.hide();
        return true;
    });

    // Registration Number validation - UPDATED PATTERN
    $('#regNumber').on('input', function() {
        const $error = $(this).siblings('.error-message');
        const value = $(this).val().trim();
        
        if (!regNumberPattern.test(value)) {
            $error.text('Invalid format. Use S3725.0187.2020').show();
            return false;
        }
        $error.hide();
        return true;
    });

    // Email validation
    $('#email').on('input', function() {
        const $error = $(this).siblings('.error-message');
        const value = $(this).val().trim();
        
        if (!emailPattern.test(value)) {
            $error.text('Invalid email address').show();
            return false;
        }
        $error.hide();
        return true;
    });

    // Password strength checker
    $('#password').on('input', function() {
        const password = $(this).val();
        const $error = $(this).siblings('.error-message');
        
        if (password.length < 8) {
            $error.text('Password must be at least 8 characters long').show();
            return false;
        }

        let strength = 0;
        if (password.match(/[a-z]+/)) strength += 1;
        if (password.match(/[A-Z]+/)) strength += 1;
        if (password.match(/[0-9]+/)) strength += 1;
        if (password.match(/[!@#$%^&*(),.?":{}|<>]+/)) strength += 1;

        switch(strength) {
            case 1:
                $error.text('Weak password').show();
                break;
            case 2:
                $error.text('Moderate password').show();
                break;
            case 3:
                $error.text('Strong password').show();
                break;
            case 4:
                $error.text('Very strong password').show();
                break;
        }
        return strength >= 2;
    });

    // Confirm Password validation
    $('#confirmPassword').on('input', function() {
        const $error = $(this).siblings('.error-message');
        const confirmPassword = $(this).val();
        const password = $('#password').val();
        
        if (confirmPassword !== password) {
            $error.text('Passwords do not match').show();
            return false;
        }
        $error.hide();
        return true;
    });

    // Form submission
    $('#registrationForm').on('submit', function(e) {
        e.preventDefault();
        
        if (validateForm()) {
            const formData = {
                fullName: $('#fullName').val(),
                regNumber: $('#regNumber').val(),
                sex: $('#sex').val(),
                email: $('#email').val(),
                password: $('#password').val()
            };
            
            $.ajax({
                url: 'api/register.php',
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        alert('Registration successful! Redirecting to login...');
                        window.location.href = 'login.html';
                    } else {
                        alert('Registration failed: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error:', error);
                    console.log('Response:', xhr.responseText);
                    alert('Registration failed. Please try again.');
                }
            });
        }
    });

    function validateForm() {
        let isValid = true;
        
        // Validate full name
        const nameValid = $('#fullName').val().trim().length >= 3 && /^[a-zA-Z\s]+$/.test($('#fullName').val().trim());
        if (!nameValid) {
            $('#fullName').siblings('.error-message').text('Name must be at least 3 characters and contain only letters').show();
            isValid = false;
        }
        
        // Validate registration number
        const regValid = regNumberPattern.test($('#regNumber').val().trim());
        if (!regValid) {
            $('#regNumber').siblings('.error-message').text('Invalid format. Use S3725.0187.2020').show();
            isValid = false;
        }
        
        // Validate email
        const emailValid = emailPattern.test($('#email').val().trim());
        if (!emailValid) {
            $('#email').siblings('.error-message').text('Invalid email address').show();
            isValid = false;
        }
        
        // Validate password
        const passwordValid = $('#password').val().length >= 8;
        if (!passwordValid) {
            $('#password').siblings('.error-message').text('Password must be at least 8 characters').show();
            isValid = false;
        }
        
        // Validate confirm password
        const confirmPasswordValid = $('#confirmPassword').val() === $('#password').val();
        if (!confirmPasswordValid) {
            $('#confirmPassword').siblings('.error-message').text('Passwords do not match').show();
            isValid = false;
        }
        
        // Validate sex selection
        if ($('#sex').val() === '') {
            alert('Please select gender');
            isValid = false;
        }
        
        return isValid;
    }
});
