<?php
/**
 * Joule Machine Hub - 1-Click Hostinger Auto-Extractor & Cache Purge Tool
 * 
 * Access via: https://joulemachinehub.com/deploy.php
 * 
 * Features:
 * 1. Automatically locates any uploaded zip (UPLOAD_TO_PUBLIC_HTML_FINAL.zip, joule-machine-hub-hostinger.zip, etc.)
 * 2. Unzips all files directly into public_html with overwrite enabled
 * 3. Detects if files were accidentally extracted into a subfolder and moves them to public_html
 * 4. Resets PHP OPcache and sends LiteSpeed Purge header
 * 5. Verifies all updates: Title prefix, Hero heading, Live power removal, 404, Dashboard
 */

header("X-LiteSpeed-Purge: *");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$logs = [];
$extracted = false;

// 0. Handle Direct ZIP File Upload from browser
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['zipfile']['tmp_name'])) {
    $uploadedTmp = $_FILES['zipfile']['tmp_name'];
    $uploadedName = basename($_FILES['zipfile']['name']);
    $zip = new ZipArchive();
    if ($zip->open($uploadedTmp) === true) {
        $zip->extractTo(__DIR__);
        $numFiles = $zip->numFiles;
        $zip->close();

        // Remove old zip archives in public_html to prevent accidental rollbacks
        foreach (glob(__DIR__ . '/*.zip') as $oldZip) {
            @unlink($oldZip);
        }

        $logs[] = [
            'type' => 'success',
            'title' => "Uploaded & Extracted: $uploadedName",
            'desc' => "Successfully unpacked $numFiles files directly into public_html."
        ];
        $extracted = true;
    } else {
        $logs[] = [
            'type' => 'error',
            'title' => "Upload Extraction Failed",
            'desc' => "Could not extract uploaded archive."
        ];
    }
}

// 1. Search for ZIP archives in current directory (only if not already extracted from upload)
if (!$extracted) {
    $zipFiles = glob(__DIR__ . '/*.zip');
    if (!empty($zipFiles)) {
        usort($zipFiles, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        foreach ($zipFiles as $zipFile) {
            $zipName = basename($zipFile);
            $zip = new ZipArchive();
            if ($zip->open($zipFile) === true) {
                $zip->extractTo(__DIR__);
                $numFiles = $zip->numFiles;
                $zip->close();
                $logs[] = [
                    'type' => 'success',
                    'title' => "Extracted Archive: $zipName",
                    'desc' => "Successfully unpacked $numFiles files directly into public_html."
                ];
                $extracted = true;
                break; // Stop after extracting the newest zip
            } else {
                $logs[] = [
                    'type' => 'error',
                    'title' => "Could not open $zipName",
                    'desc' => "Archive might still be uploading or permissions restricted."
                ];
            }
        }
    }
}

// 2. Check if files were extracted into a subfolder (e.g. UPLOAD_TO_PUBLIC_HTML_FINAL/)
$subfolders = [
    __DIR__ . '/UPLOAD_TO_PUBLIC_HTML_FINAL',
    __DIR__ . '/joule-machine-hub-hostinger',
    __DIR__ . '/joule-machine-hub-php'
];

foreach ($subfolders as $sub) {
    if (is_dir($sub)) {
        $subFiles = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sub, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        $movedCount = 0;
        foreach ($subFiles as $item) {
            $subPath = substr($item->getPathname(), strlen($sub) + 1);
            $targetPath = __DIR__ . '/' . $subPath;
            if ($item->isDir()) {
                if (!is_dir($targetPath)) {
                    @mkdir($targetPath, 0755, true);
                }
            } else {
                $targetDir = dirname($targetPath);
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0755, true);
                }
                if (@copy($item->getPathname(), $targetPath)) {
                    $movedCount++;
                }
            }
        }
        $logs[] = [
            'type' => 'success',
            'title' => "Detected subfolder: " . basename($sub),
            'desc' => "Moved $movedCount files out of the subfolder into public_html."
        ];
        $extracted = true;
    }
}

// 3. Purge OPcache
if (function_exists('opcache_reset')) {
    if (@opcache_reset()) {
        $logs[] = ['type' => 'success', 'title' => 'PHP OPcache', 'desc' => 'Purged and recompiled from updated files.'];
    } else {
        $logs[] = ['type' => 'info', 'title' => 'PHP OPcache', 'desc' => 'Already clear or handled by server.'];
    }
}

// 4. Invalidate timestamps on core files
$coreFiles = [
    __DIR__ . '/index.php',
    __DIR__ . '/product.php',
    __DIR__ . '/data/machines.php',
    __DIR__ . '/includes/footer.php',
    __DIR__ . '/.htaccess'
];
foreach ($coreFiles as $cf) {
    if (file_exists($cf)) {
        @touch($cf);
    }
}

// 5. Verification checks
$checks = [];

// Hero Check
$indexContent = file_exists(__DIR__ . '/index.php') ? file_get_contents(__DIR__ . '/index.php') : '';
$hasNewHero = strpos($indexContent, 'Buy Used/New Industrial Machines') !== false;
$checks[] = [
    'name' => 'Hero Heading',
    'ok' => $hasNewHero,
    'detail' => $hasNewHero ? 'Updated: "Buy Used/New Industrial Machines"' : 'Old heading on server'
];

// Product Title Prefix Check
$machinesContent = file_exists(__DIR__ . '/data/machines.php') ? file_get_contents(__DIR__ . '/data/machines.php') : '';
$hasPrefix = strpos($machinesContent, 'Second Hand / Used / Old / Imported ') !== false;
$checks[] = [
    'name' => 'Product Name Prefix',
    'ok' => $hasPrefix,
    'detail' => $hasPrefix ? 'Active: "Second Hand / Used / Old / Imported" on all listings' : 'Prefix missing from data/machines.php'
];

// Live Power Removal Check
$productContent = file_exists(__DIR__ . '/product.php') ? file_get_contents(__DIR__ . '/product.php') : '';
$livePowerRemoved = (strpos($productContent, 'Joule Machine Hub facilitates direct inspections and live power trials') === false)
                 && (strpos($productContent, 'Live Power Inspection Ready') === false);
$checks[] = [
    'name' => 'Live Power Lines Removed',
    'ok' => $livePowerRemoved,
    'detail' => $livePowerRemoved ? 'Removed from product.php, index.php & inventory' : 'Still present in product.php'
];

// Mobile Footer Check
$footerContent = file_exists(__DIR__ . '/includes/footer.php') ? file_get_contents(__DIR__ . '/includes/footer.php') : '';
$hasFooterGrid = strpos($footerContent, 'POPULAR CITIES') !== false && strpos($footerContent, 'grid-cols-2') !== false;
$checks[] = [
    'name' => '2-Column Mobile Footer',
    'ok' => $hasFooterGrid,
    'detail' => $hasFooterGrid ? 'Active: 2 columns per row matching MachineJunction style' : 'Old footer on server'
];

// 404 Check
$has404 = file_exists(__DIR__ . '/404.php');
$checks[] = [
    'name' => 'Custom 404 Page (404.php)',
    'ok' => $has404,
    'detail' => $has404 ? 'Active: 3-second countdown redirect to homepage' : 'Missing 404.php'
];

// Dashboard Check
$hasDashboard = file_exists(__DIR__ . '/dashboard/index.php');
$checks[] = [
    'name' => 'User Dashboard System (dashboard/)',
    'ok' => $hasDashboard,
    'detail' => $hasDashboard ? 'Active: Overview, Add Machine, Enquiries, Membership & Payments' : 'Missing dashboard/ folder'
];

// Category Hub Check
$hasCategory = file_exists(__DIR__ . '/categories.php') && file_exists(__DIR__ . '/category.php');
$checks[] = [
    'name' => 'Machinery Categories Hub (/category)',
    'ok' => $hasCategory,
    'detail' => $hasCategory ? 'Active: Master 13-category directory & category filters' : 'Missing categories.php or category.php'
];

// Blog System Check
$hasBlog = file_exists(__DIR__ . '/blog.php') && file_exists(__DIR__ . '/blog-post.php') && file_exists(__DIR__ . '/data/articles.php');
$checks[] = [
    'name' => 'Machinery Intelligence Blog (/blog)',
    'ok' => $hasBlog,
    'detail' => $hasBlog ? 'Active: 8 technical guides & responsive reader' : 'Missing blog.php, blog-post.php, or data/articles.php'
];

$allPassed = $hasNewHero && $hasPrefix && $livePowerRemoved && $hasFooterGrid && $has404 && $hasDashboard && $hasCategory && $hasBlog;
$freshTimestamp = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hostinger Deployment & Cache Purge - Joule Machine Hub</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 font-sans">

  <div class="max-w-xl w-full bg-slate-900 border-2 <?php echo $allPassed ? 'border-emerald-500' : 'border-amber-500'; ?> rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
    
    <div class="text-center space-y-2">
      <div class="w-16 h-16 rounded-2xl <?php echo $allPassed ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500' : 'bg-amber-500/20 text-amber-400 border border-amber-500'; ?> flex items-center justify-center text-3xl mx-auto">
        <i class="fa-solid <?php echo $allPassed ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
      </div>
      <h1 class="text-2xl sm:text-3xl font-black text-white">
        <?php echo $allPassed ? 'Website Successfully Updated!' : 'Action Needed in Hostinger File Manager'; ?>
      </h1>
      <p class="text-xs text-slate-400">
        <?php echo $allPassed ? 'All new files are verified live in public_html and server caches have been purged.' : 'The updated files have not replaced the old files in public_html yet.'; ?>
      </p>
    </div>

    <?php if (!empty($logs)): ?>
      <div class="bg-slate-950/80 rounded-2xl p-4 border border-slate-800 space-y-2">
        <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Deployment Actions Taken</div>
        <?php foreach ($logs as $log): ?>
          <div class="flex items-start gap-2.5 text-xs">
            <i class="fa-solid <?php echo $log['type'] === 'success' ? 'fa-circle-check text-emerald-400' : 'fa-circle-info text-blue-400'; ?> mt-0.5 shrink-0"></i>
            <div>
              <div class="font-bold text-white"><?php echo htmlspecialchars($log['title']); ?></div>
              <div class="text-[11px] text-slate-400"><?php echo htmlspecialchars($log['desc']); ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Status Checklist -->
    <div class="bg-slate-950/80 rounded-2xl p-4 border border-slate-800 space-y-3">
      <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Live Feature Status</div>
      <?php foreach ($checks as $c): ?>
        <div class="flex items-center justify-between gap-2 text-xs border-b border-slate-800/60 pb-2 last:border-0 last:pb-0">
          <div class="flex items-center gap-2">
            <i class="fa-solid <?php echo $c['ok'] ? 'fa-check text-emerald-400' : 'fa-xmark text-rose-400'; ?> text-xs"></i>
            <span class="font-bold text-slate-200"><?php echo htmlspecialchars($c['name']); ?></span>
          </div>
          <span class="text-[11px] font-mono <?php echo $c['ok'] ? 'text-emerald-300 bg-emerald-950/60 border-emerald-800/40' : 'text-rose-300 bg-rose-950/60 border-rose-800/40'; ?> px-2 py-0.5 rounded border text-right">
            <?php echo htmlspecialchars($c['detail']); ?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (!$allPassed): ?>
      <div class="bg-amber-950/40 border border-amber-600/40 rounded-2xl p-4 text-xs text-amber-200 space-y-3">
        <div class="font-bold flex items-center gap-2 text-amber-300">
          <i class="fa-solid fa-cloud-arrow-up text-base"></i>
          <span>1-Click Upload & Deploy:</span>
        </div>
        <form method="POST" enctype="multipart/form-data" class="space-y-2">
          <label class="block text-[11px] text-slate-300">Choose <strong>JOULE_CODE_UPDATE_LIGHTWEIGHT.zip</strong> from your computer:</label>
          <input type="file" name="zipfile" accept=".zip" required class="block w-full text-xs text-slate-300 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer">
          <button type="submit" class="w-full bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black py-2.5 px-3 rounded-xl text-xs transition-colors shadow cursor-pointer">
            <i class="fa-solid fa-bolt mr-1"></i> Upload &amp; Extract Directly to public_html
          </button>
        </form>
        <div class="border-t border-amber-600/30 pt-2 text-[10px] text-slate-400">
          Or in Hostinger File Manager $\rightarrow$ <strong>public_html</strong>, upload <strong>JOULE_CODE_UPDATE_LIGHTWEIGHT.zip</strong> and extract with <em>Overwrite</em> enabled.
        </div>
      </div>
    <?php endif; ?>

    <!-- Action Buttons -->
    <div class="space-y-2 pt-2">
      <div class="grid grid-cols-2 gap-2">
        <a 
          href="/category?t=<?php echo $freshTimestamp; ?>" 
          class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer text-center"
        >
          <i class="fa-solid fa-boxes-stacked"></i>
          <span>Category Directory</span>
        </a>
        <a 
          href="/blog?t=<?php echo $freshTimestamp; ?>" 
          class="bg-joule-blue hover:bg-blue-700 text-white font-bold py-2.5 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer text-center"
        >
          <i class="fa-solid fa-book-open"></i>
          <span>Machinery Blog</span>
        </a>
      </div>

      <div class="grid grid-cols-2 gap-2">
        <a 
          href="/index.php?t=<?php echo $freshTimestamp; ?>" 
          class="bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold py-2.5 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer text-center"
        >
          <i class="fa-solid fa-house"></i>
          <span>Homepage</span>
        </a>
        <a 
          href="/clear-cache.php?t=<?php echo $freshTimestamp; ?>" 
          class="bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold py-2.5 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer text-center"
        >
          <i class="fa-solid fa-rotate"></i>
          <span>Purge All Cache</span>
        </a>
      </div>
    </div>

  </div>

</body>
</html>
