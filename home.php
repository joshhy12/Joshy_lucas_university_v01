<?php
session_start();

// Check if user is already logged in
$isLoggedIn = isset($_SESSION['student_id']);
$studentName = $isLoggedIn ? $_SESSION['student_name'] : null;
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
            <div class="programs-grid">
                <div class="program-card">
                    <h3>💻 Bachelor of Computer Science</h3>
                    <p>Comprehensive program covering programming, algorithms, software development, and emerging technologies.</p>
                    <div class="program-details">
                        <span class="program-duration">4 Years</span>
                        <span>TZS 2,500,000/year</span>
                    </div>
                </div>
                <div class="program-card">
                    <h3>💼 Bachelor of Business Administration</h3>
                    <p>Business administration focusing on management, finance, marketing, and entrepreneurship.</p>
                    <div class="program-details">
                        <span class="program-duration">4 Years</span>
                        <span>TZS 2,200,000/year</span>
                    </div>
                </div>
                <div class="program-card">
                    <h3>🎓 Bachelor of Education</h3>
                    <p>Teacher training program for primary and secondary education with modern pedagogical methods.</p>
                    <div class="program-details">
                        <span class="program-duration">4 Years</span>
                        <span>TZS 2,000,000/year</span>
                    </div>
                </div>
                <div class="program-card">
                    <h3>⚕️ Bachelor of Medicine</h3>
                    <p>Comprehensive medical program preparing students for careers in healthcare and medical practice.</p>
                    <div class="program-details">
                        <span class="program-duration">6 Years</span>
                        <span>TZS 3,500,000/year</span>
                    </div>
                </div>
                <div class="program-card">
                    <h3>⚖️ Bachelor of Law</h3>
                    <p>Legal studies program covering constitutional, criminal, and civil law with practical training.</p>
                    <div class="program-details">
                        <span class="program-duration">4 Years</span>
                        <span>TZS 2,300,000/year</span>
                    </div>
                </div>
                <div class="program-card">
                    <h3>🔧 Bachelor of Engineering</h3>
                    <p>Engineering program with specializations in civil, mechanical, and electrical engineering.</p>
                    <div class="program-details">
                        <span class="program-duration">5 Years</span>
                        <span>TZS 2,800,000/year</span>
                    </div>
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
                <a href="registration.html" class="btn btn-secondary btn-large">🚀 Apply Now</a>
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
                    <p><a href="registration.html">Apply Now</a></p>
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

