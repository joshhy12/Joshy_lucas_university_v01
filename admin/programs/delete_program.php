<?php
require_once '../auth.php';
requireAdminAuth();
require_once '../../config/database.php';

$message = '';
$messageType = '';
$program = null;

// Get program ID from URL
$programId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($programId <= 0) {
    header("Location: view_programs.php");
    exit;
}

// Fetch program data
try {
    $stmt = $pdo->prepare("
        SELECT p.*, COUNT(e.id) as enrollment_count
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
} catch (PDOException $e) {
    $message = "Error fetching program data: " . $e->getMessage();
    $messageType = "error";
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Check if program has enrollments
        if ($program['enrollment_count'] > 0) {
            $message = "Cannot delete program with existing enrollments.";
            $messageType = "error";
        } else {
            // Delete the program
            $stmt = $pdo->prepare("DELETE FROM programs WHERE id = ?");
            if ($stmt->execute([$programId])) {
                header("Location: view_programs.php?msg=deleted");
                exit;
            } else {
                $message = "Error deleting program.";
                $messageType = "error";
            }
        }
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Program - University of Arusha</title>
    <link rel="stylesheet" href="styles/program_management.css">
</head>
<body>
    <div class="container">
        <div class="navigation">
            <a href="view_programs.php" class="nav-btn">📚 Back to Programs</a>
            <a href="../dashboard.php" class="nav-btn">📊 Dashboard</a>
        </div>

        <div class="header">
            <h1>🗑️ Delete Program</h1>
            <p>Confirm program deletion</p>
        </div>

        <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <?php if ($program): ?>
        <div class="form-container">
            <div class="program-info">
                <h3>Program to Delete</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($program['program_name']); ?></p>
                <p><strong>Code:</strong> <?php echo htmlspecialchars($program['program_code']); ?></p>
                <p><strong>Enrollments:</strong> <?php echo $program['enrollment_count']; ?></p>
            </div>

            <?php if ($program['enrollment_count'] > 0): ?>
            <div class="message error">
                Cannot delete this program because it has <?php echo $program['enrollment_count']; ?> enrollment(s).
            </div>
            <?php else: ?>
            <div class="message error">
                ⚠️ Warning: This action cannot be undone!
            </div>

            <form method="POST">
                <div class="form-actions">
                    <button type="submit" name="confirm_delete" class="btn btn-danger" 
                            onclick="return confirm('Are you sure you want to delete this program permanently?')">
                        🗑️ Delete Program
                    </button>
                    <a href="view_programs.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
