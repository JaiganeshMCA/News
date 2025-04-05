<?php
// RSS Feed URLs
$GLOBALS['feeds'] = [
    'general' => [
        'ev' => 'https://ecogears.in/category/electric-vehicles-india/feed/',
        'ev_news' => 'https://ecogears.in/category/ev-news-india/feed/',
        'ev_scooters' => 'https://ecogears.in/category/electric-scooters-india/feed/'
    ],
    'world' => [
        'toi' => 'https://timesofindia.indiatimes.com/rssfeeds/296589292.cms',
        'ht' => 'https://www.hindustantimes.com/feeds/rss/world-news/rssfeed.xml'
    ],
    'business' => [
        'toi' => 'https://timesofindia.indiatimes.com/rssfeeds/1898055.cms',
        'ht' => 'https://www.hindustantimes.com/feeds/rss/business/rssfeed.xml'
    ],
    'technology' => 'https://timesofindia.indiatimes.com/rssfeeds/66949542.cms',
    'sports' => [
        'toi' => 'https://timesofindia.indiatimes.com/rssfeeds/4719148.cms',
        'ht' => 'https://www.hindustantimes.com/feeds/rss/sports/rssfeed.xml'
    ]
];

// Pagination settings
define('ARTICLES_PER_PAGE', 12);

function fetchRssFeed($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        error_log('cURL Error: ' . curl_error($ch));
        curl_close($ch);
        return null;
    }
    
    curl_close($ch);
    
    // Parse XML
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($response);
    
    if ($xml === false) {
        error_log('Failed to parse RSS feed');
        return null;
    }
    
    return $xml;
}

function convertRssItemToArticle($item, $source = 'Ecogears') {
    $imageUrl = '';
    
    // Extract image for Ecogears feed
    if ($source === 'Ecogears') {
        // Try to get image from content:encoded with better pattern matching
        if (isset($item->children('content', true)->encoded)) {
            $content = (string)$item->children('content', true)->encoded;
            // Look for featured image first
            if (preg_match('/<img[^>]*class=["\']featured-image["\'][^>]*src=["\']([^"\']+)["\']/', $content, $matches)) {
                $imageUrl = $matches[1];
            }
            // If no featured image, look for the first image with wp-post-image class
            elseif (preg_match('/<img[^>]*class=["\'].*?wp-post-image.*?["\'][^>]*src=["\']([^"\']+)["\']/', $content, $matches)) {
                $imageUrl = $matches[1];
            }
            // If still no image, get the first image in the content
            elseif (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $content, $matches)) {
                $imageUrl = $matches[1];
            }
        }
        
        // Try media:content as fallback
        if (empty($imageUrl)) {
            $namespaces = $item->getNamespaces(true);
            if (isset($namespaces['media'])) {
                $media = $item->children($namespaces['media']);
                if (isset($media->content)) {
                    foreach ($media->content as $content) {
                        $attrs = $content->attributes();
                        if (isset($attrs['url']) && isset($attrs['medium']) && $attrs['medium'] == 'image') {
                            $imageUrl = (string)$attrs['url'];
                            break;
                        }
                    }
                    // If no image with medium attribute found, get the first media URL
                    if (empty($imageUrl) && isset($attrs['url'])) {
                        $imageUrl = (string)$attrs['url'];
                    }
                }
            }
        }
        
        // Try getting from enclosure with image type check
        if (empty($imageUrl) && isset($item->enclosure)) {
            $enclosure = $item->enclosure->attributes();
            if (isset($enclosure['type']) && strpos($enclosure['type'], 'image/') === 0) {
                $imageUrl = (string)$enclosure['url'];
            }
        }
        
        // Try getting image from description as last resort
        if (empty($imageUrl) && isset($item->description)) {
            $description = (string)$item->description;
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $description, $matches)) {
                $imageUrl = $matches[1];
            }
        }

        // Validate and clean image URL
        if (!empty($imageUrl)) {
            // Convert protocol-relative URLs to HTTPS
            if (strpos($imageUrl, '//') === 0) {
                $imageUrl = 'https:' . $imageUrl;
            }
            // Add https if protocol is missing
            elseif (strpos($imageUrl, 'http') !== 0) {
                $imageUrl = 'https://' . ltrim($imageUrl, '/');
            }
            
            // Verify if image exists and is accessible
            $headers = get_headers($imageUrl, 1);
            if (!$headers || strpos($headers[0], '200') === false || 
                (isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'image/') === false)) {
                $imageUrl = ''; // Reset if image is not accessible or not an image
            }
        }
    } else {
        // Existing image extraction logic for other sources
        if ($source === 'Times of India' || $source === 'News18') {
            // Try getting from description first for TOI and News18
            if (isset($item->description)) {
                $description = (string)$item->description;
                if (preg_match('/<img.+?src=[\'"](?P<src>.+?)[\'"].*?>/i', $description, $matches)) {
                    $imageUrl = $matches['src'];
                }
            }
            
            // Try getting from media:content
            if (empty($imageUrl)) {
                $namespaces = $item->getNamespaces(true);
                if (isset($namespaces['media'])) {
                    $media = $item->children($namespaces['media']);
                    if (isset($media->content)) {
                        foreach ($media->content as $content) {
                            $attrs = $content->attributes();
                            if (isset($attrs['url'])) {
                                $imageUrl = (string)$attrs['url'];
                                break;
                            }
                        }
                    }
                }
            }
            
            // For News18, try their specific image tag
            if (empty($imageUrl) && $source === 'News18' && isset($item->image)) {
                $imageUrl = (string)$item->image;
            }
        }
    }
    
    // Clean description
    $description = isset($item->description) ? (string)$item->description : '';
    
    // Handle CDATA sections
    if (strpos($description, '<![CDATA[') !== false) {
        $description = str_replace(['<![CDATA[', ']]>'], '', $description);
    }
    
    // Clean up HTML and remove images from description
    $description = strip_tags(preg_replace('/<img[^>]+\>/i', '', $description));
    
    // Trim description
    if (strlen($description) > 200) {
        $description = substr($description, 0, 197) . '...';
    }
    
    // Clean up title
    $title = (string)$item->title;
    if (strpos($title, '<![CDATA[') !== false) {
        $title = str_replace(['<![CDATA[', ']]>'], '', $title);
    }
    
    // Get author
    $author = '';
    if (isset($item->children('dc', true)->creator)) {
        $author = (string)$item->children('dc', true)->creator;
    }
    
    // Get categories
    $categories = [];
    if (isset($item->category)) {
        foreach ($item->category as $category) {
            $categories[] = (string)$category;
        }
    }
    
    return [
        'title' => $title,
        'description' => $description,
        'url' => (string)$item->link,
        'urlToImage' => $imageUrl,
        'publishedAt' => date('c', strtotime((string)$item->pubDate)),
        'source' => ['name' => $source],
        'author' => $author ?: $source,
        'categories' => $categories
    ];
}

function paginateArray($items, $page = 1, $perPage = ARTICLES_PER_PAGE) {
    $page = max(1, $page);
    $offset = ($page - 1) * $perPage;
    $totalItems = count($items);
    $totalPages = ceil($totalItems / $perPage);
    
    return [
        'items' => array_slice($items, $offset, $perPage),
        'current_page' => $page,
        'per_page' => $perPage,
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'has_more' => $page < $totalPages
    ];
}

function getLatestArticles($limit = 12, $page = 1) {
    $articles = [];
    
    // Fetch from all EV feeds
    foreach ($GLOBALS['feeds']['general'] as $type => $feedUrl) {
        $feed = fetchRssFeed($feedUrl);
        if ($feed) {
            foreach ($feed->channel->item as $item) {
                $article = convertRssItemToArticle($item, 'Ecogears');
                $text = $article['title'] . ' ' . $article['description'];
                $article['sentiment_score'] = analyzeSentiment($text);
                $article['sentiment_color'] = getSentimentColor($article['sentiment_score']);
                
                // Add category type to help differentiate the source
                $article['category_type'] = $type;
                
                // Add to articles array
                $articles[] = $article;
            }
        }
    }
    
    // Sort combined articles by date (newest first)
    usort($articles, function($a, $b) {
        return strtotime($b['publishedAt']) - strtotime($a['publishedAt']);
    });
    
    return paginateArray($articles, $page, $limit);
}

function getArticlesByCategory($category, $page = 1) {
    $articles = [];
    
    if ($category === 'business' || $category === 'world' || $category === 'sports') {
        // Fetch from Times of India
        $toiFeed = fetchRssFeed($GLOBALS['feeds'][$category]['toi']);
        if ($toiFeed) {
            foreach ($toiFeed->channel->item as $item) {
                $article = convertRssItemToArticle($item, 'Times of India');
                $text = $article['title'] . ' ' . $article['description'];
                $article['sentiment_score'] = analyzeSentiment($text);
                $article['sentiment_color'] = getSentimentColor($article['sentiment_score']);
                $articles[] = $article;
            }
        }
        
        // Fetch from Hindustan Times
        $htFeed = fetchRssFeed($GLOBALS['feeds'][$category]['ht']);
        if ($htFeed) {
            foreach ($htFeed->channel->item as $item) {
                $article = convertRssItemToArticle($item, 'Hindustan Times');
                $text = $article['title'] . ' ' . $article['description'];
                $article['sentiment_score'] = analyzeSentiment($text);
                $article['sentiment_color'] = getSentimentColor($article['sentiment_score']);
                $articles[] = $article;
            }
        }
        
        // Sort combined articles by date
        usort($articles, function($a, $b) {
            return strtotime($b['publishedAt']) - strtotime($a['publishedAt']);
        });
        
        return paginateArray($articles, $page);
    } else {
        // For other categories, use existing logic
        $feedUrl = '';
        switch ($category) {
            case 'technology':
                $feedUrl = $GLOBALS['feeds']['technology'];
                break;
            default:
                $feedUrl = $GLOBALS['feeds']['general']['ev']; // Default to EV feed
        }
        
        $feed = fetchRssFeed($feedUrl);
        if (!$feed) return ['items' => [], 'current_page' => 1, 'total_pages' => 0];
        
        foreach ($feed->channel->item as $item) {
            $article = convertRssItemToArticle($item);
            $text = $article['title'] . ' ' . $article['description'];
            $article['sentiment_score'] = analyzeSentiment($text);
            $article['sentiment_color'] = getSentimentColor($article['sentiment_score']);
            $articles[] = $article;
        }
        
        return paginateArray($articles, $page);
    }
}

function searchArticles($query, $page = 1) {
    $allArticles = [];
    
    // Search through all feeds
    foreach ($GLOBALS['feeds'] as $feed) {
        if (is_array($feed)) {
            // Handle nested feeds (like business category)
            foreach ($feed as $subFeed) {
                $feedData = fetchRssFeed($subFeed);
                if (!$feedData) continue;
                
                foreach ($feedData->channel->item as $item) {
                    $article = convertRssItemToArticle($item);
                    if (stripos($article['title'], $query) !== false || 
                        stripos($article['description'], $query) !== false) {
                        $text = $article['title'] . ' ' . $article['description'];
                        $article['sentiment_score'] = analyzeSentiment($text);
                        $article['sentiment_color'] = getSentimentColor($article['sentiment_score']);
                        $allArticles[] = $article;
                    }
                }
            }
        } else {
            $feedData = fetchRssFeed($feed);
            if (!$feedData) continue;
            
            foreach ($feedData->channel->item as $item) {
                $article = convertRssItemToArticle($item);
                if (stripos($article['title'], $query) !== false || 
                    stripos($article['description'], $query) !== false) {
                    $text = $article['title'] . ' ' . $article['description'];
                    $article['sentiment_score'] = analyzeSentiment($text);
                    $article['sentiment_color'] = getSentimentColor($article['sentiment_score']);
                    $allArticles[] = $article;
                }
            }
        }
    }
    
    // Sort by date
    usort($allArticles, function($a, $b) {
        return strtotime($b['publishedAt']) - strtotime($a['publishedAt']);
    });
    
    return paginateArray($allArticles, $page);
}

function getArticleBySlug($slug) {
    // Search through all feeds for the article
    foreach ($GLOBALS['feeds'] as $feed) {
        $feedData = fetchRssFeed($feed);
        if (!$feedData) continue;
        
        foreach ($feedData->channel->item as $item) {
            if (urlencode((string)$item->title) === $slug) {
                $article = convertRssItemToArticle($item);
                $text = $article['title'] . ' ' . $article['description'];
                $article['sentiment_score'] = analyzeSentiment($text);
                $article['sentiment_color'] = getSentimentColor($article['sentiment_score']);
                return $article;
            }
        }
    }
    
    return null;
}

function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('F j, Y');
}

function getCategories() {
    return [
        'general' => 'Latest Electric Vehicle News from India',
        'world' => 'World news and updates',
        'business' => 'Business and economy news',
        'technology' => 'Technology and gadgets news',
        'sports' => 'Sports news and updates'
    ];
}

function analyzeSentiment($text) {
    // Simple sentiment analysis based on positive/negative word counts
    $positiveWords = ['good', 'great', 'excellent', 'positive', 'success', 'win', 'winning', 'achievement', 'progress', 'improve'];
    $negativeWords = ['bad', 'poor', 'negative', 'fail', 'failure', 'loss', 'losing', 'problem', 'issue', 'decline'];
    
    $text = strtolower($text);
    $positiveCount = 0;
    $negativeCount = 0;
    
    foreach ($positiveWords as $word) {
        $positiveCount += substr_count($text, $word);
    }
    
    foreach ($negativeWords as $word) {
        $negativeCount += substr_count($text, $word);
    }
    
    $total = $positiveCount + $negativeCount;
    if ($total === 0) return 0;
    
    return round(($positiveCount - $negativeCount) / $total * 100);
}

function getSentimentColor($score) {
    if ($score > 30) return 'success';
    if ($score < -30) return 'danger';
    return 'warning';
}
?>