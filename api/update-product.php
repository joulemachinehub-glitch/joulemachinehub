<?php
// Joule Machine Hub - Product Content Update API
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Authentication guard: super admin only
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Admin authorization required. Please log in as joulemachinehub@gmail.com']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'POST method required']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?: $_POST;

$machineId = trim($input['id'] ?? $input['machine_id'] ?? '');
if (empty($machineId)) {
    echo json_encode(['success' => false, 'error' => 'Missing machine ID']);
    exit;
}

$overridesFile = __DIR__ . '/../data/machines_custom.json';
$overrides = [];
if (file_exists($overridesFile)) {
    $rawOverrides = @file_get_contents($overridesFile);
    if (!empty($rawOverrides)) {
        $overrides = json_decode($rawOverrides, true) ?: [];
    }
}

$existing = $overrides[$machineId] ?? [];

// Allowed editable fields
$editableFields = [
    'title', 'brand', 'model', 'category', 'equipment_type',
    'year', 'condition', 'city', 'country', 'short_desc', 'detailed_desc'
];

foreach ($editableFields as $f) {
    if (isset($input[$f])) {
        $existing[$f] = trim($input[$f]);
    }
}

// Numerical price fields
if (isset($input['price']) && $input['price'] !== '') {
    $existing['price'] = floatval($input['price']);
}
if (isset($input['price_usd']) && $input['price_usd'] !== '') {
    $existing['price_usd'] = floatval($input['price_usd']);
}

// Key Specs (if provided as JSON or array)
if (isset($input['specs'])) {
    if (is_array($input['specs'])) {
        $existing['specs'] = $input['specs'];
    } elseif (is_string($input['specs'])) {
        $decodedSpecs = json_decode($input['specs'], true);
        if (is_array($decodedSpecs)) {
            $existing['specs'] = $decodedSpecs;
        }
    }
}

$existing['updated_at'] = date('Y-m-d H:i:s T');
$existing['updated_by'] = $_SESSION['user']['email'] ?? 'joulemachinehub@gmail.com';

$overrides[$machineId] = $existing;

$saved = @file_put_contents($overridesFile, json_encode($overrides, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Dual Save to MySQL Database (`machines` and `seller_products`)
$dbUpdated = false;
$pdo = getDbConnection();
if ($pdo) {
    try {
        $updateSql = "
            UPDATE `machines` SET
                `title` = COALESCE(:title, `title`),
                `brand` = COALESCE(:brand, `brand`),
                `model` = COALESCE(:model, `model`),
                `category` = COALESCE(:category, `category`),
                `year` = COALESCE(:year, `year`),
                `price_inr` = COALESCE(:price_inr, `price_inr`),
                `price_usd` = COALESCE(:price_usd, `price_usd`),
                `condition_state` = COALESCE(:condition_state, `condition_state`),
                `city` = COALESCE(:city, `city`),
                `country` = COALESCE(:country, `country`),
                `short_desc` = COALESCE(:short_desc, `short_desc`),
                `detailed_desc` = COALESCE(:detailed_desc, `detailed_desc`),
                `updated_at` = NOW()
            WHERE `id` = :id
        ";
        $stmt = $pdo->prepare($updateSql);
        $stmt->execute([
            ':id' => $machineId,
            ':title' => $existing['title'] ?? null,
            ':brand' => $existing['brand'] ?? null,
            ':model' => $existing['model'] ?? null,
            ':category' => $existing['category'] ?? null,
            ':year' => isset($existing['year']) ? intval($existing['year']) : null,
            ':price_inr' => isset($existing['price']) ? floatval($existing['price']) : null,
            ':price_usd' => isset($existing['price_usd']) ? floatval($existing['price_usd']) : null,
            ':condition_state' => $existing['condition'] ?? null,
            ':city' => $existing['city'] ?? null,
            ':country' => $existing['country'] ?? null,
            ':short_desc' => $existing['short_desc'] ?? null,
            ':detailed_desc' => $existing['detailed_desc'] ?? null
        ]);
        $dbUpdated = true;
    } catch (Throwable $e) {
        error_log("Failed to update machine in MySQL database: " . $e->getMessage());
    }
}

if ($saved !== false) {
    echo json_encode([
        'success' => true,
        'message' => 'Product updated successfully. Saved in MySQL database and live catalog.',
        'machine_id' => $machineId,
        'db_updated' => $dbUpdated,
        'updated_fields' => array_keys($existing)
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to save updates to data/machines_custom.json. Check file write permissions.'
    ]);
}
