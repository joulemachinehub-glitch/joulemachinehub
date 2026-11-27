<?php
// Joule Machine Hub - Admin Product Approval & Notification Actions API
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/seller.php';
require_once __DIR__ . '/../data/machines.php';

if (!isAdmin() || empty($_SESSION['admin_authenticated'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error'   => 'Admin authorization required. Please log in with joulemachinehub@gmail.com.'
    ]);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'POST method required.']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input) || empty($input)) {
    $input = $_POST;
}

$action = strtolower(trim((string)($input['action'] ?? '')));
$adminEmail = $_SESSION['user']['email'] ?? JMH_ADMIN_EMAIL;

if ($action === 'approve') {
    $ref = trim((string)($input['ref'] ?? $input['product_ref'] ?? $input['id'] ?? ''));
    $edits = isset($input['edits']) && is_array($input['edits']) ? $input['edits'] : null;
    $res = approveSellerProduct($ref, $adminEmail, $edits);
    echo json_encode($res);
    exit;
}

if ($action === 'reject') {
    $ref = trim((string)($input['ref'] ?? $input['product_ref'] ?? $input['id'] ?? ''));
    $reason = trim((string)($input['reason'] ?? ''));
    $res = rejectSellerProduct($ref, $reason, $adminEmail);
    echo json_encode($res);
    exit;
}

if ($action === 'mark_notifications_read') {
    $notifId = isset($input['notif_id']) ? trim((string)$input['notif_id']) : null;
    $refId = isset($input['ref_id']) ? trim((string)$input['ref_id']) : null;
    markAdminNotificationsRead($notifId ?: null, $refId ?: null);
    echo json_encode(['success' => true, 'message' => 'Notifications marked as read.']);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Unsupported action.']);
