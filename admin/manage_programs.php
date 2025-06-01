<?php
require_once 'auth.php';
requireAdminAuth();
require_once '../config/database.php';

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    try {
        if ($action === 'add') {
            $programName = trim($_POST['program_name']);
            $programCode = strtoupper(trim($_POST['program_code']));
            $description = trim($_POST['description']);
            $durationYears = (int)$_POST['duration_years'];
            $fees = (float)$_POST['fees'];
            
            // Validate inputs
            if (empty($programName) || empty($programCode) || $durationYears <= 0 || $fees <= 0) {
                throw new Exception("All fields are required and must be valid");
            }
            
            // Check if program code already exists
            $checkStmt = $pdo->prepare("SELECT id FROM programs WHERE program_code = ?");
            $checkStmt->execute([$programCode]);
            if ($checkStmt->fetch()) {
                throw new Exception("Program code already exists");
            }
            
            // Insert new program
            $stmt = $pdo->prepare("
                INSERT INTO programs (program_name, program_code, description, duration_years, fees, status) 
                VALUES (?, ?, ?, ?, ?, 'active')
            ");
            $stmt->execute([$programName, $programCode, $description, $durationYears, $fees]);
            
            $message = "Program added successfully!";
            $messageType = "success";
            
        } elseif ($action === 'edit') {
            $programId = (int)$_POST['program_id'];
            $programName = trim($_POST['program_name']);
            $programCode = strtoupper(trim($_POST['program_code']));
            $description = trim($_POST['description']);
            $durationYears = (int)$_POST['duration_years'];
            $fees = (float)$_POST['fees'];
            $status = $_POST['status'];
            
            // Validate inputs
            if (empty($programName) || empty($programCode) || $durationYears <= 0 || $fees <= 0) {
                throw new Exception("All fields are required and must be valid");
            }
            
            // Check if program code exists for other programs
            $checkStmt = $pdo->prepare("SELECT id FROM programs WHERE program_code = ? AND id != ?");
            $checkStmt->execute([$programCode, $programId]);
            if ($checkStmt->fetch()) {
                throw new Exception("Program code already exists for another program");
            }
            
            // Update program
            $stmt = $pdo->prepare("
                UPDATE programs 
                SET program_name = ?, program_code = ?, description = ?, duration_years = ?, fees = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([$programName, $programCode, $description, $durationYears, $fees, $status, $programId]);
            
            $message = "Program updated successfully!";
            $messageType = "success";
            
        } elseif ($action === 'delete') {
            $programId = (int)$_POST['program_id'];
            
            // Check if program has enrollments
            $checkStmt = $pdo->prepare("SELECT COUNT(*) as count FROM enrollments WHERE program_id = ?");
            $checkStmt->execute([$programId]);
            $enrollmentCount = $checkStmt->fetch()['count'];
            
            if ($enrollmentCount > 0) {
                throw new Exception("Cannot delete program with existing enrollments. Set status to inactive instead.");
            }
            
            // Delete program
            $stmt = $pdo->prepare("DELETE FROM programs WHERE id = ?");
            $stmt->execute([$programId]);
            
            $message = "Program deleted successfully!";
            $messageType = "success";
        }
        
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

// Get all programs
try {
    $stmt = $pdo->prepare("
        SELECT p.*, 
               COUNT(e.id) as enrollment_count,
               COUNT(CASE WHEN e.enrollment_status = 'approved' THEN 1 END) as approved_count
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Management - University of Arusha</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .action-buttons {
            margin-bottom: 30px;
            text-align: right;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin: 0 5px;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .programs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .program-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .program-card:hover {
            transform: translateY(-5px);
        }

        .program-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }

        .program-header h3 {
            margin-bottom: 5px;
            font-size: 1.3em;
        }

        .program-code {
            background: rgba(255,255,255,0.2);
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
            display: inline-block;
        }

        .program-body {
            padding: 20px;
        }

        .program-info {
            margin-bottom: 15px;
        }

        .program-info p {
            margin: 8px 0;
            color: #666;
        }

        .program-stats {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            font-size: 1.5em;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            font-size: 0.8em;
            color: #666;
            text-transform: uppercase;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-badge.active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-badge.inactive {
            background-color: #f8d7da;
            color: #721c24;
        }

        .program-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .close {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #aaa;
        }

        .close:hover {
            color: #000;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 1em;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        @media (max-width: 768px) {
            .programs-grid {
                grid-template-columns: 1fr;
            }
            
            .program-actions {
                flex-direction: column;
            }
            
            .action-buttons {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 Program Management</h1>
            <p>University of Arusha - Academic Programs</p>
        </div>

        <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <div class="action-buttons">
            <button onclick="openAddModal()" class="btn btn-primary">➕ Add New Program</button>
            <a href="dashboard.php" class="btn btn-secondary">🏠 Back to Dashboard</a>
        </div>

        <div class="programs-grid">
            <?php foreach ($programs as $program): ?>
            <div class="program-card">
                <div class="program-header">
                    <h3><?php echo htmlspecialchars($program['program_name']); ?></h3>
                    <span class="program-code"><?php echo htmlspecialchars($program['program_code']); ?></span>
                </div>
                <div class="program-body">
                    <div class="program-info">
                        <p><strong>Duration:</strong> <?php echo $program['duration_years']; ?> years</p>
                        <p><strong>Fees:</strong> TZS <?php echo number_format($program['fees'], 2); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="status-badge <?php echo $program['status']; ?>">
                                <?php echo ucfirst($program['status']); ?>
                            </span>
                        </p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($program['description']); ?></p>
                    </div>
                    
                    <div class="program-stats">
                        <div class="stat">
                            <div class="stat-number"><?php echo $program['enrollment_count']; ?></div>
                            <div class="stat-label">Total Enrollments</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number"><?php echo $program['approved_count']; ?></div>
                            <div class="stat-label">Approved Students</div>
                        </div>
                    </div>
                    
                    <div class="program-actions">
                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($program)); ?>)" 
                                class="btn btn-warning">✏️ Edit</button>
                        <?php if ($program['enrollment_count'] == 0): ?>
                        <button onclick="deleteProgram(<?php echo $program['id']; ?>)" 
                                class="btn btn-danger">🗑️ Delete</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add Program Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>➕ Add New Program</h2>
                <span class="close" onclick="closeModal('addModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="program_name">Program Name:</label>
                    <input type="text" id="program_name" name="program_name" required>
                </div>
                
                <div class="form-group">
                    <label for="program_code">Program Code:</label>
                    <input type="text" id="program_code" name="program_code" required placeholder="e.g., BCS, BBA">
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="duration_years">Duration (Years):</label>
                    <input type="number" id="duration_years" name="duration_years" min="1" max="10" required>
                </div>
                
                <div class="form-group">
                    <label for="fees">Fees (TZS):</label>
                    <input type="number" id="fees" name="fees" min="0" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-success">💾 Add Program</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">❌ Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Program Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>✏️ Edit Program</h2>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_program_id" name="program_id">
                
                <div class="form-group">
                    <label for="edit_program_name">Program Name:</label>
                    <input type="text" id="edit_program_name" name="program_name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_program_code">Program Code:</label>
                    <input type="text" id="edit_program_code" name="program_code" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_description">Description:</label>
                    <textarea id="edit_description" name="description" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit_duration_years">Duration (Years):</label>
                    <input type="number" id="edit_duration_years" name="duration_years" min="1" max="10" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_fees">Fees (TZS):</label>
                    <input type="number" id="edit_fees" name="fees" min="0" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_status">Status:</label>
                    <select id="edit_status" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-success">💾 Update Program</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">❌ Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        function openEditModal(program) {
            document.getElementById('edit_program_id').value = program.id;
            document.getElementById('edit_program_name').value = program.program_name;
            document.getElementById('edit_program_code').value = program.program_code;
            document.getElementById('edit_description').value = program.description;
            document.getElementById('edit_duration_years').value = program.duration_years;
            document.getElementById('edit_fees').value = program.fees;
            document.getElementById('edit_status').value = program.status;
            
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function deleteProgram(programId) {
            if (confirm('Are you sure you want to delete this program? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="program_id" value="${programId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            
            if (event.target === addModal) {
                addModal.style.display = 'none';
            }
            if (event.target === editModal) {
                editModal.style.display = 'none';
            }
        }

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const programCode = form.querySelector('[name="program_code"]');
                    const fees = form.querySelector('[name="fees"]');
                    const duration = form.querySelector('[name="duration_years"]');
                    
                    if (programCode && !/^[A-Z]{2,10}$/.test(programCode.value.trim())) {
                        alert('Program code should be 2-10 uppercase letters only');
                        e.preventDefault();
                        return;
                    }
                    
                    if (fees && parseFloat(fees.value) <= 0) {
                        alert('Fees must be greater than 0');
                        e.preventDefault();
                        return;
                    }
                    
                    if (duration && (parseInt(duration.value) < 1 || parseInt(duration.value) > 10)) {
                        alert('Duration must be between 1 and 10 years');
                        e.preventDefault();
                        return;
                    }
                });
            });
        });

        // Auto-format program code to uppercase
        document.addEventListener('input', function(e) {
            if (e.target.name === 'program_code') {
                e.target.value = e.target.value.toUpperCase();
            }
        });

        // Format fees with commas
        document.addEventListener('input', function(e) {
            if (e.target.name === 'fees') {
                const value = e.target.value.replace(/,/g, '');
                if (!isNaN(value) && value !== '') {
                    e.target.value = parseFloat(value).toLocaleString();
                }
            }
        });

        // Success message auto-hide
        const message = document.querySelector('.message');
        if (message && message.classList.contains('success')) {
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
