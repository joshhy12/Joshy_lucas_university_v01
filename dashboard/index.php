<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit;
}

// Get user data
$userName = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];
$isNewUser = isset($_GET['welcome']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - University of Arusha</title>
    <link rel="stylesheet" href="style/adminstyle.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <h1>University of Arusha - Student Panel</h1>
                <div class="admin-info">
                    <span id="adminName">Welcome, <?php echo htmlspecialchars($userName); ?></span>
                    <button onclick="logout()" class="logout-btn">Logout</button>
                </div>
            </div>
        </header>

        <?php if ($isNewUser): ?>
        <div class="welcome-banner" style="background: #d4edda; color: #155724; padding: 15px; text-align: center; margin: 20px;">
            <h3>🎉 Welcome to University of Arusha! 🎉</h3>
            <p>Your registration was successful. You can now access your student dashboard.</p>
        </div>
        <?php endif; ?>

        <nav class="admin-nav">
            <ul>
                <li><a href="#" onclick="showSection('dashboard')" class="active">Dashboard</a></li>
                <li><a href="#" onclick="showSection('profile')">My Profile</a></li>
                <li><a href="#" onclick="showSection('courses')">My Courses</a></li>
                <li><a href="#" onclick="showSection('grades')">Grades</a></li>
            </ul>
        </nav>

        <main class="admin-main">
            <!-- Dashboard Section -->
            <section id="dashboard" class="admin-section active">
                <h2>Dashboard Overview</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Student ID</h3>
                        <span class="stat-number"><?php echo isset($_SESSION['reg_number']) ? htmlspecialchars($_SESSION['reg_number']) : 'N/A'; ?></span>
                    </div>
                    <div class="stat-card">
                        <h3>Email</h3>
                        <span class="stat-text"><?php echo htmlspecialchars($userEmail); ?></span>
                    </div>
                    <div class="stat-card">
                        <h3>Status</h3>
                        <span class="stat-text">Active Student</span>
                    </div>
                    <div class="stat-card">
                        <h3>Academic Year</h3>
                        <span class="stat-text">2024/2025</span>
                    </div>
                </div>
            </section>

            <!-- Profile Section -->
            <section id="profile" class="admin-section">
                <h2>My Profile</h2>
                <div class="profile-container">
                    <div class="profile-card">
                        <h3>Personal Information</h3>
                        <div class="profile-info">
                            <p><strong>Full Name:</strong> <?php echo htmlspecialchars($userName); ?></p>
                            <p><strong>Registration Number:</strong> <?php echo isset($_SESSION['reg_number']) ? htmlspecialchars($_SESSION['reg_number']) : 'N/A'; ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($userEmail); ?></p>
                            <p><strong>Gender:</strong> <?php echo isset($_SESSION['sex']) ? htmlspecialchars(ucfirst($_SESSION['sex'])) : 'N/A'; ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Courses Section -->
            <section id="courses" class="admin-section">
                <h2>My Courses</h2>
                <div class="courses-container">
                    <p>Course enrollment will be available soon. Please contact the academic office for course registration.</p>
                </div>
            </section>

            <!-- Grades Section -->
            <section id="grades" class="admin-section">
                <h2>My Grades</h2>
                <div class="grades-container">
                    <p>Grades will be available once courses are completed and evaluated.</p>
                </div>
            </section>
        </main>
    </div>

    <script>
        function showSection(sectionName) {
            // Hide all sections
            const sections = document.querySelectorAll('.admin-section');
            sections.forEach(section => section.classList.remove('active'));
            
            // Remove active class from all nav links
            const navLinks = document.querySelectorAll('.admin-nav a');
            navLinks.forEach(link => link.classList.remove('active'));
            
            // Show selected section
            document.getElementById(sectionName).classList.add('active');
            
            // Add active class to clicked nav link
            event.target.classList.add('active');
        }
        
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../logout.php';
            }
        }
    </script>
</body>
</html>
