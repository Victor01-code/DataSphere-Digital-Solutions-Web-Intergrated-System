<?php 
require_once 'php/db_connect.php';
$page_title = "Portfolio | DataSphere Digital Solutions";
$page_description = "Explore DataSphere's portfolio of successful projects - websites, apps, branding, and digital solutions delivered across Tanzania.";
$extra_css = '<link rel="stylesheet" href="css/pages.css">';
include 'php/includes/header.php'; 

// Fetch Portfolio Projects
$showcase_items = $pdo->query("SELECT title, category, image_path, description, challenge, solution, outcome, tech_stack, slug, is_featured, created_at FROM portfolio_showcase ORDER BY created_at DESC")->fetchAll();

// Fetch Completed Public DataSphere Projects
$public_projects = $pdo->query("SELECT title, type as category, image_url as image_path, description, 'Client digital transformation project powered by DataSphere.' as challenge, 'Custom engineered full-stack solution addressing core business requirements.' as solution, 'Successful delivery and deployment with 100% client satisfaction.' as outcome, 'PHP, MySQL, JavaScript, HTML5/CSS3' as tech_stack, CONCAT('ds-proj-', id) as slug, 1 as is_featured, created_at FROM projects WHERE status = 'completed' AND is_public = 1 ORDER BY created_at DESC")->fetchAll();

$portfolio_items = array_merge($showcase_items, $public_projects);
usort($portfolio_items, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Fetch Active Testimonials
$testimonials = $pdo->query("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY created_at DESC")->fetchAll();
?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php">Home</a>
                <span>/</span>
                <span>Portfolio</span>
            </div>
            <h1>Our Portfolio</h1>
            <p>Explore some of our best work and successful client projects.</p>
        </div>
    </section>

    <!-- Portfolio Section -->
    <section class="section" style="background: var(--dark-900); position: relative; overflow: hidden;">
        <div class="container" style="position: relative; z-index: 2;">
            <!-- Filter -->
            <div class="portfolio-filter" style="display: flex; justify-content: center; gap: var(--space-md); margin-bottom: var(--space-4xl); flex-wrap: wrap;">
                <button class="filter-btn active" onclick="filterPortfolio('all', this)" style="padding: 10px 25px; border-radius: var(--radius-full); border: 1px solid rgba(255,255,255,0.1); background: var(--dark-700); color: var(--gray-300); cursor: pointer; transition: all 0.3s ease;">All Projects</button>
                <button class="filter-btn" onclick="filterPortfolio('web', this)" style="padding: 10px 25px; border-radius: var(--radius-full); border: 1px solid rgba(255,255,255,0.1); background: var(--dark-700); color: var(--gray-300); cursor: pointer; transition: all 0.3s ease;">Web Development</button>
                <button class="filter-btn" onclick="filterPortfolio('branding', this)" style="padding: 10px 25px; border-radius: var(--radius-full); border: 1px solid rgba(255,255,255,0.1); background: var(--dark-700); color: var(--gray-300); cursor: pointer; transition: all 0.3s ease;">Branding</button>
                <button class="filter-btn" onclick="filterPortfolio('app', this)" style="padding: 10px 25px; border-radius: var(--radius-full); border: 1px solid rgba(255,255,255,0.1); background: var(--dark-700); color: var(--gray-300); cursor: pointer; transition: all 0.3s ease;">Mobile Apps</button>
            </div>

            <!-- Portfolio Grid -->
            <div id="portfolioGrid" class="portfolio-grid-premium" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: var(--space-2xl);">
                <?php if (empty($portfolio_items)): ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: var(--space-3xl); color: var(--gray-500);">
                        <i class="fas fa-briefcase" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.2;"></i>
                        <p>No projects showcased yet. We're working on amazing things!</p>
                    </div>
                <?php endif; ?>

                <?php foreach ($portfolio_items as $item): 
                    $category_class = 'web';
                    if (strpos(strtolower($item['category']), 'brand') !== false) $category_class = 'branding';
                    if (strpos(strtolower($item['category']), 'app') !== false) $category_class = 'app';
                ?>
                <div class="portfolio-item-premium" data-category="<?php echo $category_class; ?>" onclick="openProject('<?php echo $item['slug']; ?>')" style="position: relative; border-radius: var(--radius-2xl); overflow: hidden; height: 450px; cursor: pointer;">
                    <div class="portfolio-img-container" style="height: 100%; width: 100%; transition: transform 0.5s ease;">
                        <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" style="height: 100%; width: 100%; object-fit: cover;">
                    </div>
                    
                    <div class="portfolio-content-overlay" style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(10, 14, 23, 0.95) 0%, rgba(10, 14, 23, 0.4) 50%, transparent 100%); display: flex; flex-direction: column; justify-content: flex-end; padding: var(--space-2xl); transition: all 0.4s ease;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-md);">
                            <span style="background: <?php echo $category_class === 'web' ? 'var(--primary-blue)' : ($category_class === 'branding' ? 'var(--accent-purple)' : 'var(--accent-cyan)'); ?>; color: white; padding: 4px 12px; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 700; text-transform: uppercase;"><?php echo htmlspecialchars($item['category']); ?></span>
                            <?php if ($item['is_featured']): ?>
                                <span style="color: var(--accent-cyan); font-size: 0.8rem;"><i class="fas fa-star"></i> Featured</span>
                            <?php endif; ?>
                        </div>
                        <h3 style="font-size: 1.5rem; color: white; margin-bottom: var(--space-sm);"><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p style="color: var(--gray-400); font-size: 0.9rem; margin-bottom: var(--space-lg); opacity: 0.8;"><?php echo htmlspecialchars(substr($item['description'], 0, 80)); ?>...</p>
                        
                        <div class="portfolio-stats-mini" style="display: flex; gap: var(--space-lg); border-top: 1px solid rgba(255,255,255,0.1); padding-top: var(--space-md);">
                            <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--accent-cyan);"><i class="fas fa-microchip"></i> <span><?php echo htmlspecialchars(explode(',', $item['tech_stack'])[0]); ?></span></div>
                            <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--accent-green);"><i class="fas fa-check-circle"></i> <span>Delivered</span></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <style>
            .portfolio-item-premium:hover .portfolio-img-container { transform: scale(1.1); }
            .portfolio-item-premium:hover .portfolio-content-overlay { padding-bottom: var(--space-3xl); background: linear-gradient(to top, rgba(0, 102, 255, 0.9) 0%, rgba(10, 14, 23, 0.6) 70%, transparent 100%); }
            .filter-btn.active { background: var(--gradient-primary) !important; color: white !important; border: none !important; box-shadow: 0 10px 20px rgba(0, 102, 255, 0.3); }
            .filter-btn:hover:not(.active) { background: var(--dark-600) !important; color: white !important; }
            
            @keyframes modalIn {
                from { opacity: 0; transform: scale(0.9) translateY(30px); }
                to { opacity: 1; transform: scale(1) translateY(0); }
            }
        </style>
    </section>

    <!-- Project Details Modal -->
    <div id="projectModal" style="display: none; position: fixed; inset: 0; background: rgba(10, 14, 23, 0.95); backdrop-filter: blur(10px); z-index: 10001; align-items: center; justify-content: center; padding: var(--space-md);">
        <div style="background: var(--dark-800); border: 1px solid rgba(255,255,255,0.1); width: 100%; max-width: 900px; max-height: 90vh; border-radius: var(--radius-2xl); overflow-y: auto; position: relative; animation: modalIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);">
            <button onclick="closeProject()" style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.05); border: none; color: white; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; z-index: 10;"><i class="fas fa-times"></i></button>
            
            <div id="modalBody">
                <!-- Content injected via JS -->
            </div>
        </div>
    </div>

    <script>
        const projectData = {
            <?php foreach ($portfolio_items as $item): ?>
            '<?php echo $item['slug']; ?>': {
                title: "<?php echo addslashes($item['title']); ?>",
                category: "<?php echo htmlspecialchars($item['category']); ?>",
                tag: "<?php 
                    $cat = 'web';
                    if (strpos(strtolower($item['category']), 'brand') !== false) $cat = 'branding';
                    if (strpos(strtolower($item['category']), 'app') !== false) $cat = 'app';
                    echo $cat;
                ?>",
                image: "<?php echo $item['image_path']; ?>",
                description: "<?php echo addslashes($item['description']); ?>",
                challenge: "<?php echo addslashes($item['challenge']); ?>",
                solution: "<?php echo addslashes($item['solution']); ?>",
                outcome: "<?php echo addslashes($item['outcome']); ?>",
                tech: <?php echo json_encode(array_map('trim', explode(',', $item['tech_stack']))); ?>
            },
            <?php endforeach; ?>
        };

        function filterPortfolio(category, btn) {
            // Update buttons
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Filter items
            const items = document.querySelectorAll('.portfolio-item-premium');
            items.forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'block';
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0) scale(1)';
                    }, 10);
                } else {
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(20px) scale(0.95)';
                    setTimeout(() => {
                        item.style.display = 'none';
                    }, 300);
                }
            });
        }

        function openProject(id) {
            const data = projectData[id];
            if (!data) return;

            const modal = document.getElementById('projectModal');
            const body = document.getElementById('modalBody');

            body.innerHTML = `
                <div style="height: 300px; width: 100%; position: relative;">
                    <img src="${data.image}" style="width: 100%; height: 100%; object-fit: cover;">
                    <div style="position: absolute; inset: 0; background: linear-gradient(to top, var(--dark-800), transparent);"></div>
                </div>
                <div style="padding: var(--space-2xl);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-xl); flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <span style="color: var(--primary-blue-light); font-weight: 700; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;">${data.category}</span>
                            <h2 style="font-size: 2.5rem; color: white; margin-top: 5px;">${data.title}</h2>
                        </div>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            ${data.tech.map(t => `<span style="background: rgba(255,255,255,0.05); padding: 5px 15px; border-radius: var(--radius-full); font-size: 0.8rem; color: var(--gray-300);">${t}</span>`).join('')}
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-2xl);">
                        <div>
                            <h4 style="color: white; margin-bottom: 10px;">Overview</h4>
                            <p style="color: var(--gray-400); line-height: 1.6;">${data.description}</p>
                            
                            <h4 style="color: white; margin-top: var(--space-xl); margin-bottom: 10px;">The Challenge</h4>
                            <p style="color: var(--gray-400); line-height: 1.6;">${data.challenge}</p>
                        </div>
                        <div>
                            <h4 style="color: white; margin-bottom: 10px;">The Solution</h4>
                            <p style="color: var(--gray-400); line-height: 1.6;">${data.solution}</p>
                            
                            <div style="background: rgba(0, 102, 255, 0.1); border-left: 4px solid var(--primary-blue); padding: var(--space-lg); border-radius: var(--radius-lg); margin-top: var(--space-xl);">
                                <h4 style="color: var(--primary-blue-light); margin-bottom: 5px;">Key Outcome</h4>
                                <p style="color: white; font-weight: 500;">${data.outcome}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-top: var(--space-3xl); text-align: center;">
                        <a href="contact.php" class="btn btn-primary">Work with us on a similar project</a>
                    </div>
                </div>
            `;

            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeProject() {
            const modal = document.getElementById('projectModal');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('projectModal');
            if (event.target == modal) {
                closeProject();
            }
        }
    </script>

    <!-- Testimonials Section -->
    <section class="testimonials section" id="testimonials" style="background: var(--dark-800);">
        <div class="container">
            <div class="section-header">
                <span class="label">Client Stories</span>
                <h2>What Our Clients Say</h2>
                <p>Real feedback from businesses we've helped transform.</p>
            </div>

            <div class="testimonials-slider">
                <div class="testimonials-track" id="testimonialsTrack" style="display: flex; gap: var(--space-xl); transition: transform 0.5s ease;">
                    <?php foreach ($testimonials as $t): ?>
                    <div class="testimonial-card" style="min-width: 350px; background: var(--dark-800); padding: var(--space-2xl); border-radius: var(--radius-2xl); border: 1px solid rgba(255,255,255,0.05);">
                        <div style="font-size: 1.5rem; color: var(--primary-blue-light); margin-bottom: var(--space-lg);"><i class="fas fa-quote-left"></i></div>
                        <p style="color: var(--gray-300); line-height: 1.7; font-style: italic; margin-bottom: var(--space-xl);">"<?php echo htmlspecialchars($t['content']); ?>"</p>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="width: 50px; height: 50px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: white;">
                                <?php echo htmlspecialchars($t['author_avatar'] ?: substr($t['author_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <h4 style="margin: 0; color: white;"><?php echo htmlspecialchars($t['author_name']); ?></h4>
                                <p style="margin: 0; font-size: 0.85rem; color: var(--gray-400);"><?php echo htmlspecialchars($t['author_title']); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta section">
        <div class="cta-bg"></div>
        <div class="container">
            <div class="cta-content">
                <h2>Want to Be Our Next Success Story?</h2>
                <p>Let's create something amazing together. Get in touch to discuss your project.</p>
                <div class="cta-buttons">
                    <a href="contact.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-rocket"></i> Start Your Project
                    </a>
                    <a href="services.php" class="btn btn-secondary btn-lg">
                        View Our Services
                    </a>
                </div>
            </div>
        </div>
    </section>

<?php include 'php/includes/footer.php'; ?>
