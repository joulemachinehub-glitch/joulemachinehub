<?php
// Joule Machine Hub - Enterprise SMS Gateway & Mobile OTP Engine
// Supports Twilio, Fast2SMS, Msg91, TextLocal, and Custom HTTP Webhook / Simulation fallback

class JmhSms {
    const LOG_FILE = __DIR__ . '/../data/sms_log.json';
    const CONFIG_FILE = __DIR__ . '/../data/sms_config.json';

    /**
     * Retrieve SMS gateway configuration
     */
    public static function getConfig(): array {
        if (file_exists(self::CONFIG_FILE)) {
            $data = json_decode(@file_get_contents(self::CONFIG_FILE), true);
            if (is_array($data)) {
                return array_merge([
                    'enabled'    => true,
                    'provider'   => 'simulation',
                    'api_key'    => '',
                    'sender_id'  => 'JOULEH',
                    'custom_url' => ''
                ], $data);
            }
        }
        return [
            'enabled'    => true,
            'provider'   => 'simulation',
            'api_key'    => '',
            'sender_id'  => 'JOULEH',
            'custom_url' => ''
        ];
    }

    /**
     * Save SMS gateway configuration
     */
    public static function saveConfig(array $cfg): bool {
        $dir = dirname(self::CONFIG_FILE);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        return (bool)@file_put_contents(
            self::CONFIG_FILE, 
            json_encode($cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Dispatch mobile verification OTP via SMS
     */
    public static function sendOtpSms(string $phone, string $otp, string $recipientName = ''): array {
        $cleanPhone = preg_replace('/[^0-9+]/', '', trim($phone));
        $digitsOnly = preg_replace('/[^0-9]/', '', $cleanPhone);
        
        $cfg = self::getConfig();
        $message = "Your Joule Machine Hub mobile verification code is: {$otp}. Valid for 15 minutes to activate your seller dashboard. Do not share this code.";
        
        $sent = false;
        $error = null;
        $providerUsed = $cfg['provider'] ?? 'simulation';

        // 1. FAST2SMS GATEWAY (Popular for Indian mobile numbers)
        if (!empty($cfg['enabled']) && $providerUsed === 'fast2sms' && !empty($cfg['api_key'])) {
            try {
                $targetNumbers = strlen($digitsOnly) === 10 ? $digitsOnly : substr($digitsOnly, -10);
                $fields = [
                    'variables_values' => $otp,
                    'route'            => 'otp',
                    'numbers'          => $targetNumbers
                ];
                $postData = json_encode($fields);
                $ch = curl_init('https://www.fast2sms.com/dev/bulkV2');
                curl_setopt_array($ch, [
                    CURLOPT_HTTPHEADER     => [
                        "authorization: " . $cfg['api_key'],
                        "Content-Type: application/json"
                    ],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => $postData,
                    CURLOPT_TIMEOUT        => 8,
                    CURLOPT_SSL_VERIFYPEER => false
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if ($httpCode >= 200 && $httpCode < 300) {
                    $sent = true;
                } else {
                    $error = "Fast2SMS HTTP $httpCode: $response";
                }
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }
        // 2. MSG91 GATEWAY
        elseif (!empty($cfg['enabled']) && $providerUsed === 'msg91' && !empty($cfg['authkey'])) {
            try {
                $templateId = urlencode($cfg['template_id'] ?? '');
                $authKey = urlencode($cfg['authkey']);
                $url = "https://control.msg91.com/api/v5/otp?template_id={$templateId}&mobile={$digitsOnly}&authkey={$authKey}&otp=" . urlencode($otp);
                $res = @file_get_contents($url);
                $sent = ($res !== false);
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }
        // 3. TWILIO GATEWAY
        elseif (!empty($cfg['enabled']) && $providerUsed === 'twilio' && !empty($cfg['account_sid']) && !empty($cfg['auth_token'])) {
            try {
                $toNum = (strpos($cleanPhone, '+') === 0) ? $cleanPhone : ('+' . $cleanPhone);
                $url = "https://api.twilio.com/2010-04-01/Accounts/" . $cfg['account_sid'] . "/Messages.json";
                $data = [
                    'From' => $cfg['from_number'] ?? '',
                    'To'   => $toNum,
                    'Body' => $message
                ];
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_USERPWD        => $cfg['account_sid'] . ":" . $cfg['auth_token'],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => http_build_query($data),
                    CURLOPT_TIMEOUT        => 8,
                    CURLOPT_SSL_VERIFYPEER => false
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                $sent = ($httpCode >= 200 && $httpCode < 300);
                if (!$sent) {
                    $error = "Twilio HTTP $httpCode: $response";
                }
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }
        // 4. CUSTOM HTTP WEBHOOK
        elseif (!empty($cfg['enabled']) && !empty($cfg['custom_url'])) {
            try {
                $targetUrl = str_replace(
                    ['{phone}', '{otp}', '{name}'],
                    [urlencode($cleanPhone), urlencode($otp), urlencode($recipientName)],
                    $cfg['custom_url']
                );
                $res = @file_get_contents($targetUrl);
                $sent = ($res !== false);
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        } else {
            // Simulation Mode (Instant local success & logged for testing)
            $sent = true;
            $providerUsed = 'simulation';
        }

        // Always log to data/sms_log.json
        self::logSms([
            'timestamp' => date('Y-m-d H:i:s T'),
            'phone'     => $cleanPhone,
            'recipient' => $recipientName,
            'otp'       => $otp,
            'message'   => $message,
            'provider'  => $providerUsed,
            'sent'      => $sent,
            'error'     => $error
        ]);

        return [
            'success'  => $sent,
            'phone'    => $cleanPhone,
            'otp'      => $otp,
            'provider' => $providerUsed,
            'error'    => $error
        ];
    }

    /**
     * Retrieve the most recent OTP for a given phone (useful for UI testing & dev feedback)
     */
    public static function getLastOtpForPhone(string $phone): ?string {
        $digitsOnly = preg_replace('/[^0-9]/', '', $phone);
        if (empty($digitsOnly)) return null;

        $logFile = self::LOG_FILE;
        if (!file_exists($logFile)) return null;

        $logs = json_decode(@file_get_contents($logFile), true);
        if (!is_array($logs)) return null;

        foreach ($logs as $log) {
            $logDigits = preg_replace('/[^0-9]/', '', $log['phone'] ?? '');
            if ($logDigits === $digitsOnly && !empty($log['otp'])) {
                return (string)$log['otp'];
            }
        }
        return null;
    }

    /**
     * Write to persistent SMS log
     */
    private static function logSms(array $entry): void {
        $logFile = self::LOG_FILE;
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $logs = [];
        if (file_exists($logFile)) {
            $logs = json_decode(@file_get_contents($logFile), true) ?: [];
        }
        array_unshift($logs, $entry);
        if (count($logs) > 200) {
            $logs = array_slice($logs, 0, 200);
        }
        @file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
