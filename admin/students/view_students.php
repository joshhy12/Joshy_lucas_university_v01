<?php
require_once '../auth.php';  // Go up one directory to admin folder
requireAdminAuth();
require_once '../../config/database.php';  // Go up two directories to reach config

// Fetch all students with their enrollment information
try {
    $stmt = $pdo->prepare("
        SELECT s.*, e.registration_number, e.enrollment_status, e.academic_year, 
               p.program_name, p.program_code
        FROM students s 
        LEFT JOIN enrollments e ON s.id = e.student_id 
        LEFT JOIN programs p ON e.program_id = p.id 
        ORDER BY s.created_at DESC
    ");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching students: " . $e->getMessage();
    $students = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Students - University of Arusha</title>
    <link rel="stylesheet" href="../styles/student_management.css">
</head>
<body>
    <div class="container">
        <div class="navigation">
            <a href="../dashboard.php" class="nav-btn">📊 Dashboard</a>
            <a href="add_student.php" class="nav-btn">➕ Add Student</a>
        </div>

        <div class="header">
            <h1>👥 Student Management</h1>
            <p>View and manage student records</p>
        </div>

        <?php if (isset($error)): ?>
        <div class="message error">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <div class="students-table">
            <div class="table-header">
                <h2>📋 All Students</h2>
                <div class="table-actions">
                    <input type="text" id="searchInput" placeholder="Search students..." class="search-input">
                </div>
            </div>

            <?php if (empty($students)): ?>
            <div class="no-data">
                <h3>No students found</h3>
                <p><a href="add_student.php" class="btn btn-primary">Add First Student</a></p>
            </div>
            <?php else: ?>
            <table id="studentsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Program</th>
                        <th>Registration No.</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo $student['id']; ?></td>
                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo htmlspecialchars($student['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($student['gender']); ?></td>
                        <td><?php echo htmlspecialchars($student['program_name'] ?? 'Not Enrolled'); ?></td>
                        <td><?php echo htmlspecialchars($student['registration_number'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="status-badge <?php echo $student['enrollment_status'] ?? 'not-enrolled'; ?>">
                                <?php echo ucfirst($student['enrollment_status'] ?? 'Not Enrolled'); ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="update_student.php?id=<?php echo $student['id']; ?>" class="btn btn-edit">✏️ Edit</a>
                            <a href="delete_student.php?id=<?php echo $student['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this student?')">🗑️ Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const table = document.getElementById('studentsTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length - 1; j++) {
                    if (cells[j].textContent.toLowerCase().includes(searchValue)) {
                        found = true;
                        break;
                    }
                }

                row.style.display = found ? '' : 'none';
            }
        });
    </script>
</body>
</html>
