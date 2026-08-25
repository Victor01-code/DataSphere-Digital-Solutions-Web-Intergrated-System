<?php 
$page_title = "Our Services | DataSphere Digital Solutions";
$page_description = "Explore DataSphere's comprehensive digital services - IT Consultation, Web Development, Branding, AI Solutions, Data Analytics, and more.";
$extra_css = '<link rel="stylesheet" href="css/pages.css">';
include 'php/includes/header.php'; 
?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php">Home</a>
                <span>/</span>
                <span>Services</span>
            </div>
            <h1>Our Services</h1>
            <p>Strategy-first digital solutions. Every service begins with consultation and flows from a guided strategy.</p>
        </div>
    </section>

    <!-- IT Consultation -->
    <section class="service-detail" id="it-consultation">
        <div class="container">
            <div class="service-detail-grid">
                <div class="service-detail-content">
                    <div class="service-badge-premium"><i class="fas fa-star"></i> Core Service</div>
                    <h2>IT Consultation</h2>
                    <p>Our consultation process is the foundation of everything we do. We don't just recommend tools; we help you build a technical roadmap that aligns with your long-term business goals.</p>
                    
                    <div class="service-meta">
                        <div class="meta-item"><i class="fas fa-clock"></i> Quick Turnaround</div>
                        <div class="meta-item"><i class="fas fa-shield-alt"></i> Security Focused</div>
                    </div>

                    <ul class="service-list">
                        <li><i class="fas fa-check"></i> <strong>Digital Transformation:</strong> Audit your current systems and build a roadmap for modernization.</li>
                        <li><i class="fas fa-check"></i> <strong>Cloud Migration:</strong> Seamless transition to AWS, Azure, or Google Cloud for better scalability.</li>
                        <li><i class="fas fa-check"></i> <strong>Cybersecurity Audit:</strong> Protect your data and customers with state-of-the-art security protocols.</li>
                        <li><i class="fas fa-check"></i> <strong>Product Strategy:</strong> Define your MVP and scaling milestones for new digital products.</li>
                    </ul>

                    <div class="pricing-card-mini">
                        <div class="mini-price-header">
                            <span>Individual Strategy Session (1hr)</span>
                            <strong>50,000 TZS</strong>
                        </div>
                        <p>One-on-one expert guidance to solve your immediate technical challenges.</p>
                    </div>

                    <a href="contact.php" class="btn btn-primary btn-lg">Book Strategy Session <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="service-visual">
                    <div class="visual-accent-glow"></div>
                    <img src="assets/images/service1.png" alt="IT Consultation" style="border-radius: var(--radius-2xl); box-shadow: 0 20px 40px rgba(0,0,0,0.4);">
                    <div class="floating-icon"><i class="fas fa-lightbulb"></i></div>
                </div>
            </div>
        </div>
    </section>

    <!-- How We Work Section (Interactive Flow) -->
    <section class="section" style="background: var(--dark-800); border-top: 1px solid rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.05); position: relative; overflow: hidden;">
        <div class="container">
            <div class="section-header" style="margin-bottom: var(--space-4xl);">
                <span class="label">Interactive Journey</span>
                <h2>The DataSphere Process</h2>
                <p>A proven, strategy-first methodology designed for precision and impact.</p>
            </div>

            <div class="interactive-process-flow" style="display: flex; justify-content: space-between; position: relative; gap: var(--space-lg); flex-wrap: wrap;">
                <!-- Connecting Line (Desktop) -->
                <div class="process-line" style="position: absolute; top: 40px; left: 50px; right: 50px; height: 2px; background: linear-gradient(to right, var(--primary-blue), var(--accent-purple), var(--accent-cyan), var(--accent-green)); opacity: 0.2; z-index: 1;"></div>

                <!-- Step 1 -->
                <div class="process-step-card" style="flex: 1; min-width: 200px; z-index: 2; position: relative; text-align: center;">
                    <div class="step-icon-wrapper" style="width: 80px; height: 80px; background: var(--dark-700); border: 2px solid var(--primary-blue); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-xl); transition: all 0.3s ease; cursor: pointer; box-shadow: 0 0 20px rgba(0, 102, 255, 0.2);">
                        <span style="font-family: 'Outfit'; font-weight: 800; font-size: 1.5rem; color: var(--primary-blue-light);">01</span>
                    </div>
                    <h4 style="margin-bottom: var(--space-sm); color: white;">Consult</h4>
                    <p style="font-size: 0.85rem; color: var(--gray-400); line-height: 1.5;">We listen, audit, and understand your unique business environment.</p>
                </div>

                <!-- Step 2 -->
                <div class="process-step-card" style="flex: 1; min-width: 200px; z-index: 2; position: relative; text-align: center;">
                    <div class="step-icon-wrapper" style="width: 80px; height: 80px; background: var(--dark-700); border: 2px solid var(--accent-purple); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-xl); transition: all 0.3s ease; cursor: pointer; box-shadow: 0 0 20px rgba(139, 92, 246, 0.2);">
                        <span style="font-family: 'Outfit'; font-weight: 800; font-size: 1.5rem; color: var(--accent-purple);">02</span>
                    </div>
                    <h4 style="margin-bottom: var(--space-sm); color: white;">Strategize</h4>
                    <p style="font-size: 0.85rem; color: var(--gray-400); line-height: 1.5;">Building a clear roadmap and selecting the right tech stack.</p>
                </div>

                <!-- Step 3 -->
                <div class="process-step-card" style="flex: 1; min-width: 200px; z-index: 2; position: relative; text-align: center;">
                    <div class="step-icon-wrapper" style="width: 80px; height: 80px; background: var(--dark-700); border: 2px solid var(--accent-cyan); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-xl); transition: all 0.3s ease; cursor: pointer; box-shadow: 0 0 20px rgba(6, 182, 212, 0.2);">
                        <span style="font-family: 'Outfit'; font-weight: 800; font-size: 1.5rem; color: var(--accent-cyan);">03</span>
                    </div>
                    <h4 style="margin-bottom: var(--space-sm); color: white;">Design</h4>
                    <p style="font-size: 0.85rem; color: var(--gray-400); line-height: 1.5;">Creating emotionally resonant and human-centered user experiences.</p>
                </div>

                <!-- Step 4 -->
                <div class="process-step-card" style="flex: 1; min-width: 200px; z-index: 2; position: relative; text-align: center;">
                    <div class="step-icon-wrapper" style="width: 80px; height: 80px; background: var(--dark-700); border: 2px solid #F59E0B; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-xl); transition: all 0.3s ease; cursor: pointer; box-shadow: 0 0 20px rgba(245, 158, 11, 0.2);">
                        <span style="font-family: 'Outfit'; font-weight: 800; font-size: 1.5rem; color: #FBBF24;">04</span>
                    </div>
                    <h4 style="margin-bottom: var(--space-sm); color: white;">Build</h4>
                    <p style="font-size: 0.85rem; color: var(--gray-400); line-height: 1.5;">Iterative, agile development with technical precision and security.</p>
                </div>

                <!-- Step 5 -->
                <div class="process-step-card" style="flex: 1; min-width: 200px; z-index: 2; position: relative; text-align: center;">
                    <div class="step-icon-wrapper" style="width: 80px; height: 80px; background: var(--dark-700); border: 2px solid var(--accent-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-xl); transition: all 0.3s ease; cursor: pointer; box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);">
                        <span style="font-family: 'Outfit'; font-weight: 800; font-size: 1.5rem; color: var(--accent-green);">05</span>
                    </div>
                    <h4 style="margin-bottom: var(--space-sm); color: white;">Launch</h4>
                    <p style="font-size: 0.85rem; color: var(--gray-400); line-height: 1.5;">Deployment, optimization, and ongoing strategic support.</p>
                </div>
            </div>
        </div>

        <style>
            .process-step-card:hover .step-icon-wrapper {
                transform: translateY(-10px) scale(1.1);
                background: var(--dark-600);
            }
            @media (max-width: 992px) {
                .process-line { display: none; }
                .interactive-process-flow { flex-direction: column; align-items: center; gap: var(--space-2xl); }
            }
        </style>
    </section>

    <!-- Design & Branding -->
    <section class="service-detail" id="design-branding">
        <div class="container">
            <div class="service-detail-grid reverse">
                <div class="service-detail-content">
                    <span class="label">Visual Excellence</span>
                    <h2>Design & Branding</h2>
                    <p>We create visual identities that match your business goals. Our designs are emotionally resonant—crafted to connect and inspire your target audience across all digital touchpoints.</p>

                    <ul class="service-list">
                        <li><i class="fas fa-check"></i> <strong>Identity Systems:</strong> Logos, typography, and color palettes that define your brand.</li>
                        <li><i class="fas fa-check"></i> <strong>Brand Guidelines:</strong> Ensuring consistency across social media, web, and print.</li>
                        <li><i class="fas fa-check"></i> <strong>UI/UX Design:</strong> High-fidelity prototypes that prioritize user flow and conversion.</li>
                        <li><i class="fas fa-check"></i> <strong>Social Media Assets:</strong> Professional templates for Instagram, LinkedIn, and more.</li>
                    </ul>

                    <a href="contact.php" class="btn btn-primary btn-lg">Start Your Brand Journey <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="service-visual">
                    <div class="visual-accent-glow purple"></div>
                    <img src="assets/images/service2.png" alt="Design & Branding" style="border-radius: var(--radius-2xl);">
                    <div class="floating-icon purple"><i class="fas fa-paint-brush"></i></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Programming & Systems -->
    <section class="service-detail" id="web-development" style="background: var(--dark-800);">
        <div class="container">
            <div class="service-detail-grid">
                <div class="service-detail-content">
                    <span class="label">Engineering</span>
                    <h2>Programming & Systems</h2>
                    <p>Modular, multilingual platforms built based on your operational needs. We specialize in building custom digital engines that handle complex tasks with simple interfaces.</p>

                    <ul class="service-list">
                        <li><i class="fas fa-check"></i> <strong>Custom SaaS:</strong> Full-featured software-as-a-service platforms built for scale.</li>
                        <li><i class="fas fa-check"></i> <strong>CRM/ERP Integration:</strong> Streamline your internal operations with custom-built tools.</li>
                        <li><i class="fas fa-check"></i> <strong>API Development:</strong> Connect your systems to the world with secure, robust APIs.</li>
                        <li><i class="fas fa-check"></i> <strong>Mobile Applications:</strong> Native and cross-platform apps for iOS and Android.</li>
                    </ul>

                    <a href="contact.php" class="btn btn-primary btn-lg">Build Your Custom System <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="service-visual">
                    <div class="visual-accent-glow cyan"></div>
                    <img src="assets/images/service3.png" alt="Programming & Systems" style="border-radius: var(--radius-2xl);">
                    <div class="floating-icon cyan"><i class="fas fa-code"></i></div>
                </div>
            </div>
        </div>
    </section>

    <!-- AI Studio & Data Analytics -->
    <section class="service-detail" id="ai-data">
        <div class="container">
            <div class="service-detail-grid reverse">
                <div class="service-detail-content">
                    <span class="label">Future-Proofing</span>
                    <h2>AI Studio & Data Analytics</h2>
                    <p>Leverage the power of artificial intelligence to automate your business and turn data into actionable intelligence. We help you work smarter, not harder.</p>

                    <ul class="service-list">
                        <li><i class="fas fa-check"></i> <strong>Process Automation:</strong> Use AI to handle repetitive tasks and save your team thousands of hours.</li>
                        <li><i class="fas fa-check"></i> <strong>Intelligent Chatbots:</strong> 24/7 customer support driven by advanced NLP models.</li>
                        <li><i class="fas fa-check"></i> <strong>Data Visualization:</strong> Interactive dashboards that make sense of your business metrics.</li>
                        <li><i class="fas fa-check"></i> <strong>Predictive Analytics:</strong> Anticipate market trends and customer behavior using ML.</li>
                    </ul>

                    <a href="contact.php" class="btn btn-primary btn-lg">Leverage AI Power <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="service-visual">
                    <div class="visual-accent-glow green"></div>
                    <img src="assets/images/service4.png" alt="AI Studio" style="border-radius: var(--radius-2xl);">
                    <div class="floating-icon green"><i class="fas fa-brain"></i></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive Project Cost & Timeline Estimator (Global Acquisition Tool) -->
    <section class="section" id="estimator" style="background: var(--dark-800); border-top: 1px solid rgba(255,255,255,0.05); padding: var(--space-4xl) 0;">
        <div class="container">
            <div class="section-header">
                <span class="label">Instant Quote Calculator</span>
                <h2>Interactive Project Estimator</h2>
                <p>Select your requirements to receive an instant estimate & timeline prediction for your project.</p>
            </div>

            <div style="background: var(--dark-700); border-radius: var(--radius-2xl); padding: var(--space-3xl); border: 1px solid rgba(255,255,255,0.08); max-width: 900px; margin: 0 auto; box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: var(--space-2xl);">
                    <div>
                        <label style="color: var(--gray-300); font-weight: 600; display: block; margin-bottom: var(--space-sm);">1. Primary Service</label>
                        <select id="estService" onchange="calculateProjectEstimate()" class="form-input" style="background: var(--dark-800); color: white;">
                            <option value="500|3 days">Landing Page / Business Web (Base $500)</option>
                            <option value="1500|2 weeks" selected>Custom Web & SaaS Platform (Base $1,500)</option>
                            <option value="2500|3 weeks">Full Mobile App (iOS & Android) (Base $2,500)</option>
                            <option value="800|1 week">Brand Identity & Design System (Base $800)</option>
                            <option value="1200|2 weeks">AI Integration & Automation (Base $1,200)</option>
                        </select>
                    </div>

                    <div>
                        <label style="color: var(--gray-300); font-weight: 600; display: block; margin-bottom: var(--space-sm);">2. Project Scope & Complexity</label>
                        <select id="estScope" onchange="calculateProjectEstimate()" class="form-input" style="background: var(--dark-800); color: white;">
                            <option value="1">Standard (Single region, core features)</option>
                            <option value="1.4" selected>Advanced (Multi-language, payment gateways)</option>
                            <option value="2">Enterprise (Custom AI models, heavy traffic scale)</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top: var(--space-2xl); background: rgba(0, 102, 255, 0.08); border: 1px dashed rgba(0, 102, 255, 0.3); border-radius: var(--radius-xl); padding: var(--space-xl); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--space-lg);">
                    <div>
                        <span style="font-size: 0.85rem; color: var(--gray-400); text-transform: uppercase; letter-spacing: 1px;">Estimated Investment</span>
                        <div style="font-size: 2.25rem; font-weight: 800; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            <span id="estPriceDisplay" data-price-usd="2100">$2,100</span>
                        </div>
                    </div>
                    <div>
                        <span style="font-size: 0.85rem; color: var(--gray-400); text-transform: uppercase; letter-spacing: 1px;">Estimated Delivery</span>
                        <div style="font-size: 1.5rem; font-weight: 700; color: white;" id="estTimelineDisplay">~ 2.5 Weeks</div>
                    </div>
                    <a href="contact.php" class="btn btn-primary btn-md"><i class="fas fa-rocket"></i> Lock In Quote</a>
                </div>
            </div>
        </div>
    </section>

    <script>
    function calculateProjectEstimate() {
        const serviceSelect = document.getElementById('estService');
        const scopeSelect = document.getElementById('estScope');
        if (!serviceSelect || !scopeSelect) return;

        const [basePriceStr, timelineStr] = serviceSelect.value.split('|');
        const basePrice = parseFloat(basePriceStr);
        const multiplier = parseFloat(scopeSelect.value);

        const totalUSD = Math.round(basePrice * multiplier);
        
        const priceDisplay = document.getElementById('estPriceDisplay');
        if (priceDisplay) {
            priceDisplay.setAttribute('data-price-usd', totalUSD);
            if (typeof changeGlobalCurrency === 'function') {
                const currentCurrency = localStorage.getItem('datasphere_currency') || 'USD';
                const rateData = CURRENCY_RATES[currentCurrency] || CURRENCY_RATES.USD;
                const converted = Math.round(totalUSD * rateData.rate);
                priceDisplay.textContent = rateData.symbol + converted.toLocaleString();
            } else {
                priceDisplay.textContent = '$' + totalUSD.toLocaleString();
            }
        }

        const timelineDisplay = document.getElementById('estTimelineDisplay');
        if (timelineDisplay) {
            timelineDisplay.textContent = multiplier > 1.5 ? timelineStr + ' +' : timelineStr;
        }
    }
    </script>

    <!-- Testimonials Section -->
    <section class="testimonials section" id="testimonials">
        <div class="container">
            <div class="section-header">
                <span class="label">Testimonials</span>
                <h2>What Our Clients Say</h2>
                <p>Don't just take our word for it. Here's what our valued clients have to say about working with us.</p>
            </div>

            <div class="testimonials-slider">
                <div class="testimonials-track" id="testimonialsTrack">
                    <!-- Dynamic Testimonials will be loaded here via JS -->
                </div>
                <div class="testimonial-nav"></div>
            </div>
        </div>
    </section>

<?php include 'php/includes/footer.php'; ?>
