<?php
require_once '../auth.php';
requireAdminAuth();
require_once '../../config/database.php';


$message = '';
$messageType = '';
$student = null;

// Get student ID from URL
$studentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($studentId <= 0) {
    header("Location: view_students.php");
    exit;
}

// Fetch student data with enrollment information
try {
    $stmt = $pdo->prepare("
        SELECT s.*, e.registration_number, e.enrollment_status, e.academic_year, 
               p.program_name, p.program_code
        FROM students s 
        LEFT JOIN enrollments e ON s.id = e.student_id 
        LEFT JOIN programs p ON e.program_id = p.id 
        WHERE s.id = ?
    ");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        header("Location: view_students.php?error=student_not_found");
        exit;
    }
} catch (PDOException $e) {
    $message = "Error fetching student data: " . $e->getMessage();
    $messageType = "error";
}

// Handle deletion confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $confirmId = (int)$_POST['student_id'];
    
    if ($confirmId !== $studentId) {
        $message = "Invalid request. Please try again.";
        $messageType = "error";
    } else {
        try {
            $pdo->beginTransaction();
            
            // First, delete related enrollments
            $deleteEnrollments = $pdo->prepare("DELETE FROM enrollments WHERE student_id = ?");
            $deleteEnrollments->execute([$studentId]);
            
            // Then delete the student
            $deleteStudent = $pdo->prepare("DELETE FROM students WHERE id = ?");
            $result = $deleteStudent->execute([$studentId]);
            
            if ($result) {
                $pdo->commit();
                header("Location: view_students.php?msg=deleted&name=" . urlencode($student['full_name']));
                exit;
            } else {
                $pdo->rollBack();
                $message = "Error deleting student. Please try again.";
                $messageType = "error";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Database error: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Handle soft delete (deactivate instead of permanent delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deactivate'])) {
    $confirmId = (int)$_POST['student_id'];
    
    if ($confirmId !== $studentId) {
        $message = "Invalid request. Please try again.";
        $messageType = "error";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE students SET registration_status = 'inactive' WHERE id = ?");
            $result = $stmt->execute([$studentId]);
            
            if ($result) {
                header("Location: view_students.php?msg=deactivated&name=" . urlencode($student['full_name']));
                exit;
            } else {
                $message = "Error deactivating student. Please try again.";
                $messageType = "error";
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Student - University of Arusha</title>
    <link rel="stylesheet" href="../styles/student_management.css">
    <style>
        .delete-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .delete-header {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .delete-header h2 {
            margin: 0;
            font-size: 1.5em;
        }

        .student-details {
            padding: 30px;
        }

        .student-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }

        .danger-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .impact-list {
            list-style: none;
            padding: 0;
        }

        .impact-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .impact-list li:last-child {
            border-bottom: none;
        }

        .impact-list li::before {
            content: "⚠️";
            margin-right: 10px;
        }

        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="navigation">
            <a href="view_students.php" class="nav-btn">👥 View Students</a>
            <a href="update_student.php?id=<?php echo $studentId; ?>" class="nav-btn">✏️ Edit Student</a>
            <a href="../dashboard.php" class="nav-btn">📊 Dashboard</a>
        </div>

        <?php if (!empty($message)): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <?php if ($student): ?>
        <div class="delete-container">
            <div class="delete-header">
                <h2>🗑️ Delete Student Record</h2>
                <p>This action requires careful consideration</p>
            </div>

            <div class="student-details">
                <div class="student-card">
                    <h3>Student Information</h3>
                    <div class="profile-info">
                        <p><strong>ID:</strong> <?php echo $student['id']; ?></p>
                        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($student['full_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($student['phone_number']); ?></p>
                        <p><strong>Gender:</strong> <?php echo htmlspecialchars($student['gender']); ?></p>
                        <p><strong>Registration Status:</strong> <?php echo ucfirst($student['registration_status']); ?></p>
                        <p><strong>Created:</strong> <?php echo date('F j, Y', strtotime($student['created_at'])); ?></p>
                        
                        <?php if ($student['program_name']): ?>
                        <hr>
                        <h4>Enrollment Information</h4>
                        <p><strong>Program:</strong> <?php echo htmlspecialchars($student['program_name']); ?></p>
                        <p><strong>Program Code:</strong> <?php echo htmlspecialchars($student['program_code']); ?></p>
                        <p><strong>Registration Number:</strong> <?php echo htmlspecialchars($student['registration_number'] ?? 'N/A'); ?></p>
                        <p><strong>Enrollment Status:</strong> <?php echo ucfirst($student['enrollment_status']); ?></p>
                        <p><strong>Academic Year:</strong> <?php echo htmlspecialchars($student['academic_year']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="warning-box">
                    <h4>⚠️ Before You Proceed</h4>
                    <p>Consider these alternatives before permanently deleting this student:</p>
                    <ul class="impact-list">
                        <li>Deactivate the account instead of deleting</li>
                        <li>Update the registration status to "inactive"</li>
                        <li>Contact the student before taking action</li>
                        <li>Backup important data if needed</li>
                    </ul>
                </div>

                <div class="danger-box">
                    <h4>🚨 Deletion Impact</h4>
                    <p>Permanently deleting this student will:</p>
                    <ul class="impact-list">
                        <li>Remove all student personal information</li>
                        <li>Delete enrollment records and registration number</li>
                        <li>Remove access to student portal</li>
                        <li>This action CANNOT be undone</li>
                    </ul>
                </div>

                <div class="action-buttons">
                    <!-- Soft Delete Option (Recommended) -->
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                        <button type="submit" name="deactivate" class="btn btn-warning" onclick="return confirm('Are you sure you want to deactivate this student account?')">
                            🔒 Deactivate Account
                        </button>
                    </form>

                    <!-- Permanent Delete Option -->
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                        <button type="submit" name="confirm_delete" class="btn btn-danger" onclick="return confirmDelete()">
                            🗑️ Permanently Delete
                        </button>
                    </form>

                    <!-- Cancel Option -->
                    <a href="view_students.php" class="btn btn-secondary">
                        ↩️ Cancel
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function confirmDelete() {
            const studentName = "<?php echo addslashes($student['full_name']); ?>";
            const confirmMessage = `Are you absolutely sure you want to PERMANENTLY DELETE ${studentName}?\n\nThis will:\n- Remove all student data\n- Delete enrollment records\n- Cannot be undone\n\nType "DELETE" to confirm:`;
            
            const userInput = prompt(confirmMessage);
            
            if (userInput === "DELETE") {
                return confirm(`Last chance! This will permanently delete ${studentName} and all related data. Are you sure?`);
            } else {
                alert("Deletion cancelled. Student data is safe.");
                return false;
            }
        }

        // Warn user before leaving page
        let formSubmitted = false;
        
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                formSubmitted = true;
            });
        });

        window.addEventListener('beforeunload', function(e) {
            if (!formSubmitted) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</body>
</html>
