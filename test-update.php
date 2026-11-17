<?php
/**
 * Joule Machine Hub - Live Diagnostic & 1-Click ZIP Extractor
 * Access URL: https://joulemachinehub.com/test-update.php
 */

header("X-LiteSpeed-Purge: *");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$actionMsg = '';
$actionStatus = '';

// Handle 1-Click Extraction
if (isset($_POST['extract_zip'])) {
    $zipToExtract = trim($_POST['extract_zip']);
    $zipPath = __DIR__ . '/' . basename($zipToExtract);
    if (file_exists($zipPath)) {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) === true) {
            $zip->extractTo(__DIR__);
            $num = $zip->numFiles;
            $zip->close();
            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }
            $actionMsg = "Successfully extracted $num files from " . htmlspecialchars($zipToExtract) . " directly into public_html! Cache purged.";
            $actionStatus = 'success';
        } else {
            $actionMsg = "Failed to open zip file $zipToExtract.";
            $actionStatus = 'error';
        }
    } else {
        $actionMsg = "File $zipToExtract not found in " . __DIR__;
        $actionStatus = 'error';
    }
}

// Handle Direct Cache Purge
if (isset($_POST['purge_cache'])) {
    if (function_exists('opcache_reset')) {
        @opcache_reset();
    }
    $core = [__DIR__ . '/index.php', __DIR__ . '/product.php', __DIR__ . '/data/machines.php', __DIR__ . '/.htaccess'];
    foreach ($core as $f) {
        if (file_exists($f)) @touch($f);
    }
    $actionMsg = "PHP OPcache cleared and file timestamps updated!";
    $actionStatus = 'success';
}

// Scan Current Server Directory
$filesInDir = scandir(__DIR__);
$zipFiles = [];
foreach ($filesInDir as $f) {
    if (str_ends_with(strtolower($f), '.zip')) {
        $zipFiles[] = [
            'name' => $f,
            'size' => round(filesize(__DIR__ . '/' . $f) / 1024, 1) . ' KB',
            'mtime' => date('Y-m-d H:i:s', filemtime(__DIR__ . '/' . $f))
        ];
    }
}

// Check Key Files
$machinesFile = __DIR__ . '/data/machines.php';
$hasPrefix = file_exists($machinesFile) && str_contains(file_get_contents($machinesFile), 'Second Hand / Used / Old / Imported ');

$productFile = __DIR__ . '/product.php';
$livePowerRemoved = file_exists($productFile) && !str_contains(file_get_contents($productFile), 'facilitates direct inspections and live power trials');

$hasDashboard = file_exists(__DIR__ . '/dashboard/index.php');
$has404 = file_exists(__DIR__ . '/404.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hostinger Live Update Helper - Joule Machine Hub</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 font-sans">

  <div class="max-w-2xl w-full bg-slate-900 border-2 border-slate-700 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">

    <div class="flex items-center justify-between border-b border-slate-800 pb-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
          <i class="fa-solid fa-server text-blue-400"></i>
          <span>Hostinger Live Status Check</span>
        </h1>
        <p class="text-xs text-slate-400 mt-0.5">Directory: <code class="text-amber-300 font-mono"><?php echo __DIR__; ?></code></p>
      </div>
      <span class="text-xs font-bold px-3 py-1 rounded-full bg-slate-800 text-slate-300 border border-slate-700">
        PHP <?php echo phpversion(); ?>
      </span>
    </div>

    <?php if ($actionMsg): ?>
      <div class="p-4 rounded-2xl text-xs font-bold flex items-center gap-2 <?php echo $actionStatus === 'success' ? 'bg-emerald-950/80 text-emerald-300 border border-emerald-600' : 'bg-rose-950/80 text-rose-300 border border-rose-600'; ?>">
        <i class="fa-solid <?php echo $actionStatus === 'success' ? 'fa-circle-check text-emerald-400' : 'fa-triangle-exclamation text-rose-400'; ?> text-base"></i>
        <span><?php echo $actionMsg; ?></span>
      </div>
    <?php endif; ?>

    <!-- Status Cards -->
    <div class="grid grid-cols-2 gap-3 text-xs">
      <div class="p-3.5 rounded-2xl bg-slate-950/80 border border-slate-800">
        <div class="text-slate-400 text-[10px] uppercase font-bold">Product Title Prefix</div>
        <div class="font-black mt-1 flex items-center gap-1.5 <?php echo $hasPrefix ? 'text-emerald-400' : 'text-rose-400'; ?>">
          <i class="fa-solid <?php echo $hasPrefix ? 'fa-check' : 'fa-xmark'; ?>"></i>
          <span><?php echo $hasPrefix ? 'Active in machines.php' : 'Missing (Old File)'; ?></span>
        </div>
      </div>

      <div class="p-3.5 rounded-2xl bg-slate-950/80 border border-slate-800">
        <div class="text-slate-400 text-[10px] uppercase font-bold">Live Power Inspection Lines</div>
        <div class="font-black mt-1 flex items-center gap-1.5 <?php echo $livePowerRemoved ? 'text-emerald-400' : 'text-rose-400'; ?>">
          <i class="fa-solid <?php echo $livePowerRemoved ? 'fa-check' : 'fa-xmark'; ?>"></i>
          <span><?php echo $livePowerRemoved ? 'Removed' : 'Still Present (Old File)'; ?></span>
        </div>
      </div>

      <div class="p-3.5 rounded-2xl bg-slate-950/80 border border-slate-800">
        <div class="text-slate-400 text-[10px] uppercase font-bold">User Dashboard (dashboard/)</div>
        <div class="font-black mt-1 flex items-center gap-1.5 <?php echo $hasDashboard ? 'text-emerald-400' : 'text-rose-400'; ?>">
          <i class="fa-solid <?php echo $hasDashboard ? 'fa-check' : 'fa-xmark'; ?>"></i>
          <span><?php echo $hasDashboard ? 'Active on Server' : 'Not Uploaded Yet'; ?></span>
        </div>
      </div>

      <div class="p-3.5 rounded-2xl bg-slate-950/80 border border-slate-800">
        <div class="text-slate-400 text-[10px] uppercase font-bold">Custom 404 (404.php)</div>
        <div class="font-black mt-1 flex items-center gap-1.5 <?php echo $has404 ? 'text-emerald-400' : 'text-rose-400'; ?>">
          <i class="fa-solid <?php echo $has404 ? 'fa-check' : 'fa-xmark'; ?>"></i>
          <span><?php echo $has404 ? 'Active on Server' : 'Not Uploaded Yet'; ?></span>
        </div>
      </div>
    </div>

    <!-- ZIP Files in Current Directory -->
    <div class="bg-slate-950/80 rounded-2xl p-4 border border-slate-800 space-y-3">
      <div class="flex items-center justify-between">
        <div class="text-xs font-bold text-slate-300 flex items-center gap-2">
          <i class="fa-solid fa-file-zipper text-amber-400"></i>
          <span>ZIP Files Found in public_html:</span>
        </div>
        <span class="text-[11px] text-slate-400"><?php echo count($zipFiles); ?> found</span>
      </div>

      <?php if (empty($zipFiles)): ?>
        <div class="p-3 bg-amber-950/30 border border-amber-800/40 rounded-xl text-amber-200 text-xs">
          <i class="fa-solid fa-circle-info mr-1 text-amber-400"></i>
          No <code>.zip</code> file found in this folder. Please upload <strong>JOULE_CODE_UPDATE_LIGHTWEIGHT.zip</strong> or <strong>UPLOAD_TO_PUBLIC_HTML_FINAL.zip</strong> into this folder.
        </div>
      <?php else: ?>
        <div class="space-y-2">
          <?php foreach ($zipFiles as $zf): ?>
            <div class="flex items-center justify-between bg-slate-900 p-3 rounded-xl border border-slate-800 text-xs">
              <div>
                <div class="font-bold text-white"><?php echo htmlspecialchars($zf['name']); ?></div>
                <div class="text-[11px] text-slate-400"><?php echo $zf['size']; ?> • Modified: <?php echo $zf['mtime']; ?></div>
              </div>
              <form method="POST">
                <input type="hidden" name="extract_zip" value="<?php echo htmlspecialchars($zf['name']); ?>">
                <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black px-4 py-2 rounded-xl text-xs flex items-center gap-1.5 shadow transition-transform hover:scale-105 cursor-pointer">
                  <i class="fa-solid fa-bolt"></i>
                  <span>Extract This ZIP</span>
                </button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center gap-3 pt-2">
      <form method="POST" class="flex-1">
        <button type="submit" name="purge_cache" value="1" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition-colors cursor-pointer">
          <i class="fa-solid fa-rotate"></i>
          <span>Purge Server Cache Now</span>
        </button>
      </form>
      <a href="/index.php?t=<?php echo time(); ?>" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold py-3 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition-colors text-center cursor-pointer">
        <i class="fa-solid fa-arrow-up-right-from-square"></i>
        <span>Visit Homepage</span>
      </a>
    </div>

  </div>

</body>
</html>
