<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

require_once 'auth.php';
requireAdminAuth();
require_once '../config/database.php';

// Function to generate registration number
function generateRegistrationNumber($programCode, $studentId, $pdo) {
    $year = '2025';
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM enrollments e JOIN programs p ON e.program_id = p.id WHERE p.program_code = ? AND e.academic_year LIKE ? AND e.enrollment_status = 'approved'");
    $stmt->execute([$programCode, $year . '%']);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'];
    $sequentialNumber = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    $studentIdPadded = str_pad($studentId, 2, '0', STR_PAD_LEFT);
    return $programCode . '-' . $studentIdPadded . '-' . $sequentialNumber . '-' . $year;
}

// Handle POST requests for approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST request received");
    $enrollmentId = (int)$_POST['enrollment_id'];
    $action = $_POST['action'];
    error_log("Action: $action, Enrollment ID: $enrollmentId");

    try {
        $pdo->beginTransaction();
        error_log("Transaction started");

        if ($action === 'approve') {
            $stmt = $pdo->prepare("
                SELECT e.*, p.program_code, p.program_name, s.full_name, s.email
                FROM enrollments e
                JOIN programs p ON e.program_id = p.id
                JOIN students s ON e.student_id = s.id
                WHERE e.id = ?
            ");
            $stmt->execute([$enrollmentId]);
            $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($enrollment) {
                error_log("Enrollment found: " . print_r($enrollment, true));
                $registrationNumber = generateRegistrationNumber($enrollment['program_code'], $enrollment['student_id'], $pdo);
                error_log("Generated registration number: $registrationNumber");

                $updateStmt = $pdo->prepare("
                    UPDATE enrollments 
                    SET enrollment_status = 'approved', registration_number = ? 
                    WHERE id = ?
                ");
                $updateStmt->execute([$registrationNumber, $enrollmentId]);
                error_log("Update executed for approve");

                $pdo->commit();
                error_log("Transaction committed");
                header("Location: approve_enrollment.php?msg=approved®=" . urlencode($registrationNumber));
                exit;
            } else {
                error_log("Enrollment not found");
                throw new Exception("Enrollment not found");
            }
        } elseif ($action === 'reject') {
            $updateStmt = $pdo->prepare("
                UPDATE enrollments 
                SET enrollment_status = 'rejected' 
                WHERE id = ?
            ");
            $updateStmt->execute([$enrollmentId]);
            error_log("Update executed for reject");

            $pdo->commit();
            error_log("Transaction committed");
            header("Location: approve_enrollment.php?msg=rejected");
            exit;
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error: " . $e->getMessage());
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

// Fetch all enrollments for display
try {
    $stmt = $pdo->prepare("
        SELECT e.*, s.full_name, s.email, s.phone_number, s.gender, 
               p.program_name, p.program_code, p.fees
        FROM enrollments e
        JOIN students s ON e.student_id = s.id
        JOIN programs p ON e.program_id = p.id
        ORDER BY 
            CASE e.enrollment_status 
                WHEN 'pending' THEN 1 
                WHEN 'approved' THEN 2 
                WHEN 'rejected' THEN 3 
            END,
            e.enrollment_date DESC
    ");
    $stmt->execute();
    $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching enrollments: " . $e->getMessage());
    $enrollments = [];
    $error = "Error fetching enrollments: " . $e->getMessage();
}

// Handle success messages from redirect
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'approved' && isset($_GET['reg'])) {
        $message = "Enrollment approved successfully! Registration number: " . htmlspecialchars($_GET['reg']);
        $messageType = "success";
    } elseif ($_GET['msg'] === 'rejected') {
        $message = "Enrollment rejected successfully.";
        $messageType = "success";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/approve.css" type="text/css">
    <title>Enrollment Management - University of Arusha</title>
</head>
<body>
    <div class="container">
        <div class="navigation">
            <a href="../home.php" class="nav-btn">🏠 Home</a>
            <a href="dashboard.php" class="nav-btn">📊 Dashboard</a>
        </div>

        <div class="header">
            <h1>🎓 Enrollment Management</h1>
            <p>University of Arusha - Admissions Office</p>
        </div>

        <?php if (isset($message)): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
        <div class="message error">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <!-- Enrollments Table -->
        <div class="enrollments-table">
            <div class="table-header">
                <h2>📋 Student Enrollments</h2>
            </div>

            <?php if (empty($enrollments)): ?>
            <div class="no-data">
                <h3>No enrollments found</h3>
            </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Student Details</th>
                        <th>Program</th>
                        <th>Application Date</th>
                        <th>Status</th>
                        <th>Registration Number</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrollments as $enrollment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($enrollment['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($enrollment['program_name']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($enrollment['enrollment_date'])); ?></td>
                        <td><?php echo ucfirst($enrollment['enrollment_status']); ?></td>
                        <td><?php echo $enrollment['registration_number'] ?: 'Not assigned'; ?></td>
                        <td>
                            <?php if ($enrollment['enrollment_status'] === 'pending'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="enrollment_id" value="<?php echo $enrollment['id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-approve">✅ Approve</button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="enrollment_id" value="<?php echo $enrollment['id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-reject">❌ Reject</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
// Clean the output buffer before sending headers
ob_end_flush();
?>