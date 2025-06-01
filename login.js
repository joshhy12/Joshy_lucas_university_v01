$(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        const email = $('#email').val();
        const password = $('#password').val();
        
        $.ajax({
            url: 'api/login.php',
            method: 'POST',
            data: {
                email: email,
                password: password
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    $('#message').removeClass('error').addClass('success').text('Login successful! Redirecting...').show();
                    setTimeout(function() {
                        window.location.href = 'dashboard/index.php';
                    }, 1000);
                } else {
                    $('#message').removeClass('success').addClass('error').text(response.message).show();
                }
            },
            error: function() {
                $('#message').removeClass('success').addClass('error').text('Login failed. Please try again.').show();
            }
        });
    });
});
