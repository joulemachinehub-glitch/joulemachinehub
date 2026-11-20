<?php
// Joule Machine Hub - Dedicated Dual Seller Verification (Email & Mobile OTP)
// Enforces 6-Digit Email OTP and Mobile SMS OTP confirmation before activating seller dashboard & public listings
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/sms.php';
require_once __DIR__ . '/data/machines.php';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'joulemachinehub.com';
$pageTitle = "Verify Email & Mobile Number | Seller Quality Gate | Joule Machine Hub";
$pageDesc = "Confirm your business email address and mobile phone number with the 6-digit OTP codes to activate your machinery seller dashboard.";
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
$phone = $pending['phone'] ?? ($pending['reg_data']['phone'] ?? '');
$company = $pending['company'] ?? '';
$name = $pending['name'] ?? '';
$expiresAt = intval($pending['expires_at'] ?? (time() + 900));
$remainingSeconds = max(0, $expiresAt - time());
$mobileOtp = (string)($pending['mobile_otp'] ?? '');
$emailOtp = (string)($pending['email_otp'] ?? $pending['otp'] ?? '');

$error = '';
$success = '';

// Handle Actions (Verify, Resend, Cancel)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'verify';

    if ($action === 'resend') {
        $resendType = $_POST['resend_type'] ?? 'all';
        $resendResult = resendSellerRegistrationOtp($resendType);
        if ($resendResult['success']) {
            $success = $resendResult['message'];
            $pending = getPendingSellerRegistration();
            $expiresAt = intval($pending['expires_at'] ?? (time() + 900));
            $remainingSeconds = max(0, $expiresAt - time());
            $mobileOtp = (string)($pending['mobile_otp'] ?? '');
            $emailOtp = (string)($pending['email_otp'] ?? $pending['otp'] ?? '');
        } else {
            $error = $resendResult['message'];
        }
    } elseif ($action === 'cancel') {
        cancelPendingSellerRegistration();
        header('Location: register.php');
        exit;
    } elseif ($action === 'verify') {
        // Collect entered Email OTP
        $enteredEmailOtp = trim($_POST['email_otp'] ?? '');
        if (empty($enteredEmailOtp) && isset($_POST['e1'])) {
            $enteredEmailOtp = trim($_POST['e1'] ?? '') . trim($_POST['e2'] ?? '') . trim($_POST['e3'] ?? '') . trim($_POST['e4'] ?? '') . trim($_POST['e5'] ?? '') . trim($_POST['e6'] ?? '');
        }
        // Fallback to legacy field names
        if (empty($enteredEmailOtp) && isset($_POST['otp'])) {
            $enteredEmailOtp = trim($_POST['otp']);
        }
        if (empty($enteredEmailOtp) && isset($_POST['d1'])) {
            $enteredEmailOtp = trim($_POST['d1'] ?? '') . trim($_POST['d2'] ?? '') . trim($_POST['d3'] ?? '') . trim($_POST['d4'] ?? '') . trim($_POST['d5'] ?? '') . trim($_POST['d6'] ?? '');
        }

        // Collect entered Mobile OTP
        $enteredMobileOtp = trim($_POST['mobile_otp'] ?? '');
        if (empty($enteredMobileOtp) && isset($_POST['m1'])) {
            $enteredMobileOtp = trim($_POST['m1'] ?? '') . trim($_POST['m2'] ?? '') . trim($_POST['m3'] ?? '') . trim($_POST['m4'] ?? '') . trim($_POST['m5'] ?? '') . trim($_POST['m6'] ?? '');
        }

        $verifyResult = verifySellerRegistrationOtp($enteredEmailOtp, $enteredMobileOtp);
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

// Check if SMS provider is in simulation mode (or localhost)
$smsCfg = JmhSms::getConfig();
$isSimulationMode = ($smsCfg['provider'] ?? 'simulation') === 'simulation' || strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false;

require_once __DIR__ . '/includes/header.php';
?>

<main class="min-h-[85vh] bg-slate-900 py-10 sm:py-16 px-4 flex items-center justify-center">
  <div class="max-w-lg w-full bg-white rounded-3xl border-2 border-joule-yellow shadow-2xl overflow-hidden animate-in fade-in duration-300">
    
    <!-- Top Header Banner -->
    <div class="bg-joule-blue text-white p-6 sm:p-7 text-center border-b-4 border-joule-yellow relative">
      <div class="w-14 h-14 rounded-2xl bg-joule-yellow text-slate-950 flex items-center justify-center font-black text-2xl mx-auto mb-3 shadow-lg border-2 border-amber-300">
        <i class="fa-solid fa-shield-halved"></i>
      </div>
      <span class="inline-block uppercase tracking-wider text-[10px] font-black bg-white/20 text-yellow-300 px-3 py-1 rounded-full mb-1.5">
        Dual Quality Gate • Step 2 of 2
      </span>
      <h1 class="text-xl sm:text-2xl font-black text-white">Verify Email &amp; Mobile Number</h1>
      <p class="text-xs text-blue-200 mt-1 max-w-sm mx-auto">
        We require email and mobile confirmation to ensure authentic machinery listings and protect verified buyers worldwide.
      </p>
    </div>

    <!-- Verification Card Body -->
    <div class="p-6 sm:p-8 space-y-6">

      <!-- Contact Details Display Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
        <div class="space-y-0.5">
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Email Address:</span>
          <div class="font-extrabold text-joule-blue break-all flex items-center gap-1.5">
            <i class="fa-solid fa-envelope text-amber-500 text-[11px] shrink-0"></i>
            <span><?php echo htmlspecialchars($email); ?></span>
          </div>
        </div>
        <div class="space-y-0.5 border-t sm:border-t-0 sm:border-l border-slate-200 pt-2 sm:pt-0 sm:pl-3">
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Mobile Number:</span>
          <div class="font-extrabold text-slate-900 flex items-center gap-1.5">
            <i class="fa-solid fa-mobile-screen text-emerald-500 text-[11px] shrink-0"></i>
            <span><?php echo htmlspecialchars($phone); ?></span>
          </div>
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

      <!-- Simulation Mode Test Notice (Ensures local / staging test never gets blocked) -->
      <?php if ($isSimulationMode && !empty($mobileOtp)): ?>
        <div class="p-3 rounded-xl bg-amber-50/90 border border-amber-300 text-amber-900 text-xs flex items-center justify-between gap-2 shadow-2xs">
          <div class="flex items-center gap-2">
            <i class="fa-solid fa-vial text-amber-600 text-sm"></i>
            <div>
              <span class="font-bold">Test Mode:</span> Mobile SMS Code for <?php echo htmlspecialchars($phone); ?> is <strong class="font-mono bg-white px-2 py-0.5 rounded border border-amber-300 text-amber-950 font-black tracking-widest"><?php echo htmlspecialchars($mobileOtp); ?></strong>
            </div>
          </div>
          <button 
            type="button" 
            onclick="autoFillMobileOtp('<?php echo htmlspecialchars($mobileOtp); ?>')"
            class="text-[11px] font-black text-amber-900 hover:text-amber-950 bg-amber-200/80 hover:bg-amber-300 px-2.5 py-1 rounded-lg border border-amber-400 cursor-pointer shrink-0 transition-colors"
          >
            Auto-Fill
          </button>
        </div>
      <?php endif; ?>

      <!-- Dual 6-Digit Verification Form -->
      <form action="verify-otp.php" method="POST" id="dualOtpForm" class="space-y-6">
        <input type="hidden" name="action" value="verify">
        <input type="hidden" name="email_otp" id="fullEmailOtp" value="">
        <input type="hidden" name="mobile_otp" id="fullMobileOtp" value="">

        <!-- 1. Email OTP Input Section -->
        <div class="space-y-2 p-4 rounded-2xl bg-blue-50/40 border border-blue-200/80">
          <div class="flex items-center justify-between">
            <label class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
              <i class="fa-solid fa-envelope text-joule-brightblue"></i>
              <span>1. Email Verification Code</span>
            </label>
            <span class="text-[11px] text-slate-400 font-medium">6 digits</span>
          </div>
          
          <div class="flex items-center justify-center gap-1.5 sm:gap-2 pt-1" id="emailOtpBoxes">
            <?php for ($i = 1; $i <= 6; $i++): ?>
              <input 
                type="text" 
                name="e<?php echo $i; ?>" 
                id="e<?php echo $i; ?>" 
                inputmode="numeric" 
                pattern="[0-9]*" 
                maxlength="1" 
                autocomplete="off" 
                required 
                class="email-digit w-10 h-12 sm:w-12 sm:h-14 text-center font-black text-xl sm:text-2xl rounded-xl border-2 border-slate-300 text-slate-900 focus:outline-none focus:border-joule-blue focus:ring-2 focus:ring-blue-200 transition-all bg-white"
              >
            <?php endfor; ?>
          </div>
          <div class="text-[11px] text-slate-500 text-center pt-0.5">
            Check your inbox or spam for code sent to <strong class="text-slate-700"><?php echo htmlspecialchars($email); ?></strong>
          </div>
        </div>

        <!-- 2. Mobile SMS OTP Input Section -->
        <div class="space-y-2 p-4 rounded-2xl bg-emerald-50/40 border border-emerald-200/80">
          <div class="flex items-center justify-between">
            <label class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
              <i class="fa-solid fa-mobile-screen text-emerald-600"></i>
              <span>2. Mobile SMS Verification Code</span>
            </label>
            <span class="text-[11px] text-slate-400 font-medium">6 digits</span>
          </div>

          <div class="flex items-center justify-center gap-1.5 sm:gap-2 pt-1" id="mobileOtpBoxes">
            <?php for ($i = 1; $i <= 6; $i++): ?>
              <input 
                type="text" 
                name="m<?php echo $i; ?>" 
                id="m<?php echo $i; ?>" 
                inputmode="numeric" 
                pattern="[0-9]*" 
                maxlength="1" 
                autocomplete="off" 
                required 
                class="mobile-digit w-10 h-12 sm:w-12 sm:h-14 text-center font-black text-xl sm:text-2xl rounded-xl border-2 border-slate-300 text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-200 transition-all bg-white"
              >
            <?php endfor; ?>
          </div>
          <div class="text-[11px] text-slate-500 text-center pt-0.5">
            Check your SMS on <strong class="text-slate-700"><?php echo htmlspecialchars($phone); ?></strong>
          </div>
        </div>

        <!-- Expiration Countdown Indicator -->
        <div class="flex items-center justify-between text-xs font-bold text-slate-500 px-1">
          <span class="flex items-center gap-1.5">
            <i class="fa-solid fa-clock text-amber-500"></i>
            <span>Codes expire in:</span>
          </span>
          <span id="countdownTimer" class="font-black text-rose-600 text-sm">--:--</span>
        </div>

        <!-- Submit Button -->
        <button 
          type="submit" 
          id="verifySubmitBtn"
          class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-amber-400 to-yellow-400 hover:from-amber-500 hover:to-yellow-500 text-slate-950 font-black text-xs uppercase tracking-wider shadow-lg shadow-amber-400/20 transition-all hover:scale-[1.01] cursor-pointer flex items-center justify-center gap-2 border border-amber-300"
        >
          <i class="fa-solid fa-shield-check text-base"></i>
          <span>Verify Both Codes &amp; Activate Dashboard</span>
        </button>

      </form>

      <!-- Resend & Change Details Actions -->
      <div class="pt-4 border-t border-slate-100 space-y-2.5 text-xs">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
          <form action="verify-otp.php" method="POST" class="inline">
            <input type="hidden" name="action" value="resend">
            <input type="hidden" name="resend_type" value="all">
            <button 
              type="submit" 
              id="resendBtn" 
              class="text-joule-brightblue hover:text-blue-700 font-bold hover:underline cursor-pointer flex items-center gap-1"
            >
              <i class="fa-solid fa-rotate-right text-[11px]"></i>
              <span>Resend Both Codes</span>
              <span id="resendCountdown" class="text-slate-400 hidden">(60s)</span>
            </button>
          </form>

          <form action="verify-otp.php" method="POST" class="inline">
            <input type="hidden" name="action" value="cancel">
            <button 
              type="submit" 
              onclick="return confirm('Cancel this registration and edit your personal/company details?');"
              class="text-slate-500 hover:text-slate-700 font-semibold cursor-pointer flex items-center gap-1"
            >
              <i class="fa-solid fa-pen-to-square text-[11px]"></i>
              <span>Change Contact Details</span>
            </button>
          </form>
        </div>

        <!-- Trust Note -->
        <div class="bg-blue-50/60 rounded-xl p-3 text-[11px] text-slate-600 flex items-start gap-2 border border-blue-100">
          <i class="fa-solid fa-info-circle text-blue-600 mt-0.5 shrink-0"></i>
          <span>Note: Both Email and Mobile codes are also delivered together to your email address as a backup. Check your <strong>Spam / Junk</strong> folder if not found.</span>
        </div>
      </div>

    </div>

  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const emailDigits = [
    document.getElementById('e1'),
    document.getElementById('e2'),
    document.getElementById('e3'),
    document.getElementById('e4'),
    document.getElementById('e5'),
    document.getElementById('e6')
  ];

  const mobileDigits = [
    document.getElementById('m1'),
    document.getElementById('m2'),
    document.getElementById('m3'),
    document.getElementById('m4'),
    document.getElementById('m5'),
    document.getElementById('m6')
  ];

  const fullEmailInput = document.getElementById('fullEmailOtp');
  const fullMobileInput = document.getElementById('fullMobileOtp');

  if (emailDigits[0]) {
    emailDigits[0].focus();
  }

  function setupDigitInputs(inputs, hiddenInput, nextSectionFocus) {
    inputs.forEach((input, index) => {
      input.addEventListener('input', function() {
        const val = this.value.replace(/[^0-9]/g, '');
        this.value = val ? val.charAt(val.length - 1) : '';

        if (this.value && index < inputs.length - 1) {
          inputs[index + 1].focus();
        } else if (this.value && index === inputs.length - 1 && nextSectionFocus) {
          nextSectionFocus.focus();
        }
        syncInputs();
      });

      input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && !this.value && index > 0) {
          inputs[index - 1].focus();
        }
      });

      input.addEventListener('paste', function(e) {
        e.preventDefault();
        const pasteData = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
        if (pasteData) {
          for (let i = 0; i < inputs.length; i++) {
            inputs[i].value = pasteData.charAt(i) || '';
          }
          if (pasteData.length >= inputs.length && nextSectionFocus) {
            nextSectionFocus.focus();
          } else {
            const nextIdx = Math.min(pasteData.length, inputs.length - 1);
            inputs[nextIdx].focus();
          }
          syncInputs();
        }
      });
    });
  }

  setupDigitInputs(emailDigits, fullEmailInput, mobileDigits[0]);
  setupDigitInputs(mobileDigits, fullMobileInput, null);

  function syncInputs() {
    let emailCode = '';
    emailDigits.forEach(d => emailCode += d.value);
    fullEmailInput.value = emailCode;

    let mobileCode = '';
    mobileDigits.forEach(d => mobileCode += d.value);
    fullMobileInput.value = mobileCode;
  }

  // 15-Minute Expiration Countdown
  let remainingSeconds = <?php echo (int)$remainingSeconds; ?>;
  const countdownEl = document.getElementById('countdownTimer');
  const submitBtn = document.getElementById('verifySubmitBtn');

  function updateTimer() {
    if (remainingSeconds <= 0) {
      if (countdownEl) countdownEl.textContent = 'EXPIRED';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
      }
      return;
    }

    const mins = Math.floor(remainingSeconds / 60);
    const secs = remainingSeconds % 60;
    if (countdownEl) {
      countdownEl.textContent = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
    }
    remainingSeconds--;
  }

  updateTimer();
  setInterval(updateTimer, 1000);

  // 60-second Resend Cooldown
  const resendBtn = document.getElementById('resendBtn');
  const resendCountdown = document.getElementById('resendCountdown');
  let resendSeconds = 60;

  if (resendBtn && resendCountdown) {
    resendBtn.disabled = true;
    resendBtn.classList.add('opacity-60', 'cursor-not-allowed');
    resendCountdown.classList.remove('hidden');

    const resendTimer = setInterval(function() {
      resendSeconds--;
      if (resendSeconds <= 0) {
        clearInterval(resendTimer);
        resendBtn.disabled = false;
        resendBtn.classList.remove('opacity-60', 'cursor-not-allowed');
        resendCountdown.classList.add('hidden');
      } else {
        resendCountdown.textContent = '(' + resendSeconds + 's)';
      }
    }, 1000);
  }
});

function autoFillMobileOtp(code) {
  if (!code) return;
  for (let i = 1; i <= 6; i++) {
    const input = document.getElementById('m' + i);
    if (input && code.charAt(i - 1)) {
      input.value = code.charAt(i - 1);
    }
  }
  const fullMobile = document.getElementById('fullMobileOtp');
  if (fullMobile) fullMobile.value = code;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
