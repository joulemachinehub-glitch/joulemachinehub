<?php
// Joule Machine Hub - Admin Notification & Approval Alert Helpers
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mailer.php';

/**
 * Return path to local admin notifications JSON store.
 */
function getAdminNotificationsFile(): string {
    return __DIR__ . '/../data/admin_notifications.json';
}

/**
 * Record an admin notification in both MySQL (`admin_notifications`) and `data/admin_notifications.json`.
 *
 * @param string $type Event key such as 'product_submitted', 'product_updated', or 'new_account'
 * @param string $title Short headline for the admin feed
 * @param string $message Descriptive summary for the admin dashboard
 * @param array $meta Additional structured metadata (product_ref, email, user_id, etc.)
 * @param string $actionUrl Relative Admin Portal deep-link for reviewing the item
 * @return array Saved notification record
 */
function addAdminNotification(
    string $type,
    string $title,
    string $message,
    array $meta = [],
    string $actionUrl = '/admin?tab=approvals'
): array {
    $notifId = 'notif-' . date('YmdHis') . '-' . substr(md5(uniqid((string)mt_rand(), true)), 0, 6);
    $record = [
        'notif_id'   => $notifId,
        'type'       => $type,
        'title'      => $title,
        'message'    => $message,
        'ref_id'     => (string)($meta['product_ref'] ?? $meta['user_id'] ?? $meta['ref'] ?? ''),
        'meta'       => $meta,
        'action_url' => $actionUrl,
        'is_read'    => false,
        'created_at' => date('Y-m-d H:i:s T')
    ];

    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO `admin_notifications` (
                    `notif_id`, `type`, `title`, `message`, `ref_id`, `meta_json`, `action_url`, `is_read`
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 0)
            ");
            $stmt->execute([
                $record['notif_id'],
                $record['type'],
                $record['title'],
                $record['message'],
                $record['ref_id'],
                json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                $record['action_url']
            ]);
        } catch (Throwable $e) {
            error_log("Admin notification DB insert error: " . $e->getMessage());
        }
    }

    $file = getAdminNotificationsFile();
    $dir = dirname($file);
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }

    $existing = [];
    if (file_exists($file)) {
        $existing = json_decode(@file_get_contents($file), true);
        if (!is_array($existing)) {
            $existing = [];
        }
    }

    array_unshift($existing, $record);
    if (count($existing) > 200) {
        $existing = array_slice($existing, 0, 200);
    }
    @file_put_contents($file, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    return $record;
}

/**
 * Retrieve admin notifications ordered newest first.
 */
function getAdminNotifications(bool $unreadOnly = false): array {
    $byId = [];

    $file = getAdminNotificationsFile();
    if (file_exists($file)) {
        $decoded = json_decode(@file_get_contents($file), true);
        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                $nid = $item['notif_id'] ?? '';
                if ($nid !== '') {
                    $byId[$nid] = $item;
                }
            }
        }
    }

    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM `admin_notifications` ORDER BY `id` DESC LIMIT 200");
            $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            foreach ($rows as $row) {
                $nid = $row['notif_id'] ?? '';
                if ($nid !== '' && !isset($byId[$nid])) {
                    $byId[$nid] = [
                        'notif_id'   => $nid,
                        'type'       => $row['type'] ?? 'info',
                        'title'      => $row['title'] ?? '',
                        'message'    => $row['message'] ?? '',
                        'ref_id'     => $row['ref_id'] ?? '',
                        'meta'       => json_decode($row['meta_json'] ?? '[]', true) ?: [],
                        'action_url' => $row['action_url'] ?? '/admin?tab=approvals',
                        'is_read'    => !empty($row['is_read']),
                        'created_at' => $row['created_at'] ?? ''
                    ];
                } elseif ($nid !== '' && isset($byId[$nid])) {
                    // Preserve read state if marked read in either store
                    $byId[$nid]['is_read'] = !empty($byId[$nid]['is_read']) || !empty($row['is_read']);
                }
            }
        } catch (Throwable $e) {
            error_log("Admin notification DB read error: " . $e->getMessage());
        }
    }

    $list = array_values($byId);
    if ($unreadOnly) {
        $list = array_values(array_filter($list, function ($n) {
            return empty($n['is_read']);
        }));
    }
    return $list;
}

/**
 * Mark one notification (or all notifications when $notifId is null) as read.
 */
function markAdminNotificationsRead(?string $notifId = null, ?string $refId = null): void {
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            if ($notifId !== null && $notifId !== '') {
                $stmt = $pdo->prepare("UPDATE `admin_notifications` SET `is_read` = 1 WHERE `notif_id` = ?");
                $stmt->execute([$notifId]);
            } elseif ($refId !== null && $refId !== '') {
                $stmt = $pdo->prepare("UPDATE `admin_notifications` SET `is_read` = 1 WHERE `ref_id` = ?");
                $stmt->execute([$refId]);
            } else {
                $pdo->exec("UPDATE `admin_notifications` SET `is_read` = 1 WHERE `is_read` = 0");
            }
        } catch (Throwable $e) {
            error_log("Admin notification mark read DB error: " . $e->getMessage());
        }
    }

    $file = getAdminNotificationsFile();
    if (file_exists($file)) {
        $existing = json_decode(@file_get_contents($file), true);
        if (is_array($existing)) {
            foreach ($existing as &$item) {
                $matchId = ($notifId !== null && $notifId !== '' && ($item['notif_id'] ?? '') === $notifId);
                $matchRef = ($refId !== null && $refId !== '' && ($item['ref_id'] ?? '') === $refId);
                if (($notifId === null && $refId === null) || $matchId || $matchRef) {
                    $item['is_read'] = true;
                }
            }
            unset($item);
            @file_put_contents($file, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
    }
}

/**
 * Notify admin by in-app alert and email when a new user account is registered.
 */
function notifyAdminNewAccount(array $user): void {
    $name = trim($user['name'] ?? 'New User');
    $company = trim($user['company'] ?? 'Independent Seller');
    $email = trim($user['email'] ?? '');
    $phone = trim($user['phone'] ?? '');
    $city = trim($user['city'] ?? '');
    $country = trim($user['country'] ?? '');
    $userId = (string)($user['user_id'] ?? $user['id'] ?? '');
    $location = trim($city . ($city && $country ? ', ' : '') . $country);

    addAdminNotification(
        'new_account',
        "New Account Registered: {$name} ({$company})",
        "Account {$email} created from " . ($location ?: 'Global') . ". Ready to submit machine listings for approval.",
        [
            'user_id' => $userId,
            'name'    => $name,
            'company' => $company,
            'email'   => $email,
            'phone'   => $phone,
            'location'=> $location,
            'role'    => $user['role'] ?? 'seller'
        ],
        '/admin?tab=accounts'
    );

    try {
        JmhMailer::sendLeadNotification([
            'Notification Type'   => 'New Registered Seller Account',
            'Account User ID'     => $userId,
            'Contact Name'        => $name,
            'Company / Plant'     => $company,
            'Account Email'       => $email,
            'Mobile / WhatsApp'   => $phone ?: 'Not provided',
            'Location'            => $location ?: 'Not specified',
            'Verification Status' => !empty($user['is_verified']) ? 'Verified Account' : 'Registered',
            'Admin Portal Link'   => 'https://joulemachinehub.com/admin?tab=accounts',
            'Registered At'       => date('Y-m-d H:i:s T')
        ], "New Account Created: {$company} ({$name})", "A new user created an account on Joule Machine Hub.");
    } catch (Throwable $e) {
        error_log("Admin new account email alert failed: " . $e->getMessage());
    }
}

/**
 * Notify admin by in-app alert and email when a user adds or updates a machine awaiting approval.
 */
function notifyAdminProductPendingApproval(array $product, string $eventType = 'product_submitted'): void {
    $ref = (string)($product['product_ref'] ?? $product['id'] ?? '');
    $title = trim($product['title'] ?? 'Machinery Listing');
    $brand = trim($product['brand'] ?? '');
    $model = trim($product['model'] ?? '');
    $category = trim($product['category'] ?? '');
    $company = trim($product['seller_company'] ?? ($product['seller']['name'] ?? 'Seller'));
    $person = trim($product['contact_person'] ?? ($product['seller']['contact_person'] ?? ($product['seller']['contact'] ?? '')));
    $sellerEmail = trim($product['seller_email'] ?? ($product['seller']['email'] ?? ''));
    $sellerPhone = trim($product['seller_phone'] ?? ($product['seller']['phone'] ?? ''));
    $city = trim($product['city'] ?? '');
    $country = trim($product['country'] ?? '');
    $priceDisplay = trim($product['price_display'] ?? $product['value_raw'] ?? 'Price on Request');

    $isResubmit = ($eventType === 'product_updated' || $eventType === 'product_resubmitted');
    $headline = $isResubmit
        ? "Listing Updated (Needs Re-Approval): {$title}"
        : "New Product Pending Approval: {$title}";

    $summary = $isResubmit
        ? "{$company} updated listing #{$ref}. Website publication is paused until Admin approves."
        : "{$company} submitted {$title} (#{$ref}) for Admin approval before publishing to website.";

    addAdminNotification(
        $eventType,
        $headline,
        $summary,
        [
            'product_ref'  => $ref,
            'id'           => (string)($product['id'] ?? $ref),
            'title'        => $title,
            'brand'        => $brand,
            'model'        => $model,
            'category'     => $category,
            'price'        => $priceDisplay,
            'seller_name'  => $person ?: $company,
            'company'      => $company,
            'seller_email' => $sellerEmail,
            'seller_phone' => $sellerPhone,
            'location'     => trim($city . ($city && $country ? ', ' : '') . $country)
        ],
        '/admin?tab=approvals'
    );

    try {
        JmhMailer::sendLeadNotification([
            'Notification Type' => $isResubmit ? 'Product Updated - Admin Approval Needed' : 'New Product Submitted - Admin Approval Needed',
            'Listing Reference' => $ref,
            'Machine Title'     => $title,
            'Brand / Model'     => trim($brand . ' ' . $model),
            'Category'          => $category,
            'Asking Price'      => $priceDisplay,
            'Plant Location'    => trim($city . ($city && $country ? ', ' : '') . $country),
            'Seller Company'    => $company,
            'Contact Person'    => $person ?: 'N/A',
            'Seller Email'      => $sellerEmail ?: 'N/A',
            'Seller Phone'      => $sellerPhone ?: 'N/A',
            'Approval Status'   => 'PENDING ADMIN APPROVAL (Hidden from public website until approved)',
            'Approve / Review'  => 'https://joulemachinehub.com/admin?tab=approvals',
            'Submitted At'      => date('Y-m-d H:i:s T')
        ], $headline, "Action Required: Review and approve this machine listing in the Admin Portal to publish it on joulemachinehub.com.");
    } catch (Throwable $e) {
        error_log("Admin pending product email alert failed: " . $e->getMessage());
    }
}
