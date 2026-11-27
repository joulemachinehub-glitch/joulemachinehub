<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'joulemachinehub.com';
$pageTitle = "Reset Password | Joule Machine Hub";
$pageDesc = "Recover your Joule Machine Hub account password. Enter your registered email to reset your credentials.";
$canonicalUrl = $protocol . $host . "/forgot-password.php";
$pageRobots = "noindex, nofollow, noarchive, nosnippet";

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/data/machines.php';

$error = '';
$success = '';
$step = isset($_GET['token']) ? 2 : 1;
$token = htmlspecialchars($_GET['token'] ?? '');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? 'request';

    if ($action === 'request') {
        $email = trim(strtolower($_POST['email'] ?? ''));
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid registered email address.';
        } elseif ($email === JMH_ADMIN_EMAIL) {
            $success = "If that email is registered in our database, password reset instructions have been sent via secure administrator channel.";
        } else {
            // Check if user exists in JSON or MySQL
            $users = getUsers();
            $found = false;
            $recipientName = 'Valued Member';
            foreach ($users as $u) {
                if (strtolower($u['email'] ?? '') === $email) {
                    $found = true;
                    $recipientName = $u['name'] ?? $recipientName;
                    break;
                }
            }
            if (!$found) {
                $pdo = getDbConnection();
                if ($pdo) {
                    try {
                        $stmt = $pdo->prepare("SELECT `name` FROM `users` WHERE LOWER(`email`) = ? LIMIT 1");
                        $stmt->execute([$email]);
                        $row = $stmt->fetch();
                        if ($row) {
                            $found = true;
                            $recipientName = $row['name'] ?? $recipientName;
                        }
                    } catch (Throwable $e) {}
                }
            }

            if ($found) {
                // Generate a reset token (valid for 1 hour) and email ONLY — never expose token on HTML page
                $resetToken = bin2hex(random_bytes(16));
                $tokenFile = __DIR__ . '/data/password_resets.json';
                $resets = file_exists($tokenFile) ? (json_decode(@file_get_contents($tokenFile), true) ?: []) : [];
                $resets[$resetToken] = [
                    'email'      => $email,
                    'token'      => $resetToken,
                    'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour'))
                ];
                @file_put_contents($tokenFile, json_encode($resets, JSON_PRETTY_PRINT));

                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $baseHost = $_SERVER['HTTP_HOST'] ?? 'joulemachinehub.com';
                $resetUrl = $scheme . '://' . $baseHost . '/forgot-password.php?token=' . urlencode($resetToken);
                $safeName = htmlspecialchars($recipientName);
                $mailHtml = "<div style='font-family: Arial, sans-serif; padding: 24px; background: #f8fafc;'>
                    <div style='max-width: 560px; margin: 0 auto; background: #ffffff; border: 2px solid #0B2545; border-radius: 14px; padding: 24px;'>
                        <h2 style='color: #0B2545; margin-top: 0;'>Password Reset Request</h2>
                        <p>Hello {$safeName},</p>
                        <p>Click the button below to reset your Joule Machine Hub account password. This link expires in 1 hour.</p>
                        <p style='margin: 24px 0; text-align: center;'>
                            <a href='{$resetUrl}' style='background: #0B2545; color: #facc15; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-block;'>Reset Account Password</a>
                        </p>
                        <p style='font-size: 12px; color: #64748b;'>If you did not request this password reset, you can safely ignore this email.</p>
                    </div>
                </div>";
                try {
                    JmhMailer::sendDirect($email, '[Joule Machine Hub] Password Reset Request', $mailHtml);
                } catch (Throwable $e) {
                    error_log("Password reset mail error: " . $e->getMessage());
                }
            }
            $success = "If that email is registered in our database, password reset instructions have been sent to your email inbox.";
        }
    } elseif ($action === 'reset') {
        $token = trim($_POST['token'] ?? '');
        $newPass = trim($_POST['new_password'] ?? '');
        $confirmPass = trim($_POST['confirm_password'] ?? '');

        if (empty($newPass) || strlen($newPass) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($newPass !== $confirmPass) {
            $error = 'Passwords do not match.';
        } else {
            $tokenFile = __DIR__ . '/data/password_resets.json';
            $resets = file_exists($tokenFile) ? (json_decode(@file_get_contents($tokenFile), true) ?: []) : [];
            if (isset($resets[$token]) && strtotime($resets[$token]['expires_at']) > time() && strtolower($resets[$token]['email'] ?? '') !== JMH_ADMIN_EMAIL) {
                $email = strtolower($resets[$token]['email']);
                $newHash = password_hash($newPass, PASSWORD_BCRYPT);
                $users = getUsers();
                foreach ($users as &$u) {
                    if (strtolower($u['email'] ?? '') === $email) {
                        $u['password'] = $newPass;
                        $u['password_hash'] = $newHash;
                        break;
                    }
                }
                unset($u);
                saveUsers($users);
                $pdo = getDbConnection();
                if ($pdo) {
                    try {
                        $stmt = $pdo->prepare("UPDATE `users` SET `password` = ? WHERE LOWER(`email`) = ?");
                        $stmt->execute([$newHash, $email]);
                    } catch (Throwable $e) {}
                }
                unset($resets[$token]);
                @file_put_contents($tokenFile, json_encode($resets, JSON_PRETTY_PRINT));
                header('Location: login.php?reset_success=1');
                exit;
            } else {
                $error = 'This reset link has expired or is invalid. Please request a new one.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-12 sm:py-20 px-4 bg-slate-900 flex-1 flex items-center justify-center min-h-[80vh]">
  <div class="max-w-md w-full bg-white rounded-3xl border-2 border-joule-yellow shadow-2xl overflow-hidden animate-in fade-in duration-300">
    
    <!-- Top Header Banner -->
    <div class="bg-joule-blue text-white p-6 sm:p-8 text-center border-b-4 border-joule-yellow relative">
      <div class="w-16 h-16 rounded-2xl bg-joule-yellow text-slate-950 flex items-center justify-center font-black text-2xl mx-auto mb-3 shadow-lg border-2 border-amber-300">
        <i class="fa-solid fa-key"></i>
      </div>
      <span class="inline-block uppercase tracking-wider text-[10px] font-black bg-white/20 text-yellow-300 px-3 py-1 rounded-full mb-2">
        Security Verification
      </span>
      <h1 class="text-2xl sm:text-3xl font-black text-white">Reset Account Password</h1>
      <p class="text-xs text-blue-200 mt-1 max-w-xs mx-auto">
        <?php echo $step === 2 ? 'Enter your new secure password below.' : 'Enter your registered email address to recover access.'; ?>
      </p>
    </div>

    <!-- Form Content -->
    <div class="p-6 sm:p-8 space-y-5">
      
      <?php if (!empty($success)): ?>
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
          <div class="flex items-start gap-2.5">
            <i class="fa-solid fa-circle-check text-base text-emerald-600 shrink-0 mt-0.5"></i>
            <div><?php echo $success; ?></div>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold flex items-center gap-2.5">
          <i class="fa-solid fa-circle-exclamation text-base text-red-600 shrink-0"></i>
          <div><?php echo htmlspecialchars($error); ?></div>
        </div>
      <?php endif; ?>

      <?php if ($step === 2 && !empty($token)): ?>
        <!-- Step 2: Set New Password Form -->
        <form action="forgot-password.php" method="POST" class="space-y-4">
          <input type="hidden" name="action" value="reset">
          <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              New Password <span class="text-red-500">*</span>
            </label>
            <input 
              type="password" 
              name="new_password" 
              required 
              placeholder="Minimum 6 characters" 
              class="w-full px-3.5 py-3 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
            >
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              Confirm New Password <span class="text-red-500">*</span>
            </label>
            <input 
              type="password" 
              name="confirm_password" 
              required 
              placeholder="Re-enter new password" 
              class="w-full px-3.5 py-3 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
            >
          </div>

          <button 
            type="submit" 
            class="w-full bg-joule-blue hover:bg-blue-800 text-white font-black text-sm py-3.5 px-6 rounded-2xl shadow-lg transition-all"
          >
            Update Password &amp; Log In
          </button>
        </form>
      <?php else: ?>
        <!-- Step 1: Request Reset Link Form -->
        <form action="forgot-password.php" method="POST" class="space-y-4">
          <input type="hidden" name="action" value="request">

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              Registered Email Address
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                <i class="fa-solid fa-envelope text-xs"></i>
              </span>
              <input 
                type="email" 
                name="email" 
                required 
                placeholder="name@company.com" 
                class="w-full pl-10 pr-3.5 py-3 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
              >
            </div>
          </div>

          <button 
            type="submit" 
            class="w-full bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black text-sm py-3.5 px-6 rounded-2xl shadow-lg shadow-amber-400/20 transition-all cursor-pointer"
          >
            Send Password Reset Link
          </button>
        </form>
      <?php endif; ?>

      <div class="pt-4 border-t border-slate-200 text-center">
        <a href="login.php" class="text-xs font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1.5">
          <i class="fa-solid fa-arrow-left text-[10px]"></i>
          <span>Back to Login</span>
        </a>
      </div>

    </div>
  </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
