$(document).ready(function() {
    const regNumberPattern = /^BCS-\d{2}-\d{4}-\d{4}$/;
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

    // Registration Number validation
    $('#regNumber').on('input', function() {
        const $error = $(this).siblings('.error-message');
        const value = $(this).val().trim();
        
        if (!regNumberPattern.test(value)) {
            $error.text('Invalid format. Use BCS-00-0000-0000').show();
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
        return strength >= 3;
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

    // Load Regions
    $.ajax({
        url: 'api/regions.php',
        method: 'GET',
        success: function(regions) {
            regions.forEach(region => {
                $('#region').append(`<option value="${region.id}">${region.name}</option>`);
            });
        }
    });

    // Load Districts based on Region
    $('#region').change(function() {
        const regionId = $(this).val();
        $.ajax({
            url: `api/districts.php?region_id=${regionId}`,
            method: 'GET',
            success: function(districts) {
                $('#district').empty().append('<option value="">Select District</option>');
                districts.forEach(district => {
                    $('#district').append(`<option value="${district.id}">${district.name}</option>`);
                });
            }
        });
    });

    // Form submission
    $('#registrationForm').on('submit', function(e) {
        e.preventDefault();
        
        if (validateForm()) {
            const formData = new FormData(this);
            
            $.ajax({
                url: 'api/register.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if(response.status === 'success') {
                        alert('Registration successful!');
                        // Force redirect to login page
                        window.location.replace('home.html');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error:', error);
                    alert('Registration failed: ' + error);
                }
            });
        }
    });
    

    function validateForm() {
        let isValid = true;
        
        // Trigger validation for all fields
        isValid = $('#fullName').trigger('input').siblings('.error-message').is(':hidden') &&
                 $('#regNumber').trigger('input').siblings('.error-message').is(':hidden') &&
                 $('#email').trigger('input').siblings('.error-message').is(':hidden') &&
                 $('#password').trigger('input').siblings('.error-message').text().includes('strong') &&
                 $('#confirmPassword').trigger('input').siblings('.error-message').is(':hidden') &&
                 $('#sex').val() !== '' &&
                 $('#region').val() !== '' &&
                 $('#district').val() !== '';
        
        return isValid;
    }
});