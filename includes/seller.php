<?php
// Joule Machine Hub - Seller Machinery Management & Upload Handler
// Handles strict validation, up to 20 images upload, MySQL database persistence, and local JSON storage

require_once __DIR__ . '/db.php';

/**
 * Validates and saves a seller machinery upload.
 * All sections must be 100% completed.
 *
 * @param array $data Form POST data
 * @param array $files Form FILES data
 * @param array $user Current logged-in user
 * @return array ['success' => bool, 'message' => string, 'ref' => string, 'product' => array]
 */
function handleSellerMachineUpload($data, $files, $user) {
    // 1. Mandatory Fields Definition across all 8 sections
    $mandatoryFields = [
        // Section 1: Basic Machine Details
        'title'           => 'Machine Title / Official Name',
        'brand'           => 'Manufacturer / Brand',
        'model'           => 'Model Type / Series',
        'category'        => 'Machinery Category',
        'equipment_type'  => 'Equipment Classification',
        'year'            => 'Year of Manufacture',
        'date_of_mfg'     => 'Date / Month of Manufacture',

        // Section 2: Pricing & Valuation
        'currency_origin' => 'Transaction Currency',
        'value_raw'       => 'Machine Value / Asking Price',
        'price_usd'       => 'Estimated USD Value',

        // Section 3: Plant Location
        'country'         => 'Plant Country Location',
        'city'            => 'City / Industrial Area',
        'plant_address'   => 'Plant Facility / Warehouse Address',

        // Section 4: Operational Condition
        'condition_state' => 'Operational Condition',
        'hours_run'       => 'Running Hours / Meter Reading',
        'power_test'      => 'Power-On Inspection Readiness',

        // Section 5: Engineering Specs
        'capacity'        => 'Capacity / Tonnage Rating',
        'stroke_length'   => 'Slide Stroke / Stroke Length',
        'speed_spm'       => 'Operating Speed (SPM / RPM)',
        'die_height'      => 'Die Height / Daylight Opening',
        'motor_power'     => 'Main Motor Power Rating',
        'machine_weight'  => 'Total Machine Weight',
        'dimensions'      => 'Footprint Dimensions (L x W x H)',

        // Section 6: Descriptions & Key Features
        'short_desc'      => 'Short Technical Summary',
        'detailed_desc'   => 'Comprehensive Engineering Description',
        'feature_1'       => 'Key Feature Highlight #1',
        'feature_2'       => 'Key Feature Highlight #2',
        'feature_3'       => 'Key Feature Highlight #3',
        'feature_4'       => 'Key Feature Highlight #4',

        // Section 7: Seller Contact & Plant Authorization
        'seller_company'  => 'Seller Company Name',
        'contact_person'  => 'Contact Person Name',
        'seller_phone'    => 'Plant Contact Phone',
        'seller_email'    => 'Business Notification Email'
    ];

    // Check that every single field is non-empty (100% Mandatory fill)
    $missingFields = [];
    foreach ($mandatoryFields as $key => $label) {
        if (!isset($data[$key]) || trim($data[$key]) === '') {
            $missingFields[] = $label;
        }
    }

    if (!empty($missingFields)) {
        return [
            'success' => false,
            'message' => '100% Fill Required: Please complete the following missing fields: ' . implode(', ', array_slice($missingFields, 0, 5)) . (count($missingFields) > 5 ? ' and ' . (count($missingFields) - 5) . ' more...' : '')
        ];
    }

    // 2. Validate Image Uploads (Up to 20 images)
    $uploadedImageUrls = [];
    $uploadDir = __DIR__ . '/../assets/images/uploads/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'jfif'];
    $maxImagesAllowed = 20;

    if (isset($files['machine_images']) && !empty($files['machine_images']['name'][0])) {
        $fileCount = count($files['machine_images']['name']);
        
        if ($fileCount > $maxImagesAllowed) {
            return [
                'success' => false,
                'message' => "Maximum {$maxImagesAllowed} images allowed. You submitted {$fileCount} images."
            ];
        }

        for ($i = 0; $i < $fileCount; $i++) {
            $error = $files['machine_images']['error'][$i];
            $tmpName = $files['machine_images']['tmp_name'][$i];
            $origName = $files['machine_images']['name'][$i];
            $fileSize = $files['machine_images']['size'][$i];

            if ($error === UPLOAD_ERR_OK && is_uploaded_file($tmpName)) {
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExtensions)) {
                    continue; // Skip invalid extensions
                }

                // Check file size (max 10MB per image)
                if ($fileSize > 10 * 1024 * 1024) {
                    continue;
                }

                $newFileName = 'mach_' . time() . '_' . substr(md5(uniqid(rand(), true)), 0, 8) . '_' . ($i + 1) . '.' . $ext;
                $destination = $uploadDir . $newFileName;

                if (move_uploaded_file($tmpName, $destination)) {
                    $uploadedImageUrls[] = 'assets/images/uploads/' . $newFileName;
                }
            }
        }
    }

    // If no images were successfully uploaded via file input, require at least one photo or check existing
    if (empty($uploadedImageUrls)) {
        if (!empty($data['fallback_image_url'])) {
            $uploadedImageUrls[] = trim($data['fallback_image_url']);
        } else {
            return [
                'success' => false,
                'message' => 'Mandatory: Please upload at least 1 machine photograph (up to 20 photos supported).'
            ];
        }
    }

    // 3. Assemble Features JSON
    $features = [
        trim($data['feature_1']),
        trim($data['feature_2']),
        trim($data['feature_3']),
        trim($data['feature_4'])
    ];
    if (!empty($data['extra_features'])) {
        $extra = array_filter(array_map('trim', explode("\n", $data['extra_features'])));
        $features = array_merge($features, $extra);
    }
    $features = array_values(array_unique(array_filter($features)));

    // 4. Generate Product Reference & Sequential/Unique ID
    $productRef = 'JMH-SEL-' . strtoupper(substr(uniqid(), -6));
    $userId = !empty($user['user_id']) ? $user['user_id'] : (!empty($user['id']) ? $user['id'] : 'usr-seller-01');

    $priceUsd = floatval(preg_replace('/[^0-9.]/', '', $data['price_usd'] ?? ''));
    $rawVal = floatval(preg_replace('/[^0-9.]/', '', $data['value_raw'] ?? ''));
    if ($priceUsd <= 0 && $rawVal > 0) {
        $priceUsd = (($data['currency_origin'] ?? 'INR') === 'USD') ? $rawVal : round($rawVal / 85, 2);
    }
    if ($priceUsd <= 0) {
        $priceUsd = 10000;
    }
    $priceInr = round($priceUsd * 85);
    $priceDisplay = '$' . number_format($priceUsd) . ' USD (₹' . number_format($priceInr / 100000, 2) . ' Lakhs)';

    $brand = trim($data['brand']);
    $model = trim($data['model']);
    $rawTitle = trim($data['title']);
    if (stripos($rawTitle, 'Used ') !== 0) {
        $cleanTitle = 'Used ' . ltrim(preg_replace('/^(\s*[\/\-]\s*|\s*(Second Hand|Used|Old|Imported)\s*)+/i', '', $rawTitle));
    } else {
        $cleanTitle = $rawTitle;
    }

    // Generate unique ID and clean SEO slug
    $id = 'sp-' . time() . '-' . rand(100, 999);
    $slugBase = 'used-' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $brand . '-' . $model . '-' . substr($cleanTitle, 0, 35)));
    $slug = trim(preg_replace('/-+/', '-', $slugBase), '-');

    // Build Specifications Dictionary
    $specs = array_filter([
        'Manufacturer'        => $brand,
        'Model'               => $model,
        'Equipment Type'      => trim($data['equipment_type'] ?? $data['category']),
        'Year of Manufacture' => trim($data['year']),
        'Capacity'            => trim($data['capacity'] ?? ''),
        'Slide Stroke'        => trim($data['stroke_length'] ?? ''),
        'Operating Speed'     => trim($data['speed_spm'] ?? ''),
        'Die Height'          => trim($data['die_height'] ?? ''),
        'Main Motor Power'    => trim($data['motor_power'] ?? ''),
        'Machine Weight'      => trim($data['machine_weight'] ?? ''),
        'Dimensions'          => trim($data['dimensions'] ?? ''),
        'Running Hours'       => trim($data['hours_run'] ?? ''),
        'Power Test'          => trim($data['power_test'] ?? 'Ready for Plant Floor Inspection')
    ]);

    // Build Seller Entity
    $sellerCompany = trim($data['seller_company'] ?: ($user['company'] ?? ($user['name'] ?? 'Verified Seller')));
    $contactPerson = trim($data['contact_person'] ?: ($user['name'] ?? 'Technical Sales Desk'));
    $sellerPhone = trim($data['seller_phone'] ?: ($user['phone'] ?? ''));
    $sellerEmail = trim($data['seller_email'] ?: ($user['email'] ?? ''));
    $sellerEntity = [
        'name'         => $sellerCompany,
        'contact'      => $contactPerson,
        'phone'        => $sellerPhone,
        'email'        => $sellerEmail,
        'verified'     => true,
        'rating'       => 5.0,
        'deals_closed' => 18
    ];

    // Ensure leading slash on image URLs
    $formattedImages = array_map(function($img) {
        return '/' . ltrim($img, '/');
    }, $uploadedImageUrls);

    // Prepare Product Record
    $productRecord = [
        'id'              => $id,
        'product_ref'     => $productRef,
        'slug'            => $slug,
        'user_id'         => $userId,
        'title'           => $cleanTitle,
        'brand'           => $brand,
        'model'           => $model,
        'category'        => trim($data['category']),
        'equipment_type'  => trim($data['equipment_type']),
        'year'            => trim($data['year']),
        'date_of_mfg'     => trim($data['date_of_mfg']),
        'currency_origin' => trim($data['currency_origin'] ?? 'USD'),
        'value_raw'       => trim($data['value_raw']),
        'price'           => $priceInr,
        'price_usd'       => $priceUsd,
        'price_display'   => $priceDisplay,
        'country'         => trim($data['country']),
        'city'            => trim($data['city']),
        'location'        => 'Direct Plant Floor • ' . trim($data['city'] . ', ' . $data['country']),
        'plant_address'   => trim($data['plant_address']),
        'condition'       => trim($data['condition_state'] ?? 'Used'),
        'condition_state' => trim($data['condition_state'] ?? 'Used'),
        'hours_run'       => trim($data['hours_run'] ?? 'Plant Maintained'),
        'power_test'      => trim($data['power_test'] ?? 'Available for Factory Floor Inspection'),
        'capacity'        => trim($data['capacity'] ?? ''),
        'stroke_length'   => trim($data['stroke_length'] ?? ''),
        'speed_spm'       => trim($data['speed_spm'] ?? ''),
        'die_height'      => trim($data['die_height'] ?? ''),
        'motor_power'     => trim($data['motor_power'] ?? ''),
        'machine_weight'  => trim($data['machine_weight'] ?? ''),
        'dimensions'      => trim($data['dimensions'] ?? ''),
        'short_desc'      => trim($data['short_desc']),
        'detailed_desc'   => trim($data['detailed_desc']),
        'features'        => $features,
        'specs'           => $specs,
        'seller'          => $sellerEntity,
        'images'          => $formattedImages,
        'seller_company'  => $sellerCompany,
        'contact_person'  => $contactPerson,
        'seller_phone'    => $sellerPhone,
        'seller_email'    => $sellerEmail,
        'status'          => 'active',
        'created_at'      => date('Y-m-d H:i:s')
    ];

    // 5. Save to MySQL Database (`seller_products` table)
    $dbSaved = false;
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO `seller_products` (
                    `product_ref`, `user_id`, `title`, `brand`, `model`, `category`, `equipment_type`, 
                    `year`, `date_of_mfg`, `currency_origin`, `value_raw`, `price_usd`, `country`, 
                    `city`, `plant_address`, `condition_state`, `hours_run`, `power_test`, 
                    `capacity`, `stroke_length`, `speed_spm`, `die_height`, `motor_power`, 
                    `machine_weight`, `dimensions`, `short_desc`, `detailed_desc`, `features_json`, 
                    `images_json`, `seller_company`, `contact_person`, `seller_phone`, `seller_email`, `status`
                ) VALUES (
                    :ref, :uid, :title, :brand, :model, :cat, :eq_type,
                    :year, :mfg, :curr, :val_raw, :usd, :country,
                    :city, :addr, :cond, :hrs, :pwr,
                    :cap, :stroke, :speed, :die, :motor,
                    :weight, :dim, :sdesc, :ddesc, :feats,
                    :imgs, :comp, :person, :phone, :email, 'active'
                )
            ");
            $dbSaved = $stmt->execute([
                ':ref'     => $productRecord['product_ref'],
                ':uid'     => $productRecord['user_id'],
                ':title'   => $productRecord['title'],
                ':brand'   => $productRecord['brand'],
                ':model'   => $productRecord['model'],
                ':cat'     => $productRecord['category'],
                ':eq_type' => $productRecord['equipment_type'],
                ':year'    => $productRecord['year'],
                ':mfg'     => $productRecord['date_of_mfg'],
                ':curr'    => $productRecord['currency_origin'],
                ':val_raw' => $productRecord['value_raw'],
                ':usd'     => $productRecord['price_usd'],
                ':country' => $productRecord['country'],
                ':city'    => $productRecord['city'],
                ':addr'    => $productRecord['plant_address'],
                ':cond'    => $productRecord['condition_state'],
                ':hrs'     => $productRecord['hours_run'],
                ':pwr'     => $productRecord['power_test'],
                ':cap'     => $productRecord['capacity'],
                ':stroke'  => $productRecord['stroke_length'],
                ':speed'   => $productRecord['speed_spm'],
                ':die'     => $productRecord['die_height'],
                ':motor'   => $productRecord['motor_power'],
                ':weight'  => $productRecord['machine_weight'],
                ':dim'     => $productRecord['dimensions'],
                ':sdesc'   => $productRecord['short_desc'],
                ':ddesc'   => $productRecord['detailed_desc'],
                ':feats'   => json_encode($productRecord['features'], JSON_UNESCAPED_UNICODE),
                ':imgs'    => json_encode($productRecord['images'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ':comp'    => $productRecord['seller_company'],
                ':person'  => $productRecord['contact_person'],
                ':phone'   => $productRecord['seller_phone'],
                ':email'   => $productRecord['seller_email']
            ]);
        } catch (Throwable $e) {
            error_log("Database insert error in seller_products: " . $e->getMessage());
        }
    }

    // 6. Save / Append to `data/seller_products.json`
    $jsonFile = __DIR__ . '/../data/seller_products.json';
    $existing = [];
    if (file_exists($jsonFile)) {
        $raw = file_get_contents($jsonFile);
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $existing = $decoded;
        }
    }

    // Prepend new listing so it appears first
    array_unshift($existing, $productRecord);
    file_put_contents($jsonFile, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

    return [
        'success'  => true,
        'message'  => "Machinery listing published successfully!",
        'ref'      => $productRef,
        'product'  => $productRecord,
        'db_saved' => $dbSaved
    ];
}

/**
 * Retrieves all uploaded products for a specific seller or all sellers.
 *
 * @param string|null $userId
 * @return array
 */
function getSellerProductsList($userId = null) {
    $products = [];

    // Try MySQL first
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $sql = "SELECT * FROM `seller_products`";
            $params = [];
            if (!empty($userId)) {
                $sql .= " WHERE `user_id` = :uid";
                $params[':uid'] = $userId;
            }
            $sql .= " ORDER BY `id` DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            foreach ($rows as $row) {
                $row['features'] = json_decode($row['features_json'] ?? '[]', true) ?: [];
                $row['images'] = json_decode($row['images_json'] ?? '[]', true) ?: [];
                $products[$row['product_ref']] = $row;
            }
        } catch (Throwable $e) {
            error_log("Error querying seller_products: " . $e->getMessage());
        }
    }

    // Merge with local JSON file
    $jsonFile = __DIR__ . '/../data/seller_products.json';
    if (file_exists($jsonFile)) {
        $raw = file_get_contents($jsonFile);
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                if (empty($userId) || (isset($item['user_id']) && $item['user_id'] === $userId)) {
                    if (!isset($products[$item['product_ref']])) {
                        $products[$item['product_ref']] = $item;
                    }
                }
            }
        }
    }

    return array_values($products);
}
