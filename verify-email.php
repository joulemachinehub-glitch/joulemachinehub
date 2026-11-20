<?php
// Joule Machine Hub - Unified Email Verification Endpoint
require_once __DIR__ . '/includes/auth.php';

// If logged in, go to dashboard
if (isLoggedIn()) {
    header('Location: dashboard/index.php');
    exit;
}

// If there's an active OTP session, redirect to verify-otp.php
if (getPendingSellerRegistration()) {
    header('Location: verify-otp.php');
    exit;
}

// Otherwise go to registration
header('Location: register.php');
exit;