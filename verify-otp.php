<?php
// Joule Machine Hub - Dedicated Seller Email Verification & Quality Gate
// Enforces 6-Digit Email OTP confirmation before activating seller dashboard & public listings
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/data/machines.php';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'joulemachinehub.com';
$pageTitle = "Verify Email Address | Quality Seller Gate | Joule Machine Hub";
$pageDesc = "Confirm your business email address with the 6-digit OTP code to activate your machinery seller dashboard.";
$canonicalUrl = $protocol . $host . "/verify-otp.php";
$pageRobots = "noindex, nofollow, noarchive, nosnippet";

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: dashboard/index.php');
    exit;
}

$pending = getPendingSellerRegistration();
if (!$pending) {
    header('Location: register.php');
    exit;
}

$email = $pending['email'] ?? '';
$company = $pending['company'] ?? '';
$name = $pending['name'] ?? '';
$expiresAt = intval($pending['expires_at'] ?? (time() + 900));
$remainingSeconds = max(0, $expiresAt - time());

$error = '';
$success = '';

// Handle Actions (Verify, Resend, Cancel)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'verify';

    if ($action === 'resend') {
        $resendResult = resendSellerRegistrationOtp();
        if ($resendResult['success']) {
            $success = $resendResult['message'];
            $pending = getPendingSellerRegistration();
            $expiresAt = intval($pending['expires_at'] ?? (time() + 900));
            $remainingSeconds = max(0, $expiresAt - time());
        } else {
            $error = $resendResult['message'];
        }
    } elseif ($action === 'cancel') {
        cancelPendingSellerRegistration();
        header('Location: register.php');
        exit;
    } elseif ($action === 'verify') {
        // Collect entered OTP (either from single input or 6 digit fields)
        $code = trim($_POST['otp'] ?? '');
        if (empty($code) && isset($_POST['d1'])) {
            $code = trim($_POST['d1']) . trim($_POST['d2']) . trim($_POST['d3']) . trim($_POST['d4']) . trim($_POST['d5']) . trim($_POST['d6']);
        }

        $verifyResult = verifySellerRegistrationOtp($code);
        if ($verifyResult['success']) {
            $_SESSION['registered_just_now'] = true;
            header('Location: dashboard/index.php?registered=1&verified=1');
            exit;
        } else {
            $error = $verifyResult['message'];
            $pending = getPendingSellerRegistration();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="min-h-[82vh] bg-slate-900 py-12 sm:py-16 px-4 flex items-center justify-center">
  <div class="max-w-md w-full bg-white rounded-3xl border-2 border-joule-yellow shadow-2xl overflow-hidden animate-in fade-in duration-300">
    
    <!-- Top Header Banner -->
    <div class="bg-joule-blue text-white p-6 sm:p-7 text-center border-b-4 border-joule-yellow relative">
      <div class="w-14 h-14 rounded-2xl bg-joule-yellow text-slate-950 flex items-center justify-center font-black text-2xl mx-auto mb-3 shadow-lg border-2 border-amber-300">
        <i class="fa-solid fa-envelope-circle-check"></i>
      </div>
      <span class="inline-block uppercase tracking-wider text-[10px] font-black bg-white/20 text-yellow-300 px-3 py-1 rounded-full mb-1.5">
        Quality Listing Gate • Step 2 of 2
      </span>
      <h1 class="text-xl sm:text-2xl font-black text-white">Verify Your Email</h1>
      <p class="text-xs text-blue-200 mt-1 max-w-xs mx-auto">
        We require email confirmation to ensure all machinery listings and seller communications are authentic.
      </p>
    </div>

    <!-- Verification Card Body -->
    <div class="p-6 sm:p-8 space-y-6">

      <!-- Email Address Display Pill -->
      <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-center space-y-1">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Verification code sent to:</span>
        <div class="text-sm font-black text-joule-blue break-all">
          <i class="fa-solid fa-at text-xs text-amber-500 mr-1"></i>
          <?php echo htmlspecialchars($email); ?>
        </div>
        <div class="text-[11px] text-slate-500 font-medium">
          Company: <strong class="text-slate-800"><?php echo htmlspecialchars($company); ?></strong>
        </div>
      </div>

      <!-- Alerts -->
      <?php if (!empty($error)): ?>
        <div class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold flex items-center gap-3">
          <i class="fa-solid fa-circle-exclamation text-base shrink-0"></i>
          <div><?php echo htmlspecialchars($error); ?></div>
        </div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-3">
          <i class="fa-solid fa-circle-check text-base shrink-0 text-emerald-600"></i>
          <div><?php echo htmlspecialchars($success); ?></div>
        </div>
      <?php endif; ?>

      <!-- 6-Digit OTP Form -->
      <form action="verify-otp.php" method="POST" id="otpForm" class="space-y-5">
        <input type="hidden" name="action" value="verify">
        
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider text-center mb-3">
            Enter 6-Digit Verification Code
          </label>
          
          <!-- 6-Box Input Grid -->
          <div class="flex items-center justify-center gap-2 sm:gap-2.5" id="otpBoxes">
            <?php for ($i = 1; $i <= 6; $i++): ?>
              <input 
                type="text" 
                name="d<?php echo $i; ?>" 
                id="d<?php echo $i; ?>" 
                inputmode="numeric" 
                pattern="[0-9]*" 
                maxlength="1" 
                autocomplete="off" 
                required 
                class="otp-digit w-11 h-13 sm:w-12 sm:h-14 text-center font-black text-xl sm:text-2xl rounded-xl border-2 border-slate-300 text-slate-900 focus:outline-none focus:border-joule-blue focus:ring-2 focus:ring-blue-200 transition-all bg-slate-50/50"
              >
            <?php endfor; ?>
          </div>

          <!-- Fallback single hidden input for pasting code -->
          <input type="hidden" name="otp" id="fullOtpInput" value="">
        </div>

        <!-- Expiration Countdown Indicator -->
        <div class="flex items-center justify-between text-xs font-bold text-slate-500 px-1">
          <span class="flex items-center gap-1.5">
            <i class="fa-solid fa-clock text-amber-500"></i>
            <span>Code expires in:</span>
          </span>
          <span id="countdownTimer" class="font-black text-rose-600 text-sm">--:--</span>
        </div>

        <!-- Submit Button -->
        <button 
          type="submit" 
          id="verifySubmitBtn"
          class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-amber-400 to-yellow-400 hover:from-amber-500 hover:to-yellow-500 text-slate-950 font-black text-xs uppercase tracking-wider shadow-lg shadow-amber-400/20 transition-all hover:scale-[1.01] cursor-pointer flex items-center justify-center gap-2 border border-amber-300"
        >
          <i class="fa-solid fa-shield-check text-sm"></i>
          <span>Verify &amp; Activate Dashboard</span>
        </button>

      </form>

      <!-- Resend & Change Email Actions -->
      <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
        <form action="verify-otp.php" method="POST" class="inline">
          <input type="hidden" name="action" value="resend">
          <button 
            type="submit" 
            id="resendBtn" 
            class="text-joule-brightblue hover:text-blue-700 font-bold hover:underline cursor-pointer flex items-center gap-1"
          >
            <i class="fa-solid fa-rotate-right text-[11px]"></i>
            <span>Resend Code</span>
            <span id="resendCountdown" class="text-slate-400 hidden">(60s)</span>
          </button>
        </form>

        <form action="verify-otp.php" method="POST" class="inline">
          <input type="hidden" name="action" value="cancel">
          <button 
            type="submit" 
            onclick="return confirm('Cancel this registration and edit your details?');"
            class="text-slate-500 hover:text-slate-700 font-semibold cursor-pointer flex items-center gap-1"
          >
            <i class="fa-solid fa-pen-to-square text-[11px]"></i>
            <span>Change Details</span>
          </button>
        </form>
      </div>

      <!-- Trust Note -->
      <div class="bg-blue-50/60 rounded-xl p-3 text-[11px] text-slate-600 flex items-start gap-2 border border-blue-100">
        <i class="fa-solid fa-info-circle text-blue-600 mt-0.5 shrink-0"></i>
        <span>Can't find the email? Please check your <strong>Spam / Junk</strong> folder or wait a minute before requesting another code.</span>
      </div>

    </div>

  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const digits = [
    document.getElementById('d1'),
    document.getElementById('d2'),
    document.getElementById('d3'),
    document.getElementById('d4'),
    document.getElementById('d5'),
    document.getElementById('d6')
  ];
  const fullOtpInput = document.getElementById('fullOtpInput');
  const otpForm = document.getElementById('otpForm');

  if (digits[0]) {
    digits[0].focus();
  }

  // Handle Digit Box Auto-Advance, Backspace & Navigation
  digits.forEach((input, index) => {
    input.addEventListener('input', function(e) {
      const val = this.value.replace(/[^0-9]/g, '');
      this.value = val ? val.charAt(val.length - 1) : '';

      if (this.value && index < digits.length - 1) {
        digits[index + 1].focus();
      }
      syncFullOtp();
    });

    input.addEventListener('keydown', function(e) {
      if (e.key === 'Backspace' && !this.value && index > 0) {
        digits[index - 1].focus();
      }
    });

    // Handle Paste Event across the entire 6 boxes
    input.addEventListener('paste', function(e) {
      e.preventDefault();
      const pasteData = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
      if (pasteData) {
        for (let i = 0; i < digits.length; i++) {
          digits[i].value = pasteData[i] || '';
        }
        const lastFilled = Math.min(pasteData.length, digits.length) - 1;
        if (lastFilled >= 0 && lastFilled < digits.length - 1) {
          digits[lastFilled + 1].focus();
        } else if (lastFilled === digits.length - 1) {
          digits[digits.length - 1].focus();
        }
        syncFullOtp();
        if (pasteData.length >= 6) {
          otpForm.submit();
        }
      }
    });
  });

  function syncFullOtp() {
    let full = '';
    digits.forEach(d => { full += d.value; });
    fullOtpInput.value = full;
  }

  otpForm.addEventListener('submit', function() {
    syncFullOtp();
  });

  // Expiration Countdown Timer
  let remaining = <?php echo (int)$remainingSeconds; ?>;
  const timerEl = document.getElementById('countdownTimer');
  
  function updateCountdown() {
    if (remaining <= 0) {
      timerEl.textContent = 'Expired';
      timerEl.classList.add('text-red-700');
      return;
    }
    const mins = Math.floor(remaining / 60);
    const secs = remaining % 60;
    timerEl.textContent = (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
    remaining--;
  }
  updateCountdown();
  setInterval(updateCountdown, 1000);

  // Resend Button Cooldown (60 seconds)
  const resendBtn = document.getElementById('resendBtn');
  const resendCountdown = document.getElementById('resendCountdown');
  let cooldown = 60;

  function runResendCooldown() {
    resendBtn.disabled = true;
    resendBtn.classList.add('opacity-50', 'cursor-not-allowed');
    resendCountdown.classList.remove('hidden');

    const cdInterval = setInterval(function() {
      cooldown--;
      resendCountdown.textContent = '(' + cooldown + 's)';
      if (cooldown <= 0) {
        clearInterval(cdInterval);
        resendBtn.disabled = false;
        resendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        resendCountdown.classList.add('hidden');
      }
    }, 1000);
  }
  runResendCooldown();
});
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>