<?php
session_start();
require_once 'config/database.php';

// Check if user is already logged in
$isLoggedIn = isset($_SESSION['student_id']);
$studentName = $isLoggedIn ? $_SESSION['student_name'] : null;

// Fetch programs from database
try {
    $programsStmt = $pdo->prepare("SELECT * FROM programs WHERE status = 'active' ORDER BY program_name");
    $programsStmt->execute();
    $programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $programs = [];
    error_log("Error fetching programs: " . $e->getMessage());
}

// Function to get program icon based on program name
function getProgramIcon($programName) {
    $icons = [
        'Bachelor of Computer Science' => '💻',
        'Bachelor of Business Administration' => '💼',
        'Bachelor of Education' => '🎓',
        'Bachelor of Medicine' => '⚕️',
        'Bachelor of Law' => '⚖️',
        'Bachelor of Engineering' => '🔧'
    ];
    
    return isset($icons[$programName]) ? $icons[$programName] : '📚';
}

// Function to format currency
function formatCurrency($amount) {
    return 'TZS ' . number_format($amount, 0, '.', ',');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/style.css" type="text/css">
    <title>University of Arusha - Home</title>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="nav-container">
            <div class="logo">🎓 University of Arusha</div>
            <nav class="nav-menu">
                <a href="#home">Home</a>
                <a href="#about">About</a>
                <a href="#programs">Programs</a>
                <a href="#admissions">Admissions</a>
                <a href="#contact">Contact</a>
            </nav>
            <div class="auth-buttons">
                <?php if ($isLoggedIn): ?>
                    <span class="user-info">Welcome, <?php echo htmlspecialchars($studentName); ?>!</span>
                    <a href="dashboard/index.php" class="btn btn-primary">Dashboard</a>
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary">Login</a>
                    <a href="register.php" class="btn btn-primary">Apply Now</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1>Welcome to University of Arusha</h1>
            <p>Empowering minds, shaping futures. Join thousands of students in pursuing excellence in higher education.</p>
            <div class="hero-buttons">
                <?php if (!$isLoggedIn): ?>
                    <a href="register.php" class="btn btn-primary btn-large">🎓 Apply for Admission</a>
                    <a href="#programs" class="btn btn-secondary btn-large">📚 Explore Programs</a>
                <?php else: ?>
                    <a href="dashboard/index.php" class="btn btn-primary btn-large">📊 My Dashboard</a>
                    <a href="#programs" class="btn btn-secondary btn-large">📚 View Programs</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="about" class="features">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose University of Arusha?</h2>
                <p>Discover what makes us the premier choice for higher education in Tanzania</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🏆</div>
                    <h3>Academic Excellence</h3>
                    <p>Our programs are designed to meet international standards with experienced faculty and modern curriculum.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🌍</div>
                    <h3>Global Recognition</h3>
                    <p>Our degrees are internationally recognized, opening doors to opportunities worldwide.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💼</div>
                    <h3>Career Support</h3>
                    <p>Comprehensive career guidance and job placement assistance for all our graduates.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔬</div>
                    <h3>Modern Facilities</h3>
                    <p>State-of-the-art laboratories, libraries, and technology to enhance your learning experience.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>Diverse Community</h3>
                    <p>Join a vibrant community of students from different backgrounds and cultures.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💡</div>
                    <h3>Innovation Hub</h3>
                    <p>Encouraging research, innovation, and entrepreneurship among our students.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Programs Section -->
    <section id="programs" class="programs">
        <div class="container">
            <div class="section-title">
                <h2>Our Academic Programs</h2>
                <p>Choose from our wide range of undergraduate and graduate programs</p>
            </div>
            
            <?php if (!empty($programs)): ?>
                <div class="programs-grid">
                    <?php foreach ($programs as $program): ?>
                        <div class="program-card" data-program-id="<?php echo $program['id']; ?>">
                            <div class="program-header">
                                <h3><?php echo getProgramIcon($program['program_name']); ?> <?php echo htmlspecialchars($program['program_name']); ?></h3>
                                <span class="program-code"><?php echo htmlspecialchars($program['program_code']); ?></span>
                            </div>
                            
                            <div class="program-description">
                                <p><?php echo htmlspecialchars($program['description']); ?></p>
                            </div>
                            
                            <div class="program-details">
                                <div class="program-info">
                                    <span class="program-duration">
                                        <i class="icon">⏱️</i>
                                        <?php echo $program['duration_years']; ?> Year<?php echo $program['duration_years'] > 1 ? 's' : ''; ?>
                                    </span>
                                   
                                </div>
                                
                                <?php if (!$isLoggedIn): ?>
                                    <div class="program-actions">
                                        <a href="register.php" class="btn btn-primary btn-small">Apply Now</a>
                                    </div>
                                <?php else: ?>
                                    <div class="program-actions">
                                        <button class="btn btn-secondary btn-small" onclick="showProgramDetails(<?php echo $program['id']; ?>)">View Details</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-programs">
                    <p>No programs available at the moment. Please check back later.</p>
                </div>
            <?php endif; ?>
            
            <div class="programs-stats">
                <div class="stat-item">
                    <h4><?php echo count($programs); ?></h4>
                    <p>Active Programs</p>
                </div>
                <div class="stat-item">
                    <h4>5000+</h4>
                    <p>Students Enrolled</p>
                </div>
                <div class="stat-item">
                    <h4>95%</h4>
                    <p>Graduate Employment Rate</p>
                </div>
                <div class="stat-item">
                    <h4>50+</h4>
                    <p>Expert Faculty</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section id="admissions" class="cta">
        <div class="container">
            <h2>Ready to Start Your Journey?</h2>
            <p>Join thousands of successful graduates who started their careers at University of Arusha</p>
            <?php if (!$isLoggedIn): ?>
                <a href="register.php" class="btn btn-secondary btn-large">🚀 Apply Now</a>
            <?php else: ?>
                <a href="dashboard/index.php" class="btn btn-secondary btn-large">📊 Go to Dashboard</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>University of Arusha</h3>
                    <p>Leading institution of higher learning in Tanzania, committed to academic excellence and innovation.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <p><a href="#about">About Us</a></p>
                    <p><a href="#programs">Programs</a></p>
                    <p><a href="register.php">Apply Now</a></p>
                    <p><a href="login.html">Student Login</a></p>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <p>📍 Arusha, Tanzania</p>
                    <p>📞 +255 123 456 789</p>
                    <p>📧 info@universityofarusha.ac.tz</p>
                </div>
                <div class="footer-section">
                    <h3>Follow Us</h3>
                    <p><a href="#">Facebook</a></p>
                    <p><a href="#">Twitter</a></p>
                    <p><a href="#">LinkedIn</a></p>
                    <p><a href="#">Instagram</a></p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 University of Arusha. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Program Details Modal -->
    <div id="programModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeProgramModal()">&times;</span>
            <div id="programModalContent">
                <!-- Program details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        // Program details modal functionality
        function showProgramDetails(programId) {
            const programs = <?php echo json_encode($programs); ?>;
            const program = programs.find(p => p.id == programId);
            
            if (program) {
                const modalContent = document.getElementById('programModalContent');
                modalContent.innerHTML = `
                    <div class="program-detail-header">
                        <h2>${getProgramIcon(program.program_name)} ${program.program_name}</h2>
                        <span class="program-code-large">${program.program_code}</span>
                    </div>
                    <div class="program-detail-body">
                        <div class="program-detail-section">
                            <h3>📋 Program Description</h3>
                            <p>${program.description}</p>
                        </div>
                        <div class="program-detail-section">
                            <h3>📊 Program Details</h3>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <strong>Duration:</strong> ${program.duration_years} Year${program.duration_years > 1 ? 's' : ''}
                                </div>
                                <div class="detail-item">
                                    <strong>Annual Fees:</strong> ${formatCurrency(program.fees)}
                                </div>
                                <div class="detail-item">
                                    <strong>Program Code:</strong> ${program.program_code}
                                </div>
                                <div class="detail-item">
                                    <strong>Status:</strong> ${program.status.charAt(0).toUpperCase() + program.status.slice(1)}
                                </div>
                            </div>
                        </div>
                        <?php if (!$isLoggedIn): ?>
                        <div class="program-detail-actions">
                            <a href="register.php" class="btn btn-primary">Apply for this Program</a>
                            <a href="#contact" class="btn btn-secondary" onclick="closeProgramModal()">Contact Us</a>
                        </div>
                        <?php endif; ?>
                    </div>
                `;
                document.getElementById('programModal').style.display = 'block';
            }
        }

        function closeProgramModal() {
            document.getElementById('programModal').style.display = 'none';
        }

        function getProgramIcon(programName) {
            const icons = {
                'Bachelor of Computer Science': '💻',
                'Bachelor of Business Administration': '💼',
                'Bachelor of Education': '🎓',
                'Bachelor of Medicine': '⚕️',
                'Bachelor of Law': '⚖️',
                'Bachelor of Engineering': '🔧'
            };
            return icons[programName] || '📚';
        }

        function formatCurrency(amount) {
            return 'TZS ' + new Intl.NumberFormat().format(amount);
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('programModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
