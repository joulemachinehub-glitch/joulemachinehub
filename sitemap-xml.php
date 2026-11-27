<?php
// Dynamic XML Sitemap Generator - Joule Machine Hub
// Automatically updates whenever a new machine, category, or article is added.
if (!defined('JMH_SITEMAP_SILENT') && !headers_sent()) {
    header('Content-Type: application/xml; charset=utf-8');
    header('X-Robots-Tag: noindex, follow');
}

require_once __DIR__ . '/data/machines.php';
if (file_exists(__DIR__ . '/data/articles.php')) {
    require_once __DIR__ . '/data/articles.php';
}

$baseUrl = 'https://joulemachinehub.com';
$today = date('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

// Helper function to output an entry
if (!function_exists('sitemapUrl')) {
function sitemapUrl($loc, $priority = '0.8', $changefreq = 'weekly', $lastmod = null, $images = []) {
    global $today;
    $lm = $lastmod ?: $today;
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($loc, ENT_XML1, 'UTF-8') . "</loc>\n";
    echo "    <lastmod>$lm</lastmod>\n";
    echo "    <changefreq>$changefreq</changefreq>\n";
    echo "    <priority>$priority</priority>\n";
    if (!empty($images) && is_array($images)) {
        foreach ($images as $img) {
            $imgUrl = is_array($img) ? ($img['url'] ?? '') : (string)$img;
            $imgTitle = is_array($img) ? ($img['title'] ?? '') : '';
            if (!empty($imgUrl)) {
                echo "    <image:image>\n";
                echo "      <image:loc>" . htmlspecialchars($imgUrl, ENT_XML1, 'UTF-8') . "</image:loc>\n";
                if (!empty($imgTitle)) {
                    echo "      <image:title>" . htmlspecialchars($imgTitle, ENT_XML1, 'UTF-8') . "</image:title>\n";
                }
                echo "    </image:image>\n";
            }
        }
    }
    echo "  </url>\n";
}
}

// 1. Core Pages
sitemapUrl($baseUrl . '/', '1.0', 'daily');
sitemapUrl($baseUrl . '/about', '0.9', 'weekly');
sitemapUrl($baseUrl . '/category', '0.9', 'daily');
sitemapUrl($baseUrl . '/blog', '0.8', 'daily');
sitemapUrl($baseUrl . '/sitemap.php', '0.7', 'weekly');

// 2. Category Pages
global $categoryMap;
if (!empty($categoryMap)) {
    foreach ($categoryMap as $slug => $c) {
        sitemapUrl($baseUrl . '/category/' . $slug, '0.9', 'daily');
    }
} else {
    $uniqueCats = array_unique(array_filter(array_column($machines, 'category')));
    foreach ($uniqueCats as $uc) {
        sitemapUrl($baseUrl . '/' . getCategoryUrl($uc), '0.9', 'daily');
    }
}

// 3. Country Hubs
global $countryHubs;
if (!empty($countryHubs)) {
    foreach ($countryHubs as $k => $ch) {
        $cName = $ch['name'] ?? $k;
        sitemapUrl($baseUrl . '/' . ltrim(getCountryUrl($cName), '/'), '0.8', 'weekly');
    }
}

// 4. City Hubs
global $cityHubs;
if (!empty($cityHubs)) {
    foreach ($cityHubs as $k => $ci) {
        $cName = $ci['name'] ?? $k;
        sitemapUrl($baseUrl . '/' . ltrim(getCityUrl($cName), '/'), '0.7', 'weekly');
    }
}

// 4b. Manufacturer Hubs
$priorityManufacturers = ['komatsu', 'amada', 'dingli', 'haitian'];
foreach ($priorityManufacturers as $mfg) {
    sitemapUrl($baseUrl . '/manufacturer/' . $mfg, '0.8', 'weekly');
}

// 5. Machine Listings (Dynamic)
foreach ($machines as $m) {
    $mUrl = $baseUrl . '/' . ltrim(getMachineUrl($m), '/');
    $imgs = [];
    if (!empty($m['images']) && is_array($m['images'])) {
        foreach ($m['images'] as $imgRel) {
            $imgs[] = [
                'url' => $baseUrl . '/' . ltrim($imgRel, '/'),
                'title' => $m['title'] ?? 'Industrial Machine'
            ];
        }
    }
    sitemapUrl($mUrl, '0.9', 'weekly', null, $imgs);
}

// 6. Articles / Blog Posts
if (!empty($articles) && is_array($articles)) {
    foreach ($articles as $art) {
        $slug = $art['slug'] ?? '';
        if (!empty($slug)) {
            sitemapUrl($baseUrl . '/blog/' . $slug, '0.8', 'monthly');
        }
    }
}

echo '</urlset>' . "\n";
