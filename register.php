<?php
session_start();
require_once 'config/database.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['fullName']);
    $phoneNumber = trim($_POST['phoneNumber']);
    $gender = $_POST['sex'];
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $selectedProgram = $_POST['program'];
    
    // Basic validation
    if (empty($fullName) || empty($phoneNumber) || empty($gender) || empty($email) || empty($password) || empty($selectedProgram)) {
        $error = "All fields are required";
    }
    // Check if passwords match
    elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match";
    }
    // Check password length
    elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    }
    // Validate email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address";
    }
    // Validate phone number (basic validation)
    elseif (!preg_match('/^[0-9+\-\s()]{10,15}$/', $phoneNumber)) {
        $error = "Invalid phone number format";
    }
    else {
        try {
            // Check if email already exists
            $checkStmt = $pdo->prepare("SELECT id FROM students WHERE email = ?");
            $checkStmt->execute([$email]);
            
            if ($checkStmt->fetch()) {
                $error = "Email already exists";
            } else {
                // Get program ID
                $programStmt = $pdo->prepare("SELECT id, program_name FROM programs WHERE program_name = ? AND status = 'active'");
                $programStmt->execute([$selectedProgram]);
                $program = $programStmt->fetch();
                
                if (!$program) {
                    $error = "Invalid program selected";
                } else {
                    // Begin transaction
                    $pdo->beginTransaction();
                    
                    // Hash password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert student (REMOVED registration_status since it doesn't exist in your table)
                    $stmt = $pdo->prepare("INSERT INTO students (full_name, phone_number, gender, email, password) VALUES (?, ?, ?, ?, ?)");
                    
                    if ($stmt->execute([$fullName, $phoneNumber, $gender, $email, $hashedPassword])) {
                        // Get the newly created student ID
                        $studentId = $pdo->lastInsertId();
                        
                        // Create enrollment record
                        $currentYear = date('Y');
                        $nextYear = $currentYear + 1;
                        $academicYear = $currentYear . '/' . $nextYear;
                        
                        $enrollStmt = $pdo->prepare("INSERT INTO enrollments (student_id, program_id, enrollment_status, academic_year) VALUES (?, ?, 'pending', ?)");
                        $enrollStmt->execute([$studentId, $program['id'], $academicYear]);
                        
                        // Commit transaction
                        $pdo->commit();
                        
                        // Set session variables for login
                        $_SESSION['student_id'] = $studentId;
                        $_SESSION['student_name'] = $fullName;
                        $_SESSION['student_email'] = $email;
                        $_SESSION['phone_number'] = $phoneNumber;
                        $_SESSION['gender'] = $gender;
                        $_SESSION['selected_program'] = $program['program_name'];
                        
                        // Redirect to dashboard
                        header("Location: dashboard/index.php?welcome=1");
                        exit;
                    } else {
                        $pdo->rollBack();
                        $error = "Registration failed. Please try again.";
                    }
                }
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log($e->getMessage());
            $error = "Database error occurred. Please try again.";
        }
    }
}

// Get available programs from database
try {
    $programsStmt = $pdo->prepare("SELECT id, program_name FROM programs WHERE status = 'active' ORDER BY program_name");
    $programsStmt->execute();
    $programs = $programsStmt->fetchAll();
} catch (PDOException $e) {
    $programs = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - University of Arusha</title>
    <link rel="stylesheet" href="styles/register.css">
</head>
<body>
    <div class="container">
        <div class="home-link">
            <a href="home.php">← Back to Home</a>
        </div>
        
        <div class="header">
            <h2>🎓 Student Registration</h2>
            <p>Join University of Arusha - Complete Your Application</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" id="registrationForm">




            <div class="form-section">
                <h3>📋 Personal Information</h3>
                <div class="form-container">
                    <div class="form-column">
                        <div class="form-group">
                            <label for="fullName">Full Name:</label>
                            <input type="text" id="fullName" name="fullName" 
                                   value="<?php echo isset($_POST['fullName']) ? htmlspecialchars($_POST['fullName']) : ''; ?>" 
                                   placeholder="Enter your full name"
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="phoneNumber">Phone Number:</label>
                            <input type="text" id="phoneNumber" name="phoneNumber" 
                                   placeholder="+255 123 456 789"
                                   value="<?php echo isset($_POST['phoneNumber']) ? htmlspecialchars($_POST['phoneNumber']) : ''; ?>" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="sex">Gender:</label>
                            <select id="sex" name="sex" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo (isset($_POST['sex']) && $_POST['sex'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo (isset($_POST['sex']) && $_POST['sex'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo (isset($_POST['sex']) && $_POST['sex'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-column">
                        <div class="form-group">
                            <label for="email">Email Address:</label>
                            <input type="email" id="email" name="email" 
                                   placeholder="your.email@example.com"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" id="password" name="password" 
                                   placeholder="Create a strong password"
                                   required>
                            <div class="password-requirements">
                                💡 Password must be at least 8 characters long
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirmPassword">Confirm Password:</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" 
                                   placeholder="Re-enter your password"
                                   required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>🎓 Academic Information</h3>
                <div class="form-group full-width">
                    <label for="program">Select Program:</label>
                    <select id="program" name="program" required>
                        <option value="">Choose your desired program</option>
                        <?php foreach ($programs as $program): ?>
                            <option value="<?php echo htmlspecialchars($program['program_name']); ?>"
                                    <?php echo (isset($_POST['program']) && $_POST['program'] == $program['program_name']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($program['program_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="submit-section">
                <button type="submit" class="submit-btn" id="submitBtn">
                    🚀 Register Now
                </button>
            </div>
        </form>

        <div class="login-link">
            <p>Already have an account? <a href="login.html">Login here</a></p>
        </div>
    </div>

    <script>
        // Form validation and enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registrationForm');
            const submitBtn = document.getElementById('submitBtn');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirmPassword');

            // Real-time password validation
            function validatePasswords() {
                if (password.value && confirmPassword.value) {
                    if (password.value === confirmPassword.value) {
                        confirmPassword.classList.remove('error');
                        confirmPassword.classList.add('success');
                    } else {
                        confirmPassword.classList.remove('success');
                        confirmPassword.classList.add('error');
                    }
                }
            }

            password.addEventListener('input', validatePasswords);
            confirmPassword.addEventListener('input', validatePasswords);

            // Form submission with loading state
            form.addEventListener('submit', function(e) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
                
                // Re-enable button after 3 seconds in case of error
                setTimeout(() => {
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                }, 3000);
            });

            // Enhanced input validation
            const inputs = document.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.checkValidity()) {
                        this.classList.remove('error');
                        this.classList.add('success');
                    } else {
                        this.classList.remove('success');
                        this.classList.add('error');
                    }
                });

                input.addEventListener('input', function() {
                    if (this.classList.contains('error') && this.checkValidity()) {
                        this.classList.remove('error');
                        this.classList.add('success');
                    }
                });
            });

            // Phone number formatting
            const phoneInput = document.getElementById('phoneNumber');
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.startsWith('255')) {
                    value = '+' + value;
                } else if (value.startsWith('0')) {
                    value = '+255' + value.substring(1);
                }
                e.target.value = value;
            });
        });
    </script>
</body>
</html>
