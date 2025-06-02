<?php
require_once '../auth.php';
requireAdminAuth();
require_once '../../config/database.php';


// Get all programs with enrollment statistics
try {
    $stmt = $pdo->prepare("
        SELECT p.*,
               COUNT(e.id) as enrollment_count,
               COUNT(CASE WHEN e.enrollment_status = 'approved' THEN 1 END) as approved_count,
               COUNT(CASE WHEN e.enrollment_status = 'pending' THEN 1 END) as pending_count
        FROM programs p
        LEFT JOIN enrollments e ON p.id = e.program_id
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $programs = $stmt->fetchAll();
} catch (PDOException $e) {
    $programs = [];
    $error = "Error fetching programs: " . $e->getMessage();
}

// Handle success messages
$message = '';
$messageType = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'added':
            $message = "Program added successfully!";
            $messageType = "success";
            break;
        case 'updated':
            $message = "Program updated successfully!";
            $messageType = "success";
            break;
        case 'deleted':
            $message = "Program deleted successfully!";
            $messageType = "success";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Programs - University of Arusha</title>
    <link rel="stylesheet" href="../styles/student_management.css">
        <link rel="stylesheet" href="../styles/program_management.css">

</head>
<body>
    <div class="container">
        <div class="navigation">
            <a href="../dashboard.php" class="nav-btn">📊 Dashboard</a>
            <a href="add_program.php" class="nav-btn">➕ Add Program</a>
        </div>

        <div class="header">
            <h1>📚 Program Management</h1>
            <p>University of Arusha - Academic Programs</p>
        </div>

        <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
        <div class="message error">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <div class="action-buttons">
            <a href="add_program.php" class="btn btn-primary">➕ Add New Program</a>
            <input type="text" id="searchInput" placeholder="Search programs..." class="search-input">
        </div>

        <?php if (empty($programs)): ?>
        <div class="no-data">
            <h3>No programs found</h3>
            <p><a href="add_program.php" class="btn btn-primary">Add First Program</a></p>
        </div>
        <?php else: ?>
        <div class="programs-grid" id="programsGrid">
            <?php foreach ($programs as $program): ?>
            <div class="program-card" data-program="<?php echo strtolower($program['program_name'] . ' ' . $program['program_code']); ?>">
                <div class="program-header">
                    <h3><?php echo htmlspecialchars($program['program_name']); ?></h3>
                    <span class="program-code"><?php echo htmlspecialchars($program['program_code']); ?></span>
                </div>
                
                <div class="program-body">
                    <div class="program-info">
                        <p><strong>Duration:</strong> <?php echo $program['duration_years']; ?> year<?php echo $program['duration_years'] > 1 ? 's' : ''; ?></p>
                        <p><strong>Fees:</strong> TZS <?php echo number_format($program['fees'], 2); ?></p>
                        <p><strong>Status:</strong>
                            <span class="status-badge <?php echo $program['status']; ?>">
                                <?php echo ucfirst($program['status']); ?>
                            </span>
                        </p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($program['description']); ?></p>
                        <p><strong>Created:</strong> <?php echo date('M j, Y', strtotime($program['created_at'])); ?></p>
                    </div>
                    
                    <div class="program-stats">
                        <div class="stat">
                            <div class="stat-number"><?php echo $program['enrollment_count']; ?></div>
                            <div class="stat-label">Total Enrollments</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number"><?php echo $program['approved_count']; ?></div>
                            <div class="stat-label">Approved</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number"><?php echo $program['pending_count']; ?></div>
                            <div class="stat-label">Pending</div>
                        </div>
                    </div>
                    
                    <div class="program-actions">
                        <a href="update_program.php?id=<?php echo $program['id']; ?>" class="btn btn-warning">✏️ Edit</a>
                        <a href="delete_program.php?id=<?php echo $program['id']; ?>" class="btn btn-danger">🗑️ Delete</a>
                        <a href="program_details.php?id=<?php echo $program['id']; ?>" class="btn btn-info">👁️ View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const programCards = document.querySelectorAll('.program-card');

            programCards.forEach(card => {
                const programData = card.getAttribute('data-program');
                if (programData.includes(searchValue)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Auto-hide success messages
        const message = document.querySelector('.message.success');
        if (message) {
            setTimeout(() => {
                message.style.opacity = '0';
                setTimeout(() => {
                    message.style.display = 'none';
                }, 300);
            }, 3000);
        }
    </script>
</body>
</html>
