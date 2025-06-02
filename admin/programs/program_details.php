<?php
require_once '../auth.php';
requireAdminAuth();
require_once '../../config/database.php';

$message = '';
$messageType = '';
$program = null;
$enrollments = [];

// Get program ID from URL
$programId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($programId <= 0) {
    header("Location: view_programs.php");
    exit;
}

// Fetch program data with statistics
try {
    $stmt = $pdo->prepare("
        SELECT p.*,
               COUNT(e.id) as total_enrollments,
               COUNT(CASE WHEN e.enrollment_status = 'approved' THEN 1 END) as approved_count,
               COUNT(CASE WHEN e.enrollment_status = 'pending' THEN 1 END) as pending_count,
               COUNT(CASE WHEN e.enrollment_status = 'rejected' THEN 1 END) as rejected_count
        FROM programs p
        LEFT JOIN enrollments e ON p.id = e.program_id
        WHERE p.id = ?
        GROUP BY p.id
    ");
    $stmt->execute([$programId]);
    $program = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$program) {
        header("Location: view_programs.php");
        exit;
    }

    // Fetch enrollment details
    $enrollStmt = $pdo->prepare("
        SELECT s.full_name, s.email, s.phone_number, s.gender,
               e.enrollment_status, e.registration_number, e.enrollment_date, e.academic_year
        FROM enrollments e
        JOIN students s ON e.student_id = s.id
        WHERE e.program_id = ?
        ORDER BY e.enrollment_date DESC
    ");
    $enrollStmt->execute([$programId]);
    $enrollments = $enrollStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message = "Error fetching program data: " . $e->getMessage();
    $messageType = "error";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Details - University of Arusha</title>
     <link rel="stylesheet" href="../styles/student_management.css">
        <link rel="stylesheet" href="../styles/program_management.css">
    <style>
        .details-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .detail-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .detail-card h3 {
            color: #667eea;
            margin-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            font-size: 0.9em;
            color: #64748b;
            margin-top: 5px;
        }
        
        .enrollments-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .table-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        th {
            background: #f8fafc;
            font-weight: bold;
            color: #374151;
        }
        
        tr:hover {
            background: #f8fafc;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .status-badge.approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-badge.rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .no-enrollments {
            text-align: center;
            padding: 40px;
            color: #64748b;
        }
        
        @media (max-width: 768px) {
            .details-container {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="navigation">
            <a href="view_programs.php" class="nav-btn">📚 All Programs</a>
            <a href="update_program.php?id=<?php echo $programId; ?>" class="nav-btn">✏️ Edit Program</a>
            <a href="delete_program.php?id=<?php echo $programId; ?>" class="nav-btn">🗑️ Delete</a>
            <a href="../dashboard.php" class="nav-btn">📊 Dashboard</a>
        </div>

        <div class="header">
            <h1>👁️ Program Details</h1>
            <p>Comprehensive program information and enrollment data</p>
        </div>

        <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <?php if ($program): ?>
        <div class="details-container">
            <!-- Program Information -->
            <div class="detail-card">
                <h3>📚 Program Information</h3>
                <div class="program-details">
                    <p><strong>Program Name:</strong> <?php echo htmlspecialchars($program['program_name']); ?></p>
                    <p><strong>Program Code:</strong> <?php echo htmlspecialchars($program['program_code']); ?></p>
                    <p><strong>Duration:</strong> <?php echo $program['duration_years']; ?> year<?php echo $program['duration_years'] > 1 ? 's' : ''; ?></p>
                    <p><strong>Annual Fees:</strong> TZS <?php echo number_format($program['fees'], 2); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge <?php echo $program['status']; ?>">
                            <?php echo ucfirst($program['status']); ?>
                        </span>
                    </p>
                    <p><strong>Description:</strong></p>
                    <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-top: 10px;">
                        <?php echo nl2br(htmlspecialchars($program['description'])); ?>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="detail-card">
                <h3>🔧 System Information</h3>
                <div class="system-details">
                    <p><strong>Program ID:</strong> <?php echo $program['id']; ?></p>
                    <p><strong>Created Date:</strong> <?php echo date('F j, Y g:i A', strtotime($program['created_at'])); ?></p>
                    <?php if ($program['updated_at']): ?>
                    <p><strong>Last Updated:</strong> <?php echo date('F j, Y g:i A', strtotime($program['updated_at'])); ?></p>
                    <?php endif; ?>
                    <p><strong>Database Status:</strong> Active</p>
                </div>
            </div>
        </div>

        <!-- Enrollment Statistics -->
        <div class="detail-card">
            <h3>📊 Enrollment Statistics</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $program['total_enrollments']; ?></div>
                    <div class="stat-label">Total Enrollments</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $program['approved_count']; ?></div>
                    <div class="stat-label">Approved Students</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $program['pending_count']; ?></div>
                    <div class="stat-label">Pending Applications</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $program['rejected_count']; ?></div>
                    <div class="stat-label">Rejected Applications</div>
                </div>
            </div>
        </div>

        <!-- Enrolled Students -->
        <div class="enrollments-table">
            <div class="table-header">
                <h3>👥 Enrolled Students</h3>
                <span><?php echo count($enrollments); ?> student<?php echo count($enrollments) != 1 ? 's' : ''; ?></span>
            </div>

            <?php if (empty($enrollments)): ?>
            <div class="no-enrollments">
                <h4>No enrollments found</h4>
                <p>This program doesn't have any student enrollments yet.</p>
            </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Status</th>
                        <th>Registration No.</th>
                        <th>Academic Year</th>
                        <th>Enrollment Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrollments as $enrollment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($enrollment['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($enrollment['email']); ?></td>
                        <td><?php echo htmlspecialchars($enrollment['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($enrollment['gender']); ?></td>
                        <td>
                            <span class="status-badge <?php echo $enrollment['enrollment_status']; ?>">
                                <?php echo ucfirst($enrollment['enrollment_status']); ?>
                            </span>
                        </td>
                        <td><?php echo $enrollment['registration_number'] ?: 'Not assigned'; ?></td>
                        <td><?php echo htmlspecialchars($enrollment['academic_year']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($enrollment['enrollment_date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="form-actions" style="margin-top: 30px;">
            <a href="view_programs.php" class="btn btn-secondary">← Back to Programs</a>
            <a href="update_program.php?id=<?php echo $programId; ?>" class="btn btn-primary">✏️ Edit Program</a>
            <?php if ($program['total_enrollments'] == 0): ?>
            <a href="delete_program.php?id=<?php echo $programId; ?>" class="btn btn-danger">🗑️ Delete Program</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Print functionality
        function printDetails() {
            window.print();
        }

        // Add print button
        document.addEventListener('DOMContentLoaded', function() {
            const actionsDiv = document.querySelector('.form-actions');
            const printBtn = document.createElement('button');
            printBtn.className = 'btn btn-info';
            printBtn.innerHTML = '🖨️ Print Details';
            printBtn.onclick = printDetails;
            actionsDiv.appendChild(printBtn);
        });

        // Search functionality for enrollments table
        function addSearchToTable() {
            const tableHeader = document.querySelector('.table-header');
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.placeholder = 'Search students...';
            searchInput.style.padding = '8px';
            searchInput.style.borderRadius = '5px';
            searchInput.style.border = '1px solid #ccc';
            
            searchInput.addEventListener('keyup', function() {
                const searchValue = this.value.toLowerCase();
                const rows = document.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchValue) ? '' : 'none';
                });
            });
            
            if (tableHeader && document.querySelector('tbody tr')) {
                tableHeader.appendChild(searchInput);
            }
        }

        // Add search if there are enrollments
        if (document.querySelector('tbody tr')) {
            addSearchToTable();
        }
    </script>
</body>
</html>
