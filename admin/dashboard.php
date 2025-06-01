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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }

        .admin-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logout-btn {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 8px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background-color: white;
            color: #2c3e50;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2c3e50;
        }

        .admin-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .menu-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .menu-card .icon {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .menu-card h3 {
            color: #2c3e50;
            font-size: 1.3rem;
            margin-bottom: 15px;
        }

        .menu-card p {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .menu-card a {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .menu-card a:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44, 62, 80, 0.3);
        }

        .recent-activity {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            padding: 30px;
            margin-top: 30px;
        }

        .recent-activity h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .activity-item {
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .activity-content {
            flex: 1;
        }

        .activity-content h4 {
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .activity-content p {
            color: #666;
            font-size: 0.9rem;
        }

        .activity-time {
            color: #999;
            font-size: 0.8rem;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }

        .alert-info {
            background-color: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .admin-info {
                flex-direction: column;
                gap: 10px;
            }

            .container {
                padding: 20px 15px;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
            }

            .admin-menu {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>
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
                <a href="manage_students.php">Manage Students</a>
            </div>

            <div class="menu-card">
                <div class="icon">📚</div>
                <h3>Program Management</h3>
                <p>Add, edit, or remove academic programs. Set program fees and requirements.</p>
                <a href="manage_programs.php">Manage Programs</a>
            </div>

            <div class="menu-card">
                <div class="icon">📊</div>
                <h3>Reports & Analytics</h3>
                <p>Generate reports on enrollments, student statistics, and program performance.</p>
                <a href="reports.php">View Reports</a>
            </div>

            <div class="menu-card">
                <div class="icon">⚙️</div>
                <h3>System Settings</h3>
                <p>Configure system settings, manage admin users, and update university information.</p>
                <a href="settings.php">System Settings</a>
            </div>

            <div class="menu-card">
                <div class="icon">📧</div>
                <h3>Communications</h3>
                <p>Send notifications to students, manage email templates, and communication logs.</p>
                <a href="communications.php">Communications</a>
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
                                switch($enrollment['enrollment_status']) {
                                    case 'pending': echo '⏳'; break;
                                    case 'approved': echo '✅'; break;
                                    case 'rejected': echo '❌'; break;
                                    default: echo '📝';
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
