<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - University of Arusha</title>
    <link rel="stylesheet" href="style/admin-style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <h2>University of Arusha</h2>
                <h3>Admin Panel</h3>
            </div>
            <form id="adminLoginForm">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="login-btn">Login</button>
                <div id="message" class="message"></div>
            </form>
        </div>
    </div>
    <script src="admin-script.js"></script>
</body>
</html>
