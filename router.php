<?php
// Local router for PHP built-in web server
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Dynamic XML Sitemap (Always auto-updated)
if ($uri === '/sitemap.xml') {
    require __DIR__ . '/sitemap-xml.php';
    return true;
}

// 301 Permanent Redirect /index.php or /index.html to / (GSC Canonical Fix)
if ($uri === '/index.php' || $uri === '/index.html') {
    $qs = !empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '';
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: /" . $qs);
    exit;
}

// Serve static assets directly if file exists
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// 1. Route /product/slug to product.php
if (preg_match('#^/product/([a-zA-Z0-9-]+)/?$#', $uri, $matches)) {
    $_GET['slug'] = $matches[1];
    require __DIR__ . '/product.php';
    return true;
}

// 2. Route /machine/slug to product.php
if (preg_match('#^/machine/([a-zA-Z0-9-]+)/?$#', $uri, $matches)) {
    $_GET['slug'] = $matches[1];
    require __DIR__ . '/product.php';
    return true;
}

// Master Category Directory Hub: /category or /categories
if (preg_match('#^/(category|categories)/?$#', $uri)) {
    require __DIR__ . '/categories.php';
    return true;
}

// 3. Route /category/slug to category.php
if (preg_match('#^/category/([a-zA-Z0-9-]+)/?$#', $uri, $matches)) {
    $_GET['slug'] = $matches[1];
    require __DIR__ . '/category.php';
    return true;
}

// Master About Us Hub: /about or /about-us
if (preg_match('#^/(about|about-us)/?$#', $uri)) {
    require __DIR__ . '/about.php';
    return true;
}

// Blog Directory Hub: /blog or /blogs
if (preg_match('#^/(blog|blogs)/?$#', $uri)) {
    require __DIR__ . '/blog.php';
    return true;
}

// Route /blog/slug to blog-post.php
if (preg_match('#^/blog/([a-zA-Z0-9-]+)/?$#', $uri, $matches)) {
    $_GET['slug'] = $matches[1];
    require __DIR__ . '/blog-post.php';
    return true;
}

// Route /manufacturer/slug to manufacturer.php
if (preg_match('#^/manufacturer/([a-zA-Z0-9-]+)/?$#', $uri, $matches)) {
    $_GET['manufacturer'] = $matches[1];
    require __DIR__ . '/manufacturer.php';
    return true;
}

// 4. Route /country/slug to country.php
if (preg_match('#^/country/([a-zA-Z0-9-]+)/?$#', $uri, $matches)) {
    $_GET['country'] = $matches[1];
    require __DIR__ . '/country.php';
    return true;
}

// 5. Route /city/slug to city.php
if (preg_match('#^/city/([a-zA-Z0-9-]+)/?$#', $uri, $matches)) {
    $_GET['city'] = $matches[1];
    require __DIR__ . '/city.php';
    return true;
}

// 5b. Route /dashboard and /dashboard/subpage
if (preg_match('#^/dashboard(?:/([a-zA-Z0-9_-]+))?/?$#', $uri, $matches)) {
    $subpage = !empty($matches[1]) ? $matches[1] : 'index';
    if (file_exists(__DIR__ . '/dashboard/' . $subpage . '.php')) {
        require __DIR__ . '/dashboard/' . $subpage . '.php';
        return true;
    }
}

// 6. Direct root slug routing (e.g. /used-chun-zu-cbf-35s-cold-bolt-former-header, /login, /dashboard)
if (preg_match('#^/([a-zA-Z0-9-]+)/?$#', $uri, $matches)) {
    $candidate = $matches[1];

    // Check direct php script match (e.g. /login -> login.php, /dashboard -> dashboard.php)
    if (file_exists(__DIR__ . '/' . $candidate . '.php')) {
        require __DIR__ . '/' . $candidate . '.php';
        return true;
    }

    require_once __DIR__ . '/data/machines.php';
    
    // Check machine keyword slug
    $m = findMachineBySlugOrId($candidate);
    if ($m) {
        $_GET['slug'] = $candidate;
        require __DIR__ . '/product.php';
        return true;
    }

    // Check category slug
    global $categoryMap;
    if (isset($categoryMap[$candidate])) {
        $_GET['slug'] = $candidate;
        require __DIR__ . '/category.php';
        return true;
    }

    // Check country slug
    global $countryHubs;
    $cleanCandidate = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $candidate));
    foreach ($countryHubs as $k => $hub) {
        $cleanK = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $k));
        $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $hub['name'] ?? ''));
        if ($cleanK === $cleanCandidate || $cleanName === $cleanCandidate || (isset($hub['code']) && strtolower($hub['code']) === strtolower($candidate))) {
            $_GET['country'] = $k;
            require __DIR__ . '/country.php';
            return true;
        }
    }

    // Check city slug
    global $cityHubs;
    if (isset($cityHubs[$candidate])) {
        $_GET['city'] = $candidate;
        require __DIR__ . '/city.php';
        return true;
    }
    if (!empty($cityHubs)) {
        $cleanCandidate = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $candidate));
        foreach ($cityHubs as $k => $c) {
            $cleanK = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $k));
            if ($cleanK === $cleanCandidate || strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $c['name'] ?? '')) === $cleanCandidate) {
                $_GET['city'] = $k;
                require __DIR__ . '/city.php';
                return true;
            }
        }
    }
}

// Default fallback for homepage
if ($uri === '/' || $uri === '/index.php') {
    require __DIR__ . '/index.php';
    return true;
}

// Any unmatched route serves custom 404 page
http_response_code(404);
require __DIR__ . '/404.php';
return true;
