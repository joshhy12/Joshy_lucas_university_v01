<?php
require_once 'auth.php';
requireAdminAuth();
require_once '../config/database.php';

// Get statistics
try {
    $stats = [];

    // Total students
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
    $stats['total_students'] = $stmt->fetch()['count'];

    // Pending enrollments
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM enrollments WHERE enrollment_status = 'pending'");
    $stats['pending_enrollments'] = $stmt->fetch()['count'];

    // Approved enrollments
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM enrollments WHERE enrollment_status = 'approved'");
    $stats['approved_enrollments'] = $stmt->fetch()['count'];

    // Total programs
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM programs WHERE status = 'active'");
    $stats['total_programs'] = $stmt->fetch()['count'];
} catch (PDOException $e) {
    $stats = ['total_students' => 0, 'pending_enrollments' => 0, 'approved_enrollments' => 0, 'total_programs' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - University of Arusha</title>
    <link rel="stylesheet" href="./styles/dashboard.css">

</head>

<body>
    <header class="admin-header">
        <div class="header-content">
            <h1>🎓 University of Arusha - Admin Panel</h1>
            <div class="admin-info">
                <span>Welcome, <?php echo htmlspecialchars(getAdminName()); ?></span>
                <span style="font-size: 0.9rem; opacity: 0.8;">(<?php echo htmlspecialchars(getAdminRole()); ?>)</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <div class="container">
        <?php if (isset($_GET['error']) && $_GET['error'] === 'insufficient_permissions'): ?>
            <div class="alert alert-warning">
                <strong>Access Denied:</strong> You don't have sufficient permissions to access that resource.
            </div>
        <?php endif; ?>

        <?php if ($stats['pending_enrollments'] > 0): ?>
            <div class="alert alert-info">
                <strong>Action Required:</strong> You have <?php echo $stats['pending_enrollments']; ?> pending enrollment(s) that need review.
                <a href="approve_enrollment.php" style="color: #0c5460; font-weight: bold; text-decoration: underline;">Review Now</a>
            </div>
        <?php endif; ?>

        <!-- Statistics Dashboard -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Students</h3>
                <div class="stat-number"><?php echo $stats['total_students']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Enrollments</h3>
                <div class="stat-number" style="color: #ffc107;"><?php echo $stats['pending_enrollments']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Approved Enrollments</h3>
                <div class="stat-number" style="color: #28a745;"><?php echo $stats['approved_enrollments']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Programs</h3>
                <div class="stat-number" style="color: #17a2b8;"><?php echo $stats['total_programs']; ?></div>
            </div>
        </div>

        <!-- Admin Menu -->
        <div class="admin-menu">
            <div class="menu-card">
                <div class="icon">📋</div>
                <h3>Enrollment Management</h3>
                <p>Review and approve student enrollment applications. Assign registration numbers to approved students.</p>
                <a href="approve_enrollment.php">Manage Enrollments</a>
            </div>

            <div class="menu-card">
                <div class="icon">👥</div>
                <h3>Student Management</h3>
                <p>View and manage student records, update information, and track student progress.</p>
                <a href="students/view_students.php">Manage Students</a>
            </div>

            <div class="menu-card">
                <div class="icon">📚</div>
                <h3>Program Management</h3>
                <p>Add, edit, or remove academic programs. Set program fees and requirements.</p>
                <a href="programs/view_programs.php">Manage Programs</a>
            </div>

            <div class="menu-card">
                <div class="icon">📊</div>
                <h3>Reports & Analytics</h3>
                <p>Generate reports on enrollments, student statistics, and program performance.</p>
                <a href="#">View Reports</a>
            </div>

            <div class="menu-card">
                <div class="icon">⚙️</div>
                <h3>System Settings</h3>
                <p>Configure system settings, manage admin users, and update university information.</p>
                <a href="#">System Settings</a>
            </div>

            <div class="menu-card">
                <div class="icon">📧</div>
                <h3>Communications</h3>
                <p>Send notifications to students, manage email templates, and communication logs.</p>
                <a href="#">Communications</a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity">
            <h2>Recent Activity</h2>

            <?php
            // Get recent enrollments
            try {
                $recentStmt = $pdo->prepare("
                    SELECT e.*, s.full_name, p.program_name 
                    FROM enrollments e
                    JOIN students s ON e.student_id = s.id
                    JOIN programs p ON e.program_id = p.id
                    ORDER BY e.enrollment_date DESC
                    LIMIT 5
                ");
                $recentStmt->execute();
                $recentEnrollments = $recentStmt->fetchAll();

                if (empty($recentEnrollments)): ?>
                    <div class="activity-item">
                        <div class="activity-icon">📝</div>
                        <div class="activity-content">
                            <h4>No Recent Activity</h4>
                            <p>No recent enrollment activities to display.</p>
                        </div>
                    </div>
                    <?php else:
                    foreach ($recentEnrollments as $enrollment): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <?php
                                switch ($enrollment['enrollment_status']) {
                                    case 'pending':
                                        echo '⏳';
                                        break;
                                    case 'approved':
                                        echo '✅';
                                        break;
                                    case 'rejected':
                                        echo '❌';
                                        break;
                                    default:
                                        echo '📝';
                                }
                                ?>
                            </div>
                            <div class="activity-content">
                                <h4><?php echo htmlspecialchars($enrollment['full_name']); ?></h4>
                                <p>Applied for <?php echo htmlspecialchars($enrollment['program_name']); ?> -
                                    Status: <?php echo ucfirst($enrollment['enrollment_status']); ?></p>
                            </div>
                            <div class="activity-time">
                                <?php echo date('M j, Y', strtotime($enrollment['enrollment_date'])); ?>
                            </div>
                        </div>
            <?php endforeach;
                endif;
            } catch (PDOException $e) {
                echo '<div class="activity-item"><div class="activity-content"><p>Error loading recent activity.</p></div></div>';
            }
            ?>
        </div>
    </div>

    <script>
        // Auto-refresh stats every 30 seconds
        setInterval(function() {
            // Only refresh if user is still on the page
            if (document.visibilityState === 'visible') {
                location.reload();
            }
        }, 30000);

        // Add click tracking for menu items
        document.querySelectorAll('.menu-card a').forEach(link => {
            link.addEventListener('click', function(e) {
                // Add loading state
                this.innerHTML = '⏳ Loading...';
                this.style.pointerEvents = 'none';
            });
        });

        // Welcome message for new admin sessions
        if (sessionStorage.getItem('adminJustLoggedIn')) {
            sessionStorage.removeItem('adminJustLoggedIn');

            // Show welcome notification
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                z-index: 1000;
                animation: slideIn 0.5s ease;
            `;
            notification.innerHTML = '✅ Welcome to Admin Panel!';
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Add CSS animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>

</html>