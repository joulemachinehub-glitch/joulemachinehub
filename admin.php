<?php
// Joule Machine Hub - Super Administrator Portal
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/data/machines.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Handle Logout Action
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['admin_authenticated'], $_SESSION['admin_access'], $_SESSION['user']);
    $redirectUrl = strtok($_SERVER['REQUEST_URI'] ?? '/admin', '?');
    header('Location: ' . ($redirectUrl ?: '/admin'));
    exit;
}

$isAdmin = !empty($_SESSION['admin_authenticated']) 
    && !empty($_SESSION['admin_access']) 
    && !empty($_SESSION['user']) 
    && (strtolower($_SESSION['user']['email'] ?? '') === 'joulemachinehub@gmail.com');

// 2. If Not Logged In, Handle Admin Authentication Form
$loginError = '';

// Generate or refresh captcha
if (empty($_SESSION['admin_captcha_ans']) || isset($_GET['new_captcha'])) {
    $n1 = rand(6, 28);
    $n2 = rand(4, 18);
    $_SESSION['admin_captcha_n1'] = $n1;
    $_SESSION['admin_captcha_n2'] = $n2;
    $_SESSION['admin_captcha_ans'] = $n1 + $n2;
}

if (!$isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['admin_email'] ?? ''));
    $password = trim($_POST['admin_password'] ?? '');
    $captchaInput = intval($_POST['admin_captcha'] ?? -999);
    $expectedCaptcha = intval($_SESSION['admin_captcha_ans'] ?? -998);

    if (empty($email) || empty($password)) {
        $loginError = 'Please enter both your administrator email and password.';
    } elseif ($captchaInput !== $expectedCaptcha) {
        $loginError = 'Security verification failed: Incorrect captcha answer. Please try again.';
        $n1 = rand(6, 28);
        $n2 = rand(4, 18);
        $_SESSION['admin_captcha_n1'] = $n1;
        $_SESSION['admin_captcha_n2'] = $n2;
        $_SESSION['admin_captcha_ans'] = $n1 + $n2;
    } elseif ($email !== 'joulemachinehub@gmail.com' || $password !== 'Bala$#2001') {
        $loginError = 'Authentication failed: Invalid administrator credentials.';
        $n1 = rand(6, 28);
        $n2 = rand(4, 18);
        $_SESSION['admin_captcha_n1'] = $n1;
        $_SESSION['admin_captcha_n2'] = $n2;
        $_SESSION['admin_captcha_ans'] = $n1 + $n2;
    } else {
        // Authenticated Successfully
        $_SESSION['admin_authenticated'] = true;
        $_SESSION['admin_access'] = true;
        $_SESSION['user'] = [
            'id' => 'usr-admin-joule',
            'name' => 'Joule Super Admin',
            'company' => 'Joule Machine Hub Global Desk',
            'email' => 'joulemachinehub@gmail.com',
            'phone' => '',
            'role' => 'admin',
            'city' => 'Chennai'
        ];
        unset($_SESSION['admin_captcha_n1'], $_SESSION['admin_captcha_n2'], $_SESSION['admin_captcha_ans']);
        $isAdmin = true;
    }
}

// 3. Render Secure Admin Login Screen if not authenticated
if (!$isAdmin) {
    $pageTitle = "Admin Login - Super Administrator Portal | Joule Hub";
    $pageRobots = "noindex, nofollow, noarchive, nosnippet";
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="py-12 sm:py-20 px-4 bg-slate-900 flex-1 flex items-center justify-center min-h-[75vh]">
      <div class="max-w-md w-full bg-white rounded-3xl border-2 border-joule-yellow shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Header -->
        <div class="bg-joule-blue text-white p-7 text-center border-b-4 border-joule-yellow relative">
          <div class="w-16 h-16 rounded-2xl bg-joule-yellow text-slate-950 flex items-center justify-center font-black text-2xl mx-auto mb-3 shadow-lg">
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <span class="inline-block uppercase tracking-wider text-[10px] font-black bg-white/20 text-yellow-300 px-3 py-1 rounded-full mb-2">
            Restricted System Access
          </span>
          <h1 class="text-2xl font-black text-white">Super Admin Portal</h1>
          <p class="text-xs text-blue-200 mt-1">
            Sign in with verified administrator credentials to access machine content, photo alignment & buyer leads.
          </p>
        </div>

        <div class="p-6 sm:p-8 space-y-5">
          <?php if (!empty($loginError)): ?>
            <div class="bg-rose-50 border-2 border-rose-300 text-rose-800 rounded-xl p-3.5 text-xs flex items-center gap-2 font-semibold">
              <i class="fa-solid fa-circle-exclamation text-rose-600 text-base shrink-0"></i>
              <span><?php echo htmlspecialchars($loginError); ?></span>
            </div>
          <?php endif; ?>

          <form action="" method="POST" autocomplete="off" class="space-y-4 text-xs">
            
            <!-- Email -->
            <div>
              <label class="block text-slate-700 font-black mb-1.5">
                Administrator Email *
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                  <i class="fa-solid fa-envelope"></i>
                </span>
                <input 
                  type="email" 
                  name="admin_email" 
                  required 
                  autocomplete="off" 
                  placeholder="Enter administrator email" 
                  class="w-full pl-10 pr-3.5 py-3 border-2 border-slate-300 rounded-xl focus:outline-none focus:border-joule-blue text-xs font-semibold text-slate-900"
                >
              </div>
            </div>

            <!-- Password -->
            <div>
              <label class="block text-slate-700 font-black mb-1.5">
                Master Password *
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                  <i class="fa-solid fa-lock"></i>
                </span>
                <input 
                  type="password" 
                  name="admin_password" 
                  required 
                  autocomplete="new-password" 
                  placeholder="Enter master password" 
                  class="w-full pl-10 pr-3.5 py-3 border-2 border-slate-300 rounded-xl focus:outline-none focus:border-joule-blue text-xs font-semibold text-slate-900"
                >
              </div>
            </div>

            <!-- Security Captcha -->
            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label class="block text-slate-700 font-black">
                  Security Captcha Verification *
                </label>
                <a href="?new_captcha=1" class="text-[11px] text-joule-brightblue hover:underline flex items-center gap-1 font-bold">
                  <i class="fa-solid fa-rotate-right text-[10px]"></i>
                  <span>Reload Captcha</span>
                </a>
              </div>
              <div class="grid grid-cols-2 gap-3 items-center">
                <div class="bg-slate-900 text-yellow-300 font-mono font-black text-center py-2.5 px-3 rounded-xl border-2 border-slate-700 select-none text-sm tracking-widest shadow-inner">
                  <?php echo $_SESSION['admin_captcha_n1']; ?> + <?php echo $_SESSION['admin_captcha_n2']; ?> = ?
                </div>
                <input 
                  type="number" 
                  name="admin_captcha" 
                  required 
                  autocomplete="off" 
                  placeholder="Enter answer" 
                  class="w-full px-3.5 py-2.5 border-2 border-slate-300 rounded-xl focus:outline-none focus:border-joule-blue text-xs font-black text-slate-900 text-center"
                >
              </div>
            </div>

            <button 
              type="submit" 
              class="w-full bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black py-3.5 px-4 rounded-xl text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer border border-amber-300 mt-2"
            >
              <i class="fa-solid fa-right-to-bracket"></i>
              <span>Authenticate & Enter Admin Desk</span>
            </button>
          </form>

          <div class="border-t border-slate-200 pt-4 text-center text-xs text-slate-500">
            <a href="/" class="text-slate-600 hover:text-slate-900 font-bold inline-flex items-center gap-1">
              <i class="fa-solid fa-arrow-left text-[10px]"></i>
              <span>Back to Public Marketplace</span>
            </a>
          </div>

        </div>
      </div>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// 4. Authenticated Admin Dashboard Below
$currentUser = getCurrentUser();
$totalMachines = count($machines);

// Load inquiries
$leadsFile = __DIR__ . '/data/leads.json';
$leads = [];
if (file_exists($leadsFile)) {
    $leads = json_decode(@file_get_contents($leadsFile), true) ?: [];
}
$totalLeads = count($leads);

$pageTitle = "Admin Portal - Joule Machine Hub";
$pageRobots = "noindex, nofollow, noarchive, nosnippet"; // Strict Google non-index restriction for Admin Portal

require_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-screen bg-slate-100 pb-16">

  <!-- TOP ADMIN BAR -->
  <div class="bg-joule-blue text-white border-b-4 border-joule-yellow shadow-md">
    <div class="max-w-7xl mx-auto px-4 py-4 sm:py-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <span class="bg-amber-400 text-slate-950 text-[10px] uppercase font-black px-2.5 py-0.5 rounded-full tracking-wider">
              Super Admin Control
            </span>
            <span class="text-xs text-blue-200">Logged in as: <strong class="text-white"><?php echo htmlspecialchars($currentUser['email'] ?? 'joulemachinehub@gmail.com'); ?></strong></span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-black text-white mt-1">
            Joule Machine Hub Admin Desk
          </h1>
          <p class="text-xs text-blue-200 mt-0.5">
            Full content editing for all 42 machinery listings, 1-click image rotation, and live buyer RFQ tracking.
          </p>
        </div>

        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
          <button type="button" onclick="purgeLiveCache()" id="purgeCacheBtn" class="bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs px-3 py-2 rounded-xl transition-all flex items-center gap-1.5 shadow-md border border-emerald-400 cursor-pointer" title="Purge LiteSpeed & OPcache">
            <i class="fa-solid fa-bolt text-xs text-yellow-300"></i>
            <span>Purge Cache</span>
          </button>
          <button type="button" onclick="openAddMachineModal()" class="bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black text-xs px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 shadow-md border border-amber-300 cursor-pointer">
            <i class="fa-solid fa-circle-plus text-sm"></i>
            <span>Add Machine (Save in DB)</span>
          </button>
          <a href="/" target="_blank" class="bg-blue-900/80 hover:bg-blue-800 text-white font-bold text-xs px-3.5 py-2 rounded-xl border border-blue-700/80 transition-colors flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
            <span>View Live Site</span>
          </a>
          <a href="?action=logout" class="bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs px-3.5 py-2 rounded-xl transition-colors flex items-center gap-1.5 shadow-sm">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Sign Out</span>
          </a>
        </div>
      </div>

      <!-- Quick KPI Counters -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5">
        <div class="bg-slate-900/60 rounded-xl p-3 border border-white/10">
          <div class="text-[10px] uppercase font-bold text-blue-200">Catalog Inventory</div>
          <div class="text-xl sm:text-2xl font-black text-white"><?php echo $totalMachines; ?> Machines</div>
        </div>
        <div class="bg-slate-900/60 rounded-xl p-3 border border-white/10">
          <div class="text-[10px] uppercase font-bold text-amber-300">Total Leads / RFQs</div>
          <div class="text-xl sm:text-2xl font-black text-amber-300"><?php echo $totalLeads; ?> Submissions</div>
        </div>
        <div class="bg-slate-900/60 rounded-xl p-3 border border-white/10">
          <div class="text-[10px] uppercase font-bold text-emerald-300">Catalog Images</div>
          <div class="text-xl sm:text-2xl font-black text-emerald-300">125 Photos</div>
        </div>
        <div class="bg-slate-900/60 rounded-xl p-3 border border-white/10">
          <div class="text-[10px] uppercase font-bold text-blue-200">Global Hubs</div>
          <div class="text-xl sm:text-2xl font-black text-white">10 Countries</div>
        </div>
      </div>
    </div>
  </div>

  <!-- NAVIGATION TABS -->
  <div class="max-w-7xl mx-auto px-4 mt-6">
    <div class="bg-white rounded-2xl border-2 border-slate-200 p-2 shadow-xs flex items-center gap-2 overflow-x-auto">
      <button type="button" onclick="switchAdminTab('productsTab')" id="btnProductsTab" class="admin-tab-btn flex-1 min-w-[160px] py-2.5 px-4 rounded-xl font-black text-xs flex items-center justify-center gap-2 transition-all bg-joule-blue text-white shadow-sm">
        <i class="fa-solid fa-boxes-stacked text-amber-400"></i>
        <span>1. Edit Products (42 Units)</span>
      </button>

      <button type="button" onclick="switchAdminTab('imagesTab')" id="btnImagesTab" class="admin-tab-btn flex-1 min-w-[160px] py-2.5 px-4 rounded-xl font-bold text-xs text-slate-700 hover:text-slate-900 hover:bg-slate-100 flex items-center justify-center gap-2 transition-all">
        <i class="fa-solid fa-rotate text-blue-600"></i>
        <span>2. 1-Click Image Rotator</span>
      </button>

      <button type="button" onclick="switchAdminTab('leadsTab')" id="btnLeadsTab" class="admin-tab-btn flex-1 min-w-[160px] py-2.5 px-4 rounded-xl font-bold text-xs text-slate-700 hover:text-slate-900 hover:bg-slate-100 flex items-center justify-center gap-2 transition-all">
        <i class="fa-solid fa-envelope-open-text text-emerald-600"></i>
        <span>3. Buyer Inquiries (<?php echo $totalLeads; ?>)</span>
      </button>
    </div>
  </div>

  <!-- MAIN TAB CONTENT CONTAINER -->
  <div class="max-w-7xl mx-auto px-4 mt-6">

    <!-- ========================================== -->
    <!-- TAB 1: PRODUCT CONTENT EDITOR (42 UNITS)  -->
    <!-- ========================================== -->
    <div id="productsTab" class="admin-tab-content space-y-4">
      
      <!-- Search & Filters -->
      <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="relative w-full sm:w-96">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
            <i class="fa-solid fa-magnifying-glass"></i>
          </span>
          <input 
            type="text" 
            id="adminSearchInput" 
            onkeyup="filterAdminMachines()"
            placeholder="Search by title, brand, model, city, country..." 
            class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-600 bg-slate-50 focus:bg-white"
          >
        </div>

        <div class="flex items-center gap-3 text-xs text-slate-500 w-full sm:w-auto justify-end">
          <span>Showing <strong id="visibleMachineCount" class="text-slate-900"><?php echo $totalMachines; ?></strong> machines</span>
          <button type="button" onclick="openAddMachineModal()" class="bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black text-xs px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 shadow-xs border border-amber-300 cursor-pointer">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Add New Machine</span>
          </button>
        </div>
      </div>

      <!-- Machines Grid Table -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="adminMachinesContainer">
        <?php foreach ($machines as $idx => $m): ?>
          <div 
            class="machine-card bg-white rounded-2xl border-2 border-slate-200 overflow-hidden shadow-xs hover:border-joule-brightblue transition-all flex flex-col justify-between"
            data-search="<?php echo htmlspecialchars(strtolower($m['title'] . ' ' . $m['brand'] . ' ' . $m['model'] . ' ' . $m['category'] . ' ' . $m['city'] . ' ' . $m['country'])); ?>"
          >
            <div>
              <!-- Machine Header Thumbnail -->
              <div class="relative aspect-[16/10] bg-slate-950 overflow-hidden flex items-center justify-center">
                <img 
                  src="<?php echo htmlspecialchars($m['images'][0]); ?>" 
                  alt="<?php echo htmlspecialchars($m['title']); ?>" 
                  class="w-full h-full object-contain p-1"
                  loading="lazy"
                >
                <span class="absolute top-2 left-2 bg-slate-900/90 text-white text-[9px] font-black px-2 py-0.5 rounded border border-white/20">
                  #<?php echo $idx + 1; ?> • ID: <?php echo htmlspecialchars($m['id']); ?>
                </span>
                <span class="absolute top-2 right-2 bg-joule-yellow text-slate-950 text-[10px] font-black px-2 py-0.5 rounded shadow">
                  <?php echo formatMachinePrice($m); ?>
                </span>
              </div>

              <!-- Content Preview -->
              <div class="p-4 space-y-2">
                <div class="text-[10px] font-black uppercase text-blue-600 tracking-wider">
                  <?php echo htmlspecialchars($m['brand']); ?> • <?php echo htmlspecialchars($m['category']); ?>
                </div>

                <h3 class="font-black text-slate-950 text-sm leading-snug line-clamp-2">
                  <?php echo htmlspecialchars($m['title']); ?>
                </h3>

                <div class="text-[11px] text-slate-500 flex items-center gap-1.5 pt-1">
                  <i class="fa-solid fa-location-dot text-amber-500"></i>
                  <span><?php echo htmlspecialchars($m['city']); ?>, <?php echo htmlspecialchars($m['country']); ?> (Year: <?php echo htmlspecialchars($m['year']); ?>)</span>
                </div>

                <p class="text-[11px] text-slate-600 line-clamp-2 pt-1 border-t border-slate-100">
                  <?php echo htmlspecialchars($m['short_desc']); ?>
                </p>
              </div>
            </div>

            <!-- Card Actions -->
            <div class="p-4 pt-0 border-t border-slate-100 flex items-center gap-2 mt-2">
              <button 
                type="button" 
                onclick='openEditModal(<?php echo json_encode($m, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'
                class="flex-1 bg-joule-blue hover:bg-blue-900 text-white font-bold py-2 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer"
              >
                <i class="fa-solid fa-pen-to-square text-amber-400"></i>
                <span>Edit Content</span>
              </button>

              <button 
                type="button" 
                onclick="jumpToImageRotator('<?php echo htmlspecialchars($m['id']); ?>')"
                class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 px-3 rounded-xl text-xs flex items-center justify-center gap-1 transition-colors cursor-pointer border border-slate-300"
                title="Rotate Photos"
              >
                <i class="fa-solid fa-rotate text-blue-600"></i>
              </button>

              <a 
                href="<?php echo htmlspecialchars(getMachineUrl($m)); ?>" 
                target="_blank"
                class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 px-3 rounded-xl text-xs flex items-center justify-center gap-1 transition-colors border border-slate-300"
                title="View on site"
              >
                <i class="fa-solid fa-eye"></i>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    </div>

    <!-- ========================================== -->
    <!-- TAB 2: 1-CLICK IMAGE ROTATOR & ALIGNMENT   -->
    <!-- ========================================== -->
    <div id="imagesTab" class="admin-tab-content hidden space-y-6">
      
      <!-- Select Machine Dropdown -->
      <div class="bg-white rounded-2xl border-2 border-slate-200 p-5 shadow-xs">
        <label class="block text-slate-800 font-black text-sm mb-2">
          Select Machine to Inspect & Rotate Images Straight:
        </label>
        <select 
          id="rotatorMachineSelect" 
          onchange="renderRotatorGallery(this.value)"
          class="w-full p-3 border-2 border-slate-300 rounded-xl font-bold text-xs bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600"
        >
          <?php foreach ($machines as $idx => $m): ?>
            <option value="<?php echo htmlspecialchars($m['id']); ?>">
              #<?php echo $idx + 1; ?> - <?php echo htmlspecialchars($m['title']); ?> (<?php echo count($m['images']); ?> photos)
            </option>
          <?php endforeach; ?>
        </select>
        <p class="text-[11px] text-slate-500 mt-2">
          <i class="fa-solid fa-circle-info text-blue-500 mr-1"></i>
          Click <strong>Rotate 90° Left</strong> or <strong>Rotate 90° Right</strong> under any photo to turn it straight immediately. The file on disk updates and your live product page updates instantly.
        </p>
      </div>

      <!-- Active Machine Images Showcase -->
      <div id="rotatorGalleryContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Injected via JavaScript -->
      </div>

    </div>

    <!-- ========================================== -->
    <!-- TAB 3: BUYER INQUIRIES & LEADS DESK        -->
    <!-- ========================================== -->
    <div id="leadsTab" class="admin-tab-content hidden space-y-4">
      <div class="bg-white rounded-2xl border-2 border-slate-200 p-5 shadow-xs flex items-center justify-between">
        <div>
          <h2 class="text-lg font-black text-slate-900">Incoming Buyer Inquiries & Quotes</h2>
          <p class="text-xs text-slate-500">Live inquiries saved into <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-700 font-mono">data/leads.json</code> & MySQL database.</p>
        </div>
        <span class="bg-emerald-100 text-emerald-800 text-xs font-black px-3 py-1 rounded-full">
          Total: <?php echo $totalLeads; ?>
        </span>
      </div>

      <!-- Live Email Notifications Monitor & 1-Click Tester -->
      <div class="bg-gradient-to-r from-blue-900 to-slate-900 text-white rounded-2xl p-5 border-2 border-joule-yellow shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
              <span class="text-xs font-black uppercase tracking-wider text-joule-yellow">Live Lead Email Alert Delivery</span>
            </div>
            <p class="text-xs text-slate-300 mt-1">
              Every inquiry is instantly delivered to 3 verified mailboxes: <br class="hidden sm:inline">
              <strong class="text-white">balamuruganp0405@gmail.com</strong>, <strong class="text-white">jouleassetvaluers@gmail.com</strong>, <strong class="text-white">joulemachinehub@gmail.com</strong>
            </p>
          </div>
          <div class="flex items-center gap-2">
            <button 
              type="button" 
              onclick="document.getElementById('smtpConfigBox').classList.toggle('hidden')"
              class="bg-blue-800 hover:bg-blue-700 text-white font-bold py-2.5 px-3 rounded-xl text-xs flex items-center gap-1.5 border border-blue-600 cursor-pointer"
            >
              <i class="fa-solid fa-gear"></i>
              <span>Hostinger SMTP Setup</span>
            </button>
            <button 
              type="button" 
              id="testMailBtn"
              onclick="sendTestEmail()"
              class="bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black py-2.5 px-4 rounded-xl text-xs shrink-0 flex items-center justify-center gap-2 shadow-md cursor-pointer transition-transform hover:scale-105"
            >
              <i class="fa-solid fa-paper-plane"></i>
              <span>Send Test Email</span>
            </button>
          </div>
        </div>

        <!-- Collapsible Hostinger SMTP Setup Card -->
        <?php 
          require_once __DIR__ . '/includes/mailer.php';
          $smtpCfg = JmhMailer::getSmtpConfig();
        ?>
        <div id="smtpConfigBox" class="hidden bg-slate-950/80 rounded-xl p-4 border border-blue-500/40 space-y-3 text-xs">
          <div class="font-bold text-joule-yellow flex items-center gap-2">
            <i class="fa-solid fa-shield-halved"></i>
            <span>Hostinger Authenticated SMTP (Direct to Primary Inbox Guarantee)</span>
          </div>
          <p class="text-[11px] text-slate-300">
            For 100% Inbox delivery on Gmail, enter your Hostinger Email credentials below (Hostinger hPanel → Emails → Create Email):
          </p>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block text-slate-400 mb-1 font-semibold">SMTP Host</label>
              <input type="text" id="smtpHost" value="<?php echo htmlspecialchars($smtpCfg['host'] ?? 'smtp.hostinger.com'); ?>" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-white font-mono text-xs">
            </div>
            <div>
              <label class="block text-slate-400 mb-1 font-semibold">Hostinger Email / User</label>
              <input type="email" id="smtpUser" value="<?php echo htmlspecialchars($smtpCfg['username'] ?? ''); ?>" placeholder="e.g. notifications@joulemachinehub.com" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-white font-mono text-xs">
            </div>
            <div>
              <label class="block text-slate-400 mb-1 font-semibold">Email Password</label>
              <input type="password" id="smtpPass" placeholder="<?php echo !empty($smtpCfg['password']) ? '••••••••' : 'Enter Hostinger email password'; ?>" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-white font-mono text-xs">
            </div>
          </div>
          <div class="flex items-center justify-between pt-1">
            <label class="flex items-center gap-2 cursor-pointer text-slate-300">
              <input type="checkbox" id="smtpEnabled" <?php echo !empty($smtpCfg['enabled']) ? 'checked' : ''; ?> class="accent-joule-yellow">
              <span>Enable Authenticated SMTP Mode</span>
            </label>
            <button type="button" onclick="saveSmtpSettings()" class="bg-emerald-600 hover:bg-emerald-500 text-white font-black py-1.5 px-4 rounded-lg text-xs cursor-pointer">
              Save SMTP Configuration
            </button>
          </div>
        </div>

        <div id="testMailResult" class="hidden"></div>
      </div>

      <?php if (empty($leads)): ?>
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-500">
          <i class="fa-regular fa-folder-open text-4xl mb-3 text-slate-300"></i>
          <p class="text-sm font-bold">No buyer inquiries received yet.</p>
        </div>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($leads as $l): ?>
            <?php 
              $data = $l['data'] ?? []; 
              $cleanPhone = preg_replace('/[^0-9]/', '', $data['Buyer Mobile (WhatsApp)'] ?? $data['Seller Mobile'] ?? $data['Buyer Mobile'] ?? '');
            ?>
            <div class="bg-white rounded-2xl border-2 border-slate-200 p-4 sm:p-5 shadow-xs hover:border-blue-400 transition-colors">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div>
                  <span class="text-[10px] uppercase font-black bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                    <?php echo htmlspecialchars($data['Form Type'] ?? 'Buyer Inquiry'); ?>
                  </span>
                  <h3 class="font-black text-slate-950 text-base mt-1">
                    <?php echo htmlspecialchars($data['Machine Title'] ?? $data['Required Machine'] ?? $data['Machine Name & Model'] ?? 'General Inquiry'); ?>
                  </h3>
                </div>
                <div class="text-[11px] text-slate-400 shrink-0">
                  <i class="fa-regular fa-clock mr-1"></i><?php echo htmlspecialchars($l['timestamp'] ?? ''); ?>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs text-slate-700 py-3">
                <div>
                  <span class="text-slate-400 block text-[10px] uppercase font-bold">Buyer / Company:</span>
                  <span class="font-black text-slate-900"><?php echo htmlspecialchars($data['Buyer Name / Company'] ?? $data['Buyer Name'] ?? $data['Contact Person'] ?? 'N/A'); ?></span>
                </div>
                <div>
                  <span class="text-slate-400 block text-[10px] uppercase font-bold">Contact Phone:</span>
                  <span class="font-bold text-slate-900"><?php echo htmlspecialchars($data['Buyer Mobile (WhatsApp)'] ?? $data['Buyer Mobile'] ?? $data['Seller Mobile'] ?? 'N/A'); ?></span>
                </div>
                <div>
                  <span class="text-slate-400 block text-[10px] uppercase font-bold">City / Location:</span>
                  <span class="font-bold text-slate-900"><?php echo htmlspecialchars($data['Buyer Location'] ?? $data['Preferred City'] ?? 'N/A'); ?></span>
                </div>
              </div>

              <?php if (!empty($data['Target Budget Offer']) || !empty($data['Purchase Timeline']) || !empty($data['Buyer Note / Question'])): ?>
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 text-xs space-y-1">
                  <?php if (!empty($data['Target Budget Offer'])): ?>
                    <div><strong>Budget Offer:</strong> <?php echo htmlspecialchars($data['Target Budget Offer']); ?></div>
                  <?php endif; ?>
                  <?php if (!empty($data['Purchase Timeline'])): ?>
                    <div><strong>Timeline:</strong> <?php echo htmlspecialchars($data['Purchase Timeline']); ?></div>
                  <?php endif; ?>
                  <?php if (!empty($data['Buyer Note / Question'])): ?>
                    <div class="text-slate-600"><strong>Note:</strong> <?php echo htmlspecialchars($data['Buyer Note / Question']); ?></div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>

              <div class="mt-3 pt-2 flex items-center justify-end gap-2">
                <?php if (!empty($cleanPhone)): ?>
                  <a 
                    href="https://wa.me/<?php echo $cleanPhone; ?>" 
                    target="_blank"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2 px-4 rounded-xl flex items-center gap-1.5 shadow-sm transition-colors"
                  >
                    <i class="fa-brands fa-whatsapp"></i>
                    <span>Reply on WhatsApp (<?php echo $cleanPhone; ?>)</span>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>

</div>

<!-- ========================================== -->
<!-- EDIT PRODUCT MODAL                         -->
<!-- ========================================== -->
<div id="editProductModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-3 sm:p-4 bg-slate-950/80 backdrop-blur-xs">
  <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[92vh] flex flex-col overflow-hidden shadow-2xl border-2 border-joule-blue">
    
    <!-- Modal Header -->
    <div class="bg-joule-blue text-white p-4 sm:p-5 flex items-center justify-between border-b-2 border-joule-yellow shrink-0">
      <div>
        <span class="text-[10px] uppercase font-black text-joule-yellow">Live Content Editor</span>
        <h3 class="text-base sm:text-lg font-black text-white" id="editModalHeading">Edit Machine Details</h3>
      </div>
      <button onclick="closeEditModal()" class="w-8 h-8 rounded-full bg-blue-950 text-slate-300 hover:text-white flex items-center justify-center cursor-pointer">
        ✕
      </button>
    </div>

    <!-- Scrollable Edit Form -->
    <form id="productEditForm" onsubmit="saveProductChanges(event)" class="overflow-y-auto p-4 sm:p-6 space-y-4 text-xs flex-1">
      <input type="hidden" name="id" id="editMachineId" value="">

      <div>
        <label class="block text-slate-700 font-black mb-1">Machine Title *</label>
        <input type="text" name="title" id="editTitle" required class="w-full px-3 py-2 border border-slate-300 rounded-xl font-bold text-xs focus:ring-2 focus:ring-blue-600">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-slate-700 font-bold mb-1">Brand / Make *</label>
          <input type="text" name="brand" id="editBrand" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs">
        </div>
        <div>
          <label class="block text-slate-700 font-bold mb-1">Model Specification *</label>
          <input type="text" name="model" id="editModel" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs">
        </div>
      </div>

      <div class="grid grid-cols-3 gap-3">
        <div>
          <label class="block text-slate-700 font-bold mb-1">Category *</label>
          <input type="text" name="category" id="editCategory" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs">
        </div>
        <div>
          <label class="block text-slate-700 font-bold mb-1">Year of Mfg *</label>
          <input type="number" name="year" id="editYear" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs">
        </div>
        <div>
          <label class="block text-slate-700 font-bold mb-1">Condition</label>
          <input type="text" name="condition" id="editCondition" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3 bg-amber-50/60 border border-amber-200 p-3 rounded-xl">
        <div>
          <label class="block text-slate-800 font-black mb-1">Price in INR (₹) *</label>
          <input type="number" name="price" id="editPrice" required placeholder="e.g. 510000" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-black text-slate-900 bg-white">
          <span class="text-[10px] text-slate-500">Auto-formatted as Lakhs / Crore on frontend</span>
        </div>
        <div>
          <label class="block text-slate-800 font-black mb-1">Price in USD ($)</label>
          <input type="number" name="price_usd" id="editPriceUsd" placeholder="e.g. 6000" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-black text-slate-900 bg-white">
          <span class="text-[10px] text-slate-500">Shown to international visitors</span>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-slate-700 font-bold mb-1">City Location *</label>
          <input type="text" name="city" id="editCity" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs">
        </div>
        <div>
          <label class="block text-slate-700 font-bold mb-1">Country *</label>
          <input type="text" name="country" id="editCountry" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs">
        </div>
      </div>

      <div>
        <label class="block text-slate-700 font-bold mb-1">Short Description (Catalog Cards)</label>
        <textarea name="short_desc" id="editShortDesc" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs"></textarea>
      </div>

      <div>
        <label class="block text-slate-700 font-bold mb-1">Detailed Technical Overview</label>
        <textarea name="detailed_desc" id="editDetailedDesc" rows="4" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs"></textarea>
      </div>

      <!-- Footer Buttons -->
      <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
        <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-700 font-bold text-xs hover:bg-slate-100">
          Cancel
        </button>
        <button type="submit" id="saveEditBtn" class="bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black py-2.5 px-6 rounded-xl text-xs shadow-md flex items-center gap-2 cursor-pointer">
          <i class="fa-solid fa-floppy-disk"></i>
          <span>Save Changes Live</span>
        </button>
      </div>
    </form>

  </div>
</div>

<!-- ========================================== -->
<!-- ADD NEW MACHINE MODAL (SAVE IN DATABASE)   -->
<!-- ========================================== -->
<div id="addMachineModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-3 sm:p-4 bg-slate-950/80 backdrop-blur-xs">
  <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[92vh] flex flex-col overflow-hidden shadow-2xl border-2 border-joule-yellow">
    
    <!-- Modal Header -->
    <div class="bg-joule-blue text-white p-4 sm:p-5 flex items-center justify-between border-b-2 border-joule-yellow shrink-0">
      <div>
        <div class="flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          <span class="text-[10px] uppercase font-black text-joule-yellow tracking-wider">MySQL Database Connected</span>
        </div>
        <h3 class="text-base sm:text-lg font-black text-white mt-0.5">Add New Machinery Listing</h3>
        <p class="text-[11px] text-blue-200">Data saves directly to Hostinger MySQL (<code class="text-yellow-300 font-mono">machines</code> & <code class="text-yellow-300 font-mono">seller_products</code>) and goes live instantly.</p>
      </div>
      <button onclick="closeAddMachineModal()" class="w-8 h-8 rounded-full bg-blue-950 text-slate-300 hover:text-white flex items-center justify-center cursor-pointer">
        ✕
      </button>
    </div>

    <!-- Scrollable Add Form -->
    <form id="addMachineForm" onsubmit="saveNewMachine(event)" class="overflow-y-auto p-4 sm:p-6 space-y-4 text-xs flex-1">

      <!-- Section: Machine Name -->
      <div>
        <label class="block text-slate-700 font-black mb-1">Machine Title *</label>
        <input 
          type="text" 
          name="title" 
          required 
          placeholder="e.g. Haas VF-4 CNC Vertical Machining Center" 
          class="w-full px-3 py-2.5 border-2 border-slate-300 rounded-xl font-bold text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none"
        >
      </div>

      <!-- Section: Brand, Model, Category -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
          <label class="block text-slate-700 font-bold mb-1">Brand / Make *</label>
          <input type="text" name="brand" required placeholder="e.g. Haas" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs">
        </div>
        <div>
          <label class="block text-slate-700 font-bold mb-1">Model Specification *</label>
          <input type="text" name="model" required placeholder="e.g. VF-4" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs">
        </div>
        <div>
          <label class="block text-slate-700 font-bold mb-1">Category *</label>
          <select name="category" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs bg-white font-semibold">
            <option value="Presses & Stamping">Presses & Stamping</option>
            <option value="Cold Formers & Headers">Cold Formers & Headers</option>
            <option value="Lathes & Turning">Lathes & Turning</option>
            <option value="Machining Centers & Milling" selected>Machining Centers & Milling</option>
            <option value="Grinding Machines">Grinding Machines</option>
            <option value="Gear Machinery">Gear Machinery</option>
            <option value="Wire & Fastener Equipment">Wire & Fastener Equipment</option>
            <option value="Plastics & Rubber">Plastics & Rubber</option>
            <option value="Lifts & Access Equipment">Lifts & Access Equipment</option>
          </select>
        </div>
      </div>

      <!-- Section: Year, Condition, Equipment Type -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
          <label class="block text-slate-700 font-bold mb-1">Year of Mfg *</label>
          <input type="number" name="year" required value="<?php echo date('Y') - 5; ?>" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs">
        </div>
        <div>
          <label class="block text-slate-700 font-bold mb-1">Condition *</label>
          <select name="condition" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs bg-white">
            <option value="Brand New (OEM / Factory Direct)">Brand New (OEM / Factory Direct)</option>
            <option value="Like New / Surplus Unused">Like New / Surplus Unused</option>
            <option value="Excellent (Running Condition)" selected>Excellent (Running Condition)</option>
            <option value="Good - In Daily Production">Good - In Daily Production</option>
            <option value="Refurbished & Tested">Refurbished & Tested</option>
          </select>
        </div>
        <div>
          <label class="block text-slate-700 font-bold mb-1">Equipment Classification</label>
          <input type="text" name="equipment_type" placeholder="e.g. CNC Vertical Machining Center" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs">
        </div>
      </div>

      <!-- Section: Pricing Box (INR & USD) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-amber-50/70 border-2 border-joule-yellow p-3.5 rounded-xl">
        <div>
          <label class="block text-slate-900 font-black mb-1">Asking Price in INR (₹) *</label>
          <input 
            type="number" 
            name="price" 
            id="addPriceInr" 
            oninput="autoCalcUsd(this.value)"
            required 
            placeholder="e.g. 4500000" 
            class="w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs font-black text-slate-900 bg-white"
          >
          <span class="text-[10px] text-slate-600 block mt-0.5">Auto-converted to Lakhs / Crore on live frontend</span>
        </div>
        <div>
          <label class="block text-slate-900 font-black mb-1">Asking Price in USD ($) *</label>
          <input 
            type="number" 
            name="price_usd" 
            id="addPriceUsd" 
            required 
            placeholder="e.g. 53000" 
            class="w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs font-black text-slate-900 bg-white"
          >
          <span class="text-[10px] text-slate-600 block mt-0.5">Shown to international and global buyers</span>
        </div>
      </div>

      <!-- Section: Location -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-slate-700 font-bold mb-1">Country *</label>
          <select name="country" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs bg-white font-semibold">
            <option value="India" selected>🇮🇳 India</option>
            <option value="Japan">🇯🇵 Japan</option>
            <option value="Germany">🇩🇪 Germany</option>
            <option value="Taiwan">🇹🇼 Taiwan</option>
            <option value="South Korea">🇰🇷 South Korea</option>
            <option value="China">🇨🇳 China</option>
            <option value="Switzerland">🇨🇭 Switzerland</option>
            <option value="Singapore">🇸🇬 Singapore</option>
            <option value="United States">🇺🇸 United States</option>
            <option value="United Kingdom">🇬🇧 United Kingdom</option>
            <option value="Italy">🇮🇹 Italy</option>
          </select>
        </div>
        <div>
          <label class="block text-slate-700 font-bold mb-1">City / Industrial Zone *</label>
          <input type="text" name="city" required placeholder="e.g. Chennai, Nagoya, Dusseldorf" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs">
        </div>
      </div>

      <!-- Section: Engineering Specs -->
      <div class="bg-slate-50 border border-slate-200 p-3.5 rounded-xl space-y-3">
        <div class="font-black text-slate-800 text-[11px] uppercase tracking-wider flex items-center gap-1.5">
          <i class="fa-solid fa-sliders text-joule-brightblue"></i>
          <span>Key Engineering Parameters</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
          <div>
            <label class="block text-slate-600 text-[10px] font-bold mb-0.5">Capacity / Tonnage / Table</label>
            <input type="text" name="capacity" placeholder="e.g. 1270 x 508 mm" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs bg-white">
          </div>
          <div>
            <label class="block text-slate-600 text-[10px] font-bold mb-0.5">Stroke / Axis Travel</label>
            <input type="text" name="stroke_length" placeholder="e.g. 1270 x 508 x 635 mm" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs bg-white">
          </div>
          <div>
            <label class="block text-slate-600 text-[10px] font-bold mb-0.5">Operating Speed / RPM</label>
            <input type="text" name="speed_spm" placeholder="e.g. 10,000 RPM Inline" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs bg-white">
          </div>
          <div>
            <label class="block text-slate-600 text-[10px] font-bold mb-0.5">Main Motor Power</label>
            <input type="text" name="motor_power" placeholder="e.g. 22.4 kW (30 HP)" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs bg-white">
          </div>
          <div>
            <label class="block text-slate-600 text-[10px] font-bold mb-0.5">Total Weight</label>
            <input type="text" name="machine_weight" placeholder="e.g. 5,900 kg" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs bg-white">
          </div>
          <div>
            <label class="block text-slate-600 text-[10px] font-bold mb-0.5">Dimensions (L x W x H)</label>
            <input type="text" name="dimensions" placeholder="e.g. 3.2m x 2.4m x 2.7m" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs bg-white">
          </div>
        </div>
      </div>

      <!-- Section: Machine Photos -->
      <div>
        <label class="block text-slate-700 font-bold mb-1">
          Machine Photos (Image Paths or URLs)
        </label>
        <input 
          type="text" 
          name="images" 
          placeholder="e.g. assets/images/machines/1/used-dingli-jcpt1412hd-scissor-lift-01.jpg (separate multiple with commas)" 
          class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-mono"
        >
        <span class="text-[10px] text-slate-500 block mt-0.5">You can provide local paths or web URLs. If left empty, default machine visual is applied.</span>
      </div>

      <!-- Section: Descriptions -->
      <div>
        <label class="block text-slate-700 font-bold mb-1">Short Description (Catalog Cards)</label>
        <textarea name="short_desc" rows="2" placeholder="Brief 1-2 sentence overview for catalog cards" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs"></textarea>
      </div>

      <div>
        <label class="block text-slate-700 font-bold mb-1">Comprehensive Technical Overview</label>
        <textarea name="detailed_desc" rows="3" placeholder="Full engineering parameters, tooling packages, control units, and factory inspection status..." class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs"></textarea>
      </div>

      <!-- Footer Action Buttons -->
      <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
        <button type="button" onclick="closeAddMachineModal()" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-700 font-bold text-xs hover:bg-slate-100 cursor-pointer">
          Cancel
        </button>
        <button 
          type="submit" 
          id="saveNewMachineBtn" 
          class="bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black py-2.5 px-6 rounded-xl text-xs shadow-md flex items-center gap-2 cursor-pointer border border-amber-300 transition-transform hover:scale-105"
        >
          <i class="fa-solid fa-database"></i>
          <span>Save Data in Database & Publish</span>
        </button>
      </div>
    </form>

  </div>
</div>

<script>
const ALL_MACHINES = <?php echo json_encode($machines, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

function switchAdminTab(tabId) {
  document.querySelectorAll('.admin-tab-content').forEach(el => el.classList.add('hidden'));
  document.querySelectorAll('.admin-tab-btn').forEach(btn => {
    btn.classList.remove('bg-joule-blue', 'text-white', 'shadow-sm');
    btn.classList.add('text-slate-700', 'hover:bg-slate-100');
  });

  const target = document.getElementById(tabId);
  if (target) target.classList.remove('hidden');

  let activeBtnId = 'btnProductsTab';
  if (tabId === 'imagesTab') activeBtnId = 'btnImagesTab';
  if (tabId === 'leadsTab') activeBtnId = 'btnLeadsTab';

  const btn = document.getElementById(activeBtnId);
  if (btn) {
    btn.classList.remove('text-slate-700', 'hover:bg-slate-100');
    btn.classList.add('bg-joule-blue', 'text-white', 'shadow-sm');
  }

  if (tabId === 'imagesTab') {
    const sel = document.getElementById('rotatorMachineSelect');
    if (sel) renderRotatorGallery(sel.value);
  }
}

function filterAdminMachines() {
  const query = document.getElementById('adminSearchInput').value.toLowerCase().trim();
  const cards = document.querySelectorAll('#adminMachinesContainer .machine-card');
  let visible = 0;

  cards.forEach(card => {
    const searchData = card.getAttribute('data-search') || '';
    if (searchData.includes(query)) {
      card.style.display = 'flex';
      visible++;
    } else {
      card.style.display = 'none';
    }
  });

  const countEl = document.getElementById('visibleMachineCount');
  if (countEl) countEl.innerText = visible;
}

function openEditModal(machine) {
  document.getElementById('editMachineId').value = machine.id || '';
  document.getElementById('editTitle').value = machine.title || '';
  document.getElementById('editBrand').value = machine.brand || '';
  document.getElementById('editModel').value = machine.model || '';
  document.getElementById('editCategory').value = machine.category || '';
  document.getElementById('editYear').value = machine.year || '';
  document.getElementById('editCondition').value = machine.condition || '';
  document.getElementById('editPrice').value = machine.price || '';
  document.getElementById('editPriceUsd').value = machine.price_usd || '';
  document.getElementById('editCity').value = machine.city || '';
  document.getElementById('editCountry').value = machine.country || '';
  document.getElementById('editShortDesc').value = machine.short_desc || '';
  document.getElementById('editDetailedDesc').value = machine.detailed_desc || '';
  
  document.getElementById('editModalHeading').innerText = 'Edit #' + machine.id + ': ' + machine.title;
  document.getElementById('editProductModal').classList.remove('hidden');
}

function closeEditModal() {
  document.getElementById('editProductModal').classList.add('hidden');
}

function openAddMachineModal() {
  const form = document.getElementById('addMachineForm');
  if (form) form.reset();
  const modal = document.getElementById('addMachineModal');
  if (modal) modal.classList.remove('hidden');
}

function closeAddMachineModal() {
  const modal = document.getElementById('addMachineModal');
  if (modal) modal.classList.add('hidden');
}

function autoCalcUsd(inrVal) {
  const inr = parseFloat(inrVal) || 0;
  const usdInput = document.getElementById('addPriceUsd');
  if (usdInput && inr > 0) {
    usdInput.value = Math.round(inr / 85);
  }
}

function saveNewMachine(e) {
  e.preventDefault();
  const form = document.getElementById('addMachineForm');
  const btn = document.getElementById('saveNewMachineBtn');
  const origHtml = btn ? btn.innerHTML : '';

  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Saving in Database...</span>';
  }

  const formData = new FormData(form);

  fetch('/api/add-machine.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('✓ Success! New machine listing added and saved in MySQL database (ID: ' + data.machine_id + '). It is now live on the site!');
      closeAddMachineModal();
      window.location.reload();
    } else {
      alert('Error: ' + (data.error || 'Failed to add machine.'));
    }
  })
  .catch(err => {
    alert('Network error while adding machine: ' + err);
  })
  .finally(() => {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = origHtml;
    }
  });
}

function purgeLiveCache() {
  const btn = document.getElementById('purgeCacheBtn');
  const origHtml = btn ? btn.innerHTML : '';
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Purging...</span>';
  }

  fetch('/api/clear-cache.php')
    .then(res => res.json())
    .then(data => {
      alert('✓ All Caches Purged! Hostinger LiteSpeed server cache and OPcache cleared. Refreshing page...');
      window.location.href = window.location.pathname + '?v=' + Date.now();
    })
    .catch(err => {
      window.location.href = '/clear-cache.php';
    })
    .finally(() => {
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = origHtml;
      }
    });
}

function saveProductChanges(e) {
  e.preventDefault();
  const form = document.getElementById('productEditForm');
  const btn = document.getElementById('saveEditBtn');
  const origHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

  const formData = new FormData(form);
  const json = {};
  formData.forEach((value, key) => json[key] = value);

  fetch('/api/update-product.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(json)
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Product saved successfully! The updates are now live.');
      closeEditModal();
      window.location.reload();
    } else {
      alert('Error: ' + (data.error || 'Failed to save product.'));
    }
  })
  .catch(err => {
    alert('Network error while saving: ' + err);
  })
  .finally(() => {
    btn.disabled = false;
    btn.innerHTML = origHtml;
  });
}

function jumpToImageRotator(machineId) {
  switchAdminTab('imagesTab');
  const sel = document.getElementById('rotatorMachineSelect');
  if (sel) {
    sel.value = machineId;
    renderRotatorGallery(machineId);
  }
}

function renderRotatorGallery(machineId) {
  const container = document.getElementById('rotatorGalleryContainer');
  if (!container) return;
  const machine = ALL_MACHINES.find(m => m.id === machineId);
  if (!machine || !machine.images) {
    container.innerHTML = '<div class="col-span-3 text-center text-slate-500 py-10">Machine photos not found.</div>';
    return;
  }

  let html = '';
  machine.images.forEach((img, idx) => {
    const imgId = 'rot_img_' + idx;
    html += `
      <div class="bg-white rounded-2xl border-2 border-slate-200 p-4 space-y-3 shadow-xs">
        <div class="flex items-center justify-between text-xs">
          <span class="font-black text-slate-900">Photo #${idx + 1}</span>
          <span class="text-[10px] text-slate-500 font-mono truncate max-w-[150px]">${img.split('/').pop()}</span>
        </div>

        <div class="aspect-[4/3] bg-slate-950 rounded-xl overflow-hidden flex items-center justify-center border border-slate-200">
          <img id="${imgId}" src="${img}?v=${Date.now()}" alt="Photo" class="w-full h-full object-contain p-1 transition-transform">
        </div>

        <div class="grid grid-cols-2 gap-2 pt-1">
          <button 
            type="button" 
            onclick="rotateImage('${img}', '90_ccw', '${imgId}')"
            class="bg-blue-50 hover:bg-blue-100 text-joule-brightblue font-black py-2 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer border border-blue-200"
          >
            <i class="fa-solid fa-rotate-left"></i>
            <span>Rotate 90° Left</span>
          </button>

          <button 
            type="button" 
            onclick="rotateImage('${img}', '90_cw', '${imgId}')"
            class="bg-blue-50 hover:bg-blue-100 text-joule-brightblue font-black py-2 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer border border-blue-200"
          >
            <i class="fa-solid fa-rotate-right"></i>
            <span>Rotate 90° Right</span>
          </button>
        </div>
      </div>
    `;
  });

  container.innerHTML = html;
}

function rotateImage(imagePath, direction, elementId) {
  const imgEl = document.getElementById(elementId);
  if (imgEl) imgEl.style.opacity = '0.5';

  fetch('/api/rotate-image.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ image_path: imagePath, direction: direction })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      if (imgEl) {
        imgEl.src = imagePath + '?v=' + Date.now();
        imgEl.style.opacity = '1';
      }
    } else {
      alert('Error rotating image: ' + (data.error || 'Server error'));
      if (imgEl) imgEl.style.opacity = '1';
    }
  })
  .catch(err => {
    alert('Network error: ' + err);
    if (imgEl) imgEl.style.opacity = '1';
  });
}
function sendTestEmail() {
  const btn = document.getElementById('testMailBtn');
  const resultDiv = document.getElementById('testMailResult');
  const origText = btn ? btn.innerHTML : '';
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Sending Test Emails...</span>';
  }
  if (resultDiv) resultDiv.classList.add('hidden');

  fetch('/api/test-mail.php')
    .then(res => res.json())
    .then(data => {
      if (resultDiv) {
        resultDiv.classList.remove('hidden');
        if (data.success) {
          resultDiv.className = 'p-3 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs';
          resultDiv.innerHTML = '<strong>✓ Test Email Dispatched!</strong> Sent to all 3 mailboxes: ' + (data.recipients || []).join(', ') + '. Please check Inboxes and Spam/Junk folders.';
        } else {
          resultDiv.className = 'p-3 rounded-xl bg-rose-50 border border-rose-300 text-rose-900 text-xs';
          resultDiv.innerHTML = '<strong>Error:</strong> ' + (data.error || 'Failed to dispatch test emails');
        }
      }
    })
    .catch(err => {
      if (resultDiv) {
        resultDiv.classList.remove('hidden');
        resultDiv.className = 'p-3 rounded-xl bg-rose-50 border border-rose-300 text-rose-900 text-xs';
        resultDiv.innerHTML = '<strong>Network Error:</strong> ' + err;
      }
    })
    .finally(() => {
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = origText;
      }
    });
}

function saveSmtpSettings() {
  const host = document.getElementById('smtpHost').value.trim();
  const username = document.getElementById('smtpUser').value.trim();
  const password = document.getElementById('smtpPass').value;
  const enabled = document.getElementById('smtpEnabled').checked;

  fetch('/api/save-smtp.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ host, username, password, enabled, port: 465, encryption: 'ssl' })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('✓ Hostinger SMTP settings saved! Now click "Send Test Email" to verify direct Inbox delivery.');
    } else {
      alert('Error saving SMTP settings: ' + (data.error || 'Server error'));
    }
  })
  .catch(err => alert('Network error: ' + err));
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
