<?php
// Joule Machine Hub - Form Lead Processing & MySQL Database Storage
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';

// Allow POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$notificationEmails = [
    'balamuruganp0405@gmail.com',
    'jouleassetvaluers@gmail.com',
    'joulemachinehub@gmail.com'
];
$adminEmailList = implode(', ', $notificationEmails);
$adminEmail = 'joulemachinehub@gmail.com';
$timestamp = date('Y-m-d H:i:s T');
$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

// Extract data from POST or JSON input
$input = $_POST;
if (empty($input)) {
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $input = json_decode($rawInput, true) ?? [];
    }
}

$formType = trim($input['form_type'] ?? 'inquiry');
$leadRef = uniqid('lead_' . date('Ymd_'));

// Initialize normalized database fields
$subject = '';
$leadData = [];
$summaryText = '';

$dbMachineTitle = null;
$dbMachineId = null;
$dbMachinePrice = null;
$dbSellerName = null;
$dbName = null;
$dbCompany = null;
$dbPhone = null;
$dbEmail = null;
$dbCity = null;
$dbBudget = null;
$dbTimeline = null;
$dbNote = null;
$dbCategory = null;
$dbBrand = null;
$dbYear = null;
$dbCondition = null;

if ($formType === 'inquiry') {
    $dbMachineTitle = trim($input['machine_title'] ?? 'Industrial Machine');
    $dbMachineId = trim($input['machine_id'] ?? 'N/A');
    $dbMachinePrice = trim($input['machine_price'] ?? 'N/A');
    $dbSellerName = trim($input['seller_name'] ?? 'N/A');
    $dbName = trim($input['name'] ?? 'Anonymous Buyer');
    $dbPhone = trim($input['phone'] ?? 'N/A');
    $dbEmail = trim($input['email'] ?? '');
    $dbCity = trim($input['city'] ?? 'N/A');
    $dbBudget = trim($input['budget'] ?? 'N/A');
    $dbTimeline = trim($input['timeline'] ?? 'N/A');
    $dbNote = trim($input['note'] ?? 'No notes provided');

    $subject = "🔥 [JMH Lead] Machine Inquiry for {$dbMachineTitle} from {$dbName}";
    $summaryText = "New machine purchase inquiry submitted on Joule Machine Hub.";

    $leadData = [
        'Form Type' => 'Buyer Machine Inquiry',
        'Machine Title' => $dbMachineTitle,
        'Machine ID' => $dbMachineId,
        'Listed Price' => $dbMachinePrice,
        'Original Seller' => $dbSellerName,
        'Buyer Name / Company' => $dbName,
        'Buyer Mobile (WhatsApp)' => $dbPhone,
        'Buyer Email' => $dbEmail,
        'Buyer Location' => $dbCity,
        'Target Budget Offer' => $dbBudget,
        'Purchase Timeline' => $dbTimeline,
        'Buyer Note / Question' => $dbNote,
        'Submitted At' => $timestamp,
        'IP Address' => $clientIp
    ];

} elseif ($formType === 'post_machine') {
    $dbMachineTitle = trim($input['machine_name'] ?? 'N/A');
    $dbCategory = trim($input['category'] ?? 'N/A');
    $dbBrand = trim($input['brand'] ?? 'N/A');
    $dbYear = trim($input['year'] ?? 'N/A');
    $dbMachinePrice = trim($input['price'] ?? 'N/A');
    $dbCondition = trim($input['condition'] ?? 'N/A');
    $dbCompany = trim($input['company_name'] ?? 'N/A');
    $dbName = trim($input['contact_person'] ?? 'N/A');
    $dbPhone = trim($input['seller_mobile'] ?? 'N/A');
    $dbEmail = trim($input['seller_email'] ?? '');

    $subject = "📦 [JMH Listing] New Machine for Sale: {$dbMachineTitle} by {$dbCompany}";
    $summaryText = "A seller has submitted a new used machine listing for approval.";

    $leadData = [
        'Form Type' => 'Post Machine Listing (Seller)',
        'Machine Name & Model' => $dbMachineTitle,
        'Category' => $dbCategory,
        'Brand / Make' => $dbBrand,
        'Year of Make' => $dbYear,
        'Demand Price' => $dbMachinePrice,
        'Condition' => $dbCondition,
        'Company / Plant Name' => $dbCompany,
        'Contact Person' => $dbName,
        'Seller Mobile' => $dbPhone,
        'Seller Email' => $dbEmail,
        'Submitted At' => $timestamp,
        'IP Address' => $clientIp
    ];

} elseif ($formType === 'requirement') {
    $dbMachineTitle = trim($input['required_machine'] ?? 'N/A');
    $dbBudget = trim($input['budget'] ?? 'N/A');
    $dbCity = trim($input['preferred_city'] ?? 'N/A');
    $dbName = trim($input['buyer_name'] ?? 'N/A');
    $dbPhone = trim($input['buyer_phone'] ?? 'N/A');
    $dbEmail = trim($input['buyer_email'] ?? '');

    $subject = "📢 [JMH RFQ] New Buy Requirement: {$dbMachineTitle} from {$dbName}";
    $summaryText = "A buyer has posted a custom machinery requirement (RFQ).";

    $leadData = [
        'Form Type' => 'Post Buy Requirement (RFQ)',
        'Required Machine' => $dbMachineTitle,
        'Budget Range' => $dbBudget,
        'Preferred City' => $dbCity,
        'Buyer Name' => $dbName,
        'Buyer Mobile' => $dbPhone,
        'Buyer Email' => $dbEmail,
        'Submitted At' => $timestamp,
        'IP Address' => $clientIp
    ];

} else {
    // Generic contact form fallback
    $dbName = trim($input['name'] ?? 'Anonymous');
    $dbPhone = trim($input['phone'] ?? 'N/A');
    $dbEmail = trim($input['email'] ?? '');
    $dbNote = trim($input['message'] ?? $input['note'] ?? '');
    $subject = "📬 [JMH Contact] New Submission from {$dbName}";
    $summaryText = "New form submission received on Joule Machine Hub.";
    $leadData = array_merge(['Form Type' => 'General Inquiry'], $input, [
        'Submitted At' => $timestamp,
        'IP Address' => $clientIp
    ]);
}

// 1. SAVE DIRECTLY INTO HOSTINGER MYSQL DATABASE (`leads` TABLE)
$dbSaved = false;
$pdo = getDbConnection();
if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO `leads` (
                `lead_ref`, `form_type`, `machine_title`, `machine_id`, `machine_price`,
                `seller_name`, `name`, `company`, `phone`, `email`,
                `city`, `budget`, `timeline`, `note`, `category`,
                `brand`, `year`, `condition_state`, `raw_json`, `ip_address`
            ) VALUES (
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?
            )
        ");
        $stmt->execute([
            $leadRef,
            $formType,
            $dbMachineTitle,
            $dbMachineId,
            $dbMachinePrice,
            $dbSellerName,
            $dbName,
            $dbCompany,
            $dbPhone,
            $dbEmail,
            $dbCity,
            $dbBudget,
            $dbTimeline,
            $dbNote,
            $dbCategory,
            $dbBrand,
            $dbYear,
            $dbCondition,
            json_encode($leadData, JSON_UNESCAPED_UNICODE),
            $clientIp
        ]);
        $dbSaved = true;
    } catch (Throwable $e) {
        error_log("Failed to insert lead into MySQL database: " . $e->getMessage());
    }
}

// 2. DUAL PERSISTENT STORAGE IN LOCAL JSON (Zero Lost Leads Guarantee)
$leadsDir = __DIR__ . '/../data';
$leadsFile = $leadsDir . '/leads.json';

if (!is_dir($leadsDir)) {
    @mkdir($leadsDir, 0755, true);
}

$existingLeads = [];
if (file_exists($leadsFile)) {
    $raw = @file_get_contents($leadsFile);
    if (!empty($raw)) {
        $existingLeads = json_decode($raw, true) ?? [];
    }
}

array_unshift($existingLeads, [
    'id' => $leadRef,
    'type' => $formType,
    'db_saved' => $dbSaved,
    'timestamp' => $timestamp,
    'data' => $leadData
]);

if (count($existingLeads) > 500) {
    $existingLeads = array_slice($existingLeads, 0, 500);
}
@file_put_contents($leadsFile, json_encode($existingLeads, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// 3. CONSTRUCT PROFESSIONAL HTML EMAIL NOTIFICATION
$cleanPhone = preg_replace('/[^0-9]/', '', $dbPhone ?? '');

$rowsHtml = '';
foreach ($leadData as $key => $val) {
    if (empty($val)) continue;
    $rowsHtml .= "
        <tr>
            <td style='padding: 10px 14px; font-weight: bold; color: #1e293b; background: #f8fafc; border-bottom: 1px solid #e2e8f0; width: 35%;'>
                " . htmlspecialchars($key) . "
            </td>
            <td style='padding: 10px 14px; color: #0f172a; border-bottom: 1px solid #e2e8f0;'>
                " . nl2br(htmlspecialchars($val)) . "
            </td>
        </tr>
    ";
}

$whatsappButton = '';
if (!empty($cleanPhone)) {
    $whatsappUrl = "https://wa.me/" . $cleanPhone;
    $whatsappButton = "
        <div style='margin-top: 24px; text-align: center;'>
            <a href='{$whatsappUrl}' style='display: inline-block; background: #16a34a; color: #ffffff; font-weight: bold; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-size: 14px;'>
                💬 Reply on WhatsApp ({$cleanPhone})
            </a>
        </div>
    ";
}

$emailHtml = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>" . htmlspecialchars($subject) . "</title>
</head>
<body style='margin: 0; padding: 20px; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; line-height: 1.5;'>
    <div style='max-width: 650px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 2px solid #0d2847;'>
        
        <!-- Header -->
        <div style='background: #0d2847; color: #ffffff; padding: 24px; border-bottom: 4px solid #facc15;'>
            <h1 style='margin: 0 0 6px 0; font-size: 20px; font-weight: 800; color: #ffffff;'>
                ⚡ Joule Machine Hub
            </h1>
            <p style='margin: 0; font-size: 13px; color: #facc15; font-weight: bold;'>
                " . htmlspecialchars($summaryText) . "
            </p>
        </div>

        <!-- Body Table -->
        <div style='padding: 24px;'>
            <h2 style='margin: 0 0 16px 0; font-size: 16px; color: #0d2847; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;'>
                📋 Lead Details (Saved to Database)
            </h2>
            <table style='width: 100%; border-collapse: collapse; font-size: 13px;'>
                {$rowsHtml}
            </table>

            {$whatsappButton}
        </div>

        <!-- Footer -->
        <div style='background: #f8fafc; padding: 16px 24px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 11px; color: #64748b;'>
            Automated lead alert delivered to: <strong>{$adminEmailList}</strong> from Joule Machine Hub.<br>
            All lead submissions are securely stored in MySQL Database <code>u948237985_joulehub</code> & archived in <code>data/leads.json</code>.
        </div>

    </div>
</body>
</html>
";

require_once __DIR__ . '/../includes/mailer.php';

// 4. SEND EMAIL VIA HOSTINGER-OPTIMIZED MAILER TO ALL 3 RECIPIENTS INDIVIDUALLY
$mailResults = JmhMailer::sendLeadNotification($leadData, $subject, $summaryText);
$anyMailSent = false;
foreach ($mailResults as $r) {
    if (!empty($r['sent'])) {
        $anyMailSent = true;
        break;
    }
}

// 5. RETURN SUCCESS RESPONSE / REDIRECT URL
$redirectUrl = 'thank-you.php?type=' . urlencode($formType) . '&ref=' . urlencode($leadRef) . '&machine=' . urlencode($dbMachineTitle ?? '');

// If regular non-AJAX form submission, redirect directly
if (!empty($_POST) && (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') && (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded'))) {
    header("Location: ../" . $redirectUrl);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Your details have been submitted and saved successfully.',
    'lead_ref' => $leadRef,
    'db_saved' => $dbSaved,
    'mail_sent' => $anyMailSent,
    'mail_results' => $mailResults,
    'redirect' => $redirectUrl
]);
