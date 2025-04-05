<?php
require_once 'includes/news_functions.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$article = getArticleBySlug($slug);

if (!$article) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - NewsPortal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">NewsPortal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <form class="d-flex" action="search.php" method="GET">
                    <input class="form-control me-2" type="search" name="query" placeholder="Search news...">
                    <button class="btn btn-outline-light" type="submit">Search</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Article Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <article class="blog-post">
                    <h1 class="blog-post-title mb-3"><?php echo htmlspecialchars($article['title']); ?></h1>
                    <div class="blog-post-meta text-muted mb-4">
                        <span>By <?php echo htmlspecialchars($article['author']); ?></span>
                        <span class="mx-2">|</span>
                        <span><?php echo formatDate($article['date']); ?></span>
                        <span class="mx-2">|</span>
                        <span>Category: <?php echo htmlspecialchars($article['category']); ?></span>
                        <span class="mx-2">|</span>
                        <span><i class="far fa-eye"></i> <?php echo number_format($article['views']); ?> views</span>
                    </div>
                    <img src="<?php echo htmlspecialchars($article['image_url']); ?>" 
                         class="img-fluid rounded mb-4" 
                         alt="<?php echo htmlspecialchars($article['title']); ?>">
                    <div class="blog-post-content">
                        <?php echo nl2br(htmlspecialchars($article['content'])); ?>
                    </div>
                </article>
            </div>
            <div class="col-md-4">
                <!-- Sidebar -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Latest News</h5>
                        <div class="list-group list-group-flush">
                            <?php foreach (getLatestArticles(5) as $latestArticle): ?>
                            <a href="article.php?slug=<?php echo $latestArticle['slug']; ?>" 
                               class="list-group-item list-group-item-action">
                                <?php echo htmlspecialchars($latestArticle['title']); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Categories</h5>
                        <div class="list-group list-group-flush">
                            <?php foreach (getCategories() as $name => $description): ?>
                            <a href="category.php?cat=<?php echo strtolower($name); ?>" 
                               class="list-group-item list-group-item-action">
                                <?php echo htmlspecialchars($name); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light mt-5">
        <div class="container py-4">
            <div class="row">
                <div class="col-md-4">
                    <h5>About Us</h5>
                    <p>Your trusted source for the latest news and updates from around the world.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-light">Home</a></li>
                        <li><a href="categories.php" class="text-light">Categories</a></li>
                        <li><a href="about.php" class="text-light">About</a></li>
                        <li><a href="contact.php" class="text-light">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Follow Us</h5>
                    <div class="social-links">
                        <a href="#" class="text-light me-2"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-light me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light me-2"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center py-3 border-top border-secondary">
            <p class="mb-0">&copy; 2024 NewsPortal. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html> 