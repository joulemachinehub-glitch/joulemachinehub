<?php
// Root dashboard redirect to modular dashboard/index.php
require_once __DIR__ . '/includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

header('Location: dashboard/index.php');
exit;
