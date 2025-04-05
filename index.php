<?php
require_once 'includes/news_functions.php';

$category = isset($_GET['cat']) ? $_GET['cat'] : 'general';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$categories = getCategories();

if (!array_key_exists($category, $categories)) {
    $category = 'general';
}

// Get articles for the selected category with pagination
$result = getArticlesByCategory($category, $page);
$articles = $result['items'];

// Get category description
$categoryDescription = isset($categories[$category]) ? $categories[$category] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($category); ?> News - The BNC News</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        .top-bar { 
            background-color: #1a1a1a; 
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .site-title { 
            font-size: 2rem; 
            font-weight: bold;
            font-family: serif;
        }
        .top-bar a {
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
            font-size: 0.85rem;
        }
        .top-bar a:hover {
            color: #ccc;
        }
        .category-slider {
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none;  /* Internet Explorer 10+ */
            padding: 10px 0;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .category-slider::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }
        .category-slider .nav-link {
            display: inline-block;
            padding: 8px 20px;
            color: #666;
            text-decoration: none;
            margin: 0 5px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        .category-slider .nav-link:hover {
            background: #e9ecef;
            color: #333;
        }
        .category-slider .nav-link.active {
            background: #800000;
            color: white;
        }
        .section-title { margin-bottom: 1.5rem; }
        .card { 
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .card-title {
            font-size: 1.1rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .card-text {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 0.9rem;
            color: #666;
        }
        .article-meta {
            font-size: 0.8rem;
            color: #888;
        }
        .article-meta i {
            width: 16px;
        }
        .badge {
            font-weight: 500;
        }
        .category-header {
            position: relative;
            margin-bottom: 2rem;
            border-bottom: 1px solid #dee2e6;
        }
        .category-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: #007bff;
        }
        .category-description {
            color: #800000;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }
        .btn-custom-gray {
            background-color: #696969;
            border-color: #696969;
            color: white;
        }
        .btn-custom-gray:hover {
            background-color: #595959;
            border-color: #595959;
            color: white;
        }
        .pagination .page-link {
            color: #800000;
            border-color: #dee2e6;
        }
        .pagination .page-link:hover {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: #600000;
        }
        .pagination .page-item.active .page-link {
            background-color: #800000;
            border-color: #800000;
            color: white;
        }
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
        }
    </style>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar text-light py-2">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <a href="#">Today's Paper</a> |
                    <a href="#">Subscribe</a> |
                    <a href="#">E-Paper</a>
                </div>
                <div class="col-md-6 text-end">
                    <a href="#">Login</a> |
                    <a href="#">Sign Up</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="py-3">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="index.php" class="text-decoration-none text-dark">
                    <h1 class="site-title mb-0">The BNC News</h1>
                </a>
                <div>
                    <h2 class="mb-0" style="font-size: 1.2rem;"><?php echo ucfirst($category); ?></h2>
                </div>
            </div>
        </div>
    </header>

    <!-- Category Slider -->
    <div class="category-slider">
        <div class="container">
            <nav class="nav">
                <?php foreach ($categories as $key => $name): ?>
                <a class="nav-link <?php echo $key === $category ? 'active' : ''; ?>" 
                   href="?cat=<?php echo $key; ?>">
                    <?php echo $name; ?>
                </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="category-header pb-2">
            <h2><?php echo ucfirst($category); ?> News</h2>
            <p class="category-description mb-0"><?php echo $categoryDescription; ?></p>
        </div>

        <?php if (empty($articles)): ?>
        <div class="alert alert-warning">
            <h4 class="alert-heading">No Articles Available</h4>
            <p>We're currently unable to fetch articles. Please try again later.</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($articles as $article): ?>
            <div class="col-md-4">
                <div class="card h-100">
                    <?php if (!empty($article['urlToImage'])): ?>
                    <img src="<?php echo htmlspecialchars($article['urlToImage']); ?>" 
                         class="card-img-top" 
                         alt="<?php echo htmlspecialchars($article['title']); ?>"
                         onerror="this.onerror=null; this.src='assets/images/placeholder.jpg';">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <a href="<?php echo htmlspecialchars($article['url']); ?>" 
                               class="text-dark text-decoration-none" 
                               target="_blank">
                                <?php echo htmlspecialchars($article['title']); ?>
                            </a>
                        </h5>
                        <p class="card-text text-muted mb-3">
                            <?php echo htmlspecialchars($article['description']); ?>
                        </p>
                        <div class="article-meta text-muted small mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-user me-2"></i>
                                <span><?php echo htmlspecialchars($article['author']); ?></span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-clock me-2"></i>
                                <span><?php echo formatDate($article['publishedAt']); ?></span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-newspaper me-2"></i>
                                <span><?php echo htmlspecialchars($article['source']['name']); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-<?php echo $article['sentiment_color']; ?>">
                                Sentiment: <?php echo $article['sentiment_score']; ?>%
                            </span>
                            <button class="btn btn-sm btn-outline-secondary share-btn" 
                                    data-title="<?php echo htmlspecialchars($article['title']); ?>"
                                    data-url="<?php echo htmlspecialchars($article['url']); ?>">
                                <i class="fas fa-share-alt"></i> Share
                            </button>
                        </div>
                        <a href="<?php echo htmlspecialchars($article['url']); ?>" 
                           class="btn btn-custom-gray w-100" 
                           target="_blank">
                            Read Full Article
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($result['total_pages'] > 1): ?>
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <?php if ($result['current_page'] > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?cat=<?php echo $category; ?>&page=<?php echo $result['current_page'] - 1; ?>">Previous</a>
                    </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $result['total_pages']; $i++): ?>
                    <li class="page-item <?php echo $i === $result['current_page'] ? 'active' : ''; ?>">
                        <a class="page-link" href="?cat=<?php echo $category; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($result['current_page'] < $result['total_pages']): ?>
                    <li class="page-item">
                        <a class="page-link" href="?cat=<?php echo $category; ?>&page=<?php echo $result['current_page'] + 1; ?>">Next</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
        <?php endif; ?>
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
    <script>
    // Share functionality
    document.querySelectorAll('.share-btn').forEach(button => {
        button.addEventListener('click', async function() {
            const title = this.dataset.title;
            const url = this.dataset.url;
            
            try {
                if (navigator.share) {
                    await navigator.share({
                        title: title,
                        url: url
                    });
                } else {
                    const tempInput = document.createElement('input');
                    tempInput.value = url;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand('copy');
                    document.body.removeChild(tempInput);
                    
                    // Show tooltip
                    this.setAttribute('data-original-text', this.innerHTML);
                    this.innerHTML = '<i class="fas fa-check"></i> Copied!';
                    setTimeout(() => {
                        this.innerHTML = this.getAttribute('data-original-text');
                    }, 2000);
                }
            } catch (err) {
                console.error('Error sharing:', err);
            }
        });
    });
    </script>
</body>
</html> 