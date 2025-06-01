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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group input:valid,
        .form-group select:valid {
            border-color: #28a745;
        }

        .form-group select {
            cursor: pointer;
        }

        .form-group select option {
            padding: 10px;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 8px;
            margin-top: 20px;
            border: 1px solid #f5c6cb;
            font-weight: 500;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 12px 15px;
            border-radius: 8px;
            margin-top: 20px;
            border: 1px solid #c3e6cb;
            font-weight: 500;
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e1e5e9;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #764ba2;
        }

        .home-link {
            text-align: center;
            margin-bottom: 20px;
        }

        .home-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s ease;
        }

        .home-link a:hover {
            color: #764ba2;
        }

        .password-requirements {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
            line-height: 1.4;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
                margin: 10px;
            }

            .header h2 {
                font-size: 1.8rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }

        /* Loading animation for submit button */
        .submit-btn.loading {
            position: relative;
            color: transparent;
        }

        .submit-btn.loading::after {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="home-link">
            <a href="home.php">← Back to Home</a>
        </div>
        
        <div class="header">
            <h2>🎓 Student Registration</h2>
            <p>Join University of Arusha</p>
        </div>

        <form method="POST" id="registrationForm">
            <div class="form-group">
                <label for="fullName">Full Name:</label>
                <input type="text" id="fullName" name="fullName" 
                       value="<?php echo isset($_POST['fullName']) ? htmlspecialchars($_POST['fullName']) : ''; ?>" 
                       required>
            </div>

            <div class="form-row">
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

            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" 
                       placeholder="your.email@example.com"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                       required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <div class="password-requirements">
                    Password must be at least 8 characters long
                </div>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm Password:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>
            </div>

            <div class="form-group">
                <label for="program">Select Program:</label>
                <select id="program" name="program" required>
                    <option value="">Select Program</option>
                    <?php foreach ($programs as $program): ?>
                        <option value="<?php echo htmlspecialchars($program['program_name']); ?>"
                                <?php echo (isset($_POST['program']) && $_POST['program'] == $program['program_name']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($program['program_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn">
                Register Now
            </button>
