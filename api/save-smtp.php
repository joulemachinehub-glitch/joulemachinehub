<?php
// Joule Machine Hub - Save SMTP Configuration API
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mailer.php';

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Admin authorization required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'POST method required']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?: $_POST;

$enabled = !empty($input['enabled']);
$host = trim($input['host'] ?? 'smtp.hostinger.com');
$port = intval($input['port'] ?? 465);
$encryption = trim($input['encryption'] ?? 'ssl');
$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? '');
$fromEmail = trim($input['from_email'] ?? $username);
$fromName = trim($input['from_name'] ?? 'Joule Machine Hub');

// If password was empty but file exists, keep existing password
$current = JmhMailer::getSmtpConfig();
if (empty($password) && !empty($current['password'])) {
    $password = $current['password'];
}

$saved = JmhMailer::saveSmtpConfig([
    'enabled' => $enabled,
    'host' => $host,
    'port' => $port,
    'encryption' => $encryption,
    'username' => $username,
    'password' => $password,
    'from_email' => $fromEmail,
    'from_name' => $fromName
]);

echo json_encode([
    'success' => $saved,
    'message' => $saved ? 'SMTP configuration saved successfully' : 'Failed to save configuration',
    'config' => [
        'enabled' => $enabled,
        'host' => $host,
        'port' => $port,
        'username' => $username,
        'from_email' => $fromEmail
    ]
]);
