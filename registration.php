<?php include 'includes/header.php'; ?>
    <br><br><br><br>

    <main>
        <div class="registration-container">
            <h2>Create Account</h2>
            <form id="registrationForm" class="registration-form" action="register.php" method="POST">
                <!-- First Row -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" name="fullName" required>
                        <span class="error-message" id="nameError"></span>
                    </div>
                    <div class="form-group">
                        <label for="regNumber">Registration Number</label>
                        <input type="text" id="regNumber" name="regNumber" placeholder="BCS-00-0000-0000" required>
                        <span class="error-message" id="regError"></span>
                    </div>
                    <div class="form-group">
                        <label for="sex">Sex</label>
                        <select id="sex" name="sex" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <!-- Second Row -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                        <span class="error-message" id="emailError"></span>
                    </div>
                    <div class="form-group">
                        <label for="region">Region</label>
                        <select id="region" name="region" required>
                            <option value="">Select Region</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="district">District</label>
                        <select id="district" name="district" required>
                            <option value="">Select District</option>
                        </select>
                    </div>
                </div>

                <!-- Third Row -->
                <div class="password-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <span class="error-message" id="passwordError"></span>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                        <span class="error-message" id="confirmPasswordError"></span>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Register</button>
            </form>
        </div>
    </main>

    <script src="app.js"></script>
    <script src="registration.js"></script>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>



