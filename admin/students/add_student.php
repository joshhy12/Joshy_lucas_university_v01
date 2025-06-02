<?php
require_once '../auth.php';
requireAdminAuth();
require_once '../../config/database.php';


$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phoneNumber = trim($_POST['phone_number']);
    $gender = $_POST['gender'];
    $password = $_POST['password'];

    // Validation
    if (empty($fullName) || empty($email) || empty($phoneNumber) || empty($gender) || empty($password)) {
        $message = "All fields are required.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $messageType = "error";
    } else {
        try {
            // Check if email already exists
            $checkStmt = $pdo->prepare("SELECT id FROM students WHERE email = ?");
            $checkStmt->execute([$email]);
            
            if ($checkStmt->fetch()) {
                $message = "Email already exists. Please use a different email.";
                $messageType = "error";
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new student
                $stmt = $pdo->prepare("
                    INSERT INTO students (full_name, email, phone_number, gender, password, registration_status) 
                    VALUES (?, ?, ?, ?, ?, 'active')
                ");
                
                if ($stmt->execute([$fullName, $email, $phoneNumber, $gender, $hashedPassword])) {
                    $message = "Student added successfully!";
                    $messageType = "success";
                    // Clear form data
                    $fullName = $email = $phoneNumber = $gender = '';
                } else {
                    $message = "Error adding student. Please try again.";
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
    <title>Add Student - University of Arusha</title>
    <link rel="stylesheet" href="../styles/student_management.css">
</head>
<body>
    <div class="container">
        <div class="navigation">
            <a href="view_students.php" class="nav-btn">👥 View Students</a>
            <a href="../dashboard.php" class="nav-btn">📊 Dashboard</a>
        </div>

        <div class="header">
            <h1>➕ Add New Student</h1>
            <p>Add a new student to the university system</p>
        </div>

        <?php if (!empty($message)): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" class="student-form">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($fullName ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone_number">Phone Number *</label>
                    <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($phoneNumber ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="gender">Gender *</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male" <?php echo (isset($gender) && $gender === 'male') ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo (isset($gender) && $gender === 'female') ? 'selected' : ''; ?>>Female</option>
                        <option value="other" <?php echo (isset($gender) && $gender === 'other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required>
                    <small>Password should be at least 6 characters long</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Student</button>
                    <a href="view_students.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
