<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Admin credentials (you can store these in database later)
    $admin_users = [
        'admin' => [
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'name' => 'System Administrator',
            'role' => 'super_admin'
        ],
        'admissions' => [
            'password' => password_hash('admissions2024', PASSWORD_DEFAULT),
            'name' => 'Admissions Officer',
            'role' => 'admissions'
        ]
    ];
    
    if (isset($admin_users[$username]) && password_verify($password, $admin_users[$username]['password'])) {
        $_SESSION['admin_id'] = $username;
        $_SESSION['admin_name'] = $admin_users[$username]['name'];
        $_SESSION['admin_role'] = $admin_users[$username]['role'];
        
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - University of Arusha</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .admin-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 1.8rem;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: #2c3e50;
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .login-btn:hover {
            transform: translateY(-2px);
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .demo-info {
            margin-top: 30px;
            padding: 20px;
            background-color: #e7f3ff;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #004085;
        }

        .demo-info h4 {
            margin-bottom: 10px;
        }

        .demo-info p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="admin-icon">🔐</div>
        <h2>Admin Access</h2>
        <p style="color: #666; margin-bottom: 30px;">University of Arusha Administration</p>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="login-btn">Login to Admin Panel</button>
        </form>

        <div class="demo-info">
            <h4>Demo Credentials:</h4>
            <p><strong>Super Admin:</strong> admin / admin123</p>
            <p><strong>Admissions:</strong> admissions / admissions2024</p>
        </div>
    </div>
</body>
</html>