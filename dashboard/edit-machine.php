<?php
$pageTitle = "Edit Machine Listing";
require_once __DIR__ . '/header.php';

$ref = trim($_GET['ref'] ?? '');
$jsonFile = __DIR__ . '/../data/seller_products.json';
$existing = file_exists($jsonFile) ? (json_decode(file_get_contents($jsonFile), true) ?: []) : [];

$machine = null;
foreach ($existing as $p) {
    if (($p['product_ref'] ?? '') === $ref && (($p['user_id'] ?? '') === $userId || empty($p['user_id']))) {
        $machine = $p;
        break;
    }
}

if (!$machine) {
    header('Location: my-machines.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $currency = trim($_POST['currency'] ?? 'INR');
    $category = trim($_POST['category'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $manufacturer = trim($_POST['manufacturer'] ?? ($brand ?: ''));
    $manufacturerCountry = trim($_POST['manufacturer_country'] ?? '');
    $sellerCompany = trim($_POST['seller_company'] ?? '');
    $sellerLocation = trim($_POST['seller_location'] ?? '');
    $machineLocation = trim($_POST['machine_location'] ?? '');
    $condition = trim($_POST['condition'] ?? 'Used');
    $desc = trim($_POST['detailed_desc'] ?? '');

    if (empty($title) || empty($price)) {
        $error = 'Please fill in all mandatory fields.';
    } else {
        $updatedProduct = null;
        foreach ($existing as &$p) {
            if (($p['product_ref'] ?? '') === $ref) {
                $p['title'] = $title;
                $p['value_raw'] = $currency . ' ' . $price;
                $p['currency_origin'] = $currency;
                $p['category'] = $category;
                $p['brand'] = $brand;
                $p['model'] = $model;
                if (!empty($manufacturer)) $p['manufacturer'] = $manufacturer;
                if (!empty($manufacturerCountry)) $p['manufacturer_country'] = $manufacturerCountry;
                if (!empty($sellerCompany)) $p['seller_company'] = $sellerCompany;
                if (!empty($sellerLocation)) $p['seller_location'] = $sellerLocation;
                if (!empty($machineLocation)) $p['machine_location'] = $machineLocation;
                $p['condition_state'] = $condition;
                $p['condition'] = $condition;
                $p['detailed_desc'] = $desc;
                $p['status'] = isAdmin() ? 'active' : 'pending_approval'; // Pause public visibility until Admin re-approves
                unset($p['rejection_reason']);
                $p['updated_at'] = date('Y-m-d H:i:s');
                $updatedProduct = $p;
                break;
            }
        }
        unset($p);
        file_put_contents($jsonFile, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        if ($updatedProduct) {
            $newStatus = $updatedProduct['status'];
            $pdo = getDbConnection();
            if ($pdo) {
                try {
                    $st1 = $pdo->prepare("UPDATE `seller_products` SET `title` = ?, `category` = ?, `brand` = ?, `model` = ?, `condition_state` = ?, `detailed_desc` = ?, `status` = ?, `rejection_reason` = NULL WHERE `product_ref` = ?");
                    $st1->execute([$title, $category, $brand, $model, $condition, $desc, $newStatus, $ref]);

                    $mId = (string)($updatedProduct['id'] ?? $ref);
                    $st2 = $pdo->prepare("UPDATE `machines` SET `status` = ? WHERE `id` = ?");
                    $st2->execute([$newStatus, $mId]);
                } catch (Throwable $e) {
                    error_log("Edit machine DB sync failed: " . $e->getMessage());
                }
            }

            if ($newStatus === 'pending_approval') {
                notifyAdminProductPendingApproval($updatedProduct, 'product_updated');
                regenerateStaticSitemap();
                $success = "Machinery listing updated and submitted for Admin re-approval! Notification sent to Admin.";
            } else {
                regenerateStaticSitemap();
                $success = "Machinery listing updated live!";
            }
            $machine = $updatedProduct;
        }
    }
}
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
  <div>
    <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
      <a href="/dashboard" class="hover:text-blue-600">Dashboard</a>
      <span>/</span>
      <a href="my-machines.php" class="hover:text-blue-600">My Machines</a>
      <span>/</span>
      <span class="text-slate-800 font-bold">Edit #<?php echo htmlspecialchars($ref); ?></span>
    </div>
    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Edit Machine Details</h1>
  </div>
</div>

<?php if (!empty($success)): ?>
  <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2.5">
    <i class="fa-solid fa-circle-check text-base text-emerald-600 shrink-0"></i>
    <div><?php echo htmlspecialchars($success); ?></div>
  </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold flex items-center gap-2.5">
    <i class="fa-solid fa-circle-exclamation text-base text-red-600 shrink-0"></i>
    <div><?php echo htmlspecialchars($error); ?></div>
  </div>
<?php endif; ?>

<form action="edit-machine.php?ref=<?php echo urlencode($ref); ?>" method="POST" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 space-y-6 shadow-xs">
  
  <div>
    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Machine Title</label>
    <input type="text" name="title" value="<?php echo htmlspecialchars($machine['title'] ?? ''); ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold">
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
      <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Category</label>
      <input type="text" name="category" value="<?php echo htmlspecialchars($machine['category'] ?? ''); ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold">
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Brand</label>
      <input type="text" name="brand" value="<?php echo htmlspecialchars($machine['brand'] ?? ''); ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold">
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Model</label>
      <input type="text" name="model" value="<?php echo htmlspecialchars($machine['model'] ?? ''); ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold">
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
      <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Price</label>
      <input type="text" name="price" value="<?php echo htmlspecialchars(preg_replace('/[^0-9.]/', '', $machine['value_raw'] ?? '')); ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold">
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Currency</label>
      <select name="currency" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold bg-white">
        <option value="INR" <?php echo ($machine['currency_origin'] ?? '') === 'INR' ? 'selected' : ''; ?>>INR (₹)</option>
        <option value="USD" <?php echo ($machine['currency_origin'] ?? '') === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
        <option value="EUR" <?php echo ($machine['currency_origin'] ?? '') === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
      </select>
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Condition</label>
      <select name="condition" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold bg-white">
        <option value="Used" <?php echo ($machine['condition_state'] ?? '') === 'Used' ? 'selected' : ''; ?>>Used</option>
        <option value="New" <?php echo ($machine['condition_state'] ?? '') === 'New' ? 'selected' : ''; ?>>New</option>
        <option value="Refurbished" <?php echo ($machine['condition_state'] ?? '') === 'Refurbished' ? 'selected' : ''; ?>>Refurbished</option>
      </select>
    </div>
  </div>

  <div>
    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Description</label>
    <textarea name="detailed_desc" rows="4" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold"><?php echo htmlspecialchars($machine['detailed_desc'] ?? ''); ?></textarea>
  </div>

  <div class="flex items-center gap-3 pt-3">
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-3 px-6 rounded-xl shadow-md transition-all cursor-pointer">
      Save Changes &amp; Resubmit
    </button>
    <a href="my-machines.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs py-3 px-5 rounded-xl transition-colors">
      Cancel
    </a>
  </div>

</form>

<?php
require_once __DIR__ . '/footer.php';
?>
