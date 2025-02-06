<?php 
session_start();
// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
include 'includes/header.php'; 
?>
    
<br><br><br><br>

<main class="welcome-section">
    <div class="user-dashboard">
        <h2>Welcome, <span id="userFullName"></span>!</h2>
        <div class="user-details">
            <p>Email: <span id="userEmailDisplay"></span></p>
            <p>Registration Number: <span id="userRegNumber"></span></p>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Check session and load user data
    $.ajax({
        url: 'api/check_session.php',
        method: 'GET',
        success: function(response) {
            if(response.status === 'success') {
                $('#userFullName').text(response.user.full_name);
                $('#userEmailDisplay').text(response.user.email);
            } else {
                window.location.href = 'login.php';
            }
        }
    });

    // Logout functionality
    $('#logoutBtn').click(function() {
        $.ajax({
            url: 'api/logout.php',
            method: 'POST',
            success: function() {
                window.location.href = 'index.php';
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>