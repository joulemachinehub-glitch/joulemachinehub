<?php
// Joule Machine Hub - Enterprise Mailer & Anti-Spam Inbox Engine
// Supports Authenticated SMTP (Hostinger SSL/TLS) + Fallback to RFC-Compliant Multipart PHP mail()

class JmhMailer {
    const RECIPIENTS = [
        'balamuruganp0405@gmail.com',
        'jouleassetvaluers@gmail.com',
        'joulemachinehub@gmail.com'
    ];

    const DEFAULT_FROM_NAME = 'Joule Machine Hub Leads';
    const DEFAULT_FROM_EMAIL = 'notifications@joulemachinehub.com';

    /**
     * Load SMTP configuration from data/smtp_config.json
     */
    public static function getSmtpConfig(): array {
        $configFile = __DIR__ . '/../data/smtp_config.json';
        if (file_exists($configFile)) {
            $cfg = json_decode(@file_get_contents($configFile), true);
            if (!empty($cfg['host']) && !empty($cfg['username']) && !empty($cfg['password'])) {
                return $cfg;
            }
        }
        return [
            'enabled' => false,
            'host' => 'smtp.hostinger.com',
            'port' => 465,
            'encryption' => 'ssl', // ssl or tls
            'username' => '',
            'password' => '',
            'from_email' => self::DEFAULT_FROM_EMAIL,
            'from_name' => self::DEFAULT_FROM_NAME
        ];
    }

    /**
     * Save SMTP configuration
     */
    public static function saveSmtpConfig(array $cfg): bool {
        $configFile = __DIR__ . '/../data/smtp_config.json';
        $data = [
            'enabled' => !empty($cfg['enabled']),
            'host' => trim($cfg['host'] ?? 'smtp.hostinger.com'),
            'port' => intval($cfg['port'] ?? 465),
            'encryption' => trim($cfg['encryption'] ?? 'ssl'),
            'username' => trim($cfg['username'] ?? ''),
            'password' => trim($cfg['password'] ?? ''),
            'from_email' => trim($cfg['from_email'] ?? self::DEFAULT_FROM_EMAIL),
            'from_name' => trim($cfg['from_name'] ?? self::DEFAULT_FROM_NAME),
            'updated_at' => date('Y-m-d H:i:s T')
        ];
        return (bool)@file_put_contents($configFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Send lead inquiry notification to all registered admin emails
     */
    public static function sendLeadNotification(array $leadData, string $subject, string $summaryText): array {
        $cleanPhone = preg_replace('/[^0-9]/', '', $leadData['Buyer Mobile (WhatsApp)'] ?? $leadData['phone'] ?? $leadData['Seller Mobile'] ?? '');
        $clientEmail = $leadData['Buyer Email'] ?? $leadData['email'] ?? $leadData['Seller Email'] ?? '';

        // Clean subject from spam trigger characters
        $cleanSubject = preg_replace('/[\xF0-\xF7].../s', '', $subject);
        $cleanSubject = trim(str_replace(['🔥', '📦', '📢', '📬', '⚡'], '', $cleanSubject));
        if (!str_starts_with($cleanSubject, '[Joule Machine Hub]')) {
            $cleanSubject = '[Joule Machine Hub] ' . $cleanSubject;
        }

        $rowsHtml = '';
        $plainContent = "JOULE MACHINE HUB - NEW LEAD NOTIFICATION\n";
        $plainContent .= "==========================================\n";
        $plainContent .= "$summaryText\n\n";

        foreach ($leadData as $key => $val) {
            if (empty($val) || is_array($val)) continue;
            $valStr = (string)$val;
            $rowsHtml .= "
                <tr>
                    <td style='padding: 10px 14px; font-weight: 700; color: #0d2847; background: #f8fafc; border-bottom: 1px solid #e2e8f0; width: 35%; vertical-align: top; font-size: 13px;'>
                        " . htmlspecialchars($key) . "
                    </td>
                    <td style='padding: 10px 14px; color: #0f172a; border-bottom: 1px solid #e2e8f0; vertical-align: top; font-size: 13px;'>
                        " . nl2br(htmlspecialchars($valStr)) . "
                    </td>
                </tr>
            ";
            $plainContent .= "$key: $valStr\n";
        }

        if (!empty($cleanPhone)) {
            $plainContent .= "\nWhatsApp Direct Link: https://wa.me/$cleanPhone\n";
        }
        $plainContent .= "\nDelivered from: https://joulemachinehub.com\n";

        $whatsappButton = '';
        if (!empty($cleanPhone)) {
            $whatsappUrl = "https://wa.me/" . $cleanPhone;
            $whatsappButton = "
                <div style='margin-top: 24px; text-align: center;'>
                    <a href='{$whatsappUrl}' style='display: inline-block; background: #16a34a; color: #ffffff; font-weight: 800; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-size: 14px;'>
                        💬 Reply Directly on WhatsApp ({$cleanPhone})
                    </a>
                </div>
            ";
        }

        $htmlBody = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>" . htmlspecialchars($cleanSubject) . "</title>
</head>
<body style='margin: 0; padding: 20px; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; line-height: 1.6;'>
    <div style='max-width: 650px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 18px rgba(0,0,0,0.06); border: 2px solid #0d2847;'>
        
        <!-- Header -->
        <div style='background: #0d2847; color: #ffffff; padding: 22px 24px; border-bottom: 4px solid #facc15;'>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td>
                        <h1 style='margin: 0; font-size: 20px; font-weight: 800; color: #ffffff; letter-spacing: 0.5px;'>
                            ⚡ JOULE MACHINE HUB
                        </h1>
                        <p style='margin: 4px 0 0 0; font-size: 12px; color: #facc15; font-weight: 700; text-transform: uppercase;'>
                            " . htmlspecialchars($summaryText) . "
                        </p>
                    </td>
                    <td style='text-align: right; vertical-align: middle;'>
                        <span style='background: #facc15; color: #0d2847; font-weight: 800; font-size: 11px; padding: 4px 10px; border-radius: 6px; text-transform: uppercase;'>
                            DIRECT LEAD
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Body -->
        <div style='padding: 24px;'>
            <p style='margin: 0 0 16px 0; font-size: 14px; color: #334155;'>
                A new inquiry has been submitted on <strong>Joule Machine Hub</strong>. All lead details are recorded below:
            </p>

            <table style='width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;'>
                {$rowsHtml}
            </table>

            {$whatsappButton}
        </div>

        <!-- Footer -->
        <div style='background: #f8fafc; padding: 16px 24px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 11px; color: #64748b;'>
            Lead notification delivered to: <strong>" . implode(', ', self::RECIPIENTS) . "</strong><br>
            Protected by Joule Machine Hub • Chennai & Global Hubs • <a href='https://joulemachinehub.com' style='color: #0284c7; text-decoration: none;'>joulemachinehub.com</a>
        </div>
    </div>
</body>
</html>
        ";

        return self::sendToAll($cleanSubject, $htmlBody, $plainContent, $clientEmail);
    }

    /**
     * Dispatch email to all 3 admin addresses individually
     */
    public static function sendToAll(string $subject, string $htmlBody, string $plainText = '', string $replyTo = ''): array {
        $results = [];
        $smtpConfig = self::getSmtpConfig();
        $useSmtp = !empty($smtpConfig['enabled']) && !empty($smtpConfig['username']) && !empty($smtpConfig['password']);

        $fromEmail = !empty($smtpConfig['from_email']) ? $smtpConfig['from_email'] : self::DEFAULT_FROM_EMAIL;
        $fromName = !empty($smtpConfig['from_name']) ? $smtpConfig['from_name'] : self::DEFAULT_FROM_NAME;

        if (empty($replyTo)) {
            $replyTo = 'joulemachinehub@gmail.com';
        }

        if (empty($plainText)) {
            $plainText = strip_tags(str_replace(['<br>', '</td>', '</tr>', '</p>'], "\n", $htmlBody));
        }

        foreach (self::RECIPIENTS as $recipient) {
            $sent = false;
            $error = null;
            $methodUsed = 'mail';

            // Method 1: Authenticated Hostinger SMTP (Guaranteed 100% Primary Inbox)
            if ($useSmtp) {
                try {
                    $smtpRes = self::sendViaSmtp($smtpConfig, $recipient, $subject, $htmlBody, $plainText, $fromEmail, $fromName, $replyTo);
                    if ($smtpRes['success']) {
                        $sent = true;
                        $methodUsed = 'smtp';
                    } else {
                        $error = 'SMTP error: ' . $smtpRes['error'];
                    }
                } catch (Throwable $e) {
                    $error = 'SMTP Exception: ' . $e->getMessage();
                }
            }

            // Method 2: High-Deliverability Multipart RFC PHP mail() Fallback
            if (!$sent) {
                $boundary = '=_NextPart_' . md5(uniqid(microtime(true), true));
                $messageId = sprintf('<%s.%s@joulemachinehub.com>', uniqid(time()), bin2hex(random_bytes(4)));

                $headers = [];
                $headers[] = 'MIME-Version: 1.0';
                $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";
                $headers[] = "From: {$fromName} <{$fromEmail}>";
                $headers[] = "Reply-To: <{$replyTo}>";
                $headers[] = "Return-Path: <{$fromEmail}>";
                $headers[] = "Sender: <{$fromEmail}>";
                $headers[] = 'Date: ' . date('r');
                $headers[] = 'Message-ID: ' . $messageId;
                $headers[] = 'X-Mailer: JouleMachineHub Enterprise Mailer';
                $headers[] = 'Auto-Submitted: auto-generated';
                $headers[] = 'X-Auto-Response-Suppress: All';
                $headers[] = 'Priority: normal';
                $headers[] = 'X-Priority: 3';

                $multipartBody = "--{$boundary}\r\n";
                $multipartBody .= "Content-Type: text/plain; charset=UTF-8\r\n";
                $multipartBody .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                $multipartBody .= $plainText . "\r\n\r\n";
                $multipartBody .= "--{$boundary}\r\n";
                $multipartBody .= "Content-Type: text/html; charset=UTF-8\r\n";
                $multipartBody .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                $multipartBody .= $htmlBody . "\r\n\r\n";
                $multipartBody .= "--{$boundary}--\r\n";

                $headersString = implode("\r\n", $headers);
                $additionalParams = '-f ' . $fromEmail;

                try {
                    $sent = @mail($recipient, $subject, $multipartBody, $headersString, $additionalParams);
                } catch (Throwable $e) {
                    $error = ($error ? $error . ' | ' : '') . $e->getMessage();
                }

                if (!$sent) {
                    try {
                        $sent = @mail($recipient, $subject, $multipartBody, $headersString);
                    } catch (Throwable $e) {
                        $error = ($error ? $error . ' | ' : '') . $e->getMessage();
                    }
                }
            }

            $results[$recipient] = [
                'sent' => $sent,
                'method' => $methodUsed,
                'error' => $error,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }

        self::logDelivery($subject, $results);

        return $results;
    }

    /**
     * Pure-PHP Socket SMTP Sender (Zero Composer dependencies, works on all PHP servers)
     */
    private static function sendViaSmtp(array $cfg, string $to, string $subject, string $htmlBody, string $plainText, string $fromEmail, string $fromName, string $replyTo): array {
        $host = $cfg['host'] ?? 'smtp.hostinger.com';
        $port = intval($cfg['port'] ?? 465);
        $user = $cfg['username'] ?? '';
        $pass = $cfg['password'] ?? '';
        $enc = strtolower($cfg['encryption'] ?? 'ssl');

        $protocol = ($enc === 'ssl') ? 'ssl://' : '';
        $timeout = 15;

        $socket = @fsockopen($protocol . $host, $port, $errno, $errstr, $timeout);
        if (!$socket) {
            return ['success' => false, 'error' => "Cannot connect to $host:$port ($errstr)"];
        }

        stream_set_timeout($socket, $timeout);

        $readResponse = function() use ($socket) {
            $resp = '';
            while ($str = fgets($socket, 515)) {
                $resp .= $str;
                if (substr($str, 3, 1) === ' ') break;
            }
            return $resp;
        };

        $sendCommand = function($cmd, $expectedCode) use ($socket, $readResponse) {
            fputs($socket, $cmd . "\r\n");
            $resp = $readResponse();
            $code = substr($resp, 0, 3);
            if ($code !== (string)$expectedCode) {
                throw new Exception("SMTP command failed [$cmd]: $resp");
            }
            return $resp;
        };

        try {
            $readResponse(); // Read server greeting

            $sendCommand("EHLO " . ($_SERVER['HTTP_HOST'] ?? 'joulemachinehub.com'), 250);

            if ($enc === 'tls') {
                $sendCommand("STARTTLS", 220);
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $sendCommand("EHLO " . ($_SERVER['HTTP_HOST'] ?? 'joulemachinehub.com'), 250);
            }

            // Auth Login
            $sendCommand("AUTH LOGIN", 334);
            $sendCommand(base64_encode($user), 334);
            $sendCommand(base64_encode($pass), 235);

            // Mail From & Rcpt To
            $sendCommand("MAIL FROM: <$fromEmail>", 250);
            $sendCommand("RCPT TO: <$to>", 250);

            // Data
            $sendCommand("DATA", 354);

            $boundary = '=_NextPart_' . md5(uniqid(microtime(true), true));
            $messageId = sprintf('<%s.%s@joulemachinehub.com>', uniqid(time()), bin2hex(random_bytes(4)));

            $emailHeaders = "From: {$fromName} <{$fromEmail}>\r\n";
            $emailHeaders .= "To: <{$to}>\r\n";
            $emailHeaders .= "Reply-To: <{$replyTo}>\r\n";
            $emailHeaders .= "Subject: {$subject}\r\n";
            $emailHeaders .= "Date: " . date('r') . "\r\n";
            $emailHeaders .= "Message-ID: {$messageId}\r\n";
            $emailHeaders .= "MIME-Version: 1.0\r\n";
            $emailHeaders .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
            $emailHeaders .= "Auto-Submitted: auto-generated\r\n";
            $emailHeaders .= "X-Mailer: JouleMachineHub SMTP Client\r\n\r\n";

            $payload = $emailHeaders;
            $payload .= "--{$boundary}\r\n";
            $payload .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $payload .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $payload .= $plainText . "\r\n\r\n";
            $payload .= "--{$boundary}\r\n";
            $payload .= "Content-Type: text/html; charset=UTF-8\r\n";
            $payload .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $payload .= $htmlBody . "\r\n\r\n";
            $payload .= "--{$boundary}--\r\n";
            $payload .= "\r\n.\r\n";

            fputs($socket, $payload);
            $dataResp = $readResponse();
            if (substr($dataResp, 0, 3) !== '250') {
                throw new Exception("DATA rejected: " . $dataResp);
            }

            $sendCommand("QUIT", 221);
            fclose($socket);

            return ['success' => true];
        } catch (Throwable $e) {
            @fclose($socket);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Log dispatch results to data/mail_log.json
     */
    private static function logDelivery(string $subject, array $results): void {
        $logFile = __DIR__ . '/../data/mail_log.json';
        $logs = [];
        if (file_exists($logFile)) {
            $logs = json_decode(@file_get_contents($logFile), true) ?: [];
        }

        array_unshift($logs, [
            'subject' => $subject,
            'timestamp' => date('Y-m-d H:i:s T'),
            'results' => $results
        ]);

        if (count($logs) > 100) {
            $logs = array_slice($logs, 0, 100);
        }

        @file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
