<?php
// Joule Machine Hub - Add Machine / Listing API (Save to MySQL Database & Dual JSON)
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/seller.php';
require_once __DIR__ . '/../data/machines.php';

// Authentication guard: super admin or logged-in seller
if (!isAdmin() && !isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Authorization required. Please log in to add machinery.']);
    exit;
}

$isAdminUser = isAdmin();
$listingStatus = $isAdminUser ? 'active' : 'pending_approval';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'POST method required']);
    exit;
}

// Extract data from POST or JSON input
$input = $_POST;
if (empty($input)) {
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $input = json_decode($rawInput, true) ?? [];
    }
}

$title = trim($input['title'] ?? '');
$brand = trim($input['brand'] ?? '');
$model = trim($input['model'] ?? '');
$category = trim($input['category'] ?? 'Presses & Stamping');

if (empty($title) || empty($brand) || empty($model)) {
    echo json_encode(['success' => false, 'error' => 'Machine Title, Brand, and Model are required fields.']);
    exit;
}

$equipmentType = trim($input['equipment_type'] ?? $category);
$year = intval($input['year'] ?? date('Y'));
$priceInr = floatval($input['price'] ?? $input['price_inr'] ?? 0);
$priceUsd = floatval($input['price_usd'] ?? ($priceInr > 0 ? round($priceInr / 85) : 0));
if ($priceInr <= 0 && $priceUsd > 0) {
    $priceInr = round($priceUsd * 85);
}

$city = trim($input['city'] ?? 'Chennai');
$country = trim($input['country'] ?? 'India');
$condition = trim($input['condition'] ?? 'Good Condition');
$hoursRun = trim($input['hours_run'] ?? 'Plant Maintained');
$shortDesc = trim($input['short_desc'] ?? ($title . ' in ' . $condition . ' located in ' . $city . ', ' . $country . '.'));
$detailedDesc = trim($input['detailed_desc'] ?? $shortDesc);

// Country Flag & Code Mapping (Shared helper)
$geoMeta = buildCountryFlagMeta($country);
$flag = $geoMeta['flag'];
$countryCode = $geoMeta['code'];

// Handle Image Uploads or Image URLs
$images = [];

// 1. Check if files were uploaded
if (!empty($_FILES['machine_images']['name'][0])) {
    $uploadDir = __DIR__ . '/../assets/images/uploads/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }
    $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'jfif'];
    $fileCount = count($_FILES['machine_images']['name']);
    for ($i = 0; $i < min($fileCount, 20); $i++) {
        if ($_FILES['machine_images']['error'][$i] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['machine_images']['tmp_name'][$i];
            $origName = $_FILES['machine_images']['name'][$i];
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            if (in_array($ext, $allowedExts)) {
                $newName = 'mach_' . time() . '_' . ($i + 1) . '_' . rand(100, 999) . '.' . $ext;
                $target = $uploadDir . $newName;
                if (move_uploaded_file($tmpName, $target)) {
                    $images[] = 'assets/images/uploads/' . $newName;
                }
            }
        }
    }
}

// 2. Check if comma-separated or single image path passed via input
if (empty($images) && !empty($input['images'])) {
    if (is_array($input['images'])) {
        $images = array_map('trim', $input['images']);
    } else {
        $split = explode(',', $input['images']);
        foreach ($split as $img) {
            $trimmed = trim($img);
            if (!empty($trimmed)) $images[] = $trimmed;
        }
    }
}

// 3. Fallback placeholder if no image provided
if (empty($images)) {
    $images = ['assets/images/logo.png'];
}

// Generate Unique Sequential Machine ID
$totalExisting = count($machines);
$nextIndex = $totalExisting + 1;
$machineId = sprintf('%04d-%s', $nextIndex, date('Y'));
$productRef = 'JMH-' . strtoupper(substr(md5(uniqid()), 0, 8));

// Generate Clean SEO Slug
$slugBase = 'used-' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $brand . '-' . $model . '-' . $category));
$slug = trim($slugBase, '-');

// Format Value Raw Display
$valueRaw = '$' . number_format($priceUsd) . ' USD';
if ($priceInr >= 10000000) {
    $priceDisplay = '$' . number_format($priceUsd) . ' USD (₹' . number_format($priceInr / 10000000, 2) . ' Crore)';
} elseif ($priceInr >= 100000) {
    $priceDisplay = '$' . number_format($priceUsd) . ' USD (₹' . number_format($priceInr / 100000, 2) . ' Lakhs)';
} else {
    $priceDisplay = '$' . number_format($priceUsd) . ' USD (₹' . number_format($priceInr) . ')';
}

// Construct Key Specifications
$specs = [];
if (!empty($input['specs']) && is_array($input['specs'])) {
    $specs = $input['specs'];
} elseif (!empty($input['specs']) && is_string($input['specs'])) {
    $specs = json_decode($input['specs'], true) ?: [];
}
// Add standard parameters if provided in form
if (!empty($input['capacity'])) $specs['Capacity / Tonnage'] = trim($input['capacity']);
if (!empty($input['stroke_length'])) $specs['Stroke Length'] = trim($input['stroke_length']);
if (!empty($input['speed_spm'])) $specs['Operating Speed'] = trim($input['speed_spm']);
if (!empty($input['motor_power'])) $specs['Motor Power'] = trim($input['motor_power']);
if (!empty($input['machine_weight'])) $specs['Machine Weight'] = trim($input['machine_weight']);
if (!empty($input['dimensions'])) $specs['Dimensions'] = trim($input['dimensions']);
$specs['Manufacturer'] = $brand;
$specs['Model'] = $model;
$specs['Year'] = (string)$year;

// Key Features Highlights
$features = [];
if (!empty($input['feature_1'])) $features[] = trim($input['feature_1']);
if (!empty($input['feature_2'])) $features[] = trim($input['feature_2']);
if (!empty($input['feature_3'])) $features[] = trim($input['feature_3']);
if (!empty($input['feature_4'])) $features[] = trim($input['feature_4']);
if (empty($features)) {
    $features = [
        "Verified plant machinery in {$condition} ready for factory floor inspection",
        "Direct plant owner sourcing with zero buyer brokerage fees",
        "Complete technical parameters and OEM nameplates verified",
        "Worldwide turnkey dismantling, rigging and container shipping assistance"
    ];
}

// Current User Details
$currentUser = getCurrentUser();
$seller = [
    'name' => $currentUser['company'] ?? 'Joule Machine Hub Verified Inventory',
    'contact_person' => $currentUser['name'] ?? 'Technical Procurement Desk',
    'phone' => '',
    'whatsapp' => '',
    'email' => $currentUser['email'] ?? 'joulemachinehub@gmail.com',
    'verified' => true,
    'rating' => 4.9,
    'deals_closed' => 15,
    'member_since' => date('Y')
];

// Ensure unique SEO slug
$slug = ensureUniqueMachineSlug($slug, $productRef);

// Complete Machine Object
$newMachine = [
    'id' => $machineId,
    'product_ref' => $productRef,
    'user_id' => $currentUser['id'] ?? ($currentUser['user_id'] ?? ($isAdminUser ? 'usr-admin-joule' : 'usr-seller')),
    'item_no' => (string)$nextIndex,
    'slug' => $slug,
    'title' => $title,
    'brand' => $brand,
    'model' => $model,
    'category' => $category,
    'year' => $year,
    'equipment_type' => $equipmentType,
    'short_desc' => $shortDesc,
    'detailed_desc' => $detailedDesc,
    'price' => $priceInr,
    'price_usd' => $priceUsd,
    'price_display' => $priceDisplay,
    'currency_origin' => 'USD',
    'value_raw' => $valueRaw,
    'condition' => $condition,
    'condition_state' => $condition,
    'hours_run' => $hoursRun,
    'location' => 'Direct Plant Floor • Global Network',
    'city' => $city,
    'country' => $country,
    'country_code' => $countryCode,
    'flag' => $flag,
    'is_featured' => $isAdminUser,
    'images' => $images,
    'seller' => $seller,
    'seller_company' => $seller['name'],
    'contact_person' => $seller['contact_person'],
    'seller_phone' => $currentUser['phone'] ?? '',
    'seller_email' => $seller['email'],
    'specs' => $specs,
    'features' => $features,
    'status' => $listingStatus,
    'created_at' => date('Y-m-d H:i:s T')
];

// ========================================================
// 1. SAVE INTO MYSQL DATABASE (`machines` ONLY WHEN ADMIN-APPROVED & `seller_products`)
// ========================================================
$dbSaved = false;
$dbError = '';
$pdo = getDbConnection();
if ($pdo) {
    try {
        // A. Insert into `machines` live catalog table ONLY when Admin publishes directly
        if ($listingStatus === 'active') {
            $stmtM = $pdo->prepare("
                INSERT INTO `machines` (
                    `id`, `slug`, `title`, `brand`, `model`, `category`, `equipment_type`,
                    `year`, `price_inr`, `price_usd`, `currency_origin`, `value_raw`,
                    `condition_state`, `hours_run`, `city`, `country`, `country_code`, `flag`,
                    `images_json`, `specs_json`, `features_json`, `short_desc`, `detailed_desc`,
                    `seller_json`, `status`
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, 'active'
                )
                ON DUPLICATE KEY UPDATE
                    `title` = VALUES(`title`),
                    `brand` = VALUES(`brand`),
                    `model` = VALUES(`model`),
                    `category` = VALUES(`category`),
                    `year` = VALUES(`year`),
                    `price_inr` = VALUES(`price_inr`),
                    `price_usd` = VALUES(`price_usd`),
                    `condition_state` = VALUES(`condition_state`),
                    `city` = VALUES(`city`),
                    `country` = VALUES(`country`),
                    `short_desc` = VALUES(`short_desc`),
                    `detailed_desc` = VALUES(`detailed_desc`),
                    `status` = 'active'
            ");
            $stmtM->execute([
                $machineId,
                $slug,
                $title,
                $brand,
                $model,
                $category,
                $equipmentType,
                $year,
                $priceInr,
                $priceUsd,
                'USD',
                $valueRaw,
                $condition,
                $hoursRun,
                $city,
                $country,
                $countryCode,
                $flag,
                json_encode($images, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                json_encode($specs, JSON_UNESCAPED_UNICODE),
                json_encode($features, JSON_UNESCAPED_UNICODE),
                $shortDesc,
                $detailedDesc,
                json_encode($seller, JSON_UNESCAPED_UNICODE)
            ]);
        }

        // B. Insert into `seller_products` table with strict approval status
        $stmtS = $pdo->prepare("
            INSERT INTO `seller_products` (
                `product_ref`, `user_id`, `title`, `brand`, `model`, `category`,
                `equipment_type`, `year`, `date_of_mfg`, `currency_origin`, `value_raw`,
                `price_inr`, `price_usd`, `country`, `city`, `plant_address`,
                `condition_state`, `hours_run`, `power_test`, `capacity`, `stroke_length`,
                `speed_spm`, `die_height`, `motor_power`, `machine_weight`, `dimensions`,
                `short_desc`, `detailed_desc`, `features_json`, `images_json`,
                `seller_company`, `contact_person`, `seller_phone`, `seller_email`, `status`
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?
            )
            ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `status` = VALUES(`status`)
        ");
        $stmtS->execute([
            $productRef,
            $newMachine['user_id'],
            $title,
            $brand,
            $model,
            $category,
            $equipmentType,
            (string)$year,
            date('F Y'),
            'USD',
            $valueRaw,
            $priceInr,
            $priceUsd,
            $country,
            $city,
            $city . ', ' . $country,
            $condition,
            $hoursRun,
            'Available for Plant Inspection',
            $specs['Capacity / Tonnage'] ?? 'N/A',
            $specs['Stroke Length'] ?? 'N/A',
            $specs['Operating Speed'] ?? 'N/A',
            'Standard',
            $specs['Motor Power'] ?? 'N/A',
            $specs['Machine Weight'] ?? 'N/A',
            $specs['Dimensions'] ?? 'N/A',
            $shortDesc,
            $detailedDesc,
            json_encode($features, JSON_UNESCAPED_UNICODE),
            json_encode($images, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $seller['name'],
            $seller['contact_person'],
            $currentUser['phone'] ?? '',
            $seller['email'],
            $listingStatus
        ]);

        $dbSaved = true;
    } catch (Throwable $e) {
        $dbError = $e->getMessage();
        error_log("Database insertion failed for new machine: " . $dbError);
    }
}

// ========================================================
// 2. DUAL PERSISTENCE IN LOCAL JSON (Zero Data Loss)
// ========================================================
// Save to data/seller_products.json
$sellerProductsFile = __DIR__ . '/../data/seller_products.json';
$existingSellerProds = [];
if (file_exists($sellerProductsFile)) {
    $existingSellerProds = json_decode(@file_get_contents($sellerProductsFile), true) ?: [];
}
array_unshift($existingSellerProds, $newMachine);
@file_put_contents($sellerProductsFile, json_encode($existingSellerProds, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

if ($listingStatus === 'active') {
    // Also record in data/machines_custom.json for immediate live overrides when published by Admin
    $customFile = __DIR__ . '/../data/machines_custom.json';
    $existingOverrides = [];
    if (file_exists($customFile)) {
        $existingOverrides = json_decode(@file_get_contents($customFile), true) ?: [];
    }
    $existingOverrides[$machineId] = [
        'title' => $title,
        'brand' => $brand,
        'model' => $model,
        'category' => $category,
        'year' => $year,
        'price' => $priceInr,
        'price_usd' => $priceUsd,
        'condition' => $condition,
        'city' => $city,
        'country' => $country,
        'short_desc' => $shortDesc,
        'detailed_desc' => $detailedDesc,
        'updated_at' => date('Y-m-d H:i:s T')
    ];
    @file_put_contents($customFile, json_encode($existingOverrides, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    if (isset($GLOBALS['machines']) && is_array($GLOBALS['machines'])) {
        $GLOBALS['machines'][] = $newMachine;
    }
    regenerateStaticSitemap();
} else {
    // Notify admin when submitted by non-admin seller
    notifyAdminProductPendingApproval($newMachine, 'product_submitted');
}

echo json_encode([
    'success' => true,
    'message' => $listingStatus === 'active'
        ? 'Machine data added and saved successfully in MySQL database and live catalog!'
        : 'Machine submitted for Admin approval! Notification sent to admin and it will go live once approved.',
    'status' => $listingStatus,
    'pending_approval' => ($listingStatus === 'pending_approval'),
    'machine_id' => $machineId,
    'product_ref' => $productRef,
    'slug' => $slug,
    'db_saved' => $dbSaved,
    'db_error' => $dbError ? $dbError : null,
    'machine' => $newMachine
]);
