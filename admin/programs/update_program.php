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
    $stmt = $pdo->prepare("SELECT * FROM programs WHERE id = ?");
    $stmt->execute([$programId]);
    $program = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$program) {
        header("Location: view_programs.php?error=program_not_found");
        exit;
    }
} catch (PDOException $e) {
    $message = "Error fetching program data: " . $e->getMessage();
    $messageType = "error";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $program) {
    $programName = trim($_POST['program_name']);
    $programCode = strtoupper(trim($_POST['program_code']));
    $description = trim($_POST['description']);
    $durationYears = (int)$_POST['duration_years'];
    $fees = (float)$_POST['fees'];
    $status = $_POST['status'];
    
    // Validation
    if (empty($programName) || empty($programCode) || empty($description) || $durationYears <= 0 || $fees <= 0) {
        $message = "All fields are required and must be valid.";
        $messageType = "error";
    } elseif (!preg_match('/^[A-Z]{2,10}$/', $programCode)) {
        $message = "Program code must be 2-10 uppercase letters only.";
        $messageType = "error";
    } else {
        try {
            // Check if program code exists for other programs
            $checkStmt = $pdo->prepare("SELECT id FROM programs WHERE program_code = ? AND id != ?");
            $checkStmt->execute([$programCode, $programId]);
            
            if ($checkStmt->fetch()) {
                $message = "Program code already exists for another program.";
                $messageType = "error";
            } else {
                // Update program
                $stmt = $pdo->prepare("
                    UPDATE programs 
                    SET program_name = ?, program_code = ?, description = ?, 
                        duration_years = ?, fees = ?, status = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                if ($stmt->execute([$programName, $programCode, $description, $durationYears, $fees, $status, $programId])) {
                    header("Location: view_programs.php?msg=updated");
                    exit;
                } else {
                    $message = "Error updating program. Please try again.";
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
    <title>Update Program - University of Arusha</title>
     <link rel="stylesheet" href="../styles/student_management.css">
        <link rel="stylesheet" href="../styles/program_management.css">
</head>
<body>
    <div class="container">
        <div class="navigation">
            <a href="view_programs.php" class="nav-btn">📚 View Programs</a>
            <a href="program_details.php?id=<?php echo $programId; ?>" class="nav-btn">👁️ View Details</a>
            <a href="delete_program.php?id=<?php echo $programId; ?>" class="nav-btn">🗑️ Delete</a>
            <a href="../dashboard.php" class="nav-btn">📊 Dashboard</a>
        </div>

        <div class="header">
            <h1>✏️ Update Program</h1>
            <p>Modify program information</p>
        </div>

        <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <?php if ($program): ?>
        <div class="form-container">
            <div class="program-info">
                <h3>Current Program Information</h3>
                <p><strong>ID:</strong> <?php echo $program['id']; ?></p>
                <p><strong>Created:</strong> <?php echo date('F j, Y g:i A', strtotime($program['created_at'])); ?></p>
                <?php if ($program['updated_at']): ?>
                <p><strong>Last Updated:</strong> <?php echo date('F j, Y g:i A', strtotime($program['updated_at'])); ?></p>
                <?php endif; ?>
            </div>

            <form method="POST" class="program-form" id="updateProgramForm">
                <div class="form-group">
                    <label for="program_name">Program Name *</label>
                    <input type="text" id="program_name" name="program_name" 
                           value="<?php echo htmlspecialchars($program['program_name']); ?>"
                           placeholder="e.g., Bachelor of Computer Science" required>
                </div>

                <div class="form-group">
                    <label for="program_code">Program Code *</label>
                    <input type="text" id="program_code" name="program_code" 
                           value="<?php echo htmlspecialchars($program['program_code']); ?>"
                           placeholder="e.g., BCS, BBA (2-10 uppercase letters)" 
                           pattern="[A-Z]{2,10}" maxlength="10" required>
                    <small>Use 2-10 uppercase letters only</small>
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="4" 
                              placeholder="Describe the program, its objectives, and career prospects..." required><?php echo htmlspecialchars($program['description']); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="duration_years">Duration (Years) *</label>
                        <input type="number" id="duration_years" name="duration_years" 
                               value="<?php echo $program['duration_years']; ?>"
                               min="1" max="10" required>
                    </div>

                    <div class="form-group">
                        <label for="fees">Annual Fees (TZS) *</label>
                        <input type="number" id="fees" name="fees" 
                               value="<?php echo $program['fees']; ?>"
                               min="0" step="0.01" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="active" <?php echo $program['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $program['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="suspended" <?php echo $program['status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                    </select>
                    <small>Inactive programs won't accept new enrollments</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Update Program</button>
                    <a href="view_programs.php" class="btn btn-secondary">❌ Cancel</a>
                    <a href="program_details.php?id=<?php echo $programId; ?>" class="btn btn-info">👁️ View Details</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-format program code to uppercase
        document.getElementById('program_code').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });

        // Form validation
        document.getElementById('updateProgramForm').addEventListener('submit', function(e) {
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

            // Confirm update
            if (!confirm('Are you sure you want to update this program?')) {
                e.preventDefault();
            }
        });

        // Warn about status change
        document.getElementById('status').addEventListener('change', function() {
            if (this.value === 'inactive') {
                alert('Setting status to inactive will prevent new student enrollments for this program.');
            } else if (this.value === 'suspended') {
                alert('Suspended programs are temporarily unavailable and may affect current students.');
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
</body>
</html>
