<?php include 'includes/header.php'; ?>


<br><br>

<!-- Main Content -->
<main class="page-content">
    <section class="contact-section">
        <h1>Contact Us</h1>
        <div class="contact-content">
            <div class="contact-info">
                <h2>Get in Touch</h2>
                <div class="contact-details">
                    <p><strong>Address:</strong> Arusha, Tanzania</p>
                    <p><strong>Phone:</strong> +255 123 456 789</p>
                    <p><strong>Email:</strong> info@uoa.ac.tz</p>
                </div>
            </div>
            <div class="contact-form">
                <h2>Send us a Message</h2>
                <form id="contactForm">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject:</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message:</label>
                        <textarea id="message" name="message" required></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Send Message</button>
                </form>
            </div>
        </div>
    </section>
</main>




<?php include 'includes/footer.php'; ?>