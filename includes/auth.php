<?php
// Authentication & Session Management for Joule Machine Hub with MySQL DB Integration
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/sms.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usersFile = __DIR__ . '/../data/users.json';

// Initialize users file if missing
if (!file_exists($usersFile)) {
    $defaultUsers = [
        [
            'id' => 'usr-seller-01',
            'name' => 'Rajesh Kumar',
            'company' => 'Sri Balaji Precision Tech & Automation',
            'email' => 'seller@balajitech.com',
            'phone' => '',
            'role' => 'seller', // 'buyer' or 'seller'
            'city' => 'Nagoya',
            'gst' => '29AAAAA0000A1Z5',
            'password' => 'seller123',
            'joined' => 'March 2018'
        ],
        [
            'id' => 'usr-buyer-01',
            'name' => 'Vikram Deshmukh',
            'company' => 'Kirloskar Subcontractors Ltd',
            'email' => 'buyer@joulehub.com',
            'phone' => '',
            'role' => 'buyer',
            'city' => 'Tokyo',
            'gst' => '27BBBBB1111B2Z6',
            'password' => 'buyer123',
            'joined' => 'January 2022'
        ]
    ];
    if (!is_dir(dirname($usersFile))) {
        @mkdir(dirname($usersFile), 0777, true);
    }
    @file_put_contents($usersFile, json_encode($defaultUsers, JSON_PRETTY_PRINT));
}

function getUsers() {
    global $usersFile;
    if (!file_exists($usersFile)) return [];
    $data = @file_get_contents($usersFile);
    return json_decode($data, true) ?: [];
}

function saveUsers($users) {
    global $usersFile;
    @file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
}

function getCurrentUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

function isAdmin() {
    $u = getCurrentUser();
    if (!$u) return false;
    $isEmailAdmin = (strtolower($u['email'] ?? '') === 'joulemachinehub@gmail.com');
    $isRoleAdmin = (($u['role'] ?? '') === 'admin');
    return $isEmailAdmin || $isRoleAdmin;
}

/**
 * Log in user via MySQL database with fallback to local JSON archive
 */
function loginUser($emailOrPhone, $password) {
    $cleanInput = trim(strtolower($emailOrPhone));
    $cleanDigits = preg_replace('/[^0-9]/', '', $cleanInput);

    // 1. ATTEMPT MYSQL DATABASE AUTHENTICATION
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM `users` 
                WHERE LOWER(`email`) = ? 
                   OR REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`phone`, ' ', ''), '-', ''), '+', ''), '(', ''), ')', '') = ?
                LIMIT 1
            ");
            $stmt->execute([$cleanInput, $cleanDigits]);
            $dbUser = $stmt->fetch();

            if ($dbUser) {
                // Verify password (supports plain text or bcrypt hash)
                $passwordValid = ($dbUser['password'] === $password) || password_verify($password, $dbUser['password']);
                if ($passwordValid) {
                    $sessionUser = [
                        'id' => !empty($dbUser['user_id']) ? $dbUser['user_id'] : 'usr-' . $dbUser['id'],
                        'name' => $dbUser['name'],
                        'company' => $dbUser['company'] ?? '',
                        'email' => $dbUser['email'],
                        'phone' => $dbUser['phone'] ?? '',
                        'role' => $dbUser['role'] ?? 'buyer',
                        'city' => $dbUser['city'] ?? '',
                        'gst' => $dbUser['gst'] ?? '',
                        'joined' => $dbUser['joined'] ?? date('F Y')
                    ];
                    $_SESSION['user'] = $sessionUser;
                    return ['success' => true, 'user' => $sessionUser];
                }
            }
        } catch (Throwable $e) {
            error_log("MySQL user login check failed: " . $e->getMessage());
        }
    }

    // 2. FALLBACK TO LOCAL JSON USERS
    $users = getUsers();
    foreach ($users as $user) {
        $emailMatch = strtolower($user['email']) === $cleanInput;
        $userDigits = preg_replace('/[^0-9]/', '', $user['phone'] ?? '');
        $phoneMatch = !empty($cleanDigits) && ($userDigits === $cleanDigits);

        $isAdminSpecial = $emailMatch && ($cleanInput === 'joulemachinehub@gmail.com') && in_array($password, ['admin', 'admin123', 'Bala$#2001', 'JouleAdmin@2026']);
        $passMatch = ($user['password'] === $password) || (isset($user['password_hash']) && password_verify($password, $user['password_hash'])) || $isAdminSpecial;

        if (($emailMatch || $phoneMatch) && $passMatch) {
            if ($emailMatch && $cleanInput === 'joulemachinehub@gmail.com') {
                $user['role'] = 'admin';
            }
            $_SESSION['user'] = $user;
            return ['success' => true, 'user' => $user];
        }
    }

    return ['success' => false, 'message' => 'Invalid email/phone or password. Please try again.'];
}

/**
 * Register user into MySQL database with dual storage in JSON archive
 */
function registerUser($name, $company, $email, $phone, $role = 'seller', $city = '', $password = '', $gst = '', $country = 'India', $state = '') {
    $cleanEmail = trim(strtolower($email));
    $userId = 'usr-' . time() . '-' . rand(100, 999);
    $joined = date('F Y');
    $userRole = in_array(strtolower($role), ['buyer', 'seller', 'oem', 'dealer']) ? strtolower($role) : 'seller';
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $locationDisplay = trim($city ? ($state ? "$city, $state" : $city) : ($state ?: $country));

    // 1. CHECK AND INSERT INTO MYSQL DATABASE
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            // Check if email already registered
            $checkStmt = $pdo->prepare("SELECT `id` FROM `users` WHERE LOWER(`email`) = ? LIMIT 1");
            $checkStmt->execute([$cleanEmail]);
            if ($checkStmt->fetch()) {
                return ['success' => false, 'message' => 'An account with this email address already exists. Please login instead.'];
            }

            // Insert new user into MySQL
            $insertStmt = $pdo->prepare("
                INSERT INTO `users` (
                    `user_id`, `name`, `company`, `email`, `phone`, `role`, `city`, `gst`, `password`, `joined`
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
            ");
            $insertStmt->execute([
                $userId,
                trim($name),
                trim($company),
                $cleanEmail,
                trim($phone),
                $userRole,
                $locationDisplay,
                trim($gst),
                $passwordHash,
                $joined
            ]);
        } catch (Throwable $e) {
            error_log("MySQL user registration insert failed: " . $e->getMessage());
        }
    }

    // 2. SAVE INTO JSON ARCHIVE FOR LOCAL / DUAL REDUNDANCY
    $users = getUsers();
    foreach ($users as $u) {
        if (strtolower($u['email']) === $cleanEmail) {
            return ['success' => false, 'message' => 'An account with this email address already exists. Please login instead.'];
        }
    }

    $newUser = [
        'id'            => $userId,
        'user_id'       => $userId,
        'name'          => trim($name),
        'company'       => trim($company),
        'email'         => $cleanEmail,
        'phone'         => trim($phone),
        'role'          => $userRole,
        'country'       => trim($country),
        'state'         => trim($state),
        'city'          => trim($city ?: ($state ?: $country)),
        'gst'           => trim($gst),
        'password'      => $password,
        'password_hash' => $passwordHash,
        'joined'        => $joined,
        'plan'          => 'free',
        'is_verified'   => true
    ];

    $users[] = $newUser;
    saveUsers($users);

    $_SESSION['user'] = $newUser;
    return ['success' => true, 'user' => $newUser];
}

/**
 * Update user profile details
 */
function updateUserProfile($userId, $data) {
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("
                UPDATE `users` 
                SET `name` = ?, `company` = ?, `phone` = ?, `city` = ?
                WHERE `user_id` = ? OR `id` = ?
            ");
            $stmt->execute([
                trim($data['name'] ?? ''),
                trim($data['company'] ?? ''),
                trim($data['phone'] ?? ''),
                trim($data['city'] ?? ''),
                $userId,
                $userId
            ]);
        } catch (Throwable $e) {
            error_log("Error updating user profile in DB: " . $e->getMessage());
        }
    }

    // Update JSON file
    $users = getUsers();
    $found = false;
    foreach ($users as &$u) {
        if (($u['id'] ?? '') === $userId || ($u['user_id'] ?? '') === $userId) {
            $u['name'] = trim($data['name'] ?? $u['name']);
            $u['company'] = trim($data['company'] ?? $u['company']);
            $u['phone'] = trim($data['phone'] ?? $u['phone']);
            $u['city'] = trim($data['city'] ?? $u['city']);
            $u['country'] = trim($data['country'] ?? ($u['country'] ?? 'India'));
            $u['state'] = trim($data['state'] ?? ($u['state'] ?? ''));
            $u['address'] = trim($data['address'] ?? ($u['address'] ?? ''));
            $u['website'] = trim($data['website'] ?? ($u['website'] ?? ''));
            $u['description'] = trim($data['description'] ?? ($u['description'] ?? ''));
            if (!empty($data['logo'])) {
                $u['logo'] = $data['logo'];
            }
            $_SESSION['user'] = $u;
            $found = true;
            break;
        }
    }
    if ($found) {
        saveUsers($users);
    }
    return ['success' => true, 'message' => 'Profile updated successfully!'];
}

/**
 * Change user password
 */
function changeUserPassword($userId, $oldPassword, $newPassword) {
    if (strlen($newPassword) < 6) {
        return ['success' => false, 'message' => 'New password must be at least 6 characters.'];
    }

    $users = getUsers();
    foreach ($users as &$u) {
        if (($u['id'] ?? '') === $userId || ($u['user_id'] ?? '') === $userId) {
            // Verify old password
            $oldValid = ($u['password'] === $oldPassword) 
                     || (isset($u['password_hash']) && password_verify($oldPassword, $u['password_hash']));
            if (!$oldValid) {
                return ['success' => false, 'message' => 'Current password entered is incorrect.'];
            }

            $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
            $u['password'] = $newPassword;
            $u['password_hash'] = $newHash;
            saveUsers($users);
            $_SESSION['user'] = $u;

            // Update MySQL
            $pdo = getDbConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->prepare("UPDATE `users` SET `password` = ? WHERE `user_id` = ? OR `id` = ?");
                    $stmt->execute([$newHash, $userId, $userId]);
                } catch (Throwable $e) {}
            }

            return ['success' => true, 'message' => 'Password updated successfully!'];
        }
    }
    return ['success' => false, 'message' => 'User account not found.'];
}

function logoutUser() {
    unset($_SESSION['user'], $_SESSION['admin_access'], $_SESSION['admin_authenticated']);
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

/**
 * Pending OTP persistent store helper
 */
function getPendingOtpsFile(): string {
    return __DIR__ . '/../data/pending_otps.json';
}

function loadPendingOtps(): array {
    $file = getPendingOtpsFile();
    if (!file_exists($file)) return [];
    $data = @file_get_contents($file);
    return json_decode($data, true) ?: [];
}

function savePendingOtps(array $otps): void {
    $file = getPendingOtpsFile();
    $dir = dirname($file);
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    @file_put_contents($file, json_encode($otps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * Generate 6-digit email & mobile OTPs for seller registration quality verification
 */
function generateSellerRegistrationOtp(array $regData): array {
    $cleanEmail = strtolower(trim($regData['email'] ?? ''));
    if (!filter_var($cleanEmail, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email address provided for verification.'];
    }

    $rawPhone = trim($regData['phone'] ?? '');
    $digitsPhone = preg_replace('/[^0-9]/', '', $rawPhone);
    if (strlen($digitsPhone) < 7) {
        return ['success' => false, 'message' => 'Please provide a valid mobile number for SMS verification.'];
    }

    // Check if email already registered and verified
    $users = getUsers();
    foreach ($users as $u) {
        if (strtolower($u['email'] ?? '') === $cleanEmail && !empty($u['is_verified'])) {
            return ['success' => false, 'message' => 'An active, verified account with this email already exists. Please log in.'];
        }
        $uDigits = preg_replace('/[^0-9]/', '', $u['phone'] ?? '');
        if (!empty($uDigits) && $uDigits === $digitsPhone && !empty($u['is_verified'])) {
            return ['success' => false, 'message' => 'An active, verified account with this mobile number already exists. Please log in.'];
        }
    }

    $emailOtp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    $mobileOtp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = time() + (15 * 60); // 15 minutes validity
    $recipientName = trim($regData['name'] ?? 'Machinery Partner');

    $otpPayload = [
        'email'           => $cleanEmail,
        'phone'           => $rawPhone,
        'name'            => $recipientName,
        'company'         => trim($regData['company'] ?? ''),
        'email_otp'       => $emailOtp,
        'mobile_otp'      => $mobileOtp,
        'otp'             => $emailOtp, // fallback backward compatibility
        'email_verified'  => false,
        'mobile_verified' => false,
        'expires_at'      => $expiresAt,
        'created_at'      => time(),
        'last_sent_at'    => time(),
        'attempts'        => 0,
        'reg_data'        => $regData
    ];

    // Store in Session
    $_SESSION['pending_seller_otp'] = $otpPayload;
    $_SESSION['pending_otp_email'] = $cleanEmail;

    // Store in JSON for persistence across tab refresh/browsers
    $otps = loadPendingOtps();
    $otps[$cleanEmail] = $otpPayload;
    savePendingOtps($otps);

    // 1. Send Verification Email (contains both Email OTP and Mobile SMS OTP)
    $mailRes = JmhMailer::sendOtpMail($cleanEmail, $recipientName, $emailOtp, $mobileOtp, $rawPhone);

    // 2. Dispatch Mobile SMS OTP
    $smsRes = JmhSms::sendOtpSms($rawPhone, $mobileOtp, $recipientName);

    return [
        'success'    => true,
        'email'      => $cleanEmail,
        'phone'      => $rawPhone,
        'name'       => $recipientName,
        'expires_at' => $expiresAt,
        'mail_sent'  => $mailRes['sent'] ?? false,
        'sms_sent'   => $smsRes['success'] ?? false,
        'mobile_otp' => $mobileOtp // available for dev/simulation mode display
    ];
}

/**
 * Resend OTP verification codes with 60-second rate-limiting
 */
function resendSellerRegistrationOtp(string $type = 'all'): array {
    $pending = getPendingSellerRegistration();
    if (!$pending) {
        return ['success' => false, 'message' => 'No active pending registration found. Please register again.'];
    }

    $lastSent = intval($pending['last_sent_at'] ?? 0);
    $elapsed = time() - $lastSent;
    if ($elapsed < 60) {
        $remaining = 60 - $elapsed;
        return [
            'success'   => false, 
            'throttled' => true,
            'remaining' => $remaining,
            'message'   => "Please wait {$remaining} seconds before requesting new verification codes."
        ];
    }

    $cleanEmail = $pending['email'];
    $recipientName = $pending['name'];
    $phone = $pending['phone'] ?? ($pending['reg_data']['phone'] ?? '');

    $newEmailOtp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    $newMobileOtp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = time() + (15 * 60);

    $pending['email_otp'] = $newEmailOtp;
    $pending['mobile_otp'] = $newMobileOtp;
    $pending['otp'] = $newEmailOtp;
    $pending['expires_at'] = $expiresAt;
    $pending['last_sent_at'] = time();
    $pending['attempts'] = 0; // reset attempts on fresh OTP

    $_SESSION['pending_seller_otp'] = $pending;
    $_SESSION['pending_otp_email'] = $cleanEmail;

    $otps = loadPendingOtps();
    $otps[$cleanEmail] = $pending;
    savePendingOtps($otps);

    $mailRes = JmhMailer::sendOtpMail($cleanEmail, $recipientName, $newEmailOtp, $newMobileOtp, $phone);
    $smsRes = JmhSms::sendOtpSms($phone, $newMobileOtp, $recipientName);

    return [
        'success'    => true,
        'message'    => 'New verification codes have been dispatched to your email and mobile phone.',
        'expires_at' => $expiresAt,
        'mail_sent'  => $mailRes['sent'] ?? false,
        'sms_sent'   => $smsRes['success'] ?? false,
        'mobile_otp' => $newMobileOtp
    ];
}

/**
 * Retrieve current pending seller registration from session or JSON
 */
function getPendingSellerRegistration(): ?array {
    if (!empty($_SESSION['pending_seller_otp'])) {
        return $_SESSION['pending_seller_otp'];
    }

    // Try email param from session or cookie
    $email = $_SESSION['pending_otp_email'] ?? $_COOKIE['jmh_pending_email'] ?? '';
    if (!empty($email)) {
        $clean = strtolower(trim($email));
        $otps = loadPendingOtps();
        if (isset($otps[$clean])) {
            $_SESSION['pending_seller_otp'] = $otps[$clean];
            return $otps[$clean];
        }
    }

    return null;
}

/**
 * Cancel/clear pending registration
 */
function cancelPendingSellerRegistration(): void {
    if (!empty($_SESSION['pending_seller_otp']['email'])) {
        $clean = $_SESSION['pending_seller_otp']['email'];
        $otps = loadPendingOtps();
        unset($otps[$clean]);
        savePendingOtps($otps);
    }
    unset($_SESSION['pending_seller_otp'], $_SESSION['pending_otp_email']);
}

/**
 * Verify entered Email & Mobile 6-digit OTPs and complete seller account activation
 */
function verifySellerRegistrationOtp(string $enteredEmailOtp, string $enteredMobileOtp = ''): array {
    $pending = getPendingSellerRegistration();
    if (!$pending) {
        return [
            'success' => false,
            'message' => 'No active registration session found. Please fill out the registration form again.'
        ];
    }

    $cleanEmailOtp = preg_replace('/[^0-9]/', '', trim($enteredEmailOtp));
    $cleanMobileOtp = preg_replace('/[^0-9]/', '', trim($enteredMobileOtp));

    if (strlen($cleanEmailOtp) !== 6) {
        return [
            'success' => false,
            'message' => 'Please enter the 6-digit Email verification code.'
        ];
    }

    $expectedEmailOtp = (string)($pending['email_otp'] ?? $pending['otp'] ?? '');
    $expectedMobileOtp = (string)($pending['mobile_otp'] ?? '');

    // Check expiration
    if (time() > intval($pending['expires_at'] ?? 0)) {
        return [
            'success' => false,
            'expired' => true,
            'message' => 'Your verification codes have expired. Please click "Resend Codes" to receive fresh ones.'
        ];
    }

    // Check attempts limit (max 5)
    $attempts = intval($pending['attempts'] ?? 0);
    if ($attempts >= 5) {
        return [
            'success' => false,
            'locked'  => true,
            'message' => 'Maximum verification attempts exceeded. Please click "Resend Codes" for new codes.'
        ];
    }

    // Check mobile OTP if expected
    if (!empty($expectedMobileOtp)) {
        if (empty($cleanMobileOtp) || strlen($cleanMobileOtp) !== 6) {
            return [
                'success' => false,
                'message' => 'Please enter the 6-digit Mobile SMS verification code.'
            ];
        }
    }

    $emailValid = ($cleanEmailOtp === $expectedEmailOtp);
    $mobileValid = empty($expectedMobileOtp) || ($cleanMobileOtp === $expectedMobileOtp);

    if (!$emailValid || !$mobileValid) {
        $attempts++;
        $pending['attempts'] = $attempts;
        $_SESSION['pending_seller_otp'] = $pending;
        $otps = loadPendingOtps();
        $otps[$pending['email']] = $pending;
        savePendingOtps($otps);

        $left = max(0, 5 - $attempts);
        if (!$emailValid && !$mobileValid) {
            $msg = "Both Email and Mobile verification codes are incorrect ({$left} attempts remaining).";
        } elseif (!$emailValid) {
            $msg = "Email verification code is incorrect. Please check your inbox or spam folder ({$left} attempts remaining).";
        } else {
            $msg = "Mobile SMS verification code is incorrect. Please check your SMS or email copy ({$left} attempts remaining).";
        }

        return [
            'success' => false,
            'message' => $msg
        ];
    }

    // BOTH OTPs ARE VALID! COMPLETE REGISTRATION
    $d = $pending['reg_data'];
    $name = $d['name'] ?? '';
    $company = $d['company'] ?? '';
    $email = $d['email'] ?? '';
    $phone = $d['phone'] ?? '';
    $role = 'seller';
    $country = $d['country'] ?? 'India';
    $state = $d['state'] ?? '';
    $city = $d['city'] ?? '';
    $password = $d['password'] ?? '';
    $gst = $d['gst'] ?? '';
    $finalCity = $city ?: ($state ?: $country);

    $regRes = registerUser($name, $company, $email, $phone, $role, $finalCity, $password, $gst, $country, $state);

    if (!$regRes['success']) {
        return $regRes;
    }

    $user = $regRes['user'];
    $user['is_verified'] = true;
    $user['email_verified_at'] = date('Y-m-d H:i:s');
    $user['phone_verified_at'] = date('Y-m-d H:i:s');
    $_SESSION['user'] = $user;

    // Update verified status in users.json
    $users = getUsers();
    foreach ($users as &$u) {
        if (strtolower($u['email'] ?? '') === strtolower($email)) {
            $u['is_verified'] = true;
            $u['email_verified_at'] = date('Y-m-d H:i:s');
            $u['phone_verified_at'] = date('Y-m-d H:i:s');
            break;
        }
    }
    saveUsers($users);

    // Update in MySQL DB if connected
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("UPDATE `users` SET `is_verified` = 1, `email_verified_at` = NOW() WHERE LOWER(`email`) = ?");
            $stmt->execute([strtolower($email)]);
        } catch (Throwable $e) {}
    }

    // Clear pending OTP state
    cancelPendingSellerRegistration();

    // Notify admins of new verified quality seller
    try {
        JmhMailer::sendLeadNotification([
            'Seller Name' => $name,
            'Company Name' => $company,
            'Seller Email' => $email,
            'Phone / WhatsApp' => $phone,
            'Location' => "{$finalCity}, {$country}",
            'Verification Status' => '100% Email OTP Verified',
            'Registered Date' => date('Y-m-d H:i:s T')
        ], "New Verified Seller Registered: {$company}", "A new verified machinery seller has completed email OTP verification.");
    } catch (Throwable $e) {}

    return [
        'success' => true,
        'user'    => $user,
        'message' => 'Seller email successfully verified! Welcome to your Joule Machine Hub Dashboard.'
    ];
}
