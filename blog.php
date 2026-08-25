<?php 
require_once 'php/db_connect.php';
$page_title = "Blog & Insights | DataSphere Digital Solutions";
$page_description = "DataSphere Blog - Digital insights, tech tips, success stories, and industry trends to help your business thrive.";
$extra_css = '<link rel="stylesheet" href="css/pages.css">';
include 'php/includes/header.php'; 

// Fetch Published Posts
$posts = $pdo->query("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC")->fetchAll();
?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php">Home</a>
                <span>/</span>
                <span>Blog</span>
            </div>
            <h1>Blog & Insights</h1>
            <p>Expert articles, digital trends, and tips to help your business grow.</p>
        </div>
    </section>

    <!-- Blog Section -->
    <section class="section" style="background: var(--dark-900); position: relative; overflow: hidden;">
        <div class="container" style="position: relative; z-index: 2;">
            <div class="blog-grid-premium" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: var(--space-2xl);">
                <?php if (empty($posts)): ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: var(--space-3xl); color: var(--gray-500);">
                        <i class="fas fa-newspaper" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.2;"></i>
                        <p>No articles published yet. Check back soon!</p>
                    </div>
                <?php endif; ?>

                <?php foreach ($posts as $post): ?>
                <article class="blog-card-interactive" onclick="openArticle('<?php echo $post['slug']; ?>')" style="background: var(--dark-800); border: 1px solid rgba(255,255,255,0.05); border-radius: var(--radius-2xl); overflow: hidden; transition: all 0.4s ease; cursor: pointer; display: flex; flex-direction: column;">
                    <div class="blog-visual-premium" style="position: relative; height: 240px; overflow: hidden;">
                        <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;">
                        <span class="blog-tag-premium" style="position: absolute; top: 20px; right: 20px; background: var(--primary-blue); color: white; padding: 4px 15px; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; z-index: 2;"><?php echo htmlspecialchars($post['category']); ?></span>
                    </div>
                    <div class="blog-body-premium" style="padding: var(--space-2xl); flex: 1; display: flex; flex-direction: column;">
                        <div class="blog-meta-premium" style="display: flex; gap: var(--space-lg); color: var(--gray-400); font-size: 0.8rem; margin-bottom: var(--space-md);">
                            <span><i class="far fa-calendar" style="color: var(--primary-blue-light); margin-right: 5px;"></i> <?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                            <span><i class="far fa-clock" style="color: var(--accent-cyan); margin-right: 5px;"></i> <?php echo htmlspecialchars($post['read_time']); ?></span>
                        </div>
                        <h3 style="font-size: 1.35rem; color: white; margin-bottom: var(--space-md); line-height: 1.4; transition: color 0.3s ease;"><?php echo htmlspecialchars($post['title']); ?></h3>
                        <p style="color: var(--gray-400); font-size: 0.9rem; line-height: 1.6; margin-bottom: var(--space-xl); opacity: 0.8; flex: 1;"><?php echo htmlspecialchars($post['summary']); ?></p>
                        
                        <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid rgba(255,255,255,0.05); padding-top: var(--space-lg);">
                            <span class="read-more-link" style="color: var(--primary-blue-light); font-weight: 600; font-size: 0.85rem; text-decoration: none; display: flex; align-items: center; gap: 8px;">
                                Full Article <i class="fas fa-arrow-right" style="font-size: 0.75rem;"></i>
                            </span>
                            <div style="display: flex; gap: 15px; color: var(--gray-500); font-size: 1rem;">
                                <i class="far fa-heart" onclick="handleLike(event, this)" title="Like"></i>
                                <i class="far fa-share-square" onclick="handleShare(event, '<?php echo addslashes($post['title']); ?>')" title="Share"></i>
                            </div>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>

        <style>
            .blog-card-interactive:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.4); border-color: rgba(255,255,255,0.1) !important; }
            .blog-card-interactive:hover img { transform: scale(1.1); }
            .blog-card-interactive:hover h3 { color: var(--primary-blue-light) !important; }
            .far.fa-heart.active { color: #EF4444; font-weight: 900; }
            
            @keyframes articleModalIn {
                from { opacity: 0; transform: translateY(50px); }
                to { opacity: 1; transform: translateY(0); }
            }
        </style>
    </section>

    <!-- Article Modal -->
    <div id="articleModal" style="display: none; position: fixed; inset: 0; background: rgba(10, 14, 23, 0.98); backdrop-filter: blur(15px); z-index: 10001; overflow-y: auto; padding: var(--space-xl) var(--space-md);">
        <div style="background: var(--dark-800); border: 1px solid rgba(255,255,255,0.05); width: 100%; max-width: 800px; margin: 0 auto; border-radius: var(--radius-2xl); position: relative; animation: articleModalIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);">
            <button onclick="closeArticle()" style="position: sticky; top: 20px; float: right; margin-right: 20px; background: rgba(255,255,255,0.05); border: none; color: white; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; z-index: 10;"><i class="fas fa-times"></i></button>
            
            <div id="articleContent">
                <!-- Content injected via JS -->
            </div>
        </div>
    </div>

    <script>
        const blogArticles = {
            <?php foreach ($posts as $post): ?>
            '<?php echo $post['slug']; ?>': {
                title: "<?php echo addslashes($post['title']); ?>",
                date: "<?php echo date('M d, Y', strtotime($post['created_at'])); ?>",
                readTime: "<?php echo $post['read_time']; ?>",
                category: "<?php echo $post['category']; ?>",
                image: "<?php echo $post['image_path']; ?>",
                content: `<?php echo str_replace("`", "\`", $post['content']); ?>`
            },
            <?php endforeach; ?>
        };

        function openArticle(id) {
            const article = blogArticles[id];
            if (!article) return;

            const modal = document.getElementById('articleModal');
            const content = document.getElementById('articleContent');

            content.innerHTML = `
                <div style="padding: var(--space-3xl);">
                    <span style="background: var(--primary-blue); color: white; padding: 4px 15px; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">${article.category}</span>
                    <h2 style="font-size: 2.5rem; color: white; margin: var(--space-lg) 0; line-height: 1.2;">${article.title}</h2>
                    
                    <div style="display: flex; gap: var(--space-xl); color: var(--gray-400); font-size: 0.9rem; margin-bottom: var(--space-2xl);">
                        <span><i class="far fa-calendar" style="color: var(--primary-blue-light); margin-right: 8px;"></i>${article.date}</span>
                        <span><i class="far fa-clock" style="color: var(--accent-cyan); margin-right: 8px;"></i>${article.readTime}</span>
                        <span><i class="far fa-user" style="color: var(--accent-purple); margin-right: 8px;"></i>DataSphere Team</span>
                    </div>

                    <img src="${article.image}" style="width: 100%; border-radius: var(--radius-xl); margin-bottom: var(--space-2xl); border: 1px solid rgba(255,255,255,0.05);">
                    
                    <div class="article-rich-text" style="color: var(--gray-300); line-height: 1.8; font-size: 1.1rem;">
                        ${article.content}
                    </div>

                    <div style="margin-top: var(--space-3xl); padding-top: var(--space-2xl); border-top: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                        <div style="display: flex; gap: var(--space-xl);">
                            <button onclick="handleLike(event, this)" style="background: none; border: none; color: var(--gray-400); cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 1rem; transition: all 0.3s ease;">
                                <i class="far fa-heart"></i> <span>Like Article</span>
                            </button>
                            <button onclick="handleShare(event, '${article.title}')" style="background: none; border: none; color: var(--gray-400); cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 1rem; transition: all 0.3s ease;">
                                <i class="far fa-share-square"></i> <span>Share</span>
                            </button>
                        </div>
                        <a href="contact.php" class="btn btn-primary">Discuss this Topic</a>
                    </div>
                </div>
            `;

            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeArticle() {
            const modal = document.getElementById('articleModal');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        function handleLike(event, el) {
            event.stopPropagation();
            const icon = el.tagName === 'I' ? el : el.querySelector('i');
            const span = el.tagName === 'BUTTON' ? el.querySelector('span') : null;
            
            icon.classList.toggle('active');
            icon.classList.toggle('far');
            icon.classList.toggle('fas');
            
            if (icon.classList.contains('active')) {
                showNotification('Article added to your favorites!', 'success');
                if (span) span.textContent = 'Liked';
            } else {
                if (span) span.textContent = 'Like Article';
            }
        }

        function handleShare(event, title) {
            event.stopPropagation();
            if (navigator.share) {
                navigator.share({
                    title: title,
                    text: 'Check out this article from DataSphere Digital Solutions',
                    url: window.location.href
                }).catch(err => console.log('Error sharing:', err));
            } else {
                // Fallback
                const dummy = document.createElement('input');
                document.body.appendChild(dummy);
                dummy.value = window.location.href;
                dummy.select();
                document.execCommand('copy');
                document.body.removeChild(dummy);
                showNotification('Link copied to clipboard!', 'success');
            }
        }

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('articleModal');
            if (event.target == modal) {
                closeArticle();
            }
        }
    </script>

            <!-- Pagination -->
            <div class="pagination" style="display: flex; justify-content: center; gap: var(--space-sm); margin-top: var(--space-3xl);">
                <a href="#" class="btn btn-secondary btn-sm" style="min-width: 44px;"><i class="fas fa-chevron-left"></i></a>
                <a href="#" class="btn btn-primary btn-sm" style="min-width: 44px;">1</a>
                <a href="#" class="btn btn-secondary btn-sm" style="min-width: 44px;">2</a>
                <a href="#" class="btn btn-secondary btn-sm" style="min-width: 44px;"><i class="fas fa-chevron-right"></i></a>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="section" style="background: var(--dark-800);">
        <div class="container">
            <div class="newsletter">
                <h3>Subscribe to Our Newsletter</h3>
                <p>Get the latest digital insights, tips, and industry updates delivered to your inbox.</p>
                <form class="newsletter-form" id="newsletterForm">
                    <input type="email" class="form-input" placeholder="Enter your email address" required>
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </form>
            </div>
        </div>
    </section>

<?php include 'php/includes/footer.php'; ?>
