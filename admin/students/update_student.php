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

// Fetch student data
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        header("Location: view_students.php");
        exit;
    }
} catch (PDOException $e) {
    $message = "Error fetching student data: " . $e->getMessage();
    $messageType = "error";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phoneNumber = trim($_POST['phone_number']);
    $gender = $_POST['gender'];
    $registrationStatus = $_POST['registration_status'];
    $updatePassword = !empty($_POST['password']);
    $password = $_POST['password'];

    // Validation
    if (empty($fullName) || empty($email) || empty($phoneNumber) || empty($gender)) {
        $message = "All fields except password are required.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $messageType = "error";
    } else {
        try {
            // Check if email already exists for other students
            $checkStmt = $pdo->prepare("SELECT id FROM students WHERE email = ? AND id != ?");
            $checkStmt->execute([$email, $studentId]);
            
            if ($checkStmt->fetch()) {
                $message = "Email already exists. Please use a different email.";
                $messageType = "error";
            } else {
                // Update student
                if ($updatePassword) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        UPDATE students 
                        SET full_name = ?, email = ?, phone_number = ?, gender = ?, registration_status = ?, password = ?
                        WHERE id = ?
                    ");
                    $result = $stmt->execute([$fullName, $email, $phoneNumber, $gender, $registrationStatus, $hashedPassword, $studentId]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE students 
                        SET full_name = ?, email = ?, phone_number = ?, gender = ?, registration_status = ?
                        WHERE id = ?
                    ");
                    $result = $stmt->execute([$fullName, $email, $phoneNumber, $gender, $registrationStatus, $studentId]);
                }
                
                if ($result) {
                    $message = "Student updated successfully!";
                    $messageType = "success";
                    // Refresh student data
                    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
                    $stmt->execute([$studentId]);
                    $student = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $message = "Error updating student. Please try again.";
                    $messageType = "error";
                }
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
    <title>Update Student - University of Arusha</title>
    <link rel="stylesheet" href="../styles/student_management.css">
</head>
<body>
    <div class="container">
        <div class="navigation">
            <a href="view_students.php" class="nav-btn">👥 View Students</a>
            <a href="add_student.php" class="nav-btn">➕ Add Student</a>
            <a href="../dashboard.php" class="nav-btn">📊 Dashboard</a>
        </div>

        <div class="header">
            <h1>✏️ Update Student</h1>
            <p>Edit student information</p>
        </div>

        <?php if (!empty($message)): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <?php if ($student): ?>
        <div class="form-container">
            <div class="student-info">
                <h3>Editing: <?php echo htmlspecialchars($student['full_name']); ?></h3>
                <p>Student ID: <?php echo $student['id']; ?></p>
                <p>Created: <?php echo date('F j, Y', strtotime($student['created_at'])); ?></p>
            </div>

            <form method="POST" class="student-form">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone_number">Phone Number *</label>
                    <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($student['phone_number']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="gender">Gender *</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male" <?php echo ($student['gender'] === 'male') ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo ($student['gender'] === 'female') ? 'selected' : ''; ?>>Female</option>
                        <option value="other" <?php echo ($student['gender'] === 'other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="registration_status">Registration Status *</label>
                    <select id="registration_status" name="registration_status" required>
                        <option value="active" <?php echo ($student['registration_status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($student['registration_status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        <option value="suspended" <?php echo ($student['registration_status'] === 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                        <option value="graduated" <?php echo ($student['registration_status'] === 'graduated') ? 'selected' : ''; ?>>Graduated</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">New Password (Leave blank to keep current password)</label>
                    <input type="password" id="password" name="password" placeholder="Enter new password or leave blank">
                    <small>Only fill this field if you want to change the password</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Student</button>
                    <a href="view_students.php" class="btn btn-secondary">Cancel</a>
                    <a href="delete_student.php?id=<?php echo $student['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this student?')">Delete Student</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Form validation
        document.querySelector('.student-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            
            if (password && password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long');
                return false;
            }
        });

        // Confirmation for sensitive changes
        document.getElementById('registration_status').addEventListener('change', function() {
            const status = this.value;
            if (status === 'suspended' || status === 'inactive') {
                if (!confirm('Are you sure you want to change the status to ' + status + '? This may affect the student\'s access to the system.')) {
                    this.value = '<?php echo $student['registration_status']; ?>';
                }
            }
        });
    </script>
</body>
</html>
