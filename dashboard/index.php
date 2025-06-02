<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../config/database.php';

// Get updated student information
$studentId = $_SESSION['student_id'];
$stmt = $pdo->prepare("
    SELECT s.*, e.registration_number, e.enrollment_status, e.academic_year, p.program_name, p.program_code, p.fees
    FROM students s 
    LEFT JOIN enrollments e ON s.id = e.student_id 
    LEFT JOIN programs p ON e.program_id = p.id 
    WHERE s.id = ?
");
$stmt->execute([$studentId]);
$studentData = $stmt->fetch();

if (!$studentData) {
    header("Location: ../login.html");
    exit; 
}

$isNewUser = isset($_GET['welcome']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - University of Arusha</title>
    <link rel="stylesheet" href="../styles/adminstyle.css" type="text/css">

</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <h1>University of Arusha - Student Portal</h1>
                <div class="admin-info">
                    <span id="studentName">Welcome, <?php echo htmlspecialchars($studentData['full_name']); ?></span>
                    <button onclick="logout()" class="logout-btn">Logout</button>
                </div>
            </div>
        </header>

        <?php if ($isNewUser): ?>
        <div class="welcome-banner" style="background: #d4edda; color: #155724; padding: 15px; text-align: center; margin: 20px;">
            <h3>🎉 Welcome to University of Arusha! 🎉</h3>
            <p>Your registration was successful. Complete your enrollment process to get your registration number.</p>
        </div>
        <?php endif; ?>

        <nav class="admin-nav">
            <ul>
                <li><a href="#" onclick="showSection('dashboard')" class="active">Dashboard</a></li>
                <li><a href="#" onclick="showSection('profile')">My Profile</a></li>
                <li><a href="#" onclick="showSection('enrollment')">Enrollment Status</a></li>
                <li><a href="#" onclick="showSection('program')">My Program</a></li>
            </ul>
        </nav>

        <main class="admin-main">
            <!-- Dashboard Section -->
            <section id="dashboard" class="admin-section active">
                <h2>Dashboard Overview</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Registration Number</h3>
                        <span class="stat-number">
                            <?php echo $studentData['registration_number'] ? htmlspecialchars($studentData['registration_number']) : 'Pending'; ?>
                        </span>
                    </div>
                    <div class="stat-card">
                        <h3>Enrollment Status</h3>
                        <span class="stat-text <?php echo $studentData['enrollment_status']; ?>">
                            <?php echo $studentData['enrollment_status'] ? ucfirst($studentData['enrollment_status']) : 'Not Enrolled'; ?>
                        </span>
                    </div>
                    <div class="stat-card">
                        <h3>Program</h3>
                        <span class="stat-text"><?php echo htmlspecialchars($studentData['program_name'] ?? 'Not Selected'); ?></span>
                    </div>
                    <div class="stat-card">
                        <h3>Academic Year</h3>
                        <span class="stat-text"><?php echo htmlspecialchars($studentData['academic_year'] ?? 'N/A'); ?></span>
                    </div>
                </div>

                <?php if ($studentData['enrollment_status'] === 'pending'): ?>
                <div class="alert alert-warning">
                    <h4>⚠️ Action Required</h4>
                    <p>Your enrollment is pending approval. You will receive your registration number once approved by the admissions office.</p>
                </div>
                <?php elseif ($studentData['enrollment_status'] === 'approved'): ?>
                <div class="alert alert-success">
                    <h4>✅ Enrollment Approved</h4>
                    <p>Congratulations! Your enrollment has been approved. Your registration number is: <strong><?php echo htmlspecialchars($studentData['registration_number']); ?></strong></p>
                </div>
                <?php endif; ?>
            </section>

            <!-- Profile Section -->
            <section id="profile" class="admin-section">
                <h2>My Profile</h2>
                <div class="profile-container">
                    <div class="profile-card">
                        <h3>Personal Information</h3>
                        <div class="profile-info">
                            <p><strong>Full Name:</strong> <?php echo htmlspecialchars($studentData['full_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($studentData['email']); ?></p>
                            <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($studentData['phone_number']); ?></p>
                            <p><strong>Gender:</strong> <?php echo htmlspecialchars($studentData['gender']); ?></p>
                            <p><strong>Registration Status:</strong><?php echo $studentData['enrollment_status'] ? ucfirst($studentData['enrollment_status']) : 'Not Enrolled'; ?>
</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Enrollment Section -->
            <section id="enrollment" class="admin-section">
                <h2>Enrollment Status</h2>
                <div class="enrollment-container">
                    <?php if ($studentData['enrollment_status'] === 'pending'): ?>
                    <div class="enrollment-card pending">
                        <h3>📋 Enrollment Pending</h3>
                        <p>Your enrollment application is currently under review by the admissions office.</p>
                        <div class="enrollment-details">
                            <p><strong>Application Date:</strong> <?php echo date('F j, Y', strtotime($studentData['created_at'])); ?></p>
                            <p><strong>Selected Program:</strong> <?php echo htmlspecialchars($studentData['program_name']); ?></p>
                            <p><strong>Program Code:</strong> <?php echo htmlspecialchars($studentData['program_code']); ?></p>
                            <p><strong>Academic Year:</strong> <?php echo htmlspecialchars($studentData['academic_year']); ?></p>
                        </div>
                        <div class="next-steps">
                            <h4>Next Steps:</h4>
                            <ul>
                                <li>Wait for admissions office review</li>
                                <li>Check your email for updates</li>
                                <li>Prepare required documents</li>
                            </ul>
                        </div>
                    </div>
                    <?php elseif ($studentData['enrollment_status'] === 'approved'): ?>
                    <div class="enrollment-card approved">
                        <h3>✅ Enrollment Approved</h3>
                        <p>Congratulations! Your enrollment has been approved.</p>
                        <div class="enrollment-details">
                            <p><strong>Registration Number:</strong> <span class="reg-number"><?php echo htmlspecialchars($studentData['registration_number']); ?></span></p>
                            <p><strong>Program:</strong> <?php echo htmlspecialchars($studentData['program_name']); ?></p>
                            <p><strong>Program Code:</strong> <?php echo htmlspecialchars($studentData['program_code']); ?></p>
                            <p><strong>Academic Year:</strong> <?php echo htmlspecialchars($studentData['academic_year']); ?></p>
                            <p><strong>Program Fees:</strong> TZS <?php echo number_format($studentData['fees'], 2); ?></p>
                        </div>
                    </div>
                    <?php elseif ($studentData['enrollment_status'] === 'rejected'): ?>
                    <div class="enrollment-card rejected">
                        <h3>❌ Enrollment Rejected</h3>
                        <p>Unfortunately, your enrollment application was not approved.</p>
                        <p>Please contact the admissions office for more information.</p>
                    </div>
                    <?php else: ?>
                    <div class="enrollment-card">
                        <h3>No Enrollment Found</h3>
                        <p>You are not currently enrolled in any program.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Program Section -->
            <section id="program" class="admin-section">
                <h2>My Program</h2>
                <div class="program-container">
                    <?php if ($studentData['program_name']): ?>
                    <div class="program-card">
                        <h3><?php echo htmlspecialchars($studentData['program_name']); ?></h3>
                        <div class="program-details">
                            <p><strong>Program Code:</strong> <?php echo htmlspecialchars($studentData['program_code']); ?></p>
                            <p><strong>Academic Year:</strong> <?php echo htmlspecialchars($studentData['academic_year']); ?></p>
                            <p><strong>Enrollment Status:</strong> 
                                <span class="status-badge <?php echo $studentData['enrollment_status']; ?>">
                                    <?php echo ucfirst($studentData['enrollment_status']); ?>
                                </span>
                            </p>
                            <?php if ($studentData['fees']): ?>
                            <p><strong>Program Fees:</strong> TZS <?php echo number_format($studentData['fees'], 2); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($studentData['enrollment_status'] === 'approved'): ?>
                        <div class="program-actions">
                            <h4>Available Actions:</h4>
                            <ul>
                                <li>📚 View Course Catalog</li>
                                <li>📝 Register for Courses</li>
                                <li>💰 Pay Tuition Fees</li>
                                <li>📄 Download Enrollment Letter</li>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="program-card">
                        <h3>No Program Selected</h3>
                        <p>You haven't selected a program yet.</p>
                    </div>
                    <?php endif; ?>
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
