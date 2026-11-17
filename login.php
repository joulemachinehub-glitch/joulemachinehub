<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'joulemachinehub.com';
$pageTitle = "Account Login | Joule Machine Hub";
$pageDesc = "Log in to your Joule Machine Hub account to manage your machinery inventory, review buyer inquiries, and access your dashboard.";
$canonicalUrl = $protocol . $host . "/login";
$pageRobots = "noindex, nofollow, noarchive, nosnippet";

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/data/machines.php';

$error = '';
$success = '';

if (isset($_GET['registered'])) {
    $success = 'Account created successfully! Please log in below.';
}
if (isset($_GET['logged_out'])) {
    $success = 'You have been safely logged out.';
}
if (isset($_GET['reset_success'])) {
    $success = 'Your password has been reset successfully. Please log in with your new password.';
}

if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin.php');
    } else {
        header('Location: dashboard/index.php');
    }
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $remember = isset($_POST['remember']) ? true : false;

    if (empty($email) || empty($password)) {
        $error = 'Please enter both your email address and password.';
    } else {
        $result = loginUser($email, $password);
        if ($result['success']) {
            $_SESSION['welcome_back'] = true;
            if ($remember) {
                // Set remember me cookie for 30 days
                setcookie('jmh_user_email', $email, time() + (30 * 86400), '/', '', true, true);
            }
            $u = $result['user'] ?? $_SESSION['user'] ?? [];
            if (strtolower($u['email'] ?? '') === 'joulemachinehub@gmail.com' || ($u['role'] ?? '') === 'admin') {
                $_SESSION['admin_authenticated'] = true;
                $_SESSION['admin_access'] = true;
                header('Location: admin.php');
            } else {
                header('Location: dashboard/index.php');
            }
            exit;
        } else {
            $error = $result['message'];
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
        <i class="fa-solid fa-right-to-bracket"></i>
      </div>
      <span class="inline-block uppercase tracking-wider text-[10px] font-black bg-white/20 text-yellow-300 px-3 py-1 rounded-full mb-2">
        Member Portal Access
      </span>
      <h1 class="text-2xl sm:text-3xl font-black text-white">Log Into Your Account</h1>
      <p class="text-xs text-blue-200 mt-1 max-w-xs mx-auto">
        Access your machinery listings, buyer enquiries, and membership tools.
      </p>
    </div>

    <!-- Form Content -->
    <div class="p-6 sm:p-8 space-y-5">
      
      <?php if (!empty($success)): ?>
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2.5">
          <i class="fa-solid fa-circle-check text-base text-emerald-600 shrink-0"></i>
          <div><?php echo htmlspecialchars($success); ?></div>
        </div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold flex items-center gap-2.5">
          <i class="fa-solid fa-circle-exclamation text-base text-red-600 shrink-0"></i>
          <div><?php echo htmlspecialchars($error); ?></div>
        </div>
      <?php endif; ?>

      <form action="login.php" method="POST" class="space-y-4">
        
        <!-- Email Input -->
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Email Address
          </label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
              <i class="fa-solid fa-envelope text-xs"></i>
            </span>
            <input 
              type="text" 
              name="email" 
              value="<?php echo htmlspecialchars($_POST['email'] ?? ($_COOKIE['jmh_user_email'] ?? '')); ?>" 
              required 
              placeholder="name@company.com" 
              class="w-full pl-10 pr-3.5 py-3 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue focus:ring-1 focus:ring-joule-blue"
            >
          </div>
        </div>

        <!-- Password Input -->
        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
              Password
            </label>
            <a href="forgot-password.php" class="text-xs font-bold text-blue-600 hover:text-blue-800">
              Forgot password?
            </a>
          </div>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
              <i class="fa-solid fa-lock text-xs"></i>
            </span>
            <input 
              type="password" 
              name="password" 
              required 
              placeholder="Enter your account password" 
              class="w-full pl-10 pr-3.5 py-3 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue focus:ring-1 focus:ring-joule-blue"
            >
          </div>
        </div>

        <!-- Remember Me Checkbox -->
        <div class="flex items-center justify-between pt-1">
          <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
            <input 
              type="checkbox" 
              name="remember" 
              value="1" 
              <?php echo !empty($_COOKIE['jmh_user_email']) ? 'checked' : ''; ?>
              class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500"
            >
            <span>Remember me on this browser</span>
          </label>
        </div>

        <!-- Login Button -->
        <div class="pt-2">
          <button 
            type="submit" 
            class="w-full bg-joule-blue hover:bg-blue-800 text-white font-black text-sm py-3.5 px-6 rounded-2xl shadow-lg shadow-blue-900/20 transition-all flex items-center justify-center gap-2 cursor-pointer"
          >
            <i class="fa-solid fa-right-to-bracket"></i>
            <span>Log In to Dashboard</span>
          </button>
        </div>

      </form>

      <!-- Create New Account Button / Link -->
      <div class="pt-4 border-t border-slate-200 text-center space-y-3">
        <p class="text-xs text-slate-600">
          New to Joule Machine Hub?
        </p>
        <a 
          href="register.php" 
          class="w-full inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs py-3 px-4 rounded-xl border border-slate-300 transition-all"
        >
          <i class="fa-solid fa-user-plus text-blue-600"></i>
          <span>Create a New Company Account</span>
        </a>
      </div>

    </div>
  </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
