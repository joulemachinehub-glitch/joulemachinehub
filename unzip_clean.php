<?php
header("X-LiteSpeed-Purge: *");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header('Content-Type: text/plain; charset=utf-8');

$output = [];

// 1. Delete ALL old .zip files currently sitting in public_html FIRST
$deleted = 0;
foreach (glob(__DIR__ . '/*.zip') as $z) {
    if (@unlink($z)) {
        $deleted++;
    }
}
$output[] = "Deleted $deleted stale .zip files in public_html";

// 2. Remove stale subfolders if any
$subfolders = [
    __DIR__ . '/UPLOAD_TO_PUBLIC_HTML_FINAL',
    __DIR__ . '/joule-machine-hub-hostinger',
    __DIR__ . '/joule-machine-hub-php'
];
foreach ($subfolders as $sub) {
    if (is_dir($sub)) {
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sub, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $f) {
            if ($f->isDir()) {
                @rmdir($f->getRealPath());
            } else {
                @unlink($f->getRealPath());
            }
        }
        @rmdir($sub);
        $output[] = "Cleaned subfolder: " . basename($sub);
    }
}

// 3. Handle Direct ZIP File Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['zipfile']['tmp_name'])) {
    $uploadedTmp = $_FILES['zipfile']['tmp_name'];
    $zip = new ZipArchive();
    if ($zip->open($uploadedTmp) === true) {
        $extractedCount = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $entryName = str_replace('\\', '/', $stat['name']);
            if (empty($entryName) || substr($entryName, -1) === '/') {
                continue;
            }
            $target = __DIR__ . '/' . $entryName;
            @mkdir(dirname($target), 0755, true);
            $content = $zip->getFromIndex($i);
            if ($content !== false) {
                file_put_contents($target, $content);
                $extractedCount++;
            }
        }
        $zip->close();

        // Also clean any files in __DIR__ with literal backslashes created on Linux
        foreach (glob(__DIR__ . '/*') as $f) {
            if (is_file($f) && strpos(basename($f), '\\') !== false) {
                @unlink($f);
            }
        }

        $output[] = "Successfully unpacked $extractedCount files directly into public_html with normalized forward slashes";
    } else {
        $output[] = "ERROR: Failed to open uploaded ZIP archive";
    }
} else {
    $output[] = "No ZIP file posted; performed cleanup only.";
}

// 4. Purge OPcache
if (function_exists('opcache_reset')) {
    @opcache_reset();
    $output[] = "PHP OPcache purged and reset";
}

// 5. Invalidate timestamps on core files
$core = [
    'index.php',
    'includes/header.php',
    'includes/footer.php',
    'includes/machine_template.php',
    'country.php',
    'assets/js/main.js'
];
foreach ($core as $c) {
    $p = __DIR__ . '/' . $c;
    if (file_exists($p)) {
        @touch($p);
    }
}

// 6. Direct server inspection
$indexC = file_exists(__DIR__ . '/index.php') ? file_get_contents(__DIR__ . '/index.php') : '';
$footerC = file_exists(__DIR__ . '/includes/footer.php') ? file_get_contents(__DIR__ . '/includes/footer.php') : '';
$headerC = file_exists(__DIR__ . '/includes/header.php') ? file_get_contents(__DIR__ . '/includes/header.php') : '';

$hasNewMediatorFaq = strpos($indexC, 'online B2B industrial machinery portal that acts as a mediator') !== false;
$hasOldRigging = strpos($indexC, 'specialized industrial rigging contractors') !== false;
$hasEmail = strpos($footerC, 'info.machinejunction') !== false;
$hasPhone = strpos($footerC, '96543') !== false;
$hasNewCopyright = strpos($footerC, 'Joule Machine Hub. All rights reserved.') !== false;
$hasCompactLogo = strpos($headerC, 'lg:h-[56px]') !== false;

$output[] = "--- SERVER FILE VERIFICATION ---";
$output[] = "header.php has reduced logo (lg:h-[56px]): " . ($hasCompactLogo ? "YES (SUCCESS)" : "NO (FAIL)");
$output[] = "index.php has new mediator FAQ: " . ($hasNewMediatorFaq ? "YES (SUCCESS)" : "NO (FAIL)");
$output[] = "index.php has old rigging FAQ: " . ($hasOldRigging ? "YES (FAIL)" : "NO (CLEAN)");
$output[] = "footer.php has email: " . ($hasEmail ? "YES (FAIL)" : "NO (CLEAN)");
$output[] = "footer.php has phone: " . ($hasPhone ? "YES (FAIL)" : "NO (CLEAN)");
$output[] = "footer.php has new copyright: " . ($hasNewCopyright ? "YES (SUCCESS)" : "NO (FAIL)");

echo implode("\n", $output);
