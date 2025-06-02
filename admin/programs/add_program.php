<?php
require_once '../auth.php';
requireAdminAuth();
require_once '../../config/database.php';

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $programName = trim($_POST['program_name']);
    $programCode = strtoupper(trim($_POST['program_code']));
    $description = trim($_POST['description']);
    $durationYears = (int)$_POST['duration_years'];
    $fees = (float)$_POST['fees'];
    
    // Validation
    if (empty($programName) || empty($programCode) || empty($description) || $durationYears <= 0 || $fees <= 0) {
        $message = "All fields are required and must be valid.";
        $messageType = "error";
    } elseif (!preg_match('/^[A-Z]{2,10}$/', $programCode)) {
        $message = "Program code must be 2-10 uppercase letters only.";
        $messageType = "error";
    } else {
        try {
            // Check if program code already exists
            $checkStmt = $pdo->prepare("SELECT id FROM programs WHERE program_code = ?");
            $checkStmt->execute([$programCode]);
            
            if ($checkStmt->fetch()) {
                $message = "Program code already exists. Please use a different code.";
                $messageType = "error";
            } else {
                // Insert new program
                $stmt = $pdo->prepare("
                    INSERT INTO programs (program_name, program_code, description, duration_years, fees, status, created_at)
                    VALUES (?, ?, ?, ?, ?, 'active', NOW())
                ");
                
                if ($stmt->execute([$programName, $programCode, $description, $durationYears, $fees])) {
                    header("Location: view_programs.php?msg=added");
                    exit;
                } else {
                    $message = "Error adding program. Please try again.";
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
    <title>Add Program - University of Arusha</title>
    <link rel="stylesheet" href="../styles/student_management.css">
        <link rel="stylesheet" href="../styles/program_management.css">
</head>
<body>
    <div class="container">
        <div class="navigation">
            <a href="view_programs.php" class="nav-btn">📚 View Programs</a>
            <a href="../dashboard.php" class="nav-btn">📊 Dashboard</a>
        </div>

        <div class="header">
            <h1>➕ Add New Program</h1>
            <p>Create a new academic program</p>
        </div>

        <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" class="program-form" id="addProgramForm">
                <div class="form-group">
                    <label for="program_name">Program Name *</label>
                    <input type="text" id="program_name" name="program_name" 
                           value="<?php echo isset($_POST['program_name']) ? htmlspecialchars($_POST['program_name']) : ''; ?>"
                           placeholder="e.g., Bachelor of Computer Science" required>
                </div>

                <div class="form-group">
                    <label for="program_code">Program Code *</label>
                    <input type="text" id="program_code" name="program_code" 
                           value="<?php echo isset($_POST['program_code']) ? htmlspecialchars($_POST['program_code']) : ''; ?>"
                           placeholder="e.g., BCS, BBA (2-10 uppercase letters)" 
                           pattern="[A-Z]{2,10}" maxlength="10" required>
                    <small>Use 2-10 uppercase letters only</small>
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="4" 
                              placeholder="Describe the program, its objectives, and career prospects..." required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="duration_years">Duration (Years) *</label>
                        <input type="number" id="duration_years" name="duration_years" 
                               value="<?php echo isset($_POST['duration_years']) ? $_POST['duration_years'] : ''; ?>"
                               min="1" max="10" required>
                    </div>

                    <div class="form-group">
                        <label for="fees">Annual Fees (TZS) *</label>
                        <input type="number" id="fees" name="fees" 
                               value="<?php echo isset($_POST['fees']) ? $_POST['fees'] : ''; ?>"
                               min="0" step="0.01" placeholder="e.g., 2000000" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Add Program</button>
                    <a href="view_programs.php" class="btn btn-secondary">❌ Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-format program code to uppercase
        document.getElementById('program_code').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });

        // Format fees with commas for better readability
        document.getElementById('fees').addEventListener('input', function(e) {
            let value = e.target.value.replace(/,/g, '');
            if (!isNaN(value) && value !== '') {
                // Don't format while typing, just validate
                if (parseFloat(value) < 0) {
                    e.target.value = '';
                }
            }
        });

        // Form validation
        document.getElementById('addProgramForm').addEventListener('submit', function(e) {
            const programCode = document.getElementById('program_code').value.trim();
            const fees = document.getElementById('fees').value;
            const duration = document.getElementById('duration_years').value;

            if (!/^[A-Z]{2,10}$/.test(programCode)) {
                alert('Program code must be 2-10 uppercase letters only');
                e.preventDefault();
                return;
            }

            if (parseFloat(fees) <= 0) {
                alert('Fees must be greater than 0');
                e.preventDefault();
                return;
            }

            if (parseInt(duration) < 1 || parseInt(duration) > 10) {
                alert('Duration must be between 1 and 10 years');
                e.preventDefault();
                return;
            }
        });

        // Character counter for description
        const descriptionField = document.getElementById('description');
        const maxLength = 500;

        descriptionField.addEventListener('input', function() {
            const remaining = maxLength - this.value.length;
            let counter = document.getElementById('desc-counter');
            
            if (!counter) {
                counter = document.createElement('small');
                counter.id = 'desc-counter';
                counter.style.color = '#6c757d';
                this.parentNode.appendChild(counter);
            }
            
            counter.textContent = `${remaining} characters remaining`;
            
            if (remaining < 50) {
                counter.style.color = '#dc3545';
            } else {
                counter.style.color = '#6c757d';
            }
        });
    </script>
