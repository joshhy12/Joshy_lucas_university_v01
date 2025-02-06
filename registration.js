$(document).ready(function() {
    // Registration Number Validation
    function validateRegNumber(regNumber) {
        const pattern = /^BCS-\d{2}-\d{4}-\d{4}$/;
        return pattern.test(regNumber);
    }

    // Email Validation
    function validateEmail(email) {
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return pattern.test(email);
    }

    // Password Validation
    function validatePassword(password) {
        return password.length >= 8 && 
               /[A-Z]/.test(password) && 
               /[a-z]/.test(password) && 
               /[0-9]/.test(password) && 
               /[^A-Za-z0-9]/.test(password);
    }

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

    // Form Submission
    $('#registrationForm').submit(function(e) {
        e.preventDefault();
        let isValid = true;

        // Validate Registration Number
        if (!validateRegNumber($('#regNumber').val())) {
            $('#regError').text('Invalid registration number format').show();
            isValid = false;
        }

        // Validate Email
        if (!validateEmail($('#email').val())) {
            $('#emailError').text('Invalid email format').show();
            isValid = false;
        }

        // Validate Password
        if (!validatePassword($('#password').val())) {
            $('#passwordError').text('Password must be at least 8 characters and include uppercase, lowercase, number, and special character').show();
            isValid = false;
        }

        // Confirm Password
        if ($('#password').val() !== $('#confirmPassword').val()) {
            $('#confirmPasswordError').text('Passwords do not match').show();
            isValid = false;
        }

        if (isValid) {
            // Submit form data
            $.ajax({
                url: 'api/register.php',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    alert('Registration successful!');
                    window.location.href = 'login.html';
                },
                error: function(xhr) {
                    alert('Registration failed. Please try again.');
                }
            });
        }
    });
});
