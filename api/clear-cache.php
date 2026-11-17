<?php
// Joule Machine Hub - JSON Cache Purge API
header('Content-Type: application/json; charset=utf-8');

// 1. LiteSpeed Cache Purge Header
header("X-LiteSpeed-Purge: *");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$results = [];

// 2. Clear PHP OPcache
if (function_exists('opcache_reset')) {
    $results['opcache'] = @opcache_reset() ? 'purged' : 'restricted_or_ok';
} else {
    $results['opcache'] = 'not_available';
}

// 3. Clear APCu
if (function_exists('apcu_clear_cache')) {
    @apcu_clear_cache();
    $results['apcu'] = 'purged';
}

// 4. Touch style.css for asset version bump
$styleCss = __DIR__ . '/../assets/css/style.css';
if (file_exists($styleCss)) {
    @touch($styleCss);
    $results['assets'] = 'timestamp_bumped';
}

echo json_encode([
    'success' => true,
    'message' => 'Hostinger LiteSpeed and OPcache successfully purged! Browser cache busted.',
    'timestamp' => time(),
    'details' => $results
]);
