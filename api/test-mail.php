<?php
// Joule Machine Hub - Mail Transport Diagnostic & Test Dispatcher
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mailer.php';

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Admin authorization required. Access via /admin']);
    exit;
}

$subject = "🧪 [JMH System Test] Live Email Verification - " . date('Y-m-d H:i:s');
$html = "
<div style='font-family: Arial, sans-serif; padding: 20px; background: #f8fafc; border-radius: 12px; border: 2px solid #0d2847;'>
    <h2 style='color: #0d2847; margin: 0 0 10px 0;'>⚡ Joule Machine Hub Email Test</h2>
    <p style='color: #334155;'>This is a diagnostic verification email sent from your live server.</p>
    <div style='background: #e0f2fe; padding: 12px; border-radius: 8px; border-left: 4px solid #0284c7; margin: 15px 0;'>
        <strong>Status:</strong> Mail system is active and routing correctly.<br>
        <strong>Host:</strong> " . htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'joulemachinehub.com') . "<br>
        <strong>Timestamp:</strong> " . date('r') . "<br>
        <strong>PHP Version:</strong> " . phpversion() . "
    </div>
    <p style='font-size: 12px; color: #64748b;'>Delivered to balamuruganp0405@gmail.com, jouleassetvaluers@gmail.com, joulemachinehub@gmail.com</p>
</div>
";

$results = JmhMailer::sendToAll($subject, $html);

echo json_encode([
    'success' => true,
    'message' => 'Test email triggered for all 3 admin addresses',
    'results' => $results,
    'recipients' => JmhMailer::RECIPIENTS
]);
