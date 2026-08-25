<?php
require_once 'php/db_connect.php';

try {
    // Add Blog Posts Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `blog_posts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `category` varchar(100) DEFAULT NULL,
            `content` text NOT NULL,
            `summary` text DEFAULT NULL,
            `image_path` varchar(255) DEFAULT 'assets/images/service1.png',
            `author_id` int(11) DEFAULT NULL,
            `read_time` varchar(50) DEFAULT '5 min read',
            `status` enum('draft','published') DEFAULT 'draft',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`),
            KEY `author_id` (`author_id`),
            CONSTRAINT `blog_posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    // Add Portfolio Projects Table (Marketing/Case Studies)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `portfolio_showcase` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `category` varchar(100) DEFAULT NULL,
            `description` text NOT NULL,
            `challenge` text DEFAULT NULL,
            `solution` text DEFAULT NULL,
            `outcome` text DEFAULT NULL,
            `tech_stack` text DEFAULT NULL, -- Comma separated list
            `image_path` varchar(255) DEFAULT 'assets/images/service3.png',
            `client_name` varchar(255) DEFAULT NULL,
            `is_featured` tinyint(1) DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    // Add some initial data if empty
    $count = $pdo->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("
            INSERT INTO `blog_posts` (`title`, `slug`, `category`, `content`, `summary`, `read_time`, `status`, `author_id`) VALUES
            ('How AI is Scaling SMEs in East Africa', 'how-ai-scaling-smes', 'Technology', 'Full content here...', 'Exploring the digital shift in regional markets...', '5 min read', 'published', 2),
            ('The Mobile-First Mandate for 2026', 'mobile-first-mandate-2026', 'Development', 'Full content here...', 'Why responsive design is no longer enough...', '4 min read', 'published', 2)
        ");
    }

    $count = $pdo->query("SELECT COUNT(*) FROM portfolio_showcase")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("
            INSERT INTO `portfolio_showcase` (`title`, `slug`, `category`, `description`, `challenge`, `solution`, `outcome`, `tech_stack`, `client_name`, `is_featured`) VALUES
            ('TechMart E-Commerce', 'techmart-ecommerce', 'Web Development', 'High-volume retail engine...', 'Scaling SKUs...', 'Headless architecture...', '300% increase in signups...', 'React.js, Node.js, PostgreSQL', 'TechMart Solutions', 1),
            ('Zao Farm Identity', 'zaofarm-identity', 'Branding', 'Geometric Organic visual system...', 'Bridging High-Tech and Earth-Centric...', 'Visual language development...', '$2.5M Series A funding...', 'Strategy, UI/UX, Social Kit', 'Zao Farm', 1)
        ");
    }

    echo "Migration successful: blog_posts and portfolio_showcase tables created.";
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
