<?php
require_once 'includes/news_functions.php';

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$results = !empty($query) ? searchArticles($query) : [];
$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - The BNC News</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar bg-dark text-light py-2">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <small>Today's Paper | Subscribe | E-Paper</small>
                </div>
                <div class="col-md-6 text-end">
                    <small>Login | Sign Up</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="py-3 border-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h1 class="site-title">The BNC News</h1>
                </div>
                <div class="col-md-8">
                    <form class="d-flex" action="search.php" method="GET">
                        <input class="form-control me-2" type="search" name="query" placeholder="Search news..." value="<?php echo htmlspecialchars($query); ?>">
                        <button class="btn btn-outline-dark" type="submit">Search</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <?php foreach ($categories as $name => $description): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="category.php?cat=<?php echo $name; ?>">
                            <?php echo ucfirst($name); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Search Results -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <h1 class="mb-4">Search Results</h1>
                <?php if (empty($query)): ?>
                <div class="alert alert-info">Please enter a search term.</div>
                <?php elseif (empty($results)): ?>
                <div class="alert alert-info">No results found for "<?php echo htmlspecialchars($query); ?>".</div>
                <?php else: ?>
                <p class="mb-4">Found <?php echo count($results); ?> results for "<?php echo htmlspecialchars($query); ?>"</p>
                <div class="row">
                    <?php foreach ($results as $article): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <?php if ($article['urlToImage']): ?>
                            <img src="<?php echo htmlspecialchars($article['urlToImage']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($article['title']); ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="article.php?slug=<?php echo urlencode($article['title']); ?>" 
                                       class="text-dark text-decoration-none">
                                        <?php echo htmlspecialchars($article['title']); ?>
                                    </a>
                                </h5>
                                <p class="card-text">
                                    <?php echo htmlspecialchars($article['description'] ?? ''); ?>
                                </p>
                                <div class="article-meta">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($article['source']['name']); ?> | 
                                            <?php echo formatDate($article['publishedAt']); ?>
                                        </small>
                                        <span class="badge bg-<?php echo $article['sentiment_color']; ?>">
                                            Sentiment: <?php echo $article['sentiment_score']; ?>%
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="<?php echo htmlspecialchars($article['url']); ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           target="_blank">
                                            Read Full Article
                                        </a>
                                        <button class="btn btn-sm btn-outline-secondary share-btn" 
                                                data-title="<?php echo htmlspecialchars($article['title']); ?>"
                                                data-url="<?php echo htmlspecialchars($article['url']); ?>">
                                            <i class="fas fa-share-alt"></i> Share
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <!-- Categories -->
                <div class="card mb-4">
                    <div class="card-header bg-dark text-light">
                        <h5 class="card-title mb-0">Categories</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach ($categories as $name => $description): ?>
                        <a href="category.php?cat=<?php echo $name; ?>" 
                           class="list-group-item list-group-item-action">
                            <?php echo ucfirst($name); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-dark text-light">
                        <h5 class="card-title mb-0">Latest News</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach (getLatestArticles(5) as $latestArticle): ?>
                        <a href="article.php?slug=<?php echo urlencode($latestArticle['title']); ?>" 
                           class="list-group-item list-group-item-action">
                            <?php echo htmlspecialchars($latestArticle['title']); ?>
                            <small class="d-block text-muted">
                                Sentiment: <span class="badge bg-<?php echo $latestArticle['sentiment_color']; ?>">
                                    <?php echo $latestArticle['sentiment_score']; ?>%
                                </span>
                            </small>
                        </a>
                        <?php endforeach; ?>
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
            <p class="mb-0">&copy; 2024 The BNC News. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    <script>
    // Share functionality
    document.querySelectorAll('.share-btn').forEach(button => {
        button.addEventListener('click', function() {
            const title = this.dataset.title;
            const url = this.dataset.url;
            
            if (navigator.share) {
                navigator.share({
                    title: title,
                    url: url
                }).catch(console.error);
            } else {
                // Fallback for browsers that don't support Web Share API
                const tempInput = document.createElement('input');
                tempInput.value = url;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                alert('Link copied to clipboard!');
            }
        });
    });
    </script>
</body>
</html> 