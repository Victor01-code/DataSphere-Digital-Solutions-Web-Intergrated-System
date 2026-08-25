<?php 
$page_title = "Contact Us | DataSphere Digital Solutions";
$page_description = "Contact DataSphere Digital Solutions - Get in touch for digital services, consultations, and project inquiries in Arusha, Tanzania.";
$extra_css = '<link rel="stylesheet" href="css/pages.css">';
include 'php/includes/header.php'; 
?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php">Home</a>
                <span>/</span>
                <span>Contact</span>
            </div>
            <h1>Get In Touch</h1>
            <p>Have a project in mind? We'd love to hear from you. Let's create something amazing together.</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="section">
        <div class="container">
            <div class="contact-grid">
                <!-- Contact Info -->
                <div class="contact-info">
                    <h2>Let's Start a Conversation</h2>
                    <p>Whether you have a question, want to discuss a project, or just want to say hello, we're here to help.</p>

                    <div class="contact-methods">
                        <div class="contact-method">
                            <div class="contact-method-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div>
                                <h4>Our Location</h4>
                                <a href="https://www.google.com/maps/place/DataSphere+Digital+Solutions/@-3.4013604,36.7028116,17z/data=!4m14!1m7!3m6!1s0x1837056795399de5:0xedf1a79fcbbdc05!2sDataSphere+Digital+Solutions!8m2!3d-3.4013658!4d36.7053865!16s%2Fg%2F11zk1dvs78!3m5!1s0x1837056795399de5:0xedf1a79fcbbdc05!8m2!3d-3.4013658!4d36.7053865!16s%2Fg%2F11zk1dvs78?entry=ttu&g_ep=EgoyMDI2MDcxNS4wIKXMDSoASAFQAw%3D%3D" target="_blank" style="text-decoration: none;">Arusha, Tanzania</a>
                            </div>
                        </div>

                        <div class="contact-method">
                            <div class="contact-method-icon"><i class="fas fa-phone"></i></div>
                            <div>
                                <h4>Phone Number</h4>
                                <a href="tel:+255693038737">+255 693 038 737</a>
                            </div>
                        </div>

                        <div class="contact-method">
                            <div class="contact-method-icon"><i class="fas fa-envelope"></i></div>
                            <div>
                                <h4>Email Address</h4>
                                <a href="mailto:datasphereds@gmail.com">datasphereds@gmail.com</a>
                            </div>
                        </div>
                    </div>

                    <div class="contact-social">
                        <h4>Follow Us</h4>
                        <div class="footer-social" style="justify-content: flex-start;">
                            <a href="https://www.facebook.com/profile.php?id=61580372648018" class="social-link" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://www.linkedin.com/in/datasphere-digital-solutions-5a8103395/" class="social-link" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                            <a href="https://www.instagram.com/datas_phere/" class="social-link" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="contact-form-wrapper">
                    <h3>Send Us a Message</h3>
                    <form id="contactForm" action="php/handle_contact.php" method="POST" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="firstName">First Name *</label>
                                <input type="text" id="firstName" name="firstName" class="form-input" placeholder="John" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="lastName">Last Name *</label>
                                <input type="text" id="lastName" name="lastName" class="form-input" placeholder="Doe" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="email">Email Address *</label>
                                <input type="email" id="email" name="email" class="form-input" placeholder="john@example.com" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-input" placeholder="+1 (555) 000-0000">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="service">Required Service *</label>
                                <select id="service" name="service" class="form-input" style="background: var(--dark-800); color: white;" required>
                                    <option value="">-- Select Service --</option>
                                    <option value="Web & Mobile Systems">Web & Mobile Systems</option>
                                    <option value="Design & Branding">Design & Branding</option>
                                    <option value="AI & Data Analytics">AI & Data Analytics</option>
                                    <option value="IT Consultation">IT Consultation</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="budget">Estimated Budget</label>
                                <select id="budget" name="budget" class="form-input" style="background: var(--dark-800); color: white;">
                                    <option value="<$1,000">Under $1,000</option>
                                    <option value="$1,000 - $3,000" selected>$1,000 - $3,000</option>
                                    <option value="$3,000 - $10,000">$3,000 - $10,000</option>
                                    <option value="$10,000+">$10,000+</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="message">Project Overview / Message *</label>
                            <textarea id="message" name="message" class="form-textarea" placeholder="Tell us about your goals, target audience, and timeframe..." required></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="requirement_file">Attach Requirement File / RFP * (PDF, DOCX, PNG, JPG)</label>
                            <input type="file" id="requirement_file" name="requirement_file" class="form-input" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg" required style="padding: 8px;">
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                            <i class="fas fa-paper-plane"></i> Send Message & Requirement
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

<?php include 'php/includes/footer.php'; ?>
